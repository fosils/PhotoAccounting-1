<?php
switch($_REQUEST['function']){
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
	case 'mark_image_as_deleted':
		require_once 'AccountImage.php';
		$accoutImage = new AccountImage();
		$accoutImage->markImageAsDeleted($_POST['image_id']);
		break;
}