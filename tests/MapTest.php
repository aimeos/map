<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 * @author Taylor Otwell, Aimeos.org developers
 */


namespace Aimeos;


class MapTest extends \PHPUnit\Framework\TestCase
{
	public function testFunction()
	{
		$this->assertInstanceOf( Map::class, \map() );
		$this->assertInstanceOf( Map::class, \map( [] ) );
		$this->assertInstanceOf( Map::class, \map( 'a' ) );
		$this->assertInstanceOf( Map::class, \map( new Map ) );
	}


	public function testIsMap()
	{
		$this->assertTrue( is_map( map() ) );
		$this->assertFalse( is_map( null ) );
		$this->assertFalse( is_map( true ) );
	}


	public function testMagicCall()
	{
		$m = new Map( ['a' => new TestMapObject(), 'b' => new TestMapObject()] );
		$this->assertSame( ['a' => 1, 'b' => 2], $m->setId( 1 )->getCode()->toArray() );
	}


	public function testMagicToArray()
	{
		$m = new Map( ['name' => 'Hello'] );
		$this->assertSame( ['name' => 'Hello'], $m->__toArray() );
	}


	public function testAfter()
	{
		$this->assertSame( [1 => 'a'], Map::from( [0 => 'b', 1 => 'a'] )->after( 'b' )->toArray() );
	}


	public function testAfterInt()
	{
		$this->assertSame( ['b' => 0], Map::from( ['a' => 1, 'b' => 0] )->after( 1 )->toArray() );
	}


	public function testAfterNone()
	{
		$this->assertSame( [], Map::from( [0 => 'b', 1 => 'a'] )->after( 'c' )->toArray() );
	}


	public function testAfterCallback()
	{
		$this->assertSame( [2 => 'b'], Map::from( ['a', 'c', 'b'] )->after( function( $item, $key ) {
			return $item >= 'c';
		} )->toArray() );
	}


	public function testAll()
	{
		$m = new Map( ['name' => 'Hello'] );
		$this->assertSame( ['name' => 'Hello'], $m->all() );
	}


	public function testArsortNummeric()
	{
		$m = ( new Map( [1 => -3, 2 => -2, 3 => -4, 4 => -1, 5 => 0, 6 => 4, 7 => 3, 8 => 1, 9 => 2] ) )->arsort();

		$this->assertInstanceOf( Map::class, $m );
		$this->assertSame( [6 => 4, 7 => 3, 9 => 2, 8 => 1, 5 => 0, 4 => -1, 2 => -2, 1 => -3, 3 => -4], $m->toArray() );
	}


	public function testArsortStrings()
	{
		$m = ( new Map( ['c' => 'bar-10', 1 => 'bar-1', 'a' => 'foo'] ) )->arsort();

		$this->assertInstanceOf( Map::class, $m );
		$this->assertSame( ['a' => 'foo', 'c' => 'bar-10', 1 => 'bar-1'], $m->toArray() );
	}


	public function testArsortStringsCase()
	{
		$m = ( new Map( [0 => 'C', 1 => 'b'] ) );

		$this->assertSame( [1 => 'b', 0 => 'C'], $m->arsort()->toArray() );
		$this->assertSame( [0 => 'C', 1 => 'b'], $m->arsort( SORT_STRING|SORT_FLAG_CASE )->toArray() );
	}


	public function testAsortNummeric()
	{
		$m = ( new Map( [1 => -3, 2 => -2, 3 => -4, 4 => -1, 5 => 0, 6 => 4, 7 => 3, 8 => 1, 9 => 2] ) )->asort();

		$this->assertInstanceOf( Map::class, $m );
		$this->assertSame( [3 => -4, 1 => -3, 2 => -2, 4 => -1, 5 => 0, 8 => 1, 9 => 2, 7 => 3, 6 => 4], $m->toArray() );
	}


	public function testAsortStrings()
	{
		$m = ( new Map( ['a' => 'foo', 'c' => 'bar-10', 1 => 'bar-1'] ) )->asort();

		$this->assertInstanceOf( Map::class, $m );
		$this->assertSame( [1 => 'bar-1', 'c' => 'bar-10', 'a' => 'foo'], $m->toArray() );
	}


	public function testAsortStringsCase()
	{
		$m = ( new Map( [0 => 'C', 1 => 'b'] ) );

		$this->assertSame( [0 => 'C', 1 => 'b'], $m->asort()->toArray() );
		$this->assertSame( [1 => 'b', 0 => 'C'], $m->asort( SORT_STRING|SORT_FLAG_CASE )->toArray() );
	}


	public function testAt()
	{
		$this->assertSame( 1, Map::from( [1, 3, 5] )->at( 0 ) );
		$this->assertSame( 3, Map::from( [1, 3, 5] )->at( 1 ) );
		$this->assertSame( 5, Map::from( [1, 3, 5] )->at( -1 ) );
		$this->assertNull( Map::from( [1, 3, 5] )->at( 3 ) );
	}


	public function testAvg()
	{
		$this->assertSame( 3.0, Map::from( [1, 3, 5] )->avg() );
		$this->assertSame( 2.0, Map::from( [1, null, 5] )->avg() );
		$this->assertSame( 2.0, Map::from( [1, 'sum', 5] )->avg() );
	}


	public function testAvgPath()
	{
		$this->assertSame( 30.0, Map::from( [['p' => 30], ['p' => 50], ['p' => 10]] )->avg( 'p' ) );
		$this->assertSame( 40.0, Map::from( [['i' => ['p' => 30]], ['i' => ['p' => 50]]] )->avg( 'i/p' ) );
	}


	public function testBefore()
	{
		$this->assertSame( [0 => 'b'], Map::from( [0 => 'b', 1 => 'a'] )->before( 'a' )->toArray() );
	}


	public function testBeforeInt()
	{
		$this->assertSame( ['a' => 1], Map::from( ['a' => 1, 'b' => 0] )->before( 0 )->toArray() );
	}


	public function testBeforeNone()
	{
		$this->assertSame( [], Map::from( [0 => 'b', 1 => 'a'] )->before( 'b' )->toArray() );
	}


	public function testBeforeCallback()
	{
		$this->assertSame( [0 => 'a'], Map::from( ['a', 'c', 'b'] )->before( function( $item, $key ) {
			return $key >= 1;
		} )->toArray() );
	}


	public function testBool()
	{
		$this->assertEquals( true, Map::from( ['a' => true] )->bool( 'a' ) );
		$this->assertEquals( true, Map::from( ['a' => '1'] )->bool( 'a' ) );
		$this->assertEquals( true, Map::from( ['a' => 1.1] )->bool( 'a' ) );
		$this->assertEquals( true, Map::from( ['a' => '10'] )->bool( 'a' ) );
		$this->assertEquals( true, Map::from( ['a' => 'abc'] )->bool( 'a' ) );
		$this->assertEquals( true, Map::from( ['a' => ['b' => ['c' => true]]] )->bool( 'a/b/c' ) );

		$this->assertEquals( false, Map::from( [] )->bool( 'b' ) );
		$this->assertEquals( false, Map::from( ['b' => ''] )->bool( 'b' ) );
		$this->assertEquals( false, Map::from( ['b' => null] )->bool( 'b' ) );
		$this->assertEquals( false, Map::from( ['b' => [true]] )->bool( 'b' ) );
		$this->assertEquals( false, Map::from( ['b' => new \stdClass] )->bool( 'b' ) );
	}


	public function testBoolClosure()
	{
		$this->assertEquals( true, Map::from( [] )->bool( 'c', function() { return rand( 1, 2 ); } ) );
	}


	public function testBoolException()
	{
		$this->expectException( \RuntimeException::class );
		Map::from( [] )->bool( 'c', new \RuntimeException( 'error' ) );
	}


	public function testCall()
	{
		$m = new Map( ['a' => new TestMapObject(), 'b' => new TestMapObject()] );

		$this->assertSame( ['a' => 'p1', 'b' => 'p2'], $m->call( 'get', [1] )->toArray() );
		$this->assertSame( ['a' => ['prop' => 'p3'], 'b' => ['prop' => 'p4']], $m->call( 'toArray' )->toArray() );
	}


	public function testCast()
	{
		$this->assertEquals( ['1', '1', '1', 'yes'], Map::from( [true, 1, 1.0, 'yes'] )->cast()->all() );
		$this->assertEquals( [true, true, true, true], Map::from( [true, 1, 1.0, 'yes'] )->cast( 'bool' )->all() );
		$this->assertEquals( [1, 1, 1, 0], Map::from( [true, 1, 1.0, 'yes'] )->cast( 'int' )->all() );
		$this->assertEquals( [1.0, 1.0, 1.0, 0.0], Map::from( [true, 1, 1.0, 'yes'] )->cast( 'float' )->all() );
		$this->assertEquals( [[], []], Map::from( [new \stdClass, new \stdClass] )->cast( 'array' )->all() );
		$this->assertEquals( [new \stdClass, new \stdClass], Map::from( [[], []] )->cast( 'object' )->all() );
	}


	public function testChunk()
	{
		$m = new Map( [0, 1, 2, 3, 4] );
		$this->assertSame( [[0, 1, 2], [3, 4]], $m->chunk( 3 )->toArray() );
	}


	public function testChunkException()
	{
		$this->expectException( \InvalidArgumentException::class );
		Map::from( [] )->chunk( 0 );
	}


	public function testChunkKeys()
	{
		$m = new Map( ['a' => 0, 'b' => 1, 'c' => 2] );
		$this->assertSame( [['a' => 0, 'b' => 1], ['c' => 2]], $m->chunk( 2, true )->toArray() );
	}


	public function testClear()
	{
		$m = new Map( ['foo', 'bar'] );
		$this->assertInstanceOf( Map::class, $m->clear() );
	}


	public function testClone()
	{
		$m1 = new Map( [new \stdClass, new \stdClass] );
		$m2 = $m1->clone();

		$this->assertInstanceOf( Map::class, $m1->clear() );
		$this->assertInstanceOf( Map::class, $m2 );
		$this->assertCount( 2, $m2 );
		$this->assertNotSame( $m2->first(), $m1->first() );
	}


	public function testCol()
	{
		$map = new Map( [['foo' => 'one', 'bar' => 'two']] );
		$r = $map->col( 'bar' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( [0 => 'two'], $r->toArray() );
	}


	public function testColIndex()
	{
		$map = new Map( [['foo' => 'one', 'bar' => 'two']] );
		$r = $map->col( 'bar', 'foo' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['one' => 'two'], $r->toArray() );
	}


	public function testColIndexDuplicate()
	{
		$map = new Map( [['id' => 'ix', 'val' => 'v1'], ['id' => 'ix', 'val' => 'v2']] );
		$r = $map->col( null, 'id' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['ix' => ['id' => 'ix', 'val' => 'v2']], $r->toArray() );
	}


	public function testColIndexNull()
	{
		$map = new Map( [['bar' => 'two']] );
		$r = $map->col( 'bar', 'foo' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['two'], $r->toArray() );
	}


	public function testColIndexOnly()
	{
		$map = new Map( [['foo' => 'one', 'bar' => 'two']] );
		$r = $map->col( null, 'foo' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['one' => ['foo' => 'one', 'bar' => 'two']], $r->toArray() );
	}


	public function testColRecursive()
	{
		$map = new Map( [['foo' => ['bar' => 'one', 'baz' => 'two']]] );
		$r = $map->col( 'foo/baz', 'foo/bar' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['one' => 'two'], $r->toArray() );
	}


	public function testColRecursiveNull()
	{
		$map = new Map( [['foo' => ['bar' => 'one']]] );
		$r = $map->col( 'foo/baz', 'foo/bar' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['one' => null], $r->toArray() );
	}


	public function testColRecursiveIndexNull()
	{
		$map = new Map( [['foo' => ['baz' => 'two']]] );
		$r = $map->col( 'foo/baz', 'foo/bar' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['two'], $r->toArray() );
	}


	public function testCollapse()
	{
		$m = Map::from( [0 => ['a' => 0, 'b' => 1], 1 => ['c' => 2, 'd' => 3]]);
		$this->assertSame( ['a' => 0, 'b' => 1, 'c' => 2, 'd' => 3], $m->collapse()->toArray() );
	}


	public function testCollapseOverwrite()
	{
		$m = Map::from( [0 => ['a' => 0, 'b' => 1], 1 => ['a' => 2]] );
		$this->assertSame( ['a' => 2, 'b' => 1], $m->collapse()->toArray() );
	}


	public function testCollapseRecursive()
	{
		$m = Map::from( [0 => [0 => 0, 1 => 1], 1 => [0 => ['a' => 2, 0 => 3], 1 => 4]] );
		$this->assertSame( [0 => 3, 1 => 4, 'a' => 2], $m->collapse()->toArray() );
	}


	public function testCollapseDepth()
	{
		$m = Map::from( [0 => [0 => 0, 'a' => 1], 1 => [0 => ['b' => 2, 0 => 3], 1 => 4]] );
		$this->assertSame( [0 => ['b' => 2, 0 => 3], 'a' => 1, 1 => 4], $m->collapse( 1 )->toArray() );
	}


	public function testCollapseIterable()
	{
		$m = Map::from( [0 => [0 => 0, 'a' => 1], 1 => Map::from( [0 => ['b' => 2, 0 => 3], 1 => 4] )] );
		$this->assertSame( [0 => 3, 'a' => 1, 'b' => 2, 1 => 4], $m->collapse()->toArray() );
	}


	public function testCollapseException()
	{
		$this->expectException( \InvalidArgumentException::class );
		Map::from( [] )->collapse( -1 );
	}


	public function testCombine()
	{
		$r = Map::from( ['name', 'age'] )->combine( ['Tom', 29] );
		$this->assertSame( ['name' => 'Tom', 'age' => 29], $r->toArray() );
	}


	public function testCompare()
	{
		$this->assertEquals( true, Map::from( ['foo', 'bar'] )->compare( 'foo' ) );
		$this->assertEquals( true, Map::from( ['foo', 'bar'] )->compare( 'Foo', false ) );
		$this->assertEquals( true, Map::from( [123, 12.3] )->compare( '12.3' ) );
		$this->assertEquals( true, Map::from( [false, true] )->compare( '1' ) );
		$this->assertEquals( false, Map::from( ['foo', 'bar'] )->compare( 'Foo' ) );
		$this->assertEquals( false, Map::from( ['foo', 'bar'] )->compare( 'baz' ) );
		$this->assertEquals( false, Map::from( [new \stdClass(), 'bar'] )->compare( 'foo' ) );
	}


	public function testConcatWithArray()
	{
		$first = new Map( [1, 2] );
		$r = $first->concat( ['a', 'b'] )->concat( ['x' => 'foo', 'y' => 'bar'] );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( [1, 2, 'a', 'b', 'foo', 'bar'], $r->toArray() );
	}


	public function testConcatMap()
	{
		$first = new Map( [1, 2] );
		$second = new Map( ['a', 'b'] );
		$third = new Map( ['x' => 'foo', 'y' => 'bar'] );

		$r = $first->concat( $second )->concat( $third );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( [1, 2, 'a', 'b', 'foo', 'bar'], $r->toArray() );
	}


	public function testConstruct()
	{
		$map = new Map;
		$this->assertEmpty( $map->toArray() );
	}


	public function testConstructMap()
	{
		$firstMap = new Map( ['foo' => 'bar'] );
		$secondMap = new Map( $firstMap );

		$this->assertInstanceOf( Map::class, $firstMap );
		$this->assertInstanceOf( Map::class, $secondMap );
		$this->assertSame( ['foo' => 'bar'], $secondMap->toArray() );
	}


	public function testConstructArray()
	{
		$map = new Map( ['foo' => 'bar'] );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertSame( ['foo' => 'bar'], $map->toArray() );
	}


	public function testConstructTraversable()
	{
		$map = new Map( new \ArrayObject( [1, 2, 3] ) );
		$this->assertSame( [1, 2, 3], $map->toArray() );
	}


	public function testConstructTraversableKeys()
	{
		$map = new Map( new \ArrayObject( ['foo' => 1, 'bar' => 2, 'baz' => 3] ) );
		$this->assertSame( ['foo' => 1, 'bar' => 2, 'baz' => 3], $map->toArray() );
	}


	public function testContains()
	{
		$this->assertTrue( Map::from( ['a', 'b'] )->contains( 'a' ) );
		$this->assertTrue( Map::from( ['a', 'b'] )->contains( ['a', 'c'] ) );
		$this->assertTrue( Map::from( ['a', 'b'] )->some( function( $item, $key ) {
			return $item === 'a';
		} ) );
	}


	public function testContainsWhere()
	{
		$this->assertTrue( Map::from( [['type' => 'name']] )->contains( 'type', 'name' ) );
		$this->assertTrue( Map::from( [['type' => 'name']] )->contains( 'type', '==', 'name' ) );
		$this->assertFalse( Map::from( [['type' => 'name']] )->contains( 'type', '!=', 'name' ) );
	}


	public function testCopy()
	{
		$m1 = new Map( ['foo', 'bar'] );
		$m2 = $m1->copy();

		$this->assertInstanceOf( Map::class, $m1->clear() );
		$this->assertInstanceOf( Map::class, $m2 );
		$this->assertCount( 2, $m2 );
	}


	public function testCountable()
	{
		$m = new Map( ['foo', 'bar'] );
		$this->assertCount( 2, $m );
	}


	public function testCountBy()
	{
		$r = Map::from( [1, 'foo', 2, 'foo', 1] )->countBy();

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( [1 => 2, 'foo' => 2, 2 => 1], $r->toArray() );
	}


	public function testCountByCallback()
	{
		$r = Map::from( ['a@gmail.com', 'b@yahoo.com', 'c@gmail.com'] )->countBy( function( $email ) {
			return substr( (string) strrchr( $email, '@' ), 1 );
		} );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['gmail.com' => 2, 'yahoo.com' => 1], $r->toArray() );
	}


	public function testCountByFloat()
	{
		$r = Map::from( [1.11, 3.33, 3.33, 9.99] )->countBy();

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['1.11' => 1, '3.33' => 2, '9.99' => 1], $r->toArray() );
	}


	public function testDelimiter()
	{
		$this->assertSame( '/', Map::delimiter() );
		$this->assertSame( '/', Map::delimiter( '.' ) );
		$this->assertSame( '.', Map::delimiter( '/' ) );
		$this->assertSame( '/', Map::delimiter() );
	}


	public function testDiff()
	{
		$m = new Map( ['id' => 1, 'first_word' => 'Hello'] );
		$r = $m->diff( new Map( ['first_word' => 'Hello', 'last_word' => 'World'] ) );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['id' => 1], $r->toArray() );
	}


	public function testDiffUsingWithMap()
	{
		$m = new Map( ['en_GB', 'fr', 'HR'] );
		$r = $m->diff( new Map( ['en_gb', 'hr'] ) );

		$this->assertInstanceOf( Map::class, $r );
		// demonstrate that diffKeys wont support case insensitivity
		$this->assertSame( ['en_GB', 'fr', 'HR'], $r->values()->toArray() );
	}


	public function testDiffCallback()
	{
		$m1 = new Map( ['a' => 'green', 'b' => 'brown', 'c' => 'blue', 'red'] );
		$m2 = new Map( ['A' => 'Green', 'yellow', 'red'] );
		$r1 = $m1->diff( $m2 );
		$r2 = $m1->diff( $m2, 'strcasecmp' );

		// demonstrate that the case of the keys will affect the output when diff is used
		$this->assertInstanceOf( Map::class, $r1 );
		$this->assertSame( ['a' => 'green', 'b' => 'brown', 'c' => 'blue'], $r1->toArray() );

		// allow for case insensitive difference
		$this->assertInstanceOf( Map::class, $r2 );
		$this->assertSame( ['b' => 'brown', 'c' => 'blue'], $r2->toArray() );
	}


	public function testDiffAssoc()
	{
		$m1 = new Map( ['id' => 1, 'first_word' => 'Hello', 'not_affected' => 'value'] );
		$m2 = new Map( ['id' => 123, 'foo_bar' => 'Hello', 'not_affected' => 'value'] );
		$r = $m1->diffAssoc( $m2 );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['id' => 1, 'first_word' => 'Hello'], $r->toArray() );
	}


	public function testDiffAssocCallback()
	{
		$m1 = new Map( ['a' => 'green', 'b' => 'brown', 'c' => 'blue', 'red'] );
		$m2 = new Map( ['A' => 'green', 'yellow', 'red'] );
		$r1 = $m1->diffAssoc( $m2 );
		$r2 = $m1->diffAssoc( $m2, 'strcasecmp' );

		// demonstrate that the case of the keys will affect the output when diffAssoc is used
		$this->assertInstanceOf( Map::class, $r1 );
		$this->assertSame( ['a' => 'green', 'b' => 'brown', 'c' => 'blue', 'red'], $r1->toArray() );

		// allow for case insensitive difference
		$this->assertInstanceOf( Map::class, $r2 );
		$this->assertSame( ['b' => 'brown', 'c' => 'blue', 'red'], $r2->toArray() );
	}


	public function testDiffKeys()
	{
		$m1 = new Map( ['id' => 1, 'first_word' => 'Hello'] );
		$m2 = new Map( ['id' => 123, 'foo_bar' => 'Hello'] );
		$r = $m1->diffKeys( $m2 );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['first_word' => 'Hello'], $r->toArray() );
	}


	public function testDiffKeysCallback()
	{
		$m1 = new Map( ['id' => 1, 'first_word' => 'Hello'] );
		$m2 = new Map( ['ID' => 123, 'foo_bar' => 'Hello'] );
		$r1 = $m1->diffKeys( $m2 );
		$r2 = $m1->diffKeys( $m2, 'strcasecmp' );

		// demonstrate that diffKeys wont support case insensitivity
		$this->assertInstanceOf( Map::class, $r1 );
		$this->assertSame( ['id'=>1, 'first_word'=> 'Hello'], $r1->toArray() );

		// allow for case insensitive difference
		$this->assertInstanceOf( Map::class, $r2 );
		$this->assertSame( ['first_word' => 'Hello'], $r2->toArray() );
	}


	public function testDump()
	{
		$r = Map::from( ['a' => 'foo', 'b' => 'bar'] )->dump()->sort()->dump( 'print_r' );

		$this->assertInstanceOf( Map::class, $r );
		$this->expectOutputString( 'Array
(
    [a] => foo
    [b] => bar
)
Array
(
    [0] => bar
    [1] => foo
)
' );
	}


	public function testDuplicates()
	{
		$r = Map::from( [1, 2, '1', 3] )->duplicates();

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( [2 => '1'], $r->toArray() );
	}


	public function testDuplicatesColumn()
	{
		$r = Map::from( [['p' => '1'], ['p' => 1], ['p' => 2]] )->duplicates( 'p' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( [1 => ['p' => 1]], $r->toArray() );
	}


	public function testDuplicatesPath()
	{
		$r = Map::from( [['i' => ['p' => '1']], ['i' => ['p' => 1]]] )->duplicates( 'i/p' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( [1 => ['i' => ['p' => 1]]], $r->toArray() );
	}


	public function testEach()
	{
		$m = new Map( $original = [1, 2, 'foo' => 'bar', 'bam' => 'baz'] );

		$result = [];
		$r = $m->each( function( $item, $key ) use ( &$result ) {
			$result[$key] = $item;
		} );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( $original, $result );
	}


	public function testEachFalse()
	{
		$m = new Map( $original = [1, 2, 'foo' => 'bar', 'bam' => 'baz'] );

		$result = [];
		$r = $m->each( function( $item, $key ) use ( &$result ) {
			$result[$key] = $item;
			if( is_string( $key ) ) {
				return false;
			}
		} );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( [1, 2, 'foo' => 'bar'], $result );
	}


	public function testEmpty()
	{
		$m = new Map;
		$this->assertTrue( $m->empty() );
	}


	public function testEmptyFalse()
	{
		$m = new Map( ['foo'] );
		$this->assertFalse( $m->empty() );
	}


	public function testEquals()
	{
		$map = new Map( ['foo' => 'one', 'bar' => 'two'] );

		$this->assertTrue( $map->equals( ['foo' => 'one', 'bar' => 'two'] ) );
		$this->assertTrue( $map->equals( ['bar' => 'two', 'foo' => 'one'] ) );
	}


	public function testEqualsTypes()
	{
		$map = new Map( ['foo' => 1, 'bar' => '2'] );

		$this->assertTrue( $map->equals( ['foo' => '1', 'bar' => 2] ) );
		$this->assertTrue( $map->equals( ['bar' => 2, 'foo' => '1'] ) );
	}


	public function testEqualsNoKeys()
	{
		$map = new Map( ['foo' => 'one', 'bar' => 'two'] );

		$this->assertTrue( $map->equals( [0 => 'one', 1 => 'two'] ) );
		$this->assertTrue( $map->equals( [0 => 'two', 1 => 'one'] ) );
	}


	public function testEqualsLess()
	{
		$map = new Map( ['foo' => 'one', 'bar' => 'two'] );
		$this->assertFalse( $map->equals( ['foo' => 'one'] ) );
	}


	public function testEqualsLessKeys()
	{
		$map = new Map( ['foo' => 'one', 'bar' => 'two'] );
		$this->assertFalse( $map->equals( ['foo' => 'one'] ) );
	}


	public function testEqualsMore()
	{
		$map = new Map( ['foo' => 'one', 'bar' => 'two'] );
		$this->assertFalse( $map->equals( ['foo' => 'one', 'bar' => 'two', 'baz' => 'three'] ) );
	}


	public function testEvery()
	{
		$this->assertTrue( Map::from( [0 => 'a', 1 => 'b'] )->every( function( $value, $key ) {
			return is_string( $value );
		} ) );

		$this->assertFalse( Map::from( [0 => 'a', 1 => 100] )->every( function( $value, $key ) {
			return is_string( $value );
		} ) );
	}


	public function testExcept()
	{
		$this->assertSame( ['a' => 1, 'c' => 3], Map::from( ['a' => 1, 'b' => 2, 'c' => 3] )->except( 'b' )->toArray() );
		$this->assertSame( [2 => 'b'], Map::from( [1 => 'a', 2 => 'b', 3 => 'c'] )->except( [1, 3] )->toArray() );
	}


	public function testExplode()
	{
		$map = Map::explode( ',', 'a,b,c' );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertSame( ['a', 'b', 'c'], $map->toArray() );
	}


	public function testExplodeString()
	{
		$map = Map::explode( '<-->', 'a a<-->b b<-->c c' );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertSame( ['a a', 'b b', 'c c'], $map->toArray() );
	}


	public function testExplodeSplit()
	{
		$map = Map::explode( '', 'string' );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertSame( ['s', 't', 'r', 'i', 'n', 'g'], $map->toArray() );
	}


	public function testExplodeSplitSize()
	{
		$map = Map::explode( '', 'string', 6 );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertSame( ['s', 't', 'r', 'i', 'n', 'g'], $map->toArray() );
	}


	public function testExplodeLength()
	{
		$map = Map::explode( '|', 'a|b|c', 2 );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertSame( ['a', 'b|c'], $map->toArray() );
	}


	public function testExplodeSplitLength()
	{
		$map = Map::explode( '', 'string', 2 );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertSame( ['s', 't', 'ring'], $map->toArray() );
	}


	public function testExplodeNegativeLength()
	{
		$map = Map::explode( '|', 'a|b|c|d', -2 );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertSame( ['a', 'b'], $map->toArray() );
	}


	public function testExplodeSplitNegativeLength()
	{
		$map = Map::explode( '', 'string', -3 );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertSame( ['s', 't', 'r'], $map->toArray() );
	}


	public function testFilter()
	{
		$m = new Map( [['id' => 1, 'name' => 'Hello'], ['id' => 2, 'name' => 'World']] );

		$this->assertSame( [1 => ['id' => 2, 'name' => 'World']], $m->filter( function( $item ) {
			return $item['id'] == 2;
		} )->toArray() );
	}


	public function testFilterNoCallback()
	{
		$m = new Map( ['', 'Hello', '', 'World'] );
		$r = $m->filter();

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['Hello', 'World'], $r->values()->toArray() );
	}


	public function testFilterRemove()
	{
		$m = new Map( ['id' => 1, 'first' => 'Hello', 'second' => 'World'] );

		$this->assertSame( ['first' => 'Hello', 'second' => 'World'], $m->filter( function( $item, $key ) {
			return $key != 'id';
		} )->toArray() );
	}


	public function testFind()
	{
		$m = new Map( ['foo', 'bar', 'baz', 'boo'] );
		$result = $m->find( function( $value, $key ) {
			return !strncmp( $value, 'ba', 2 );
		} );
		$this->assertSame( 'bar', $result );
	}


	public function testFindLast()
	{
		$m = new Map( ['foo', 'bar', 'baz', 'boo'] );
		$result = $m->find( function( $value, $key ) {
			return !strncmp( $value, 'ba', 2 );
		}, null, true );
		$this->assertSame( 'baz', $result );
	}


	public function testFindDefault()
	{
		$m = new Map( ['foo', 'bar', 'baz'] );
		$result = $m->find( function( $value ) {
			return false;
		}, 'none' );
		$this->assertSame( 'none', $result );
	}


	public function testFindException()
	{
		$m = new Map( ['foo', 'bar', 'baz'] );

		$this->expectException( \RuntimeException::class );

		$result = $m->find( function( $value ) {
			return false;
		}, new \RuntimeException( 'error' ) );
	}


	public function testFirst()
	{
		$m = new Map( ['foo', 'bar'] );
		$this->assertSame( 'foo', $m->first() );
	}


	public function testFirstWithDefault()
	{
		$m = new Map;
		$result = $m->first( 'default' );
		$this->assertSame( 'default', $result );
	}


	public function testFirstWithException()
	{
		$m = new Map;

		$this->expectException( \RuntimeException::class );
		$result = $m->first( new \RuntimeException( 'error' ) );
	}


	public function testFirstWithClosure()
	{
		$m = new Map;
		$result = $m->first( function() { return rand( 10, 11 ); } );

		$this->assertGreaterThanOrEqual( 10, $result );
	}


	public function testFirstKey()
	{
		$this->assertSame( 'a', Map::from( ['a' => 1, 'b' => 2] )->firstKey() );
	}


	public function testFirstKeyEmpty()
	{
		$this->assertSame( null, Map::from( [] )->firstKey() );
	}


	public function testFlat()
	{
		$m = Map::from( [[0, 1], [2, 3]] );
		$this->assertSame( [0, 1, 2, 3], $m->flat()->toArray() );
	}


	public function testFlatNone()
	{
		$m = Map::from( [[0, 1], [2, 3]] );
		$this->assertSame( [[0, 1], [2, 3]], $m->flat( 0 )->toArray() );
	}


	public function testFlatRecursive()
	{
		$m = Map::from( [[0, 1], [[2, 3], 4]] );
		$this->assertSame( [0, 1, 2, 3, 4], $m->flat()->toArray() );
	}


	public function testFlatDepth()
	{
		$m = Map::from( [[0, 1], [[2, 3], 4]] );
		$this->assertSame( [0, 1, [2, 3], 4], $m->flat( 1 )->toArray() );
	}


	public function testFlatTraversable()
	{
		$m = Map::from( [[0, 1], Map::from( [[2, 3], 4] )] );
		$this->assertSame( [0, 1, 2, 3, 4], $m->flat()->toArray() );
	}


	public function testFlatException()
	{
		$this->expectException( \InvalidArgumentException::class );
		Map::from( [] )->flat( -1 );
	}


	public function testFlip()
	{
		$m = Map::from( ['a' => 'X', 'b' => 'Y'] );
		$this->assertSame( ['X' => 'a', 'Y' => 'b'], $m->flip()->toArray() );
	}


	public function testFloat()
	{
		$this->assertSame( 1.0, Map::from( ['a' => true] )->float( 'a' ) );
		$this->assertSame( 1.0, Map::from( ['a' => 1] )->float( 'a' ) );
		$this->assertSame( 1.1, Map::from( ['a' => '1.1'] )->float( 'a' ) );
		$this->assertSame( 10.0, Map::from( ['a' => '10'] )->float( 'a' ) );
		$this->assertSame( 1.1, Map::from( ['a' => ['b' => ['c' => 1.1]]] )->float( 'a/b/c' ) );
		$this->assertSame( 1.1, Map::from( [] )->float( 'a', 1.1 ) );

		$this->assertSame( 0.0, Map::from( [] )->float( 'b' ) );
		$this->assertSame( 0.0, Map::from( ['b' => ''] )->float( 'b' ) );
		$this->assertSame( 0.0, Map::from( ['a' => 'abc'] )->float( 'a' ) );
		$this->assertSame( 0.0, Map::from( ['b' => null] )->float( 'b' ) );
		$this->assertSame( 0.0, Map::from( ['b' => [true]] )->float( 'b' ) );
		$this->assertSame( 0.0, Map::from( ['b' => new \stdClass] )->float( 'b' ) );
	}


	public function testFloatClosure()
	{
		$this->assertSame( 1.1, Map::from( [] )->float( 'c', function() { return 1.1; } ) );
	}


	public function testFloatException()
	{
		$this->expectException( \RuntimeException::class );
		Map::from( [] )->float( 'c', new \RuntimeException( 'error' ) );
	}


	public function testFromNull()
	{
		$m = Map::from( null );

		$this->assertInstanceOf( Map::class, $m );
		$this->assertSame( [], $m->toArray() );
	}


	public function testFromValue()
	{
		$m = Map::from( 'a' );

		$this->assertInstanceOf( Map::class, $m );
		$this->assertSame( [0 => 'a'], $m->toArray() );
	}


	public function testFromMap()
	{
		$firstMap = Map::from( ['foo' => 'bar'] );
		$secondMap = Map::from( $firstMap );

		$this->assertInstanceOf( Map::class, $firstMap );
		$this->assertInstanceOf( Map::class, $secondMap );
		$this->assertSame( $firstMap, $secondMap );
	}


	public function testFromArray()
	{
		$map = Map::from( ['foo' => 'bar'] );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertSame( ['foo' => 'bar'], $map->toArray() );
	}


	public function testFromJson()
	{
		$map = Map::fromJson( '["a", "b"]' );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertSame( ['a', 'b'], $map->toArray() );
	}


	public function testFromJsonObject()
	{
		$map = Map::fromJson( '{"a": "b"}' );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertSame( ['a' => 'b'], $map->toArray() );
	}


	public function testFromJsonEmpty()
	{
		$map = Map::fromJson( '""' );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertSame( [''], $map->toArray() );
	}


	public function testFromJsonException()
	{
		$this->expectException( '\RuntimeException' );
		Map::fromJson( '' )->toArray();
	}


	public function testGetArray()
	{
		$map = new Map;

		$class = new \ReflectionClass( $map );
		$method = $class->getMethod( 'array' );
		$method->setAccessible( true );

		$items = new \ArrayIterator( ['foo' => 'bar'] );
		$array = $method->invokeArgs( $map, [$items] );
		$this->assertSame( ['foo' => 'bar'], $array );

		$items = new Map( ['foo' => 'bar'] );
		$array = $method->invokeArgs( $map, [$items] );
		$this->assertSame( ['foo' => 'bar'], $array );

		$items = ['foo' => 'bar'];
		$array = $method->invokeArgs( $map, [$items] );
		$this->assertSame( ['foo' => 'bar'], $array );
	}


	public function testGetIterator()
	{
		$m = new Map( ['foo'] );
		$this->assertInstanceOf( \ArrayIterator::class, $m->getIterator() );
		$this->assertSame( ['foo'], $m->getIterator()->getArrayCopy() );
	}


	public function testGet()
	{
		$map = new Map( ['a' => 1, 'b' => 2, 'c' => 3] );
		$this->assertSame( 2, $map->get( 'b' ) );
	}


	public function testGetPath()
	{
		$this->assertSame( 'Y', Map::from( ['a' => ['b' => ['c' => 'Y']]] )->get( 'a/b/c' ) );
	}


	public function testGetPathObject()
	{
		$obj = new \stdClass;
		$obj->b = 'X';

		$this->assertSame( 'X', Map::from( ['a' => $obj] )->get( 'a/b' ) );
	}


	public function testGetWithNull()
	{
		$map = new Map( [1, 2, 3] );
		$this->assertNull( $map->get( 'a' ) );
	}


	public function testGetWithException()
	{
		$m = new Map;

		$this->expectException( \RuntimeException::class );
		$m->get( 'Y', new \RuntimeException( 'error' ) );
	}


	public function testGetWithClosure()
	{
		$m = new Map;
		$result = $m->get( 1, function() { return rand( 10, 11 ); } );

		$this->assertGreaterThanOrEqual( 10, $result );
	}


	public function testGrep()
	{
		$r = Map::from( ['ab', 'bc', 'cd'] )->grep( '/b/' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['ab', 'bc'], $r->toArray() );
	}


	public function testGrepInvert()
	{
		$r = Map::from( ['ab', 'bc', 'cd'] )->grep( '/a/', PREG_GREP_INVERT );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( [1 => 'bc', 2 => 'cd'], $r->toArray() );
	}


	public function testGrepException()
	{
		set_error_handler( function( $errno, $str, $file, $line ) { return true; } );

		$this->expectException( \RuntimeException::class );
		Map::from( [] )->grep( 'b' );
	}


	public function testGrepWarning()
	{
		if( method_exists( $this, 'expectWarning' ) ) {
			$this->expectWarning(); // PHPUnit 8+
		} else {
			$this->expectException( \PHPUnit\Framework\Error\Warning::class ); // PHP 7.1
		}

		Map::from( [] )->grep( 'b' );
	}


	public function testGrepNumbers()
	{
		$r = Map::from( [1.5, 0, 0.0, 'a'] )->grep( '/^(\d+)?\.\d+$/' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( [1.5], $r->toArray() );
	}


	public function testGroupBy()
	{
		$list = [
			10 => ['aid' => 123, 'code' => 'x-abc'],
			20 => ['aid' => 123, 'code' => 'x-def'],
			30 => ['aid' => 456, 'code' => 'x-def']
		];
		$expected = [
			123 => [10 => ['aid' => 123, 'code' => 'x-abc'], 20 => ['aid' => 123, 'code' => 'x-def']],
			456 => [30 => ['aid' => 456, 'code' => 'x-def']]
		];

		$r = Map::from( $list )->groupBy( 'aid' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( $expected, $r->toArray() );
	}


	public function testGroupByCallback()
	{
		$list = [
			10 => ['aid' => 123, 'code' => 'x-abc'],
			20 => ['aid' => 123, 'code' => 'x-def'],
			30 => ['aid' => 456, 'code' => 'x-def']
		];
		$expected = [
			'abc' => [10 => ['aid' => 123, 'code' => 'x-abc']],
			'def' => [20 => ['aid' => 123, 'code' => 'x-def'], 30 => ['aid' => 456, 'code' => 'x-def']]
		];

		$r = Map::from( $list )->groupBy( function( $item, $key ) {
			return substr( $item['code'], -3 );
		} );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( $expected, $r->toArray() );
	}


	public function testGroupByInvalid()
	{
		$list = [
			10 => ['aid' => 123, 'code' => 'x-abc'],
			20 => ['aid' => 123, 'code' => 'x-def'],
			30 => ['aid' => 456, 'code' => 'x-def']
		];
		$expected = [
			'' => [
				10 => ['aid' => 123, 'code' => 'x-abc'],
				20 => ['aid' => 123, 'code' => 'x-def'],
				30 => ['aid' => 456, 'code' => 'x-def']
			]
		];

		$r = Map::from( $list )->groupBy( 'xid' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( $expected, $r->toArray() );
	}


	public function testGroupByObject()
	{
		$list = [
			10 => (object) ['aid' => 123, 'code' => 'x-abc'],
			20 => (object) ['aid' => 123, 'code' => 'x-def'],
			30 => (object) ['aid' => 456, 'code' => 'x-def']
		];
		$expected = [
			123 => [10 => (object) ['aid' => 123, 'code' => 'x-abc'], 20 => (object) ['aid' => 123, 'code' => 'x-def']],
			456 => [30 => (object) ['aid' => 456, 'code' => 'x-def']]
		];

		$r = Map::from( $list )->groupBy( 'aid' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( $expected, $r->toArray() );
	}


	public function testHas()
	{
		$m = new Map( ['id' => 1, 'first' => 'Hello', 'second' => 'World'] );

		$this->assertTrue( $m->has( 'first' ) );
		$this->assertFalse( $m->has( 'third' ) );
	}


	public function testHasMultiple()
	{
		$m = new Map( ['id' => 1, 'first' => 'Hello', 'second' => 'World'] );

		$this->assertTrue( $m->has( ['first', 'second'] ) );
		$this->assertFalse( $m->has( ['first', 'third'] ) );
	}


	public function testHasPath()
	{
		$m = new Map( ['a' => ['b' => ['c' => 'Y']]] );

		$this->assertTrue( $m->has( 'a/b/c' ) );
		$this->assertFalse( $m->has( 'a/b/c/d' ) );
		$this->assertTrue( $m->has( ['a', 'a/b', 'a/b/c'] ) );
		$this->assertFalse( $m->has( ['a', 'a/b', 'a/b/c', 'a/b/c/d'] ) );
	}


	public function testIf()
	{
		$r = Map::from( ['a'] )->if(
			true,
			function( Map $_ ) { return ['b']; }
		);

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['b'], $r->all() );
	}


	public function testIfThen()
	{
		$r = Map::from( ['a'] )->if(
			function( Map $map ) { return $map->in( 'a' ); },
			function( Map $_ ) { $this->assertTrue( true ); },
			function( Map $_ ) { $this->assertTrue( false ); }
		);

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( [], $r->all() );
	}


	public function testIfElse()
	{
		$r = Map::from( ['a'] )->if(
			function( Map $map ) { return $map->in( 'c' ); },
			function( Map $_ ) { $this->assertTrue( false ); },
			function( Map $_ ) { $this->assertTrue( true ); }
		);

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( [], $r->all() );
	}


	public function testIfTrue()
	{
		$r = Map::from( ['a', 'b'] )->if( true, function( $map ) {
			return $map->push( 'c' );
		} );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['a', 'b', 'c'], $r->all() );
	}


	public function testIfFalse()
	{
		$r = Map::from( ['a', 'b'] )->if( false, null, function( $map ) {
			return $map->pop();
		} );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['b'], $r->all() );
	}


	public function testIfAny()
	{
		$r = Map::from( ['a'] )->ifAny(
			function( Map $_ ) { return ['b']; }
		);

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['b'], $r->all() );
	}


	public function testIfAnyFalse()
	{
		$r = Map::from( [] )->ifAny(
			function( Map $m ) { return $m->push( 'b' ); },
			function( Map $m ) { return $m->push( 'c' ); }
		);

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['c'], $r->all() );
	}


	public function testIfAnyNone()
	{
		$r = Map::from( ['a'] )->ifAny();

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['a'], $r->all() );
	}


	public function testImplements()
	{
		$this->assertTrue( Map::from( [new Map(), new Map()] )->implements( '\Countable' ) );
		$this->assertFalse( Map::from( [new Map(), new \stdClass()] )->implements( '\Countable' ) );
		$this->assertFalse( Map::from( [new Map(), 123] )->implements( '\Countable' ) );
	}


	public function testImplementsException()
	{
		$this->expectException( \UnexpectedValueException::class );
		Map::from( [new Map(), 123] )->implements( '\Countable', true );
	}


	public function testImplementsCustomException()
	{
		$this->expectException( \RuntimeException::class );
		Map::from( [new Map(), 123] )->implements( '\Countable',  \RuntimeException::class );
	}


	public function testIn()
	{
		$this->assertTrue( Map::from( ['a', 'b'] )->in( 'a' ) );
		$this->assertTrue( Map::from( ['a', 'b'] )->in( ['a', 'b'] ) );
		$this->assertFalse( Map::from( ['a', 'b'] )->in( 'x' ) );
		$this->assertFalse( Map::from( ['a', 'b'] )->in( ['a', 'x'] ) );
		$this->assertFalse( Map::from( ['1', '2'] )->in( 2, true ) );
	}


	public function testIncludes()
	{
		$this->assertTrue( Map::from( ['a', 'b'] )->includes( 'a' ) );
		$this->assertFalse( Map::from( ['a', 'b'] )->includes( 'x' ) );
	}


	public function testIndex()
	{
		$m = new Map( [4 => 'a', 8 => 'b'] );

		$this->assertSame( 1, $m->index( '8' ) );
	}


	public function testIndexClosure()
	{
		$m = new Map( [4 => 'a', 8 => 'b'] );

		$this->assertSame( 1, $m->index( function( $key ) {
			return $key == '8';
		} ) );
	}


	public function testIndexNotFound()
	{
		$m = new Map( [] );

		$this->assertNull( $m->index( 'b' ) );
	}


	public function testIndexNotFoundClosure()
	{
		$m = new Map( [] );

		$this->assertNull( $m->index( function( $key ) {
			return false;
		} ) );
	}


	public function testInsertAfter()
	{
		$r = Map::from( ['a' => 'foo', 'b' => 'bar'] )->insertAfter( 'foo', 'baz' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['a' => 'foo', 0 => 'baz', 'b' => 'bar'], $r->toArray() );
	}


	public function testInsertAfterArray()
	{
		$r = Map::from( ['foo', 'bar'] )->insertAfter( 'foo', ['baz', 'boo'] );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['foo', 'baz', 'boo', 'bar'], $r->toArray() );
	}


	public function testInsertAfterEnd()
	{
		$r = Map::from( ['foo', 'bar'] )->insertAfter( null, 'baz' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['foo', 'bar', 'baz'], $r->toArray() );
	}


	public function testInsertAt()
	{
		$r = Map::from( ['a' => 'foo', 'b' => 'bar'] )->insertAt( 1, 'baz', 'c' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['a' => 'foo', 'c' => 'baz', 'b' => 'bar'], $r->toArray() );
	}


	public function testInsertAtBegin()
	{
		$r = Map::from( ['a' => 'foo', 'b' => 'bar'] )->insertAt( 0, 'baz' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( [0 => 'baz', 'a' => 'foo', 'b' => 'bar'], $r->toArray() );
	}


	public function testInsertAtEnd()
	{
		$r = Map::from( ['a' => 'foo', 'b' => 'bar'] )->insertAt( 5, 'baz' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['a' => 'foo', 'b' => 'bar', 0 => 'baz'], $r->toArray() );
	}


	public function testInsertAtNegative()
	{
		$r = Map::from( ['a' => 'foo', 'b' => 'bar'] )->insertAt( -1, 'baz' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['a' => 'foo', 0 => 'baz', 'b' => 'bar'], $r->toArray() );
	}

	public function testInsertAtNegativeKey()
	{
		$r = Map::from( ['a' => 'foo', 'b' => 'bar'] )->insertAt( -1, 'baz', 'c' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['a' => 'foo', 'c' => 'baz', 'b' => 'bar'], $r->toArray() );
	}


	public function testInsertBefore()
	{
		$r = Map::from( ['a' => 'foo', 'b' => 'bar'] )->insertBefore( 'bar', 'baz' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['a' => 'foo', 0 => 'baz', 'b' => 'bar'], $r->toArray() );
	}


	public function testInsertBeforeArray()
	{
		$r = Map::from( ['foo', 'bar'] )->insertBefore( 'bar', ['baz', 'boo'] );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['foo', 'baz', 'boo', 'bar'], $r->toArray() );
	}


	public function testInsertBeforeEnd()
	{
		$r = Map::from( ['foo', 'bar'] )->insertBefore( null, 'baz' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['foo', 'bar', 'baz'], $r->toArray() );
	}


	public function testInString()
	{
		$this->assertEquals( true, Map::from( ['abc'] )->inString( 'c' ) );
		$this->assertEquals( true, Map::from( ['abc'] )->inString( 'bc' ) );
		$this->assertEquals( true, Map::from( [12345] )->inString( '23' ) );
		$this->assertEquals( true, Map::from( [123.4] )->inString( 23.4 ) );
		$this->assertEquals( true, Map::from( [12345] )->inString( false ) );
		$this->assertEquals( true, Map::from( [12345] )->inString( true ) );
		$this->assertEquals( true, Map::from( [false] )->inString( false ) );
		$this->assertEquals( true, Map::from( ['abc'] )->inString( '' ) );
		$this->assertEquals( true, Map::from( [''] )->inString( false ) );
		$this->assertEquals( true, Map::from( ['abc'] )->inString( 'BC', false ) );
		$this->assertEquals( true, Map::from( ['abc', 'def'] )->inString( ['de', 'xy'] ) );
		$this->assertEquals( false, Map::from( ['abc', 'def'] )->inString( ['E', 'x'] ) );
		$this->assertEquals( false, Map::from( ['abc', 'def'] )->inString( 'E' ) );
		$this->assertEquals( false, Map::from( [23456] )->inString( true ) );
		$this->assertEquals( false, Map::from( [false] )->inString( 0 ) );
	}


	public function testInt()
	{
		$this->assertEquals( 1, Map::from( ['a' => true] )->int( 'a' ) );
		$this->assertEquals( 1, Map::from( ['a' => '1'] )->int( 'a' ) );
		$this->assertEquals( 1, Map::from( ['a' => 1.1] )->int( 'a' ) );
		$this->assertEquals( 10, Map::from( ['a' => '10'] )->int( 'a' ) );
		$this->assertEquals( 1, Map::from( ['a' => ['b' => ['c' => 1]]] )->int( 'a/b/c' ) );
		$this->assertEquals( 1, Map::from( [] )->int( 'a', 1 ) );

		$this->assertEquals( 0, Map::from( [] )->int( 'b' ) );
		$this->assertEquals( 0, Map::from( ['b' => ''] )->int( 'b' ) );
		$this->assertEquals( 0, Map::from( ['b' => 'abc'] )->int( 'b' ) );
		$this->assertEquals( 0, Map::from( ['b' => null] )->int( 'b' ) );
		$this->assertEquals( 0, Map::from( ['b' => [true]] )->int( 'b' ) );
		$this->assertEquals( 0, Map::from( ['b' => new \stdClass] )->int( 'b' ) );
	}


	public function testIntClosure()
	{
		$this->assertEquals( 1, Map::from( [] )->int( 'c', function() { return rand( 1, 1 ); } ) );
	}


	public function testIntException()
	{
		$this->expectException( \RuntimeException::class );
		Map::from( [] )->int( 'c', new \RuntimeException( 'error' ) );
	}


	public function testIntersect()
	{
		$m = new Map( ['id' => 1, 'first_word' => 'Hello'] );
		$i = new Map( ['first_world' => 'Hello', 'last_word' => 'World'] );
		$r = $m->intersect( $i );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['first_word' => 'Hello'], $r->toArray() );
	}


	public function testIntersectCallback()
	{
		$m = new Map( ['id' => 1, 'first_word' => 'Hello', 'last_word' => 'World'] );
		$i = new Map( ['first_world' => 'Hello', 'last_world' => 'world'] );
		$r = $m->intersect( $i, 'strcasecmp' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['first_word' => 'Hello', 'last_word' => 'World'], $r->toArray() );
	}


	public function testIntersectAssoc()
	{
		$m = new Map( ['id' => 1, 'name' => 'Mateus', 'age' => 18] );
		$i = new Map( ['name' => 'Mateus', 'firstname' => 'Mateus'] );
		$r = $m->intersectAssoc( $i );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['name' => 'Mateus'], $r->toArray() );
	}


	public function testIntersectAssocCallback()
	{
		$m = new Map( ['id' => 1, 'first_word' => 'Hello', 'last_word' => 'World'] );
		$i = new Map( ['first_word' => 'hello', 'Last_word' => 'world'] );
		$r = $m->intersectAssoc( $i, 'strcasecmp' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['first_word' => 'Hello'], $r->toArray() );
	}


	public function testIntersectKeys()
	{
		$m = new Map( ['id' => 1, 'name' => 'Mateus', 'age' => 18] );
		$i = new Map( ['name' => 'Mateus', 'surname' => 'Guimaraes'] );
		$r = $m->intersectKeys( $i );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['name' => 'Mateus'], $r->toArray() );
	}


	public function testIntersectKeysCallback()
	{
		$m = new Map( ['id' => 1, 'first_word' => 'Hello', 'last_word' => 'World'] );
		$i = new Map( ['First_word' => 'Hello', 'last_word' => 'world'] );
		$r = $m->intersectKeys( $i, 'strcasecmp' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['first_word' => 'Hello', 'last_word' => 'World'], $r->toArray() );
	}


	public function testIs()
	{
		$map = new Map( ['foo' => 1, 'bar' => 2] );

		$this->assertTrue( $map->is( ['foo' => 1, 'bar' => 2] ) );
		$this->assertTrue( $map->is( ['bar' => 2, 'foo' => 1] ) );
		$this->assertTrue( $map->is( ['foo' => '1', 'bar' => '2'] ) );
	}


	public function testIsStrict()
	{
		$map = new Map( ['foo' => 1, 'bar' => 2] );

		$this->assertTrue( $map->is( ['foo' => 1, 'bar' => 2], true ) );
		$this->assertFalse( $map->is( ['bar' => 2, 'foo' => 1], true ) );
		$this->assertFalse( $map->is( ['foo' => '1', 'bar' => '2'], true ) );
	}


	public function testIsEmpty()
	{
		$m = new Map;
		$this->assertTrue( $m->isEmpty() );
	}


	public function testIsEmptyFalse()
	{
		$m = new Map( ['foo'] );
		$this->assertFalse( $m->isEmpty() );
	}


	public function testIsNumeric()
	{
		$this->assertTrue( Map::from( [] )->isNumeric() );
		$this->assertTrue( Map::from( [1] )->isNumeric() );
		$this->assertTrue( Map::from( [1.1] )->isNumeric() );
		$this->assertTrue( Map::from( [010] )->isNumeric() );
		$this->assertTrue( Map::from( [0x10] )->isNumeric() );
		$this->assertTrue( Map::from( [0b10] )->isNumeric() );
		$this->assertTrue( Map::from( ['010'] )->isNumeric() );
		$this->assertTrue( Map::from( ['10'] )->isNumeric() );
		$this->assertTrue( Map::from( [' 10'] )->isNumeric() );
		$this->assertTrue( Map::from( ['10.1'] )->isNumeric() );
		$this->assertTrue( Map::from( ['10e2'] )->isNumeric() );

		$this->assertFalse( Map::from( ['0b10'] )->isNumeric() );
		$this->assertFalse( Map::from( ['0x10'] )->isNumeric() );
		$this->assertFalse( Map::from( ['null'] )->isNumeric() );
		$this->assertFalse( Map::from( [null] )->isNumeric() );
		$this->assertFalse( Map::from( [true] )->isNumeric() );
		$this->assertFalse( Map::from( [[]] )->isNumeric() );
		$this->assertFalse( Map::from( [''] )->isNumeric() );
	}


	public function testIsObject()
	{
		$this->assertTrue( Map::from( [] )->isObject() );
		$this->assertTrue( Map::from( [new \stdClass] )->isObject() );

		$this->assertFalse( Map::from( [1] )->isObject() );
	}


	public function testIsScalar()
	{
		$this->assertTrue( Map::from( [] )->isScalar() );
		$this->assertTrue( Map::from( [1] )->isScalar() );
		$this->assertTrue( Map::from( [1.1] )->isScalar() );
		$this->assertTrue( Map::from( ['abc'] )->isScalar() );
		$this->assertTrue( Map::from( [true, false] )->isScalar() );

		$this->assertFalse( Map::from( [new \stdClass] )->isScalar() );
		$this->assertFalse( Map::from( [null] )->isScalar() );
		$this->assertFalse( Map::from( [[1]] )->isScalar() );
	}


	public function testJoin()
	{
		$m = new Map( ['a', 'b', null, false] );
		$this->assertSame( 'ab', $m->join() );
		$this->assertSame( 'a-b--', $m->join( '-' ) );
	}


	public function testJsonSerialize()
	{
		$this->assertSame( '["a","b"]', json_encode( new Map( ['a', 'b'] ) ) );
		$this->assertSame( '{"a":0,"b":1}', json_encode( new Map( ['a' => 0, 'b' => 1] ) ) );
	}


	public function testKeys()
	{
		$m = ( new Map( ['name' => 'test', 'last' => 'user'] ) )->keys();

		$this->assertInstanceOf( Map::class, $m );
		$this->assertSame( ['name', 'last'], $m->toArray() );
	}


	public function testKrsortNummeric()
	{
		$m = ( new Map( [6 => 4, 7 => 3, 9 => 2, 8 => 1, 5 => 0, 4 => -1, 2 => -2, 1 => -3, 3 => -4] ) )->krsort();

		$this->assertInstanceOf( Map::class, $m );
		$this->assertSame( [9 => 2, 8 => 1, 7 => 3, 6 => 4, 5 => 0, 4 => -1, 3 => -4, 2 => -2, 1 => -3], $m->toArray() );
	}


	public function testKrsortStrings()
	{
		$m = ( new Map( ['b' => 'bar-1', 'a' => 'foo', 'c' => 'bar-10'] ) )->krsort();

		$this->assertInstanceOf( Map::class, $m );
		$this->assertSame( ['c' => 'bar-10', 'b' => 'bar-1', 'a' => 'foo'], $m->toArray() );
	}


	public function testKsortNummeric()
	{
		$m = ( new Map( [3 => -4, 1 => -3, 2 => -2, 4 => -1, 5 => 0, 8 => 1, 9 => 2, 7 => 3, 6 => 4] ) )->ksort();

		$this->assertInstanceOf( Map::class, $m );
		$this->assertSame( [1 => -3, 2 => -2, 3 => -4, 4 => -1, 5 => 0, 6 => 4, 7 => 3, 8 => 1, 9 => 2], $m->toArray() );
	}


	public function testKsortStrings()
	{
		$m = ( new Map( ['a' => 'foo', 'c' => 'bar-10', 'b' => 'bar-1'] ) )->ksort();

		$this->assertInstanceOf( Map::class, $m );
		$this->assertSame( ['a' => 'foo', 'b' => 'bar-1', 'c' => 'bar-10'], $m->toArray() );
	}


	public function testLast()
	{
		$m = new Map( ['foo', 'bar'] );
		$this->assertSame( 'bar', $m->last() );
	}


	public function testLastWithDefault()
	{
		$m = new Map;
		$result = $m->last( 'default' );
		$this->assertSame( 'default', $result );
	}


	public function testLastWithException()
	{
		$m = new Map;

		$this->expectException( \RuntimeException::class );
		$result = $m->last( new \RuntimeException( 'error' ) );
	}


	public function testLastWithClosure()
	{
		$m = new Map;
		$result = $m->last( function() { return rand( 10, 11 ); } );

		$this->assertGreaterThanOrEqual( 10, $result );
	}


	public function testLastKey()
	{
		$this->assertSame( 'b', Map::from( ['a' => 1, 'b' => 2] )->lastKey() );
	}


	public function testLastKeyEmpty()
	{
		$this->assertSame( null, Map::from( [] )->lastKey() );
	}


	public function testLtrim()
	{
		$this->assertEquals( ["abc\n", "cde\r\n"], Map::from( [" abc\n", "\tcde\r\n"] )->ltrim()->toArray() );
		$this->assertEquals( [" b c", "xa"], Map::from( ["a b c", "cbxa"] )->ltrim( 'abc' )->toArray() );
	}


	public function testMap()
	{
		$m = new Map( ['first' => 'test', 'last' => 'user'] );
		$m = $m->map( function( $item, $key ) {
			return $key . '-' . strrev( $item );
		} );

		$this->assertInstanceOf( Map::class, $m );
		$this->assertSame( ['first' => 'first-tset', 'last' => 'last-resu'], $m->toArray() );
	}


	public function testMax()
	{
		$this->assertSame( 5, Map::from( [1, 3, 2, 5, 4] )->max() );
		$this->assertSame( 'foo', Map::from( ['bar', 'foo', 'baz'] )->max() );
	}


	public function testMaxEmpty()
	{
		$this->assertNull( Map::from( [] )->max() );
	}


	public function testMaxPath()
	{
		$this->assertSame( 50, Map::from( [['p' => 30], ['p' => 50], ['p' => 10]] )->max( 'p' ) );
		$this->assertSame( 50, Map::from( [['i' => ['p' => 30]], ['i' => ['p' => 50]]] )->max( 'i/p' ) );
	}


	public function testMergeArray()
	{
		$m = new Map( ['name' => 'Hello'] );
		$r = $m->merge( ['id' => 1] );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['name' => 'Hello', 'id' => 1], $r->toArray() );
	}


	public function testMergeMap()
	{
		$m = new Map( ['name' => 'Hello'] );
		$r = $m->merge( new Map( ['name' => 'World', 'id' => 1] ) );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['name' => 'World', 'id' => 1], $r->toArray() );
	}


	public function testMergeRecursive()
	{
		$r = Map::from( ['a' => 1, 'b' => 2] )->merge( ['b' => 4, 'c' => 6], true );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['a' => 1, 'b' => [2, 4], 'c' => 6], $r->toArray() );
	}


	public function testMethod()
	{
		Map::method( 'foo', function() {
			return $this->filter( function( $item ) {
				return strpos( $item, 'a' ) === 0;
			})->unique()->values();
		} );

		$m = new Map( ['a', 'a', 'aa', 'aaa', 'bar'] );

		$this->assertSame( ['a', 'aa', 'aaa'], $m->foo()->toArray() );
	}


	public function testMethodInternal()
	{
		Map::method( 'foo', function() {
			return $this->list;
		} );

		$m = new Map( ['a', 'aa', 'aaa'] );

		$this->assertSame( ['a', 'aa', 'aaa'], $m->foo() );
	}


	public function testMethodNotAvailable()
	{
		$m = new Map( [] );
		$r = $m->bar();

		$this->assertInstanceOf( Map::class, $r );
		$this->assertTrue( $r->isEmpty() );
	}


	public function testMethodStatic()
	{
		Map::method( 'baz', function() {
			return [];
		} );

		$this->assertSame( [], Map::baz() );
	}


	public function testMethodStaticException()
	{
		$this->expectException(\BadMethodCallException::class);
		Map::bar();
	}


	public function testMin()
	{
		$this->assertSame( 1, Map::from( [2, 3, 1, 5, 4] )->min() );
		$this->assertSame( 'bar', Map::from( ['baz', 'foo', 'bar'] )->min() );
	}


	public function testMinEmpty()
	{
		$this->assertNull( Map::from( [] )->min() );
	}


	public function testMinPath()
	{
		$this->assertSame( 10, Map::from( [['p' => 30], ['p' => 50], ['p' => 10]] )->min( 'p' ) );
		$this->assertSame( 30, Map::from( [['i' => ['p' => 30]], ['i' => ['p' => 50]]] )->min( 'i/p' ) );
	}


	public function testNone()
	{
		$this->assertFalse( Map::from( ['a', 'b'] )->none( 'a' ) );
		$this->assertFalse( Map::from( ['a', 'b'] )->none( ['a', 'b'] ) );
		$this->assertFalse( Map::from( ['a', 'b'] )->none( ['a', 'x'] ) );
		$this->assertTrue( Map::from( ['a', 'b'] )->none( 'x' ) );
		$this->assertTrue( Map::from( ['1', '2'] )->none( 2, true ) );
		$this->assertTrue( Map::from( ['a', 'b'] )->none( ['x', 'y'] ) );
	}


	public function testNth()
	{
		$m = Map::from( ['a', 'b', 'c', 'd', 'e', 'f'] );

		$this->assertSame( [0 => 'a', 2 => 'c', 4 => 'e'], $m->nth( 2 )->toArray() );
		$this->assertSame( [1 => 'b', 3 => 'd', 5 => 'f'], $m->nth( 2, 1 )->toArray() );
	}


	public function testOffsetAccess()
	{
		$m = new Map( ['name' => 'test'] );
		$this->assertSame( 'test', $m['name'] );

		$m['name'] = 'foo';
		$this->assertSame( 'foo', $m['name'] );
		$this->assertTrue( isset( $m['name'] ) );

		unset( $m['name'] );
		$this->assertFalse( isset( $m['name'] ) );

		$m[] = 'bar';
		$this->assertSame( 'bar', $m[0] );
	}


	public function testOffsetExists()
	{
		$m = new Map( ['foo', 'bar', 'baz' => null] );

		$this->assertTrue( $m->offsetExists( 0 ) );
		$this->assertTrue( $m->offsetExists( 1 ) );
		$this->assertFalse( $m->offsetExists( 1000 ) );
		$this->assertFalse( $m->offsetExists( 'baz' ) );
	}


	public function testOffsetGet()
	{
		$m = new Map( ['foo', 'bar'] );

		$this->assertSame( 'foo', $m->offsetGet( 0 ) );
		$this->assertSame( 'bar', $m->offsetGet( 1 ) );
	}


	public function testOffsetSet()
	{
		$m = new Map( ['foo', 'foo'] );
		$m->offsetSet( 1, 'bar' );

		$this->assertSame( 'bar', $m[1] );
	}


	public function testOffsetSetAppend()
	{
		$m = new Map( ['foo', 'foo'] );
		$m->offsetSet( null, 'qux' );

		$this->assertSame( 'qux', $m[2] );
	}


	public function testOffsetUnset()
	{
		$m = new Map( ['foo', 'bar'] );

		$m->offsetUnset( 1 );
		$this->assertFalse( isset( $m[1] ) );
	}


	public function testOnly()
	{
		$this->assertSame( ['a' => 1], Map::from( ['a' => 1, 0 => 'b'] )->only( 'a' )->toArray() );
		$this->assertSame( [0 => 'b', 1 => 'c'], Map::from( ['a' => 1, 0 => 'b', 1 => 'c'] )->only( [0, 1] )->toArray() );
	}


	public function testOrder()
	{
		$m = Map::from( ['a' => 1, 1 => 'c', 0 => 'b'] );

		$this->assertSame( [0 => 'b', 1 => 'c', 'a' => 1], $m->order( [0, 1, 'a'] )->toArray() );
		$this->assertSame( [0 => 'b', 1 => 'c', 2 => null], $m->order( [0, 1, 2] )->toArray() );
		$this->assertSame( [0 => 'b', 1 => 'c'], $m->order( [0, 1] )->toArray() );
	}


	public function testPad()
	{
		$this->assertSame( [1, 2, 3, null, null], Map::from( [1, 2, 3] )->pad( 5 )->toArray() );
		$this->assertSame( [null, null, 1, 2, 3], Map::from( [1, 2, 3] )->pad( -5 )->toArray() );

		$this->assertSame( [1, 2, 3, '0', '0'], Map::from( [1, 2, 3] )->pad( 5, '0' )->toArray() );
		$this->assertSame( [1, 2, 3], Map::from( [1, 2, 3] )->pad( 2 )->toArray() );

		$this->assertSame( [0 => 1, 1 => 2, 2 => null], Map::from( [10 => 1, 20 => 2] )->pad( 3 )->toArray() );
		$this->assertSame( ['a' => 1, 'b' => 2, 0 => 3], Map::from( ['a' => 1, 'b' => 2] )->pad( 3, 3 )->toArray() );
	}


	public function testPartition()
	{
		$expected = [[0 => 1, 1 => 2], [2 => 3, 3 => 4], [4 => 5]];

		$this->assertSame( $expected, Map::from( [1, 2, 3, 4, 5] )->partition( 3 )->toArray() );
	}


	public function testPartitionClosure()
	{
		$expected = [[0 => 1, 3 => 4], [1 => 2, 4 => 5], [2 => 3]];

		$this->assertSame( $expected, Map::from( [1, 2, 3, 4, 5] )->partition( function( $val, $idx ) {
			return $idx % 3;
		} )->toArray() );
	}


	public function testPartitionEmpty()
	{
		$this->assertSame( [], Map::from( [] )->partition( 2 )->toArray() );
	}


	public function testPartitionInvalid()
	{
		$this->expectException( \InvalidArgumentException::class );
		Map::from( [1] )->partition( [] );
	}


	public function testPipe()
	{
		$map = new Map( [1, 2, 3] );

		$this->assertSame( 3, $map->pipe( function( $map ) {
			return $map->last();
		} ) );
	}


	public function testPluck()
	{
		$map = new Map( [['foo' => 'one', 'bar' => 'two']] );
		$r = $map->pluck( 'bar' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( [0 => 'two'], $r->toArray() );
	}


	public function testPop()
	{
		$m = new Map( ['foo', 'bar'] );

		$this->assertSame( 'bar', $m->pop() );
		$this->assertSame( ['foo'], $m->toArray() );
	}


	public function testPos()
	{
		$m = new Map( [4 => 'a', 8 => 'b'] );

		$this->assertSame( 1, $m->pos( 'b' ) );
	}


	public function testPosClosure()
	{
		$m = new Map( [4 => 'a', 8 => 'b'] );

		$this->assertSame( 1, $m->pos( function( $item, $key ) {
			return $item === 'b';
		} ) );
	}


	public function testPosNotFound()
	{
		$m = new Map( [] );

		$this->assertNull( $m->pos( 'b' ) );
	}


	public function testPrefix()
	{
		$fcn = function( $item, $key ) {
			return ( ord( $item ) + ord( $key ) ) . '-';
		};

		$this->assertSame( ['1-a', '1-b'], Map::from( ['a', 'b'] )->prefix( '1-' )->toArray() );
		$this->assertSame( ['1-a', ['1-b']], Map::from( ['a', ['b']] )->prefix( '1-' )->toArray() );
		$this->assertSame( ['1-a', ['b']], Map::from( ['a', ['b']] )->prefix( '1-', 1 )->toArray() );
		$this->assertSame( ['145-a', '147-b'], Map::from( ['a', 'b'] )->prefix( $fcn )->toArray() );
	}


	public function testPrepend()
	{
		$m = ( new Map( ['one', 'two', 'three', 'four'] ) )->prepend( 'zero' );
		$this->assertInstanceOf( Map::class, $m );
		$this->assertSame( ['zero', 'one', 'two', 'three', 'four'], $m->toArray() );
	}


	public function testPull()
	{
		$m = new Map( ['foo', 'bar'] );

		$this->assertSame( 'foo', $m->pull( 0 ) );
		$this->assertSame( [1 => 'bar'], $m->toArray() );
	}


	public function testPullDefault()
	{
		$m = new Map( [] );
		$value = $m->pull( 0, 'foo' );
		$this->assertSame( 'foo', $value );
	}


	public function testPullWithException()
	{
		$m = new Map;

		$this->expectException( \RuntimeException::class );
		$result = $m->pull( 'Y', new \RuntimeException( 'error' ) );
	}


	public function testPullWithClosure()
	{
		$m = new Map;
		$result = $m->pull( 1, function() { return rand( 10, 11 ); } );

		$this->assertGreaterThanOrEqual( 10, $result );
	}


	public function testPush()
	{
		$m = ( new Map( [] ) )->push( 'foo' );

		$this->assertInstanceOf( Map::class, $m );
		$this->assertSame( ['foo'], $m->toArray() );
	}


	public function testPut()
	{
		$r = Map::from( [] )->put( 'foo', 1 );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['foo' => 1], $r->toArray() );
	}


	public function testRandom()
	{
		$m = new Map( ['a' => 1, 'b' => 2, 'c' => 3] );
		$r = $m->random();

		$this->assertCount( 1, $r );
		$this->assertCount( 1, $r->intersectAssoc( $m ) );
	}


	public function testRandomEmpty()
	{
		$m = new Map();
		$this->assertCount( 0, $m->random() );
	}


	public function testRandomException()
	{
		$this->expectException( \InvalidArgumentException::class );
		( new Map() )->random( 0 );
	}


	public function testRandomMax()
	{
		$m = new Map( ['a' => 1, 'b' => 2, 'c' => 3] );
		$this->assertCount( 3, $m->random( 4 )->intersectAssoc( $m ) );
	}


	public function testRandomMultiple()
	{
		$m = new Map( ['a' => 1, 'b' => 2, 'c' => 3] );
		$this->assertCount( 2, $m->random( 2 )->intersectAssoc( $m ) );
	}


	public function testReduce()
	{
		$m = new Map( [1, 2, 3] );
		$this->assertSame( 6, $m->reduce( function( $carry, $element ) {
			return $carry += $element;
		} ) );
	}


	public function testReject()
	{
		$m = new Map( [2 => 'a', 6 => null, 13 => 'm'] );

		$this->assertSame( [6 => null], $m->reject()->toArray() );
	}


	public function testRejectCallback()
	{
		$m = new Map( [2 => 'a', 6 => 'b', 13 => 'm', 30 => 'z'] );

		$this->assertSame( [13 => 'm', 30 => 'z'], $m->reject( function( $value ) {
			return $value < 'm';
		} )->toArray() );
	}


	public function testRejectValue()
	{
		$m = new Map( [2 => 'a', 13 => 'm', 30 => 'z'] );
		$r = $m->reject( 'm' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( [2 => 'a', 30 => 'z'], $r->toArray() );
	}


	public function testRekey()
	{
		$m = new Map( ['a' => 2, 'b' => 4] );
		$m = $m->rekey( function( $item, $key ) {
			return 'key-' . $key;
		} );

		$this->assertInstanceOf( Map::class, $m );
		$this->assertSame( ['key-a' => 2, 'key-b' => 4], $m->toArray() );
	}


	public function testRemoveNumeric()
	{
		$m = new Map( ['foo', 'bar'] );
		$r = $m->remove( 0 );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertFalse( isset( $m['foo'] ) );
	}


	public function testRemoveNumericMultiple()
	{
		$m = new Map( ['foo', 'bar', 'baz'] );
		$r = $m->remove( [0, 2] );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertFalse( isset( $m[0] ) );
		$this->assertFalse( isset( $m[2] ) );
		$this->assertTrue( isset( $m[1] ) );
	}


	public function testRemoveString()
	{
		$m = new Map( ['foo' => 'bar', 'baz' => 'qux'] );
		$r = $m->remove( 'foo' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertFalse( isset( $m['foo'] ) );
	}


	public function testRemoveStringMultiple()
	{
		$m = new Map( ['name' => 'test', 'foo' => 'bar', 'baz' => 'qux'] );
		$r = $m->remove( ['foo', 'baz'] );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertFalse( isset( $m['foo'] ) );
		$this->assertFalse( isset( $m['baz'] ) );
		$this->assertTrue( isset( $m['name'] ) );
	}


	public function testReplaceArray()
	{
		$m = new Map( ['a', 'b', 'c'] );
		$r = $m->replace( [1 => 'd', 2 => 'e'] );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['a', 'd', 'e'], $r->toArray() );
	}


	public function testReplaceMap()
	{
		$m = new Map( ['a', 'b', 'c'] );
		$r = $m->replace( new Map( [1 => 'd', 2 => 'e'] ) );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['a', 'd', 'e'], $r->toArray() );
	}


	public function testReplaceNonRecursive()
	{
		$m = new Map( ['a', 'b', ['c']] );
		$r = $m->replace( [1 => 'd', 2 => [1 => 'f']], false );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['a', 'd', [1 => 'f']], $r->toArray() );
	}


	public function testReplaceRecursiveArray()
	{
		$m = new Map( ['a', 'b', ['c', 'd']] );
		$r = $m->replace( ['z', 2 => [1 => 'e']] );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['z', 'b', ['c', 'e']], $r->toArray() );
	}


	public function testReplaceRecursiveMap()
	{
		$m = new Map( ['a', 'b', ['c', 'd']] );
		$r = $m->replace( new Map( ['z', 2 => [1 => 'e']] ) );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['z', 'b', ['c', 'e']], $r->toArray() );
	}


	public function testReverse()
	{
		$m = new Map( ['hello', 'world'] );
		$reversed = $m->reverse();

		$this->assertInstanceOf( Map::class, $reversed );
		$this->assertSame( [1 => 'world', 0 => 'hello'], $reversed->toArray() );
	}


	public function testReverseKeys()
	{
		$m = new Map( ['name' => 'test', 'last' => 'user'] );
		$reversed = $m->reverse();

		$this->assertInstanceOf( Map::class, $reversed );
		$this->assertSame( ['last' => 'user', 'name' => 'test'], $reversed->toArray() );
	}


	public function testRsortNummeric()
	{
		$m = ( new Map( [-1, -3, -2, -4, -5, 0, 5, 3, 1, 2, 4] ) )->rsort();

		$this->assertInstanceOf( Map::class, $m );
		$this->assertSame( [5, 4, 3, 2, 1, 0, -1, -2, -3, -4, -5], $m->toArray() );
	}


	public function testRsortStrings()
	{
		$m = ( new Map( ['bar-10', 'foo', 'bar-1'] ) )->rsort();

		$this->assertInstanceOf( Map::class, $m );
		$this->assertSame( ['foo', 'bar-10', 'bar-1'], $m->toArray() );
	}


	public function testRtrim()
	{
		$this->assertEquals( [" abc", "\tcde"], Map::from( [" abc\n", "\tcde\r\n"] )->rtrim()->toArray() );
		$this->assertEquals( ["a b ", "cbx"], Map::from( ["a b c", "cbxa"] )->rtrim( 'abc' )->toArray() );
	}


	public function testSearch()
	{
		$m = new Map( [false, 0, 1, [], ''] );

		$this->assertNull( $m->search( 'false' ) );
		$this->assertNull( $m->search( '1' ) );
		$this->assertSame( 0, $m->search( false ) );
		$this->assertSame( 1, $m->search( 0 ) );
		$this->assertSame( 2, $m->search( 1 ) );
		$this->assertSame( 3, $m->search( [] ) );
		$this->assertSame( 4, $m->search( '' ) );
	}


	public function testSep()
	{
		$this->assertSame( 'baz', Map::from( ['foo' => ['bar' => 'baz']] )->sep( '/' )->get( 'foo/bar' ) );
	}


	public function testSet()
	{
		$map = Map::from( [] );
		$r = $map->set( 'foo', 1 );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertSame( ['foo' => 1], $map->toArray() );
	}


	public function testSetNested()
	{
		$map = Map::from( ['foo' => 1] );
		$r = $map->set( 'bar', ['nested' => 'two'] );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertSame( ['foo' => 1, 'bar' => ['nested' => 'two']], $map->toArray() );
	}


	public function testSetOverwrite()
	{
		$map = Map::from( ['foo' => 3] );
		$r = $map->set( 'foo', 3 );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertSame( ['foo' => 3], $map->toArray() );
	}


	public function testShift()
	{
		$m = new Map( ['foo', 'bar'] );

		$this->assertSame( 'foo', $m->shift() );
		$this->assertSame( 'bar', $m->first() );
		$this->assertSame( 1, $m->count() );
	}


	public function testShuffle()
	{
		$map = new Map( range( 0, 100, 10 ) );

		$firstRandom = $map->copy()->shuffle();
		$secondRandom = $map->copy()->shuffle();

		$this->assertInstanceOf( Map::class, $firstRandom );
		$this->assertInstanceOf( Map::class, $secondRandom );
		$this->assertNotEquals( $firstRandom->toArray(), $secondRandom->toArray() );
	}


	public function testShuffleAssoc()
	{
		$map = new Map( range( 0, 100, 10 ) );

		$result = $map->copy()->shuffle( true );

		$this->assertInstanceOf( Map::class, $result );
		$this->assertFalse( $map->is( $result, true ) );

		foreach( $map as $key => $value ) {
			$this->assertSame( $value, $result[$key] );
		}
	}


	public function testSkip()
	{
		$this->assertSame( [2 => 3, 3 => 4], Map::from( [1, 2, 3, 4] )->skip( 2 )->toArray() );
	}


	public function testSkipFunction()
	{
		$fcn = function( $item, $key ) {
			return $item < 4;
		};

		$this->assertSame( [3 => 4], Map::from( [1, 2, 3, 4] )->skip( $fcn )->toArray() );
	}


	public function testSkipException()
	{
		$this->expectException( \InvalidArgumentException::class );
		Map::from( [] )->skip( [] );
	}


	public function testSliceOffset()
	{
		$map = ( new Map( [1, 2, 3, 4, 5, 6, 7, 8] ) )->slice( 3 );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertSame( [4, 5, 6, 7, 8], $map->values()->toArray() );
	}


	public function testSliceNegativeOffset()
	{
		$map = ( new Map( [1, 2, 3, 4, 5, 6, 7, 8] ) )->slice( -3 );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertSame( [6, 7, 8], $map->values()->toArray() );
	}


	public function testSliceOffsetAndLength()
	{
		$map = ( new Map( [1, 2, 3, 4, 5, 6, 7, 8] ) )->slice( 3, 3 );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertSame( [4, 5, 6], $map->values()->toArray() );
	}


	public function testSliceOffsetAndNegativeLength()
	{
		$map = ( new Map( [1, 2, 3, 4, 5, 6, 7, 8] ) )->slice( 3, -1 );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertSame( [4, 5, 6, 7], $map->values()->toArray() );
	}


	public function testSliceNegativeOffsetAndLength()
	{
		$map = ( new Map( [1, 2, 3, 4, 5, 6, 7, 8] ) )->slice( -5, 3 );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertSame( [4, 5, 6], $map->values()->toArray() );
	}


	public function testSliceNegativeOffsetAndNegativeLength()
	{
		$map = ( new Map( [1, 2, 3, 4, 5, 6, 7, 8] ) )->slice( -6, -2 );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertSame( [3, 4, 5, 6], $map->values()->toArray() );
	}


	public function testSome()
	{
		$this->assertTrue( Map::from( ['a', 'b'] )->some( 'a' ) );
		$this->assertFalse( Map::from( ['a', 'b'] )->some( 'c' ) );
	}


	public function testSomeStrict()
	{
		$this->assertTrue( Map::from( ['1', '2'] )->some( '2', true ) );
		$this->assertFalse( Map::from( ['1', '2'] )->some( 2, true ) );
	}


	public function testSomeList()
	{
		$this->assertTrue( Map::from( ['a', 'b'] )->some( ['a', 'c'] ) );
		$this->assertFalse( Map::from( ['a', 'b'] )->some( ['c', 'd'] ) );
	}


	public function testSomeListStrict()
	{
		$this->assertTrue( Map::from( ['1', '2'] )->some( ['2'], true ) );
		$this->assertFalse( Map::from( ['1', '2'] )->some( [2], true ) );
	}


	public function testSomeCallback()
	{
		$fcn = function( $item, $key ) {
			return $item === 'a';
		};

		$this->assertTrue( Map::from( ['a', 'b'] )->some( $fcn ) );
		$this->assertFalse( Map::from( ['c', 'd'] )->some( $fcn ) );
	}


	public function testSortNummeric()
	{
		$m = ( new Map( [-1, -3, -2, -4, -5, 0, 5, 3, 1, 2, 4] ) )->sort();

		$this->assertInstanceOf( Map::class, $m );
		$this->assertSame( [-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], $m->toArray() );
	}


	public function testSortStrings()
	{
		$m = ( new Map( ['foo', 'bar-10', 'bar-1'] ) )->sort();

		$this->assertInstanceOf( Map::class, $m );
		$this->assertSame( ['bar-1', 'bar-10', 'foo'], $m->toArray() );
	}


	public function testSplice()
	{
		$m = new Map( ['foo', 'baz'] );
		$r = $m->splice( 1 );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['foo'], $m->toArray() );
	}


	public function testSpliceReplace()
	{
		$m = new Map( ['foo', 'baz'] );
		$r = $m->splice( 1, 0, 'bar' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['foo', 'bar', 'baz'], $m->toArray() );
	}


	public function testSpliceRemove()
	{
		$m = new Map( ['foo', 'baz'] );
		$r = $m->splice( 1, 1 );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['foo'], $m->toArray() );
	}


	public function testSpliceCut()
	{
		$m = new Map( ['foo', 'baz'] );
		$r = $m->splice( 1, 1, 'bar' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['foo', 'bar'], $m->toArray() );
		$this->assertSame( ['baz'], $r->toArray() );
	}


	public function testSpliceAll()
	{
		$m = new Map( ['foo', 'baz'] );
		$r = $m->splice( 1, null, ['bar'] );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['foo', 'bar'], $m->toArray() );
	}


	public function testStrAfter()
	{
		$this->assertEquals( ['1', '1', '1'], Map::from( [1, 1.0, true, ['x'], new \stdClass] )->strAfter( '' )->all() );
		$this->assertEquals( ['0', '0'], Map::from( [0, 0.0, false, []] )->strAfter( '' )->all() );
		$this->assertEquals( ['üß'], Map::from( ['äöüß'] )->strAfter( 'ö' )->all() );
		$this->assertEquals( ['abc'], Map::from( ['abc'] )->strAfter( '' )->all() );
		$this->assertEquals( ['c'], Map::from( ['abc'] )->strAfter( 'b' )->all() );
		$this->assertEquals( [''], Map::from( ['abc'] )->strAfter( 'c' )->all() );
		$this->assertEquals( [], Map::from( ['abc'] )->strAfter( 'x' )->all() );
		$this->assertEquals( [], Map::from( [''] )->strAfter( '' )->all() );
	}


	public function testStrBefore()
	{
		$this->assertEquals( ['1', '1', '1'], Map::from( [1, 1.0, true, ['x'], new \stdClass] )->strBefore( '' )->all() );
		$this->assertEquals( ['0', '0'], Map::from( [0, 0.0, false, []] )->strBefore( '' )->all() );
		$this->assertEquals( ['äö'], Map::from( ['äöüß'] )->strBefore( 'ü' )->all() );
		$this->assertEquals( ['abc'], Map::from( ['abc'] )->strBefore( '' )->all() );
		$this->assertEquals( ['a'], Map::from( ['abc'] )->strBefore( 'b' )->all() );
		$this->assertEquals( [''], Map::from( ['abc'] )->strBefore( 'a' )->all() );
		$this->assertEquals( [], Map::from( ['abc'] )->strBefore( 'x' )->all() );
		$this->assertEquals( [], Map::from( [''] )->strBefore( '' )->all() );
	}


	public function testStrContains()
	{
		$this->assertTrue( Map::from( ['abc'] )->strContains( '' ) );
		$this->assertTrue( Map::from( ['abc'] )->strContains( 'a' ) );
		$this->assertTrue( Map::from( ['abc'] )->strContains( 'b' ) );
		$this->assertTrue( Map::from( ['abc'] )->strContains( ['b', 'd'] ) );
		$this->assertTrue( Map::from( [12345] )->strContains( '23' ) );
		$this->assertTrue( Map::from( [123.4] )->strContains( 23.4 ) );
		$this->assertTrue( Map::from( [12345] )->strContains( false ) );
		$this->assertTrue( Map::from( [12345] )->strContains( true ) );
		$this->assertTrue( Map::from( [false] )->strContains( false ) );
		$this->assertTrue( Map::from( [''] )->strContains( false ) );
		$this->assertTrue( Map::from( ['abc'] )->strContains( 'c', 'ASCII' ) );

		$this->assertFalse( Map::from( ['abc'] )->strContains( 'd' ) );
		$this->assertFalse( Map::from( ['abc'] )->strContains( 'cb' ) );
		$this->assertFalse( Map::from( [23456] )->strContains( true ) );
		$this->assertFalse( Map::from( [false] )->strContains( 0 ) );
		$this->assertFalse( Map::from( ['abc'] )->strContains( ['d', 'e'] ) );
		$this->assertFalse( Map::from( ['abc'] )->strContains( 'cb', 'ASCII' ) );
	}


	public function testStrContainsAll()
	{
		$this->assertTrue( Map::from( ['abc', 'def'] )->strContainsAll( '' ) );
		$this->assertTrue( Map::from( ['abc', 'cba'] )->strContainsAll( 'a' ) );
		$this->assertTrue( Map::from( ['abc', 'bca'] )->strContainsAll( 'bc' ) );
		$this->assertTrue( Map::from( [12345, '230'] )->strContainsAll( '23' ) );
		$this->assertTrue( Map::from( [123.4, 23.42] )->strContainsAll( 23.4 ) );
		$this->assertTrue( Map::from( [12345, '234'] )->strContainsAll( [true, false] ) );
		$this->assertTrue( Map::from( ['', false] )->strContainsAll( false ) );
		$this->assertTrue( Map::from( ['abc', 'def'] )->strContainsAll( ['b', 'd'] ) );
		$this->assertTrue( Map::from( ['abc', 'ecf'] )->strContainsAll( 'c', 'ASCII' ) );

		$this->assertFalse( Map::from( ['abc', 'def'] )->strContainsAll( 'd' ) );
		$this->assertFalse( Map::from( ['abc', 'cab'] )->strContainsAll( 'cb' ) );
		$this->assertFalse( Map::from( [23456, '123'] )->strContainsAll( true ) );
		$this->assertFalse( Map::from( [false, '000'] )->strContainsAll( 0 ) );
		$this->assertFalse( Map::from( ['abc', 'acf'] )->strContainsAll( ['d', 'e'] ) );
		$this->assertFalse( Map::from( ['abc', 'bca'] )->strContainsAll( 'cb', 'ASCII' ) );
	}


	public function testStrEnds()
	{
		$this->assertTrue( Map::from( ['abc'] )->strEnds( '' ) );
		$this->assertTrue( Map::from( ['abc'] )->strEnds( 'c' ) );
		$this->assertTrue( Map::from( ['abc'] )->strEnds( 'bc' ) );
		$this->assertTrue( Map::from( ['abc'] )->strEnds( ['b', 'c'] ) );
		$this->assertTrue( Map::from( ['abc'] )->strEnds( 'c', 'ASCII' ) );
		$this->assertFalse( Map::from( ['abc'] )->strEnds( 'a' ) );
		$this->assertFalse( Map::from( ['abc'] )->strEnds( 'cb' ) );
		$this->assertFalse( Map::from( ['abc'] )->strEnds( ['d', 'b'] ) );
		$this->assertFalse( Map::from( ['abc'] )->strEnds( 'cb', 'ASCII' ) );
	}


	public function testStrEndsAll()
	{
		$this->assertTrue( Map::from( ['abc', 'def'] )->strEndsAll( '' ) );
		$this->assertTrue( Map::from( ['abc', 'bac'] )->strEndsAll( 'c' ) );
		$this->assertTrue( Map::from( ['abc', 'cbc'] )->strEndsAll( 'bc' ) );
		$this->assertTrue( Map::from( ['abc', 'def'] )->strEndsAll( ['c', 'f'] ) );
		$this->assertTrue( Map::from( ['abc', 'efc'] )->strEndsAll( 'c', 'ASCII' ) );
		$this->assertFalse( Map::from( ['abc', 'fed'] )->strEndsAll( 'd' ) );
		$this->assertFalse( Map::from( ['abc', 'bca'] )->strEndsAll( 'ca' ) );
		$this->assertFalse( Map::from( ['abc', 'acf'] )->strEndsAll( ['a', 'c'] ) );
		$this->assertFalse( Map::from( ['abc', 'bca'] )->strEndsAll( 'ca', 'ASCII' ) );
	}


	public function testStrLower()
	{
		$this->assertEquals( ["my string"], Map::from( ['My String'] )->strLower()->all() );
		$this->assertEquals( ["τάχιστη"], Map::from( ['Τάχιστη'] )->strLower()->all() );

		$list = [mb_convert_encoding( 'ÄPFEL', 'ISO-8859-1' ), 'BIRNEN'];
		$expected = [mb_convert_encoding( 'äpfel', 'ISO-8859-1' ), "birnen"];
		$this->assertEquals( $expected, Map::from( $list )->strLower( 'ISO-8859-1' )->all() );

		$this->assertEquals( [123], Map::from( [123] )->strLower()->all() );
		$this->assertEquals( [new \stdClass], Map::from( [new \stdClass] )->strLower()->all() );
	}


	public function testString()
	{
		$this->assertSame( '1', Map::from( ['a' => true] )->string( 'a' ) );
		$this->assertSame( '1', Map::from( ['a' => 1] )->string( 'a' ) );
		$this->assertSame( '1.1', Map::from( ['a' => 1.1] )->string( 'a' ) );
		$this->assertSame( 'abc', Map::from( ['a' => 'abc'] )->string( 'a' ) );
		$this->assertSame( 'yes', Map::from( ['a' => ['b' => ['c' => 'yes']]] )->string( 'a/b/c' ) );
		$this->assertSame( 'no', Map::from( [] )->string( 'a', 'no' ) );

		$this->assertSame( '', Map::from( [] )->string( 'b' ) );
		$this->assertSame( '', Map::from( ['b' => ''] )->string( 'b' ) );
		$this->assertSame( '', Map::from( ['b' => null] )->string( 'b' ) );
		$this->assertSame( '', Map::from( ['b' => [true]] )->string( 'b' ) );
		$this->assertSame( '', Map::from( ['b' => new \stdClass] )->string( 'b' ) );
	}


	public function testStrUpper()
	{
		$this->assertEquals( ["MY STRING"], Map::from( ['My String'] )->strUpper()->all() );
		$this->assertEquals( ["ΤΆΧΙΣΤΗ"], Map::from( ['τάχιστη'] )->strUpper()->all() );

		$list = [mb_convert_encoding( 'äpfel', 'ISO-8859-1' ), 'birnen'];
		$expected = [mb_convert_encoding( 'ÄPFEL', 'ISO-8859-1' ), "BIRNEN"];
		$this->assertEquals( $expected, Map::from( $list )->strUpper( 'ISO-8859-1' )->all() );

		$this->assertEquals( [123], Map::from( [123] )->strUpper()->all() );
		$this->assertEquals( [new \stdClass], Map::from( [new \stdClass] )->strUpper()->all() );
	}


	public function testStringClosure()
	{
		$this->assertSame( 'no', Map::from( [] )->string( 'c', function() { return 'no'; } ) );
	}


	public function testStringException()
	{
		$this->expectException( \RuntimeException::class );
		Map::from( [] )->string( 'c', new \RuntimeException( 'error' ) );
	}


	public function testStringReplace()
	{
		$this->assertEquals( ['google.de', 'aimeos.de'], Map::from( ['google.com', 'aimeos.com'] )->strReplace( '.com', '.de' )->all() );
		$this->assertEquals( ['google.de', 'aimeos.de'], Map::from( ['google.com', 'aimeos.org'] )->strReplace( ['.com', '.org'], '.de' )->all() );
		$this->assertEquals( ['google.de', 'aimeos'], Map::from( ['google.com', 'aimeos.org'] )->strReplace( ['.com', '.org'], ['.de'] )->all() );
		$this->assertEquals( ['google.fr', 'aimeos.de'], Map::from( ['google.com', 'aimeos.org'] )->strReplace( ['.com', '.org'], ['.fr', '.de'] )->all() );
		$this->assertEquals( ['google.de', 'aimeos.de'], Map::from( ['google.com', 'aimeos.com'] )->strReplace( ['.com', '.co'], ['.co', '.de', '.fr'] )->all() );
		$this->assertEquals( ['google.de', 'aimeos.de', 123], Map::from( ['google.com', 'aimeos.com', 123] )->strReplace( '.com', '.de' )->all() );
		$this->assertEquals( ['GOOGLE.de', 'AIMEOS.de'], Map::from( ['GOOGLE.COM', 'AIMEOS.COM'] )->strReplace( '.com', '.de', true )->all() );
   }


	public function testStrStarts()
	{
		$this->assertTrue( Map::from( ['abc'] )->strStarts( '' ) );
		$this->assertTrue( Map::from( ['abc'] )->strStarts( 'a' ) );
		$this->assertTrue( Map::from( ['abc'] )->strStarts( 'ab' ) );
		$this->assertTrue( Map::from( ['abc'] )->strStarts( ['a', 'b'] ) );
		$this->assertTrue( Map::from( ['abc'] )->strStarts( 'ab', 'ASCII' ) );
		$this->assertFalse( Map::from( ['abc'] )->strStarts( 'b' ) );
		$this->assertFalse( Map::from( ['abc'] )->strStarts( 'bc' ) );
		$this->assertFalse( Map::from( ['abc'] )->strStarts( ['b', 'c'] ) );
		$this->assertFalse( Map::from( ['abc'] )->strStarts( 'bc', 'ASCII' ) );
	}


	public function testStrStartsAll()
	{
		$this->assertTrue( Map::from( ['abc', 'def'] )->strStartsAll( '' ) );
		$this->assertTrue( Map::from( ['abc', 'acb'] )->strStartsAll( 'a' ) );
		$this->assertTrue( Map::from( ['abc', 'aba'] )->strStartsAll( 'ab' ) );
		$this->assertTrue( Map::from( ['abc', 'def'] )->strStartsAll( ['a', 'd'] ) );
		$this->assertTrue( Map::from( ['abc', 'acf'] )->strStartsAll( 'a', 'ASCII' ) );
		$this->assertFalse( Map::from( ['abc', 'def'] )->strStartsAll( 'd' ) );
		$this->assertFalse( Map::from( ['abc', 'bca'] )->strStartsAll( 'ab' ) );
		$this->assertFalse( Map::from( ['abc', 'bac'] )->strStartsAll( ['a', 'c'] ) );
		$this->assertFalse( Map::from( ['abc', 'cab'] )->strStartsAll( 'ab', 'ASCII' ) );
	}


	public function testSuffix()
	{
		$fcn = function( $item, $key ) {
			return '-' . ( ord( $item ) + ord( $key ) );
		};

		$this->assertSame( ['a-1', 'b-1'], Map::from( ['a', 'b'] )->suffix( '-1' )->toArray() );
		$this->assertSame( ['a-1', ['b-1']], Map::from( ['a', ['b']] )->suffix( '-1' )->toArray() );
		$this->assertSame( ['a-1', ['b']], Map::from( ['a', ['b']] )->suffix( '-1', 1 )->toArray() );
		$this->assertSame( ['a-145', 'b-147'], Map::from( ['a', 'b'] )->suffix( $fcn )->toArray() );
	}


	public function testSum()
	{
		$this->assertSame( 9.0, Map::from( [1, 3, 5] )->sum() );
		$this->assertSame( 6.0, Map::from( [1, 'sum', 5] )->sum() );
	}


	public function testSumPath()
	{
		$this->assertSame( 90.0, Map::from( [['p' => 30], ['p' => 50], ['p' => 10]] )->sum( 'p' ) );
		$this->assertSame( 80.0, Map::from( [['i' => ['p' => 30]], ['i' => ['p' => 50]]] )->sum( 'i/p' ) );
	}


	public function testTake()
	{
		$this->assertSame( [1, 2], Map::from( [1, 2, 3, 4] )->take( 2 )->toArray() );
	}


	public function testTakeOffset()
	{
		$this->assertSame( [1 => 2, 2 => 3], Map::from( [1, 2, 3, 4] )->take( 2, 1 )->toArray() );
	}


	public function testTakeNegativeOffset()
	{
		$this->assertSame( [2 => 3, 3 => 4], Map::from( [1, 2, 3, 4] )->take( 2, -2 )->toArray() );
	}


	public function testTakeFunction()
	{
		$fcn = function( $item, $key ) {
			return $item < 2;
		};

		$this->assertSame( [1 => 2, 2 => 3], Map::from( [1, 2, 3, 4] )->take( 2, $fcn )->toArray() );
	}


	public function testTakeException()
	{
		$this->expectException( \InvalidArgumentException::class );
		Map::from( [] )->take( 0, [] );
	}


	public function testTap()
	{
		$map = new Map( [1, 2, 3] );

		$this->assertSame( 3, $map->tap( function( $map ) {
			return $map->clear();
		} )->count() );
	}


	public function testTimes()
	{
		$this->assertSame( [0 => 0, 1 => 10, 2 => 20], Map::times( 3, function( $num ) {
			return $num * 10;
		} )->toArray() );
	}


	public function testTimesKeys()
	{
		$this->assertSame( [0 => 0, 2 => 5, 4 => 10], Map::times( 3, function( $num, &$key ) {
			$key = $num * 2;
			return $num * 5;
	   } )->toArray() );
	}


	public function testTimesObjects()
	{
		$this->assertEquals( [0 => new \stdClass(), 1 => new \stdClass()], Map::times( 2, function( $num ) {
			return new \stdClass();
		} )->toArray() );
	}


	public function testTranspose()
	{
		$m = Map::from( [
			['name' => 'A', 2020 => 200, 2021 => 100, 2022 => 50],
			['name' => 'B', 2020 => 300, 2021 => 200, 2022 => 100],
			['name' => 'C', 2020 => 400, 2021 => 300, 2022 => 200],
		] );

		$expected = [
			'name' => ['A', 'B', 'C'],
			2020 => [200, 300, 400],
			2021 => [100, 200, 300],
			2022 => [50, 100, 200]
		];

		$this->assertSame( $expected, $m->transpose()->toArray() );
	}


	public function testTransposeLength()
	{
		$m = Map::from( [
			['name' => 'A', 2020 => 200, 2021 => 100, 2022 => 50],
			['name' => 'B', 2020 => 300, 2021 => 200],
			['name' => 'C', 2020 => 400]
		] );

		$expected = [
			'name' => ['A', 'B', 'C'],
			2020 => [200, 300, 400],
			2021 => [100, 200],
			2022 => [50]
		];

		$this->assertSame( $expected, $m->transpose()->toArray() );
	}


	public function testTraverse()
	{
		$expected = [
			['id' => 1, 'pid' => null, 'name' => 'n1', 'children' => [
				['id' => 2, 'pid' => 1, 'name' => 'n2', 'children' => []],
				['id' => 3, 'pid' => 1, 'name' => 'n3', 'children' => []]
			]],
			['id' => 2, 'pid' => 1, 'name' => 'n2', 'children' => []],
			['id' => 3, 'pid' => 1, 'name' => 'n3', 'children' => []],
		];

		$r = Map::from( [[
			'id' => 1, 'pid' => null, 'name' => 'n1', 'children' => [
				['id' => 2, 'pid' => 1, 'name' => 'n2', 'children' => []],
				['id' => 3, 'pid' => 1, 'name' => 'n3', 'children' => []]
			]
		]] )->traverse();

		$this->assertSame( $expected, $r->toArray() );
	}


	public function testTraverseCallback()
	{
		$r = Map::from( [[
			'id' => 1, 'pid' => null, 'name' => 'n1', 'children' => [
				['id' => 2, 'pid' => 1, 'name' => 'n2', 'children' => []],
				['id' => 3, 'pid' => 1, 'name' => 'n3', 'children' => []]
			]
		]] )->traverse( function( $entry, $key, $level ) {
			return str_repeat( '-', $level ) . '- ' . $entry['name'];
		} );

		$this->assertSame( ['- n1', '-- n2', '-- n3'], $r->toArray() );
	}


	public function testTraverseCallbackObject()
	{
		$r = Map::from( [(object) [
			'id' => 1, 'pid' => null, 'name' => 'n1', 'children' => [
				(object) ['id' => 2, 'pid' => 1, 'name' => 'n2', 'children' => []],
				(object) ['id' => 3, 'pid' => 1, 'name' => 'n3', 'children' => []]
			]
		]] )->traverse( function( $entry, $key, $level ) {
			return str_repeat( '-', $level ) . '- ' . $entry->name;
		} );

		$this->assertSame( ['- n1', '-- n2', '-- n3'], $r->toArray() );
	}


	public function testTraverseNestkey()
	{
		$expected = [
			['id' => 1, 'pid' => null, 'name' => 'n1', 'nodes' => [
				['id' => 2, 'pid' => 1, 'name' => 'n2', 'nodes' => []]
			]],
			['id' => 2, 'pid' => 1, 'name' => 'n2', 'nodes' => []],
		];

		$r = Map::from( [[
			'id' => 1, 'pid' => null, 'name' => 'n1', 'nodes' => [
				['id' => 2, 'pid' => 1, 'name' => 'n2', 'nodes' => []]
			]
		]] )->traverse( null, 'nodes' );

		$this->assertSame( $expected, $r->toArray() );
	}


	public function testTree()
	{
		$expected = [
			1 => [
				'id' => 1, 'pid' => null, 'name' => 'Root', 'children' => [
					2 => ['id' => 2, 'pid' => 1, 'name' => '1/2', 'children' => [
						4 => ['id' => 4, 'pid' => 2, 'name' => '1/2/4', 'children' => []],
						5 => ['id' => 5, 'pid' => 2, 'name' => '1/2/5', 'children' => []],
					]],
					3 => ['id' => 3, 'pid' => 1, 'name' => '1/3', 'children' => [
						6 => ['id' => 6, 'pid' => 3, 'name' => '1/3/6', 'children' => []],
						7 => ['id' => 7, 'pid' => 3, 'name' => '1/3/7', 'children' => []],
					]]
				]
			]
		];

		$data = [
			['id' => 1, 'pid' => null, 'name' => 'Root'],
			['id' => 2, 'pid' => 1, 'name' => '1/2'],
			['id' => 3, 'pid' => 1, 'name' => '1/3'],
			['id' => 4, 'pid' => 2, 'name' => '1/2/4'],
			['id' => 5, 'pid' => 2, 'name' => '1/2/5'],
			['id' => 6, 'pid' => 3, 'name' => '1/3/6'],
			['id' => 7, 'pid' => 3, 'name' => '1/3/7'],
		];

		$m = new Map( $data );
		$this->assertSame( $expected, $m->tree( 'id', 'pid' )->toArray() );
	}


	public function testTrim()
	{
		$this->assertEquals( ["abc", "cde"], Map::from( [" abc\n", "\tcde\r\n"] )->trim()->toArray() );
		$this->assertEquals( [" b ", "x"], Map::from( ["a b c", "cbax"] )->trim( 'abc' )->toArray() );
	}


	public function testToArray()
	{
		$m = new Map( ['name' => 'Hello'] );
		$this->assertSame( ['name' => 'Hello'], $m->toArray() );
	}


	public function testToJson()
	{
		$m = new Map( ['name' => 'Hello'] );
		$this->assertSame( '{"name":"Hello"}', $m->toJson() );
	}


	public function testToJsonOptions()
	{
		$m = new Map( ['name', 'Hello'] );
		$this->assertSame( '{"0":"name","1":"Hello"}', $m->toJson( JSON_FORCE_OBJECT ) );
	}


	public function testToUrl()
	{
		$this->assertSame( 'a=1&b=2', Map::from( ['a' => 1, 'b' => 2] )->toUrl() );
	}


	public function testToUrlNested()
	{
		$url = Map::from( ['a' => ['b' => 'abc', 'c' => 'def'], 'd' => 123] )->toUrl();
		$this->assertSame( 'a%5Bb%5D=abc&a%5Bc%5D=def&d=123', $url );
	}


	public function testUasort()
	{
		$m = ( new Map( ['a' => 'foo', 'c' => 'bar-10', 1 => 'bar-1'] ) )->uasort( function( $a, $b ) {
			return strrev( $a ) <=> strrev( $b );
		} );

		$this->assertInstanceOf( Map::class, $m );
		$this->assertSame( ['c' => 'bar-10', 1 => 'bar-1', 'a' => 'foo'], $m->toArray() );
	}


	public function testUksort()
	{
		$m = ( new Map( ['a' => 'foo', 'c' => 'bar-10', 1 => 'bar-1'] ) )->uksort( function( $a, $b ) {
			return (string) $a <=> (string) $b;
		} );

		$this->assertInstanceOf( Map::class, $m );
		$this->assertSame( [1 => 'bar-1', 'a' => 'foo', 'c' => 'bar-10'], $m->toArray() );
	}


	public function testUsort()
	{
		$m = ( new Map( ['foo', 'bar-10', 'bar-1'] ) )->usort( function( $a, $b ) {
			return strrev( $a ) <=> strrev( $b );
		} );

		$this->assertInstanceOf( Map::class, $m );
		$this->assertSame( ['bar-10', 'bar-1', 'foo'], $m->toArray() );
	}


	public function testUnionArray()
	{
		$m = new Map( ['name' => 'Hello'] );
		$r = $m->union( ['id' => 1] );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['name' => 'Hello', 'id' => 1], $r->toArray() );
	}


	public function testUnionMap()
	{
		$m = new Map( ['name' => 'Hello'] );
		$r = $m->union( new Map( ['name' => 'World', 'id' => 1] ) );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['name' => 'Hello', 'id' => 1], $r->toArray() );
	}


	public function testUnique()
	{
		$m = new Map( ['Hello', 'World', 'World'] );
		$r = $m->unique();

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['Hello', 'World'], $r->toArray() );
	}


	public function testUniqueKey()
	{
		$m = new Map( [['p' => '1'], ['p' => 1], ['p' => 2]] );
		$r = $m->unique( 'p' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( [['p' => 1], ['p' => 2]], $r->toArray() );
	}


	public function testUniquePath()
	{
		$m = new Map( [['i' => ['p' => '1']], ['i' => ['p' => 1]]] );
		$r = $m->unique( 'i/p' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( [['i' => ['p' => 1]]], $r->toArray() );
	}


	public function testUnshift()
	{
		$m = ( new Map( ['one', 'two', 'three', 'four'] ) )->unshift( 'zero' );
		$this->assertInstanceOf( Map::class, $m );
		$this->assertSame( ['zero', 'one', 'two', 'three', 'four'], $m->toArray() );
	}


	public function testUnshiftWithKey()
	{
		$m = ( new Map( ['one' => 1, 'two' => 2] ) )->unshift( 0, 'zero' );
		$this->assertInstanceOf( Map::class, $m );
		$this->assertSame( ['zero' => 0, 'one' => 1, 'two' => 2], $m->toArray() );
	}


	public function testValues()
	{
		$m = new Map( ['id' => 1, 'name' => 'Hello'] );
		$r = $m->values();

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( [1, 'Hello'], $r->toArray() );
	}


	public function testWalk()
	{
		$m = new Map( ['a', 'B', ['c', 'd'], 'e'] );
		$r = $m->walk( function( &$value ) {
			$value = strtoupper( $value );
		} );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['A', 'B', ['C', 'D'], 'E'], $r->toArray() );
	}


	public function testWalkNonRecursive()
	{
		$m = new Map( ['a', 'B', ['c', 'd'], 'e'] );
		$r = $m->walk( function( &$value ) {
			$value = ( !is_array( $value ) ? strtoupper( $value ) : $value );
		}, null, false );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['A', 'B', ['c', 'd'], 'E'], $r->toArray() );
	}


	public function testWalkData()
	{
		$m = new Map( [1, 2, 3] );
		$r = $m->walk( function( &$value, $key, $data ) {
			$value = $data[$value] ?? $value;
		}, [1 => 'one', 2 => 'two'] );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertSame( ['one', 'two', 3], $r->toArray() );
	}


	public function testWhere()
	{
		$m = Map::from( [['p' => 10], ['p' => 20], ['p' => 30]] );

		$this->assertInstanceOf( Map::class, $m->where( 'p', '!=', null ) );
		$this->assertSame( [['p' => 10]], $m->where( 'p', '==', 10 )->toArray() );
		$this->assertSame( [], $m->where( 'p', '===', '10' )->toArray() );
		$this->assertSame( [1 => ['p' => 20], 2 => ['p' => 30]], $m->where( 'p', '!=', 10 )->toArray() );
		$this->assertSame( [['p' => 10], ['p' => 20], ['p' => 30]], $m->where( 'p', '!==', '10' )->toArray() );
		$this->assertSame( [1 => ['p' => 20], 2 => ['p' => 30]], $m->where( 'p', '>', 10 )->toArray() );
		$this->assertSame( [['p' => 10], ['p' => 20]], $m->where( 'p', '<', 30 )->toArray() );
		$this->assertSame( [['p' => 10], ['p' => 20]], $m->where( 'p', '<=', 20 )->toArray() );
		$this->assertSame( [1 => ['p' => 20], 2 => ['p' => 30]], $m->where( 'p', '>=', 20 )->toArray() );
	}


	public function testWhereBetween()
	{
		$m = Map::from( [['p' => 10], ['p' => 20], ['p' => 30]] );

		$this->assertSame( [['p' => 10], ['p' => 20]], $m->where( 'p', '-', [10, 20] )->toArray() );
		$this->assertSame( [['p' => 10]], $m->where( 'p', '-', [10] )->toArray() );
		$this->assertSame( [['p' => 10]], $m->where( 'p', '-', 10 )->toArray() );
	}


	public function testWhereIn()
	{
		$m = Map::from( [['p' => 10], ['p' => 20], ['p' => 30]] );

		$this->assertSame( [['p' => 10], 2 => ['p' => 30]], $m->where( 'p', 'in', [10, 30] )->toArray() );
		$this->assertSame( [['p' => 10]], $m->where( 'p', 'in', 10 )->toArray() );
	}


	public function testWhereNotFound()
	{
		$this->assertSame( [], Map::from( [['p' => 10]] )->where( 'x', '==', [0] )->toArray() );
	}


	public function testWherePath()
	{
		$m = Map::from( [['item' => ['id' => 3, 'price' => 10]], ['item' => ['id' => 4, 'price' => 50]]] );

		$this->assertSame( [1 => ['item' => ['id' => 4, 'price' => 50]]], $m->where( 'item/price', '>', 30 )->toArray() );
	}


	public function testZip()
	{
		$m = new Map( [1, 2, 3] );
		$en = ['one', 'two', 'three'];
		$es = ['uno', 'dos', 'tres'];

		$expected = [
			[1, 'one', 'uno'],
			[2, 'two', 'dos'],
			[3, 'three', 'tres'],
		];

		$this->assertSame( $expected, $m->zip( $en, $es )->toArray() );
	}
}



class TestMapObject
{
	/**
	 * @var int
	 */
	private static $num = 1;

	/**
	 * @var int
	 */
	private static $prop = 1;


	public function get( int $prop ) : string
	{
		return 'p' . self::$prop++;
	}

	public function getCode() : int
	{
		return self::$num++;
	}

	public function setId( int $id ) : self
	{
		return $this;
	}

	/**
	 * @return array<string,string>
	 */
	public function toArray() : array
	{
		return ['prop' => 'p' . self::$prop++];
	}
}