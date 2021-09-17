<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://sejoli.co.id
 * @since      1.0.0
 *
 * @package    Sejoli_Rest_Api
 * @subpackage Sejoli_Rest_Api/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Sejoli_Rest_Api
 * @subpackage Sejoli_Rest_Api/includes
 * @author     Sejoli Team <admin@sejoli.co.id>
 */
class Sejoli_Rest_Api {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Sejoli_Rest_Api_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'SEJOLI_REST_API_VERSION' ) ) {
			$this->version = SEJOLI_REST_API_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'sejoli-rest-api';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_rest_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Sejoli_Rest_Api_Loader. Orchestrates the hooks of the plugin.
	 * - Sejoli_Rest_Api_i18n. Defines internationalization functionality.
	 * - Sejoli_Rest_Api_Admin. Defines all hooks for the admin area.
	 * - Sejoli_Rest_Api_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once SEJOLI_REST_API_DIR . 'includes/class-sejoli-rest-api-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once SEJOLI_REST_API_DIR . 'includes/class-sejoli-rest-api-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the REST API
		 * side of the site.
		 */
		require_once SEJOLI_REST_API_DIR . 'rest/main.php';
		require_once SEJOLI_REST_API_DIR . 'rest/products.php';
		require_once SEJOLI_REST_API_DIR . 'rest/sales.php';

		/**
		 * The class responsible for defining CLI command and function
		 * side of the site.
		 */
		require_once SEJOLI_REST_API_DIR . 'cli/class-sejoli-rest-api-main.php';

		/**
		 * The class responsible for defining API related functions.
		 */
		require_once SEJOLI_REST_API_DIR . 'includes/class-sejoli-rest-api-api.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once SEJOLI_REST_API_DIR . 'admin/class-sejoli-rest-api-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once SEJOLI_REST_API_DIR . 'public/class-sejoli-rest-api-public.php';

		$this->loader = new Sejoli_Rest_Api_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Sejoli_Rest_Api_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Sejoli_Rest_Api_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$admin = new Sejoli_Rest_Api\Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$public = new Sejoli_Rest_Api\Front( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the REST-API functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_rest_hooks() {

		$products = new Sejoli_Rest_Api\Rest\Products();
		$this->loader->add_action( 'rest_api_init',	$products, 'do_register', 10 );

		$sales = new Sejoli_Rest_Api\Rest\Sales();
		$this->loader->add_action( 'rest_api_init',	$sales, 'do_register', 10 );

		// $store_courier = new SejoliCOD\Rest\StoreCourier();
		// $this->loader->add_action( 'rest_api_init',	$store_courier, 'do_register', 10 );

		// $order = new SejoliCOD\Rest\Order();
		// $this->loader->add_action( 'rest_api_init',	$order, 'do_register', 10 );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Sejoli_Rest_Api_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
