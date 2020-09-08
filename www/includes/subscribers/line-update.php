<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php require_once('../helpers/EmailAddressValidator.php');?>
<?php

/********************************/
$userID = get_app_info('main_userID');
$app = isset($_POST['app']) && is_numeric($_POST['app']) ? mysqli_real_escape_string($mysqli, (int)$_POST['app']) : exit;
$listID = isset($_POST['list_id']) && is_numeric($_POST['list_id']) ? mysqli_real_escape_string($mysqli, (int)$_POST['list_id']) : exit;
$line = $_POST['line'];
$gdpr_tag = isset($_POST['gdpr_tag']) ? 1 : 0;
$time = time();
/********************************/

//Empty skipped_emails table
$q = 'DELETE FROM skipped_emails WHERE list = '.$listID;
mysqli_query($mysqli, $q);

//get comma separated lists belonging to this app
$q2 = 'SELECT id FROM lists WHERE app = '.$app;
$r2 = mysqli_query($mysqli, $q2);
if ($r2)
{
	$all_lists = '';
    while($row = mysqli_fetch_array($r2)) $all_lists .= $row['id'].',';
    $all_lists = substr($all_lists, 0, -1);
}

//if user did not enter anything
if($line=='')
{
	//show error msg
	header("Location: ".get_app_info('path').'/update-list?i='.$app.'&l='.$listID.'&e=2'); 
	exit;
}

$line_array = explode("\r\n", $line);

for($i=0;$i<count($line_array);$i++)
{
	$the_line = explode(',', mysqli_real_escape_string($mysqli, $line_array[$i]));
	
	if(count($the_line)==1)
	{
		$name = '';
		$email = $the_line[0];
	}
	else
	{
		$name = strip_tags($the_line[0]);
		$email = $the_line[1];
	}
	
	$email = filter_var(trim($email),FILTER_SANITIZE_EMAIL);
	
	//get email's domain
	$email_explode = explode('@', trim($email));
	$email_domain = $email_explode[1];
	
	$q = 'SELECT custom_fields FROM subscribers WHERE list = '.$listID.' AND email = "'.trim($email).'" AND userID = '.$userID;
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) > 0) //if so, update subscriber
	{
		while($row = mysqli_fetch_array($r))
	    {
	    	$custom_values = $row['custom_fields'];
	    } 
		
		if($columns==1)
		{
			$email = trim($linearray[0]);
		}
		else if($columns==2)
		{
			$name = strip_tags($linearray[0]);
			$email = trim($linearray[1]);
		}
		else if($columns==$custom_fields_count+2)
		{
			$name = strip_tags($linearray[0]);
			$email = trim($linearray[1]);
		}
		
		$gdpr_status = $gdpr_tag ? ', gdpr = '.$gdpr_tag : '';
		$email = filter_var($email,FILTER_SANITIZE_EMAIL);
	    
		if(!isset($name) || $name=='')
			$q = 'UPDATE subscribers SET '.$gdpr_status.' WHERE email = "'.$email.'" AND list = '.$listID;
		else
			$q = 'UPDATE subscribers SET name = "'.$name.'" '.$gdpr_status.' WHERE email = "'.$email.'" AND list = '.$listID;

		mysqli_query($mysqli, $q);
		
		skipped_emails($email, 'Exists');
	}
	else
	{
		//Check if user set the list to unsubscribe from all lists
		$q = 'SELECT unsubscribe_all_list FROM lists WHERE id = '.$listID;
		$r = mysqli_query($mysqli, $q);
		if ($r) while($row = mysqli_fetch_array($r)) $unsubscribe_all_list = $row['unsubscribe_all_list'];
		
		//See if we should check for unsubscribe status in all lists
		$unsubscribe_line = $unsubscribe_all_list ? '(complaint = 1 OR unsubscribed = 1)' : 'complaint = 1';
		
		//Check if this email is previously marked as bounced, if so, we shouldn't add it
		$q = 'SELECT email from subscribers WHERE ( email = "'.trim($email).'" AND bounced = 1 ) OR ( email = "'.trim($email).'" AND list IN ('.$all_lists.') AND '.$unsubscribe_line.' )';
		$r = mysqli_query($mysqli, $q);
		if (mysqli_num_rows($r) == 0)
		{
			$q2 = '(SELECT id FROM suppression_list WHERE email = "'.trim($email).'" AND app = '.$app.') UNION (SELECT id FROM blocked_domains WHERE domain = "'.$email_domain.'" AND app = '.$app.')';
			$r2 = mysqli_query($mysqli, $q2);
			if (mysqli_num_rows($r2) == 0)
			{
				$validator = new EmailAddressValidator;			
				if ($validator->check_email_address(trim($email))) 
				{
					$email = filter_var($email,FILTER_SANITIZE_EMAIL);
					$q = 'INSERT INTO subscribers (userID, name, email, list, timestamp, gdpr) values('.$userID.', "'.$name.'", "'.trim($email).'", '.$listID.', '.$time.', '.$gdpr_tag.')';
					mysqli_query($mysqli, $q);
				}
				else skipped_emails(trim($email), 'Malformed');
			}
			else
			{
				//Update block_attempts count				
				$q3 = 'UPDATE suppression_list SET block_attempts = block_attempts+1, timestamp = "'.$time.'" WHERE email = "'.trim($email).'" AND app = '.$app;
				$q4 = 'UPDATE blocked_domains SET block_attempts = block_attempts+1, timestamp = "'.$time.'" WHERE domain = "'.$email_domain.'" AND app = '.$app;
				mysqli_query($mysqli, $q3);
				mysqli_query($mysqli, $q4);
				
				skipped_emails(trim($email), 'Suppressed');
			}
		}
		else skipped_emails(trim($email), 'Bounced');
	}
}

//--------------------------------------------------------------//
function skipped_emails($email, $reason)
//--------------------------------------------------------------//
{
	global $mysqli;
	global $app;
	global $listID;
	
	if($reason=='Malformed') $reason = 1;
	if($reason=='Bounced') $reason = 2;
	if($reason=='Exists') $reason = 3;
	if($reason=='Suppressed') $reason = 4;
	
	$q = 'INSERT INTO skipped_emails (app, list, email, reason) VALUES ('.$app.', '.$listID.', "'.$email.'", '.$reason.')';
	mysqli_query($mysqli, $q);
}

header("Location: ".get_app_info('path').'/subscribers?i='.$app.'&l='.$listID); 

?>
