<?php

/** COPYRIGHT Time at Task Aps*/

/** Post-Cache means images to be kept in memory after having been viewed (so one can quickly go backwards in the image gallery.*/
define("POST_CACHE", 2);
define("PRE_CACHE", 5);

/**
 * Iterates over a directory and returns file objects.
 *
 * @param string $dir
 * @param mixed $filter
 * @param bool $recursive defaults to false
 * @param bool $addDirs return directories as well as files - defaults to false
 * @return array
 *
 */
function getFilesInDir($dir, $filter='', $recursive=false, $addDirs=false){

	$res = array();

	$dirIterator = new DirectoryIterator($dir);
	while($dirIterator->valid()) {
		if(!$dirIterator->isDot()) {
			$file = $dirIterator->getPathname();
			$isDir = is_dir($file);
			if(!$isDir || $addDirs){
				if(empty($filter) || fnmatch($filter, $file)){
					$res[] = $file;
				}
			}
			if($isDir && $recursive){
				$res = array_merge(
						$res,
						getFilesInDir($file, $filter='', $recursive));
			}
		}
		$dirIterator->next();
	}

	return $res;
}


if(isset($_GET['imageID']) && isset($_GET['imageonly'])){
	$files = getImagesInDir('images');
	if(!isset($_GET['data'])){
		header("Content-type: image/png");
		echo file_get_contents($files[$_GET['imageID']-1]);
	}
	else{
		header("Content-type: image/png");
		echo file_get_contents($files[$_GET['imageID']]);
	}
	die();
}

function getImagesInDir($dir){
	//  return array_slice(array_merge(getFilesInDir('images', '*.jp*g',false, false), getFilesInDir('images','*.png',false, false)), 0, PRE_CACHE);
	return array_merge(getFilesInDir('images', '*.jp*g',false, false), getFilesInDir('images','*.png',false, false));
}

if(isset($_GET['getimages'])){
	// Get all images files in current directory
	$files = getImagesInDir('images');
	echo json_encode($files);
	die();
}

$files = getImagesInDir('images');
require_once 'php/AccountImage.php';
$accountImage=new AccountImage();
$accountImage->updateFileNames($files);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf8" />
<link rel="stylesheet" type="text/css" href="css/jviewer.css">
<script src="js/gsdom.js"></script>
<script src="js/jviewer.js"></script>
<script src="js/YUI/build/yui/yui-min.js"></script>
<script src="js/accountsTable.js"></script>

<script>
	YUI_config = {
		    debug: false,
		    combine: false,
		    Base: <?php echo "'https://".$_SERVER['HTTP_HOST'].str_replace("index.php", "", $_SERVER['PHP_SELF'])."'" ; ?>,
		    root: '/js/YUI/build/'
	};
	var $ = function(id){
		return document.getElementById(id);
	}

	var my_codes = Array();

	/* This array represent the keycode for keys that can't be used as a hotkey. */
	document.reservedKeys = [
		8, // backspace
		9, // tab
		13, // enter
		16, // shift
		17, // ctrl
		18, // alt
		32, // space
		45, // insert
		46 // delete
	];

	JSViewer.start(<?php echo count($files); ?>, <?php echo POST_CACHE; ?>, <?php echo PRE_CACHE; ?>, <?php echo isset($_GET['imageID'])?$_GET['imageID']:0; ?>,my_codes);
   </script>
</head>
<body>
	<div id="jsv_left">
		<div id="log"></div>
		<div id="log2"></div>
	</div>
	<div id="jsv_right">
		<div id="flash_errors"></div>
		<div id="jsv_form">
			<ul>
				<li><div id="jsv_image_name"></div></li>
				<li><label for="jsv_enclosure_number">Enclosure Number</label>
					<div>
						<input type="text" name="jsv_enclosure_number" id="jsv_enclosure_number" value="1"
							size="10" /><span id="error_enclosure_number" class="field_error"></span>
					</div>
				</li>
				<li><label for="jsv_date">Date</label>
					<div>
						<input type="text" name="jsv_date" id="jsv_date" size="10" /><span
							id="error_date" class="field_error"></span>
					</div>
				</li>
				<li><label for="jsv_text">Text</label>
					<div>
						<textarea name="jsv_text" id="jsv_text" cols="41"></textarea>
						<br /> <span id="error_text" class="field_error"></span>
					</div>
				</li>
				<li><label for="jsv_offset_account">Offset Account</label>
					<div>
						<input type="text" name="jsv_offset_account" id="jsv_offset_account" size="10" /><span
							id="error_offset_account" class="field_error"></span>
					</div>
				</li>
				<li><label for="jsv_account">Account</label>
					<div>
						<input type="text" name="jsv_account" id="jsv_account" size="10" />
						<span id="error_account" class="field_error"></span>
						<span id="account_name" class="account_name"></span>
					</div>
				</li>
				<li><label for="jsv_amount">Amount</label>
					<div>
						<input type="text" name="jsv_amount" id="jsv_amount" size="10" /><span
							id="error_amount" class="field_error"></span>
					</div>
				</li>
			</ul>
		</div>
		<div id="economicAccountsData" class="yui3-skin-sam"></div>
	</div>
</body>
</html>
