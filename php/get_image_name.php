<?php
session_name('PhotoAccounting');
session_start();

if (isset($_POST['image_id'])&&isset($_SESSION['files'])){
	echo getFirstPartOfName(basename($_SESSION['files'][$_POST['image_id']]));
}

function getFirstPartOfName($name){
	$pos=strpos($name,"_");
	return $pos>0 ? substr($name,0,$pos) : $name;
}