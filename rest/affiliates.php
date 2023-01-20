<?php
namespace Sejoli_Rest_Api\Rest;

/**
 * Main class responsible for rest api functions
 * @since   1.0.0
 */
class Affiliates extends \Sejoli_Rest_Api\Rest {

	/**
	 * Register class routes
	 * Hooked via action rest_api_init, priority 10
	 * @since    1.0.0
	 */
	public function do_register() {

		$routes = array(
			'affiliate' => array(
				'endpoint'			  => '/affiliate',
	    		'methods'		      => \WP_REST_Server::READABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_get_affiliate_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	            'args' => array(
					'affiliate_id' => array(
						'type' => 'number',
					),
				)
	    	)
	    );

	    self::register_routes( $routes );
	
	}

	/**
	 * Get affiliates data by user rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_get_affiliate_data( \WP_REST_Request $request ) {

		$_request = wp_parse_args( $request->get_params(), [
			'affiliate_id' => NULL,
		] );

        $params = array(
	        'affiliate_id' => $_request['affiliate_id']
	    );

	    $query = \SejoliSA\Model\Affiliate::reset()
	                ->set_filter_from_array($params);

	    $affiliate = $query->get()->respond();

	    if( ! is_wp_error( $affiliate ) ) {

	    	if( true === $affiliate['valid'] ) :

				return $this->respond_success( true, $affiliate, 'Data successfully found', 200 );

			else:

				return $this->respond_error( 'invalid-data' );

			endif;

		} else {
			
			return $this->respond_error( 'invalid-data' );
		
		}

	}
	
}
