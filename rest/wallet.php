<?php
namespace Sejoli_Rest_Api\Rest;

/**
 * Main class responsible for rest api functions
 * @since   1.0.0
 */
class Wallet extends \Sejoli_Rest_Api\Rest {

	/**
	 * Register class routes
	 * Hooked via action rest_api_init, priority 10
	 * @since    1.0.0
	 */
	public function do_register() {

		$routes = array(
			'wallet' => array(
				'endpoint'			  => '/wallet',
	    		'methods'		      => \WP_REST_Server::READABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_get_wallet_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	            'args' => array(
					'user_id' => array(
						'type' => 'number',
					)
				)
	    	),
	    	'saldo-wallet' => array(
				'endpoint'			  => '/wallet/available-saldo',
	    		'methods'		      => \WP_REST_Server::READABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_get_available_saldo_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	            'args' => array(
					'user_id' => array(
						'type' => 'number',
					)
				)
	    	),
	    	'request-fund' => array(
				'endpoint'			  => '/wallet/request-fund',
	    		'methods'		      => \WP_REST_Server::CREATABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_create_request_fund_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	            'args' => array(
					'amount' => array(
						'type' => 'number',
					),
					'information' => array(
						'type' => 'string',
					)
				)
	    	)
	    );

	    self::register_routes( $routes );
	
	}

	/**
	 * Get wallet data rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_get_wallet_data( \WP_REST_Request $request ) {

		$_request = wp_parse_args( $request->get_params(), [
	        'user_id' => NULL		
	    ] );

	    $table = [
	        'start'   => NULL,
	        'length'  => NULL,
	        'order'   => NULL,
	        'filter'  => NULL
	    ];

        $data = [];

		$table['filter']['user_id']	= ( empty( $_request['user_id'] ) ) ? get_current_user_id() : intval( $_request['user_id'] );

		$return = sejoli_wallet_get_history( $table['filter'], $table );

        if( false !== $return['valid'] ) :

            foreach( $return['wallet'] as $_data ) :

				$detail = '';

				switch( $_data->label ) :

					case 'cashback' :
						$product = sejolisa_get_product( $_data->product_id );
						$detail  = sprintf(
										__('Cashback dari order %s untuk produk %s', 'sejoli-rest-api'),
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

					case 'order' :

						$detail = sprintf(
									__('Pembayaran untuk order %s', 'sejoli-rest-api'),
									$_data->order_id
								  );
						break;

					case 'request' :

						$detail = __('Request pencairan', 'sejoli-rest-api');
						break;

					case 'manual'	:

						$detail	= $_data->meta_data['note'] . ' ' . $_data->meta_data['input'];
						break;

				endswitch;

                $data[] = array(
					'created_at'	=> date( 'Y/m/d', strtotime( $_data->created_at ) ),
					'detail'        => $detail,
                    'point' 		=> sejolisa_price_format( $_data->value ),
                    'type'  		=> $_data->type,
					'refundable'    => boolval( $_data->refundable )
                );

            endforeach;

        endif;

		if( !empty( $data ) ) :

			return $this->respond_success( true, $data, 'Data successfully found', 200 );

		else:
			
			return $this->respond_error( 'invalid-data' );
		
		endif;

	}

	/**
	 * Get available saldo data rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_get_available_saldo_data( \WP_REST_Request $request ) {

		$_request = wp_parse_args( $request->get_params(), [
	        'user_id' => NULL		
	    ] );

		$current_user = wp_get_current_user();

	    if ( isset( $current_user->ID ) && $current_user->ID > 0 && $_request['user_id'] === NULL ) :
	        $_request['user_id'] = $current_user->ID;
	    endif;

		$wallet_data = sejoli_get_user_wallet_data( $_request['user_id'] );
		$wallet 	 = $wallet_data['wallet'];

		if( !empty( $wallet_data ) ) :

			return $this->respond_success( true, $wallet, 'Data successfully found', 200 );

		else:
			
			return $this->respond_error( 'invalid-data' );
		
		endif;

	}

	/**
	 * Create request fund data rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_create_request_fund_data( \WP_REST_Request $request ) {

		$_request = wp_parse_args( $request->get_body_params(), [
	        'amount'     => 0.0,
	        'information' => NULL
	    ] );

		$valid = true;

        $response = array(
            'valid'   => false,
            'message' => NULL
        );

        $params = array(
            'amount'      => $_request['amount'],
            'information' => $_request['information']
        );

        $messages = array();

        if( empty( $params['amount'] ) ) :

            $valid      = false;
            $messages[] = __('Jumlah pencairan dana kosong', 'sejoli-rest-api');
        
        endif;

        if( empty( $params['information'] ) ) :

            $valid      = false;
            $messages[] = __('Informasi terkait rekening penerima kosong', 'sejoli-rest-api');
        
        endif;

        if( $valid ) :

            $response = sejoli_request_wallet_fund(array(
                'value' => $params['amount'],
                'note'  => $params['information']
            ));

            if( false !== $response['valid'] ) :

                $response['message'] = __('Permintaan pencairan dana telah dikirim. Kami akan mengecek dan memvalidasi pencairan dana anda', 'sejoli-rest-api');
            
            else :
            
                $response['message'] = implode('<br />', $response['messages']['error']);
            
            endif;
        
        else :
        
            $response['message'] = implode('<br />', $messages);
        
        endif;

        $response['data'] = $_request;

        if( false !== $response['valid'] ) :

			return $this->respond_success( true, $response, 'Data successfully found', 200 );

		else:
			
			return $this->respond_error( 'invalid-data' );
		
		endif;

	}

}