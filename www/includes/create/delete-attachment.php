<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	//------------------------------------------------------//
	//                      	INIT                       //
	//------------------------------------------------------//
	
	$filename = mysqli_real_escape_string($mysqli, filter_var($_POST['filename'],FILTER_SANITIZE_SPECIAL_CHARS));
	$campaign_id = isset($_POST['campaign_id']) && is_numeric($_POST['campaign_id']) ? mysqli_real_escape_string($mysqli, (int)$_POST['campaign_id']) : exit;
	
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	//delete file
	if(unlink('../../uploads/attachments/'.$campaign_id.'/'.basename($filename)))
		echo true;
?>