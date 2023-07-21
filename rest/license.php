<?php
namespace Sejoli_Rest_Api\Rest;

/**
 * Main class responsible for rest api functions
 * @since   1.0.0
 */
class License extends \Sejoli_Rest_Api\Rest {

	/**
	 * Register class routes
	 * Hooked via action rest_api_init, priority 10
	 * @since    1.0.0
	 */
	public function do_register() {

		$routes = array(
			'license_input' => array(
				'endpoint'			  => '/license/input',
	    		'methods'		      => \WP_REST_Server::CREATABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_input_license_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	            'args' => array(
					'user_email' => array(
						'type' => 'string',
					),
					'license' => array(
						'type' => 'string',
					),
				)
	    	),
	    	'license_validate' => array(
				'endpoint'			  => '/license/validate',
	    		'methods'		      => \WP_REST_Server::CREATABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_validate_license_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	            'args' => array(
					'user_email' => array(
						'type' => 'string',
					),
					'user_pass' => array(
						'type' => 'string',
					),
					'license' => array(
						'type' => 'string',
					),
				)
	    	),
	    	'license' => array(
				'endpoint'			  => '/license',
	    		'methods'		      => \WP_REST_Server::READABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_get_license_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	            'args' => array(
					'user_id' => array(
						'type' => 'number',
					),
					'status' => array(
						'type' => 'string',
					)
				)
	    	),
	    );

	    self::register_routes( $routes );
	
	}

	/**
	 * Input license data rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_input_license_data( \WP_REST_Request $request ) {

		$_request = wp_parse_args( $request->get_body_params(), [
			'user_email' => NULL,
			'license'	 => NULL
		] );

		$response = array(
			'valid'   => false,
			'message' => __('Terjadi kesalahan di sistem', 'sejoli-rest-api')
		);

		$post_data = array(
            'user_email' => $_request['user_email'],
			'license'	 => $_request['license'],
			'string'	 => $_SERVER['HTTP_HOST']
        );

		$request_url = 'https://member.sejoli.co.id/sejoli-license/';
		$response    =  wp_remote_post($request_url, array(
							'timeout' => 120,
							'body'    => $post_data
					    ));

		if( is_wp_error( $response ) ) :

			return $this->respond_error( 'invalid-response', $response->get_error_messages() );

		else :

			$json_result   = json_decode(wp_remote_retrieve_body( $response ), true);
			$response_code = intval(wp_remote_retrieve_response_code( $response ));

			if( 200 === $response_code ) :

				if( isset( $json_result['valid'] ) && true === boolval( $json_result['valid'] ) ) :

					update_option('_sejoli_license_check', $json_result);

					$theme_option_url = add_query_arg([
						'page'	  => 'crb_carbon_fields_container_sejoli.php',
						'success' => 'license-valid'
					], admin_url('admin.php'));

					$response = [
	                    'valid'  => true,
	                    'info'   => 'license-valid',
	                    'redirect_link' => $theme_option_url,
	                    'result' => $json_result
	                ];

	                return $this->respond_success( true, $response, 'Register license successfuly.', 200 );

				else :

					$args             = array();
					$args['page']     = 'sejoli-license-form';
					$args['error']	  = 'license-not-valid';
					$args['messages'] = array_map('urlencode', array_map('strip_tags', $json_result['messages']));

					$response = [
	                    'valid'  => false,
	                    'info'   => 'license-not-valid',
	                    'redirect_link' => add_query_arg($args, admin_url('admin.php')),
	                    'result' => $json_result
	                ];

	                return $this->respond_error( 'invalid-response', $response );

				endif;

			else :

				$args               = array();
				$args['page']       = 'sejoli-license-form';
				$args['error']      = 'license-not-valid';
				$args['messages'][] = sprintf( __('Error response code : %s. Tidak bisa menghubungi server lisensi', 'sejoli-rest-api'), $response_code );

				$response = [
                    'valid'  => false,
                    'info'   => 'license-not-valid',
                    'redirect_link' => add_query_arg($args, admin_url('admin.php')),
                    'result' => $json_result
                ];

                return $this->respond_error( 'invalid-response', $response );

			endif;

		endif;

	}

	/**
	 * Validate license data rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_validate_license_data( \WP_REST_Request $request ) {

		$_request = wp_parse_args( $request->get_body_params(), [
			'user_email' => NULL,
			'user_pass'	 => NULL,
			'license'	 => NULL
		] );

		$response = array(
			'valid'   => false,
			'message' => __('Terjadi kesalahan di sistem', 'sejoli-rest-api')
		);

		$data = array(
            'user_email' => $_request['user_email'],
			'user_pass'	 => $_request['user_pass'],
			'license'	 => $_request['license'],
			'string'	 => $_SERVER['HTTP_HOST']
        );

		$link = add_query_arg(array(
					'license' => $data['license'],
					'string'  => $data['string']
				), 'https://member.sejoli.co.id/sejoli-validate-license');

		$response = wp_remote_get( $link );
		$response = json_decode( wp_remote_retrieve_body( $response ), true );

		if( false !== $response['valid'] ) :

			$response['message'] = __('<p>Lisensi ditemukan dan valid untuk instalasi ini.</p> <p>Anda bisa melakukan reset lisensi</p>', 'sejoli-rest-api');

			return $this->respond_success( true, $response, 'Validate license successfuly.', 200 );

		else:

			return $this->respond_error( 'invalid-data' );

		endif;

	}

	/**
	 * Get license data by user rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_get_license_data( \WP_REST_Request $request ) {

		$_request = wp_parse_args( $request->get_params(), [
			'user_id' => NULL,
			'status'  => NULL
		] );

        $data = [];

        $current_user = wp_get_current_user();

        if ( isset( $current_user->ID ) && $current_user->ID > 0 && $_request['user_id'] === NULL ) :
            $_request['user_id'] = $current_user->ID;
        endif;

        $response = sejolisa_get_licenses( $_request );

        if( false !== $response['valid'] ) :
        
            $data = $response['licenses'];

			return $this->respond_success( true, $data, 'Data successfully found', 200 );

		else:
			
			return $this->respond_error( 'invalid-data' );
		
		endif;

	}

}
