<?php 
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	//------------------------------------------------------//
	function get_app_data($val)
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT '.$val.' FROM apps WHERE id = "'.get_app_info('app').'" AND userID = '.get_app_info('main_userID');
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				return $row[$val];
		    }  
		}
	}
	
	//------------------------------------------------------//
	function get_saved_data($val)
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT '.$val.' FROM ares_emails WHERE id = '.mysqli_real_escape_string($mysqli, $_GET['ae']);
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				$value = stripslashes($row[$val]);
		    	
		    	//if title
		    	if($val == 'title')
		    	{
			    	//tags for subject
					preg_match_all('/\[([a-zA-Z0-9!#%^&*()+=$@._\-\:|\/?<>~`"\'\s]+),\s*fallback=/i', $value, $matches_var, PREG_PATTERN_ORDER);
					preg_match_all('/,\s*fallback=([a-zA-Z0-9!,#%^&*()+=$@._\-\:|\/?<>~`"\'\s]*)\]/i', $value, $matches_val, PREG_PATTERN_ORDER);
					preg_match_all('/(\[[a-zA-Z0-9!#%^&*()+=$@._\-\:|\/?<>~`"\'\s]+,\s*fallback=[a-zA-Z0-9!,#%^&*()+=$@._\-\:|\/?<>~`"\'\s]*\])/i', $value, $matches_all, PREG_PATTERN_ORDER);
					preg_match_all('/\[([^\]]+),\s*fallback=/i', $value, $matches_var, PREG_PATTERN_ORDER);
					preg_match_all('/,\s*fallback=([^\]]*)\]/i', $value, $matches_val, PREG_PATTERN_ORDER);
					preg_match_all('/(\[[^\]]+,\s*fallback=[^\]]*\])/i', $value, $matches_all, PREG_PATTERN_ORDER);
					$matches_var = $matches_var[1];
					$matches_val = $matches_val[1];
					$matches_all = $matches_all[1];
					for($i=0;$i<count($matches_var);$i++)
					{		
						$field = $matches_var[$i];
						$fallback = $matches_val[$i];
						$tag = $matches_all[$i];
						//for each match, replace tag with fallback
						$value = str_replace($tag, $fallback, $value);
					}
					$value = str_replace('[Name]', get_saved_data('from_name'), $value);
					$value = str_replace('[Email]', get_saved_data('from_email'), $value);
					
					//convert date
					date_default_timezone_set(get_app_info('timezone'));
					$today = time();
					$currentdaynumber = strftime('%d', $today);
					$currentday = strftime('%A', $today);
					$currentmonthnumber = strftime('%m', $today);
					$currentmonth = strftime('%B', $today);
					$currentyear = strftime('%Y', $today);
					$unconverted_date = array('[currentdaynumber]', '[currentday]', '[currentmonthnumber]', '[currentmonth]', '[currentyear]');
					$converted_date = array($currentdaynumber, $currentday, $currentmonthnumber, $currentmonth, $currentyear);
					$value = str_replace($unconverted_date, $converted_date, $value);
		    	}
				
				return $value;
		    }  
		}
	}
	
	//------------------------------------------------------//
	function get_click_percentage($cid)
	//------------------------------------------------------//
	{
		global $mysqli;
		$clicks_join = '';
		$clicks_array = array();
		$clicks_unique = 0;
		
		$q = 'SELECT * FROM links WHERE ares_emails_id = '.mysqli_real_escape_string($mysqli, $cid);
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
		    	$id = stripslashes($row['id']);
				$link = stripslashes($row['link']);
				$clicks = stripslashes($row['clicks']);
				if($clicks!='')
					$clicks_join .= $clicks.',';				
		    }  
		}
		
		$clicks_array = explode(',', $clicks_join);
		$clicks_unique = count(array_unique($clicks_array));
		
		return $clicks_unique-1;
	}
	
	//------------------------------------------------------//
	function get_unsubscribes()
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT last_ares FROM subscribers WHERE last_ares = '.mysqli_real_escape_string($mysqli, $_GET['ae']).' AND unsubscribed = 1';
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    return mysqli_num_rows($r); 
		}
		else
		{
			return 0;
		}
	}
	
	//------------------------------------------------------//
	function get_bounced()
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT last_ares FROM subscribers WHERE last_ares = '.mysqli_real_escape_string($mysqli, $_GET['ae']).' AND bounced = 1';
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    return mysqli_num_rows($r); 
		}
		else
		{
			return 0;
		}
	}
	
	//------------------------------------------------------//
	function get_complaints()
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT last_ares FROM subscribers WHERE last_ares = '.mysqli_real_escape_string($mysqli, $_GET['ae']).' AND complaint = 1';
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    return mysqli_num_rows($r); 
		}
		else
		{
			return 0;
		}
	}
	
	//------------------------------------------------------//
	function get_ares_data($val)
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT '.$val.' FROM ares WHERE id = '.mysqli_real_escape_string($mysqli, $_GET['a']);
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				return $row[$val];
		    }  
		}
	}
	
	//------------------------------------------------------//
	function get_ares_type_name($val)
	//------------------------------------------------------//
	{
		$type = get_ares_data($val);
		switch($type)
		{
			case 1:
			return 'Drip campaign';
			break;
			
			case 2:
			return 'Sent annually based on <strong>'.get_ares_data('custom_field').'</strong>';
			break;
			
			case 3:
			return 'Sent once based on <strong>'.get_ares_data('custom_field').'</strong>';
			break;
		}
	}
	
?>