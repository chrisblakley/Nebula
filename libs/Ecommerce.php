<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

//Recommended WooCommerce Google Analytics Plugin: https://wordpress.org/plugins/enhanced-e-commerce-for-woocommerce-store/

if ( !trait_exists('Ecommerce') ){
	trait Ecommerce {
		public function hooks(){
			//Declare support for WooCommerce
			add_action('after_setup_theme', array($this, 'theme_setup_ecommerce'));

			//Replace WooCommerce start wrapper
			remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
			add_action('woocommerce_before_main_content', array($this, 'custom_woocommerce_start'), 10);

			//Replace WooCommerce end wrapper
			remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
			add_action('woocommerce_after_main_content', array($this, 'custom_woocommerce_end'), 10);

			//WooCommerce admin notifications
			add_action('admin_notices', array($this, 'woocommerce_admin_notices'));

			//Set custom dimensions before the Google Analytics pageview is sent. DO NOT send any events in this function!
			add_action('nebula_ga_before_send_pageview', array($this, 'woo_custom_ga_dimensions'));

			//Set dimensions and send events after the Google Analytics pageview is sent
			add_action('nebula_ga_after_send_pageview', array($this, 'woo_custom_ga_events'));

			//Remove WooCommerce Breadcrumbs
			//add_action('init', array($this, 'remove_woo_breadcrumbs'));

			//Checkout visitor data
			add_action('woocommerce_payment_complete', array($this, 'woocommerce_order_data'));

			//JSON-LD for Products
			add_action('nebula_metadata_end', array($this, 'json_ld_ecommerce'));
		}

		//Declare support for WooCommerce
		public function theme_setup_ecommerce(){
			add_theme_support('woocommerce');
		}

		//Replace WooCommerce start wrapper
		public function custom_woocommerce_start(){
			echo '<section id="woocommerce" class="nebula-woocommerce">';
		}

		//Replace WooCommerce end wrapper
		public function custom_woocommerce_end(){
			echo '</section>';
		}

		//WooCommerce admin notifications
		public function woocommerce_admin_notices(){
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
				echo '<div class="nebula-admin-notice notice notice-info"><p>WooCommerce is active, but while the recommended plugin Enhanced Ecommerce Google Analytics Plugin for WooCommerce is installed, it is not activated. <a href="plugins.php">Manage Plugins &raquo;</a></p></div>';
			}
		}

		//Set custom dimensions before the Google Analytics pageview is sent. DO NOT send any events in this function!
		public function woo_custom_ga_dimensions(){
			//Set custom dimension for if the cart is empty or full
			if ( $this->get_option('cd_woocart') ){
				echo 'gaCustomDimensions.wooCart = "' . $this->get_option('cd_woocart') . '";'; //Add to the global custom dimension JavaScript object
				$cart_text = ( WC()->cart->get_cart_contents_count() >= 1 )? 'Full Cart (' . WC()->cart->get_cart_contents_count() . ')' : 'Empty Cart';
				echo 'ga("set", gaCustomDimensions["wooCart"], "' . $cart_text . '");';
			}
		}

		//Set dimensions and send events after the Google Analytics pageview is sent
		public function woo_custom_ga_events(){
			echo 'nebula.site.ecommerce = true;'; //Set the ecommerce setting to true

			//Set custom dimension and send event on order received page.
			if ( is_order_received_page() ){
				if ( $this->get_option('cd_woocustomer') ){
					echo 'gaCustomDimensions.wooCustomer = "' . $this->get_option('cd_woocustomer') . '";'; //Add to the global custom dimension JavaScript object
					echo 'ga("set", gaCustomDimensions["wooCustomer"], "Order Received");';
				}
				echo 'ga("send", "event", "Ecommerce", "Order Received", "Order Received page load (Success from payment gateway)");';
			}
		}

		//Remove WooCommerce Breadcrumbs
		public function remove_woo_breadcrumbs() {
			remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0);
		}

		//Checkout visitor data
		public function woocommerce_order_data($order_id){
			if ( $this->get_option('visitors_db') ){
				$order = new WC_Order($order_id);

				//Append order ID and product IDs
				$products = array();
				$items = $order->get_items();
				foreach ( $items as $item ) {
					$products['ecommerce_product_ids'] = $item['product_id'];
				}
				$products['ecommerce_order_id'] = $order_id;
				$this->append_visitor($products);

				//Update Customer data
				$this->update_visitor_data(array(
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
		public function json_ld_ecommerce(){
			$override = apply_filters('pre_nebula_json_ld_ecommerce', false);
			if ( !empty($override) ){echo $override; return;}

			if ( function_exists('is_product') && is_product() ){ //if is product
				global $post;
				$product = new WC_Product($post->ID);

				$company_type = ( $this->get_option('business_type') )? $this->get_option('business_type') : 'LocalBusiness';
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

						"description": "<?php echo $this->excerpt(array('words' => 100, 'more' => '', 'ellipsis' => false, 'structured' => false)); ?>",

						"offers": {
							"@type": "Offer",
							"priceCurrency": "USD",
							"price": "<?php echo $product->price; ?>",
							"itemCondition": "http://schema.org/NewCondition",
							"availability": "<?php echo ( $product->is_in_stock() )? 'http://schema.org/InStock' : 'http://schema.org/OutOfStock'; ?>",
							"seller": {
								"@type": "<?php echo $company_type; ?>",
								"name": "<?php echo ( $this->get_option('site_owner') )? $this->get_option('site_owner') : get_bloginfo('name'); ?>",
								"image": "<?php echo get_theme_file_uri('/assets/img/logo.png'); ?>",
								"telephone": "+<?php echo $this->get_option('phone_number'); ?>",
								<?php if ( $company_type === 'LocalBusiness' ): ?>
									"priceRange": "",
								<?php endif; ?>
								"address": {
									"@type": "PostalAddress",
									"streetAddress": "<?php echo $this->get_option('street_address'); ?>",
									"addressLocality": "<?php echo $this->get_option('locality'); ?>",
									"addressRegion": "<?php echo $this->get_option('region'); ?>",
									"postalCode": "<?php echo $this->get_option('postal_code'); ?>",
									"addressCountry": "<?php echo $this->get_option('country_name'); ?>"
								}
							}
						}
					}
				</script>
				<?php
			}
		}
	}
}