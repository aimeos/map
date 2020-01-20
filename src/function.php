<?php

if( !function_exists( 'map' ) )
{
	function map( iterable $items = [] )
	{
		return new \Aimeos\Map( $items );
	}
}