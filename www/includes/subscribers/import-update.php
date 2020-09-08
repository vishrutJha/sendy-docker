<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php require_once('../helpers/EmailAddressValidator.php');?>
<?php require_once('../helpers/parsecsv.php');?>
<?php

/********************************/
//Validate imported CSV file
$csvfile = $_FILES['csv_file']['tmp_name'];
$csvfilename = $_FILES['csv_file']['name'];
$csvfilename_array = explode('.', $csvfilename);
$csvfile_ext1 = $csvfilename_array[count($csvfilename_array)-2];
$csvfile_ext2 = $csvfilename_array[count($csvfilename_array)-1];
if($csvfile_ext1=='php' || $csvfile_ext2!='csv' || $csvfilename=='.htaccess') exit;

$csv = new parseCSV();
$csv->heading = false;
$csv->auto($csvfile);
$databasetable = "subscribers";
$fieldseparator = ",";
$lineseparator = "\n";
$userID = get_app_info('main_userID');
$app = isset($_POST['app']) && is_numeric($_POST['app']) ? mysqli_real_escape_string($mysqli, (int)$_POST['app']) : exit;
$listID = isset($_POST['list_id']) && is_numeric($_POST['list_id']) ? mysqli_real_escape_string($mysqli, (int)$_POST['list_id']) : exit;
$gdpr_tag = isset($_POST['gdpr_tag']) ? 1 : 0;
$cron = $_POST['cron'];
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

if(!file_exists($csvfile)) {
	header("Location: ".get_app_info('path').'/update-list?i='.$app.'&l='.$listID.'&e=3'); 
	exit;
}

foreach ($csv->data as $key => $line)
{	
	//Get the columns	
	$linearray = array();
	if(count($csv->data)==1)
	{
		$file = fopen($csvfile,"r");
		$size = filesize($csvfile);
		$csvcontent = fread($file,$size);
		fclose($file);
		$linearray = explode($fieldseparator,$csvcontent);
		$columns = count($linearray);
		$columns_additional = $columns - 2;
	}
	else
	{
		foreach($line as $val) array_push($linearray, $val);
		$columns = count($linearray);
		$columns_additional = $columns - 2;
	}
	
	//If cron is setup, just upload CSV and let cron take care of CSV import
	if($cron)
	{
		//Create /csvs/ directory in /uploads/ if it doesn't exist
		if(!file_exists("../../uploads/csvs")) 
		{
			//Create /csvs/ directory
			if(!mkdir("../../uploads/csvs", 0777))
			{
				header("Location: ".get_app_info('path').'/update-list?i='.$app.'&l='.$listID.'&e=4'); 
				exit;
			}
			else
			{
				//chmod uploaded file
				chmod("../../uploads/csvs",0777);
			}
		}
		
		//Check if column count matches the list
		$q2 = 'SELECT custom_fields FROM lists WHERE id = '.$listID;
		$r2 = mysqli_query($mysqli, $q2);
		if ($r2)
		{
			$custom_fields_count = 0;
			
		    while($row = mysqli_fetch_array($r2)) $custom_fields = $row['custom_fields'];
		    
		    //if there are custom fields in this list,
		    if($custom_fields!='')
		    {
		    	$custom_fields_value = '';
			    $custom_fields_array = explode('%s%', $custom_fields);
			    $custom_fields_count = count($custom_fields_array);
		    }
		}
		
		if($columns != $custom_fields_count+2)
		{
			header("Location: ".get_app_info('path').'/update-list?i='.$app.'&l='.$listID.'&e=1'); 
			exit;
		}
		else
		{
			//Move CSV file to /uploads/csvs/ directory
			if(move_uploaded_file($csvfile, "../../uploads/csvs/".$userID.'-'.$listID.'.csv'))
			{
				//chmod uploaded file
				chmod("../../uploads/csvs/".$userID.'-'.$listID.'.csv',0777);
				
				//Update total_records
				$linecount = count(file("../../uploads/csvs/".$userID.'-'.$listID.'.csv'));
				$q = 'UPDATE lists SET total_records = '.$linecount.', gdpr = '.$gdpr_tag.' WHERE id = '.$listID;
				mysqli_query($mysqli, $q);
				
				//CSV uploaded successfully, redirect back to list
				header("Location: ".get_app_info('path').'/list?i='.$app); 
			}
			//If upload fail, throw an error with informational message
			else
			{
				header("Location: ".get_app_info('path').'/update-list?i='.$app.'&l='.$listID.'&e=4'); 
				exit;
			}
		}
		
		exit;
	}
	
	//If cron is NOT setup, import immediately. Start by checking for duplicates
	$q = 'SELECT custom_fields FROM subscribers WHERE list = '.$listID.' AND (email = "'.$linearray[0].'" || email = "'.trim($linearray[1]).'") AND userID = '.$userID;
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) > 0) //if so, update subscriber
	{
		while($row = mysqli_fetch_array($r))
	    {
	    	$custom_values = $row['custom_fields'];
	    } 
		
		//Get the list of custom fields for this list
		$q2 = 'SELECT custom_fields FROM lists WHERE id = '.$listID;
		$r2 = mysqli_query($mysqli, $q2);
		if ($r2)
		{
			$custom_fields = '';
			
		    while($row = mysqli_fetch_array($r2))
		    {
				$custom_fields = $row['custom_fields'];
		    }  
		    
		    //if there are custom fields in this list,
		    if($custom_fields!='')
		    {
		    	$custom_fields_value = '';
			    $custom_fields_array = explode('%s%', $custom_fields);
			    $custom_fields_count = count($custom_fields_array);
			    
			    $custom_values_array = explode('%s%', $custom_values);
			    
			    //prepare custom field string
			    for($i=2;$i<$columns_additional+2;$i++)
			    {
			    	$custom_fields_array2 = explode(':', $custom_fields_array[$i-2]);
			    	//if custom field format is Date
					if($custom_fields_array2[1]=='Date')
					{
						if($linearray[$i]=="") $value = $linearray[$i];
						else
						{
							$date_value1 = strtotime($linearray[$i]);
							$date_value2 = strftime("%b %d, %Y 12am", $date_value1);
							$value = strtotime($date_value2);
						}
						$custom_fields_value .= $linearray[$i]=='' ? $custom_values_array[$i-2] : $value;
					    $custom_fields_value .= '%s%';
					}
					//else if custom field format is Text
					else
					{
					    $custom_fields_value .= $linearray[$i]=='' ? $custom_values_array[$i-2] : strip_tags($linearray[$i]);
					    $custom_fields_value .= '%s%';
					}
			    }
		    }
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
	    
		if(!isset($name) || $name=='')
			$q = 'UPDATE subscribers SET custom_fields = "'.substr($custom_fields_value, 0, -3).'" '.$gdpr_status.' WHERE email = "'.$email.'" AND list = '.$listID;
		else
			$q = 'UPDATE subscribers SET name = "'.$name.'", custom_fields = "'.substr($custom_fields_value, 0, -3).'" '.$gdpr_status.' WHERE email = "'.$email.'" AND list = '.$listID;

		mysqli_query($mysqli, $q);
		
		skipped_emails(trim($linearray[1]), 'Exists');
	}
	else
	{			
		//Get the list of custom fields for this list
		$q2 = 'SELECT custom_fields FROM lists WHERE id = '.$listID;
		$r2 = mysqli_query($mysqli, $q2);
		if ($r2)
		{
		    while($row = mysqli_fetch_array($r2))
		    {
				$custom_fields = $row['custom_fields'];
		    }  
		    
		    //if there are custom fields in this list,
		    if($custom_fields!='')
		    {
		    	$custom_fields_value = '';
			    $custom_fields_array = explode('%s%', $custom_fields);
			    $custom_fields_count = count($custom_fields_array);
			    
			    //prepare custom field string
			    for($i=2;$i<$columns_additional+2;$i++)
			    {
			    	$custom_fields_array2 = explode(':', $custom_fields_array[$i-2]);
			    	//if custom field format is Date
					if($custom_fields_array2[1]=='Date')
					{
						if($linearray[$i]=="") $value = $linearray[$i];
						else
						{
							$date_value1 = strtotime($linearray[$i]);
							$date_value2 = strftime("%b %d, %Y 12am", $date_value1);
							$value = strtotime($date_value2);
						}
						$custom_fields_value .= $value;
					    $custom_fields_value .= '%s%';
					}
					//else if custom field format is Text
					else
					{
					    $custom_fields_value .= strip_tags($linearray[$i]);
					    $custom_fields_value .= '%s%';
					}
			    }
		    }
		}
		
		//Check if user set the list to unsubscribe from all lists
		$q = 'SELECT unsubscribe_all_list FROM lists WHERE id = '.$listID;
		$r = mysqli_query($mysqli, $q);
		if ($r) while($row = mysqli_fetch_array($r)) $unsubscribe_all_list = $row['unsubscribe_all_list'];
		
		//See if we should check for unsubscribe status in all lists
		$unsubscribe_line = $unsubscribe_all_list ? '(complaint = 1 OR unsubscribed = 1)' : 'complaint = 1';
		
		//get email's domain
		$email_explode = explode('@', trim($linearray[0]));
		$email_explode2 = explode('@', trim($linearray[1]));
		$email_domain = $email_explode[1];
		$email_domain2 = $email_explode2[1];
		
		//Check if this email is previously marked as bounced, if so, we shouldn't add it
		$q = 'SELECT email from subscribers WHERE ( (email = "'.$linearray[0].'" || email = " '.$linearray[1].'" || email = "'.$linearray[1].'") AND bounced = 1 ) OR ( (email = "'.$linearray[0].'" || email = " '.$linearray[1].'" || email = "'.$linearray[1].'") AND list IN ('.$all_lists.') AND '.$unsubscribe_line.' )';
		$r = mysqli_query($mysqli, $q);
		if (mysqli_num_rows($r) == 0)
		{			
			$q2 = '(SELECT id FROM suppression_list WHERE (email = "'.$linearray[0].'" || email = " '.$linearray[1].'" || email = "'.$linearray[1].'") AND app = '.$app.') 
					UNION 
					(SELECT id FROM blocked_domains WHERE (domain = "'.$email_domain.'" || domain = "'.$email_domain2.'") AND app = '.$app.')';
			
			$r2 = mysqli_query($mysqli, $q2);
			if (mysqli_num_rows($r2) == 0)
			{			
				$validator = new EmailAddressValidator;
				
				//if CSV has only 1 column, insert into email column
				if($columns==1)
				{
					if ($validator->check_email_address(trim($linearray[0]))) 
					{
						//insert email into database
						$query = 'INSERT INTO '.$databasetable.' (userID, email, list, timestamp, gdpr) values('.$userID.', "'.trim($linearray[0]).'", '.$listID.', '.$time.', '.$gdpr_tag.')';
						mysqli_query($mysqli, $query);
						$inserted_id = mysqli_insert_id($mysqli);
					}
					else skipped_emails(trim($linearray[0]), 'Malformed');
				}
				//if CSV has 2 columns, insert into name and email columns
				else if($columns==2)
				{
					if ($validator->check_email_address(trim($linearray[1]))) 
					{
						//insert name & email into database
						$query = 'INSERT INTO '.$databasetable.' (userID, name, email, list, timestamp, gdpr) values('.$userID.', "'.strip_tags($linearray[0]).'", "'.trim($linearray[1]).'", '.$listID.', '.$time.', '.$gdpr_tag.')';
						mysqli_query($mysqli, $query);
						$inserted_id = mysqli_insert_id($mysqli);
					}
					else skipped_emails(trim($linearray[1]), 'Malformed');
				}
				//if number of CSV columns matches database, insert name, email and all custom fields
				else if($columns==$custom_fields_count+2)
				{
					if ($validator->check_email_address(trim($linearray[1]))) 
					{
						//insert name & email into database
						$query = 'INSERT INTO '.$databasetable.' (userID, name, email, list, timestamp, gdpr) values('.$userID.', "'.strip_tags($linearray[0]).'", "'.trim($linearray[1]).'", '.$listID.', '.$time.', '.$gdpr_tag.')';
						mysqli_query($mysqli, $query);
						$inserted_id = mysqli_insert_id($mysqli);
						
						//update custom fields values
					    $q3 = 'UPDATE '.$databasetable.' SET custom_fields = "'.substr($custom_fields_value, 0, -3).'" WHERE id = '.$inserted_id;
					    $r3 = mysqli_query($mysqli, $q3);
					    if ($r3){}
					}
					else skipped_emails(trim($linearray[1]), 'Malformed');
				}
				else
				{
					header("Location: ".get_app_info('path').'/update-list?i='.$app.'&l='.$listID.'&e=1'); 
					exit;
				}
			}
			else
			{				
				//Update block_attempts count				
				$q3 = 'UPDATE suppression_list SET block_attempts = block_attempts+1, timestamp = "'.$time.'" WHERE (email = "'.$linearray[0].'" || email = " '.$linearray[1].'" || email = "'.$linearray[1].'") AND app = '.$app;
				$q4 = 'UPDATE blocked_domains SET block_attempts = block_attempts+1, timestamp = "'.$time.'" WHERE (domain = "'.$email_domain.'" || domain = "'.$email_domain2.'") AND app = '.$app;
				mysqli_query($mysqli, $q3);
				mysqli_query($mysqli, $q4);
				
				skipped_emails(trim($linearray[1]), 'Suppressed');
			}
		}
		else
		{
			skipped_emails(trim($linearray[1]), 'Bounced');
		}
	}
}

//Once everything is imported, reset count and remove CSV
//set currently_processing to 0
$q = 'UPDATE lists SET currently_processing=0, prev_count=0, total_records=0 WHERE id = '.$listID;
mysqli_query($mysqli, $q);
//delete CSV file
unlink($csvfile);

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

//return
header("Location: ".get_app_info('path').'/subscribers?i='.$app.'&l='.$listID);
?>