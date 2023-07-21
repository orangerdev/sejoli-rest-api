<?php
namespace Sejoli_Rest_Api\Rest;

use Carbon\Carbon;

/**
 * Main class responsible for rest api functions
 * @since   1.0.0
 */
class Rewards extends \Sejoli_Rest_Api\Rest {

	/**
	 * Register class routes
	 * Hooked via action rest_api_init, priority 10
	 * @since    1.0.0
	 */
	public function do_register() {

		$routes = array(
			'reward-point' => array(
				'endpoint'			  => '/reward-point',
	    		'methods'		      => \WP_REST_Server::READABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_get_reward_point_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	            'args' => array(
					'user_id' => array(
						'type' => 'number',
					),
					'length' => array(
						'type' => 'number',
					)
				)
	    	),
	    	'reward-exchange' => array(
				'endpoint'			  => '/reward-exchange',
	    		'methods'		      => \WP_REST_Server::READABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_get_reward_exchange_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	    	),
	    	'request-reward-exchange' => array(
				'endpoint'			  => '/reward-exchange/request',
	    		'methods'		      => \WP_REST_Server::CREATABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_request_reward_exchange_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	            'args' => array(
					'reward_id' => array(
						'type' => 'number',
					)
				)
	    	)
	    );

	    self::register_routes( $routes );
	
	}

	/**
	 * Get user reward point data rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_get_reward_point_data( \WP_REST_Request $request ) {

		$_request = wp_parse_args( $request->get_params(), [
	        'user_id' => NULL,
	        'length'  => NULL	    
	    ] );

	    $table  = array(
	    	'start'  => 0,
			'length' => ( empty( $_request['length'] ) ) ? 10 : intval( $_request['length'] ),
			'draw'	 => 1,
            'filter' => [],
            'search' => []
	    );

        $params = array(
			'user_id' => $_request['user_id']
        );

        $total = 0;
        $data  = [];

		$table['filter']['user_id']	= ( empty( $params['user_id'] ) ) ? get_current_user_id() : intval( $params['user_id'] );

		$return = sejoli_reward_get_history( $table['filter'], $table );

        if( false !== $return['valid'] ) :

            foreach( $return['points'] as $_data ) :

				$detail = '';

				if( 'in' === $_data->type ) :

					switch( $_data->meta_data['type'] ) :

						case 'order' :

							$product = sejolisa_get_product( $_data->product_id );
							$detail  = sprintf(
											__('Poin dari order %s untuk produk %s', 'sejoli-rest-api'),
											$_data->order_id,
											$product->post_title
									   );
							break;

						case 'affiliate' :

							$product = sejolisa_get_product( $_data->product_id );
							$detail  = sprintf(
											__('Poin dari affiliasi order %s untuk produk %s, tier %s', 'sejoli-rest-api'),
											$_data->order_id,
											$product->post_title,
											$_data->meta_data['tier']
									  );
							break;

						case 'manual' :
						
							$detail = $_data->meta_data['note'] . '. ' . $_data->meta_data['input'];
							break;

					endswitch;

				else :

					$detail = $_data->meta_data['note'];

				endif;

                $data[] = array(
					'created_at' => date( 'Y/m/d', strtotime( $_data->created_at ) ),
					'detail'   	 => $detail,
                    'point' 	 => $_data->point,
                    'type'  	 => $_data->type
                );

            endforeach;

            $total = count( $data );

        endif;

		if( !empty( $data ) ) :

			return $this->respond_success( true, $data, 'Data successfully found', 200 );

		else:
			
			return $this->respond_error( 'invalid-data' );
		
		endif;

	}

	/**
	 * Get reward exchange data rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_get_reward_exchange_data( \WP_REST_Request $request ) {

	    $rewards = new \WP_Query([
            'post_type'      => SEJOLI_REWARD_CPT,
            'posts_per_page' => -1,
			'post_status'    => 'publish',
			'meta_key'       => '_reward_point',
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC'
        ]);

        if( $rewards->have_posts() ) :

			while( $rewards->have_posts() ) :

                $rewards->the_post();

                $data[] = [
                    'id'   		=> get_the_ID(),
					'image'     => get_the_post_thumbnail_url(get_the_ID(), 'lager'),
                    'title' 	=> get_the_title(),
					'content'   => wpautop(get_the_content()),
					'point'     => carbon_get_the_post_meta('reward_point')
                ];

            endwhile;

        endif;

        wp_reset_query();

		if( !empty( $data ) ) :

			return $this->respond_success( true, $data, 'Data successfully found', 200 );

		else:
			
			return $this->respond_error( 'invalid-data' );
		
		endif;

	}

	/**
	 * Request reward exchange data rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_request_reward_exchange_data( \WP_REST_Request $request ) {

		$_request = wp_parse_args( $request->get_body_params(), [
	        'reward_id' => NULL
	    ] );

	    $response = array(
			'valid'   => false,
			'message' => __('Terjadi kesalahan di sistem', 'sejoli-rest-api')
		);

		$params = array(
			'reward_id' => $_request['reward_id']
		);

		if( !empty( $params['reward_id'] ) ) :

			$exchange_response   = sejoli_exchange_reward( $params['reward_id'] );
			$response['valid']   = $exchange_response['valid'];
			$response['message'] = implode('. ', $exchange_response['messages']);

		endif;

		if( false !== $response['valid'] ) :

			return $this->respond_success( true, $response, 'Data successfully found', 200 );

		else:
			
			return $this->respond_error( 'invalid-data' );
		
		endif;

	}

}