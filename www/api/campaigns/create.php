<?php include('../_connect.php');?>
<?php include('../../includes/helpers/short.php');?>
<?php 
	//-------------------------- ERRORS -------------------------//
	$error_core = array('No data passed', 'API key not passed', 'Invalid API key');
	$error_passed = array(
	  'From name not passed'
	, 'From email not passed'
	, 'Reply to email not passed'
	, 'Subject not passed'
	, 'HTML not passed'
	, 'List or segment ID(s) not passed'
	, 'One or more list IDs are invalid'
	, 'List or segment IDs does not belong to a single brand'
	, 'Brand ID not passed.'
	, 'One or more segment IDs are invalid'
	);
	//-----------------------------------------------------------//
	
	//--------------------------- POST --------------------------//
	//api_key	
	$api_key = isset($_POST['api_key']) ? mysqli_real_escape_string($mysqli, $_POST['api_key']) : null;
	
	//from_name
	$from_name = isset($_POST['from_name']) ? mysqli_real_escape_string($mysqli, $_POST['from_name']) : null;
	
	//from_email
	$from_email = isset($_POST['from_email']) ? mysqli_real_escape_string($mysqli, $_POST['from_email']) : null;
	
	//reply_to
	$reply_to = isset($_POST['reply_to']) ? mysqli_real_escape_string($mysqli, $_POST['reply_to']) : null;
	
	//title
	$title = isset($_POST['title']) ? mysqli_real_escape_string($mysqli, $_POST['title']) : null;
	
	//subject
	$subject = isset($_POST['subject']) ? mysqli_real_escape_string($mysqli, $_POST['subject']) : null;
	
	//plain_text
	$plain_text = isset($_POST['plain_text']) ? mysqli_real_escape_string($mysqli, $_POST['plain_text']) : null;
	
	//html_text
	$html_text = isset($_POST['html_text']) ? mysqli_real_escape_string($mysqli, $_POST['html_text']) : null;
	
	//list_ids (comma separated)
	$list_ids = isset($_POST['list_ids']) ? mysqli_real_escape_string($mysqli, $_POST['list_ids']) : null;
	
	//segment_ids (comma separated)
	$segment_ids = isset($_POST['segment_ids']) ? mysqli_real_escape_string($mysqli, $_POST['segment_ids']) : null;
	
	//exclude_list_ids (comma separated)
	$exclude_list_ids = isset($_POST['exclude_list_ids']) ? mysqli_real_escape_string($mysqli, $_POST['exclude_list_ids']) : null;
	
	//exclude_segments_ids (comma separated)
	$exclude_segments_ids = isset($_POST['exclude_segments_ids']) ? mysqli_real_escape_string($mysqli, $_POST['exclude_segments_ids']) : null;
	
	//send_campaign (1 or 0)
	$send_campaign = isset($_POST['send_campaign']) ? mysqli_real_escape_string($mysqli, $_POST['send_campaign']) : 0;
	
	//brand_id (requierd if send_campaign is set to 0)
	$app = isset($_POST['brand_id']) && is_numeric($_POST['brand_id']) ? mysqli_real_escape_string($mysqli, (int)$_POST['brand_id']) : null;
	
	//query_string
	$query_string = isset($_POST['query_string']) ? mysqli_real_escape_string($mysqli, $_POST['query_string']) : null;
	
	//query_string
	$json = isset($_POST['json']) ? mysqli_real_escape_string($mysqli, $_POST['json']) : 0;
	//-----------------------------------------------------------//
	
	//----------------------- VERIFICATION ----------------------//
	//Core data
	if($api_key==null && $from_name==null && $from_email==null && $reply_to==null && $subject==null && $plain_text==null && $html_text==null && $list_ids==null)
	{
		echo $error_core[0];
		exit;
	}
	if($api_key==null)
	{
		echo $error_core[1];
		exit;
	}
	else if(!verify_api_key($api_key))
	{
		echo $error_core[2];
		exit;
	}
	
	//Passed data
	if($from_name==null)
	{
		echo $error_passed[0];
		exit;
	}
	else if($from_email==null)
	{
		echo $error_passed[1];
		exit;
	}
	else if($reply_to==null)
	{
		echo $error_passed[2];
		exit;
	}
	else if($subject==null)
	{
		echo $error_passed[3];
		exit;
	}
	else if($html_text==null)
	{
		echo $error_passed[4];
		exit;
	}
	else if($send_campaign && $list_ids==null)
	{		
		if($segment_ids==null)
		{
			echo $error_passed[5];
			exit;
		}
	}
	
	if(!$send_campaign && $app==null)
	{
		echo $error_passed[8];
		exit;
	}
	else
	{
		if($send_campaign)
		{
			$brand_id_array = array();
			
			//Check if all lists passed into the API exists, else throw error
			if($list_ids != null)
			{
				$list_id = explode(',', $list_ids);
				foreach($list_id as $listid)
				{
					$listid = trim(short($listid,true));
					$q = 'SELECT app FROM lists WHERE id = '.$listid;
					$r = mysqli_query($mysqli, $q);
					if (mysqli_num_rows($r) == 0) 
					{
						echo $error_passed[6]; 
						exit;
					}
					else while($row = mysqli_fetch_array($r)) array_push($brand_id_array, $row['app']);
				}
			}
			
			//Check if all segment ids passed into the API exists, else throw error
			if($segment_ids != null)
			{
				$seg_id = explode(',', $segment_ids);
				foreach($seg_id as $segid)
				{
					$segid = trim($segid);
					$q = 'SELECT app FROM seg WHERE id = '.$segid;
					$r = mysqli_query($mysqli, $q);
					if (mysqli_num_rows($r) == 0) 
					{
						echo $error_passed[9]; 
						exit;
					}
					else while($row = mysqli_fetch_array($r)) array_push($brand_id_array, $row['app']);
				}
			}
			
			//Check if all exclude lists passed into the API exists, else throw error
			if($exclude_list_ids != null)
			{
				$exclude_list_id = explode(',', $exclude_list_ids);
				foreach($exclude_list_id as $excludelistid)
				{
					$excludelistid = trim(short($excludelistid,true));
					$q = 'SELECT app FROM lists WHERE id = '.$excludelistid;
					$r = mysqli_query($mysqli, $q);
					if (mysqli_num_rows($r) == 0) 
					{
						echo $error_passed[6]; 
						exit;
					}
					else while($row = mysqli_fetch_array($r)) array_push($brand_id_array, $row['app']);
				}
			}
			
			//Check if all exclude segment ids passed into the API exists, else throw error
			if($exclude_segments_ids != null)
			{
				$exclude_seg_id = explode(',', $exclude_segments_ids);
				foreach($exclude_seg_id as $excludesegid)
				{
					$excludesegid = trim($excludesegid);
					$q = 'SELECT app FROM seg WHERE id = '.$excludesegid;
					$r = mysqli_query($mysqli, $q);
					if (mysqli_num_rows($r) == 0) 
					{
						echo $error_passed[9]; 
						exit;
					}
					else while($row = mysqli_fetch_array($r)) array_push($brand_id_array, $row['app']);
				}
			}
			
			//Check if all list IDs belong to the same brand, else throw error
			if(count(array_unique($brand_id_array)) != 1)
			{
				echo $error_passed[7];
				exit;
			}
			else
			{
				$app = $brand_id_array[0];
			}
		}
	}
	//-----------------------------------------------------------//
	
	//-------------------------- QUERY --------------------------//
	
	//Get userID
	$q = 'SELECT id FROM login ORDER BY id ASC LIMIT 1';
	$r = mysqli_query($mysqli, $q);
	if ($r) while($row = mysqli_fetch_array($r)) $userID = $row['id'];
	
	if($send_campaign)
	{
		//Set send time
		$sent = time();
		
		if($list_ids != null)
		{
			//Get list IDs
			foreach($list_id as $listid)
			{
				$listids .= trim(short($listid,true)).',';
			}
			$listids = substr($listids, 0, -1);
		}
		
		if($exclude_list_ids != null)
		{
			//Get list IDs
			foreach($exclude_list_id as $excludelistid)
			{
				$excludelistids .= trim(short($excludelistid,true)).',';
			}
			$excludelistids = substr($excludelistids, 0, -1);
		}
		
		//Get number of recipients to send to
		
		//Include main list query
		$main_query = $list_ids == null ? '' : 'subscribers.list in ('.$listids.') ';

		//Include segmentation query
		$seg_query = $main_query != '' && $segment_ids != null ? 'OR ' : ''; 
		$seg_query .= $segment_ids == null ? '' : '(subscribers_seg.seg_id IN ('.$segment_ids.')) ';
		
		//Exclude list query
		$exclude_query = $exclude_list_ids == null ? '' : 'subscribers.email NOT IN (SELECT email FROM subscribers WHERE list IN ('.$excludelistids.')) ';
		
		//Exclude segmentation query
		$exclude_seg_query = $exclude_query != '' && $exclude_segments_ids != null ? 'AND ' : ''; 
		$exclude_seg_query .= $exclude_segments_ids == null ? '' : 'subscribers.email NOT IN (SELECT subscribers.email FROM subscribers LEFT JOIN subscribers_seg ON (subscribers.id = subscribers_seg.subscriber_id) WHERE subscribers_seg.seg_id IN ('.$exclude_segments_ids.'))';
		
		//Check if we should send to GDPR subscribers only
		if($list_ids!=null) $q = 'SELECT gdpr_only FROM apps LEFT JOIN lists ON (apps.id = lists.app) WHERE lists.id IN ('.$listids.') LIMIT 1';
		else $q = 'SELECT gdpr_only FROM apps LEFT JOIN seg ON (apps.id = seg.app) WHERE seg.id IN ('.$segment_ids.') LIMIT 1';
		$r = mysqli_query($mysqli, $q);
		if ($r) while($row = mysqli_fetch_array($r)) $gdpr_only = $row['gdpr_only'];
		$gdpr_line = $gdpr_only ? 'AND gdpr = 1 ' : '';
	
		//Get totals from lists
		$q  = 'SELECT 1 FROM subscribers';
		$q .= $segment_ids==null && $exclude_segments_ids==null ? ' ' : ' LEFT JOIN subscribers_seg ON (subscribers.id = subscribers_seg.subscriber_id) ';
		$q .= 'WHERE ('.$main_query.$seg_query.') ';
		$q .= $exclude_query != '' || $exclude_seg_query != '' ? 'AND ('.$exclude_query.$exclude_seg_query.') ' : '';
		$q .= 'AND subscribers.unsubscribed = 0 AND subscribers.bounced = 0 AND subscribers.complaint = 0 AND subscribers.confirmed = 1 '.$gdpr_line.'
			   GROUP BY subscribers.email';
		$r = mysqli_query($mysqli, $q);
		if ($r)
		{
		    $to_send = mysqli_num_rows($r);
		}
		else 
		{
			echo 'Unable to calculate totals';
			exit;
		}

		
		//Create and send campaign
		$q2 = 'INSERT INTO campaigns (userID, app, from_name, from_email, reply_to, title, label, plain_text, html_text, wysiwyg, sent, to_send, send_date, lists, segs, lists_excl, segs_excl, timezone, query_string) VALUES ('.$userID.', '.$app.', "'.$from_name.'", "'.$from_email.'", "'.$reply_to.'", "'.$subject.'", "'.$title.'", "'.$plain_text.'", "'.$html_text.'", 1, "'.$sent.'", '.$to_send.', 0, "'.$listids.'", "'.$segment_ids.'", "'.$excludelistids.'", "'.$exclude_segments_ids.'", 0, "'.$query_string.'")';
		$r2 = mysqli_query($mysqli, $q2);
		if ($r2) 
		{
			echo 'Campaign created and now sending';
			
			//Check if monthly quota needs to be updated
			$q = 'SELECT allocated_quota, current_quota FROM apps WHERE id = '.$app;
			$r = mysqli_query($mysqli, $q);
			if($r) 
			{
				while($row = mysqli_fetch_array($r)) 
				{
					$allocated_quota = $row['allocated_quota'];
					$current_quota = $row['current_quota'];
					$updated_quota = $current_quota + $to_send;
				}
			}
			//Update quota if a monthly limit was set
			if($allocated_quota!=-1)
			{
				//if so, update quota
				$q = 'UPDATE apps SET current_quota = '.$updated_quota.' WHERE id = '.$app;
				mysqli_query($mysqli, $q);
			}
		}
		else echo 'Unable to create and send campaign';
		exit;
	}
	else
	{
		//Create draft
		$q2 = 'INSERT INTO campaigns (userID, app, from_name, from_email, reply_to, title, label, plain_text, html_text, wysiwyg, query_string) VALUES ('.$userID.', '.$app.', "'.$from_name.'", "'.$from_email.'", "'.$reply_to.'", "'.$subject.'", "'.$title.'", "'.$plain_text.'", "'.$html_text.'", 1, "'.$query_string.'")';
		$r2 = mysqli_query($mysqli, $q2);
		if ($r2) 
		{
			//if asked for JSON response
			if($json)
			{
				$campaign_id = mysqli_insert_id($mysqli);
				echo '{"status":"Campaign created", "campaign_id": "'.$campaign_id.'"}';
			}
			//Otherwise return plain text response by default
			else echo 'Campaign created';
		}
		else echo 'Unable to create campaign';
		exit;
	}
	//-----------------------------------------------------------//
?>