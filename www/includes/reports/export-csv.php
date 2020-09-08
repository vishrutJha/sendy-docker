<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 

/********************************/
$userID = get_app_info('main_userID');
$campaign_id = isset($_GET['c']) && is_numeric($_GET['c']) ? mysqli_real_escape_string($mysqli, (int)$_GET['c']) : '';
$link_id = isset($_GET['l']) && is_numeric($_GET['l']) ? mysqli_real_escape_string($mysqli, (int)$_GET['l']) : '';
$action = isset($_GET['a']) ? $_GET['a'] : '';
$additional_query = '';
/********************************/

if($action == 'clicks')
{
	//file name
	$filename = 'clicked.csv';
	$additional_query = 'AND subscribers.unsubscribed = 0 AND subscribers.bounced = 0 AND subscribers.complaint = 0';
	
	//get
	$clicks_join = '';
	$clicks_array = array();
	$clicks_unique = 0;
	
	$q = 'SELECT id, clicks FROM links WHERE campaign_id = '.$campaign_id;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
	    	$id = stripslashes($row['id']);
			$clicks = stripslashes($row['clicks']);
			if($clicks!='')
				$clicks_join .= $clicks.',';				
	    }  
	}
	
	$clicks_array = explode(',', $clicks_join);
	$clicks_unique = array_unique($clicks_array);
	$subscribers = substr(implode(',', $clicks_unique), 0, -1);
}
else if($action == 'opens')
{
	//file name
	$filename = 'opened.csv';
	$additional_query = 'AND subscribers.unsubscribed = 0 AND subscribers.bounced = 0 AND subscribers.complaint = 0';
	
	$q = 'SELECT opens FROM campaigns WHERE id = '.$campaign_id;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
  			$opens = $row['opens'];
  			preg_match_all('!\d+!', $opens, $matches_var);
			$opens_array_no_country = $matches_var[0];  			
  			$opens_unique = array_unique($opens_array_no_country);
	  		$subscribers = implode(',', $opens_unique);
	    }  
	}
}
else if($action == 'unopens')
{
	//file name
	$filename = 'unopened.csv';
	$additional_query = 'AND subscribers.unsubscribed = 0 AND subscribers.bounced = 0 AND subscribers.complaint = 0 AND subscribers.confirmed = 1';
	
	$q = 'SELECT opens FROM campaigns WHERE id = '.$campaign_id;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {  			
  			$opens = $row['opens'];
  			preg_match_all('!\d+!', $opens, $matches_var);
			$opens_array_no_country = $matches_var[0];    			
  			$opens_unique_ini = array_unique($opens_array_no_country);
  			$opens_unique = array();
  			foreach($opens_unique_ini as $ou2)
  			{
	  			$opens_unique[$ou2] = $ou2;
  			}
	    }  
	}
	
	//Get lists the campaign was sent to
	$q = 'SELECT to_send_lists, segs FROM campaigns WHERE id = '.$campaign_id;
	$r = mysqli_query($mysqli, $q);
	if ($r) 
	{
		while($row = mysqli_fetch_array($r)) 
		{
			$to_send_lists = $row['to_send_lists'];
			$segs = $row['segs'];
		}
	}
	
	$sid_not_opened = array();
	$subscribers = '';
	
	$q = 'SELECT id, email FROM subscribers WHERE list IN ('.$to_send_lists.') AND unsubscribed = 0 AND bounced = 0 AND complaint = 0 AND confirmed = 1 AND last_campaign = '.$campaign_id;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$sid = $row['id'];
			$email = $row['email'];
			if(!isset($opens_unique[$sid])) $sid_not_opened[$email] = $sid;
	    }  
	}
	
	$q = 'SELECT subscribers_seg.subscriber_id as subscriber_id, subscribers.email as email FROM subscribers_seg LEFT JOIN subscribers ON (subscribers.id = subscribers_seg.subscriber_id) WHERE subscribers_seg.seg_id IN ('.$segs.') '.$additional_query;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$sid = $row['subscriber_id'];
			$email = $row['email'];
			if(!isset($opens_unique[$sid])) $sid_not_opened[$email] = $sid;
	    }  
	}	
	
    $subscribers = implode(',', $sid_not_opened);
}
else if($action == 'unsubscribes')
{
	//file name
	$filename = 'unsubscribed.csv';
	
	$q = 'SELECT id FROM subscribers WHERE last_campaign = '.$campaign_id.' AND unsubscribed = 1';
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
		$unsubscribes_array = array();
	    while($row = mysqli_fetch_array($r))
	    {
  			$unsubscriber_id = $row['id'];
  			array_push($unsubscribes_array, $unsubscriber_id);
	    }  
	    
	    $subscribers = implode(',', $unsubscribes_array);
	}
}
else if($action == 'bounces')
{
	//file name
	$filename = 'bounced.csv';
	
	$q = 'SELECT id FROM subscribers WHERE last_campaign = '.$campaign_id.' AND bounced = 1';
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
		$unsubscribes_array = array();
	    while($row = mysqli_fetch_array($r))
	    {
  			$unsubscriber_id = $row['id'];
  			array_push($unsubscribes_array, $unsubscriber_id);
	    }  
	    
	    $subscribers = implode(',', $unsubscribes_array);
	}
}
else if($action == 'complaints')
{
	//file name
	$filename = 'marked-as-spam.csv';
	
	$q = 'SELECT id FROM subscribers WHERE last_campaign = '.$campaign_id.' AND complaint = 1';
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
		$unsubscribes_array = array();
	    while($row = mysqli_fetch_array($r))
	    {
  			$unsubscriber_id = $row['id'];
  			array_push($unsubscribes_array, $unsubscriber_id);
	    }  
	    
	    $subscribers = implode(',', $unsubscribes_array);
	}
}
else if($action == 'recipient_clicks')
{
	//file name
	$filename = 'recipients-who-clicked.csv';
	$additional_query = 'AND subscribers.unsubscribed = 0 AND subscribers.bounced = 0 AND subscribers.complaint = 0';
	
	//get strings of click ids
	$q = 'SELECT clicks, link FROM links WHERE id = '.$link_id;
	$r = mysqli_query($mysqli, $q);
	if ($r) 
	{	
		while($row = mysqli_fetch_array($r)) 
		{
			$subscribers = $row['clicks'];
			$the_link = $row['link'];
		}
	}
	
	//Get only unique subscriber ids
	$sid_array = explode(',', $subscribers);
	$sid_array_unique = array_unique($sid_array);
	$subscribers = implode(',', $sid_array_unique);
}
else
{
	//file name
	$filename = $action.'.csv';
	
	$q = 'SELECT opens FROM campaigns WHERE id = '.$campaign_id;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
  			$opens = $row['opens'];
  			
  			$opens_array = explode(',', $opens);
  			$opens_array_country_match = array();
  			
  			foreach($opens_array as $o)
  			{
	  			$f = explode(':', $o);
	  			if(array_key_exists(1, $f)) $ff = $f[1];
	  			else $ff = '';
	  			
	  			if($ff==$action)
	  				array_push($opens_array_country_match, $f[0]);
  			}
  			
  			$opens_unique = array_unique($opens_array_country_match);
	  		$subscribers = implode(',', $opens_unique);
	    }  
	}
}

//Export
$select = 'SELECT subscribers.id, subscribers.name, subscribers.email, subscribers.join_date, subscribers.timestamp, subscribers.list, subscribers.ip, subscribers.country, subscribers.referrer, subscribers.method, subscribers.added_via, subscribers.gdpr, lists.name as list_name  
			FROM subscribers 
			LEFT JOIN lists
			ON (subscribers.list = lists.id)
			where subscribers.id IN ('.$subscribers.') '.$additional_query;
$export = mysqli_query($mysqli, $select);
if($export)
{
	while($row = mysqli_fetch_array($export))
    {
		$subr_id = $row['id'];
		$name = '"'.$row['name'].'"';
		$email = '"'.$row['email'].'"';
		$list_name = '"'.$row['list_name'].'"';
		
		//Join date, IP, Country and Referrer
		$join_date = $row['join_date'];
		$last_activity = $row['timestamp'];
		$ip = $row['ip'];
		$signedup_country_code = $row['country'];
		$signedup_country = country_code_to_country($signedup_country_code);
		$referrer = $row['referrer'];
		
		//Opt-in method
		$optin_method = $row['method'];
		if($optin_method==1) $optin_method = 'Single opt-in';
		else if($optin_method==2) $optin_method = 'Double opt-in';
		
		//Added via
		$added_via = $row['added_via'];	
		if($added_via=='')
		{	
			if($join_date=='') $added_via = 'App interface';
			else $added_via = 'API';
		}
		else
		{
			if($added_via==1 || $join_date=='')
				$added_via = 'App interface';
			else if($added_via==2 || ($join_date!='' && $ip=='No data' && $signedup_country=='No data'))
				$added_via = 'API';
			else if($added_via==3)
				$added_via = 'Standard subscribe form';
		}
		
		//GDPR
		$gdpr = $row['gdpr'];
		$gdpr_status = $gdpr ? 'Yes' : 'No';
		
		//Count number of times subscriber opened the campaign
		$add_line_1 = $add_line_2 = '';
		if($action == 'opens')
		{
			$opened_times_count = array_count_values($opens_array_no_country);
			$opened_times = $opened_times_count[$subr_id];
			$add_line_1 = '"'.$opened_times.'",';
			$add_line_2 = '"'._('Opens').'",';
		}
		//Count number of times subscriber clicked any of the links in the campaign
		else if($action == 'clicks')
		{
			$clicked_times_count = array_count_values($clicks_array);
			$clicked_times = $clicked_times_count[$subr_id];
			$add_line_1 = '"'.$clicked_times.'",';
			$add_line_2 = '"'._('Clicks').'",';
		}
		//Count number of times subscriber clicked a specific link in the campaign
		else if($action == 'recipient_clicks')
		{
			$clicked_times_count = array_count_values($sid_array);
			$clicked_times = $clicked_times_count[$subr_id];
			$add_line_1 = '"'.$clicked_times.'",';
			$add_line_2 = '"'._('Clicks').'",';
		}
		
		//Parse join_date & last activity date
		$join_date = $join_date=='' ? '' : parse_date($join_date, 'long', false);
		$last_activity = $last_activity=='' ? '' : parse_date($last_activity, 'long', false);
		
		$data .= $name.','.$email.','.$list_name.',"'.$join_date.'","'.$last_activity.'",'.$add_line_1.'"'.$added_via.'","'.$optin_method.'","'.$ip.'","'.$signedup_country.'","'.$signedup_country_code.'","'.$referrer.'","'.$gdpr_status.'"'."\n";
    } 
    $data = substr($data, 0, -1);
    
    $first_line = '"'._('Name').'","'._('Email').'","'._('List').'","'._('Joined').'","'._('Last activity').'",'.$add_line_2.'"'._('Added via').'","'._('Opt-in method').'","'._('IP address').'","'._('Country').'","'._('Country code').'","'._('Signed up from').'","'._('GDPR').'"'."\n";
    
    $data = $first_line.str_replace("\r" , "" , $data);
    
	if($data == "") $data = "\n(0) Records Found!\n";
	
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=$filename");
	header("Pragma: no-cache");
	header("Expires: 0");
	print "$data";
}
else echo '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html;charset=utf-8"/><link rel="Shortcut Icon" type="image/ico" href="/img/favicon.png"><title>'._('Can\'t export CSV').'</title></head><style type="text/css">body{background: #ffffff;font-family: Helvetica, Arial;}#wrapper{background: #f7f9fc;width: 300px;height: 155px;margin: -140px 0 0 -150px;position: absolute;top: 50%;left: 50%;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;}p{text-align: center;line-height: 18px;font-size: 12px;padding: 0 30px;}h2{font-weight: normal;text-align: center;font-size: 20px;}a{color: #000;}a:hover{text-decoration: none;}</style><body><div id="wrapper"><p><h2>'._('Can\'t export CSV').'</h2></p><p>'._('There is either nothing to export, or the number of records may be too large. If it\'s the latter, try increasing MySQL\'s max_allowed_packet.').'</p></div></body></html>'; 
?>