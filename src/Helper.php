<?php
namespace Motekar\WPLibs;

/**
 * Helper Class
 */
class Helper
{

	public static function view( $path, $args = [] )
	{
		$path = realpath( __DIR__ . "/../resources/views" ) . "/{$path}.php";

		if ( file_exists( $path ) ) {
			extract( $args, EXTR_SKIP );
		 	include( $path );
		} else {
			echo "View file not found: $path";
		}
	}
}
