<?php
/**
 * PHP extensions must be enabled: php_soap, php_openssl
 * @author igreactive
 *
 */
class EconomicSoapClient {
	private static $client;
	
	/**
	 * Note: This might be helpfull: 
	 * http://apiforum.e-conomic.com/soap-f8/economic-api-exceptions-authorizationexception-t4678.html
	 */
	private function connect() {
		require_once 'includes/web_service_credentials.php';
		ini_set('max_execution_time', 0);
		try {
			$this->client = new SoapClient("https://www.e-conomic.com/secure/api1/EconomicWebservice.asmx?WSDL", array("trace" => 1, "exceptions" => 1));
			$this->client->Connect($credentials);
		} catch (SoapFault $fault) {
			trigger_error(sprintf("Soap fault %s - %s", $fault->faultcode, $fault->faultstring), E_USER_ERROR);
		}
	}
	
	public function getAccounts(){
		$this->connect();
		$accounts = array();
		
		try {
			$accounts_result = $this->client->Account_GetAll()->Account_GetAllResult;
			
			if (is_object($accounts_result) && property_exists($accounts_result, 'AccountHandle')) {
				$accounts_handle = $this->client->Account_GetDataArray(array(
						'entityHandles' => $accounts_result->AccountHandle
				))->Account_GetDataArrayResult;
			
				if (is_object($accounts_handle) && property_exists($accounts_handle, 'AccountData')) {
					$accounts = $accounts_handle->AccountData;
				} else {
					throw new Exception('No Account data available '. $e->getMessage());
				}
			}			
		} catch (Exception $e) {
			throw new Exception('Accounts could not be returned '. $e->getMessage());
		}
		// load hotKeys from database. Number variable is primary key in hot_keys table
		require_once "../data/PhotoAccountingDatalayer.php";
		$db = new PhotoAccountingDatalayer();
		$result=$db->HK_GetAll();
		while ($row = pg_fetch_assoc($result)) {
			foreach ( $accounts as $key=>$value){
				// if number of account is this same as hotkey id add hotkey to response
				if($value->Number==$row['id']) {
					$value->hotkey=$row['hot_key'];
					break;
				}
				
			}
		}		
		echo json_encode($accounts);
	}
	
	// persist hotkeys in database 
	public function setHotKeys($id, $hot_key) {
		require_once "../data/PhotoAccountingDatalayer.php";
		$db = new PhotoAccountingDatalayer();
		$result=$db->HK_Update($id, $hot_key);
		if(pg_num_rows($result)==0){
			$result=$db->HK_Create($id, $hot_key);
		}
		// Init the returned object
		$detail = new stdClass;
		$detail->status = 1;
		if (!$result) {
			$errors['common'] = pg_last_error();
			$detail->status = 0;
			$detail->errors = $errors;
		}
		// Print results in JSON
		echo json_encode($detail);
	}
}