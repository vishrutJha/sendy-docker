<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	$app = isset($_POST['app']) && is_numeric($_POST['app']) ? mysqli_real_escape_string($mysqli, (int)$_POST['app']) : 0;
	$email_id = isset($_POST['email_id']) && is_numeric($_POST['email_id']) ? mysqli_real_escape_string($mysqli, (int)$_POST['email_id']) : 0;
	
	$q = 'DELETE FROM suppression_list WHERE app = '.$app.' AND id = '.$email_id;
	$r = mysqli_query($mysqli, $q);
	if ($r) echo true;
	else echo false;
?>