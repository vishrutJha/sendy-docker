<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	$campaign_id = isset($_GET['c']) && is_numeric($_GET['c']) ? mysqli_real_escape_string($mysqli, (int)$_GET['c']) : exit;
	$filename = 'campaign-errors.csv';
	$lines = "Name,Email,Error code\n";
	
	$q = 'SELECT errors FROM campaigns WHERE id = '.$campaign_id;
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$errors = $row['errors'];
	    }  
	}
	
	$errors_array = explode(',', $errors);
	
	foreach($errors_array as $errs)
	{
		$errs_array = explode(':', $errs);
		$subscriber_id = $errs_array[0];
		$error_code = $errs_array[1];
		
		$q2 = 'SELECT name, email FROM subscribers WHERE id = '.$subscriber_id;
		$r2 = mysqli_query($mysqli, $q2);
		if ($r2 && mysqli_num_rows($r2) > 0)
		{
		    while($row = mysqli_fetch_array($r2))
		    {
				$name = $row['name'];
				$email = $row['email'];
				$lines .= $name.','.$email.','.$error_code."\n";
		    }  
		}
	}
	
	//Export
	$lines = str_replace("\r" , "" , $lines);
	if ( $lines == "" )
	{
	    $lines = "\n(0) Records Found!\n";                    
	}
	
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=$filename");
	header("Pragma: no-cache");
	header("Expires: 0");
	print "$lines";
?>