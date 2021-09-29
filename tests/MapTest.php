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
		$this->assertEquals( ['a' => 1, 'b' => 2], $m->setId( null )->getCode()->toArray() );
	}


	public function testMagicToArray()
	{
		$m = new Map( ['name' => 'Hello'] );
		$this->assertEquals( ['name' => 'Hello'], $m->__toArray() );
	}


	public function testAfter()
	{
		$this->assertEquals( [1 => 'a'], Map::from( [0 => 'b', 1 => 'a'] )->after( 'b' )->toArray() );
	}


	public function testAfterInt()
	{
		$this->assertEquals( ['b' => 0], Map::from( ['a' => 1, 'b' => 0] )->after( 1 )->toArray() );
	}


	public function testAfterNone()
	{
		$this->assertEquals( [], Map::from( [0 => 'b', 1 => 'a'] )->after( 'c' )->toArray() );
	}


	public function testAfterCallback()
	{
		$this->assertEquals( [2 => 'b'], Map::from( ['a', 'c', 'b'] )->after( function( $item, $key ) {
			return $item >= 'c';
		} )->toArray() );
	}


	public function testAll()
	{
		$m = new Map( ['name' => 'Hello'] );
		$this->assertEquals( ['name' => 'Hello'], $m->all() );
	}


	public function testArsortNummeric()
	{
		$m = ( new Map( [1 => -3, 2 => -2, 3 => -4, 4 => -1, 5 => 0, 6 => 4, 7 => 3, 8 => 1, 9 => 2] ) )->arsort();

		$this->assertInstanceOf( Map::class, $m );
		$this->assertEquals( [6 => 4, 7 => 3, 9 => 2, 8 => 1, 5 => 0, 4 => -1, 2 => -2, 1 => -3, 3 => -4], $m->toArray() );
	}


	public function testArsortStrings()
	{
		$m = ( new Map( ['c' => 'bar-10', 1 => 'bar-1', 'a' => 'foo'] ) )->arsort();

		$this->assertInstanceOf( Map::class, $m );
		$this->assertEquals( ['a' => 'foo', 1 => 'bar-1', 'c' => 'bar-10'], $m->toArray() );
	}


	public function testArsortStringsCase()
	{
		$m = ( new Map( [0 => 'C', 1 => 'b'] ) );

		$this->assertEquals( [1 => 'b', 0 => 'C'], $m->arsort()->toArray() );
		$this->assertEquals( [0 => 'C', 1 => 'b'], $m->arsort( SORT_STRING|SORT_FLAG_CASE )->toArray() );
	}


	public function testAsortNummeric()
	{
		$m = ( new Map( [1 => -3, 2 => -2, 3 => -4, 4 => -1, 5 => 0, 6 => 4, 7 => 3, 8 => 1, 9 => 2] ) )->asort();

		$this->assertInstanceOf( Map::class, $m );
		$this->assertEquals( [3 => -4, 1 => -3, 2 => -2, 4 => -1, 5 => 0, 8 => 1, 9 => 2, 7 => 3, 6 => 4], $m->toArray() );
	}


	public function testAsortStrings()
	{
		$m = ( new Map( ['a' => 'foo', 'c' => 'bar-10', 1 => 'bar-1'] ) )->asort();

		$this->assertInstanceOf( Map::class, $m );
		$this->assertEquals( ['c' => 'bar-10', 1 => 'bar-1', 'a' => 'foo'], $m->toArray() );
	}


	public function testAsortStringsCase()
	{
		$m = ( new Map( [0 => 'C', 1 => 'b'] ) );

		$this->assertEquals( [0 => 'C', 1 => 'b'], $m->asort()->toArray() );
		$this->assertEquals( [1 => 'b', 0 => 'C'], $m->asort( SORT_STRING|SORT_FLAG_CASE )->toArray() );
	}


	public function testBefore()
	{
		$this->assertEquals( [0 => 'b'], Map::from( [0 => 'b', 1 => 'a'] )->before( 'a' )->toArray() );
	}


	public function testBeforeInt()
	{
		$this->assertEquals( ['a' => 1], Map::from( ['a' => 1, 'b' => 0] )->before( 0 )->toArray() );
	}


	public function testBeforeNone()
	{
		$this->assertEquals( [], Map::from( [0 => 'b', 1 => 'a'] )->before( 'b' )->toArray() );
	}


	public function testBeforeCallback()
	{
		$this->assertEquals( [0 => 'a'], Map::from( ['a', 'c', 'b'] )->before( function( $item, $key ) {
			return $key >= 1;
		} )->toArray() );
	}


	public function testCall()
	{
		$m = new Map( ['a' => new TestMapObject(), 'b' => new TestMapObject()] );

		$this->assertEquals( ['a' => 'p1', 'b' => 'p2'], $m->call( 'get', ['prop'] )->toArray() );
		$this->assertEquals( ['a' => ['prop' => 'p3'], 'b' => ['prop' => 'p4']], $m->call( 'toArray' )->toArray() );
	}


	public function testChunk()
	{
		$m = new Map( [0, 1, 2, 3, 4] );
		$this->assertEquals( [[0, 1, 2], [3, 4]], $m->chunk( 3 )->toArray() );
	}


	public function testChunkException()
	{
		$this->expectException( \InvalidArgumentException::class );
		Map::from( [] )->chunk( 0 );
	}


	public function testChunkKeys()
	{
		$m = new Map( ['a' => 0, 'b' => 1, 'c' => 2] );
		$this->assertEquals( [['a' => 0, 'b' => 1], ['c' => 2]], $m->chunk( 2, true )->toArray() );
	}


	public function testClear()
	{
		$m = new Map( ['foo', 'bar'] );
		$this->assertInstanceOf( Map::class, $m->clear() );
	}


	public function testCol()
	{
		$map = new Map( [['foo' => 'one', 'bar' => 'two']] );
		$r = $map->col( 'bar' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( [0 => 'two'], $r->toArray() );
	}


	public function testColIndex()
	{
		$map = new Map( [['foo' => 'one', 'bar' => 'two']] );
		$r = $map->col( 'bar', 'foo' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['one' => 'two'], $r->toArray() );
	}


	public function testColIndexDuplicate()
	{
		$map = new Map( [['id' => 'ix', 'val' => 'v1'], ['id' => 'ix', 'val' => 'v2']] );
		$r = $map->col( null, 'id' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['ix' => ['id' => 'ix', 'val' => 'v2']], $r->toArray() );
	}


	public function testColIndexNull()
	{
		$map = new Map( [['bar' => 'two']] );
		$r = $map->col( 'bar', 'foo' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['two'], $r->toArray() );
	}


	public function testColIndexOnly()
	{
		$map = new Map( [['foo' => 'one', 'bar' => 'two']] );
		$r = $map->col( null, 'foo' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['one' => ['foo' => 'one', 'bar' => 'two']], $r->toArray() );
	}


	public function testColRecursive()
	{
		$map = new Map( [['foo' => ['bar' => 'one', 'baz' => 'two']]] );
		$r = $map->col( 'foo/baz', 'foo/bar' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['one' => 'two'], $r->toArray() );
	}


	public function testColRecursiveNull()
	{
		$map = new Map( [['foo' => ['bar' => 'one']]] );
		$r = $map->col( 'foo/baz', 'foo/bar' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['one' => null], $r->toArray() );
	}


	public function testColRecursiveIndexNull()
	{
		$map = new Map( [['foo' => ['baz' => 'two']]] );
		$r = $map->col( 'foo/baz', 'foo/bar' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['two'], $r->toArray() );
	}


	public function testCollapse()
	{
		$m = Map::from( [0 => ['a' => 0, 'b' => 1], 1 => ['c' => 2, 'd' => 3]]);
		$this->assertEquals( ['a' => 0, 'b' => 1, 'c' => 2, 'd' => 3], $m->collapse()->toArray() );
	}


	public function testCollapseOverwrite()
	{
		$m = Map::from( [0 => ['a' => 0, 'b' => 1], 1 => ['a' => 2]] );
		$this->assertEquals( ['a' => 2, 'b' => 1], $m->collapse()->toArray() );
	}


	public function testCollapseRecursive()
	{
		$m = Map::from( [0 => [0 => 0, 1 => 1], 1 => [0 => ['a' => 2, 0 => 3], 1 => 4]] );
		$this->assertEquals( [0 => 3, 1 => 4, 'a' => 2], $m->collapse()->toArray() );
	}


	public function testCollapseDepth()
	{
		$m = Map::from( [0 => [0 => 0, 'a' => 1], 1 => [0 => ['b' => 2, 0 => 3], 1 => 4]] );
		$this->assertEquals( [0 => ['b' => 2, 0 => 3], 1 => 4, 'a' => 1], $m->collapse( 1 )->toArray() );
	}


	public function testCollapseIterable()
	{
		$m = Map::from( [0 => [0 => 0, 'a' => 1], 1 => Map::from( [0 => ['b' => 2, 0 => 3], 1 => 4] )] );
		$this->assertEquals( [0 => 3, 'a' => 1, 'b' => 2, 1 => 4], $m->collapse()->toArray() );
	}


	public function testCollapseException()
	{
		$this->expectException( \InvalidArgumentException::class );
		Map::from( [] )->collapse( -1 );
	}


	public function testCombine()
	{
		$r = Map::from( ['name', 'age'] )->combine( ['Tom', 29] );
		$this->assertEquals( ['name' => 'Tom', 'age' => 29], $r->toArray() );
	}


	public function testConcatWithArray()
	{
		$first = new Map( [1, 2] );
		$r = $first->concat( ['a', 'b'] )->concat( ['x' => 'foo', 'y' => 'bar'] );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( [1, 2, 'a', 'b', 'foo', 'bar'], $r->toArray() );
	}


	public function testConcatMap()
	{
		$first = new Map( [1, 2] );
		$second = new Map( ['a', 'b'] );
		$third = new Map( ['x' => 'foo', 'y' => 'bar'] );

		$r = $first->concat( $second )->concat( $third );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( [1, 2, 'a', 'b', 'foo', 'bar'], $r->toArray() );
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
		$this->assertEquals( ['foo' => 'bar'], $secondMap->toArray() );
	}


	public function testConstructArray()
	{
		$map = new Map( ['foo' => 'bar'] );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertEquals( ['foo' => 'bar'], $map->toArray() );
	}


	public function testConstructTraversable()
	{
		$map = new Map( new \ArrayObject( [1, 2, 3] ) );
		$this->assertEquals( [1, 2, 3], $map->toArray() );
	}


	public function testConstructTraversableKeys()
	{
		$map = new Map( new \ArrayObject( ['foo' => 1, 'bar' => 2, 'baz' => 3] ) );
		$this->assertEquals( ['foo' => 1, 'bar' => 2, 'baz' => 3], $map->toArray() );
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
		$this->assertEquals( [1 => 2, 'foo' => 2, 2 => 1], $r->toArray() );
	}


	public function testCountByCallback()
	{
		$r = Map::from( ['a@gmail.com', 'b@yahoo.com', 'c@gmail.com'] )->countBy( function( $email ) {
			return substr( strrchr( $email, '@' ), 1 );
		} );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['gmail.com' => 2, 'yahoo.com' => 1], $r->toArray() );
	}


	public function testCountByFloat()
	{
		$r = Map::from( [1.11, 3.33, 3.33, 9.99] )->countBy();

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['1.11' => 1, '3.33' => 2, '9.99' => 1], $r->toArray() );
	}


	public function testDelimiter()
	{
		$this->assertEquals( '/', Map::delimiter() );
		$this->assertEquals( '/', Map::delimiter( '.' ) );
		$this->assertEquals( '.', Map::delimiter( '/' ) );
		$this->assertEquals( '/', Map::delimiter() );
	}


	public function testDiff()
	{
		$m = new Map( ['id' => 1, 'first_word' => 'Hello'] );
		$r = $m->diff( new Map( ['first_word' => 'Hello', 'last_word' => 'World'] ) );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['id' => 1], $r->toArray() );
	}


	public function testDiffUsingWithMap()
	{
		$m = new Map( ['en_GB', 'fr', 'HR'] );
		$r = $m->diff( new Map( ['en_gb', 'hr'] ) );

		$this->assertInstanceOf( Map::class, $r );
		// demonstrate that diffKeys wont support case insensitivity
		$this->assertEquals( ['en_GB', 'fr', 'HR'], $r->values()->toArray() );
	}


	public function testDiffCallback()
	{
		$m1 = new Map( ['a' => 'green', 'b' => 'brown', 'c' => 'blue', 'red'] );
		$m2 = new Map( ['A' => 'Green', 'yellow', 'red'] );
		$r1 = $m1->diff( $m2 );
		$r2 = $m1->diff( $m2, 'strcasecmp' );

		// demonstrate that the case of the keys will affect the output when diff is used
		$this->assertInstanceOf( Map::class, $r1 );
		$this->assertEquals( ['a' => 'green', 'b' => 'brown', 'c' => 'blue'], $r1->toArray() );

		// allow for case insensitive difference
		$this->assertInstanceOf( Map::class, $r2 );
		$this->assertEquals( ['b' => 'brown', 'c' => 'blue'], $r2->toArray() );
	}


	public function testDiffAssoc()
	{
		$m1 = new Map( ['id' => 1, 'first_word' => 'Hello', 'not_affected' => 'value'] );
		$m2 = new Map( ['id' => 123, 'foo_bar' => 'Hello', 'not_affected' => 'value'] );
		$r = $m1->diffAssoc( $m2 );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['id' => 1, 'first_word' => 'Hello'], $r->toArray() );
	}


	public function testDiffAssocCallback()
	{
		$m1 = new Map( ['a' => 'green', 'b' => 'brown', 'c' => 'blue', 'red'] );
		$m2 = new Map( ['A' => 'green', 'yellow', 'red'] );
		$r1 = $m1->diffAssoc( $m2 );
		$r2 = $m1->diffAssoc( $m2, 'strcasecmp' );

		// demonstrate that the case of the keys will affect the output when diffAssoc is used
		$this->assertInstanceOf( Map::class, $r1 );
		$this->assertEquals( ['a' => 'green', 'b' => 'brown', 'c' => 'blue', 'red'], $r1->toArray() );

		// allow for case insensitive difference
		$this->assertInstanceOf( Map::class, $r2 );
		$this->assertEquals( ['b' => 'brown', 'c' => 'blue', 'red'], $r2->toArray() );
	}


	public function testDiffKeys()
	{
		$m1 = new Map( ['id' => 1, 'first_word' => 'Hello'] );
		$m2 = new Map( ['id' => 123, 'foo_bar' => 'Hello'] );
		$r = $m1->diffKeys( $m2 );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['first_word' => 'Hello'], $r->toArray() );
	}


	public function testDiffKeysCallback()
	{
		$m1 = new Map( ['id' => 1, 'first_word' => 'Hello'] );
		$m2 = new Map( ['ID' => 123, 'foo_bar' => 'Hello'] );
		$r1 = $m1->diffKeys( $m2 );
		$r2 = $m1->diffKeys( $m2, 'strcasecmp' );

		// demonstrate that diffKeys wont support case insensitivity
		$this->assertInstanceOf( Map::class, $r1 );
		$this->assertEquals( ['id'=>1, 'first_word'=> 'Hello'], $r1->toArray() );

		// allow for case insensitive difference
		$this->assertInstanceOf( Map::class, $r2 );
		$this->assertEquals( ['first_word' => 'Hello'], $r2->toArray() );
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
		$this->assertEquals( [2 => '1'], $r->toArray() );
	}


	public function testDuplicatesColumn()
	{
		$r = Map::from( [['p' => '1'], ['p' => 1], ['p' => 2]] )->duplicates( 'p' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( [1 => ['p' => 1]], $r->toArray() );
	}


	public function testDuplicatesPath()
	{
		$r = Map::from( [['i' => ['p' => '1']], ['i' => ['p' => 1]]] )->duplicates( 'i/p' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( [1 => ['i' => ['p' => '1']]], $r->toArray() );
	}


	public function testEach()
	{
		$m = new Map( $original = [1, 2, 'foo' => 'bar', 'bam' => 'baz'] );

		$result = [];
		$r = $m->each( function( $item, $key ) use ( &$result ) {
			$result[$key] = $item;
		} );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( $original, $result );
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
		$this->assertEquals( [1, 2, 'foo' => 'bar'], $result );
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
		$this->assertFalse( $map->equals( ['foo' => 'one'], true ) );
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
		$this->assertEquals( ['a' => 1, 'c' => 3], Map::from( ['a' => 1, 'b' => 2, 'c' => 3] )->except( 'b' )->toArray() );
		$this->assertEquals( [2 => 'b'], Map::from( [1 => 'a', 2 => 'b', 3 => 'c'] )->except( [1, 3] )->toArray() );
	}


	public function testExplode()
	{
		$map = Map::explode( ',', 'a,b,c' );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertEquals( ['a', 'b', 'c'], $map->toArray() );
	}


	public function testExplodeString()
	{
		$map = Map::explode( '<-->', 'a a<-->b b<-->c c' );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertEquals( ['a a', 'b b', 'c c'], $map->toArray() );
	}


	public function testExplodeSplit()
	{
		$map = Map::explode( '', 'string' );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertEquals( ['s', 't', 'r', 'i', 'n', 'g'], $map->toArray() );
	}


	public function testExplodeSplitSize()
	{
		$map = Map::explode( '', 'string', 6 );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertEquals( ['s', 't', 'r', 'i', 'n', 'g'], $map->toArray() );
	}


	public function testExplodeLength()
	{
		$map = Map::explode( '|', 'a|b|c', 2 );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertEquals( ['a', 'b|c'], $map->toArray() );
	}


	public function testExplodeSplitLength()
	{
		$map = Map::explode( '', 'string', 2 );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertEquals( ['s', 't', 'ring'], $map->toArray() );
	}


	public function testExplodeNegativeLength()
	{
		$map = Map::explode( '|', 'a|b|c|d', -2 );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertEquals( ['a', 'b'], $map->toArray() );
	}


	public function testExplodeSplitNegativeLength()
	{
		$map = Map::explode( '', 'string', -3 );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertEquals( ['s', 't', 'r'], $map->toArray() );
	}


	public function testFilter()
	{
		$m = new Map( [['id' => 1, 'name' => 'Hello'], ['id' => 2, 'name' => 'World']] );

		$this->assertEquals( [1 => ['id' => 2, 'name' => 'World']], $m->filter( function( $item ) {
			return $item['id'] == 2;
		} )->toArray() );
	}


	public function testFilterNoCallback()
	{
		$m = new Map( ['', 'Hello', '', 'World'] );
		$r = $m->filter();

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['Hello', 'World'], $r->values()->toArray() );
	}


	public function testFilterRemove()
	{
		$m = new Map( ['id' => 1, 'first' => 'Hello', 'second' => 'World'] );

		$this->assertEquals( ['first' => 'Hello', 'second' => 'World'], $m->filter( function( $item, $key ) {
			return $key != 'id';
		} )->toArray() );
	}


	public function testFind()
	{
		$m = new Map( ['foo', 'bar', 'baz', 'boo'] );
		$result = $m->find( function( $value, $key ) {
			return !strncmp( $value, 'ba', 2 );
		} );
		$this->assertEquals( 'bar', $result );
	}


	public function testFindLast()
	{
		$m = new Map( ['foo', 'bar', 'baz', 'boo'] );
		$result = $m->find( function( $value, $key ) {
			return !strncmp( $value, 'ba', 2 );
		}, null, true );
		$this->assertEquals( 'baz', $result );
	}


	public function testFindDefault()
	{
		$m = new Map( ['foo', 'bar', 'baz'] );
		$result = $m->find( function( $value ) {
			return false;
		}, 'none' );
		$this->assertEquals( 'none', $result );
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
		$this->assertEquals( 'foo', $m->first() );
	}


	public function testFirstWithDefault()
	{
		$m = new Map;
		$result = $m->first( 'default' );
		$this->assertEquals( 'default', $result );
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
		$this->assertEquals( 'a', Map::from( ['a' => 1, 'b' => 2] )->firstKey() );
	}


	public function testFirstKeyEmpty()
	{
		$this->assertEquals( null, Map::from( [] )->firstKey() );
	}


	public function testFlat()
	{
		$m = Map::from( [[0, 1], [2, 3]] );
		$this->assertEquals( [0, 1, 2, 3], $m->flat()->toArray() );
	}


	public function testFlatNone()
	{
		$m = Map::from( [[0, 1], [2, 3]] );
		$this->assertEquals( [[0, 1], [2, 3]], $m->flat( 0 )->toArray() );
	}


	public function testFlatRecursive()
	{
		$m = Map::from( [[0, 1], [[2, 3], 4]] );
		$this->assertEquals( [0, 1, 2, 3, 4], $m->flat()->toArray() );
	}


	public function testFlatDepth()
	{
		$m = Map::from( [[0, 1], [[2, 3], 4]] );
		$this->assertEquals( [0, 1, [2, 3], 4], $m->flat( 1 )->toArray() );
	}


	public function testFlatTraversable()
	{
		$m = Map::from( [[0, 1], Map::from( [[2, 3], 4] )] );
		$this->assertEquals( [0, 1, 2, 3, 4], $m->flat()->toArray() );
	}


	public function testFlatException()
	{
		$this->expectException( \InvalidArgumentException::class );
		Map::from( [] )->flat( -1 );
	}


	public function testFlip()
	{
		$m = Map::from( ['a' => 'X', 'b' => 'Y'] );
		$this->assertEquals( ['X' => 'a', 'Y' => 'b'], $m->flip()->toArray() );
	}


	public function testFromNull()
	{
		$m = Map::from( null );

		$this->assertInstanceOf( Map::class, $m );
		$this->assertEquals( [], $m->toArray() );
	}


	public function testFromValue()
	{
		$m = Map::from( 'a' );

		$this->assertInstanceOf( Map::class, $m );
		$this->assertEquals( [0 => 'a'], $m->toArray() );
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
		$this->assertEquals( ['foo' => 'bar'], $map->toArray() );
	}


	public function testFromJson()
	{
		$map = Map::fromJson( '["a", "b"]' );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertEquals( ['a', 'b'], $map->toArray() );
	}


	public function testFromJsonObject()
	{
		$map = Map::fromJson( '{"a": "b"}' );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertEquals( ['a' => 'b'], $map->toArray() );
	}


	public function testFromJsonEmpty()
	{
		$map = Map::fromJson( '""' );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertEquals( [''], $map->toArray() );
	}


	public function testFromJsonException()
	{
		$this->expectException( '\RuntimeException' );
		Map::fromJson( '' );
	}


	public function testGetArray()
	{
		$map = new Map;

		$class = new \ReflectionClass( $map );
		$method = $class->getMethod( 'getArray' );
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
		$this->assertEquals( ['foo'], $m->getIterator()->getArrayCopy() );
	}


	public function testGet()
	{
		$map = new Map( ['a' => 1, 'b' => 2, 'c' => 3] );
		$this->assertEquals( 2, $map->get( 'b' ) );
	}


	public function testGetPath()
	{
		$this->assertEquals( 'Y', Map::from( ['a' => ['b' => ['c' => 'Y']]] )->get( 'a/b/c' ) );
	}


	public function testGetPathObject()
	{
		$obj = new \stdClass;
		$obj->b = 'X';

		$this->assertEquals( 'X', Map::from( ['a' => $obj] )->get( 'a/b' ) );
	}


	public function testGetWithNull()
	{
		$map = new Map( [1, 2, 3] );
		$this->assertNull( $map->get( null ) );
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
		$this->assertEquals( ['ab', 'bc'], $r->toArray() );
	}


	public function testGrepInvert()
	{
		$r = Map::from( ['ab', 'bc', 'cd'] )->grep( '/a/', PREG_GREP_INVERT );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( [1 => 'bc', 2 => 'cd'], $r->toArray() );
	}


	public function testGrepException()
	{
		set_error_handler( function( $errno ) {} );

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
		$this->assertEquals( [1.5], $r->toArray() );
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
		$this->assertEquals( $expected, $r->toArray() );
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
		$this->assertEquals( $expected, $r->toArray() );
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
		$this->assertEquals( $expected, $r->toArray() );
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
		$this->assertEquals( ['b'], $r->all() );
	}


	public function testIfThen()
	{
		$r = Map::from( ['a'] )->if(
			function( Map $map ) { return $map->in( 'a' ); },
			function( Map $_ ) { $this->assertTrue( true ); },
			function( Map $_ ) { $this->assertTrue( false ); }
		);

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( [], $r->all() );
	}


	public function testIfElse()
	{
		$r = Map::from( ['a'] )->if(
			function( Map $map ) { return $map->in( 'c' ); },
			function( Map $_ ) { $this->assertTrue( false ); },
			function( Map $_ ) { $this->assertTrue( true ); }
		);

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( [], $r->all() );
	}


	public function testIfTrue()
	{
		$r = Map::from( ['a'] )->if( true );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['a'], $r->all() );
	}


	public function testIfFalse()
	{
		$r = Map::from( ['a'] )->if( false );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( [], $r->all() );
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

		$this->assertEquals( 1, $m->index( '8' ) );
	}


	public function testIndexClosure()
	{
		$m = new Map( [4 => 'a', 8 => 'b'] );

		$this->assertEquals( 1, $m->index( function( $key ) {
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
		$this->assertEquals( ['a' => 'foo', 0 => 'baz', 'b' => 'bar'], $r->toArray() );
	}


	public function testInsertAfterArray()
	{
		$r = Map::from( ['foo', 'bar'] )->insertAfter( 'foo', ['baz', 'boo'] );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['foo', 'baz', 'boo', 'bar'], $r->toArray() );
	}


	public function testInsertAfterEnd()
	{
		$r = Map::from( ['foo', 'bar'] )->insertAfter( null, 'baz' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['foo', 'bar', 'baz'], $r->toArray() );
	}


	public function testInsertBefore()
	{
		$r = Map::from( ['a' => 'foo', 'b' => 'bar'] )->insertBefore( 'bar', 'baz' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['a' => 'foo', 0 => 'baz', 'b' => 'bar'], $r->toArray() );
	}


	public function testInsertBeforeArray()
	{
		$r = Map::from( ['foo', 'bar'] )->insertBefore( 'bar', ['baz', 'boo'] );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['foo', 'baz', 'boo', 'bar'], $r->toArray() );
	}


	public function testInsertBeforeEnd()
	{
		$r = Map::from( ['foo', 'bar'] )->insertBefore( null, 'baz' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['foo', 'bar', 'baz'], $r->toArray() );
	}


	public function testIntersect()
	{
		$m = new Map( ['id' => 1, 'first_word' => 'Hello'] );
		$i = new Map( ['first_world' => 'Hello', 'last_word' => 'World'] );
		$r = $m->intersect( $i );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['first_word' => 'Hello'], $r->toArray() );
	}


	public function testIntersectCallback()
	{
		$m = new Map( ['id' => 1, 'first_word' => 'Hello', 'last_word' => 'World'] );
		$i = new Map( ['first_world' => 'Hello', 'last_world' => 'world'] );
		$r = $m->intersect( $i, 'strcasecmp' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['first_word' => 'Hello', 'last_word' => 'World'], $r->toArray() );
	}


	public function testIntersectAssoc()
	{
		$m = new Map( ['id' => 1, 'name' => 'Mateus', 'age' => 18] );
		$i = new Map( ['name' => 'Mateus', 'firstname' => 'Mateus'] );
		$r = $m->intersectAssoc( $i );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['name' => 'Mateus'], $r->toArray() );
	}


	public function testIntersectAssocCallback()
	{
		$m = new Map( ['id' => 1, 'first_word' => 'Hello', 'last_word' => 'World'] );
		$i = new Map( ['first_word' => 'hello', 'Last_word' => 'world'] );
		$r = $m->intersectAssoc( $i, 'strcasecmp' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['first_word' => 'Hello'], $r->toArray() );
	}


	public function testIntersectKeys()
	{
		$m = new Map( ['id' => 1, 'name' => 'Mateus', 'age' => 18] );
		$i = new Map( ['name' => 'Mateus', 'surname' => 'Guimaraes'] );
		$r = $m->intersectKeys( $i );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['name' => 'Mateus'], $r->toArray() );
	}


	public function testIntersectKeysCallback()
	{
		$m = new Map( ['id' => 1, 'first_word' => 'Hello', 'last_word' => 'World'] );
		$i = new Map( ['First_word' => 'Hello', 'last_word' => 'world'] );
		$r = $m->intersectKeys( $i, 'strcasecmp' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['first_word' => 'Hello', 'last_word' => 'World'], $r->toArray() );
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


	public function testJoin()
	{
		$m = new Map( ['a', 'b', null, false] );
		$this->assertEquals( 'ab', $m->join() );
		$this->assertEquals( 'a-b--', $m->join( '-' ) );
	}


	public function testKeys()
	{
		$m = ( new Map( ['name' => 'test', 'last' => 'user'] ) )->keys();

		$this->assertInstanceOf( Map::class, $m );
		$this->assertEquals( ['name', 'last'], $m->toArray() );
	}


	public function testKrsortNummeric()
	{
		$m = ( new Map( [6 => 4, 7 => 3, 9 => 2, 8 => 1, 5 => 0, 4 => -1, 2 => -2, 1 => -3, 3 => -4] ) )->krsort();

		$this->assertInstanceOf( Map::class, $m );
		$this->assertEquals( [9 => 2, 8 => 1, 7 => 3, 6 => 4, 5 => 0, 4 => -1, 3 => -4, 2 => -2, 1 => -3], $m->toArray() );
	}


	public function testKrsortStrings()
	{
		$m = ( new Map( [1 => 'bar-1', 'a' => 'foo', 'c' => 'bar-10'] ) )->krsort();

		$this->assertInstanceOf( Map::class, $m );
		$this->assertEquals( ['c' => 'bar-10', 'a' => 'foo', 1 => 'bar-1'], $m->toArray() );
	}


	public function testKsortNummeric()
	{
		$m = ( new Map( [3 => -4, 1 => -3, 2 => -2, 4 => -1, 5 => 0, 8 => 1, 9 => 2, 7 => 3, 6 => 4] ) )->ksort();

		$this->assertInstanceOf( Map::class, $m );
		$this->assertEquals( [1 => -3, 2 => -2, 3 => -4, 4 => -1, 5 => 0, 6 => 4, 7 => 3, 8 => 1, 9 => 2], $m->toArray() );
	}


	public function testKsortStrings()
	{
		$m = ( new Map( ['a' => 'foo', 'c' => 'bar-10', 1 => 'bar-1'] ) )->ksort();

		$this->assertInstanceOf( Map::class, $m );
		$this->assertEquals( [1 => 'bar-1', 'a' => 'foo', 'c' => 'bar-10'], $m->toArray() );
	}


	public function testLast()
	{
		$m = new Map( ['foo', 'bar'] );
		$this->assertEquals( 'bar', $m->last() );
	}


	public function testLastWithDefault()
	{
		$m = new Map;
		$result = $m->last( 'default' );
		$this->assertEquals( 'default', $result );
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
		$this->assertEquals( 'b', Map::from( ['a' => 1, 'b' => 2] )->lastKey() );
	}


	public function testLastKeyEmpty()
	{
		$this->assertEquals( null, Map::from( [] )->lastKey() );
	}


	public function testMap()
	{
		$m = new Map( ['first' => 'test', 'last' => 'user'] );
		$m = $m->map( function( $item, $key ) {
			return $key . '-' . strrev( $item );
		} );

		$this->assertInstanceOf( Map::class, $m );
		$this->assertEquals( ['first' => 'first-tset', 'last' => 'last-resu'], $m->toArray() );
	}


	public function testMax()
	{
		$this->assertEquals( 5, Map::from( [1, 3, 2, 5, 4] )->max() );
		$this->assertEquals( 'foo', Map::from( ['bar', 'foo', 'baz'] )->max() );
	}


	public function testMaxEmpty()
	{
		$this->assertNull( Map::from( [] )->max() );
	}


	public function testMaxPath()
	{
		$this->assertEquals( 50, Map::from( [['p' => 30], ['p' => 50], ['p' => 10]] )->max( 'p' ) );
		$this->assertEquals( 50, Map::from( [['i' => ['p' => 30]], ['i' => ['p' => 50]]] )->max( 'i/p' ) );
	}


	public function testMergeArray()
	{
		$m = new Map( ['name' => 'Hello'] );
		$r = $m->merge( ['id' => 1] );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['name' => 'Hello', 'id' => 1], $r->toArray() );
	}


	public function testMergeMap()
	{
		$m = new Map( ['name' => 'Hello'] );
		$r = $m->merge( new Map( ['name' => 'World', 'id' => 1] ) );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['name' => 'World', 'id' => 1], $r->toArray() );
	}


	public function testMergeRecursive()
	{
		$r = Map::from( ['a' => 1, 'b' => 2] )->merge( ['b' => 4, 'c' => 6], true );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['a' => 1, 'b' => [2, 4], 'c' => 6], $r->toArray() );
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

		$this->assertEquals( ['a', 'aa', 'aaa'], $m->foo() );
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
		$this->assertEquals( 1, Map::from( [2, 3, 1, 5, 4] )->min() );
		$this->assertEquals( 'bar', Map::from( ['baz', 'foo', 'bar'] )->min() );
	}


	public function testMinEmpty()
	{
		$this->assertNull( Map::from( [] )->min() );
	}


	public function testMinPath()
	{
		$this->assertEquals( 10, Map::from( [['p' => 30], ['p' => 50], ['p' => 10]] )->min( 'p' ) );
		$this->assertEquals( 30, Map::from( [['i' => ['p' => 30]], ['i' => ['p' => 50]]] )->min( 'i/p' ) );
	}


	public function testNth()
	{
		$m = Map::from( ['a', 'b', 'c', 'd', 'e', 'f'] );

		$this->assertEquals( [0 => 'a', 2 => 'c', 4 => 'e'], $m->nth( 2 )->toArray() );
		$this->assertEquals( [1 => 'b', 3 => 'd', 5 => 'f'], $m->nth( 2, 1 )->toArray() );
	}


	public function testOffsetAccess()
	{
		$m = new Map( ['name' => 'test'] );
		$this->assertEquals( 'test', $m['name'] );

		$m['name'] = 'foo';
		$this->assertEquals( 'foo', $m['name'] );
		$this->assertTrue( isset( $m['name'] ) );

		unset( $m['name'] );
		$this->assertFalse( isset( $m['name'] ) );

		$m[] = 'bar';
		$this->assertEquals( 'bar', $m[0] );
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

		$this->assertEquals( 'foo', $m->offsetGet( 0 ) );
		$this->assertEquals( 'bar', $m->offsetGet( 1 ) );
	}


	public function testOffsetSet()
	{
		$m = new Map( ['foo', 'foo'] );
		$m->offsetSet( 1, 'bar' );

		$this->assertEquals( 'bar', $m[1] );
	}


	public function testOffsetSetAppend()
	{
		$m = new Map( ['foo', 'foo'] );
		$m->offsetSet( null, 'qux' );

		$this->assertEquals( 'qux', $m[2] );
	}


	public function testOffsetUnset()
	{
		$m = new Map( ['foo', 'bar'] );

		$m->offsetUnset( 1 );
		$this->assertFalse( isset( $m[1] ) );
	}


	public function testOnly()
	{
		$this->assertEquals( ['a' => 1], Map::from( ['a' => 1, 0 => 'b'] )->only( 'a' )->toArray() );
		$this->assertEquals( [0 => 'b', 1 => 'c'], Map::from( ['a' => 1, 0 => 'b', 1 => 'c'] )->only( [0, 1] )->toArray() );
	}


	public function testPad()
	{
		$this->assertEquals( [1, 2, 3, null, null], Map::from( [1, 2, 3] )->pad( 5 )->toArray() );
		$this->assertEquals( [null, null, 1, 2, 3], Map::from( [1, 2, 3] )->pad( -5 )->toArray() );

		$this->assertEquals( [1, 2, 3, '0', '0'], Map::from( [1, 2, 3] )->pad( 5, '0' )->toArray() );
		$this->assertEquals( [1, 2, 3], Map::from( [1, 2, 3] )->pad( 2 )->toArray() );
	}


	public function testPartition()
	{
		$expected = [[0 => 1, 1 => 2], [2 => 3, 3 => 4], [4 => 5]];

		$this->assertEquals( $expected, Map::from( [1, 2, 3, 4, 5] )->partition( 3 )->toArray() );
	}


	public function testPartitionClosure()
	{
		$expected = [[0 => 1, 3 => 4], [1 => 2, 4 => 5], [2 => 3]];

		$this->assertEquals( $expected, Map::from( [1, 2, 3, 4, 5] )->partition( function( $val, $idx ) {
			return $idx % 3;
		} )->toArray() );
	}


	public function testPartitionEmpty()
	{
		$this->assertEquals( [], Map::from( [] )->partition( 2 )->toArray() );
	}


	public function testPartitionInvalid()
	{
		$this->expectException( \InvalidArgumentException::class );
		Map::from( [1] )->partition( [] );
	}


	public function testPipe()
	{
		$map = new Map( [1, 2, 3] );

		$this->assertEquals( 3, $map->pipe( function( $map ) {
			return $map->last();
		} ) );
	}


	public function testPop()
	{
		$m = new Map( ['foo', 'bar'] );

		$this->assertEquals( 'bar', $m->pop() );
		$this->assertEquals( ['foo'], $m->toArray() );
	}


	public function testPos()
	{
		$m = new Map( [4 => 'a', 8 => 'b'] );

		$this->assertEquals( 1, $m->pos( 'b' ) );
	}


	public function testPosClosure()
	{
		$m = new Map( [4 => 'a', 8 => 'b'] );

		$this->assertEquals( 1, $m->pos( function( $item, $key ) {
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

		$this->assertEquals( ['1-a', '1-b'], Map::from( ['a', 'b'] )->prefix( '1-' )->toArray() );
		$this->assertEquals( ['1-a', ['1-b']], Map::from( ['a', ['b']] )->prefix( '1-' )->toArray() );
		$this->assertEquals( ['1-a', ['b']], Map::from( ['a', ['b']] )->prefix( '1-', 1 )->toArray() );
		$this->assertEquals( ['145-a', '147-b'], Map::from( ['a', 'b'] )->prefix( $fcn )->toArray() );
	}


	public function testPrepend()
	{
		$m = ( new Map( ['one', 'two', 'three', 'four'] ) )->prepend( 'zero' );
		$this->assertInstanceOf( Map::class, $m );
		$this->assertEquals( ['zero', 'one', 'two', 'three', 'four'], $m->toArray() );
	}


	public function testPull()
	{
		$m = new Map( ['foo', 'bar'] );

		$this->assertEquals( 'foo', $m->pull( 0 ) );
		$this->assertEquals( [1 => 'bar'], $m->toArray() );
	}


	public function testPullDefault()
	{
		$m = new Map( [] );
		$value = $m->pull( 0, 'foo' );
		$this->assertEquals( 'foo', $value );
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
		$this->assertEquals( ['foo'], $m->toArray() );
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
		$this->assertEquals( 6, $m->reduce( function( $carry, $element ) {
			return $carry += $element;
		} ) );
	}


	public function testReject()
	{
		$m = new Map( [2 => 'a', 6 => null, 13 => 'm'] );

		$this->assertEquals( [6 => null], $m->reject()->toArray() );
	}


	public function testRejectCallback()
	{
		$m = new Map( [2 => 'a', 6 => 'b', 13 => 'm', 30 => 'z'] );

		$this->assertEquals( [13 => 'm', 30 => 'z'], $m->reject( function( $value ) {
			return $value < 'm';
		} )->toArray() );
	}


	public function testRejectValue()
	{
		$m = new Map( [2 => 'a', 13 => 'm', 30 => 'z'] );
		$r = $m->reject( 'm' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( [2 => 'a', 30 => 'z'], $r->toArray() );
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
		$this->assertEquals( ['a', 'd', 'e'], $r->toArray() );
	}


	public function testReplaceMap()
	{
		$m = new Map( ['a', 'b', 'c'] );
		$r = $m->replace( new Map( [1 => 'd', 2 => 'e'] ) );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['a', 'd', 'e'], $r->toArray() );
	}


	public function testReplaceNonRecursive()
	{
		$m = new Map( ['a', 'b', ['c']] );
		$r = $m->replace( [1 => 'd', 2 => [1 => 'f']], false );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['a', 'd', [1 => 'f']], $r->toArray() );
	}


	public function testReplaceRecursiveArray()
	{
		$m = new Map( ['a', 'b', ['c', 'd']] );
		$r = $m->replace( ['z', 2 => [1 => 'e']] );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['z', 'b', ['c', 'e']], $r->toArray() );
	}


	public function testReplaceRecursiveMap()
	{
		$m = new Map( ['a', 'b', ['c', 'd']] );
		$r = $m->replace( new Map( ['z', 2 => [1 => 'e']] ) );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['z', 'b', ['c', 'e']], $r->toArray() );
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
		$this->assertEquals( [5, 4, 3, 2, 1, 0, -1, -2, -3, -4, -5], $m->toArray() );
	}


	public function testRsortStrings()
	{
		$m = ( new Map( ['bar-10', 'foo', 'bar-1'] ) )->rsort();

		$this->assertInstanceOf( Map::class, $m );
		$this->assertEquals( ['foo', 'bar-10', 'bar-1'], $m->toArray() );
	}


	public function testSearch()
	{
		$m = new Map( [false, 0, 1, [], ''] );

		$this->assertNull( $m->search( 'false' ) );
		$this->assertNull( $m->search( '1' ) );
		$this->assertEquals( 0, $m->search( false ) );
		$this->assertEquals( 1, $m->search( 0 ) );
		$this->assertEquals( 2, $m->search( 1 ) );
		$this->assertEquals( 3, $m->search( [] ) );
		$this->assertEquals( 4, $m->search( '' ) );
	}


	public function testSep()
	{
		$this->assertEquals( 'baz', Map::from( ['foo' => ['bar' => 'baz']] )->sep( '/' )->get( 'foo/bar' ) );
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

		$this->assertEquals( 'foo', $m->shift() );
		$this->assertEquals( 'bar', $m->first() );
		$this->assertEquals( 1, $m->count() );
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
			$this->assertEquals( $value, $result[$key] );
		}
	}


	public function testSkip()
	{
		$this->assertEquals( [2 => 3, 3 => 4], Map::from( [1, 2, 3, 4] )->skip( 2 )->toArray() );
	}


	public function testSkipFunction()
	{
		$fcn = function( $item, $key ) {
			return $item < 4;
		};

		$this->assertEquals( [3 => 4], Map::from( [1, 2, 3, 4] )->skip( $fcn )->toArray() );
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
		$this->assertEquals( [4, 5, 6, 7, 8], $map->values()->toArray() );
	}


	public function testSliceNegativeOffset()
	{
		$map = ( new Map( [1, 2, 3, 4, 5, 6, 7, 8] ) )->slice( -3 );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertEquals( [6, 7, 8], $map->values()->toArray() );
	}


	public function testSliceOffsetAndLength()
	{
		$map = ( new Map( [1, 2, 3, 4, 5, 6, 7, 8] ) )->slice( 3, 3 );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertEquals( [4, 5, 6], $map->values()->toArray() );
	}


	public function testSliceOffsetAndNegativeLength()
	{
		$map = ( new Map( [1, 2, 3, 4, 5, 6, 7, 8] ) )->slice( 3, -1 );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertEquals( [4, 5, 6, 7], $map->values()->toArray() );
	}


	public function testSliceNegativeOffsetAndLength()
	{
		$map = ( new Map( [1, 2, 3, 4, 5, 6, 7, 8] ) )->slice( -5, 3 );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertEquals( [4, 5, 6], $map->values()->toArray() );
	}


	public function testSliceNegativeOffsetAndNegativeLength()
	{
		$map = ( new Map( [1, 2, 3, 4, 5, 6, 7, 8] ) )->slice( -6, -2 );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertEquals( [3, 4, 5, 6], $map->values()->toArray() );
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
		$this->assertEquals( [-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], $m->toArray() );
	}


	public function testSortStrings()
	{
		$m = ( new Map( ['foo', 'bar-10', 'bar-1'] ) )->sort();

		$this->assertInstanceOf( Map::class, $m );
		$this->assertEquals( ['bar-1', 'bar-10', 'foo'], $m->toArray() );
	}


	public function testSplice()
	{
		$m = new Map( ['foo', 'baz'] );
		$r = $m->splice( 1 );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['foo'], $m->toArray() );
	}


	public function testSpliceReplace()
	{
		$m = new Map( ['foo', 'baz'] );
		$r = $m->splice( 1, 0, 'bar' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['foo', 'bar', 'baz'], $m->toArray() );
	}


	public function testSpliceRemove()
	{
		$m = new Map( ['foo', 'baz'] );
		$r = $m->splice( 1, 1 );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['foo'], $m->toArray() );
	}


	public function testSpliceCut()
	{
		$m = new Map( ['foo', 'baz'] );
		$r = $m->splice( 1, 1, 'bar' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['foo', 'bar'], $m->toArray() );
		$this->assertEquals( ['baz'], $r->toArray() );
	}


	public function testSpliceAll()
	{
		$m = new Map( ['foo', 'baz'] );
		$r = $m->splice( 1, null, ['bar'] );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['foo', 'bar'], $m->toArray() );
	}


	public function testSuffix()
	{
		$fcn = function( $item, $key ) {
			return '-' . ( ord( $item ) + ord( $key ) );
		};

		$this->assertEquals( ['a-1', 'b-1'], Map::from( ['a', 'b'] )->suffix( '-1' )->toArray() );
		$this->assertEquals( ['a-1', ['b-1']], Map::from( ['a', ['b']] )->suffix( '-1' )->toArray() );
		$this->assertEquals( ['a-1', ['b']], Map::from( ['a', ['b']] )->suffix( '-1', 1 )->toArray() );
		$this->assertEquals( ['a-145', 'b-147'], Map::from( ['a', 'b'] )->suffix( $fcn )->toArray() );
	}


	public function testSum()
	{
		$this->assertEquals( 9, Map::from( [1, 3, 5] )->sum() );
		$this->assertEquals( 6, Map::from( [1, 'sum', 5] )->sum() );
	}


	public function testSumPath()
	{
		$this->assertEquals( 90, Map::from( [['p' => 30], ['p' => 50], ['p' => 10]] )->sum( 'p' ) );
		$this->assertEquals( 80, Map::from( [['i' => ['p' => 30]], ['i' => ['p' => 50]]] )->sum( 'i/p' ) );
	}


	public function testTake()
	{
		$this->assertEquals( [1, 2], Map::from( [1, 2, 3, 4] )->take( 2 )->toArray() );
	}


	public function testTakeOffset()
	{
		$this->assertEquals( [1 => 2, 2 => 3], Map::from( [1, 2, 3, 4] )->take( 2, 1 )->toArray() );
	}


	public function testTakeNegativeOffset()
	{
		$this->assertEquals( [2 => 3, 3 => 4], Map::from( [1, 2, 3, 4] )->take( 2, -2 )->toArray() );
	}


	public function testTakeFunction()
	{
		$fcn = function( $item, $key ) {
			return $item < 2;
		};

		$this->assertEquals( [1 => 2, 2 => 3], Map::from( [1, 2, 3, 4] )->take( 2, $fcn )->toArray() );
	}


	public function testTakeException()
	{
		$this->expectException( \InvalidArgumentException::class );
		Map::from( [] )->take( 0, [] );
	}


	public function testTap()
	{
		$map = new Map( [1, 2, 3] );

		$this->assertEquals( 3, $map->tap( function( $map ) {
			return $map->clear();
		} )->count() );
	}


	public function testTimes()
	{
		$this->assertEquals( [0 => 0, 1 => 10, 2 => 20], Map::times( 3, function( $num ) {
			return $num * 10;
		} )->toArray() );
	}


	public function testTimesKeys()
	{
		$this->assertEquals( [0 => 0, 2 => 5, 4 => 10], Map::times( 3, function( $num, &$key ) {
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

		$this->assertEquals( $expected, $r->toArray() );
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

		$this->assertEquals( ['- n1', '-- n2', '-- n3'], $r->toArray() );
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

		$this->assertEquals( ['- n1', '-- n2', '-- n3'], $r->toArray() );
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

		$this->assertEquals( $expected, $r->toArray() );
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
		$this->assertEquals( $expected, $m->tree( 'id', 'pid' )->toArray() );
	}


	public function testToArray()
	{
		$m = new Map( ['name' => 'Hello'] );
		$this->assertEquals( ['name' => 'Hello'], $m->toArray() );
	}


	public function testToJson()
	{
		$m = new Map( ['name' => 'Hello'] );
		$this->assertEquals( '{"name":"Hello"}', $m->toJson() );
	}


	public function testToJsonOptions()
	{
		$m = new Map( ['name', 'Hello'] );
		$this->assertEquals( '{"0":"name","1":"Hello"}', $m->toJson( JSON_FORCE_OBJECT ) );
	}


	public function testToUrl()
	{
		$this->assertEquals( 'a=1&b=2', Map::from( ['a' => 1, 'b' => 2] )->toUrl() );
	}


	public function testToUrlNested()
	{
		$url = Map::from( ['a' => ['b' => 'abc', 'c' => 'def'], 'd' => 123] )->toUrl();
		$this->assertEquals( 'a%5Bb%5D=abc&a%5Bc%5D=def&d=123', $url );
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

		$this->assertEquals( $expected, $m->transpose()->toArray() );
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

		$this->assertEquals( $expected, $m->transpose()->toArray() );
	}


	public function testUasort()
	{
		$m = ( new Map( ['a' => 'foo', 'c' => 'bar-10', 1 => 'bar-1'] ) )->uasort( function( $a, $b ) {
			return strrev( $a ) <=> strrev( $b );
		} );

		$this->assertInstanceOf( Map::class, $m );
		$this->assertEquals( ['c' => 'bar-10', 1 => 'bar-1', 'a' => 'foo'], $m->toArray() );
	}


	public function testUksort()
	{
		$m = ( new Map( ['a' => 'foo', 'c' => 'bar-10', 1 => 'bar-1'] ) )->uksort( function( $a, $b ) {
			return (string) $a <=> (string) $b;
		} );

		$this->assertInstanceOf( Map::class, $m );
		$this->assertEquals( [1 => 'bar-1', 'a' => 'foo', 'c' => 'bar-10'], $m->toArray() );
	}


	public function testUsort()
	{
		$m = ( new Map( ['foo', 'bar-10', 'bar-1'] ) )->usort( function( $a, $b ) {
			return strrev( $a ) <=> strrev( $b );
		} );

		$this->assertInstanceOf( Map::class, $m );
		$this->assertEquals( ['bar-10', 'bar-1', 'foo'], $m->toArray() );
	}


	public function testUnionArray()
	{
		$m = new Map( ['name' => 'Hello'] );
		$r = $m->union( ['id' => 1] );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['name' => 'Hello', 'id' => 1], $r->toArray() );
	}


	public function testUnionMap()
	{
		$m = new Map( ['name' => 'Hello'] );
		$r = $m->union( new Map( ['name' => 'World', 'id' => 1] ) );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['name' => 'Hello', 'id' => 1], $r->toArray() );
	}


	public function testUnique()
	{
		$m = new Map( ['Hello', 'World', 'World'] );
		$r = $m->unique();

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['Hello', 'World'], $r->toArray() );
	}


	public function testUniqueKey()
	{
		$m = new Map( [['p' => '1'], ['p' => 1], ['p' => 2]] );
		$r = $m->unique( 'p' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( [['p' => 1], ['p' => 2]], $r->toArray() );
	}


	public function testUniquePath()
	{
		$m = new Map( [['i' => ['p' => '1']], ['i' => ['p' => 1]]] );
		$r = $m->unique( 'i/p' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( [['i' => ['p' => '1']]], $r->toArray() );
	}


	public function testUnshift()
	{
		$m = ( new Map( ['one', 'two', 'three', 'four'] ) )->unshift( 'zero' );
		$this->assertInstanceOf( Map::class, $m );
		$this->assertEquals( ['zero', 'one', 'two', 'three', 'four'], $m->toArray() );
	}


	public function testUnshiftWithKey()
	{
		$m = ( new Map( ['one' => 1, 'two' => 2] ) )->unshift( 0, 'zero' );
		$this->assertInstanceOf( Map::class, $m );
		$this->assertEquals( ['zero' => 0, 'one' => 1, 'two' => 2], $m->toArray() );
	}


	public function testValues()
	{
		$m = new Map( ['id' => 1, 'name' => 'Hello'] );
		$r = $m->values();

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( [1, 'Hello'], $r->toArray() );
	}


	public function testWalk()
	{
		$m = new Map( ['a', 'B', ['c', 'd'], 'e'] );
		$r = $m->walk( function( &$value ) {
			$value = strtoupper( $value );
		} );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['A', 'B', ['C', 'D'], 'E'], $r->toArray() );
	}


	public function testWalkNonRecursive()
	{
		$m = new Map( ['a', 'B', ['c', 'd'], 'e'] );
		$r = $m->walk( function( &$value ) {
			$value = ( !is_array( $value ) ? strtoupper( $value ) : $value );
		}, null, false );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['A', 'B', ['c', 'd'], 'E'], $r->toArray() );
	}


	public function testWalkData()
	{
		$m = new Map( [1, 2, 3] );
		$r = $m->walk( function( &$value, $key, $data ) {
			$value = $data[$value] ?? $value;
		}, [1 => 'one', 2 => 'two'] );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['one', 'two', 3], $r->toArray() );
	}


	public function testWhere()
	{
		$m = Map::from( [['p' => 10], ['p' => 20], ['p' => 30]] );

		$this->assertInstanceOf( Map::class, $m->where( 'p', '!=', null ) );
		$this->assertEquals( [['p' => 10]], $m->where( 'p', '==', 10 )->toArray() );
		$this->assertEquals( [], $m->where( 'p', '===', '10' )->toArray() );
		$this->assertEquals( [1 => ['p' => 20], 2 => ['p' => 30]], $m->where( 'p', '!=', 10 )->toArray() );
		$this->assertEquals( [['p' => 10], ['p' => 20], ['p' => 30]], $m->where( 'p', '!==', '10' )->toArray() );
		$this->assertEquals( [1 => ['p' => 20], 2 => ['p' => 30]], $m->where( 'p', '>', 10 )->toArray() );
		$this->assertEquals( [['p' => 10], ['p' => 20]], $m->where( 'p', '<', 30 )->toArray() );
		$this->assertEquals( [['p' => 10], ['p' => 20]], $m->where( 'p', '<=', 20 )->toArray() );
		$this->assertEquals( [1 => ['p' => 20], 2 => ['p' => 30]], $m->where( 'p', '>=', 20 )->toArray() );
	}


	public function testWhereBetween()
	{
		$m = Map::from( [['p' => 10], ['p' => 20], ['p' => 30]] );

		$this->assertEquals( [['p' => 10], ['p' => 20]], $m->where( 'p', '-', [10, 20] )->toArray() );
		$this->assertEquals( [['p' => 10]], $m->where( 'p', '-', [10] )->toArray() );
		$this->assertEquals( [['p' => 10]], $m->where( 'p', '-', 10 )->toArray() );
	}


	public function testWhereIn()
	{
		$m = Map::from( [['p' => 10], ['p' => 20], ['p' => 30]] );

		$this->assertEquals( [['p' => 10], 2 => ['p' => 30]], $m->where( 'p', 'in', [10, 30] )->toArray() );
		$this->assertEquals( [['p' => 10]], $m->where( 'p', 'in', 10 )->toArray() );
	}


	public function testWhereNotFound()
	{
		$this->assertEquals( [], Map::from( [['p' => 10]] )->where( 'x', '==', [0] )->toArray() );
	}


	public function testWherePath()
	{
		$m = Map::from( [['item' => ['id' => 3, 'price' => 10]], ['item' => ['id' => 4, 'price' => 50]]] );

		$this->assertEquals( [1 => ['item' => ['id' => 4, 'price' => 50]]], $m->where( 'item/price', '>', 30 )->toArray() );
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

		$this->assertEquals( $expected, $m->zip( $en, $es )->toArray() );
	}
}



class TestMapObject
{
	private static $num = 1;
	private static $prop = 1;

	public function get( $prop )
	{
		return 'p' . self::$prop++;
	}

	public function getCode()
	{
		return self::$num++;
	}

	public function setId( $id )
	{
		return $this;
	}

	public function toArray()
	{
		return ['prop' => 'p' . self::$prop++];
	}
}