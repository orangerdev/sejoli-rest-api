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
	    		'methods'		      => \WP_REST_Server::CREATABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_get_sales_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	            'args' => array(
					'start' => array(
						'type' => 'number',
					),
					'length' => array(
						'type' => 'number',
					),
					'sort' => array(
						'type' => 'string',
					),
	                'sort_by' => array(
						'type' => 'string',
					),
					'item_status' => array(
						'type' => 'string',
					),
	                'search' => array(
						'type' => 'string',
					),
					'product_id' => array(
						'type' => 'number',
					),
					'user_id' => array(
						'type' => 'number',
					),
					'affiliate_id' => array(
						'type' => 'number',
					),
					'coupon_id' => array(
						'type' => 'number',
					),
					'payment_gateway' => array(
						'type' => 'string',
					),
					'status' => array(
						'type' => 'string',
					),
					'type' => array(
						'type' => 'string',
					)
				)
	    	),
	    	'sales_detail' => array(
				'endpoint'			  => '/sales/detail',
	    		'methods'		      => \WP_REST_Server::READABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_get_sales_single_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	            'args' => array(
					'order_id' => array(
						'type' => 'number',
					),
				)
	    	),
	    );

	    self::register_routes( $routes );
	
	}

	/**
	 * Get sales data rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_get_sales_data( \WP_REST_Request $request ) {

		$_request = wp_parse_args( $request->get_body_params(), [
			'start'  		  => NULL,
	        'length' 		  => NULL,
	        'order'  		  => NULL,
	        'filter' 		  => NULL,
	        'product_id'      => NULL,
	        'user_id'         => NULL,
	        'affiliate_id'    => NULL,
	        'coupon_id'       => NULL,
	        'payment_gateway' => NULL,
	        'status'          => NULL,
	        'type'            => NULL
		] );

		$args = array(
            'product_id'      => $_request['product_id'],
	        'user_id'         => $_request['user_id'],
	        'affiliate_id'    => $_request['affiliate_id'],
	        'coupon_id'       => $_request['coupon_id'],
	        'payment_gateway' => $_request['payment_gateway'],
	        'status'          => $_request['status'],
	        'type'            => $_request['type']
        );

        $current_user = wp_get_current_user();

	    if ( isset( $current_user->ID ) && $current_user->ID > 0 && $_request['user_id'] === NULL ) :
	        $args['user_id'] = $current_user->ID;
	    endif;

        $order = sejolisa_get_orders( $args, $_request );

		if( ! is_wp_error( $order ) && true === $order['valid'] ) :

			return $this->respond_success( true, $order, 'Data successfully found', 200 );

		else:
			
			return $this->respond_error( 'invalid-data' );
		
		endif;

	}

	/**
	 * Get single sales data rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_get_sales_single_data( \WP_REST_Request $request ) {

		$_request = wp_parse_args( $request->get_params(), [
			'order_id' => NULL,
		] );

		$params = array(
            'ID' => $_request['order_id']
        );

        $order = sejolisa_get_order( $params );

		if( ! is_wp_error( $order ) && true === $order['valid'] ) :

			return $this->respond_success( true, $order, 'Data successfully found', 200 );

		else:
			
			return $this->respond_error( 'invalid-data' );
		
		endif;

	}	

}