<?php
namespace Sejoli_Rest_Api\Rest;

/**
 * Main class responsible for rest api functions
 * @since   1.0.0
 */
class Order extends \Sejoli_Rest_Api\Rest {

	/**
	 * Register class routes
	 * Hooked via action rest_api_init, priority 10
	 * @since    1.0.0
	 */
	public function do_register() {

		$routes = array(
			'order' => array(
				'endpoint'			  => '/order',
	    		'methods'		      => \WP_REST_Server::CREATABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_create_order_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	            'args' => array(
					'user_id' => array(
						'type' => 'number',
					),
					'affiliate_id' => array(
						'type' => 'number',
					),
					'coupon' => array(
						'type' => 'string',
					),
					'payment_gateway' => array(
						'type' => 'string',
					),
					'quantity' => array(
						'type' => 'number',
					),
					'user_email' => array(
						'type' => 'string',
					),
					'user_name' => array(
						'type' => 'string',
					),
					'user_password' => array(
						'type' => 'string',
					),
					'postal_code' => array(
						'type' => 'number',
					),
					'user_phone' => array(
						'type' => 'number',
					),
					'district_id' => array(
						'type' => 'number',
					),
					'shipment' => array(
						'type' => 'string',
					),
					'wallet' => array(
						'type' => 'string',
					),
					'product_id' => array(
						'type' => 'number',
					),
					'variants' => array(
						'type' => 'string',
					),
					'other' => array(
						'type' => 'string',
					),
				)
	    	),
	    	'order_renewal' => array(
				'endpoint'			  => '/order/renewal',
	    		'methods'		      => \WP_REST_Server::CREATABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_create_order_renewal_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	            'args' => array(
					'user_id' => array(
						'type' => 'number',
					),
					'affiliate_id' => array(
						'type' => 'number',
					),
					'coupon' => array(
						'type' => 'string',
					),
					'wallet' => array(
						'type' => 'string',
					),
					'payment_gateway' => array(
						'type' => 'string',
					),
					'quantity' => array(
						'type' => 'number',
					),
					'product_id' => array(
						'type' => 'number',
					),
					'order_id' => array(
						'type' => 'number',
					),
				)
	    	)
	    );

	    self::register_routes( $routes );
	
	}

	/**
	 * Create order data rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_create_order_data( \WP_REST_Request $request ) {

		$_request = wp_parse_args( $request->get_body_params(), [
			'user_id'         => NULL,
            'affiliate_id'    => NULL,
            'coupon'          => NULL,
            'payment_gateway' => 'manual',
            'quantity'        => 1,
            'user_email'      => NULL,
            'user_name'       => NULL,
            'user_password'   => NULL,
            'postal_code'	  => NULL,
            'user_phone'      => NULL,
            'district_id'     => NULL,
            'shipment'        => NULL,
			'wallet'		  => NULL,
            'product_id'      => 0,
            'variants'        => [],
			'other'			  => [],
		] );

        $args = wp_parse_args( $_POST,[
            'user_id'         => $_request['user_id'],
            'affiliate_id'    => $_request['affiliate_id'],
            'coupon'          => $_request['coupon'],
            'payment_gateway' => $_request['payment_gateway'],
            'quantity'        => $_request['quantity'],
            'user_email'      => $_request['user_email'],
            'user_name'       => $_request['user_name'],
            'user_password'   => $_request['user_password'],
            'postal_code'	  => $_request['postal_code'],
            'user_phone'      => $_request['user_phone'],
            'district_id'     => $_request['district_id'],
            'shipment'        => $_request['shipment'],
			'wallet'		  => $_request['wallet'],
            'product_id'      => $_request['product_id'],
            'variants'        => $_request['variants'],
			'other'			  => $_request['other'],
        ] );

		if( is_array( $args ) ) :

            $current_user = wp_get_current_user();

            if ( isset( $current_user->ID ) && $current_user->ID > 0 ) :
                $args['user_id'] = $current_user->ID;
            endif;

            do_action('sejoli/checkout/do', $args);

            $order    = sejolisa_get_respond('order');
            $checkout = sejolisa_get_respond('checkout');

            if( false === $checkout['valid'] ) :

                $response = [
                    'valid'    => false,
                    'messages' => $checkout['messages']['error'],
                ];

                return $this->respond_error( 'invalid-data', $checkout['messages']['error'] );

            elseif( false === $order['valid'] ) :

                $response = [
                    'valid'    => false,
                    'messages' => $order['messages']['error'],
                ];

                return $this->respond_error( 'invalid-data', $order['messages']['error'] );

            else:

                $d_order = $order['order'];

                $messages = [sprintf( __('Order created successfully. Order ID #%s', 'sejoli-rest-api'), $d_order['ID'] )];

                if( 0 < count( $order['messages']['warning'] ) ) :

                    foreach( $order['messages']['warning'] as $message ) :

                        $messages[] = $message;

                    endforeach;

                endif;

                if( 0 < count( $order['messages']['info'] ) ) :

                    foreach( $order['messages']['info'] as $message ) :

                        $messages[] = $message;

                    endforeach;

                endif;

                $response = [
                    'valid'  => true,
                    'info'   => $messages,
                    'redirect_link' => site_url('checkout/loading?order_id='.$d_order['ID']),
                    'orders' => $d_order
                ];

                if( true === $response['valid'] ) :

                	return $this->respond_success( true, $response, 'Order created successfully.', 200 );

                else:

                	return $this->respond_error( 'invalid-data' );

                endif;

            endif;

        endif;

	}

	/**
	 * Create order renewal data rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_create_order_renewal_data( \WP_REST_Request $request ) {

		$_request = wp_parse_args( $request->get_body_params(), [
			'user_id'         => NULL,
            'affiliate_id'    => NULL,
            'coupon'          => NULL,
			'wallet'		  => NULL,
            'payment_gateway' => 'manual',
            'quantity'        => 1,
            'product_id'      => 0,
			'order_id'        => 0
		] );

		$args = wp_parse_args( $_POST,[
            'user_id'         => $_request['user_id'],
            'affiliate_id'    => $_request['affiliate_id'],
            'coupon'          => $_request['coupon'],
			'wallet'		  => $_request['wallet'],
            'payment_gateway' => $_request['payment_gateway'],
            'quantity'        => $_request['quantity'],
            'product_id'      => $_request['product_id'],
			'order_id'        => $_request['order_id']
        ] );

		if(is_array($args)) :

            $current_user = wp_get_current_user();

            if ( isset( $current_user->ID ) && $current_user->ID > 0 ) :
                $args['user_id'] = $current_user->ID;
            endif;

			do_action('sejoli/checkout/renew', $args);

            $order    = sejolisa_get_respond('order');
            $checkout = sejolisa_get_respond('checkout');

            if( false === $checkout['valid'] ) :

                $response = [
                    'valid'    => false,
                    'messages' => $checkout['messages']['error'],
                ];

                return $this->respond_error( 'invalid-response', $checkout['messages']['error'] );

            elseif( false === $order['valid'] ) :

                $response = [
                    'valid'    => false,
                    'messages' => $order['messages']['error'],
                ];

                return $this->respond_error( 'invalid-response', $order['messages']['error'] );

            else:

                $d_order = $order['order'];

                $messages = [sprintf( __('Order created successfully. Order ID #%s', 'sejoli-rest-api'), $d_order['ID'] )];

                if( 0 < count( $order['messages']['warning'] ) ) :

                    foreach( $order['messages']['warning'] as $message ) :
                        
                        $messages[] = $message;

                    endforeach;

                endif;

                if( 0 < count( $order['messages']['info'] ) ) :
                    
                    foreach( $order['messages']['info'] as $message ) :

                        $messages[] = $message;

                    endforeach;

                endif;

                $response = [
                    'valid'  => true,
                    'info'   => $messages,
                    'redirect_link' => site_url('checkout/loading?order_id='.$d_order['ID']),
                    'orders' => $d_order
                ];

                if( true === $response['valid'] ) :

                	return $this->respond_success( true, $response, 'Order created successfully.', 200 );

                else:

                	return $this->respond_error( 'invalid-data' );

                endif;

            endif;

        endif;

	}

}