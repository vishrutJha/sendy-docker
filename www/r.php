<?php 
	ini_set('display_errors', 0);
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
	include('includes/helpers/locale.php');
	include_once('includes/helpers/geo/geolite/geoip.inc');
	require 'includes/helpers/geo/geolite2/vendor/autoload.php';
	use GeoIp2\Database\Reader;
	
	$time = time();
	
	//get variable
	if(isset($_GET['c']))
	{
		$campaign_id = is_numeric(short($_GET['c'], true)) ? short($_GET['c'], true) : exit;
		
		//Set language
		$q = 'SELECT login.language, campaigns.from_email FROM campaigns, login WHERE campaigns.id = '.$campaign_id.' AND login.app = campaigns.app';
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0) 
		{
			while($row = mysqli_fetch_array($r)) 
			{
				$language = $row['language'];
				$email = $row['from_email'];
			}
		}
		set_locale($language);
		
		$feedback = _('Thank you for your confirmation!');
	}
	else
	{
		//new encrytped string		
		if(!filter_var(short($_GET['e'], true),FILTER_VALIDATE_EMAIL))
		{
			$i_array = array();
			$i_array = explode('/', mysqli_real_escape_string($mysqli, short($_GET['e'], true)));
			$email = mysqli_real_escape_string($mysqli, $i_array[0]);
			$app = is_numeric($i_array[1]) ? $i_array[1] : '';
			$webversion_string = short($i_array[2].'/'.$i_array[3].'/'.$i_array[4]);
			$webversion = APP_PATH.'/w/'.$webversion_string;
			$subscriber_id = is_numeric($i_array[2]) ? $i_array[2] : '';
			$campaign_id = is_numeric($i_array[4]) ? $i_array[4] : '';
		}
		//old encrypted string
		else
		{
			$email = filter_var(short($_GET['e'], true),FILTER_VALIDATE_EMAIL) ? mysqli_real_escape_string($mysqli, short($_GET['e'], true)) : exit;
			$app = is_numeric(short($_GET['a'], true)) ? short($_GET['a'], true) : exit;
			$webversion_string = isset($_GET['w']) ? mysqli_real_escape_string($mysqli, $_GET['w']) : exit;
			$webversion = isset($_GET['w']) ? APP_PATH.'/w/'.$webversion_string : exit;
			$webversion_string_array = explode('/', $webversion_string);
			$subscriber_id = (int)short($webversion_string_array[0], true);
			$subscriber_id = is_numeric($subscriber_id) ? $subscriber_id : '';
			$campaign_id = short($webversion_string_array[2], true);
			$campaign_id = is_numeric($campaign_id) ? $campaign_id : '';
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
		
		//Check if email exists in the list
		$q = 'SELECT list FROM subscribers WHERE id = '.$subscriber_id;
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) == 0)
		{
			$feedback = _('Email does not exist.');
		}
		else
		{
			while($row = mysqli_fetch_array($r))
		    {
			    //Get list ID this subscriber belongs to
				$list_id = $row['list'];
				
				//Get custom reconsent_success_url 
				$q2 = 'SELECT reconsent_success_url FROM lists WHERE id = '.$list_id;
				$r2 = mysqli_query($mysqli, $q2);
				if ($r2 && mysqli_num_rows($r2) > 0) while($row = mysqli_fetch_array($r2)) $reconsent_success_url = $row['reconsent_success_url'];
		    }  
		    
			//Set language
			$q = 'SELECT login.language FROM campaigns, login WHERE campaigns.id = '.$campaign_id.' AND login.app = campaigns.app';
			$r = mysqli_query($mysqli, $q);
			if ($r && mysqli_num_rows($r) > 0) while($row = mysqli_fetch_array($r)) $language = $row['language'];
			set_locale($language);
			
			$ipaddress = ipaddress();
			
			//Get country code
			if(version_compare(PHP_VERSION, '5.4')==-1)
			{
				$gi = geoip_open("includes/helpers/geo/geolite/GeoIP.dat",GEOIP_STANDARD);
				$country = geoip_country_code_by_addr($gi, $ipaddress);
				geoip_close($gi);
			}
			else
			{
				$reader = new Reader('includes/helpers/geo/geolite2/vendor/geoip2/geoip2/maxmind-db/GeoLite2-Country.mmdb');
				try 
				{
					$record = $reader->country($ipaddress);
					$country = $record->country->isoCode;
				}
				catch (Exception $e) { $country = ''; }
			}
			
			//Get list of list IDs that belong to the same brand
			$list_array = array();
			$q = 'SELECT id FROM lists WHERE app = '.$app;
			$r = mysqli_query($mysqli, $q);
			if ($r && mysqli_num_rows($r) > 0)
			{
			    while($row = mysqli_fetch_array($r))
			    {
					array_push($list_array, $row['id']);
			    }  
			}
			$list_ids = implode(',', $list_array);
			
			//Update 'gdpr' flag for email address that exists in this brand
			$q = 'UPDATE subscribers SET ip = "'.$ipaddress.'", country = "'.$country.'", referrer = "'.$webversion.'", timestamp = "'.$time.'", gdpr = 1 WHERE list IN ('.$list_ids.') AND email = "'.$email.'"';
			$r = mysqli_query($mysqli, $q);
			if ($r)
			    $feedback = _('Thank you for your confirmation!');
			else
				$feedback = _('Oops. There was an error.');
			
			
			//Set open
			$q = 'SELECT opens, opens_tracking, links_tracking FROM campaigns WHERE id = '.$campaign_id;
			$r = mysqli_query($mysqli, $q);
			if ($r && mysqli_num_rows($r) > 0)
			{
				$opened = false;
			    while($row = mysqli_fetch_array($r))
			    {
				    $opens_tracking = $row['opens_tracking'];
					$links_tracking = $row['links_tracking'];
					$opens = $row['opens'];
					$opens_array = explode(',', $opens);
					foreach($opens_array as $open)
					{
						$open_array = explode(':', $open);
						$sid = $open_array[0];
						if($sid == $subscriber_id) $opened = true;
					}
			    }  
			}
			if(!$opened) 
			{
				if($opens_tracking) 
					file_get_contents_curl($app_path.'/t/'.$webversion_string_array[2].'/'.$webversion_string_array[0]);
			}
			
			//Update click count
			$val = '';
			$q = 'SELECT clicks, link FROM links WHERE link = "'.$app_path.'/r?c='.short($campaign_id).'"';
			$r = mysqli_query($mysqli, $q);
			if ($r && mysqli_num_rows($r) > 0)
			{
			    while($row = mysqli_fetch_array($r))
			    {
					$clicks = $row['clicks'];
					$link = $row['link'];
					
					if($clicks=='')
						$val = $subscriber_id;
					else
					{
						$clicks .= ','.$subscriber_id;
						$val = $clicks;
					}
			    }  
			}	
			if($links_tracking && !empty($val))
			{
				$q = 'UPDATE links SET clicks = "'.$val.'" WHERE link = "'.$app_path.'/r?c='.short($campaign_id).'"';
				mysqli_query($mysqli, $q);
			}
		}
	}
	
	//Check if user wants to redirect to a custom page
	if($feedback==_('Thank you for your confirmation!'))
	{
		if($reconsent_success_url!='')
		{
			header("Location: $reconsent_success_url");
			exit;
		}
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
?>

<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="robots" content="noindex, nofollow">
		<link rel="Shortcut Icon" type="image/ico" href="<?php echo $app_path;?>/img/favicon.png">
		<title><?php echo strip_tags($feedback);?></title>
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
			
			width: 400px;
			padding-bottom: 10px;
			
			margin: -190px 0 0 -200px;
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
			<h2><?php echo $feedback;?></h2>
			<p><img src="img/tick.jpg" height="92" /></p>
			<p><?php echo '<strong>'._('Your email address').'</strong><br/>'.$email;?></p>
		</div>
	</body>
</html>