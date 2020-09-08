<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	//------------------------------------------------------//
	//                      VARIABLES                       //
	//------------------------------------------------------//
	
	$userID = isset($_POST['uid']) && is_numeric($_POST['uid']) ? mysqli_real_escape_string($mysqli, (int)$_POST['uid']) : exit;
	$company = mysqli_real_escape_string($mysqli, $_POST['company']);
	$name = mysqli_real_escape_string($mysqli, $_POST['personal_name']);
	$email = mysqli_real_escape_string($mysqli, $_POST['email']);
	$password = mysqli_real_escape_string($mysqli, $_POST['password']);
	$aws_key = isset($_POST['aws_key']) ? mysqli_real_escape_string($mysqli, trim($_POST['aws_key'])) : '';
	$aws_secret = isset($_POST['aws_secret']) ? mysqli_real_escape_string($mysqli, trim($_POST['aws_secret'])) : '';
	$paypal = isset($_POST['paypal']) ? mysqli_real_escape_string($mysqli, $_POST['paypal']) : '';
	$timezone = mysqli_real_escape_string($mysqli, $_POST['timezone']);
	$language = mysqli_real_escape_string($mysqli, $_POST['language']);
	$ses_endpoint = mysqli_real_escape_string($mysqli, $_POST['ses_endpoint']);
	$send_rate = is_numeric($_POST['send_rate']) ? mysqli_real_escape_string($mysqli, (int)$_POST['send_rate']) : '';
	$ses_send_rate = isset($_POST['ses_send_rate']) ? mysqli_real_escape_string($mysqli, $_POST['ses_send_rate']) : '';
	$brands_rows = is_numeric($_POST['brands_rows']) ? mysqli_real_escape_string($mysqli, (int)$_POST['brands_rows']) : '';
	$strict_delete = is_numeric($_POST['strict_delete']) ? mysqli_real_escape_string($mysqli, (int)$_POST['strict_delete']) : '';
	
	//Validate send rate settings
	if($send_rate!='' && $ses_send_rate!='')
	{
		if($send_rate==0 || $send_rate=='') $send_rate = 'NULL';
		else if($send_rate > $ses_send_rate) $send_rate = $ses_send_rate;
	}
	
	//app data
	$from_name = isset($_POST['from_name']) ? mysqli_real_escape_string($mysqli, $_POST['from_name']) : '';
	$from_email = isset($_POST['from_email']) ? mysqli_real_escape_string($mysqli, $_POST['from_email']) : '';
	$reply_to = isset($_POST['reply_to']) ? mysqli_real_escape_string($mysqli, $_POST['reply_to']) : '';
	$campaign_report_rows = is_numeric($_POST['campaign_report_rows']) ? $_POST['campaign_report_rows'] : 10;
	$query_string = mysqli_real_escape_string($mysqli, $_POST['query_string']);
	$gdpr_only = isset($_POST['gdpr_only']) && $_POST['gdpr_only']==1 ? 1 : 0;
	$gdpr_only_ar = isset($_POST['gdpr_only_ar']) && $_POST['gdpr_only_ar']==1 ? 1 : 0;
	$gdpr_options = isset($_POST['gdpr_options']) && $_POST['gdpr_options']==1 ? 1 : 0;
	$recaptcha_sitekey = mysqli_real_escape_string($mysqli, $_POST['recaptcha_sitekey']);
	$recaptcha_secretkey = mysqli_real_escape_string($mysqli, $_POST['recaptcha_secretkey']);
	$test_email_prefix = mysqli_real_escape_string($mysqli, $_POST['test_email_prefix']);
	
	if($password=='')
		$change_pass = false;
	else
	{
		$change_pass = true;
		$pass_encrypted = hash('sha512', $password.'PectGtma');		
	}
	
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	//Check if email exists in login table
	$q = 'SELECT username FROM login WHERE username = "'.$email.'" AND id != '.get_app_info('userID');
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) > 0)
	{
		echo "email exists";
		exit;
	}
		
	if(!get_app_info('is_sub_user'))
	{
		if($change_pass)
			$q = 'UPDATE login SET company="'.$company.'", name="'.$name.'", username="'.$email.'", password="'.$pass_encrypted.'", s3_key="'.$aws_key.'", s3_secret="'.$aws_secret.'", paypal="'.$paypal.'", timezone = "'.$timezone.'", language = "'.$language.'", ses_endpoint = "'.$ses_endpoint.'", brands_rows = '.$brands_rows.', strict_delete = '.$strict_delete.' WHERE id = '.$userID;
		else
			$q = 'UPDATE login SET company="'.$company.'", name="'.$name.'", username="'.$email.'", s3_key="'.$aws_key.'", s3_secret="'.$aws_secret.'", paypal="'.$paypal.'", timezone = "'.$timezone.'", language = "'.$language.'", ses_endpoint = "'.$ses_endpoint.'", brands_rows = '.$brands_rows.', strict_delete = '.$strict_delete.' WHERE id = '.$userID;
		$r = mysqli_query($mysqli, $q);
		if ($r)
		{
			if($send_rate!='' && $ses_send_rate!='')
			{
				//Update send_rate in database
				mysqli_query($mysqli, 'UPDATE login SET send_rate = '.$send_rate);
			}
			
			echo true; 
		}
	}
	else
	{
		//If userID POSTed here isn't the userID this user logs in with, then exit.
		if($userID != get_app_info('userID')) exit;
		
		if($change_pass)
			$q = 'UPDATE login SET company="'.$company.'", name="'.$name.'", username="'.$email.'", password="'.$pass_encrypted.'", timezone = "'.$timezone.'", language = "'.$language.'" WHERE id = '.$userID;
		else
			$q = 'UPDATE login SET company="'.$company.'", name="'.$name.'", username="'.$email.'", timezone = "'.$timezone.'", language = "'.$language.'" WHERE id = '.$userID;
		$r = mysqli_query($mysqli, $q);
		if ($r)
		{
			if(!get_app_info('campaigns_only'))
				$additional_line1 = ', from_name = "'.$from_name.'", from_email = "'.$from_email.'", reply_to = "'.$reply_to.'", query_string = "'.$query_string.'"'.', test_email_prefix = "'.$test_email_prefix.'"';
			else
				$additional_line1 = '';
			
			if(!get_app_info('campaigns_only') || !get_app_info('reports_only') || !get_app_info('lists_only'))	
				$additional_line2 = ', campaign_report_rows = '.$campaign_report_rows;
			else
				$additional_line2 = '';
			
			if(!get_app_info('lists_only'))		
				$additional_line3 = ', gdpr_only = '.$gdpr_only.', gdpr_only_ar = '.$gdpr_only_ar.', gdpr_options = '.$gdpr_options.', recaptcha_sitekey = "'.$recaptcha_sitekey.'", recaptcha_secretkey = "'.$recaptcha_secretkey.'"';
			else	
				$additional_line3 = '';
			
		    //save sending app data
			$q2 = 'UPDATE apps SET app_name = "'.$company.'" '.$additional_line3.' '.$additional_line1.' '.$additional_line2.' WHERE id = '.get_app_info('restricted_to_app').' AND userID = '.get_app_info('main_userID');
			$r2 = mysqli_query($mysqli, $q2);
			if ($r2)
			{
			    echo true; 
			} 
		}
	} 
?>