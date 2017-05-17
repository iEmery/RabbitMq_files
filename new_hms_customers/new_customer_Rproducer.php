<?php
session_start();

//WAIT FOR NOTIFY

//NEW CUSTOMER
$local_list = array();
while (true) {
	require_once( './ PSWebServiceLibrary.php' );
	try {
	    // creating web service access
		$webService = new PrestaShopWebservice('http://10.3.51.37:32200/', 'HAK8IHRW4KWXTQS8CAIJ7YESQDUNYCPX', false);
	 
	    // The key-value array
		$opt = array(
		    'resource' => 'customers',
		    'display'  => 'full'
		);

		// call to retrieve all customers
		$xml = $webService->get($opt);
		$resources = $xml->customers->children();

		if (!empty($resources)) {

			//Check if different with local
			$check_new = array_diff($local_list, $resources);
			if (!empty($check_new)) {

				$index = 1;
				foreach ($check_new as $customer){

					//user data
				    $data = array(
				    		"id" => $customer->id,
				    		"email" => $customer->email,
				    		"first_name" => $customer->first_name,
				    		"last_name" => $customer->last_name,
				    		"street" => $customer->street,
				    		"city" => $customer->city,
				    		"zipcode" => $customer->zipcode,
				    		"state" => $customer->state,
				    		"country" => $customer->country,
				    		"mobile" => $customer->mobile,
				    		"phone" => $customer->phone);


					//rabbitmq
					if ($data->is_email_verified = true) {
						$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
						$channel = $connection->channel();
						$channel->queue_declare('new_HMS_customer', false, false, false, false);
						$channel->basic_publish($data, '', 'new_HMS_customer');

						$channel->close();
						$connection->close();
					}
					$index++;
				}
			}
			$local_list = $resources;
		}

		
	}
	catch (PrestaShopWebserviceException $ex) {
	    // Shows a message related to the error
	    echo 'Other error: <br />' . $ex->getMessage();
	}

}