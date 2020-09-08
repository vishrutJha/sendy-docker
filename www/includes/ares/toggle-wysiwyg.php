<?php 
	include('../functions.php');
	include('../login/auth.php');
	
	$toggle = isset($_POST['toggle']) ? mysqli_real_escape_string($mysqli, $_POST['toggle']) : exit;
	$c = isset($_POST['ae']) && is_numeric($_POST['ae']) ? mysqli_real_escape_string($mysqli, (int)$_POST['ae']) : exit;
	
	if($toggle==_('Save and switch to HTML editor'))
		$toggle = 0;
	else
		$toggle = 1;
	
	$q = 'UPDATE ares_emails SET wysiwyg='.$toggle.' WHERE id='.$c;
	$r = mysqli_query($mysqli, $q);
	if ($r)
		echo true;
?>