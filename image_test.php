<?php
	require_once "aws-sdk/1.5.14/sdk.class.php";
	
	// Instantiate the AmazonS3 class
	$s3 = new AmazonS3(array("key"=>"AKIAINETGK3VTKANM25Q", "secret"=>"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"));
	$s3->ssl_verification = false;
	$bucket = "all-user-files";
	 
	$response = $s3->list_objects($bucket);
?>
<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=Cp1252">
		<title>Image Upload Test</title>
	</head>
    <body>
    <div style='border:1px solid black;'>
    	<div style='background-color:#0c0c0c;color:white;'>Existing Files</div>
    	<div>
    		<ul>
		    <?php
			    if($response->isOk()){
			    	
				    foreach($response->body->Contents as $obj){
				    	$url = $s3->get_object_url($bucket, $obj->Key, (time()+3600));
				    	
				    	print "<li etag='{$obj->ETag}' size='{$obj->Size}' type='{$obj->StorageClass}'><a target='_blank' href='$url'>{$obj->Key}</a></li>";
				    }
				    
			    }
			?>
			</ul>
    	</div>
    </div>
    <div style='border:1px solid black;'>
    	<div style='background-color:#0c0c0c;color:white;'>Upload New File</div>
    	<div>
			<form action='ImageUploader.php' method='POST' id='fImageUpload' enctype='multipart/form-data'>
    			<ul style='list-style:none;'>
    				<li>
    					<label for='deviceToken'>Device Token</label>
    					<input type='text' name='devicetoken' id='devicetoken' />
    				</li>
    				<li>
    					<label for='deviceToken'>Image Type</label>
    					<input type='text' name='imagetype' id='imagetype' />
    				</li>
    				<li>
    					<label for='deviceToken'>Image</label>
    					<input type='file' name='newImage' id='newImage' />
    				</li>
    				<li style='text-indent:100px;'>
    					<button type='submit' id='submitImage' name='submitImage'>Submit Image</button>
    				</li>
    			</ul>
    		</form>
    	</div>
    </body>
</html>