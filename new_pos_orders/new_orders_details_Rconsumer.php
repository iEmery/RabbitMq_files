<?php
session_start();


//CONFIG
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();
$channel->queue_declare('newCRMuser', false, false, false, false);


//CALLBACK
$callback = function($msg) {
    $data = $msg->data;


    //suiteCRM SOAP
    $url = "http://{site_url}/service/v4_1/soap.php?wsdl";
    $username = "admin";
    $password = "password";

    //require NuSOAP
    require_once("../nusoap/lib/nusoap.php");

    //retrieve WSDL
    $client = new nusoap_client($url, 'wsdl');

    //display errors
    $err = $client->getError();
    if ($err)
    {
        echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
        echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
        exit();
    }

    //login 
    $login_parameters = array(
         'user_auth' => array(
              'user_name' => $username,
              'password' => md5($password),
              'version' => '1'
         ),
         'application_name' => 'SoapTest',
         'name_value_list' => array(
         ),
    );

    $login_result = $client->call('login', $login_parameters);
    $session_id =  $login_result['id'];

    //insert user
    $insert_data = array(
        'session' => $session_id,
        'module_name' => 'Accounts',
        'name_value_list' => array(
            $data->$id,
            $data->$email,
            $data->$first_name,
            $data->$last_name,
            $data->$street,
            $data->$city,
            $data->$zipcode,
            $data->$state,
            $data->$country,
            $data->$mobile,
            $data->$phone)
        );
    $insert_result = $client->call('set_entry', $insert_data);

};

//RESULT
$channel->basic_consume('hello', '', false, true, false, false, $callback);

while(count($channel->callbacks)) {
    $channel->wait();
}

?>










<!DOCTYPE html>
<html>
<head>
    <title>RabbitConsumer</title>
</head>
<body>

</body>
</html>