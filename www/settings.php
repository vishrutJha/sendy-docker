<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/settings/main.php');?>
<?php include('includes/create/timezone.php');?>
<?php check_simplexml();?>
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/settings/main.js?10"></script>
<script type="text/javascript">
	$(document).ready(function() {
		//accordion
		$("#collapse0").on('shown', function(){$("#aws_key").focus();});
		$("#collapse2").on('shown', function(){$("#send_rate").focus();});
		$("#collapse3").on('shown', function(){$("#paypal").focus();});
		$("#collapse4").on('shown', function(){$("#brands_rows").focus();});
	});
</script>
<div class="row-fluid">
	<div class="span2">
		
		<?php if(get_app_info('is_sub_user')):?>
		
		<?php include('includes/sidebar.php');?>
		
		<?php else:?>
		
		<div class="sidebar-nav sidebar-box" style="padding: 19px;">
			<h3><?php echo _('Amazon SES Quota');?></h3><br/>
			<?php include('includes/helpers/ses-quota.php');?>
		</div>
		
		<?php endif;?>
		
	</div>
    <div class="span5">
    	<h2><?php echo _('Settings');?></h2><br/>
    	
    	<div class="alert alert-success" style="display:none;">
		  <button class="close" onclick="$('.alert-success').hide();">×</button>
		  <strong><?php echo _('Settings have been saved!');?></strong>
		</div>
		
		<div class="alert alert-error" id="alert-error1" style="display:none;">
		  <button class="close" onclick="$('.alert-error').hide();">×</button>
		  <strong><?php echo _('Sorry, unable to save. Please try again later!');?></strong>
		</div>
		
	    <form action="<?php echo get_app_info('path')?>/includes/settings/save.php" method="POST" accept-charset="utf-8" class="form-vertical" id="settings-form">
	    	
	    	<label class="control-label" for="company"><?php echo _('Company');?></label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="company" name="company" placeholder="<?php echo _('Your company');?>" value="<?php echo get_user_data('company');?>">
	            </div>
	        </div>
	        
	    	<label class="control-label" for="personal_name"><?php echo _('Name');?></label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="personal_name" name="personal_name" placeholder="<?php echo _('Your name');?>" value="<?php echo get_user_data('name');?>">
	            </div>
	        </div>
	        
	        <label class="control-label" for="email"><?php echo _('Email');?></label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="email" name="email" placeholder="<?php echo _('Your email address');?>" value="<?php echo get_user_data('username');?>" autocomplete="off">
	            </div>
	        </div>
	        <div class="alert alert-error" id="alert-error2" style="display:none;">
			  <button class="close" onclick="$('.alert-error').hide();">×</button>
			  <span><i class="icon icon-warning-sign"></i> <?php echo _('This login email address is already in use by one of your brands. Please find the brand that uses this login email address and change it to something else so that you can save. Or use another email address.');?></span>
			</div>
	        
	        <label class="control-label" for="password"><?php echo _('Password (leave blank to not change it)');?></label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="password" class="input-xlarge" id="password" name="password" placeholder="<?php echo _('Your password');?>" autocomplete="new-password">
	            </div>
	        </div>
	        
	        <div>
		        <div id="enable-2fa-btn" style="display: <?php echo get_user_data('auth_enabled') ? 'none' : 'block'; ?>">
			        <p><?php echo _('Two-factor authentication is currently <strong class="label">disabled</strong>');?></p>
			        <button id="enable-two-factor-btn" class="btn">
			        	<span class="icon icon-key"></span> <?php echo _('Enable two-factor authentication');?>
			        </button>
			    </div>
			    <div id="disable-2fa-btn" style="display: <?php echo get_user_data('auth_enabled') ? 'block' : 'none'; ?>">
				    <p><?php echo _('Two-factor authentication is currently <strong class="label label-success">enabled</strong>');?></p>
			    	<button id="disable-two-factor-btn" class="btn">
			    		<span class="icon icon-key"></span> <?php echo _('Disable two-factor authentication');?>
			    	</button>
			    	<p id="re-key"><a href="javascript:void(0)" id="regenerate-key"><span class="icon icon-refresh"></span> <?php echo _('Regenerate secret key');?></a></p>
			    </div>
		    </div>
		    <br/>
		    
		    <div id="two-factor-setup" class="well">
			    	<p>
				    	<strong><?php echo _('What is "Two-factor authentication"?');?></strong><br/>
				    	<?php echo _('Two-factor authentication provides an additional layer of security by requiring a one time password (OTP) in addition to email & password credentials. You can get OTP codes without an internet or cellular connection using a <a href="#two_factor_apps"data-toggle="modal" style="text-decoration:underline;">two-factor application</a>.');?>
			    	</p><br/>
				    <p><?php echo _('<strong>Step 1</strong>: Scan the following QR code (or enter the secret key) into your <a href="#two_factor_apps"data-toggle="modal" style="text-decoration:underline;">two-factor application</a>:');?></p>
				    <p><img src="" id="qr-code"/></p>
				    <p><strong><?php echo _('Secret key:');?></strong> <span id="secret-key"></span></p>
				    <br/>
				    <p><?php echo _('<strong>Step 2</strong>: Use your <a href="#two_factor_apps"data-toggle="modal" style="text-decoration:underline;">two-factor application</a> to generate an OTP code, then paste it below:');?></p>
				    <p><input type="text" class="input-xlarge" id="otp" name="otp" placeholder="<?php echo _('OTP code, eg. 123456');?>" autocomplete="off"></p>
				    <button id="confirm-two-factor-btn" class="btn"><span class="icon icon-chevron-sign-right"></span> <?php echo _('Confirm and enable two-factor authentication');?></button>
				    <br/>
				    <br/>
				    <a href="javascript:void(0)" id="cancel-two-factor"><span class="icon icon-remove-sign"></span> Cancel</a>
				    <br/>
		    </div>
	        <br/>
	        
	        <!-- Two factor authenticator apps link -->
			<div id="two_factor_apps" class="modal hide fade">
			<div class="modal-header">
			  <button type="button" class="close" data-dismiss="modal">&times;</button>
			  <h3><i class="icon icon-mobile-phone" style="margin-top: 5px;"></i> <?php echo _('Two-factor authentication applications');?></h3>
			</div>
			<div class="modal-body">
			<p><?php echo _('There are many \'Two-factor authentication\' mobile apps available in the market for free that can be used with services that supports 2FA like Amazon, Gmail, Dropbox, Facebook etc. The most common is \'Google Authenticator\'. The following are links to Google Authenticator app for both iOS and Android:');?></p>
			<h3><?php echo _('Google Authenticator');?></h3>
			<div class="well">
				<p class="google-auth"><img src="img/google-authenticator.png" title="Google Authenticator" width="100" height="100"/></p><br/>
				<div class="google-auth-links">
					<p><span class="icon icon-apple"></span> <strong><?php echo _('iOS');?></strong> → <a href="https://itunes.apple.com/app/google-authenticator/id388497605?mt=8" target="_blank">https://itunes.apple.com/app/google-authenticator/id388497605?mt=8</a></p>
					<p><span class="icon icon-android"></span> <strong><?php echo _('Android');?></strong> → <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank">https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2</a></p>
				</div>
			</div>
			</div>
			<div class="modal-footer">
			  <a href="#" class="btn btn-inverse" data-dismiss="modal"><i class="icon icon-ok-sign"></i> <?php echo _('Okay');?></a>
			</div>
			</div>
			<!-- Two factor authenticator apps link -->
	        
	        <?php 
		        include('includes/helpers/two-factor/vendor/base32.php');
		        $base32 = new Base32();
		        $secret_key_ran = strtoupper(ran_string(10, 10, true, false, true));
		        $secret_key = $base32->encode($secret_key_ran);
		        $r = mysqli_query($mysqli, 'SELECT company, username FROM login WHERE id = '.get_app_info('main_userID'));
		        if ($r) 
		        {
			        while($row = mysqli_fetch_array($r)) 
			        {
				        $main_company = $row['company'];  
				        $main_email = $row['username'];  
				    }
			    }
		        $issuer = get_app_info('is_sub_user') ? $main_company : 'Sendy';
		        $qrcode = '//chart.apis.google.com/chart?cht=qr&chs=200x200&chl=otpauth://totp/'.$issuer.':'.get_app_info('email').'?secret='.$secret_key.'&issuer='.$issuer;
	        ?>
	        
	        <script type="text/javascript">
		        //Two-factor
				$("#enable-two-factor-btn").click(function(e){
					e.preventDefault(); 
					show_two_factor_setup();
				});
				$("#confirm-two-factor-btn").click(function(e){
					e.preventDefault(); 
					confirm_otp();
				});
				$("#otp").keypress(function(e) {
				    if(e.keyCode == 13) {
						e.preventDefault();
						confirm_otp();
				    }
				});
				$("#disable-two-factor-btn").click(function(e){
					e.preventDefault(); 
					if(confirm("Are you sure you want to disable two-factor authentication?"))
					{
						$.post("<?php echo get_app_info("path");?>/includes/settings/two-factor.php", { enable: 0, otp: 0, uid: <?php echo get_app_info('userID');?> },
						  function(data) {
						      if(data)
						      {
							      $("#disable-2fa-btn").slideUp();
							      $("#enable-2fa-btn").slideDown();
							      $("#two-factor-setup").slideUp();
						      } else alert("<?php echo _('Unable to save. Please try again.');?>");
						  }
						);
					}
				});
				$("#regenerate-key").click(function(e){
					show_two_factor_setup();
				});
				$("#cancel-two-factor").click(function(e){
					e.preventDefault(); 
					$("#two-factor-setup").slideUp();
				});
				function confirm_otp()
				{
					$.post("<?php echo get_app_info("path");?>/includes/settings/two-factor.php", { enable: 1, key: "<?php echo $secret_key;?>", otp: $("#otp").val(), uid: <?php echo get_app_info('userID');?> },
					  function(data) {
					      if(data)
					      {
						      if(data=="confirmed")
						      {
						      	$("#two-factor-setup").slideUp();
						      	$("#disable-2fa-btn").slideDown();
						      	$("#enable-2fa-btn").slideUp();
						      	$("#re-key").hide();
						      }
						      else if(data=="not confirmed")
						        alert("<?php echo _('OTP is correct, but unable to save.');?>");
						      else if(data=="incorrect")
						      	alert("<?php echo _('OTP code is incorrect. Please try again.');?>");
						      else if(data=="not numeric")
						      	alert("<?php echo _('Please enter a valid, numeric OTP code.');?>");
					      } else alert("<?php echo _('Unable to save. Please try again.');?>");
					  }
					);
				}
				function show_two_factor_setup()
				{
					$("#two-factor-setup").slideDown();
					$("#qr-code").attr("src", "<?php echo $qrcode;?>");
					$("#secret-key").text("<?php echo $secret_key;?>");
					$("#otp").val("");
					$("#otp").focus();
				}
	        </script>
	        
	        <label for="timezone"><?php echo _('Select your timezone');?></label>
    		<select id="timezone" name="timezone">
			  <option value="<?php echo get_user_data('timezone');?>"><?php echo get_user_data('timezone');?></option> 
			  <?php get_timezone_list();?>
			</select>
			
			<br/><br/>
			
			<label for="language"><?php echo _('Select your language');?></label>
    		<select id="language" name="language">
			  <option value="<?php echo get_user_data('language');?>"><?php echo get_user_data('language');?></option>
			  <?php 
					if($handle = opendir('locale')) 
					{
						$i = -1;						
					    while (false !== ($file = readdir($handle))) 
					    {
					    	if($file!='.' && $file!='..' && substr($file, 0, 1)!='.')	
					    	{
					    		if(get_user_data('language')!=$file)
							    	echo '<option value="'.$file.'">'.$file.'</option>';
						    }
							
							$i++;
					    }
					    closedir($handle);
					}
			  ?>
			</select>
	        
	        <br/><br/>
			<?php if(!get_app_info('is_sub_user')): //if not sub user ?>
			
			<hr/>
			
			<div class="accordion" id="accordion">
				
				<div class="accordion-group">
					<div class="accordion-heading">
						<a href="#collapse0" class="accordion-toggle" data-parent="#accordion" data-toggle="collapse"><h3><span class="icon icon-key"></span> <?php echo _('Amazon Web Services Credentials');?></h3></a>
					</div>
					
					<div id="collapse0" class="accordion-body collapse">
						<p class="alert"><span class="icon icon-info-sign"></span> <?php echo _('Please see Step 5 of the Get Started Guide on how to create a set of IAM credentials to hook up your Amazon SES account with your Sendy installation');?> &rarr; <a href="https://sendy.co/get-started#step5" target="_blank">https://sendy.co/get-started#step5</a></p>
				        <label class="control-label" for="aws_key"><?php echo _('AWS Access Key ID');?></label>
				    	<div class="control-group">
					    	<div class="controls">
				              <input type="text" class="input-xlarge" id="aws_key" name="aws_key" placeholder="<?php echo _('AWS Access Key ID');?>" value="<?php echo get_user_data('s3_key');?>">
				            </div>
				        </div>
				        
				        <label class="control-label" for="aws_secret"><?php echo _('AWS Secret Access Key');?></label>
				    	<div class="control-group">
					    	<div class="controls">
				              <input type="password" class="input-xlarge" id="aws_secret" name="aws_secret" placeholder="<?php echo _('AWS Secret Acesss Key');?>" value="<?php echo get_user_data('s3_secret');?>">
				            </div>
				        </div>
				        <br/>
					</div>
				</div>
		        
		        <div class="accordion-group">
					<div class="accordion-heading">
						<a href="#collapse1" class="accordion-toggle" data-parent="#accordion" data-toggle="collapse"><h3><span class="icon icon-globe"></span> <?php echo _('Amazon SES region');?></h3></a>
					</div>
					
					<div id="collapse1" class="accordion-body collapse">
						<p class="alert"><span class="icon icon-info-sign"></span> <?php echo _('Please select the same region as what\'s set in your Amazon SES console in the region selection drop down menu at the top right.');?></p>      
				        <label for="ses_endpoint"><?php echo _('Your Amazon SES region');?></label>
				        <?php 
					        $ses_endpoint_data = array(
					          'us-east-1' => 'N. Virginia'
					        , 'us-east-2' => 'Ohio'
					        , 'us-west-2' => 'Oregon'
					        , 'ca-central-1' => 'Canada'
					        , 'eu-west-1' => 'Ireland'
					        , 'eu-central-1' => 'Frankfurt'
					        , 'eu-west-2' => 'London'
					        , 'ap-southeast-1' => 'Singapore'
					        , 'ap-southeast-2' => 'Sydney'
					        , 'ap-northeast-1' => 'Tokyo'
					        , 'ap-northeast-2' => 'Seoul'
					        , 'ap-south-1' => 'Mumbai'
					        , 'sa-east-1' => 'Sao Paulo'
					        );
					        
					        //Set $endpoint_name
					        $user_set_endpoint_explode = explode('.', get_user_data('ses_endpoint'));
					        $user_set_endpoint = $user_set_endpoint_explode[1]; // us-east-1
					        $endpoint_name = $ses_endpoint_data[$user_set_endpoint];
				        ?>
			    		<select id="ses_endpoint" name="ses_endpoint">
						  <option value="<?php echo get_user_data('ses_endpoint');?>"><?php echo $endpoint_name;?></option> 
						  <?php foreach($ses_endpoint_data as $key => $value): ?>
						  	  <?php if($value != $endpoint_name):?>
								  <option value="email.<?php echo $key;?>.amazonaws.com"><?php echo $value;?></option> 
							  <?php endif;?>
						  <?php endforeach;?>
						</select>
				        <br/><br/>
					</div>
		        </div>
		        
		        <?php if(get_app_info('s3_key')!='' && get_app_info('s3_secret')!=''):?>
		        
		        <div class="accordion-group">
					<div class="accordion-heading">
				        <a href="#collapse2" class="accordion-toggle" data-parent="#accordion" data-toggle="collapse"><h3><span class="icon icon-time"></span> <?php echo _('Sending rate');?></h3></a>
					</div>
					
					<div id="collapse2" class="accordion-body collapse">
						<p class="alert"><span class="icon icon-info-sign"></span> <?php echo _('Sendy sends bulks of emails in parallel per second according to your Amazon SES send rate. Depending on the capability of your server, your server may have trouble processing huge bulks of emails efficiently if your SES send rate is very high. Lowering your send rate may yield better sending speed and stability.');?></p> 
				        <label class="control-label" for="aws_key"><?php echo _('Adjust sending rate'); echo ' '; echo '('; echo _('Max'); echo ': '; echo $send_rate; echo ')';?></label>
				    	<div class="control-group">
				            <div class="input-prepend input-append">
				              <input type="text" class="input-xlarge" id="send_rate" name="send_rate" value="<?php echo get_user_data('send_rate')=='' || get_user_data('send_rate')==0 ? $send_rate : get_user_data('send_rate');?>" style="width: 80px;"><span class="add-on"><?php echo _('emails per second');?></span>
				              <input type="hidden" id="ses_send_rate" name="ses_send_rate" value="<?php echo $send_rate;?>">
				            </div>
				        </div>
				        <br/>
					</div>
		        </div>
		        
		        <?php endif;?>
		        
		        <div class="accordion-group">
					<div class="accordion-heading">
				        <a href="#collapse3" class="accordion-toggle" data-parent="#accordion" data-toggle="collapse"><h3><span class="icon icon-dollar"></span> <?php echo _('PayPal account');?></h3></a>
					</div>
					
					<div id="collapse3" class="accordion-body collapse">
				        <p class="alert"><span class="icon icon-info-sign"></span> <?php echo _('If you charge your client(s) a fee for sending newsletters, they\'ll pay to this PayPal account. Also, don\'t forget to turn <strong>Auto Return</strong> ON in your PayPal account under <strong>Profile > My sellings tools > Website preferences</strong> so that your client will be automatically re-directed to the sending script after payment. Just use your website\'s URL for the <strong>Return URL</strong> to be able to save.');?></p>
				        <label class="control-label" for="paypal"><?php echo _('PayPal email address');?></label>
				    	<div class="control-group">
					    	<div class="controls">
				              <input type="text" class="input-xlarge" id="paypal" name="paypal" placeholder="<?php echo _('PayPal account email address');?>" value="<?php echo get_user_data('paypal');?>">
				            </div>
				        </div>
				        <br/>
					</div>
		        </div>
		        
		        <div class="accordion-group">
			        <div class="accordion-heading">
				        <a href="#collapse4" class="accordion-toggle" data-parent="#accordion" data-toggle="collapse"><h3><span class="icon icon-reorder"></span> <?php echo _('Miscellaneous');?></h3></a>
			        </div>
			        
			        <div id="collapse4" class="accordion-body collapse">
				        <br/>
				        
				        <label class="control-label" for="brands_rows"><?php echo _('No. of brands to show per page');?></label>
					    	<div class="control-group">
					    	<div class="input-prepend input-append">
				              <input type="text" class="input-xlarge" id="brands_rows" name="brands_rows" placeholder="10" value="<?php echo get_app_info('brands_rows');?>" style="width: 80px;"><span class="add-on"><?php echo _('brands per page');?></span>
				            </div>
				        </div>
				        
				        <br/>
				        
				        <label class="control-label" for="brands_rows"><?php echo _('Require typing \'DELETE\' before a brand, campaign, list or template can be deleted');?></label>
				    	<div class="btn-group" data-toggle="buttons-radio">
						  <a href="javascript:void(0)" title="" class="btn" id="strict_delete_on"><i class="icon icon-ok"></i> <?php echo _('Yes');?></a>
						  <a href="javascript:void(0)" title="" class="btn" id="strict_delete_off"><i class="icon icon-remove-sign"></i> <?php echo _('No');?></a>
						</div>
						
						<script type="text/javascript">
							$(document).ready(function() {
								<?php if(get_app_info('strict_delete')):?>
								$("#strict_delete_on").button('toggle');
								$("#strict_delete").val("1");
								<?php else:?>
								$("#strict_delete_off").button('toggle');
								$("#strict_delete").val("0");
								<?php endif;?>
								
								$("#strict_delete_on").click(function(){
									$("#strict_delete").val("1");
								});
								$("#strict_delete_off").click(function(){
									$("#strict_delete").val("0");
								});
							});
						</script>
						
						<input type="hidden" name="strict_delete" id="strict_delete" value="<?php echo get_app_info('strict_delete');?>">
				        
				        <?php if(get_app_info('is_sub_user')):?>
				        <input type="hidden" name="id" value="<?php echo get_app_info('app');?>">
				        <?php endif;?>
				        
				        <br/>
			        </div>
		        </div>
		    
			</div>
	        
	        <?php else:?>
	        
	        <hr/>
	        
	        <div class="accordion" id="accordion">
		        <?php if(!get_app_info('campaigns_only')):?>
		        
		        <div class="accordion-group">
			        <div class="accordion-heading">
				        <a href="#collapse0" class="accordion-toggle" data-parent="#accordion" data-toggle="collapse"><h3><span class="icon icon-envelope-alt"></span> <?php echo _('Sending preferences');?></h3></a>
			        </div>
			        
			        <div id="collapse0" class="accordion-body collapse">
				        <br/>
				        <label class="control-label" for="from_name"><?php echo _('From name');?></label>
				    	<div class="control-group">
					    	<div class="controls">
				              <input type="text" class="input-xlarge" id="from_name" name="from_name" placeholder="<?php echo _('From name');?>" value="<?php echo get_saved_data('from_name');?>">
				            </div>
				        </div>
				        
				        <label class="control-label" for="from_email"><?php echo _('From email');?></label>
				    	<div class="control-group">
					    	<div class="controls">
				              <input type="text" class="input-xlarge" <?php echo verify_identity($main_email)=='verified' || (get_app_info('s3_key')=='' && get_app_info('s3_secret')=='') ? '' : 'disabled="disabled"'; ?> id="from_email" name="from_email" placeholder="<?php echo _('From email');?>" value="<?php echo get_saved_data('from_email');?>">
				            </div>
				        </div>
				        
				        <label class="control-label" for="reply_to"><?php echo _('Reply to email');?></label>
				    	<div class="control-group">
					    	<div class="controls">
				              <input type="text" class="input-xlarge" id="reply_to" name="reply_to" placeholder="<?php echo _('Reply to email');?>" value="<?php echo get_saved_data('reply_to');?>">
				            </div>
				        </div>
				        
				        <br/><br/>
			        </div>
		        </div>
		        
		        <?php endif;?>
		        
		        <?php if(!get_app_info('lists_only')):?>
		        
		        <div class="accordion-group">
			        <div class="accordion-heading">		        
				        <a href="#collapse1" class="accordion-toggle" data-parent="#accordion" data-toggle="collapse"><h3><span class="icon icon-certificate"></span> <?php echo _('GDPR features');?></h3></a>
			        </div>
		        
					<div id="collapse1" class="accordion-body collapse">
				        <p class="alert"><?php echo _('The <a href="https://www.eugdpr.org/the-regulation.html" target="_blank" style="text-decoration: underline;">General Data Protection Regulation (GDPR)</a> is a regulation in EU law on data protection and privacy for all individuals within the European Union. The GDPR regulation affects anyone in the world who collect and process the personal data of EU users. If you collect and process data of EU users, these GDPR features will be useful to you.')?></p>
				        
				        <label class="control-label"><?php echo _('GDPR options');?></label>
				    	<div class="control-group">
					    	<div class="dashed-box">
						    	<div class="checkbox">
								  <label><input type="checkbox" name="gdpr_options" <?php echo get_saved_data('gdpr_options')==1 ? 'checked' : '';?>><?php echo _('Show me GDPR options where applicable');?> <br/><br/><i class="thirtytwo"><?php echo _('By enabling this option, GDPR features will show up in the app where appropriate. For example, subscribe form dialogues will present you with an option to enable \'GDPR fields\' amongst many others.');?></i></label>
								</div>
					    	</div>
				        </div>
				        
				        <br/>
				        
				        <label class="control-label"><?php echo _('GDPR safe switch');?></label>
				    	<div class="control-group">
					    	<div class="dashed-box">
						    	<div class="checkbox">
								  <label><input type="checkbox" name="gdpr_only" <?php echo get_saved_data('gdpr_only')==1 ? 'checked' : '';?>><?php echo _('Only send Campaigns to subscribers with <span class="label label-warning">GDPR</span> tag');?> <br/><br/><i class="thirtytwo"><?php echo _('Subscribers who signup through the provided \'Ready-to-use subscribe form\' or the embeddable \'Subscribe form HTML code\' with \'gdpr\' parameter set to \'true\' will be tagged with \'GDPR\'. By enabling this option, your future Campaigns will only send to subscribers tagged with \'GDPR\'.');?></i></label>
								</div>
					    	</div>
					    	<br/>
					    	<div class="dashed-box">
						    	<div class="checkbox">
								  <label><input type="checkbox" name="gdpr_only_ar" <?php echo get_saved_data('gdpr_only_ar')==1 ? 'checked' : '';?>><?php echo _('Only send Autoresponders to subscribers with <span class="label label-warning">GDPR</span> tag');?> <br/><br/><i class="thirtytwo"><?php echo _('Subscribers who signup through the provided \'Ready-to-use subscribe form\' or the embeddable \'Subscribe form HTML code\' with \'gdpr\' parameter set to \'true\' will be tagged with \'GDPR\'. By enabling this option, your future Autoresponders will only send to subscribers tagged with \'GDPR\'.');?></i></label>
								</div>
					    	</div>
				        </div>
				        
				        <br/><br/>
					</div>
		        </div>
		        
		        <div class="accordion-group">
			        <div class="accordion-heading">	
				        <a href="#collapse2" class="accordion-toggle" data-parent="#accordion" data-toggle="collapse"><h3><span class="icon icon-ok"></span> <?php echo _('Google reCAPTCHA v2');?></h3></a>
			        </div>
			        
					<div id="collapse2" class="accordion-body collapse">
				        <div class="alert">
					        <span class="icon icon-info-sign"></span> <?php echo _('You can use Google\'s reCAPTCHA v2 to protect your subscription forms from spam bots. To enable, register your site on <a href="https://www.google.com/recaptcha/admin/create" target="_blank" style="text-decoration: underline;">Google\'s reCAPTCHA website</a> and select \'reCAPTCHA v2 > "I\'m not a robot" tickbox\' to get your \'Site key\' and \'Secret key\', then paste them below. reCAPTCHA will be disabled if the following fields are empty.');?>
				        </div>
				        
				        <label class="control-label" for="recaptcha_sitekey"><?php echo _('Site key');?></label>
				    	<div class="control-group">
					    	<div class="controls">
				              <input type="text" class="input-xlarge" id="recaptcha_sitekey" name="recaptcha_sitekey" placeholder="Site key" value="<?php echo get_saved_data('recaptcha_sitekey');?>">
				            </div>
				        </div>
				        
				        <label class="control-label" for="recaptcha_secretkey"><?php echo _('Secret key');?></label>
				    	<div class="control-group">
					    	<div class="controls">
				              <input type="password" class="input-xlarge" id="recaptcha_secretkey" name="recaptcha_secretkey" placeholder="Secret key" value="<?php echo get_saved_data('recaptcha_secretkey');?>">
				            </div>
				        </div>
				        
				        <br/><br/>
					</div>
				</div>
		        
		        <?php endif;?>
		        
		        
		        <?php if(!get_app_info('campaigns_only') || !get_app_info('reports_only') || !get_app_info('lists_only')):?>
		        
	        	<div class="accordion-group">
			        <div class="accordion-heading">		        
				        <a href="#collapse3" class="accordion-toggle" data-parent="#accordion" data-toggle="collapse"><h3><span class="icon icon-reorder"></span> <?php echo _('Miscellaneous');?></h3></a>
			        </div>
			        
			        <div id="collapse3" class="accordion-body collapse">
				        <br/>
				        <?php if(!get_app_info('campaigns_only')):?>
				        
				        <label class="control-label" for="query_string" style="width:70%;"><?php echo _('Default URL query string');?></label>
				    	<div class="control-group">
					    	<div class="controls">
				              <input type="text" class="input-xlarge" id="query_string" name="query_string" placeholder="utm_source=newsletter&utm_medium=<?php echo get_app_info('company')?>&utm_campaign=email_marketing" value="<?php echo get_saved_data('query_string');?>" style="width: 70%;">
				              <br/>
				              <span><i class="thirtytwo"><?php echo _('(Default query string to append to all links in your campaigns and autoresponders. A good use case is Google Analytics tracking. Don\'t include \'?\' in your query string.)')?></i></span>
				            </div>
				        </div>
				        
				        <br/>
				        
				        <label class="control-label" for="test_email_prefix"><?php echo _('Prefix for subject line of test emails');?></label>
				    	<div class="control-group">
					    	<div class="controls">
				              <input type="text" class="input-xlarge" id="test_email_prefix" name="test_email_prefix" placeholder="[Test]" value="<?php echo get_saved_data('test_email_prefix');?>">
				              <br/>
				              <span><i class="thirtytwo"><?php echo _('(Optionally add a prefix to the subject line of test emails)')?></i></span>
				            </div>
				        </div>
				        
				        <br/>
				        
				        <?php endif;?>
				        
				        <?php if(!get_app_info('campaigns_only') || !get_app_info('reports_only') || !get_app_info('lists_only')):?>
				        
				        <label class="control-label" for="campaign_report_rows"><?php echo _('No. of rows to show in campaigns, reports and lists');?></label>
				    	<div class="control-group">
					    	<div class="input-prepend input-append">
				              <input type="text" class="input-xlarge" id="campaign_report_rows" name="campaign_report_rows" placeholder="10" value="<?php echo get_saved_data('campaign_report_rows');?>" style="width: 80px;"><span class="add-on"><?php echo _('rows per page');?></span>
				            </div>
				        </div>
				        
				        <br/>
				        
				        <?php endif;?>
			        </div>
	        	</div>
		        
		        <?php endif;?>
		    </div>
		        
	        <input type="hidden" name="id" value="<?php echo get_app_info('app');?>">
	        
	        <?php endif;?>
        
			<br/>
	        
	        <input type="hidden" name="uid" value="<?php echo get_app_info('userID');?>">
	        
	        <?php $ii = get_app_info('is_sub_user') ? '?i='.get_app_info('app') : ''?>
	        <input type="hidden" name="redirect" id="redirect" value="<?php echo get_app_info('path').'/settings'.$ii;?>">
	        
	        <button type="submit" class="btn btn-inverse"><i class="icon-ok icon-white"></i> <?php echo _('Save');?></button>
	    </form>
    </div>   
    
    <!-- Check if sub user -->
	<?php if(!get_app_info('is_sub_user')):?>	 
    <div class="span5">
	    <h3><?php echo _('Your license key');?></h3><br/>
	    <div>
		    <div class="well"><strong id="license-key"><?php echo get_user_data('license');?></strong></div>
		    <p><?php echo _('You\'ll need this license key to');?> <a href="https://sendy.co/get-updated?l=<?php echo get_app_info('license');?>" target="_blank" style="text-decoration:underline"><?php echo _('download the latest version of Sendy on our website');?></a>.</p>
	    </div>
	    
	    <br/><br/>
	    
	    <h3><?php echo _('Your API key');?></h3><br/>
	    <div>
		    <div class="well">
			    <strong id="api-key"><?php echo get_user_data('api_key');?></strong>
			    <br/><br/>
			    <button id="regenerate-btn" class="btn"><span class="icon-refresh"></span> <?php echo _('Regenerate API key?');?></button>
			    <p class="alert" id="regenerate-api-info" style="display: none; margin-top: 20px;">
				    <span class="icon icon-info-sign"></span> <?php echo _('Please note that tracking links, unsubscribe and web version links are encrypted with your API key, regenerating your API key means they will cease to work for emails that were already sent out. Also, if you currently use Sendy\'s subscribe form or API for your website or application, please update the API key or forms with the newly generated one. Your old API key will be stored and can be restored at a later time if you change your mind.');?>
				    <br/><br/>
				    <a href="javascript:void(0)" id="regenerate-btn-confirm" class="btn btn-inverse"><span class="icon icon-ok"></span> <?php echo _('Click to regenerate API key');?></a>
				    <br/><br/>
				</p>
				<?php 
					$q = 'SELECT api_key_prev FROM login WHERE id = '.get_app_info('main_userID');
					$r = mysqli_query($mysqli, $q);
					if ($r && mysqli_num_rows($r) > 0) while($row = mysqli_fetch_array($r)) $api_key_prev = $row['api_key_prev'];
				?>
				<p><a href="javascript:void(0)" id="restore-api-key" style="<?php echo $api_key_prev=='' ? 'display: none;' : 'display: block;';?> margin-top: 10px; font-size: 12px;"><span class="icon icon-undo"></span> <?php echo _('Restore previous API key?');?></a></p>
		    </div>
	    </div>
	    <script type="text/javascript">
	    	$(document).ready(function() {
	    		$("#license-key, #api-key").mouseover(function(){$(this).selectText();});
	    		
	    		//Regenerate API key
	    		$("#regenerate-btn").click(function(e){
					e.preventDefault();
					$("#regenerate-api-info").slideDown();
				});
				
				//Confirm regenerate API key
	    		$("#regenerate-btn-confirm").click(function(e){
					e.preventDefault(); 
					
					$.post("includes/settings/regenerate-api-key.php", { regenerate: "true" },
					  function(data) {
					      if(data!="failed") 
					      {
						      $("#api-key").text(data);
						      $("#restore-api-key").css("display", "block");
						      $("#regenerate-api-info").slideUp(function(){
							     alert("<?php echo _('Your API key has been regenerated!');?>"); 
						      });
						  }
					      else alert("<?php echo _('Sorry, unable to regenerate API key. Please try again later!');?>");
					  }
					);
				});
				
				//Restore previous API key
				$("#restore-api-key").click(function(e){
					e.preventDefault(); 
					
					if(confirm("<?php echo _('This will restore your API key to the previous key. Do you want to proceed?');?>"))
					{
						$.post("includes/settings/restore-api-key.php", { restore: "true" },
						  function(data) {
						      if(data!="failed") 
						      {
							      $("#api-key").text(data);
							      $("#restore-api-key").css("display", "none");
							      $("#regenerate-api-info").slideUp(function(){
								     alert("<?php echo _('Your previous API key has been restored!');?>"); 
							      });
							  }
						      else alert("<?php echo _('Sorry, unable to restore API key. Please try again later!');?>");
						  }
						);
					}
				});
	    	});
	    </script>
	    <p>Visit the <a href="https://sendy.co/api?app_path=<?php echo get_app_info('path');?>" target="_blank" style="text-decoration:underline">API page</a> to view Sendy's API documentation or explore <a href="https://sendy.co/api#third-party-resources-integrations" target="_blank" style="text-decoration:underline">third party resources and integrations</a>.</p>
	    
	    <br/><br/>
	    
	    <h3><?php echo _('Zapier integration');?></h3><br/>
	    <p class="alert"><span class="icon icon-info-sign"></span> <?php echo _('<a href="https://zapier.com" target="_blank" style="text-decoration:underline">Zapier</a>\'s integration with <a href="https://zapier.com/zapbook/sendy/" target="_blank" style="text-decoration:underline">Sendy</a> opens up a whole world of automation! You can integrate Sendy with over 1,000 apps available in <a href="https://zapier.com/zapbook/" target="_blank" style="text-decoration:underline">Zapier\'s app directory</a>. The following are a few ready to use "Zaps" to get you started. Alternatively you can visit Zapier and create your own "Zaps" to integrate Sendy with any other apps. You\'d be prompted to <a href="https://zapier.com/app/signup" target="_blank" style="text-decoration:underline">signup for a free Zapier account</a> if you don\'t have one.');?></p>
	    <div>
		    <script src="https://zapier.com/zapbook/embed/widget.js?guided_zaps=<?php echo get_app_info('zaps');?>&container=false">
	    </div>
    </div>
    
    <?php endif;?>
</div>
<!-- Validation -->
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/validate.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$("#settings-form").validate({
			rules: {
				company: {
					required: true
				},
				personal_name: {
					required: true
				},
				email: {
					required: true,
					email: true
				},
				send_rate: {
					required: true
					,range: [1, <?php echo $send_rate;?>]
					,number: true
				},
				campaign_report_rows: {
					required: true,
					digits: true,
					min: 1	
				}
			},
			messages: {
				company: "<?php echo addslashes(_('Your company name is required'));?>",
				personal_name: "<?php echo addslashes(_('Your name is required'));?>",
				email: "<?php echo addslashes(_('A valid email is required'));?>"
			}
		});
		
		<?php if(get_app_info('is_sub_user')):?>
		//accordion1
		$("#collapse0").on('shown', function(){$('html, body').animate({scrollTop:$(this).parent().parent().position().top-60}, 'slow', 'easeOutExpo'); $("#from_name").focus();});
		$("#collapse1").on('shown', function(){$('html, body').animate({scrollTop:$(this).parent().parent().position().top-10}, 'slow', 'easeOutExpo');});
		$("#collapse2").on('shown', function(){$('html, body').animate({scrollTop:$(this).parent().parent().position().top+50}, 'slow', 'easeOutExpo'); $("#recaptcha_sitekey").focus();});
		$("#collapse3").on('shown', function(){$('html, body').animate({scrollTop:$(this).parent().parent().position().top+100}, 'slow', 'easeOutExpo'); $("#query_string").focus();});
		<?php endif;?>
	});
</script>
<?php include('includes/footer.php');?>