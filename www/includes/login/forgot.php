<?php
	
// If brand user attempts to reset password, the 'From email' that's saved in the brand settings will be used to send the password reset email via Amazon SES using the main user's IAM credentials. 
// Email will be sent to the login email address.
	
//------------------------------------------------------//
//                          INIT                        //
//------------------------------------------------------//

include('../functions.php');
include('../helpers/PHPMailerAutoload.php');
require_once('../helpers/ses.php');
require_once('../helpers/EmailAddressValidator.php');
require_once('../helpers/short.php');

$email = mysqli_real_escape_string($mysqli, $_POST['email']);
$email_domain_array = explode('@', $email);
$email_domain = $email_domain_array[1];
$app_path = get_app_info('path');

//------------------------------------------------------//
//                         EVENTS                       //
//------------------------------------------------------//

//Get 'main user' login email address
$r = mysqli_query($mysqli, 'SELECT id, username, s3_key, s3_secret, ses_endpoint, api_key FROM login ORDER BY id ASC LIMIT 1');
if ($r) 
{
	while($row = mysqli_fetch_array($r)) 
	{
		$main_user_id = $row['id'];
		$main_user_email_address = $row['username'];
		$aws_key = stripslashes($row['s3_key']);
		$aws_secret = stripslashes($row['s3_secret']);
		$ses_endpoint = stripslashes($row['ses_endpoint']);
		$api_key = stripslashes($row['api_key']);
	}
}

$q = 'SELECT id, company, app FROM login WHERE username = "'.$email.'" LIMIT 1';
$r = mysqli_query($mysqli, $q);
if ($r && mysqli_num_rows($r) > 0)
{
	while($row = mysqli_fetch_array($r))
    {
    	$uid = $row['id'];
		$company = stripslashes($row['company']);
		$app = stripslashes($row['app']);
    } 
    
    $rpk = ran_string(20, 20, true, false, true);    
    $q2 = 'UPDATE login SET reset_password_key = "'.$rpk.'" WHERE id = '.$main_user_id;
    $r2 = mysqli_query($mysqli, $q2);
    if ($r2)
    {
	    $password_reset_link = $app_path.'/includes/login/reset.php?d='.short('{"rpk":"'.$rpk.'", "id":"'.$uid.'"}');    
    }
    
    $q2 = 'SELECT from_email FROM apps WHERE id = '.$app;
    $r2 = mysqli_query($mysqli, $q2);
    if ($r2) while($row = mysqli_fetch_array($r2)) $from_email = $row['from_email'];
    $from_email = $from_email=='' ? $email : $from_email;
    
    //send a password reset confirmation email 
	$plain_text = 'We have received a request to reset your password. If you did not send this request, please ignore this email.

To reset your password, please click the following link:

Password reset link: '.$password_reset_link;

    $message = "<div style=\"margin: -10px -10px; padding:50px 30px 50px 30px; height:100%;\">
	<div style=\"margin:0 auto; max-width:660px;\">
		<div style=\"float: left; background-color: #FFFFFF; padding:10px 30px 10px 30px; border: 1px solid #f6f6f6;\">
			<div style=\"float: left; max-width: 106px; margin: 10px 20px 15px 0;\">
				<img src=\"$app_path/img/key.gif\" style=\"width: 50px;\"/>
			</div>
			<div style=\"float: left; max-width:470px;\">
				<p style=\"line-height: 21px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 12px;\">
					<strong style=\"line-height: 21px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 18px;\">"._('Reset your password')."</strong>
				</p>	
				<div style=\"line-height: 21px; min-height: 100px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 12px;\">
					<p style=\"line-height: 21px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 12px;\">"._('We have received a request to reset your password. If you did not send this request, please ignore this email.')."</p>
					<p style=\"line-height: 21px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 12px;\">"._('To reset your password, please click the following link:')."</p>
					<p style=\"line-height: 21px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 12px; margin-bottom: 25px; background-color:#f7f9fc; padding: 15px;\">
						<strong>"._('Password reset link').": </strong><a style=\"color:#4371AB; text-decoration:none;\" href=\"$password_reset_link\">$password_reset_link</a>
					</p>
					<p style=\"line-height: 21px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 12px;\">
					</p>
				</div>
			</div>
		</div>
	</div>
</div>";
    
    //send email to me
	$mail = new PHPMailer();
	if($aws_key!='' && $aws_secret!='')
	{
		//Initialize ses class
		$ses = new SimpleEmailService($aws_key, $aws_secret, $ses_endpoint);
		
		//Check if user's AWS keys are valid
		$testAWSCreds = $ses->getSendQuota();
		if($testAWSCreds)
		{			
			//Check if login email is verified in Amazon SES console
			$v_addresses = $ses->ListIdentities();
			$verifiedEmailsArray = array();
			$verifiedDomainsArray = array();
			foreach($v_addresses['Addresses'] as $val){
				$validator = new EmailAddressValidator;
				if ($validator->check_email_address($val)) array_push($verifiedEmailsArray, $val);
				else array_push($verifiedDomainsArray, $val);
			}
			$veriStatus = true;
			$getIdentityVerificationAttributes = $ses->getIdentityVerificationAttributes($email);
			foreach($getIdentityVerificationAttributes['VerificationStatus'] as $getIdentityVerificationAttribute) 
				if($getIdentityVerificationAttribute=='Pending') $veriStatus = false;
			
			//If login email address is in Amazon SES console,
			if(in_array($email, $verifiedEmailsArray) || in_array($email_domain, $verifiedDomainsArray))
			{
				//and the email address is 'Verified'
				if($veriStatus)
				{
					//Send password reset email via Amazon SES
					$mail->IsAmazonSES();
					$mail->AddAmazonSESKey($aws_key, $aws_secret);
				}
			}
		}
	}
	$mail->CharSet	  =	"UTF-8";
	$mail->From       = $from_email;
	$mail->FromName   = $company;
	$mail->Subject = '['.$company.'] '._('Reset your password');
	$mail->AltBody = $plain_text;
	$mail->Body = $message;
	$mail->IsHTML(true);
	$mail->AddAddress($email, $company);
	$mail->Send();
    
    echo $email == $main_user_email_address ? 'main_user' : true;
    exit;
}
else
{
	echo _('Email does not exist.');
	exit;
}
?>