<?php
namespace Sejoli_Rest_Api\Rest;

/**
 * Main class responsible for rest api functions
 * @since   1.0.0
 */
class Donation extends \Sejoli_Rest_Api\Rest {

	/**
	 * Register class routes
	 * Hooked via action rest_api_init, priority 10
	 * @since    1.0.0
	 */
	public function do_register() {

		$routes = array(
			'sales' => array(
				'endpoint'			  => '/donation',
	    		'methods'		      => 'GET',
	    		'callback'			  => array( $this, 'get_donation_data' ),
	    		'permission_callback' => '__return_true',
	    	)
	    );

	    self::register_routes( $routes );
	
	}

	/**
	 * Get donation data rest request
	 * @param 	$data data from api request
	 * @return  array|WP_Error
	 * @since   1.0.0
	 */
	public function get_donation_data( $data ) {

		$args = array( 
		    'post_type'   => 'sejoli-product', 
		    'post_status' => 'publish', 
		    'nopaging'    => true ,
		    'meta_query'  => array(
		        array(
		            'key'     => '_donation_active',
		            'value'   => 'yes',
		            'compare' => '=',
		        ),
		    ),
		);

	    $query = new \WP_Query($args);
	    $posts = $query->get_posts();

		if( ! is_wp_error( $posts ) ) {

			$output = array();
		    foreach( $posts as $post ) {
		        
		        $product_id   = $post->ID;
		        $product      = sejolisa_get_product($product_id);
		        $progress     = sejolisa_get_donation_progress($product_id);
		        $donatur_list = sejolisa_get_donatur_list($product_id);

		        foreach($donatur_list as $list) :

		        	$donatur_name   = $list['name'];
	                $donatur_time   = $list['human_time'];
	                $total_donation = $list['total'];

	                if($list !== ""):
		                
		                $donatur[] = [
		                    'name'  => $donatur_name,
		                    'time' 	=> $donatur_time,
							'total' => $total_donation
		                ];
		            
		            else:
		            
		            	$donatur = [];
		            
		            endif;

                endforeach;

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

		        $output[] = array( 
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
		        	'affiliate'			=> $product->affiliate,
		        	'donation'			=> $product->donation,
		        	'donation_progress' => array(
		        								'percentage' => $progress['percent'].'%',
		        								'total'	     => $progress['total']
		        							),
		        	'donation_users'    => $donatur
		        );
		    
		    }
		    
		    return wp_send_json( $output ); // getting data in json format.

		}

		return $this->respond_error();

	}     

}
