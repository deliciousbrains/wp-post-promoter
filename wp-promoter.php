<?php
/**
 * Plugin Name: Delicious Brains WP Promoter
 * Plugin URI: https://deliciousbrains.com
 * Description: WordPress must-use plugin for promoting posts via email and social media.
 * Author: Delicious Brains
 * Version: 1.0
 * Author URI: https://deliciousbrains.com
 **/

define( 'DBI_PROMOTER_BASE_DIR', WPMU_PLUGIN_DIR . '/' . basename( __DIR__ ) );
define( 'DBI_PROMOTER_BASE_URL', WPMU_PLUGIN_URL . '/' . basename( __DIR__ ) );

if ( is_admin() || defined( 'DOING_CRON' ) ) {
	( new \DeliciousBrains\WPPromoter\Email() )->init();
	( new \DeliciousBrains\WPPromoter\Social() )->init();
}
