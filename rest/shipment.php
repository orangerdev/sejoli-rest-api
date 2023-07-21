<?php
namespace Sejoli_Rest_Api\Rest;

/**
 * Main class responsible for rest api functions
 * @since   1.0.0
 */
class Shipment extends \Sejoli_Rest_Api\Rest {

	/**
	 * Register class routes
	 * Hooked via action rest_api_init, priority 10
	 * @since    1.0.0
	 */
	public function do_register() {

		$routes = array(
			'shipment' => array(
				'endpoint'			  => '/shipment',
	    		'methods'		      => \WP_REST_Server::CREATABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_get_shipment_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	            'args' => array(
					'product_id' => array(
						'type' => 'number',
					),
					'district_id' => array(
						'type' => 'number',
					),
					'quantity' => array(
						'type' => 'number',
					),
					'shipment' => array(
						'type' => 'string',
					),
					'variants' => array(
						'type' => 'string',
					)
				)
	    	)
	    );

	    self::register_routes( $routes );
	
	}

	/**
	 * Get shipment data rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_get_shipment_data( \WP_REST_Request $request ) {

  		$_request = wp_parse_args( $request->get_body_params(), [
            'product_id'  => NULL,
            'district_id' => NULL,
            'quantity'	  => 1,
            'shipment'    => NULL,
            'variants'    => [],
        ]);

        $response = [];

        $response = apply_filters( 'sejoli/frontend/checkout/shipping-methods', [], $_request );

		if( !empty( $response ) ) :

			return $this->respond_success( true, $response, 'Data successfully found', 200 );

		else:
			
			return $this->respond_error( 'invalid-data' );
		
		endif;

	}	

}