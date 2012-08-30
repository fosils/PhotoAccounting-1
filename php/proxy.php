<?php
switch($_REQUEST['function']){
	case 'get_image_name':
		require_once 'AccountImage.php';
		$accoutImage = new AccountImage();
		$accoutImage->getImageName($_POST['image_id']);
		break;
	case 'get_accounts':
		require_once 'EconomicSoapClient.php';
		$economic_client = new EconomicSoapClient();
		$accounts = $economic_client->getAccounts();		
		break;
	case 'get_image_detail':
		require_once 'AccountImage.php';
		$accoutImage = new AccountImage();
		$accoutImage->getImageDetail($_POST['image_id']);
		break;
	case 'set_image_detail':
		require_once 'AccountImage.php';
		$accoutImage = new AccountImage();
		$accoutImage->setImageDetail($_POST['image_id']);
		break;
}