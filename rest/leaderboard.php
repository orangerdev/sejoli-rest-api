<?php
namespace Sejoli_Rest_Api\Rest;

/**
 * Main class responsible for rest api functions
 * @since   1.0.0
 */
class Leaderboard extends \Sejoli_Rest_Api\Rest {

	/**
	 * Register class routes
	 * Hooked via action rest_api_init, priority 10
	 * @since    1.0.0
	 */
	public function do_register() {

		$routes = array(
			'leaderboard' => array(
				'endpoint'			  => '/leaderboard',
	    		'methods'		      => \WP_REST_Server::CREATABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_get_leaderboard_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	            'args' => array(
					'product_id' => array(
						'type' => 'number',
					),
					'start_date' => array(
						'type' => 'string',
					),
					'end_date' => array(
						'type' => 'string',
					),
				)
	    	)
	    );

	    self::register_routes( $routes );
	
	}

	/**
	 * Get leaderboard data rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_get_leaderboard_data( \WP_REST_Request $request ) {

		$_request = wp_parse_args( $request->get_body_params(), [
	        'product_id' => NULL,
	        'start_date' => NULL,
	        'end_date'   => NULL		
	    ] );

		$response = [];

        $post_data = wp_parse_args($_REQUEST,[
            'product_id'        => $_request['product_id'],
            'affiliate_id'      => NULL,
            'calculate'         => 'order',
            'order_status'      => ['completed'],
            'commission_status' => NULL,
            'start_date'        => $_request['start_date'],
            'end_date'          => $_request['end_date'],
            'sort'              => NULL,
            'limit'             => 10,
        ]);

        $temp = [];
        $data = sejolisa_get_affiliate_statistic( $post_data );

        if( isset($data['statistic']) ) :

            $i = 1;
            foreach( $data['statistic'] as $_data ) :

                $temp[] = [
                    'rank'      => $i,
                    'ID'        => $_data->ID,
                    'image'     => get_avatar_url($_data->ID),
                    'name'      => $_data->user_name,
                    'raw_total' => $_data->total,
                    'total'     => ('total' === $post_data['calculate']) ? sejolisa_price_format($_data->total) : $_data->total
                ];

                $i++;

            endforeach;

        endif;

        $response = $temp;

		if( !empty( $data ) && !empty( $response ) ) :

			return $this->respond_success( true, $response, 'Data successfully found', 200 );

		else:
			
			return $this->respond_error( 'invalid-data' );
		
		endif;

	}	

}