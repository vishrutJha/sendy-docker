<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 	
	$q = 'SELECT api_key_prev FROM login WHERE id = '.get_app_info('userID');
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0) while($row = mysqli_fetch_array($r)) $api_key_prev = $row['api_key_prev'];
	
	$q = 'UPDATE login SET api_key = "'.$api_key_prev.'", api_key_prev = "" WHERE id = '.get_app_info('userID');
	$r = mysqli_query($mysqli, $q);
	if ($r) echo $api_key_prev;
	else echo "failed";
?>