<?php
namespace Sejoli_Rest_Api\Rest;

/**
 * Main class responsible for rest api functions
 * @since   1.0.0
 */
class Coupon extends \Sejoli_Rest_Api\Rest {

	/**
	 * Register class routes
	 * Hooked via action rest_api_init, priority 10
	 * @since    1.0.0
	 */
	public function do_register() {

		$routes = array(
			'apply_coupon' => array(
				'endpoint'			  => '/coupon/apply',
	    		'methods'		      => \WP_REST_Server::CREATABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_apply_coupon_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	            'args' => array(
					'order_id' => array(
						'type' => 'number',
					),
					'product_id' => array(
						'type' => 'number',
					),
					'coupon' => array(
						'type' => 'string',
					),
					'quantity' => array(
						'type' => 'number',
					),
					'type' => array(
						'type' => 'string',
					),
					'payment_gateway' => array(
						'type' => 'string',
					),
					'shipment' => array(
						'type' => 'string',
					),
					'calculate' => array(
						'type' => 'string',
					)
				)
	    	),
	    	'unapply_coupon' => array(
				'endpoint'			  => '/coupon/unapply',
	    		'methods'		      => \WP_REST_Server::CREATABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_unapply_coupon_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	            'args' => array(
					'order_id' => array(
						'type' => 'number',
					),
					'product_id' => array(
						'type' => 'number',
					),
					'coupon' => array(
						'type' => 'string',
					),
					'quantity' => array(
						'type' => 'number',
					),
					'type' => array(
						'type' => 'string',
					),
					'payment_gateway' => array(
						'type' => 'string',
					),
					'shipment' => array(
						'type' => 'string',
					),
					'calculate' => array(
						'type' => 'string',
					)
				)
	    	)
	    );

	    self::register_routes( $routes );
	
	}

	/**
	 * Get apply coupon data rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_apply_coupon_data( \WP_REST_Request $request ) {

		$response = [];
		$args     = [];

		$_request = wp_parse_args( $request->get_body_params(), [
			'order_id'        => NULL,
			'product_id'      => NULL,
            'coupon'          => NULL,
            'quantity'        => NULL,
            'type'            => NULL,
            'payment_gateway' => NULL,
            'shipment'        => NULL,
			'calculate'		  => NULL
		] );

		// Ordinary checkout
        if ( $_request['calculate'] === "default" ) :

            $args = wp_parse_args( $_POST,[
                'product_id'      => $_request['product_id'],
                'coupon'          => $_request['coupon'],
                'quantity'        => $_request['quantity'],
                'type'            => $_request['type'],
                'payment_gateway' => $_request['payment_gateway'],
                'shipment'        => $_request['shipment'],
				'calculate'		  => $_request['calculate']
            ]);

		elseif ( $_request['calculate'] === "renew" ) :

			$args = wp_parse_args( $_POST,[
				'order_id'   => $_request['order_id'],
                'product_id' => $_request['product_id'],
                'coupon'     => $_request['coupon'],
                'quantity'   => $_request['quantity'],
				'calculate'	 => $_request['calculate']
            ]);

        endif;

		if( is_array( $args ) && !empty( $args['coupon'] ) ) :

			$coupon          = sejolisa_get_coupon_by_code( $args['coupon'] );
			$currentDateTime = date('Y-m-d H:i:s');

			if(
				($coupon['valid'] && $coupon['coupon']['limit_use'] > 0 && $coupon['coupon']['limit_date'] !== null && $coupon['coupon']['usage'] < $coupon['coupon']['limit_use'] && $currentDateTime < $coupon['coupon']['limit_date'] && $coupon['coupon']['status'] == 'active') ||
                ($coupon['valid'] && $coupon['coupon']['limit_use'] > 0 && $coupon['coupon']['limit_date'] === null && $coupon['coupon']['usage'] < $coupon['coupon']['limit_use'] && $coupon['coupon']['status'] === 'active') ||
                ($coupon['valid'] && $coupon['coupon']['limit_use'] === 0 && $coupon['coupon']['limit_date'] !== null && $currentDateTime < $coupon['coupon']['limit_date'] && $coupon['coupon']['status'] = 'active') ||
                ($coupon['valid'] && $coupon['coupon']['limit_use'] === 0 && $coupon['coupon']['limit_date'] === null && $coupon['coupon']['status'] === 'active')
			):

				do_action('sejoli/frontend/checkout/apply-coupon', $coupon, $args);

				$response = sejolisa_get_respond('apply-coupon');

			elseif( $coupon['valid'] && $coupon['coupon']['status'] === 'inactive' ) :

				$response = [
					'valid'    => false,
					'messages' => [__('Kupon tidak aktif', 'sejoli')]
				];

			elseif( $coupon['valid'] && $coupon['coupon']['limit_use'] > 0 && $coupon['coupon']['limit_date'] === null && $coupon['coupon']['usage'] >= $coupon['coupon']['limit_use'] && $coupon['coupon']['status'] === 'active' ) :

				$response = [
					'valid'    => false,
					'messages' => [__('Batas penggunaan kupon sudah mencapai batas')]
				];

			elseif($coupon['valid'] && $coupon['coupon']['limit_use'] == 0 && $coupon['coupon']['limit_date'] != null && $currentDateTime > $coupon['coupon']['limit_date'] && $coupon['coupon']['status'] == 'active') :

				$response = [
					'valid'    => false,
					'messages' => [__('Batas penggunaan kupon sudah berakhir', 'sejoli')]
				];

			elseif($coupon['valid'] && $coupon['coupon']['limit_use'] > 0 && $coupon['coupon']['limit_date'] != null && $coupon['coupon']['usage'] >= $coupon['coupon']['limit_use'] && $currentDateTime > $coupon['coupon']['limit_date'] && $coupon['coupon']['status'] == 'active') :

				$response = [
					'valid'    => false,
					'messages' => [__('Batas penggunaan kupon sudah mencapai batas dan sudah berakhir', 'sejoli')]
				];

			else :

				$response = [
					'valid'    => false,
					'messages' => [__('Kode kupon tidak valid', 'sejoli')]
				];

			endif;

		endif;  		

		if( !empty( $response ) ) :

			return $this->respond_success( true, $response, 'Coupon successfully applied', 200 );

		else:
			
			return $this->respond_error( 'invalid-data' );
		
		endif;

	}	

	/**
	 * Get apply coupon data rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_unapply_coupon_data( \WP_REST_Request $request ) {

		$response = [];
		$args     = [];

		$_request = wp_parse_args( $request->get_body_params(), [
			'order_id'        => NULL,
			'product_id'      => NULL,
            'coupon'          => NULL,
            'quantity'        => NULL,
            'type'            => NULL,
            'payment_gateway' => NULL,
            'shipment'        => NULL,
			'calculate'		  => NULL
		] );

		// Ordinary checkout
        if ( $_request['calculate'] === "default" ) :

            $args = wp_parse_args( $_POST,[
                'product_id'      => $_request['product_id'],
                'coupon'          => $_request['coupon'],
                'quantity'        => $_request['quantity'],
                'type'            => $_request['type'],
                'payment_gateway' => $_request['payment_gateway'],
                'shipment'        => $_request['shipment'],
				'calculate'		  => $_request['calculate']
            ]);

		elseif ( $_request['calculate'] === "renew" ) :

			$args = wp_parse_args( $_POST,[
				'order_id'   => $_request['order_id'],
                'product_id' => $_request['product_id'],
                'coupon'     => $_request['coupon'],
                'quantity'   => $_request['quantity'],
				'calculate'	 => $_request['calculate']
            ]);

        endif;

		if( is_array( $args ) ) :

			$args['coupon'] = NULL;

			if ( intval( $args['product_id'] ) > 0 ) :

				do_action( 'sejoli/frontend/checkout/delete-coupon', $args );

				$response = sejolisa_get_respond( 'delete-coupon' );

			endif;

			$response = [
				'valid'    => false,
				'messages' => [ __('Hapus kupon gagal') ]
			];

		endif;		

		if( !empty( $response ) ) :

			return $this->respond_success( true, $response, 'Coupon successfully unapplied', 200 );

		else:
			
			return $this->respond_error( 'invalid-data' );
		
		endif;

	}	

}