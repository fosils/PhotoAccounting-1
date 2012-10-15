<?php

	/** COPYRIGHT Time at Task Aps*/
	require_once "aws-sdk/1.5.14/sdk.class.php";
	require_once "data/PhotoAccountingDatalayer.php";
	
	$db = new PhotoAccountingDatalayer();
	
	$xmlContent = array();
	if(sizeof($_FILES) > 0) {
		foreach($_FILES as $imageFile) {
			if (!isset($imageFile['tmp_name']) ||
				strlen($imageFile['tmp_name']) == 0) {
				echo "<response><code>200</code></response>";
			} else {
				$illegalCharRegEx = '/[^(\x20-\x7F)]*/';
    				$devicetoken = preg_replace($illegalCharRegEx,'', trim($_REQUEST["devicetoken"]));	
    				$imagetype = $imageFile["name"];//preg_replace($illegalCharRegEx,'', trim($_REQUEST["imagetype"]));	
				$imagetype = substr($imagetype, strrpos($imagetype, ".")+1);
				$email = "";//@trim($_REQUEST["email"]);
				
				$folderName = "images/{$devicetoken}";
				$imageCategory = $imagetype;
				
				if(!is_dir($folderName)) {
					mkdir($folderName);
				}

        			//////////////////////////////////////////////////////////////////////////////
        			// PostgreSql Code
        			//////////////////////////////////////////////////////////////////////////////
        			$customer_id = 0;

        			$result = $db->CDV_Get($devicetoken);
        			$result = (pg_num_rows($result) <= 0) ? null : pg_fetch_assoc($result);

        			if(is_null($result)){
                			$result = create_customer();

                			if(!is_null($result)){
			                        $customer_id = $result["id"];

                        			$result = $db->CDV_Create($customer_id, $devicetoken);

                        			if(!$result){
                               				echo "<response><code>200</code></response>";
                                			exit();
                        			}
                			}else{
                       				echo "<response><code>200</code></response>";
                       				exit();
                			}
        			}
        			//////////////////////////////////////////////////////////////////////////////			
	
				$newfilename = @strtotime("now");
				$newfilename = "{$imageCategory}_{$newfilename}.{$imagetype}";
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
