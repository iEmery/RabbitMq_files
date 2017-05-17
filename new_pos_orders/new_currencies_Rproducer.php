<?php
session_start();

//NEW CURRENCY 
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
		    'resource' => 'currencies',
		    'display'  => 'full'
		);

		// call to retrieve all customers
		$xml = $webService->get($opt_order_details);
		$resources = $xml->currencies->children();

		if (!empty($resources)) {

			//Check if different with local
			$check_new = array_diff($local_list, $resources);
			if (!empty($check_new)) {

				$index = 1;
				foreach ($check_new as $currency){
					
					//currency data
				    $data = array(
				    		"id" => $currency->id,
				    		"name" => $currency->name,
				    		"iso_code" => $currency->iso_code,
				    		"conversion_rate" => $currency->conversion_rate,
				    		"deleted" => $currency->deleted,
				    		"active" => $currency->active);


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