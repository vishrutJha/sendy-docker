<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php include('../helpers/PHPMailerAutoload.php');?>
<?php include('../helpers/short.php');?>
<?php

//POST variables
$display_errors = isset($_POST['display_errors']) && is_numeric($_POST['display_errors']) ? mysqli_real_escape_string($mysqli, (int)$_POST['display_errors']) : 0;
$campaign_id = isset($_POST['campaign_id']) && is_numeric($_POST['campaign_id']) ? mysqli_real_escape_string($mysqli, (int)$_POST['campaign_id']) : exit;
$test_email = mysqli_real_escape_string($mysqli, $_POST['test_email']);
$test_email = str_replace(" ", "", $test_email);
$test_email_array = explode(',', $test_email);

//select campaign to send test email
$q = 'SELECT * FROM campaigns WHERE id = '.$campaign_id.' AND userID = '.get_app_info('main_userID');
$r = mysqli_query($mysqli, $q);
if ($r && mysqli_num_rows($r) > 0)
{
    while($row = mysqli_fetch_array($r))
    {
    	$from_name = stripslashes($row['from_name']);
    	$from_email = stripslashes($row['from_email']);
    	$reply_to = stripslashes($row['reply_to']);
		$title = stripslashes($row['title']);
		$plain_text = stripslashes($row['plain_text']);
		$html_text = stripslashes($row['html_text']);
    }  
    
    //get smtp settings
	$q3 = 'SELECT apps.id, apps.smtp_host, apps.smtp_port, apps.smtp_ssl, apps.smtp_username, apps.smtp_password, apps.allocated_quota, apps.custom_domain, apps.custom_domain_protocol, apps.custom_domain_enabled FROM campaigns, apps WHERE apps.id = campaigns.app AND campaigns.id = '.$campaign_id;
	$r3 = mysqli_query($mysqli, $q3);
	if ($r3 && mysqli_num_rows($r3) > 0)
	{
	    while($row = mysqli_fetch_array($r3))
	    {
	    	$app = $row['id'];
			$smtp_host = $row['smtp_host'];
			$smtp_port = $row['smtp_port'];
			$smtp_ssl = $row['smtp_ssl'];
			$smtp_username = $row['smtp_username'];
			$smtp_password = $row['smtp_password'];
			$allocated_quota = $row['allocated_quota'];
			$custom_domain = $row['custom_domain'];
			$custom_domain_protocol = $row['custom_domain_protocol'];
			$custom_domain_enabled = $row['custom_domain_enabled'];
			if($custom_domain!='' && $custom_domain_enabled)
			{
				$parse = parse_url(get_app_info('path'));
				$domain = $parse['host'];
				$protocol = $parse['scheme'];
				$app_path = str_replace($domain, $custom_domain, get_app_info('path'));
				$app_path = str_replace($protocol, $custom_domain_protocol, $app_path);
			}
			else $app_path = get_app_info('path');
	    }  
	}
	
	$webversion = $app_path.'/w/'.short($campaign_id);
	
	//Get `test_email_prefix` value
	$q4 = 'SELECT test_email_prefix FROM apps WHERE id = '.$app;
	$r4 = mysqli_query($mysqli, $q4);
	if ($r4 && mysqli_num_rows($r4) > 0) while($row = mysqli_fetch_array($r4)) $test_email_prefix = $row['test_email_prefix'];
    
    //tags for subject
	preg_match_all('/\[([a-zA-Z0-9!#%^&*()+=$@._\-\:|\/?<>~`"\'\s]+),\s*fallback=/i', $title, $matches_var, PREG_PATTERN_ORDER);
	preg_match_all('/,\s*fallback=([a-zA-Z0-9!,#%^&*()+=$@._\-\:|\/?<>~`"\'\s]*)\]/i', $title, $matches_val, PREG_PATTERN_ORDER);
	preg_match_all('/(\[[a-zA-Z0-9!#%^&*()+=$@._\-\:|\/?<>~`"\'\s]+,\s*fallback=[a-zA-Z0-9!,#%^&*()+=$@._\-\:|\/?<>~`"\'\s]*\])/i', $title, $matches_all, PREG_PATTERN_ORDER);
	preg_match_all('/\[([^\]]+),\s*fallback=/i', $title, $matches_var, PREG_PATTERN_ORDER);
	preg_match_all('/,\s*fallback=([^\]]*)\]/i', $title, $matches_val, PREG_PATTERN_ORDER);
	preg_match_all('/(\[[^\]]+,\s*fallback=[^\]]*\])/i', $title, $matches_all, PREG_PATTERN_ORDER);
	$matches_var = $matches_var[1];
	$matches_val = $matches_val[1];
	$matches_all = $matches_all[1];
	for($i=0;$i<count($matches_var);$i++)
	{		
		$field = $matches_var[$i];
		$fallback = $matches_val[$i];
		$tag = $matches_all[$i];
		//for each match, replace tag with fallback
		$title = str_replace($tag, $fallback, $title);
	}
    
    //tags for HTML
	preg_match_all('/\[([a-zA-Z0-9!#%^&*()+=$@._\-\:|\/?<>~`"\'\s]+),\s*fallback=/i', $html_text, $matches_var, PREG_PATTERN_ORDER);
	preg_match_all('/,\s*fallback=([a-zA-Z0-9!,#%^&*()+=$@._\-\:|\/?<>~`"\'\s]*)\]/i', $html_text, $matches_val, PREG_PATTERN_ORDER);
	preg_match_all('/(\[[a-zA-Z0-9!#%^&*()+=$@._\-\:|\/?<>~`"\'\s]+,\s*fallback=[a-zA-Z0-9!,#%^&*()+=$@._\-\:|\/?<>~`"\'\s]*\])/i', $html_text, $matches_all, PREG_PATTERN_ORDER);
	preg_match_all('/\[([^\]]+),\s*fallback=/i', $html_text, $matches_var, PREG_PATTERN_ORDER);
	preg_match_all('/,\s*fallback=([^\]]*)\]/i', $html_text, $matches_val, PREG_PATTERN_ORDER);
	preg_match_all('/(\[[^\]]+,\s*fallback=[^\]]*\])/i', $html_text, $matches_all, PREG_PATTERN_ORDER);
	$matches_var = $matches_var[1];
	$matches_val = $matches_val[1];
	$matches_all = $matches_all[1];
	for($i=0;$i<count($matches_var);$i++)
	{   
		$field = $matches_var[$i];
		$fallback = $matches_val[$i];
		$tag = $matches_all[$i];
		//for each match, replace tag with fallback
		$html_text = str_replace($tag, $fallback, $html_text);
	}
	//tags for Plain text
	preg_match_all('/\[([a-zA-Z0-9!#%^&*()+=$@._\-\:|\/?<>~`"\'\s]+),\s*fallback=/i', $plain_text, $matches_var, PREG_PATTERN_ORDER);
	preg_match_all('/,\s*fallback=([a-zA-Z0-9!,#%^&*()+=$@._\-\:|\/?<>~`"\'\s]*)\]/i', $plain_text, $matches_val, PREG_PATTERN_ORDER);
	preg_match_all('/(\[[a-zA-Z0-9!#%^&*()+=$@._\-\:|\/?<>~`"\'\s]+,\s*fallback=[a-zA-Z0-9!,#%^&*()+=$@._\-\:|\/?<>~`"\'\s]*\])/i', $plain_text, $matches_all, PREG_PATTERN_ORDER);
	preg_match_all('/\[([^\]]+),\s*fallback=/i', $plain_text, $matches_var, PREG_PATTERN_ORDER);
	preg_match_all('/,\s*fallback=([^\]]*)\]/i', $plain_text, $matches_val, PREG_PATTERN_ORDER);
	preg_match_all('/(\[[^\]]+,\s*fallback=[^\]]*\])/i', $plain_text, $matches_all, PREG_PATTERN_ORDER);
	$matches_var = $matches_var[1];
	$matches_val = $matches_val[1];
	$matches_all = $matches_all[1];
	for($i=0;$i<count($matches_var);$i++)
	{   
		$field = $matches_var[$i];
		$fallback = $matches_val[$i];
		$tag = $matches_all[$i];
		//for each match, replace tag with fallback
		$plain_text = str_replace($tag, $fallback, $plain_text);
	}
    
    //set web version links
	$html_text = str_replace('<webversion', '<a href="'.$webversion.'" ', $html_text);
	$html_text = str_replace('</webversion>', '</a>', $html_text);
	$html_text = str_replace('[webversion]', $webversion, $html_text);
	$plain_text = str_replace('[webversion]', $webversion, $plain_text);
	
	//set unsubscribe links
	$html_text = str_replace('<unsubscribe', '<a href="'.$app_path.'/unsubscribe-success.php?c='.$campaign_id.'" ', $html_text);
	$html_text = str_replace('</unsubscribe>', '</a>', $html_text);
	$html_text = str_replace('[unsubscribe]', $app_path.'/unsubscribe-success.php?c='.$campaign_id, $html_text);
	$plain_text = str_replace('[unsubscribe]', $app_path.'/unsubscribe-success.php?c='.$campaign_id, $plain_text);
	
	//set reconsent links
	$html_text = str_replace('[reconsent]', $app_path.'/reconsent-success?c='.$campaign_id, $html_text);
	$plain_text = str_replace('[reconsent]', $app_path.'/reconsent-success.php?c='.$campaign_id, $plain_text);
	
	//convert date tags
	if(get_app_info('timezone')!='') date_default_timezone_set(get_app_info('timezone'));
	$today = time();
	$currentdaynumber = strftime('%d', $today);
	$currentday = strftime('%A', $today);
	$currentmonthnumber = strftime('%m', $today);
	$currentmonth = strftime('%B', $today);
	$currentyear = strftime('%Y', $today);
	$unconverted_date = array('[currentdaynumber]', '[currentday]', '[currentmonthnumber]', '[currentmonth]', '[currentyear]');
	$converted_date = array($currentdaynumber, $currentday, $currentmonthnumber, $currentmonth, $currentyear);
	$html_text = str_replace($unconverted_date, $converted_date, $html_text);
	$plain_text = str_replace($unconverted_date, $converted_date, $plain_text);
	$title = str_replace($unconverted_date, $converted_date, $title);
}

for($i=0;$i<count($test_email_array);$i++)
{
	//Name tag
	$html_text = str_replace('[Name]', '', $html_text);
	$plain_text = str_replace('[Name]', '', $plain_text);
	$title = str_replace('[Name]', '', $title);
	
	//Email tag
	$html_text2 = str_replace('[Email]', $test_email_array[$i], $html_text);
	$plain_text2 = str_replace('[Email]', $test_email_array[$i], $plain_text);
	$title2 = str_replace('[Email]', $test_email_array[$i], $title);
	$title2 = $test_email_prefix!='' ? $test_email_prefix.' '.$title2 : $title2;
	
	//send test email
	$mail = new PHPMailer();
	if(get_app_info('s3_key')!='' && get_app_info('s3_secret')!='')
	{
		$mail->IsAmazonSES();
		$mail->AddAmazonSESKey(get_app_info('s3_key'), get_app_info('s3_secret'));
	}
	else if($smtp_host!='' && $smtp_port!='' && $smtp_username!='' && $smtp_password!='')
	{
		$mail->IsSMTP();
		$mail->SMTPDebug = $display_errors ? 3 : 0;
		$mail->SMTPAuth = true;
		$mail->SMTPSecure = $smtp_ssl;
		$mail->Host = $smtp_host;
		$mail->Port = $smtp_port; 
		$mail->Username = $smtp_username;  
		$mail->Password = $smtp_password;
	}
	$mail->Timezone   = get_app_info('timezone');
	$mail->CharSet	  =	"UTF-8";
	$mail->From       = $from_email;
	$mail->FromName   = $from_name;
	$mail->Subject = $title2;
	$mail->AltBody = $plain_text2;
	$mail->Body = $html_text2;
	$mail->IsHTML(true);
	$mail->AddAddress($test_email_array[$i], '');
	$mail->AddReplyTo($reply_to, $from_name);
	$mail->AddCustomHeader('List-Unsubscribe: <'.$app_path.'/unsubscribe-success.php?c='.$campaign_id.'>');
	if(file_exists('../../uploads/attachments/'.$campaign_id))
	{
		foreach(glob('../../uploads/attachments/'.$campaign_id.'/*') as $attachment)
		{
			$attachment = filter_var($attachment,FILTER_SANITIZE_SPECIAL_CHARS);
			$attachment_filename = basename($attachment);
			$attachment = '../../uploads/attachments/'.$campaign_id.'/'.$attachment_filename;
			
			if(file_exists($attachment))
			    $mail->AddAttachment($attachment);
		}
	}
	$send_test_email = $mail->Send();
	
	//Check if message is rejected by Amazon SES
	if($send_test_email['code']=='400')
	{
		echo html_entity_decode($send_test_email['full_error']);
	}
	else
	{
		echo 'ok,';
	}
	
	//Update quota if a monthly limit was set
	if($allocated_quota!=-1)
	{
		//if so, update quota
		$q4 = 'UPDATE apps SET current_quota = current_quota+1 WHERE id = '.$app;
		mysqli_query($mysqli, $q4);
	}
	
	//Save last used test email
	mysqli_query($mysqli, 'UPDATE apps SET test_email = "'.$test_email.'" WHERE id = '.$app);
}

?>