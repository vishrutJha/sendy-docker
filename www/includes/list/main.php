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
	function get_lists_data($val, $lid)
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT '.$val.' FROM lists WHERE app = "'.get_app_info('app').'" AND id = '.$lid.' AND userID = '.get_app_info('main_userID');
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				return stripslashes($row[$val]);
		    }  
		}
	}
	
	//------------------------------------------------------//
	function get_lists($except_list_id)
	//------------------------------------------------------//
	{
		global $mysqli;
		
		//Get list sorting preference
		$q = 'SELECT templates_lists_sorting FROM apps WHERE id = '.get_app_info('app');
		$r = mysqli_query($mysqli, $q);
		if ($r) while($row = mysqli_fetch_array($r)) $templates_lists_sorting = $row['templates_lists_sorting'];
		$sortby = $templates_lists_sorting=='date' ? 'id DESC' : 'name ASC';
		
		//Get lists from the brand
		$q = 'SELECT id, name FROM lists WHERE app = '.get_app_info('app').' AND id NOT IN '.$except_list_id.' AND userID = '.get_app_info('main_userID').' ORDER BY '.$sortby;
		$r = mysqli_query($mysqli, $q);
		if ($r)
		{
			$options = '';
			
		    while($row = mysqli_fetch_array($r))
		    {
				$id = $row['id'];
				$name = $row['name'];
				
				$options .= '<option value="'.$id.'">'.$name.'</option>';
		    }  
		    
		    return $options;
		}
	}
	
	//------------------------------------------------------//
	function get_subscribers_count($lid)
	//------------------------------------------------------//
	{
		global $mysqli;
		
		//Check if the list has a pending CSV for importing via cron
		$server_path_array = explode('list.php', $_SERVER['SCRIPT_FILENAME']);
		$server_path = $server_path_array[0];
		
		if (file_exists($server_path.'uploads/csvs') && $handle = opendir($server_path.'uploads/csvs')) 
		{
		    while (false !== ($file = readdir($handle))) 
		    {
		    	if($file!='.' && $file!='..' && $file!='.DS_Store' && $file!='.svn')
		    	{
			    	$file_array = explode('-', $file);
			    	
			    	if(!empty($file_array))
			    	{
				    	if(str_replace('.csv', '', $file_array[1])==$lid)
					    	return _('Checking..').'
					    		<script type="text/javascript">
					    			$(document).ready(function() {
					    			
					    				list_interval = setInterval(function(){get_list_count('.$lid.')}, 2000);
						    			
						    			function get_list_count(lid)
						    			{
						    				clearInterval(list_interval);
							    			$.post("includes/list/progress.php", { list_id: lid, user_id: '.get_app_info('main_userID').' },
											  function(data) {
											      if(data)
											      {
											      	if(data.indexOf("%)") != -1)
											      		list_interval = setInterval(function(){get_list_count('.$lid.')}, 2000);
											      		
											      	$("#progress'.$lid.'").html(data);
											      }
											      else
											      {
											      	$("#progress'.$lid.'").html("'._('Error retrieving count').'");
											      }
											  }
											);
										}
										
						    		});
					    		</script>';
			    	}
			    }
		    }
		    closedir($handle);
		}
		
		//if not, just return the subscriber count
		$q = 'SELECT COUNT(list) FROM subscribers use index (s_list) WHERE list = '.$lid.' AND unsubscribed = 0 AND bounced = 0 AND complaint = 0 AND confirmed = 1';
		$r = mysqli_query($mysqli, $q);
		if ($r)
		{
			while($row = mysqli_fetch_array($r))
		    {
				return number_format($row['COUNT(list)']);
		    } 
		}
	}
	
	//------------------------------------------------------//
	function get_unsubscribers_count($lid)
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT COUNT(list) FROM subscribers use index (s_list) WHERE list = '.$lid.' AND unsubscribed = 1 AND bounced = 0';
		$r = mysqli_query($mysqli, $q);
		if ($r) while($row = mysqli_fetch_array($r)) return number_format($row['COUNT(list)']);
	}
	
	//------------------------------------------------------//
	function get_unsubscribers_percentage($subscribers, $unsubscribers)
	//------------------------------------------------------//
	{
		$subscribers = str_replace(',', '', $subscribers);
		$unsubscribers = str_replace(',', '', $unsubscribers);
		$sub_unsub_total = $subscribers+$unsubscribers;
		$unsub_percentage = $sub_unsub_total==0 ? round($unsubscribers * 100, 2) : round($unsubscribers / ($sub_unsub_total) * 100, 2);
		return $unsub_percentage;
	}
	
	//------------------------------------------------------//
	function get_bounced_count($lid)
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT COUNT(list) FROM subscribers use index (s_list) WHERE list = '.$lid.' AND bounced = 1';
		$r = mysqli_query($mysqli, $q);
		if ($r) while($row = mysqli_fetch_array($r)) return number_format($row['COUNT(list)']);
	}
	
	//------------------------------------------------------//
	function get_bounced_percentage($bouncers, $subscribers)
	//------------------------------------------------------//
	{
		$subscribers = str_replace(',', '', $subscribers);
		$bouncers = str_replace(',', '', $bouncers);
		$bounce_subs_total = $subscribers+$bouncers;
		$bounce_percentage = $bounce_subs_total==0 ? round($bouncers * 100, 2) : round($bouncers / ($bounce_subs_total) * 100, 2);
		return $bounce_percentage;
	}
	
	//------------------------------------------------------//
	function get_segment_count($lid)
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT COUNT(id) FROM seg WHERE list = '.$lid;
		$r = mysqli_query($mysqli, $q);
		if ($r) while($row = mysqli_fetch_array($r)) return $row['COUNT(id)'];
	}
	
	//------------------------------------------------------//
	function get_ar_count($lid)
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT COUNT(id) FROM ares WHERE list = '.$lid;
		$r = mysqli_query($mysqli, $q);
		if ($r) while($row = mysqli_fetch_array($r)) return $row['COUNT(id)'];
	}
	
	//------------------------------------------------------//
	function get_gdpr_count($lid)
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT COUNT(id) FROM subscribers use index (s_list) WHERE unsubscribed = 0 AND bounced = 0 AND complaint = 0 AND confirmed = 1 AND gdpr = 1 AND list = '.$lid;
		$r = mysqli_query($mysqli, $q);
		if ($r) while($row = mysqli_fetch_array($r)) return number_format($row['COUNT(id)']);
	}
	
	//------------------------------------------------------//
	function get_gdpr_percentage($lid, $gdpr_subs)
	//------------------------------------------------------//
	{
		global $mysqli;
		
		//Get subscriber count
		$q = "SELECT COUNT(*) FROM subscribers WHERE list = '$lid' AND unsubscribed = 0 AND bounced = 0 AND complaint = 0 AND confirmed = 1";
		$r = mysqli_query($mysqli, $q);
		if ($r) while($row = mysqli_fetch_array($r)) $subscribers = $row['COUNT(*)'];
		
		$subscribers = str_replace(',', '', $subscribers);
		$gdpr_subs = str_replace(',', '', $gdpr_subs);
		$gdpr_percentage = $gdpr_subs==0 ? 0 : round($gdpr_subs / ($subscribers) * 100, 2);
		return $gdpr_percentage;
	}
	
	//------------------------------------------------------//
	function has_gdpr_subscribers()
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT COUNT(subscribers.gdpr) as gdpr_subs_no FROM subscribers, lists, apps WHERE subscribers.list = lists.id AND lists.app = apps.id AND subscribers.gdpr = 1 AND apps.id = '.get_app_info('app');
		$r = mysqli_query($mysqli, $q);
		if ($r) while($row = mysqli_fetch_array($r)) $gdpr_subs_no = $row['gdpr_subs_no'];
		if($gdpr_subs_no > 0) return true;
		else return false;
	}
	
	//------------------------------------------------------//
	function totals($app)
	//------------------------------------------------------//
	{
		global $mysqli;
			
		$q = 'SELECT id FROM lists WHERE app = '.$app.' AND userID = '.get_app_info('main_userID');
		$r = mysqli_query($mysqli, $q);
		if ($r) return mysqli_num_rows($r);
	}
	
	//------------------------------------------------------//
	function pagination($limit)
	//------------------------------------------------------//
	{		
		global $p;
		
		$curpage = $p;
		
		$next_page_num = 0;
		$prev_page_num = 0;
		
		$total_lists = totals(get_app_info('app'));
		$total_pages = @ceil($total_lists/$limit);
		
		if($total_lists > $limit)
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
					echo '<button class="btn" onclick="window.location=\''.get_app_info('path').'/list?i='.get_app_info('app').'\'"><span class="icon icon icon-arrow-left"></span></button>';
				else
					echo '<button class="btn" onclick="window.location=\''.get_app_info('path').'/list?i='.get_app_info('app').'&p='.$prev_page_num.'\'"><span class="icon icon icon-arrow-left"></span></button>';
			else
				echo '<button class="btn disabled"><span class="icon icon icon-arrow-left"></span></button>';
			
			//Next btn
			if($curpage==$total_pages)
				echo '<button class="btn disabled"><span class="icon icon icon-arrow-right"></span></button>';
			else
				echo '<button class="btn" onclick="window.location=\''.get_app_info('path').'/list?i='.get_app_info('app').'&p='.$next_page_num.'\'"><span class="icon icon icon-arrow-right"></span></button>';
					
			echo '</div>';
		}
	}
?>