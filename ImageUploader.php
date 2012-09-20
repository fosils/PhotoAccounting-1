<?php

	/** COPYRIGHT Time at Task Aps*/
	require_once "aws-sdk/1.5.14/sdk.class.php";
	
	$xmlContent = array();
	if(sizeof($_FILES) > 0) {
		foreach($_FILES as $imageFile) {
			if($imageFile['size'] <= 0)
				continue;
			
			if (!isset($imageFile['tmp_name']) ||
				strlen($imageFile['tmp_name']) == 0) {
				echo "<response><code>200</code></response>";
			} else {
				///////////////////////////////////////////////////////////////////
				// S3 Code
				///////////////////////////////////////////////////////////////////
				// Instantiate the AmazonS3 class
				$s3 = new AmazonS3(array("key"=>"AKIAINETGK3VTKANM25Q", "secret"=>"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"));
				$s3->ssl_verification = false;
				///////////////////////////////////////////////////////////////////
				
				$folderName = "images/{$_REQUEST["devicetoken"]}";
				$imageCategory = $_REQUEST["imagetype"];
				if(!is_dir($folderName)) {
					mkdir($folderName);
				}
				
				$newfilename = @strtotime("now");
				$newfilename = $imageCategory."_".$newfilename.".jpeg";
				move_uploaded_file($imageFile['tmp_name'],
						$folderName."/".$newfilename);
				
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
					echo "<response><code>100</code></response>";
				}else{
    				echo "<response><code>200</code></response>";
				}
			}
		}
    } else {
    	echo "<response><code>200</code></response>";
    }
    
?>
