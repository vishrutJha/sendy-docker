<?php 
	include('../functions.php');
	include('../login/auth.php');
	
	$toggle = mysqli_real_escape_string($mysqli, $_POST['toggle']);
	$app = isset($_POST['i']) && is_numeric($_POST['i']) ? mysqli_real_escape_string($mysqli, (int)$_POST['i']) : exit;
	$c = isset($_POST['c']) && is_numeric($_POST['c']) ? mysqli_real_escape_string($mysqli, (int)$_POST['c']) : exit;
	
	if($toggle==_('Save and switch to HTML editor'))
		$toggle = 0;
	else
		$toggle = 1;
	
	$q = 'UPDATE campaigns SET wysiwyg='.$toggle.' WHERE app = '.$app.' AND id='.$c;
	$r = mysqli_query($mysqli, $q);
	if ($r)
		echo true;
?>