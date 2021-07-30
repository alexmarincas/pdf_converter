<?php

$fileName = $_FILES['fileToUpload']['name'];
$fileTmpLoc = $_FILES['fileToUpload']['tmp_name'];
$fileType = $_FILES['fileToUpload']['type'];
$fileSize = $_FILES['fileToUpload']['size'];
$fileErrorMsg = $_FILES['fileToUpload']['error'];

// UPLOAD DOCUMENT
if(!$fileTmpLoc){
	echo "a";

}elseif($fileType != "application/pdf"){
	echo "b";
}else{

	$fileName = str_replace(' ', '', $fileName);
	
	$temp = explode(".", $fileName);
	$newfileName = 'pdf.' . end($temp);
	
	
	if(move_uploaded_file($fileTmpLoc, "../docs/$newfileName")){
		echo "c";
	}else{
		echo "d";
	}
}

?>