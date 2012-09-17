<?php
	$userEmail = $_GET['email'];
	$folderName = $_GET["devicetoken"];
	
	if(!is_dir($folderName)) {
		mkdir($folderName);
	}
				
	$fileName = $folderName."/useremail.txt";
	$fp = fopen($fileName, 'w');
	fwrite($fp,$userEmail);
	fflush($fp);
	fclose($fp);
	unset($fp);
	echo "<response><code>100</code></response>";
    
//    } else {
//    	echo "<response><code>200</code></response>";
//    }
    
?>
