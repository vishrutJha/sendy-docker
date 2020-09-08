<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 

/********************************/
$userID = get_app_info('main_userID');
$app = isset($_GET['i']) && is_numeric($_GET['i']) ? get_app_info('app') : exit;
$listID = isset($_GET['l']) && is_numeric($_GET['l']) ? mysqli_real_escape_string($mysqli, $_GET['l']) : exit;

//Check if sub user is trying to download CSVs from other brands
if(get_app_info('is_sub_user')) 
{
	$q = 'SELECT app FROM lists WHERE id = '.$listID;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0) while($row = mysqli_fetch_array($r)) $app_attached_to_listID = $row['app'];

	if(get_app_info('app')!=get_app_info('restricted_to_app') || $app_attached_to_listID!=get_app_info('restricted_to_app'))
	{
		echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/list?i='.get_app_info('restricted_to_app').'"</script>';
		exit;
	}
}

if(isset($_GET['a']))
{
	$additional = 'AND unsubscribed = 0 AND bounced = 0 AND complaint = 0 AND confirmed = 1';
	$filename_additional = '-active';
}
else if(isset($_GET['c']))
{
	$additional = 'AND confirmed = 0';
	$filename_additional = '-unconfirmed';
}
else if(isset($_GET['u']))
{
	$additional = 'AND unsubscribed = 1 AND bounced = 0';
	$filename_additional = '-unsubscribed';
}
else if(isset($_GET['b']))
{
	$additional = 'AND bounced = 1';
	$filename_additional = '-bounced';
}
else if(isset($_GET['cp']))
{
	$additional = 'AND complaint = 1';
	$filename_additional = '-marked-as-spam';
}
else if(isset($_GET['g']))
{
	$additional = 'AND gdpr = 1';
	$filename_additional = '-gdpr';
}
else
{
	$additional = '';
	$filename_additional = '-all';
}
/********************************/

$q = 'SELECT name, custom_fields FROM lists WHERE id = '.$listID.' AND userID = '.$userID;
$r = mysqli_query($mysqli, $q);
if ($r && mysqli_num_rows($r) > 0)
{
    while($row = mysqli_fetch_array($r))
    {
		$list_name = $row['name'];
		$custom_fields = $row['custom_fields'];
		$filename = str_replace(' ', '-', $list_name);
		$filename = strtolower($filename.$filename_additional).'.csv';
    }  
}

$q2 = 'SELECT name, email, custom_fields, join_date, timestamp, ip, country, referrer, method, added_via, unsubscribed, bounced, complaint, confirmed, gdpr FROM subscribers WHERE list = '.$listID.' '.$additional.' AND userID = '.$userID.' ORDER BY timestamp DESC';
$r2 = mysqli_query($mysqli, $q2);
if ($r2 && mysqli_num_rows($r2) > 0)
{
	$data = '';
    while($row = mysqli_fetch_array($r2))
    {
		$name = '"'.$row['name'].'"';
		$email = '"'.$row['email'].'"';
		
		//Join date, IP, Country and Referrer
		$join_date = $row['join_date'];
		$last_activity = $row['timestamp'];
		$ip = $row['ip'];
		$signedup_country_code = $row['country'];
		$signedup_country = country_code_to_country($signedup_country_code);
		$referrer = $row['referrer'];
		$status = 'Active';
		$unsubscribed = $row['unsubscribed'];
		$bounced = $row['bounced'];
		$complaint = $row['complaint'];
		$confirmed = $row['confirmed'];
		$gdpr = $row['gdpr'];
		
		//Status
		if($bounced) $status = 'Bounced';
		else if($complaint) $status = 'Marked as spam';
		else if($unsubscribed) $status = 'Unsubscribed';
		else if(!$confirmed) $status = 'Unconfirmed';
		
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
		$gdpr_status = $gdpr ? 'Yes' : 'No';
		
		//Parse join_date & last activity date
		$join_date = $join_date=='' ? '' : parse_date($join_date, 'long', false);
		$last_activity = $last_activity=='' ? '' : parse_date($last_activity, 'long', false);
		
		$custom_values = $row['custom_fields'];
		$cf_value = '';		
		if($custom_fields=='')
		{
			$data .= $name.','.$email.',"'.$join_date.'","'.$last_activity.'","'.$added_via.'","'.$optin_method.'","'.$ip.'","'.$signedup_country.'","'.$signedup_country_code.'","'.$referrer.'","'.$status.'","'.$gdpr_status.'"'."\n";
		}
		else
		{
			//format custom fields into CSV
			$custom_fields_array = explode('%s%', $custom_fields);
			$custom_values_array = explode('%s%', $custom_values);
			for($i=0;$i<count($custom_fields_array);$i++)
			{
				$cf_field_array = explode(':', $custom_fields_array[$i]);
				
				if($cf_field_array[1]=='Date' && $cf_field_array[1]!='')
					$cf_value .= '"'.strftime("%b %d %Y", $custom_values_array[$i]).'",';
				else			
					$cf_value .= '"'.$custom_values_array[$i].'",';
			}
			$cf_value = substr($cf_value, 0, -1);
			
			
			$data .= $name.','.$email.',"'.$join_date.'","'.$last_activity.'","'.$added_via.'","'.$optin_method.'","'.$ip.'","'.$signedup_country.'","'.$signedup_country_code.'","'.$referrer.'","'.$status.'","'.$gdpr_status.'",'.$cf_value."\n";
		}
    }
    $data = substr($data, 0, -1);
    
    //Header
    if($custom_fields=='')
    {
	    $first_line = '"'._('Name').'","'._('Email').'","'._('Joined').'","'._('Last activity').'","'._('Added via').'","'._('Opt-in method').'","'._('IP address').'","'._('Country').'","'._('Country code').'","'._('Signed up from').'","'._('Status').'","'._('GDPR').'"'."\n";
	}
	else
	{
		for($j=0;$j<count($custom_fields_array);$j++)
		{
			$cf_field_array = explode(':', $custom_fields_array[$j]);
			$cf_field .= '"'.$cf_field_array[0].'",';
		}
		$cf_field = substr($cf_field, 0, -1);
		
		$first_line = '"'._('Name').'","'._('Email').'","'._('Joined').'","'._('Last activity').'","'._('Added via').'","'._('Opt-in method').'","'._('IP address').'","'._('Country').'","'._('Country code').'","'._('Signed up from').'","'._('Status').'","'._('GDPR').'",'.$cf_field."\n";
	}
    
    $data = $first_line.str_replace("\r" , "" , $data);
}

if($data == "") $data = "\n(0) Records Found!\n";

header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=$filename");
header("Pragma: no-cache");
header("Expires: 0");
print "$data";
 
?>