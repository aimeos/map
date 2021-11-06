<?php


if( !function_exists( 'is_map' ) )
{
	/**
	 * Checks if the given variable is a Map object.
	 *
	 * @param mixed $var Variable to check for
	 * @return bool TRUE if the variable is a Map object, FALSE if not
	 */
	function is_map( $var ) : bool
	{
		return $var instanceof \Aimeos\Map;
	}
}


if( !function_exists( 'map' ) )
{
	/**
	 * Wraps the given variable into a Map object if necessary.
	 *
	 * @param mixed $items Variable to wrap into a Map object
	 * @return \Aimeos\Map<int|string,mixed> Map object
	 */
	function map( $items = [] ) : \Aimeos\Map
	{
		if( $items instanceof \Aimeos\Map ) {
			return $items;
		}

		return new \Aimeos\Map( $items );
	}
}