<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://sejoli.co.id
 * @since      1.0.0
 *
 * @package    Sejoli_Rest_Api
 * @subpackage Sejoli_Rest_Api/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Sejoli_Rest_Api
 * @subpackage Sejoli_Rest_Api/includes
 * @author     Sejoli Team <admin@sejoli.co.id>
 */
class Sejoli_Rest_Api_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'sejoli-rest-api',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
