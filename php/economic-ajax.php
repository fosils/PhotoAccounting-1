<?php
require_once 'EconomicSoapClient.php';
$economic_client = new EconomicSoapClient();
$accounts = $economic_client->getAccounts();
echo json_encode($accounts);