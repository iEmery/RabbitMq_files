<?php
session_start();

//NEW ORDER DETAILS
$local_list_order_details = array();
$local_list_order_invoices = array();
$local_list_order_payments = array();

while (true) {
	require_once( '../ PSWebServiceLibrary.php' );
	try {
	    // creating web service access
		$webService = new PrestaShopWebservice('http://10.3.51.37:32200/', 'BS9B3BWGCY5MXQ8KWDUUIR7TJP3RW3LF', false);
	 
	    // The key-value array
		$opt_order_details = array(
		    'resource' => 'order_details',
		    'display'  => 'full'
		);

		// call to retrieve all order details
		$xml = $webService->get($opt_order_details);
		$resources = $xml->order_payements->children();

		if (!empty($resources)) {

			//Check if different with local list
			$check_new = array_diff($local_list, $resources);
			if (!empty($check_new)) {

				$index = 1;
				foreach ($check_new as $order_payement){
					
					//order data
				    $data = array(
				    		"id" => $order_payement->id,
				    		"id_shop" => $order_payement->id_shop,
				    		"order_reference" => $order_payement->order_reference,
				    		"id_currency" => $order_payement->id_currency,
				    		"amount" => $order_payement->amount,
				    		"payement_method" => $order_payement->payement_method,
				    		"conversion_rate" => $order_payement->conversion_rate,
				    		"date_add" => $order_payement->date_add,
				    		"product_name" => "",
				    		"product_quantity" => "",
				    		"product_price" => ""
				    		"reduction_alount" => "",
				    		"product_quantity_discount" => "",
				    		"total_price_tax_incl" => "");


					//rabbitmq
					if ($data->is_email_verified = true) {
						$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
						$channel = $connection->channel();
						$channel->queue_declare('new_POS_order_detail', false, false, false, false);
						$channel->basic_publish($data, '', 'new_POS_order_detail');

						$channel->close();
						$connection->close();
					}
					$index++;
				}
				$local_list = $resources;
			}
		}

		
	}
	catch (PrestaShopWebserviceException $ex) {
	    // Shows a message related to the error
	    echo 'Other error: <br />' . $ex->getMessage();
	}

}