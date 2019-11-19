# Working with PHP arrays easily

Easy to use and elegant handling for PHP arrays with an array-like map object
as offered by Javascript, jQuery and Laravel Collections.

**Table of contents**

* [Why](#why)
* [List of methods](#methods)
* [Method documentation](#method-documentation)
* [Custom methods](#custom-methods)
* [Performance](#performance)

## Why

Instead of:
```
$list = [['id' => 'one', 'value' => 'value1'], ['id' => 'two', 'value' => 'value2'], null];
$list[] = ['id' => 'three', 'value' => 'value3'];    // add element
unset( $list[0] );                                   // remove element
$list = array_filter( $list );                       // remove empty values
sort( $list );                                       // sort elements
$pairs = array_column( $list, 'value', 'id' );       // create ['three' => 'value3']
$value = reset( $pairs ) ?: null;                    // return first value
```

Only use:
```
$list = [['id' => 'one', 'value' => 'value1'], ['id' => 'two', 'value' => 'value2'], null];
$value = map( $list )
    ->push( ['id' => 'three', 'value' => 'value3'] ) // add element
    ->remove( 0 )                                    // remove element
    ->filter()                                       // remove empty values
    ->sort()                                         // sort elements
    ->col( 'value', 'id' )                           // create ['three' => 'value3']
    ->first();                                       // return first value
```

Of course, you can still use:
```
$map[] = ['id' => 'three', 'value' => 'value3'];
$value = $map['three'];
```

Also, the map object enables you to do much more advanced things because you can
pass anonymous functions to a lot of methods, e.g.:
```
$map->each( function( $val, $key ) {
	echo $key . ': ' . $val;
} );
```


## Methods

* [map()](#map-function) : Creates a new map from elements
* [__construct()](#__construct) : Creates a new map
* [__call()](#__call) : Calls a custom method
* [__callStatic()](#__callstatic) : Calls a custom method statically
* [clear()](#clear) : Removes all elements
* [col()](#col) : Creates a key/value mapping
* [concat()](#concat) : Combines the elements
* [copy()](#copy) : Creates a new copy
* [count()](#count) : Returns the number of elements
* [diff()](#diff) : Returns the missing elements
* [diffAssoc()](#diffassoc) : Returns the missing elements and checks keys
* [diffKeys()](#diffkeys) : Returns the missing elements by keys
* [each()](#each) : Applies a callback to each element
* [equals()](#equals) : Tests if maps are equal
* [filter()](#filter) : Applies a filter to the map elements
* [first()](#first) : Returns the first element
* [from()](#from) : Creates a new map from passed elements
* [get()](#get) : Returns an element by key
* [getIterator()](#getiterator) : Returns an iterator for the elements
* [has()](#has) : Tests if a key exists
* [intersect()](#intersect) : Returns the shared elements
* [intersectAssoc()](#intersectassoc) : Returns the shared elements and checks keys
* [intersectKeys()](#intersectkeys) : Returns the shared elements by keys
* [isEmpty()](#isempty) : Tests if map is empty
* [keys()](#keys) : Returns the keys
* [ksort()](#ksort) : Sorts by keys
* [last()](#last) : Returns the last element
* [map()](#map) : Applies a callback to each element and returns the results
* [merge()](#merge) : Combines elements overwriting existing ones
* [method()](#method) : Registers a custom method
* [offsetExists()](#offsetexists) : Checks if the key exists
* [offsetGet()](#offsetget) : Returns an element by key
* [offsetSet()](#offsetset) : Overwrites an element
* [offsetUnset()](#offsetunset) : Removes an element by key
* [pipe()](#pipe) : Applies a callback to the map
* [pop()](#pop) : Returns and removes the last element
* [pull()](#pull) : Returns and removes an element by key
* [push()](#push) : Adds an element to the end
* [reduce()](#reduce) : Computes a value for the map content
* [remove()](#remove) : Removes an element by key
* [replace()](#replace) : Replaces elements recursively
* [reverse()](#reverse) : Reverses the array order
* [search()](#search) : Find the key of an element
* [set()](#set) : Overwrites an element
* [shift()](#shift) : Returns and removes the first element
* [shuffle()](#shuffle) : Randomizes the element order
* [slice()](#slice) : Returns a slice of the map
* [sort()](#sort) : Sorts the elements
* [splice()](#splice) : Replaces a slice by new elements
* [toArray()](#toarray) : Returns the plain array
* [union()](#union) : Combines the element without overwriting
* [unique()](#unique) : Returns unique elements
* [unshift()](#unshift) : Adds an element at the beginning
* [values()](#values) : Returns all elements with new keys


## Method documentation

### map() function

Returns a new map for the passed elements.

```php
function map( iterable $elements ) : \Aimeos\Map
```

* @param iterable `$elements` List of elements


### __construct()

Creates a new map.

```php
public function __construct( iterable $elements = [] )
```

* @param iterable `$elements` List of elements


### __call()

Handles dynamic calls to custom methods for the class.

Calls a custom method added by Map::method(). The called method
has access to the internal array by using $this->items.

```php
public function __call( string $name, array $params )
```

* @param string `$name` Method name
* @param array `$params` List of parameters
* @return mixed Result from called function
* @throws \BadMethodCallException

**Examples:**
```php
Map::method( 'foo', function( $arg1, $arg2 ) {
    return $this->items;
} );
(new Map( ['bar'] ))->foo( $arg1, $arg2 );
```


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


### clear()

Removes all elements from the current map.

```php
public function clear() : self
```

* @return self Same map for fluid interface


### col()

Returns the values of a single column/property from an array of arrays or list of elements in a new map.

```php
public function col( string $valuecol, $indexcol = null ) : self
```

* @param string `$valuecol` Name of the value property
* @param string|null `$indexcol` Name of the index property
* @return self New instance with mapped entries

**Examples:**
```php
Map::from( [['id' => 'i1', 'val' => 'v1'], ['id' => 'i2', 'val' => 'v2']] )->col( 'val', 'id' );
```

**Results:**
```php
['i1' => 'v1', 'i2' => 'v2']
```

If `$indexcol` is omitted, the result will be indexed from 0-n.
The col() method works for objects implementing the __isset() and __get() methods too.


### concat()

Pushs all of the given elements onto the map without creating a new map.

```php
public function concat( iterable $elements ) : self
```

* @param iterable `$elements` List of elements
* @return self Updated map for fluid interface

**Examples:**
```php
Map::from( ['foo'] )->concat( new Map( ['bar] ));
```

**Results:**
```php
['foo', 'bar']
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


### diff()

Returns the keys/values in the map whose values are not present in the passed elements in a new map.

```php
public function diff( iterable $elements, callable $callback = null ) : self
```

* @param iterable `$elements` List of elements
* @param  callable|null `$callback` Function with (valueA, valueB) parameters and returns -1 (<), 0 (=) and 1 (>)
* @return self New map

**Examples:**
```php
Map::from( ['a' => 'foo', 'b' => 'bar] )->diff( ['bar'] );
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
* @param  callable|null `$callback` Function with (valueA, valueB) parameters and returns -1 (<), 0 (=) and 1 (>)
* @return self New map

**Examples:**
```php
Map::from( ['a' => 'foo', 'b' => 'bar] )->diffAssoc( new Map( ['foo', 'b' => 'bar'] ) );
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
* @param  callable|null `$callback` Function with (keyA, keyB) parameters and returns -1 (<), 0 (=) and 1 (>)
* @return self New map

**Examples:**
```php
Map::from( ['a' => 'foo', 'b' => 'bar] )->diffKeys( new Map( ['foo', 'b' => 'baz'] ) );
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


### each()

Executes a callback over each entry until FALSE is returned.

```php
public function each( callable $callback ) : self
```

* @param callable `$callback` Function with (value, key) parameters and returns TRUE/FALSE
* @return self Same map for fluid interface

**Examples:**
```php
$result = [];
Map::from( [0 => 'a', 1 => 'b'] )->each( function( $value, $key ) use ( &$result ) {
    $result[$key] = strtoupper( $value );
    return false;
} );
```

The `$result` array will contain [0 => 'A'] because FALSE is returned
after the first entry and all other entries are then skipped.


### equals()

Tests if the passed elements are equal to the elements in the map.

```php
public function equals( iterable $elements, $assoc = false ) : bool
```

* @param iterable `$elements` List of elements to test against
* @param bool `$assoc` TRUE to compare keys too, FALSE to compare only values
* @return bool TRUE if both are equal, FALSE if not

**Examples:**
```php
Map::from( ['a'] )->equals( ['a', 'b'] );
Map::from( ['a', 'b'] )->equals( ['b'] );
Map::from( ['a', 'b'] )->equals( ['b', 'a'] );
```

**Results:**
The first and second example will return FALSE, the third example will return TRUE

If the second parameter is TRUE, keys are compared too:

```php
Map::from( [0 => 'a'] )->equals( [1 => 'a'], true );
Map::from( [1 => 'a'] )->equals( [0 => 'a'], true );
Map::from( [0 => 'a'] )->equals( [0 => 'a'], true );
```

The first and second example above will also return FALSE and only the third
example will return TRUE

Keys and values are compared by their string values:
```php
(string) $item1 === (string) $item2
```


### filter()

Runs a filter over each element of the map and returns a new map.

```php
public function filter( callable $callback = null ) : self
```

* @param  callable|null `$callback` Function with (item) parameter and returns TRUE/FALSE
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
removed if their value converted to boolean is FALSE:
```php
(bool) $value === false
```



### first()

Returns the first element from the map passing the given truth test.

```php
public function first( callable $callback = null, $default = null )
```

* @param callable|null `$callback` Function with (value, key) parameters and returns TRUE/FALSE
* @param mixed `$default` Default value if no element matches
* @return mixed First value of map or default value

**Examples:**
```php
Map::from( ['a', 'b'] )->first();
Map::from( ['a', 'c', 'e'] )->first( function( $value, $key ) {
    return `$value` >= 'b';
} );
Map::from( [] )->first( null, 'x' );
```

Result:
The first example will return 'a', the second 'c' and the third 'x'.


### from()

Creates a new map instance if the value isn't one already.

```php
public static function from( iterable $elements = [] ) : self
```

* @param iterable `$elements` List of elements
* @return self New map

**Examples:**
```php
Map::from( [] );
Map::from( new Map() );
Map::from( new ArrayObject() );
```


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
```

**Results:**
The first example will return 'X', the second 'Z'


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


### has()

Determines if an element exists in the map by its key.

```php
public function has( $key ) : bool
```

* @param mixed `$key` Key of the requested item
* @return bool TRUE if key is available in map, FALSE if not

**Examples:**
```php
Map::from( ['a' => 'X', 'b' => 'Y'] )->has( 'a' );
Map::from( ['a' => 'X', 'b' => 'Y'] )->has( 'c' );
Map::from( ['a' => 'X', 'b' => 'Y'] )->has( 'X' );
```

**Results:**
The first example will return TRUE while the second and third one will return FALSE


### in()

Tests if the passed element is part of the map.

```php
public function in( $element, bool $strict = false ) : bool
```

* @param mixed $element Element to search for in the map
* @param bool $strict TRUE to check the type too, using FALSE '1' and 1 will be the same
* @return bool TRUE if element is available in map, FALSE if not

**Examples:**
```php
Map::from( ['a', 'b'] )->in( 'a' );
Map::from( ['a', 'b'] )->in( 'x' );
Map::from( ['1', '2'] )->in( 2, true );
```

**Results:**
The first example will return TRUE while the second and third one will return FALSE


### intersect()

Returns all values in a new map that are available in both, the map and the given elements.

```php
public function intersect( iterable $elements, callable $callback = null ) : self
```

* @param iterable `$elements` List of elements
* @param  callable|null `$callback` Function with (valueA, valueB) parameters and returns -1 (<), 0 (=) and 1 (>)
* @return self New map

**Examples:**
```php
Map::from( ['a' => 'foo', 'b' => 'bar] )->intersect( ['bar'] );
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
* @param  callable|null `$callback` Function with (valueA, valueB) parameters and returns -1 (<), 0 (=) and 1 (>)
* @return self New map

**Examples:**
```php
Map::from( ['a' => 'foo', 'b' => 'bar] )->intersectAssoc( new Map( ['foo', 'b' => 'bar'] ) );
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
* @param  callable|null `$callback` Function with (keyA, keyB) parameters and returns -1 (<), 0 (=) and 1 (>)
* @return self New map

**Examples:**
```php
Map::from( ['a' => 'foo', 'b' => 'bar] )->intersectKeys( new Map( ['foo', 'b' => 'baz'] ) );
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


### isEmpty()

Determines if the map is empty or not.

```php
public function isEmpty() : bool
```

* @return bool TRUE if map is empty, FALSE if not

**Examples:**
```php
Map::from( [] );
Map::from( ['a'] );
```

**Results:**
The first example returns TRUE while the second returns FALSE


### join()

Concatenates the string representation of all elements.

Objects that implement __toString() does also work, otherwise (and in case
of arrays) a PHP notice is generated. NULL and FALSE values are treated as
empty strings.

```php
public function join( $glue = '' ) : string
```

* @param mixed $element Element to search for in the map
* @param bool $strict TRUE to check the type too, using FALSE '1' and 1 will be the same
* @return bool TRUE if element is available in map, FALSE if not

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
The first example returns a map containing [0, 1] while the second one will
return a map with ['a', 'b'].


### ksort()

Sorts the map elements by their keys without creating a new map.

```php
public function ksort( callable $callback = null, int $options = SORT_REGULAR ) : self
```

* @param callable|null `$callback` Function with (keyA, keyB) parameters and returns -1 (<), 0 (=) and 1 (>)
* @param int `$options` Sort options for ksort()
* @return self Updated map for fluid interface

**Examples:**
```php
Map::from( ['b' => 0, 'a' => 1] )->ksort();
Map::from( [1 => 'a', 0 => 'b'] )->ksort();
```

**Results:**
The first example will sort the map elements to ['a' => 1, 'b' => 0] while the second
one will sort the map entries to [0 => 'b', 1 => 'a'].

If a callback is passed, the given function will be used to compare the keys.
The function must accept two parameters (key A and B) and must return
-1 if key A is smaller than key B, 0 if both are equal and 1 if key A is
greater than key B. Both, a method name and an anonymous function can be passed:

```php
Map::from( ['b' => 'a', 'a' => 'b'] )->ksort( 'strcasecmp' );
Map::from( ['b' => 'a', 'a' => 'b'] )->ksort( function( $keyA, $keyB ) {
    return strtolower( $keyA ) <=> strtolower( $keyB );
} );
```

Both examples will re-sort the entries to ['a' => 'b', 'b' => 'a']. The third
parameter modifies how the keys are compared. Possible values are:
- SORT_REGULAR : compare elements normally (don't change types)
- SORT_NUMERIC : compare elements numerically
- SORT_STRING : compare elements as strings
- SORT_LOCALE_STRING : compare elements as strings, based on the current locale or changed by setlocale()
- SORT_NATURAL : compare elements as strings using "natural ordering" like natsort()
- SORT_FLAG_CASE : use SORT_STRING|SORT_FLAG_CASE and SORT_NATURALSORT_FLAG_CASE to sort strings case-insensitively


### last()

Returns the last element from the map.

```php
public function last( callable $callback = null, $default = null )
```

* @param callable|null `$callback` Function with (item, key) parameters and returns TRUE/FALSE
* @param mixed `$default` Default value if no element matches
* @return mixed Last value of map or default value

**Examples:**
```php
Map::from( ['a', 'b'] )->last();
Map::from( ['a', 'c', 'e'] )->last( function( $value, $key ) {
    return $value < 'd';
} );
Map::from( [] )->last( null, 'x' );
```

Result:
The first example will return 'b', the second 'c' and the third 'x'.


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


### merge()

Merges the map with the given elements without returning a new map.
Items with the same keys will be overwritten

```php
public function merge( iterable $elements ) : self
```

* @param iterable `$elements` List of elements
* @return self Updated map for fluid interface

**Examples:**
```php
Map::from( ['a', 'b'] )->merge( ['b', 'c'] );
Map::from( ['a' => 1, 'b' => 2] )->merge( ['b' => 4, 'c' => 6] );
```

**Results:**
```php
['a', 'b', 'b', 'c']
['a' => 1, 'b' => 4, 'c' => 6]
```


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

Access to the class properties:
```php
(new Map( ['bar'] ))->foo( $arg1, $arg2 );
```

Error because $this->elements isn't available:
```php
Map::foo( $arg1, $arg2 );
```


### offsetExists()

Determines if an element exists at an offset.

```php
public function offsetExists( $key )
```

* @param mixed `$key` Key to check for
* @return bool TRUE if key exists, FALSE if not

**Examples:**
```php
$map = Map::from( ['a' => 1, 'b' => 3] );
isset( $map['b'] );
isset( $map['c'] );
```

**Results:**
The first `isset()` will return TRUE while the second one will return FALSE


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


### pipe()

Passes the map to the given callback and return the result.

```php
public function pipe( callable $callback )
```

* @param callable `$callback` Function with map as parameter which returns arbitrary result
* @return mixed Result returned by the callback

**Examples:**
```php
Map::from( ['a', 'b'] )->pipe( function( $map ) {
    return join( '-', $map->toArray() );
} );
```

**Results:**
"a-b" will be returned


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

* @param string|int|iterable `$keys` List of keys
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

Recursively replaces elements in the map with the given elements without returning a new map.

```php
public function replace( iterable $elements ) : self
```

* @param iterable `$elements` List of elements
* @return self Updated map for fluid interface

**Examples:**
```php
Map::from( ['a' => 1, 2 => 'b'] )->replace( ['a' => 2] );
Map::from( ['a' => 1, 'b' => ['c' => 3, 'd' => 4]] )->replace( ['b' => ['c' => 9]] );
```

**Results:**
The first example will result in `['a' => 2, 2 => 'b']` while the second one
will produce `['a' => 1, 'b' => ['c' => 9, 'd' => 4]]`.


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


### search()

Searches the map for a given value and return the corresponding key if successful.

```php
public function search( $value, $strict = true )
```

* @param mixed `$value` Item to search for
* @param bool `$strict` TRUE if type of the element should be checked too
* @return mixed|null Value from map or null if not found

**Examples:**
```php
Map::from( ['a', 'b', 'c'] )->search( 'b' );
Map::from( [1, 2, 3] )->search( '2', true );
```

**Results:**
The first example will return 1 (array index) while the second one will
return NULL because the types doesn't match (int vs. string)


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

* @return mixed|null Value from map or null if not found

**Examples:**
```php
Map::from( ['a', 'b'] )->shift();
Map::from( [] )->shift();
```

**Results:**
The first example returns "a" and shortens the map to ['b'] only while the
second example will return NULL

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
public function shuffle() : self
```

* @return self Updated map for fluid interface

**Examples:**
```php
Map::from( [2 => 'a', 4 => 'b'] )->shuffle();
```

**Results:**
The map will contain "a" and "b" in random order and with new keys assigned


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


### sort()

Sorts all elements using a callback without returning a new map.

```php
public function sort( callable $callback = null, int $options = SORT_REGULAR ) : self
```

* @param callable|null `$callback` Function with (itemA, itemB) parameters and returns -1 (<), 0 (=) and 1 (>)
* @param int `$options` Sort options for asort()
* @return self Updated map for fluid interface

**Examples:**
```php
Map::from( ['a' => 1, 'b' => 0] )->sort();
Map::from( [0 => 'b', 1 => 'a'] )->sort();
```

**Results:**
The first example will sort the map elements to ['b' => 0, 'a' => 1] while the second
one will sort the map entries to [1 => 'a', 0 => 'b'].

If a callback is passed, the given function will be used to compare the values.
The function must accept two parameters (key A and B) and must return
-1 if key A is smaller than key B, 0 if both are equal and 1 if key A is
greater than key B. Both, a method name and an anonymous function can be passed:

```php
Map::from( ['b' => 'a', 'a' => 'B'] )->sort( 'strcasecmp' );
Map::from( ['b' => 'a', 'a' => 'B'] )->sort( function( $keyA, $keyB ) {
    return strtolower( $keyA ) <=> strtolower( $keyB );
} );
```

Both examples will re-sort the entries to ['a' => 'B', 'b' => 'a'] because
the ASCII value for "B" is smaller than for "a".

The third parameter modifies how the values are compared. Possible parameter values are:
- SORT_REGULAR : compare elements normally (don't change types)
- SORT_NUMERIC : compare elements numerically
- SORT_STRING : compare elements as strings
- SORT_LOCALE_STRING : compare elements as strings, based on the current locale or changed by setlocale()
- SORT_NATURAL : compare elements as strings using "natural ordering" like natsort()
- SORT_FLAG_CASE : use SORT_STRING|SORT_FLAG_CASE and SORT_NATURALSORT_FLAG_CASE to sort strings case-insensitively

The keys are preserved using this method with and without callback function.


### splice()

Removes a portion of the map and replace it with the given replacement, then return the updated map.

```php
public function splice( int $offset, int $length = null, $replacement = [] ) : self
```

* @param int `$offset` Number of elements to start from
* @param int|null `$length` Number of elements to remove
* @param mixed `$replacement` List of elements to insert
* @return self New map

**Examples:**
```php
Map::from( ['a', 'b', 'c'] )->slice( 1 );
Map::from( ['a', 'b', 'c'] )->slice( 1, 1, ['x', 'y'] );
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


### toArray()

Returns the elements as a plain array.

```php
public function toArray() : array
```

* @return array Plain array


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

Two elements are condidered equal if comparing their string representions returns TRUE:
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
Map::from( ['a', 'b', 'b', 'c'] )->unique();
```

**Results:**
A new map with `['a', 'b', 'c']` as content


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
    return strrev( join( '-', $this->items ) );
} );
Map::from( ['a', 'b'] )->strrev( '-' );
```

Make sure, you register the method before using it. You can pass arbitrary parameters
to your function and it has access to the internas of the map. Thus, your function
can use `$this` to call all available methods:

```php
Map::method( 'notInBoth', function( iterable $elements ) {
    return new self( $elements->diff( $this->diff( $elements ) ) );
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
aware that this adds another method call. Using these methods for creating the
map object lasts around 1.5x compared to the time for `new Map()` only. Also,
`Map::from()` is faster than `map()`.

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
compared to `$map->empty()`, the costs are around 4ms in total.

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
$map->reverse()->pop(); // until pop() returns NULL
$map->push( 'z' )->push( 'y' )->push( 'x' )->reverse();
```
