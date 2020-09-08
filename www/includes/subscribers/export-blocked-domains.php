<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 

/********************************/
$userID = get_app_info('main_userID');
$app = isset($_GET['i']) && is_numeric($_GET['i']) ? get_app_info('app') : exit;

//Check if sub user is trying to download CSVs from other brands
if(get_app_info('is_sub_user')) 
{
	if(get_app_info('app')!=get_app_info('restricted_to_app'))
	{
		echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/blacklist-suppression?i='.get_app_info('restricted_to_app').'"</script>';
		exit;
	}
}

//Get brand name
$q = 'SELECT app_name FROM apps WHERE id = '.$app;
$r = mysqli_query($mysqli, $q);
if ($r && mysqli_num_rows($r) > 0) while($row = mysqli_fetch_array($r)) $brand_name = convert_to_filename($row['app_name']);

//File name
$filename = $brand_name.'-blocked-domains.csv';

$q2 = 'SELECT domain, timestamp, block_attempts FROM blocked_domains WHERE app = '.$app.' ORDER BY block_attempts DESC, timestamp DESC';
$r2 = mysqli_query($mysqli, $q2);
if ($r2 && mysqli_num_rows($r2) > 0)
{
	$data = '';
    while($row = mysqli_fetch_array($r2))
    {
		$domain = $row['domain'];
		$timestamp = '"'.parse_date($row['timestamp'], 'long', false).'"';
		$block_attempts = $row['block_attempts'];
		
		$data .= $domain.','.$timestamp.',"'.$block_attempts.'"'."\n";
    }
    $data = substr($data, 0, -1);
    
    //Header
    $first_line = '"'._('Domain').'","'._('Last update').'","'._('Block attempts').'"'."\n";
    
    $data = $first_line.str_replace("\r" , "" , $data);
}

if($data == "") $data = "\n(0) Records Found!\n";

header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=$filename");
header("Pragma: no-cache");
header("Expires: 0");
print "$data";
 
?>