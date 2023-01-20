<?php
namespace Sejoli_Rest_Api\Rest;

/**
 * Main class responsible for rest api functions
 * @since   1.0.0
 */
class Products extends \Sejoli_Rest_Api\Rest {

	/**
	 * Register class routes
	 * Hooked via action rest_api_init, priority 10
	 * @since    1.0.0
	 */
	public function do_register() {

		$routes = array(
			'product' => array(
				'endpoint'			  => '/products',
	    		'methods'		      => \WP_REST_Server::READABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_get_products_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	            'args' => array(
					'limit' => array(
						'type' => 'number',
					),
				)
	    	),
	    	'product_detail' => array(
				'endpoint'			  => '/products/detail',
	    		'methods'		      => \WP_REST_Server::READABLE,
	    		'callback'			  => array( $this, 'sejoli_rest_get_product_detail_data' ),
	    		'permission_callback' => function () {
	                return is_user_logged_in();
	            },
	            'args' => array(
					'product_id' => array(
						'type' => 'number',
					),
				)
	    	)
	    );

	    self::register_routes( $routes );

	}

	/**
	 * Get product data rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_get_products_data( \WP_REST_Request $request ) {

		$_request = wp_parse_args( $request->get_params(), [
			'limit' => NULL,
		] );

		$args = array( 
		    'post_type'      => 'sejoli-product', 
		    'post_status'    => 'publish', 
		    'posts_per_page' => $_request['limit']
		);

	    $query = new \WP_Query($args);
	    $posts = $query->get_posts();

		if( ! is_wp_error( $posts ) ) {

			$output = array();
		    foreach( $posts as $post ) {
		        
		        $product_id              = $post->ID;
		        $product                 = sejolisa_get_product($product_id);
		        $featured_img_url        = get_the_post_thumbnail_url($product_id, 'full'); 
		        $product_active          = boolval(carbon_get_post_meta($product_id, 'enable_sale'));
				$product_price           = floatval(apply_filters( 'sejoli/product/price', 0, $post));
				$product_weight          = carbon_get_post_meta($product_id, 'shipment_weight');
				$product_type            = carbon_get_post_meta($product_id, 'product_type');
				$payment_type            = carbon_get_post_meta($product_id, 'payment_type');
				$biaya_awal              = carbon_get_post_meta($product_id, 'subscription_has_signup_fee');
				$harga_biaya_awal        = carbon_get_post_meta($product_id, 'subscription_signup_fee');
				$product_enable_quantity = boolval(carbon_get_post_meta($product_id, 'enable_quantity'));
				$product_access_code 	 = carbon_get_post_meta($product_id, 'coupon_access_checkout');
	            $variants                = carbon_get_post_meta($product_id, 'product_variants');

	            if (isset($variants[0]['variant'])):

		            foreach( $variants[0]['variant'] as $i => $_variant_type ) :

		                $variant_name          = $_variant_type['name'];
		                $variant_price         = floatval($_variant_type['extra_price']);
		                $display_variant_price = sejolisa_price_format($variant_price);

		                if($_variant_type !== ""):
			                
			                $product_variants[] = [
			                    'label' 	=> $variant_name,
			                    'price' 	=> (0.0 === $variant_price) ? NULL : $display_variant_price,
								'raw_price' => $variant_price,
								'weight'	=> intval($_variant_type['extra_weight'])
			                ];
			           
			            else:
			           
			            	$product_variants = [];
			           
			            endif;

		            endforeach;

		        endif;

		        $output = array(
		        	'valid' => true,
		        	'products' => array(
			        	'id' 				=> $product_id, 
			        	'author' 			=> $post->post_author,
			        	'date_created' 		=> $post->post_date,
			        	'date_created_gmt' 	=> $post->post_date_gmt,
			        	'date_modified' 	=> $post->post_modified,
			        	'date_modified_gmt' => $post->post_modified_gmt,
			        	'title' 			=> $post->post_title,
			        	'content' 			=> $post->post_content,
			        	'excerpt' 			=> $post->post_excerpt,
			        	'slug' 				=> $post->post_name,
			        	'permalink' 		=> $post->guid,
			        	'status' 			=> $post->post_status,
			        	'product_thumbnail' => $featured_img_url,
			        	'product_active' 	=> $product_active,
			        	'product_weight' 	=> $product_weight,
			        	'product_price' 	=> sejolisa_price_format( $product_price ),
			        	'product_raw_price' => $product_price,
			        	'product_type' 		=> $product_type,
			        	'payment_type' 		=> $payment_type,
			        	'biaya_awal' 		=> $biaya_awal,
			        	'harga_biaya_awal' 	=> $harga_biaya_awal,
			        	'subscription'		=> $product->subscription,
			        	'license'			=> $product->license,
			        	'affiliate'			=> $product->affiliate,
			        	'files'				=> $product->files,
			        	'reward_point'		=> $product->reward_point,
			        	'cashback'			=> $product->cashback,
			        	'donation'			=> $product->donation,
			        	'enable_quantity' 	=> $product_enable_quantity,
			        	'access_code' 		=> $product_access_code,
			        	'variants' 			=> array(
			        								'type'    => isset($variants[0]['_type']) ? $variants[0]['_type'] : '',
			        								'name'    => isset($variants[0]['name']) ? $variants[0]['name'] : '',
			        								'variant' => isset($product_variants) ? $product_variants : ''
			        							)
			        )
			    );
		    }
		    
		    if( !empty($output) ):

		    	return $this->respond_success( true, $output, 'Data successfully found', 200 );

		    else:

		    	return $this->respond_error( 'invalid-data' );

		    endif;

		} else {
			
			return $this->respond_error( 'invalid-data' );
		
		}

	}

	/**
	 * Get product data rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function sejoli_rest_get_product_detail_data( \WP_REST_Request $request ) {

		$_request = wp_parse_args( $request->get_params(), [
			'product_id' => NULL,
		] );

		$args = array( 
		    'post_type'   => 'sejoli-product', 
		    'post_status' => 'publish', 
		    'p' 		  => $_request['product_id'] 
		);

	    $query = new \WP_Query($args);
	    $posts = $query->get_posts();

		if( ! is_wp_error( $posts ) ) {

			$output = array();
		    foreach( $posts as $post ) {
		        
		        $product_id              = $post->ID;
		        $product                 = sejolisa_get_product($product_id);
		        $featured_img_url        = get_the_post_thumbnail_url($product_id, 'full'); 
		        $product_active          = boolval(carbon_get_post_meta($product_id, 'enable_sale'));
				$product_price           = floatval(apply_filters( 'sejoli/product/price', 0, $post));
				$product_weight          = carbon_get_post_meta($product_id, 'shipment_weight');
				$product_type            = carbon_get_post_meta($product_id, 'product_type');
				$payment_type            = carbon_get_post_meta($product_id, 'payment_type');
				$biaya_awal              = carbon_get_post_meta($product_id, 'subscription_has_signup_fee');
				$harga_biaya_awal        = carbon_get_post_meta($product_id, 'subscription_signup_fee');
				$product_enable_quantity = boolval(carbon_get_post_meta($product_id, 'enable_quantity'));
				$product_access_code 	 = carbon_get_post_meta($product_id, 'coupon_access_checkout');
	            $variants                = carbon_get_post_meta($product_id, 'product_variants');

	            if (isset($variants[0]['variant'])):

		            foreach( $variants[0]['variant'] as $i => $_variant_type ) :

		                $variant_name          = $_variant_type['name'];
		                $variant_price         = floatval($_variant_type['extra_price']);
		                $display_variant_price = sejolisa_price_format($variant_price);

		                if($_variant_type !== ""):
			                
			                $product_variants[] = [
			                    'label' 	=> $variant_name,
			                    'price' 	=> (0.0 === $variant_price) ? NULL : $display_variant_price,
								'raw_price' => $variant_price,
								'weight'	=> intval($_variant_type['extra_weight'])
			                ];
			           
			            else:
			           
			            	$product_variants = [];
			           
			            endif;

		            endforeach;

		        endif;

		        $output = array(
		        	'valid' => true,
		        	'products' => array(
			        	'id' 				=> $product_id, 
			        	'author' 			=> $post->post_author,
			        	'date_created' 		=> $post->post_date,
			        	'date_created_gmt' 	=> $post->post_date_gmt,
			        	'date_modified' 	=> $post->post_modified,
			        	'date_modified_gmt' => $post->post_modified_gmt,
			        	'title' 			=> $post->post_title,
			        	'content' 			=> $post->post_content,
			        	'excerpt' 			=> $post->post_excerpt,
			        	'slug' 				=> $post->post_name,
			        	'permalink' 		=> $post->guid,
			        	'status' 			=> $post->post_status,
			        	'product_thumbnail' => $featured_img_url,
			        	'product_active' 	=> $product_active,
			        	'product_weight' 	=> $product_weight,
			        	'product_price' 	=> sejolisa_price_format( $product_price ),
			        	'product_raw_price' => $product_price,
			        	'product_type' 		=> $product_type,
			        	'payment_type' 		=> $payment_type,
			        	'biaya_awal' 		=> $biaya_awal,
			        	'harga_biaya_awal' 	=> $harga_biaya_awal,
			        	'subscription'		=> $product->subscription,
			        	'license'			=> $product->license,
			        	'affiliate'			=> $product->affiliate,
			        	'files'				=> $product->files,
			        	'reward_point'		=> $product->reward_point,
			        	'cashback'			=> $product->cashback,
			        	'donation'			=> $product->donation,
			        	'enable_quantity' 	=> $product_enable_quantity,
			        	'access_code' 		=> $product_access_code,
			        	'variants' 			=> array(
			        								'type'    => isset($variants[0]['_type']) ? $variants[0]['_type'] : '',
			        								'name'    => isset($variants[0]['name']) ? $variants[0]['name'] : '',
			        								'variant' => isset($product_variants) ? $product_variants : ''
			        							)
			        )
			    );
		    }
		    
		    if( !empty($output) ):

		    	return $this->respond_success( true, $output, 'Data successfully found', 200 );

		    else:

		    	return $this->respond_error( 'invalid-data' );

		    endif;

		} else {
			
			return $this->respond_error( 'invalid-data' );
		
		}

	}

}
