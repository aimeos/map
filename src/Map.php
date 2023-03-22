<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 * @author Taylor Otwell, Aimeos.org developers
 */


namespace Aimeos;


/**
 * Handling and operating on a list of elements easily
 * Inspired by Laravel Collection class, PHP map data structure and Javascript
 *
 * @template-implements \ArrayAccess<int|string,mixed>
 * @template-implements \IteratorAggregate<int|string,mixed>
 */
class Map implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable
{
	/**
	 * @var array<string,\Closure>
	 */
	protected static $methods = [];

	/**
	 * @var string
	 */
	protected static $delim = '/';

	/**
	 * @var array<int|string,mixed>|\Closure|iterable|mixed
	 */
	protected $list;

	/**
	 * @var string
	 */
	protected $sep = '/';


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
		$this->sep = self::$delim;
		$this->list = $elements;
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
	 * @param array<mixed> $params List of parameters
	 * @return mixed Result from called function or new map with results from the element methods
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
	 * has access to the internal array by using $this->list().
	 *
	 * Examples:
	 *  Map::method( 'case', function( $case = CASE_LOWER ) {
	 *      return new static( array_change_key_case( $this->list(), $case ) );
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
	 * @param array<mixed> $params List of parameters
	 * @return mixed|self Result from called function or new map with results from the element methods
	 */
	public function __call( string $name, array $params )
	{
		if( isset( static::$methods[$name] ) ) {
			return call_user_func_array( static::$methods[$name]->bindTo( $this, static::class ), $params );
		}

		$result = [];

		foreach( $this->list() as $key => $item )
		{
			if( is_object( $item ) ) {
				$result[$key] = $item->{$name}( ...$params );
			}
		}

		return new static( $result );
	}


	/**
	 * Returns the elements as a plain array.
	 *
	 * @return array<int|string,mixed> Plain array
	 */
	public function __toArray() : array
	{
		return $this->list = $this->array( $this->list );
	}


	/**
	 * Sets or returns the seperator for paths to values in multi-dimensional arrays or objects.
	 *
	 * The static method only changes the separator for new maps created afterwards.
	 * Already existing maps will continue to use the previous separator. To change
	 * the separator of an existing map, use the sep() method instead.
	 *
	 * Examples:
	 *  Map::delimiter( '.' );
	 *  Map::from( ['foo' => ['bar' => 'baz']] )->get( 'foo.bar' );
	 *
	 * Results:
	 *  '/'
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
	 * This method creates a lazy Map and the string is split after calling
	 * another method that operates on the Map contents.
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
	 * @return self<int|string,mixed> New map with splitted parts
	 */
	public static function explode( string $delimiter, string $string, int $limit = PHP_INT_MAX ) : self
	{
		return new static( function() use ( $delimiter, $string, $limit ) {

			if( $delimiter !== '' ) {
				return explode( $delimiter, $string, $limit );
			}

			$limit = $limit ?: 1;
			$parts = mb_str_split( $string );

			if( $limit < 1 ) {
				return array_slice( $parts, 0, $limit );
			}

			if( $limit < count( $parts ) )
			{
				$result = array_slice( $parts, 0, $limit );
				$result[] = join( '', array_slice( $parts, $limit ) );
				return $result;
			}

			return $parts;
		} );
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
	 * @return self<int|string,mixed> New map object
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
	 * This method creates a lazy Map and the string is decoded after calling
	 * another method that operates on the Map contents. Thus, the exception in
	 * case of an error isn't thrown immediately but after calling the next method.
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
	 * @return self<int|string,mixed> New map from decoded JSON string
	 * @throws \RuntimeException If the passed JSON string is invalid
	 */
	public static function fromJson( string $json, int $options = JSON_BIGINT_AS_STRING ) : self
	{
		return new static( function() use ( $json, $options ) {

			if( ( $result = json_decode( $json, true, 512, $options ) ) !== null ) {
				return $result;
			}

			throw new \RuntimeException( 'Not a valid JSON string: ' . $json );
		} );
	}


	/**
	 * Registers a custom method or returns the existing one.
	 *
	 * The registed method has access to the class properties if called non-static.
	 *
	 * Examples:
	 *  Map::method( 'foo', function( $arg1, $arg2 ) {
	 *      return $this->list();
	 *  } );
	 *
	 * Dynamic calls have access to the class properties:
	 *  Map::from( ['bar'] )->foo( $arg1, $arg2 );
	 *
	 * Static calls yield an error because $this->elements isn't available:
	 *  Map::foo( $arg1, $arg2 );
	 *
	 * @param string $method Method name
	 * @param \Closure|null $fcn Anonymous function or NULL to return the closure if available
	 * @return \Closure|null Registered anonymous function or NULL if none has been registered
	 */
	public static function method( string $method, \Closure $fcn = null ) : ?\Closure
	{
		if( $fcn ) {
			self::$methods[$method] = $fcn;
		}

		return self::$methods[$method] ?? null;
	}


	/**
	 * Creates a new map by invoking the closure the given number of times.
	 *
	 * This method creates a lazy Map and the entries are generated after calling
	 * another method that operates on the Map contents. Thus, the passed callback
	 * is not called immediately!
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
	 * @return self<int|string,mixed> New map with the generated elements
	 */
	public static function times( int $num, \Closure $callback ) : self
	{
		return new static( function() use ( $num, $callback ) {

			$list = [];

			for( $i = 0; $i < $num; $i++ ) {
				$key = $i;
				$list[$key] = $callback( $i, $key );
			}

			return $list;
		} );
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
	 * @param \Closure|int|string $value Value or function with (item, key) parameters
	 * @return self<int|string,mixed> New map with the elements after the given one
	 */
	public function after( $value ) : self
	{
		if( ( $pos = $this->pos( $value ) ) === null ) {
			return new static();
		}

		return new static( array_slice( $this->list(), $pos + 1, null, true ) );
	}


	/**
	 * Returns the elements as a plain array.
	 *
	 * @return array<int|string,mixed> Plain array
	 */
	public function all() : array
	{
		return $this->list = $this->array( $this->list );
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
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function arsort( int $options = SORT_REGULAR ) : self
	{
		arsort( $this->list(), $options );
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
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function asort( int $options = SORT_REGULAR ) : self
	{
		asort( $this->list(), $options );
		return $this;
	}


	/**
	 * Returns the value at the given position.
	 *
	 * Examples:
	 *  Map::from( [1, 3, 5] )->at( 0 );
	 *  Map::from( [1, 3, 5] )->at( 1 );
	 *  Map::from( [1, 3, 5] )->at( -1 );
	 *  Map::from( [1, 3, 5] )->at( 3 );
	 *
	 * Results:
	 * The first line will return "1", the second one "3", the third one "5" and
	 * the last one NULL.
	 *
	 * The position starts from zero and a position of "0" returns the first element
	 * of the map, "1" the second and so on. If the position is negative, the
	 * sequence will start from the end of the map.
	 *
	 * @param int $pos Position of the value in the map
	 * @return mixed|null Value at the given position or NULL if no value is available
	 */
	public function at( int $pos )
	{
		$pair = array_slice( $this->list(), $pos, 1 );
		return !empty( $pair ) ? current( $pair ) : null;
	}


	/**
	 * Returns the average of all integer and float values in the map.
	 *
	 * Examples:
	 *  Map::from( [1, 3, 5] )->avg();
	 *  Map::from( [1, null, 5] )->avg();
	 *  Map::from( [1, 'sum', 5] )->avg();
	 *  Map::from( [['p' => 30], ['p' => 50], ['p' => 10]] )->avg( 'p' );
	 *  Map::from( [['i' => ['p' => 30]], ['i' => ['p' => 50]]] )->avg( 'i/p' );
	 *
	 * Results:
	 * The first line will return "3", the second and third one "2", the forth
	 * one "30" and the last one "40".
	 *
	 * This does also work for multi-dimensional arrays by passing the keys
	 * of the arrays separated by the delimiter ("/" by default), e.g. "key1/key2/key3"
	 * to get "val" from ['key1' => ['key2' => ['key3' => 'val']]]. The same applies to
	 * public properties of objects or objects implementing __isset() and __get() methods.
	 *
	 * @param string|null $key Key or path to the values in the nested array or object to compute the average for
	 * @return float Average of all elements or 0 if there are no elements in the map
	 */
	public function avg( string $key = null ) : float
	{
		$cnt = count( $this->list() );
		return $cnt > 0 ? $this->sum( $key ) / $cnt : 0;
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
	 * @param \Closure|int|string $value Value or function with (item, key) parameters
	 * @return self<int|string,mixed> New map with the elements before the given one
	 */
	public function before( $value ) : self
	{
		return new static( array_slice( $this->list(), 0, $this->pos( $value ), true ) );
	}


	/**
	 * Returns an element by key and casts it to boolean if possible.
	 *
	 * Examples:
	 *  Map::from( ['a' => true] )->bool( 'a' );
	 *  Map::from( ['a' => '1'] )->bool( 'a' );
	 *  Map::from( ['a' => 1.1] )->bool( 'a' );
	 *  Map::from( ['a' => '10'] )->bool( 'a' );
	 *  Map::from( ['a' => 'abc'] )->bool( 'a' );
	 *  Map::from( ['a' => ['b' => ['c' => true]]] )->bool( 'a/b/c' );
	 *  Map::from( [] )->bool( 'c', function() { return rand( 1, 2 ); } );
	 *  Map::from( [] )->bool( 'a', true );
	 *
	 *  Map::from( [] )->bool( 'b' );
	 *  Map::from( ['b' => ''] )->bool( 'b' );
	 *  Map::from( ['b' => null] )->bool( 'b' );
	 *  Map::from( ['b' => [true]] )->bool( 'b' );
	 *  Map::from( ['b' => resource] )->bool( 'b' );
	 *  Map::from( ['b' => new \stdClass] )->bool( 'b' );
	 *
	 *  Map::from( [] )->bool( 'c', new \Exception( 'error' ) );
	 *
	 * Results:
	 * The first eight examples will return TRUE while the 9th to 14th example
	 * returns FALSE. The last example will throw an exception.
	 *
	 * This does also work for multi-dimensional arrays by passing the keys
	 * of the arrays separated by the delimiter ("/" by default), e.g. "key1/key2/key3"
	 * to get "val" from ['key1' => ['key2' => ['key3' => 'val']]]. The same applies to
	 * public properties of objects or objects implementing __isset() and __get() methods.
	 *
	 * @param int|string $key Key or path to the requested item
	 * @param mixed $default Default value if key isn't found (will be casted to bool)
	 * @return bool Value from map or default value
	 */
	public function bool( $key, $default = false ) : bool
	{
		return (bool) ( is_scalar( $val = $this->get( $key, $default ) ) ? $val : $default );
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
	 * @param array<mixed> $params List of parameters
	 * @return self<int|string,mixed> New map with results from all elements
	 */
	public function call( string $name, array $params = [] ) : self
	{
		$result = [];

		foreach( $this->list() as $key => $item )
		{
			if( is_object( $item ) ) {
				$result[$key] = $item->{$name}( ...$params );
			}
		}

		return new static( $result );
	}


	/**
	 * Casts all entries to the passed type.
	 *
	 * Examples:
	 *  Map::from( [true, 1, 1.0, 'yes'] )->cast();
	 *  Map::from( [true, 1, 1.0, 'yes'] )->cast( 'bool' );
	 *  Map::from( [true, 1, 1.0, 'yes'] )->cast( 'int' );
	 *  Map::from( [true, 1, 1.0, 'yes'] )->cast( 'float' );
	 *  Map::from( [new stdClass, new stdClass] )->cast( 'array' );
	 *  Map::from( [[], []] )->cast( 'object' );
	 *
	 * Results:
	 * The examples will return (in this order):
	 * ['1', '1', '1.0', 'yes']
	 * [true, true, true, true]
	 * [1, 1, 1, 0]
	 * [1.0, 1.0, 1.0, 0.0]
	 * [[], []]
	 * [new stdClass, new stdClass]
	 *
	 * Casting arrays and objects to scalar values won't return anything useful!
	 *
	 * @param string $type Type to cast the values to ("string", "bool", "int", "float", "array", "object")
	 * @return self<int|string,mixed> Updated map with casted elements
	 */
	public function cast( string $type = 'string' ) : self
	{
		foreach( $this->list() as &$item )
		{
			switch( $type )
			{
				case 'bool': $item = (bool) $item; break;
				case 'int': $item = (int) $item; break;
				case 'float': $item = (float) $item; break;
				case 'string': $item = (string) $item; break;
				case 'array': $item = (array) $item; break;
				case 'object': $item = (object) $item; break;
			}
		}

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
	 * @return self<int|string,mixed> New map with elements chunked in sub-arrays
	 * @throws \InvalidArgumentException If size is smaller than 1
	 */
	public function chunk( int $size, bool $preserve = false ) : self
	{
		if( $size < 1 ) {
			throw new \InvalidArgumentException( 'Chunk size must be greater or equal than 1' );
		}

		return new static( array_chunk( $this->list(), $size, $preserve ) );
	}


	/**
	 * Removes all elements from the current map.
	 *
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function clear() : self
	{
		$this->list = [];
		return $this;
	}


	/**
	 * Clones the map and all objects within.
	 *
	 * Examples:
	 *  Map::from( [new \stdClass, new \stdClass] )->clone();
	 *
	 * Results:
	 *   [new \stdClass, new \stdClass]
	 *
	 * The objects within the Map are NOT the same as before but new cloned objects.
	 * This is different to copy(), which doesn't clone the objects within.
	 *
	 * The keys are preserved using this method.
	 *
	 * @return self<int|string,mixed> New map with cloned objects
	 */
	public function clone() : self
	{
		$list = [];

		foreach( $this->list() as $key => $item ) {
			$list[$key] = is_object( $item ) ? clone $item : $item;
		}

		return new static( $list );
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
	 * @return self<int|string,mixed> New map with mapped entries
	 */
	public function col( string $valuecol = null, string $indexcol = null ) : self
	{
		$vparts = explode( $this->sep, (string) $valuecol );
		$iparts = explode( $this->sep, (string) $indexcol );

		if( count( $vparts ) === 1 && count( $iparts ) === 1 ) {
			return new static( array_column( $this->list(), $valuecol, $indexcol ) );
		}

		$list = [];

		foreach( $this->list() as $item )
		{
			$v = $valuecol ? $this->val( $item, $vparts ) : $item;

			if( $indexcol !== null && ( $key = $this->val( $item, $iparts ) ) !== null ) {
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
	 * @return self<int|string,mixed> New map with all sub-array elements added into it recursively, up to the specified depth
	 * @throws \InvalidArgumentException If depth must be greater or equal than 0 or NULL
	 */
	public function collapse( int $depth = null ) : self
	{
		if( $depth < 0 ) {
			throw new \InvalidArgumentException( 'Depth must be greater or equal than 0 or NULL' );
		}

		$result = [];
		$this->kflatten( $this->list(), $result, $depth ?? 0x7fffffff );
		return new static( $result );
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
	 * @param iterable<int|string,mixed> $values Values of the new map
	 * @return self<int|string,mixed> New map
	 */
	public function combine( iterable $values ) : self
	{
		return new static( array_combine( $this->list(), $this->array( $values ) ) );
	}


	/**
	 * Compares the value against all map elements.
	 *
	 * Examples:
	 *  Map::from( ['foo', 'bar'] )->compare( 'foo' );
	 *  Map::from( ['foo', 'bar'] )->compare( 'Foo', false );
	 *  Map::from( [123, 12.3] )->compare( '12.3' );
	 *  Map::from( [false, true] )->compare( '1' );
	 *  Map::from( ['foo', 'bar'] )->compare( 'Foo' );
	 *  Map::from( ['foo', 'bar'] )->compare( 'baz' );
	 *  Map::from( [new \stdClass(), 'bar'] )->compare( 'foo' );
	 *
	 * Results:
	 * The first four examples return TRUE, the last three examples will return FALSE.
	 *
	 * All scalar values (bool, float, int and string) are casted to string values before
	 * comparing to the given value. Non-scalar values in the map are ignored.
	 *
	 * @param string $value Value to compare map elements to
	 * @param bool $case TRUE if comparison is case sensitive, FALSE to ignore upper/lower case
	 * @return bool TRUE If at least one element matches, FALSE if value is not in map
	 */
	public function compare( string $value, bool $case = true ) : bool
	{
		$fcn = $case ? 'strcmp' : 'strcasecmp';

		foreach( $this->list() as $item )
		{
			if( is_scalar( $item ) && !$fcn( (string) $item, $value ) ) {
				return true;
			}
		}

		return false;
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
	 * The keys of the passed elements are NOT preserved!
	 *
	 * @param iterable<int|string,mixed> $elements List of elements
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function concat( iterable $elements ) : self
	{
		$this->list();

		foreach( $elements as $item ) {
			$this->list[] = $item;
		}

		return $this;
	}


	/**
	 * Determines if an item exists in the map.
	 *
	 * This method combines the power of the where() method with some() to check
	 * if the map contains at least one of the passed values or conditions.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b'] )->contains( 'a' );
	 *  Map::from( ['a', 'b'] )->contains( ['a', 'c'] );
	 *  Map::from( ['a', 'b'] )->contains( function( $item, $key ) {
	 *    return $item === 'a'
	 *  } );
	 *  Map::from( [['type' => 'name']] )->contains( 'type', 'name' );
	 *  Map::from( [['type' => 'name']] )->contains( 'type', '==', 'name' );
	 *
	 * Results:
	 * All method calls will return TRUE because at least "a" is included in the
	 * map or there's a "type" key with a value "name" like in the last two
	 * examples.
	 *
	 * Check the where() method for available operators.
	 *
	 * @param \Closure|iterable|mixed $values Anonymous function with (item, key) parameter, element or list of elements to test against
	 * @param string|null $op Operator used for comparison
	 * @param mixed $value Value used for comparison
	 * @return bool TRUE if at least one element is available in map, FALSE if the map contains none of them
	 */
	public function contains( $key, string $operator = null, $value = null ) : bool
	{
		if( $operator === null ) {
			return $this->some( $key );
		}

		if( $value === null ) {
			return !$this->where( $key, '==', $operator )->isEmpty();
		}

		return !$this->where( $key, $operator, $value )->isEmpty();
	}


	/**
	 * Creates a new map with the same elements.
	 *
	 * Both maps share the same array until one of the map objects modifies the
	 * array. Then, the array is copied and the copy is modfied (copy on write).
	 *
	 * @return self<int|string,mixed> New map
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
		return count( $this->list() );
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
	 * @return self<int|string,mixed> New map with values as keys and their count as value
	 */
	public function countBy( callable $callback = null ) : self
	{
		$callback = $callback ?: function( $value ) {
			return (string) $value;
		};

		return new static( array_count_values( array_map( $callback, $this->list() ) ) );
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
	public function dd( callable $callback = null ) : void
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
	 * The keys are preserved using this method.
	 *
	 * @param iterable<int|string,mixed> $elements List of elements
	 * @param  callable|null $callback Function with (valueA, valueB) parameters and returns -1 (<), 0 (=) and 1 (>)
	 * @return self<int|string,mixed> New map
	 */
	public function diff( iterable $elements, callable $callback = null ) : self
	{
		if( $callback ) {
			return new static( array_udiff( $this->list(), $this->array( $elements ), $callback ) );
		}

		return new static( array_diff( $this->list(), $this->array( $elements ) ) );
	}


	/**
	 * Returns the keys/values in the map whose keys AND values are not present in the passed elements in a new map.
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
	 * The keys are preserved using this method.
	 *
	 * @param iterable<int|string,mixed> $elements List of elements
	 * @param  callable|null $callback Function with (valueA, valueB) parameters and returns -1 (<), 0 (=) and 1 (>)
	 * @return self<int|string,mixed> New map
	 */
	public function diffAssoc( iterable $elements, callable $callback = null ) : self
	{
		if( $callback ) {
			return new static( array_diff_uassoc( $this->list(), $this->array( $elements ), $callback ) );
		}

		return new static( array_diff_assoc( $this->list(), $this->array( $elements ) ) );
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
	 * The keys are preserved using this method.
	 *
	 * @param iterable<int|string,mixed> $elements List of elements
	 * @param  callable|null $callback Function with (keyA, keyB) parameters and returns -1 (<), 0 (=) and 1 (>)
	 * @return self<int|string,mixed> New map
	 */
	public function diffKeys( iterable $elements, callable $callback = null ) : self
	{
		if( $callback ) {
			return new static( array_diff_ukey( $this->list(), $this->array( $elements ), $callback ) );
		}

		return new static( array_diff_key( $this->list(), $this->array( $elements ) ) );
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
	 * @return self<int|string,mixed> Same map for fluid interface
	 */
	public function dump( callable $callback = null ) : self
	{
		$callback ? $callback( $this->list() ) : print_r( $this->list() );
		return $this;
	}


	/**
	 * Returns the duplicate values from the map.
	 *
	 * For nested arrays, you have to pass the name of the column of the nested
	 * array which should be used to check for duplicates.
	 *
	 * Examples:
	 *  Map::from( [1, 2, '1', 3] )->duplicates()
	 *  Map::from( [['p' => '1'], ['p' => 1], ['p' => 2]] )->duplicates( 'p' )
	 *  Map::from( [['i' => ['p' => '1']], ['i' => ['p' => 1]]] )->duplicates( 'i/p' )
	 *
	 * Results:
	 *  [2 => '1']
	 *  [1 => ['p' => 1]]
	 *  [1 => ['i' => ['p' => '1']]]
	 *
	 * This does also work for multi-dimensional arrays by passing the keys
	 * of the arrays separated by the delimiter ("/" by default), e.g. "key1/key2/key3"
	 * to get "val" from ['key1' => ['key2' => ['key3' => 'val']]]. The same applies to
	 * public properties of objects or objects implementing __isset() and __get() methods.
	 *
	 * The keys are preserved using this method.
	 *
	 * @param string|null $key Key or path of the nested array or object to check for
	 * @return self<int|string,mixed> New map
	 */
	public function duplicates( string $key = null ) : self
	{
		$list = $this->list();
		$items = ( $key !== null ? $this->col( $key )->toArray() : $list );

		return new static( array_diff_key( $list, array_unique( $items ) ) );
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
	 * @return self<int|string,mixed> Same map for fluid interface
	 */
	public function each( \Closure $callback ) : self
	{
		foreach( $this->list() as $key => $item )
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
		return empty( $this->list() );
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
	 * @param iterable<int|string,mixed> $elements List of elements to test against
	 * @return bool TRUE if both are equal, FALSE if not
	 */
	public function equals( iterable $elements ) : bool
	{
		$list = $this->list();
		$elements = $this->array( $elements );

		return array_diff( $list, $elements ) === [] && array_diff( $elements, $list ) === [];
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
		foreach( $this->list() as $key => $item )
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
	 * The keys in the result map are preserved.
	 *
	 * @param iterable<string|int>|array<string|int>|string|int $keys List of keys to remove
	 * @return self<int|string,mixed> New map
	 */
	public function except( $keys ) : self
	{
		return $this->copy()->remove( $keys );
	}


	/**
	 * Applies a filter to all elements of the map and returns a new map.
	 *
	 * Examples:
	 *  Map::from( [null, 0, 1, '', '0', 'a'] )->filter();
	 *  Map::from( [2 => 'a', 6 => 'b', 13 => 'm', 30 => 'z'] )->filter( function( $value, $key ) {
	 *      return $key < 10 && $value < 'n';
	 *  } );
	 *
	 * Results:
	 *  [1, 'a']
	 *  ['a', 'b']
	 *
	 * If no callback is passed, all values which are empty, null or false will be
	 * removed if their value converted to boolean is FALSE:
	 *  (bool) $value === false
	 *
	 * The keys in the result map are preserved.
	 *
	 * @param  callable|null $callback Function with (item, key) parameters and returns TRUE/FALSE
	 * @return self<int|string,mixed> New map
	 */
	public function filter( callable $callback = null ) : self
	{
		if( $callback ) {
			return new static( array_filter( $this->list(), $callback, ARRAY_FILTER_USE_BOTH ) );
		}

		return new static( array_filter( $this->list() ) );
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
		foreach( ( $reverse ? array_reverse( $this->list() ) : $this->list() ) as $key => $value )
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
		if( ( $value = reset( $this->list() ) ) !== false ) {
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
		$list = $this->list();

		if( function_exists( 'array_key_first' ) ) {
			return array_key_first( $list );
		}

		reset( $list );
		return key( $list );
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
	 * Keys are NOT preserved using this method!
	 *
	 * @param int|null $depth Number of levels to flatten multi-dimensional arrays or NULL for all
	 * @return self<int|string,mixed> New map with all sub-array elements added into it recursively, up to the specified depth
	 * @throws \InvalidArgumentException If depth must be greater or equal than 0 or NULL
	 */
	public function flat( int $depth = null ) : self
	{
		if( $depth < 0 ) {
			throw new \InvalidArgumentException( 'Depth must be greater or equal than 0 or NULL' );
		}

		$result = [];
		$this->flatten( $this->list(), $result, $depth ?? 0x7fffffff );
		return new static( $result );
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
	 * @return self<int|string,mixed> New map with keys as values and values as keys
	 */
	public function flip() : self
	{
		return new static( array_flip( $this->list() ) );
	}


	/**
	 * Returns an element by key and casts it to float if possible.
	 *
	 * Examples:
	 *  Map::from( ['a' => true] )->float( 'a' );
	 *  Map::from( ['a' => 1] )->float( 'a' );
	 *  Map::from( ['a' => '1.1'] )->float( 'a' );
	 *  Map::from( ['a' => '10'] )->float( 'a' );
	 *  Map::from( ['a' => ['b' => ['c' => 1.1]]] )->float( 'a/b/c' );
	 *  Map::from( [] )->float( 'c', function() { return 1.1; } );
	 *  Map::from( [] )->float( 'a', 1.1 );
	 *
	 *  Map::from( [] )->float( 'b' );
	 *  Map::from( ['b' => ''] )->float( 'b' );
	 *  Map::from( ['b' => null] )->float( 'b' );
	 *  Map::from( ['b' => 'abc'] )->float( 'b' );
	 *  Map::from( ['b' => [1]] )->float( 'b' );
	 *  Map::from( ['b' => #resource] )->float( 'b' );
	 *  Map::from( ['b' => new \stdClass] )->float( 'b' );
	 *
	 *  Map::from( [] )->float( 'c', new \Exception( 'error' ) );
	 *
	 * Results:
	 * The first eight examples will return the float values for the passed keys
	 * while the 9th to 14th example returns 0. The last example will throw an exception.
	 *
	 * This does also work for multi-dimensional arrays by passing the keys
	 * of the arrays separated by the delimiter ("/" by default), e.g. "key1/key2/key3"
	 * to get "val" from ['key1' => ['key2' => ['key3' => 'val']]]. The same applies to
	 * public properties of objects or objects implementing __isset() and __get() methods.
	 *
	 * @param int|string $key Key or path to the requested item
	 * @param mixed $default Default value if key isn't found (will be casted to float)
	 * @return float Value from map or default value
	 */
	public function float( $key, $default = 0.0 ) : float
	{
		return (float) ( is_scalar( $val = $this->get( $key, $default ) ) ? $val : $default );
	}


	/**
	 * Returns an element from the map by key.
	 *
	 * Examples:
	 *  Map::from( ['a' => 'X', 'b' => 'Y'] )->get( 'a' );
	 *  Map::from( ['a' => 'X', 'b' => 'Y'] )->get( 'c', 'Z' );
	 *  Map::from( ['a' => ['b' => ['c' => 'Y']]] )->get( 'a/b/c' );
	 *  Map::from( [] )->get( 'Y', new \Exception( 'error' ) );
	 *  Map::from( [] )->get( 'Y', function() { return rand(); } );
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
	 * @param int|string $key Key or path to the requested item
	 * @param mixed $default Default value if no element matches
	 * @return mixed Value from map or default value
	 */
	public function get( $key, $default = null )
	{
		$list = $this->list();

		if( array_key_exists( $key, $list ) ) {
			return $list[$key];
		}

		if( ( $v = $this->val( $list, explode( $this->sep, (string) $key ) ) ) !== null ) {
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
	 * @return \ArrayIterator<int|string,mixed> Iterator for map elements
	 */
	public function getIterator() : \ArrayIterator
	{
		return new \ArrayIterator( $this->list = $this->array( $this->list ) );
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
	 * @return self<int|string,mixed> New map containing only the matched elements
	 */
	public function grep( string $pattern, int $flags = 0 ) : self
	{
		if( ( $result = preg_grep( $pattern, $this->list(), $flags ) ) === false )
		{
			switch( preg_last_error() )
			{
				case PREG_INTERNAL_ERROR: $msg = 'Internal error'; break;
				case PREG_BACKTRACK_LIMIT_ERROR: $msg = 'Backtrack limit error'; break;
				case PREG_RECURSION_LIMIT_ERROR: $msg = 'Recursion limit error'; break;
				case PREG_BAD_UTF8_ERROR: $msg = 'Bad UTF8 error'; break;
				case PREG_BAD_UTF8_OFFSET_ERROR: $msg = 'Bad UTF8 offset error'; break;
				case PREG_JIT_STACKLIMIT_ERROR: $msg = 'JIT stack limit error'; break;
				default: $msg = 'Unknown error';
			}

			throw new \RuntimeException( 'Regular expression error: ' . $msg );
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
	 * @param  \Closure|string|int $key Closure function with (item, idx) parameters returning the key or the key itself to group by
	 * @return self<int|string,mixed> New map with elements grouped by the given key
	 */
	public function groupBy( $key ) : self
	{
		$result = [];

		foreach( $this->list() as $idx => $item )
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
	 * @param array<int|string>|int|string $key Key of the requested item or list of keys
	 * @return bool TRUE if key or keys are available in map, FALSE if not
	 */
	public function has( $key ) : bool
	{
		$list = $this->list();

		foreach( (array) $key as $entry )
		{
			if( array_key_exists( $entry, $list ) === false
				&& $this->val( $list, explode( $this->sep, (string) $entry ) ) === null
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
	 * map object.
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
	 *  Map::from( ['a', 'b'] )->if( true, function( $map ) {
	 *    return $map->push( 'c' );
	 *  } );
	 *
	 *  Map::from( ['a', 'b'] )->if( false, null, function( $map ) {
	 *    return $map->pop();
	 *  } );
	 *
	 * Results:
	 * The first example returns "found" while the second one returns "then" and
	 * the third one "else". The forth one will return ['a', 'b', 'c'] while the
	 * fifth one will return 'b', which is turned into a map of ['b'] again.
	 *
	 * Since PHP 7.4, you can also pass arrow function like `fn($map) => $map->has('c')`
	 * (a short form for anonymous closures) as parameters. The automatically have access
	 * to previously defined variables but can not modify them. Also, they can not have
	 * a void return type and must/will always return something. Details about
	 * [PHP arrow functions](https://www.php.net/manual/en/functions.arrow.php)
	 *
	 * @param \Closure|bool $condition Boolean or function with (map) parameter returning a boolean
	 * @param \Closure|null $then Function with (map, condition) parameter (optional)
	 * @param \Closure|null $else Function with (map, condition) parameter (optional)
	 * @return self<int|string,mixed> New map
	 */
	public function if( $condition, \Closure $then = null, \Closure $else = null ) : self
	{
		if( $condition instanceof \Closure ) {
			$condition = $condition( $this );
		}

		if( $condition ) {
			return $then ? static::from( $then( $this, $condition ) ) : $this;
		} elseif( $else ) {
			return static::from( $else( $this, $condition ) );
		}

		return $this;
	}


	/**
	 * Executes callbacks depending if the map contains elements or not.
	 *
	 * If callbacks for "then" and/or "else" are passed, these callbacks will be
	 * executed and their returned value is passed back within a Map object. In
	 * case no "then" or "else" closure is given, the method will return the same
	 * map object.
	 *
	 * Examples:
	 *  Map::from( ['a'] )->ifAny( function( $map ) {
	 *    $map->push( 'b' );
	 *  } );
	 *
	 *  Map::from( [] )->ifAny( null, function( $map ) {
	 *    return $map->push( 'b' );
	 *  } );
	 *
	 *  Map::from( ['a'] )->ifAny( function( $map ) {
	 *    return 'c';
	 *  } );
	 *
	 * Results:
	 * The first example returns a Map containing ['a', 'b'] because the the initial
	 * Map is not empty. The second one returns  a Map with ['b'] because the initial
	 * Map is empty and the "else" closure is used. The last example returns ['c']
	 * as new map content.
	 *
	 * Since PHP 7.4, you can also pass arrow function like `fn($map) => $map->has('c')`
	 * (a short form for anonymous closures) as parameters. The automatically have access
	 * to previously defined variables but can not modify them. Also, they can not have
	 * a void return type and must/will always return something. Details about
	 * [PHP arrow functions](https://www.php.net/manual/en/functions.arrow.php)
	 *
	 * @param \Closure|null $then Function with (map, condition) parameter (optional)
	 * @param \Closure|null $else Function with (map, condition) parameter (optional)
	 * @return self<int|string,mixed> New map
	 */
	public function ifAny( \Closure $then = null, \Closure $else = null ) : self
	{
		return $this->if( !empty( $this->list() ), $then, $else );
	}


	/**
	 * Executes callbacks depending if the map is empty or not.
	 *
	 * If callbacks for "then" and/or "else" are passed, these callbacks will be
	 * executed and their returned value is passed back within a Map object. In
	 * case no "then" or "else" closure is given, the method will return the same
	 * map object.
	 *
	 * Examples:
	 *  Map::from( [] )->ifEmpty( function( $map ) {
	 *    $map->push( 'a' );
	 *  } );
	 *
	 *  Map::from( ['a'] )->ifEmpty( null, function( $map ) {
	 *    return $map->push( 'b' );
	 *  } );
	 *
	 * Results:
	 * The first example returns a Map containing ['a'] because the the initial Map
	 * is empty. The second one returns  a Map with ['a', 'b'] because the initial
	 * Map is not empty and the "else" closure is used.
	 *
	 * Since PHP 7.4, you can also pass arrow function like `fn($map) => $map->has('c')`
	 * (a short form for anonymous closures) as parameters. The automatically have access
	 * to previously defined variables but can not modify them. Also, they can not have
	 * a void return type and must/will always return something. Details about
	 * [PHP arrow functions](https://www.php.net/manual/en/functions.arrow.php)
	 *
	 * @param \Closure|null $then Function with (map, condition) parameter (optional)
	 * @param \Closure|null $else Function with (map, condition) parameter (optional)
	 * @return self<int|string,mixed> New map
	 */
	public function ifEmpty( \Closure $then = null, \Closure $else = null ) : self
	{
		return $this->if( empty( $this->list() ), $then, $else );
	}


	/**
	 * Tests if all entries in the map are objects implementing the given interface.
	 *
	 * Examples:
	 *  Map::from( [new Map(), new Map()] )->implements( '\Countable' );
	 *  Map::from( [new Map(), new stdClass()] )->implements( '\Countable' );
	 *  Map::from( [new Map(), 123] )->implements( '\Countable' );
	 *  Map::from( [new Map(), 123] )->implements( '\Countable', true );
	 *  Map::from( [new Map(), 123] )->implements( '\Countable', '\RuntimeException' );
	 *
	 * Results:
	 *  The first example returns TRUE while the second and third one return FALSE.
	 *  The forth example will throw an UnexpectedValueException while the last one
	 *  throws a RuntimeException.
	 *
	 * @param string $interface Name of the interface that must be implemented
	 * @param \Throwable|bool $throw Passing TRUE or an exception name will throw the exception instead of returning FALSE
	 * @return bool TRUE if all entries implement the interface or FALSE if at least one doesn't
	 * @throws \UnexpectedValueException|\Throwable If one entry doesn't implement the interface
	 */
	public function implements( string $interface, $throw = false ) : bool
	{
		foreach( $this->list() as $entry )
		{
			if( !( $entry instanceof $interface ) )
			{
				if( $throw )
				{
					$name = is_string( $throw ) ? $throw : '\UnexpectedValueException';
					throw new $name( "Does not implement $interface: " . print_r( $entry, true ) );
				}

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
			return in_array( $element, $this->list(), $strict );
		};

		foreach( $element as $entry )
		{
			if( in_array( $entry, $this->list(), $strict ) === false ) {
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

			foreach( $this->list() as $key => $item )
			{
				if( $value( $key ) ) {
					return $pos;
				}

				++$pos;
			}

			return null;
		}

		$pos = array_search( $value, array_keys( $this->list() ) );
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
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function insertAfter( $element, $value ) : self
	{
		$position = ( $element !== null && ( $pos = $this->pos( $element ) ) !== null ? $pos : count( $this->list() ) );
		array_splice( $this->list(), $position + 1, 0, $this->array( $value ) );

		return $this;
	}


	/**
	 * Inserts the item at the given position in the map.
	 *
	 * Examples:
	 *  Map::from( ['a' => 'foo', 'b' => 'bar'] )->insertAt( 0, 'baz' );
	 *  Map::from( ['a' => 'foo', 'b' => 'bar'] )->insertAt( 1, 'baz', 'c' );
	 *  Map::from( ['a' => 'foo', 'b' => 'bar'] )->insertAt( 4, 'baz' );
	 *  Map::from( ['a' => 'foo', 'b' => 'bar'] )->insertAt( -1, 'baz', 'c' );
	 *
	 * Results:
	 *  [0 => 'baz', 'a' => 'foo', 'b' => 'bar']
	 *  ['a' => 'foo', 'c' => 'baz', 'b' => 'bar']
	 *  ['a' => 'foo', 'b' => 'bar', 'c' => 'baz']
	 *  ['a' => 'foo', 'c' => 'baz', 'b' => 'bar']
	 *
	 * @param int $pos Position the element it should be inserted at
	 * @param mixed $element Element to be inserted
	 * @param mixed|null $key Element key or NULL to assign an integer key automatically
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function insertAt( int $pos, $element, $key = null ) : self
	{
		if( $key !== null )
		{
			$list = $this->list();

			$this->list = array_merge(
				array_slice( $list, 0, $pos, true ),
				[$key => $element],
				array_slice( $list, $pos, null, true )
			);
		}
		else
		{
			array_splice( $this->list(), $pos, 0, [$element] );
		}

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
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function insertBefore( $element, $value ) : self
	{
		$position = ( $element !== null && ( $pos = $this->pos( $element ) ) !== null ? $pos : count( $this->list() ) );
		array_splice( $this->list(), $position, 0, $this->array( $value ) );

		return $this;
	}


	/**
	 * Tests if the passed value or values are part of the strings in the map.
	 *
	 * Examples:
	 *  Map::from( ['abc'] )->inString( 'c' );
	 *  Map::from( ['abc'] )->inString( 'bc' );
	 *  Map::from( [12345] )->inString( '23' );
	 *  Map::from( [123.4] )->inString( 23.4 );
	 *  Map::from( [12345] )->inString( false );
	 *  Map::from( [12345] )->inString( true );
	 *  Map::from( [false] )->inString( false );
	 *  Map::from( ['abc'] )->inString( '' );
	 *  Map::from( [''] )->inString( false );
	 *  Map::from( ['abc'] )->inString( 'BC', false );
	 *  Map::from( ['abc', 'def'] )->inString( ['de', 'xy'] );
	 *  Map::from( ['abc', 'def'] )->inString( ['E', 'x'] );
	 *  Map::from( ['abc', 'def'] )->inString( 'E' );
	 *  Map::from( [23456] )->inString( true );
	 *  Map::from( [false] )->inString( 0 );
	 *
	 * Results:
	 * The first eleven examples will return TRUE while the last four will return FALSE
	 *
	 * All scalar values (bool, float, int and string) are casted to string values before
	 * comparing to the given value. Non-scalar values in the map are ignored.
	 *
	 * @param array|string $value Value or values to compare the map elements, will be casted to string type
	 * @param bool $case TRUE if comparison is case sensitive, FALSE to ignore upper/lower case
	 * @return bool TRUE If at least one element matches, FALSE if value is not in any string of the map
	 * @deprecated Use multi-byte aware strContains() instead
	 */
	public function inString( $value, bool $case = true ) : bool
	{
		$fcn = $case ? 'strpos' : 'stripos';

		foreach( (array) $value as $val )
		{
			if( (string) $val === '' ) {
				return true;
			}

			foreach( $this->list() as $item )
			{
				if( is_scalar( $item ) && $fcn( (string) $item, (string) $val ) !== false ) {
					return true;
				}
			}
		}

		return false;
	}


	/**
	 * Returns an element by key and casts it to integer if possible.
	 *
	 * Examples:
	 *  Map::from( ['a' => true] )->int( 'a' );
	 *  Map::from( ['a' => '1'] )->int( 'a' );
	 *  Map::from( ['a' => 1.1] )->int( 'a' );
	 *  Map::from( ['a' => '10'] )->int( 'a' );
	 *  Map::from( ['a' => ['b' => ['c' => 1]]] )->int( 'a/b/c' );
	 *  Map::from( [] )->int( 'c', function() { return rand( 1, 1 ); } );
	 *  Map::from( [] )->int( 'a', 1 );
	 *
	 *  Map::from( [] )->int( 'b' );
	 *  Map::from( ['b' => ''] )->int( 'b' );
	 *  Map::from( ['b' => 'abc'] )->int( 'b' );
	 *  Map::from( ['b' => null] )->int( 'b' );
	 *  Map::from( ['b' => [1]] )->int( 'b' );
	 *  Map::from( ['b' => #resource] )->int( 'b' );
	 *  Map::from( ['b' => new \stdClass] )->int( 'b' );
	 *
	 *  Map::from( [] )->int( 'c', new \Exception( 'error' ) );
	 *
	 * Results:
	 * The first seven examples will return 1 while the 8th to 14th example
	 * returns 0. The last example will throw an exception.
	 *
	 * This does also work for multi-dimensional arrays by passing the keys
	 * of the arrays separated by the delimiter ("/" by default), e.g. "key1/key2/key3"
	 * to get "val" from ['key1' => ['key2' => ['key3' => 'val']]]. The same applies to
	 * public properties of objects or objects implementing __isset() and __get() methods.
	 *
	 * @param int|string $key Key or path to the requested item
	 * @param mixed $default Default value if key isn't found (will be casted to integer)
	 * @return int Value from map or default value
	 */
	public function int( $key, $default = 0 ) : int
	{
		return (int) ( is_scalar( $val = $this->get( $key, $default ) ) ? $val : $default );
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
	 * The keys are preserved using this method.
	 *
	 * @param iterable<int|string,mixed> $elements List of elements
	 * @param  callable|null $callback Function with (valueA, valueB) parameters and returns -1 (<), 0 (=) and 1 (>)
	 * @return self<int|string,mixed> New map
	 */
	public function intersect( iterable $elements, callable $callback = null ) : self
	{
		$list = $this->list();
		$elements = $this->array( $elements );

		if( $callback ) {
			return new static( array_uintersect( $list, $elements, $callback ) );
		}

		// using array_intersect() is 7x slower
		return ( new static( $list ) )
			->remove( array_keys( array_diff( $list, $elements ) ) )
			->remove( array_keys( array_diff( $elements, $list ) ) );
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
	 * The keys are preserved using this method.
	 *
	 * @param iterable<int|string,mixed> $elements List of elements
	 * @param  callable|null $callback Function with (valueA, valueB) parameters and returns -1 (<), 0 (=) and 1 (>)
	 * @return self<int|string,mixed> New map
	 */
	public function intersectAssoc( iterable $elements, callable $callback = null ) : self
	{
		$elements = $this->array( $elements );

		if( $callback ) {
			return new static( array_uintersect_assoc( $this->list(), $elements, $callback ) );
		}

		return new static( array_intersect_assoc( $this->list(), $elements ) );
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
	 * The keys are preserved using this method.
	 *
	 * @param iterable<int|string,mixed> $elements List of elements
	 * @param  callable|null $callback Function with (keyA, keyB) parameters and returns -1 (<), 0 (=) and 1 (>)
	 * @return self<int|string,mixed> New map
	 */
	public function intersectKeys( iterable $elements, callable $callback = null ) : self
	{
		$list = $this->list();
		$elements = $this->array( $elements );

		if( $callback ) {
			return new static( array_intersect_ukey( $list, $elements, $callback ) );
		}

		// using array_intersect_key() is 1.6x slower
		return ( new static( $list ) )
			->remove( array_keys( array_diff_key( $list, $elements ) ) )
			->remove( array_keys( array_diff_key( $elements, $list ) ) );
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
	 * @param iterable<int|string,mixed> $list List of key/value pairs to compare with
	 * @param bool $strict TRUE for comparing order of elements too, FALSE for key/values only
	 * @return bool TRUE if given list is equal, FALSE if not
	 */
	public function is( iterable $list, bool $strict = false ) : bool
	{
		$list = $this->array( $list );

		if( $strict ) {
			return $this->list() === $list;
		}

		return $this->list() == $list;
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
		return empty( $this->list() );
	}


	/**
	 * Determines if all entries are numeric values.
	 *
	 * Examples:
	 *  Map::from( [] )->isNumeric();
	 *  Map::from( [1] )->isNumeric();
	 *  Map::from( [1.1] )->isNumeric();
	 *  Map::from( [010] )->isNumeric();
	 *  Map::from( [0x10] )->isNumeric();
	 *  Map::from( [0b10] )->isNumeric();
	 *  Map::from( ['010'] )->isNumeric();
	 *  Map::from( ['10'] )->isNumeric();
	 *  Map::from( ['10.1'] )->isNumeric();
	 *  Map::from( [' 10 '] )->isNumeric();
	 *  Map::from( ['10e2'] )->isNumeric();
	 *  Map::from( ['0b10'] )->isNumeric();
	 *  Map::from( ['0x10'] )->isNumeric();
	 *  Map::from( ['null'] )->isNumeric();
	 *  Map::from( [null] )->isNumeric();
	 *  Map::from( [true] )->isNumeric();
	 *  Map::from( [[]] )->isNumeric();
	 *  Map::from( [''] )->isNumeric();
	 *
	 * Results:
	 *  The first eleven examples return TRUE while the last seven return FALSE
	 *
	 * @return bool TRUE if all map entries are numeric values, FALSE if not
	 */
	public function isNumeric() : bool
	{
		$result = true;

		foreach( $this->list() as $val )
		{
			if( !is_numeric( $val ) ) {
				$result = false;
			}
		}

		return $result;
	}


	/**
	 * Determines if all entries are objects.
	 *
	 * Examples:
	 *  Map::from( [] )->isObject();
	 *  Map::from( [new stdClass] )->isObject();
	 *  Map::from( [1] )->isObject();
	 *
	 * Results:
	 *  The first two examples return TRUE while the last one return FALSE
	 *
	 * @return bool TRUE if all map entries are objects, FALSE if not
	 */
	public function isObject() : bool
	{
		$result = true;

		foreach( $this->list() as $val )
		{
			if( !is_object( $val ) ) {
				$result = false;
			}
		}

		return $result;
	}


	/**
	 * Determines if all entries are scalar values.
	 *
	 * Examples:
	 *  Map::from( [] )->isScalar();
	 *  Map::from( [1] )->isScalar();
	 *  Map::from( [1.1] )->isScalar();
	 *  Map::from( ['abc'] )->isScalar();
	 *  Map::from( [true, false] )->isScalar();
	 *  Map::from( [new stdClass] )->isScalar();
	 *  Map::from( [#resource] )->isScalar();
	 *  Map::from( [null] )->isScalar();
	 *  Map::from( [[1]] )->isScalar();
	 *
	 * Results:
	 *  The first five examples return TRUE while the others return FALSE
	 *
	 * @return bool TRUE if all map entries are scalar values, FALSE if not
	 */
	public function isScalar() : bool
	{
		$result = true;

		foreach( $this->list() as $val )
		{
			if( !is_scalar( $val ) ) {
				$result = false;
			}
		}

		return $result;
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
		return implode( $glue, $this->list() );
	}


	/**
	 * Specifies the data which should be serialized to JSON by json_encode().
	 *
	 * Examples:
	 *   json_encode( Map::from( ['a', 'b'] ) );
	 *   json_encode( Map::from( ['a' => 0, 'b' => 1] ) );
	 *
	 * Results:
	 *   ["a", "b"]
	 *   {"a":0,"b":1}
	 *
	 * @return array<int|string,mixed> Data to serialize to JSON
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize()
	{
		return $this->list = $this->array( $this->list );
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
	 * @return self<int|string,mixed> New map
	 */
	public function keys() : self
	{
		return new static( array_keys( $this->list() ) );
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
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function krsort( int $options = SORT_REGULAR ) : self
	{
		krsort( $this->list(), $options );
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
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function ksort( int $options = SORT_REGULAR ) : self
	{
		ksort( $this->list(), $options );
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
		if( ( $value = end( $this->list() ) ) !== false ) {
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
		$list = $this->list();

		if( function_exists( 'array_key_last' ) ) {
			return array_key_last( $list );
		}

		end( $list );
		return key( $list );
	}


	/**
	 * Removes the passed characters from the left of all strings.
	 *
	 * Examples:
	 *  Map::from( [" abc\n", "\tcde\r\n"] )->ltrim();
	 *  Map::from( ["a b c", "cbxa"] )->ltrim( 'abc' );
	 *
	 * Results:
	 * The first example will return ["abc\n", "cde\r\n"] while the second one will return [" b c", "xa"].
	 *
	 * @param string $chars List of characters to trim
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function ltrim( string $chars = " \n\r\t\v\x00" ) : self
	{
		foreach( $this->list() as &$entry )
		{
			if( is_string( $entry ) ) {
				$entry = ltrim( $entry, $chars );
			}
		}

		return $this;
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
	 * The keys are preserved using this method.
	 *
	 * @param callable $callback Function with (value, key) parameters and returns computed result
	 * @return self<int|string,mixed> New map with the original keys and the computed values
	 */
	public function map( callable $callback ) : self
	{
		$list = $this->list();
		$keys = array_keys( $list );
		$elements = array_map( $callback, $list, $keys );

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
		$vals = $key !== null ? $this->col( $key )->toArray() : $this->list();
		return !empty( $vals ) ? max( $vals ) : null;
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
	 * The keys are preserved using this method.
	 *
	 * @param iterable<int|string,mixed> $elements List of elements
	 * @param bool $recursive TRUE to merge nested arrays too, FALSE for first level elements only
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function merge( iterable $elements, bool $recursive = false ) : self
	{
		if( $recursive ) {
			$this->list = array_merge_recursive( $this->list(), $this->array( $elements ) );
		} else {
			$this->list = array_merge( $this->list(), $this->array( $elements ) );
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
		$vals = $key !== null ? $this->col( $key )->toArray() : $this->list();
		return !empty( $vals ) ? min( $vals ) : null;
	}


	/**
	 * Tests if none of the elements are part of the map.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b'] )->none( 'x' );
	 *  Map::from( ['a', 'b'] )->none( ['x', 'y'] );
	 *  Map::from( ['1', '2'] )->none( 2, true );
	 *  Map::from( ['a', 'b'] )->none( 'a' );
	 *  Map::from( ['a', 'b'] )->none( ['a', 'b'] );
	 *  Map::from( ['a', 'b'] )->none( ['a', 'x'] );
	 *
	 * Results:
	 * The first three examples will return TRUE while the other ones will return FALSE
	 *
	 * @param mixed|array $element Element or elements to search for in the map
	 * @param bool $strict TRUE to check the type too, using FALSE '1' and 1 will be the same
	 * @return bool TRUE if none of the elements is part of the map, FALSE if at least one is
	 */
	public function none( $element, bool $strict = false ) : bool
	{
		$list = $this->list();

		if( !is_array( $element ) ) {
			return !in_array( $element, $list, $strict );
		};

		foreach( $element as $entry )
		{
			if( in_array( $entry, $list, $strict ) === true ) {
				return false;
			}
		}

		return true;
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
	 * @return self<int|string,mixed> New map
	 */
	public function nth( int $step, int $offset = 0 ) : self
	{
		$pos = 0;
		$result = [];

		foreach( $this->list() as $key => $item )
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
	 * @param int|string $key Key to check for
	 * @return bool TRUE if key exists, FALSE if not
	 */
	public function offsetExists( $key ) : bool
	{
		return isset( $this->list()[$key] );
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
	 * @param int|string $key Key to return the element for
	 * @return mixed Value associated to the given key
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet( $key )
	{
		return $this->list()[$key] ?? null;
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
	 * @param int|string|null $key Key to set the element for or NULL to append value
	 * @param mixed $value New value set for the key
	 */
	public function offsetSet( $key, $value ) : void
	{
		if( $key !== null ) {
			$this->list()[$key] = $value;
		} else {
			$this->list()[] = $value;
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
	 * @param int|string $key Key for unsetting the item
	 */
	public function offsetUnset( $key ) : void
	{
		unset( $this->list()[$key] );
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
	 * The keys are preserved using this method.
	 *
	 * @param iterable<mixed>|array<mixed>|string|int $keys Keys of the elements that should be returned
	 * @return self<int|string,mixed> New map with only the elements specified by the keys
	 */
	public function only( $keys ) : self
	{
		return $this->intersectKeys( array_flip( $this->array( $keys ) ) );
	}


	/**
	 * Returns a new map with elements ordered by the passed keys.
	 *
	 * If there are less keys passed than available in the map, the remaining
	 * elements are removed. Otherwise, if keys are passed that are not in the
	 * map, they will be also available in the returned map but their value is
	 * NULL.
	 *
	 * Examples:
	 *  Map::from( ['a' => 1, 1 => 'c', 0 => 'b'] )->order( [0, 1, 'a'] );
	 *  Map::from( ['a' => 1, 1 => 'c', 0 => 'b'] )->order( [0, 1, 2] );
	 *  Map::from( ['a' => 1, 1 => 'c', 0 => 'b'] )->order( [0, 1] );
	 *
	 * Results:
	 *  [0 => 'b', 1 => 'c', 'a' => 1]
	 *  [0 => 'b', 1 => 'c', 2 => null]
	 *  [0 => 'b', 1 => 'c']
	 *
	 * The keys are preserved using this method.
	 *
	 * @param iterable<mixed> $keys Keys of the elements in the required order
	 * @return self<int|string,mixed> New map with elements ordered by the passed keys
	 */
	public function order( iterable $keys ) : self
	{
		$result = [];
		$list = $this->list();

		foreach( $this->array( $keys ) as $key ) {
			$result[$key] = $list[$key] ?? null;
		}

		return new static( $result );
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
	 *  Map::from( [10 => 1, 20 => 2] )->pad( 3 );
	 *  Map::from( ['a' => 1, 'b' => 2] )->pad( 3, 3 );
	 *
	 * Results:
	 *  [1, 2, 3, null, null]
	 *  [null, null, 1, 2, 3]
	 *  [1, 2, 3, '0', '0']
	 *  [1, 2, 3]
	 *  [0 => 1, 1 => 2, 2 => null]
	 *  ['a' => 1, 'b' => 2, 0 => 3]
	 *
	 * Associative keys are preserved, numerical keys are replaced and numerical
	 * keys are used for the new elements.
	 *
	 * @param int $size Total number of elements that should be in the list
	 * @param mixed $value Value to fill up with if the map length is smaller than the given size
	 * @return self<int|string,mixed> New map
	 */
	public function pad( int $size, $value = null ) : self
	{
		return new static( array_pad( $this->list(), $size, $value ) );
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
	 * @return self<int|string,mixed> New map
	 */
	public function partition( $number ) : self
	{
		$list = $this->list();

		if( empty( $list ) ) {
			return new static();
		}

		$result = [];

		if( $number instanceof \Closure )
		{
			foreach( $list as $idx => $item ) {
				$result[$number( $item, $idx )][$idx] = $item;
			}

			return new static( $result );
		}
		elseif( is_int( $number ) )
		{
			$start = 0;
			$size = (int) ceil( count( $list ) / $number );

			for( $i = 0; $i < $number; $i++ )
			{
				$result[] = array_slice( $list, $start, $size, true );
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
	 * Returns the values of a single column/property from an array of arrays or objects in a new map.
	 *
	 * This method is an alias for col(). For performance reasons, col() should
	 * be preferred because it uses one method call less than pluck().
	 *
	 * @param string|null $valuecol Name or path of the value property
	 * @param string|null $indexcol Name or path of the index property
	 * @return self<int|string,mixed> New map with mapped entries
	 */
	public function pluck( string $valuecol = null, string $indexcol = null ) : self
	{
		return $this->col( $valuecol, $indexcol );
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
		return array_pop( $this->list() );
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
	 * @param \Closure|mixed $value Value to search for or function with (item, key) parameters return TRUE if value is found
	 * @return int|null Position of the found value (zero based) or NULL if not found
	 */
	public function pos( $value ) : ?int
	{
		$pos = 0;
		$list = $this->list();

		if( $value instanceof \Closure )
		{
			foreach( $list as $key => $item )
			{
				if( $value( $item, $key ) ) {
					return $pos;
				}

				++$pos;
			}
		}

		foreach( $list as $key => $item )
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
	 * The keys of the original map are preserved in the returned map.
	 *
	 * @param \Closure|string $prefix Prefix string or anonymous function with ($item, $key) as parameters
	 * @param int|null $depth Maximum depth to dive into multi-dimensional arrays starting from "1"
	 * @return self<int|string,mixed> Updated map for fluid interface
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

		$this->list = $fcn( $this->list(), $prefix, $depth ?? 0x7fffffff );
		return $this;
	}


	/**
	 * Pushes an element onto the beginning of the map without returning a new map.
	 *
	 * This method is an alias for unshift().
	 *
	 * @param mixed $value Item to add at the beginning
	 * @param int|string|null $key Key for the item or NULL to reindex all numerical keys
	 * @return self<int|string,mixed> Updated map for fluid interface
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
	 * @param int|string $key Key to retrieve the value for
	 * @param mixed $default Default value if key isn't available
	 * @return mixed Value from map or default value
	 */
	public function pull( $key, $default = null )
	{
		$value = $this->get( $key, $default );
		unset( $this->list()[$key] );

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
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function push( $value ) : self
	{
		$this->list()[] = $value;
		return $this;
	}


	/**
	 * Sets the given key and value in the map without returning a new map.
	 *
	 * Examples:
	 *  Map::from( ['a'] )->put( 1, 'b' );
	 *  Map::from( ['a'] )->put( 0, 'b' );
	 *
	 * Results:
	 * The first example results in ['a', 'b'] while the second one produces ['b']
	 *
	 * This method is an alias for set(). For performance reasons, set() should be
	 * preferred because it uses one method call less than put().
	 *
	 * @param int|string $key Key to set the new value for
	 * @param mixed $value New element that should be set
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function put( $key, $value ) : self
	{
		return $this->set( $key, $value );
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
	 * @return self<int|string,mixed> New map with key/element pairs from original map in random order
	 * @throws \InvalidArgumentException If requested number of elements is less than 1
	 */
	public function random( int $max = 1 ) : self
	{
		if( $max < 1 ) {
			throw new \InvalidArgumentException( 'Requested number of elements must be greater or equal than 1' );
		}

		$list = $this->list();

		if( empty( $list ) ) {
			return new static();
		}

		if( ( $num = count( $list ) ) < $max ) {
			$max = $num;
		}

		$keys = array_rand( $list, $max );

		return new static( array_intersect_key( $list, array_flip( (array) $keys ) ) );
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
		return array_reduce( $this->list(), $callback, $initial );
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
	 * removed. The keys of the original map are preserved in the returned map.
	 *
	 * @param Closure|mixed $callback Function with (item) parameter which returns TRUE/FALSE or value to compare with
	 * @return self<int|string,mixed> New map
	 */
	public function reject( $callback = true ) : self
	{
		$isCallable = $callback instanceof \Closure;

		return new static( array_filter( $this->list(), function( $value, $key ) use  ( $callback, $isCallable ) {
			return $isCallable ? !$callback( $value, $key ) : $value != $callback;
		}, ARRAY_FILTER_USE_BOTH ) );
	}


	/**
	 * Changes the keys according to the passed function.
	 *
	 * Examples:
	 *  Map::from( ['a' => 2, 'b' => 4] )->rekey( function( $value, $key ) {
	 *      return 'key-' . $key;
	 *  } );
	 *
	 * Results:
	 *  ['key-a' => 2, 'key-b' => 4]
	 *
	 * @param callable $callback Function with (value, key) parameters and returns new key
	 * @return self<int|string,mixed> New map with new keys and original values
	 */
	public function rekey( callable $callback ) : self
	{
		$list = $this->list();
		$keys = array_keys( $list );
		$newKeys = array_map( $callback, $list, $keys );

		return new static( array_combine( $newKeys, $list ) ?: [] );
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
	 * @param iterable<string|int>|array<string|int>|string|int $keys List of keys to remove
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function remove( $keys ) : self
	{
		foreach( $this->array( $keys ) as $key ) {
			unset( $this->list()[$key] );
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
	 * The keys are preserved using this method.
	 *
	 * @param iterable<int|string,mixed> $elements List of elements
	 * @param bool $recursive TRUE to replace recursively (default), FALSE to replace elements only
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function replace( iterable $elements, bool $recursive = true ) : self
	{
		if( $recursive ) {
			$this->list = array_replace_recursive( $this->list(), $this->array( $elements ) );
		} else {
			$this->list = array_replace( $this->list(), $this->array( $elements ) );
		}

		return $this;
	}


	/**
	 * Reverses the element order with keys without returning a new map.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b'] )->reverse();
	 *  Map::from( ['name' => 'test', 'last' => 'user'] )->reverse();
	 *
	 * Results:
	 *  ['b', 'a']
	 *  ['last' => 'user', 'name' => 'test']
	 *
	 * The keys are preserved using this method.
	 *
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function reverse() : self
	{
		$this->list = array_reverse( $this->list(), true );
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
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function rsort( int $options = SORT_REGULAR ) : self
	{
		rsort( $this->list(), $options );
		return $this;
	}


	/**
	 * Removes the passed characters from the right of all strings.
	 *
	 * Examples:
	 *  Map::from( [" abc\n", "\tcde\r\n"] )->rtrim();
	 *  Map::from( ["a b c", "cbxa"] )->rtrim( 'abc' );
	 *
	 * Results:
	 * The first example will return [" abc", "\tcde"] while the second one will return ["a b ", "cbx"].
	 *
	 * @param string $chars List of characters to trim
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function rtrim( string $chars = " \n\r\t\v\x00" ) : self
	{
		foreach( $this->list() as &$entry )
		{
			if( is_string( $entry ) ) {
				$entry = rtrim( $entry, $chars );
			}
		}

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
	 * @return mixed|null Key associated to the value or null if not found
	 */
	public function search( $value, $strict = true )
	{
		if( ( $result = array_search( $value, $this->list(), $strict ) ) !== false ) {
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
	 * @param string $char Separator character, e.g. "." for "key.to.value" instead of "key/to/value"
	 * @return self<int|string,mixed> Same map for fluid interface
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
	 *  ['a', 'b']
	 *  ['b']
	 *
	 * @param int|string $key Key to set the new value for
	 * @param mixed $value New element that should be set
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function set( $key, $value ) : self
	{
		$this->list()[(string) $key] = $value;
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
		return array_shift( $this->list() );
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
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function shuffle( bool $assoc = false ) : self
	{
		if( $assoc )
		{
			$list = $this->list();
			$keys = array_keys( $list );
			shuffle( $keys );
			$items = [];

			foreach( $keys as $key ) {
				$items[$key] = $list[$key];
			}

			$this->list = $items;
		}
		else
		{
			shuffle( $this->list() );
		}


		return $this;
	}


	/**
	 * Returns a new map with the given number of items skipped.
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
	 * The keys of the items returned in the new map are the same as in the original one.
	 *
	 * @param \Closure|int $offset Number of items to skip or function($item, $key) returning true for skipped items
	 * @return self<int|string,mixed> New map
	 */
	public function skip( $offset ) : self
	{
		if( is_scalar( $offset ) ) {
			return new static( array_slice( $this->list(), (int) $offset, null, true ) );
		}

		if( is_callable( $offset ) )
		{
			$idx = 0;
			$list = $this->list();

			foreach( $list as $key => $item )
			{
				if( !$offset( $item, $key ) ) {
					break;
				}

				++$idx;
			}

			return new static( array_slice( $list, $idx, null, true ) );
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
	 * The keys of the items returned in the new map are the same as in the original one.
	 *
	 * @param int $offset Number of elements to start from
	 * @param int|null $length Number of elements to return or NULL for no limit
	 * @return self<int|string,mixed> New map
	 */
	public function slice( int $offset, int $length = null ) : self
	{
		return new static( array_slice( $this->list(), $offset, $length, true ) );
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
		$list = $this->list();

		if( is_iterable( $values ) )
		{
			foreach( $values as $entry )
			{
				if( in_array( $entry, $list, $strict ) === true ) {
					return true;
				}
			}

			return false;
		}
		elseif( is_callable( $values ) )
		{
			foreach( $list as $key => $item )
			{
				if( $values( $item, $key ) ) {
					return true;
				}
			}
		}
		elseif( in_array( $values, $list, $strict ) === true )
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
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function sort( int $options = SORT_REGULAR ) : self
	{
		sort( $this->list(), $options );
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
	 * Numerical array indexes are NOT preserved.
	 *
	 * @param int $offset Number of elements to start from
	 * @param int|null $length Number of elements to remove, NULL for all
	 * @param mixed $replacement List of elements to insert
	 * @return self<int|string,mixed> New map
	 */
	public function splice( int $offset, int $length = null, $replacement = [] ) : self
	{
		if( $length === null ) {
			$length = count( $this->list() );
		}

		return new static( array_splice( $this->list(), $offset, $length, (array) $replacement ) );
	}


	/**
	 * Returns the strings after the passed value.
	 *
	 * Examples:
	 *  Map::from( ['äöüß'] )->strAfter( 'ö' );
	 *  Map::from( ['abc'] )->strAfter( '' );
	 *  Map::from( ['abc'] )->strAfter( 'b' );
	 *  Map::from( ['abc'] )->strAfter( 'c' );
	 *  Map::from( ['abc'] )->strAfter( 'x' );
	 *  Map::from( [''] )->strAfter( '' );
	 *  Map::from( [1, 1.0, true, ['x'], new \stdClass] )->strAfter( '' );
	 *  Map::from( [0, 0.0, false, []] )->strAfter( '' );
	 *
	 * Results:
	 *  ['üß']
	 *  ['abc']
	 *  ['c']
	 *  ['']
	 *  []
	 *  []
	 *  ['1', '1', '1']
	 *  ['0', '0']
	 *
	 * All scalar values (bool, int, float, string) will be converted to strings.
	 * Non-scalar values as well as empty strings will be skipped and are not part of the result.
	 *
	 * @param string $value Character or string to search for
	 * @param bool $case TRUE if search should be case insensitive, FALSE if case-sensitive
	 * @param string $encoding Character encoding of the strings, e.g. "UTF-8" (default), "ASCII", "ISO-8859-1", etc.
	 * @return self<int|string,mixed> New map
	 */
	public function strAfter( string $value, bool $case = false, string $encoding = 'UTF-8' ) : self
	{
		$list = [];
		$len = mb_strlen( $value );
		$fcn = $case ? 'mb_stripos' : 'mb_strpos';

		foreach( $this->list() as $key => $entry )
		{
			if( is_scalar( $entry ) )
			{
				$pos = null;
				$str = (string) $entry;

				if( $str !== '' && $value !== '' && ( $pos = $fcn( $str, $value, 0, $encoding ) ) !== false ) {
					$list[$key] = mb_substr( $str, $pos + $len, null, $encoding );
				} elseif( $str !== '' && $pos !== false ) {
					$list[$key] = $str;
				}
			}
		}

		return new static( $list );
	}


	/**
	 * Returns the strings before the passed value.
	 *
	 * Examples:
	 *  Map::from( ['äöüß'] )->strBefore( 'ü' );
	 *  Map::from( ['abc'] )->strBefore( '' );
	 *  Map::from( ['abc'] )->strBefore( 'b' );
	 *  Map::from( ['abc'] )->strBefore( 'a' );
	 *  Map::from( ['abc'] )->strBefore( 'x' );
	 *  Map::from( [''] )->strBefore( '' );
	 *  Map::from( [1, 1.0, true, ['x'], new \stdClass] )->strAfter( '' );
	 *  Map::from( [0, 0.0, false, []] )->strAfter( '' );
	 *
	 * Results:
	 *  ['äö']
	 *  ['abc']
	 *  ['a']
	 *  ['']
	 *  []
	 *  []
	 *  ['1', '1', '1']
	 *  ['0', '0']
	 *
	 * All scalar values (bool, int, float, string) will be converted to strings.
	 * Non-scalar values as well as empty strings will be skipped and are not part of the result.
	 *
	 * @param string $value Character or string to search for
	 * @param bool $case TRUE if search should be case insensitive, FALSE if case-sensitive
	 * @param string $encoding Character encoding of the strings, e.g. "UTF-8" (default), "ASCII", "ISO-8859-1", etc.
	 * @return self<int|string,mixed> New map
	 */
	public function strBefore( string $value, bool $case = false, string $encoding = 'UTF-8' ) : self
	{
		$list = [];
		$fcn = $case ? 'mb_strripos' : 'mb_strrpos';

		foreach( $this->list() as $key => $entry )
		{
			if( is_scalar( $entry ) )
			{
				$pos = null;
				$str = (string) $entry;

				if( $str !== '' && $value !== '' && ( $pos = $fcn( $str, $value, 0, $encoding ) ) !== false ) {
					$list[$key] = mb_substr( $str, 0, $pos, $encoding );
				} elseif( $str !== '' && $pos !== false ) {
					$list[$key] = $str;
				} else {
				}
			}
		}

		return new static( $list );
	}


	/**
	 * Tests if at least one of the passed strings is part of at least one entry.
	 *
	 * Examples:
	 *  Map::from( ['abc'] )->strContains( '' );
	 *  Map::from( ['abc'] )->strContains( 'a' );
	 *  Map::from( ['abc'] )->strContains( 'bc' );
	 *  Map::from( [12345] )->strContains( '23' );
	 *  Map::from( [123.4] )->strContains( 23.4 );
	 *  Map::from( [12345] )->strContains( false );
	 *  Map::from( [12345] )->strContains( true );
	 *  Map::from( [false] )->strContains( false );
	 *  Map::from( [''] )->strContains( false );
	 *  Map::from( ['abc'] )->strContains( ['b', 'd'] );
	 *  Map::from( ['abc'] )->strContains( 'c', 'ASCII' );
	 *
	 *  Map::from( ['abc'] )->strContains( 'd' );
	 *  Map::from( ['abc'] )->strContains( 'cb' );
	 *  Map::from( [23456] )->strContains( true );
	 *  Map::from( [false] )->strContains( 0 );
	 *  Map::from( ['abc'] )->strContains( ['d', 'e'] );
	 *  Map::from( ['abc'] )->strContains( 'cb', 'ASCII' );
	 *
	 * Results:
	 * The first eleven examples will return TRUE while the last six will return FALSE.
	 *
	 * @param array|string $value The string or list of strings to search for in each entry
	 * @param string $encoding Character encoding of the strings, e.g. "UTF-8" (default), "ASCII", "ISO-8859-1", etc.
	 * @return bool TRUE if one of the entries contains one of the strings, FALSE if not
	 */
	public function strContains( $value, string $encoding = 'UTF-8' ) : bool
	{
		foreach( $this->list() as $entry )
		{
			$entry = (string) $entry;

			foreach( (array) $value as $str )
			{
				$str = (string) $str;

				if( ( $str === '' || mb_strpos( $entry, (string) $str, 0, $encoding ) !== false ) ) {
					return true;
				}
			}
		}

		return false;
	}


	/**
	 * Tests if all of the entries contains one of the passed strings.
	 *
	 * Examples:
	 *  Map::from( ['abc', 'def'] )->strContainsAll( '' );
	 *  Map::from( ['abc', 'cba'] )->strContainsAll( 'a' );
	 *  Map::from( ['abc', 'bca'] )->strContainsAll( 'bc' );
	 *  Map::from( [12345, '230'] )->strContainsAll( '23' );
	 *  Map::from( [123.4, 23.42] )->strContainsAll( 23.4 );
	 *  Map::from( [12345, '234'] )->strContainsAll( [true, false] );
	 *  Map::from( ['', false] )->strContainsAll( false );
	 *  Map::from( ['abc', 'def'] )->strContainsAll( ['b', 'd'] );
	 *  Map::from( ['abc', 'ecf'] )->strContainsAll( 'c', 'ASCII' );
	 *
	 *  Map::from( ['abc', 'def'] )->strContainsAll( 'd' );
	 *  Map::from( ['abc', 'cab'] )->strContainsAll( 'cb' );
	 *  Map::from( [23456, '123'] )->strContainsAll( true );
	 *  Map::from( [false, '000'] )->strContainsAll( 0 );
	 *  Map::from( ['abc', 'acf'] )->strContainsAll( ['d', 'e'] );
	 *  Map::from( ['abc', 'bca'] )->strContainsAll( 'cb', 'ASCII' );
	 *
	 * Results:
	 * The first nine examples will return TRUE while the last six will return FALSE.
	 *
	 * @param array|string $value The string or list of strings to search for in each entry
	 * @param string $encoding Character encoding of the strings, e.g. "UTF-8" (default), "ASCII", "ISO-8859-1", etc.
	 * @return bool TRUE if all of the entries contains at least one of the strings, FALSE if not
	 */
	public function strContainsAll( $value, string $encoding = 'UTF-8' ) : bool
	{
		$list = [];

		foreach( $this->list() as $entry )
		{
			$entry = (string) $entry;
			$list[$entry] = 0;

			foreach( (array) $value as $str )
			{
				$str = (string) $str;

				if( (int) ( $str === '' || mb_strpos( $entry, (string) $str, 0, $encoding ) !== false ) ) {
					$list[$entry] = 1; break;
				}
			}
		}

		return array_sum( $list ) === count( $list );
	}


	/**
	 * Tests if at least one of the entries ends with one of the passed strings.
	 *
	 * Examples:
	 *  Map::from( ['abc'] )->strEnds( '' );
	 *  Map::from( ['abc'] )->strEnds( 'c' );
	 *  Map::from( ['abc'] )->strEnds( 'bc' );
	 *  Map::from( ['abc'] )->strEnds( ['b', 'c'] );
	 *  Map::from( ['abc'] )->strEnds( 'c', 'ASCII' );
	 *  Map::from( ['abc'] )->strEnds( 'a' );
	 *  Map::from( ['abc'] )->strEnds( 'cb' );
	 *  Map::from( ['abc'] )->strEnds( ['d', 'b'] );
	 *  Map::from( ['abc'] )->strEnds( 'cb', 'ASCII' );
	 *
	 * Results:
	 * The first five examples will return TRUE while the last four will return FALSE.
	 *
	 * @param array|string $value The string or strings to search for in each entry
	 * @param string $encoding Character encoding of the strings, e.g. "UTF-8" (default), "ASCII", "ISO-8859-1", etc.
	 * @return bool TRUE if one of the entries ends with one of the strings, FALSE if not
	 */
	public function strEnds( $value, string $encoding = 'UTF-8' ) : bool
	{
		foreach( $this->list() as $entry )
		{
			$entry = (string) $entry;

			foreach( (array) $value as $str )
			{
				$len = mb_strlen( (string) $str );

				if( ( $str === '' || mb_strpos( $entry, (string) $str, -$len, $encoding ) !== false ) ) {
					return true;
				}
			}
		}

		return false;
	}


	/**
	 * Tests if all of the entries ends with at least one of the passed strings.
	 *
	 * Examples:
	 *  Map::from( ['abc', 'def'] )->strEndsAll( '' );
	 *  Map::from( ['abc', 'bac'] )->strEndsAll( 'c' );
	 *  Map::from( ['abc', 'cbc'] )->strEndsAll( 'bc' );
	 *  Map::from( ['abc', 'def'] )->strEndsAll( ['c', 'f'] );
	 *  Map::from( ['abc', 'efc'] )->strEndsAll( 'c', 'ASCII' );
	 *  Map::from( ['abc', 'fed'] )->strEndsAll( 'd' );
	 *  Map::from( ['abc', 'bca'] )->strEndsAll( 'ca' );
	 *  Map::from( ['abc', 'acf'] )->strEndsAll( ['a', 'c'] );
	 *  Map::from( ['abc', 'bca'] )->strEndsAll( 'ca', 'ASCII' );
	 *
	 * Results:
	 * The first five examples will return TRUE while the last four will return FALSE.
	 *
	 * @param array|string $value The string or strings to search for in each entry
	 * @param string $encoding Character encoding of the strings, e.g. "UTF-8" (default), "ASCII", "ISO-8859-1", etc.
	 * @return bool TRUE if all of the entries ends with at least one of the strings, FALSE if not
	 */
	public function strEndsAll( $value, string $encoding = 'UTF-8' ) : bool
	{
		$list = [];

		foreach( $this->list() as $entry )
		{
			$entry = (string) $entry;
			$list[$entry] = 0;

			foreach( (array) $value as $str )
			{
				$len = mb_strlen( (string) $str );

				if( (int) ( $str === '' || mb_strpos( $entry, (string) $str, -$len, $encoding ) !== false ) ) {
					$list[$entry] = 1; break;
				}
			}
		}

		return array_sum( $list ) === count( $list );
	}


	/**
	 * Returns an element by key and casts it to string if possible.
	 *
	 * Examples:
	 *  Map::from( ['a' => true] )->string( 'a' );
	 *  Map::from( ['a' => 1] )->string( 'a' );
	 *  Map::from( ['a' => 1.1] )->string( 'a' );
	 *  Map::from( ['a' => 'abc'] )->string( 'a' );
	 *  Map::from( ['a' => ['b' => ['c' => 'yes']]] )->string( 'a/b/c' );
	 *  Map::from( [] )->string( 'a', function() { return 'no'; } );
	 *
	 *  Map::from( [] )->string( 'b' );
	 *  Map::from( ['b' => ''] )->string( 'b' );
	 *  Map::from( ['b' => null] )->string( 'b' );
	 *  Map::from( ['b' => [true]] )->string( 'b' );
	 *  Map::from( ['b' => resource] )->string( 'b' );
	 *  Map::from( ['b' => new \stdClass] )->string( 'b' );
	 *
	 *  Map::from( [] )->string( 'c', new \Exception( 'error' ) );
	 *
	 * Results:
	 * The first six examples will return the value as string while the 9th to 12th
	 * example returns an empty string. The last example will throw an exception.
	 *
	 * This does also work for multi-dimensional arrays by passing the keys
	 * of the arrays separated by the delimiter ("/" by default), e.g. "key1/key2/key3"
	 * to get "val" from ['key1' => ['key2' => ['key3' => 'val']]]. The same applies to
	 * public properties of objects or objects implementing __isset() and __get() methods.
	 *
	 * @param int|string $key Key or path to the requested item
	 * @param mixed $default Default value if key isn't found (will be casted to bool)
	 * @return string Value from map or default value
	 */
	public function string( $key, $default = '' ) : string
	{
		return (string) ( is_scalar( $val = $this->get( $key, $default ) ) ? $val : $default );
	}


	/**
	 * Converts all alphabetic characters in strings to lower case.
	 *
	 * Examples:
	 *  Map::from( ['My String'] )->strLower();
	 *  Map::from( ['Τάχιστη'] )->strLower();
	 *  Map::from( ['Äpfel', 'Birnen'] )->strLower( 'ISO-8859-1' );
	 *  Map::from( [123] )->strLower();
	 *  Map::from( [new stdClass] )->strLower();
	 *
	 * Results:
	 * The first example will return ["my string"], the second one ["τάχιστη"] and
	 * the third one ["äpfel", "birnen"]. The last two strings will be unchanged.
	 *
	 * @param string $encoding Character encoding of the strings, e.g. "UTF-8" (default), "ASCII", "ISO-8859-1", etc.
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function strLower( string $encoding = 'UTF-8' ) : self
	{
		foreach( $this->list() as &$entry )
		{
			if( is_string( $entry ) ) {
				$entry = mb_strtolower( $entry, $encoding );
			}
		}

		return $this;
	}


	/**
	 * Replaces all occurrences of the search string with the replacement string.
	 *
	 * Examples:
	 * Map::from( ['google.com', 'aimeos.com'] )->strReplace( '.com', '.de' );
	 * Map::from( ['google.com', 'aimeos.org'] )->strReplace( ['.com', '.org'], '.de' );
	 * Map::from( ['google.com', 'aimeos.org'] )->strReplace( ['.com', '.org'], ['.de'] );
	 * Map::from( ['google.com', 'aimeos.org'] )->strReplace( ['.com', '.org'], ['.fr', '.de'] );
	 * Map::from( ['google.com', 'aimeos.com'] )->strReplace( ['.com', '.co'], ['.co', '.de', '.fr'] );
	 * Map::from( ['google.com', 'aimeos.com', 123] )->strReplace( '.com', '.de' );
	 * Map::from( ['GOOGLE.COM', 'AIMEOS.COM'] )->strReplace( '.com', '.de', true );
	 *
	 * Restults:
	 * ['google.de', 'aimeos.de']
	 * ['google.de', 'aimeos.de']
	 * ['google.de', 'aimeos']
	 * ['google.fr', 'aimeos.de']
	 * ['google.de', 'aimeos.de']
	 * ['google.de', 'aimeos.de', 123]
	 * ['GOOGLE.de', 'AIMEOS.de']
	 *
	 * If you use an array of strings for search or search/replacement, the order of
	 * the strings matters! Each search string found is replaced by the corresponding
	 * replacement string at the same position.
	 *
	 * In case of array parameters and if the number of replacement strings is less
	 * than the number of search strings, the search strings with no corresponding
	 * replacement string are replaced with empty strings. Replacement strings with
	 * no corresponding search string are ignored.
	 *
	 * An array parameter for the replacements is only allowed if the search parameter
	 * is an array of strings too!
	 *
	 * Because the method replaces from left to right, it might replace a previously
	 * inserted value when doing multiple replacements. Entries which are non-string
	 * values are left untouched.
	 *
	 * @param array|string $search String or list of strings to search for
	 * @param array|string $replace String or list of strings of replacement strings
	 * @param bool $case TRUE if replacements should be case insensitive, FALSE if case-sensitive
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function strReplace( $search, $replace, bool $case = false ) : self
	{
		$fcn = $case ? 'str_ireplace' : 'str_replace';

		foreach( $this->list() as &$entry )
		{
			if( is_string( $entry ) ) {
				$entry = $fcn( $search, $replace, $entry );
			}
		}

		return $this;
	}


	/**
	 * Tests if at least one of the entries starts with at least one of the passed strings.
	 *
	 * Examples:
	 *  Map::from( ['abc'] )->strStarts( '' );
	 *  Map::from( ['abc'] )->strStarts( 'a' );
	 *  Map::from( ['abc'] )->strStarts( 'ab' );
	 *  Map::from( ['abc'] )->strStarts( ['a', 'b'] );
	 *  Map::from( ['abc'] )->strStarts( 'ab', 'ASCII' );
	 *  Map::from( ['abc'] )->strStarts( 'b' );
	 *  Map::from( ['abc'] )->strStarts( 'bc' );
	 *  Map::from( ['abc'] )->strStarts( ['b', 'c'] );
	 *  Map::from( ['abc'] )->strStarts( 'bc', 'ASCII' );
	 *
	 * Results:
	 * The first five examples will return TRUE while the last four will return FALSE.
	 *
	 * @param array|string $value The string or strings to search for in each entry
	 * @param string $encoding Character encoding of the strings, e.g. "UTF-8" (default), "ASCII", "ISO-8859-1", etc.
	 * @return bool TRUE if all of the entries ends with at least one of the strings, FALSE if not
	 */
	public function strStarts( $value, string $encoding = 'UTF-8' ) : bool
	{
		foreach( $this->list() as $entry )
		{
			$entry = (string) $entry;

			foreach( (array) $value as $str )
			{
				if( ( $str === '' || mb_strpos( $entry, (string) $str, 0, $encoding ) === 0 ) ) {
					return true;
				}
			}
		}

		return false;
	}


	/**
	 * Tests if all of the entries starts with one of the passed strings.
	 *
	 * Examples:
	 *  Map::from( ['abc', 'def'] )->strStartsAll( '' );
	 *  Map::from( ['abc', 'acb'] )->strStartsAll( 'a' );
	 *  Map::from( ['abc', 'aba'] )->strStartsAll( 'ab' );
	 *  Map::from( ['abc', 'def'] )->strStartsAll( ['a', 'd'] );
	 *  Map::from( ['abc', 'acf'] )->strStartsAll( 'a', 'ASCII' );
	 *  Map::from( ['abc', 'def'] )->strStartsAll( 'd' );
	 *  Map::from( ['abc', 'bca'] )->strStartsAll( 'ab' );
	 *  Map::from( ['abc', 'bac'] )->strStartsAll( ['a', 'c'] );
	 *  Map::from( ['abc', 'cab'] )->strStartsAll( 'ab', 'ASCII' );
	 *
	 * Results:
	 * The first five examples will return TRUE while the last four will return FALSE.
	 *
	 * @param array|string $value The string or strings to search for in each entry
	 * @param string $encoding Character encoding of the strings, e.g. "UTF-8" (default), "ASCII", "ISO-8859-1", etc.
	 * @return bool TRUE if one of the entries starts with one of the strings, FALSE if not
	 */
	public function strStartsAll( $value, string $encoding = 'UTF-8' ) : bool
	{
		$list = [];

		foreach( $this->list() as $entry )
		{
			$entry = (string) $entry;
			$list[$entry] = 0;

			foreach( (array) $value as $str )
			{
				if( (int) ( $str === '' || mb_strpos( $entry, (string) $str, 0, $encoding ) === 0 ) ) {
					$list[$entry] = 1; break;
				}
			}
		}

		return array_sum( $list ) === count( $list );
	}


	/**
	 * Converts all alphabetic characters in strings to upper case.
	 *
	 * Examples:
	 *  Map::from( ['My String'] )->strUpper();
	 *  Map::from( ['τάχιστη'] )->strUpper();
	 *  Map::from( ['äpfel', 'birnen'] )->strUpper( 'ISO-8859-1' );
	 *  Map::from( [123] )->strUpper();
	 *  Map::from( [new stdClass] )->strUpper();
	 *
	 * Results:
	 * The first example will return ["MY STRING"], the second one ["ΤΆΧΙΣΤΗ"] and
	 * the third one ["ÄPFEL", "BIRNEN"]. The last two strings will be unchanged.
	 *
	 * @param string $encoding Character encoding of the strings, e.g. "UTF-8" (default), "ASCII", "ISO-8859-1", etc.
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function strUpper( string $encoding = 'UTF-8' ) :self
	{
		foreach( $this->list() as &$entry )
		{
			if( is_string( $entry ) ) {
				$entry = mb_strtoupper( $entry, $encoding );
			}
		}

		return $this;
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
	 * The keys are preserved using this method.
	 *
	 * @param \Closure|string $suffix Suffix string or anonymous function with ($item, $key) as parameters
	 * @param int|null $depth Maximum depth to dive into multi-dimensional arrays starting from "1"
	 * @return self<int|string,mixed> Updated map for fluid interface
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

		$this->list = $fcn( $this->list(), $suffix, $depth ?? 0x7fffffff );
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
	 * @return float Sum of all elements or 0 if there are no elements in the map
	 */
	public function sum( string $key = null ) : float
	{
		$vals = $key !== null ? $this->col( $key )->toArray() : $this->list();
		return !empty( $vals ) ? array_sum( $vals ) : 0;
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
	 * The keys of the items returned in the new map are the same as in the original one.
	 *
	 * @param int $size Number of items to return
	 * @param \Closure|int $offset Number of items to skip or function($item, $key) returning true for skipped items
	 * @return self<int|string,mixed> New map
	 */
	public function take( int $size, $offset = 0 ) : self
	{
		$list = $this->list();

		if( is_scalar( $offset ) ) {
			return new static( array_slice( $list, (int) $offset, $size, true ) );
		}

		if( is_callable( $offset ) )
		{
			$idx = 0;

			foreach( $list as $key => $item )
			{
				if( !$offset( $item, $key ) ) {
					break;
				}

				++$idx;
			}

			return new static( array_slice( $list, $idx, $size, true ) );
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
	 * It will sort the list in reverse order (`[1, 2, 3]`) while keeping the keys,
	 * then prints the items without the first (`[2, 3]`) in the function passed
	 * to `tap()` and returns the first item ("1") at the end.
	 *
	 * @param callable $callback Function receiving ($map) parameter
	 * @return self<int|string,mixed> Same map for fluid interface
	 */
	public function tap( callable $callback ) : self
	{
		$callback( clone $this );
		return $this;
	}


	/**
	 * Returns the elements as a plain array.
	 *
	 * @return array<int|string,mixed> Plain array
	 */
	public function toArray() : array
	{
		return $this->list = $this->array( $this->list );
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
	 * @return string|null Array encoded as JSON string or NULL on failure
	 */
	public function toJson( int $options = 0 ) : ?string
	{
		$result = json_encode( $this->list(), $options );
		return $result !== false ? $result : null;
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
		return http_build_query( $this->list(), '', '&', PHP_QUERY_RFC3986 );
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
	 * @return self<int|string,mixed> New map
	 */
	public function transpose() : self
	{
		$result = [];

		foreach( (array) $this->first( [] ) as $key => $col ) {
			$result[$key] = array_column( $this->list(), $key );
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
	 * @return self<int|string,mixed> New map with all items as flat list
	 */
	public function traverse( \Closure $callback = null, string $nestKey = 'children' ) : self
	{
		$result = [];
		$this->visit( $this->list(), $result, 0, $callback, $nestKey );

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
	 * @return self<int|string,mixed> New map with one or more root tree nodes
	 */
	public function tree( string $idKey, string $parentKey, string $nestKey = 'children' ) : self
	{
		$this->list();
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
	 * Removes the passed characters from the left/right of all strings.
	 *
	 * Examples:
	 *  Map::from( [" abc\n", "\tcde\r\n"] )->trim();
	 *  Map::from( ["a b c", "cbax"] )->trim( 'abc' );
	 *
	 * Results:
	 * The first example will return ["abc", "cde"] while the second one will return [" b ", "x"].
	 *
	 * @param string $chars List of characters to trim
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function trim( string $chars = " \n\r\t\v\x00" ) : self
	{
		foreach( $this->list() as &$entry )
		{
			if( is_string( $entry ) ) {
				$entry = trim( $entry, $chars );
			}
		}

		return $this;
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
	 * @param callable $callback Function with (itemA, itemB) parameters and returns -1 (<), 0 (=) and 1 (>)
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function uasort( callable $callback ) : self
	{
		uasort( $this->list(), $callback );
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
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function uksort( callable $callback ) : self
	{
		uksort( $this->list(), $callback );
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
	 * The keys are preserved using this method and no new map is created.
	 *
	 * @param iterable<int|string,mixed> $elements List of elements
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function union( iterable $elements ) : self
	{
		$this->list = $this->list() + $this->array( $elements );
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
	 * @return self<int|string,mixed> New map
	 */
	public function unique( string $key = null ) : self
	{
		if( $key !== null ) {
			return $this->col( null, $key )->values();
		}

		return new static( array_unique( $this->list() ) );
	}


	/**
	 * Pushes an element onto the beginning of the map without returning a new map.
	 *
	 * Examples:
	 *  Map::from( ['a', 'b'] )->unshift( 'd' );
	 *  Map::from( ['a', 'b'] )->unshift( 'd', 'first' );
	 *
	 * Results:
	 *  ['d', 'a', 'b']
	 *  ['first' => 'd', 0 => 'a', 1 => 'b']
	 *
	 * The keys of the elements are only preserved in the new map if no key is passed.
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
	 * @param int|string|null $key Key for the item or NULL to reindex all numerical keys
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function unshift( $value, $key = null ) : self
	{
		if( $key === null ) {
			array_unshift( $this->list(), $value );
		} else {
			$this->list = [$key => $value] + $this->list();
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
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function usort( callable $callback ) : self
	{
		usort( $this->list(), $callback );
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
	 * @return self<int|string,mixed> New map of the values
	 */
	public function values() : self
	{
		return new static( array_values( $this->list() ) );
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
	 * @return self<int|string,mixed> Updated map for fluid interface
	 */
	public function walk( callable $callback, $data = null, bool $recursive = true ) : self
	{
		if( $recursive ) {
			array_walk_recursive( $this->list(), $callback, $data );
		} else {
			array_walk( $this->list(), $callback, $data );
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
	 * The keys of the original map are preserved in the returned map.
	 *
	 * @param string $key Key or path of the value in the array or object used for comparison
	 * @param string $op Operator used for comparison
	 * @param mixed $value Value used for comparison
	 * @return self<int|string,mixed> New map for fluid interface
	 */
	public function where( string $key, string $op, $value ) : self
	{
		return $this->filter( function( $item ) use ( $key, $op, $value ) {

			if( ( $val = $this->val( $item, explode( $this->sep, $key ) ) ) !== null )
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
	 * @param array<int|string,mixed>|\Traversable<int|string,mixed>|\Iterator<int|string,mixed> $arrays List of arrays to merge with at the same position
	 * @return self<int|string,mixed> New map of arrays
	 */
	public function zip( ...$arrays ) : self
	{
		$args = array_map( function( $items ) {
			return $this->array( $items );
		}, $arrays );

		return new static( array_map( null, $this->list(), ...$args ) );
	}


	/**
	 * Returns a plain array of the given elements.
	 *
	 * @param mixed $elements List of elements or single value
	 * @return array<int|string,mixed> Plain array
	 */
	protected function array( $elements ) : array
	{
		if( is_array( $elements ) ) {
			return $elements;
		}

		if( $elements instanceof \Closure ) {
			return (array) $elements();
		}

		if( $elements instanceof \Aimeos\Map ) {
			return $elements->toArray();
		}

		if( is_iterable( $elements ) ) {
			return iterator_to_array( $elements, true );
		}

		return $elements !== null ? [$elements] : [];
	}


	/**
	 * Flattens a multi-dimensional array or map into a single level array.
	 *
	 * @param iterable<int|string,mixed> $entries Single of multi-level array, map or everything foreach can be used with
	 * @param array<mixed> &$result Will contain all elements from the multi-dimensional arrays afterwards
	 * @param int $depth Number of levels to flatten in multi-dimensional arrays
	 */
	protected function flatten( iterable $entries, array &$result, int $depth ) : void
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
	 * Flattens a multi-dimensional array or map into a single level array.
	 *
	 * @param iterable<int|string,mixed> $entries Single of multi-level array, map or everything foreach can be used with
	 * @param array<int|string,mixed> $result Will contain all elements from the multi-dimensional arrays afterwards
	 * @param int $depth Number of levels to flatten in multi-dimensional arrays
	 */
	protected function kflatten( iterable $entries, array &$result, int $depth ) : void
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
	 * Returns a reference to the array of elements
	 *
	 * @return array Reference to the array of elements
	 */
	protected function &list() : array
	{
		if( !is_array( $this->list ) ) {
			$this->list = $this->array( $this->list );
		}

		return $this->list;
	}


	/**
	 * Returns a configuration value from an array.
	 *
	 * @param array<mixed>|object $entry The array or object to look at
	 * @param array<string> $parts Path parts to look for inside the array or object
	 * @return mixed Found value or null if no value is available
	 */
	protected function val( $entry, array $parts )
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
	 * Visits each entry, calls the callback and returns the items in the result argument
	 *
	 * @param iterable<int|string,mixed> $entries List of entries with children (optional)
	 * @param array<mixed> $result Numerically indexed list of all visited entries
	 * @param int $level Current depth of the nodes in the tree
	 * @param \Closure|null $callback Callback with ($entry, $key, $level) arguments, returns the entry added to result
	 * @param string $nestKey Key to the children of each entry
	 */
	protected function visit( iterable $entries, array &$result, int $level, ?\Closure $callback, string $nestKey ) : void
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
