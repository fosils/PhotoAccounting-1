<?php
require_once "PGDatalayer.php";

class PhotoAccountingDatalyer extends PGDatalayer{
	public function __construct(){
		parent::__construct("localhost", "photo_accounting", "photo_editor", "Htbp4SAaxm6K");
	}	
	
	/**
	 * Customer Table
	 */
	public function CUST_Create($email){
		return $this->Exec("insert into customers(email) values(:email);", array(":email"=>$email));
	}
	public function CUST_CreateEmpty(){
		return $this->Exec("insert into customers(email) values(null);");
	}
	public function CUST_GetByEmail($email){
		return $this->Exec("select * from customers where email=:email order by customer_id desc limit 1;", array(":email"=>$email));
	}
	public function CUST_GetFirstEmpty(){
		return $this->Exec("select * from customers where email is null order by customer_id limit 1");
	}
	public function CUST_GetLastEmpty(){
		return $this->Exec("select * from customers where email is null order by customer_id desc limit 1");
	}
	public function CUST_GetByID($id){
		return $this->Exec("select * from customers where customer_id=:customer_id;", array(":customer_id"=>$id));
	}
	public function CUST_UpdateEmail($customer_id, $email){
		return $this->Exec("update customers set email=:email where customer_id=:customer_id;", array(":customer_id"=>$customer_id, ":email"=>$email));
	}
	/**
	 * Receipts
	 */	
	public function RCT_Create($customer_id){
		return $this->Exec("insert into receipts(customer_id) values(:customer_id) RETURNING id",array(":customer_id"=>$customer_id));
	}
	public function RCT_GetById($id){
		return $this->Exec(" select * from receipts where id=:id",array(":id"=>$id));
	}
	public function RCT_Update($entry_date, $text, $amount, $account, $offset_account, $id){
		return $this->Exec("update receipts set entry_date=:entry_date, text=:text, amount=:amount, account=:account, offset_account=:offset_account where id=:id", 
				array(":entry_date"=>$entry_date, ":text"=>$text, ":amount"=>$amount, ":account"=>$account, ":offset_account"=>$offset_account, ":id"=>$id));
	}
	public function RCT_UpdateImageName($image_name, $id){
		return $this->Exec("update receipts set image_name=:image_name  where id=:id", array(":image_name"=>$image_name, ":id"=>$id));
	}

	/**
	 * Customer Receipts
	 */
	public function CDV_Create($customer_id, $device_id){
		return $this->Exec("insert into cust_devices(customer_id, udid) values(:customer_id, :device_id);", array(":customer_id"=>$customer_id, ":device_id"=>$device_id));
	}
	public function CDV_GetCustomerID($device_id){
		return $this->Exec("select * from cust_devices where udid=:device_id limit 1;", array(":device_id"=>$device_id));
	}
        public function CDV_ExistsForCustomer($customer_id, $device_id){
                return $this->Exec("select 1 as Exists from cust_devices where customer_id=:customer_id and udid=:device_id limit 1;", array(":customer_id"=>$customer_id, ":device_id"=>$device_id));
        }
	public function CDV_Get($device_id){
		return $this->Exec("select * from cust_devices where udid=:device_id limit 1", array(":device_id"=>$device_id));
	}
}

function create_customer($email=null){
	$db = new PhotoAccountingDatalyer();
	
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
