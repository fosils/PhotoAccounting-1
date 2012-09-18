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
    	<div style='background-color:#0c0c0c'>Existing Files</div>
    	<div>
    		<ul>
		    <?php
			    if($response->isOk()){
			    	
				    foreach($response->body->Contents as $obj){
				    	$url = $s3->get_object_url($bucket, $obj-Key, '1 hour', array(
						    'response' => array(
						        'content-type'     => 'text/plain',
						        'content-language' => 'en-US',
						        'expires'          => gmdate(DATE_RFC2822, strtotime('1 January 1980'))
						    )
						));
				    	
				    	print "<li etag='{$obj->ETag}' size='{$obj->Size}' type='{$obj->StorageClass}'><a target='_blank' href='$url'>{$obj->Key}</a></li>";
				    }
				    
			    }
			?>
			</ul>
    	</div>
    </div>
    <div style='border:1px solid black;'>
    	<div style='background-color:#0c0c0c'>Upload New File</div>
    	<div>
    		<form action='ImageUploader.php' method='GET'>
    			<ul>
    				<li>
    					<label for='deviceToken'>Device Token</label>
    					<input type='text' name='deviceToken' id='deviceToken' />
    				</li>
    				<li>
    					<label for='deviceToken'>Image Type</label>
    					<input type='text' name='imageType' id='imageType' />
    				</li>
    				<li>
    					<label for='deviceToken'>Image</label>
    					<input type='file' name='newImage' id='newImage' />
    				</li>
    				<li>
    					<button type='submit' id='submitImage' name='submitImage'>Submit Image</button>
    				</li>
    			</ul>
    		</form>
    	</div>
    </body>
</html>