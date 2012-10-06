<?php
        require_once "aws-sdk/1.5.14/sdk.class.php";
        require_once "data/PhotoAccountingDatalayer.php";

        $db = new PhotoAccountingDatalyer();

	$userEmail = $_REQUEST['email'];
	$folderName = $_REQUEST["devicetoken"];
	$devicetoken = $folderName;
	
	//////////////////////////////////////////////////////////////////////////////
        // PostgreSql Code
        //////////////////////////////////////////////////////////////////////////////
        $result = $db->CUST_GetByEmail($userEmail);
        $result = (pg_num_rows($result) <= 0) ? null : pg_fetch_assoc($result);
        $customer_id = 0;

        if(is_null($result)){
                unset($result);

                $result = $db->CUST_Create($userEmail);

                if(is_bool($result) && $result){
                        unset($result);
                        $result = $db->CUST_GetByEmail($userEmail);
                        $result = (pg_num_rows($result) > 0) ? pg_fetch_assoc($result) : null;

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

        $result = $db->CDV_ExistsForCustomer($customer_id, $devicetoken);
        $result = (pg_num_rows($result) <= 0) ? null : pg_fetch_assoc($result);

        print_r($result);

        if(is_null($result)){
                $result = $db->CDV_Create($customer_id, $devicetoken);

                if(!$result){
                        echo "<response><code>200</code></response>";
                        exit();
                }
        }
        ///////////////////////////////////////////////////////////////////////////////
	
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
