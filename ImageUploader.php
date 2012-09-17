<?php

/** COPYRIGHT Time at Task Aps*/


	$xmlContent = array();
	if(sizeof($_FILES) > 0) {
		foreach($_FILES as $imageFile) {
			if (!isset($imageFile['tmp_name']) ||
				strlen($imageFile['tmp_name']) == 0) {
				echo "<response><code>200</code></response>";
			} else {
				$folderName = $_GET["devicetoken"];
				$imageCategory = $_GET["imagetype"];
				if(!is_dir($folderName)) {
					mkdir($folderName);
				}
				
				$newfilename = strtotime("now");
				$newfilename = $imageCategory."_".$newfilename.".jpeg";
				move_uploaded_file($imageFile['tmp_name'],
						$folderName."/".$newfilename);
				echo "<response><code>100</code></response>";
			}
		}
    } else {
    	echo "<response><code>200</code></response>";
    }
    
?>
