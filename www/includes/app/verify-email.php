<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php require_once('../helpers/ses.php'); ?>
<?php require_once('../helpers/EmailAddressValidator.php'); ?>
<?php 
	//------------------------------------------------------//
	//                      VARIABLES                       //
	//------------------------------------------------------//
	//From email validation
	$from_email = isset($_POST['from_email']) ? $_POST['from_email'] : '';
	$auto_verify = $_POST['auto_verify']=='no' ? false : true;
	$ses = new SimpleEmailService(get_app_info('s3_key'), get_app_info('s3_secret'), get_app_info('ses_endpoint'));
	
	//Check SES daily sending quota
	$quoteArray = array();
	foreach($ses->getSendQuota() as $quota){
		array_push($quoteArray, $quota);
	}
	$daily_quota = round($quoteArray[0]);
	
	$validator = new EmailAddressValidator;
	if (!$validator->check_email_address($from_email)) 
	{
		echo 'Invalid email';
		exit;
	}

	//If main admin user login email address is not verified in Amazon SES console, send the generic verification email from Amazon
	if(verify_identity(get_app_info('email')) != 'verified')
	{
		//Verify email address		
		$ses->verifyEmailAddress($from_email);
		$ses->verifyEmailAddress(get_app_info('email'));
		echo 'success2';
	}
	//Otherwise, create a custom verification template and send that instead
	else
	{
		//If still in sandbox mode
		if($daily_quota==200)
		{
			$ses->verifyEmailAddress($from_email);
			echo 'success';
		}
		else
		{
			$content_title = _('Please verify your email address');
			$content_body = _('Before you can use the email address as your \'From email\' to send emails, please click the following link to verify that the email address belongs to you');
			
			//Create custom verification email template
			$ses->deleteCustomVerificationEmailTemplate('SendyVerificationTemplate');
			$ses->createCustomVerificationEmailTemplate(get_app_info('email'), get_app_info('path'), $content_title, $content_body);
			
			//Send custom verification email
			if($ses->sendCustomVerificationEmail($from_email))
				echo 'success';
			else
				echo 'failed';
		}
	}
		
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
?>