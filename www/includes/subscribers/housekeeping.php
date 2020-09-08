<?php 
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	//------------------------------------------------------//
	function get_unconfirmed_total($lid, $period)
	//------------------------------------------------------//
	{
		global $mysqli;
		
		$oneweek = 604800;
		$twoweeks = 1209600;
		
		if($period==0) //All unconfirmed users from this list
			$q = 'SELECT COUNT(*) FROM subscribers WHERE list = '.$lid.' AND confirmed = 0 AND bounced = 0 AND complaint = 0';
		else if($period==1) //Unconfirmed for 1 week
			$q = 'SELECT COUNT(*) FROM subscribers WHERE list = '.$lid.' AND confirmed = 0 AND bounced = 0 AND complaint = 0 AND UNIX_TIMESTAMP() - timestamp < 604800';
		else if($period==2) //Unconfirmed for more than 1 week
			$q = 'SELECT COUNT(*) FROM subscribers WHERE list = '.$lid.' AND confirmed = 0 AND bounced = 0 AND complaint = 0 AND (UNIX_TIMESTAMP() - timestamp < 1209600 AND UNIX_TIMESTAMP() - timestamp > 604800)';
		else if($period==3) //Unconfirmed for more than 2 weeks
			$q = 'SELECT COUNT(*) FROM subscribers WHERE list = '.$lid.' AND confirmed = 0 AND bounced = 0 AND complaint = 0 AND UNIX_TIMESTAMP() - timestamp >= 1209600';
		$r = mysqli_query($mysqli, $q);
		if ($r) while($row = mysqli_fetch_array($r)) return number_format($row['COUNT(*)']);
	}
	
	//------------------------------------------------------//
	function get_notopen_total($lid, $app, $totals=true)
	//------------------------------------------------------//
	{
		global $mysqli;
		$opens = '';
		$o = '';
		$s = array();
		$total = 0;
		
		//--------------------------------------------------------------------------------------------------------//
		//Get the timestamp of the latest campaign sent to this list (with $lid) or any segments in this list
		//--------------------------------------------------------------------------------------------------------//
		
		//Check if `seg` table have any rows
		$q = 'SELECT COUNT(*) FROM seg';
		$r = mysqli_query($mysqli, $q);
		if ($r) while($row = mysqli_fetch_array($r)) $seg = $row['COUNT(*)'];
		
		if($seg==0)
			$q = 'SELECT sent, opens FROM campaigns WHERE FIND_IN_SET("'.$lid.'", to_send_lists) > 0 AND campaigns.app = '.$app.' 
				  GROUP BY campaigns.id
				  ORDER BY campaigns.sent ASC';
		else
			$q = 'SELECT campaigns.sent, campaigns.opens FROM seg 
				LEFT JOIN campaigns ON 
				(
					(FIND_IN_SET(seg.id, campaigns.segs) AND seg.list = '.$lid.') 
					OR 
					FIND_IN_SET("'.$lid.'", to_send_lists) > 0
				) 
				WHERE campaigns.app = '.$app.' 
				GROUP BY campaigns.id
				ORDER BY campaigns.sent ASC';
			
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {				
			    $opens .= $row['opens'].',';
				$latest_campaign_date = $row['sent'];
			}
		}
		$opens = substr($opens, 0, -1);
		preg_match_all('!\d+!', $opens, $matches_var);
		$opens_array_no_country = $matches_var[0];
		$o = array_unique($opens_array_no_country);
		
		//--------------------------------------------------------------------------------------------------------//
		// The following checks the combined 'opens' column data for each subscriber in the list to see if 
		// they exists inside, if they are not, they're inactive.
		//--------------------------------------------------------------------------------------------------------//
		
		//Get the total number of unopens
		$q2 = 'SELECT id FROM subscribers WHERE list = '.$lid.' AND subscribers.unsubscribed = 0 AND subscribers.bounced = 0 AND subscribers.complaint = 0 AND subscribers.confirmed = 1 AND timestamp <= "'.$latest_campaign_date.'"';
		$r2 = mysqli_query($mysqli, $q2);
		if ($r2 && mysqli_num_rows($r2) > 0)
		{
		    while($row = mysqli_fetch_array($r2))
		    {
			    $sid = $row['id'];
			    array_push($s, $sid);
		    }  
		}
		$opened = array_intersect($o,$s); //match opens ids array ($o) with subscriber ids ($s) array
		$unopens = array_diff($s, $opened); // remove opens ids ($opened) from subscriber ids ($s) array
		$total = count($unopens); //the rest of the subscriber ids didn't open, count the total
		
		//return either totals or list of subscriber ids (to delete subscribers for the latter)
		return $totals ? number_format($total) : implode(',', $unopens);
	}
	
	//------------------------------------------------------//
	function get_notclick_total($lid, $app, $totals=true)
	//------------------------------------------------------//
	{
		global $mysqli;
		$campaign_ids = '';
		$c = '';
		$s = array();
		$total = 0;
				
		//--------------------------------------------------------------------------------------------------------//
		//Get the timestamp of the latest campaign sent to this list (with $lid) or any segments in this list
		//--------------------------------------------------------------------------------------------------------//
		
		//Check if `seg` table have any rows
		$q = 'SELECT COUNT(*) FROM seg';
		$r = mysqli_query($mysqli, $q);
		if ($r) while($row = mysqli_fetch_array($r)) $seg = $row['COUNT(*)'];
		
		if($seg==0)
			$q = 'SELECT sent, opens FROM campaigns WHERE FIND_IN_SET("'.$lid.'", to_send_lists) > 0 AND campaigns.app = '.$app.' 
				  GROUP BY campaigns.id
				  ORDER BY campaigns.sent ASC';
		else
			$q = 'SELECT campaigns.id, campaigns.sent FROM seg 
				LEFT JOIN campaigns ON 
				(
					(FIND_IN_SET(seg.id, campaigns.segs) AND seg.list = '.$lid.') 
					OR 
					FIND_IN_SET("'.$lid.'", to_send_lists) > 0
				) 
				WHERE campaigns.app = '.$app.' 
				GROUP BY campaigns.id
				ORDER BY campaigns.sent ASC';
			
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
			    $campaign_ids .= $row['id'].',';
				$latest_campaign_date = $row['sent'];
			}
		}
		$campaign_ids = substr($campaign_ids, 0, -1);
		
		$q = 'SELECT clicks FROM links WHERE campaign_id IN ('.$campaign_ids.')';
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				$clicks .= $row['clicks'].',';
		    }  
		}
		$c = explode(',', substr($clicks, 0, -1));
		$c = array_unique($c);
		
		//--------------------------------------------------------------------------------------------------------//
		// The following checks the combined 'opens' column data for each subscriber in the list to see if 
		// they exists inside, if they are not, they're inactive.
		//--------------------------------------------------------------------------------------------------------//
		
		//Get the total number of unopens
		$q2 = 'SELECT id FROM subscribers WHERE list = '.$lid.' AND subscribers.unsubscribed = 0 AND subscribers.bounced = 0 AND subscribers.complaint = 0 AND subscribers.confirmed = 1 AND timestamp <= "'.$latest_campaign_date.'"';
		$r2 = mysqli_query($mysqli, $q2);
		if ($r2 && mysqli_num_rows($r2) > 0)
		{
		    while($row = mysqli_fetch_array($r2))
		    {
			    $sid = $row['id'];
			    array_push($s, $sid);
		    }  
		}
		$clicked = array_intersect($c,$s); //match clicks ids array ($c) with subscriber ids ($s) array
		$unclicks = array_diff($s, $clicked); // remove clicks ids ($clicked) from subscriber ids ($s) array
		$total = count($unclicks); //the rest of the subscriber ids didn't click, count the total
		
		//return either totals or list of subscriber ids (to delete subscribers for the latter)
		return $totals ? number_format($total) : implode(',', $unclicks);
	}
	
	//------------------------------------------------------//
	function suppression_list_total($app)
	//------------------------------------------------------//
	{
		global $mysqli;
		global $s;
		
		if($s!='')
			$s_more = ' AND email LIKE "%'.$s.'%"';
		else
			$s_more = '';
			
		$q = 'SELECT COUNT(*) FROM suppression_list WHERE app = '.$app.$s_more;
		$r = mysqli_query($mysqli, $q);
		if ($r) while($row = mysqli_fetch_array($r)) return $row['COUNT(*)'];
	}
	
	//------------------------------------------------------//
	function suppression_blocked_domain_list_total($app)
	//------------------------------------------------------//
	{
		global $mysqli;
		global $s;
		
		if($s!='')
			$s_more = ' AND domain LIKE "%'.$s.'%"';
		else
			$s_more = '';
			
		$q = 'SELECT COUNT(*) FROM blocked_domains WHERE app = '.$app.$s_more;
		$r = mysqli_query($mysqli, $q);
		if ($r) while($row = mysqli_fetch_array($r)) return $row['COUNT(*)'];
	}
	
	//------------------------------------------------------//
	function pagination_blacklist($type, $limit, $app)
	//------------------------------------------------------//
	{
		global $p;
		global $s;
		
		$curpage = $p;
		
		$next_page_num = 0;
		$prev_page_num = 0;
		
		$total_subs = $type=='suppression' ? suppression_list_total($app) : suppression_blocked_domain_list_total($app);;
		$total_pages = @ceil($total_subs/$limit);
		
		if($s!='')
			$s_more = '&s='.$s;
		else
			$s_more = '';
		
		if($total_subs > $limit)
		{
			if($curpage>=2)
			{
				$next_page_num = $curpage+1;
				$prev_page_num = $curpage-1;
			}
			else
			{
				$next_page_num = 2;
			}
		
			echo '<div class="btn-group" id="pagination">';
			
			//Prev btn
			if($curpage>=2)
				if($prev_page_num==1)
					echo '<button class="btn" onclick="window.location=\''.get_app_info('path').'/blacklist-'.$type.'?i='.get_app_info('app').$s_more.'\'"><span class="icon icon icon-arrow-left"></span></button>';
				else
					echo '<button class="btn" onclick="window.location=\''.get_app_info('path').'/blacklist-'.$type.'?i='.get_app_info('app').$s_more.'&p='.$prev_page_num.'\'"><span class="icon icon icon-arrow-left"></span></button>';
			else
				echo '<button class="btn disabled"><span class="icon icon icon-arrow-left"></span></button>';
			
			//Next btn
			if($curpage==$total_pages)
				echo '<button class="btn disabled"><span class="icon icon icon-arrow-right"></span></button>';
			else
				echo '<button class="btn" onclick="window.location=\''.get_app_info('path').'/blacklist-'.$type.'?i='.get_app_info('app').$s_more.'&p='.$next_page_num.'\'"><span class="icon icon icon-arrow-right"></span></button>';
					
			echo '</div>';
		}
	}
	
	//------------------------------------------------------//
	function pagination_housekeeping($type, $total_subs, $limit, $app)
	//------------------------------------------------------//
	{
		global $p;
		global $s;
		
		$curpage = $p;
		
		$next_page_num = 0;
		$prev_page_num = 0;
		
		$total_pages = @ceil($total_subs/$limit);
		
		if($total_subs > $limit)
		{
			if($curpage>=2)
			{
				$next_page_num = $curpage+1;
				$prev_page_num = $curpage-1;
			}
			else
			{
				$next_page_num = 2;
			}
		
			echo '<div class="btn-group" id="pagination">';
			
			//Prev btn
			if($curpage>=2)
				if($prev_page_num==1)
					echo '<button class="btn" onclick="window.location=\''.get_app_info('path').'/housekeeping-'.$type.'?i='.get_app_info('app').'\'"><span class="icon icon icon-arrow-left"></span></button>';
				else
					echo '<button class="btn" onclick="window.location=\''.get_app_info('path').'/housekeeping-'.$type.'?i='.get_app_info('app').'&p='.$prev_page_num.'\'"><span class="icon icon icon-arrow-left"></span></button>';
			else
				echo '<button class="btn disabled"><span class="icon icon icon-arrow-left"></span></button>';
			
			//Next btn
			if($curpage==$total_pages)
				echo '<button class="btn disabled"><span class="icon icon icon-arrow-right"></span></button>';
			else
				echo '<button class="btn" onclick="window.location=\''.get_app_info('path').'/housekeeping-'.$type.'?i='.get_app_info('app').'&p='.$next_page_num.'\'"><span class="icon icon icon-arrow-right"></span></button>';
					
			echo '</div>';
		}
	}
?>