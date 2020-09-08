<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	$lid = is_numeric($_POST['lid']) ? mysqli_real_escape_string($mysqli, (int)$_POST['lid']) : exit;
	$enable_gdpr = isset($_POST['enable_gdpr']) ? mysqli_real_escape_string($mysqli, $_POST['enable_gdpr']) : '';
	$marketing_permission = isset($_POST['marketing_permission']) ? mysqli_real_escape_string($mysqli, $_POST['marketing_permission']) : '';
	$what_to_expect = isset($_POST['what_to_expect']) ? mysqli_real_escape_string($mysqli, $_POST['what_to_expect']) : '';
	if($enable_gdpr=='yes') $enable_gdpr = 1;
	else if($enable_gdpr=='no') $enable_gdpr = 0;
	else exit;
	
	if(isset($_POST['enabled_disable_only']))
	{
		//Save GDPR fields to list
		$q = 'UPDATE lists SET gdpr_enabled = '.$enable_gdpr.' WHERE id = '.$lid;
		$r = mysqli_query($mysqli, $q);
		if ($r) echo true;
		else echo false;
	}
	else
	{
		//Save GDPR fields to list
		$q = 'UPDATE lists SET gdpr_enabled = '.$enable_gdpr.', marketing_permission = "'.$marketing_permission.'", what_to_expect = "'.$what_to_expect.'" WHERE id = '.$lid;
		$r = mysqli_query($mysqli, $q);
		if ($r) echo 'saved';
		else echo 'failed';
	}
?>