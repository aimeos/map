<?php

if( !function_exists( 'map' ) )
{
	function map( iterable $items )
	{
		return \Aimeos\Map::from( $items );
	}
}