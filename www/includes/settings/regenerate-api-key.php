<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	$api_key = str_makerand(20, 20, true, false, true);
	$api_key_prev = get_app_info('api_key');
	
	$q = 'UPDATE login SET api_key = "'.$api_key.'", api_key_prev = "'.$api_key_prev.'" WHERE id = '.get_app_info('userID');
	$r = mysqli_query($mysqli, $q);
	if ($r) echo $api_key;
	else echo "failed";
?>