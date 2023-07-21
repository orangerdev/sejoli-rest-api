<?php
namespace Sejoli_Rest_Api\Rest;

/**
 * Main class responsible for rest api functions
 * @since   1.0.0
 */
class JointVenture extends \Sejoli_Rest_Api\Rest {

	/**
	 * Register class routes
	 * Hooked via action rest_api_init, priority 10
	 * @since    1.0.0
	 */
	public function do_register() {

		$routes = array(
			'jv-order' => array(
				'endpoint'			  => '/jv-order',
	    		'methods'		      => \WP_REST_Server::READABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_get_jv_order_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	            'args' => array(
					'product_id' => array(
						'type' => 'number',
					)
				)
	    	),
	    	'jv-mutasi' => array(
				'endpoint'			  => '/jv-mutasi',
	    		'methods'		      => \WP_REST_Server::READABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_get_jv_mutasi_data' ),
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
     * Set JV products related
     * @since   1.0.0
     * @param   integer     $product_requested
     * @return  integer|array
     */
    protected function set_products( $product_requested ) {

        $jv_data     = (array) get_user_meta( get_current_user_id(), 'sejoli_jv_data', true );
        $jv_products = wp_list_pluck( $jv_data, 'product_id' );

        if( 0 === count( $jv_products ) ) :

            return -999;

        else :

            if( in_array( $product_requested, $jv_products ) ) :
        
                return $product_requested;
        
            else :
        
                return $jv_products;
        
            endif;

        endif;

        return -999;

    }

	/**
	 * Get jv order data rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_get_jv_order_data( \WP_REST_Request $request ) {

		$_request = wp_parse_args( $request->get_params(), [
	        'product_id' => NULL	    
	    ] );

        $data        = [];
        $jv_products = array();
        $respond     = sejoli_jv_get_orders( $_request );

        if( false !== $respond['valid'] ) :

            $data = $respond['orders'];

        endif;      

		if( !empty( $data ) && false !== $respond['valid'] ) :

			return $this->respond_success( true, $data, 'Data successfully found', 200 );

		else:
			
			return $this->respond_error( 'invalid-data' );
		
		endif;

	}

	/**
     * Set JV note data
     * @since   1.0.0
     * @param   object   $jv [description]
     */
    protected function set_note( object $jv ) {

        $note = '';

        if( 'in' === $jv->type ) :

            $product_name = '';
            $product      = sejolisa_get_product( $jv->product_id );

            if( is_a($product, 'WP_Post') ) :
                $product_name = $product->post_title;
            else :
                $product_name = 'ID '.$jv->product_id.' '. __('(telah dihapus)', 'sejoli-rest-api');
            endif;

            $note = sprintf( __('Penjualan produk %s dari INV %s', 'sejoli-rest-api'), $product_name, $jv->order_id );

        else :

            $meta_data = maybe_unserialize( $jv->meta_data );
            $note      = $meta_data['note'];

        endif;

        return $note;
    
    }

	/**
	 * Get jv earning data rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_get_jv_mutasi_data( \WP_REST_Request $request ) {

		$_request = wp_parse_args( $request->get_params(), [
	        'user_id' => NULL	    
	    ] );

        $data = [];

        $jv_products = array();

        $user_id = (
                    !current_user_can('manage_options') ||
                    !isset( $_request['user_id'] )
                   ) ? get_current_user_id() : intval( $_request['user_id'] );

        $respond = sejoli_jv_get_single_user_data( $user_id, $_request );

        if( false !== $respond['valid'] ) :

            foreach( $respond['jv'] as $i => $jv ) :

                $data[$i] = (array) $jv;

                $data[$i]['note']        = $this->set_note( $jv );
                $data[$i]['created_at']  = date( 'Y M d', strtotime( $jv->created_at ) );
                $data[$i]['value']       = sejolisa_price_format( $jv->value );
                $data[$i]['raw_value']   = floatval( $jv->value );

            endforeach;

        endif;

		if( !empty( $data ) && false !== $respond['valid'] ) :

			return $this->respond_success( true, $data, 'Data successfully found', 200 );

		else:
			
			return $this->respond_error( 'invalid-data' );
		
		endif;

	}	

}