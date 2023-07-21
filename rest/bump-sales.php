<?php
namespace Sejoli_Rest_Api\Rest;

use Carbon\Carbon;

/**
 * Main class responsible for rest api functions
 * @since   1.0.0
 */
class BumpSales extends \Sejoli_Rest_Api\Rest {

	/**
	 * Register class routes
	 * Hooked via action rest_api_init, priority 10
	 * @since    1.0.0
	 */
	public function do_register() {

		$routes = array(
			'bump-sales' => array(
				'endpoint'			  => '/bump-sales/product',
	    		'methods'		      => \WP_REST_Server::READABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_get_bump_sales_product_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	            'args' => array(
					'product_id' => array(
						'type' => 'number',
					)
				)
	    	)
	    );

	    self::register_routes( $routes );
	
	}

	/**
	 * Get bump sales product data rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_get_bump_sales_product_data( \WP_REST_Request $request ) {

		$_request = wp_parse_args( $request->get_params(), [
	        'product_id' => NULL		
	    ] );

	    $product        = sejolisa_get_product( $_request['product_id'] );
		$product_format = sejolisa_carbon_get_post_meta( $product->ID, 'product_format' );
        $product_type   = sejolisa_carbon_get_post_meta( $product->ID, 'product_type' );
        
        if( $product_type === "digital" && $product_format === "main-product" ) {

            foreach( $product->bump_product as $key => $id_bump_product ) {

				$product_bump_sales      = sejolisa_get_product( $id_bump_product );
				$biaya_awal_bump_product = floatval( sejolisa_carbon_get_post_meta( $product_bump_sales->ID, 'subscription_signup_fee' ) );
				
				if( $biaya_awal_bump_product > 0 ) {
					
					$bump_product_price = ( $product_bump_sales->price + $biaya_awal_bump_product ) - $product->price;
				
				} else {

					$bump_product_price = $product_bump_sales->price - $product->price;
				
				}

        		$bump_product_total_price = floatval( $bump_product_price );

				$bump_sales_product[] = [
					'ID'      		  => $product_bump_sales->ID,
	                'image'   	      => get_the_post_thumbnail_url( $product_bump_sales->ID, 'full' ),
	                'price'     	  => sejolisa_price_format( $bump_product_price ),
	                'subtotal'  	  => sejolisa_price_format( $bump_product_total_price ),
	                'enable_quantity' => sejolisa_carbon_get_post_meta( $product_bump_sales->ID, 'enable_quantity' ),
					'product' 		  => $product_bump_sales
				];

            }

        }

		if( !empty( $bump_sales_product ) ) :

			return $this->respond_success( true, $bump_sales_product, 'Data successfully found', 200 );

		else:
			
			return $this->respond_error( 'invalid-data' );
		
		endif;

	}	

}