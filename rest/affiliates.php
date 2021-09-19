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
			'affiliate_by_user' => array(
				'endpoint'			  => '/affiliate/user/(?P<user_id>\d+)',
	    		'methods'			  => 'GET',
	    		'callback'			  => array( $this, 'get_affiliate_data_by_user' ),
	    		'permission_callback' => '__return_true',
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
	public function get_affiliate_data_by_user( $data ) {

        $params = array(
	        'affiliate_id' => $data['user_id']
	    );

	    $query = \SejoliSA\Model\Affiliate::reset()
	                ->set_filter_from_array($params);

	    $affiliate = $query->get()->respond();

	    if( ! is_wp_error( $affiliate ) ) {

			return wp_parse_args($affiliate,[
		        'valid'    => false,
		        'messages' => []
		    ]);

		}

		return $this->respond_error();

	}
	
}
