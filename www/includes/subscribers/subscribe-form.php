<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php include('../helpers/short.php');?>
<?php 
	$lid = is_numeric($_POST['lid']) ? mysqli_real_escape_string($mysqli, $_POST['lid']) : exit;
	$app = is_numeric($_POST['app']) ? mysqli_real_escape_string($mysqli, $_POST['app']) : exit;
	$from_email = filter_var($_POST['from_email'],FILTER_VALIDATE_EMAIL) ? mysqli_real_escape_string($mysqli, $_POST['from_email']) : exit;
	$result = '';
	
	//Check if reCAPTCHA is enabled and get custom domain settings
	$q = 'SELECT recaptcha_sitekey, recaptcha_secretkey, custom_domain, custom_domain_protocol, custom_domain_enabled FROM apps WHERE id = '.$app;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$recaptcha_sitekey = $row['recaptcha_sitekey'];
			$recaptcha_secretkey = $row['recaptcha_secretkey'];
			$recaptcha_enabled = $recaptcha_sitekey!='' && $recaptcha_secretkey!='' ? true : false;
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

	if($recaptcha_enabled)
		$result .= "<script src='https://www.google.com/recaptcha/api.js'></script>
";
	
	$result .= '<form action="'.$app_path.'/subscribe" method="POST" accept-charset="utf-8">
	<label for="name">Name</label><br/>
	<input type="text" name="name" id="name"/>
	<br/>
	<label for="email">Email</label><br/>
	<input type="email" name="email" id="email"/>';
	
	$q = 'SELECT custom_fields, gdpr_enabled, marketing_permission, what_to_expect FROM lists WHERE id = '.$lid;
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$custom_fields = $row['custom_fields'];
			$gdpr_enabled = $row['gdpr_enabled'];
			$marketing_permission = $row['marketing_permission'];
			$what_to_expect = $row['what_to_expect'];
	    } 
	    if($custom_fields!='')
	    {
	    	$custom_fields_array = explode('%s%', $custom_fields);
	    	foreach($custom_fields_array as $cf)
	    	{
	    		$cf_array = explode(':', $cf);
			    $result .= '
<br/>
<label for="'.str_replace(' ', '', $cf_array[0]).'">'.$cf_array[0].'</label><br/>
<input type="text" name="'.str_replace(' ', '', $cf_array[0]).'" id="'.str_replace(' ', '', $cf_array[0]).'"/>';
			}
	    } 
	}
	
	if($gdpr_enabled)
	{
		$result .= '<br/><br/>
<input type="checkbox" name="gdpr" id="gdpr"/>
<span><strong>Marketing permission</strong>: ';

		if($marketing_permission=='')
		{
			$result .= 'I give my consent to '.$app_name.' to be in touch with me via email using the information I have provided in this form for the purpose of news, updates and marketing.';
		}
		else
		{
			$result .= $marketing_permission;
		}
		
		$result .= '</span>
<br/><br/>
<span><strong>What to expect</strong>: ';

		if($what_to_expect=='')
		{
			$result .= 'If you wish to withdraw your consent and stop hearing from us, simply click the unsubscribe link at the bottom of every email we send or contact us at '.$from_email.'. We value and respect your personal data and privacy. To view our privacy policy, please visit our website. By submitting this form, you agree that we may process your information in accordance with these terms.';
		}
		else
		{
			$result .= $what_to_expect;
		}
		
		$result .= '</span>
<br/>';
	}
	
	$result .= '<br/>';
	
	if($recaptcha_enabled)
		$result .= '
	<p class="g-recaptcha" data-sitekey="'.$recaptcha_sitekey.'"></p>';
	
	$result .= '<div style="display:none;">
	<label for="hp">HP</label><br/>
	<input type="text" name="hp" id="hp"/>
	</div>
	<input type="hidden" name="list" value="'.short($lid).'"/>
	<input type="hidden" name="subform" value="yes"/>
	<input type="submit" name="submit" id="submit"/>
</form>';

	echo $result;
?>