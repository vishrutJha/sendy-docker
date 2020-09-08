<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php include('housekeeping.php');?>
<?php 
	$lid = isset($_POST['lid']) && is_numeric($_POST['lid']) ? mysqli_real_escape_string($mysqli, (int)$_POST['lid']) : exit;
	$app = isset($_POST['app']) && is_numeric($_POST['app']) ? mysqli_real_escape_string($mysqli, (int)$_POST['app']) : exit;
	
	echo get_notclick_total($lid, $app);
?>