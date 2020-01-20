<?php


if( !function_exists( 'is_map' ) )
{
	function is_map( $var ) : bool
	{
		return $var instanceof \Aimeos\Map;
	}
}


if( !function_exists( 'map' ) )
{
	function map( iterable $items = [] ) : \Aimeos\Map
	{
		return new \Aimeos\Map( $items );
	}
}