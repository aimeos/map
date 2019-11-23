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
		$this->assertInstanceOf( Map::class, \map( [] ) );
	}


	public function testClear()
	{
		$m = new Map( ['foo', 'bar'] );
		$this->assertInstanceOf( Map::class, $m->clear() );
	}


	public function testCol()
	{
		$map = new Map( [['foo' => 'one', 'bar' => 'two']] );
		$secondMap = $map->col( 'bar' );

		$this->assertInstanceOf( Map::class, $secondMap );
		$this->assertEquals( [0 => 'two'], $secondMap->toArray() );
	}


	public function testColIndex()
	{
		$map = new Map( [['foo' => 'one', 'bar' => 'two']] );
		$secondMap = $map->col( 'bar', 'foo' );

		$this->assertInstanceOf( Map::class, $secondMap );
		$this->assertEquals( ['one' => 'two'], $secondMap->toArray() );
	}


	public function testConcatWithArray()
	{
		$expected = [
			0 => 4,
			1 => 5,
			2 => 6,
			3 => 'a',
			4 => 'b',
			5 => 'c',
			6 => 'Jonny',
			7 => 'from',
			8 => 'Laroe',
			9 => 'Jonny',
			10 => 'from',
			11 => 'Laroe',
		];

		$map = new Map( [4, 5, 6] );
		$map = $map->concat( ['a', 'b', 'c'] );
		$map = $map->concat( ['who' => 'Jonny', 'preposition' => 'from', 'where' => 'Laroe'] );
		$actual = $map->concat( ['who' => 'Jonny', 'preposition' => 'from', 'where' => 'Laroe'] );

		$this->assertInstanceOf( Map::class, $actual );
		$this->assertSame( $expected, $actual->toArray() );
	}


	public function testConcatWithMap()
	{
		$expected = [
			0 => 4,
			1 => 5,
			2 => 6,
			3 => 'a',
			4 => 'b',
			5 => 'c',
			6 => 'Jonny',
			7 => 'from',
			8 => 'Laroe',
			9 => 'Jonny',
			10 => 'from',
			11 => 'Laroe',
		];

		$firstMap = new Map( [4, 5, 6] );
		$secondMap = new Map( ['a', 'b', 'c'] );
		$thirdMap = new Map( ['who' => 'Jonny', 'preposition' => 'from', 'where' => 'Laroe'] );
		$firstMap = $firstMap->concat( $secondMap );
		$firstMap = $firstMap->concat( $thirdMap );
		$actual = $firstMap->concat( $thirdMap );

		$this->assertInstanceOf( Map::class, $actual );
		$this->assertSame( $expected, $actual->toArray() );
	}


	public function testConstruct()
	{
		$map = new Map;
		$this->assertEmpty( $map->toArray() );
	}


	public function testConstructFromMap()
	{
		$firstMap = new Map( ['foo' => 'bar'] );
		$secondMap = new Map( $firstMap );

		$this->assertInstanceOf( Map::class, $firstMap );
		$this->assertInstanceOf( Map::class, $secondMap );
		$this->assertEquals( ['foo' => 'bar'], $secondMap->toArray() );
	}


	public function testConstructFromArray()
	{
		$map = new Map( ['foo' => 'bar'] );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertEquals( ['foo' => 'bar'], $map->toArray() );
	}


	public function testConstructFromTraversable()
	{
		$map = new Map( new \ArrayObject( [1, 2, 3] ) );
		$this->assertEquals( [1, 2, 3], $map->toArray() );
	}


	public function testConstructFromTraversableWithKeys()
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


	public function testEach()
	{
		$m = new Map( $original = [1, 2, 'foo' => 'bar', 'bam' => 'baz'] );

		$result = [];
		$r = $m->each( function( $item, $key ) use ( &$result ) {
			$result[$key] = $item;
		} );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( $original, $result );

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


	public function testEqualsKeys()
	{
		$map = new Map( ['foo' => 1, 'bar' => '2'] );

		$this->assertTrue( $map->equals( ['foo' => '1', 'bar' => 2], true ) );
		$this->assertFalse( $map->equals( ['0' => 1, '1' => '2'], true ) );
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


	public function testEqualsMoreKeys()
	{
		$map = new Map( ['foo' => 'one', 'bar' => 'two'] );
		$this->assertFalse( $map->equals( ['foo' => 'one', 'bar' => 'two', 'baz' => 'three'], true ) );
	}


	public function testFilter()
	{
		$m = new Map( [['id' => 1, 'name' => 'Hello'], ['id' => 2, 'name' => 'World']] );
		$this->assertEquals( [1 => ['id' => 2, 'name' => 'World']], $m->filter( function( $item ) {
			return $item['id'] == 2;
		} )->toArray() );

		$m = new Map( ['', 'Hello', '', 'World'] );
		$r = $m->filter();
		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['Hello', 'World'], $r->values()->toArray() );

		$m = new Map( ['id' => 1, 'first' => 'Hello', 'second' => 'World'] );
		$this->assertEquals( ['first' => 'Hello', 'second' => 'World'], $m->filter( function( $item, $key ) {
			return $key != 'id';
		} )->toArray() );
	}


	public function testFirstReturnsFirstItemInMap()
	{
		$m = new Map( ['foo', 'bar'] );
		$this->assertEquals( 'foo', $m->first() );
	}


	public function testFirstWithCallback()
	{
		$data = new Map( ['foo', 'bar', 'baz'] );
		$result = $data->first( function( $value ) {
			return $value === 'bar';
		} );
		$this->assertEquals( 'bar', $result );
	}


	public function testFirstWithCallbackAndDefault()
	{
		$data = new Map( ['foo', 'bar'] );
		$result = $data->first( function( $value ) {
			return $value === 'baz';
		}, 'default' );
		$this->assertEquals( 'default', $result );
	}


	public function testFirstWithDefaultAndWithoutCallback()
	{
		$data = new Map;
		$result = $data->first( null, 'default' );
		$this->assertEquals( 'default', $result );
	}


	public function testFromMap()
	{
		$firstMap = Map::from( ['foo' => 'bar'] );
		$secondMap = Map::from( $firstMap );

		$this->assertInstanceOf( Map::class, $firstMap );
		$this->assertInstanceOf( Map::class, $secondMap );
		$this->assertEquals( ['foo' => 'bar'], $secondMap->toArray() );
	}


	public function testFromArray()
	{
		$map = Map::from( ['foo' => 'bar'] );

		$this->assertInstanceOf( Map::class, $map );
		$this->assertEquals( ['foo' => 'bar'], $map->toArray() );
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


	public function testGetWithNullReturnsNull()
	{
		$map = new Map( [1, 2, 3] );
		$this->assertNull( $map->get( null ) );
	}


	public function testHas()
	{
		$data = new Map( ['id' => 1, 'first' => 'Hello', 'second' => 'World'] );

		$this->assertTrue( $data->has( 'first' ) );
		$this->assertFalse( $data->has( 'third' ) );
	}


	public function testIn()
	{
		$this->assertTrue( Map::from( ['a', 'b'] )->in( 'a' ) );
		$this->assertFalse( Map::from( ['a', 'b'] )->in( 'x' ) );
		$this->assertFalse( Map::from( ['1', '2'] )->in( 2, true ) );
	}


	public function testIntersec()
	{
		$m = new Map( ['id' => 1, 'first_word' => 'Hello'] );
		$i = new Map( ['first_world' => 'Hello', 'last_word' => 'World'] );
		$r = $m->intersect( $i );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['first_word' => 'Hello'], $r->toArray() );
	}


	public function testIntersecCallback()
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


	public function testIntersecAssocCallback()
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


	public function testIntersecKeysCallback()
	{
		$m = new Map( ['id' => 1, 'first_word' => 'Hello', 'last_word' => 'World'] );
		$i = new Map( ['First_word' => 'Hello', 'last_word' => 'world'] );
		$r = $m->intersectKeys( $i, 'strcasecmp' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertEquals( ['first_word' => 'Hello', 'last_word' => 'World'], $r->toArray() );
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


	public function testKsort()
	{
		$data = ( new Map( ['b' => 'me', 'a' => 'test'] ) )->ksort();

		$this->assertInstanceOf( Map::class, $data );
		$this->assertSame( ['a' => 'test', 'b' => 'me'], $data->toArray() );
	}


	public function testLastReturnsLastItemInMap()
	{
		$m = new Map( ['foo', 'bar'] );
		$this->assertEquals( 'bar', $m->last() );
	}


	public function testLastWithCallback()
	{
		$data = new Map( [100, 200, 300] );
		$result = $data->last( function( $value ) {
			return $value < 250;
		} );
		$this->assertEquals( 200, $result );
		$result = $data->last( function( $value, $key ) {
			return $key < 2;
		} );
		$this->assertEquals( 200, $result );
	}


	public function testLastWithCallbackAndDefault()
	{
		$data = new Map( ['foo', 'bar'] );
		$result = $data->last( function( $value ) {
			return $value === 'baz';
		}, 'default' );
		$this->assertEquals( 'default', $result );
	}


	public function testLastWithDefaultAndWithoutCallback()
	{
		$data = new Map;
		$result = $data->last( null, 'default' );
		$this->assertEquals( 'default', $result );
	}


	public function testMap()
	{
		$data = new Map( ['first' => 'test', 'last' => 'user'] );
		$data = $data->map( function( $item, $key ) {
			return $key . '-' . strrev( $item );
		} );

		$this->assertInstanceOf( Map::class, $data );
		$this->assertEquals( ['first' => 'first-tset', 'last' => 'last-resu'], $data->toArray() );
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


	public function testMethod()
	{
		Map::method( 'foo', function() {
			return $this->filter( function( $item ) {
				return strpos( $item, 'a' ) === 0;
			})
				->unique()
				->values();
		} );

		$m = new Map( ['a', 'a', 'aa', 'aaa', 'bar'] );

		$this->assertSame( ['a', 'aa', 'aaa'], $m->foo()->toArray() );
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


	public function testOffsetAccess()
	{
		$m = new Map( ['name' => 'test'] );
		$this->assertEquals( 'test', $m['name'] );

		$m['name'] = 'me';
		$this->assertEquals( 'me', $m['name'] );
		$this->assertTrue( isset( $m['name'] ) );

		unset( $m['name'] );
		$this->assertFalse( isset( $m['name'] ) );

		$m[] = 'jason';
		$this->assertEquals( 'jason', $m[0] );
	}


	public function testOffsetExists()
	{
		$m = new Map( ['foo', 'bar'] );

		$this->assertTrue( $m->offsetExists( 0 ) );
		$this->assertTrue( $m->offsetExists( 1 ) );
		$this->assertFalse( $m->offsetExists( 1000 ) );
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

		$m->offsetSet( null, 'qux' );
		$this->assertEquals( 'qux', $m[2] );
	}


	public function testOffsetUnset()
	{
		$m = new Map( ['foo', 'bar'] );

		$m->offsetUnset( 1 );
		$this->assertFalse( isset( $m[1] ) );
	}


	public function testPipe()
	{
		$map = new Map( [1, 2, 3] );

		$this->assertEquals( 3, $map->pipe( function( $map ) {
			return $map->last();
		} ) );
	}


	public function testPopReturnsAndRemovesLastItemInMap()
	{
		$m = new Map( ['foo', 'bar'] );

		$this->assertEquals( 'bar', $m->pop() );
		$this->assertEquals( 'foo', $m->first() );
		$this->assertEquals( 1, $m->count() );
	}


	public function testPullRetrievesItemFromMap()
	{
		$m = new Map( ['foo', 'bar'] );

		$this->assertEquals( 'foo', $m->pull( 0 ) );
	}


	public function testPullRemovesItemFromMap()
	{
		$m = new Map( ['foo', 'bar'] );
		$m->pull( 0 );
		$this->assertEquals( [1 => 'bar'], $m->toArray() );
	}


	public function testPullReturnsDefault()
	{
		$m = new Map( [] );
		$value = $m->pull( 0, 'foo' );
		$this->assertEquals( 'foo', $value );
	}


	public function testPush()
	{
		$m = ( new Map( [] ) )->push( 'foo' );

		$this->assertInstanceOf( Map::class, $m );
		$this->assertEquals( ['foo'], $m->toArray() );
	}


	public function testReduce()
	{
		$m = new Map( [1, 2, 3] );
		$this->assertEquals( 6, $m->reduce( function( $carry, $element ) {
			return $carry += $element;
		} ) );
	}


	public function testRemoveSingleKey()
	{
		$m = new Map( ['foo', 'bar'] );
		$r = $m->remove( 0 );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertFalse( isset( $m['foo'] ) );

		$m = new Map( ['foo' => 'bar', 'baz' => 'qux'] );
		$r = $m->remove( 'foo' );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertFalse( isset( $m['foo'] ) );
	}


	public function testRemoveArrayOfKeys()
	{
		$m = new Map( ['foo', 'bar', 'baz'] );
		$r = $m->remove( [0, 2] );

		$this->assertInstanceOf( Map::class, $r );
		$this->assertFalse( isset( $m[0] ) );
		$this->assertFalse( isset( $m[2] ) );
		$this->assertTrue( isset( $m[1] ) );

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
		$data = new Map( ['hello', 'world'] );
		$reversed = $data->reverse();

		$this->assertInstanceOf( Map::class, $reversed );
		$this->assertSame( [1 => 'world', 0 => 'hello'], $reversed->toArray() );

		$data = new Map( ['name' => 'test', 'last' => 'user'] );
		$reversed = $data->reverse();

		$this->assertInstanceOf( Map::class, $reversed );
		$this->assertSame( ['last' => 'user', 'name' => 'test'], $reversed->toArray() );
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


	public function testSearchReturnsNullWhenItemIsNotFound()
	{
		$m = new Map( [1, 2, 3, 4, 5, 'foo' => 'bar'] );

		$this->assertNull( $m->search( 6 ) );
		$this->assertNull( $m->search( 'foo' ) );
		$this->assertNull( $m->search( function( $value ) {
			return $value < 1 && is_numeric( $value );
		} ) );
		$this->assertNull( $m->search( function( $value ) {
			return $value == 'nope';
		} ) );
	}


	public function testSetAddsItemToMap()
	{
		$map = new Map;
		$this->assertSame( [], $map->toArray() );

		$r = $map->set( 'foo', 1 );
		$this->assertInstanceOf( Map::class, $map );
		$this->assertSame( ['foo' => 1], $map->toArray() );

		$r = $map->set( 'bar', ['nested' => 'two'] );
		$this->assertInstanceOf( Map::class, $map );
		$this->assertSame( ['foo' => 1, 'bar' => ['nested' => 'two']], $map->toArray() );

		$r = $map->set( 'foo', 3 );
		$this->assertInstanceOf( Map::class, $map );
		$this->assertSame( ['foo' => 3, 'bar' => ['nested' => 'two']], $map->toArray() );
	}


	public function testShiftReturnsAndRemovesFirstItemInMap()
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
		$this->assertNotEquals( $firstRandom, $secondRandom );
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


	public function testSort()
	{
		$data = ( new Map( [5, 3, 1, 2, 4] ) )->sort();
		$this->assertInstanceOf( Map::class, $data );
		$this->assertEquals( [1, 2, 3, 4, 5], $data->values()->toArray() );

		$data = ( new Map( [-1, -3, -2, -4, -5, 0, 5, 3, 1, 2, 4] ) )->sort();
		$this->assertInstanceOf( Map::class, $data );
		$this->assertEquals( [-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], $data->values()->toArray() );

		$data = ( new Map( ['foo', 'bar-10', 'bar-1'] ) )->sort();
		$this->assertInstanceOf( Map::class, $data );
		$this->assertEquals( ['bar-1', 'bar-10', 'foo'], $data->values()->toArray() );
	}


	public function testSplice()
	{
		$data = new Map( ['foo', 'baz'] );
		$data->splice( 1 );

		$this->assertInstanceOf( Map::class, $data );
		$this->assertEquals( ['foo'], $data->toArray() );

		$data = new Map( ['foo', 'baz'] );
		$data->splice( 1, 0, 'bar' );

		$this->assertInstanceOf( Map::class, $data );
		$this->assertEquals( ['foo', 'bar', 'baz'], $data->toArray() );

		$data = new Map( ['foo', 'baz'] );
		$data->splice( 1, 1 );

		$this->assertInstanceOf( Map::class, $data );
		$this->assertEquals( ['foo'], $data->toArray() );

		$data = new Map( ['foo', 'baz'] );
		$cut = $data->splice( 1, 1, 'bar' );

		$this->assertInstanceOf( Map::class, $data );
		$this->assertInstanceOf( Map::class, $cut );
		$this->assertEquals( ['foo', 'bar'], $data->toArray() );
		$this->assertEquals( ['baz'], $cut->toArray() );
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


	public function testUnshift()
	{
		$m = ( new Map( ['one', 'two', 'three', 'four'] ) )->unshift( 'zero' );
		$this->assertInstanceOf( Map::class, $m );
		$this->assertEquals( ['zero', 'one', 'two', 'three', 'four'], $m->toArray() );

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
}



class TestMapObject
{
	private static $num = 1;

	public function setId( $id )
	{
		return $this;
	}

	public function getCode()
	{
		return self::$num++;
	}
}