<?php
        require_once "aws-sdk/1.5.14/sdk.class.php";
        require_once "data/PhotoAccountingDatalayer.php";

        $db = new PhotoAccountingDatalayer();

	$userEmail = $_REQUEST['email'];
	$folderName = $_REQUEST["devicetoken"];
	$devicetoken = $folderName;
	

        //////////////////////////////////////////////////////////////////////////////
        // PostgreSql Code
        //////////////////////////////////////////////////////////////////////////////
        $customer_id = 0;

        $result = $db->CDV_Get($devicetoken);
        $result = (pg_num_rows($result) <= 0) ? null : pg_fetch_assoc($result);

        if(is_null($result)){
                $result = create_customer($userEmail);
                
		if(!is_null($result)){
                        $customer_id = $result["id"];
                        print "customer_id: $customer_id\r\n";

                        $result = $db->CDV_Create($customer_id, $devicetoken);
			
			//If the result is false then the procedure failed.
			//this means that the table structure changed or the database is altered in some
			//other way.
                        if(!$result){
                               echo "<response><code>200</code></response>";
                                exit();
                        }
                }else{
                       echo "<response><code>200</code></response>";
                       exit();
                }
        }else{
                $customer_id = $result["id"];

                $result = $db->CUST_UpdateEmail($customer_id, $userEmail);

                if(!$result){
                        echo "<response><code>200</code></response>";
                        exit();
                }
        }
        //////////////////////////////////////////////////////////////////////////////	
	
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
    
?>
