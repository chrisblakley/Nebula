<?php
	//Recommended WooCommerce Google Analytics Plugin: https://wordpress.org/plugins/enhanced-e-commerce-for-woocommerce-store/

	//Declare support for WooCommerce
	add_theme_support('woocommerce');

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

	//Set custom dimension and send event on order received page.
	add_action('nebula_ga_after_send_pageview', 'nebula_woo_custom_events');
	function nebula_woo_custom_events(){
		if ( is_order_received_page() ){
			if ( nebula_option('woo_ordered') ){
				echo 'ga("set", gaCustomDimensions["wooCustomer"], "Order Received");';
		    }
		    echo 'ga("send", "event", "Ecommerce", "Checkout", "Order Received");';
		}
	}