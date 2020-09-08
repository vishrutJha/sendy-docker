<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php include('housekeeping.php');?>
<?php 
	$app = isset($_POST['app']) && is_numeric($_POST['app']) ? mysqli_real_escape_string($mysqli, (int)$_POST['app']) : 0;
	$lid = isset($_POST['lid']) && is_numeric($_POST['lid']) ? mysqli_real_escape_string($mysqli, (int)$_POST['lid']) : 0;
	$action = is_numeric($_POST['action']) ? mysqli_real_escape_string($mysqli, $_POST['action']) : exit;
	
	//Delete unopen subscribers
	if($action==1) 
	{
		//Get subscriber ids to delete
		$sub_ids = get_notopen_total($lid, $app, false);
	}
	//Delete unclick subscribers
	else if($action==2) 
	{
		//Get subscriber ids to delete
		$sub_ids = get_notclick_total($lid, $app, false);
	}
	
	//Delete subscribers
	$q = 'DELETE FROM subscribers WHERE id IN ('.$sub_ids.')';
	$r = mysqli_query($mysqli, $q);	
	if($r)
	{
		//Return total number of subscribers after deleting
		if($action==1)
		{
			echo get_totals($lid).':'.get_notclick_total($lid, $app);
		}
		else if($action==2)
		{
			echo get_totals($lid).':'.get_notopen_total($lid, $app);
		}
	}
	else echo false;
	
	//------------------------------------------------------//
	function get_totals($lid='')
	//------------------------------------------------------//
	{
		global $mysqli;
		
		$q = 'SELECT COUNT(*) FROM subscribers WHERE list = '.$lid.' AND unsubscribed = 0 AND bounced = 0 AND complaint = 0 AND confirmed = 1';
		$r = mysqli_query($mysqli, $q);
		if ($r) while($row = mysqli_fetch_array($r)) return number_format($row['COUNT(*)']);
	}
?>