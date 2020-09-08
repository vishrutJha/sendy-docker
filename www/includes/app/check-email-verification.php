<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	require_once('../helpers/EmailAddressValidator.php');
?>
<?php 
	//------------------------------------------------------//
	//                      VARIABLES                       //
	//------------------------------------------------------//
	
	//From email validation
	$from_email = isset($_POST['from_email']) ? $_POST['from_email'] : '';
	$auto_verify = $_POST['auto_verify']=='no' ? false : true;
	
	$validator = new EmailAddressValidator;
	if (!$validator->check_email_address($from_email)) 
	{
		echo 'Invalid email';
		exit;
	}
		
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	//Check if from email is verified in SES console
	if(!get_app_info('is_sub_user') && get_app_info('s3_key')!='' && get_app_info('s3_secret')!='')
	{
		if(verify_identity($from_email)=='unverified')
		{
			//Attempt to verify the email address, a verification email will be sent to the 'From email' address by Amazon SES
			if($auto_verify)
				$ses->verifyEmailAddress($from_email);
			
			echo 'unverified'; //From email address or domain IS NOT verified in SES console
		}
		else if(verify_identity($from_email)=='pending')
			echo 'pending verification'; //From email address or domain is 'pending verification' in SES console
		else if(verify_identity($from_email)=='verified')
			echo 'verified'; //From email address or domain IS verified in SES console
	}
?>