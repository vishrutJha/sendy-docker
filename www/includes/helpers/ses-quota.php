<?php
	if(get_app_info('s3_key')=='' && get_app_info('s3_secret')==''){}
	else
	{
		require_once('includes/helpers/ses.php');
		$ses = new SimpleEmailService(get_app_info('s3_key'), get_app_info('s3_secret'), get_app_info('ses_endpoint'));
		
		//Get success or error codes from API call
		$testAWSCreds = $ses->getSendQuota();
		
		//getSendQuota
		$quoteArray = array();
		foreach($ses->getSendQuota() as $quota){
			array_push($quoteArray, $quota);
		}
		$daily_quota = round($quoteArray[0]);
		$sends_left = $daily_quota - round($quoteArray[2]);
		$sent_today = round($quoteArray[2]);
		$send_rate = round($quoteArray[1]);
		if($daily_quota==0) $quota_color = 'label-important';
		else if($daily_quota==200) $quota_color = '';
		else $quota_color = 'label-success';
		
		//getAccountSendingEnabled
/*
		$getAccountStatus = $ses->getAccountSendingEnabled();
		$account_status_color = $getAccountStatus=='Enabled' ? 'label-success' : 'label-important';
		if($getAccountStatus=='Disabled') $quota_color = 'label-important'; //If sending status is 'Disabled', all $quota_color will be red
*/
	}

	if(get_app_info('s3_key')=='' && get_app_info('s3_secret')==''):
?>
			
<p><strong><?php echo _('Amazon SES is not set up as we can\'t find your AWS credentials in');?> <a href="<?php echo get_app_info('path');?>/settings" style="text-decoration: underline"><?php echo _('settings');?></a>.</strong></p>
<p><strong><?php echo _('If you entered SMTP credentials when you create or edit a brand, emails will be sent via SMTP. Otherwise, emails will be sent via your server (not recommended).');?></strong></p>
<p><a href="https://sendy.co/get-started" target="_blank"><?php echo _('View Get Started guide');?> &rarr;</a></p>

<?php else:?>

<!-- <p><strong><?php echo _('Sending status');?>:</strong> <span class="label <?php echo $account_status_color;?>"><?php echo $getAccountStatus;?></span></p> -->
<p><strong><?php echo _('SES region');?>:</strong> <span class="label <?php echo $quota_color;?>"><?php echo get_app_info('ses_region');?></span></p>
<p><strong><?php echo _('Daily quota');?>:</strong> <span class="label <?php echo $quota_color;?>"><?php echo number_format($daily_quota);?></span></p>
<p><strong><?php echo _('Sends left');?>:</strong> <span class="label <?php echo $quota_color;?>"><?php echo number_format($sends_left);?></span></p>
<p><strong><?php echo _('Sent today');?>:</strong> <span class="label <?php echo $quota_color;?>"><?php echo number_format($sent_today);?></span></p>
<p><strong><?php echo _('Send rate');?>:</strong> <span class="label <?php echo $quota_color;?>"><?php echo number_format($send_rate);?> <?php echo _('per sec');?></span></p>

<?php if($testAWSCreds=='AccessDenied'):?>
<br/>
<span style="color:#BB4D47;"><p><strong><?php echo _('Error');?>: AccessDenied</strong></p><p><?php echo _('Your Sendy installation is unable to get your SES quota from Amazon because you did not attach "AmazonSESFullAccess" user policy to your IAM credentials. Please re-do Step 5.2 and 5.3 of the <a href="https://sendy.co/get-started#step5" target="_blank">Get Started Guide</a> carefully to resolve this error.');?></p></span>

<?php elseif($testAWSCreds=='RequestExpired'):?>
<br/>
<span style="color:#BB4D47;"><p><strong><?php echo _('Error');?>: RequestExpired</strong></p><p><?php echo _('Your Sendy installation is unable to get your SES quota from Amazon because your server clock is out of sync with NTP. To fix this, Amazon requires you to <strong>sync your server clock with NTP</strong>. Request your host to sync your server clock with NTP with the following command via SSH:');?></p><p><code>sudo /usr/sbin/ntpdate 0.north-america.pool.ntp.org 1.north-america.pool.ntp.org 2.north-america.pool.ntp.org 3.north-america.pool.ntp.org</code></p></span>

<?php elseif($testAWSCreds=='InvalidClientTokenId' || $testAWSCreds=='SignatureDoesNotMatch'):?>
<br/>
<span style="color:#BB4D47;"><p><strong><?php echo _('Error');?>: <?php echo $testAWSCreds;?></strong></p><p><?php echo _('Your Sendy installation is unable to get your SES quota from Amazon because the \'Amazon Web Services Credentials\' set in Sendy\'s main Settings are incorrect. You probably did not copy and pasted your IAM credentials fully or properly into the settings. Please re-do Step 5.2 and 5.3 of the <a href="https://sendy.co/get-started#step5" target="_blank">Get Started Guide</a> carefully to resolve this error.');?></p></span>

<?php elseif($testAWSCreds=='OptInRequired'):?>
<br/>
<span style="color:#BB4D47;"><p><strong><?php echo _('Error');?>: OptInRequired</strong></p><p><?php echo _('Your Sendy installation is unable to get your SES quota from Amazon because you have not completed your sign up of Amazon SES. Here\'s what you should do:');?></p><ol><li><?php echo _('Visit');?> <a href="https://console.aws.amazon.com/ses/signup" target="_blank"><?php echo _('your Amazon SES console');?></a></li><li><?php echo _('Click the \'Sign Up For Amazon SES\' button to finish your signup');?></li></ol><p><?php echo _('Once you\'ve completed your signup, this error will disappear.');?></p></span>

<?php elseif($daily_quota=='200'):?>
<br/>
<span style="color:#BB4D47;"><p><?php echo _('You\'re currently in Amazon SES\'s "Sandbox mode".');?></p><p><?php echo _('Please request Amazon to "<a href="http://aws.amazon.com/ses/fullaccessrequest/" target="_blank">raise your SES Sending Limits</a>" to be able to send to and from any email address as well as raise your daily sending quota from 200 to any number you need.');?></p><p><?php echo _('Please also make sure to select the same \'Region\' as what is set in your Sendy Settings (under \'Amazon SES region\') when requesting for \'SES Sending Limits\' increase.');?></p></span>

<?php elseif($daily_quota=='0' && $send_rate=='0' && $sent_today=='0' && get_app_info('s3_key')!='' && get_app_info('s3_key')!=''):?>
<br/>
<span style="color:#BB4D47;"><p><strong><?php echo _('Error');?>: <?php print_r($ses->getSendQuota());?></strong></p></span>

<?php endif;
	endif;
?>