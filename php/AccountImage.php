<?php
class AccountImage{
	
	function _construct(){		
	}	

	public function geImageName($imageID){
		session_name('PhotoAccounting');
		session_start();		
		if (isset($imageID)&&isset($_SESSION['files'])){
			echo $this->getFirstPartOfName(basename($_SESSION['files'][$imageID]));
		}		
	}
	
	private function getFirstPartOfName($name){
		$pos=strpos($name,"_");
		return $pos>0 ? substr($name,0,$pos) : $name;
	}
}