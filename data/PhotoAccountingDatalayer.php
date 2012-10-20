<?php
require_once "PGDatalayer.php";

class PhotoAccountingDatalayer extends PGDatalayer{
	public function __construct(){
		parent::__construct("localhost", "photo_accounting", "photo_editor", "Htbp4SAaxm6K");
	}	
	
	/**
	 * Customer Table
	 */
	public function CUST_Create($email){
		return $this->Exec("INSERT INTO customers(email) VALUES(:email);", array(":email"=>$email));
	}
	public function CUST_CreateEmpty(){
		return $this->Exec("INSERT INTO customers(email) VALUES(null);");
	}
	public function CUST_GetByEmail($email){
		return $this->Exec("SELECT * FROM customers WHERE email=:email ORDER BY id DESC LIMIT 1;", array(":email"=>$email));
	}
	public function CUST_GetFirstEmpty(){
		return $this->Exec("SELECT * FROM customers WHERE email IS NULL ORDER BY id LIMIT 1");
	}
	public function CUST_GetLastEmpty(){
		return $this->Exec("SELECT * FROM customers WHERE email IS NULL ORDER BY id DESC LIMIT 1");
	}
	public function CUST_GetByID($id){
		return $this->Exec("SELECT * FROM customers WHERE id=:id;", array(":id"=>$id));
	}
	public function CUST_UpdateEmail($customer_id, $email){
		return $this->Exec("UPDATE customers SET email=:email WHERE id=:id;", array(":id"=>$customer_id, ":email"=>$email));
	}
	/**
	 * Receipts
	 */	
	public function RCT_Create($customer_id, $s3url){
		return $this->Exec("INSERT INTO receipts(customer_id, s3url, received_date) VALUES(:customer_id, :s3url, current_timestamp) RETURNING id;", array(":customer_id"=>$customer_id, ":s3url"=>$s3url));
	}
	public function RCT_GetById($id){
		return $this->Exec(" SELECT * FROM receipts WHERE id=:id",array(":id"=>$id));
	}
	public function RCT_Update($entry_date, $text, $amount, $account, $vat_code, $offset_account, $id){
		return $this->Exec("UPDATE receipts SET entry_date=:entry_date, text=:text, amount=:amount, account=:account, vat_code=:vat_code, offset_account=:offset_account WHERE id=:id", array(":entry_date"=>$entry_date, ":text"=>$text, ":amount"=>$amount, ":account"=>$account, ":vat_code"=>$vat_code, ":offset_account"=>$offset_account, ":id"=>$id));
	}
	public function RCT_UpdateImageName($image_name, $id){
		return $this->Exec("UPDATE receipts SET image_name=:image_name WHERE id=:id", array(":image_name"=>$image_name, ":id"=>$id));
	}
	public function RCT_SetImageAsDeleted($id, $deleted){
		return $this->Exec("UPDATE receipts SET deleted=:deleted  WHERE id=:id", array(":id"=>$id, ":deleted"=>$deleted));
	}

	/**
	 * Customer Receipts
	 */
	public function CDV_Create($customer_id, $device_id){
		return $this->Exec("INSERT INTO cust_devices(customer_id, udid) VALUES(:customer_id, :device_id);", array(":customer_id"=>$customer_id, ":device_id"=>$device_id));
	}
	public function CDV_GetCustomerID($device_id){
		return $this->Exec("SELECT * FROM cust_devices WHERE udid=:device_id LIMIT 1;", array(":device_id"=>$device_id));
	}
    public function CDV_ExistsForCustomer($customer_id, $device_id){
    	return $this->Exec("SELECT 1 as Exists FROM cust_devices WHERE customer_id=:customer_id AND udid=:device_id LIMIT 1;", array(":customer_id"=>$customer_id, ":device_id"=>$device_id));
    }
	public function CDV_Get($device_id){
		return $this->Exec("SELECT * FROM cust_devices WHERE udid=:device_id LIMIT 1", array(":device_id"=>$device_id));
	}
	
	/**
	 *  HotKeys
	 */
	public function HK_GetAll() {
		return $this->Exec("SELECT * FROM hot_keys;");
	}
	public function HK_Create($id,$hot_key) {
		return $this->Exec("INSERT INTO hot_keys(id, hot_key) VALUES(:id,:hot_key);",array(":id"=>$id, ":hot_key"=>$hot_key));
	}
	public function HK_Update($id,$hot_key) {
		return $this->Exec("UPDATE hot_keys set hot_key=:hot_key WHERE id=:id RETURNING id;",array(":id"=>$id, ":hot_key"=>$hot_key));
	}
}

function create_customer($email=null){
	$db = new PhotoAccountingDatalayer();
	
	if(!is_null($email)){
		$result = $db->CUST_GetByEmail($email);	
		$result = (pg_num_rows($result) > 0) ? pg_fetch_assoc($result) : null;
	
		if(!is_null($result)){
			return $result;
		}
	}

	$result = (!is_null($email)) ? $db->CUST_Create($email) : $db->CUST_CreateEmpty();
       
	if($result){
	    unset($result);
        $result = (!is_null($email)) ? $db->CUST_GetByEmail($email) : $db->CUST_GetLastEmpty();    
		return  (pg_num_rows($result) > 0) ? pg_fetch_assoc($result) : null;
	}

	return null;
}
?>
