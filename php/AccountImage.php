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
		
		// $dbconn is declared in this "include_once"
		require_once "includes/db.php";
		
		// Sanitize database inputs
		$image_id = pg_escape_string($imageID);
		
		// Performing SQL query
		$query = "SELECT id, entry_date, text, amount, account, offset_account, image_name FROM receipts WHERE id = {$image_id}";
		$result = pg_query($query) or die('Query failed: ' . pg_last_error());
		
		// Get number of rows
		$rows = pg_num_rows($result);
		
		if ($rows == 0) {
			// Insert a new row with default values and get its id
			$create_query = "INSERT INTO receipts (customer_id) VALUES (1) RETURNING id";
			$create_result = pg_query($create_query); // or die('Create query failed: ' . pg_last_error());
			$create_row = pg_fetch_array($create_result);
			$create_id = $create_row[0];
		
			// Get the newly created row. Yes, we intentionally overwrite the earlier '$result' variable
			$query = "SELECT * FROM receipts WHERE id = {$create_id}";
			$result = pg_query($query); // or die('Query failed: ' . pg_last_error());
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
			$detail->offset_account = $row['offset_account'];
			$detail->image_name = $row['image_name'];
		}
		
		// Free resultset
		pg_free_result($result);
		
		// Closing connection
		pg_close($dbconn);
		
		// Print results in JSON
		echo json_encode($detail);		
	}
	
	public function setImageDetail($imageID){
		// Abort early if there is nothing to process
		// if (!isset($_POST['id'])) { die(); }
		if (!isset($imageID) || empty($imageID) || $imageID < 1) {
			die();
		}
		
		// $dbconn is declared in this "include_once"
		require_once "includes/db.php";
		
		// Extract request parameters trimming spaces as well
		$image_id = trim($imageID);
		$entry_date = trim($_POST['date']);
		$text = trim($_POST['text']);
		$amount = trim($_POST['amount']);
		$account = trim($_POST['account']);
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
			$offset_account = pg_escape_string($offset_account);
		
			// Construct SQL query
			$query = "UPDATE receipts SET "
			."entry_date = '{$entry_date}', "
			."text = '{$text}', "
			."amount = {$amount}, "
			."account = {$account}, "
			."offset_account = {$offset_account} "
			."WHERE id = {$image_id}";
			$result = pg_query($query); // or die('Query failed: ' . pg_last_error());
		
			// Evaluate result
			if (!$result) {
			$errors['common'] = pg_last_error();
		
			$detail->status = 0;
			$detail->errors = $errors;
			}
		
			// Free resultset
			pg_free_result($result);
		} else {
			$detail->status = 0;
			$detail->errors = $errors;
			}
		
			// Closing connection
			pg_close($dbconn);
		
			// Print results in JSON
			echo json_encode($detail);		
	}
	
	public function updateFileNames($files){
		require_once "includes/db.php";
		$i=1;
		foreach ($files as $file) {
			$image_name=$this->getFirstPartOfName(basename($file));			
			$query = "UPDATE receipts SET image_name = '{$image_name}' WHERE id = {$i}";
			$result = pg_query($query);
			pg_free_result($result);
			$i++;			
		}	
		pg_close($dbconn);
	}
	
	private function getFirstPartOfName($name){
		$pos=strpos($name,"_");
		return $pos>0 ? substr($name,0,$pos) : $name;
	}	
}