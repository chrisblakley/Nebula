<?php
//Recommended WooCommerce Google Analytics Plugin: https://wordpress.org/plugins/enhanced-e-commerce-for-woocommerce-store/

//Declare support for WooCommerce
add_action('after_setup_theme', 'nebula_theme_setup_ecommerce');
function nebula_theme_setup_ecommerce(){
	add_theme_support('woocommerce');
}

//Replace WooCommerce start wrapper
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
add_action('woocommerce_before_main_content', 'custom_woocommerce_start', 10);
function custom_woocommerce_start(){
	echo '<section id="woocommerce" class="nebula-woocommerce">';
}

//Replace WooCommerce end wrapper
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
add_action('woocommerce_after_main_content', 'custom_woocommerce_end', 10);
function custom_woocommerce_end(){
	echo '</section>';
}

//WooCommerce admin notifications
add_action('admin_notices', 'nebula_woocommerce_admin_notices');
function nebula_woocommerce_admin_notices(){
	//Check for problematic plugin WooCommerce Google Analytics Integration
	if ( is_plugin_active('woocommerce-google-analytics-integration/woocommerce-google-analytics-integration.php') ){
		echo '<div class="nebula-admin-notice error"><p>It is recommended to deactivate and remove the plugin WooCommerce Google Analytics Integration in favor of the plugin Enhanced Ecommerce Google Analytics Plugin for WooCommerce. <a href="plugins.php">Manage Plugins &raquo;</a></p></div>';
	} elseif ( file_exists(WP_PLUGIN_DIR . '/woocommerce-google-analytics-integration') ){
		echo '<div class="nebula-admin-notice notice notice-info"><p>Notice: The plugin WooCommerce Google Analytics Integration is deactivated but should be removed entirely! <a href="plugins.php">Manage Plugins &raquo;</a></p></div>';
	}

	//Check for approved plugin Enhanced Ecommerce Google Analytics Plugin for WooCommerce
	if ( !file_exists(WP_PLUGIN_DIR . '/enhanced-e-commerce-for-woocommerce-store') ){
		echo '<div class="nebula-admin-notice notice notice-info"><p>WooCommerce is active, but the recommended plugin Enhanced Ecommerce Google Analytics Plugin for WooCommerce is not installed. <a href="themes.php?page=tgmpa-install-plugins">Install Recommended Plugins &raquo;</a></p></div>';
	} elseif ( !is_plugin_active('enhanced-e-commerce-for-woocommerce-store/woocommerce-enhanced-ecommerce-google-analytics-integration.php') ){
		echo '<div class="nebula-admin-notice notice notice-info"><p>WooCommerce is active, and the recommended plugin Enhanced Ecommerce Google Analytics Plugin for WooCommerce is installed but not activated. <a href="plugins.php">Manage Plugins &raquo;</a></p></div>';
	}
}

//Set custom dimensions before the Google Analytics pageview is sent. DO NOT send any events in this function!
add_action('nebula_ga_before_send_pageview', 'nebula_woo_custom_dimensions');
function nebula_woo_custom_dimensions(){
	//Set custom dimension for if the cart is empty or full
	if ( nebula_option('cd_woocart') ){
		echo 'gaCustomDimensions.wooCart = "' . nebula_option('cd_woocart') . '";'; //Add to the global custom dimension JavaScript object
		$cart_text = ( WC()->cart->get_cart_contents_count() >= 1 )? 'Full Cart (' . WC()->cart->get_cart_contents_count() . ')' : 'Empty Cart';
		echo 'ga("set", gaCustomDimensions["wooCart"], "' . $cart_text . '");';
	}
}

//Set dimensions and send events after the Google Analytics pageview is sent
add_action('nebula_ga_after_send_pageview', 'nebula_woo_custom_events');
function nebula_woo_custom_events(){
	echo 'nebula.site.ecommerce = true;'; //Set the ecommerce setting to true

	//Set custom dimension and send event on order received page.
	if ( is_order_received_page() ){
		if ( nebula_option('cd_woocustomer') ){
			echo 'gaCustomDimensions.wooCustomer = "' . nebula_option('cd_woocustomer') . '";'; //Add to the global custom dimension JavaScript object
			echo 'ga("set", gaCustomDimensions["wooCustomer"], "Order Received");';
	    }
	    echo 'ga("set", gaCustomDimensions["timestamp"], localTimestamp());';
	    echo 'ga("send", "event", "Ecommerce", "Order Received", "Order Received page load (Success from payment gateway)");';
	}
}

//Remove WooCommerce Breadcrumbs
//add_action('init', 'nebula_remove_woo_breadcrumbs');
function nebula_remove_woo_breadcrumbs() {
	remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0);
}

//Checkout visitor data
add_action('woocommerce_payment_complete', 'nebula_woocommerce_order_data');
function nebula_woocommerce_order_data($order_id){
	if ( nebula_option('visitors_db') ){
		$order = new WC_Order($order_id);

		//Append order ID and product IDs
		$products = array();
		$items = $order->get_items();
		foreach ( $items as $item ) {
		    $products['ecommerce_product_ids'] = $item['product_id'];
		}
		$products['ecommerce_order_id'] = $order_id;
		nebula_append_visitor($products);

		//Update Customer data
		nebula_update_visitor(array(
			'wp_role' => 'Customer',
			'email_address' => $order->billing_email,
			'first_name' => $order->billing_first_name,
			'last_name' => $order->billing_last_name,
			'full_name' => $order->billing_first_name . ' ' . $order->billing_last_name,
			'street_full' => $order->billing_address_1 . ' ' . $order->billing_address_2,
			'city' => $order->billing_city,
			'state_abbr' => $order->billing_state,
			'zip_code' => $order->billing_postcode,
			'country' => $order->billing_country,
			'phone_number' => $order->billing_phone,
		));
	}
}

//JSON-LD for Products
add_action('nebula_metadata_end', 'nebula_json_ld_ecommerce');
function nebula_json_ld_ecommerce(){
	$override = apply_filters('pre_nebula_json_ld_ecommerce', false);
	if ( $override !== false ){echo $override; return;}

	if ( is_product() ){ //if is product
		global $post;
		$product = new WC_Product($post->ID);

		$company_type = 'LocalBusiness'; //@TODO "Nebula" 0: Consider a Nebula Option for this type (LocalBusiness (default), Organization, etc)
		?>
			<script type="application/ld+json">
				{
					"@context": "http://schema.org/",
					"@type": "Product",
					"name": "<?php echo get_the_title(); ?>",

					<?php $post_thumbnail_meta = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full'); ?>
					"image": {
						"@type": "ImageObject",
						"url": "<?php echo $post_thumbnail_meta[0]; ?>",
						"width": "<?php echo $post_thumbnail_meta[1]; ?>",
						"height": "<?php echo $post_thumbnail_meta[2]; ?>"
					},

					"description": "<?php echo nebula_excerpt(array('length' => 100, 'more' => '', 'ellipsis' => false, 'structured' => false)); ?>",

					"offers": {
						"@type": "Offer",
						"priceCurrency": "USD",
						"price": "<?php echo $product->price; ?>",
						"itemCondition": "http://schema.org/NewCondition",
						"availability": "<?php echo ( $product->is_in_stock() )? 'http://schema.org/InStock' : 'http://schema.org/OutOfStock'; ?>",
						"seller": {
							"@type": "<?php echo $company_type; ?>",
							"name": "<?php echo ( nebula_option('site_owner') )? nebula_option('site_owner') : get_bloginfo('name'); ?>"
						}
					}
				}
			</script>
		<?php
	}
}