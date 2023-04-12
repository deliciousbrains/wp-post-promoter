<?php

namespace DeliciousBrains\WPPromoter;

class Display {

	/**
	 * @param       $file
	 * @param       $name
	 * @param array $deps
	 * @param null  $version
	 * @param bool  $in_footer
	 *
	 * @return mixed
	 */
	public static function enqueue( $file, $name, $deps = array(), $version = null, $in_footer = false ) {
		$parts = explode( '.', $file );
		$ext   = array_pop( $parts );
		$file  = implode( '.', $parts );

		$base = '/build/' . $file . '.' . $ext;

		$path = DBI_PROMOTER_BASE_DIR . $base;
		$src  = DBI_PROMOTER_BASE_URL . $base;

		if ( is_null( $version ) ) {
			$version = filemtime( $path );
		}

		if ( 'js' === $ext ) {
			wp_enqueue_script( $name, $src, $deps, $version, $in_footer );
		} else {
			wp_enqueue_style( $name, $src, $deps, $version );
		}

		return $name;
	}
}
