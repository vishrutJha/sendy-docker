<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	$app = isset($_POST['app']) && is_numeric($_POST['app']) ? mysqli_real_escape_string($mysqli, (int)$_POST['app']) : 0;
	$domain_id = isset($_POST['domain_id']) && is_numeric($_POST['domain_id']) ? mysqli_real_escape_string($mysqli, (int)$_POST['domain_id']) : 0;
	
	$q = 'DELETE FROM blocked_domains WHERE app = '.$app.' AND id = '.$domain_id;
	$r = mysqli_query($mysqli, $q);
	if ($r) echo true;
	else echo false;
?>