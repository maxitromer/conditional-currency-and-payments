<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function conversor_1($from_currency,$to_currency){

// currencyconverterapi.com 

// Plugins settings with the access key for every converter
include ('settings.php');

// Initialize CURL:
$ch = curl_init('https://free.currencyconverterapi.com/api/v6/convert?q='.urlencode($from_currency).'_'.urlencode($to_currency).'&apiKey='. $plugin_settings['currencyconverterapi.com_access_key'] .'&compact=ultra');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Store the data:
$json = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

// Si el CURL dá error
if ($err) {

	$converted = array (
		"service" => "currencyconverterapi.com", // Service for the currency converter
		"status" => "ERROR: en la llamada a la URL" . "-" . $err, // Status for this service ("OK" is working or "ERROR: BLABLABLA" if is not)
		"price" => 0.00 // Valor del Cambio 
	);

// SI el CURL trae info
} else {

	// Decode JSON response:
	$exchangeRates = json_decode($json, true);


	// Si el valor existe arma el array
	if (isset($exchangeRates[''.urlencode($from_currency).'_'.urlencode($to_currency).''])) {

		$val = floatval($exchangeRates[''.urlencode($from_currency).'_'.urlencode($to_currency).'']);

	 	$converted = array (
			"service" => "currencyconverterapi.com", // Service for the currency converter
			"status" => "OK", // Status for this service ("OK" is working or "ERROR: BLABLABLA" if is not)
			"price" => number_format($val, 2, '.', '') // Valor del Cambio
	 	);		

	// Si el valor no existe el JSON viene con un error que tambíen va en el array
	} else {

	 	$converted = array (
			"service" => "currencyconverterapi.com", // Service for the currency converter
			"status" => "ERROR: en la respuesta del JSON" . "-" . $exchangeRates["error"], // Status for this service ("OK" is working or "ERROR: BLABLABLA" if is not)
			"price" => 0.00 // Valor del Cambio
	 	);	

	}

}

return $converted;

}

function conversor_2($from_currency,$to_currency){

// fxmarketapi.com 	3.878.951

// Plugins settings with the access key for every converter
include ('settings.php');

$curl = curl_init();

curl_setopt_array( $curl, array(
  CURLOPT_URL => 'https://fxmarketapi.com/apiconvert?api_key='. $plugin_settings['fxmarketapi.com_access_key'] .'&from='.urlencode($from_currency).'&to='.urlencode($to_currency).'&amount=1',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

// Si el CURL dá error
if ($err) {

	$converted = array (
		"service" => "fxmarketapi.com", // Service for the currency converter
		"status" => "ERROR: en la llamada a la URL" . "-" . $err, // Status for this service ("OK" is working or "ERROR: BLABLABLA" if is not)
		"price" => 0.00 // Valor del Cambio
	);

// SI el CURL trae info
} else {

	// Decode JSON response:
	$exchangeRates = json_decode($response, true);

	// Si el valor existe arma el array
	if ($exchangeRates["price"]) {

		$val = floatval($exchangeRates["price"]);

	 	$converted = array (
			"service" => "fxmarketapi.com", // Service for the currency converter
			"status" => "OK", // Status for this service ("OK" is working or "ERROR: BLABLABLA" if is not)
			"price" => number_format($val, 2, '.', '') // Valor del Cambio
	 	);	

	// Si el valor no existe el JSON viene con un error que tambíen va en el array
	} else {

	 	$converted = array (
			"service" => "fxmarketapi.com", // Service for the currency converter
			"status" => "ERROR: en la respuesta del JSON", // Status for this service ("OK" is working or "ERROR: BLABLABLA" if is not)
			"price" => 0.00 // Valor del Cambio
	 	);

	}
}

return $converted;

}

function conversor_3($to_currency){

// apilayer.net 959.993 / currencylayer.com 360.699

// Plugins settings with the access key for every converter
include ('settings.php');

// Initialize CURL:
$ch = curl_init('http://apilayer.net/api/live?access_key='. $plugin_settings['currencylayer.com_access_key'] .'&currencies='.urlencode($to_currency).'&format=1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Store the data:
$json = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

// Si el CURL dá error
if ($err) {

	$converted = array (
		"service" => "currencylayer.com", // Service for the currency converter
		"status" => "ERROR: en la llamada a la URL" . "-" . $err, // Status for this service ("OK" is working or "ERROR: BLABLABLA" if is not)
		"price" => 0.00 // Valor del Cambio
	);

// SI el CURL trae info
} else {

	// Decode JSON response:
	$exchangeRates = json_decode($json, true);

	// Si la llamada es exitosa arma el array
	if ($exchangeRates["success"] == 1) {

		$val = floatval($exchangeRates['quotes']['USD'.urlencode($to_currency).'']);

	 	$converted = array (
			"service" => "currencylayer.com", // Service for the currency converter
			"status" => "OK", // Status for this service ("OK" is working or "ERROR: BLABLABLA" if is not)
			"price" => number_format($val, 2, '.', '') // Valor del Cambio
	 	);	
	// Si el JSON viene con un error tambíen va en el array
	} else {

	 	$converted = array (
			"service" => "currencylayer.com", // Service for the currency converter
			"status" => "ERROR: en la respuesta del JSON" . "-" . $exchangeRates["error"]["info"], // Status for this service ("OK" is working or "ERROR: BLABLABLA" if is not)
			"price" => 0.00 // Valor del Cambio
	 	);	
	}
}

return $converted;

}


// Función de conversión de monedas
function conversor_monedas($from_Currency,$to_Currency,$amount){

	// Prueba con el primer conversor
	$price = conversor_1($from_Currency, $to_Currency);

	// Si el valor del primer conversor es 0 (indicando un error) prueba con el segundo
	if ($price["price"] == 0) {

		$price = conversor_2($from_Currency, $to_Currency);

	} 

	// Si además el valor del segundo conversor es 0 (indicando un error) y la moneda desde la cual se quiere convertir es "USD" prueba con el tercero
	if ($price["price"] == 0 AND $from_Currency == "USD") {

		$price = conversor_3($to_Currency);

	} 

	// Si el valor sigue siendo 0 ...
	if ($price["price"] == 0) {

		// INFORMA DEL ERROR
		echo "Error en conversor de monedas<br>";
		$val = 0;

	// Si en cualquiera de los 3 casos se logró un valor diferente a 0 (indicando que no hay error) ...
	} else {

		//  Le dá formato
		$val = $price["price"];
		
	}

  $total = $val * $amount;
  return number_format($total, 2, '.', '');
}