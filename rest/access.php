<?php
namespace Sejoli_Rest_Api\Rest;

use Carbon\Carbon;

/**
 * Main class responsible for rest api functions
 * @since   1.0.0
 */
class Access extends \Sejoli_Rest_Api\Rest {

	/**
     * Product data from order and subscription
     * @since   1.0.0
     * @var     array
     */
    protected $access_products = array();

    /**
     * All product IDs
     * @since   1.0.0
     * @var     array
     */
    protected $product_ids = NULL;

    /**
     * All content IDs
     * @since   1.0.0
     * @var     array
     */
    protected $content_ids = NULL;

    /**
     * All bonus data
     * @since   1.0.0
     * @var     array
     */
    protected $bonus_contents = [];

	/**
	 * Register class routes
	 * Hooked via action rest_api_init, priority 10
	 * @since    1.0.0
	 */
	public function do_register() {

		$routes = array(
			'sales' => array(
				'endpoint'			  => '/access',
	    		'methods'		      => \WP_REST_Server::READABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_get_access_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	            'args' => array(
					'user_id' => array(
						'type' => 'number',
					)
				)
	    	)
	    );

	    self::register_routes( $routes );
	
	}

	/**
     * Set all products data
     * @since   1.0.0
     * @param   array $products [description]
     * @return  void
     */
    protected function set_product_access_data( $products ) {

        foreach( $products as $product ) :
            
            $active_order_time = ( 0 > strtotime($product->updated_at)) ? $product->created_at : $product->updated_at;

            $dt = Carbon::parse( $active_order_time )->diffInDays( Carbon::now() );
            $attachments = sejolisa_carbon_get_post_meta( $product->product_id, 'attachments' );

            $this->access_products[$product->product_id] = [
                'since_day'    => intval( $dt ),
                'active_time'  => $active_order_time,
                'product_id'   => $product->product_id,
                'product_name' => $product->product_name,
                'content'      => [],
                'attachments'  => apply_filters( 'sejoli/attachments/links', $attachments, $product->product_id )
            ];

            $this->product_ids[] = $product->product_id;

        endforeach;
    
    }

    /**
     * Set all bonus data
     * @since   1.0.0
     * @param   array   $affiliates
     */
    protected function set_bonus_access_data( $affiliates ) {

        foreach( $affiliates as $affiliate ) :

            $content    = get_user_meta( $affiliate->affiliate_id, '_sejoli_bonus_affiliate_' .  $affiliate->product_id, true );
            $product_id = $affiliate->product_id;

            if( $content && isset( $this->access_products[$product_id] ) ) :

                if( !array_key_exists( 'bonus', $this->access_products[$product_id] ) ) :
                    $this->access_products[$product_id]['bonus'] = array();
                endif;

                $this->access_products[$product_id]['bonus'][$affiliate->affiliate_id] = [
                    'name'         => $affiliate->affiliate_name,
                    'affiliate_id' => $affiliate->affiliate_id,
                    'product_id'   => $affiliate->product_id
                ];
    
            endif;

        endforeach;
    
    }

    /**
     * Set all bonus affiliate data
     * @since   1.0.0
     * @param   array   $affiliates
     */
    protected function set_bonus_access_affiliate_data( $affiliates ) {

        foreach( $affiliates as $affiliate) :

            $content    = get_user_meta( $affiliate->affiliate_id, '_sejoli_bonus_affiliate_' .  $affiliate->product_id, true );
            $product_id = $affiliate->product_id;

            if( $content ) :

                if( !array_key_exists( $product_id, $this->access_products ) ) :
                    $this->access_products[$product_id]= array();
                endif;

                $this->access_products[$product_id][$affiliate->affiliate_id] = [
                    'title'   => sprintf( __('Bonus dari %s', 'sejoli'), $affiliate->affiliate_name ),
                    'content' => $content,
                ];
    
            endif;

        endforeach;
    
    }

	/**
     * Get all access ID
     * @since   1.0.0
     * @return  void
     */
    protected function get_access_ids() {

        if( is_array( $this->product_ids ) && 0 < count( $this->product_ids ) ) :

            global $wpdb;

            $query  = "SELECT post_id FROM ".$wpdb->postmeta." WHERE meta_key LIKE '_product_association|||_|id' AND meta_value IN (".implode(',', $this->product_ids).")";
            $result = $wpdb->get_results( $query );

            if( $result ) :
    
                foreach( $result as $_data ) :
    
                    if( !is_array( $this->content_ids ) || !in_array( $_data->post_id, $this->content_ids ) ) :
    
                        $this->content_ids[] = $_data->post_id;
    
                    endif;
    
                endforeach;
    
            endif;

        endif;
    
    }

    /**
     * Get all access content
     * @since   1.0.0
     * @return  void
     */
    protected function get_access_content() {

        if( is_array( $this->content_ids ) && 0 < count( $this->content_ids ) ) :

            $contents = get_posts([
                'post_type'      => 'sejoli-access',
                'include'        => $this->content_ids,
                'posts_per_page' => -1,
            ]);

            foreach( $contents as $content ) :
                
                $drip_day = intval( sejolisa_carbon_get_post_meta($content->ID, 'drip_day') );
                $products = sejolisa_carbon_get_post_meta($content->ID, 'product_association');

                foreach( $products as $product ) :

                    // set content
                    if( isset($this->access_products[$product['id']]) ) :

                        $bought_day = $this->access_products[$product['id']]['since_day'];

                        if( $drip_day <= $bought_day ) :
                            $this->access_products[$product['id']]['content'][] = [
                                'id'    => $content->ID,
                                'link'  => get_permalink($content->ID),
                                'title' => $content->post_title
                            ];
                        endif;

                    endif;

                endforeach;

            endforeach;

        endif;

    }

	/**
	 * Get access data rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_get_access_data( \WP_REST_Request $request ) {

		$_request = wp_parse_args( $request->get_params(), [
	        'user_id' => NULL	
	    ] );

		$data = [];

        $current_user = wp_get_current_user();

        if ( isset( $current_user->ID ) && $current_user->ID > 0 && $_request['user_id'] === NULL ) :
            $_request['user_id'] = $current_user->ID;
        endif;

        // SET PRODUCTS
        $products_by_order = sejolisa_get_product_by_orders( $_request['user_id'] );

        if(
            false !== $products_by_order['valid'] &&
            0 < count( $products_by_order['products'] )
        ) :
            $this->set_product_access_data( $products_by_order['products'] );
        endif;

        $products_by_subscription = sejolisa_get_product_by_subscriptions( $_request['user_id'] );

        if(
            false !== $products_by_subscription['valid'] &&
            0 < count( $products_by_subscription['products'] )
        ) :
            $this->set_product_access_data( $products_by_subscription['products'] );
        endif;

        // SET AFFILIATE
        $affiliates_by_order = sejolisa_get_affiliate_by_orders( $_request['user_id'] );

        if(
            false !== $affiliates_by_order['valid'] &&
            0 < count( $affiliates_by_order['affiliates'] )
        ) :
            $this->set_bonus_access_data( $affiliates_by_order['affiliates'] );
        endif;

        $affiliates_by_subscription = sejolisa_get_affiliate_by_subscriptions( $_request['user_id'] );

        if(
            false !== $affiliates_by_subscription['valid'] &&
            0 < count( $affiliates_by_subscription['affiliates'] )
        ) :
            $this->set_bonus_access_data( $affiliates_by_subscription['affiliates'] );
        endif;

        $this->get_access_ids();
        $this->get_access_content();

        $data = $this->access_products;

		if( !empty( $data ) ) :

			return $this->respond_success( true, $data, 'Data successfully found', 200 );

		else:
			
			return $this->respond_error( 'invalid-data' );
		
		endif;

	}	

}