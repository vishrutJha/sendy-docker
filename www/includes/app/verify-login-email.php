<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php require_once('../helpers/ses.php'); ?>
<?php require_once('../helpers/EmailAddressValidator.php'); ?>
<?php 
	//------------------------------------------------------//
	//                      VARIABLES                       //
	//------------------------------------------------------//
	//From email validation
	$login_email = isset($_POST['login_email']) ? $_POST['login_email'] : '';
	
	$validator = new EmailAddressValidator;
	if (!$validator->check_email_address($login_email)) 
	{
		echo 'Invalid email';
		exit;
	}

	$ses = new SimpleEmailService(get_app_info('s3_key'), get_app_info('s3_secret'), get_app_info('ses_endpoint'));
	
	if($ses->verifyEmailAddress($login_email))
		echo 'success';
	else
		echo 'failed';
?>