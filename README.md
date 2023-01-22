<a class="badge" href="https://github.com/aimeos/map/actions"><img src="https://github.com/aimeos/map/actions/workflows/php.yml/badge.svg" alt="Build Status" height="20"></a>
<a class="badge" href="https://coveralls.io/github/aimeos/map"><img src="https://coveralls.io/repos/github/aimeos/map/badge.svg" alt="Coverage Status" height="20"></a>
<a class="badge" href="https://packagist.org/packages/aimeos/map"><img src="https://poser.pugx.org/aimeos/map/license.svg" alt="License" height="20"></a>
<a class="badge" href="https://packagist.org/packages/aimeos/map"><img src="https://poser.pugx.org/aimeos/map/v/stable" alt="Latest Stable Version" height="20"></a>
<a class="badge" href="https://packagist.org/packages/aimeos/map"><img src="https://badgen.net/github/stars/aimeos/map" alt="Stars" height="20"></a>
<a class="badge" href="https://packagist.org/packages/aimeos/map"><img src="https://poser.pugx.org/aimeos/map/downloads" alt="Downloads" height="20"></a>

# PHP arrays and collections made easy

Easy and elegant handling of PHP arrays by using an array-like collection object
as offered by jQuery and Laravel Collections.

```bash
composer req aimeos/map
```

**Table of contents**

* [Why PHP Map](#why-php-map)
* [List of methods](#methods)
    * [Create](#create)
    * [Access](#access)
    * [Add](#add)
    * [Aggregate](#aggregate)
    * [Debug](#debug)
    * [Order](#orderby)
    * [Shorten](#shorten)
    * [Test](#test)
    * [Transform](#transform)
    * [Misc](#misc)
* [Documentation](#method-documentation)
* [Custom methods](#custom-methods)
* [Performance](#performance)
* [Upgrade guide](#upgrade-guide)

## Why PHP Map

**Instead of:**

```php
$list = [['id' => 'one', 'value' => 'value1'], ['id' => 'two', 'value' => 'value2'], null];
$list[] = ['id' => 'three', 'value' => 'value3'];    // add element
unset( $list[0] );                                   // remove element
$list = array_filter( $list );                       // remove empty values
sort( $list );                                       // sort elements
$pairs = array_column( $list, 'value', 'id' );       // create ['three' => 'value3']
$value = reset( $pairs ) ?: null;                    // return first value
```

**Only use:**

```php
$list = [['id' => 'one', 'value' => 'value1'], ['id' => 'two', 'value' => 'value2'], null];
$value = map( $list )                                // create Map
    ->push( ['id' => 'three', 'value' => 'value3'] ) // add element
    ->remove( 0 )                                    // remove element
    ->filter()                                       // remove empty values
    ->sort()                                         // sort elements
    ->col( 'value', 'id' )                           // create ['three' => 'value3']
    ->first();                                       // return first value
```

**You can still use:**

```php
$map[] = ['id' => 'three', 'value' => 'value3'];
$value = $map[0];
count( $map );
foreach( $map as $key => value );
```

**Use callbacks:**

Also, the map object allows you to pass anonymous functions to a lot of methods, e.g.:

```php
$map->each( function( $val, $key ) {
	echo $key . ': ' . $val;
} );
```

**jQuery style:**

If your map elements are objects, you can call their methods for each object and get
the result as new map just like in jQuery:

```php
// MyClass implements setStatus() (returning $this) and getCode() (initialized by constructor)

$map = Map::from( ['a' => new MyClass( 'x' ), 'b' => new MyClass( 'y' )] );
$map->setStatus( 1 )->getCode()->toArray();
```

This will call `setStatus( 1 )` on both objects. If `setStatus()` implementation
returns `$this`, the new map will also contain:

```php
['a' => MyClass(), 'b' => MyClass()]
```

On those new map elements, `getCode()` will be called which returns `x` for the
first object and `y` for the second. The map created from the results of `getCode()`
will return:

```php
['a' => 'x', 'b' => 'y']
```


## Methods

<nav>

<a href="#map-function">function map</a>
<a href="#is_map-function">function is_map</a>
<a href="#__call">__call</a>
<a href="#__callstatic">__callStatic</a>
<a href="#__construct">__construct</a>
<a href="#after">after</a>
<a href="#all">all</a>
<a href="#arsort">arsort</a>
<a href="#asort">asort</a>
<a href="#at">at</a>
<a href="#avg">avg</a>
<a href="#before">before</a>
<a href="#bool">bool</a>
<a href="#call">call</a>
<a href="#cast">cast</a>
<a href="#chunk">chunk</a>
<a href="#clear">clear</a>
<a href="#clone">clone</a>
<a href="#col">col</a>
<a href="#collapse">collapse</a>
<a href="#combine">combine</a>
<a href="#compare">compare</a>
<a href="#concat">concat</a>
<a href="#contains">contains</a>
<a href="#copy">copy</a>
<a href="#count">count</a>
<a href="#countby">countBy</a>
<a href="#dd">dd</a>
<a href="#delimiter">delimiter</a>
<a href="#diff">diff</a>
<a href="#diffassoc">diffAssoc</a>
<a href="#diffkeys">diffKeys</a>
<a href="#dump">dump</a>
<a href="#duplicates">duplicates</a>
<a href="#each">each</a>
<a href="#empty">empty</a>
<a href="#equals">equals</a>
<a href="#every">every</a>
<a href="#except">except</a>
<a href="#explode">explode</a>
<a href="#filter">filter</a>
<a href="#find">find</a>
<a href="#first">first</a>
<a href="#firstkey">firstKey</a>
<a href="#flat">flat</a>
<a href="#flip">flip</a>
<a href="#float">float</a>
<a href="#from">from</a>
<a href="#fromjson">fromJson</a>
<a href="#get">get</a>
<a href="#getiterator">getIterator</a>
<a href="#grep">grep</a>
<a href="#groupby">groupBy</a>
<a href="#has">has</a>
<a href="#if">if</a>
<a href="#ifany">ifAny</a>
<a href="#ifempty">ifEmpty</a>
<a href="#implements">implements</a>
<a href="#in">in</a>
<a href="#includes">includes</a>
<a href="#index">index</a>
<a href="#insertafter">insertAfter</a>
<a href="#insertat">insertAt</a>
<a href="#insertbefore">insertBefore</a>
<a href="#instring">inString</a>
<a href="#int">int</a>
<a href="#intersect">intersect</a>
<a href="#intersectassoc">intersectAssoc</a>
<a href="#intersectkeys">intersectKeys</a>
<a href="#is">is</a>
<a href="#isempty">isEmpty</a>
<a href="#isnumeric">isNumeric</a>
<a href="#isobject">isObject</a>
<a href="#isscalar">isScalar</a>
<a href="#join">join</a>
<a href="#jsonserialize">jsonSerialize</a>
<a href="#keys">keys</a>
<a href="#krsort">krsort</a>
<a href="#ksort">ksort</a>
<a href="#last">last</a>
<a href="#lastkey">lastKey</a>
<a href="#ltrim">ltrim</a>
<a href="#map">map</a>
<a href="#max">max</a>
<a href="#merge">merge</a>
<a href="#method">method</a>
<a href="#min">min</a>
<a href="#none">none</a>
<a href="#nth">nth</a>
<a href="#offsetexists">offsetExists</a>
<a href="#offsetget">offsetGet</a>
<a href="#offsetset">offsetSet</a>
<a href="#offsetunset">offsetUnset</a>
<a href="#only">only</a>
<a href="#order">order</a>
<a href="#pad">pad</a>
<a href="#partition">partition</a>
<a href="#pipe">pipe</a>
<a href="#pluck">pluck</a>
<a href="#pop">pop</a>
<a href="#pos">pos</a>
<a href="#prefix">prefix</a>
<a href="#prepend">prepend</a>
<a href="#pull">pull</a>
<a href="#push">push</a>
<a href="#put">put</a>
<a href="#random">random</a>
<a href="#reduce">reduce</a>
<a href="#reject">reject</a>
<a href="#rekey">rekey</a>
<a href="#remove">remove</a>
<a href="#replace">replace</a>
<a href="#reverse">reverse</a>
<a href="#rsort">rsort</a>
<a href="#rtrim">rtrim</a>
<a href="#search">search</a>
<a href="#sep">sep</a>
<a href="#set">set</a>
<a href="#shift">shift</a>
<a href="#shuffle">shuffle</a>
<a href="#skip">skip</a>
<a href="#slice">slice</a>
<a href="#some">some</a>
<a href="#sort">sort</a>
<a href="#splice">splice</a>
<a href="#split">split</a>
<a href="#strafter">strAfter</a>
<a href="#strcontains">strContains</a>
<a href="#strcontainsall">strContainsAll</a>
<a href="#strends">strEnds</a>
<a href="#strendsall">strEndsAll</a>
<a href="#string">string</a>
<a href="#strlower">strLower</a>
<a href="#strreplace">strReplace</a>
<a href="#strstarts">strStarts</a>
<a href="#strstartsall">strStartsAll</a>
<a href="#strupper">strUpper</a>
<a href="#suffix">suffix</a>
<a href="#sum">sum</a>
<a href="#take">take</a>
<a href="#tap">tap</a>
<a href="#times">times</a>
<a href="#toarray">toArray</a>
<a href="#tojson">toJson</a>
<a href="#tourl">toUrl</a>
<a href="#transpose">transpose</a>
<a href="#traverse">traverse</a>
<a href="#tree">tree</a>
<a href="#trim">trim</a>
<a href="#uasort">uasort</a>
<a href="#uksort">uksort</a>
<a href="#union">union</a>
<a href="#unique">unique</a>
<a href="#unshift">unshift</a>
<a href="#usort">usort</a>
<a href="#values">values</a>
<a href="#walk">walk</a>
<a href="#where">where</a>
<a href="#zip">zip</a>

</nav>

### Create

* [function map()](#map-function) : Creates a new map from passed elements
* [__construct()](#__construct) : Creates a new map
* [clone()](#clone) : Clones the map and all objects within
* [copy()](#copy) : Creates a new copy
* [explode()](#explode) : Splits a string into a map of elements
* [from()](#from) : Creates a new map from passed elements
* [fromJson()](#fromjson) : Creates a new map from a JSON string
* [times()](#times) : Creates a new map by invoking the closure a number of times
* [tree()](#tree) : Creates a tree structure from the list items

### Access

* [__call()](#__call) : Calls a custom method
* [__callStatic()](#__callstatic) : Calls a custom method statically
* [all()](#all) : Returns the plain array
* [at()](#at) : Returns the value at the given position
* [bool()](#bool) : Returns an element by key and casts it to boolean
* [call()](#call) : Calls the given method on all items
* [find()](#find) : Returns the first/last matching element
* [first()](#first) : Returns the first element
* [firstKey()](#firstkey) : Returns the first key
* [get()](#get) : Returns an element by key
* [index()](#index) : Returns the numerical index of the given key
* [int()](#int) : Returns an element by key and casts it to integer
* [float()](#float) : Returns an element by key and casts it to float
* [keys()](#keys) : Returns all keys
* [last()](#last) : Returns the last element
* [lastKey()](#lastkey) : Returns the last key
* [pop()](#pop) : Returns and removes the last element
* [pos()](#pos) : Returns the numerical index of the value
* [pull()](#pull) : Returns and removes an element by key
* [random()](#random) : Returns random elements preserving keys
* [search()](#search) : Find the key of an element
* [shift()](#shift) : Returns and removes the first element
* [string()](#string) : Returns an element by key and casts it to string
* [toArray()](#toarray) : Returns the plain array
* [unique()](#unique) : Returns all unique elements preserving keys
* [values()](#values) : Returns all elements with new keys

### Add

* [concat()](#concat) : Adds all elements with new keys
* [insertAfter()](#insertafter) : Inserts the value after the given element
* [insertAt()](#insertat) : Inserts the element at the given position in the map
* [insertBefore()](#insertbefore) : Inserts the value before the given element
* [merge()](#merge) : Combines elements overwriting existing ones
* [pad()](#pad) : Fill up to the specified length with the given value
* [prepend()](#prepend) : Adds an element at the beginning
* [push()](#push) : Adds an element to the end
* [put()](#put) : Sets the given key and value in the map
* [set()](#set) : Overwrites or adds an element
* [union()](#union) : Adds the elements without overwriting existing ones
* [unshift()](#unshift) : Adds an element at the beginning

### Aggregate

* [avg()](#avg) : Returns the average of all values
* [count()](#count) : Returns the total number of elements
* [countBy()](#countby) : Counts how often the same values are in the map
* [max()](#max) : Returns the maximum value of all elements
* [min()](#max) : Returns the minium value of all elements
* [sum()](#sum) : Returns the sum of all values in the map

### Debug

* [dd()](#dd) : Prints the map content and terminates the script
* [dump()](#dump) : Prints the map content
* [tap()](#tap) : Passes a clone of the map to the given callback

### OrderBy

* [arsort()](#arsort) : Reverse sort elements preserving keys
* [asort()](#asort) : Sort elements preserving keys
* [krsort()](#krsort) : Reverse sort elements by keys
* [ksort()](#ksort) : Sort elements by keys
* [order()](#order) : Orders elements by the passed keys
* [reverse()](#reverse) : Reverses the array order preserving keys
* [rsort()](#rsort) : Reverse sort elements using new keys
* [shuffle()](#shuffle) : Randomizes the element order
* [sort()](#sort) : Sorts the elements assigning new keys
* [uasort()](#uasort) : Sorts elements preserving keys using callback
* [uksort()](#uksort) : Sorts elements by keys using callback
* [usort()](#usort) : Sorts elements using callback assigning new keys

### Shorten

* [after()](#after) : Returns the elements after the given one
* [before()](#before) : Returns the elements before the given one
* [clear()](#clear) : Removes all elements
* [diff()](#diff) : Returns the elements missing in the given list
* [diffAssoc()](#diffassoc) : Returns the elements missing in the given list and checks keys
* [diffKeys()](#diffkeys) : Returns the elements missing in the given list by keys
* [except()](#except) : Returns a new map without the passed element keys
* [filter()](#filter) : Applies a filter to all elements
* [grep()](#grep) : Applies a regular expression to all elements
* [intersect()](#intersect) : Returns the elements shared
* [intersectAssoc()](#intersectassoc) : Returns the elements shared and checks keys
* [intersectKeys()](#intersectkeys) : Returns the elements shared by keys
* [nth()](#nth) : Returns every nth element from the map
* [only()](#only) : Returns only those elements specified by the keys
* [pop()](#pop) : Returns and removes the last element
* [pull()](#pull) : Returns and removes an element by key
* [reject()](#reject) : Removes all matched elements
* [remove()](#remove) : Removes an element by key
* [shift()](#shift) : Returns and removes the first element
* [skip()](#skip) : Skips the given number of items and return the rest
* [slice()](#slice) : Returns a slice of the map
* [take()](#take) : Returns a new map with the given number of items
* [where()](#where) : Filters the list of elements by a given condition

### Test

* [function is_map()](#is_map-function) : Tests if the variable is a map object
* [compare()](#compare) : Compares the value against all map elements
* [contains()](#contains) : Tests if an item exists in the map
* [each()](#each) : Applies a callback to each element
* [empty()](#empty) : Tests if map is empty
* [equals()](#equals) : Tests if map contents are equal
* [every()](#every) : Verifies that all elements pass the test of the given callback
* [has()](#has) : Tests if a key exists
* [if()](#if) : Executes callbacks depending on the condition
* [ifAny()](#ifany) : Executes callbacks if the map contains elements
* [ifEmpty()](#ifempty) : Executes callbacks if the map is empty
* [in()](#in) : Tests if element is included
* [includes()](#includes) : Tests if element is included
* [inString()](#instring) : Tests if the item is part of the strings in the map
* [is()](#is) : Tests if the map consists of the same keys and values
* [isEmpty()](#isempty) : Tests if map is empty
* [isNumeric()](#isnumeric) : Tests if all entries are numeric values
* [isObject()](#isobject) : Tests if all entries are objects
* [isScalar()](#isscalar) : Tests if all entries are scalar values.
* [implements()](#implements) : Tests if all entries are objects implementing the interface
* [none()](#none) : Tests if none of the elements are part of the map
* [some()](#some) : Tests if at least one element is included
* [strContains()](#strcontains) : Tests if at least one of the passed strings is part of at least one entry
* [strContainsAll()](#strcontainsall) : Tests if all of the entries contains one of the passed strings
* [strEnds()](#strends) : Tests if at least one of the entries ends with one of the passed strings
* [strEndsAll()](#strendsall) : Tests if all of the entries ends with at least one of the passed strings
* [strStarts()](#strstarts) : Tests if at least one of the entries starts with at least one of the passed strings
* [strStartsAll()](#strstartsall) : Tests if all of the entries starts with one of the passed strings

### Transform

* [cast()](#cast) : Casts all entries to the passed type
* [chunk()](#chunk) : Splits the map into chunks
* [col()](#col) : Creates a key/value mapping
* [collapse()](#collapse) : Collapses multi-dimensional elements overwriting elements
* [combine()](#combine) : Combines the map elements as keys with the given values
* [flat()](#flat) : Flattens multi-dimensional elements without overwriting elements
* [flip()](#flip) : Exchanges keys with their values
* [groupBy()](#groupby) : Groups associative array elements or objects
* [join()](#join) : Returns concatenated elements as string with separator
* [ltrim()](#ltrim) : Removes the passed characters from the left of all strings
* [map()](#map) : Applies a callback to each element and returns the results
* [partition()](#partition) : Breaks the list into the given number of groups
* [pipe()](#pipe) : Applies a callback to the whole map
* [pluck()](#pluck) : Creates a key/value mapping
* [prefix()](#prefix) : Adds a prefix to each map entry
* [reduce()](#reduce) : Computes a single value from the map content
* [rekey()](#rekey) : Changes the keys according to the passed function
* [replace()](#replace) : Replaces elements recursively
* [rtrim()](#rtrim) : Removes the passed characters from the right of all strings
* [splice()](#splice) : Replaces a slice by new elements
* [strAfter()](#strafter) : Returns the strings after the passed value
* [strLower()](#strlower) : Converts all alphabetic characters to lower case
* [strReplace()](#strreplace) : Replaces all occurrences of the search string with the replacement string
* [strUpper()](#strupper) : Converts all alphabetic characters to upper case
* [suffix()](#suffix) : Adds a suffix to each map entry
* [toJson()](#tojson) : Returns the elements in JSON format
* [toUrl()](#tourl) : Creates a HTTP query string
* [transpose()](#transpose) : Exchanges rows and columns for a two dimensional map
* [traverse()](#traverse) : Traverses trees of nested items passing each item to the callback
* [trim()](#trim) : Removes the passed characters from the left/right of all strings
* [walk()](#walk) : Applies the given callback to all elements
* [zip()](#zip) : Merges the values of all arrays at the corresponding index

### Misc

* [delimiter()](#delimiter) : Sets or returns the seperator for paths to multi-dimensional arrays
* [getIterator()](#getiterator) : Returns an iterator for the elements
* [jsonSerialize()](#jsonserialize) : Specifies the data which should be serialized to JSON
* [method()](#method) : Registers a custom method
* [offsetExists()](#offsetexists) : Checks if the key exists
* [offsetGet()](#offsetget) : Returns an element by key
* [offsetSet()](#offsetset) : Overwrites an element
* [offsetUnset()](#offsetunset) : Removes an element by key
* [sep()](#sep) : Sets the seperator for paths to multi-dimensional arrays in the current map



## Method documentation

### is_map() function

Tests if the variable is a map object

```php
function is_map( $var ) : bool
```

* @param **mixed** `$var` Variable to test

**Examples:**

```php
is_map( new Map() );
// true

is_map( [] );
// false
```


### map() function

Returns a new map for the passed elements.

```php
function map( $elements = [] ) : \Aimeos\Map
```

* @param **mixed** `$elements` List of elements or single value
* @return **\Aimeos\Map** Map instance

**Examples:**

```php
// array
map( [] );

// null
map( null );

// scalar
map( 'a' );

// object
map( new \stdClass() );

// map object
map( new Map() );

// iterable object
map( new ArrayObject() );

// closure evaluated lazily
map( function() {
    return [];
} );
```


### __construct()

Creates a new map object.

```php
public function __construct( $elements = [] )
```

* @param **mixed** `$elements` Single element, list of elements, Map object, iterable objects or iterators, everything else

**Examples:**

```php
// array
new Map( [] );

// null
new Map( null );

// scalar
new Map( 'a' );

// object
new Map( new \stdClass() );

// map object
new Map( new Map() );

// iterable object
new Map( new ArrayObject() );

// closure evaluated lazily
new Map( function() {
    return [];
} );
```


### __call()

Handles dynamic calls to custom methods for the class.

```php
public function __call( string $name, array $params )
```

* @param **string** `$name` Method name
* @param **array&#60;mixed&#62;** `$params` List of parameters
* @return **mixed** Result from called function or new map with results from the element methods

Calls a custom method added by [Map::method()](#method). The called method
has access to the internal array by using `$this->items`.

**Examples:**

```php
Map::method( 'case', function( $case = CASE_LOWER ) {
    return new self( array_change_key_case( $this->items, $case ) );
} );

Map::from( ['a' => 'bar'] )->case( CASE_UPPER );
// ['A' => 'bar']
```

This does also allow calling object methods if the items are objects:

```php
$item = new MyClass(); // with method setStatus() (returning $this) and getCode() implemented
Map::from( [$item, $item] )->setStatus( 1 )->getCode()->toArray();
```

This will call the `setStatus()` method of each element in the map and
use their return values to create a new map. On the new map, the `getCode()`
method is called for every element and its return values are also stored in a new
map. This last map is then returned and the map keys from the original map are
preserved in the returned map.

If the elements are not objects, they are skipped and if this applies to all
elements, an empty map is returned. In case the map contains objects of mixed
types and one of them doesn't implement the called method, an error will be thrown.


### __callStatic()

Handles static calls to custom methods for the class.

```php
public static function __callStatic( string $name, array $params )
```

* @param **string** `$name` Method name
* @param **array&#60;mixed&#62;** `$params` List of parameters
* @return **mixed** Result from called function or new map with results from the element methods
* @throws **\BadMethodCallException** If no method has been registered for that name

Calls a custom method added by [Map::method()](#method) statically. The called method
has no access to the internal array because no object is available.

**Examples:**

```php
Map::method( 'foo', function( $arg1, $arg2 ) {} );
Map::foo( $arg1, $arg2 );
```


### after()

Returns the elements after the given one.

```php
public function after( $value ) : self
```

* @param **\Closure&#124;int&#124;string** `$value` Value or function with (item, key) parameters
* @return **self&#60;int&#124;string,mixed&#62;** New map with the elements after the given one

The keys are preserved using this method.

**Examples:**

```php
Map::from( [0 => 'b', 1 => 'a'] )->after( 'b' );
// [1 => 'a']

Map::from( ['a' => 1, 'b' => 0] )->after( 1 );
// ['b' => 0]

Map::from( [0 => 'b', 1 => 'a'] )->after( 'c' );
// []

Map::from( ['a', 'c', 'b'] )->after( function( $item, $key ) {
    return $item >= 'c';
} );
// [2 => 'b']
```


### all()

Returns the elements as a plain array.

```php
public function all() : array
```

* @return **array** Plain array

**Examples:**

```php
Map::from( ['a'] )->all();
// ['a']
```


### arsort()

Sorts all elements in reverse order and maintains the key association.

```php
public function arsort( int $options = SORT_REGULAR ) : self
```

* @param **int** `$options` Sort options for `arsort()`
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

The keys are preserved using this method and no new map is created.

The `$options` parameter modifies how the values are compared. Possible parameter values are:
- SORT_REGULAR : compare elements normally (don't change types)
- SORT_NUMERIC : compare elements numerically
- SORT_STRING : compare elements as strings
- SORT_LOCALE_STRING : compare elements as strings, based on the current locale or changed by `setlocale()`
- SORT_NATURAL : compare elements as strings using "natural ordering" like `natsort()`
- SORT_FLAG_CASE : use SORT_STRING&#124;SORT_FLAG_CASE and SORT_NATURAL&#124;SORT_FLAG_CASE to sort strings case-insensitively

**Examples:**

```php
Map::from( ['b' => 0, 'a' => 1] )->arsort();
// ['a' => 1, 'b' => 0]

Map::from( ['a', 'b'] )->arsort();
// ['b', 'a']

Map::from( [0 => 'C', 1 => 'b'] )->arsort();
// [1 => 'b', 0 => 'C']

Map::from( [0 => 'C', 1 => 'b'] )->arsort( SORT_STRING|SORT_FLAG_CASE );
// [0 => 'C', 1 => 'b'] because 'C' -> 'c' and 'c' > 'b'
```


### asort()

Sorts all elements and maintains the key association.

```php
public function asort( int $options = SORT_REGULAR ) : self
```

* @param **int** `$options` Sort options for `asort()`
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

The keys are preserved using this method and no new map is created.

The parameter modifies how the values are compared. Possible parameter values are:
- SORT_REGULAR : compare elements normally (don't change types)
- SORT_NUMERIC : compare elements numerically
- SORT_STRING : compare elements as strings
- SORT_LOCALE_STRING : compare elements as strings, based on the current locale or changed by `setlocale()`
- SORT_NATURAL : compare elements as strings using "natural ordering" like `natsort()`
- SORT_FLAG_CASE : use SORT_STRING&#124;SORT_FLAG_CASE and SORT_NATURAL&#124;SORT_FLAG_CASE to sort strings case-insensitively

**Examples:**

```php
Map::from( ['a' => 1, 'b' => 0] )->asort();
// ['b' => 0, 'a' => 1]

Map::from( [0 => 'b', 1 => 'a'] )->asort();
// [1 => 'a', 0 => 'b']

Map::from( [0 => 'C', 1 => 'b'] )->asort();
// [0 => 'C', 1 => 'b'] because 'C' < 'b'

Map::from( [0 => 'C', 1 => 'b'] )->arsort( SORT_STRING|SORT_FLAG_CASE );
// [1 => 'b', 0 => 'C'] because 'C' -> 'c' and 'c' > 'b'
```


### at()

Returns the value at the given position.

```php
public function at( int $pos )
```

* @param **int** `$pos` Position of the value in the map
* @return **mixed&#134;null** Value at the given position or NULL if no value is available

The position starts from zero and a position of "0" returns the first element
of the map, "1" the second and so on. If the position is negative, the sequence
will start from the end of the map.

**Examples:**

```php
Map::from( [1, 3, 5] )->at( 0 );
// 1

Map::from( [1, 3, 5] )->at( 1 );
// 3

Map::from( [1, 3, 5] )->at( -1 );
// 5

Map::from( [1, 3, 5] )->at( 3 );
// NULL
```


### avg()

Returns the average of all integer and float values in the map.

```php
public function avg( string $key = null ) : float
```

* @param **string&#124;null** `$key` Key or path to the values in the nested array or object to compute the average for
* @return **float** Average of all elements or 0 if there are no elements in the map

This does also work for multi-dimensional arrays by passing the keys
of the arrays separated by the delimiter ("/" by default), e.g. "key1/key2/key3"
to get "val" from `['key1' => ['key2' => ['key3' => 'val']]]`. The same applies to
public properties of objects or objects implementing `__isset()` and `__get()` methods.

**Examples:**

```php
Map::from( [1, 3, 5] )->avg();
// 3

Map::from( [1, null, 5] )->avg();
// 2

Map::from( [1, 'sum', 5] )->avg();
// 2

Map::from( [['p' => 30], ['p' => 50], ['p' => 10]] )->avg( 'p' );
// 30

Map::from( [['i' => ['p' => 30]], ['i' => ['p' => 50]]] )->avg( 'i/p' );
// 40
```


### before()

Returns the elements before the given one.

```php
public function before( $value ) : self
```

* @param **\Closure&#124;int&#124;string** `$value` Value or function with (item, key) parameters
* @return **self&#60;int&#124;string,mixed&#62;** New map with the elements before the given one

The keys are preserved using this method.

**Examples:**

```php
Map::from( ['a' => 1, 'b' => 0] )->before( 0 );
// ['a' => 1]

Map::from( [0 => 'b', 1 => 'a'] )->before( 'a' );
// [0 => 'b']

Map::from( [0 => 'b', 1 => 'a'] )->before( 'c' );
// []

Map::from( ['a', 'c', 'b'] )->before( function( $item, $key ) {
    return $key >= 1;
} );
// [0 => 'a']
```


### bool()

Returns an element by key and casts it to boolean if possible.

```php
public function bool( $key, $default = false ) : bool
```

* @param **int&#124;string** `$key` Key or path to the requested item
* @param **mixed** `$default` Default value if key isn't found (will be casted to bool)
* @return **bool** Value from map or default value

This does also work to map values from multi-dimensional arrays by passing the keys
of the arrays separated by the delimiter ("/" by default), e.g. `key1/key2/key3`
to get `val` from `['key1' => ['key2' => ['key3' => 'val']]]`. The same applies to
public properties of objects or objects implementing `__isset()` and `__get()` methods.

**Examples:**

```php
Map::from( ['a' => true] )->bool( 'a' );
// true

Map::from( ['a' => '1'] )->bool( 'a' );
// true (casted to boolean)

Map::from( ['a' => 1.1] )->bool( 'a' );
// true (casted to boolean)

Map::from( ['a' => '10'] )->bool( 'a' );
// true (casted to boolean)

Map::from( ['a' => 'abc'] )->bool( 'a' );
// true (casted to boolean)

Map::from( ['a' => ['b' => ['c' => true]]] )->bool( 'a/b/c' );
// true

Map::from( [] )->bool( 'c', function() { return rand( 1, 2 ); } );
// true (value returned by closure is casted to boolean)

Map::from( [] )->bool( 'a', true );
// true (default value used)

Map::from( [] )->bool( 'a' );
// false

Map::from( ['b' => ''] )->bool( 'b' );
// false (casted to boolean)

Map::from( ['b' => null] )->bool( 'b' );
// false (null is not scalar)

Map::from( ['b' => [true]] )->bool( 'b' );
// false (arrays are not scalar)

Map::from( ['b' => '#resource'] )->bool( 'b' );
// false (resources are not scalar)

Map::from( ['b' => new \stdClass] )->bool( 'b' );
// false (objects are not scalar)

Map::from( [] )->bool( 'c', new \Exception( 'error' ) );
// throws exception
```


### call()

Calls the given method on all items and returns the result.

```php
public function call( string $name, array $params = [] ) : self
```

* @param **string** `$name` Method name
* @param **array&#60;mixed&#62;** `$params` List of parameters
* @return **self&#60;int&#124;string,mixed&#62;** New map with results from all elements

This method can call methods on the map entries that are also implemented
by the map object itself and are therefore not reachable when using the
magic `__call()` method. If some entries are not objects, they will be skipped.

The keys from the original map are preserved in the returned in the new map.

**Examples:**

```php
$item = new MyClass( ['myprop' => 'val'] ); // implements methods get() and toArray()

Map::from( [$item, $item] )->call( 'get', ['myprop'] );
// ['val', 'val']

Map::from( [$item, $item] )->call( 'toArray' );
// [['myprop' => 'val'], ['myprop' => 'val']]
```


### cast()

Casts all entries to the passed type.

```php
public function cast( string $type = 'string' ) : self
```

* @param **string** `$type` Type to cast the values to ("string", "bool", "int", "float", "array", "object")
* @return **self&#60;int&#124;string,mixed&#62;** Updated map with casted elements

Casting arrays and objects to scalar values won't return anything useful!

**Examples:**

```php
Map::from( [true, 1, 1.0, 'yes'] )->cast();
// ['1', '1', '1.0', 'yes']

Map::from( [true, 1, 1.0, 'yes'] )->cast( 'bool' );
// [true, true, true, true]

Map::from( [true, 1, 1.0, 'yes'] )->cast( 'int' );
// [1, 1, 1, 0]

Map::from( [true, 1, 1.0, 'yes'] )->cast( 'float' );
// [1.0, 1.0, 1.0, 0.0]

Map::from( [new stdClass, new stdClass] )->cast( 'array' );
// [[], []]

Map::from( [[], []] )->cast( 'object' );
// [new stdClass, new stdClass]
```


### chunk()

Chunks the map into arrays with the given number of elements.

```php
public function chunk( int $size, bool $preserve = false ) : self
```

* @param **int** `$size` Maximum size of the sub-arrays
* @param **bool** `$preserve` Preserve keys in new map
* @return **self&#60;int&#124;string,mixed&#62;** New map with elements chunked in sub-arrays
* @throws **\InvalidArgumentException** If size is smaller than 1

The last chunk may contain less elements than the given number.

The sub-arrays of the returned map are plain PHP arrays. If you need Map
objects, then wrap them with [Map::from()](#from) when you iterate over the map.

**Examples:**

```php
Map::from( [0, 1, 2, 3, 4] )->chunk( 3 );
// [[0, 1, 2], [3, 4]]

Map::from( ['a' => 0, 'b' => 1, 'c' => 2] )->chunk( 2 );
// [['a' => 0, 'b' => 1], ['c' => 2]]
```


### clear()

Removes all elements from the current map.

```php
public function clear() : self
```

* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

**Examples:**

```php
Map::from( [0, 1] )->clear();
// internal : []
```


### clone()

Clones the map and all objects within.

```php
public function clone() : self
```

* @return **self&#60;int&#124;string,mixed&#62;** New map with cloned objects

The objects within the Map are NOT the same as before but new cloned objects.
This is different to [`copy()`](#copy), which doesn't clone the objects within.

The keys are preserved using this method.

**Examples:**

```php
Map::from( [new \stdClass, new \stdClass] )->clone();
// [new \stdClass, new \stdClass]
```


### col()

Returns the values of a single column/property from an array of arrays or list of elements in a new map.

```php
public function col( string $valuecol = null, string $indexcol = null ) : self
```

* @param **string&#124;null** `$valuecol` Name or path of the value property
* @param **string&#124;null** `$indexcol` Name or path of the index property
* @return **self&#60;int&#124;string,mixed&#62;** New map with mapped entries

If $indexcol is omitted, it's value is NULL or not set, the result will be indexed from 0-n.
Items with the same value for $indexcol will overwrite previous items and only the last one
will be part of the resulting map.

This does also work to map values from multi-dimensional arrays by passing the keys
of the arrays separated by the delimiter ("/" by default), e.g. `key1/key2/key3`
to get `val` from `['key1' => ['key2' => ['key3' => 'val']]]`. The same applies to
public properties of objects or objects implementing `__isset()` and `__get()` methods.

**Examples:**

```php
Map::from( [['id' => 'i1', 'val' => 'v1'], ['id' => 'i2', 'val' => 'v2']] )->col( 'val' );
// ['v1', 'v2']

Map::from( [['id' => 'i1', 'val' => 'v1'], ['id' => 'i2', 'val' => 'v2']] )->col( 'val', 'id' );
// ['i1' => 'v1', 'i2' => 'v2']

Map::from( [['id' => 'i1', 'val' => 'v1'], ['id' => 'i2', 'val' => 'v2']] )->col( null, 'id' );
// ['i1' => ['id' => 'i1', 'val' => 'v1'], 'i2' => ['id' => 'i2', 'val' => 'v2']]

Map::from( [['id' => 'ix', 'val' => 'v1'], ['id' => 'ix', 'val' => 'v2']] )->col( null, 'id' );
// ['ix' => ['id' => 'ix', 'val' => 'v2']]

Map::from( [['foo' => ['bar' => 'one', 'baz' => 'two']]] )->col( 'foo/baz', 'foo/bar' );
// ['one' => 'two']

Map::from( [['foo' => ['bar' => 'one']]] )->col( 'foo/baz', 'foo/bar' );
// ['one' => null]

Map::from( [['foo' => ['baz' => 'two']]] )->col( 'foo/baz', 'foo/bar' );
// ['two']
```


### collapse()

Collapses all sub-array elements recursively to a new map.

```php
public function collapse( int $depth = null ) : self
```

* @param **int&#124;null** `$depth` Number of levels to collapse for multi-dimensional arrays or NULL for all
* @return **self&#60;int&#124;string,mixed&#62;** New map with all sub-array elements added into it recursively, up to the specified depth

The keys are preserved and already existing elements will be overwritten. This
is also true for numeric keys! This method is similar than [flat()](#flat) but replaces
already existing elements.

A value smaller than 1 for depth will return the same map elements. Collapsing
does also work if elements implement the "Traversable" interface (which the Map
object does).

**Examples:**

```php
Map::from( [0 => ['a' => 0, 'b' => 1], 1 => ['c' => 2, 'd' => 3]] )->collapse();
// ['a' => 0, 'b' => 1, 'c' => 2, 'd' => 3]

Map::from( [0 => ['a' => 0, 'b' => 1], 1 => ['a' => 2]] )->collapse();
// ['a' => 2, 'b' => 1]

Map::from( [0 => [0 => 0, 1 => 1], 1 => [0 => ['a' => 2, 0 => 3], 1 => 4]] )->collapse();
// [0 => 3, 1 => 4, 'a' => 2]

Map::from( [0 => [0 => 0, 'a' => 1], 1 => [0 => ['b' => 2, 0 => 3], 1 => 4]] )->collapse( 1 );
// [0 => ['b' => 2, 0 => 3], 1 => 4, 'a' => 1]

Map::from( [0 => [0 => 0, 'a' => 1], 1 => Map::from( [0 => ['b' => 2, 0 => 3], 1 => 4] )] )->collapse();
// [0 => 3, 'a' => 1, 'b' => 2, 1 => 4]
```


### combine()

Combines the values of the map as keys with the passed elements as values.

```php
public function combine( iterable $values ) : self
```

* @param **iterable&#60;int&#124;string,mixed&#62;** `$values` Values of the new map
* @return **self&#60;int&#124;string,mixed&#62;** New map

**Examples:**

```php
Map::from( ['name', 'age'] )->combine( ['Tom', 29] );
// ['name' => 'Tom', 'age' => 29]
```


### compare()

Compares the value against all map elements.

```php
public function compare( string $value, bool $case = true ) : bool
```

* @param **string** `$value` Value to compare map elements to
* @param **bool** `$case` TRUE if comparison is case sensitive, FALSE to ignore upper/lower case
* @return **bool** TRUE If at least one element matches, FALSE if value is not in map

All scalar values (bool, float, int and string) are casted to string values before
comparing to the given value. Non-scalar values in the map are ignored.

**Examples:**

```php
Map::from( ['foo', 'bar'] )->compare( 'foo' );
// true

Map::from( ['foo', 'bar'] )->compare( 'Foo', false );
// true (case insensitive)

Map::from( [123, 12.3] )->compare( '12.3' );
// true

Map::from( [false, true] )->compare( '1' );
// true

Map::from( ['foo', 'bar'] )->compare( 'Foo' );
// false (case sensitive)

Map::from( ['foo', 'bar'] )->compare( 'baz' );
// false

Map::from( [new \stdClass(), 'bar'] )->compare( 'foo' );
// false
```


### concat()

Pushs all of the given elements onto the map without creating a new map.

```php
public function concat( iterable $elements ) : self
```

* @param **iterable&#60;int&#124;string,mixed&#62;** `$elements` List of elements
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

The keys of the passed elements are NOT preserved!

**Examples:**

```php
Map::from( ['foo'] )->concat( ['bar'] );
// ['foo', 'bar']

Map::from( ['foo'] )->concat( new Map( ['bar' => 'baz'] ) );
// ['foo', 'baz']
```


### contains()

Determines if an item exists in the map.

```php
public function contains( $key, string $operator = null, $value = null ) : bool
```

This method combines the power of the `where()` method with `some()` to check
if the map contains at least one of the passed values or conditions.

* @param **\Closure&#124;iterable&#124;mixed** `$values` Anonymous function with (item, key) parameter, element or list of elements to test against
* @param **string&#124;null** `$op` Operator used for comparison
* @param **mixed** `$value` Value used for comparison
* @return **bool** TRUE if at least one element is available in map, FALSE if the map contains none of them

Check the [`where()`](#where)] method for available operators.

**Examples:**

```php
Map::from( ['a', 'b'] )->contains( 'a' );
// true

Map::from( ['a', 'b'] )->contains( ['a', 'c'] );
// true

Map::from( ['a', 'b'] )->contains( function( $item, $key ) {
    return $item === 'a'
} );
// true

Map::from( [['type' => 'name']] )->contains( 'type', 'name' );
// true

Map::from( [['type' => 'name']] )->contains( 'type', '!=', 'name' );
// false
```


### copy()

Creates a new map with the same elements.

```php
public function copy() : self
```

* @return **self&#60;int&#124;string,mixed&#62;** New map

Both maps share the same array until one of the map objects modifies the
array. Then, the array is copied and the copy is modfied (copy on write).

**Examples:**

```php
$m = Map::from( ['foo', 'bar'] );

$m2 = $m->copy();
// internal: ['foo', 'bar'] both two maps
```


### count()

Counts the number of elements in the map.

```php
public function count() : int
```

* @return **int** Number of elements

**Examples:**

```php
Map::from( ['foo', 'bar'] )->count();
// 2
```


### countBy()

Counts how often the same values are in the map.

```php
public function countBy( callable $callback = null ) : self
```

* @param **callable&#124;null** `$callback` Function with (value, key) parameters which returns the value to use for counting
* @return **self&#60;int&#124;string,mixed&#62;** New map with values as keys and their count as value

**Examples:**

```php
Map::from( [1, 'foo', 2, 'foo', 1] )->countBy();
// [1 => 2, 'foo' => 2, 2 => 1]

Map::from( [1.11, 3.33, 3.33, 9.99] )->countBy();
// ['1.11' => 1, '3.33' => 2, '9.99' => 1]

Map::from( ['a@gmail.com', 'b@yahoo.com', 'c@gmail.com'] )->countBy( function( $email ) {
    return substr( strrchr( $email, '@' ), 1 );
} );
// ['gmail.com' => 2, 'yahoo.com' => 1]
```


### dd()

Dumps the map content and terminates the script.

```php
public function dd( callable $callback = null ) : void
```

* @param **callable&#124;null** `$callback` Function receiving the map elements as parameter (optional)

The `dd()` method is very helpful to see what are the map elements passed
between two map methods in a method call chain. It stops execution of the
script afterwards to avoid further output.

**Examples:**

```php
Map::from( ['a' => 'foo', 'b' => 'bar'] )->sort()->dd()->first();
/*
Array
(
    [0] => bar
    [1] => foo
)
*/
```

The `first()` method isn't executed at all.


### delimiter()

Sets or returns the seperator for paths to values in multi-dimensional arrays or objects.

```php
public static function delimiter( ?string $char = null ) : string
```

* @param **string** `$char` Separator character, e.g. "." for "key.to.value" instaed of "key/to/value"
* @return **string** Separator used up to now

The static method only changes the separator for new maps created afterwards.
Already existing maps will continue to use the previous separator. To change
the separator of an existing map, use the [sep()](#sep) method instead.

**Examples:**

```php
Map::delimiter( '.' );
// '/'

Map::from( ['foo' => ['bar' => 'baz']] )->get( 'foo.bar' );
// 'baz'
```


### diff()

Returns the keys/values in the map whose values are not present in the passed elements in a new map.

```php
public function diff( iterable $elements, callable $callback = null ) : self
```

* @param **iterable&#60;int&#124;string,mixed&#62;** `$elements` List of elements
* @param **callable&#124;null** `$callback` Function with (valueA, valueB) parameters and returns -1 (<), 0 (=) and 1 (>)
* @return **self&#60;int&#124;string,mixed&#62;** New map

**Examples:**

```php
Map::from( ['a' => 'foo', 'b' => 'bar'] )->diff( ['bar'] );
// ['a' => 'foo']
```

If a callback is passed, the given function will be used to compare the values.
The function must accept two parameters (value A and B) and must return
-1 if value A is smaller than value B, 0 if both are equal and 1 if value A is
greater than value B. Both, a method name and an anonymous function can be passed:

```php
Map::from( [0 => 'a'] )->diff( [0 => 'A'], 'strcasecmp' );
// []

Map::from( ['b' => 'a'] )->diff( ['B' => 'A'], 'strcasecmp' );
// []

Map::from( ['b' => 'a'] )->diff( ['c' => 'A'], function( $valA, $valB ) {
    return strtolower( $valA ) <=> strtolower( $valB );
} );
// []
```

All examples will return an empty map because both contain the same values
when compared case insensitive.

The keys are preserved using this method.


### diffAssoc()

Returns the keys/values in the map whose keys AND values are not present in the passed elements in a new map.

```php
public function diffAssoc( iterable $elements, callable $callback = null ) : self
```

* @param **iterable&#60;int&#124;string,mixed&#62;** `$elements` List of elements
* @param **callable&#124;null** `$callback` Function with (valueA, valueB) parameters and returns -1 (<), 0 (=) and 1 (>)
* @return **self&#60;int&#124;string,mixed&#62;** New map

**Examples:**

```php
Map::from( ['a' => 'foo', 'b' => 'bar'] )->diffAssoc( new Map( ['foo', 'b' => 'bar'] ) );
// ['a' => 'foo']
```

If a callback is passed, the given function will be used to compare the values.
The function must accept two parameters (valA, valB) and must return
-1 if value A is smaller than value B, 0 if both are equal and 1 if value A is
greater than value B. Both, a method name and an anonymous function can be passed:

```php
Map::from( [0 => 'a'] )->diffAssoc( [0 => 'A'], 'strcasecmp' );
// []

Map::from( ['b' => 'a'] )->diffAssoc( ['B' => 'A'], 'strcasecmp' );
// []

Map::from( ['b' => 'a'] )->diffAssoc( ['c' => 'A'], function( $valA, $valB ) {
    return strtolower( $valA ) <=> strtolower( $valB );
} );
// ['b' => 'a']
```

The first and second example will return an empty map because both contain the
same values when compared case insensitive. In the third example, the keys doesn't
match ("b" vs. "c").

The keys are preserved using this method.


### diffKeys()

Returns the key/value pairs from the map whose keys are not present in the passed elements in a new map.

```php
public function diffKeys( iterable $elements, callable $callback = null ) : self
```

* @param **iterable&#60;int&#124;string,mixed&#62;** `$elements` List of elements
* @param **callable&#124;null** `$callback` Function with (keyA, keyB) parameters and returns -1 (<), 0 (=) and 1 (>)
* @return **self&#60;int&#124;string,mixed&#62;** New map

**Examples:**

```php
Map::from( ['a' => 'foo', 'b' => 'bar'] )->diffKeys( new Map( ['foo', 'b' => 'baz'] ) );
// ['a' => 'foo']
```

If a callback is passed, the given function will be used to compare the keys.
The function must accept two parameters (key A and B) and must return
-1 if key A is smaller than key B, 0 if both are equal and 1 if key A is
greater than key B. Both, a method name and an anonymous function can be passed:

```php
Map::from( [0 => 'a'] )->diffKeys( [0 => 'A'], 'strcasecmp' );
// []

Map::from( ['b' => 'a'] )->diffKeys( ['B' => 'X'], 'strcasecmp' );
// []

Map::from( ['b' => 'a'] )->diffKeys( ['c' => 'a'], function( $keyA, $keyB ) {
    return strtolower( $keyA ) <=> strtolower( $keyB );
} );
// ['b' => 'a']
```

The first and second example will return an empty map because both contain
the same keys when compared case insensitive. The third example will return
['b' => 'a'] because the keys doesn't match ("b" vs. "c").

The keys are preserved using this method.


### dump()

Dumps the map content using the given function (print_r by default).

```php
public function dump( callable $callback = null ) : self
```

* @param **callable&#124;null** `$callback` Function receiving the map elements as parameter (optional)
* @return **self&#60;int&#124;string,mixed&#62;** Same map for fluid interface

The `dump()` method is very helpful to see what are the map elements passed
between two map methods in a method call chain.

**Examples:**

```php
Map::from( ['a' => 'foo', 'b' => 'bar'] )->dump()->asort()->dump( 'var_dump' );
/*
Array
(
    [a] => foo
    [b] => bar
)

array(1) {
  ["b"]=>
  string(3) "bar"
  ["a"]=>
  string(3) "foo"
}
*/
```


### duplicates()

Returns the duplicate values from the map.

```php
public function duplicates( string $col = null ) : self
```

* @param **string&#124;null** `$col` Key of the nested array or object to check for
* @return **self&#60;int&#124;string,mixed&#62;** New map

For nested arrays, you have to pass the name of the column of the nested array which
should be used to check for duplicates.

This does also work to map values from multi-dimensional arrays by passing the keys
of the arrays separated by the delimiter ("/" by default), e.g. `key1/key2/key3`
to get `val` from `['key1' => ['key2' => ['key3' => 'val']]]`. The same applies to
public properties of objects or objects implementing `__isset()` and `__get()` methods.

The keys in the result map are preserved.

**Examples:**

```php
Map::from( [1, 2, '1', 3] )->duplicates()
// [2 => '1']

Map::from( [['p' => '1'], ['p' => 1], ['p' => 2]] )->duplicates( 'p' )
// [1 => ['p' => 1]]

Map::from( [['i' => ['p' => '1']], ['i' => ['p' => 1]]] )->duplicates( 'i/p' )
// [1 => ['i' => ['p' => '1']]]
```


### each()

Executes a callback over each entry until FALSE is returned.

```php
public function each( \Closure $callback ) : self
```

* @param **\Closure** `$callback` Function with (value, key) parameters and returns TRUE/FALSE
* @return **self&#60;int&#124;string,mixed&#62;** Same map for fluid interface

**Examples:**

```php
$result = [];
Map::from( [0 => 'a', 1 => 'b'] )->each( function( $value, $key ) use ( &$result ) {
    $result[$key] = strtoupper( $value );
    return false;
} );
// $result = [0 => 'A']
```

The `$result` array will contain `[0 => 'A']` because FALSE is returned
after the first entry and all other entries are then skipped.


### empty()

Determines if the map is empty or not.

```php
public function empty() : bool
```

* @return **bool** TRUE if map is empty, FALSE if not

The method is equivalent to isEmpty().

**Examples:**

```php
Map::from( [] )->empty();
// true

Map::from( ['a'] )->empty();
// false
```


### equals()

Tests if the passed elements are equal to the elements in the map.

```php
public function equals( iterable $elements ) : bool
```

* @param **iterable&#60;int&#124;string,mixed&#62;** `$elements` List of elements to test against
* @return **bool** TRUE if both are equal, FALSE if not

The method differs to [is()](#is) in the fact that it doesn't care about the keys
by default. The elements are only loosely compared and the keys are ignored.

Values are compared by their string values:
```php
(string) $item1 === (string) $item2
```

**Examples:**

```php
Map::from( ['a'] )->equals( ['a', 'b'] );
// false

Map::from( ['a', 'b'] )->equals( ['b'] );
// false

Map::from( ['a', 'b'] )->equals( ['b', 'a'] );
// true
```


### every()

Verifies that all elements pass the test of the given callback.

```php
public function every( \Closure $callback ) : bool
```

* @param **\Closure** `$callback` Function with (value, key) parameters and returns TRUE/FALSE
* @return **bool** True if all elements pass the test, false if if fails for at least one element

**Examples:**

```php
Map::from( [0 => 'a', 1 => 'b'] )->every( function( $value, $key ) {
    return is_string( $value );
} );
// true

Map::from( [0 => 'a', 1 => 100] )->every( function( $value, $key ) {
    return is_string( $value );
} );
// false
```


### except()

Returns a new map without the passed element keys.

```php
public function except( $keys ) : self
```

* @param **iterable&#60;int&#124;string&#62;&#124;array&#60;int&#124;string&#62;&#124;string&#124;int** `$keys` List of keys to remove
* @return **self&#60;int&#124;string,mixed&#62;** New map

The keys in the result map are preserved.

**Examples:**

```php
Map::from( ['a' => 1, 'b' => 2, 'c' => 3] )->except( 'b' );
// ['a' => 1, 'c' => 3]

Map::from( [1 => 'a', 2 => 'b', 3 => 'c'] )->except( [1, 3] );
// [2 => 'b']
```


### explode()

Creates a new map with the string splitted by the delimiter.

```php
public static function explode( string $delimiter , string $string , int $limit = PHP_INT_MAX ) : self
```

* @param **string** `$delimiter` Delimiter character, string or empty string
* @param **string** `$string` String to split
* @param **int** `$limit` Maximum number of element with the last element containing the rest of the string
* @return **self&#60;int&#124;string,mixed&#62;** New map with splitted parts

A limit of "0" is treated the same as "1". If limit is negative, the rest of
the string is dropped and not part of the returned map.

This method creates a lazy Map and the string is split after calling
another method that operates on the Map contents.

**Examples:**

```php
Map::explode( ',', 'a,b,c' );
// ['a', 'b', 'c']

Map::explode( '<-->', 'a a<-->b b<-->c c' );
// ['a a', 'b b', 'c c']

Map::explode( '', 'string' );
// ['s', 't', 'r', 'i', 'n', 'g']

Map::explode( '|', 'a|b|c', 2 );
// ['a', 'b|c']

Map::explode( '', 'string', 2 );
// ['s', 't', 'ring']

Map::explode( '|', 'a|b|c|d', -2 );
// ['a', 'b']

Map::explode( '', 'string', -3 );
// ['s', 't', 'r']
```


### filter()

Runs a filter over each element of the map and returns a new map.

```php
public function filter( callable $callback = null ) : self
```

* @param **callable&#124;null** `$callback` Function with (item, key) parameters and returns TRUE/FALSE
* @return **self&#60;int&#124;string,mixed&#62;** New map

If no callback is passed, all values which are empty, null or false will be
removed if their value converted to boolean is FALSE:
```php
(bool) $value === false
```

The keys in the result map are preserved.

**Examples:**

```php
Map::from( [null, 0, 1, '', '0', 'a'] )->filter();
// [1, 'a']

Map::from( [2 => 'a', 6 => 'b', 13 => 'm', 30 => 'z'] )->filter( function( $value, $key ) {
    return $key < 10 && $value < 'n';
} );
// ['a', 'b']
```


### find()

Returns the first matching element where the callback returns TRUE.

```php
public function find( \Closure $callback, $default = null, bool $reverse = false )
```

* @param **\Closure** `$callback` Function with (value, key) parameters and returns TRUE/FALSE
* @param **mixed** `$default` Default value or exception if the map contains no elements
* @param **bool** `$reverse` TRUE to test elements from back to front, FALSE for front to back (default)
* @return **mixed&#124;null** First matching value, passed default value or an exception

**Examples:**

```php
Map::from( ['a', 'c', 'e'] )->find( function( $value, $key ) {
    return $value >= 'b';
} );
// 'c'

Map::from( ['a', 'c', 'e'] )->find( function( $value, $key ) {
    return $value >= 'b';
}, null, true );
// 'e' because $reverse = true

Map::from( [] )->find( function( $value, $key ) {
    return $value >= 'b';
}, 'none' );
// 'none'

Map::from( [] )->find( function( $value, $key ) {
    return $value >= 'b';
}, new \Exception( 'error' ) );
// throws \Exception
```


### first()

Returns the first element from the map.

```php
public function first( $default = null )
```

* @param **mixed** `$default` Default value or exception if the map contains no elements
* @return **mixed** First value of map, (generated) default value or an exception

**Examples:**

```php
Map::from( ['a', 'b'] )->first();
// 'a'

Map::from( [] )->first( 'x' );
// 'x'

Map::from( [] )->first( new \Exception( 'error' ) );
// throws \Exception

Map::from( [] )->first( function() { return rand(); } );
// random integer
```


### firstKey()

Returns the first key from the map.

```php
public function firstKey()
```

* @return **mixed** First key of map or NULL if empty

**Examples:**

```php
Map::from( ['a' => 1, 'b' => 2] )->lastKey();
// 'a'

Map::from( [] )->lastKey();
// null
```


### flat()

Creates a new map with all sub-array elements added recursively.

```php
public function flat( int $depth = null ) : self
```

* @param **int&#124;null** `$depth` Number of levels to flatten multi-dimensional arrays
* @return **self&#60;int&#124;string,mixed&#62;** New map with all sub-array elements added into it recursively, up to the specified depth

The keys are not preserved and the new map elements will be numbered from
0-n. A value smaller than 1 for depth will return the same map elements
indexed from 0-n. Flattening does also work if elements implement the
"Traversable" interface (which the Map object does).

This method is similar than [collapse()](#collapse) but doesn't replace existing elements.
Keys are NOT preserved using this method!

**Examples:**

```php
Map::from( [[0, 1], [2, 3]] )->flat();
// [0, 1, 2, 3]

Map::from( [[0, 1], [[2, 3], 4]] )->flat();
// [0, 1, 2, 3, 4]

Map::from( [[0, 1], [[2, 3], 4]] )->flat( 1 );
// [0, 1, [2, 3], 4]

Map::from( [[0, 1], Map::from( [[2, 3], 4] )] )->flat();
// [0, 1, 2, 3, 4]
```


### flip()

Exchanges the keys with their values and vice versa.

```php
public function flip() : self
```

* @return **self&#60;int&#124;string,mixed&#62;** New map with keys as values and values as keys

**Examples:**

```php
Map::from( ['a' => 'X', 'b' => 'Y'] )->flip();
// ['X' => 'a', 'Y' => 'b']
```


### float()

Returns an element by key and casts it to float if possible.

```php
public function float( $key, $default = 0.0 ) : float
```

* @param **int&#124;string** `$key` Key or path to the requested item
* @param **mixed** `$default` Default value if key isn't found (will be casted to float)
* @return **float** Value from map or default value

This does also work to map values from multi-dimensional arrays by passing the keys
of the arrays separated by the delimiter ("/" by default), e.g. `key1/key2/key3`
to get `val` from `['key1' => ['key2' => ['key3' => 'val']]]`. The same applies to
public properties of objects or objects implementing `__isset()` and `__get()` methods.

**Examples:**

```php
Map::from( ['a' => true] )->float( 'a' );
// 1.0 (casted to float)

Map::from( ['a' => 1] )->float( 'a' );
// 1.0 (casted to float)

Map::from( ['a' => '1.1'] )->float( 'a' );
// 1.1 (casted to float)

Map::from( ['a' => '10'] )->float( 'a' );
// 10.0 (casted to float)

Map::from( ['a' => ['b' => ['c' => 1.1]]] )->float( 'a/b/c' );
// 1.1

Map::from( [] )->float( 'c', function() { return 1.1; } );
// 1.1

Map::from( [] )->float( 'a', 1 );
// 1.0 (default value used)

Map::from( [] )->float( 'a' );
// 0.0

Map::from( ['b' => ''] )->float( 'b' );
// 0.0 (casted to float)

Map::from( ['a' => 'abc'] )->float( 'a' );
// 0.0 (casted to float)

Map::from( ['b' => null] )->float( 'b' );
// 0.0 (null is not scalar)

Map::from( ['b' => [true]] )->float( 'b' );
// 0.0 (arrays are not scalar)

Map::from( ['b' => '#resource'] )->float( 'b' );
// 0.0 (resources are not scalar)

Map::from( ['b' => new \stdClass] )->float( 'b' );
// 0.0 (objects are not scalar)

Map::from( [] )->float( 'c', new \Exception( 'error' ) );
// throws exception
```


### from()

Creates a new map instance if the value isn't one already.

```php
public static function from( $elements = [] ) : self
```

* @param **mixed** `$elements` List of elements or single value
* @return **self&#60;int&#124;string,mixed&#62;** New map

**Examples:**

```php
// array
Map::from( [] );

// null
Map::from( null );

// scalar
Map::from( 'a' );

// object
Map::from( new \stdClass() );

// map object
Map::from( new Map() );

// iterable object
Map::from( new ArrayObject() );

// closure evaluated lazily
Map::from( function() {
    return [];
} );
```


### fromJson()

Creates a new map instance from a JSON string.

```php
public static function fromJson( string $json, int $options = JSON_BIGINT_AS_STRING ) : self
```

* @param **int** `$options` Combination of JSON_* constants
* @return **self&#60;int&#124;string,mixed&#62;** New map from decoded JSON string
* @throws **\RuntimeException** If the passed JSON string is invalid

There are several options available for decoding the JSON string which are described in
the [PHP json_decode() manual](https://www.php.net/manual/en/function.json-decode.php).
The parameter can be a single JSON_* constant or a bitmask of several constants combine
by bitwise OR (&#124;), e.g.:

This method creates a lazy Map and the string is decoded after calling
another method that operates on the Map contents. Thus, the exception in
case of an error isn't thrown immediately but after calling the next method.

```php
JSON_BIGINT_AS_STRING|JSON_INVALID_UTF8_IGNORE
```

**Examples:**

```php
Map::fromJson( '["a", "b"]' );
// ['a', 'b']

Map::fromJson( '{"a": "b"}' );
// ['a' => 'b']

Map::fromJson( '""' );
['']
```


### get()

Returns an element from the map by key.

```php
public function get( $key, $default = null )
```

* @param **int&#124;string** `$key` Key or path to the requested item
* @param **mixed** `$default` Default value if no element matches
* @return **mixed** Value from map or default value

This does also work to map values from multi-dimensional arrays by passing the keys
of the arrays separated by the delimiter ("/" by default), e.g. `key1/key2/key3`
to get `val` from `['key1' => ['key2' => ['key3' => 'val']]]`. The same applies to
public properties of objects or objects implementing `__isset()` and `__get()` methods.

**Examples:**

```php
Map::from( ['a' => 'X', 'b' => 'Y'] )->get( 'a' );
// 'X'

Map::from( ['a' => 'X', 'b' => 'Y'] )->get( 'c', 'Z' );
// 'Z'

Map::from( ['a' => ['b' => ['c' => 'Y']]] )->get( 'a/b/c' );
// 'Y'

Map::from( [] )->get( 'c', new \Exception( 'error' ) );
// throws \Exception

Map::from( [] )->get( 'c', function() { return rand(); } );
// random integer
```


### getIterator()

Returns an iterator for the elements.

```php
public function getIterator() : \ArrayIterator
```

* @return **\Iterator** Over map elements

This method will be used by e.g. `foreach()` to loop over all entries.

**Examples:**

```php
foreach( Map::from( ['a', 'b'] ) as $value ) {
    // ...
}
```


### grep()

Returns only items which matches the regular expression.

```php
public function grep( string $pattern, int $flags = 0 ) : self
```

* @param **string** `$pattern` Regular expression pattern, e.g. "/ab/"
* @param **int** `$flags` PREG_GREP_INVERT to return elements not matching the pattern
* @return **self&#60;int&#124;string,mixed&#62;** New map containing only the matched elements

All items are converted to string first before they are compared to the
regular expression. Thus, fractions of ".0" will be removed in float numbers
which may result in unexpected results. The keys are preserved using this method.

**Examples:**

```php
Map::from( ['ab', 'bc', 'cd'] )->grep( '/b/' );
// ['ab', 'bc']

Map::from( ['ab', 'bc', 'cd'] )->grep( '/a/', PREG_GREP_INVERT );
// ['bc', 'cd']

Map::from( [1.5, 0, 1.0, 'a'] )->grep( '/^(\d+)?\.\d+$/' );
// [1.5]
// float 1.0 is converted to string "1"
```


### groupBy()

Groups associative array elements or objects by the passed key or closure.

```php
public function groupBy( $key ) : self
```

* @param **\Closure&#124;string&#124;int** `$key` Closure function with (item, idx) parameters returning the key or the key itself to group by
* @return **self&#60;int&#124;string,mixed&#62;** New map with elements grouped by the given key

Instead of overwriting items with the same keys like to the [col()](#col) method does,
[groupBy()](#groupby) keeps all entries in sub-arrays. It's preserves the keys of the
orignal map entries too.

**Examples:**

```php
$list = [
    10 => ['aid' => 123, 'code' => 'x-abc'],
    20 => ['aid' => 123, 'code' => 'x-def'],
    30 => ['aid' => 456, 'code' => 'x-def']
];

Map::from( $list )->groupBy( 'aid' );
/*
[
    123 => [
        10 => ['aid' => 123, 'code' => 'x-abc'],
        20 => ['aid' => 123, 'code' => 'x-def']
    ],
    456 => [
        30 => ['aid' => 456, 'code' => 'x-def']
    ]
]
*/

Map::from( $list )->groupBy( function( $item, $key ) {
    return substr( $item['code'], -3 );
} );
/*
[
    'abc' => [
        10 => ['aid' => 123, 'code' => 'x-abc']
    ],
    'def' => [
        20 => ['aid' => 123, 'code' => 'x-def'],
        30 => ['aid' => 456, 'code' => 'x-def']
    ]
]
*/
```

In case the passed key doesn't exist in one or more items, these items are stored
in a sub-array using an empty string as key:

```php
$list = [
    10 => ['aid' => 123, 'code' => 'x-abc'],
    20 => ['aid' => 123, 'code' => 'x-def'],
    30 => ['aid' => 456, 'code' => 'x-def']
];

Map::from( $list )->groupBy( 'xid' );
/*
[
    '' => [
        10 => ['aid' => 123, 'code' => 'x-abc'],
        20 => ['aid' => 123, 'code' => 'x-def'],
        30 => ['aid' => 456, 'code' => 'x-def']
    ]
]
*/
```


### has()

Determines if a key or several keys exists in the map.

```php
public function has( $key ) : bool
```

* @param **array&#60;int&#124;string&#60;&#124;int&#124;string** `$key` Key or path to the requested item
* @return **bool** TRUE if key is available in map, FALSE if not

If several keys are passed as array, all keys must exist in the map to
return TRUE.

This does also work to map values from multi-dimensional arrays by passing the keys
of the arrays separated by the delimiter ("/" by default), e.g. `key1/key2/key3`
to get `val` from `['key1' => ['key2' => ['key3' => 'val']]]`. The same applies to
public properties of objects or objects implementing `__isset()` and `__get()` methods.

**Examples:**

```php
Map::from( ['a' => 'X', 'b' => 'Y'] )->has( 'a' );
// true

Map::from( ['a' => 'X', 'b' => 'Y'] )->has( ['a', 'b'] );
// false

Map::from( ['a' => ['b' => ['c' => 'Y']]] )->has( 'a/b/c' );
// true

Map::from( ['a' => 'X', 'b' => 'Y'] )->has( 'c' );
// false

Map::from( ['a' => 'X', 'b' => 'Y'] )->has( ['a', 'c'] );
// false

Map::from( ['a' => 'X', 'b' => 'Y'] )->has( 'X' );
// false
```


### if()

Executes callbacks depending on the condition.

```php
public function if( $condition, \Closure $then, \Closure $else = null ) : self
```

* @param **\Closure&#124;bool** `$condition` Boolean or function with (map) parameter returning a boolean
* @param **\Closure** `$then` Function with (map) parameter
* @param **\Closure&#124;null** `$else` Function with (map) parameter (optional)
* @return **self&#60;int&#124;string,mixed&#62;** New map for fluid interface

If callbacks for "then" and/or "else" are passed, these callbacks will be
executed and their returned value is passed back within a Map object. In
case no "then" or "else" closure is given, the method will return the same
map object if the condition is true or an empty map object if it's false.

**Examples:**

```php
Map::from( ['a' => 1, 'b' => 0] )->if(
    'a' == 'b',
    function( Map $_ ) { echo "then"; }
);
// no output

Map::from( ['a' => 1, 'b' => 0] )->if(
    function( Map $map ) { return $map->has( 'a' ); },
    function( Map $_ ) { echo "then"; },
    function( Map $_ ) { echo "else"; }
);
// then

Map::from( ['a' => 1, 'b' => 0] )->if(
    fn( Map $map ) => $map->has( 'c' ),
    function( Map $_ ) { echo "then"; },
    function( Map $_ ) { echo "else"; }
);
// else

Map::from( ['a', 'b'] )->if( true, function( $map ) {
    return $map->push( 'c' );
} );
// ['a', 'b', 'c']

Map::from( ['a', 'b'] )->if( false, null, function( $map ) {
  return $map->pop();
} );
// ['b']
```

Since PHP 7.4, you can also pass arrow function like `fn($map) => $map->has('c')`
(a short form for anonymous closures) as parameters. The automatically have access
to previously defined variables but can not modify them. Also, they can not have
a void return type and must/will always return something. Details about
[PHP arrow functions](https://www.php.net/manual/en/functions.arrow.php)


### ifAny()

* Executes callbacks depending if the map contains elements or not.

```php
public function ifAny( \Closure $then = null, \Closure $else = null ) : self
```

* @param **\Closure&#124;null** `$then` Function with (map, condition) parameter (optional)
* @param **\Closure&#124;null** `$else` Function with (map, condition) parameter (optional)
* @return **self<int&#124;string,mixed>** New map for fluid interface

If callbacks for "then" and/or "else" are passed, these callbacks will be
executed and their returned value is passed back within a Map object. In
case no "then" or "else" closure is given, the method will return the same
map object.

**Examples:**

```php
Map::from( ['a'] )->ifAny( function( $map ) {
  $map->push( 'b' );
} );
// ['a', 'b']

Map::from( [] )->ifAny( null, function( $map ) {
  return $map->push( 'b' );
} );
// ['b']

Map::from( ['a'] )->ifAny( function( $map ) {
  return 'c';
} );
// ['c']
```

Since PHP 7.4, you can also pass arrow function like `fn($map) => $map->has('c')`
(a short form for anonymous closures) as parameters. The automatically have access
to previously defined variables but can not modify them. Also, they can not have
a void return type and must/will always return something. Details about
[PHP arrow functions](https://www.php.net/manual/en/functions.arrow.php)


### ifEmpty()

* Executes callbacks depending if the map is empty or not.

```php
public function ifEmpty( \Closure $then = null, \Closure $else = null ) : self
```

* @param **\Closure&#124;null** `$then` Function with (map, condition) parameter (optional)
* @param **\Closure&#124;null** `$else` Function with (map, condition) parameter (optional)
* @return **self<int&#124;string,mixed>** New map for fluid interface

If callbacks for "then" and/or "else" are passed, these callbacks will be
executed and their returned value is passed back within a Map object. In
case no "then" or "else" closure is given, the method will return the same
map object.

**Examples:**

```php
Map::from( [] )->ifEmpty( function( $map ) {
    $map->push( 'a' );
} );
// ['a']

Map::from( ['a'] )->ifEmpty( null, function( $map ) {
    return $map->push( 'b' );
} );
// ['a', 'b']
```

Since PHP 7.4, you can also pass arrow function like `fn($map) => $map->has('c')`
(a short form for anonymous closures) as parameters. The automatically have access
to previously defined variables but can not modify them. Also, they can not have
a void return type and must/will always return something. Details about
[PHP arrow functions](https://www.php.net/manual/en/functions.arrow.php)


# implements()

Tests if all entries in the map are objects implementing the given interface.

```php
public function implements( string $interface, $throw = false ) : bool
```

* @param **string** `$interface` Name of the interface that must be implemented
* @param **\Throwable&#124;bool** `$throw` Passing TRUE or an exception name will throw the exception instead of returning FALSE
* @return **bool** TRUE if all entries implement the interface or FALSE if at least one doesn't
* @throws **\UnexpectedValueException&#124;\Throwable** If one entry doesn't implement the interface

**Examples:**

```php
Map::from( [new Map(), new Map()] )->implements( '\Countable' );
// true

Map::from( [new Map(), new \stdClass()] )->implements( '\Countable' );
// false

Map::from( [new Map(), 123] )->implements( '\Countable' );
// false

Map::from( [new Map(), 123] )->implements( '\Countable', true );
// throws \UnexpectedValueException

Map::from( [new Map(), 123] )->implements( '\Countable', '\RuntimeException' );
// throws \RuntimeException
```


### in()

Tests if the passed element or elements are part of the map.

```php
public function in( $element, bool $strict = false ) : bool
```

* @param **mixed&#124;array** `$element` Element or elements to search for in the map
* @param **bool** `$strict` TRUE to check the type too, using FALSE '1' and 1 will be the same
* @return **bool** TRUE if all elements are available in map, FALSE if not

**Examples:**

```php
Map::from( ['a', 'b'] )->in( 'a' );
// true

Map::from( ['a', 'b'] )->in( ['a', 'b'] );
// true

Map::from( ['a', 'b'] )->in( 'x' );
// false

Map::from( ['a', 'b'] )->in( ['a', 'x'] );
// false

Map::from( ['1', '2'] )->in( 2, true );
// false
```


## includes()

Tests if the passed element or elements are part of the map.

```php
public function includes( $element, bool $strict = false ) : bool
```

* @param **mixed&#124;array** `$element` Element or elements to search for in the map
* @param **bool** `$strict` TRUE to check the type too, using FALSE '1' and 1 will be the same
* @return **bool** TRUE if all elements are available in map, FALSE if not

This method is an alias for [in()](#in). For performance reasons, `in()` should be preferred
because it uses one method call less than `includes()`.

**Examples:**

```php
Map::from( ['a', 'b'] )->includes( 'a' );
// true

Map::from( ['a', 'b'] )->includes( ['a', 'b'] );
// true

Map::from( ['a', 'b'] )->includes( 'x' );
// false

Map::from( ['a', 'b'] )->includes( ['a', 'x'] );
// false

Map::from( ['1', '2'] )->includes( 2, true );
// false
```


### index()

Returns the numerical index of the given key.

```php
public function index( $value ) : ?int
```

* @param **\Closure&#124;string&#124;int** `$value` Key to search for or function with (key) parameters return TRUE if key is found
* @return **int&#124;null** Position of the found value (zero based) or NULL if not found

**Examples:**

```php
Map::from( [4 => 'a', 8 => 'b'] )->index( '8' );
// 1

Map::from( [4 => 'a', 8 => 'b'] )->index( function( $key ) {
    return $key == '8';
} );
// 1
```

Both examples will return "1" because the value "b" is at the second position
and the returned index is zero based so the first item has the index "0".


### insertAfter()

Inserts the value or values after the given element.

```php
public function insertAfter( $element, $value ) : self
```

* @param **mixed** `$element` Element after the value is inserted
* @param **mixed** `$value` Element or list of elements to insert
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

Numerical array indexes are not preserved.

**Examples:**

```php
Map::from( ['a' => 'foo', 'b' => 'bar'] )->insertAfter( 'foo', 'baz' );
// ['a' => 'foo', 0 => 'baz', 'b' => 'bar']

Map::from( ['foo', 'bar'] )->insertAfter( 'foo', ['baz', 'boo'] );
// ['foo', 'baz', 'boo', 'bar']

Map::from( ['foo', 'bar'] )->insertAfter( null, 'baz' );
// ['foo', 'bar', 'baz']
```


### insertAt()

Inserts the item at the given position in the map.

```php
public function insertAt( int $pos, $element, $key = null ) : self
```

* @param **int** `$pos` Position the element it should be inserted at
* @param **mixed** `$element` Element to be inserted
* @param **mixed&#124;null** `$key` Element key or NULL to assign an integer key automatically
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

**Examples:**

```php
Map::from( ['a' => 'foo', 'b' => 'bar'] )->insertAt( 0, 'baz' );
// [0 => 'baz', 'a' => 'foo', 'b' => 'bar']

Map::from( ['a' => 'foo', 'b' => 'bar'] )->insertAt( 1, 'baz', 'c' );
// ['a' => 'foo', 'c' => 'baz', 'b' => 'bar']

Map::from( ['a' => 'foo', 'b' => 'bar'] )->insertAt( 5, 'baz' );
// ['a' => 'foo', 'b' => 'bar', 'c' => 'baz']

Map::from( ['a' => 'foo', 'b' => 'bar'] )->insertAt( -1, 'baz', 'c' );
// ['a' => 'foo', 'c' => 'baz', 'b' => 'bar']
```


### insertBefore()

Inserts the value or values before the given element.

```php
public function insertBefore( $element, $value ) : self
```

* @param **mixed** `$element` Element before the value is inserted
* @param **mixed** `$value` Element or list of elements to insert
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

Numerical array indexes are not preserved.

**Examples:**

```php
Map::from( ['a' => 'foo', 'b' => 'bar'] )->insertBefore( 'bar', 'baz' );
// ['a' => 'foo', 0 => 'baz', 'b' => 'bar']

Map::from( ['foo', 'bar'] )->insertBefore( 'bar', ['baz', 'boo'] );
// ['foo', 'baz', 'boo', 'bar']

Map::from( ['foo', 'bar'] )->insertBefore( null, 'baz' );
// ['foo', 'bar', 'baz']
```


### inString()

Tests if the passed value or value are part of the strings in the map.

This method is deprecated in favor of the multi-byte aware [strContains()](#strcontains) method.

```php
public function inString( $value, bool $case = true ) : bool
```

* @param **array&#124;string** `$value` Value or values to compare the map elements, will be casted to string type
* @param **bool** `$case` TRUE if comparison is case sensitive, FALSE to ignore upper/lower case
* @return **bool** TRUE If at least one element matches, FALSE if value is not in any string of the map

All scalar values (bool, float, int and string) are casted to string values before
comparing to the given value. Non-scalar values in the map are ignored.

**Examples:**

```php
Map::from( ['abc'] )->inString( 'c' );
// true ('abc' contains 'c')

Map::from( ['abc'] )->inString( 'bc' );
// true ('abc' contains 'bc')

Map::from( [12345] )->inString( '23' );
// true ('12345' contains '23')

Map::from( [123.4] )->inString( 23.4 );
// true ('123.4' contains '23.4')

Map::from( [12345] )->inString( false );
// true ('12345' contains '')

Map::from( [12345] )->inString( true );
// true ('12345' contains '1')

Map::from( [false] )->inString( false );
// true  ('' contains '')

Map::from( ['abc'] )->inString( '' );
// true ('abc' contains '')

Map::from( [''] )->inString( false );
// true ('' contains '')

Map::from( ['abc'] )->inString( 'BC', false );
// true ('abc' contains 'BC' when case-insentive)

Map::from( ['abc', 'def'] )->inString( ['de', 'xy'] );
// true ('def' contains 'de')

Map::from( ['abc', 'def'] )->inString( ['E', 'x'] );
// false (doesn't contain "E" when case sensitive)

Map::from( ['abc', 'def'] )->inString( 'E' );
// false (doesn't contain "E" when case sensitive)

Map::from( [23456] )->inString( true );
// false ('23456' doesn't contain '1')

Map::from( [false] )->inString( 0 );
// false ('' doesn't contain '0')
```


### int()

Returns an element by key and casts it to integer if possible.

```php
public function int( $key, $default = 0 ) : int
```

* @param **int&#124;string** `$key` Key or path to the requested item
* @param **mixed** `$default` Default value if key isn't found (will be casted to int)
* @return **int** Value from map or default value

This does also work to map values from multi-dimensional arrays by passing the keys
of the arrays separated by the delimiter ("/" by default), e.g. `key1/key2/key3`
to get `val` from `['key1' => ['key2' => ['key3' => 'val']]]`. The same applies to
public properties of objects or objects implementing `__isset()` and `__get()` methods.

**Examples:**

```php
Map::from( ['a' => true] )->int( 'a' );
// 1

Map::from( ['a' => '1'] )->int( 'a' );
// 1 (casted to integer)

Map::from( ['a' => 1.1] )->int( 'a' );
// 1 (casted to integer)

Map::from( ['a' => '10'] )->int( 'a' );
// 10 (casted to integer)

Map::from( ['a' => ['b' => ['c' => 1]]] )->int( 'a/b/c' );
// 1

Map::from( [] )->int( 'c', function() { return rand( 1, 1 ); } );
// 1

Map::from( [] )->int( 'a', 1 );
// 1 (default value used)

Map::from( [] )->int( 'a' );
// 0

Map::from( ['b' => ''] )->int( 'b' );
// 0 (casted to integer)

Map::from( ['a' => 'abc'] )->int( 'a' );
// 0 (casted to integer)

Map::from( ['b' => null] )->int( 'b' );
// 0 (null is not scalar)

Map::from( ['b' => [true]] )->int( 'b' );
// 0 (arrays are not scalar)

Map::from( ['b' => '#resource'] )->int( 'b' );
// 0 (resources are not scalar)

Map::from( ['b' => new \stdClass] )->int( 'b' );
// 0 (objects are not scalar)

Map::from( [] )->int( 'c', new \Exception( 'error' ) );
// throws exception
```


### intersect()

Returns all values in a new map that are available in both, the map and the given elements.

```php
public function intersect( iterable $elements, callable $callback = null ) : self
```

* @param **iterable&#60;int&#124;string,mixed&#62;** `$elements` List of elements
* @param **callable&#124;null** `$callback` Function with (valueA, valueB) parameters and returns -1 (<), 0 (=) and 1 (>)
* @return **self&#60;int&#124;string,mixed&#62;** New map

The keys are preserved using this method.

**Examples:**

```php
Map::from( ['a' => 'foo', 'b' => 'bar'] )->intersect( ['bar'] );
// ['b' => 'bar']
```

If a callback is passed, the given function will be used to compare the values.
The function must accept two parameters (vaA, valB) and must return
-1 if value A is smaller than value B, 0 if both are equal and 1 if value A is
greater than value B. Both, a method name and an anonymous function can be passed:

```php
Map::from( [0 => 'a'] )->intersect( [0 => 'A'], 'strcasecmp' );
// ['a']

Map::from( ['b' => 'a'] )->intersect( ['B' => 'A'], 'strcasecmp' );
// ['a']

Map::from( ['b' => 'a'] )->intersect( ['c' => 'A'], function( $valA, $valB ) {
    return strtolower( $valA ) <=> strtolower( $valB );
} );
// ['a']
```


### intersectAssoc()

Returns all values in a new map that are available in both, the map and the given elements while comparing the keys too.

```php
public function intersectAssoc( iterable $elements, callable $callback = null ) : self
```

* @param **iterable&#60;int&#124;string,mixed&#62;** `$elements` List of elements
* @param **callable&#124;null** `$callback` Function with (valueA, valueB) parameters and returns -1 (<), 0 (=) and 1 (>)
* @return **self&#60;int&#124;string,mixed&#62;** New map

The keys are preserved using this method.

**Examples:**

```php
Map::from( ['a' => 'foo', 'b' => 'bar'] )->intersectAssoc( new Map( ['foo', 'b' => 'bar'] ) );
// ['a' => 'foo']
```

If a callback is passed, the given function will be used to compare the values.
The function must accept two parameters (valA, valB) and must return
-1 if value A is smaller than value B, 0 if both are equal and 1 if value A is
greater than value B. Both, a method name and an anonymous function can be passed:

```php
Map::from( [0 => 'a'] )->intersectAssoc( [0 => 'A'], 'strcasecmp' );
// [0 => 'a']

Map::from( ['b' => 'a'] )->intersectAssoc( ['B' => 'A'], 'strcasecmp' );
// ['b' => 'a']

Map::from( ['b' => 'a'] )->intersectAssoc( ['c' => 'A'], function( $valA, $valB ) {
    return strtolower( $valA ) <=> strtolower( $valB );
} );
// []
```


### intersectKeys()

Returns all values in a new map that are available in both, the map and the given elements by comparing the keys only.

```php
public function intersectKeys( iterable $elements, callable $callback = null ) : self
```

* @param **iterable&#60;int&#124;string,mixed&#62;** `$elements` List of elements
* @param **callable&#124;null** `$callback` Function with (keyA, keyB) parameters and returns -1 (<), 0 (=) and 1 (>)
* @return **self&#60;int&#124;string,mixed&#62;** New map

The keys are preserved using this method.

**Examples:**

```php
Map::from( ['a' => 'foo', 'b' => 'bar'] )->intersectKeys( new Map( ['foo', 'b' => 'baz'] ) );
// ['b' => 'bar']
```

If a callback is passed, the given function will be used to compare the keys.
The function must accept two parameters (key A and B) and must return
-1 if key A is smaller than key B, 0 if both are equal and 1 if key A is
greater than key B. Both, a method name and an anonymous function can be passed:

```php
Map::from( [0 => 'a'] )->intersectKeys( [0 => 'A'], 'strcasecmp' );
// [0 => 'a']

Map::from( ['b' => 'a'] )->intersectKeys( ['B' => 'X'], 'strcasecmp' );
// ['b' => 'a']

Map::from( ['b' => 'a'] )->intersectKeys( ['c' => 'a'], function( $keyA, $keyB ) {
    return strtolower( $keyA ) <=> strtolower( $keyB );
} );
// []
```


### is()

Tests if the map consists of the same keys and values

```php
public function is( iterable $list, bool $strict = false ) : bool
```

* @param **iterable&#60;int&#124;string,mixed&#62;** `$list` List of key/value pairs to compare with
* @param **bool** `$strict` TRUE for comparing order of elements too, FALSE for key/values only
* @return **bool** TRUE if given list is equal, FALSE if not

**Examples:**

```php
Map::from( ['a', 'b'] )->is( ['b', 'a'] );
// true

Map::from( ['a', 'b'] )->is( ['b', 'a'], true );
// false

Map::from( [1, 2] )->is( ['1', '2'] );
// false
```


### isEmpty()

Determines if the map is empty or not.

```php
public function isEmpty() : bool
```

* @return **bool** TRUE if map is empty, FALSE if not

The method is equivalent to [empty()](#empty).

**Examples:**

```php
Map::from( [] )->isEmpty();
// true

Map::from( ['a'] )-isEmpty();
// false
```


### isObject()

Determines if all entries are objects.

```php
public function isObject() : bool
```

* @return **bool** TRUE if all map entries are objects, FALSE if not

**Examples:**

```php
Map::from( [] )->isObject();
// true

Map::from( [new stdClass] )->isObject();
// true

Map::from( [1] )->isObject();
// false
```


### isNumeric()

Determines if all entries are numeric values.

```php
public function isNumeric() : bool
```

* @return **bool** TRUE if all map entries are numeric values, FALSE if not

**Examples:**

```php
Map::from( [] )->isNumeric();
// true

Map::from( [1] )->isNumeric();
// true

Map::from( [1.1] )->isNumeric();
// true

Map::from( [010] )->isNumeric();
// true

Map::from( [0x10] )->isNumeric();
// true

Map::from( [0b10] )->isNumeric();
// true

Map::from( ['010'] )->isNumeric();
// true

Map::from( ['10'] )->isNumeric();
// true

Map::from( ['10.1'] )->isNumeric();
// true

Map::from( [' 10 '] )->isNumeric();
// true

Map::from( ['10e2'] )->isNumeric();
// true

Map::from( ['0b10'] )->isNumeric();
// false

Map::from( ['0x10'] )->isNumeric();
// false

Map::from( ['null'] )->isNumeric();
// false

Map::from( [null] )->isNumeric();
// false

Map::from( [true] )->isNumeric();
// false

Map::from( [[]] )->isNumeric();
// false

Map::from( [''] )->isNumeric();
// false
```


### isScalar()

Determines if all entries are scalar values.

```php
public function isScalar() : bool
```

* @return **bool** TRUE if all map entries are scalar values, FALSE if not

**Examples:**

```php
Map::from( [] )->isScalar();
// true

Map::from( [1] )->isScalar();
// true

Map::from( [1.1] )->isScalar();
// true

Map::from( ['abc'] )->isScalar();
// true

Map::from( [true, false] )->isScalar();
// true

Map::from( [new stdClass] )->isScalar();
// false

Map::from( [resource] )->isScalar();
// false

Map::from( [null] )->isScalar();
// false

Map::from( [[1]] )->isScalar();
// false
```


### join()

Concatenates the string representation of all elements.

```php
public function join( $glue = '' ) : string
```

* @param **string** `$glue` Character or string added between elements
* @return **string** String of concatenated map elements

Objects that implement `__toString()` does also work, otherwise (and in case
of arrays) a PHP notice is generated. NULL and FALSE values are treated as
empty strings.

**Examples:**

```php
Map::from( ['a', 'b', false] )->join();
// 'ab'

Map::from( ['a', 'b', null, false] )->join( '-' );
// 'a-b--'
```


### jsonSerialize()

Specifies the data which should be serialized to JSON by json_encode().

```php
public function jsonSerialize()
```

* @return **array&#60;int&#124;string,mixed&#62;** Data to serialize to JSON

**Examples:**

```php
json_encode( Map::from( ['a', 'b'] ) );
// ["a", "b"]

json_encode( Map::from( ['a' => 0, 'b' => 1] ) );
// {"a":0,"b":1}
```


### keys()

Returns the keys of the map elements in a new map object.

```php
public function keys() : self
```

* @return **self&#60;int&#124;string,mixed&#62;** New map

**Examples:**

```php
Map::from( ['a', 'b'] );
// [0, 1]

Map::from( ['a' => 0, 'b' => 1] );
// ['a', 'b']
```


### krsort()

Sorts the elements by their keys in reverse order.

```php
public function krsort( int $options = SORT_REGULAR ) : self
```

* @param **int** `$options` Sort options for `krsort()`
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

The parameter modifies how the keys are compared. Possible values are:
- SORT_REGULAR : compare elements normally (don't change types)
- SORT_NUMERIC : compare elements numerically
- SORT_STRING : compare elements as strings
- SORT_LOCALE_STRING : compare elements as strings, based on the current locale or changed by `setlocale()`
- SORT_NATURAL : compare elements as strings using "natural ordering" like `natsort()`
- SORT_FLAG_CASE : use SORT_STRING&#124;SORT_FLAG_CASE and SORT_NATURAL&#124;SORT_FLAG_CASE to sort strings case-insensitively

The keys are preserved using this method and no new map is created.

**Examples:**

```php
Map::from( ['b' => 0, 'a' => 1] )->krsort();
// ['a' => 1, 'b' => 0]

Map::from( [1 => 'a', 0 => 'b'] )->krsort();
// [0 => 'b', 1 => 'a']
```


### ksort()

Sorts the elements by their keys.

```php
public function ksort( int $options = SORT_REGULAR ) : self
```

* @param **int** `$options` Sort options for `ksort()`
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

The parameter modifies how the keys are compared. Possible values are:
- SORT_REGULAR : compare elements normally (don't change types)
- SORT_NUMERIC : compare elements numerically
- SORT_STRING : compare elements as strings
- SORT_LOCALE_STRING : compare elements as strings, based on the current locale or changed by `setlocale()`
- SORT_NATURAL : compare elements as strings using "natural ordering" like `natsort()`
- SORT_FLAG_CASE : use SORT_STRING&#124;SORT_FLAG_CASE and SORT_NATURAL&#124;SORT_FLAG_CASE to sort strings case-insensitively

The keys are preserved using this method and no new map is created.

**Examples:**

```php
Map::from( ['b' => 0, 'a' => 1] )->ksort();
// ['a' => 1, 'b' => 0]

Map::from( [1 => 'a', 0 => 'b'] )->ksort();
// [0 => 'b', 1 => 'a']
```


### last()

Returns the last element from the map.

```php
public function last( $default = null )
```

* @param **mixed** `$default` Default value or exception if the map contains no elements
* @return **mixed** Last value of map, (generated) default value or an exception

**Examples:**

```php
Map::from( ['a', 'b'] )->last();
// 'b'

Map::from( [] )->last( 'x' );
// 'x'

Map::from( [] )->last( new \Exception( 'error' ) );
// throws \Exception

Map::from( [] )->last( function() { return rand(); } );
// random integer
```


### lastKey()

Returns the last key from the map.

```php
public function lastKey()
```

* @return **mixed** Last key of map or NULL if empty

**Examples:**

```php
Map::from( ['a' => 1, 'b' => 2] )->lastKey();
// 'b'

Map::from( [] )->lastKey();
// null
```


### ltrim()

Removes the passed characters from the left of all strings.

```php
public function ltrim( string $chars = " \n\r\t\v\x00" ) : self
```

* @param **string** `$chars` List of characters to trim
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

**Examples:**

```php
Map::from( [" abc\n", "\tcde\r\n"] )->ltrim();
// ["abc\n", "cde\r\n"]

Map::from( ["a b c", "cbxa"] )->ltrim( 'abc' );
// [" b c", "xa"]
```


### map()

Calls the passed function once for each element and returns a new map for the result.

```php
public function map( callable $callback ) : self
```

* @param **callable** `$callback` Function with (value, key) parameters and returns computed result
* @return **self&#60;int&#124;string,mixed&#62;** New map with the original keys and the computed values

The keys are preserved using this method.

**Examples:**

```php
Map::from( ['a' => 2, 'b' => 4] )->map( function( $value, $key ) {
    return $value * 2;
} );
// ['a' => 4, 'b' => 8]
```


### max()

Returns the maximum value of all elements.

```php
public function max( string $col = null )
```

* @param **string&#124;null** `$col` Key in the nested array or object to check for
* @return **mixed** Maximum value or NULL if there are no elements in the map

This does also work to map values from multi-dimensional arrays by passing the keys
of the arrays separated by the delimiter ("/" by default), e.g. `key1/key2/key3`
to get `val` from `['key1' => ['key2' => ['key3' => 'val']]]`. The same applies to
public properties of objects or objects implementing `__isset()` and `__get()` methods.

Be careful comparing elements of different types because this can have
unpredictable results due to the [PHP comparison rules](https://www.php.net/manual/en/language.operators.comparison.php)

**Examples:**

```php
Map::from( [1, 3, 2, 5, 4] )->max();
// 5

Map::from( ['bar', 'foo', 'baz'] )->max();
// 'foo'

Map::from( [['p' => 30], ['p' => 50], ['p' => 10]] )->max( 'p' );
// 50

Map::from( [['i' => ['p' => 30]], ['i' => ['p' => 50]]] )->max( 'i/p' );
// 50
```


### merge()

Merges the map with the given elements without returning a new map.

```php
public function merge( iterable $elements, bool $recursive = false ) : self
```

* @param **iterable&#60;int&#124;string,mixed&#62;** `$elements` List of elements
* @param **bool** `$recursive` TRUE to merge nested arrays too, FALSE for first level elements only
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

Elements with the same non-numeric keys will be overwritten, elements with the
same numeric keys will be added.

The method is similar to [replace()](#replace) but doesn't replace elements with the same
numeric keys. If you want to be sure that all passed elements are added without
replacing existing ones, use [concat()](#concat) instead.

The keys are preserved using this method.

**Examples:**

```php
Map::from( ['a', 'b'] )->merge( ['b', 'c'] );
// ['a', 'b', 'b', 'c']

Map::from( ['a' => 1, 'b' => 2] )->merge( ['b' => 4, 'c' => 6] );
// ['a' => 1, 'b' => 4, 'c' => 6]

Map::from( ['a' => 1, 'b' => 2] )->merge( ['b' => 4, 'c' => 6], true );
// ['a' => 1, 'b' => [2, 4], 'c' => 6]
```


### method()

Registers a custom method or returns the existing one.

```php
public static function method( string $method, \Closure $fcn = null ) : ?\Closure
```

* @param **string** `$method` Method name
* @param **\Closure&#124;null** `$fcn` Anonymous function or NULL to return the closure if available
* @return **\Closure&#124;null** Registered anonymous function or NULL if none has been registered

The registed method has access to the class properties if called non-static.

**Examples:**

```php
Map::method( 'foo', function( $arg1, $arg2 ) {
    return array_merge( $this->elements, [$arg1, $arg2] );
} );

Map::method( 'foo' );
// registered closure

Map::method( 'foo2' );
// NULL

Map::from( ['bar'] )->foo( 'foo', 'baz' );
// ['bar', 'foo', 'baz']



Map::foo( 'foo', 'baz' );
// error because `$this->elements` isn't available
```

Static calls can't access `$this->elements` but can operate on the parameter values:

```php
Map::method( 'bar', function( $arg1, $arg2 ) {
    return new static( [$arg1, $arg2] );
} );

Map::foo( 'foo', 'baz' );
// ['foo', 'baz']
```


### min()

Returns the minimum value of all elements.

```php
public function min( string $col = null )
```

* @param **string&#124;null** `$col` Key in the nested array or object to check for
* @return **mixed** Minimum value or NULL if there are no elements in the map

This does also work to map values from multi-dimensional arrays by passing the keys
of the arrays separated by the delimiter ("/" by default), e.g. `key1/key2/key3`
to get `val` from `['key1' => ['key2' => ['key3' => 'val']]]`. The same applies to
public properties of objects or objects implementing `__isset()` and `__get()` methods.

Be careful comparing elements of different types because this can have
unpredictable results due to the [PHP comparison rules](https://www.php.net/manual/en/language.operators.comparison.php)

**Examples:**

```php
Map::from( [2, 3, 1, 5, 4] )->min();
// 1

Map::from( ['baz', 'foo', 'bar'] )->min();
// 'bar'

Map::from( [['p' => 30], ['p' => 50], ['p' => 10]] )->min( 'p' );
// 10

Map::from( [['i' => ['p' => 30]], ['i' => ['p' => 50]]] )->min( 'i/p' );
// 30
```


### none()

Tests if none of the elements are part of the map.

```php
public function none( $element, bool $strict = false ) : bool
```

* @param **mixed&#124;array** `$element` Element or elements to search for in the map
* @param **bool** `$strict` TRUE to check the type too, using FALSE '1' and 1 will be the same
* @return **bool** TRUE if none of the elements is part of the map, FALSE if at least one is

**Examples:**

```php
Map::from( ['a', 'b'] )->none( 'x' );
// true

Map::from( ['1', '2'] )->none( 2, true );
// true

Map::from( ['a', 'b'] )->none( 'a' );
// false

Map::from( ['a', 'b'] )->none( ['a', 'b'] );
// false

Map::from( ['a', 'b'] )->none( ['a', 'x'] );
// false
```


### nth()

Returns every nth element from the map.

```php
public function nth( int $step, int $offset = 0 ) : self
```

* @param **int** `$step` Step width
* @param **int** `$offset` Number of element to start from (0-based)
* @return **self&#60;int&#124;string,mixed&#62;** New map

**Examples:**

```php
Map::from( ['a', 'b', 'c', 'd', 'e', 'f'] )->nth( 2 );
// ['a', 'c', 'e']

Map::from( ['a', 'b', 'c', 'd', 'e', 'f'] )->nth( 2, 1 );
// ['b', 'd', 'f']
```


### offsetExists()

Determines if an element exists at an offset.

```php
public function offsetExists( $key )
```

* @param **int&#124;string** `$key` Key to check for
* @return **bool** TRUE if key exists, FALSE if not

**Examples:**

```php
$map = Map::from( ['a' => 1, 'b' => 3, 'c' => null] );

isset( $map['b'] );
// true

isset( $map['c'] );
// false

isset( $map['d'] );
// false
```


### offsetGet()

Returns an element at a given offset.

```php
public function offsetGet( $key )
```

* @param **int&#124;string** `$key` Key to return the element for
* @return **mixed** Value associated to the given key

**Examples:**

```php
$map = Map::from( ['a' => 1, 'b' => 3] );

$map['b'];
// 3
```


### offsetSet()

Sets the element at a given offset.

```php
public function offsetSet( $key, $value )
```

* @param **int&#124;string&#124;null** `$key` Key to set the element for or NULL to append value
* @param **mixed** `$value` New value set for the key

**Examples:**

```php
$map = Map::from( ['a' => 1] );

$map['b'] = 2;
// ['a' => 1, 'b' => 2]

$map[0] = 4;
// ['a' => 1, 'b' => 2, 0 => 4]
```


### offsetUnset()

Unsets the element at a given offset.

```php
public function offsetUnset( $key )
```

* @param **int&#124;string** `$key` Key for unsetting the item

**Examples:**

```php
$map = Map::from( ['a' => 1] );

unset( $map['a'] );
// []
```


### only()

Returns a new map with only those elements specified by the given keys.

```php
public function only( $keys ) : self
```

* @param **iterable&#60;mixed&#62;&#124;array&#60;mixed&#62;&#124;string&#124;int** `$keys` Keys of the elements that should be returned
* @return **self&#60;int&#124;string,mixed&#62;** New map with only the elements specified by the keys

The keys are preserved using this method.

**Examples:**

```php
Map::from( ['a' => 1, 0 => 'b'] )->only( 'a' );
// ['a' => 1]

Map::from( ['a' => 1, 0 => 'b', 1 => 'c'] )->only( [0, 1] );
// [0 => 'b', 1 => 'c']
```


### order()

Returns a new map with elements ordered by the passed keys.

```php
public function order( iterable $keys ) : self
```

* @param **iterable&#60;mixed&#62;** `$keys` Keys of the elements in the required order
* @return **self&#60;int&#124;string,mixed&#62;** New map with elements ordered by the passed keys

The keys are preserved using this method.

**Examples:**

```php
Map::from( ['a' => 1, 1 => 'c', 0 => 'b'] )->order( [0, 1, 'a'] );
// [0 => 'b', 1 => 'c', 'a' => 1]

Map::from( ['a' => 1, 1 => 'c', 0 => 'b'] )->order( [0, 1, 2] );
// [0 => 'b', 1 => 'c', 2 => null]

Map::from( ['a' => 1, 1 => 'c', 0 => 'b'] )->order( [0, 1] );
// [0 => 'b', 1 => 'c']
```


### pad()

Fill up to the specified length with the given value

```php
public function pad( int $size, $value = null ) : self
```

* @param **int** `$size` Total number of elements that should be in the list
* @param **mixed** `$value` Value to fill up with if the map length is smaller than the given size
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

In case the given number is smaller than the number of element that are
already in the list, the map is unchanged. If the size is positive, the
new elements are padded on the right, if it's negative then the elements
are padded on the left.

Associative keys are preserved, numerical keys are replaced and numerical
keys are used for the new elements.

**Examples:**

```php
Map::from( [1, 2, 3] )->pad( 5 );
// [1, 2, 3, null, null]

Map::from( [1, 2, 3] )->pad( -5 );
// [null, null, 1, 2, 3]

Map::from( [1, 2, 3] )->pad( 5, '0' );
// [1, 2, 3, '0', '0']

Map::from( [1, 2, 3] )->pad( 2 );
// [1, 2, 3]

Map::from( [10 => 1, 20 => 2] )->pad( 3 );
// [0 => 1, 1 => 2, 2 => null]

Map::from( ['a' => 1, 'b' => 2] )->pad( 3, 3 );
// ['a' => 1, 'b' => 2, 0 => 3]
```


### partition()

Breaks the list of elements into the given number of groups.

```php
public function partition( $num ) : self
```

* @param **\Closure&#124;int** `$number` Function with (value, index) as arguments returning the bucket key or number of groups
* @return **self&#60;int&#124;string,mixed&#62;** New map

The keys of the original map are preserved in the returned map.

**Examples:**

```php
Map::from( [1, 2, 3, 4, 5] )->partition( 3 );
// [[0 => 1, 1 => 2], [2 => 3, 3 => 4], [4 => 5]]

Map::from( [1, 2, 3, 4, 5] )->partition( function( $val, $idx ) {
	return $idx % 3;
} );
// [0 => [0 => 1, 3 => 4], 1 => [1 => 2, 4 => 5], 2 => [2 => 3]]
```


### pipe()

Passes the map to the given callback and return the result.

```php
public function pipe( \Closure $callback )
```

* @param **\Closure** `$callback` Function with map as parameter which returns arbitrary result
* @return **mixed** Result returned by the callback

**Examples:**

```php
Map::from( ['a', 'b'] )->pipe( function( $map ) {
    return strrev( $map->join( '-' ) );
} );
// 'b-a'
```


### pluck()

Returns the values of a single column/property from an array of arrays or list of elements in a new map.

```php
public function pluck( string $valuecol = null, string $indexcol = null ) : self
```

* @param **string&#124;null** `$valuecol` Name or path of the value property
* @param **string&#124;null** `$indexcol` Name or path of the index property
* @return **self&#60;int&#124;string,mixed&#62;** New map with mapped entries

This method is an alias for [col()](#col). For performance reasons, `col()` should
be preferred because it uses one method call less than `pluck()`.


### pop()

Returns and removes the last element from the map.

```php
public function pop()
```

* @return **mixed** Last element of the map or null if empty

**Examples:**

```php
Map::from( ['a', 'b'] )->pop();
// 'b', map contains ['a']
```


### pos

Returns the numerical index of the value.

```php
public function pos( $value ) : ?int
```

* @param **\Closure&#124;mixed** `$value` Value to search for or function with (item, key) parameters return TRUE if value is found
* @return **int&#124;null** Position of the found value (zero based) or NULL if not found

**Examples:**

```php
Map::from( [4 => 'a', 8 => 'b'] )->pos( 'b' );
// 1

Map::from( [4 => 'a', 8 => 'b'] )->pos( function( $item, $key ) {
    return $item === 'b';
} );
// 1
```

Both examples will return "1" because the value "b" is at the second position
and the returned index is zero based so the first item has the index "0".


### prefix

Adds a prefix in front of each map entry.

```php
public function prefix( $prefix, int $depth = null ) : self
```

* @param **\Closure&#124;string** `$prefix` Function with map as parameter which returns arbitrary result
* @param **int&#124;null** `$depth` Maximum depth to dive into multi-dimensional arrays starting from "1"
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

By default, nested arrays are walked recusively so all entries at all levels are prefixed.
The keys of the original map are preserved in the returned map.

**Examples:**

```php
Map::from( ['a', 'b'] )->prefix( '1-' );
// ['1-a', '1-b']

Map::from( ['a', ['b']] )->prefix( '1-' );
// ['1-a', ['1-b']]

Map::from( ['a', ['b']] )->prefix( '1-', 1 );
// ['1-a', ['b']]

Map::from( ['a', 'b'] )->prefix( function( $item, $key ) {
    return ( ord( $item ) + ord( $key ) ) . '-';
} );
// ['145-a', '147-b']
```


### prepend()

Pushes an element onto the beginning of the map without returning a new map.

```php
public function prepend( $value, $key = null ) : self
```

* @param **mixed** `$value` Item to add at the beginning
* @param **int&#124;string&#124;null** `$key` Key for the item or NULL to reindex all numerical keys
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

This method is an alias for the [unshift()](#unshift) method.

**Examples:**

```php
Map::from( ['a', 'b'] )->prepend( 'd' );
// ['d', 'a', 'b']

Map::from( ['a', 'b'] )->prepend( 'd', 'first' );
// ['first' => 'd', 0 => 'a', 1 => 'b']
```


### pull()

Returns and removes an element from the map by its key.

```php
public function pull( $key, $default = null )
```

* @param **int&#124;string** `$key` Key to retrieve the value for
* @param **mixed** `$default` Default value if key isn't available
* @return **mixed** Value from map or default value

**Examples:**

```php
Map::from( ['a', 'b', 'c'] )->pull( 1 );
// 'b', map contains ['a', 'c']

Map::from( ['a', 'b', 'c'] )->pull( 'x', 'none' );
// 'none', map contains ['a', 'b', 'c']
```


### push()

Adds an element onto the end of the map without returning a new map.

```php
public function push( $value ) : self
```

* @param **mixed** `$value` Value to add to the end
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

**Examples:**

```php
Map::from( ['a', 'b'] )->push( 'aa' );
// ['a', 'b', 'aa']
```


### put()

Sets the given key and value in the map without returning a new map.

```php
public function put( $key, $value ) : self
```

This method is an alias for `set()`. For performance reasons, `set()` should be
preferred because it uses one method call less than `put()`.

* @param **int&#124;string** `$key` Key to set the new value for
* @param **mixed** `$value` New element that should be set
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

**Examples:**

```php
Map::from( ['a'] )->put( 1, 'b' );
// [0 => 'a', 1 => 'b']

Map::from( ['a'] )->put( 0, 'b' );
// [0 => 'b']
```


### random()

Returns one or more random element from the map.

```php
public function random( int $max = 1 ) : self
```

* @param **int** `$max` Maximum number of elements that should be returned
* @return **self&#60;int&#124;string,mixed&#62;** New map with key/element pairs from original map in random order
* @throws **\InvalidArgumentException** If requested number of elements is less than 1

The less elements are in the map, the less random the order will be, especially
if the maximum number of values is high or close to the number of elements.

The keys of the original map are preserved in the returned map.

**Examples:**

```php
Map::from( [2, 4, 8, 16] )->random();
// [2 => 8] or any other key/value pair

Map::from( [2, 4, 8, 16] )->random( 2 );
// [3 => 16, 0 => 2] or any other key/value pair

Map::from( [2, 4, 8, 16] )->random( 5 );
// [0 => 2,  1 => 4, 2 => 8, 3 => 16] in random order
```


### reduce()

Iteratively reduces the array to a single value using a callback function.

```php
public function reduce( callable $callback, $initial = null )
```

* @param **callable** `$callback` Function with (result, value) parameters and returns result
* @param **mixed** `$initial` Initial value when computing the result
* @return **mixed** Value computed by the callback function

Afterwards, the map will be empty.

**Examples:**

```php
Map::from( [2, 8] )->reduce( function( $result, $value ) {
    return $result += $value;
}, 10 );
// 20 because 10 + 2 + 8 and map equals []
```


### reject()

Removes all matched elements and returns a new map.

```php
public function reject( $callback = true ) : self
```

* @param **Closure&#124;mixed** `$callback` Function with (item) parameter which returns TRUE/FALSE or value to compare with
* @return **self&#60;int&#124;string,mixed&#62;** New map

This method is the inverse of the [filter()](#filter) and should return TRUE
if the item should be removed from the returned map.

If no callback is passed, all values which are NOT empty, null or false will be
removed. The keys of the original map are preserved in the returned map.

**Examples:**

```php
Map::from( [2 => 'a', 6 => 'b', 13 => 'm', 30 => 'z'] )->reject( function( $value, $key ) {
    return $value < 'm';
} );
// [13 => 'm', 30 => 'z']

Map::from( [2 => 'a', 13 => 'm', 30 => 'z'] )->reject( 'm' );
// [2 => 'a', 30 => 'z']

Map::from( [2 => 'a', 6 => null, 13 => 'm'] )->reject();
// [6 => null]
```


### rekey()

Changes the keys according to the passed function.

```php
public function rekey( callable $callback ) : self
```

* @param **callable** `$callback` Function with (value, key) parameters and returns new key
* @return **self&#60;int&#124;string,mixed&#62;** New map with new keys and original values

**Examples:**

```php
Map::from( ['a' => 2, 'b' => 4] )->rekey( function( $value, $key ) {
    return 'key-' . $key;
} );
// ['key-a' => 2, 'key-b' => 4]
```


### remove()

Removes one or more elements from the map by its keys without returning a new map.

```php
public function remove( $keys ) : self
```

* @param **iterable&#60;int&#124;string&#62;&#124;array&#60;int&#124;string&#62;&#124;string&#124;int** `$keys` List of keys
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

**Examples:**

```php
Map::from( ['a' => 1, 2 => 'b'] )->remove( 'a' );
// [2 => 'b']

Map::from( ['a' => 1, 2 => 'b'] )->remove( [2, 'a'] );
// []
```


### replace()

Replaces elements in the map with the given elements without returning a new map.

```php
public function replace( iterable $elements, bool $recursive = true ) : self
```

* @param **iterable&#60;int&#124;string,mixed&#62;** `$elements` List of elements
* @param **bool** `$recursive` TRUE to replace recursively (default), FALSE to replace elements only
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

The method is similar to [merge()](#merge) but also replaces elements with numeric keys.
These would be added by `merge()` with a new numeric key.

The keys are preserved in the returned map.

**Examples:**

```php
Map::from( ['a' => 1, 2 => 'b'] )->replace( ['a' => 2] );
// ['a' => 2, 2 => 'b']

Map::from( ['a' => 1, 'b' => ['c' => 3, 'd' => 4]] )->replace( ['b' => ['c' => 9]] );
// ['a' => 1, 'b' => ['c' => 9, 'd' => 4]]
```


### reverse()

Reverses the element order without returning a new map.

```php
public function reverse() : self
```

* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

The keys are preserved using this method.

**Examples:**

```php
Map::from( ['a', 'b'] )->reverse();
// ['b', 'a']

Map::from( ['name' => 'test', 'last' => 'user'] )->reverse();
// ['last' => 'user', 'name' => 'test']
```


### rsort()

Sorts all elements in reverse order without maintaining the key association.

```php
public function rsort( int $options = SORT_REGULAR ) : self
```

* @param **int** `$options` Sort options for `rsort()`
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

The parameter modifies how the values are compared. Possible parameter values are:
- SORT_REGULAR : compare elements normally (don't change types)
- SORT_NUMERIC : compare elements numerically
- SORT_STRING : compare elements as strings
- SORT_LOCALE_STRING : compare elements as strings, based on the current locale or changed by `setlocale()`
- SORT_NATURAL : compare elements as strings using "natural ordering" like `natsort()`
- SORT_FLAG_CASE : use SORT_STRING&#124;SORT_FLAG_CASE and SORT_NATURAL&#124;SORT_FLAG_CASE to sort strings case-insensitively

The keys are NOT preserved and elements get a new index. No new map is created.

**Examples:**

```php
Map::from( ['a' => 1, 'b' => 0] )->rsort();
// [0 => 1, 1 => 0]

Map::from( [0 => 'b', 1 => 'a'] )->rsort();
// [0 => 'b', 1 => 'a']

Map::from( [0 => 'C', 1 => 'b'] )->rsort();
// [0 => 'b', 1 => 'C']

Map::from( [0 => 'C', 1 => 'b'] )->rsort( SORT_STRING|SORT_FLAG_CASE );
// [0 => 'C', 1 => 'b'] because 'C' -> 'c' and 'c' > 'b'
```


### rtrim()

Removes the passed characters from the right of all strings.

```php
public function rtrim( string $chars = " \n\r\t\v\x00" ) : self
```

* @param **string** `$chars` List of characters to trim
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

**Examples:**

```php
Map::from( [" abc\n", "\tcde\r\n"] )->rtrim();
// [" abc", "\tcde"]

Map::from( ["a b c", "cbxa"] )->rtrim( 'abc' );
// ["a b ", "cbx"]
```


### search()

Searches the map for a given value and return the corresponding key if successful.

```php
public function search( $value, $strict = true )
```

* @param **mixed** `$value` Item to search for
* @param **bool** `$strict` TRUE if type of the element should be checked too
* @return **mixed&#124;null** Value from map or null if not found

**Examples:**

```php
Map::from( ['a', 'b', 'c'] )->search( 'b' );
// 1

Map::from( [1, 2, 3] )->search( '2', true );
// null because the types doesn't match (int vs. string)
```


### sep()

Sets the seperator for paths to values in multi-dimensional arrays or objects.

```php
public static function sep( string $char ) : self
```

* @param **string** `$char` Separator character, e.g. "." for "key.to.value" instead of "key/to/value"
* @return **self&#60;int&#124;string,mixed&#62;** Same map for fluid interface

This method only changes the separator for the current map instance. To
change the separator for all maps created afterwards, use the static
[Map::delimiter()](#delimiter) method instead.

**Examples:**

```php
Map::from( ['foo' => ['bar' => 'baz']] )->sep( '.' )->get( 'foo.bar' );
// 'baz'
```


### set()

Sets an element in the map by key without returning a new map.

```php
public function set( $key, $value ) : self
```

* @param **int&#124;string** `$key` Key to set the new value for
* @param **mixed** `$value` New element that should be set
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

**Examples:**

```php
Map::from( ['a'] )->set( 1, 'b' );
// [0 => 'a', 1 => 'b']

Map::from( ['a'] )->set( 0, 'b' );
// [0 => 'b']
```


### shift()

Returns and removes the first element from the map.

```php
public function shift()
```

* @return **mixed&#124;null** Value from map or null if not found

**Examples:**

```php
Map::from( ['a', 'b'] )->shift();
// 'a'

Map::from( [] )->shift();
// null
```

**Performance note:**

The bigger the list, the higher the performance impact because `shift()`
reindexes all existing elements. Usually, it's better to [reverse()](#reverse)
the list and [pop()](#pop) entries from the list afterwards if a significant
number of elements should be removed from the list:

```php
$map->reverse()->pop();
```

instead of

```php
$map->shift();
```


### shuffle()

Shuffles the elements in the map without returning a new map.

```php
public function shuffle( bool $assoc = false ) : self
```

* @param **bool** `$assoc` True to preserve keys, false to assign new keys
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

**Examples:**

```php
Map::from( [2 => 'a', 4 => 'b'] )->shuffle();
// ['a', 'b'] in random order with new keys

Map::from( [2 => 'a', 4 => 'b'] )->shuffle( true );
// [2 => 'a', 4 => 'b'] in random order with keys preserved
```


### skip()

Returns a new map with the given number of items skipped.

```php
public function skip( $offset ) : self
```

* @param **\Closure&#124;int** `$offset` Number of items to skip or function($item, $key) returning true for skipped items
* @return **self&#60;int&#124;string,mixed&#62;** New map

The keys of the items returned in the new map are the same as in the original one.

**Examples:**

```php
Map::from( [1, 2, 3, 4] )->skip( 2 );
// [2 => 3, 3 => 4]

Map::from( [1, 2, 3, 4] )->skip( function( $item, $key ) {
    return $item < 4;
} );
// [3 => 4]
```


### slice()

Returns a map with the slice from the original map.

```php
public function slice( int $offset, int $length = null ) : self
```

* @param **int** `$offset` Number of elements to start from
* @param **int&#124;null** `$length` Number of elements to return or NULL for no limit
* @return **self&#60;int&#124;string,mixed&#62;** New map

The rules for offsets are:
- If offset is non-negative, the sequence will start at that offset
- If offset is negative, the sequence will start that far from the end

Similar for the length:
- If length is given and is positive, then the sequence will have up to that many elements in it
- If the array is shorter than the length, then only the available array elements will be present
- If length is given and is negative then the sequence will stop that many elements from the end
- If it is omitted, then the sequence will have everything from offset up until the end

The keys of the items returned in the new map are the same as in the original one.

**Examples:**

```php
Map::from( ['a', 'b', 'c'] )->slice( 1 );
// ['b', 'c']

Map::from( ['a', 'b', 'c'] )->slice( 1, 1 );
// ['b']

Map::from( ['a', 'b', 'c', 'd'] )->slice( -2, -1 );
// ['c']
```


### some()

Tests if at least one element passes the test or is part of the map.

```php
public function some( $values, bool $strict = false ) : bool
```

* @param **\Closure&#124;iterable&#124;mixed** `$values` Anonymous function with (item, key) parameter, element or list of elements to test against
* @param **bool** `$strict` TRUE to check the type too, using FALSE '1' and 1 will be the same
* @return **bool** TRUE if at least one element is available in map, FALSE if the map contains none of them

**Examples:**

```php
Map::from( ['a', 'b'] )->some( 'a' );
// true

Map::from( ['a', 'b'] )->some( ['a', 'c'] );
// true

Map::from( ['a', 'b'] )->some( function( $item, $key ) {
    return $item === 'a'
} );
// true

Map::from( ['a', 'b'] )->some( ['c', 'd'] );
// false

Map::from( ['1', '2'] )->some( [2], true );
// false
```


### sort()

Sorts all elements without maintaining the key association.

```php
public function sort( int $options = SORT_REGULAR ) : self
```

* @param **int** `$options` Sort options for `sort()`
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

The parameter modifies how the values are compared. Possible parameter values are:
- SORT_REGULAR : compare elements normally (don't change types)
- SORT_NUMERIC : compare elements numerically
- SORT_STRING : compare elements as strings
- SORT_LOCALE_STRING : compare elements as strings, based on the current locale or changed by `setlocale()`
- SORT_NATURAL : compare elements as strings using "natural ordering" like `natsort()`
- SORT_FLAG_CASE : use SORT_STRING&#124;SORT_FLAG_CASE and SORT_NATURAL&#124;SORT_FLAG_CASE to sort strings case-insensitively

The keys are NOT preserved and elements get a new index. No new map is created.

**Examples:**

```php
Map::from( ['a' => 1, 'b' => 0] )->sort();
// [0 => 0, 1 => 1]

Map::from( [0 => 'b', 1 => 'a'] )->sort();
// [0 => 'a', 1 => 'b']
```


### splice()

Removes a portion of the map and replace it with the given replacement, then return the updated map.

```php
public function splice( int $offset, int $length = null, $replacement = [] ) : self
```

* @param **int** `$offset` Number of elements to start from
* @param **int&#124;null** `$length` Number of elements to remove, NULL for all
* @param **mixed** `$replacement` List of elements to insert
* @return **self&#60;int&#124;string,mixed&#62;** New map

The rules for offsets are:
- If offset is non-negative, the sequence will start at that offset
- If offset is negative, the sequence will start that far from the end

Similar for the length:
- If length is given and is positive, then the sequence will have up to that many elements in it
- If the array is shorter than the length, then only the available array elements will be present
- If length is given and is negative then the sequence will stop that many elements from the end
- If it is omitted, then the sequence will have everything from offset up until the end

Numerical array indexes are NOT preserved.

**Examples:**

```php
Map::from( ['a', 'b', 'c'] )->splice( 1 );
// ['b', 'c'] and map contains ['a']

Map::from( ['a', 'b', 'c'] )->splice( 1, 1, ['x', 'y'] );
// ['b'] and map contains ['a', 'x', 'y', 'c']
```


### strAfter()

Returns the strings after the passed value.

```php
public function strAfter( string $value, bool $case = false, string $encoding = 'UTF-8' ) : self
```

* @param **string** `$value` Character or string to search for
* @param **bool** `$case` TRUE if search should be case insensitive, FALSE if case-sensitive
* @param **string** `$encoding` Character encoding of the strings, e.g. "UTF-8" (default), "ASCII", "ISO-8859-1", etc.
* @return **self&#60;int&#124;string,mixed&#62;** New map

All scalar values (bool, int, float, string) will be converted to strings.
Non-scalar values as well as empty strings will be skipped and are not part of the result.

**Examples:**

```php
Map::from( ['äöüß'] )->strAfter( 'ö' );
// ['üß']

Map::from( ['abc'] )->strAfter( '' );
// ['abc']

Map::from( ['abc'] )->strAfter( 'b' );
// ['c']

Map::from( ['abc'] )->strAfter( 'c' );
// ['']

Map::from( ['abc'] )->strAfter( 'x' )
// []

Map::from( [''] )->strAfter( '' );
// []

Map::from( [1, 1.0, true, ['x'], new \stdClass] )->strAfter( '' );
// ['1', '1', '1']

Map::from( [0, 0.0, false, []] )->strAfter( '' );
// ['0', '0']
```


### strBefore()

Returns the strings before the passed value.

```php
public function strBefore( string $value, bool $case = false, string $encoding = 'UTF-8' ) : self
```

* @param **string** `$value` Character or string to search for
* @param **bool** `$case` TRUE if search should be case insensitive, FALSE if case-sensitive
* @param **string** `$encoding` Character encoding of the strings, e.g. "UTF-8" (default), "ASCII", "ISO-8859-1", etc.
* @return **self&#60;int&#124;string,mixed&#62;** New map

All scalar values (bool, int, float, string) will be converted to strings.
Non-scalar values as well as empty strings will be skipped and are not part of the result.

**Examples:**

```php
Map::from( ['äöüß'] )->strBefore( 'ü' );
// ['äö']

Map::from( ['abc'] )->strBefore( '' );
// ['abc']

Map::from( ['abc'] )->strBefore( 'b' );
// ['a']

Map::from( ['abc'] )->strBefore( 'a' );
// ['']

Map::from( ['abc'] )->strBefore( 'x' )
// []

Map::from( [''] )->strBefore( '' );
// []

Map::from( [1, 1.0, true, ['x'], new \stdClass] )->strBefore( '' );
// ['1', '1', '1']

Map::from( [0, 0.0, false, []] )->strBefore( '' );
// ['0', '0']
```


### strContains()

Tests if at least one of the passed strings is part of at least one entry.

```php
public function strContains( $value, string $encoding = 'UTF-8' ) : bool
```

* @param **array&#124;string** `$value` The string or list of strings to search for in each entry
* @param **string** `$encoding` Character encoding of the strings, e.g. "UTF-8" (default), "ASCII", "ISO-8859-1", etc.
* @return **bool** TRUE if one of the entries contains one of the strings, FALSE if not

**Examples:**

```php
Map::from( ['abc'] )->strContains( '' );
// true

Map::from( ['abc'] )->strContains( 'a' );
// true

Map::from( ['abc'] )->strContains( 'bc' );
// true

Map::from( [12345] )->strContains( '23' );
// true

Map::from( [123.4] )->strContains( 23.4 );
// true

Map::from( [12345] )->strContains( false );
// true ('12345' contains '')

Map::from( [12345] )->strContains( true );
// true ('12345' contains '1')

Map::from( [false] )->strContains( false );
// true  ('' contains '')

Map::from( [''] )->strContains( false );
// true ('' contains '')

Map::from( ['abc'] )->strContains( ['b', 'd'] );
// true

Map::from( ['abc'] )->strContains( 'c', 'ASCII' );
// true

Map::from( ['abc'] )->strContains( 'd' );
// false

Map::from( ['abc'] )->strContains( 'cb' );
// false

Map::from( [23456] )->strContains( true );
// false ('23456' doesn't contain '1')

Map::from( [false] )->strContains( 0 );
// false ('' doesn't contain '0')

Map::from( ['abc'] )->strContains( ['d', 'e'] );
// false

Map::from( ['abc'] )->strContains( 'cb', 'ASCII' );
// false
```


### strContainsAll()

Tests if all of the entries contains one of the passed strings.

```php
public function strContainsAll( $value, string $encoding = 'UTF-8' ) : bool
```

* @param **array&#124;string** `$value` The string or list of strings to search for in each entry
* @param **string** `$encoding` Character encoding of the strings, e.g. "UTF-8" (default), "ASCII", "ISO-8859-1", etc.
* @return **bool** TRUE if all of the entries contains at least one of the strings, FALSE if not

**Examples:**

```php
Map::from( ['abc', 'def'] )->strContainsAll( '' );
// true

Map::from( ['abc', 'cba'] )->strContainsAll( 'a' );
// true

Map::from( ['abc', 'bca'] )->strContainsAll( 'bc' );
// true

Map::from( [12345, '230'] )->strContainsAll( '23' );
// true

Map::from( [123.4, 23.42] )->strContainsAll( 23.4 );
// true

Map::from( [12345, '234'] )->strContainsAll( [true, false] );
// true ('12345' contains '1' and '234' contains '')

Map::from( ['', false] )->strContainsAll( false );
// true ('' contains '')

Map::from( ['abc', 'def'] )->strContainsAll( ['b', 'd'] );
// true

Map::from( ['abc', 'ecf'] )->strContainsAll( 'c', 'ASCII' );
// true

Map::from( ['abc', 'def'] )->strContainsAll( 'd' );
// false

Map::from( ['abc', 'cab'] )->strContainsAll( 'cb' );
// false

Map::from( [23456, '123'] )->strContains( true );
// false ('23456' doesn't contain '1')

Map::from( [false, '000'] )->strContains( 0 );
// false ('' doesn't contain '0')

Map::from( ['abc', 'acf'] )->strContainsAll( ['d', 'e'] );
// false

Map::from( ['abc', 'bca'] )->strContainsAll( 'cb', 'ASCII' );
// false
```


### strEnds()

Tests if at least one of the entries ends with one of the passed strings.

```php
public function strEnds( $value, string $encoding = 'UTF-8' ) : bool
```

* @param **array&#124;string** `$value` The string or list of strings to search for in each entry
* @param **string** `$encoding` Character encoding of the strings, e.g. "UTF-8" (default), "ASCII", "ISO-8859-1", etc.
* @return **bool** TRUE if one of the entries ends with the string, FALSE if not

**Examples:**

```php
Map::from( ['abc'] )->strEnds( '' );
// true

Map::from( ['abc'] )->strEnds( 'c' );
// true

Map::from( ['abc'] )->strEnds( 'bc' );
// true

Map::from( ['abc'] )->strEnds( ['b', 'c'] );
// true

Map::from( ['abc'] )->strEnds( 'c', 'ASCII' );
// true

Map::from( ['abc'] )->strEnds( 'a' );
// false

Map::from( ['abc'] )->strEnds( 'cb' );
// false

Map::from( ['abc'] )->strEnds( ['d', 'b'] );
// false

Map::from( ['abc'] )->strEnds( 'cb', 'ASCII' );
// false
```


### strEndsAll()

Tests if all of the entries ends with at least one of the passed strings.

```php
public function strEndsAll( $value, string $encoding = 'UTF-8' ) : bool
```

* @param **array&#124;string** `$value` The string or list of strings to search for in each entry
* @param **string** `$encoding` Character encoding of the strings, e.g. "UTF-8" (default), "ASCII", "ISO-8859-1", etc.
* @return **bool** TRUE if all of the entries ends with at least one of the strings, FALSE if not

**Examples:**

```php
Map::from( ['abc', 'def'] )->strEndsAll( '' );
// true

Map::from( ['abc', 'bac'] )->strEndsAll( 'c' );
// true

Map::from( ['abc', 'cbc'] )->strEndsAll( 'bc' );
// true

Map::from( ['abc', 'def'] )->strEndsAll( ['c', 'f'] );
// true

Map::from( ['abc', 'efc'] )->strEndsAll( 'c', 'ASCII' );
// true

Map::from( ['abc', 'fed'] )->strEndsAll( 'd' );
// false

Map::from( ['abc', 'bca'] )->strEndsAll( 'ca' );
// false

Map::from( ['abc', 'acf'] )->strEndsAll( ['a', 'c'] );
// false

Map::from( ['abc', 'bca'] )->strEndsAll( 'ca', 'ASCII' );
// false
```


### string()

Returns an element by key and casts it to string if possible.

```php
public function string( $key, $default = '' ) : string
```

* @param **int&#124;string** `$key` Key or path to the requested item
* @param **mixed** `$default` Default value if key isn't found (will be casted to string)
* @return **string** Value from map or default value

This does also work to map values from multi-dimensional arrays by passing the keys
of the arrays separated by the delimiter ("/" by default), e.g. `key1/key2/key3`
to get `val` from `['key1' => ['key2' => ['key3' => 'val']]]`. The same applies to
public properties of objects or objects implementing `__isset()` and `__get()` methods.

**Examples:**

```php
Map::from( ['a' => true] )->string( 'a' );
// '1'

Map::from( ['a' => 1] )->string( 'a' );
// '1'

Map::from( ['a' => 1.1] )->string( 'a' );
// '1.1'

Map::from( ['a' => 'abc'] )->string( 'a' );
// 'abc'

Map::from( ['a' => ['b' => ['c' => 'yes']]] )->string( 'a/b/c' );
// 'yes'

Map::from( [] )->string( 'c', function() { return 'no'; } );
// 'no'

Map::from( [] )->string( 'b' );
// ''

Map::from( ['b' => ''] )->string( 'b' );
// ''

Map::from( ['b' => null] )->string( 'b' );
// ''

Map::from( ['b' => [true]] )->string( 'b' );
// ''

Map::from( ['b' => '#resource'] )->string( 'b' );
// '' (resources are not scalar)

Map::from( ['b' => new \stdClass] )->string( 'b' );
// '' (objects are not scalar)

Map::from( [] )->string( 'c', new \Exception( 'error' ) );
// throws exception
```


### strLower()

Converts all alphabetic characters in strings to lower case.

```php
public function strLower( string $encoding = 'UTF-8' ) : self
```

* @param **string** `$encoding` Character encoding of the strings, e.g. "UTF-8" (default), "ASCII", "ISO-8859-1", etc.
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

**Examples:**

```php
Map::from( ['My String'] )->strLower();
// ["my string"]

Map::from( ['Τάχιστη'] )->strLower();
// ["τάχιστη"]

Map::from( ['Äpfel', 'Birnen'] )->strLower( 'ISO-8859-1' );
// ["äpfel", "birnen"]
```


### strReplace()

Replaces all occurrences of the search string with the replacement string.

```php
public function strReplace( $search, $replace, bool $case = false ) : self
```

* @param **array&#124;string** `$search` String or list of strings to search for
* @param **array&#124;string** `$replace` String or list of strings of replacement strings
* @param **bool** `$case` TRUE if replacements should be case insensitive, FALSE if case-sensitive
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

If you use an array of strings for search or search/replacement, the order of
the strings matters! Each search string found is replaced by the corresponding
replacement string at the same position.

In case of array parameters and if the number of replacement strings is less
than the number of search strings, the search strings with no corresponding
replacement string are replaced with empty strings. Replacement strings with
no corresponding search string are ignored.

An array parameter for the replacements is only allowed if the search parameter
is an array of strings too!

Because the method replaces from left to right, it might replace a previously
inserted value when doing multiple replacements. Entries which are non-string
values are left untouched.

**Examples:**

```php
Map::from( ['google.com', 'aimeos.com'] )->strReplace( '.com', '.de' );
// ['google.de', 'aimeos.de']

Map::from( ['google.com', 'aimeos.org'] )->strReplace( ['.com', '.org'], '.de' );
// ['google.de', 'aimeos.de']

Map::from( ['google.com', 'aimeos.org'] )->strReplace( ['.com', '.org'], ['.de'] );
// ['google.de', 'aimeos']

Map::from( ['google.com', 'aimeos.org'] )->strReplace( ['.com', '.org'], ['.fr', '.de'] );
// ['google.fr', 'aimeos.de']

Map::from( ['google.com', 'aimeos.com'] )->strReplace( ['.com', '.co'], ['.co', '.de', '.fr'] );
// ['google.de', 'aimeos.de']

Map::from( ['google.com', 'aimeos.com', 123] )->strReplace( '.com', '.de' );
// ['google.de', 'aimeos.de', 123]

Map::from( ['GOOGLE.COM', 'AIMEOS.COM'] )->strReplace( '.com', '.de', true );
// ['GOOGLE.de', 'AIMEOS.de']
```


### strStarts()

Tests if at least one of the entries starts with at least one of the passed strings.

```php
public function strStarts( $value, string $encoding = 'UTF-8' ) : bool
```

* @param **array&#124;string** `$value` The string or list of strings to search for in each entry
* @param **string** `$encoding` Character encoding of the strings, e.g. "UTF-8" (default), "ASCII", "ISO-8859-1", etc.
* @return **bool** TRUE if one of the entries starts with one of the strings, FALSE if not

**Examples:**

```php
Map::from( ['abc'] )->strStarts( '' );
// true

Map::from( ['abc'] )->strStarts( 'a' );
// true

Map::from( ['abc'] )->strStarts( 'ab' );
// true

Map::from( ['abc'] )->strStarts( ['a', 'b'] );
// true

Map::from( ['abc'] )->strStarts( 'ab', 'ASCII' );
// true

Map::from( ['abc'] )->strStarts( 'b' );
// false

Map::from( ['abc'] )->strStarts( 'bc' );
// false

Map::from( ['abc'] )->strStarts( ['b', 'c'] );
// false

Map::from( ['abc'] )->strStarts( 'bc', 'ASCII' );
// false
```


### strStartsAll()

Tests if all of the entries starts with one of the passed strings.

```php
public function strStartsAll( $value, string $encoding = 'UTF-8' ) : bool
```

* @param **array&#124;string** `$value` The string or list of strings to search for in each entry
* @param **string** `$encoding` Character encoding of the strings, e.g. "UTF-8" (default), "ASCII", "ISO-8859-1", etc.
* @return **bool** TRUE if one of the entries starts with one of the strings, FALSE if not

**Examples:**

```php
Map::from( ['abc', 'def'] )->strStartsAll( '' );
// true

Map::from( ['abc', 'acb'] )->strStartsAll( 'a' );
// true

Map::from( ['abc', 'aba'] )->strStartsAll( 'ab' );
// true

Map::from( ['abc', 'def'] )->strStartsAll( ['a', 'd'] );
// true

Map::from( ['abc', 'acf'] )->strStartsAll( 'a', 'ASCII' );
// true

Map::from( ['abc', 'def'] )->strStartsAll( 'd' );
// false

Map::from( ['abc', 'bca'] )->strStartsAll( 'ab' );
// false

Map::from( ['abc', 'bac'] )->strStartsAll( ['a', 'c'] );
// false

Map::from( ['abc', 'cab'] )->strStartsAll( 'ab', 'ASCII' );
// false
```


### strUpper()

Converts all alphabetic characters in strings to upper case.

```php
public function strUpper( string $encoding = 'UTF-8' ) :self
```

* @param **string** `$encoding` Character encoding of the strings, e.g. "UTF-8" (default), "ASCII", "ISO-8859-1", etc.
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

**Examples:**

```php
Map::from( ['My String'] )->strUpper();
// ["MY STRING"]

Map::from( ['τάχιστη'] )->strUpper();
// ["ΤΆΧΙΣΤΗ"]

Map::from( ['äpfel', 'birnen'] )->strUpper( 'ISO-8859-1' );
// ["ÄPFEL", "BIRNEN"]
```


### suffix

Adds a suffix at the end of each map entry.

```php
public function suffix( $suffix, int $depth = null ) : self
```

* @param **\Closure&#124;string** `$suffix` Function with map as parameter which returns arbitrary result
* @param **int&#124;null** `$depth` Maximum depth to dive into multi-dimensional arrays starting from "1"
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

By defaul, nested arrays are walked recusively so all entries at all levels are suffixed.
The keys are preserved using this method.

**Examples:**

```php
Map::from( ['a', 'b'] )->suffix( '-1' );
// ['a-1', 'b-1']

Map::from( ['a', ['b']] )->suffix( '-1' );
// ['a-1', ['b-1']]

Map::from( ['a', ['b']] )->suffix( '-1', 1 );
// ['a-1', ['b']]

Map::from( ['a', 'b'] )->suffix( function( $item, $key ) {
    return '-' . ( ord( $item ) + ord( $key ) );
} );
// ['a-145', 'b-147']
```


### sum()

Returns the sum of all integer and float values in the map.

```php
public function sum( string $col = null ) : float
```

* @param **string&#124;null** `$col` Key in the nested array or object to sum up
* @return **float** Sum of all elements or 0 if there are no elements in the map

This does also work to map values from multi-dimensional arrays by passing the keys
of the arrays separated by the delimiter ("/" by default), e.g. `key1/key2/key3`
to get `val` from `['key1' => ['key2' => ['key3' => 'val']]]`. The same applies to
public properties of objects or objects implementing `__isset()` and `__get()` methods.

**Examples:**

```php
Map::from( [1, 3, 5] )->sum();
// 9

Map::from( [1, 'sum', 5] )->sum();
// 6

Map::from( [['p' => 30], ['p' => 50], ['p' => 10]] )->sum( 'p' );
// 90

Map::from( [['i' => ['p' => 30]], ['i' => ['p' => 50]]] )->sum( 'i/p' );
// 80
```


### take()

Returns a new map with the given number of items.

```php
public function take( int $size, $offset = 0 ) : self
```

* @param **int** `$size` Number of items to return
* @param **\Closure&#124;int** `$offset` Number of items to skip or function($item, $key) returning true for skipped items
* @return **self&#60;int&#124;string,mixed&#62;** New map

The keys of the items returned in the new map are the same as in the original one.

**Examples:**

```php
Map::from( [1, 2, 3, 4] )->take( 2 );
// [0 => 1, 1 => 2]

Map::from( [1, 2, 3, 4] )->take( 2, 1 );
// [1 => 2, 2 => 3]

Map::from( [1, 2, 3, 4] )->take( 2, -2 );
// [2 => 3, 3 => 4]

Map::from( [1, 2, 3, 4] )->take( 2, function( $item, $key ) {
    return $item < 2;
} );
// [1 => 2, 2 => 3]
```


### tap()

Passes a clone of the map to the given callback.

```php
public function tap( callable $callback ) : self
```

* @param **callable** `$callback` Function receiving ($map) parameter
* @return **self&#60;int&#124;string,mixed&#62;** Same map for fluid interface

Use it to "tap" into a chain of methods to check the state between two
method calls. The original map is not altered by anything done in the
callback.

**Examples:**

```php
Map::from( [3, 2, 1] )->rsort()->tap( function( $map ) {
    print_r( $map->remove( 0 )->toArray() );
} )->first();
// 1
```

It will sort the list in reverse order(`[1, 2, 3]`), then prints the items (`[2, 3]`)
without the first one in the function passed to `tap()` and returns the first item
("1") at the end.


### times()

Creates a new map by invoking the closure the given number of times.

```php
public static function times( int $num, \Closure $callback ) : self
```

* @param **int** `$num` Number of times the function is called
* @param **\Closure** `$callback` Function with (value, key) parameters and returns new value
* @return **self&#60;int&#124;string,mixed&#62;** New map with the generated elements

This method creates a lazy Map and the entries are generated after calling
another method that operates on the Map contents. Thus, the passed callback
is not called immediately!

**Examples:**

```php
Map::times( 3, function( $num ) {
    return $num * 10;
} );
// [0 => 0, 1 => 10, 2 => 20]

Map::times( 3, function( $num, &$key ) {
    $key = $num * 2;
    return $num * 5;
} );
// [0 => 0, 2 => 5, 4 => 10]

Map::times( 2, function( $num ) {
    return new \stdClass();
} );
// [0 => new \stdClass(), 1 => new \stdClass()]
```


### toArray()

Returns the elements as a plain array.

```php
public function toArray() : array
```

* @return **array** Plain array

**Examples:**

```php
Map::from( ['a'] )->toArray();
// ['a']
```


### toJson()

Returns the elements encoded as JSON string.

```php
public function toJson( int $options = 0 ) : ?string
```

* @param **int** `$options` Combination of JSON_* constants
* @return **string&#124;null** Array encoded as JSON string or NULL on failure

There are several options available to modify the JSON string which are described in
the [PHP json_encode() manual](https://www.php.net/manual/en/function.json-encode.php).
The parameter can be a single JSON_* constant or a bitmask of several constants
combine by bitwise OR (&#124;), e.g.:

```php
JSON_FORCE_OBJECT|JSON_HEX_QUOT
```

**Examples:**

```php
Map::from( ['a', 'b'] )->toJson();
// '["a","b"]'

Map::from( ['a' => 'b'] )->toJson();
// '{"a":"b"}'

Map::from( ['a', 'b'] )->toJson( JSON_FORCE_OBJECT );
// '{"0":"a", "1":"b"}'
```


### toUrl()

Creates a HTTP query string from the map elements.

```php
public function toUrl() : string
```

* @return **string** Parameter string for GET requests

**Examples:**

```php
Map::from( ['a' => 1, 'b' => 2] )->toUrl();
// a=1&b=2

Map::from( ['a' => ['b' => 'abc', 'c' => 'def'], 'd' => 123] )->toUrl();
// a%5Bb%5D=abc&a%5Bc%5D=def&d=123
```


### transpose()

Exchanges rows and columns for a two dimensional map.

```php
public function transpose() : self
```

* @return **self&#60;int&#124;string,mixed&#62;** New map

**Examples:**

```php
Map::from( [
  ['name' => 'A', 2020 => 200, 2021 => 100, 2022 => 50],
  ['name' => 'B', 2020 => 300, 2021 => 200, 2022 => 100],
  ['name' => 'C', 2020 => 400, 2021 => 300, 2022 => 200],
] )->transpose();
/*
[
  'name' => ['A', 'B', 'C'],
  2020 => [200, 300, 400],
  2021 => [100, 200, 300],
  2022 => [50, 100, 200]
]
*/

Map::from( [
  ['name' => 'A', 2020 => 200, 2021 => 100, 2022 => 50],
  ['name' => 'B', 2020 => 300, 2021 => 200],
  ['name' => 'C', 2020 => 400]
] );
/*
[
  'name' => ['A', 'B', 'C'],
  2020 => [200, 300, 400],
  2021 => [100, 200],
  2022 => [50]
]
*/
```


### traverse()

Traverses trees of nested items passing each item to the callback.

```php
public function traverse( \Closure $callback = null, string $nestKey = 'children' ) : self
```

* @param **\Closure&#124;null** `$callback` Callback with (entry, key, level) arguments, returns the entry added to result
* @param **string** `$nestKey` Key to the children of each item
* @return **self&#60;int&#124;string,mixed&#62;** New map with all items as flat list

This does work for nested arrays and objects with public properties or
objects implementing `__isset()` and `__get()` methods. To build trees
of nested items, use the [tree()](#tree) method.

**Examples:**

```php
Map::from( [[
  'id' => 1, 'pid' => null, 'name' => 'n1', 'children' => [
    ['id' => 2, 'pid' => 1, 'name' => 'n2', 'children' => []],
    ['id' => 3, 'pid' => 1, 'name' => 'n3', 'children' => []]
  ]
]] )->traverse();
/*
[
  ['id' => 1, 'pid' => null, 'name' => 'n1', 'children' => [...]],
  ['id' => 2, 'pid' => 1, 'name' => 'n2', 'children' => []],
  ['id' => 3, 'pid' => 1, 'name' => 'n3', 'children' => []],
]
*/

Map::from( [[
  'id' => 1, 'pid' => null, 'name' => 'n1', 'children' => [
    ['id' => 2, 'pid' => 1, 'name' => 'n2', 'children' => []],
    ['id' => 3, 'pid' => 1, 'name' => 'n3', 'children' => []]
  ]
]] )->traverse( function( $entry, $key, $level ) {
  return str_repeat( '-', $level ) . '- ' . $entry['name'];
} );
// ['- n1', '-- n2', '-- n3']

Map::from( [[
  'id' => 1, 'pid' => null, 'name' => 'n1', 'nodes' => [
    ['id' => 2, 'pid' => 1, 'name' => 'n2', 'nodes' => []]
  ]
]] )->traverse( null, 'nodes' );
/*
[
  ['id' => 1, 'pid' => null, 'name' => 'n1', 'nodes' => [...]],
  ['id' => 2, 'pid' => 1, 'name' => 'n2', 'nodes' => []],
]
*/
```


### tree()

Creates a tree structure from the list items.

```php
public function tree( string $idKey, string $parentKey, string $nestKey = 'children' ) : self
```

* @param **string** `$idKey` Name of the key with the unique ID of the node
* @param **string** `$parentKey` Name of the key with the ID of the parent node
* @param **string** `$nestKey` Name of the key with will contain the children of the node
* @return **self&#60;int&#124;string,mixed&#62;** New map with one or more root tree nodes

Use this method to rebuild trees e.g. from database records. To traverse
trees, use the [traverse()](#traverse) method.

**Examples:**

```php
Map::from( [
  ['id' => 1, 'pid' => null, 'lvl' => 0, 'name' => 'n1'],
  ['id' => 2, 'pid' => 1, 'lvl' => 1, 'name' => 'n2'],
  ['id' => 3, 'pid' => 2, 'lvl' => 2, 'name' => 'n3'],
  ['id' => 4, 'pid' => 1, 'lvl' => 1, 'name' => 'n4'],
  ['id' => 5, 'pid' => 3, 'lvl' => 2, 'name' => 'n5'],
  ['id' => 6, 'pid' => 1, 'lvl' => 1, 'name' => 'n6'],
] )->tree( 'id', 'pid' );
/*
[1 => [
  'id' => 1, 'pid' => null, 'lvl' => 0, 'name' => 'n1', 'children' => [
    2 => ['id' => 2, 'pid' => 1, 'lvl' => 1, 'name' => 'n2', 'children' => [
      3 => ['id' => 3, 'pid' => 2, 'lvl' => 2, 'name' => 'n3', 'children' => []]
    ]],
    4 => ['id' => 4, 'pid' => 1, 'lvl' => 1, 'name' => 'n4', 'children' => [
      5 => ['id' => 5, 'pid' => 3, 'lvl' => 2, 'name' => 'n5', 'children' => []]
    ]],
    6 => ['id' => 6, 'pid' => 1, 'lvl' => 1, 'name' => 'n6', 'children' => []]
  ]
]]
*/
```

To build the tree correctly, the items must be in order or at least the
nodes of the lower levels must come first. For a tree like this:

```
n1
|- n2
|  |- n3
|- n4
|  |- n5
|- n6
```

Accepted item order:
- in order: n1, n2, n3, n4, n5, n6
- lower levels first: n1, n2, n4, n6, n3, n5

If your items are unordered, apply [usort()](#usort) first to the map entries, e.g.

```php
Map::from( [['id' => 3, 'lvl' => 2], ...] )->usort( function( $item1, $item2 ) {
  return $item1['lvl'] <=> $item2['lvl'];
} );
```


### trim()

Removes the passed characters from the left/right of all strings.

```php
public function trim( string $chars = " \n\r\t\v\x00" ) : self
```

* @param **string** `$chars` List of characters to trim
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

**Examples:**

```php
Map::from( [" abc\n", "\tcde\r\n"] )->trim();
// ["abc", "cde"]

Map::from( ["a b c", "cbax"] )->trim( 'abc' );
// [" b ", "x"]
```


### uasort()

Sorts all elements using a callback and maintains the key association.

```php
public function uasort( callable $callback ) : self
```

* @param **callable** `$callback` Function with (itemA, itemB) parameters and returns -1 (<), 0 (=) and 1 (>)
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

The given callback will be used to compare the values. The callback must accept
two parameters (item A and B) and must return -1 if item A is smaller than
item B, 0 if both are equal and 1 if item A is greater than item B. Both, a
method name and an anonymous function can be passed.

The keys are preserved using this method and no new map is created.

**Examples:**

```php
Map::from( ['a' => 'B', 'b' => 'a'] )->uasort( 'strcasecmp' );
// ['b' => 'a', 'a' => 'B']

Map::from( ['a' => 'B', 'b' => 'a'] )->uasort( function( $itemA, $itemB ) {
    return strtolower( $itemA ) <=> strtolower( $itemB );
} );
// ['b' => 'a', 'a' => 'B']
```


## uksort()

Sorts the map elements by their keys using a callback.

```php
public function uksort( callable $callback ) : self
```

* @param **callable** `$callback` Function with (keyA, keyB) parameters and returns -1 (<), 0 (=) and 1 (>)
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

The given callback will be used to compare the keys. The callback must accept
two parameters (key A and B) and must return -1 if key A is smaller than
key B, 0 if both are equal and 1 if key A is greater than key B. Both, a
method name and an anonymous function can be passed.

The keys are preserved using this method and no new map is created.

**Examples:**

```php
Map::from( ['B' => 'a', 'a' => 'b'] )->uksort( 'strcasecmp' );
// ['a' => 'b', 'B' => 'a']

Map::from( ['B' => 'a', 'a' => 'b'] )->uksort( function( $keyA, $keyB ) {
    return strtolower( $keyA ) <=> strtolower( $keyB );
} );
// ['a' => 'b', 'B' => 'a']
```


### union()

Builds a union of the elements and the given elements without returning a new map.
Existing keys in the map will not be overwritten

```php
public function union( iterable $elements ) : self
```

* @param **iterable&#60;int&#124;string,mixed&#62;** `$elements` List of elements
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

If list entries should be overwritten, use [merge()](#merge) instead.
The keys are preserved using this method and no new map is created.

**Examples:**

```php
Map::from( [0 => 'a', 1 => 'b'] )->union( [0 => 'c'] );
// [0 => 'a', 1 => 'b'] because the key 0 isn't overwritten

Map::from( ['a' => 1, 'b' => 2] )->union( ['c' => 1] );
// ['a' => 1, 'b' => 2, 'c' => 1]
```


### unique()

Returns only unique elements from the map in a new map

```php
public function unique( string $key = null ) : self
```

* @param **string&#124;null** `$key` Key or path of the nested array or object to check for
* @return **self&#60;int&#124;string,mixed&#62;** New map

Two elements are considered equal if comparing their string representions returns TRUE:

```php
(string) $elem1 === (string) $elem2
```

The keys of the elements are only preserved in the new map if no key is passed.

**Examples:**

```php
Map::from( [0 => 'a', 1 => 'b', 2 => 'b', 3 => 'c'] )->unique();
// [0 => 'a', 1 => 'b', 3 => 'c']

Map::from( [['p' => '1'], ['p' => 1], ['p' => 2]] )->unique( 'p' );
// [['p' => 1], ['p' => 2]]

Map::from( [['i' => ['p' => '1']], ['i' => ['p' => 1]]] )->unique( 'i/p' );
// [['i' => ['p' => '1']]]
```


### unshift()

Pushes an element onto the beginning of the map without returning a new map.

```php
public function unshift( $value, $key = null ) : self
```

* @param **mixed** `$value` Item to add at the beginning
* @param **int&#124;string&#124;null** `$key` Key for the item or NULL to reindex all numerical keys
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

The keys of the elements are only preserved in the new map if no key is passed.

**Examples:**

```php
Map::from( ['a', 'b'] )->unshift( 'd' );
// ['d', 'a', 'b']

Map::from( ['a', 'b'] )->unshift( 'd', 'first' );
// ['first' => 'd', 0 => 'a', 1 => 'b']
```

**Performance note:**

The bigger the list, the higher the performance impact because `unshift()`
needs to create a new list and copies all existing elements to the new
array. Usually, it's better to [push()](#push) new entries at the end and
[reverse()](#reverse) the list afterwards:

```php
$map->push( 'a' )->push( 'b' )->reverse();
```

instead of

```php
$map->unshift( 'a' )->unshift( 'b' );
```


### usort()

Sorts all elements using a callback without maintaining the key association.

```php
public function usort( callable $callback ) : self
```

* @param **callable** `$callback` Function with (itemA, itemB) parameters and returns -1 (<), 0 (=) and 1 (>)
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

The given callback will be used to compare the values. The callback must accept
two parameters (item A and B) and must return -1 if item A is smaller than
item B, 0 if both are equal and 1 if item A is greater than item B. Both, a
method name and an anonymous function can be passed.

The keys are NOT preserved and elements get a new index. No new map is created.

**Examples:**

```php
Map::from( ['a' => 'B', 'b' => 'a'] )->usort( 'strcasecmp' );
// [0 => 'a', 1 => 'B']

Map::from( ['a' => 'B', 'b' => 'a'] )->usort( function( $itemA, $itemB ) {
    return strtolower( $itemA ) <=> strtolower( $itemB );
} );
// [0 => 'a', 1 => 'B']
```


### values()

Resets the keys and return the values in a new map.

```php
public function values() : self
```

* @return **self&#60;int&#124;string,mixed&#62;** New map of the values

**Examples:**

```php
Map::from( ['x' => 'b', 2 => 'a', 'c'] )->values();
// [0 => 'b', 1 => 'a', 2 => 'c']
```


### walk()

Applies the given callback to all elements.

```php
public function walk( callable $callback, $data = null, bool $recursive = true ) : self
```

* @param **callable** `$callback` Function with (item, key, data) parameters
* @param **mixed** `$data` Arbitrary data that will be passed to the callback as third parameter
* @param **bool** `$recursive` TRUE to traverse sub-arrays recursively (default), FALSE to iterate Map elements only
* @return **self&#60;int&#124;string,mixed&#62;** Updated map for fluid interface

To change the values of the Map, specify the value parameter as reference
(`&$value`). You can only change the values but not the keys nor the array
structure.

By default, Map elements which are arrays will be traversed recursively.
To iterate over the Map elements only, pass FALSE as third parameter.

**Examples:**

```php
Map::from( ['a', 'B', ['c', 'd'], 'e'] )->walk( function( &$value ) {
    $value = strtoupper( $value );
} );
// ['A', 'B', ['C', 'D'], 'E']

Map::from( [66 => 'B', 97 => 'a'] )->walk( function( $value, $key ) {
    echo 'ASCII ' . $key . ' is ' . $value . "\n";
} );
/*
ASCII 66 is B
ASCII 97 is a
*/

Map::from( [1, 2, 3] )->walk( function( &$value, $key, $data ) {
    $value = $data[$value] ?? $value;
}, [1 => 'one', 2 => 'two'] );
// ['one', 'two', 3]
```


### where()

Filters the list of elements by a given condition.

```php
public function where( string $key, string $op, $value ) : self
```

* @param **string** `$key` Key or path of the value of the array or object used for comparison
* @param **string** `$op` Operator used for comparison
* @param **mixed** `$value` Value used for comparison
* @return **self&#60;int&#124;string,mixed&#62;** New map for fluid interface

Available operators are:

* '==' : Equal
* '===' : Equal and same type
* '!=' : Not equal
* '!==' : Not equal and same type
* '<=' : Smaller than an equal
* '>=' : Greater than an equal
* '<' : Smaller
* '>' : Greater
* 'in' : Array of value which are in the list of values
* '-' : Values between array of start and end value, e.g. [10, 100] (inclusive)

The keys of the original map are preserved in the returned map.

**Examples:**

```php
Map::from( [
  ['id' => 1, 'type' => 'name'],
  ['id' => 2, 'type' => 'short'],
] )->where( 'type', '==', 'name' );
/*
[
    ['id' => 1, 'type' => 'name']
]
*/

Map::from( [
  ['id' => 3, 'price' => 10],
  ['id' => 4, 'price' => 50],
] )->where( 'price', '>', 20 );
/*
[
    ['id' => 4, 'price' => 50]
]
*/

Map::from( [
  ['id' => 3, 'price' => 10],
  ['id' => 4, 'price' => 50],
] )->where( 'price', 'in', [10, 25] );
/*
[
    ['id' => 3, 'price' => 10]
]
*/

Map::from( [
  ['id' => 3, 'price' => 10],
  ['id' => 4, 'price' => 50],
] )->where( 'price', '-', [10, 100] );
/*
[
    ['id' => 3, 'price' => 10],
    ['id' => 4, 'price' => 50]
]
*/

Map::from( [
  ['item' => ['id' => 3, 'price' => 10]],
  ['item' => ['id' => 4, 'price' => 50]],
] )->where( 'item/price', '>', 30 );
/*
[
    ['id' => 4, 'price' => 50]
]
*/
```


### zip()

Merges the values of all arrays at the corresponding index.

```php
public function zip( $array1, ... ) : self
```

* @param **array&#60;int&#124;string,mixed&#62;&#124;\Traversable&#60;int&#124;string,mixed&#62;&#124;\Iterator&#60;int&#124;string,mixed&#62;** `$array1` List of arrays to merge with at the same position
* @return **self&#60;int&#124;string,mixed&#62;** New map of arrays

**Examples:**

```php
Map::from( [1, 2, 3] )->zip( ['one', 'two', 'three'], ['uno', 'dos', 'tres'] );
/*
[
    [1, 'one', 'uno'],
    [2, 'two', 'dos'],
    [3, 'three', 'tres'],
]
*/
```



## Custom methods

Most of the time, it's enough to pass an anonymous function to the [pipe()](#pipe) method
to implement custom functionality in map objects:

```php
Map::from( ['a', 'b'] )->pipe( function( $map ) {
    return strrev( $map->join( '-' ) );
} );
```

If you need some functionality more often and at different places in your source
code, than it's better to register a custom method once and only call it everywhere:

```php
Map::method( 'strrev', function( $sep ) {
    return strrev( join( '-', $this->list() ) );
} );
Map::from( ['a', 'b'] )->strrev( '-' );
```

Make sure, you register the method before using it. You can pass arbitrary parameters
to your function and it has access to the internas of the map. Thus, your function
can use `$this` to call all available methods:

```php
Map::method( 'notInBoth', function( iterable $elements ) {
    return new self( $this->diff( $elements ) + Map::from( $elements )->diff( $this->items ) );
} );
```

Your custom method has access to `$this->items` array which contains the map
elements and can also use the internal `$this->getArray( iterable $list )` method to
convert iterable parameters (arrays, generators and objects implementing \Traversable)
to plain arrays:

```php
Map::method( 'mycombine', function( iterable $keys ) {
    return new self( array_combine( $this->getArray( $keys ), $this-items ) );
} );
```



## Performance

The performance most methods only depends on the array_* function that are used
internally by the Map class. If the methods of the Map class contains additional
code, it's optimized to be as fast as possible.

### Creating Map vs. array

Creating an map object with an array instead of creating a plain array only is
significantly slower (ca. 10x) but in absolute values we are talking about nano
seconds. It will only get notable if you create 10,000 map objects instead of
10,000 arrays. Then, creating maps will last ca. 10ms longer.

Usually, this isn't much of a problem because applications create arrays with
lots of elements instead of 10,000+ arrays. Nevertheless, if your application
creates a very large number of arrays within one area, you should think about
avoiding map objects in that area.

If you use the [map()](#map-function) function or [Map::from()](#from) to create
map objects, then be aware that this adds another function call. Using these methods
for creating the map object lasts around 1.1x resp. 1.3x compared to the time for
`new Map()`.

Conclusion: Using `new Map()` is fastest and `map()` is faster than `Map::from()`.

### Populating Map vs. array

Adding an element to a Map object using `$map[] = 'a'` is ca. 5x slower than
doing the same on a plain array. This is because the method [offsetSet()](#offsetSet) will
be called instead of adding the new element to the array directly. This applies
to the `$map->push( 'a' )` method too.

When creating arrays in loops, you should populate the array first and then
create a Map object from the the array:

```php
$list = [];
for( $i = 0; $i < 1000; $i++ ) {
	$list[] = $i;
}
$map = map( $list );
```

The array is **NOT** copied when creating the Map object so there's virtually no
performance loss using the Map afterwards.

### Using Map methods vs. language constructs

Language constructs such as `empty()`, `count()` or `isset()` are faster than
calling a method and using `$map->isEmpty()` or `$map->count()` is ca. 4x
slower.

Again, we are talking about nano seconds. For 10,000 calls to `empty( $array )`
compared to `$map->isEmpty()`, the costs are around 4ms in total.

### Using Map methods vs. array_* functions

Using the Map methods instead of the array_* functions adds an additional method
call. Internally, the Map objects uses the same array_* functions but offers a
much more usable interface.

The time for the additional method call is almost neglectable because the array_*
methods needs much longer to perform the operation on the array elements depending
on the size of the array.

### Using anonymous functions

Several Map methods support passing an anonymous function that is applied to
every element of the map. PHP needs some time to call the passed function and
to execute its code. Depending on the number of elements, this may have a
significant impact on performance!

The [pipe()](#pipe) method of the Map object is an exception because it receives the
whole map object instead of each element separately. Its performance mainly
depends on the implemented code:

```php
$map->pipe( function( Map $map ) {
	// perform operations on the map
} );
```

### Using shift() and unshift()

Both methods are costly, especially on large arrays. The used `array_shift()` and
`array_unshift()` functions will reindex all numerical keys of the array.

If you want to reduce or create a large list of elements from the beginning in
an iterative way, you should use [reverse()](#reverse) and [pop()](#pop)/[push()](#push)
instead of [shift()](#shift) and [unshift()](#unshift)/[prepend()](#prepend):

```php
$map->reverse()->pop(); // use pop() until it returns NULL
$map->push( 'z' )->push( 'y' )->push( 'x' )->reverse(); // use push() for adding
```


## Upgrade guide

### 2.x -> 3.x

#### Use list() method

When adding own methods to the Map object, don't access the `$this->list` class
variable directly. It's not guaranteed to be an array any more but will store
the value passed to the Map constructor. Instead, use the `list() method to get
a reference to the array of elements:

```php
$this->list();
```

As it's a reference to the array of elements, you can modify it directly or even
use PHP functions that require a variable reference:

```php
$this->list()[] = 123;
reset( $this->list() );
```

#### Renamed internal methods

Two internal methods have been renamed and you have to use their new name if you
have added own methods to the Map object:

```php
// instead of $this->getArray( $array )
$this->array( $array )

 // instead of $this->getValue( $entry, array $parts )
$this->val( $entry, array $parts )
```

### 1.x -> 2.x

#### jQuery style method calls

You can call methods of objects in a map like this:

```php
// MyClass implements setStatus() (returning $this) and getCode() (initialized by constructor)

$map = Map::from( ['a' => new MyClass( 'x' ), 'b' => new MyClass( 'y' )] );
$map->setStatus( 1 )->getCode()->toArray();
```

Before, it was checked if the objects really implement `setStatus()` and `getCode()`.

This isn't the case any more to avoid returning an empty map if the method name is
wrong or the called method is implemented using the `__call()` magic method. Now, PHP
generates a fatal error if the method isn't implemented by all objects.

#### Second equals() parameter

The second parameter of the [equals()](#equals) method (`$assoc`) to compare keys
too has been removed. Use the [is()](#is) method instead:

```php
// 1.x
map( ['one' => 1] )->equals( ['one' => 1], true );

// 2.x
map( ['one' => 1] )->is( ['one' => 1] );
```

#### New find() argument

A default value or exception object can be passed to the [find()](#find) method now
as second argument. The `$reverse` argument has been moved to the third position.

```php
// 1.x
Map::from( ['a', 'c', 'e'] )->find( function( $value, $key ) {
    return $value >= 'b';
}, true );

// 2.x
Map::from( ['a', 'c', 'e'] )->find( function( $value, $key ) {
    return $value >= 'b';
}, null, true );
```

#### groupBy() semantic change

If the key passed to [groupBy()](#groupby) didn't exist, the items have been grouped
using the given key. Now, an empty string is used as key to offer easier checking and
sorting of the keys.

```php
Map::from( [
    10 => ['aid' => 123, 'code' => 'x-abc'],
] )->groupBy( 'xid' );

// 1.x
[
    'xid' => [
        10 => ['aid' => 123, 'code' => 'x-abc']
    ]
]

// 2.x
[
    '' => [
        10 => ['aid' => 123, 'code' => 'x-abc']
    ]
]
```

#### offsetExists() semantic change

To be consistent with typical PHP behavior, the [offsetExists()](#offsetexists) method
use `isset()` instead of `array_key_exists()` now. This changes the behavior when dealing
with NULL values.

```php
$m = Map::from( ['foo' => null] );

// 1.x
isset( $m['foo'] ); // true

// 2.x
isset( $m['foo'] ); // false
```

#### Renamed split() method

The static `Map::split()` method has been renamed to [Map::explode()](#explode) and
the argument order has changed. This avoids conflicts with the Laravel split() method
and is in line with the PHP `explode()` method.

```php
// 1.x
Map::split( 'a,b,c', ',' );

// 2.x
Map::explode( ',', 'a,b,c' );
```
