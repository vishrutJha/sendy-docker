<?php 
	ini_set('display_errors', 0);
	include('includes/config.php');
	include('includes/helpers/locale.php');
	include('includes/helpers/integrations/zapier/triggers/functions.php');
	include('includes/helpers/subscription.php');
	//--------------------------------------------------------------//
	function dbConnect() { //Connect to database
	//--------------------------------------------------------------//
	    // Access global variables
	    global $mysqli;
	    global $dbHost;
	    global $dbUser;
	    global $dbPass;
	    global $dbName;
	    global $dbPort;
	    
	    // Attempt to connect to database server
	    if(isset($dbPort)) $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
	    else $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
	
	    // If connection failed...
	    if ($mysqli->connect_error) {
	        fail("<!DOCTYPE html><html><head><meta http-equiv=\"Content-Type\" content=\"text/html;charset=utf-8\"/><link rel=\"Shortcut Icon\" type=\"image/ico\" href=\"/img/favicon.png\"><title>"._('Can\'t connect to database')."</title></head><style type=\"text/css\">body{background: #ffffff;font-family: Helvetica, Arial;}#wrapper{background: #f2f2f2;width: 300px;height: 110px;margin: -140px 0 0 -150px;position: absolute;top: 50%;left: 50%;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;}p{text-align: center;line-height: 18px;font-size: 12px;padding: 0 30px;}h2{font-weight: normal;text-align: center;font-size: 20px;}a{color: #000;}a:hover{text-decoration: none;}</style><body><div id=\"wrapper\"><p><h2>"._('Can\'t connect to database')."</h2></p><p>"._('There is a problem connecting to the database. Please try again later.')."</p></div></body></html>");
	    }
	    
	    global $charset; mysqli_set_charset($mysqli, isset($charset) ? $charset : "utf8");
	    
	    return $mysqli;
	}
	//--------------------------------------------------------------//
	function fail($errorMsg) { //Database connection fails
	//--------------------------------------------------------------//
	    echo $errorMsg;
	    exit;
	}
	// connect to database
	dbConnect();
?>
<?php 
	include('includes/helpers/short.php');
	include_once('includes/helpers/PHPMailerAutoload.php');
	
	//new encrytped string
	if(!is_numeric(short($_GET['e'], true)))
	{
		$i_array = explode('/', short($_GET['e'], true));
		$email_id = mysqli_real_escape_string($mysqli, $i_array[0]);
		$list_id = mysqli_real_escape_string($mysqli, $i_array[1]);
	}
	//old encrypted string
	else
	{
		$email_id = mysqli_real_escape_string($mysqli, short($_GET['e'], true));
		$list_id = mysqli_real_escape_string($mysqli, short($_GET['l'], true));
	}
	
	$time = time();
	$join_date = round($time/60)*60;
	
	//Set language
	$q = 'SELECT login.language FROM lists, login WHERE lists.id = '.$list_id.' AND login.app = lists.app';
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0) while($row = mysqli_fetch_array($r)) $language = $row['language'];
	set_locale($language);
	
	$q = 'UPDATE subscribers SET confirmed = 1, timestamp = "'.$time.'", join_date = CASE WHEN join_date IS NULL THEN '.$join_date.' ELSE join_date END WHERE id = '.$email_id.' AND list = '.$list_id;
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
		//get thank you message etc
		$q2 = 'SELECT app, name, userID, thankyou, thankyou_subject, thankyou_message, confirm_url, notify_new_signups, notification_email, custom_fields, unsubscribe_from_list, unsubscribe_list_id FROM lists WHERE id = '.$list_id;
		$r2 = mysqli_query($mysqli, $q2);
		if ($r2)
		{
		    while($row = mysqli_fetch_array($r2))
		    {
				$userID = $row['userID'];
				$app = $row['app'];
				$list_name = $row['name'];
				$thankyou = $row['thankyou'];
				$thankyou_subject = stripslashes($row['thankyou_subject']);
				$thankyou_message = stripslashes($row['thankyou_message']);
				$confirm_url = stripslashes($row['confirm_url']);
				$notify_new_signups = $row['notify_new_signups'];
				$notification_email = $row['notification_email'];
				$custom_fields = $row['custom_fields'];
				$unsubscribe_from_list = $row['unsubscribe_from_list'];
				$unsubscribe_list_id = $row['unsubscribe_list_id'];
		    }  
		}
		//get email address of subscribing user
		$q3 = 'SELECT name, email, custom_fields FROM subscribers WHERE id = '.$email_id;
		$r3 = mysqli_query($mysqli, $q3);
		if ($r3)
		{
		    while($row = mysqli_fetch_array($r3))
		    {
				$name = $row['name'];
				$email = $row['email'];
				$custom_values = $row['custom_fields'];
		    }  
		}
		//get smtp credentials and other data
		$q4 = 'SELECT from_name, from_email, reply_to, smtp_host, smtp_port, smtp_ssl, smtp_username, smtp_password, allocated_quota, custom_domain, custom_domain_protocol, custom_domain_enabled FROM apps WHERE id = '.$app;
		$r4 = mysqli_query($mysqli, $q4);
		if ($r4)
		{
		    while($row = mysqli_fetch_array($r4))
		    {
				$from_name = $row['from_name'];
				$from_email = $row['from_email'];
				$reply_to = $row['reply_to'];
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
					$parse = parse_url(APP_PATH);
					$domain = $parse['host'];
					$protocol = $parse['scheme'];
					$app_path = str_replace($domain, $custom_domain, APP_PATH);
					$app_path = str_replace($protocol, $custom_domain_protocol, $app_path);
				}
				else $app_path = APP_PATH;
		    }  
		}
		//get AWS creds
		$q = 'SELECT s3_key, s3_secret FROM login WHERE id = '.$userID;
		$r = mysqli_query($mysqli, $q);
		if ($r)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				$s3_key = $row['s3_key'];
				$s3_secret = $row['s3_secret'];
		    }
		}
		
		//Zapier Trigger 'new_user_subscribed' event
		zapier_trigger_new_user_subscribed($name, $email, $list_id);
		
		//Send email notification of new signup if enabled
		if($notify_new_signups)
		{			
			//get custom fields values
		    $j = 0;
		    $cf_value = '';
		    $custom_values_array = explode('%s%', $custom_values);
		    foreach($custom_fields_array as $cf_fields)
			{
				$k = 0;
				$cf_fields_array = explode(':', $cf_fields);
				foreach ($_POST as $key => $value)
				{
					//if custom field matches POST data but IS NOT name, email, list or submit
					if(str_replace(' ', '', $cf_fields_array[0])==$key && ($key!='name' && $key!='email' && $key!='list' && $key!='submit'))
					{
						//if user left field empty
						if($value=='')
						{
							$cf_value .= '';
						}
						else
						{
							//if custom field format is Date
							if($cf_fields_array[1]=='Date')
							{
								$date_value1 = strtotime($value);
								$date_value2 = strftime("%b %d, %Y 12am", $date_value1);
								$value = strtotime($date_value2);
								$cf_value .= $value;
							}
							//else if custom field format is Text
							else
								$cf_value .= strip_tags($value);
						}
					}
					else
					{
						$k++;
					}
				}
				if(count($_POST)==$k) $cf_value .= $custom_values_array[$j];			
				$cf_value .= '%s%';
				$j++;
			}
			
			//Populate custom fields (if available) for notification email
			if($custom_fields!='')
			{
				$custom_field_lines = '';
				$custom_fields_array = explode('%s%', $custom_fields);
				$custom_fields_values_array = explode('%s%', $custom_values);
				for($c=0;$c<count($custom_fields_array);$c++)
				{
					$fields_array = explode(':', $custom_fields_array[$c]);
					$values_array = $fields_array[1]=='Date' ? strftime("%b %d, %Y", $custom_fields_values_array[$c]) : $custom_fields_values_array[$c];
					$custom_field_lines .= '<strong>'.$fields_array[0].': </strong>'.$values_array.'<br/>';
				}
			}
								
			$notification_subject = '[New subscriber] List: '.$list_name;
			$notification_message = "<div style=\"margin: -10px -10px; padding:50px 30px 50px 30px; height:100%;\">
				<div style=\"margin:0 auto; max-width:660px;\">
					<div style=\"float: left; background-color: #FFFFFF; padding:10px 30px 10px 30px; border: 1px solid #f6f6f6;\">
						<div style=\"float: left; max-width: 106px; margin: 10px 20px 15px 0;\">
							<img src=\"".get_gravatar($email, 88)."\"/>
						</div>
						<div style=\"float: left; max-width:470px;\">
							<p style=\"line-height: 21px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 12px;\">
								<strong style=\"line-height: 21px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 18px;\">"._('You have a new subscriber!')."</strong>
							</p>	
							<div style=\"line-height: 21px; min-height: 100px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 12px;\">
								<p style=\"line-height: 21px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 12px;\">"._('The following user signed up to your list').":</p>
								<p style=\"line-height: 21px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 12px; margin-bottom: 25px; background-color:#f7f9fc; padding: 15px;\">
									<strong>"._('Name').": </strong>$name<br/>
									<strong>"._('Email').": </strong>$email<br/>
									$custom_field_lines
									<strong>"._('List').": </strong><a style=\"color:#4371AB; text-decoration:none;\" href=\"".$app_path."/subscribers?i=$app&l=$list_id\">$list_name</a>
								</p>
								<p style=\"line-height: 21px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 12px;\">
								</p>
							</div>
						</div>
					</div>
				</div>
			</div>";
			
			//Send notification email
			send_email($notification_subject, $notification_message, $notification_email, '');
		}
		
		//Unsubscribe from another list?
		if($unsubscribe_from_list)
		{
			unsubscribe_from_list($email, $unsubscribe_list_id);
		}
	}
	
	if($thankyou)
	{		
		//Convert personaliztion tags
		convert_tags($thankyou_subject, $email_id, 'thankyou', 'subject');
		convert_tags($thankyou_message, $email_id, 'thankyou', 'message');
		
		//Convert name tag
		$thankyou_message = str_replace('[Name]', $name, $thankyou_message);
		$thankyou_subject = str_replace('[Name]', $name, $thankyou_subject);
		
		//Convert email tag
		$thankyou_message = str_replace('[Email]', $email, $thankyou_message);
		$thankyou_subject = str_replace('[Email]', $email, $thankyou_subject);
		
		//Unsubscribe tag
		$thankyou_message = str_replace('<unsubscribe', '<a href="'.$app_path.'/unsubscribe/'.short($email).'/'.short($list_id).'" ', $thankyou_message);
    	$thankyou_message = str_replace('</unsubscribe>', '</a>', $thankyou_message);
		$thankyou_message = str_replace('[unsubscribe]', $app_path.'/unsubscribe/'.short($email).'/'.short($list_id), $thankyou_message);
		
		//Send thankyou email
		send_email($thankyou_subject, $thankyou_message, $email, '');
		
		//Update quota if a monthly limit was set
		if($allocated_quota!=-1)
		{
			//if so, update quota
			$q4 = 'UPDATE apps SET current_quota = current_quota+1 WHERE id = '.$app;
			mysqli_query($mysqli, $q4);
		}
	}
	
	//if user sets a redirection URL
	if($confirm_url != ''):
		$confirm_url = str_replace('%n', $name, $confirm_url);
		$confirm_url = str_replace('%e', $email, $confirm_url);
		$confirm_url = str_replace('%l', short($list_id), $confirm_url);
		header("Location: ".$confirm_url);
	else:
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="robots" content="noindex, nofollow">
		<link rel="Shortcut Icon" type="image/ico" href="<?php echo $app_path;?>/img/favicon.png">
		<title><?php echo _('You\'re subscribed!');?></title>
	</head>
	<style type="text/css">
		body{
			background: #f7f9fc;
			font-family: Helvetica, Arial;
		}
		#wrapper 
		{
			background: #ffffff;
			-webkit-box-shadow: 0px 16px 46px -22px rgba(0,0,0,0.75);
			-moz-box-shadow: 0px 16px 46px -22px rgba(0,0,0,0.75);
			box-shadow: 0px 16px 46px -22px rgba(0,0,0,0.75);
			
			width: 300px;
			padding-bottom: 10px;
			
			margin: -170px 0 0 -150px;
			position: absolute;
			top: 50%;
			left: 50%;
			-webkit-border-radius: 5px;
			-moz-border-radius: 5px;
			border-radius: 5px;
		}
		p{
			text-align: center;
		}
		h2{
			font-weight: normal;
			text-align: center;
		}
		a{
			color: #000;
			text-decoration: none;
		}
		a:hover{
			text-decoration: underline;
		}
		#top-pattern{
			margin-top: -8px;
			height: 8px;
			background: url("<?php echo $app_path; ?>/img/top-pattern2.gif") repeat-x 0 0;
			background-size: auto 8px;
		}
	</style>
	<body>
		<div id="top-pattern"></div>
		<div id="wrapper">
			<h2><?php echo _('You\'re subscribed!');?></h2>
			<p><img src="img/tick.jpg" height="92" /></p>
		</div>
	</body>
</html>
<?php endif;?>