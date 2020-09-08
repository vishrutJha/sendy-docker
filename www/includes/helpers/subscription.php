<?php 	
	//------------------------------------------------------//
	function send_email($subject, $message, $to_email, $to_name) 
	//------------------------------------------------------//
	{
		global $mysqli;
		global $app;
		global $s3_key;
		global $s3_secret;
		global $smtp_host;
		global $smtp_port;
		global $smtp_ssl;
		global $smtp_username;
		global $smtp_password;
		global $from_email;
		global $from_name;
		global $reply_to;
		global $allocated_quota;
			
		$mail = new PHPMailer();	
		if($s3_key!='' && $s3_secret!='')
		{
			$mail->IsAmazonSES();
			$mail->AddAmazonSESKey($s3_key, $s3_secret);
		}
		else if($smtp_host!='' && $smtp_port!='' && $smtp_ssl!='' && $smtp_username!='' && $smtp_password!='')
		{
			$mail->IsSMTP();
			$mail->SMTPDebug = 0;
			$mail->SMTPAuth = true;
			$mail->SMTPSecure = $smtp_ssl;
			$mail->Host = $smtp_host;
			$mail->Port = $smtp_port; 
			$mail->Username = $smtp_username;  
			$mail->Password = $smtp_password;
		}
		$mail->CharSet	  =	"UTF-8";
		$mail->From       = $from_email;
		$mail->FromName   = $from_name;
		$mail->Subject = $subject;
		$mail->MsgHTML($message);
		$mail->AddAddress($to_email, $to_name);
		$mail->AddReplyTo($reply_to, $from_name);
		$mail->Send();
		
		//Update quota if a monthly limit was set
		if($allocated_quota!=-1)
		{
			//if so, update quota
			$q = 'UPDATE apps SET current_quota = current_quota+1 WHERE id = '.$app;
			mysqli_query($mysqli, $q);
		}
	}
	
	//------------------------------------------------------//
	function unsubscribe_from_list($email, $list_id) 
	//------------------------------------------------------//
	{
		global $mysqli;
		
		$q = 'UPDATE subscribers SET unsubscribed = 1, timestamp = '.time().' WHERE email = "'.$email.'" AND list = '.$list_id;
		$r = mysqli_query($mysqli, $q);
		return $r ? true : false;
	}
	
	//--------------------------------------------------------------//
	function convert_tags($content_to_replace, $sid, $email_type, $to_replace)
	//--------------------------------------------------------------//
	{
		global $mysqli;
		global $list_id;
		global $name;
		global $thankyou_subject;
		global $thankyou_message;
		global $confirmation_subject;
		global $confirmation_email;
		global $goodbye_subject;
		global $goodbye_message;
		
		preg_match_all('/\[([a-zA-Z0-9!#%^&*()+=$@._\-\:|\/?<>~`"\'\s]+),\s*fallback=/i', $content_to_replace, $matches_var, PREG_PATTERN_ORDER);
		preg_match_all('/,\s*fallback=([a-zA-Z0-9!,#%^&*()+=$@._\-\:|\/?<>~`"\'\s]*)\]/i', $content_to_replace, $matches_val, PREG_PATTERN_ORDER);
		preg_match_all('/(\[[a-zA-Z0-9!#%^&*()+=$@._\-\:|\/?<>~`"\'\s]+,\s*fallback=[a-zA-Z0-9!,#%^&*()+=$@._\-\:|\/?<>~`"\'\s]*\])/i', $content_to_replace, $matches_all, PREG_PATTERN_ORDER);
		preg_match_all('/\[([^\]]+),\s*fallback=/i', $content_to_replace, $matches_var, PREG_PATTERN_ORDER);
		preg_match_all('/,\s*fallback=([^\]]*)\]/i', $content_to_replace, $matches_val, PREG_PATTERN_ORDER);
		preg_match_all('/(\[[^\]]+,\s*fallback=[^\]]*\])/i', $content_to_replace, $matches_all, PREG_PATTERN_ORDER);
		$matches_var = $matches_var[1];
		$matches_val = $matches_val[1];
		$matches_all = $matches_all[1];
		for($i=0;$i<count($matches_var);$i++)
		{   
			$field = $matches_var[$i];
			$fallback = $matches_val[$i];
			$tag = $matches_all[$i];
			
			//if tag is Name
			if($field=='Name')
			{
				if($name=='')
					$content_to_replace = str_replace($tag, $fallback, $content_to_replace);
				else
					$content_to_replace = str_replace($tag, $name, $content_to_replace);
			}
			else //if not 'Name', it's a custom field
			{
				//Get subscriber's custom field values
				$q = 'SELECT custom_fields FROM subscribers WHERE id = '.$sid;
				$r = mysqli_query($mysqli, $q);
				if ($r) while($row = mysqli_fetch_array($r)) $custom_values = $row['custom_fields'];
								
				//if subscriber has no custom fields, use fallback
				if($custom_values=='')
					$content_to_replace = str_replace($tag, $fallback, $content_to_replace);
				//otherwise, replace custom field tag
				else
				{					
					$q5 = 'SELECT custom_fields FROM lists WHERE id = '.$list_id;
					$r5 = mysqli_query($mysqli, $q5);
					if ($r5)
					{
					    while($row2 = mysqli_fetch_array($r5)) $custom_fields = $row2['custom_fields'];
					    $custom_fields_array = explode('%s%', $custom_fields);
					    $custom_values_array = explode('%s%', $custom_values);
					    $cf_count = count($custom_fields_array);
					    $k = 0;
					    
					    for($j=0;$j<$cf_count;$j++)
					    {
						    $cf_array = explode(':', $custom_fields_array[$j]);
						    $key = str_replace(' ', '', $cf_array[0]);
						    
						    //if tag matches a custom field
						    if($field==$key)
						    {
						    	//if custom field is empty, use fallback
						    	if($custom_values_array[$j]=='')
							    	$content_to_replace = str_replace($tag, $fallback, $content_to_replace);
						    	//otherwise, use the custom field value
						    	else
						    	{
						    		//if custom field is of 'Date' type, format the date
						    		if($cf_array[1]=='Date')
							    		$content_to_replace = str_replace($tag, strftime("%a, %b %d, %Y", $custom_values_array[$j]), $content_to_replace);
						    		//otherwise just replace tag with custom field value
						    		else
								    	$content_to_replace = str_replace($tag, $custom_values_array[$j], $content_to_replace);
						    	}
						    }
						    else
						    	$k++;
					    }
					    if($k==$cf_count)
					    	$content_to_replace = str_replace($tag, $fallback, $content_to_replace);
					}
				}
			}
		}
		if($email_type=='thankyou')
		{
			if($to_replace=='subject')
				$thankyou_subject = $content_to_replace;
			else if($to_replace=='message')
				$thankyou_message = $content_to_replace;
		}
		else if($email_type=='confirm')
		{
			if($to_replace=='subject')
				$confirmation_subject = $content_to_replace;
			else if($to_replace=='message')
				$confirmation_email = $content_to_replace;
		}
		else if($email_type=='goodbye')
		{
			if($to_replace=='subject')
			$goodbye_subject = $content_to_replace;
		else if($to_replace=='message')
			$goodbye_message = $content_to_replace;
		}
	}
	
	//------------------------------------------------------//
	function get_gravatar( $email, $s = 80, $d = 'mm', $r = 'g', $img = false, $atts = array() ) 
	//------------------------------------------------------//
	{
		$url = 'https://www.gravatar.com/avatar/';
		$url .= md5( strtolower( trim( $email ) ) );
		$url .= "?s=$s&d=$d&r=$r";
		if ( $img ) {
			$url = '<img src="' . $url . '"';
			foreach ( $atts as $key => $val )
				$url .= ' ' . $key . '="' . $val . '"';
			$url .= ' />';
		}
		return $url;
	}
	
	//--------------------------------------------------------------//
	function ipaddress()
	//--------------------------------------------------------------//
	{
		//get user's ip address
		if (getenv("HTTP_CLIENT_IP")) {
			$ip = getenv("HTTP_CLIENT_IP");
		} elseif (getenv("HTTP_X_FORWARDED_FOR")) {
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		} else {
			$ip = getenv("REMOTE_ADDR");
		}
		return $ip;
	}
	
	//------------------------------------------------------//
	function country_code_exist($code)
	//------------------------------------------------------//
	{
	    $country_code = array('AF','AX','AL','DZ','AS','AD','AO','AI','AQ','AG','AR','AM','AW','AU','AT','AZ','BS','BH','BD','BB','BY','BE','BZ','BJ','BM','BT','BO','BA','BW','BV','BR','IO','VG','BN','BG','BF','BI','KH','CM','CA','CV','KY','CF','TD','CL','CN','CX','CC','CO','KM','CD','CG','CK','CR','CI','HR','CU','CY','CZ','DK','DJ','DM','DO','EC','EG','SV','GQ','ER','EE','ET','FO','FK','FJ','FI','FR','GF','PF','TF','GA','GM','GE','DE','GH','GI','GR','GL','GD','GP','GU','GT','GG','GN','GW','GY','HT','HM','VA','HN','HK','HU','IS','IN','ID','IR','IQ','IE','IM','IL','IT','JM','JP','JE','JO','KZ','KE','KI','KP','KR','KW','KG','LA','LV','LB','LS','LR','LY','LI','LT','LU','MO','MK','MG','MW','MY','MV','ML','MT','MH','MQ','MR','MU','YT','MX','FM','MD','MC','MN','ME','MS','MA','MZ','MM','NA','NR','NP','AN','NL','NC','NZ','NI','NE','NG','NU','NF','MP','NO','OM','PK','PW','PS','PA','PG','PY','PE','PH','PN','PL','PT','PR','QA','RE','RO','RU','RW','BL','SH','KN','LC','MF','PM','VC','WS','SM','ST','SA','SN','RS','SC','SL','SG','SK','SI','SB','SO','ZA','GS','ES','LK','SD','SR','SJ','SZ','SE','CH','SY','TW','TJ','TZ','TH','TL','TG','TK','TO','TT','TN','TR','TM','TC','TV','UG','UA','AE','GB','US','UM','VI','UY','UZ','VU','VE','VN','WF','EH','YE','ZM','ZW','EU','IC','SX','CW','XK');
	    return in_array($code, $country_code) ? false : true;
	}
?>