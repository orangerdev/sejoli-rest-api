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
			'affiliate_commission' => array(
				'endpoint'			  => '/affiliate/commission',
	    		'methods'		      => \WP_REST_Server::READABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_get_affiliate_commission_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	            'args' => array(
					'affiliate_id' => array(
						'type' => 'number',
					),
				)
	    	),
	    	'affiliate_order' => array(
				'endpoint'			  => '/affiliate/sales',
	    		'methods'		      => \WP_REST_Server::READABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_get_affiliate_sales_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	            'args' => array(
					'affiliate_id' => array(
						'type' => 'number',
					),
				)
	    	),
	    	'affiliate_coupon' => array(
				'endpoint'			  => '/affiliate/coupon',
	    		'methods'		      => \WP_REST_Server::READABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_get_affiliate_coupon_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	            'args' => array(
					'code' => array(
						'type' => 'string',
					),
					'coupon_parent_id' => array(
						'type' => 'number',
					),
					'status' => array(
						'type' => 'string',
					),
					'user_id' => array(
						'type' => 'number',
					),
					'limit_date' => array(
						'type' => 'string',
					),
				)
	    	),
	    	'affiliate_coupon_parent' => array(
				'endpoint'			  => '/affiliate/coupon/parent',
	    		'methods'		      => \WP_REST_Server::READABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_get_affiliate_coupon_parent_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	    	),
	    	'affiliate_create_coupon' => array(
				'endpoint'			  => '/affiliate/coupon/create',
	    		'methods'		      => \WP_REST_Server::CREATABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_create_affiliate_coupon_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	            'args' => array(
					'user_id' => array(
						'type' => 'number',
					),
					'coupon_parent_id' => array(
						'type' => 'number',
					),
					'coupon' => array(
						'type' => 'string',
					),
				)
	    	),
	    	'affiliate_network' => array(
				'endpoint'			  => '/affiliate/network',
	    		'methods'		      => \WP_REST_Server::READABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_get_affiliate_network_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	            'args' => array(
					'user_id' => array(
						'type' => 'number',
					),
				)
	    	),
	    	'affiliate_editor_bonus' => array(
				'endpoint'			  => '/affiliate/editor-bonus',
	    		'methods'		      => \WP_REST_Server::READABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_get_affiliate_editor_bonus_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	            'args' => array(
					'limit' => array(
						'type' => 'number',
					),
					'offset' => array(
						'type' => 'number',
					)
				)
	    	),
	    	'affiliate_update_editor_bonus' => array(
				'endpoint'			  => '/affiliate/editor-bonus/update',
	    		'methods'		      => \WP_REST_Server::EDITABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_update_affiliate_editor_bonus_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	            'args' => array(
	            	'user_id' => array(
						'type' => 'number',
					),
					'content' => array(
						'type' => 'string',
					),
					'product_id' => array(
						'type' => 'number',
					)
				)
	    	),
	    );

	    self::register_routes( $routes );
	
	}

	/**
	 * Get affiliates data by user rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_get_affiliate_commission_data( \WP_REST_Request $request ) {

		$_request = wp_parse_args( $request->get_params(), [
			'affiliate_id' => NULL,
		] );

        $params = array(
	        'affiliate_id' => $_request['affiliate_id']
	    );

	    $query = \SejoliSA\Model\Affiliate::reset()
	                ->set_filter_from_array( $params );

	    $affiliate = $query->get()->respond();

	    if( ! is_wp_error( $affiliate ) && true === $affiliate['valid'] ) :

	    	return $this->respond_success( true, $affiliate, 'Data successfully found', 200 );

		else:
			
			return $this->respond_error( 'invalid-data' );
		
		endif;

	}

	/**
	 * Get sales data by affiliate rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_get_affiliate_sales_data( \WP_REST_Request $request ) {

		$_request = wp_parse_args( $request->get_params(), [
			'affiliate_id' => NULL,
		] );

		$params = array(
            'product_id'      => NULL,
	        'user_id'         => NULL,
	        'affiliate_id'    => $_request['affiliate_id'],
	        'coupon_id'       => NULL,
	        'payment_gateway' => NULL,
	        'status'          => NULL,
	        'type'            => NULL
        );

        $order = sejolisa_get_orders( $params );

		if( ! is_wp_error( $order ) ) :

			return $this->respond_success( true, $order, 'Data successfully found', 200 );

		else:
			
			return $this->respond_error( 'invalid-data' );
		
		endif;

	}

	/**
	 * Get coupon affiliate data by affiliate rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_get_affiliate_coupon_data( \WP_REST_Request $request ) {

		$_request = wp_parse_args( $request->get_params(), [
	        'code' 			   => NULL,
	        'coupon_parent_id' => NULL,
	        'status'           => (false !== sejolisa_carbon_get_theme_option('sejoli_affiliate_coupon_active')) ? 'active' : 'pending',
	        'user_id'          => NULL,
	        'limit_date'       => NULL
		] );

		$current_user = wp_get_current_user();

        if ( isset( $current_user->ID ) && $current_user->ID > 0 && $_request['user_id'] === NULL ) :
            $_request['user_id'] = $current_user->ID;
        endif;

	    $respond = sejolisa_get_coupons( $_request );

		if( false !== $respond['valid'] ) :

			$data = [];

            foreach( $respond['coupons'] as $coupon ) :

                if(
                    is_array($coupon['rule']) &&
                    array_key_exists('use_by_affiliate', $coupon['rule']) &&
                    false === $coupon['rule']['use_by_affiliate']
                ) :

                	continue;

                endif;

                if( !empty($coupon['parent_discount']) ) :

                    $coupon['discount']   = unserialize($coupon['parent_discount']);
                    $coupon['limit_date'] = $coupon['parent_limit_date'];
                    $coupon['limit_use']  = $coupon['parent_limit_use'];

                endif;

                $discount = '';

                if( 'fixed' === $coupon['discount']['type'] ) :

                    $discount .= sejolisa_price_format($coupon['discount']['value']);

                    $discount .= ('per_item' === $coupon['discount']['usage']) ?
                                    ' (' . __('per item', 'sejoli-rest-api') . ')' :
                                    ' (' . __('total', 'sejoli-rest-api') . ')';

                else :

                    $discount .= $coupon['discount']['value'] . '%';

                endif;

                $data[] = [
                    'ID'          => $coupon['ID'],
                    'code'        => $coupon['code'],
                    'limit'       => [
                        'date' => $coupon['limit_date'],
                        'use'  => $coupon['limit_use']
                    ],
                    'username'      => $coupon['owner_name'],
                    'discount'      => $discount,
                    'parent_code'   => strtoupper($coupon['parent_code']),
                    'usage'         => $coupon['usage'],
                    'status'        => $coupon['status'],
                    'affiliate_use' => isset($coupon['rule']['use_by_affiliate']) ? boolval($coupon['rule']['use_by_affiliate']) : null,
                    'free_shipping' => isset($coupon['discount']['free_shipping']) ? boolval($coupon['discount']['free_shipping']) : false
                ];

            endforeach;

		endif;

		if( ! empty( $data ) ) :

			return $this->respond_success( true, $data, 'Data successfully found', 200 );

		else:
			
			return $this->respond_error( 'invalid-data' );
		
		endif;

	}

	/**
	 * Get coupon parent data by affiliate rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_get_affiliate_coupon_parent_data( \WP_REST_Request $request ) {

		$data    = [];
        $options = [];

        $response = sejolisa_get_coupons([
            'coupon_parent_id' => '0',
            'status'           => 'active'
        ]);

        if( false !== $response['valid'] ) :

            $coupons = [];

            foreach( $response['coupons'] as $_coupon ) :

                if( isset($_coupon['rule']['use_by_affiliate']) && false !== $_coupon['rule']['use_by_affiliate'] ) :
                    
                    if( strtotime($_coupon['limit_date']) > strtotime(date('Y-m-d-H-i-s')) || $_coupon['limit_date'] === NULL ) {
                    
                        $coupons[] = $_coupon;
                        $options[] = [
                            'id'   => $_coupon['ID'],
                            'text' => $_coupon['code']
                        ];
                    
                    }

                endif;

            endforeach;

            $data = [
                'valid'   => true,
                'coupons' => $coupons
            ];

        endif;

		if( ! empty( $data ) ) :

			return $this->respond_success( true, $data, 'Data successfully found', 200 );

		else:
			
			return $this->respond_error( 'invalid-data' );
		
		endif;

	}

	/**
	 * Create affiliate coupon data rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_create_affiliate_coupon_data( \WP_REST_Request $request ) {

		$_request = wp_parse_args( $request->get_body_params(), [
			'user_id'          => NULL,
            'coupon_parent_id' => NULL,
            'coupon'           => NULL
		] );

        $args = array(
            'user_id'          => $_request['user_id'],
            'coupon_parent_id' => $_request['coupon_parent_id'],
            'code'             => $_request['coupon']
        );

		if( is_array( $args ) ) :

            $current_user = wp_get_current_user();

            if ( isset( $current_user->ID ) && $current_user->ID > 0 && $_request['user_id'] === NULL ) :
                $args['user_id'] = $current_user->ID;
            endif;

            $response = [
	            'valid'    => false,
	            'messages' => []
	        ];

	        $valid = false;

            $response_by_id   = sejolisa_get_coupon_by_id( $args['coupon_parent_id'] );
            $response_by_code = sejolisa_get_coupon_by_code( $args['code'] );

            if( false !== $response_by_id['valid'] ) :

                // Check parent coupon is valid
                if(
                    0 !== intval( $response_by_id['coupon']['coupon_parent_id'] ) ||
                    'active' !== $response_by_id['coupon']['status']
                ) :

                    $response = [
                        'valid' => false,
                        'messages' => [
                            'error' => [
                                __('Kupon asli tidak bisa digunakan', 'sejoli-rest-api')
                            ]
                        ]
                    ];

                else :

                    // Check if coupon code exists
                    if( false !== $response_by_code['valid'] ) :

                        $response = [
                            'valid' => false,
                            'messages' => [
                                'error' => [
                                    sprintf( __('Kupon (%s) sudah digunakan. Ganti dengan yang lain', 'sejoli-rest-api'), $args['code'] )
                                ]
                            ]
                        ];

                    else :

                        $args['limit'] = intval( $response_by_id['coupon']['rule']['limit_affiliate_coupon'] );
                        $coupon_affiliate_available = sejolisa_is_affiliate_coupon_available( $args );

                        if( false != $coupon_affiliate_available ) :

                            $response = sejolisa_create_affiliate_coupon( $args );

                        else :

                            $response = [
                                'valid' => false,
                                'messages'  => array(
                                    'error' => array(
                                        __('Jumlah kupon yang anda buat sudah mencapai batas kepemilikan kupon per affiliasi', 'sejoli-rest-api')
                                    )
                                )
                            ];

                        endif;

                    endif;

                endif;
            
            endif;

            $valid = $response['valid'];

	        if( $valid ) :

            	return $this->respond_success( true, $response, 'Coupon created successfully.', 200 );

            else:

            	return $this->respond_error( 'invalid-response', $response['messages']['error'] );

            endif;

        endif;

	}

	/**
	 * Get affiliates network data by user rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_get_affiliate_network_data( \WP_REST_Request $request ) {

		$_request = wp_parse_args( $request->get_params(), [
			'user_id' => NULL,
		] );

        $params = array(
	        'user_id' => $_request['user_id']
	    );

	    $current_user = wp_get_current_user();

        if ( isset( $current_user->ID ) && $current_user->ID > 0 && $_request['user_id'] === NULL ) :
            $params['user_id'] = $current_user->ID;
        endif;

	    $data = sejolisa_user_get_downlines(
                        $params['user_id'],
                        sejolisa_get_max_downline_tiers()
                    );

        if( false !== $data ) :

			return $this->respond_success( true, $data, 'Data successfully found', 200 );

		else:

			return $this->respond_error( 'invalid-data' );

		endif;

	}

	/**
	 * Get affiliates editor bonus data by user rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_get_affiliate_editor_bonus_data( \WP_REST_Request $request ) {

		$_request = wp_parse_args( $request->get_params(), [
			'limit'  => NULL,
			'offset' => NULL
		] );

        $data = [];

        $products = new \WP_Query([
            'post_type'      => 'sejoli-product',
            'posts_per_page' => $_request['limit'],
            'offset'         => $_request['offset']
        ]);

        if( $products->have_posts() ) :

            while( $products->have_posts() ) :

                $products->the_post();

                $data[] = [
                    'id'    => get_the_ID(),
                    'title' => sprintf( _x(' %s #%s', 'product-options', 'sejoli-rest-api'), get_the_title(), get_the_ID())
                ];

            endwhile;

        endif;

        wp_reset_query();

        if( ! empty( $data ) ) :

			return $this->respond_success( true, $data, 'Data successfully found', 200 );

		else:
			
			return $this->respond_error( 'invalid-data' );
		
		endif;

	}

	/**
	 * Update affiliates editor bonus data by user rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_update_affiliate_editor_bonus_data( \WP_REST_Request $request ) {

        $_request = wp_parse_args( $request->get_params(), [
        	'user_id'    => NULL,
			'content'    => NULL,
            'product_id' => NULL
		] );

		$data = [
            'valid'    => false,
            'messages' => []
        ];
        
        $current_user = wp_get_current_user();

        if ( isset( $current_user->ID ) && $current_user->ID > 0 && $_request['user_id'] === NULL ) :
            $_request['user_id'] = $current_user->ID;
        endif;

        if( !empty($_request['product_id']) ) :
            
            $affiliate_id = $_request['user_id'];

            if(!empty($_request['content'])) :

                update_user_meta($affiliate_id, '_sejoli_bonus_affiliate_'.$_request['product_id'], $_request['content']);

                $data = [
                    'valid' => true,
                    'messages' => [
                        __('Bonus konten berhasil diupdate', 'sejoli-rest-api')
                    ]
                ];

            else :

                $data['messages'][] = __('Konten bonus wajib diisi', 'sejoli-rest-api');

            endif;

        endif;

        if( false !== $data['valid'] ) :

			return $this->respond_success( true, $data, 'Data successfully updated!', 200 );
		
		else:

			return $this->respond_error( 'invalid-response', 'Please check your data entry!' );

		endif;
	
	}

}
