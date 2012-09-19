<?php

	$userEmail = $_GET['email'];
	$folderName = $_GET["devicetoken"];
	
	///////////////////////////////////////////////////////////////////
	// S3 Code
	///////////////////////////////////////////////////////////////////
	// Instantiate the AmazonS3 class
	$s3 = new AmazonS3(array("key"=>"AKIAINETGK3VTKANM25Q", "secret"=>"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"));
	$bucket = "all-user-files";
	///////////////////////////////////////////////////////////////////
	
	if(!is_dir($folderName)) {
		mkdir($folderName);
	}
				
	$fileName = $folderName."/useremail.txt";
	$fp = fopen($fileName, 'w');
	fwrite($fp,$userEmail);
	fflush($fp);
	fclose($fp);
	unset($fp);
	
	///////////////////////////////////////////////////////////////////
	// S3 Code
	///////////////////////////////////////////////////////////////////
	$file = $fileName;
	$bucket = "all-user-files";
	$response = $s3->create_object($bucket, $file, array(
			'fileUpload' => $file
	));
	///////////////////////////////////////////////////////////////////
	
	if($response->isOk())
		echo "<response><code>100</code></response>";
    else 
    	echo "<response><code>200</code></response>";
//    } else {
//    	echo "<response><code>200</code></response>";
//    }
    
?>
