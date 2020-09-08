<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	//------------------------------------------------------//
	//                      	INIT                       //
	//------------------------------------------------------//
	
	$filename = mysqli_real_escape_string($mysqli, filter_var($_POST['filename'],FILTER_SANITIZE_SPECIAL_CHARS));
	$ares_id = isset($_POST['ares_id']) && is_numeric($_POST['ares_id']) ? mysqli_real_escape_string($mysqli, (int)$_POST['ares_id']) : exit;
	
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	//delete file
	if(unlink('../../uploads/attachments/a'.$ares_id.'/'.basename($filename)))
		echo true;
?>