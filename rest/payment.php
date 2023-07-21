<?php
namespace Sejoli_Rest_Api\Rest;

/**
 * Main class responsible for rest api functions
 * @since   1.0.0
 */
class Payment extends \Sejoli_Rest_Api\Rest {

	/**
	 * Register class routes
	 * Hooked via action rest_api_init, priority 10
	 * @since    1.0.0
	 */
	public function do_register() {

		$routes = array(
			'payment' => array(
				'endpoint'			  => '/payment',
	    		'methods'		      => \WP_REST_Server::READABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_get_payment_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            }
	    	)
	    );

	    self::register_routes( $routes );
	
	}

	/**
	 * Get payment option data rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_get_payment_data( \WP_REST_Request $request ) {

  		$request = wp_parse_args( $request, []);

        $response = [];

        $response['payment_gateway'] = apply_filters( 'sejoli/frontend/checkout/payment-gateway', [], $request );

		if( !empty( $response ) ) :

			return $this->respond_success( true, $response, 'Data successfully found', 200 );

		else:
			
			return $this->respond_error( 'invalid-data' );
		
		endif;

	}	

}