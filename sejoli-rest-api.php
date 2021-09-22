<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://sejoli.co.id
 * @since             1.0.0
 * @package           Sejoli_Rest_Api
 *
 * @wordpress-plugin
 * Plugin Name:       Sejoli Rest API
 * Plugin URI:        https://sejoli.co.id
 * Description:       Provide REST-API data from Sejoli (Premium Membership Plugin)
 * Version:           1.0.0
 * Author:            Sejoli Team
 * Author URI:        https://sejoli.co.id
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       sejoli-rest-api
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

global $sejoli_rest_api;

$sejoli_rest_api = new \stdClass;

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SEJOLI_REST_API_VERSION', '1.0.0' );
define( 'SEJOLI_REST_API_ENV', 'development'); // value can be `development` or `production`
define( 'SEJOLI_REST_API_DIR', plugin_dir_path(__FILE__));
define( 'SEJOLI_REST_API_URL', plugin_dir_url(__FILE__));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-sejoli-rest-api-activator.php
 */
function activate_sejoli_rest_api() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sejoli-rest-api-activator.php';
	Sejoli_Rest_Api_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-sejoli-rest-api-deactivator.php
 */
function deactivate_sejoli_rest_api() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sejoli-rest-api-deactivator.php';
	Sejoli_Rest_Api_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_sejoli_rest_api' );
register_deactivation_hook( __FILE__, 'deactivate_sejoli_rest_api' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-sejoli-rest-api.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_sejoli_rest_api() {

	$plugin = new Sejoli_Rest_Api();
	$plugin->run();

}
run_sejoli_rest_api();

if(!function_exists('__sejoli_rest_api_debug')) :
	function __sejoli_rest_api_debug() {
		$bt     = debug_backtrace();
		$caller = array_shift($bt);
		$args   = [
			"file"  => $caller["file"],
			"line"  => $caller["line"],
			"args"  => func_get_args()
		];

		if ( class_exists( 'WP_CLI' ) || class_exists( 'WP_UnitTestCase') ) :
?>
			<pre><?php print_r($args); ?></pre>
<?php
		else :
			do_action('qm/info', $args);
		endif;
	}
endif;

if(!function_exists('__sejoli_rest_api_print_debug')) :
	function __sejoli_rest_api_print_debug() {
		$bt     = debug_backtrace();
		$caller = array_shift($bt);
		$args   = [
			"file"  => $caller["file"],
			"line"  => $caller["line"],
			"args"  => func_get_args()
		];

		if('production' !== SEJOLI_REST_API_ENV) :
?>
			<pre><?php print_r($args); ?></pre>
<?php
		endif;
	}
endif;
