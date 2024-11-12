<?php

/*
Plugin Name: Conditional Currency and Payments for Woocommerce
Plugin URI: https://github.com/maxitromer/conditional-currency-and-payments
Description: Change the (auto-updated) currency and select between your Payment Gateways based on conditions
Version: 0.2.1
Author: Maxi Tromer
Author URI: https://github.com/maxitromer
Developer: Maxi Tromer
Developer URI: https://github.com/maxitromer
GitHub Plugin URI: https://github.com/maxitromer/conditional-currency-and-payments
WC requires at least: 3.0
WC tested up to: 4.5.2
Text Domain: conditional-currency-and-payments
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	if ( is_admin() ){
		if (! ( get_option( 'country_based_payments_woocommerce_settings_currencyconverterapi' ) ) ) {
			add_action( 'admin_notices', 'country_based_payments_woocommerce_admin_notice_no_configuration' );

			save_currency ( 'USD', 'ARS' );
		}
		add_filter( 'woocommerce_settings_tabs_array', 'country_based_payments_woocommerce_add_settings_tab', 50 );
		add_action( 'woocommerce_settings_tabs_country_based_payments', 'country_based_payments_woocommerce_settings_tab' );
		add_action( 'woocommerce_update_options_country_based_payments', 'country_based_payments_woocommerce_settings_tab_update' );
	}
	else {
	}
}

// PLUGIN MANAGEMENT

function country_based_payments_woocommerce_add_settings_tab($settings_tabs) {

	$settings_tabs['country_based_payments'] = __( 'Payments by Location', 'country-based-payments' );
	return $settings_tabs;
}

function country_based_payments_woocommerce_settings_tab() {
    woocommerce_admin_fields( country_based_payments_woocommerce_tab_settings() );
}

function country_based_payments_woocommerce_settings_tab_update() {
    woocommerce_update_options( country_based_payments_woocommerce_tab_settings() );
}

function country_based_payments_woocommerce_tab_settings() {

	if ( get_option( 'usd_to_ars_rate' ) ) {

		$dolar_rate = 'The actual dolar rate (in AR$) this site is using is U$D ' . get_option( 'usd_to_ars_rate' );

	} else {
		
		$dolar_rate = 'No data yet.';

		save_currency ( 'USD', 'ARS' );

	}

	$settings = array(

		'country_based_payments_section_title' => array(
			'name' => __('Currency Converter Settings', 'country-based-payments'),
			'type' => 'title',
			'desc' => __('Set the Access API for every currency converter service.', 'country-based-payments'),
			'id'   => 'country_based_payments_woocommerce_settings_country_based_payments_section_title'
		),
		
		'currencyconverterapi' => array(
			'name'        => __('currencyconverterapi.com', 'country-based-payments'),
			'type'        => 'text',
			'css'         => 'min-width:500px;',
			'desc_tip'    => __('Add here the currencyconverterapi.com access API key', 'country-based-payments'),
			'id'          => 'country_based_payments_woocommerce_settings_currencyconverterapi'
		),

		'fxmarketapi' => array(
			'name'        => __('fxmarketapi.com', 'country-based-payments'),
			'type'        => 'text',
			'css'         => 'min-width:500px;',
			'desc_tip'    => __('Add here the fxmarketapi.com access API key', 'country-based-payments'),
			'id'          => 'country_based_payments_woocommerce_settings_fxmarketapi'
		),

		'currencylayer' => array(
			'name'        => __('apilayer.net / currencylayer.com', 'country-based-payments'),
			'type'        => 'text',
			'css'         => 'min-width:500px;',
			'desc_tip'    => __('Add here the apilayer.net / currencylayer.com access API key', 'country-based-payments'),
			'id'          => 'country_based_payments_woocommerce_settings_currencylayer'
		),

		'country_based_payments_section_end' => array(
			'type' => 'sectionend',
			'id'   => 'country_based_payments_woocommerce_settings_country_based_payments_section_end'
		),


		'country_based_payments_section_2_title' => array(
			'name' => __('Dolar Rate', 'country-based-payments'),
			'type' => 'title',
			'desc' => __( $dolar_rate , 'country-based-payments'),
			'id'   => 'country_based_payments_woocommerce_settings_country_based_payments_section_2_title'
		),

		'country_based_payments2_section_end' => array(
			'type' => 'sectionend',
			'id'   => 'country_based_payments_woocommerce_settings_country_based_payments_section_2_end'
		),


	);
	return apply_filters( 'country_based_payments_woocommerce_settings', $settings );
}

// PLUGIN ADMIN FUNCTIONS

function country_based_payments_woocommerce_admin_notice_no_configuration() {
    ?>
    <div class="notice notice-error is-dismissible">
        <p><?php _e( 'The Country Based Payments integration for Woocommerce needs to be configured', 'country-based-payments' ); ?></p>
    </div>
    <?php
}

add_action('plugins_loaded', 'country_based_payments_woocommerce_load_textdomain');
function country_based_payments_woocommerce_load_textdomain() {
	load_plugin_textdomain( 'country-based-payments', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
}

// Deativate the cron job that saves rates
register_deactivation_hook( __FILE__, 'bl_deactivate' );
 
function bl_deactivate() {
   $timestamp = wp_next_scheduled( 'save_ars_to_file' );
   wp_unschedule_event( $timestamp, 'save_ars_to_file' );
}


// PLUGIN FUNCTIONS


// Saves the currency rate
function save_currency ( $default_currency, $to_convert_currency ) {

	// Incluye las funciones de los conversores de moneda 1, 2 y 3 y el conversor de monedas general
	include_once ("currency_converter_functions.php");

	$rate = conversor_monedas( $default_currency, $to_convert_currency, 1 );

	// Si el valor que trae el conversor es diferente de 0 (indicando que no hay un error) ...
	if ($rate <> 0) {

		$data_name = strtolower( $default_currency ) . '_to_' . strtolower( $to_convert_currency ) . '_rate';
		// Saves the value in the wordpress option table
		update_option( $data_name, trim($rate), '', 'yes' );

	}

	return $rate;

}

// Updates the ARS rate and run as a cronjob
function save_ars_rate () {

	save_currency ( 'USD', 'ARS' );

}

// Adds the ARS RATE CRON to the Wordpress Cron Jobs to run daily
add_action( 'save_ars_to_file', 'save_ars_rate' );

if ( ! wp_next_scheduled( 'save_ars_to_file' ) ) {
    wp_schedule_event( time(), 'daily', 'save_ars_to_file' );
}




function add_get_val() { 
    global $wp; 
    $wp->add_query_var('variable'); 
}

add_action('init','add_get_val');


// Check and Set if the Ticket Payments Variable is present in the URL

function custom_before_checkout_action() {

	if (isset($_GET["alt_pay"]) && $_GET["alt_pay"] == "1") {

		WC()->session->set("alt_pay","true");

	} else {

		WC()->session->set("alt_pay","false");

	}
}

add_action("woocommerce_before_checkout_form", "custom_before_checkout_action");

/*
function filter_gateways($gateways){

	if (WC()->session->get("alt_pay") !== "true") {

		unset($gateways['wunion']);

	}

	return $gateways;
}

add_filter('woocommerce_available_payment_gateways','filter_gateways',1);
*/

// WooCommerce Disable Payment Gateway for a Specific Country
  
function payment_gateway_disable_country( $available_gateways ) {

    if ( is_admin() ) return $available_gateways;

	$subscriptions_in_cart = FALSE;
 
	// Check the products in the cart to find subscriptions
	$cart = WC()->cart->get_cart();

	foreach( $cart as $cart_item ){
		
		$product = wc_get_product( $cart_item['product_id'] );
		
		
		if ( $product->get_type() == 'subscription' OR $product->get_type() == 'variable-subscription' ) $subscriptions_in_cart = TRUE;

	}

	// If there are subscription products ...
	if( $subscriptions_in_cart == TRUE ) {

		// Disable Mercado Pago Personalizado con Tarjetas (Payment Gateway)
		if ( isset( $available_gateways['woo-mercado-pago-custom'] ) ) {

			unset( $available_gateways['woo-mercado-pago-custom'] );

		}

		// Disable Mercado Pago Personalizado con Efectivo (Payment Gateway)
		if ( isset( $available_gateways['woo-mercado-pago-ticket'] ) ) {

			unset( $available_gateways['woo-mercado-pago-ticket'] );

		}		    

		// Disable Western Union as Payment Gateway
		if ( isset( $available_gateways['wunion'] ) ) {

			unset( $available_gateways['wunion'] );

		}

		// Disable Cash on Delivery as Payment Gateway (used as WU alternative)
		if ( isset( $available_gateways['cod'] ) ) {

			unset( $available_gateways['cod'] );

		}		
		
		// Disable Paypal Express as Payment Gateway
		if ( isset( $available_gateways['ppec_paypal'] ) ) {

			unset( $available_gateways['ppec_paypal'] );

		}	

	// If there are not subscription products ...  
	} else {
	
		global $woocommerce;

	    $my_country = $woocommerce->customer->get_billing_country();

		// If the billing country is ARGENTINA
	    if( $my_country == 'AR') {

		    // Disable Paypal Express as Payment Gateway
		    if ( isset( $available_gateways['ppec_paypal'] ) ) {

		        unset( $available_gateways['ppec_paypal'] );

		    }	

		    // Disable Western Union
		    if ( isset( $available_gateways['wunion'] ) ) {

		        unset( $available_gateways['wunion'] );

		    }
		    
		    // Disable Cash on Delivery as Payment Gateway (used as WU alternative)
		    if ( isset( $available_gateways['cod'] ) ) {

			unset( $available_gateways['cod'] );
		    }
		    
		    // If the Tickets Payments Variable is not TRUE disable Mercado Pago Personalizado con Efectivo (Payment Gateway)
		    if ( isset( $available_gateways['woo-mercado-pago-ticket'] ) && WC()->session->get("alt_pay") !== "true" ) {

		        unset( $available_gateways['woo-mercado-pago-ticket'] );

		    }

		// If the user is not from ARGENTINA only can pay with Stripe
		} else {

			// Disable Mercado Pago Personalizado con Tarjetas (Payment Gateway)
			if ( isset( $available_gateways['woo-mercado-pago-custom'] ) ) {

				unset( $available_gateways['woo-mercado-pago-custom'] );

			}

			// Disable Mercado Pago Personalizado con Efectivo (Payment Gateway)
			if ( isset( $available_gateways['woo-mercado-pago-ticket'] ) ) {

				unset( $available_gateways['woo-mercado-pago-ticket'] );

			}

			// If the Tickets Payments Variable is not TRUE disable Western Union as Paypament Gateway
			if ( isset( $available_gateways['wunion'] ) && WC()->session->get("alt_pay") !== "true" ) {

				unset( $available_gateways['wunion'] );

			}
		    
			// If the Tickets Payments Variable is not TRUE disable Cash on Delivery (alternative to WU Gateway) as Paypament Gateway
			if ( isset( $available_gateways['cod'] ) && WC()->session->get("alt_pay") !== "true" ) {

				unset( $available_gateways['cod'] );

			}		    

		}	

	}

	return $available_gateways;

}


// Change the currency symbol by country

function change_existing_currency_symbol( $currency_symbol, $currency ) {

  if ( ! is_admin() ) { 

    global $woocommerce;

    $my_country = $woocommerce->customer->get_billing_country();

	// If the billing country is ARGENTINA
    if( $my_country == 'AR') {

    	$subscriptions_in_cart = FALSE;

		// Check the products in the cart to find subscriptions
    	$cart = WC()->cart->get_cart();
 
		foreach( $cart as $cart_item ){
		 
		    $product = wc_get_product( $cart_item['product_id'] );
		 
			
		    if ( $product->get_type() == 'subscription' OR $product->get_type() == 'variable-subscription' ) $subscriptions_in_cart = TRUE;

		}

		// If there are subscription products (user can pay only with Stripe so the currency will be USD)
		if ( $subscriptions_in_cart == TRUE ) {
		
			$currency_symbol = 'U$D ';

		// If there are not subscription products (user can pay only with Mercado Pago so the currency will be ARS)
		} else {
		
			$currency_symbol = 'AR$ '; 
		
		}

	// If the user is not from ARGENTINA only can pay with Stripe so the currency will be USD	
    } else {

        $currency_symbol = 'U$D '; 

    }

    return $currency_symbol;

  }
}


// Utility function to change the prices with a multiplier (number)
function get_price_multiplier() {

  if ( ! is_admin() ) { 

    global $woocommerce;

    $my_country = $woocommerce->customer->get_billing_country();

    if( $my_country == 'AR') {

    	$subscriptions_in_cart = FALSE;

		// Check the products in the cart to find subscriptions
    	$cart = WC()->cart->get_cart();
 
		foreach( $cart as $cart_item ){
		 
		    $product = wc_get_product( $cart_item['product_id'] );
		 
			
		    if ( $product->get_type() == 'subscription' OR $product->get_type() == 'variable-subscription' ) $subscriptions_in_cart = TRUE;

		}
		
		// If there are subscription products (user can pay only with Stripe so the currency needs to remain as USD)
		if ( $subscriptions_in_cart == TRUE ) {
		
			$price_multiplier = 1; 
		

		// If there are not subscription products (user can pay only with Mercado Pago so the currency needs to be converted to ARS)	
		} else {

			$default_currency    = 'USD';
			$to_convert_currency = 'ARS';

			$data_name = strtolower( $default_currency ) . '_to_' . strtolower( $to_convert_currency ) . '_rate';

	        $price_multiplier = get_option( $data_name ); 

		}	

	// If the user is not from ARGENTINA only can pay with Stripe so the currency needs to remain as USD	
    } else {

		$price_multiplier = 1; 

    }

    return $price_multiplier;

  }

}

// Change simple, grouped, external products and variations
function custom_price( $price, $product ) {

    return $price * get_price_multiplier();

}


// Change variable (price range)
function custom_variable_price( $price, $variation, $product ) {

    // Delete product cached price
    wc_delete_product_transients($variation->get_id());

    return $price * get_price_multiplier();
}


// Handling price caching
function add_price_multiplier_to_variation_prices_hash( $hash ) {

    $hash[] = get_price_multiplier();

    return $hash;
}


// Fire the currency functions after the site is loaded
function fire_currency_filters () {

	// Change the currency symbol by country
	add_filter('woocommerce_currency_symbol', 'change_existing_currency_symbol', 10, 2);

	// Change simple, grouped, external products and variations
	add_filter('woocommerce_product_get_price', 'custom_price', 99, 2 );
	add_filter('woocommerce_product_get_regular_price', 'custom_price', 99, 2 );
	add_filter('woocommerce_product_variation_get_regular_price', 'custom_price', 99, 2 );
	add_filter('woocommerce_product_variation_get_price', 'custom_price', 99, 2 );

	// Change variable (price range)
	add_filter('woocommerce_variation_prices_price', 'custom_variable_price', 99, 3 );
	add_filter('woocommerce_variation_prices_regular_price', 'custom_variable_price', 99, 3 );

	// Handling price caching
	add_filter( 'woocommerce_get_variation_prices_hash', 'add_price_multiplier_to_variation_prices_hash', 99, 1 );

};

// add_action( 'wp_loaded', 'fire_currency_filters' );


// Fire the country functions after the site is loaded
function fire_country_filters () {

	// WooCommerce Disable Payment Gateway for a Specific Country
	add_filter( 'woocommerce_available_payment_gateways', 'payment_gateway_disable_country' );

};

add_action( 'wp_loaded', 'fire_country_filters' );
