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
	protected static $delim = '/';
	protected $list = [];
	protected $sep;


	/**
	 * Creates a new map.
	 *
	 * Returns a new map instance containing the list of elements. In case of
	 * an empty array or null, the map object will contain an empty list.
	 *
	 * @param mixed $elements List of elements or single value
	 */
	public function __construct( $elements = [] )
	{
		$this->list = $this->getArray( $elements );
		$this->sep = self::$delim;
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
			if( is_object( $item ) ) {
				$result[$key] = $item->{$name}( ...$params );
			}
		}

		return new self( $result );
	}


	/**
	 * Returns the elements as a plain array.
	 *
	 * @return array Plain array
	 */
	public function __toArray() : array
	{
		return $this->list;
	}


	/**
	 * Sets or returns the seperator for paths to values in multi-dimensional arrays or objects.
	 *
	 * The static method only changes the separator for new maps created afterwards.
	 * Already existing maps will continue to use the previous separator. To change
	 * the separator of an existing map, use the sep() method instead.
	 *
	 * Examples:
	 *  Map::delimiter( '/' );
	 *  Map::from( ['foo' => ['bar' => 'baz']] )->get( 'foo/bar' );
	 *
	 * Results:
	 *  '.'
	 *  'baz'
	 *
	 * @param string|null $char Separator character, e.g. "." for "key.to.value" instead of "key/to/value"
	 * @return string Separator used up to now
	 */
	public static function delimiter( ?string $char = null ) : string
	{
		$old = self::$delim;

		if( $char ) {
			self::$delim = $char;
		}

		return $old;
	}


	/**
	 * Creates a new map with the string splitted by the delimiter.
	 *
	 * Examples:
	 *  Map::explode( ',', 'a,b,c' );
	 *  Map::explode( '<-->', 'a a<-->b b<-->c c' );
	 *  Map::explode( '', 'string' );
	 *  Map::explode( '|', 'a|b|c', 2 );
	 *  Map::explode( '', 'string', 2 );
	 *  Map::explode( '|', 'a|b|c|d', -2 );
	 *  Map::explode( '', 'string', -3 );
	 *
	 * Results:
	 *  ['a', 'b', 'c']
	 *  ['a a', 'b b', 'c c']
	 *  ['s', 't', 'r', 'i', 'n', 'g']
	 *  ['a', 'b|c']
	 *  ['s', 't', 'ring']
	 *  ['a', 'b']
	 *  ['s', 't', 'r']
	 *
	 * A limit of "0" is treated the same as "1". If limit is negative, the rest of
	 * the string is dropped and not part of the returned map.
	 *
	 * @param string $delimiter Delimiter character, string or empty string
	 * @param string $string String to split
	 * @param int $limit Maximum number of element with the last element containing the rest of the string
	 * @return self New map with splitted parts
	 */
	public static function explode( string $delimiter, string $string, int $limit = PHP_INT_MAX ) : self
	{
		if( $delimiter !== '' ) {
			return new static( explode( $delimiter, $string, $limit ) );
		}

		$limit = $limit ?: 1;
		$m = new static( str_split( $string ) );

		if( $limit < 1 ) {
			return $m->slice( 0, $limit );
		} elseif( $limit < $m->count() ) {
			return $m->slice( 0, $limit )->push( join( '', $m->slice( $limit )->toArray() ) );
		}

		return $m;
	}


	/**
	 * Creates a new map instance if the value isn't one already.
	 *
	 * Examples:
	 *  Map::from( [] );
	 *  Map::from( null );
	 *  Map::from( 'a' );
	 *  Map::from( new Map() );
	 *  Map::from( new ArrayObject() );
	 *
	 * Results:
	 * A new map instance containing the list of elements. In case of an empty
	 * array or null, the map object will contain an empty list. If a map object
	 * is passed, it will be returned instead of creating a new instance.
	 *
	 * @param mixed $elements List of elements or single element
	 * @return self Map object
	 */
	public static function from( $elements = [] ) : self
	{
		if( $elements instanceof self ) {
			return $elements;
		}

		return new static( $elements );
	}


	/**
	 * Creates a new map instance from a JSON string.
	 *
	 * Examples:
	 *  Map::fromJson( '["a", "b"]' );
	 *  Map::fromJson( '{"a": "b"}' );
	 *  Map::fromJson( '""' );
	 *
	 * Results:
	 *  ['a', 'b']
	 *  ['a' => 'b']
	 *  ['']
	 *
	 * There are several options available for decoding the JSON string:
	 * {@link https://www.php.net/manual/en/function.json-decode.php}
	 * The parameter can be a single JSON_* constant or a bitmask of several
	 * constants combine by bitwise OR (|), e.g.:
	 *
	 *  JSON_BIGINT_AS_STRING|JSON_INVALID_UTF8_IGNORE
	 *
	 * @param int $options Combination of JSON_* constants
	 * @return self Map from decoded JSON string
	 */
	public static function fromJson( string $json, int $options = JSON_BIGINT_AS_STRING ) : self
	{
		if( ( $result = json_decode( $json, true, 512, $options ) ) !== null ) {
			return new static( $result );
		}

		throw new \RuntimeException( 'Not a valid JSON string: ' . $json );
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
	 * Creates a new map by invoking the closure the given number of times.
	 *
	 * Examples:
	 *  Map::times( 3, function( $num ) {
	 *    return $num * 10;
	 *  } );
	 *  Map::times( 3, function( $num, &$key ) {
	 *    $key = $num * 2;
	 *    return $num * 5;
	 *  } );
	 *  Map::times( 2, function( $num ) {
	 *    return new \stdClass();
	 *  } );
	 *
	 * Results:
	 *  [0 => 0, 1 => 10, 2 => 20]
	 *  [0 => 0, 2 => 5, 4 => 10]
	 *  [0 => new \stdClass(), 1 => new \stdClass()]
	 *
	 * @param int $num Number of times the function is called
	 * @param \Closure $callback Function with (value, key) parameters and returns new value
	 */
	public static function times( int $num, \Closure $callback )
	{
		$list = [];

		for( $i = 0; $i < $num; $i++ ) {
			$key = $i;
			$list[$key] = $callback( $i, $key );
		}

		return new self( $list );
	}


	/**
	 * Returns the elements after the given one.
	 *
	 * Examples:
	 *  Map::from( ['a' => 1, 'b' => 0] )->after( 1 );
	 *  Map::from( [0 => 'b', 1 => 'a'] )->after( 'b' );
	 *  Map::from( [0 => 'b', 1 => 'a'] )->after( 'c' );
	 *  Map::from( ['a', 'c', 'b'] )->after( function( $item, $key ) {
	 *      return $item >= 'c';
	 *  } );
	 *
	 * Results:
	 *  ['b' => 0]
	 *  [1 => 'a']
	 *  []
	 *  [2 => 'b']
	 *
	 * The keys are preserved using this method.
	 *
	 * @param mixed $value Value or function with (item, key) parameters
	 * @return self New map with the elements after the given one
	 */
	public function after( $value ) : self
	{
		if( ( $pos = $this->pos( $value ) ) === null ) {
			return new self();
		}

		return new self( array_slice( $this->list, $pos + 1, null, true ) );
	}


	/**
	 * Returns the elements as a plain array.
	 *
	 * @return array Plain array
	 */
	public function all() : array
	{
		return $this->list;
	}


	/**
	 * Sorts all elements in reverse order and maintains the key association.
	 *
	 * Examples:
	 *  Map::from( ['b' => 0, 'a' => 1] )->arsort();
	 *  Map::from( ['a', 'b'] )->arsort();
	 *  Map::from( [0 => 'C', 1 => 'b'] )->arsort();
	 *  Map::from( [0 => 'C', 1 => 'b'] )->arsort( SORT_STRING|SORT_FLAG_CASE );
	 *
	 * Results:
	 *  ['a' => 1, 'b' => 0]
	 *  ['b', 'a']
	 *  [1 => 'b', 0 => 'C']
	 *  [0 => 'C', 1 => 'b'] // because 'C' -> 'c' and 'c' > 'b'
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
	 *  Map::from( [0 => 'C', 1 => 'b'] )->asort();
	 *  Map::from( [0 => 'C', 1 => 'b'] )->arsort( SORT_STRING|SORT_FLAG_CASE );
	 *
	 * Results:
	 *  ['b' => 0, 'a' => 1]
	 *  [1 => 'a', 0 => 'b']
	 *  [0 => 'C', 1 => 'b'] // because 'C' < 'b'
	 *  [1 => 'b', 0 => 'C'] // because 'C' -> 'c' and 'c' > 'b'
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
	 * Returns the elements before the given one.
	 *
	 * Examples:
	 *  Map::from( ['a' => 1, 'b' => 0] )->before( 0 );
	 *  Map::from( [0 => 'b', 1 => 'a'] )->before( 'a' );
	 *  Map::from( [0 => 'b', 1 => 'a'] )->before( 'b' );
	 *  Map::from( ['a', 'c', 'b'] )->before( function( $item, $key ) {
	 *      return $key >= 1;
	 *  } );
	 *
	 * Results:
	 *  ['a' => 1]
	 *  [0 => 'b']
	 *  []
	 *  [0 => 'a']
	 *
	 * The keys are preserved using this method.
	 *
	 * @param mixed $value Value or function with (item, key) parameters
	 * @return self New map with the elements before the given one
	 */
	public function before( $value ) : self
	{
		return new self( array_slice( $this->list, 0, $this->pos( $value ), true ) );
	}


	/**
	 * Calls the given method on all items and returns the result.
	 *
	 * This method can call methods on the map entries that are also implemented
	 * by the map object itself and are therefore not reachable when using the
	 * magic __call() method.
	 *
	 * Examples:
	 *  $item = new MyClass(); // implements methods get() and toArray()
	 *  Map::from( [$item, $item] )->call( 'get', ['myprop'] );
	 *  Map::from( [$item, $item] )->call( 'toArray' );
	 *
	 * Results:
	 * The first example will return ['...', '...'] while the second one returns [[...], [...]].
	 *
	 * If some entries are not objects, they will be skipped. The map keys from the
	 * original map are preserved in the returned map.
	 *
	 * @param string $name Method name
	 * @param array $params List of parameters
	 * @return self Map with results from all elements
	 */
	public function call( string $name, array $params = [] ) : self
	{
		$result = [];

		foreach( $this->list as $key => $item )
		{
			if( is_object( $item ) ) {
				$result[$key] = $item->{$name}( ...$params );
			}
		}

		return new self( $result );
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
	 * Returns the values of a single column/property from an array of arrays or objects in a new map.
	 *
	 * Examples:
	 *  Map::from( [['id' => 'i1', 'val' => 'v1'], ['id' => 'i2', 'val' => 'v2']] )->col( 'val' );
	 *  Map::from( [['id' => 'i1', 'val' => 'v1'], ['id' => 'i2', 'val' => 'v2']] )->col( 'val', 'id' );
	 *  Map::from( [['id' => 'i1', 'val' => 'v1'], ['id' => 'i2', 'val' => 'v2']] )->col( null, 'id' );
	 *  Map::from( [['id' => 'ix', 'val' => 'v1'], ['id' => 'ix', 'val' => 'v2']] )->col( null, 'id' );
	 *  Map::from( [['foo' => ['bar' => 'one', 'baz' => 'two']]] )->col( 'foo/baz', 'foo/bar' );
	 *  Map::from( [['foo' => ['bar' => 'one']]] )->col( 'foo/baz', 'foo/bar' );
	 *  Map::from( [['foo' => ['baz' => 'two']]] )->col( 'foo/baz', 'foo/bar' );
	 *
	 * Results:
	 *  ['v1', 'v2']
	 *  ['i1' => 'v1', 'i2' => 'v2']
	 *  ['i1' => ['id' => 'i1', 'val' => 'v1'], 'i2' => ['id' => 'i2', 'val' => 'v2']]
	 *  ['ix' => ['id' => 'ix', 'val' => 'v2']]
	 *  ['one' => 'two']
	 *  ['one' => null]
	 *  ['two']
	 *
	 * If $indexcol is omitted, it's value is NULL or not set, the result will be indexed from 0-n.
	 * Items with the same value for $indexcol will overwrite previous items and only the last
	 * one will be part of the resulting map.
	 *
	 * This does also work to map values from multi-dimensional arrays by passing the keys
	 * of the arrays separated by the delimiter ("/" by default), e.g. "key1/key2/key3"
	 * to get "val" from ['key1' => ['key2' => ['key3' => 'val']]]. The same applies to
	 * public properties of objects or objects implementing __isset() and __get() methods.
	 *
	 * @param string|null $valuecol Name or path of the value property
	 * @param string|null $indexcol Name or path of the index property
	 * @return self New instance with mapped entries
	 */
	public function col( string $valuecol = null, string $indexcol = null ) : self
	{
		$vparts = explode( $this->sep, $valuecol );
		$iparts = explode( $this->sep, $indexcol );

		if( count( $vparts ) === 1 && count( $iparts ) === 1 ) {
			return new static( array_column( $this->list, $valuecol, $indexcol ) );
		}

		$list = [];

		foreach( $this->list as $item )
		{
			$v = $valuecol ? $this->getValue( $item, $vparts ) : $item;

			if( $indexcol !== null && ( $key = $this->getValue( $item, $iparts ) ) !== null ) {
				$list[(string) $key] = $v;
			} else {
				$list[] = $v;
			}
		}

		return new static( $list );
	}


	/**
	 * Collapses all sub-array elements recursively to a new map overwriting existing keys.
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
		$this->kflatten( $this->list, $result, $depth ?? 0x7fffffff );
		return new self( $result );
	}


	/**
	 * Pushs all of the given elements onto the map with new keys without creating a new map.
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
	 * Combines the values of the map as keys with the passed elements as values.
	 *
	 * Examples:
	 *  Map::from( ['name', 'age'] )->combine( ['Tom', 29] );
	 *
	 * Results:
	 *  ['name' => 'Tom', 'age' => 29]
	 *
	 * @param iterable $values Values of the new map
	 * @return self New map
	 */
	public function combine( iterable $values ) : self
	{
		return new static( array_combine( $this->list, $this->getArray( $values ) ) );
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
	 * Counts the total number of elements in the map.
	 *
	 * @return int Number of elements
	 */
	public function count() : int
	{
		return count( $this->list );
	}


	/**
	 * Counts how often the same values are in the map.
	 *
	 * Examples:
	 *  Map::from( [1, 'foo', 2, 'foo', 1] )->countBy();
	 *  Map::from( [1.11, 3.33, 3.33, 9.99] )->countBy();
	 *  Map::from( ['a@gmail.com', 'b@yahoo.com', 'c@gmail.com'] )->countBy( function( $email ) {
	 *    return substr( strrchr( $email, '@' ), 1 );
	 *  } );
	 *
	 * Results:
	 *  [1 => 2, 'foo' => 2, 2 => 1]
	 *  ['1.11' => 1, '3.33' => 2, '9.99' => 1]
	 *  ['gmail.com' => 2, 'yahoo.com' => 1]
	 *
	 * Counting values does only work for integers and strings because these are
	 * the only types allowed as array keys. All elements are casted to strings
	 * if no callback is passed. Custom callbacks need to make sure that only
	 * string or integer values are returned!
	 *
	 * @param  callable|null $callback Function with (value, key) parameters which returns the value to use for counting
	 * @return self New map with values as keys and their count as value
	 */
	public function countBy( callable $callback = null ) : self
	{
		$callback = $callback ?: function( $value ) {
			return (string) $value;
		};

		return new static( array_count_values( array_map( $callback, $this->list ) ) );
	}


	/**
	 * Dumps the map content and terminates the script.
	 *
	 * The dd() method is very helpful to see what are the map elements passed
	 * between two map methods in a method call chain. It stops execution of the
	 * script afterwards to avoid further output.
	 *
	 * Examples:
	 *  Map::from( ['a' => 'foo', 'b' => 'bar'] )->sort()->dd();
	 *
	 * Results:
	 *  Array
	 *  (
	 *      [0] => bar
	 *      [1] => foo
	 *  )
	 *
	 * @param callable|null $callback Function receiving the map elements as parameter (optional)
	 */
	public function dd( callable $callback = null ) : self
	{
		$this->dump( $callback );
		exit( 1 );
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
	 * Dumps the map content using the given function (print_r by default).
	 *
	 * The dump() method is very helpful to see what are the map elements passed
	 * between two map methods in a method call chain.
	 *
	 * Examples:
	 *  Map::from( ['a' => 'foo', 'b' => 'bar'] )->dump()->asort()->dump( 'var_dump' );
	 *
	 * Results:
	 *  Array
	 *  (
	 *      [a] => foo
	 *      [b] => bar
	 *  )
	 *  array(1) {
	 *    ["b"]=>
	 *    string(3) "bar"
	 *    ["a"]=>
	 *    string(3) "foo"
	 *  }
	 *
	 * @param callable|null $callback Function receiving the map elements as parameter (optional)
	 * @return self Same map for fluid interface
	 */
	public function dump( callable $callback = null ) : self
	{
		$callback ? $callback( $this->list ) : print_r( $this->list );
		return $this;
	}


	/**
	 * Returns the duplicate values from the map.
	 *
	 * The keys in the result map are the same as in the original one. For nested
	 * arrays, you have to pass the name of the column of the nested array which
	 * should be used to check for duplicates.
	 *
	 * Examples:
	 *  Map::from( [1, 2, '1', 3] )->duplicates()
	 *  Map::from( [['p' => '1'], ['p' => 1], ['p' => 2]] )->duplicates( 'p' )
	 *  Map::from( [['i' => ['p' => '1']], ['i' => ['p' => 1]]] )->duplicates( 'i/p' )
	 *
	 * Results:
	 *  [2 => '1']
	 *  [1 => ['i' => ['p' => '1']]]
	 *
	 * This does also work for multi-dimensional arrays by passing the keys
	 * of the arrays separated by the delimiter ("/" by default), e.g. "key1/key2/key3"
	 * to get "val" from ['key1' => ['key2' => ['key3' => 'val']]]. The same applies to
	 * public properties of objects or objects implementing __isset() and __get() methods.
	 *
	 * @param string|null $key Key or path of the nested array or object to check for
	 * @return self New map
	 */
	public function duplicates( string $key = null ) : self
	{
		$list = ( $key !== null ? $this->col( $key )->toArray() : $this->list );
		return new static( array_diff_key( $this->list, array_unique( $list ) ) );
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
	 * The method differs to is() in the fact that it doesn't care about the keys
	 * by default. The elements are only loosely compared and the keys are ignored.
	 *
	 * Values are compared by their string values:
	 * (string) $item1 === (string) $item2
	 *
	 * @param iterable $elements List of elements to test against
	 * @return bool TRUE if both are equal, FALSE if not
	 */
	public function equals( iterable $elements ) : bool
	{
		$elements = $this->getArray( $elements );
		return array_diff( $this->list, $elements ) === [] && array_diff( $elements, $this->list ) === [];
	}


	/**
	 * Verifies that all elements pass the test of the given callback.
	 *
	 * Examples:
	 *  Map::from( [0 => 'a', 1 => 'b'] )->every( function( $value, $key ) {
	 *      return is_string( $value );
	 *  } );
	 *
	 *  Map::from( [0 => 'a', 1 => 100] )->every( function( $value, $key ) {
	 *      return is_string( $value );
	 *  } );
	 *
	 * The first example will return TRUE because all values are a string while
	 * the second example will return FALSE.
	 *
	 * @param \Closure $callback Function with (value, key) parameters and returns TRUE/FALSE
	 * @return bool True if all elements pass the test, false if if fails for at least one element
	 */
	public function every( \Closure $callback ) : bool
	{
		foreach( $this->list as $key => $item )
		{
			if( $callback( $item, $key ) === false ) {
				return false;
			}
		}

		return true;
	}


	/**
	 * Returns a new map without the passed element keys.
	 *
	 * Examples:
	 *  Map::from( ['a' => 1, 'b' => 2, 'c' => 3] )->except( 'b' );
	 *  Map::from( [1 => 'a', 2 => 'b', 3 => 'c'] )->except( [1, 3] );
	 *
	 * Results:
	 *  ['a' => 1, 'c' => 3]
	 *  [2 => 'b']
	 *
	 * @param mixed|array $keys List of keys to remove
	 * @return self New map
	 */
	public function except( $keys ) : self
	{
		return $this->copy()->remove( $keys );
	}


	/**
	 * Applies a filter to all elements of the map and returns a new map.
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
	 * Returns the first/last matching element where the callback returns TRUE.
	 *
	 * Examples:
	 *  Map::from( ['a', 'c', 'e'] )->find( function( $value, $key ) {
	 *      return $value >= 'b';
	 *  } );
	 *  Map::from( ['a', 'c', 'e'] )->find( function( $value, $key ) {
	 *      return $value >= 'b';
	 *  }, null, true );
	 *  Map::from( [] )->find( function( $value, $key ) {
	 *      return $value >= 'b';
	 *  }, 'none' );
	 *  Map::from( [] )->find( function( $value, $key ) {
	 *      return $value >= 'b';
	 *  }, new \Exception( 'error' ) );
	 *
	 * Results:
	 * The first example will return 'c' while the second will return 'e' (last element).
	 * The third one will return "none" and the last one will throw the exception.
	 *
	 * @param \Closure $callback Function with (value, key) parameters and returns TRUE/FALSE
	 * @param mixed $default Default value or exception if the map contains no elements
	 * @param bool $reverse TRUE to test elements from back to front, FALSE for front to back (default)
	 * @return mixed First matching value, passed default value or an exception
	 */
	public function find( \Closure $callback, $default = null, bool $reverse = false )
	{
		foreach( ( $reverse ? array_reverse( $this->list ) : $this->list ) as $key => $value )
		{
			if( $callback( $value, $key ) ) {
				return $value;
			}
		}

		if( $default instanceof \Throwable ) {
			throw $default;
		}

		return $default;
	}


	/**
	 * Returns the first element from the map.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b'] )->first();
	 *  Map::from( [] )->first( 'x' );
	 *  Map::from( [] )->first( new \Exception( 'error' ) );
	 *  Map::from( [] )->first( function() { return rand(); } );
	 *
	 * Results:
	 * The first example will return 'b' and the second one 'x'. The third example
	 * will throw the exception passed if the map contains no elements. In the
	 * fourth example, a random value generated by the closure function will be
	 * returned.
	 *
	 * @param mixed $default Default value or exception if the map contains no elements
	 * @return mixed First value of map, (generated) default value or an exception
	 */
	public function first( $default = null )
	{
		if( ( $value = reset( $this->list ) ) !== false ) {
			return $value;
		}

		if( $default instanceof \Closure ) {
			return $default();
		}

		if( $default instanceof \Throwable ) {
			throw $default;
		}

		return $default;
	}


	/**
	 * Returns the first key from the map.
	 *
	 * Examples:
	 *  Map::from( ['a' => 1, 'b' => 2] )->firstKey();
	 *  Map::from( [] )->firstKey();
	 *
	 * Results:
	 * The first example will return 'a' and the second one NULL.
	 *
	 * @return mixed First key of map or NULL if empty
	 */
	public function firstKey()
	{
		if( function_exists( 'array_key_first' ) ) {
			return array_key_first( $this->list );
		}

		reset( $this->list );
		return key( $this->list );
	}


	/**
	 * Creates a new map with all sub-array elements added recursively withput overwriting existing keys.
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
		$this->flatten( $this->list, $result, $depth ?? 0x7fffffff );
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
	 *  Map::from( ['a' => ['b' => ['c' => 'Y']]] )->get( 'a/b/c' );
	 *  Map::from( [] )->get( 'Y', new \Exception( 'error' ) );
	 *  Map::from( [] )->get( function() { return rand(); } );
	 *
	 * Results:
	 * The first example will return 'X', the second 'Z' and the third 'Y'. The forth
	 * example will throw the exception passed if the map contains no elements. In
	 * the fifth example, a random value generated by the closure function will be
	 * returned.
	 *
	 * This does also work for multi-dimensional arrays by passing the keys
	 * of the arrays separated by the delimiter ("/" by default), e.g. "key1/key2/key3"
	 * to get "val" from ['key1' => ['key2' => ['key3' => 'val']]]. The same applies to
	 * public properties of objects or objects implementing __isset() and __get() methods.
	 *
	 * @param mixed $key Key or path to the requested item
	 * @param mixed $default Default value if no element matches
	 * @return mixed Value from map or default value
	 */
	public function get( $key, $default = null )
	{
		if( array_key_exists( $key, $this->list ) ) {
			return $this->list[$key];
		}

		if( ( $v = $this->getValue( $this->list, explode( $this->sep, $key ) ) ) !== null ) {
			return $v;
		}

		if( $default instanceof \Closure ) {
			return $default();
		}

		if( $default instanceof \Throwable ) {
			throw $default;
		}

		return $default;
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
	 * Returns only items which matches the regular expression.
	 *
	 * All items are converted to string first before they are compared to the
	 * regular expression. Thus, fractions of ".0" will be removed in float numbers
	 * which may result in unexpected results.
	 *
	 * Examples:
	 *  Map::from( ['ab', 'bc', 'cd'] )->grep( '/b/' );
	 *  Map::from( ['ab', 'bc', 'cd'] )->grep( '/a/', PREG_GREP_INVERT );
	 *  Map::from( [1.5, 0, 1.0, 'a'] )->grep( '/^(\d+)?\.\d+$/' );
	 *
	 * Results:
	 *  ['ab', 'bc']
	 *  ['bc', 'cd']
	 *  [1.5] // float 1.0 is converted to string "1"
	 *
	 * The keys are preserved using this method.
	 *
	 * @param string $pattern Regular expression pattern, e.g. "/ab/"
	 * @param int $flags PREG_GREP_INVERT to return elements not matching the pattern
	 * @return self New map containing only the matched elements
	 */
	public function grep( string $pattern, int $flags = 0 ) : self
	{
		if( ( $result = preg_grep( $pattern, $this->list, $flags ) ) === false ) {
			throw new \RuntimeException( 'Regular expression error: ' . preg_last_error_msg() );
		}

		return new static( $result );
	}


	/**
	 * Groups associative array elements or objects by the passed key or closure.
	 *
	 * Instead of overwriting items with the same keys like to the col() method
	 * does, groupBy() keeps all entries in sub-arrays. It's preserves the keys
	 * of the orignal map entries too.
	 *
	 * Examples:
	 *  $list = [
	 *    10 => ['aid' => 123, 'code' => 'x-abc'],
	 *    20 => ['aid' => 123, 'code' => 'x-def'],
	 *    30 => ['aid' => 456, 'code' => 'x-def']
	 *  ];
	 *  Map::from( $list )->groupBy( 'aid' );
	 *  Map::from( $list )->groupBy( function( $item, $key ) {
	 *    return substr( $item['code'], -3 );
	 *  } );
	 *  Map::from( $list )->groupBy( 'xid' );
	 *
	 * Results:
	 *  [
	 *    123 => [10 => ['aid' => 123, 'code' => 'x-abc'], 20 => ['aid' => 123, 'code' => 'x-def']],
	 *    456 => [30 => ['aid' => 456, 'code' => 'x-def']]
	 *  ]
	 *  [
	 *    'abc' => [10 => ['aid' => 123, 'code' => 'x-abc']],
	 *    'def' => [20 => ['aid' => 123, 'code' => 'x-def'], 30 => ['aid' => 456, 'code' => 'x-def']]
	 *  ]
	 *  [
	 *    '' => [
	 *      10 => ['aid' => 123, 'code' => 'x-abc'],
	 *      20 => ['aid' => 123, 'code' => 'x-def'],
	 *      30 => ['aid' => 456, 'code' => 'x-def']
	 *    ]
	 *  ]
	 *
	 * In case the passed key doesn't exist in one or more items, these items
	 * are stored in a sub-array using an empty string as key.
	 *
	 * @param  \Closure|string $key Closure function with (item, idx) parameters returning the key or the key itself to group by
	 * @return self New map with elements grouped by the given key
	 */
	public function groupBy( $key ) : self
	{
		$result = [];

		foreach( $this->list as $idx => $item )
		{
			if( is_callable( $key ) ) {
				$keyval = $key( $item, $idx );
			} elseif( ( is_array( $item ) || $item instanceof \ArrayAccess ) && isset( $item[$key] ) ) {
				$keyval = $item[$key];
			} elseif( is_object( $item ) && isset( $item->{$key} ) ) {
				$keyval = $item->{$key};
			} else {
				$keyval = '';
			}

			$result[$keyval][$idx] = $item;
		}

		return new static( $result );
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
	 *  Map::from( ['a' => ['b' => ['c' => 'Y']]] )->has( 'a/b/c' );
	 *  Map::from( ['a' => 'X', 'b' => 'Y'] )->has( 'c' );
	 *  Map::from( ['a' => 'X', 'b' => 'Y'] )->has( ['a', 'c'] );
	 *  Map::from( ['a' => 'X', 'b' => 'Y'] )->has( 'X' );
	 *
	 * Results:
	 * The first three examples will return TRUE while the other ones will return FALSE
	 *
	 * This does also work for multi-dimensional arrays by passing the keys
	 * of the arrays separated by the delimiter ("/" by default), e.g. "key1/key2/key3"
	 * to get "val" from ['key1' => ['key2' => ['key3' => 'val']]]. The same applies to
	 * public properties of objects or objects implementing __isset() and __get() methods.
	 *
	 * @param mixed|array $key Key of the requested item or list of keys
	 * @return bool TRUE if key or keys are available in map, FALSE if not
	 */
	public function has( $key ) : bool
	{
		foreach( (array) $key as $entry )
		{
			if( array_key_exists( $entry, $this->list ) === false
				&& $this->getValue( $this->list, explode( $this->sep, $entry ) ) === null
			) {
				return false;
			}
		}

		return true;
	}


	/**
	 * Executes callbacks depending on the condition.
	 *
	 * If callbacks for "then" and/or "else" are passed, these callbacks will be
	 * executed and their returned value is passed back within a Map object. In
	 * case no "then" or "else" closure is given, the method will return the same
	 * map object if the condition is true or an empty map object if it's false.
	 *
	 * Examples:
	 *  Map::from( [] )->if( strpos( 'abc', 'b' ) !== false, function( $map ) {
	 *    echo 'found';
	 *  } );
	 *
	 *  Map::from( [] )->if( function( $map ) {
	 *    return $map->empty();
	 *  }, function( $map ) {
	 *    echo 'then';
	 *  } );
	 *
	 *  Map::from( ['a'] )->if( function( $map ) {
	 *    return $map->empty();
	 *  }, function( $map ) {
	 *    echo 'then';
	 *  }, function( $map ) {
	 *    echo 'else';
	 *  } );
	 *
	 *  Map::from( ['a'] )->if( function( $map ) {
	 *    return $map->search( 'a' );
	 *  } );
	 *
	 *  Map::from( ['a'] )->if( function( $map ) {
	 *    return $map->search( 'b' );
	 *  } )->sort();
	 *
	 * Results:
	 * The first example returns "found" while the second one returns "then" and
	 * the third one "else". The forth one will return the same map and the last
	 * one an empty map so nothing will be sorted.
	 *
	 * Since PHP 7.4, you can also pass arrow function like `fn($map) => $map->has('c')`
	 * (a short form for anonymous closures) as parameters. The automatically have access
	 * to previously defined variables but can not modify them. Also, they can not have
	 * a void return type and must/will always return something. Details about
	 * [PHP arrow functions](https://www.php.net/manual/en/functions.arrow.php)
	 *
	 * @param \Closure|bool $condition Boolean or function with (map) parameter returning a boolean
	 * @param \Closure|null $then Function with (map) parameter (optional)
	 * @param \Closure|null $else Function with (map) parameter (optional)
	 * @return self Same map for fluid interface
	 */
	public function if( $condition, \Closure $then = null, \Closure $else = null ) : self
	{
		if( $condition instanceof \Closure ) {
			$condition = $condition( $this );
		}

		if( $condition ) {
			$result = $then ? $then( $this ) : $this;
		} elseif( $else ) {
			$result = $else( $this );
		} else {
			$result = [];
		}

		return new self( $result );
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

		foreach( $element as $entry )
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
	 * This method is an alias for in(). For performance reasons, in() should be
	 * preferred because it uses one method call less than includes().
	 *
	 * @param mixed|array $element Element or elements to search for in the map
	 * @param bool $strict TRUE to check the type too, using FALSE '1' and 1 will be the same
	 * @return bool TRUE if all elements are available in map, FALSE if not
	 */
	public function includes( $element, bool $strict = false ) : bool
	{
		return $this->in( $element, $strict );
	}


	/**
	 * Returns the numerical index of the given key.
	 *
	 * Examples:
	 *  Map::from( [4 => 'a', 8 => 'b'] )->index( '8' );
	 *  Map::from( [4 => 'a', 8 => 'b'] )->index( function( $key ) {
	 *      return $key == '8';
	 *  } );
	 *
	 * Results:
	 * Both examples will return "1" because the value "b" is at the second position
	 * and the returned index is zero based so the first item has the index "0".
	 *
	 * @param \Closure|string|int $value Key to search for or function with (key) parameters return TRUE if key is found
	 * @return int|null Position of the found value (zero based) or NULL if not found
	 */
	public function index( $value ) : ?int
	{
		if( $value instanceof \Closure )
		{
			$pos = 0;

			foreach( $this->list as $key => $item )
			{
				if( $value( $key ) ) {
					return $pos;
				}

				++$pos;
			}

			return null;
		}

		$pos = array_search( $value, array_keys( $this->list ) );
		return $pos !== false ? $pos : null;
	}


	/**
	 * Inserts the value or values after the given element.
	 *
	 * Examples:
	 *  Map::from( ['a' => 'foo', 'b' => 'bar'] )->insertAfter( 'foo', 'baz' );
	 *  Map::from( ['foo', 'bar'] )->insertAfter( 'foo', ['baz', 'boo'] );
	 *  Map::from( ['foo', 'bar'] )->insertAfter( null, 'baz' );
	 *
	 * Results:
	 *  ['a' => 'foo', 0 => 'baz', 'b' => 'bar']
	 *  ['foo', 'baz', 'boo', 'bar']
	 *  ['foo', 'bar', 'baz']
	 *
	 * Numerical array indexes are not preserved.
	 *
	 * @param mixed $element Element after the value is inserted
	 * @param mixed $value Element or list of elements to insert
	 * @return self Updated map for fluid interface
	 */
	public function insertAfter( $element, $value ) : self
	{
		$position = ( $element !== null && ( $pos = $this->pos( $element ) ) !== null ? $pos : count( $this->list ) );
		array_splice( $this->list, $position + 1, 0, $this->getArray( $value ) );

		return $this;
	}


	/**
	 * Inserts the value or values before the given element.
	 *
	 * Examples:
	 *  Map::from( ['a' => 'foo', 'b' => 'bar'] )->insertBefore( 'bar', 'baz' );
	 *  Map::from( ['foo', 'bar'] )->insertBefore( 'bar', ['baz', 'boo'] );
	 *  Map::from( ['foo', 'bar'] )->insertBefore( null, 'baz' );
	 *
	 * Results:
	 *  ['a' => 'foo', 0 => 'baz', 'b' => 'bar']
	 *  ['foo', 'baz', 'boo', 'bar']
	 *  ['foo', 'bar', 'baz']
	 *
	 * Numerical array indexes are not preserved.
	 *
	 * @param mixed $element Element before the value is inserted
	 * @param mixed $value Element or list of elements to insert
	 * @return self Updated map for fluid interface
	 */
	public function insertBefore( $element, $value ) : self
	{
		$position = ( $element !== null && ( $pos = $this->pos( $element ) ) !== null ? $pos : count( $this->list ) );
		array_splice( $this->list, $position, 0, $this->getArray( $value ) );

		return $this;
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
	 * Tests if the map consists of the same keys and values
	 *
	 * Examples:
	 *  Map::from( ['a', 'b'] )->is( ['b', 'a'] );
	 *  Map::from( ['a', 'b'] )->is( ['b', 'a'], true );
	 *  Map::from( [1, 2] )->is( ['1', '2'] );
	 *
	 * Results:
	 *  The first example returns TRUE while the second and third one returns FALSE
	 *
	 * @param iterable $list List of key/value pairs to compare with
	 * @param bool $strict TRUE for comparing order of elements too, FALSE for key/values only
	 * @param bool TRUE if given list is equal, FALSE if not
	 */
	public function is( iterable $list, bool $strict = false ) : bool
	{
		$list = $this->getArray( $list );

		if( $strict ) {
			return $this->list === $list;
		}

		return $this->list == $list;
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
	 * Returns the keys of the all elements in a new map object.
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
	 *  Map::from( [] )->last( new \Exception( 'error' ) );
	 *  Map::from( [] )->last( function() { return rand(); } );
	 *
	 * Results:
	 * The first example will return 'b' and the second one 'x'. The third example
	 * will throw the exception passed if the map contains no elements. In the
	 * fourth example, a random value generated by the closure function will be
	 * returned.
	 *
	 * @param mixed $default Default value or exception if the map contains no elements
	 * @return mixed Last value of map, (generated) default value or an exception
	 */
	public function last( $default = null )
	{
		if( ( $value = end( $this->list ) ) !== false ) {
			return $value;
		}

		if( $default instanceof \Closure ) {
			return $default();
		}

		if( $default instanceof \Throwable ) {
			throw $default;
		}

		return $default;
	}


	/**
	 * Returns the last key from the map.
	 *
	 * Examples:
	 *  Map::from( ['a' => 1, 'b' => 2] )->lastKey();
	 *  Map::from( [] )->lastKey();
	 *
	 * Results:
	 * The first example will return 'b' and the second one NULL.
	 *
	 * @return mixed Last key of map or NULL if empty
	 */
	public function lastKey()
	{
		if( function_exists( 'array_key_last' ) ) {
			return array_key_last( $this->list );
		}

		end( $this->list );
		return key( $this->list );
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
	 * Returns the maximum value of all elements.
	 *
	 * Examples:
	 *  Map::from( [1, 3, 2, 5, 4] )->max()
	 *  Map::from( ['bar', 'foo', 'baz'] )->max()
	 *  Map::from( [['p' => 30], ['p' => 50], ['p' => 10]] )->max( 'p' )
	 *  Map::from( [['i' => ['p' => 30]], ['i' => ['p' => 50]]] )->max( 'i/p' )
	 *
	 * Results:
	 * The first line will return "5", the second one "foo" and the third/fourth
	 * one return both 50.
	 *
	 * This does also work for multi-dimensional arrays by passing the keys
	 * of the arrays separated by the delimiter ("/" by default), e.g. "key1/key2/key3"
	 * to get "val" from ['key1' => ['key2' => ['key3' => 'val']]]. The same applies to
	 * public properties of objects or objects implementing __isset() and __get() methods.
	 *
	 * Be careful comparing elements of different types because this can have
	 * unpredictable results due to the PHP comparison rules:
	 * {@link https://www.php.net/manual/en/language.operators.comparison.php}
	 *
	 * @param string|null $key Key or path to the value of the nested array or object
	 * @return mixed Maximum value or NULL if there are no elements in the map
	 */
	public function max( string $key = null )
	{
		if( empty( $this->list ) ) {
			return null;
		}

		return max( $key !== null ? $this->col( $key )->toArray() : $this->list );
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
	 *  Map::from( ['a' => 1, 'b' => 2] )->merge( ['b' => 4, 'c' => 6], true );
	 *
	 * Results:
	 *  ['a', 'b', 'b', 'c']
	 *  ['a' => 1, 'b' => 4, 'c' => 6]
	 *  ['a' => 1, 'b' => [2, 4], 'c' => 6]
	 *
	 * The method is similar to replace() but doesn't replace elements with
	 * the same numeric keys. If you want to be sure that all passed elements
	 * are added without replacing existing ones, use concat() instead.
	 *
	 * @param iterable $elements List of elements
	 * @param bool $recursive TRUE to merge nested arrays too, FALSE for first level elements only
	 * @return self Updated map for fluid interface
	 */
	public function merge( iterable $elements, bool $recursive = false ) : self
	{
		if( $recursive ) {
			$this->list = array_merge_recursive( $this->list, $this->getArray( $elements ) );
		} else {
			$this->list = array_merge( $this->list, $this->getArray( $elements ) );
		}

		return $this;
	}


	/**
	 * Returns the minimum value of all elements.
	 *
	 * Examples:
	 *  Map::from( [2, 3, 1, 5, 4] )->min()
	 *  Map::from( ['baz', 'foo', 'bar'] )->min()
	 *  Map::from( [['p' => 30], ['p' => 50], ['p' => 10]] )->min( 'p' )
	 *  Map::from( [['i' => ['p' => 30]], ['i' => ['p' => 50]]] )->min( 'i/p' )
	 *
	 * Results:
	 * The first line will return "1", the second one "bar", the third one
	 * returns 10 while the last one returns 30.
	 *
	 * This does also work for multi-dimensional arrays by passing the keys
	 * of the arrays separated by the delimiter ("/" by default), e.g. "key1/key2/key3"
	 * to get "val" from ['key1' => ['key2' => ['key3' => 'val']]]. The same applies to
	 * public properties of objects or objects implementing __isset() and __get() methods.
	 *
	 * Be careful comparing elements of different types because this can have
	 * unpredictable results due to the PHP comparison rules:
	 * {@link https://www.php.net/manual/en/language.operators.comparison.php}
	 *
	 * @param string|null $key Key or path to the value of the nested array or object
	 * @return mixed Minimum value or NULL if there are no elements in the map
	 */
	public function min( string $key = null )
	{
		if( empty( $this->list ) ) {
			return null;
		}

		return min( $key !== null ? $this->col( $key )->toArray() : $this->list );
	}


	/**
	 * Returns every nth element from the map.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b', 'c', 'd', 'e', 'f'] )->nth( 2 );
	 *  Map::from( ['a', 'b', 'c', 'd', 'e', 'f'] )->nth( 2, 1 );
	 *
	 * Results:
	 *  ['a', 'c', 'e']
	 *  ['b', 'd', 'f']
	 *
	 * @param int $step Step width
	 * @param int $offset Number of element to start from (0-based)
	 * @return self New map
	 */
	public function nth( int $step, int $offset = 0 ) : self
	{
		$pos = 0;
		$result = [];

		foreach( $this->list as $key => $item )
		{
			if( $pos++ % $step === $offset ) {
				$result[$key] = $item;
			}
		}

		return new static( $result );
	}


	/**
	 * Determines if an element exists at an offset.
	 *
	 * Examples:
	 *  $map = Map::from( ['a' => 1, 'b' => 3, 'c' => null] );
	 *  isset( $map['b'] );
	 *  isset( $map['c'] );
	 *  isset( $map['d'] );
	 *
	 * Results:
	 *  The first isset() will return TRUE while the second and third one will return FALSE
	 *
	 * @param mixed $key Key to check for
	 * @return bool TRUE if key exists, FALSE if not
	 */
	public function offsetExists( $key )
	{
		return isset( $this->list[$key] );
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
		return $this->list[$key] ?? null;
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
	 * Returns a new map with only those elements specified by the given keys.
	 *
	 * Examples:
	 *  Map::from( ['a' => 1, 0 => 'b'] )->only( 'a' );
	 *  Map::from( ['a' => 1, 0 => 'b', 1 => 'c'] )->only( [0, 1] );
	 *
	 * Results:
	 *  ['a' => 1]
	 *  [0 => 'b', 1 => 'c']
	 *
	 * @param iterable|array|string|int $keys Keys of the elements that should be returned
	 * @return self New map with only the elements specified by the keys
	 */
	public function only( $keys ) : self
	{
		return $this->intersectKeys( array_flip( $this->getArray( $keys ) ) );
	}


	/**
	 * Fill up to the specified length with the given value
	 *
	 * In case the given number is smaller than the number of element that are
	 * already in the list, the map is unchanged. If the size is positive, the
	 * new elements are padded on the right, if it's negative then the elements
	 * are padded on the left.
	 *
	 * Examples:
	 *  Map::from( [1, 2, 3] )->pad( 5 );
	 *  Map::from( [1, 2, 3] )->pad( -5 );
	 *  Map::from( [1, 2, 3] )->pad( 5, '0' );
	 *  Map::from( [1, 2, 3] )->pad( 2 );
	 *
	 * Results:
	 *  [1, 2, 3, null, null]
	 *  [null, null, 1, 2, 3]
	 *  [1, 2, 3, '0', '0']
	 *  [1, 2, 3]
	 *
	 * @param int $size Total number of elements that should be in the list
	 * @return self New map
	 */
	public function pad( int $size, $value = null ) : self
	{
		return new static( array_pad( $this->list, $size, $value ) );
	}


	/**
	 * Breaks the list of elements into the given number of groups.
	 *
	 * Examples:
	 *  Map::from( [1, 2, 3, 4, 5] )->partition( 3 );
	 *  Map::from( [1, 2, 3, 4, 5] )->partition( function( $val, $idx ) {
	 *		return $idx % 3;
	 *	} );
	 *
	 * Results:
	 *  [[0 => 1, 1 => 2], [2 => 3, 3 => 4], [4 => 5]]
	 *  [0 => [0 => 1, 3 => 4], 1 => [1 => 2, 4 => 5], 2 => [2 => 3]]
	 *
	 * The keys of the original map are preserved in the returned map.
	 *
	 * @param \Closure|int $number Function with (value, index) as arguments returning the bucket key or number of groups
	 * @return self New map
	 */
	public function partition( $number ) : self
	{
		if( empty( $this->list ) ) {
			return new static();
		}

		$result = [];

		if( $number instanceof \Closure )
		{
			foreach( $this->list as $idx => $item ) {
				$result[$number( $item, $idx )][$idx] = $item;
			}

			return new static( $result );
		}
		elseif( is_int( $number ) )
		{
			$start = 0;
			$size = (int) ceil( count( $this->list ) / $number );

			for( $i = 0; $i < $number; $i++ )
			{
				$result[] = array_slice( $this->list, $start, $size, true );
				$start += $size;
			}

			return new static( $result );
		}

		throw new \InvalidArgumentException( 'Parameter is no closure or integer' );
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
	 * Returns the numerical index of the value.
	 *
	 * Examples:
	 *  Map::from( [4 => 'a', 8 => 'b'] )->pos( 'b' );
	 *  Map::from( [4 => 'a', 8 => 'b'] )->pos( function( $item, $key ) {
	 *      return $item === 'b';
	 *  } );
	 *
	 * Results:
	 * Both examples will return "1" because the value "b" is at the second position
	 * and the returned index is zero based so the first item has the index "0".
	 *
	 * @param \Closure|string|int $value Value to search for or function with (item, key) parameters return TRUE if value is found
	 * @return int|null Position of the found value (zero based) or NULL if not found
	 */
	public function pos( $value ) : ?int
	{
		$pos = 0;

		if( $value instanceof \Closure )
		{
			foreach( $this->list as $key => $item )
			{
				if( $value( $item, $key ) ) {
					return $pos;
				}

				++$pos;
			}
		}

		foreach( $this->list as $key => $item )
		{
			if( $item === $value ) {
				return $pos;
			}

			++$pos;
		}

		return null;
	}


	/**
	 * Adds a prefix in front of each map entry.
	 *
	 * By defaul, nested arrays are walked recusively so all entries at all levels are prefixed.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b'] )->prefix( '1-' );
	 *  Map::from( ['a', ['b']] )->prefix( '1-' );
	 *  Map::from( ['a', ['b']] )->prefix( '1-', 1 );
	 *  Map::from( ['a', 'b'] )->prefix( function( $item, $key ) {
	 *      return ( ord( $item ) + ord( $key ) ) . '-';
	 *  } );
	 *
	 * Results:
	 *  The first example returns ['1-a', '1-b'] while the second one will return
	 *  ['1-a', ['1-b']]. In the third example, the depth is limited to the first
	 *  level only so it will return ['1-a', ['b']]. The forth example passing
	 *  the closure will return ['145-a', '147-b'].
	 *
	 * @param \Closure|string $prefix Prefix string or anonymous function with ($item, $key) as parameters
	 * @param int|null $depth Maximum depth to dive into multi-dimensional arrays starting from "1"
	 * @return self Updated map for fluid interface
	 */
	public function prefix( $prefix, int $depth = null ) : self
	{
		$fcn = function( array $list, $prefix, int $depth ) use ( &$fcn ) {

			foreach( $list as $key => $item )
			{
				if( is_array( $item ) ) {
					$list[$key] = $depth > 1 ? $fcn( $item, $prefix, $depth - 1 ) : $item;
				} else {
					$list[$key] = ( is_callable( $prefix ) ? $prefix( $item, $key ) : $prefix ) . $item;
				}
			}

			return $list;
		};

		$this->list = $fcn( $this->list, $prefix, $depth ?? 0x7fffffff );
		return $this;
	}


	/**
	 * Pushes an element onto the beginning of the map without returning a new map.
	 *
	 * This method is an alias for unshift().
	 *
	 * @param mixed $value Item to add at the beginning
	 * @param mixed $key Key for the item
	 * @return self Same map for fluid interface
	 */
	public function prepend( $value, $key = null ) : self
	{
		return $this->unshift( $value, $key );
	}


	/**
	 * Returns and removes an element from the map by its key.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b', 'c'] )->pull( 1 );
	 *  Map::from( ['a', 'b', 'c'] )->pull( 'x', 'none' );
	 *  Map::from( [] )->pull( 'Y', new \Exception( 'error' ) );
	 *  Map::from( [] )->pull( 'Z', function() { return rand(); } );
	 *
	 * Results:
	 * The first example will return "b" and the map contains ['a', 'c'] afterwards.
	 * The second one will return "none" and the map content stays untouched. If you
	 * pass an exception as default value, it will throw that exception if the map
	 * contains no elements. In the fourth example, a random value generated by the
	 * closure function will be returned.
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
	 * Returns one or more random element from the map incl. their keys.
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

		if( empty( $this->list ) ) {
			return new self();
		}

		if( ( $num = count( $this->list ) ) < $max ) {
			$max = $num;
		}

		$keys = array_rand( $this->list, $max );

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
	 * Removes all matched elements and returns a new map.
	 *
	 * Examples:
	 *  Map::from( [2 => 'a', 6 => 'b', 13 => 'm', 30 => 'z'] )->reject( function( $value, $key ) {
	 *      return $value < 'm';
	 *  } );
	 *  Map::from( [2 => 'a', 13 => 'm', 30 => 'z'] )->reject( 'm' );
	 *  Map::from( [2 => 'a', 6 => null, 13 => 'm'] )->reject();
	 *
	 * Results:
	 *  [13 => 'm', 30 => 'z']
	 *  [2 => 'a', 30 => 'z']
	 *  [6 => null]
	 *
	 * This method is the inverse of the filter() and should return TRUE if the
	 * item should be removed from the returned map.
	 *
	 * If no callback is passed, all values which are NOT empty, null or false will be
	 * removed.
	 *
	 * @param Closure|mixed $callback Function with (item) parameter which returns TRUE/FALSE or value to compare with
	 * @return self New map
	 */
	public function reject( $callback = true ) : self
	{
		$isCallable = $callback instanceof \Closure;

		return new static( array_filter( $this->list, function( $value, $key ) use  ( $callback, $isCallable ) {
			return $isCallable ? !$callback( $value, $key ) : $value != $callback;
		}, ARRAY_FILTER_USE_BOTH ) );
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
	 * @param iterable|array|string|int $keys List of keys to remove
	 * @return self Same map for fluid interface
	 */
	public function remove( $keys ) : self
	{
		foreach( $this->getArray( $keys ) as $key ) {
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
	 * Reverses the element order with keys without returning a new map.
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
	 * Sorts all elements in reverse order using new keys.
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
	 * Sets the seperator for paths to values in multi-dimensional arrays or objects.
	 *
	 * This method only changes the separator for the current map instance. To
	 * change the separator for all maps created afterwards, use the static
	 * delimiter() method instead.
	 *
	 * Examples:
	 *  Map::from( ['foo' => ['bar' => 'baz']] )->sep( '/' )->get( 'foo/bar' );
	 *
	 * Results:
	 *  'baz'
	 *
	 * @param string|null $char Separator character, e.g. "." for "key.to.value" instead of "key/to/value"
	 * @return self Same map for fluid interface
	 */
	public function sep( string $char ) : self
	{
		$this->sep = $char;
		return $this;
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
		$this->list[(string) $key] = $value;
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
	 *  Map::from( [2 => 'a', 4 => 'b'] )->shuffle( true );
	 *
	 * Results:
	 * The map in the first example will contain "a" and "b" in random order and
	 * with new keys assigned. The second call will also return all values in
	 * random order but preserves the keys of the original list.
	 *
	 * @param bool $assoc True to preserve keys, false to assign new keys
	 * @return self Updated map for fluid interface
	 */
	public function shuffle( bool $assoc = false ) : self
	{
		if( $assoc )
		{
			$keys = array_keys( $this->list );
			shuffle( $keys );
			$list = [];

			foreach( $keys as $key ) {
				$list[$key] = $this->list[$key];
			}

			$this->list = $list;
		}
		else
		{
			shuffle( $this->list );
		}


		return $this;
	}


	/**
	 * Returns a new map with the given number of items skipped.
	 *
	 * The keys of the items returned in the new map are the same as in the original one.
	 *
	 * Examples:
	 *  Map::from( [1, 2, 3, 4] )->skip( 2 );
	 *  Map::from( [1, 2, 3, 4] )->skip( function( $item, $key ) {
	 *      return $item < 4;
	 *  } );
	 *
	 * Results:
	 *  [2 => 3, 3 => 4]
	 *  [3 => 4]
	 *
	 * @param \Closure|int $offset Number of items to skip or function($item, $key) returning true for skipped items
	 * @return self New map
	 */
	public function skip( $offset ) : self
	{
		if( is_scalar( $offset ) ) {
			return new static( array_slice( $this->list, (int) $offset, null, true ) );
		}

		if( is_callable( $offset ) )
		{
			$idx = 0;

			foreach( $this->list as $key => $item )
			{
				if( !$offset( $item, $key ) ) {
					break;
				}

				++$idx;
			}

			return new static( array_slice( $this->list, $idx, null, true ) );
		}

		throw new \InvalidArgumentException( 'Only an integer or a closure is allowed as first argument for skip()' );
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
	 * @param int|null $length Number of elements to return or NULL for no limit
	 * @return self New map
	 */
	public function slice( int $offset, int $length = null ) : self
	{
		return new static( array_slice( $this->list, $offset, $length, true ) );
	}


	/**
	 * Tests if at least one element passes the test or is part of the map.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b'] )->some( 'a' );
	 *  Map::from( ['a', 'b'] )->some( ['a', 'c'] );
	 *  Map::from( ['a', 'b'] )->some( function( $item, $key ) {
	 *    return $item === 'a'
	 *  } );
	 *  Map::from( ['a', 'b'] )->some( ['c', 'd'] );
	 *  Map::from( ['1', '2'] )->some( [2], true );
	 *
	 * Results:
	 * The first three examples will return TRUE while the fourth and fifth will return FALSE
	 *
	 * @param \Closure|iterable|mixed $values Anonymous function with (item, key) parameter, element or list of elements to test against
	 * @param bool $strict TRUE to check the type too, using FALSE '1' and 1 will be the same
	 * @return bool TRUE if at least one element is available in map, FALSE if the map contains none of them
	 */
	public function some( $values, bool $strict = false ) : bool
	{
		if( is_iterable( $values ) )
		{
			foreach( $values as $entry )
			{
				if( in_array( $entry, $this->list, $strict ) === true ) {
					return true;
				}
			}

			return false;
		}
		elseif( is_callable( $values ) )
		{
			foreach( $this->list as $key => $item )
			{
				if( $values( $item, $key ) ) {
					return true;
				}
			}
		}
		elseif( in_array( $values, $this->list, $strict ) === true )
		{
			return true;
		}

		return false;
	}


	/**
	 * Sorts all elements using new keys.
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
	 *  Map::from( ['a', 'b', 'c'] )->splice( 1 );
	 *  Map::from( ['a', 'b', 'c'] )->splice( 1, 1, ['x', 'y'] );
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
	 * Numerical array indexes are not preserved.
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
	 * Adds a suffix at the end of each map entry.
	 *
	 * By defaul, nested arrays are walked recusively so all entries at all levels are suffixed.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b'] )->suffix( '-1' );
	 *  Map::from( ['a', ['b']] )->suffix( '-1' );
	 *  Map::from( ['a', ['b']] )->suffix( '-1', 1 );
	 *  Map::from( ['a', 'b'] )->suffix( function( $item, $key ) {
	 *      return '-' . ( ord( $item ) + ord( $key ) );
	 *  } );
	 *
	 * Results:
	 *  The first example returns ['a-1', 'b-1'] while the second one will return
	 *  ['a-1', ['b-1']]. In the third example, the depth is limited to the first
	 *  level only so it will return ['a-1', ['b']]. The forth example passing
	 *  the closure will return ['a-145', 'b-147'].
	 *
	 * @param \Closure|string $suffix Suffix string or anonymous function with ($item, $key) as parameters
	 * @param int|null $depth Maximum depth to dive into multi-dimensional arrays starting from "1"
	 * @return self Updated map for fluid interface
	 */
	public function suffix( $suffix, int $depth = null ) : self
	{
		$fcn = function( $list, $suffix, $depth ) use ( &$fcn ) {

			foreach( $list as $key => $item )
			{
				if( is_array( $item ) ) {
					$list[$key] = $depth > 1 ? $fcn( $item, $suffix, $depth - 1 ) : $item;
				} else {
					$list[$key] = $item . ( is_callable( $suffix ) ? $suffix( $item, $key ) : $suffix );
				}
			}

			return $list;
		};

		$this->list = $fcn( $this->list, $suffix, $depth ?? 0x7fffffff );
		return $this;
	}


	/**
	 * Returns the sum of all integer and float values in the map.
	 *
	 * Examples:
	 *  Map::from( [1, 3, 5] )->sum();
	 *  Map::from( [1, 'sum', 5] )->sum();
	 *  Map::from( [['p' => 30], ['p' => 50], ['p' => 10]] )->sum( 'p' );
	 *  Map::from( [['i' => ['p' => 30]], ['i' => ['p' => 50]]] )->sum( 'i/p' );
	 *
	 * Results:
	 * The first line will return "9", the second one "6", the third one "90"
	 * and the last one "80".
	 *
	 * This does also work for multi-dimensional arrays by passing the keys
	 * of the arrays separated by the delimiter ("/" by default), e.g. "key1/key2/key3"
	 * to get "val" from ['key1' => ['key2' => ['key3' => 'val']]]. The same applies to
	 * public properties of objects or objects implementing __isset() and __get() methods.
	 *
	 * @param string|null $key Key or path to the values in the nested array or object to sum up
	 * @return mixed Sum of all elements or 0 if there are no elements in the map
	 */
	public function sum( string $key = null ) : int
	{
		return array_sum( $key !== null ? $this->col( $key )->toArray() : $this->list );
	}


	/**
	 * Returns a new map with the given number of items.
	 *
	 * The keys of the items returned in the new map are the same as in the original one.
	 *
	 * Examples:
	 *  Map::from( [1, 2, 3, 4] )->take( 2 );
	 *  Map::from( [1, 2, 3, 4] )->take( 2, 1 );
	 *  Map::from( [1, 2, 3, 4] )->take( 2, -2 );
	 *  Map::from( [1, 2, 3, 4] )->take( 2, function( $item, $key ) {
	 *      return $item < 2;
	 *  } );
	 *
	 * Results:
	 *  [0 => 1, 1 => 2]
	 *  [1 => 2, 2 => 3]
	 *  [2 => 3, 3 => 4]
	 *  [1 => 2, 2 => 3]
	 *
	 * @param int $size Number of items to return
	 * @param \Closure|int $offset Number of items to skip or function($item, $key) returning true for skipped items
	 * @return self New map
	 */
	public function take( int $size, $offset = 0 ) : self
	{
		if( is_scalar( $offset ) ) {
			return new static( array_slice( $this->list, (int) $offset, $size, true ) );
		}

		if( is_callable( $offset ) )
		{
			$idx = 0;

			foreach( $this->list as $key => $item )
			{
				if( !$offset( $item, $key ) ) {
					break;
				}

				++$idx;
			}

			return new static( array_slice( $this->list, $idx, $size, true ) );
		}

		throw new \InvalidArgumentException( 'Only an integer or a closure is allowed as second argument for take()' );
	}


	/**
	 * Passes a clone of the map to the given callback.
	 *
	 * Use it to "tap" into a chain of methods to check the state between two
	 * method calls. The original map is not altered by anything done in the
	 * callback.
	 *
	 * Examples:
	 *  Map::from( [3, 2, 1] )->rsort()->tap( function( $map ) {
	 *    print_r( $map->remove( 0 )->toArray() );
	 *  } )->first();
	 *
	 * Results:
	 * It will sort the list in reverse order(`[1, 2, 3]`) while keeping the keys,
	 * then prints the items without the first (`[2, 3]`) in the function passed
	 * to `tap()` and returns the first item ("1") at the end.
	 *
	 * @param callable $callback Function receiving ($map) parameter
	 * @return self Same map for fluid interface
	 */
	public function tap( callable $callback ) : self
	{
		$callback( clone $this );
		return $this;
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
	 * Creates a HTTP query string from the map elements.
	 *
	 * Examples:
	 *  Map::from( ['a' => 1, 'b' => 2] )->toUrl();
	 *  Map::from( ['a' => ['b' => 'abc', 'c' => 'def'], 'd' => 123] )->toUrl();
	 *
	 * Results:
	 *  a=1&b=2
	 *  a%5Bb%5D=abc&a%5Bc%5D=def&d=123
	 *
	 * @return string Parameter string for GET requests
	 */
	public function toUrl() : string
	{
		return http_build_query( $this->list, null, '&', PHP_QUERY_RFC3986 );
	}


	/**
	 * Exchanges rows and columns for a two dimensional map.
	 *
	 * Examples:
	 *  Map::from( [
	 *    ['name' => 'A', 2020 => 200, 2021 => 100, 2022 => 50],
	 *    ['name' => 'B', 2020 => 300, 2021 => 200, 2022 => 100],
	 *    ['name' => 'C', 2020 => 400, 2021 => 300, 2022 => 200],
	 *  ] )->transpose();
	 *
	 *  Map::from( [
	 *    ['name' => 'A', 2020 => 200, 2021 => 100, 2022 => 50],
	 *    ['name' => 'B', 2020 => 300, 2021 => 200],
	 *    ['name' => 'C', 2020 => 400]
	 *  ] );
	 *
	 * Results:
	 *  [
	 *    'name' => ['A', 'B', 'C'],
	 *    2020 => [200, 300, 400],
	 *    2021 => [100, 200, 300],
	 *    2022 => [50, 100, 200]
	 *  ]
	 *
	 *  [
	 *    'name' => ['A', 'B', 'C'],
	 *    2020 => [200, 300, 400],
	 *    2021 => [100, 200],
	 *    2022 => [50]
	 *  ]
	 *
	 * @return self New map
	 */
	public function transpose() : self
	{
		$result = [];

		foreach( $this->first( [] ) as $key => $col ) {
			$result[$key] = array_column( $this->list, $key );
		}

		return new static( $result );
	}


	/**
	 * Traverses trees of nested items passing each item to the callback.
	 *
	 * This does work for nested arrays and objects with public properties or
	 * objects implementing __isset() and __get() methods. To build trees
	 * of nested items, use the tree() method.
	 *
	 * Examples:
	 *   Map::from( [[
	 *     'id' => 1, 'pid' => null, 'name' => 'n1', 'children' => [
	 *       ['id' => 2, 'pid' => 1, 'name' => 'n2', 'children' => []],
	 *       ['id' => 3, 'pid' => 1, 'name' => 'n3', 'children' => []]
	 *     ]
	 *   ]] )->traverse();
	 *
	 *   Map::from( [[
	 *     'id' => 1, 'pid' => null, 'name' => 'n1', 'children' => [
	 *       ['id' => 2, 'pid' => 1, 'name' => 'n2', 'children' => []],
	 *       ['id' => 3, 'pid' => 1, 'name' => 'n3', 'children' => []]
	 *     ]
	 *   ]] )->traverse( function( $entry, $key, $level ) {
	 *     return str_repeat( '-', $level ) . '- ' . $entry['name'];
	 *   } );
	 *
	 *   Map::from( [[
	 *     'id' => 1, 'pid' => null, 'name' => 'n1', 'children' => [
	 *       ['id' => 2, 'pid' => 1, 'name' => 'n2', 'children' => []],
	 *       ['id' => 3, 'pid' => 1, 'name' => 'n3', 'children' => []]
	 *     ]
	 *   ]] )->traverse( function( $entry, $key, $level ) {
	 *     return !isset( $entry['children'] ) ? $entry : null;
	 *   } )->filter();
	 *
	 *   Map::from( [[
	 *     'id' => 1, 'pid' => null, 'name' => 'n1', 'nodes' => [
	 *       ['id' => 2, 'pid' => 1, 'name' => 'n2', 'nodes' => []]
	 *     ]
	 *   ]] )->traverse( null, 'nodes' );
	 *
	 * Results:
	 *   [
	 *     ['id' => 1, 'pid' => null, 'name' => 'n1', 'children' => [...]],
	 *     ['id' => 2, 'pid' => 1, 'name' => 'n2', 'children' => []],
	 *     ['id' => 3, 'pid' => 1, 'name' => 'n3', 'children' => []],
	 *   ]
	 *
	 *   ['- n1', '-- n2', '-- n3']
	 *
	 *   [
	 *     ['id' => 2, 'pid' => 1, 'name' => 'n2', 'children' => []],
	 *     ['id' => 3, 'pid' => 1, 'name' => 'n3', 'children' => []],
	 *   ]
	 *
	 *   [
	 *     ['id' => 1, 'pid' => null, 'name' => 'n1', 'nodes' => [...]],
	 *     ['id' => 2, 'pid' => 1, 'name' => 'n2', 'nodes' => []],
	 *   ]
	 *
	 * @param \Closure|null $callback Callback with (entry, key, level) arguments, returns the entry added to result
	 * @param string $nestKey Key to the children of each item
	 * @return self New map with all items as flat list
	 */
	public function traverse( \Closure $callback = null, string $nestKey = 'children' ) : self
	{
		$result = [];
		$this->visit( $this->list, $result, 0, $callback, $nestKey );

		return map( $result );
	}


	/**
	 * Creates a tree structure from the list items.
	 *
	 * Use this method to rebuild trees e.g. from database records. To traverse
	 * trees, use the traverse() method.
	 *
	 * Examples:
	 *  Map::from( [
	 *    ['id' => 1, 'pid' => null, 'lvl' => 0, 'name' => 'n1'],
	 *    ['id' => 2, 'pid' => 1, 'lvl' => 1, 'name' => 'n2'],
	 *    ['id' => 3, 'pid' => 2, 'lvl' => 2, 'name' => 'n3'],
	 *    ['id' => 4, 'pid' => 1, 'lvl' => 1, 'name' => 'n4'],
	 *    ['id' => 5, 'pid' => 3, 'lvl' => 2, 'name' => 'n5'],
	 *    ['id' => 6, 'pid' => 1, 'lvl' => 1, 'name' => 'n6'],
	 *  ] )->tree( 'id', 'pid' );
	 *
	 * Results:
	 *   [1 => [
	 *     'id' => 1, 'pid' => null, 'lvl' => 0, 'name' => 'n1', 'children' => [
	 *       2 => ['id' => 2, 'pid' => 1, 'lvl' => 1, 'name' => 'n2', 'children' => [
	 *         3 => ['id' => 3, 'pid' => 2, 'lvl' => 2, 'name' => 'n3', 'children' => []]
	 *       ]],
	 *       4 => ['id' => 4, 'pid' => 1, 'lvl' => 1, 'name' => 'n4', 'children' => [
	 *         5 => ['id' => 5, 'pid' => 3, 'lvl' => 2, 'name' => 'n5', 'children' => []]
	 *       ]],
	 *       6 => ['id' => 6, 'pid' => 1, 'lvl' => 1, 'name' => 'n6', 'children' => []]
	 *     ]
	 *   ]]
	 *
	 * To build the tree correctly, the items must be in order or at least the
	 * nodes of the lower levels must come first. For a tree like this:
	 * n1
	 * |- n2
	 * |  |- n3
	 * |- n4
	 * |  |- n5
	 * |- n6
	 *
	 * Accepted item order:
	 * - in order: n1, n2, n3, n4, n5, n6
	 * - lower levels first: n1, n2, n4, n6, n3, n5
	 *
	 * If your items are unordered, apply usort() first to the map entries, e.g.
	 *   Map::from( [['id' => 3, 'lvl' => 2], ...] )->usort( function( $item1, $item2 ) {
	 *     return $item1['lvl'] <=> $item2['lvl'];
	 *   } );
	 *
	 * @param string $idKey Name of the key with the unique ID of the node
	 * @param string $parentKey Name of the key with the ID of the parent node
	 * @param string $nestKey Name of the key with will contain the children of the node
	 * @return self New map with one or more root tree nodes
	 */
	public function tree( string $idKey, string $parentKey, string $nestKey = 'children' ) : self
	{
		$trees = $refs = [];

		foreach( $this->list as &$node )
		{
			$node[$nestKey] = [];
			$refs[$node[$idKey]] = &$node;

			if( $node[$parentKey] ) {
				$refs[$node[$parentKey]][$nestKey][$node[$idKey]] = &$node;
			} else {
				$trees[$node[$idKey]] = &$node;
			}
		}

		return map( $trees );
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
	 * Builds a union of the elements and the given elements without overwriting existing ones.
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
	 * Returns only unique elements from the map incl. their keys.
	 *
	 * Examples:
	 *  Map::from( [0 => 'a', 1 => 'b', 2 => 'b', 3 => 'c'] )->unique();
	 *  Map::from( [['p' => '1'], ['p' => 1], ['p' => 2]] )->unique( 'p' )
	 *  Map::from( [['i' => ['p' => '1']], ['i' => ['p' => 1]]] )->unique( 'i/p' )
	 *
	 * Results:
	 * [0 => 'a', 1 => 'b', 3 => 'c']
	 * [['p' => 1], ['p' => 2]]
	 * [['i' => ['p' => '1']]]
	 *
	 * Two elements are considered equal if comparing their string representions returns TRUE:
	 * (string) $elem1 === (string) $elem2
	 *
	 * The keys of the elements are only preserved in the new map if no key is passed.
	 *
	 * @param string|null $key Key or path of the nested array or object to check for
	 * @return self New map
	 */
	public function unique( string $key = null ) : self
	{
		if( $key !== null ) {
			return $this->col( null, $key )->values();
		}

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
	 * Sorts all elements using a callback using new keys.
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
	 *  Map::from( ['x' => 'b', 2 => 'a', 'c'] )->values();
	 *
	 * Results:
	 * A new map with [0 => 'b', 1 => 'a', 2 => 'c'] as content
	 *
	 * @return self New map of the values
	 */
	public function values() : self
	{
		return new static( array_values( $this->list ) );
	}


	/**
	 * Applies the given callback to all elements.
	 *
	 * To change the values of the Map, specify the value parameter as reference
	 * (&$value). You can only change the values but not the keys nor the array
	 * structure.
	 *
	 * Examples:
	 *  Map::from( ['a', 'B', ['c', 'd'], 'e'] )->walk( function( &$value ) {
	 *    $value = strtoupper( $value );
	 *  } );
	 *  Map::from( [66 => 'B', 97 => 'a'] )->walk( function( $value, $key ) {
	 *    echo 'ASCII ' . $key . ' is ' . $value . "\n";
	 *  } );
	 *  Map::from( [1, 2, 3] )->walk( function( &$value, $key, $data ) {
	 *    $value = $data[$value] ?? $value;
	 *  }, [1 => 'one', 2 => 'two'] );
	 *
	 * Results:
	 * The first example will change the Map elements to:
	 *   ['A', 'B', ['C', 'D'], 'E']
	 * The output of the second one will be:
	 *  ASCII 66 is B
	 *  ASCII 97 is a
	 * The last example changes the Map elements to:
	 *  ['one', 'two', 3]
	 *
	 * By default, Map elements which are arrays will be traversed recursively.
	 * To iterate over the Map elements only, pass FALSE as third parameter.
	 *
	 * @param callable $callback Function with (item, key, data) parameters
	 * @param mixed $data Arbitrary data that will be passed to the callback as third parameter
	 * @param bool $recursive TRUE to traverse sub-arrays recursively (default), FALSE to iterate Map elements only
	 * @return self Map for fluid interface
	 */
	public function walk( callable $callback, $data = null, bool $recursive = true ) : self
	{
		if( $recursive ) {
			array_walk_recursive( $this->list, $callback, $data );
		} else {
			array_walk( $this->list, $callback, $data );
		}

		return $this;
	}


	/**
	 * Filters the list of elements by a given condition.
	 *
	 * Examples:
	 *  Map::from( [
	 *    ['id' => 1, 'type' => 'name'],
	 *    ['id' => 2, 'type' => 'short'],
	 *  ] )->where( 'type', '==', 'name' );
	 *
	 *  Map::from( [
	 *    ['id' => 3, 'price' => 10],
	 *    ['id' => 4, 'price' => 50],
	 *  ] )->where( 'price', '>', 20 );
	 *
	 *  Map::from( [
	 *    ['id' => 3, 'price' => 10],
	 *    ['id' => 4, 'price' => 50],
	 *  ] )->where( 'price', 'in', [10, 25] );
	 *
	 *  Map::from( [
	 *    ['id' => 3, 'price' => 10],
	 *    ['id' => 4, 'price' => 50],
	 *  ] )->where( 'price', '-', [10, 100] );
	 *
	 *  Map::from( [
	 *    ['item' => ['id' => 3, 'price' => 10]],
	 *    ['item' => ['id' => 4, 'price' => 50]],
	 *  ] )->where( 'item/price', '>', 30 );
	 *
	 * Results:
	 *  [0 => ['id' => 1, 'type' => 'name']]
	 *  [1 => ['id' => 4, 'price' => 50]]
	 *  [0 => ['id' => 3, 'price' => 10]]
	 *  [0 => ['id' => 3, 'price' => 10], ['id' => 4, 'price' => 50]]
	 *  [1 => ['item' => ['id' => 4, 'price' => 50]]]
	 *
	 * Available operators are:
	 * * '==' : Equal
	 * * '===' : Equal and same type
	 * * '!=' : Not equal
	 * * '!==' : Not equal and same type
	 * * '<=' : Smaller than an equal
	 * * '>=' : Greater than an equal
	 * * '<' : Smaller
	 * * '>' : Greater
	 * 'in' : Array of value which are in the list of values
	 * '-' : Values between array of start and end value, e.g. [10, 100] (inclusive)
	 *
	 * This does also work for multi-dimensional arrays by passing the keys
	 * of the arrays separated by the delimiter ("/" by default), e.g. "key1/key2/key3"
	 * to get "val" from ['key1' => ['key2' => ['key3' => 'val']]]. The same applies to
	 * public properties of objects or objects implementing __isset() and __get() methods.
	 *
	 * @param string $key Key or path of the value in the array or object used for comparison
	 * @param string $op Operator used for comparison
	 * @param mixed $value Value used for comparison
	 */
	public function where( string $key, string $op, $value ) : self
	{
		return $this->filter( function( $item ) use ( $key, $op, $value ) {

			if( ( $val = $this->getValue( $item, explode( $this->sep, $key ) ) ) !== null )
			{
				switch( $op )
				{
					case '-':
						$list = (array) $value;
						return $val >= current( $list ) && $val <= end( $list );
					case 'in': return in_array( $val, (array) $value );
					case '<': return $val < $value;
					case '>': return $val > $value;
					case '<=': return $val <= $value;
					case '>=': return $val >= $value;
					case '===': return $val === $value;
					case '!==': return $val !== $value;
					case '!=': return $val != $value;
					default: return $val == $value;
				}
			}

			return false;
		} );
	}


	/**
	 * Merges the values of all arrays at the corresponding index.
	 *
	 * Examples:
	 *  $en = ['one', 'two', 'three'];
	 *  $es = ['uno', 'dos', 'tres'];
	 *  $m = new Map( [1, 2, 3] )->zip( $en, $es );
	 *
	 * Results:
	 *  [
	 *    [1, 'one', 'uno'],
	 *    [2, 'two', 'dos'],
	 *    [3, 'three', 'tres'],
	 *  ]
	 *
	 * @param array|\Traversable|\Iterator $arrays List of arrays to merge with at the same position
	 * @return self New map of arrays
	 */
	public function zip( ...$arrays ) : self
	{
		$args = array_map( function( $items ) {
			return $this->getArray( $items );
		}, $arrays );

		return new static( array_map( null, $this->list, ...$args ) );
	}


	/**
	 * Returns a plain array of the given elements.
	 *
	 * @param mixed $elements List of elements or single value
	 * @return array Plain array
	 */
	protected function getArray( $elements ) : array
	{
		if( is_array( $elements ) ) {
			return $elements;
		}

		if( $elements instanceof self ) {
			return $elements->toArray();
		}

		if( is_iterable( $elements ) ) {
			return iterator_to_array( $elements );
		}

		return $elements !== null ? [$elements] : [];
	}


	/**
	 * Flattens a multi-dimensional array or map into a single level array.
	 *
	 * @param iterable $entries Single of multi-level array, map or everything foreach can be used with
	 * @param array &$result Will contain all elements from the multi-dimensional arrays afterwards
	 * @param int $depth Number of levels to flatten in multi-dimensional arrays
	 */
	protected function flatten( iterable $entries, array &$result, int $depth )
	{
		foreach( $entries as $entry )
		{
			if( is_iterable( $entry ) && $depth > 0 ) {
				$this->flatten( $entry, $result, $depth - 1 );
			} else {
				$result[] = $entry;
			}
		}
	}


	/**
	 * Returns a configuration value from an array.
	 *
	 * @param array|object $entry The array or object to look at
	 * @param array $parts Path parts to look for inside the array or object
	 * @return mixed Found value or null if no value is available
	 */
	protected function getValue( $entry, array $parts )
	{
		foreach( $parts as $part )
		{
			if( ( is_array( $entry ) || $entry instanceof \ArrayAccess ) && isset( $entry[$part] ) ) {
				$entry = $entry[$part];
			} elseif( is_object( $entry ) && isset( $entry->{$part} ) ) {
				$entry = $entry->{$part};
			} else {
				return null;
			}
		}

		return $entry;
	}


	/**
	 * Flattens a multi-dimensional array or map into a single level array.
	 *
	 * @param iterable $entries Single of multi-level array, map or everything foreach can be used with
	 * @param array $result Will contain all elements from the multi-dimensional arrays afterwards
	 * @param int $depth Number of levels to flatten in multi-dimensional arrays
	 */
	protected function kflatten( iterable $entries, array &$result, int $depth )
	{
		foreach( $entries as $key => $entry )
		{
			if( is_iterable( $entry ) && $depth > 0 ) {
				$this->kflatten( $entry, $result, $depth - 1 );
			} else {
				$result[$key] = $entry;
			}
		}
	}


	/**
	 * Visits each entry, calls the callback and returns the items in the result argument
	 *
	 * @param interable $entries List of entries with children (optional)
	 * @param array $result Numerically indexed list of all visited entries
	 * @param int $level Current depth of the nodes in the tree
	 * @param \Closure|null $callback Callback with ($entry, $key, $level) arguments, returns the entry added to result
	 * @param string $nestKey Key to the children of each entry
	 */
	protected function visit( iterable $entries, array &$result, int $level, ?\Closure $callback, string $nestKey )
	{
		foreach( $entries as $key => $entry )
		{
			$result[] = $callback ? $callback( $entry, $key, $level ) : $entry;

			if( ( is_array( $entry ) || $entry instanceof \ArrayAccess ) && isset( $entry[$nestKey] ) ) {
				$this->visit( $entry[$nestKey], $result, $level + 1, $callback, $nestKey );
			} elseif( is_object( $entry ) && isset( $entry->{$nestKey} ) ) {
				$this->visit( $entry->{$nestKey}, $result, $level + 1, $callback, $nestKey );
			}
		}
	}
}
