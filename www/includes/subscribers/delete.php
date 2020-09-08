<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	$subscriber_id = isset($_POST['subscriber_id']) && is_numeric($_POST['subscriber_id']) ? mysqli_real_escape_string($mysqli, (int)$_POST['subscriber_id']) : exit;
	$option = is_numeric($_POST['option']) ? mysqli_real_escape_string($mysqli, $_POST['option']) : exit;
	$app = is_numeric($_POST['app']) ? mysqli_real_escape_string($mysqli, $_POST['app']) : exit;
	
	//Delete from this list only
	if($option==1)
	{
		$q = 'DELETE FROM subscribers WHERE id = '.$subscriber_id.' AND userID = '.get_app_info('main_userID');
		$r = mysqli_query($mysqli, $q);
		if ($r)
		{
			echo true; 
		}
	}
	//Delete from ALL lists
	else if($option==2)
	{
		$q = 'SELECT email FROM subscribers WHERE id = '.$subscriber_id;
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0) while($row = mysqli_fetch_array($r)) $email = $row['email'];
		
		$list_array = array();
		$q = 'SELECT id FROM lists WHERE app = '.$app;
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				array_push($list_array, $row['id']);
		    }  
		}
		$lists = implode(',', $list_array);
		
		$q = 'DELETE FROM subscribers WHERE email = "'.$email.'" AND list IN ('.$lists.') AND userID = '.get_app_info('main_userID');
		$r = mysqli_query($mysqli, $q);
		if ($r)
		{
			echo true; 
		}
	}
?>