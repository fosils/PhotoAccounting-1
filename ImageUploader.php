<?php

	/** COPYRIGHT Time at Task Aps*/
	require_once "aws-sdk/1.5.14/sdk.class.php";
	require_once "data/PhotoAccountingDatalayer.php";
	
	$db = new PhotoAccountingDatalyer();
	
	$xmlContent = array();
	if(sizeof($_FILES) > 0) {
		foreach($_FILES as $imageFile) {
			if (!isset($imageFile['tmp_name']) ||
				strlen($imageFile['tmp_name']) == 0) {
				echo "<response><code>200</code></response>";
			} else {
				$illegalCharRegEx = '/[^(\x20-\x7F)]*/';
	    			$devicetoken = preg_replace($illegalCharRegEx,'', trim($_REQUEST["devicetoken"]));	
	    			$imagetype = preg_replace($illegalCharRegEx,'', trim($_REQUEST["imagetype"]));	
				$email = @trim($_REQUEST["email"]);
				
				$folderName = "images/{$devicetoken}";
				$imageCategory = $imagetype;
				
				if(!is_dir($folderName)) {
					mkdir($folderName);
				}
				
				//////////////////////////////////////////////////////////////////////////////
				// PostgreSql Code
				//////////////////////////////////////////////////////////////////////////////
				$result = $db->CDV_GetCustomerID($devicetoken);
				$result = (is_bool($result)) ? null : pg_fetch_assoc($result);
				$customer_id = 0;
				
				if(is_null($result)){
					unset($result);
						
					$result = $db->CUST_Create($email);
					
					if($result){
						pg_free_result($result);
						$result = $db->CUST_GetByEmail($email);
						$result = (!is_bool($result)) ? pg_fetch_assoc($result) : null;
						
						if(!is_null($result))
							$customer_id = $result["customer_id"];
						else{
							echo "<response><code>200</code></response>";
							exit();
						}
					}
					
				}else{
					$customer_id = $result["customer_id"];
				}
				///////////////////////////////////////////////////////////////////////////////
				
				$newfilename = @strtotime("now");
				$newfilename = $imageCategory."_".$newfilename.".jpeg";
				move_uploaded_file($imageFile['tmp_name'],
							$folderName."/".$newfilename);
				
				//Check to make sure that the uploaded file contains something
				//This should take care of any 0 byte files being stored on the server.
				if($imageFile['size'] <= 0){
					echo "<response><code>200</code></response>";
					continue;
				}else{
					///////////////////////////////////////////////////////////////////
					// S3 Code
					///////////////////////////////////////////////////////////////////
					// Instantiate the AmazonS3 class
					$s3 = new AmazonS3(array("key"=>"AKIAINETGK3VTKANM25Q", "secret"=>"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"));
					$s3->ssl_verification = false;
					///////////////////////////////////////////////////////////////////
				
					///////////////////////////////////////////////////////////////////
					// S3 Code
					///////////////////////////////////////////////////////////////////
					$file = $folderName."/".$newfilename;
					$bucket = "all-user-files";
					$response = $s3->create_object($bucket, $file, array(
							'fileUpload' => $file
					));
					///////////////////////////////////////////////////////////////////
					
					if ($response->isOk()){
						////////////////////////////////////////////////////////////////////////////////
						// PostgreSql 
						////////////////////////////////////////////////////////////////////////////////
						$result = $db->RCT_Create($customer_id, "$bucket/$file");
						
						if(!$result){
	    						echo "<response><code>200</code></response>";
	    						exit();
						}
						////////////////////////////////////////////////////////////////////////////////
						
						echo "<response><code>100</code></response>";
					}else{
	    				echo "<response><code>200</code></response>";
					}
				}
			}
		}
    } else {
    	echo "<response><code>200</code></response>";
    }
    
?>
