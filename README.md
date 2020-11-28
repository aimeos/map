[![Build Status](https://circleci.com/gh/aimeos/map.svg?style=shield)](https://circleci.com/gh/aimeos/map)
[![Coverage Status](https://coveralls.io/repos/github/aimeos/map/badge.svg)](https://coveralls.io/github/aimeos/map)
[![License](https://poser.pugx.org/aimeos/map/license.svg)](https://packagist.org/packages/aimeos/map)
[![Latest Stable Version](https://poser.pugx.org/aimeos/map/v/stable)](https://packagist.org/packages/aimeos/map)
[![Total Downloads](https://poser.pugx.org/aimeos/map/downloads)](https://packagist.org/packages/aimeos/map)

# Working with PHP arrays easily

Easy to use and elegant handling for PHP arrays with an array-like map object
as offered by jQuery and Laravel Collections.

```bash
composer req aimeos/map
```

**Table of contents**

* [Why](#why)
* [List of methods](#methods)
    * [Create](#create)
    * [Access](#access)
    * [Add](#add)
    * [Aggregate](#aggregate)
    * [Debug](#debug)
    * [Order](#order)
    * [Shorten](#shorten)
    * [Test](#test)
    * [Transform](#transform)
    * [Misc](#misc)
* [Documentation](#method-documentation)
* [Custom methods](#custom-methods)
* [Performance](#performance)

## Why

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
// MyClass implements setId() (returning $this) and getCode() (initialized by constructor)

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

### Create

* [function map()](#map-function) : Creates a new map from passed elements
* [__construct()](#__construct) : Creates a new map
* [copy()](#copy) : Creates a new copy
* [from()](#from) : Creates a new map from passed elements
* [fromJson()](#fromjson) : Creates a new map from a JSON string
* [split()](#split) : Splits a string into map elements

### Access

* [__call()](#__call) : Calls a custom method
* [__callStatic()](#__callstatic) : Calls a custom method statically
* [call()](#call) : Calls the given method on all items
* [find()](#find) : Returns the first/last matching element
* [first()](#first) : Returns the first element
* [firstKey()](#firstkey) : Returns the first key
* [get()](#get) : Returns an element by key
* [keys()](#keys) : Returns all keys
* [last()](#last) : Returns the last element
* [lastKey()](#lastkey) : Returns the last key
* [pop()](#pop) : Returns and removes the last element
* [pos()](#pos) : Returns the numerical index of the value
* [pull()](#pull) : Returns and removes an element by key
* [random()](#random) : Returns random elements preserving keys
* [search()](#search) : Find the key of an element
* [shift()](#shift) : Returns and removes the first element
* [toArray()](#toarray) : Returns the plain array
* [unique()](#unique) : Returns all unique elements preserving keys
* [values()](#values) : Returns all elements with new keys
* [where()](#where) : Filters the list of elements by a given condition

### Add

* [concat()](#concat) : Adds all elements with new keys
* [merge()](#merge) : Combines elements overwriting existing ones
* [pad()](#pad) : Fill up to the specified length with the given value
* [push()](#push) : Adds an element to the end
* [set()](#set) : Overwrites an element
* [union()](#union) : Adds the elements without overwriting existing ones
* [unshift()](#unshift) : Adds an element at the beginning

### Aggregate

* [count()](#count) : Returns the total number of elements
* [countBy()](#countby) : Counts how often the same values are in the map
* [max()](#max) : Returns the maximum value of all elements
* [min()](#max) : Returns the minium value of all elements
* [sum()](#sum) :. Returns the sum of all values in the map

### Debug

* [dump()](#dump) : Prints the map content

### Order

* [arsort()](#arsort) : Reverse sort elements preserving keys
* [asort()](#asort) : Sort elements preserving keys
* [krsort()](#krsort) : Reverse sort elements by keys
* [ksort()](#ksort) : Sort elements by keys
* [rsort()](#rsort) : Reverse sort elements using new keys
* [reverse()](#reverse) : Reverses the array order preserving keys
* [shuffle()](#shuffle) : Randomizes the element order
* [sort()](#sort) : Sorts the elements assigning new keys
* [uasort()](#uasort) : Sorts elements preserving keys using callback
* [uksort()](#uksort) : Sorts elements by keys using callback
* [usort()](#usort) : Sorts elements using callback assigning new keys

### Shorten

* [clear()](#clear) : Removes all elements
* [diff()](#diff) : Returns the elements missing in the given list
* [diffAssoc()](#diffassoc) : Returns the elements missing in the given list and checks keys
* [diffKeys()](#diffkeys) : Returns the elements missing in the given list by keys
* [except()](#except) : Returns a new map without the passed element keys
* [filter()](#filter) : Applies a filter to the all elements
* [intersect()](#intersect) : Returns the elements shared
* [intersectAssoc()](#intersectassoc) : Returns the elements shared and checks keys
* [intersectKeys()](#intersectkeys) : Returns the elements shared by keys
* [nth()](#nth) : Returns every nth element from the map
* [only()](#only) : Returns only those elements specified by the keys
* [pop()](#pop) : Returns and removes the last element
* [pull()](#pull) : Returns and removes an element by key
* [remove()](#remove) : Removes an element by key
* [shift()](#shift) : Returns and removes the first element
* [skip()](#skip) : Skips the given number of items and return the rest
* [slice()](#slice) : Returns a slice of the map
* [take()](#take) : Returns a new map with the given number of items

### Test

* [function is_map()](#is_map-function) : Tests if the variable is a map object
* [each()](#each) : Applies a callback to each element
* [empty()](#empty) : Tests if map is empty
* [equals()](#equals) : Tests if map contents are equal
* [every()](#every) : Verifies that all elements pass the test of the given callback
* [has()](#has) : Tests if a key exists
* [in()](#in) : Tests if element is included
* [includes()](#includes) : Tests if element is included
* [is()](#is) : Tests if the map consists of the same keys and values
* [isEmpty()](#isempty) : Tests if map is empty
* [some()](#some) : Tests if at least one element is included

### Transform

* [chunk()](#chunk) : Splits the map into chunks
* [combine()](#combine) : Combines the map elements as keys with the given values
* [col()](#col) : Creates a key/value mapping
* [collapse()](#collapse) : Collapses multi-dimensional elements overwriting elements
* [flip()](#flip) : Exchanges keys with their values
* [flat()](#flat) : Flattens multi-dimensional elements without overwriting elements
* [groupBy()](#groupby) : Groups associative array elements or objects
* [join()](#join) : Returns concatenated elements as string with separator
* [map()](#map) : Applies a callback to each element and returns the results
* [partition()](#partition) : Breaks the list into the given number of groups
* [pipe()](#pipe) : Applies a callback to the whole map
* [prefix()](#prefix) : Adds a prefix to each map entry
* [reduce()](#reduce) : Computes a single value from the map content
* [replace()](#replace) : Replaces elements recursively
* [splice()](#splice) : Replaces a slice by new elements
* [suffix()](#suffix) : Adds a suffix to each map entry
* [toJson()](#tojson) : Returns the elements in JSON format
* [toUrl()](#tourl) : Creates a HTTP query string
* [transpose()](#transpose) : Exchanges rows and columns for a two dimensional map
* [walk()](#walk) : Applies the given callback to all elements
* [zip()](#zip) : Merges the values of all arrays at the corresponding index

### Misc

* [getIterator()](#getiterator) : Returns an iterator for the elements
* [method()](#method) : Registers a custom method
* [offsetExists()](#offsetexists) : Checks if the key exists
* [offsetGet()](#offsetget) : Returns an element by key
* [offsetSet()](#offsetset) : Overwrites an element
* [offsetUnset()](#offsetunset) : Removes an element by key



## Method documentation

### is_map() function

Tests if the variable is a map object

```php
function is_map( $var ) : bool
```

* @param mixed `$var` Variable to test


### map() function

Returns a new map for the passed elements.

```php
function map( $elements = [] ) : \Aimeos\Map
```

* @param mixed `$elements` List of elements or single value
* @return \Aimeos\Map Map instance

**Examples:**

```php
Map::from( [] );
Map::from( null );
Map::from( 'a' );
Map::from( new Map() );
Map::from( new ArrayObject() );
```

**Results:**

A new map instance containing the list of elements. In case of an empty
array or null, the map object will contain an empty list. If a map object
is passed, it will be returned instead of creating a new instance.


### __construct()

Creates a new map.

```php
public function __construct( iterable $elements = [] )
```

* @param iterable `$elements` List of elements


### __call()

Handles dynamic calls to custom methods for the class.

Calls a custom method added by Map::method(). The called method
has access to the internal array by using `$this->items`.

```php
public function __call( string $name, array $params )
```

* @param string `$name` Method name
* @param array `$params` List of parameters
* @return mixed&#124;self Result from called function or map with results from the element methods

**Examples:**

```php
Map::method( 'case', function( $case = CASE_LOWER ) {
    return new self( array_change_key_case( $this->items, $case ) );
} );
Map::from( ['a' => 'bar'] )->case( CASE_UPPER );

$item = new MyClass(); // with method setStatus() (returning $this) and getCode() implemented
Map::from( [$item, $item] )->setStatus( 1 )->getCode()->toArray();
```

**Results:**

The first example will return `['A' => 'bar']`.

The second one will call the `setStatus()` method of each element in the map and
use their return values to create a new map. On the new map, the `getCode()`
method is called for every element and its return values are also stored in a new
map. This last map is then returned.
If the elements are not objects or don't implement the method, they are skipped.
If this applies to all elements, an empty map is returned. The map keys from the
original map are preserved in the returned map.


### __callStatic()

Handles static calls to custom methods for the class.

Calls a custom method added by Map::method() statically. The called method
has no access to the internal array because no object is available.

```php
public static function __callStatic( string $name, array $params )
```

* @param string `$name` Method name
* @param array `$params` List of parameters
* @return mixed Result from called function
* @throws \BadMethodCallException

**Examples:**

```php
Map::method( 'foo', function( $arg1, $arg2 ) {} );
Map::foo( $arg1, $arg2 );
```


### arsort()

Sorts all elements in reverse order and maintains the key association.

```php
public function arsort( int $options = SORT_REGULAR ) : self
```

* @param int `$options` Sort options for `arsort()`
* @return self Updated map for fluid interface

**Examples:**

```php
Map::from( ['b' => 0, 'a' => 1] )->arsort();
Map::from( [1 => 'a', 0 => 'b'] )->arsort();
```

**Results:**

```php
['a' => 1, 'b' => 0]
[0 => 'b', 1 => 'a']
```

The parameter modifies how the values are compared. Possible parameter values are:
- SORT_REGULAR : compare elements normally (don't change types)
- SORT_NUMERIC : compare elements numerically
- SORT_STRING : compare elements as strings
- SORT_LOCALE_STRING : compare elements as strings, based on the current locale or changed by setlocale()
- SORT_NATURAL : compare elements as strings using "natural ordering" like natsort()
- SORT_FLAG_CASE : use SORT_STRING&#124;SORT_FLAG_CASE and SORT_NATURAL&#124;SORT_FLAG_CASE to sort strings case-insensitively

The keys are preserved using this method and no new map is created.


### asort()

Sorts all elements and maintains the key association.

```php
public function asort( int $options = SORT_REGULAR ) : self
```

* @param int `$options` Sort options for `asort()`
* @return self Updated map for fluid interface

**Examples:**

```php
Map::from( ['a' => 1, 'b' => 0] )->asort();
Map::from( [0 => 'b', 1 => 'a'] )->asort();
```

**Results:**

```php
['b' => 0, 'a' => 1]
[1 => 'a', 0 => 'b']
```

The parameter modifies how the values are compared. Possible parameter values are:
- SORT_REGULAR : compare elements normally (don't change types)
- SORT_NUMERIC : compare elements numerically
- SORT_STRING : compare elements as strings
- SORT_LOCALE_STRING : compare elements as strings, based on the current locale or changed by setlocale()
- SORT_NATURAL : compare elements as strings using "natural ordering" like natsort()
- SORT_FLAG_CASE : use SORT_STRING&#124;SORT_FLAG_CASE and SORT_NATURAL&#124;SORT_FLAG_CASE to sort strings case-insensitively

The keys are preserved using this method and no new map is created.


### call()

Calls the given method on all items and returns the result.

```php
public function call( string $name, array $params = [] ) : self
```

* @param string $name Method name
* @param array $params List of parameters
* @return self Map with results from all elements

**Examples:**

```
$item = new MyClass(); // implements methods get() and toArray()
Map::from( [$item, $item] )->call( 'get', ['myprop'] );
Map::from( [$item, $item] )->call( 'toArray' );
```

**Results:**

The first example will return `['...', '...']` while the second one returns `[[...], [...]]`.

This method can call methods on the map entries that are also implemented
by the map object itself and are therefore not reachable when using the
magic __call() method.

If some entries are not objects, they will be skipped. The keys from the
original map are preserved in the returned in the new map.


### chunk()

Chunks the map into arrays with the given number of elements.

```php
public function chunk( int $size, bool $preserve = false ) : self
```

* @param int `$size` Maximum size of the sub-arrays
* @param bool `$preserve` Preserve keys in new map
* @return self New map with elements chunked in sub-arrays
* @throws \InvalidArgumentException If size is smaller than 1

**Examples:**

```php
Map::from( [0, 1, 2, 3, 4] )->chunk( 3 );
Map::from( ['a' => 0, 'b' => 1, 'c' => 2] )->chunk( 2 );
```

**Results:**

```php
[[0, 1, 2], [3, 4]]
[['a' => 0, 'b' => 1], ['c' => 2]]
```

The last chunk may contain less elements than the given number.

The sub-arrays of the returned map are plain PHP arrays. If you need Map
objects, then wrap them with `Map::from()` when you iterate over the map.


### clear()

Removes all elements from the current map.

```php
public function clear() : self
```

* @return self Same map for fluid interface


### col()

Returns the values of a single column/property from an array of arrays or list of elements in a new map.

```php
public function col( string $valuecol = null, string $indexcol = null ) : self
```

* @param string&#124;null `$valuecol` Name of the value property
* @param string&#124;null `$indexcol` Name of the index property
* @return self New instance with mapped entries

**Examples:**

```php
Map::from( [['id' => 'i1', 'val' => 'v1'], ['id' => 'i2', 'val' => 'v2']] )->col( 'val' );
Map::from( [['id' => 'i1', 'val' => 'v1'], ['id' => 'i2', 'val' => 'v2']] )->col( 'val', 'id' );
Map::from( [['id' => 'i1', 'val' => 'v1'], ['id' => 'i2', 'val' => 'v2']] )->col( null, 'id' );
```

**Results:**

```php
['v1', 'v2']
['i1' => 'v1', 'i2' => 'v2']
['i1' => ['id' => 'i1', 'val' => 'v1'], 'i2' => ['id' => 'i2', 'val' => 'v2']]
```

If `$indexcol` is omitted, the result will be indexed from 0-n.
The col() method works for objects implementing the __isset() and __get() methods too.


### collapse()

Collapses all sub-array elements recursively to a new map.

```php
public function collapse( int $depth = null ) : self
```

* @param int&#124;null `$depth` Number of levels to collapse for multi-dimensional arrays or NULL for all
* @return self New map with all sub-array elements added into it recursively, up to the specified depth

**Examples:**

```php
Map::from( [0 => ['a' => 0, 'b' => 1], 1 => ['c' => 2, 'd' => 3]] )->collapse();
Map::from( [0 => ['a' => 0, 'b' => 1], 1 => ['a' => 2]] )->collapse();
Map::from( [0 => [0 => 0, 1 => 1], 1 => [0 => ['a' => 2, 0 => 3], 1 => 4]] )->collapse();
Map::from( [0 => [0 => 0, 'a' => 1], 1 => [0 => ['b' => 2, 0 => 3], 1 => 4]] )->collapse( 1 );
Map::from( [0 => [0 => 0, 'a' => 1], 1 => Map::from( [0 => ['b' => 2, 0 => 3], 1 => 4] )] )->collapse();
```

**Results:**

```php
['a' => 0, 'b' => 1, 'c' => 2, 'd' => 3]
['a' => 2, 'b' => 1]
[0 => 3, 1 => 4, 'a' => 2]
[0 => ['b' => 2, 0 => 3], 1 => 4, 'a' => 1]
[0 => 3, 'a' => 1, 'b' => 2, 1 => 4]
```

The keys are preserved and already existing elements will be overwritten. This
is also true for numeric keys!

A value smaller than 1 for depth will return the same map elements. Collapsing
does also work if elements implement the "Traversable" interface (which the Map
object does).

This method is similar than flat() but replaces already existing elements.


### concat()

Pushs all of the given elements onto the map without creating a new map.

```php
public function concat( iterable $elements ) : self
```

* @param iterable `$elements` List of elements
* @return self Updated map for fluid interface

**Examples:**

```php
Map::from( ['foo'] )->concat( new Map( ['bar'] ));
```

**Results:**

```php
['foo', 'bar']
```


### combine()

Combines the values of the map as keys with the passed elements as values.

```php
public function combine( iterable $values ) : self
```

* @param iterable `$values` Values of the new map
* @return self New map

**Examples:**

```php
Map::from( ['name', 'age'] )->combine( ['Tom', 29] );
```

**Results:**

```php
['name' => 'Tom', 'age' => 29]
```


### copy()

Creates a new map with the same elements.

Both maps share the same array until one of the map objects modifies the
array. Then, the array is copied and the copy is modfied (copy on write).

```php
public function copy() : self
```

* @return self New map


### count()

Counts the number of elements in the map.

```php
public function count() : int
```

* @return int Number of elements


### countBy()

Counts how often the same values are in the map.

```php
public function countBy( callable $callback = null ) : self
```

* @param  callable&#124;null `$callback` Function with (value, key) parameters which returns the value to use for counting
* @return self New map with values as keys and their count as value

**Examples:**

```php
Map::from( [1, 'foo', 2, 'foo', 1] )->countBy();
Map::from( [1.11, 3.33, 3.33, 9.99] )->countBy();
Map::from( ['a@gmail.com', 'b@yahoo.com', 'c@gmail.com'] )->countBy( function( $email ) {
    return substr( strrchr( $email, '@' ), 1 );
} );
```

**Results:**

```php
[1 => 2, 'foo' => 2, 2 => 1]
['1.11' => 1, '3.33' => 2, '9.99' => 1]
['gmail.com' => 2, 'yahoo.com' => 1]
```


### diff()

Returns the keys/values in the map whose values are not present in the passed elements in a new map.

```php
public function diff( iterable $elements, callable $callback = null ) : self
```

* @param iterable `$elements` List of elements
* @param  callable&#124;null `$callback` Function with (valueA, valueB) parameters and returns -1 (<), 0 (=) and 1 (>)
* @return self New map

**Examples:**

```php
Map::from( ['a' => 'foo', 'b' => 'bar'] )->diff( ['bar'] );
```

**Results:**

```php
['a' => 'foo']
```

If a callback is passed, the given function will be used to compare the values.
The function must accept two parameters (value A and B) and must return
-1 if value A is smaller than value B, 0 if both are equal and 1 if value A is
greater than value B. Both, a method name and an anonymous function can be passed:

```php
Map::from( [0 => 'a'] )->diff( [0 => 'A'], 'strcasecmp' );
Map::from( ['b' => 'a'] )->diff( ['B' => 'A'], 'strcasecmp' );
Map::from( ['b' => 'a'] )->diff( ['c' => 'A'], function( $valA, $valB ) {
    return strtolower( $valA ) <=> strtolower( $valB );
} );
```

All examples will return an empty map because both contain the same values
when compared case insensitive.


### diffAssoc()

Returns the keys/values in the map whose keys and values are not present in the passed elements in a new map.

```php
public function diffAssoc( iterable $elements, callable $callback = null ) : self
```

* @param iterable `$elements` List of elements
* @param  callable&#124;null `$callback` Function with (valueA, valueB) parameters and returns -1 (<), 0 (=) and 1 (>)
* @return self New map

**Examples:**

```php
Map::from( ['a' => 'foo', 'b' => 'bar'] )->diffAssoc( new Map( ['foo', 'b' => 'bar'] ) );
```

**Results:**

```php
['a' => 'foo']
```

If a callback is passed, the given function will be used to compare the values.
The function must accept two parameters (value A and B) and must return
-1 if value A is smaller than value B, 0 if both are equal and 1 if value A is
greater than value B. Both, a method name and an anonymous function can be passed:

```php
Map::from( [0 => 'a'] )->diffAssoc( [0 => 'A'], 'strcasecmp' );
Map::from( ['b' => 'a'] )->diffAssoc( ['B' => 'A'], 'strcasecmp' );
Map::from( ['b' => 'a'] )->diffAssoc( ['c' => 'A'], function( $valA, $valB ) {
    return strtolower( $valA ) <=> strtolower( $valB );
} );
```

The first example will return an empty map because both contain the same
values when compared case insensitive. The second and third example will return
an empty map because 'A' is part of the passed array but the keys doesn't match
("b" vs. "B" and "b" vs. "c").


### diffKeys()

Returns the key/value pairs from the map whose keys are not present in the passed elements in a new map.

```php
public function diffKeys( iterable $elements, callable $callback = null ) : self
```

* @param iterable `$elements` List of elements
* @param  callable&#124;null `$callback` Function with (keyA, keyB) parameters and returns -1 (<), 0 (=) and 1 (>)
* @return self New map

**Examples:**

```php
Map::from( ['a' => 'foo', 'b' => 'bar'] )->diffKeys( new Map( ['foo', 'b' => 'baz'] ) );
```

**Results:**

```php
['a' => 'foo']
```

If a callback is passed, the given function will be used to compare the keys.
The function must accept two parameters (key A and B) and must return
-1 if key A is smaller than key B, 0 if both are equal and 1 if key A is
greater than key B. Both, a method name and an anonymous function can be passed:

```php
Map::from( [0 => 'a'] )->diffKeys( [0 => 'A'], 'strcasecmp' );
Map::from( ['b' => 'a'] )->diffKeys( ['B' => 'X'], 'strcasecmp' );
Map::from( ['b' => 'a'] )->diffKeys( ['c' => 'a'], function( $keyA, $keyB ) {
    return strtolower( $keyA ) <=> strtolower( $keyB );
} );
```

The first and second example will return an empty map because both contain
the same keys when compared case insensitive. The third example will return
['b' => 'a'] because the keys doesn't match ("b" vs. "c").


### dump()

Dumps the map content using the given function (print_r by default).

The `dump()` method is very helpful to see what are the map elements passed
between two map methods in a method call chain.

```php
public function dump( callable $callback = null ) : self
```

* @param callable `$callback` Function receiving the map elements as parameter (optional)
* @return self Same map for fluid interface

**Examples:**

```php
Map::from( ['a' => 'foo', 'b' => 'bar'] )->dump()->asort()->dump( 'var_dump' );
```

**Results:**

```php
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
```


### duplicates()

Returns the duplicate values from the map.

```php
public function duplicates( string $col = null ) : self
```

* @param string|null $col Key of the nested array or object to check for
* @return self New map

**Examples:**

```php
Map::from( [1, 2, '1', 3] )->duplicates()
Map::from( [['p' => '1'], ['p' => 1], ['p' => 2]] )->duplicates( 'p' )
```

**Results:**

```php
[2 => '1']
[1 => ['p' => 1]]
```

The keys in the result map are the same as in the original one. For nested
arrays, you have to pass the name of the column of the nested array which
should be used to check for duplicates.


### each()

Executes a callback over each entry until `FALSE` is returned.

```php
public function each( \Closure $callback ) : self
```

* @param \Closure `$callback` Function with (value, key) parameters and returns `TRUE`/`FALSE`
* @return self Same map for fluid interface

**Examples:**

```php
$result = [];
Map::from( [0 => 'a', 1 => 'b'] )->each( function( $value, $key ) use ( &$result ) {
    $result[$key] = strtoupper( $value );
    return false;
} );
```

The `$result` array will contain `[0 => 'A']` because `FALSE` is returned
after the first entry and all other entries are then skipped.


### empty()

Determines if the map is empty or not.

```php
public function empty() : bool
```

* @return bool TRUE if map is empty, FALSE if not

**Examples:**

```php
Map::from( [] )->empty();
Map::from( ['a'] )->empty();
```

**Results:**

The first example returns TRUE while the second returns FALSE

The method is equivalent to isEmpty().


### equals()

Tests if the passed elements are equal to the elements in the map.

```php
public function equals( iterable $elements, $assoc = false ) : bool
```

* @param iterable `$elements` List of elements to test against
* @param bool `$assoc` `TRUE` to compare keys too, `FALSE` to compare only values
* @return bool `TRUE` if both are equal, `FALSE` if not

**Examples:**

```php
Map::from( ['a'] )->equals( ['a', 'b'] );
Map::from( ['a', 'b'] )->equals( ['b'] );
Map::from( ['a', 'b'] )->equals( ['b', 'a'] );
```

**Results:**

The first and second example will return `FALSE`, the third example will return `TRUE`

The method differs to is() in the fact that it doesn't care about the keys
by default. The elements are only loosely compared and the keys are ignored.

If the second parameter is `TRUE`, keys are compared too:

```php
Map::from( [0 => 'a'] )->equals( [1 => 'a'], true );
Map::from( [1 => 'a'] )->equals( [0 => 'a'], true );
Map::from( [0 => 'a'] )->equals( [0 => 'a'], true );
```

The first and second example above will also return `FALSE` and only the third
example will return `TRUE`

Keys and values are compared by their string values:
```php
(string) $item1 === (string) $item2
```


### every()

Verifies that all elements pass the test of the given callback.

```php
public function every( \Closure $callback ) : bool
```

* @param \Closure `$callback` Function with (value, key) parameters and returns TRUE/FALSE
* @return bool True if all elements pass the test, false if if fails for at least one element

**Examples:**

```php
Map::from( [0 => 'a', 1 => 'b'] )->every( function( $value, $key ) {
    return is_string( $value );
} );

Map::from( [0 => 'a', 1 => 100] )->every( function( $value, $key ) {
    return is_string( $value );
} );
```

**Results:**

```php
true
false
```


### except()

Returns a new map without the passed element keys.

```php
public function except( $keys ) : self
```

* @param mixed|array `$keys` List of keys to remove
* @return self New map

**Examples:**

```php
Map::from( ['a' => 1, 'b' => 2, 'c' => 3] )->except( 'b' );
Map::from( [1 => 'a', 2 => 'b', 3 => 'c'] )->except( [1, 3] );
```

**Results:**

```php
['a' => 1, 'c' => 3]
[2 => 'b']
```


### filter()

Runs a filter over each element of the map and returns a new map.

```php
public function filter( callable $callback = null ) : self
```

* @param  callable&#124;null `$callback` Function with (item) parameter and returns `TRUE`/`FALSE`
* @return self New map

**Examples:**

```php
Map::from( [2 => 'a', 6 => 'b', 13 => 'm', 30 => 'z'] )->filter( function( $value, $key ) {
    return `$key` < 10 && `$value` < 'n';
} );
```

**Results:**

```php
['a', 'b']
```

If no callback is passed, all values which are empty, null or false will be
removed if their value converted to boolean is `FALSE`:
```php
(bool) $value === false
```


### find()

Returns the first matching element where the callback returns TRUE.

```php
public function find( \Closure $callback, bool $reverse = false )
```

* @param \Closure `$callback` Function with (value, key) parameters and returns TRUE/FALSE
* @param bool `$reverse` TRUE to test elements from back to front, FALSE for front to back (default)
* @return mixed&#124;null First matching value or NULL

**Examples:**

```php
Map::from( ['a', 'c', 'e'] )->find( function( $value, $key ) {
    return $value >= 'b';
} );
Map::from( ['a', 'c', 'e'] )->find( function( $value, $key ) {
    return $value >= 'b';
}, true );
```

**Results:**

The first example will return 'c' while the second will return 'e' (last element).


### first()

Returns the first element from the map.

```php
public function first( $default = null )
```

* @param mixed `$default` Default value or exception if the map contains no elements
* @return mixed First value of map, (generated) default value or an exception

**Examples:**

```php
Map::from( ['a', 'b'] )->first();
Map::from( [] )->first( 'x' );
Map::from( [] )->first( new \Exception( 'error' ) );
Map::from( [] )->first( function() { return rand(); } );
```

**Results:**

The first example will return 'a' and the second one 'x'. The third example
will throw the exception passed if the map contains no elements. In the
fourth example, a random value generated by the closure function will be
returned.


### firstKey()

Returns the first key from the map.

```php
public function firstKey()
```

* @return mixed First key of map or `NULL` if empty

**Examples:**

```php
Map::from( ['a' => 1, 'b' => 2] )->lastKey();
Map::from( [] )->lastKey();
```

**Results:**

The first example will return 'a' and the second one `NULL`.


### flat()

Creates a new map with all sub-array elements added recursively

```php
public function flat( int $depth = null ) : self
```

* @param int&#124;null `$depth` Number of levels to flatten multi-dimensional arrays
* @return self New map with all sub-array elements added into it recursively, up to the specified depth

**Examples:**

```php
Map::from( [[0, 1], [2, 3]] )->flat();
Map::from( [[0, 1], [[2, 3], 4]] )->flat();
Map::from( [[0, 1], [[2, 3], 4]] )->flat( 1 );
Map::from( [[0, 1], Map::from( [[2, 3], 4] )] )->flat();
```

**Results:**

```php
[0, 1, 2, 3]
[0, 1, 2, 3, 4]
[0, 1, [2, 3], 4]
[0, 1, 2, 3, 4]
```

The keys are not preserved and the new map elements will be numbered from
0-n. A value smaller than 1 for depth will return the same map elements
indexed from 0-n. Flattening does also work if elements implement the
"Traversable" interface (which the Map object does).

This method is similar than collapse() but doesn't replace existing elements.


### flip()

Exchanges the keys with their values and vice versa.

```php
public function flip() : self
```

* @return self New map with keys as values and values as keys

**Examples:**

```php
Map::from( ['a' => 'X', 'b' => 'Y'] )->flip();
```

**Results:**

```php
['X' => 'a', 'Y' => 'b']
```


### from()

Creates a new map instance if the value isn't one already.

```php
public static function from( $elements = [] ) : self
```

* @param mixed `$elements` List of elements or single value
* @return self New map

**Examples:**

```php
Map::from( [] );
Map::from( null );
Map::from( 'a' );
Map::from( new Map() );
Map::from( new ArrayObject() );
```

**Results:**

A new map instance containing the list of elements. In case of an empty
array or null, the map object will contain an empty list. If a map object
is passed, it will be returned instead of creating a new instance.


### fromJson()

Creates a new map instance from a JSON string.

```php
public static function fromJson( string $json, int $options = JSON_BIGINT_AS_STRING ) : self
```

* @param int `$options` Combination of JSON_* constants
* @return self Map from decoded JSON string

**Examples:**

```php
Map::fromJson( '["a", "b"]' );
Map::fromJson( '{"a": "b"}' );
Map::fromJson( '""' );
```

**Results:**

```php
['a', 'b']
['a' => 'b']
['']
```

There are several options available for decoding the JSON string:
[https://www.php.net/manual/en/function.json-decode.php](https://www.php.net/manual/en/function.json-decode.php)
The parameter can be a single JSON_* constant or a bitmask of several
constants combine by bitwise OR (|), e.g.:

`JSON_BIGINT_AS_STRING|JSON_INVALID_UTF8_IGNORE`


### get()

Returns an element from the map by key.

```php
public function get( $key, $default = null )
```

* @param mixed `$key` Key of the requested item
* @param mixed `$default` Default value if no element matches
* @return mixed Value from map or default value

**Examples:**

```php
Map::from( ['a' => 'X', 'b' => 'Y'] )->get( 'a' );
Map::from( ['a' => 'X', 'b' => 'Y'] )->get( 'c', 'Z' );
Map::from( [] )->get( new \Exception( 'error' ) );
Map::from( [] )->get( function() { return rand(); } );
```

**Results:**

The first example will return "X", the second "Z". The third example
will throw the exception passed if the map contains no elements. In the
fourth example, a random value generated by the closure function will be
returned.


### getIterator()

Returns an iterator for the elements.

This method will be used by e.g. foreach() to loop over all entries:

```php
public function getIterator() : \Iterator
```

* @return \Iterator Over map elements

**Examples:**

```
foreach( Map::from( ['a', 'b'] ) as $value )
```


### groupBy()

Groups associative array elements or objects by the passed key or closure.

Instead of overwriting items with the same keys like to the `col()` method does,
`groupBy()` keeps all entries in sub-arrays. It's preserves the keys of the
orignal map entries too.

```php
public function groupBy( $key ) : self
```

* @param  \Closure|string $key Closure function with (item, idx) parameters returning the key or the key itself to group by
* @return self New map with elements grouped by the given key

**Examples:**

```php
$list = [
    10 => ['aid' => 123, 'code' => 'x-abc'],
    20 => ['aid' => 123, 'code' => 'x-def'],
    30 => ['aid' => 456, 'code' => 'x-def']
];
Map::from( $list )->groupBy( 'aid' );
Map::from( $list )->groupBy( function( $item, $key ) {
    return substr( $item['code'], -3 );
} );
Map::from( $list )->groupBy( 'xid' );
```

**Results:**

[
    123 => [
        10 => ['aid' => 123, 'code' => 'x-abc'],
        20 => ['aid' => 123, 'code' => 'x-def']
    ],
    456 => [
        30 => ['aid' => 456, 'code' => 'x-def']
    ]
]
[
    'abc' => [
        10 => ['aid' => 123, 'code' => 'x-abc']
    ],
    'def' => [
        20 => ['aid' => 123, 'code' => 'x-def'],
        30 => ['aid' => 456, 'code' => 'x-def']
    ]
]
[
    'xid' => [
        10 => ['aid' => 123, 'code' => 'x-abc'],
        20 => ['aid' => 123, 'code' => 'x-def']
        30 => ['aid' => 456, 'code' => 'x-def']
    ]
]

In case the passed key doesn't exist in one or more items, these items are stored
in a sub-array using passed string as key.


### has()

Determines if a key or several keys exists in the map.

If several keys are passed as array, all keys must exist in the map for
`TRUE` to be returned.

```php
public function has( $key ) : bool
```

* @param mixed&#124;array `$key` Key of the requested item
* @return bool `TRUE` if key is available in map, `FALSE` if not

**Examples:**

```php
Map::from( ['a' => 'X', 'b' => 'Y'] )->has( 'a' );
Map::from( ['a' => 'X', 'b' => 'Y'] )->has( ['a', 'b'] );
Map::from( ['a' => 'X', 'b' => 'Y'] )->has( 'c' );
Map::from( ['a' => 'X', 'b' => 'Y'] )->has( ['a', 'c'] );
Map::from( ['a' => 'X', 'b' => 'Y'] )->has( 'X' );
```

**Results:**

The first and second example will return `TRUE` while the other ones will return `FALSE`


### in()

Tests if the passed element or elements are part of the map.

```php
public function in( $element, bool $strict = false ) : bool
```

* @param mixed&#124;array `$element` Element or elements to search for in the map
* @param bool `$strict` `TRUE` to check the type too, using `FALSE` '1' and 1 will be the same
* @return bool `TRUE` if all elements are available in map, `FALSE` if not

**Examples:**

```php
Map::from( ['a', 'b'] )->in( 'a' );
Map::from( ['a', 'b'] )->in( ['a', 'b'] );
Map::from( ['a', 'b'] )->in( 'x' );
Map::from( ['a', 'b'] )->in( ['a', 'x'] );
Map::from( ['1', '2'] )->in( 2, true );
```

**Results:**

The first and second example will return `TRUE` while the other ones will return `FALSE`


## includes()

Tests if the passed element or elements are part of the map.

```php
public function includes( $element, bool $strict = false ) : bool
```

* @param mixed&#124;array `$element` Element or elements to search for in the map
* @param bool `$strict` TRUE to check the type too, using FALSE '1' and 1 will be the same
* @return bool TRUE if all elements are available in map, FALSE if not

**Examples:**

```php
Map::from( ['a', 'b'] )->includes( 'a' );
Map::from( ['a', 'b'] )->includes( ['a', 'b'] );
Map::from( ['a', 'b'] )->includes( 'x' );
Map::from( ['a', 'b'] )->includes( ['a', 'x'] );
Map::from( ['1', '2'] )->includes( 2, true );
```

**Results:**

The first and second example will return TRUE while the other ones will return FALSE

This method is an alias for `in()`. For performance reasons, `in()` should be preferred
because it uses one method call less than `includes()`.


### intersect()

Returns all values in a new map that are available in both, the map and the given elements.

```php
public function intersect( iterable $elements, callable $callback = null ) : self
```

* @param iterable `$elements` List of elements
* @param  callable&#124;null `$callback` Function with (valueA, valueB) parameters and returns -1 (<), 0 (=) and 1 (>)
* @return self New map

**Examples:**

```php
Map::from( ['a' => 'foo', 'b' => 'bar'] )->intersect( ['bar'] );
```

**Results:**

```php
['b' => 'bar']
```

If a callback is passed, the given function will be used to compare the values.
The function must accept two parameters (value A and B) and must return
-1 if value A is smaller than value B, 0 if both are equal and 1 if value A is
greater than value B. Both, a method name and an anonymous function can be passed:

```php
Map::from( [0 => 'a'] )->intersect( [0 => 'A'], 'strcasecmp' );
Map::from( ['b' => 'a'] )->intersect( ['B' => 'A'], 'strcasecmp' );
Map::from( ['b' => 'a'] )->intersect( ['c' => 'A'], function( $valA, $valB ) {
    return strtolower( $valA ) <=> strtolower( $valB );
} );
```

All examples will return a map containing ['a'] because both contain the same
values when compared case insensitive.


### intersectAssoc()

Returns all values in a new map that are available in both, the map and the given elements while comparing the keys too.

```php
public function intersectAssoc( iterable $elements, callable $callback = null ) : self
```

* @param iterable `$elements` List of elements
* @param  callable&#124;null `$callback` Function with (valueA, valueB) parameters and returns -1 (<), 0 (=) and 1 (>)
* @return self New map

**Examples:**

```php
Map::from( ['a' => 'foo', 'b' => 'bar'] )->intersectAssoc( new Map( ['foo', 'b' => 'bar'] ) );
```

**Results:**

```php
['a' => 'foo']
```

If a callback is passed, the given function will be used to compare the values.
The function must accept two parameters (value A and B) and must return
-1 if value A is smaller than value B, 0 if both are equal and 1 if value A is
greater than value B. Both, a method name and an anonymous function can be passed:

```php
Map::from( [0 => 'a'] )->intersectAssoc( [0 => 'A'], 'strcasecmp' );
Map::from( ['b' => 'a'] )->intersectAssoc( ['B' => 'A'], 'strcasecmp' );
Map::from( ['b' => 'a'] )->intersectAssoc( ['c' => 'A'], function( $valA, $valB ) {
    return strtolower( $valA ) <=> strtolower( $valB );
} );
```

The first example will return [0 => 'a'] because both contain the same
values when compared case insensitive. The second and third example will return
an empty map because the keys doesn't match ("b" vs. "B" and "b" vs. "c").


### intersectKeys()

Returns all values in a new map that are available in both, the map and the given elements by comparing the keys only.

```php
public function intersectKeys( iterable $elements, callable $callback = null ) : self
```

* @param iterable `$elements` List of elements
* @param  callable&#124;null `$callback` Function with (keyA, keyB) parameters and returns -1 (<), 0 (=) and 1 (>)
* @return self New map

**Examples:**

```php
Map::from( ['a' => 'foo', 'b' => 'bar'] )->intersectKeys( new Map( ['foo', 'b' => 'baz'] ) );
```

**Results:**

```php
['b' => 'bar']
```

If a callback is passed, the given function will be used to compare the keys.
The function must accept two parameters (key A and B) and must return
-1 if key A is smaller than key B, 0 if both are equal and 1 if key A is
greater than key B. Both, a method name and an anonymous function can be passed:

```php
Map::from( [0 => 'a'] )->intersectKeys( [0 => 'A'], 'strcasecmp' );
Map::from( ['b' => 'a'] )->intersectKeys( ['B' => 'X'], 'strcasecmp' );
Map::from( ['b' => 'a'] )->intersectKeys( ['c' => 'a'], function( $keyA, $keyB ) {
    return strtolower( $keyA ) <=> strtolower( $keyB );
} );
```

The first example will return a map with [0 => 'a'] and the second one will
return a map with ['b' => 'a'] because both contain the same keys when compared
case insensitive. The third example will return an empty map because the keys
doesn't match ("b" vs. "c").


### is()

Tests if the map consists of the same keys and values

```php
public function is( iterable $list, bool $strict = false ) : bool
```

* @param iterable `$list` List of key/value pairs to compare with
* @param bool `$strict` TRUE for comparing order of elements too, FALSE for key/values only
* @param bool TRUE if given list is equal, FALSE if not

**Examples:**
```php
Map::from( ['a', 'b'] )->is( ['b', 'a'] );
Map::from( ['a', 'b'] )->is( ['b', 'a'], true );
Map::from( [1, 2] )->is( ['1', '2'] );
```

**Results:**
The first example returns TRUE while the second and third one returns FALSE


### isEmpty()

Determines if the map is empty or not.

```php
public function isEmpty() : bool
```

* @return bool `TRUE` if map is empty, `FALSE` if not

**Examples:**

```php
Map::from( [] )->isEmpty();
Map::from( ['a'] )-isEmpty();
```

**Results:**

The first example returns `TRUE` while the second returns `FALSE`

The method is equivalent to empty().


### join()

Concatenates the string representation of all elements.

Objects that implement __toString() does also work, otherwise (and in case
of arrays) a PHP notice is generated. `NULL` and `FALSE` values are treated as
empty strings.

```php
public function join( $glue = '' ) : string
```

* @param string `$glue` Character or string added between elements
* @return string String of concatenated map elements

**Examples:**

```php
Map::from( ['a', 'b', false] )->join();
Map::from( ['a', 'b', null, false] )->join( '-' );
```

**Results:**

The first example will return "ab" while the second one will return "a-b--"


### keys()

Returns the keys of the map elements in a new map object.

```php
public function keys() : self
```

* @return self New map

**Examples:**

```php
Map::from( ['a', 'b'] );
Map::from( ['a' => 0, 'b' => 1] );
```

**Results:**

The first example returns a map containing `[0, 1]` while the second one will
return a map with `['a', 'b']`.


### krsort()

Sorts the elements by their keys in reverse order.

```php
public function krsort( int $options = SORT_REGULAR ) : self
```

* @param int `$options` Sort options for `krsort()`
* @return self Updated map for fluid interface

**Examples:**

```php
Map::from( ['b' => 0, 'a' => 1] )->krsort();
Map::from( [1 => 'a', 0 => 'b'] )->krsort();
```

**Results:**

```php
['a' => 1, 'b' => 0]
[0 => 'b', 1 => 'a']
```

The parameter modifies how the keys are compared. Possible values are:
- SORT_REGULAR : compare elements normally (don't change types)
- SORT_NUMERIC : compare elements numerically
- SORT_STRING : compare elements as strings
- SORT_LOCALE_STRING : compare elements as strings, based on the current locale or changed by setlocale()
- SORT_NATURAL : compare elements as strings using "natural ordering" like natsort()
- SORT_FLAG_CASE : use SORT_STRING&#124;SORT_FLAG_CASE and SORT_NATURAL&#124;SORT_FLAG_CASE to sort strings case-insensitively

The keys are preserved using this method and no new map is created.


### ksort()

Sorts the elements by their keys.

```php
public function ksort( int $options = SORT_REGULAR ) : self
```

* @param int `$options` Sort options for `ksort()`
* @return self Updated map for fluid interface

**Examples:**

```php
Map::from( ['b' => 0, 'a' => 1] )->ksort();
Map::from( [1 => 'a', 0 => 'b'] )->ksort();
```

**Results:**

```php
['a' => 1, 'b' => 0]
[0 => 'b', 1 => 'a']
```

The parameter modifies how the keys are compared. Possible values are:
- SORT_REGULAR : compare elements normally (don't change types)
- SORT_NUMERIC : compare elements numerically
- SORT_STRING : compare elements as strings
- SORT_LOCALE_STRING : compare elements as strings, based on the current locale or changed by setlocale()
- SORT_NATURAL : compare elements as strings using "natural ordering" like natsort()
- SORT_FLAG_CASE : use SORT_STRING&#124;SORT_FLAG_CASE and SORT_NATURAL&#124;SORT_FLAG_CASE to sort strings case-insensitively

The keys are preserved using this method and no new map is created.


### last()

Returns the last element from the map.

```php
public function last( $default = null )
```

* @param mixed `$default` Default value or exception if the map contains no elements
* @return mixed Last value of map, (generated) default value or an exception

**Examples:**

```php
Map::from( ['a', 'b'] )->last();
Map::from( [] )->last( 'x' );
Map::from( [] )->last( new \Exception( 'error' ) );
Map::from( [] )->last( function() { return rand(); } );
```

**Results:**

The first example will return 'b' and the second one 'x'. The third example
will throw the exception passed if the map contains no elements. In the
fourth example, a random value generated by the closure function will be
returned.


### lastKey()

Returns the last key from the map.

```php
public function lastKey()
```

* @return mixed Last key of map or `NULL` if empty

**Examples:**

```php
Map::from( ['a' => 1, 'b' => 2] )->lastKey();
Map::from( [] )->lastKey();
```

**Results:**

The first example will return 'b' and the second one `NULL`.


### map()

Calls the passed function once for each element and returns a new map for the result.

```php
public function map( callable $callback ) : self
```

* @param callable `$callback` Function with (value, key) parameters and returns computed result
* @return self New map with the original keys and the computed values

**Examples:**

```php
Map::from( ['a' => 2, 'b' => 4] )->map( function( $value, $key ) {
    return $value * 2;
} );
```

**Results:**

```php
['a' => 4, 'b' => 8]
```


### max()

Returns the maximum value of all elements.

For nested arrays, you have to pass the name of the column of the nested
array which should be used for comparison.

```php
public function max( string $col = null )
```

* @param string|null $col Key in the nested array or object to check for
* @return mixed Maximum value or NULL if there are no elements in the map

**Examples:**

```php
Map::from( [1, 3, 2, 5, 4] )->max()
Map::from( ['bar', 'foo', 'baz'] )->max()
Map::from( [['p' => 30], ['p' => 50], ['p' => 10]] )->max( 'p' )
```

**Results:**

The first line will return "5", the second one "foo" while the third one
returns 50.

If you need a function to retrieve the maximum of all values, then use:

```php
$sum = Map::from( [['v' => ['p' => 10]]] )->reduce( function( $result, $entry ) {
    return max( $entry['v']['p'] ?? null, $result );
} );
```

Be careful comparing elements of different types because this can have
unpredictable results due to the [PHP comparison rules](https://www.php.net/manual/en/language.operators.comparison.php)


### merge()

Merges the map with the given elements without returning a new map.

Elements with the same non-numeric keys will be overwritten, elements with the
same numeric keys will be added.

```php
public function merge( iterable $elements, bool $recursive = false ) : self
```

* @param iterable `$elements` List of elements
* @param bool $recursive TRUE to merge nested arrays too, FALSE for first level elements only
* @return self Updated map for fluid interface

**Examples:**

```php
Map::from( ['a', 'b'] )->merge( ['b', 'c'] );
Map::from( ['a' => 1, 'b' => 2] )->merge( ['b' => 4, 'c' => 6] );
Map::from( ['a' => 1, 'b' => 2] )->merge( ['b' => 4, 'c' => 6], true );
```

**Results:**

```php
['a', 'b', 'b', 'c']
['a' => 1, 'b' => 4, 'c' => 6]
['a' => 1, 'b' => [2, 4], 'c' => 6]
```

The method is similar to `replace()` but doesn't replace elements with the same
numeric keys. If you want to be sure that all passed elements are added without
replacing existing ones, use `concat()` instead.


### method()

Registers a custom method that has access to the class properties if called non-static.

```php
public static function method( string $name, \Closure $function )
```

* @param string `$name` Method name
* @param \Closure `$function` Anonymous method

**Examples:**

```php
Map::method( 'foo', function( $arg1, $arg2 ) {
    return $this->elements;
} );
```

Dynamic calls have access to the class properties:
```php
(new Map( ['bar'] ))->foo( $arg1, $arg2 );
```

Static calls yield an error because `$this->elements` isn't available:
```php
Map::foo( $arg1, $arg2 );
```


### min()

Returns the minimum value of all elements.

For nested arrays, you have to pass the name of the column of the nested
array which should be used for comparison.

```php
public function min( string $col = null )
```

* @param string|null $col Key in the nested array or object to check for
* @return mixed Minimum value or NULL if there are no elements in the map

**Examples:**

```php
Map::from( [2, 3, 1, 5, 4] )->min()
Map::from( ['baz', 'foo', 'bar'] )->min()
Map::from( [['p' => 30], ['p' => 50], ['p' => 10]] )->min( 'p' )
```

**Results:**

The first line will return "1", the second one "bar" while the third one returns 10.

If you need a function to retrieve the minimum of all values, then use:

```php
$sum = Map::from( [['v' => ['p' => 10]]] )->reduce( function( $result, $entry ) {
    return min( $entry['v']['p'] ?? null, $result );
} );
```

Be careful comparing elements of different types because this can have
unpredictable results due to the [PHP comparison rules](https://www.php.net/manual/en/language.operators.comparison.php)


### nth()

Returns every nth element from the map.

```php
public function nth( int $step, int $offset = 0 ) : self
```

* @param int `$step` Step width
* @param int `$offset` Number of element to start from (0-based)
* @return self New map

**Examples:**

```php
Map::from( ['a', 'b', 'c', 'd', 'e', 'f'] )->nth( 2 );
Map::from( ['a', 'b', 'c', 'd', 'e', 'f'] )->nth( 2, 1 );
```

**Results:**

```php
['a', 'c', 'e']
['b', 'd', 'f']
```

### offsetExists()

Determines if an element exists at an offset.

```php
public function offsetExists( $key )
```

* @param mixed `$key` Key to check for
* @return bool `TRUE` if key exists, `FALSE` if not

**Examples:**

```php
$map = Map::from( ['a' => 1, 'b' => 3] );
isset( $map['b'] );
isset( $map['c'] );
```

**Results:**

The first `isset()` will return `TRUE` while the second one will return `FALSE`


### offsetGet()

Returns an element at a given offset.

```php
public function offsetGet( $key )
```

* @param mixed `$key` Key to return the element for
* @return mixed Value associated to the given key

**Examples:**

```php
$map = Map::from( ['a' => 1, 'b' => 3] );
$map['b'];
```

**Results:**

`$map['b']` will return 3


### offsetSet()

Sets the element at a given offset.

```php
public function offsetSet( $key, $value )
```

* @param mixed `$key` Key to set the element for
* @param mixed `$value` New value set for the key

**Examples:**

```php
$map = Map::from( ['a' => 1] );
$map['b'] = 2;
$map[0] = 4;
```

**Results:**

```php
['a' => 1, 'b' => 2, 0 => 4]
```


### offsetUnset()

Unsets the element at a given offset.

```php
public function offsetUnset( $key )
```

* @param string `$key` Key for unsetting the item

**Examples:**

```php
$map = Map::from( ['a' => 1] );
unset( $map['a'] );
```

**Results:**

The map will be empty


### only()

Returns a new map with only those elements specified by the given keys.

```php
public function only( $keys ) : self
```

* @param array|string|int `$keys` Keys of the elements that should be returned
* @return self New map with only the elements specified by the keys

**Examples:**

```php
Map::from( ['a' => 1, 0 => 'b'] )->only( 'a' );
Map::from( ['a' => 1, 0 => 'b', 1 => 'c'] )->only( [0, 1] );
```

**Results:**

```php
['a' => 1]
[0 => 'b', 1 => 'c']
```


## pad()

Fill up to the specified length with the given value

```php
public function pad( int $size, $value = null ) : self
```

* @param int `$size` Total number of elements that should be in the list
* @return self Updated map for fluid interface

**Examples:**

```php
Map::from( [1, 2, 3] )->pad( 5 );
Map::from( [1, 2, 3] )->pad( -5 );
Map::from( [1, 2, 3] )->pad( 5, '0' );
Map::from( [1, 2, 3] )->pad( 2 );
```

**Results:**

```php
[1, 2, 3, null, null]
[null, null, 1, 2, 3]
[1, 2, 3, '0', '0']
[1, 2, 3]
```

In case the given number is smaller than the number of element that are
already in the list, the map is unchanged. If the size is positive, the
new elements are padded on the right, if it's negative then the elements
are padded on the left.


### partition()

Breaks the list of elements into the given number of groups.

```php
public function partition( $num ) : self
```

* @param \Closure|int `$number` Function with (value, index) as arguments returning the bucket key or number of groups
* @return self New map

**Examples:**

```php
Map::from( [1, 2, 3, 4, 5] )->partition( 3 );
Map::from( [1, 2, 3, 4, 5] )->partition( function( $val, $idx ) {
	return $idx % 3;
} );
```

**Results:**

```php
[[0 => 1, 1 => 2], [2 => 3, 3 => 4], [4 => 5]]
[0 => [0 => 1, 3 => 4], 1 => [1 => 2, 4 => 5], 2 => [2 => 3]]
```

The keys of the original map are preserved in the returned map.


### pipe()

Passes the map to the given callback and return the result.

```php
public function pipe( \Closure $callback )
```

* @param \Closure `$callback` Function with map as parameter which returns arbitrary result
* @return mixed Result returned by the callback

**Examples:**

```php
Map::from( ['a', 'b'] )->pipe( function( $map ) {
    return strrev( $map->join( '-' ) );
} );
```

**Results:**

"b-a" will be returned


### pop()

Returns and removes the last element from the map.

```php
public function pop()
```

* @return mixed Last element of the map or null if empty

**Examples:**

```php
Map::from( ['a', 'b'] )->pop();
```

**Results:**

"b" will be returned and the map only contains `['a']` afterwards


### pos

Returns the numerical index of the value.

```php
public function pos( $value ) : int
```

* @param \Closure|mixed $value Value to search for or function with (item, key) parameters return TRUE if value is found
* @return int Position of the found value (zero based)

**Examples:**

```php
Map::from( [4 => 'a', 8 => 'b'] )->pos( 'b' );
Map::from( [4 => 'a', 8 => 'b'] )->pos( function( $item, $key ) {
    return $item === 'b';
} );
```

**Results:**

Both examples will return "1" because the value "b" is at the second position
and the returned index is zero based so the first item has the index "0".


### prefix

Adds a prefix in front of each map entry.

```php
public function prefix( $prefix )
```

* @param \Closure|string `$prefix` Function with map as parameter which returns arbitrary result
* @return self Updated map for fluid interface

**Examples:**

```php
Map::from( ['a', 'b'] )->prefix( '1-' );
Map::from( ['a', ['b']] )->prefix( '1-' );
Map::from( ['a', 'b'] )->prefix( function( $item, $key ) {
    return ( ord( $item ) + ord( $key ) ) . '-';
} );
```

**Results:**

The first example returns `['1-a', '1-b']` while the second one will return
`['1-a', ['1-b']]`. The third example passing the closure will return
`['145-a', '147-b']`.

Nested arrays are walked recusively so all entries at all levels are prefixed.


### pull()

Returns and removes an element from the map by its key.

```php
public function pull( $key, $default = null )
```

* @param mixed `$key` Key to retrieve the value for
* @param mixed `$default` Default value if key isn't available
* @return mixed Value from map or default value

**Examples:**

```php
Map::from( ['a', 'b', 'c'] )->pull( 1 );
Map::from( ['a', 'b', 'c'] )->pull( 'x', 'none' );
```

**Results:**

The first example will return "b" and the map contains `['a', 'c']` afterwards.
The second one will return "none" and the map content stays untouched.


### push()

Adds an element onto the end of the map without returning a new map.

```php
public function push( $value ) : self
```

* @param mixed `$value` Value to add to the end
* @return self Same map for fluid interface

**Examples:**

```php
Map::from( ['a', 'b'] )->push( 'aa' );
```

**Results:**

```php
['a', 'b', 'aa']
```


### random()

Returns one or more random element from the map.

```php
public function random( int $max = 1 ) : self
```

* @param int `$max` Maximum number of elements that should be returned
* @return self New map with key/element pairs from original map in random order
* @throws \InvalidArgumentException If requested number of elements is less than 1

**Examples:**

```php
*  Map::from( [2, 4, 8, 16] )->random();
*  Map::from( [2, 4, 8, 16] )->random( 2 );
*  Map::from( [2, 4, 8, 16] )->random( 5 );
```

**Results:**

The first example will return a map including `[0 => 8]` or any other value,
the second one will return a map with `[0 => 16, 1 => 2]` or any other values
and the third example will return a map of the whole list in random order. The
less elements are in the map, the less random the order will be, especially if
the maximum number of values is high or close to the number of elements.

The keys of the original map are preserved in the returned map.


### reduce()

Iteratively reduces the array to a single value using a callback function.
Afterwards, the map will be empty.

```php
public function reduce( callable $callback, $initial = null )
```

* @param callable `$callback` Function with (result, value) parameters and returns result
* @param mixed `$initial` Initial value when computing the result
* @return mixed Value computed by the callback function

**Examples:**

```php
Map::from( [2, 8] )->reduce( function( $result, $value ) {
    return $result += $value;
}, 10 );
```

**Results:**

"20" will be returned because the sum is computed by 10 (initial value) + 2 + 8


### remove()

Removes one or more elements from the map by its keys without returning a new map.

```php
public function remove( $keys ) : self
```

* @param string&#124;int&#124;iterable `$keys` List of keys
* @return self Same map for fluid interface

**Examples:**

```php
Map::from( ['a' => 1, 2 => 'b'] )->remove( 'a' );
Map::from( ['a' => 1, 2 => 'b'] )->remove( [2, 'a'] );
```

**Results:**

The first example will result in `[2 => 'b']` while the second one resulting
in an empty list


### replace()

Replaces elements in the map with the given elements without returning a new map.

```php
public function replace( iterable $elements, bool $recursive = true ) : self
```

* @param iterable `$elements` List of elements
* @param bool `$recursive` TRUE to replace recursively (default), FALSE to replace elements only
* @return self Updated map for fluid interface

**Examples:**

```php
Map::from( ['a' => 1, 2 => 'b'] )->replace( ['a' => 2] );
Map::from( ['a' => 1, 'b' => ['c' => 3, 'd' => 4]] )->replace( ['b' => ['c' => 9]] );
```

**Results:**

```php
['a' => 2, 2 => 'b']
['a' => 1, 'b' => ['c' => 9, 'd' => 4]]
```

The method is similar to `merge()` but also replaces elements with numeric keys.
These would be added by `merge()` with a new numeric key.


### reverse()

Reverses the element order without returning a new map.

```php
public function reverse() : self
```

* @return self Updated map for fluid interface

**Examples:**

```php
Map::from( ['a', 'b'] )->reverse();
```

**Results:**

```php
['b', 'a']
```


### rsort()

Sorts all elements in reverse order without maintaining the key association.

```php
public function rsort( int $options = SORT_REGULAR ) : self
```

* @param int `$options` Sort options for `rsort()`
* @return self Updated map for fluid interface

**Examples:**

```php
Map::from( ['a' => 1, 'b' => 0] )->rsort();
Map::from( [0 => 'b', 1 => 'a'] )->rsort();
```

**Results:**

```php
[0 => 1, 1 => 0]
[0 => 'b', 1 => 'a']
```

The parameter modifies how the values are compared. Possible parameter values are:
- SORT_REGULAR : compare elements normally (don't change types)
- SORT_NUMERIC : compare elements numerically
- SORT_STRING : compare elements as strings
- SORT_LOCALE_STRING : compare elements as strings, based on the current locale or changed by setlocale()
- SORT_NATURAL : compare elements as strings using "natural ordering" like natsort()
- SORT_FLAG_CASE : use SORT_STRING&#124;SORT_FLAG_CASE and SORT_NATURAL&#124;SORT_FLAG_CASE to sort strings case-insensitively

The keys aren't preserved and elements get a new index. No new map is created


### search()

Searches the map for a given value and return the corresponding key if successful.

```php
public function search( $value, $strict = true )
```

* @param mixed `$value` Item to search for
* @param bool `$strict` `TRUE` if type of the element should be checked too
* @return mixed&#124;null Value from map or null if not found

**Examples:**

```php
Map::from( ['a', 'b', 'c'] )->search( 'b' );
Map::from( [1, 2, 3] )->search( '2', true );
```

**Results:**

The first example will return 1 (array index) while the second one will
return `NULL` because the types doesn't match (int vs. string)


### set()

Sets an element in the map by key without returning a new map.

```php
public function set( $key, $value ) : self
```

* @param mixed `$key` Key to set the new value for
* @param mixed `$value` New element that should be set
* @return self Same map for fluid interface

**Examples:**

```php
Map::from( ['a'] )->set( 1, 'b' );
Map::from( ['a'] )->set( 0, 'b' );
```

**Results:**

The first example results in `['a', 'b']` while the second one produces `['b']`


### shift()

Returns and removes the first element from the map.

```php
public function shift()
```

* @return mixed&#124;null Value from map or null if not found

**Examples:**

```php
Map::from( ['a', 'b'] )->shift();
Map::from( [] )->shift();
```

**Results:**

The first example returns "a" and shortens the map to ['b'] only while the
second example will return `NULL`

**Performance note:**

The bigger the list, the higher the performance impact because shift()
reindexes all existing elements. Usually, it's better to reverse() the list
and pop() entries from the list afterwards if a significant number of elements
should be removed from the list:

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

* @param bool $assoc True to preserve keys, false to assign new keys
* @return self Updated map for fluid interface

**Examples:**

```php
Map::from( [2 => 'a', 4 => 'b'] )->shuffle();
Map::from( [2 => 'a', 4 => 'b'] )->shuffle( true );
```

**Results:**

The map in the first example will contain "a" and "b" in random order and
with new keys assigned. The second call will also return all values in
random order but preserves the keys of the original list.


### skip()

Returns a new map with the given number of items skipped.

```php
public function skip( $offset ) : self
```

* @param \Closure|int $offset Number of items to skip or function($item, $key) returning true for skipped items
* @return self New map

**Examples:**

```php
Map::from( [1, 2, 3, 4] )->skip( 2 );
Map::from( [1, 2, 3, 4] )->skip( function( $item, $key ) {
    return $item < 4;
} );
```

**Results:**

```php
[2 => 3, 3 => 4]
[3 => 4]
```

The keys of the items returned in the new map are the same as in the original one.


### slice()

Returns a map with the slice from the original map.

```php
public function slice( int $offset, int $length = null ) : self
```

* @param int `$offset` Number of elements to start from
* @param int `$length` Number of elements to return
* @return self New map

**Examples:**

```php
Map::from( ['a', 'b', 'c'] )->slice( 1 );
Map::from( ['a', 'b', 'c'] )->slice( 1, 1 );
Map::from( ['a', 'b', 'c', 'd'] )->slice( -2, -1 );
```

**Results:**

The first example will return `['b', 'c']` and the second one `['b']` only.
The third example returns `['c']` because the slice starts at the second
last value and ends before the last value.

The rules for offsets are:
- If offset is non-negative, the sequence will start at that offset
- If offset is negative, the sequence will start that far from the end

Similar for the length:
- If length is given and is positive, then the sequence will have up to that many elements in it
- If the array is shorter than the length, then only the available array elements will be present
- If length is given and is negative then the sequence will stop that many elements from the end
- If it is omitted, then the sequence will have everything from offset up until the end


### some()

Tests if at least one of the elements in the list is part of the map.

```php
public function some( iterable $list, bool $strict = false ) : bool
```

* @param iterable `$list` List of elements to test the map agains
* @param bool `$strict` TRUE to check the type too, using FALSE '1' and 1 will be the same
* @return bool TRUE if at least one element is available in map, FALSE if the map contains none of them

**Examples:**

```php
Map::from( ['a', 'b'] )->some( ['a', 'c'] );
Map::from( ['a', 'b'] )->some( ['c', 'd'] );
Map::from( ['1', '2'] )->some( [2], true );
```

**Results:**

The first example will return TRUE while the second and third one will return FALSE


### sort()

Sorts all elements without maintaining the key association.

```php
public function sort( int $options = SORT_REGULAR ) : self
```

* @param int `$options` Sort options for `sort()`
* @return self Updated map for fluid interface

**Examples:**

```php
Map::from( ['a' => 1, 'b' => 0] )->sort();
Map::from( [0 => 'b', 1 => 'a'] )->sort();
```

**Results:**

```php
[0 => 0, 1 => 1]
[0 => 'a', 1 => 'b']
```

The parameter modifies how the values are compared. Possible parameter values are:
- SORT_REGULAR : compare elements normally (don't change types)
- SORT_NUMERIC : compare elements numerically
- SORT_STRING : compare elements as strings
- SORT_LOCALE_STRING : compare elements as strings, based on the current locale or changed by setlocale()
- SORT_NATURAL : compare elements as strings using "natural ordering" like natsort()
- SORT_FLAG_CASE : use SORT_STRING&#124;SORT_FLAG_CASE and SORT_NATURAL&#124;SORT_FLAG_CASE to sort strings case-insensitively

The keys aren't preserved and elements get a new index. No new map is created.


### splice()

Removes a portion of the map and replace it with the given replacement, then return the updated map.

```php
public function splice( int $offset, int $length = null, $replacement = [] ) : self
```

* @param int `$offset` Number of elements to start from
* @param int&#124;null `$length` Number of elements to remove, NULL for all
* @param mixed `$replacement` List of elements to insert
* @return self New map

**Examples:**

```php
Map::from( ['a', 'b', 'c'] )->splice( 1 );
Map::from( ['a', 'b', 'c'] )->splice( 1, 1, ['x', 'y'] );
```

**Results:**

The first example removes all entries after "a", so only `['a']` will be left
in the map and `['b', 'c']` is returned. The second example replaces/returns "b"
(start at 1, length 1) with `['x', 'y']` so the new map will contain
`['a', 'x', 'y', 'c']` afterwards.

The rules for offsets are:
- If offset is non-negative, the sequence will start at that offset
- If offset is negative, the sequence will start that far from the end

Similar for the length:
- If length is given and is positive, then the sequence will have up to that many elements in it
- If the array is shorter than the length, then only the available array elements will be present
- If length is given and is negative then the sequence will stop that many elements from the end
- If it is omitted, then the sequence will have everything from offset up until the end


### split()

Creates a new map with the string splitted by the delimiter.

```php
public static function split( string $delimiter, string $str ) : self
```

* @param string `$str` String to split
* @param string `$delimiter` Delimiter character or string
* @return self New map with splitted parts

**Examples:**

```php
Map::split( 'a,b,c' );
Map::split( 'a a<-->b b<-->c c', '<-->' );
Map::split( 'string', '' );
```

**Results:**

```php
['a', 'b', 'c']
['a a', 'b b', 'c c']
['s', 't', 'r', 'i', 'n', 'g']
```


### suffix

Adds a suffix at the end of each map entry.

```php
public function suffix( $suffix )
```

* @param \Closure|string `$suffix` Function with map as parameter which returns arbitrary result
* @return self Updated map for fluid interface

**Examples:**

```php
Map::from( ['a', 'b'] )->suffix( '-1' );
Map::from( ['a', ['b']] )->suffix( '-1' );
Map::from( ['a', 'b'] )->suffix( function( $item, $key ) {
    return '-' . ( ord( $item ) + ord( $key ) );
} );
```

**Results:**

The first example returns `['a-1', 'b-1']` while the second one will return
`['a-1', ['b-1']]`. The third example passing the closure will return
`['a-145', 'b-147']`.

Nested arrays are walked recusively so all entries at all levels are suffixed.


### sum()

Returns the sum of all integer and float values in the map.

For nested arrays, you have to pass the name of the column of the nested
array which should be used for comparison.

```php
public function sum( string $col = null ) : int
```

* @param string|null $col Key in the nested array or object to sum up
* @return mixed Sum of all elements or 0 if there are no elements in the map

**Examples:**

```php
Map::from( [1, 3, 5] )->sum()
Map::from( [1, 'sum', 5] )->sum()
Map::from( [['p' => 30], ['p' => 50], ['p' => 10]] )->sum( 'p' )
```

**Results:**

The first line will return "9", the second one "6" and the last one "90".

If you need a function to retrieve the sum of all values, then use:

```php
$sum = Map::from( [['v' => ['p' => 10]]] )->reduce( function( $result, $entry ) {
    return $result += $entry['v']['p'] ?? 0;
}, 0 );
```


### take()

Returns a new map with the given number of items.

```php
public function take( int $size, $offset = 0 ) : self
```

* @param int `$size` Number of items to return
* @param \Closure|int $offset Number of items to skip or function($item, $key) returning true for skipped items
* @return self New map

**Examples:**

```php
Map::from( [1, 2, 3, 4] )->take( 2 );
Map::from( [1, 2, 3, 4] )->take( 2, 1 );
Map::from( [1, 2, 3, 4] )->take( 2, -2 );
Map::from( [1, 2, 3, 4] )->take( 2, function( $item, $key ) {
    return $item < 2;
} );
```

**Results:**

```php
[0 => 1, 1 => 2]
[1 => 2, 2 => 3]
[2 => 3, 3 => 4]
[1 => 2, 2 => 3]
```

The keys of the items returned in the new map are the same as in the original one.


### toArray()

Returns the elements as a plain array.

```php
public function toArray() : array
```

* @return array Plain array


### toJson()

Returns the elements encoded as JSON string.

```php
public function toJson( int $options = 0 ) : string
```

* @param int `$options` Combination of JSON_* constants
* @return string Array encoded as JSON string

There are several options available to modify the JSON output:
[https://www.php.net/manual/en/function.json-encode.php](https://www.php.net/manual/en/function.json-encode.php)
The parameter can be a single JSON_* constant or a bitmask of several constants
combine by bitwise OR (&#124;), e.g.:

```php
 JSON_FORCE_OBJECT|JSON_HEX_QUOT
```


### toUrl()

Creates a HTTP query string from the map elements.

```php
public function toUrl() : string
```

* @return string Parameter string for GET requests

**Examples:**

```php
Map::from( ['a' => 1, 'b' => 2] )->toUrl();
Map::from( ['a' => ['b' => 'abc', 'c' => 'def'], 'd' => 123] )->toUrl();
```

**Results:**

```php
a=1&b=2
a%5Bb%5D=abc&a%5Bc%5D=def&d=123
```


### transpose()

Exchanges rows and columns for a two dimensional map.

```php
public function transpose() : self
```

* @return self New map

**Examples:**

```php
Map::from( [
  ['name' => 'A', 2020 => 200, 2021 => 100, 2022 => 50],
  ['name' => 'B', 2020 => 300, 2021 => 200, 2022 => 100],
  ['name' => 'C', 2020 => 400, 2021 => 300, 2022 => 200],
] )->transpose();

Map::from( [
  ['name' => 'A', 2020 => 200, 2021 => 100, 2022 => 50],
  ['name' => 'B', 2020 => 300, 2021 => 200],
  ['name' => 'C', 2020 => 400]
] );
```

**Results:**

```php
[
  'name' => ['A', 'B', 'C'],
  2020 => [200, 300, 400],
  2021 => [100, 200, 300],
  2022 => [50, 100, 200]
]

[
  'name' => ['A', 'B', 'C'],
  2020 => [200, 300, 400],
  2021 => [100, 200],
  2022 => [50]
]
```


### uasort()

Sorts all elements using a callback and maintains the key association.

```php
public function uasort( callable $callback ) : self
```

* @param callable `$callback` Function with (itemA, itemB) parameters and returns -1 (<), 0 (=) and 1 (>)
* @return self Updated map for fluid interface

The given callback will be used to compare the values. The callback must accept
two parameters (item A and B) and must return -1 if item A is smaller than
item B, 0 if both are equal and 1 if item A is greater than item B. Both, a
method name and an anonymous function can be passed.

**Examples:**

```php
Map::from( ['a' => 'B', 'b' => 'a'] )->uasort( 'strcasecmp' );
Map::from( ['a' => 'B', 'b' => 'a'] )->uasort( function( $itemA, $itemB ) {
    return strtolower( $itemA ) <=> strtolower( $itemB );
} );
```

**Results:**

```php
['b' => 'a', 'a' => 'B']
['b' => 'a', 'a' => 'B']
```

The keys are preserved using this method and no new map is created.


## uksort()

Sorts the map elements by their keys using a callback.

```php
public function uksort( callable $callback ) : self
```

* @param callable `$callback` Function with (keyA, keyB) parameters and returns -1 (<), 0 (=) and 1 (>)
* @return self Updated map for fluid interface

The given callback will be used to compare the keys. The callback must accept
two parameters (key A and B) and must return -1 if key A is smaller than
key B, 0 if both are equal and 1 if key A is greater than key B. Both, a
method name and an anonymous function can be passed.

**Examples:**

```php
Map::from( ['B' => 'a', 'a' => 'b'] )->uksort( 'strcasecmp' );
Map::from( ['B' => 'a', 'a' => 'b'] )->uksort( function( $keyA, $keyB ) {
    return strtolower( $keyA ) <=> strtolower( $keyB );
} );
```

**Results:**

```php
['a' => 'b', 'B' => 'a']
['a' => 'b', 'B' => 'a']
```

The keys are preserved using this method and no new map is created.


### union()

Builds a union of the elements and the given elements without returning a new map.
Existing keys in the map will not be overwritten

```php
public function union( iterable $elements ) : self
```

* @param iterable `$elements` List of elements
* @return self Updated map for fluid interface

**Examples:**

```php
Map::from( [0 => 'a', 1 => 'b'] )->union( [0 => 'c'] );
Map::from( ['a' => 1, 'b' => 2] )->union( ['c' => 1] );
```

**Results:**

The first example will result in `[0 => 'a', 1 => 'b']` because the key 0
isn't overwritten. In the second example, the result will be a combined
list: `['a' => 1, 'b' => 2, 'c' => 1]`.

If list entries should be overwritten,  please use merge() instead!


### unique()

Returns only unique elements from the map in a new map

```php
public function unique() : self
```

* @return self New map

**Examples:**

```php
Map::from( [0 => 'a', 1 => 'b', 2 => 'b', 3 => 'c'] )->unique();
```

**Results:**

A new map with `[0 => 'a', 1 => 'b', 3 => 'c']` as content

Two elements are condidered equal if comparing their string representions returns `TRUE`:
```php
(string) $elem1 === (string) $elem2
```

The keys of the elements are preserved in the new map.


### unshift()

Pushes an element onto the beginning of the map without returning a new map.

```php
public function unshift( $value, $key = null ) : self
```

* @param mixed `$value` Item to add at the beginning
* @param mixed `$key` Key for the item
* @return self Same map for fluid interface

**Examples:**

```php
Map::from( ['a', 'b'] )->unshift( 'd' );
Map::from( ['a', 'b'] )->unshift( 'd', 'first' );
```

**Results:**

The first example will result in `['d', 'a', 'b']` while the second one will
produce `['first' => 'd', 0 => 'a', 1 => 'b']`.


### usort()

Sorts all elements using a callback without maintaining the key association.

```php
public function usort( callable $callback ) : self
```

* @param callable `$callback` Function with (itemA, itemB) parameters and returns -1 (<), 0 (=) and 1 (>)
* @return self Updated map for fluid interface

The given callback will be used to compare the values. The callback must accept
two parameters (item A and B) and must return -1 if item A is smaller than
item B, 0 if both are equal and 1 if item A is greater than item B. Both, a
method name and an anonymous function can be passed.

**Examples:**

```php
Map::from( ['a' => 'B', 'b' => 'a'] )->usort( 'strcasecmp' );
Map::from( ['a' => 'B', 'b' => 'a'] )->usort( function( $itemA, $itemB ) {
    return strtolower( $itemA ) <=> strtolower( $itemB );
} );
```

**Results:**

```php
[0 => 'a', 1 => 'B']
[0 => 'a', 1 => 'B']
```

The keys aren't preserved and elements get a new index. No new map is created.


**Performance note:**

The bigger the list, the higher the performance impact because unshift()
needs to create a new list and copies all existing elements to the new
array. Usually, it's better to push() new entries at the end and reverse()
the list afterwards:

```php
$map->push( 'a' )->push( 'b' )->reverse();
```

instead of

```php
$map->unshift( 'a' )->unshift( 'b' );
```


### values()

Resets the keys and return the values in a new map.

```php
public function values() : self
```

* @return self New map of the values

**Examples:**

```php
Map::from( ['x' => 'b', 2 => 'a', 'c'] )->values();
```

**Results:**

A new map with `[0 => 'b', 1 => 'a', 2 => 'c']` as content


### walk()

Applies the given callback to all elements.

To change the values of the Map, specify the value parameter as reference
(&$value). You can only change the values but not the keys nor the array
structure.

```php
public function walk( callable $callback, $data = null, bool $recursive = true ) : self
```

* @param callable $callback Function with (item, key, data) parameters
* @param mixed $data Arbitrary data that will be passed to the callback as third parameter
* @param bool $recursive TRUE to traverse sub-arrays recursively (default), FALSE to iterate Map elements only
* @return self Map for fluid interface

**Examples:**

```php
Map::from( ['a', 'B', ['c', 'd'], 'e'] )->walk( function( &$value ) {
    $value = strtoupper( $value );
} );
Map::from( [66 => 'B', 97 => 'a'] )->walk( function( $value, $key ) {
    echo 'ASCII ' . $key . ' is ' . $value . "\n";
} );
Map::from( [1, 2, 3] )->walk( function( &$value, $key, $data ) {
    $value = $data[$value] ?? $value;
}, [1 => 'one', 2 => 'two'] );
```

**Results:**

The first example will change the Map elements to:
```php
   ['A', 'B', ['C', 'D'], 'E']
```
The output of the second one will be:
```
  ASCII 66 is B
  ASCII 97 is a
```
The last example changes the Map elements to:
```php
  ['one', 'two', 3]
```

By default, Map elements which are arrays will be traversed recursively.
To iterate over the Map elements only, pass FALSE as third parameter.


### where()

Filters the list of elements by a given condition.

```php
public function where( string $key, string $op, $value ) : self
```

* @param string `$key` Key of the array or object to used for comparison
* @param string `$op` Operator used for comparison
* @param mixed `$value` Value used for comparison

**Examples:**

```php
Map::from( [
  ['id' => 1, 'type' => 'name'],
  ['id' => 2, 'type' => 'short'],
] )->where( 'type', '==', 'name' );

Map::from( [
  ['id' => 3, 'price' => 10],
  ['id' => 4, 'price' => 50],
] )->where( 'price', '>', 20 );

Map::from( [
  ['id' => 3, 'price' => 10],
  ['id' => 4, 'price' => 50],
] )->where( 'price', 'in', [10, 25] );

Map::from( [
  ['id' => 3, 'price' => 10],
  ['id' => 4, 'price' => 50],
] )->where( 'price', '-', [10, 100] );
```

**Results:**

```php
[['id' => 1, 'type' => 'name']]
[['id' => 4, 'price' => 50]]
[['id' => 3, 'price' => 10]]
[['id' => 3, 'price' => 10], ['id' => 4, 'price' => 50]]
```

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


### zip()

Merges the values of all arrays at the corresponding index.

```php
public function zip( $array1, ... ) : self
```

* @param array|\Traversable|\Iterator `$array1` List of values to merge with
* @return self New map of arrays

**Examples:**

```php
Map::from( [1, 2, 3] )->zip( ['one', 'two', 'three'], ['uno', 'dos', 'tres'] );
```

**Results:**

```php
[
    [1, 'one', 'uno'],
    [2, 'two', 'dos'],
    [3, 'three', 'tres'],
]
```



## Custom methods

Most of the time, it's enough to pass an anonymous function to the pipe() method
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
    return strrev( join( '-', $this->list ) );
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
elements and can also use the internal `getArray( iterable $list )` method to convert
iterable parameters (arrays, generators and objects implementing \Traversable) to
plain arrays:

```php
Map::method( 'combine', function( iterable $keys ) {
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

If you use the `map()` function or `Map::from()` to create map objects, then be
aware that this adds another function call. Using these methods for creating the
map object lasts around 1.1x resp. 1.3x compared to the time for `new Map()`.
Conclusion: Using `new Map()` is fastest and `map()` is faster than `Map::from()`.

### Populating Map vs. array

Adding an element to a Map object using `$map[] = 'a'` is ca. 5x slower than
doing the same on a plain array. This is because the method `offsetSet()` will
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

The `pipe()` method of the Map object is an exception because it receives the
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
an iterative way, you should use `reverse()` and `pop()`/`push()` instead of
`shift()` and `unshift()`:

```php
$map->reverse()->pop(); // use pop() until it returns NULL
$map->push( 'z' )->push( 'y' )->push( 'x' )->reverse(); // use push() for adding
```
