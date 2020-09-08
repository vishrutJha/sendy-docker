<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	$app = isset($_POST['app']) && is_numeric($_POST['app']) ? mysqli_real_escape_string($mysqli, (int)$_POST['app']) : 0;
	$lid = isset($_POST['lid']) && is_numeric($_POST['lid']) ? mysqli_real_escape_string($mysqli, (int)$_POST['lid']) : 0;
	$action = is_numeric($_POST['action']) ? mysqli_real_escape_string($mysqli, $_POST['action']) : exit;
	
	//If delete unconfirmed subscribers from individual lists
	if($app == 0)
	{
		$list_line = 'list = '.$lid;
	}
	else //If delete unconfirmed subscribers from ALL lists
	{
		$lids = '';		
		$q = 'SELECT id FROM lists WHERE app = '.$app.' AND opt_in = 1';
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    	$lids .= $row['id'].',';
		    	
		    $lids = substr($lids, 0, -1);
		}
		$list_line = 'list IN ('.$lids.')';
	}
	
	if($action==1) //Delete unconfirmed subscribers who didn't confirm their subscription for 1 week
		$q = 'DELETE FROM subscribers WHERE '.$list_line.' AND confirmed = 0 AND bounced = 0 AND complaint = 0 AND UNIX_TIMESTAMP() - timestamp < 604800';
	else if($action==2) //Delete unconfirmed subscribers who didn't confirm their subscription for more than 1 week
		$q = 'DELETE FROM subscribers WHERE '.$list_line.' AND confirmed = 0 AND bounced = 0 AND complaint = 0 AND (UNIX_TIMESTAMP() - timestamp < 1209600 AND UNIX_TIMESTAMP() - timestamp > 604800)';	
	else if($action==3) //Delete unconfirmed subscribers who didn't confirm their subscription for more than 2 weeks
		$q = 'DELETE FROM subscribers WHERE '.$list_line.' AND confirmed = 0 AND bounced = 0 AND complaint = 0 AND UNIX_TIMESTAMP() - timestamp >= 1209600';	
	else if($action==0) //Delete all unconfirmed subscribers
		$q = 'DELETE FROM subscribers WHERE '.$list_line.' AND confirmed = 0 AND bounced = 0 AND complaint = 0';

	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
		$q2 = 'SELECT COUNT(*) FROM subscribers WHERE list = '.$lid.' AND confirmed = 0 AND bounced = 0 AND complaint = 0';
		$r2 = mysqli_query($mysqli, $q2);
		if ($r2)
		{
		    while($row = mysqli_fetch_array($r2))
				echo $row['COUNT(*)'];
		}
	}
	else echo false;
?>