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
	function map( $items = [] ) : \Aimeos\Map
	{
		if( $items instanceof \Aimeos\Map ) {
			return $items;
		}

		return new \Aimeos\Map( $items );
	}
}