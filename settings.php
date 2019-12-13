<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$plugin_settings = array(

	'currencyconverterapi.com_access_key' => get_option( 'country_based_payments_woocommerce_settings_currencyconverterapi' ),
	'fxmarketapi.com_access_key'          => get_option( 'country_based_payments_woocommerce_settings_fxmarketapi'          ),
	'currencylayer.com_access_key'        => get_option( 'country_based_payments_woocommerce_settings_currencylayer'        ),

);

