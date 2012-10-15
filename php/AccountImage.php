<?php
class AccountImage{

	public function getImageDetail($imageID){
		// See sql/photo_accounting.sql for the relevant table structure & sample data
		
		// Abort early if there is nothing to process
		if (!isset($imageID)) {
			header("HTTP/1.1 500 Server error");
			die("Invalid request");
		}
		
		if ($imageID < 0) {
			header("HTTP/1.1 404 Not Found");
			die("Invalid request");
		}
		
		
		$image_id = pg_escape_string($imageID);
		
		// Performing SQL query
		require_once "../data/PhotoAccountingDatalayer.php";
		$db = new PhotoAccountingDatalayer();
		$result=$db->RCT_GetById($image_id);
		

		
		// Get number of rows
		$rows = pg_num_rows($result);
		if(is_null($result)){
			// Insert a new row with default values and get its id
			$create_result=$db->RCT_Create(1,'');
			$create_row = pg_fetch_array($create_result);
			$create_id = $create_row[0];
			// Get the newly created row. Yes, we intentionally overwrite the earlier '$result' variable
			$result=$db->RCT_GetById($create_id);
		}
		
		// If a record was found, set it
		$detail = array();
		while ($row = pg_fetch_assoc($result)) {
			$detail = new stdClass;
		
			// This returns date strings like 2012-03-28
			// $detail->date = $row['entry_date'];
		
			// This returns date strings like 28-03-2012
			if (($timestamp = strtotime($row['entry_date'])) === false) {
				$detail->date = date('d-m-Y');
			} else {
				$detail->date = date('d-m-Y', $timestamp);
			}
		
			$detail->text = $row['text'];
			$detail->amount = $row['amount'];
			$detail->account = $row['account'];
			$detail->vat_code = $row['vat_code'];
			$detail->offset_account = $row['offset_account'];
			$detail->image_name = $row['image_name'];
			$detail->deleted = $row['deleted'];
		}
		echo json_encode($detail);		
	}
	
	public function setImageDetail($imageID){
		// Abort early if there is nothing to process
		// if (!isset($_POST['id'])) { die(); }
		if (!isset($imageID) || empty($imageID) || $imageID < 1) {
			die();
		}
		
		// Extract request parameters trimming spaces as well
		$image_id = trim($imageID);
		$entry_date = trim($_POST['date']);
		$text = trim($_POST['text']);
		$amount = trim($_POST['amount']);
		$account = trim($_POST['account']);
		$vat_code= trim($_POST['vat_code']);
		$offset_account = trim($_POST['offset_account']);
		
		// START : validation
		// Store errors in this array using keys similar to that on the page
		$errors = array();
		
		// Check date
		if (empty($entry_date)) {
			// $entry_date = 'null';
			$entry_date = date('Y-m-d');
		} else {
			if (($timestamp = strtotime($entry_date)) === false) {
				$errors['date'] = 'Please use an accepted date format i.e. 25-02-2012';
			} else {
				$entry_date = date('Y-m-d', $timestamp);
			}
		}
		
		// Check text
		if (empty($text)) {
			// Funny enough, the next line saves the string 'null'
			// $text = null;
			$text = '';
		} else {
			if (strlen($text) > 9999) {
				$errors['text'] = 'Please use a maximum of 9999 characters';
			}
		}
		
		// Check amount
		if (empty($amount)) {
			$amount = 0;
		} else {
			if (!is_numeric($amount)) {
				$errors['amount'] = 'Please use a number i.e. 2.00';
			}
		}
		
		// Check account
		if (empty($account)) {
			$account = 'null';
		} else {
			// Next line is not 100% reliable
			// if (!is_int($account)) {
			if ((string)(int)$account !== (string)$account) {
				$errors['account'] = 'Please use an integer i.e. 3120';
			}
		}
		
		// Check vat_code
		if (empty($vat_code)) {
			$vat_code = '';
		} else {
			// max length vat_code is 10 chars
			if (strlen($vat_code) > 10) {
				$errors['vat_code'] = 'Maximu lenght vat code is 10 chars';
			}
		}
		
		// Check offset_account
		if (empty($offset_account)) {
			$offset_account = 'null';
		} else {
			// Next line is not 100% reliable
			// if (!is_int($offset_account)) {
			if ((string)(int)$offset_account !== (string)$offset_account) {
				$errors['offset_account'] = 'Please use an integer i.e. 4488';
			}
		}
		// END : validation
		
		// Init the returned object
		$detail = new stdClass;
		$detail->status = 1;
		// $detail->image_id = $image_id;
		$detail->image_id = (int)$image_id;
		// $detail->errors = null;
		
		// Check if we should do db operations based on validation errors
		if (empty($errors)) {
			// Sanitize database inputs
			$image_id = pg_escape_string($image_id);
			$entry_date = pg_escape_string($entry_date);
			$text = pg_escape_string($text);
			$amount = pg_escape_string($amount);
			$account = pg_escape_string($account);
			$vat_code = pg_escape_string($vat_code);
			$offset_account = pg_escape_string($offset_account);
		
			// SQL query
			require_once "../data/PhotoAccountingDatalayer.php";
			$db = new PhotoAccountingDatalayer();
			$result=$db->RCT_Update($entry_date, $text, $amount, $account, $vat_code, $offset_account, $image_id);
			
		
			// Evaluate result
			if (!$result) {
			$errors['common'] = pg_last_error();
		
			$detail->status = 0;
			$detail->errors = $errors;
			}

		} else {
			$detail->status = 0;
			$detail->errors = $errors;
			}
		
			// Print results in JSON
			echo json_encode($detail);		
	}
	
	public function updateFileNames($files){
		require_once "data/PhotoAccountingDatalayer.php";
		$db = new PhotoAccountingDatalayer();
		$i=1;
		foreach ($files as $file) {
			$image_name=$this->getFirstPartOfName(basename($file));			
			$db->RCT_UpdateImageName($image_name, $i);
			$i++;			
		}	
	}
	
	public function markImageAsDeleted($imageID){
		// Abort early if there is nothing to process
		if (!isset($imageID) || empty($imageID) || $imageID < 1) {
			die();
		}
		// Extract request parameters trimming spaces as well
		$image_id = trim($imageID);
		$deleted = trim($_POST['deleted']);
	
		// Check deleted field
		if (!($deleted=='0' || $deleted=='1')) {
			$errors['deleted'] = 'Deleted value must be 0 OR 1';
		}
	
		// Init the returned object
		$detail = new stdClass;
		$detail->status = 1;
		$detail->image_id = (int)$image_id;
		$image_id = pg_escape_string($image_id);
	
		// SQL query
		require_once "../data/PhotoAccountingDatalayer.php";
		$db = new PhotoAccountingDatalayer();
		$result=$db->RCT_SetImageAsDeleted($image_id,$deleted);
			
		// Evaluate result
		if (!$result) {
			$errors['common'] = pg_last_error();
			$detail->status = 0;
			$detail->errors = $errors;
		}
		// Print results in JSON
		echo json_encode($detail);
	}
	
	private function getFirstPartOfName($name){
		$pos=strpos($name,"_");
		return $pos>0 ? substr($name,0,$pos) : $name;
	}	
}