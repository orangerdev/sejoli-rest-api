<?php
namespace Sejoli_Rest_Api\Rest;

/**
 * Main class responsible for rest api functions
 * @since   1.0.0
 */
class Sales extends \Sejoli_Rest_Api\Rest {

	/**
	 * Register class routes
	 * Hooked via action rest_api_init, priority 10
	 * @since    1.0.0
	 */
	public function do_register() {

		$routes = array(
			'sales' => array(
				'endpoint'			  => '/sales',
	    		'methods'		      => 'GET',
	    		'callback'			  => array( $this, 'get_sales_data' ),
	    		'permission_callback' => '__return_true',
	    	),
	    	'sales_detail' => array(
				'endpoint'			  => '/sales/(?P<order_id>\d+)',
	    		'methods'		      => 'GET',
	    		'callback'			  => array( $this, 'get_sales_single_data' ),
	    		'permission_callback' => '__return_true',
	    	),
	    	'sales_by_user' => array(
				'endpoint'			  => '/sales/user/(?P<user_id>\d+)',
	    		'methods'			  => 'GET',
	    		'callback'			  => array( $this, 'get_sales_data_by_user' ),
	    		'permission_callback' => '__return_true',
	    	),
	    	'sales_by_affiliate' => array(
				'endpoint'			  => '/sales/affiliate/(?P<affiliate_id>\d+)',
	    		'methods'			  => 'GET',
	    		'callback'			  => array( $this, 'get_sales_data_by_affiliate' ),
	    		'permission_callback' => '__return_true',
	    	),
	    	'sales_by_product' => array(
				'endpoint'			  => '/sales/product/(?P<product_id>\d+)',
	    		'methods'			  => 'GET',
	    		'callback'			  => array( $this, 'get_sales_data_by_product' ),
	    		'permission_callback' => '__return_true',
	    	),
	    	'sales_by_status' => array(
				'endpoint'			  => '/sales/status/(?P<status>[a-zA-Z0-9-]+)',
	    		'methods'			  => 'GET',
	    		'callback'			  => array( $this, 'get_sales_data_by_status' ),
	    		'permission_callback' => '__return_true',
	    	),
	    	'sales_by_order_type' => array(
				'endpoint'			  => '/sales/type/(?P<order_type>[a-zA-Z0-9-]+)',
	    		'methods'			  => 'GET',
	    		'callback'			  => array( $this, 'get_sales_data_by_order_type' ),
	    		'permission_callback' => '__return_true',
	    	)
	    );

	    self::register_routes( $routes );
	
	}

	/**
	 * Get sales data rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function get_sales_data( $data ) {

		$params = array(
            'product_id'      => NULL,
	        'user_id'         => NULL,
	        'affiliate_id'    => NULL,
	        'coupon_id'       => NULL,
	        'payment_gateway' => NULL,
	        'status'          => NULL,
	        'type'            => NULL
        );

        $order = sejolisa_get_orders($params);

		if( ! is_wp_error( $order ) ) {

			return $order;

		}

		return $this->respond_error();

	}

	/**
	 * Get single sales data rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function get_sales_single_data( $data ) {

		$params = array(
            'ID' => $data['order_id']
        );

        $order = sejolisa_get_order($params);

		if( ! is_wp_error( $order ) ) {

			return $order;

		}

		return $this->respond_error();

	}

	/**
	 * Get sales data by user rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function get_sales_data_by_user( $data ) {

		$params = array(
            'product_id'      => NULL,
	        'user_id'         => $data['user_id'],
	        'affiliate_id'    => NULL,
	        'coupon_id'       => NULL,
	        'payment_gateway' => NULL,
	        'status'          => NULL,
	        'type'            => NULL
        );

        $order = sejolisa_get_orders($params);

		if( ! is_wp_error( $order ) ) {

			return $order;

		}

		return $this->respond_error();

	}

	/**
	 * Get sales data by affiliate rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function get_sales_data_by_affiliate( $data ) {

		$params = array(
            'product_id'      => NULL,
	        'user_id'         => NULL,
	        'affiliate_id'    => $data['affiliate_id'],
	        'coupon_id'       => NULL,
	        'payment_gateway' => NULL,
	        'status'          => NULL,
	        'type'            => NULL
        );

        $order = sejolisa_get_orders($params);

		if( ! is_wp_error( $order ) ) {

			return $order;

		}

		return $this->respond_error();

	}

	/**
	 * Get sales data by product rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function get_sales_data_by_product( $data ) {

		$params = array(
            'product_id'      => $data['product_id'],
	        'user_id'         => NULL,
	        'affiliate_id'    => NULL,
	        'coupon_id'       => NULL,
	        'payment_gateway' => NULL,
	        'status'          => NULL,
	        'type'            => NULL
        );

        $order = sejolisa_get_orders($params);

		if( ! is_wp_error( $order ) ) {

			return $order;

		}

		return $this->respond_error();

	}

	/**
	 * Get sales data by status rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function get_sales_data_by_status( $data ) {

		$params = array(
            'product_id'      => NULL,
	        'user_id'         => NULL,
	        'affiliate_id'    => NULL,
	        'coupon_id'       => NULL,
	        'payment_gateway' => NULL,
	        'status'          => $data['status'],
	        'type'            => NULL
        );

        $order = sejolisa_get_orders($params);

		if( ! is_wp_error( $order ) ) {

			return $order;

		}

		return $this->respond_error();

	}

	/**
	 * Get sales data by status rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function get_sales_data_by_order_type( $data ) {

		$params = array(
            'product_id'      => NULL,
	        'user_id'         => NULL,
	        'affiliate_id'    => NULL,
	        'coupon_id'       => NULL,
	        'payment_gateway' => NULL,
	        'status'          => NULL,
	        'type'            => $data['order_type']
        );

        $order = sejolisa_get_orders($params);

		if( ! is_wp_error( $order ) ) {

			return $order;

		}

		return $this->respond_error();

	}

}
