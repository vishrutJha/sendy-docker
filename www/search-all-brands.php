<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/subscribers/main.php');?>
<?php include('includes/helpers/short.php');?>
<?php if(isset($_GET['s'])) $s = trim($_GET['s']);?>
<div class="row-fluid">
    <div class="span2">
        <div class="sidebar-nav sidebar-box" style="padding: 19px;">
			<h3><?php echo _('Amazon SES Quota');?></h3><br/>
			<?php include('includes/helpers/ses-quota.php');?>
		</div>
    </div> 
    <div class="span10">
    	<h2><?php echo _('Search brands');?></h2> <br/>
		
		<div class="well">
			<p><?php echo _('Keyword');?>: <span class="label label-info"><?php echo htmlentities($s);?></span> | Results <span class="label label-info" id="results-count"></span> <a href="<?php echo get_app_info('path')?>" title="" style="float:right;"><i class="icon icon-double-angle-left"></i> <?php echo _('Back to brands');?></a></p>
		</div>
		
		<br/>
		
	    <table class="table table-striped responsive">
		  <thead>
		    <tr>
		      <th><?php echo _('ID');?></th>
		      <th><?php echo _('Brands');?></th>
		      <th><?php echo _('Sending limits');?></th>
		      <th><?php echo _('Used');?></th>
		      <th><?php echo _('Edit');?></th>
		      <th><?php echo _('Delete');?></th>
		    </tr>
		  </thead>
		  <tbody>		  	
		  	<?php		  		
		  		$q = 'SELECT * FROM apps WHERE userID = '.get_app_info('userID').' AND app_name LIKE "%'.$s.'%" ORDER BY app_name ASC';
			  	$r = mysqli_query($mysqli, $q);
			  	$number_of_results = mysqli_num_rows($r);
			  	echo '
			  	<script type="text/javascript">
			  	$(document).ready(function() {
			  		$("#results-count").text("'.$number_of_results.'");
			  		$(".brand-id").mouseover(function(){
						$(this).selectText();
					});
			  	});
			    </script>
			  	';
			  	if ($r && $number_of_results > 0)
			  	{
			  	    while($row = mysqli_fetch_array($r))
			  	    {
			  			$id = $row['id'];
			  			$title = $row['app_name'];
			  			$from_email = explode('@', $row['from_email']);
			  			$get_domain = $from_email[1];
			  			$allocated_quota = $row['allocated_quota'];
			  			$current_quota = $row['current_quota'];
			  			$day_of_reset = $row['day_of_reset'];
			  			$month_of_next_reset = $row['month_of_next_reset'];
			  			$year_of_next_reset = $row['year_of_next_reset'];
			  			$brand_logo_filename = $row['brand_logo_filename'];
			  			$no_expiry = $row['no_expiry'];
			  			
			  			//Brand logo
			  			if($brand_logo_filename=='') $logo_image = 'https://www.google.com/s2/favicons?domain='.$get_domain;
			  			else $logo_image = get_app_info('path').'/uploads/logos/'.$brand_logo_filename;
			  			
			  			//Check if limit needs to be reset	
						$today_unix_timestamp = time();
						$brand_monthly_quota = $allocated_quota;
						if($brand_monthly_quota!=-1)
						{				
							//Date today
							$day_today = strftime("%e", $today_unix_timestamp);
							$month_today = strftime("%b", $today_unix_timestamp);
							$year_today = strftime("%G", $today_unix_timestamp);
							
							//Find the number of the last day of this month
							$no_of_days_this_month = cal_days_in_month(CAL_GREGORIAN, strftime("%m", $today_unix_timestamp), $year_today);
							
							$brand_limit_resets_on = $day_of_reset>$no_of_days_this_month ? $no_of_days_this_month : $day_of_reset;
							
							//Get UNIX timestamp of 'date today' and 'date of next reset' for comparison
							$date_today_unix = strtotime($day_today.' '.$month_today.' '.$year_today);
							$date_on_reset_unix = strtotime($brand_limit_resets_on.' '.$month_of_next_reset.' '.$year_of_next_reset);
							
							//If date of reset has already passed today's date, reset current limit to 0
							if($date_today_unix>=$date_on_reset_unix)
							{
								//If today's 'day' is passed 'day_of_reset', +1 month for next reset's month
								if($day_today >= $brand_limit_resets_on) $plus_one_month = '+1 month';
								
								//Prepare day, month and year of next reset
								$month_next_unix = strtotime('1 '.$month_today.' '.$year_today.' '.$plus_one_month);
								$month_next = strftime("%b", $month_next_unix);
								$year_next = strftime("%G", $month_next_unix);
								
								//If brand limits is set to 'No expiry'
								if(!$no_expiry)
								{
									//Reset current limit to 0 and set the month_of_next_reset & year_of_next_reset to the next month
									$q2 = 'UPDATE apps SET current_quota = 0, month_of_next_reset = "'.$month_next.'", year_of_next_reset = "'.$year_next.'" WHERE id = '.$id;
									$r2 = mysqli_query($mysqli, $q2);
									if($r2) 
									{
										//Set $current_quota to 0 since current_quota has been reset
										$current_quota = 0;
									}
								}
							}
						}
			  			
			  			//Prepare numbers
			  			if($allocated_quota==-1) 
			  			{
			  				$allocated_quota = '<span style="font-size: 16px;color:#969696;">&infin;</span>';
			  				$current_quota = '<span style="font-size: 16px;color:#969696;">&infin;</span>';
			  				$limit_type = '';
			  			}
			  			else
			  			{
				  			$allocated_quota = number_format($allocated_quota);
			  				if($current_quota>$row['allocated_quota']) $current_quota = '<span style="color:#FF0000;font-weight:bold;">'.number_format($current_quota).'</span>';
			  				else $current_quota = number_format($current_quota);
			  				
			  				$limit_type = $no_expiry ? '<span class="badge">no expiry</span>' : '<span class="badge">monthly</span>';
			  			}
			  			
			  			echo '
			  			<tr id="'.$id.'">
			  				<td><span class="label brand-id">'.$id.'</span></td>
			  				<td><a href="'.get_app_info('path').'/app?i='.$id.'" title=""><img src="'.$logo_image.'" style="margin:-3px 5px 0 0; width:16px; height: 16px;"/>'.$title.'</a></td>
			  				<td>'.$allocated_quota.' '.$limit_type.'</td>
			  				<td>'.$current_quota.'</td>
			  				<td><a href="'.get_app_info('path').'/edit-brand?i='.$id.'" title=""><span class="icon icon-pencil"></span></a></td>
			  				<td><a href="#" title="'._('Delete').' '.$title.'" id="delete-btn-'.$id.'"><span class="icon icon-trash"></span></a></td>
			  				<script type="text/javascript">
					    	$("#delete-btn-'.$id.'").click(function(e){
							e.preventDefault(); 
							c = confirm("'._('All campaigns, lists, subscribers will be permanently deleted. Confirm delete').' '.$title.'?");
							if(c)
							{
								$.post("includes/app/delete.php", { id: '.$id.' },
								  function(data) {
								      if(data)
								      {
								      	$("#'.$id.'").fadeOut();
								      }
								      else
								      {
								      	alert("'._('Sorry, unable to delete. Please try again later!').'");
								      }
								  }
								);
							}
							});
						    </script>
			  			</tr>';
			  	    }  
			  	    
			  	    echo '</tbody>
						</table>
			  	    ';
			  	}
			  	else
			  	{
			  		echo '
			  			<tr>
			  				<td>'._('No brands found.').'</td>
			  				<td></td>
			  				<td></td>
			  				<td></td>
			  				<td></td>
			  				<td></td>
			  			</tr>
			  		';
			  	}
		  	?>
		    
		  </tbody>
		</table>
    </div>   
</div>

<?php include('includes/footer.php');?>
