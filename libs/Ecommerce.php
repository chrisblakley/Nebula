<?php

if ( !defined('ABSPATH') ){ die(); } //Exit if accessed directly

//Recommended WooCommerce Google Analytics 4 Plugin: https://wordpress.org/plugins/woocommerce-google-analytics-integration/

if ( !trait_exists('Ecommerce') ){
	trait Ecommerce {
		public function hooks(){
			if ( !is_customize_preview() ){
				//Register this script without using it so that Woocommerce Google Analytics does not block the Nebula GA send_page_view.
				add_action('wp_enqueue_scripts', function(){
					wp_register_script('google-tag-manager', 'https://www.googletagmanager.com/gtag/js?id=' . esc_html(nebula()->get_option('ga_tracking_id')), array(), nebula()->child_version(), false); //Remember: This script is not actually used anywhere! This is only to prevent Woocommerce from sending its own page_view to GA4.
				}, 1);

				add_action('after_setup_theme', array($this, 'theme_setup_ecommerce'));
				remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
				add_action('woocommerce_before_main_content', array($this, 'custom_woocommerce_start'), 10);
				remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
				add_action('woocommerce_after_main_content', array($this, 'custom_woocommerce_end'), 10);

				add_filter('nebula_brain', array($this, 'ecommerce_nebula_data'));

				//add_action('init', array($this, 'remove_woo_breadcrumbs'));
				add_action('woocommerce_payment_complete', array($this, 'woocommerce_order_data'));
				add_action('nebula_metadata_end', array($this, 'json_ld_ecommerce'));

				if ( is_user_logged_in() ){
					add_filter('nebula_warnings', array($this, 'woocommerce_admin_notices'));
				}

				if ( !$this->is_background_request() ){
					add_action('nebula_ga_before_pageview', array($this, 'woo_custom_ga_dimensions'));
					add_action('nebula_ga_after_pageview', array($this, 'woo_custom_ga_events'));
				}
			}
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
		public function woocommerce_admin_notices($nebula_warnings){
			//Check for problematic plugin WooCommerce Google Analytics Integration

			//Note: May 2023 - Commented these all out as we need to start from scratch here with GA4. Everything is back on the table.
				//This one (by Woocommerce itself) seems to be the best for GA4 without the bloat: https://wordpress.org/plugins/woocommerce-google-analytics-integration/

// 			if ( is_plugin_active('woocommerce-google-analytics-integration/woocommerce-google-analytics-integration.php') ){
// 				$nebula_warnings['ecommerce_bad_ga_plugin'] = array(
// 					'level' => 'error',
// 					'description' => '<i class="fa-regular fa-fw fa-credit-card"></i> It is recommended to deactivate and remove the plugin <a href="plugins.php">WooCommerce Google Analytics Integration</a> in favor of the plugin <a href="https://wordpress.org/plugins/enhanced-e-commerce-for-woocommerce-store/" target="_blank" rel="noopener noreferrer">Google Analytics and Google Shopping plugin for WooCommerce</a>. <a href="plugins.php">Manage Plugins &raquo;</a>'
// 				);
// 			} elseif ( file_exists(WP_PLUGIN_DIR . '/woocommerce-google-analytics-integration') ){
// 				$nebula_warnings['ecommerce_bad_ga_plugin'] = array(
// 					'level' => 'warn',
// 					'description' => '<i class="fa-regular fa-fw fa-credit-card"></i> The plugin WooCommerce Google Analytics Integration is deactivated but should be removed entirely! <a href="plugins.php">Manage Plugins &raquo;</a>'
// 				);
// 			}
//
// 			//Check for approved plugin Google Analytics and Google Shopping plugin for WooCommerce
// 			if ( !file_exists(WP_PLUGIN_DIR . '/enhanced-e-commerce-for-woocommerce-store') ){
// 				$nebula_warnings['ecommerce_good_ga_plugin'] = array(
// 					'level' => 'warn',
// 					'description' => '<i class="fa-regular fa-fw fa-credit-card"></i> WooCommerce is active, but the recommended plugin <a href="https://wordpress.org/plugins/enhanced-e-commerce-for-woocommerce-store/" target="_blank" rel="noopener noreferrer">Google Analytics and Google Shopping plugin for WooCommerce</a> is not installed. <a href="themes.php?page=tgmpa-install-plugins">Install Recommended Plugins &raquo;</a>'
// 				);
// 			} elseif ( !is_plugin_active('enhanced-e-commerce-for-woocommerce-store/enhanced-ecommerce-google-analytics.php') ){
// 				$nebula_warnings['ecommerce_good_ga_plugin'] = array(
// 					'level' => 'warn',
// 					'description' => '<i class="fa-regular fa-fw fa-credit-card"></i> WooCommerce is active, but while the recommended plugin <a href="plugins.php">Google Analytics and Google Shopping plugin for WooCommerce</a> is installed, it is not activated. <a href="plugins.php">Manage Plugins &raquo;</a>'
// 				);
// 			}

			return $nebula_warnings;
		}

		//Add custom dimensions/metrics to the Nebula object
		public function ecommerce_nebula_data($brain){
			$brain['site']['ecommerce'] = true;
			return $brain;
		}

		//Set custom dimensions before the Google Analytics pageview is sent. DO NOT send any events in this function!
		public function woo_custom_ga_dimensions(){
			//Set custom dimension for if the cart is empty or full
			if ( !empty(WC()->cart) ){
				$cart_text = ( WC()->cart->get_cart_contents_count() >= 1 )? 'Full Cart (' . WC()->cart->get_cart_contents_count() . ')' : 'Empty Cart';

				echo 'gtag("set", "user_properties", {
					woocommerce_cart : "' . $cart_text . '"
				});';
			}
		}

		//Set dimensions and send events after the Google Analytics pageview is sent
		public function woo_custom_ga_events(){
			echo 'nebula.site.ecommerce = true;'; //Set the ecommerce setting to true

			//View product detail page
			if ( is_product() ){
				global $product;

				if ( !is_object($product) ){ //The global $product is only an object of class WC_Product when the_post() is used. Else, it comes over as a string.
					$product = wc_get_product(get_the_ID());
				}

				$variation = wc_get_product($product->get_variation_id()); //If no variation, this will appear the same as the product itself

				$product_item = array(
					'item_id' => $product->get_id(),
					'item_name' => $product->get_name(),
					'item_variant' => $variation->get_name(),
					'currency' => 'USD',
					'price' => $product->get_price(),
					'quantity' => 1,
				);

				echo 'gtag("event", "view_item", {
					event_category: "Ecommerce",
					event_action: "View Item",
					event_label: "Product: ' . $product->get_name() . ' (ID: ' . $product->get_id() . ')",
					value: "' . $product->get_price() . '",
					currency: "USD",
					items: [' . wp_json_encode($product_item) . '],
					non_interaction: true
				});';
			}

			//View Cart and Checkout pages
			if ( is_cart() || is_checkout() && is_dev() ){
				$page_type = '';
				if ( is_cart() ){
					$page_type = 'view_cart';
				} elseif ( is_checkout_pay_page() ){
					$page_type = 'add_payment_info';
				} elseif ( is_checkout() ){
					$page_type = 'begin_checkout';
				}

				global $woocommerce;
				$cart_items = $woocommerce->cart->get_cart();
				$cart_total = $woocommerce->cart->get_cart_contents_total();

				$product_items = array(); //Prep this for the GA payload
				$index = 0;
				foreach( $cart_items as $cart_item => $item_properties ){ //Loop through all of the items in the cart
					$product = wc_get_product($item_properties['data']->get_id());
					$variation = wc_get_product($product->get_variation_id()); //If no variation, this will appear the same as the product itself

					$item_variant = '';
					if ( !empty($variation) ){
						$item_variant = $variation->get_name();
					}

					$product_items[] = array(
						'item_id' => $item_properties['data']->get_id(),
						'item_name' => $product->get_name(),
						'item_variant' => $item_variant,
						'currency' => 'USD',
						'price' => get_post_meta($item_properties['product_id'], '_price', true),
						'quantity' => $item_properties['quantity'],
						'index' => $index, //This just counts up for each unique item in the cart
					);

					$index++;
				}

				echo 'gtag("event", "' . $page_type . '", {
					event_category: "Ecommerce",
					event_action: "Checkout: ' . $page_type . '",
					event_label: "Cart Total: ' . $cart_total . ' (' . count($product_items) . ' items)",
					value: "' . $cart_total . '",
					currency: "USD",
					items: ' . wp_json_encode($product_items) . ',
					non_interaction: true
				});';
			}

			if ( is_order_received_page() ){
				echo 'gtag("event", "order_received", { //This event is to provide redundancy for the "purchase" event
					event_category: "Ecommerce",
					event_action: "Order Received",
					event_label: "Order Received page load (Success from payment gateway)"
				});';
			}
		}

		//Remove WooCommerce Breadcrumbs
		public function remove_woo_breadcrumbs() {
			remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0);
		}

		//Checkout visitor data
		public function woocommerce_order_data($order_id){
			$order = new WC_Order($order_id);

			echo '<script>';
			echo 'gtag("set", "user_properties", {
				woocommerce_customer : "Order Received"
			});';

			$product_items = array(); //Prep this for the GA payload
			$index = 0;
			foreach ( $order->get_items() as $item_id => $item ){ //Loop through all of the items in the order
				$variation = wc_get_product($item->get_variation_id()); //If no variation, this will appear the same as the product itself

				$item_variant = '';
				if ( !empty($variation) ){
					$item_variant = $variation->get_name();
				}

				$product_items[] = array(
					'item_id' => $item->get_product_id(),
					'item_name' => $item->get_name(),
					'currency' => 'USD',
					'item_variant' => $item_variant,
					'price' => $item->get_meta('_price', true),
					'quantity' => $item->get_quantity(),
					'index' => $index,
				);

				$index++;
			}

			echo 'gtag("event", "nebula_purchase", {
				event_category: "Ecommerce",
				event_action: "Purchase (Nebula)",
				event_label: "Order ID: ' . $order->get_id() . ' (' . $order->get_total() . ')",
				transaction_id: "' . $order->get_id() . '",
				value: ' . $order->get_total() . ', //Grand total price
				tax: ' . $order->get_total_tax() . ',
				shipping: ' . $order->get_shipping_total() . ',
				currency: "' . $order->get_currency() . '",
				items: ' . wp_json_encode($product_items) . '
			});';

			echo '</script>';

			if ( $this->get_option('hubspot_portal') ){
				$order = new WC_Order($order_id);
				?>

				<?php if ( 1==2 ): //@todo "Nebula" 0: See if this script tag will break anything! If this can't be done here, try the above "woo_custom_ga_events" function... but can order details be accessed from that page? ?>
				<script>
					<?php //Don't use nv() here because this is being included with the initial Hubspot data before the pageview is sent ?>
					_hsq.push(["identify", {
						role: 'Customer',
						email: '<?php echo $order->billing_email; ?>',
						firstname: '<?php echo $order->billing_first_name; ?>',
						lastname: '<?php echo $order->billing_last_name; ?>',
						full_name: '<?php echo $order->billing_first_name . ' ' . $order->billing_last_name; ?>',
						street_full: '<?php echo $order->billing_address_1 . ' ' . $order->billing_address_2; ?>',
						city: '<?php echo $order->billing_city; ?>',
						state: '<?php echo $order->billing_state; ?>',
						zipcode: '<?php echo $order->billing_postcode; ?>',
						country: '<?php echo $order->billing_country; ?>',
						phone: '<?php echo $order->billing_phone; ?>',
					}]);
				</script>
				<?php endif; ?>
				<?php
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
						"name": "<?php echo esc_html(get_the_title()); ?>",

						<?php $post_thumbnail_meta = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full'); ?>
						"image": {
							"@type": "ImageObject",
							"url": "<?php echo $post_thumbnail_meta[0]; ?>",
							"width": "<?php echo $post_thumbnail_meta[1]; ?>",
							"height": "<?php echo $post_thumbnail_meta[2]; ?>"
						},

						"description": <?php echo wp_json_encode($this->excerpt(array('words' => 100, 'more' => '', 'ellipsis' => false, 'structured' => false))); ?>,

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