<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 * @author Taylor Otwell, Aimeos.org developers
 */


namespace Aimeos;


/**
 * Handling and operating on a list of elements easily
 * Inspired by Laravel Collection class, PHP map data structure and Javascript
 */
class Map implements \ArrayAccess, \Countable, \IteratorAggregate
{
	protected static $methods = [];
	protected $list = [];


	/**
	 * Creates a new map.
	 *
	 * @param iterable $elements List of elements
	 */
	public function __construct( iterable $elements = [] )
	{
		$this->list = $this->getArray( $elements );
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
	 * has access to the internal array by using $this->list.
	 *
	 * Examples:
	 *  Map::method( 'case', function( $case = CASE_LOWER ) {
	 *      return new self( array_change_key_case( $this->list, $case ) );
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

		foreach( $this->list as $key => $item )
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
	 * @param iterable $elements List of elements
	 * @return self New map
	 */
	public static function from( iterable $elements = [] ) : self
	{
		return new static( $elements );
	}


	/**
	 * Creates a new map with the string splitted by the delimiter.
	 *
	 * Examples:
	 *  Map::split( 'a,b,c' );
	 *  Map::split( 'a a<-->b b<-->c c', '<-->' );
	 *  Map::split( 'string', '' );
	 *
	 * Results:
	 *  ['a', 'b', 'c']
	 *  ['a a', 'b b', 'c c']
	 *  ['s', 't', 'r', 'i', 'n', 'g']
	 *
	 * @param string $delimiter Delimiter character or string
	 * @param string $str String to split
	 * @return self New map with splitted parts
	 */
	public static function split( string $str, string $delimiter = ',' ) : self
	{
		if( $delimiter !== '' ) {
			return new static( explode( $delimiter, $str ) );
		}

		return new static( str_split( $str ) );
	}


	/**
	 * Registers a custom method that has access to the class properties if called non-static.
	 *
	 * Examples:
	 *  Map::method( 'foo', function( $arg1, $arg2 ) {
	 *      return $this->list;
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
	 * Sorts all elements in reverse order and maintains the key association.
	 *
	 * Examples:
	 *  Map::from( ['b' => 0, 'a' => 1] )->arsort();
	 *  Map::from( [1 => 'a', 0 => 'b'] )->arsort();
	 *
	 * Results:
	 *  ['a' => 1, 'b' => 0]
	 *  [0 => 'b', 1 => 'a']
	 *
	 * The parameter modifies how the values are compared. Possible parameter values are:
	 * - SORT_REGULAR : compare elements normally (don't change types)
	 * - SORT_NUMERIC : compare elements numerically
	 * - SORT_STRING : compare elements as strings
	 * - SORT_LOCALE_STRING : compare elements as strings, based on the current locale or changed by setlocale()
	 * - SORT_NATURAL : compare elements as strings using "natural ordering" like natsort()
	 * - SORT_FLAG_CASE : use SORT_STRING|SORT_FLAG_CASE and SORT_NATURALSORT_FLAG_CASE to sort strings case-insensitively
	 *
	 * The keys are preserved using this method and no new map is created.
	 *
	 * @param int $options Sort options for arsort()
	 * @return self Updated map for fluid interface
	 */
	public function arsort( int $options = SORT_REGULAR ) : self
	{
		arsort( $this->list, $options );
		return $this;
	}


	/**
	 * Sorts all elements and maintains the key association.
	 *
	 * Examples:
	 *  Map::from( ['a' => 1, 'b' => 0] )->asort();
	 *  Map::from( [0 => 'b', 1 => 'a'] )->asort();
	 *
	 * Results:
	 *  ['b' => 0, 'a' => 1]
	 *  [1 => 'a', 0 => 'b']
	 *
	 * The parameter modifies how the values are compared. Possible parameter values are:
	 * - SORT_REGULAR : compare elements normally (don't change types)
	 * - SORT_NUMERIC : compare elements numerically
	 * - SORT_STRING : compare elements as strings
	 * - SORT_LOCALE_STRING : compare elements as strings, based on the current locale or changed by setlocale()
	 * - SORT_NATURAL : compare elements as strings using "natural ordering" like natsort()
	 * - SORT_FLAG_CASE : use SORT_STRING|SORT_FLAG_CASE and SORT_NATURALSORT_FLAG_CASE to sort strings case-insensitively
	 *
	 * The keys are preserved using this method and no new map is created.
	 *
	 * @param int $options Sort options for asort()
	 * @return self Updated map for fluid interface
	 */
	public function asort( int $options = SORT_REGULAR ) : self
	{
		asort( $this->list, $options );
		return $this;
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

		return new static( array_chunk( $this->list, $size, $preserve ) );
	}


	/**
	 * Removes all elements from the current map.
	 *
	 * @return self Same map for fluid interface
	 */
	public function clear() : self
	{
		$this->list = [];
		return $this;
	}


	/**
	 * Returns the values of a single column/property from an array of arrays or list of elements in a new map.
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
	 * @param string|null $indexcol Name of the index property
	 * @return self New instance with mapped entries
	 */
	public function col( string $valuecol, string $indexcol = null ) : self
	{
		return new static( array_column( $this->list, $valuecol, $indexcol ) );
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
		$this->kflatten( $this->list, $result, $depth ?? INF );
		return new self( $result );
	}


	/**
	 * Pushs all of the given elements onto the map without creating a new map.
	 *
	 * Examples:
	 *  Map::from( ['foo'] )->concat( new Map( ['bar'] ));
	 *
	 * Results:
	 *  ['foo', 'bar']
	 *
	 * @param iterable $elements List of elements
	 * @return self Updated map for fluid interface
	 */
	public function concat( iterable $elements ) : self
	{
		foreach( $elements as $item ) {
			$this->list[] = $item;
		}

		return $this;
	}


	/**
	 * Creates a new map with the same elements.
	 *
	 * Both maps share the same array until one of the map objects modifies the
	 * array. Then, the array is copied and the copy is modfied (copy on write).
	 *
	 * @return self New map
	 */
	public function copy() : self
	{
		return clone $this;
	}


	/**
	 * Counts the number of elements in the map.
	 *
	 * @return int Number of elements
	 */
	public function count() : int
	{
		return count( $this->list );
	}


	/**
	 * Returns the keys/values in the map whose values are not present in the passed elements in a new map.
	 *
	 * Examples:
	 *  Map::from( ['a' => 'foo', 'b' => 'bar'] )->diff( ['bar'] );
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
	 * @param iterable $elements List of elements
	 * @param  callable|null $callback Function with (valueA, valueB) parameters and returns -1 (<), 0 (=) and 1 (>)
	 * @return self New map
	 */
	public function diff( iterable $elements, callable $callback = null ) : self
	{
		if( $callback ) {
			return new static( array_udiff( $this->list, $this->getArray( $elements ), $callback ) );
		}

		return new static( array_diff( $this->list, $this->getArray( $elements ) ) );
	}


	/**
	 * Returns the keys/values in the map whose keys and values are not present in the passed elements in a new map.
	 *
	 * Examples:
	 *  Map::from( ['a' => 'foo', 'b' => 'bar'] )->diffAssoc( new Map( ['foo', 'b' => 'bar'] ) );
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
	 * @param iterable $elements List of elements
	 * @param  callable|null $callback Function with (valueA, valueB) parameters and returns -1 (<), 0 (=) and 1 (>)
	 * @return self New map
	 */
	public function diffAssoc( iterable $elements, callable $callback = null ) : self
	{
		if( $callback ) {
			return new static( array_diff_uassoc( $this->list, $this->getArray( $elements ), $callback ) );
		}

		return new static( array_diff_assoc( $this->list, $this->getArray( $elements ) ) );
	}


	/**
	 * Returns the key/value pairs from the map whose keys are not present in the passed elements in a new map.
	 *
	 * Examples:
	 *  Map::from( ['a' => 'foo', 'b' => 'bar'] )->diffKeys( new Map( ['foo', 'b' => 'baz'] ) );
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
	 * @param iterable $elements List of elements
	 * @param  callable|null $callback Function with (keyA, keyB) parameters and returns -1 (<), 0 (=) and 1 (>)
	 * @return self New map
	 */
	public function diffKeys( iterable $elements, callable $callback = null ) : self
	{
		if( $callback ) {
			return new static( array_diff_ukey( $this->list, $this->getArray( $elements ), $callback ) );
		}

		return new static( array_diff_key( $this->list, $this->getArray( $elements ) ) );
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
		foreach( $this->list as $key => $item )
		{
			if( $callback( $item, $key ) === false ) {
				break;
			}
		}

		return $this;
	}


	/**
	 * Determines if the map is empty or not.
	 *
	 * Examples:
	 *  Map::from( [] )->empty();
	 *  Map::from( ['a'] )->empty();
	 *
	 * Results:
	 *  The first example returns TRUE while the second returns FALSE
	 *
	 * The method is equivalent to isEmpty().
	 *
	 * @return bool TRUE if map is empty, FALSE if not
	 */
	public function empty() : bool
	{
		return empty( $this->list );
	}


	/**
	 * Tests if the passed elements are equal to the elements in the map.
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
	 * @param iterable $elements List of elements to test against
	 * @param bool $assoc TRUE to compare keys too, FALSE to compare only values
	 * @return bool TRUE if both are equal, FALSE if not
	 */
	public function equals( iterable $elements, $assoc = false ) : bool
	{
		$elements = $this->getArray( $elements );

		if( $assoc ) {
			return array_diff_assoc( $this->list, $elements ) === [] && array_diff_assoc( $elements, $this->list ) === [];
		}

		return array_diff( $this->list, $elements ) === [] && array_diff( $elements, $this->list ) === [];
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
			return new static( array_filter( $this->list, $callback, ARRAY_FILTER_USE_BOTH ) );
		}

		return new static( array_filter( $this->list ) );
	}


	/**
	 * Returns the first matching element where the callback returns TRUE.
	 *
	 * Examples:
	 *  Map::from( ['a', 'c', 'e'] )->find( function( $value, $key ) {
	 *      return $value >= 'b';
	 *  } );
	 *  Map::from( ['a', 'c', 'e'] )->find( function( $value, $key ) {
	 *      return $value >= 'b';
	 *  }, true );
	 *
	 * Results:
	 * The first example will return 'c' while the second will return 'e' (last element).
	 *
	 * @param \Closure $callback Function with (value, key) parameters and returns TRUE/FALSE
	 * @param bool $last TRUE to test elements from back to front, FALSE for front to back (default)
	 * @return mixed|null First matching value or NULL
	 */
	public function find( \Closure $callback, bool $last = false )
	{
		foreach( ( $last ? array_reverse( $this->list ) : $this->list ) as $key => $value )
		{
			if( $callback( $value, $key ) ) {
				return $value;
			}
		}

		return null;
	}


	/**
	 * Returns the first element from the map.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b'] )->first();
	 *  Map::from( [] )->first( 'x' );
	 *
	 * Results:
	 * The first example will return 'a' and the second one 'x'.
	 *
	 * @param mixed $default Default value if map is empty
	 * @return mixed First value of map or default value
	 */
	public function first( $default = null )
	{
		return ( $value = reset( $this->list ) ) !== false ? $value : $default;
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
		$this->flatten( $this->list, $result, $depth ?? INF );
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
		return new self( array_flip( $this->list ) );
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
		return array_key_exists( $key, $this->list ) ? $this->list[$key] : $default;
	}


	/**
	 * Returns an iterator for the elements.
	 *
	 * This method will be used by e.g. foreach() to loop over all entries:
	 *  foreach( Map::from( ['a', 'b'] ) as $value )
	 *
	 * @return \Iterator Over map elements
	 */
	public function getIterator() : \Iterator
	{
		return new \ArrayIterator( $this->list );
	}


	/**
	 * Determines if a key or several keys exists in the map.
	 *
	 * If several keys are passed as array, all keys must exist in the map for
	 * TRUE to be returned.
	 *
	 * Examples:
	 *  Map::from( ['a' => 'X', 'b' => 'Y'] )->has( 'a' );
	 *  Map::from( ['a' => 'X', 'b' => 'Y'] )->has( ['a', 'b'] );
	 *  Map::from( ['a' => 'X', 'b' => 'Y'] )->has( 'c' );
	 *  Map::from( ['a' => 'X', 'b' => 'Y'] )->has( ['a', 'c'] );
	 *  Map::from( ['a' => 'X', 'b' => 'Y'] )->has( 'X' );
	 *
	 * Results:
	 * The first and second example will return TRUE while the other ones will return FALSE
	 *
	 * @param mixed|array $key Key of the requested item or list of keys
	 * @return bool TRUE if key or keys are available in map, FALSE if not
	 */
	public function has( $key ) : bool
	{
		foreach( (array) $key as $entry )
		{
			if( array_key_exists( $entry, $this->list ) === false ) {
				return false;
			}
		}

		return true;
	}


	/**
	 * Tests if the passed element or elements are part of the map.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b'] )->in( 'a' );
	 *  Map::from( ['a', 'b'] )->in( ['a', 'b'] );
	 *  Map::from( ['a', 'b'] )->in( 'x' );
	 *  Map::from( ['a', 'b'] )->in( ['a', 'x'] );
	 *  Map::from( ['1', '2'] )->in( 2, true );
	 *
	 * Results:
	 * The first and second example will return TRUE while the other ones will return FALSE
	 *
	 * @param mixed|array $element Element or elements to search for in the map
	 * @param bool $strict TRUE to check the type too, using FALSE '1' and 1 will be the same
	 * @return bool TRUE if all elements are available in map, FALSE if not
	 */
	public function in( $element, bool $strict = false ) : bool
	{
		if( !is_array( $element ) ) {
			return in_array( $element, $this->list, $strict );
		};

		foreach( array_unique( $element ) as $entry )
		{
			if( in_array( $entry, $this->list, $strict ) === false ) {
				return false;
			}
		}

		return true;
	}


	/**
	 * Tests if the passed element or elements are part of the map.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b'] )->includes( 'a' );
	 *  Map::from( ['a', 'b'] )->includes( ['a', 'b'] );
	 *  Map::from( ['a', 'b'] )->includes( 'x' );
	 *  Map::from( ['a', 'b'] )->includes( ['a', 'x'] );
	 *  Map::from( ['1', '2'] )->includes( 2, true );
	 *
	 * Results:
	 * The first and second example will return TRUE while the other ones will return FALSE
	 *
	 * @param mixed|array $element Element or elements to search for in the map
	 * @param bool $strict TRUE to check the type too, using FALSE '1' and 1 will be the same
	 * @return bool TRUE if all elements are available in map, FALSE if not
	 *
	 * This method is an alias for in(). For performance reasons, in() should be
	 * preferred because it uses one method call less than includes().
	 */
	public function includes( $element, bool $strict = false ) : bool
	{
		return $this->in( $element, $strict );
	}


	/**
	 * Returns all values in a new map that are available in both, the map and the given elements.
	 *
	 * Examples:
	 *  Map::from( ['a' => 'foo', 'b' => 'bar'] )->intersect( ['bar'] );
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
	 * @param iterable $elements List of elements
	 * @param  callable|null $callback Function with (valueA, valueB) parameters and returns -1 (<), 0 (=) and 1 (>)
	 * @return self New map
	 */
	public function intersect( iterable $elements, callable $callback = null ) : self
	{
		$elements = $this->getArray( $elements );

		if( $callback ) {
			return new static( array_uintersect( $this->list, $elements, $callback ) );
		}

		// using array_intersect() is 7x slower
		return ( new static( $this->list ) )
			->remove( array_keys( array_diff( $this->list, $elements ) ) )
			->remove( array_keys( array_diff( $elements, $this->list ) ) );
	}


	/**
	 * Returns all values in a new map that are available in both, the map and the given elements while comparing the keys too.
	 *
	 * Examples:
	 *  Map::from( ['a' => 'foo', 'b' => 'bar'] )->intersectAssoc( new Map( ['foo', 'b' => 'bar'] ) );
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
	 * @param iterable $elements List of elements
	 * @param  callable|null $callback Function with (valueA, valueB) parameters and returns -1 (<), 0 (=) and 1 (>)
	 * @return self New map
	 */
	public function intersectAssoc( iterable $elements, callable $callback = null ) : self
	{
		$elements = $this->getArray( $elements );

		if( $callback ) {
			return new static( array_uintersect_assoc( $this->list, $elements, $callback ) );
		}

		return new static( array_intersect_assoc( $this->list, $elements ) );
	}


	/**
	 * Returns all values in a new map that are available in both, the map and the given elements by comparing the keys only.
	 *
	 * Examples:
	 *  Map::from( ['a' => 'foo', 'b' => 'bar'] )->intersectKeys( new Map( ['foo', 'b' => 'baz'] ) );
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
	 * @param iterable $elements List of elements
	 * @param  callable|null $callback Function with (keyA, keyB) parameters and returns -1 (<), 0 (=) and 1 (>)
	 * @return self New map
	 */
	public function intersectKeys( iterable $elements, callable $callback = null ) : self
	{
		$elements = $this->getArray( $elements );

		if( $callback ) {
			return new static( array_intersect_ukey( $this->list, $elements, $callback ) );
		}

		// using array_intersect_key() is 1.6x slower
		return ( new static( $this->list ) )
			->remove( array_keys( array_diff_key( $this->list, $elements ) ) )
			->remove( array_keys( array_diff_key( $elements, $this->list ) ) );
	}


	/**
	 * Determines if the map is empty or not.
	 *
	 * Examples:
	 *  Map::from( [] )->isEmpty();
	 *  Map::from( ['a'] )->isEmpty();
	 *
	 * Results:
	 *  The first example returns TRUE while the second returns FALSE
	 *
	 * The method is equivalent to empty().
	 *
	 * @return bool TRUE if map is empty, FALSE if not
	 */
	public function isEmpty() : bool
	{
		return empty( $this->list );
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
		return implode( $glue, $this->list );
	}


	/**
	 * Returns the keys of the map elements in a new map object.
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
		return new static( array_keys( $this->list ) );
	}


	/**
	 * Sorts the elements by their keys in reverse order.
	 *
	 * Examples:
	 *  Map::from( ['b' => 0, 'a' => 1] )->krsort();
	 *  Map::from( [1 => 'a', 0 => 'b'] )->krsort();
	 *
	 * Results:
	 *  ['a' => 1, 'b' => 0]
	 *  [0 => 'b', 1 => 'a']
	 *
	 * The parameter modifies how the keys are compared. Possible values are:
	 * - SORT_REGULAR : compare elements normally (don't change types)
	 * - SORT_NUMERIC : compare elements numerically
	 * - SORT_STRING : compare elements as strings
	 * - SORT_LOCALE_STRING : compare elements as strings, based on the current locale or changed by setlocale()
	 * - SORT_NATURAL : compare elements as strings using "natural ordering" like natsort()
	 * - SORT_FLAG_CASE : use SORT_STRING|SORT_FLAG_CASE and SORT_NATURALSORT_FLAG_CASE to sort strings case-insensitively
	 *
	 * The keys are preserved using this method and no new map is created.
	 *
	 * @param int $options Sort options for krsort()
	 * @return self Updated map for fluid interface
	 */
	public function krsort( int $options = SORT_REGULAR ) : self
	{
		krsort( $this->list, $options );
		return $this;
	}


	/**
	 * Sorts the elements by their keys.
	 *
	 * Examples:
	 *  Map::from( ['b' => 0, 'a' => 1] )->ksort();
	 *  Map::from( [1 => 'a', 0 => 'b'] )->ksort();
	 *
	 * Results:
	 *  ['a' => 1, 'b' => 0]
	 *  [0 => 'b', 1 => 'a']
	 *
	 * The parameter modifies how the keys are compared. Possible values are:
	 * - SORT_REGULAR : compare elements normally (don't change types)
	 * - SORT_NUMERIC : compare elements numerically
	 * - SORT_STRING : compare elements as strings
	 * - SORT_LOCALE_STRING : compare elements as strings, based on the current locale or changed by setlocale()
	 * - SORT_NATURAL : compare elements as strings using "natural ordering" like natsort()
	 * - SORT_FLAG_CASE : use SORT_STRING|SORT_FLAG_CASE and SORT_NATURALSORT_FLAG_CASE to sort strings case-insensitively
	 *
	 * The keys are preserved using this method and no new map is created.
	 *
	 * @param int $options Sort options for ksort()
	 * @return self Updated map for fluid interface
	 */
	public function ksort( int $options = SORT_REGULAR ) : self
	{
		ksort( $this->list, $options );
		return $this;
	}


	/**
	 * Returns the last element from the map.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b'] )->last();
	 *  Map::from( [] )->last( 'x' );
	 *
	 * Results:
	 * The first example will return 'b' and the second one 'x'.
	 *
	 * @param mixed $default Default value if the map contains no elements
	 * @return mixed Last value of map or default value
	 */
	public function last( $default = null )
	{
		return ( $value = end( $this->list ) ) !== false ? $value : $default;
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
		$keys = array_keys( $this->list );
		$elements = array_map( $callback, $this->list, $keys );

		return new static( array_combine( $keys, $elements ) ?: [] );
	}


	/**
	 * Merges the map with the given elements without returning a new map.
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
	 * @param iterable $elements List of elements
	 * @return self Updated map for fluid interface
	 */
	public function merge( iterable $elements ) : self
	{
		$this->list = array_merge( $this->list, $this->getArray( $elements ) );
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
		return array_key_exists( $key, $this->list );
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
		return $this->list[$key];
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
			$this->list[$key] = $value;
		} else {
			$this->list[] = $value;
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
		unset( $this->list[$key] );
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
		return array_pop( $this->list );
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
		unset( $this->list[$key] );

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
		$this->list[] = $value;
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
	 * @throws \InvalidArgumentException If requested number of elements is less than 1
	 */
	public function random( int $max = 1 ) : self
	{
		if( $max < 1 ) {
			throw new \InvalidArgumentException( 'Requested number of elements must be greater or equal than 1' );
		}

		if( empty( $this->list ) || ( $keys = @array_rand( $this->list, $max ) ) === null
			&& ( $keys = array_rand( $this->list, count( $this->list ) ) ) === null
		) {
			return new self();
		}

		return new self( array_intersect_key( $this->list, array_flip( (array) $keys ) ) );
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
		return array_reduce( $this->list, $callback, $initial );
	}


	/**
	 * Removes one or more elements from the map by its keys without returning a new map.
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
			unset( $this->list[$key] );
		}

		return $this;
	}


	/**
	 * Replaces elements in the map with the given elements without returning a new map.
	 *
	 * Examples:
	 *  Map::from( ['a' => 1, 2 => 'b'] )->replace( ['a' => 2] );
	 *  Map::from( ['a' => 1, 'b' => ['c' => 3, 'd' => 4]] )->replace( ['b' => ['c' => 9]] );
	 *
	 * Results:
	 *  ['a' => 2, 2 => 'b']
	 *  ['a' => 1, 'b' => ['c' => 9, 'd' => 4]]
	 *
	 * The method is similar to merge() but it also replaces elements with numeric
	 * keys. These would be added by merge() with a new numeric key.
	 *
	 * @param iterable $elements List of elements
	 * @param bool $recursive TRUE to replace recursively (default), FALSE to replace elements only
	 * @return self Updated map for fluid interface
	 */
	public function replace( iterable $elements, bool $recursive = true ) : self
	{
		if( $recursive ) {
			$this->list = array_replace_recursive( $this->list, $this->getArray( $elements ) );
		} else {
			$this->list = array_replace( $this->list, $this->getArray( $elements ) );
		}

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
		$this->list = array_reverse( $this->list, true );
		return $this;
	}


	/**
	 * Sorts all elements in reverse order without maintaining the key association.
	 *
	 * Examples:
	 *  Map::from( ['a' => 1, 'b' => 0] )->rsort();
	 *  Map::from( [0 => 'b', 1 => 'a'] )->rsort();
	 *
	 * Results:
	 *  [0 => 1, 1 => 0]
	 *  [0 => 'b', 1 => 'a']
	 *
	 * The parameter modifies how the values are compared. Possible parameter values are:
	 * - SORT_REGULAR : compare elements normally (don't change types)
	 * - SORT_NUMERIC : compare elements numerically
	 * - SORT_STRING : compare elements as strings
	 * - SORT_LOCALE_STRING : compare elements as strings, based on the current locale or changed by setlocale()
	 * - SORT_NATURAL : compare elements as strings using "natural ordering" like natsort()
	 * - SORT_FLAG_CASE : use SORT_STRING|SORT_FLAG_CASE and SORT_NATURALSORT_FLAG_CASE to sort strings case-insensitively
	 *
	 * The keys aren't preserved and elements get a new index. No new map is created
	 *
	 * @param int $options Sort options for rsort()
	 * @return self Updated map for fluid interface
	 */
	public function rsort( int $options = SORT_REGULAR ) : self
	{
		rsort( $this->list, $options );
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
		if( ( $result = array_search( $value, $this->list, $strict ) ) !== false ) {
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
		$this->list[$key] = $value;
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
		return array_shift( $this->list );
	}


	/**
	 * Shuffles the elements in the map without returning a new map.
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
		shuffle( $this->list );
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
	 * @param int $offset Number of elements to start from
	 * @param int $length Number of elements to return
	 * @return self New map
	 */
	public function slice( int $offset, int $length = null ) : self
	{
		return new static( array_slice( $this->list, $offset, $length, true ) );
	}


	/**
	 * Sorts all elements without maintaining the key association.
	 *
	 * Examples:
	 *  Map::from( ['a' => 1, 'b' => 0] )->sort();
	 *  Map::from( [0 => 'b', 1 => 'a'] )->sort();
	 *
	 * Results:
	 *  [0 => 0, 1 => 1]
	 *  [0 => 'a', 1 => 'b']
	 *
	 * The parameter modifies how the values are compared. Possible parameter values are:
	 * - SORT_REGULAR : compare elements normally (don't change types)
	 * - SORT_NUMERIC : compare elements numerically
	 * - SORT_STRING : compare elements as strings
	 * - SORT_LOCALE_STRING : compare elements as strings, based on the current locale or changed by setlocale()
	 * - SORT_NATURAL : compare elements as strings using "natural ordering" like natsort()
	 * - SORT_FLAG_CASE : use SORT_STRING|SORT_FLAG_CASE and SORT_NATURALSORT_FLAG_CASE to sort strings case-insensitively
	 *
	 * The keys aren't preserved and elements get a new index. No new map is created.
	 *
	 * @param int $options Sort options for sort()
	 * @return self Updated map for fluid interface
	 */
	public function sort( int $options = SORT_REGULAR ) : self
	{
		sort( $this->list, $options );
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
	 * @param int $offset Number of elements to start from
	 * @param int|null $length Number of elements to remove, NULL for all
	 * @param mixed $replacement List of elements to insert
	 * @return self New map
	 */
	public function splice( int $offset, int $length = null, $replacement = [] ) : self
	{
		if( $length === null ) {
			$length = count( $this->list );
		}

		return new static( array_splice( $this->list, $offset, $length, $replacement ) );
	}


	/**
	 * Returns the elements as a plain array.
	 *
	 * @return array Plain array
	 */
	public function toArray() : array
	{
		return $this->list;
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
		return json_encode( $this->list, $options );
	}


	/**
	 * Sorts all elements using a callback and maintains the key association.
	 *
	 * The given callback will be used to compare the values. The callback must accept
	 * two parameters (item A and B) and must return -1 if item A is smaller than
	 * item B, 0 if both are equal and 1 if item A is greater than item B. Both, a
	 * method name and an anonymous function can be passed.
	 *
	 * Examples:
	 *  Map::from( ['a' => 'B', 'b' => 'a'] )->uasort( 'strcasecmp' );
	 *  Map::from( ['a' => 'B', 'b' => 'a'] )->uasort( function( $itemA, $itemB ) {
	 *      return strtolower( $itemA ) <=> strtolower( $itemB );
	 *  } );
	 *
	 * Results:
	 *  ['b' => 'a', 'a' => 'B']
	 *  ['b' => 'a', 'a' => 'B']
	 *
	 * The keys are preserved using this method and no new map is created.
	 *
	 * @param callable|null $callback Function with (itemA, itemB) parameters and returns -1 (<), 0 (=) and 1 (>)
	 * @return self Updated map for fluid interface
	 */
	public function uasort( callable $callback ) : self
	{
		uasort( $this->list, $callback );
		return $this;
	}


	/**
	 * Sorts the map elements by their keys using a callback.
	 *
	 * The given callback will be used to compare the keys. The callback must accept
	 * two parameters (key A and B) and must return -1 if key A is smaller than
	 * key B, 0 if both are equal and 1 if key A is greater than key B. Both, a
	 * method name and an anonymous function can be passed.
	 *
	 * Examples:
	 *  Map::from( ['B' => 'a', 'a' => 'b'] )->uksort( 'strcasecmp' );
	 *  Map::from( ['B' => 'a', 'a' => 'b'] )->uksort( function( $keyA, $keyB ) {
	 *      return strtolower( $keyA ) <=> strtolower( $keyB );
	 *  } );
	 *
	 * Results:
	 *  ['a' => 'b', 'B' => 'a']
	 *  ['a' => 'b', 'B' => 'a']
	 *
	 * The keys are preserved using this method and no new map is created.
	 *
	 * @param callable $callback Function with (keyA, keyB) parameters and returns -1 (<), 0 (=) and 1 (>)
	 * @return self Updated map for fluid interface
	 */
	public function uksort( callable $callback ) : self
	{
		uksort( $this->list, $callback );
		return $this;
	}


	/**
	 * Builds a union of the elements and the given elements without returning a new map.
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
	 * @param iterable $elements List of elements
	 * @return self Updated map for fluid interface
	 */
	public function union( iterable $elements ) : self
	{
		$this->list += $this->getArray( $elements );
		return $this;
	}


	/**
	 * Returns only unique elements from the map in a new map
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
		return new static( array_unique( $this->list ) );
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
			array_unshift( $this->list, $value );
		} else {
			$this->list = [$key => $value] + $this->list;
		}

		return $this;
	}


	/**
	 * Sorts all elements using a callback without maintaining the key association.
	 *
	 * The given callback will be used to compare the values. The callback must accept
	 * two parameters (item A and B) and must return -1 if item A is smaller than
	 * item B, 0 if both are equal and 1 if item A is greater than item B. Both, a
	 * method name and an anonymous function can be passed.
	 *
	 * Examples:
	 *  Map::from( ['a' => 'B', 'b' => 'a'] )->usort( 'strcasecmp' );
	 *  Map::from( ['a' => 'B', 'b' => 'a'] )->usort( function( $itemA, $itemB ) {
	 *      return strtolower( $itemA ) <=> strtolower( $itemB );
	 *  } );
	 *
	 * Results:
	 *  [0 => 'a', 1 => 'B']
	 *  [0 => 'a', 1 => 'B']
	 *
	 * The keys aren't preserved and elements get a new index. No new map is created.
	 *
	 * @param callable $callback Function with (itemA, itemB) parameters and returns -1 (<), 0 (=) and 1 (>)
	 * @return self Updated map for fluid interface
	 */
	public function usort( callable $callback ) : self
	{
		usort( $this->list, $callback );
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
		return new static( array_values( $this->list ) );
	}


	/**
	 * Returns a plain array of the given elements.
	 *
	 * @param iterable $elements List of elements
	 * @return array Plain array
	 */
	protected function getArray( iterable $elements ) : array
	{
		if( $elements instanceof self ) {
			return $elements->toArray();
		}

		if( is_array( $elements ) ) {
			return $elements;
		}

		return iterator_to_array( $elements );
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
