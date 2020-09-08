<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 	
	$ares_id = isset($_POST['ares_id']) && is_numeric($_POST['ares_id']) ? mysqli_real_escape_string($mysqli, (int)$_POST['ares_id']) : exit;
	$ares_title = mysqli_real_escape_string($mysqli, $_POST['ares_title']);
	
	//Update campaign title
	$q = 'UPDATE ares SET name = "'.$ares_title.'" WHERE id = '.$ares_id;
	$r = mysqli_query($mysqli, $q);
	if ($r) echo true;
	else echo false;
?>