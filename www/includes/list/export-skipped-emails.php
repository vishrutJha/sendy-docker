<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	/********************************/
	$l = isset($_GET['l']) && is_numeric($_GET['l']) ? mysqli_real_escape_string($mysqli, (int)$_GET['l']) : '';
	$filename = 'not-imported.csv';
	$data = '';
	/********************************/
	
	//Get all skipped eamils from last import
	$q = 'SELECT email, reason FROM skipped_emails use index (s_list) WHERE list = '.$l.' ORDER BY reason ASC, email ASC';
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$email = $row['email'];
			$reason = $row['reason'];
			
			if($reason==1)
				$data .= $email.',Malformed'."\n";
			else if($reason==2)
				$data .= $email.',Unsubscribed / Bounced /Marked as spam'."\n";
			else if($reason==3)
				$data .= $email.',Already exists'."\n";
			else if($reason==4)
				$data .= $email.',Suppressed'."\n";
	    }  
	}
	$first_line = '"'._('Email address').'","'._('Reason').'"'."\n";
	$data = $first_line.str_replace("\r" , "" , $data);
	
	header("Content-type: application/csv");
	header("Content-Disposition: attachment; filename=$filename");
	header("Pragma: no-cache");
	header("Expires: 0");
	print "$data";
?>