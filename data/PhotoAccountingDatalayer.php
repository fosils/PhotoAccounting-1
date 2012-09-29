<?php
require_once "PGDatalayer.php";

class PhotoAccountingDatalyer extends PGDatalyaer{
	public function __construct(){
		parent::__construct("localhost", "photo_accounting");
	}	
	
	/**
	 * Customer Table
	 */
	public function CUST_Create($email){
		return $this->Exec("insert into customers(email) values(:email);", array(":email"=>$email));
	}
	public function CUST_GetByEmail($email){
		return $this->Exec("select * from customers where email=:email order by customer_id desc limit 1;", array(":email"=>$email));
	}
	public function CUST_GetByID($id){
		return $this->Exec("select * from customers where customer_id=:customer_id;", array(":customer_id"=>$id));
	}
	
	
	public function RCT_ListAll(){
		return $this->Exec("select * from receipts;");
	}
	public function RCT_Create($customer_id, $s3url){
		return $this->Exec("insert into receipts(customer_id, s3url, received_date) values(:customer_id, :s3url, current_date);", array(":customer_id"=>$customer_id, ":s3url"=>$s3url));
	}

	
	public function CDV_Create($customer_id, $device_id){
		return $this->Exec("insert into customers(email) values(:email);", array(":email"=>$email));
	}
	public function CDV_GetCustomerID($device_id){
		return $this->Exec("select * from cust_devices where device_id=:device_id limit 1;", array(":device_id"=>$device_id));
	}
}
?>
