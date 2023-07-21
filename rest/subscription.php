<?php
namespace Sejoli_Rest_Api\Rest;

use Carbon\Carbon;

/**
 * Main class responsible for rest api functions
 * @since   1.0.0
 */
class Subscription extends \Sejoli_Rest_Api\Rest {

	/**
	 * Register class routes
	 * Hooked via action rest_api_init, priority 10
	 * @since    1.0.0
	 */
	public function do_register() {

		$routes = array(
			'sales' => array(
				'endpoint'			  => '/subscription',
	    		'methods'		      => \WP_REST_Server::CREATABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_get_subscription_data' ),
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
					'order' => array(
						'type' => 'string',
					),
	                'filter' => array(
						'filter' => 'string',
					),
					'type' => array(
						'type' => 'string',
					),
					'status' => array(
						'type' => 'string',
					),
					'user_id' => array(
						'type' => 'number',
					),
	                'product_id' => array(
						'filter' => 'number',
					)
				)
	    	),
	    	'sales_detail' => array(
				'endpoint'			  => '/subscription/detail',
	    		'methods'		      => \WP_REST_Server::READABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_get_subscription_single_data' ),
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
	public function sejoli_rest_get_subscription_data( \WP_REST_Request $request ) {

		$_request = wp_parse_args( $request->get_body_params(), [
			'start'  		  => NULL,
	        'length' 		  => NULL,
	        'order'  		  => NULL,
	        'filter' 		  => [],
	        'type'            => NULL,
	        'status'          => NULL,
	        'user_id'         => NULL,
	        'product_id'      => NULL
		] );

		$data = [];

		$args = array(
	        'type'            => $_request['type'],
	        'status'          => $_request['status'],
	        'user_id'         => $_request['user_id'],
            'product_id'      => $_request['product_id']
        );

        $current_user = wp_get_current_user();

        if ( isset( $current_user->ID ) && $current_user->ID > 0 && $_request['user_id'] === NULL ) :
            $args['user_id'] = $current_user->ID;
        endif;

        $respond = sejolisa_get_subscriptions( $args, $_request );

		if( false !== $respond['valid'] ) :

            $data = $respond['subscriptions'];
            $temp = array();

            $i = 0;

            foreach( $data as $_dt ) :

                $temp[$i]       = $_dt;
                $temp[$i]->link = add_query_arg( array( 'order_id' => $_dt->order_id) , home_url('checkout/renew/') );

                if( strtotime($_dt->end_date) > current_time('timestamp') ) :

                    $temp[$i]->day_left = Carbon::createFromDate($_dt->end_date)->diffInDays(Carbon::now());
                    $temp[$i]->expired  = false;
                    $temp[$i]->renewal  = true;

                else :

                    $temp[$i]->day_left = 0;
                    $temp[$i]->expired  = true;
                    $temp[$i]->renewal  = true;

                    if( $_dt->status === 'expired' ) :

                        $temp[$i]->status = 'expired';
                    
                    else:
                    
                        $temp[$i]->status = 'inactive';
                    
                    endif;

                    $max_renewal_day = absint( sejolisa_carbon_get_post_meta( $_dt->product_id, 'subscription_max_renewal_days') );

                    if(
                        0 < $max_renewal_day &&
                        $max_renewal_day < sejolisa_get_difference_day( strtotime($_dt->end_date) )
                    ) :

                        $temp[$i]->renewal = false;
                        $temp[$i]->link    = get_permalink( $_dt->product_id );
                    
                    endif;

                endif;

                $i++;

            endforeach;

            $data = $temp;

		endif;

		if( !empty( $data ) && true === $respond['valid'] ) :

			return $this->respond_success( true, $data, 'Data successfully found', 200 );

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
	public function sejoli_rest_get_subscription_single_data( \WP_REST_Request $request ) {

		$_request = wp_parse_args( $request->get_params(), [
			'order_id' => NULL,
		] );

		$params = array(
            'ID' => $_request['order_id']
        );

        $order = sejolisa_get_subscription_by_order( $params['ID'] );

		if( ! is_wp_error( $order ) && true === $order['valid'] ) :

			return $this->respond_success( true, $order, 'Data successfully found', 200 );

		else:
			
			return $this->respond_error( 'invalid-data' );
		
		endif;

	}	

}