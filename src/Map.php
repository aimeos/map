<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 * @author Taylor Otwell, Aimeos.org developers
 */


namespace Aimeos;


/**
 * Handling and operating on a list of items easily
 * Inspired by Laravel Collection class, PHP map data structure and Javascript
 */
class Map implements \ArrayAccess, \Countable, \IteratorAggregate
{
	protected static $methods = [];
	protected $items = [];


	/**
	 * Creates a new map.
	 *
	 * @param iterable $items List of items
	 */
	public function __construct( iterable $items = [] )
	{
		$this->items = $this->getArray( $items );
	}


	/**
	 * Handles static calls to custom methods for the class.
	 *
	 * Calls a custom method added by Map::method() statically. The called method
	 * has no access to the internal array because no object is available.
	 *
	 * Examples:
	 *  Map::method( 'foo', function( $arg1, $arg2 ) {} );
	 *  Map::foo( $arg1, $arg2 );
	 *
	 * @param string $name Method name
	 * @param array $params List of parameters
	 * @return mixed Result from called function
	 *
	 * @throws \BadMethodCallException
	 */
	public static function __callStatic( string $name, array $params )
	{
		if( !isset( static::$methods[$name] ) ) {
			throw new \BadMethodCallException( sprintf( 'Method %s::%s does not exist.', static::class, $name ) );
		}

		return call_user_func_array( \Closure::bind( static::$methods[$name], null, static::class ), $params );
	}


	/**
	 * Handles dynamic calls to custom methods for the class.
	 *
	 * Calls a custom method added by Map::method(). The called method
	 * has access to the internal array by using $this->items.
	 *
	 * Examples:
	 *  Map::method( 'case', function( $case = CASE_LOWER ) {
	 *      return new self( array_change_key_case( $this->items, $case ) );
	 *  } );
	 *  Map::from( ['a' => 'bar'] )->case( CASE_UPPER );
	 *
	 *  $item = new MyClass(); // with method setId() and getCode()
	 *  Map::from( [$item, $item] )->setId( null )->getCode();
	 *
	 * Results:
	 * The first example will return ['A' => 'bar'].
	 *
	 * The second one will call the setId() method of each element in the map and use
	 * their return values to create a new map. On the new map, the getCode() method
	 * is called for every element and its return values are also stored in a new map.
	 * This last map is then returned.
	 * If this applies to all elements, an empty map is returned. The map keys from the
	 * original map are preserved in the returned map.
	 *
	 * @param string $name Method name
	 * @param array $params List of parameters
	 * @return mixed|self Result from called function or map with results from the element methods
	 */
	public function __call( string $name, array $params )
	{
		if( isset( static::$methods[$name] ) ) {
			return call_user_func_array( static::$methods[$name]->bindTo( $this, static::class ), $params );
		}

		$result = [];

		foreach( $this->items as $key => $item )
		{
			if( is_object( $item ) && method_exists( $item, $name ) ) {
				$result[$key] = $item->{$name}( ...$params );
			}
		}

		return new self( $result );
	}


	/**
	 * Creates a new map instance if the value isn't one already.
	 *
	 * Examples:
	 *  Map::from( [] );
	 *  Map::from( new Map() );
	 *  Map::from( new ArrayObject() );
	 *
	 * @param iterable $items List of items
	 * @return self New map
	 */
	public static function from( iterable $items = [] ) : self
	{
		return new static( $items );
	}


	/**
	 * Registers a custom method that has access to the class properties if called non-static.
	 *
	 * Examples:
	 *  Map::method( 'foo', function( $arg1, $arg2 ) {
	 *      return $this->items;
	 *  } );
	 *
	 * Dynamic calls have access to the class properties:
	 *  (new Map( ['bar'] ))->foo( $arg1, $arg2 );
	 *
	 * Static calls yield an error because $this->elements isn't available:
	 *  Map::foo( $arg1, $arg2 );
	 *
	 * @param string $name Method name
	 * @param \Closure $function Anonymous method
	 */
	public static function method( string $name, \Closure $function )
	{
		static::$methods[$name] = $function;
	}


	/**
	 * Chunks the map into arrays with the given number of elements.
	 *
	 * Examples:
	 *  Map::from( [0, 1, 2, 3, 4] )->chunk( 3 );
	 *  Map::from( ['a' => 0, 'b' => 1, 'c' => 2] )->chunk( 2 );
	 *
	 * Results:
	 *  [[0, 1, 2], [3, 4]]
	 *  [['a' => 0, 'b' => 1], ['c' => 2]]
	 *
	 * The last chunk may contain less elements than the given number.
	 *
	 * The sub-arrays of the returned map are plain PHP arrays. If you need Map
	 * objects, then wrap them with Map::from() when you iterate over the map.
	 *
	 * @param int $size Maximum size of the sub-arrays
	 * @param bool $preserve Preserve keys in new map
	 * @return self New map with elements chunked in sub-arrays
	 * @throws \InvalidArgumentException If size is smaller than 1
	 */
	public function chunk( int $size, bool $preserve = false ) : self
	{
		if( $size < 1 ) {
			throw new \InvalidArgumentException( 'Chunk size must be greater or equal than 1' );
		}

		return new static( array_chunk( $this->items, $size, $preserve ) );
	}


	/**
	 * Removes all items from the current map.
	 *
	 * @return self Same map for fluid interface
	 */
	public function clear() : self
	{
		$this->items = [];
		return $this;
	}


	/**
	 * Returns the values of a single column/property from an array of arrays or list of items in a new map.
	 *
	 * Examples:
	 *  Map::from( [['id' => 'i1', 'val' => 'v1'], ['id' => 'i2', 'val' => 'v2']] )->col( 'val', 'id' );
	 *
	 * Results:
	 *  ['i1' => 'v1', 'i2' => 'v2']
	 *
	 * If $indexcol is omitted, the result will be indexed from 0-n.
	 * The col() method works for objects implementing the __isset() and __get() methods too.
	 *
	 * @param string $valuecol Name of the value property
	 * @param string|int|null $indexcol Name of the index property
	 * @return self New instance with mapped entries
	 */
	public function col( string $valuecol, $indexcol = null ) : self
	{
		return new static( array_column( $this->items, $valuecol, $indexcol ) );
	}


	/**
	 * Collapses all sub-array elements recursively to a new map.
	 *
	 * Examples:
	 *  Map::from( [0 => ['a' => 0, 'b' => 1], 1 => ['c' => 2, 'd' => 3]] )->collapse();
	 *  Map::from( [0 => ['a' => 0, 'b' => 1], 1 => ['a' => 2]] )->collapse();
	 *  Map::from( [0 => [0 => 0, 1 => 1], 1 => [0 => ['a' => 2, 0 => 3], 1 => 4]] )->collapse();
	 *  Map::from( [0 => [0 => 0, 'a' => 1], 1 => [0 => ['b' => 2, 0 => 3], 1 => 4]] )->collapse( 1 );
	 *  Map::from( [0 => [0 => 0, 'a' => 1], 1 => Map::from( [0 => ['b' => 2, 0 => 3], 1 => 4] )] )->collapse();
	 *
	 * Results:
	 *  ['a' => 0, 'b' => 1, 'c' => 2, 'd' => 3]
	 *  ['a' => 2, 'b' => 1]
	 *  [0 => 3, 1 => 4, 'a' => 2]
	 *  [0 => ['b' => 2, 0 => 3], 1 => 4, 'a' => 1]
	 *  [0 => 3, 'a' => 1, 'b' => 2, 1 => 4]
	 *
	 * The keys are preserved and already existing elements will be overwritten.
	 * This is also true for numeric keys! A value smaller than 1 for depth will
	 * return the same map elements. Collapsing does also work if elements
	 * implement the "Traversable" interface (which the Map object does).
	 *
	 * This method is similar than flat() but replaces already existing elements.
	 *
	 * @param int|null $depth Number of levels to collapse for multi-dimensional arrays or NULL for all
	 * @return self New map with all sub-array elements added into it recursively, up to the specified depth
	 * @throws \InvalidArgumentException If depth must be greater or equal than 0 or NULL
	 */
	public function collapse( int $depth = null ) : self
	{
		if( $depth < 0 ) {
			throw new \InvalidArgumentException( 'Depth must be greater or equal than 0 or NULL' );
		}

		$result = [];
		$this->kflatten( $this->items, $result, $depth ?? INF );
		return new self( $result );
	}


	/**
	 * Pushs all of the given items onto the map without creating a new map.
	 *
	 * Examples:
	 *  Map::from( ['foo'] )->concat( new Map( ['bar] ));
	 *
	 * Results:
	 *  ['foo', 'bar']
	 *
	 * @param iterable $items List of items
	 * @return self Updated map for fluid interface
	 */
	public function concat( iterable $items ) : self
	{
		foreach( $items as $item ) {
			$this->items[] = $item;
		}

		return $this;
	}


	/**
	 * Creates a new map with the same items.
	 *
	 * Both maps share the same array until one of the map objects modifies the
	 * array. Then, the array is copied and the copy is modfied (copy on write).
	 *
	 * @return self New map
	 */
	public function copy() : self
	{
		return new static( $this->items );
	}


	/**
	 * Counts the number of items in the map.
	 *
	 * @return int Number of items
	 */
	public function count() : int
	{
		return count( $this->items );
	}


	/**
	 * Returns the keys/values in the map whose values are not present in the passed items in a new map.
	 *
	 * Examples:
	 *  Map::from( ['a' => 'foo', 'b' => 'bar] )->diff( ['bar'] );
	 *
	 * Results:
	 *  ['a' => 'foo']
	 *
	 * If a callback is passed, the given function will be used to compare the values.
	 * The function must accept two parameters (value A and B) and must return
	 * -1 if value A is smaller than value B, 0 if both are equal and 1 if value A is
	 * greater than value B. Both, a method name and an anonymous function can be passed:
	 *
	 *  Map::from( [0 => 'a'] )->diff( [0 => 'A'], 'strcasecmp' );
	 *  Map::from( ['b' => 'a'] )->diff( ['B' => 'A'], 'strcasecmp' );
	 *  Map::from( ['b' => 'a'] )->diff( ['c' => 'A'], function( $valA, $valB ) {
	 *      return strtolower( $valA ) <=> strtolower( $valB );
	 *  } );
	 *
	 * All examples will return an empty map because both contain the same values
	 * when compared case insensitive.
	 *
	 * @param iterable $items List of items
	 * @param  callable|null $callback Function with (valueA, valueB) parameters and returns -1 (<), 0 (=) and 1 (>)
	 * @return self New map
	 */
	public function diff( iterable $items, callable $callback = null ) : self
	{
		if( $callback ) {
			return new static( array_udiff( $this->items, $this->getArray( $items ), $callback ) );
		}

		return new static( array_diff( $this->items, $this->getArray( $items ) ) );
	}


	/**
	 * Returns the keys/values in the map whose keys and values are not present in the passed items in a new map.
	 *
	 * Examples:
	 *  Map::from( ['a' => 'foo', 'b' => 'bar] )->diffAssoc( new Map( ['foo', 'b' => 'bar'] ) );
	 *
	 * Results:
	 *  ['a' => 'foo']
	 *
	 * If a callback is passed, the given function will be used to compare the values.
	 * The function must accept two parameters (value A and B) and must return
	 * -1 if value A is smaller than value B, 0 if both are equal and 1 if value A is
	 * greater than value B. Both, a method name and an anonymous function can be passed:
	 *
	 *  Map::from( [0 => 'a'] )->diffAssoc( [0 => 'A'], 'strcasecmp' );
	 *  Map::from( ['b' => 'a'] )->diffAssoc( ['B' => 'A'], 'strcasecmp' );
	 *  Map::from( ['b' => 'a'] )->diffAssoc( ['c' => 'A'], function( $valA, $valB ) {
	 *      return strtolower( $valA ) <=> strtolower( $valB );
	 *  } );
	 *
	 * The first example will return an empty map because both contain the same
	 * values when compared case insensitive. The second and third example will return
	 * an empty map because 'A' is part of the passed array but the keys doesn't match
	 * ("b" vs. "B" and "b" vs. "c").
	 *
	 * @param iterable $items List of items
	 * @param  callable|null $callback Function with (valueA, valueB) parameters and returns -1 (<), 0 (=) and 1 (>)
	 * @return self New map
	 */
	public function diffAssoc( iterable $items, callable $callback = null ) : self
	{
		if( $callback ) {
			return new static( array_diff_uassoc( $this->items, $this->getArray( $items ), $callback ) );
		}

		return new static( array_diff_assoc( $this->items, $this->getArray( $items ) ) );
	}


	/**
	 * Returns the key/value pairs from the map whose keys are not present in the passed items in a new map.
	 *
	 * Examples:
	 *  Map::from( ['a' => 'foo', 'b' => 'bar] )->diffKeys( new Map( ['foo', 'b' => 'baz'] ) );
	 *
	 * Results:
	 *  ['a' => 'foo']
	 *
	 * If a callback is passed, the given function will be used to compare the keys.
	 * The function must accept two parameters (key A and B) and must return
	 * -1 if key A is smaller than key B, 0 if both are equal and 1 if key A is
	 * greater than key B. Both, a method name and an anonymous function can be passed:
	 *
	 *  Map::from( [0 => 'a'] )->diffKeys( [0 => 'A'], 'strcasecmp' );
	 *  Map::from( ['b' => 'a'] )->diffKeys( ['B' => 'X'], 'strcasecmp' );
	 *  Map::from( ['b' => 'a'] )->diffKeys( ['c' => 'a'], function( $keyA, $keyB ) {
	 *      return strtolower( $keyA ) <=> strtolower( $keyB );
	 *  } );
	 *
	 * The first and second example will return an empty map because both contain
	 * the same keys when compared case insensitive. The third example will return
	 * ['b' => 'a'] because the keys doesn't match ("b" vs. "c").
	 *
	 * @param iterable $items List of items
	 * @param  callable|null $callback Function with (keyA, keyB) parameters and returns -1 (<), 0 (=) and 1 (>)
	 * @return self New map
	 */
	public function diffKeys( iterable $items, callable $callback = null ) : self
	{
		if( $callback ) {
			return new static( array_diff_ukey( $this->items, $this->getArray( $items ), $callback ) );
		}

		return new static( array_diff_key( $this->items, $this->getArray( $items ) ) );
	}


	/**
	 * Executes a callback over each entry until FALSE is returned.
	 *
	 * Examples:
	 *  $result = [];
	 *  Map::from( [0 => 'a', 1 => 'b'] )->each( function( $value, $key ) use ( &$result ) {
	 *      $result[$key] = strtoupper( $value );
	 *      return false;
	 *  } );
	 *
	 * The $result array will contain [0 => 'A'] because FALSE is returned
	 * after the first entry and all other entries are then skipped.
	 *
	 * @param \Closure $callback Function with (value, key) parameters and returns TRUE/FALSE
	 * @return self Same map for fluid interface
	 */
	public function each( \Closure $callback ) : self
	{
		foreach( $this->items as $key => $item )
		{
			if( $callback( $item, $key ) === false ) {
				break;
			}
		}

		return $this;
	}


	/**
	 * Tests if the passed items are equal to the items in the map.
	 *
	 * Examples:
	 *  Map::from( ['a'] )->equals( ['a', 'b'] );
	 *  Map::from( ['a', 'b'] )->equals( ['b'] );
	 *  Map::from( ['a', 'b'] )->equals( ['b', 'a'] );
	 *
	 * Results:
	 * The first and second example will return FALSE, the third example will return TRUE
	 *
	 * If the second parameter is TRUE, keys are compared too:

	 *  Map::from( [0 => 'a'] )->equals( [1 => 'a'], true );
	 *  Map::from( [1 => 'a'] )->equals( [0 => 'a'], true );
	 *  Map::from( [0 => 'a'] )->equals( [0 => 'a'], true );
	 *
	 * The first and second example above will also return FALSE and only the third
	 * example will return TRUE
	 *
	 * Keys and values are compared by their string values:
	 * (string) $item1 === (string) $item2
	 *
	 * @param iterable $items List of items to test against
	 * @param bool $assoc TRUE to compare keys too, FALSE to compare only values
	 * @return bool TRUE if both are equal, FALSE if not
	 */
	public function equals( iterable $items, $assoc = false ) : bool
	{
		$items = $this->getArray( $items );

		if( $assoc ) {
			return array_diff_assoc( $this->items, $items ) === [] && array_diff_assoc( $items, $this->items ) === [];
		}

		return array_diff( $this->items, $items ) === [] && array_diff( $items, $this->items ) === [];
	}


	/**
	 * Runs a filter over each element of the map and returns a new map.
	 *
	 * Examples:
	 *  Map::from( [2 => 'a', 6 => 'b', 13 => 'm', 30 => 'z'] )->filter( function( $value, $key ) {
	 *      return $key < 10 && $value < 'n';
	 *  } );
	 *
	 * Results:
	 *  ['a', 'b']
	 *
	 * If no callback is passed, all values which are empty, null or false will be
	 * removed if their value converted to boolean is FALSE:
	 *  (bool) $value === false
	 *
	 * @param  callable|null $callback Function with (item) parameter and returns TRUE/FALSE
	 * @return self New map
	 */
	public function filter( callable $callback = null ) : self
	{
		if( $callback ) {
			return new static( array_filter( $this->items, $callback, ARRAY_FILTER_USE_BOTH ) );
		}

		return new static( array_filter( $this->items ) );
	}



	/**
	 * Returns the first element from the map passing the given truth test.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b'] )->first();
	 *  Map::from( ['a', 'b'] )->first( 'x' );
	 *  Map::from( ['a', 'c', 'e'] )->first( null, function( $value, $key ) {
	 *      return $value >= 'b';
	 *  } );
	 *
	 * Result:
	 * The first example will return 'a', the second 'x' and the third 'c'.
	 *
	 * @param mixed $default Default value if no element matches
	 * @param \Closure|null $callback Function with (value, key) parameters and returns TRUE/FALSE
	 * @return mixed First value of map or default value
	 */
	public function first( $default = null, \Closure $callback = null )
	{
		if( $callback )
		{
			foreach( $this->items as $key => $value )
			{
				if( $callback( $value, $key ) ) {
					return $value;
				}
			}

			return $default;
		}

		return reset( $this->items ) ?: $default;
	}


	/**
	 * Creates a new map with all sub-array elements added recursively
	 *
	 * Examples:
	 *  Map::from( [[0, 1], [2, 3]] )->flat();
	 *  Map::from( [[0, 1], [[2, 3], 4]] )->flat();
	 *  Map::from( [[0, 1], [[2, 3], 4]] )->flat( 1 );
	 *  Map::from( [[0, 1], Map::from( [[2, 3], 4] )] )->flat();
	 *
	 * Results:
	 *  [0, 1, 2, 3]
	 *  [0, 1, 2, 3, 4]
	 *  [0, 1, [2, 3], 4]
	 *  [0, 1, 2, 3, 4]
	 *
	 * The keys are not preserved and the new map elements will be numbered from
	 * 0-n. A value smaller than 1 for depth will return the same map elements
	 * indexed from 0-n. Flattening does also work if elements implement the
	 * "Traversable" interface (which the Map object does).
	 *
	 * This method is similar than collapse() but doesn't replace existing elements.
	 *
	 * @param int|null $depth Number of levels to flatten multi-dimensional arrays or NULL for all
	 * @return self New map with all sub-array elements added into it recursively, up to the specified depth
	 * @throws \InvalidArgumentException If depth must be greater or equal than 0 or NULL
	 */
	public function flat( int $depth = null ) : self
	{
		if( $depth < 0 ) {
			throw new \InvalidArgumentException( 'Depth must be greater or equal than 0 or NULL' );
		}

		$result = [];
		$this->flatten( $this->items, $result, $depth ?? INF );
		return new self( $result );
	}


	/**
	 * Exchanges the keys with their values and vice versa.
	 *
	 * Examples:
	 *  Map::from( ['a' => 'X', 'b' => 'Y'] )->flip();
	 *
	 * Results:
	 *  ['X' => 'a', 'Y' => 'b']
	 *
	 * @return self New map with keys as values and values as keys
	 */
	public function flip() : self
	{
		return new self( array_flip( $this->items ) );
	}


	/**
	 * Returns an element from the map by key.
	 *
	 * Examples:
	 *  Map::from( ['a' => 'X', 'b' => 'Y'] )->get( 'a' );
	 *  Map::from( ['a' => 'X', 'b' => 'Y'] )->get( 'c', 'Z' );
	 *
	 * Results:
	 * The first example will return 'X', the second 'Z'
	 *
	 * @param mixed $key Key of the requested item
	 * @param mixed $default Default value if no element matches
	 * @return mixed Value from map or default value
	 */
	public function get( $key, $default = null )
	{
		return array_key_exists( $key, $this->items ) ? $this->items[$key] : $default;
	}


	/**
	 * Returns an iterator for the items.
	 *
	 * This method will be used by e.g. foreach() to loop over all entries:
	 *  foreach( Map::from( ['a', 'b'] ) as $value )
	 *
	 * @return \Iterator Over map items
	 */
	public function getIterator() : \Iterator
	{
		return new \ArrayIterator( $this->items );
	}


	/**
	 * Determines if an element exists in the map by its key.
	 *
	 * Examples:
	 *  Map::from( ['a' => 'X', 'b' => 'Y'] )->has( 'a' );
	 *  Map::from( ['a' => 'X', 'b' => 'Y'] )->has( 'c' );
	 *  Map::from( ['a' => 'X', 'b' => 'Y'] )->has( 'X' );
	 *
	 * Results:
	 * The first example will return TRUE while the second and third one will return FALSE
	 *
	 * @param mixed $key Key of the requested item
	 * @return bool TRUE if key is available in map, FALSE if not
	 */
	public function has( $key ) : bool
	{
		return array_key_exists( $key, $this->items );
	}


	/**
	 * Tests if the passed element is part of the map.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b'] )->in( 'a' );
	 *  Map::from( ['a', 'b'] )->in( 'x' );
	 *  Map::from( ['1', '2'] )->in( 2, true );
	 *
	 * Results:
	 * The first example will return TRUE while the second and third one will return FALSE
	 *
	 * @param mixed $element Element to search for in the map
	 * @param bool $strict TRUE to check the type too, using FALSE '1' and 1 will be the same
	 * @return bool TRUE if element is available in map, FALSE if not
	 */
	public function in( $element, bool $strict = false ) : bool
	{
		return in_array( $element, $this->items, $strict );
	}


	/**
	 * Returns all values in a new map that are available in both, the map and the given items.
	 *
	 * Examples:
	 *  Map::from( ['a' => 'foo', 'b' => 'bar] )->intersect( ['bar'] );
	 *
	 * Results:
	 *  ['b' => 'bar']
	 *
	 * If a callback is passed, the given function will be used to compare the values.
	 * The function must accept two parameters (value A and B) and must return
	 * -1 if value A is smaller than value B, 0 if both are equal and 1 if value A is
	 * greater than value B. Both, a method name and an anonymous function can be passed:
	 *
	 *  Map::from( [0 => 'a'] )->intersect( [0 => 'A'], 'strcasecmp' );
	 *  Map::from( ['b' => 'a'] )->intersect( ['B' => 'A'], 'strcasecmp' );
	 *  Map::from( ['b' => 'a'] )->intersect( ['c' => 'A'], function( $valA, $valB ) {
	 *      return strtolower( $valA ) <=> strtolower( $valB );
	 *  } );
	 *
	 * All examples will return a map containing ['a'] because both contain the same
	 * values when compared case insensitive.
	 *
	 * @param iterable $items List of items
	 * @param  callable|null $callback Function with (valueA, valueB) parameters and returns -1 (<), 0 (=) and 1 (>)
	 * @return self New map
	 */
	public function intersect( iterable $items, callable $callback = null ) : self
	{
		if( $callback ) {
			return new static( array_uintersect( $this->items, $this->getArray( $items ), $callback ) );
		}

		return new static( array_intersect( $this->items, $this->getArray( $items ) ) );
	}


	/**
	 * Returns all values in a new map that are available in both, the map and the given items while comparing the keys too.
	 *
	 * Examples:
	 *  Map::from( ['a' => 'foo', 'b' => 'bar] )->intersectAssoc( new Map( ['foo', 'b' => 'bar'] ) );
	 *
	 * Results:
	 *  ['a' => 'foo']
	 *
	 * If a callback is passed, the given function will be used to compare the values.
	 * The function must accept two parameters (value A and B) and must return
	 * -1 if value A is smaller than value B, 0 if both are equal and 1 if value A is
	 * greater than value B. Both, a method name and an anonymous function can be passed:
	 *
	 *  Map::from( [0 => 'a'] )->intersectAssoc( [0 => 'A'], 'strcasecmp' );
	 *  Map::from( ['b' => 'a'] )->intersectAssoc( ['B' => 'A'], 'strcasecmp' );
	 *  Map::from( ['b' => 'a'] )->intersectAssoc( ['c' => 'A'], function( $valA, $valB ) {
	 *      return strtolower( $valA ) <=> strtolower( $valB );
	 *  } );
	 *
	 * The first example will return [0 => 'a'] because both contain the same
	 * values when compared case insensitive. The second and third example will return
	 * an empty map because the keys doesn't match ("b" vs. "B" and "b" vs. "c").
	 *
	 * @param iterable $items List of items
	 * @param  callable|null $callback Function with (valueA, valueB) parameters and returns -1 (<), 0 (=) and 1 (>)
	 * @return self New map
	 */
	public function intersectAssoc( iterable $items, callable $callback = null ) : self
	{
		if( $callback ) {
			return new static( array_uintersect_assoc( $this->items, $this->getArray( $items ), $callback ) );
		}

		return new static( array_intersect_assoc( $this->items, $this->getArray( $items ) ) );
	}


	/**
	 * Returns all values in a new map that are available in both, the map and the given items by comparing the keys only.
	 *
	 * Examples:
	 *  Map::from( ['a' => 'foo', 'b' => 'bar] )->intersectKeys( new Map( ['foo', 'b' => 'baz'] ) );
	 *
	 * Results:
	 *  ['b' => 'bar']
	 *
	 * If a callback is passed, the given function will be used to compare the keys.
	 * The function must accept two parameters (key A and B) and must return
	 * -1 if key A is smaller than key B, 0 if both are equal and 1 if key A is
	 * greater than key B. Both, a method name and an anonymous function can be passed:
	 *
	 *  Map::from( [0 => 'a'] )->intersectKeys( [0 => 'A'], 'strcasecmp' );
	 *  Map::from( ['b' => 'a'] )->intersectKeys( ['B' => 'X'], 'strcasecmp' );
	 *  Map::from( ['b' => 'a'] )->intersectKeys( ['c' => 'a'], function( $keyA, $keyB ) {
	 *      return strtolower( $keyA ) <=> strtolower( $keyB );
	 *  } );
	 *
	 * The first example will return a map with [0 => 'a'] and the second one will
	 * return a map with ['b' => 'a'] because both contain the same keys when compared
	 * case insensitive. The third example will return an empty map because the keys
	 * doesn't match ("b" vs. "c").
	 *
	 * @param iterable $items List of items
	 * @param  callable|null $callback Function with (keyA, keyB) parameters and returns -1 (<), 0 (=) and 1 (>)
	 * @return self New map
	 */
	public function intersectKeys( iterable $items, callable $callback = null ) : self
	{
		if( $callback ) {
			return new static( array_intersect_ukey( $this->items, $this->getArray( $items ), $callback ) );
		}

		return new static( array_intersect_key( $this->items, $this->getArray( $items ) ) );
	}


	/**
	 * Determines if the map is empty or not.
	 *
	 * Examples:
	 *  Map::from( [] );
	 *  Map::from( ['a'] );
	 *
	 * Results:
	 *  The first example returns TRUE while the second returns FALSE
	 *
	 * @return bool TRUE if map is empty, FALSE if not
	 */
	public function isEmpty() : bool
	{
		return empty( $this->items );
	}


	/**
	 * Concatenates the string representation of all elements.
	 *
	 * Objects that implement __toString() does also work, otherwise (and in case
	 * of arrays) a PHP notice is generated. NULL and FALSE values are treated as
	 * empty strings.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b', false] )->join();
	 *  Map::from( ['a', 'b', null, false] )->join( '-' );
	 *
	 * Results:
	 * The first example will return "ab" while the second one will return "a-b--"
	 *
	 * @param string $glue Character or string added between elements
	 * @return string String of concatenated map elements
	 */
	public function join( string $glue = '' ) : string
	{
		return implode( $glue, $this->items );
	}


	/**
	 * Returns the keys of the map items in a new map object.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b'] );
	 *  Map::from( ['a' => 0, 'b' => 1] );
	 *
	 * Results:
	 * The first example returns a map containing [0, 1] while the second one will
	 * return a map with ['a', 'b'].
	 *
	 * @return self New map
	 */
	public function keys() : self
	{
		return new static( array_keys( $this->items ) );
	}


	/**
	 * Sorts the map items by their keys without creating a new map.
	 *
	 * Examples:
	 *  Map::from( ['b' => 0, 'a' => 1] )->ksort();
	 *  Map::from( [1 => 'a', 0 => 'b'] )->ksort();
	 *
	 * Results:
	 * The first example will sort the map items to ['a' => 1, 'b' => 0] while the second
	 * one will sort the map entries to [0 => 'b', 1 => 'a'].
	 *
	 * If a callback is passed, the given function will be used to compare the keys.
	 * The function must accept two parameters (key A and B) and must return
	 * -1 if key A is smaller than key B, 0 if both are equal and 1 if key A is
	 * greater than key B. Both, a method name and an anonymous function can be passed:
	 *
	 *  Map::from( ['b' => 'a', 'a' => 'b'] )->ksort( 'strcasecmp' );
	 *  Map::from( ['b' => 'a', 'a' => 'b'] )->ksort( function( $keyA, $keyB ) {
	 *      return strtolower( $keyA ) <=> strtolower( $keyB );
	 *  } );
	 *
	 * Both examples will re-sort the entries to ['a' => 'b', 'b' => 'a']. The third
	 * parameter modifies how the keys are compared. Possible values are:
	 * - SORT_REGULAR : compare items normally (don't change types)
	 * - SORT_NUMERIC : compare items numerically
	 * - SORT_STRING : compare items as strings
	 * - SORT_LOCALE_STRING : compare items as strings, based on the current locale or changed by setlocale()
	 * - SORT_NATURAL : compare items as strings using "natural ordering" like natsort()
	 * - SORT_FLAG_CASE : use SORT_STRING|SORT_FLAG_CASE and SORT_NATURALSORT_FLAG_CASE to sort strings case-insensitively
	 *
	 * @param callable|null $callback Function with (keyA, keyB) parameters and returns -1 (<), 0 (=) and 1 (>)
	 * @param int $options Sort options for ksort()
	 * @return self Updated map for fluid interface
	 */
	public function ksort( callable $callback = null, int $options = SORT_REGULAR ) : self
	{
		$callback ? uksort( $this->items, $callback ) : ksort( $this->items, $options );
		return $this;
	}


	/**
	 * Returns the last element from the map.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b'] )->last();
	 *  Map::from( ['a', 'b'] )->last( 'x' );
	 *  Map::from( ['a', 'c', 'e'] )->last( null, function( $value, $key ) {
	 *      return $value < 'd';
	 *  } );
	 *
	 * Result:
	 * The first example will return 'b', the second 'x' and the third 'c'.
	 *
	 * @param mixed $default Default value if no element matches
	 * @param \Closure|null $callback Function with (item, key) parameters and returns TRUE/FALSE
	 * @return mixed Last value of map or default value
	 */
	public function last( $default = null, \Closure $callback = null )
	{
		if( $callback )
		{
			foreach( array_reverse( $this->items, true ) as $key => $value )
			{
				if( $callback( $value, $key ) ) {
					return $value;
				}
			}

			return $default;
		}

		return end( $this->items ) ?: $default;
	}


	/**
	 * Calls the passed function once for each element and returns a new map for the result.
	 *
	 * Examples:
	 *  Map::from( ['a' => 2, 'b' => 4] )->map( function( $value, $key ) {
	 *      return $value * 2;
	 *  } );
	 *
	 * Results:
	 *  ['a' => 4, 'b' => 8]
	 *
	 * @param callable $callback Function with (value, key) parameters and returns computed result
	 * @return self New map with the original keys and the computed values
	 */
	public function map( callable $callback ) : self
	{
		$keys = array_keys( $this->items );
		$items = array_map( $callback, $this->items, $keys );

		return new static( array_combine( $keys, $items ) ?: [] );
	}


	/**
	 * Merges the map with the given items without returning a new map.
	 *
	 * Elements with the same non-numeric keys will be overwritten, elements
	 * with the same numeric keys will be added.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b'] )->merge( ['b', 'c'] );
	 *  Map::from( ['a' => 1, 'b' => 2] )->merge( ['b' => 4, 'c' => 6] );
	 *
	 * Results:
	 *  ['a', 'b', 'b', 'c']
	 *  ['a' => 1, 'b' => 4, 'c' => 6]
	 *
	 * The method is similar to replace() but doesn't replace elements with
	 * the same numeric keys. If you want to be sure that all passed elements
	 * are added without replacing existing ones, use concat() instead.
	 *
	 * @param iterable $items List of items
	 * @return self Updated map for fluid interface
	 */
	public function merge( iterable $items ) : self
	{
		$this->items = array_merge( $this->items, $this->getArray( $items ) );
		return $this;
	}


	/**
	 * Determines if an element exists at an offset.
	 *
	 * Examples:
	 *  $map = Map::from( ['a' => 1, 'b' => 3] );
	 *  isset( $map['b'] );
	 *  isset( $map['c'] );
	 *
	 * Results:
	 *  The first isset() will return TRUE while the second one will return FALSE
	 *
	 * @param mixed $key Key to check for
	 * @return bool TRUE if key exists, FALSE if not
	 */
	public function offsetExists( $key )
	{
		return array_key_exists( $key, $this->items );
	}


	/**
	 * Returns an element at a given offset.
	 *
	 * Examples:
	 *  $map = Map::from( ['a' => 1, 'b' => 3] );
	 *  $map['b'];
	 *
	 * Results:
	 *  $map['b'] will return 3
	 *
	 * @param mixed $key Key to return the element for
	 * @return mixed Value associated to the given key
	 */
	public function offsetGet( $key )
	{
		return $this->items[$key];
	}


	/**
	 * Sets the element at a given offset.
	 *
	 * Examples:
	 *  $map = Map::from( ['a' => 1] );
	 *  $map['b'] = 2;
	 *  $map[0] = 4;
	 *
	 * Results:
	 *  ['a' => 1, 'b' => 2, 0 => 4]
	 *
	 * @param mixed $key Key to set the element for
	 * @param mixed $value New value set for the key
	 */
	public function offsetSet( $key, $value )
	{
		if( $key !== null ) {
			$this->items[$key] = $value;
		} else {
			$this->items[] = $value;
		}
	}


	/**
	 * Unsets the element at a given offset.
	 *
	 * Examples:
	 *  $map = Map::from( ['a' => 1] );
	 *  unset( $map['a'] );
	 *
	 * Results:
	 *  The map will be empty
	 *
	 * @param string $key Key for unsetting the item
	 */
	public function offsetUnset( $key )
	{
		unset( $this->items[$key] );
	}


	/**
	 * Passes the map to the given callback and return the result.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b'] )->pipe( function( $map ) {
	 *      return join( '-', $map->toArray() );
	 *  } );
	 *
	 * Results:
	 *  "a-b" will be returned
	 *
	 * @param \Closure $callback Function with map as parameter which returns arbitrary result
	 * @return mixed Result returned by the callback
	 */
	public function pipe( \Closure $callback )
	{
		return $callback( $this );
	}


	/**
	 * Returns and removes the last element from the map.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b'] )->pop();
	 *
	 * Results:
	 *  "b" will be returned and the map only contains ['a'] afterwards
	 *
	 * @return mixed Last element of the map or null if empty
	 */
	public function pop()
	{
		return array_pop( $this->items );
	}


	/**
	 * Returns and removes an element from the map by its key.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b', 'c'] )->pull( 1 );
	 *  Map::from( ['a', 'b', 'c'] )->pull( 'x', 'none' );
	 *
	 * Results:
	 * The first example will return "b" and the map contains ['a', 'c'] afterwards.
	 * The second one will return "none" and the map content stays untouched.
	 *
	 *
	 * @param mixed $key Key to retrieve the value for
	 * @param mixed $default Default value if key isn't available
	 * @return mixed Value from map or default value
	 */
	public function pull( $key, $default = null )
	{
		$value = $this->get( $key, $default );
		unset( $this->items[$key] );

		return $value;
	}


	/**
	 * Pushes an element onto the end of the map without returning a new map.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b'] )->push( 'aa' );
	 *
	 * Results:
	 *  ['a', 'b', 'aa']
	 *
	 * @param mixed $value Value to add to the end
	 * @return self Same map for fluid interface
	 */
	public function push( $value ) : self
	{
		$this->items[] = $value;
		return $this;
	}


	/**
	 * Returns one or more random element from the map.
	 *
	 * Examples:
	 *  Map::from( [2, 4, 8, 16] )->random();
	 *  Map::from( [2, 4, 8, 16] )->random( 2 );
	 *  Map::from( [2, 4, 8, 16] )->random( 5 );
	 *
	 * Results:
	 * The first example will return a map including [0 => 8] or any other value,
	 * the second one will return a map with [0 => 16, 1 => 2] or any other values
	 * and the third example will return a map of the whole list in random order. The
	 * less elements are in the map, the less random the order will be, especially if
	 * the maximum number of values is high or close to the number of elements.
	 *
	 * The keys of the original map are preserved in the returned map.
	 *
	 * @param int $max Maximum number of elements that should be returned
	 * @return self New map with key/element pairs from original map in random order
	 */
	public function random( int $max = 1 ) : self
	{
		if( ( $keys = @array_rand( $this->items, min( $max, count( $this->items ) ) ) ) === null ) {
			return new self();
		}

		return new self( array_intersect_key( $this->items, array_flip( (array) $keys ) ) );
	}


	/**
	 * Iteratively reduces the array to a single value using a callback function.
	 * Afterwards, the map will be empty.
	 *
	 * Examples:
	 *  Map::from( [2, 8] )->reduce( function( $result, $value ) {
	 *      return $result += $value;
	 *  }, 10 );
	 *
	 * Results:
	 *  "20" will be returned because the sum is computed by 10 (initial value) + 2 + 8
	 *
	 * @param callable $callback Function with (result, value) parameters and returns result
	 * @param mixed $initial Initial value when computing the result
	 * @return mixed Value computed by the callback function
	 */
	public function reduce( callable $callback, $initial = null )
	{
		return array_reduce( $this->items, $callback, $initial );
	}


	/**
	 * Removes one or more items from the map by its keys without returning a new map.
	 *
	 * Examples:
	 *  Map::from( ['a' => 1, 2 => 'b'] )->remove( 'a' );
	 *  Map::from( ['a' => 1, 2 => 'b'] )->remove( [2, 'a'] );
	 *
	 * Results:
	 * The first example will result in [2 => 'b'] while the second one resulting
	 * in an empty list
	 *
	 * @param mixed|array $keys List of keys
	 * @return self Same map for fluid interface
	 */
	public function remove( $keys ) : self
	{
		foreach( (array) $keys as $key ) {
			unset( $this->items[$key] );
		}

		return $this;
	}


	/**
	 * Recursively replaces items in the map with the given items without returning a new map.
	 *
	 * Examples:
	 *  Map::from( ['a' => 1, 2 => 'b'] )->replace( ['a' => 2] );
	 *  Map::from( ['a' => 1, 'b' => ['c' => 3, 'd' => 4]] )->replace( ['b' => ['c' => 9]] );
	 *
	 * Results:
	 * The first example will result in ['a' => 2, 2 => 'b'] while the second one
	 * will produce ['a' => 1, 'b' => ['c' => 9, 'd' => 4]].
	 *
	 * @param iterable $items List of items
	 * @return self Updated map for fluid interface
	 */
	public function replace( iterable $items ) : self
	{
		$this->items = array_replace_recursive( $this->items, $this->getArray( $items ) );
		return $this;
	}


	/**
	 * Reverses the element order without returning a new map.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b'] )->reverse();
	 *
	 * Results:
	 *  ['b', 'a']
	 *
	 * @return self Updated map for fluid interface
	 */
	public function reverse() : self
	{
		$this->items = array_reverse( $this->items, true );
		return $this;
	}


	/**
	 * Searches the map for a given value and return the corresponding key if successful.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b', 'c'] )->search( 'b' );
	 *  Map::from( [1, 2, 3] )->search( '2', true );
	 *
	 * Results:
	 * The first example will return 1 (array index) while the second one will
	 * return NULL because the types doesn't match (int vs. string)
	 *
	 * @param mixed $value Item to search for
	 * @param bool $strict TRUE if type of the element should be checked too
	 * @return mixed|null Value from map or null if not found
	 */
	public function search( $value, $strict = true )
	{
		if( ( $result = array_search( $value, $this->items, $strict ) ) !== false ) {
			return $result;
		}

		return null;
	}


	/**
	 * Sets an element in the map by key without returning a new map.
	 *
	 * Examples:
	 *  Map::from( ['a'] )->set( 1, 'b' );
	 *  Map::from( ['a'] )->set( 0, 'b' );
	 *
	 * Results:
	 * The first example results in ['a', 'b'] while the second one produces ['b']
	 *
	 * @param mixed $key Key to set the new value for
	 * @param mixed $value New element that should be set
	 * @return self Same map for fluid interface
	 */
	public function set( $key, $value ) : self
	{
		$this->items[$key] = $value;
		return $this;
	}


	/**
	 * Returns and removes the first element from the map.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b'] )->shift();
	 *  Map::from( [] )->shift();
	 *
	 * Results:
	 * The first example returns "a" and shortens the map to ['b'] only while the
	 * second example will return NULL
	 *
	 * Performance note:
	 * The bigger the list, the higher the performance impact because shift()
	 * reindexes all existing elements. Usually, it's better to reverse() the list
	 * and pop() entries from the list afterwards if a significant number of elements
	 * should be removed from the list:
	 *
	 *  $map->reverse()->pop();
	 * instead of
	 *  $map->shift( 'a' );
	 *
	 * @return mixed|null Value from map or null if not found
	 */
	public function shift()
	{
		return array_shift( $this->items );
	}


	/**
	 * Shuffles the items in the map without returning a new map.
	 *
	 * Examples:
	 *  Map::from( [2 => 'a', 4 => 'b'] )->shuffle();
	 *
	 * Results:
	 * The map will contain "a" and "b" in random order and with new keys assigned
	 *
	 * @return self Updated map for fluid interface
	 */
	public function shuffle() : self
	{
		shuffle( $this->items );
		return $this;
	}


	/**
	 * Returns a map with the slice from the original map.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b', 'c'] )->slice( 1 );
	 *  Map::from( ['a', 'b', 'c'] )->slice( 1, 1 );
	 *  Map::from( ['a', 'b', 'c', 'd'] )->slice( -2, -1 );
	 *
	 * Results:
	 * The first example will return ['b', 'c'] and the second one ['b'] only.
	 * The third example returns ['c'] because the slice starts at the second
	 * last value and ends before the last value.
	 *
	 * The rules for offsets are:
	 * - If offset is non-negative, the sequence will start at that offset
	 * - If offset is negative, the sequence will start that far from the end
	 *
	 * Similar for the length:
	 * - If length is given and is positive, then the sequence will have up to that many elements in it
	 * - If the array is shorter than the length, then only the available array elements will be present
	 * - If length is given and is negative then the sequence will stop that many elements from the end
	 * - If it is omitted, then the sequence will have everything from offset up until the end
	 *
	 * @param int $offset Number of items to start from
	 * @param int $length Number of items to return
	 * @return self New map
	 */
	public function slice( int $offset, int $length = null ) : self
	{
		return new static( array_slice( $this->items, $offset, $length, true ) );
	}


	/**
	 * Sorts all elements using a callback without returning a new map.
	 *
	 * Examples:
	 *  Map::from( ['a' => 1, 'b' => 0] )->sort();
	 *  Map::from( [0 => 'b', 1 => 'a'] )->sort();
	 *
	 * Results:
	 * The first example will sort the map items to ['b' => 0, 'a' => 1] while the second
	 * one will sort the map entries to [1 => 'a', 0 => 'b'].
	 *
	 * If a callback is passed, the given function will be used to compare the values.
	 * The function must accept two parameters (key A and B) and must return
	 * -1 if key A is smaller than key B, 0 if both are equal and 1 if key A is
	 * greater than key B. Both, a method name and an anonymous function can be passed:
	 *
	 *  Map::from( ['b' => 'a', 'a' => 'B'] )->sort( 'strcasecmp' );
	 *  Map::from( ['b' => 'a', 'a' => 'B'] )->sort( function( $keyA, $keyB ) {
	 *      return strtolower( $keyA ) <=> strtolower( $keyB );
	 *  } );
	 *
	 * Both examples will re-sort the entries to ['a' => 'B', 'b' => 'a'] because
	 * the ASCII value for "B" is smaller than for "a".
	 *
	 * The third parameter modifies how the values are compared. Possible parameter values are:
	 * - SORT_REGULAR : compare items normally (don't change types)
	 * - SORT_NUMERIC : compare items numerically
	 * - SORT_STRING : compare items as strings
	 * - SORT_LOCALE_STRING : compare items as strings, based on the current locale or changed by setlocale()
	 * - SORT_NATURAL : compare items as strings using "natural ordering" like natsort()
	 * - SORT_FLAG_CASE : use SORT_STRING|SORT_FLAG_CASE and SORT_NATURALSORT_FLAG_CASE to sort strings case-insensitively
	 *
	 * The keys are preserved using this method with and without callback function.
	 *
	 * @param callable|null $callback Function with (itemA, itemB) parameters and returns -1 (<), 0 (=) and 1 (>)
	 * @param int $options Sort options for asort()
	 * @return self Updated map for fluid interface
	 */
	public function sort( callable $callback = null, int $options = SORT_REGULAR ) : self
	{
		$callback ? uasort( $this->items, $callback ) : asort( $this->items, $options );
		return $this;
	}


	/**
	 * Removes a portion of the map and replace it with the given replacement, then return the updated map.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b', 'c'] )->slice( 1 );
	 *  Map::from( ['a', 'b', 'c'] )->slice( 1, 1, ['x', 'y'] );
	 *
	 * Results:
	 * The first example removes all entries after "a", so only ['a'] will be left
	 * in the map and ['b', 'c'] is returned. The second example replaces/returns "b"
	 * (start at 1, length 1) with ['x', 'y'] so the new map will contain
	 * ['a', 'x', 'y', 'c'] afterwards.
	 *
	 * The rules for offsets are:
	 * - If offset is non-negative, the sequence will start at that offset
	 * - If offset is negative, the sequence will start that far from the end
	 *
	 * Similar for the length:
	 * - If length is given and is positive, then the sequence will have up to that many elements in it
	 * - If the array is shorter than the length, then only the available array elements will be present
	 * - If length is given and is negative then the sequence will stop that many elements from the end
	 * - If it is omitted, then the sequence will have everything from offset up until the end
	 *
	 * @param int $offset Number of items to start from
	 * @param int|null $length Number of items to remove
	 * @param mixed $replacement List of items to insert
	 * @return self New map
	 */
	public function splice( int $offset, int $length = null, $replacement = [] ) : self
	{
		if( $length === null ) {
			return new static( array_splice( $this->items, $offset ) );
		}

		return new static( array_splice( $this->items, $offset, $length, $replacement ) );
	}


	/**
	 * Returns the elements as a plain array.
	 *
	 * @return array Plain array
	 */
	public function toArray() : array
	{
		return $this->items;
	}


	/**
	 * Returns the elements encoded as JSON string.
	 *
	 * There are several options available to modify the JSON output:
	 * {@link https://www.php.net/manual/en/function.json-encode.php}
	 * The parameter can be a single JSON_* constant or a bitmask of several
	 * constants combine by bitwise OR (|), e.g.:
	 *
	 *  JSON_FORCE_OBJECT|JSON_HEX_QUOT
	 *
	 * @param int $options Combination of JSON_* constants
	 * @return string Array encoded as JSON string
	 */
	public function toJson( int $options = 0 ) : string
	{
		return json_encode( $this->items, $options );
	}


	/**
	 * Builds a union of the elements and the given items without returning a new map.
	 * Existing keys in the map will not be overwritten
	 *
	 * Examples:
	 *  Map::from( [0 => 'a', 1 => 'b'] )->union( [0 => 'c'] );
	 *  Map::from( ['a' => 1, 'b' => 2] )->union( ['c' => 1] );
	 *
	 * Results:
	 * The first example will result in [0 => 'a', 1 => 'b'] because the key 0
	 * isn't overwritten. In the second example, the result will be a combined
	 * list: ['a' => 1, 'b' => 2, 'c' => 1].
	 *
	 * If list entries should be overwritten,  please use merge() instead!
	 *
	 * @param iterable $items List of items
	 * @return self Updated map for fluid interface
	 */
	public function union( iterable $items ) : self
	{
		$this->items += $this->getArray( $items );
		return $this;
	}


	/**
	 * Returns only unique items from the map in a new map
	 *
	 * Examples:
	 *  Map::from( [0 => 'a', 1 => 'b', 2 => 'b', 3 => 'c'] )->unique();
	 *
	 * Results:
	 * A new map with [0 => 'a', 1 => 'b', 3 => 'c'] as content
	 *
	 * Two elements are condidered equal if comparing their string representions returns TRUE:
	 * (string) $elem1 === (string) $elem2
	 *
	 * The keys of the elements are preserved in the new map.
	 *
	 * @return self New map
	 */
	public function unique() : self
	{
		return new static( array_unique( $this->items ) );
	}


	/**
	 * Pushes an element onto the beginning of the map without returning a new map.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b'] )->unshift( 'd' );
	 *  Map::from( ['a', 'b'] )->unshift( 'd', 'first' );
	 *
	 * Results:
	 * The first example will result in ['d', 'a', 'b'] while the second one will
	 * produce ['first' => 'd', 0 => 'a', 1 => 'b'].
	 *
	 * Performance note:
	 * The bigger the list, the higher the performance impact because unshift()
	 * needs to create a new list and copies all existing elements to the new
	 * array. Usually, it's better to push() new entries at the end and reverse()
	 * the list afterwards:
	 *
	 *  $map->push( 'a' )->push( 'b' )->reverse();
	 * instead of
	 *  $map->unshift( 'a' )->unshift( 'b' );
	 *
	 * @param mixed $value Item to add at the beginning
	 * @param mixed $key Key for the item
	 * @return self Same map for fluid interface
	 */
	public function unshift( $value, $key = null ) : self
	{
		if( $key === null ) {
			array_unshift( $this->items, $value );
		} else {
			$this->items = [$key => $value] + $this->items;
		}

		return $this;
	}


	/**
	 * Resets the keys and return the values in a new map.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b', 'b', 'c'] )->unique();
	 *
	 * Results:
	 * A new map with ['a', 'b', 'c'] as content
	 *
	 * @return self New map of the values
	 */
	public function values() : self
	{
		return new static( array_values( $this->items ) );
	}


	/**
	 * Returns a plain array of the given items.
	 *
	 * @param iterable $items List of items
	 * @return array Plain array
	 */
	protected function getArray( iterable $items ) : array
	{
		if( is_array( $items ) ) {
			return $items;
		} elseif( $items instanceof self ) {
			return $items->toArray();
		} elseif( $items instanceof \Traversable ) {
			return iterator_to_array( $items );
		}

		return (array) $items;
	}


	/**
	 * Flattens a multi-dimensional array or map into a single level array.
	 *
	 * @param iterable $entries Single of multi-level array, map or everything foreach can be used with
	 * @param array &$result Will contain all elements from the multi-dimensional arrays afterwards
	 * @param float $depth Number of levels to flatten in multi-dimensional arrays
	 * @return array Single level array with all elements
	 */
	protected function flatten( iterable $entries, array &$result, float $depth )
	{
		foreach( $entries as $entry )
		{
			if( is_iterable( $entry ) && $depth > 0.1 ) {
				$this->flatten( $entry, $result, $depth - 1 );
			} else {
				$result[] = $entry;
			}
		}
	}


	/**
	 * Flattens a multi-dimensional array or map into a single level array.
	 *
	 * @param iterable $entries Single of multi-level array, map or everything foreach can be used with
	 * @param array &$result Will contain all elements from the multi-dimensional arrays afterwards
	 * @param float $depth Number of levels to flatten in multi-dimensional arrays
	 * @return array Single level array with all elements
	 */
	protected function kflatten( iterable $entries, array &$result, float $depth )
	{
		foreach( $entries as $key => $entry )
		{
			if( is_iterable( $entry ) && $depth > 0.1 ) {
				$this->kflatten( $entry, $result, $depth - 1 );
			} else {
				$result[$key] = $entry;
			}
		}
	}
}
