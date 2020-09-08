<?php 
	include('includes/config.php');
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
	
	//get variable
	$i = mysqli_real_escape_string($mysqli, $_GET['i']);
	$i_array = array();
	$i_array = explode('/', $i);
	
	//new encrytped string
	if(count($i_array)==1)
	{
		$i_array = array();
		$i_array = explode('/', short($i, true));
		$i_array[0] = short($i_array[0]);
		$i_array[1] = short($i_array[1]);
		$i_array[2] = short($i_array[2]);
	}
	
	//Get all data
	$userID = mysqli_real_escape_string($mysqli, short($i_array[0], true));
	$link_id = mysqli_real_escape_string($mysqli, short($i_array[1], true));
	$campaign_id = mysqli_real_escape_string($mysqli, short($i_array[2], true));
	$time = time();
	
	$q = 'SELECT clicks, link, ares_emails_id FROM links WHERE id = '.$link_id;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$clicks = $row['clicks'];
			$link = htmlspecialchars_decode($row['link']);
			$ares_emails_id = $row['ares_emails_id'];
			
			if($clicks=='')
				$val = $userID;
			else
			{
				$clicks .= ','.$userID;
				$val = $clicks;
			}
	    }  
	}
	else
	{
		echo '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/><meta name="viewport" content="width=device-width, initial-scale=1"><link rel="Shortcut Icon" type="image/ico" href="'.APP_PATH.'/img/favicon.png"><title>'._('Link no longer exists').'</title></head><style type="text/css">body{background: #ffffff;font-family: Helvetica, Arial;}#wrapper{background: #ffffff; border: 1px solid #ededed; width: 300px;height: 70px;margin: -140px 0 0 -150px;position: absolute;top: 50%;left: 50%;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;}p{text-align: center;}h2{font-weight: normal;text-align: center;}a{color: #000;}a:hover{text-decoration: none;}#top-pattern{margin-top: -8px;height: 8px;background: url("'.APP_PATH.'/img/top-pattern2.gif") repeat-x 0 0;background-size: auto 8px;}</style><body><div id="top-pattern"></div><div id="wrapper"><h2>'._('Link no longer exists').'</h2></div></body></html>';
		exit;
	}
	
	//Set click
	$q2 = 'UPDATE links SET clicks = "'.$val.'" WHERE id = '.$link_id;
	$r2 = mysqli_query($mysqli, $q2);
	if ($r2){}
	
	//Update subscriber's timestamp
	$q = 'UPDATE subscribers SET timestamp = "'.$time.'" WHERE id = '.$userID;
	mysqli_query($mysqli, $q);
	
	//Set open
	$q = $ares_emails_id=='' ? 'SELECT opens, app FROM campaigns WHERE id = '.$campaign_id : 'SELECT opens FROM ares_emails WHERE id = '.$campaign_id;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
		$opened = false;
	    while($row = mysqli_fetch_array($r))
	    {
			$opens = $row['opens'];
			$app = $ares_emails_id=='' ? $row['opens'] : 0;
			$opens_array = explode(',', $opens);
			foreach($opens_array as $open)
			{
				$open_array = explode(':', $open);
				$sid = $open_array[0];
				if($sid == $userID) $opened = true;
			}
	    }  
	}
	if(!$opened) 
	{
		if($ares_emails_id=='') file_get_contents_curl(APP_PATH.'/t/'.$i_array[2].'/'.$i_array[0]);
		else file_get_contents_curl(APP_PATH.'/t/'.$i_array[2].'/'.$i_array[0].'/a');
	}
	
	//tags for links
	$q = 'SELECT subscribers.name, subscribers.email, subscribers.list, subscribers.custom_fields, lists.app FROM subscribers, lists WHERE subscribers.list = lists.id AND subscribers.id = '.$userID;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$app = $row['app'];
			$name = $row['name'];
			$email = $row['email'];
			$list_id = $row['list'];
			$custom_values = $row['custom_fields'];
	    }  
	}	
	
	//Get custom domain
	$q2 = 'SELECT custom_domain, custom_domain_protocol, custom_domain_enabled FROM apps WHERE id = '.$app;
	$r2 = mysqli_query($mysqli, $q2);
	if ($r2 && mysqli_num_rows($r2) > 0)
	{
	    while($row = mysqli_fetch_array($r2))
	    {
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
	
	preg_match_all('/\[([a-zA-Z0-9!#%^&*()+=$@._\-\:|\/?<>~`"\'\s]+),\s*fallback=/i', $link, $matches_var, PREG_PATTERN_ORDER);
	preg_match_all('/,\s*fallback=([a-zA-Z0-9!,#%^&*()+=$@._\-\:|\/?<>~`"\'\s]*)\]/i', $link, $matches_val, PREG_PATTERN_ORDER);
	preg_match_all('/(\[[a-zA-Z0-9!#%^&*()+=$@._\-\:|\/?<>~`"\'\s]+,\s*fallback=[a-zA-Z0-9!,#%^&*()+=$@._\-\:|\/?<>~`"\'\s]*\])/i', $link, $matches_all, PREG_PATTERN_ORDER);
	preg_match_all('/\[([^\]]+),\s*fallback=/i', $link, $matches_var, PREG_PATTERN_ORDER);
	preg_match_all('/,\s*fallback=([^\]]*)\]/i', $link, $matches_val, PREG_PATTERN_ORDER);
	preg_match_all('/(\[[^\]]+,\s*fallback=[^\]]*\])/i', $link, $matches_all, PREG_PATTERN_ORDER);
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
				$link = str_replace($tag, $fallback, $link);
			else
				$link = str_replace($tag, $name, $link);
		}
		else //if not 'Name', it's a custom field
		{
			//if subscriber has no custom fields, use fallback
			if($custom_values=='')
				$link = str_replace($tag, $fallback, $link);
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
						    	$link = str_replace($tag, $fallback, $link);
					    	//otherwise, use the custom field value
					    	else
					    	{
					    		//if custom field is of 'Date' type, format the date
					    		if($cf_array[1]=='Date')
						    		$link = str_replace($tag, strftime("%a, %b %d, %Y", $custom_values_array[$j]), $link);
					    		//otherwise just replace tag with custom field value
					    		else
							    	$link = str_replace($tag, $custom_values_array[$j], $link);
					    	}
					    }
					    else
					    	$k++;
				    }
				    if($k==$cf_count)
				    	$link = str_replace($tag, $fallback, $link);
				}
			}
		}
	}
	//Email tag
	$link = str_replace('[Email]', $email, $link);	
	$link = str_replace('[Name]', $name, $link);	
	
	//webversion and unsubscribe tags
	if($ares_emails_id=='') //if link does not belong to an autoresponder campaign
	{
		$link = str_replace('[webversion]', $app_path.'/w/'.short($userID).'/'.short($list_id).'/'.short($campaign_id), $link);
		$link = str_replace('[unsubscribe]', $app_path.'/unsubscribe/'.short($email).'/'.short($list_id).'/'.short($campaign_id), $link);
	}
	else
	{
		$link = str_replace('[webversion]', $app_path.'/w/'.short($userID).'/'.short($list_id).'/'.short($campaign_id).'/a', $link);
		$link = str_replace('[unsubscribe]', $app_path.'/unsubscribe/'.short($email).'/'.short($list_id).'/'.short($campaign_id).'/a', $link);
	}
	
	//--------------------------------------------------------------//
	function file_get_contents_curl($url) 
	//--------------------------------------------------------------//
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$data = curl_exec($ch);
		$response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		if($response_code!=200) return 'blocked';
		else return $data;
	}
	
	//redirect to link	
	$link = stripos($link, 'http') === false ? 'http://'.$link : $link;
	header("Location: $link");
?>