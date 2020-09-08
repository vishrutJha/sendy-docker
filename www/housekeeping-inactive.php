<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/subscribers/main.php');?>
<?php include('includes/subscribers/housekeeping.php');?>

<?php 	
	if(get_app_info('is_sub_user')) 
	{
		if(get_app_info('app')!=get_app_info('restricted_to_app'))
		{
			echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/housekeeping-inactive?i='.get_app_info('restricted_to_app').'"</script>';
			exit;
		}
		else if(get_app_info('campaigns_only')==1 && get_app_info('templates_only')==1 && get_app_info('lists_only')==1 && get_app_info('reports_only')==1)
		{
			echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/logout"</script>';
			exit;
		}
		else if(get_app_info('lists_only')==1)
		{
			go_to_next_allowed_section();
		}
	}
	
	//vars
	if(isset($_GET['p'])) $p = is_numeric($_GET['p']) ? $_GET['p'] : exit;
?>

<div class="row-fluid">
    <div class="span2">
        <?php include('includes/sidebar.php');?>
    </div> 
    <div class="span10">
    	<div class="row-fluid">
	    	<div class="span12">
		    	<div>
			    	<p class="lead">
		    	<?php if(get_app_info('is_sub_user')):?>
			    	<?php echo get_app_data('app_name');?>
		    	<?php else:?>
			    	<a href="<?php echo get_app_info('path'); ?>/edit-brand?i=<?php echo get_app_info('app');?>" data-placement="right" title="<?php echo _('Edit brand settings');?>"><?php echo get_app_data('app_name');?></a>
		    	<?php endif;?>
		    </p>
		    	</div>
		    	<h2><?php echo _('Housekeeping');?></h2>
				<br/>
		    	<div class="well">
			    	<div class="btn-group" data-toggle="buttons-radio">
					  <a href="<?php echo get_app_info('path');?>/housekeeping-unconfirmed?i=<?php echo get_app_info('app');?>" title="" class="btn"><i class="icon icon-meh"></i> <?php echo _('Unconfirmed subscribers');?></a>
					  <a href="javascript:void(0)" title="" class="btn active"><i class="icon icon-moon"></i> <?php echo _('Inactive subscribers');?></a>
					</div>
		    	</div>
		    	<div class="alert alert-info">
					<p><i class="icon icon-info-sign"></i> <?php echo _('Housekeeping for \'Inactive subscribers\' allows you to bulk remove subscribers who did not open or click any campaigns that you have ever sent to them.');?></p>
				</div>
	    	</div>
	    </div>
	    
	    <br/>
	    
	    <div class="row-fluid">
		    <div class="span12">
		    	<h3><?php echo _('Inactive subscribers');?></h3><hr/>
				<table class="table table-striped responsive">
	              <thead>
	                <tr>
					  <?php 
						$lists = '';
						
						//Get lists that all existing campaigns were sent to
						$q = 'SELECT to_send_lists FROM campaigns WHERE app = '.get_app_info('app').' AND to_send = recipients';
						$r = mysqli_query($mysqli, $q);
						if ($r && mysqli_num_rows($r) > 0)
						{
						  while($row = mysqli_fetch_array($r))
						  {
								$to_send_lists = $row['to_send_lists'];
								$lists .= $to_send_lists!='' ? $to_send_lists.',' : '';
						  }  
						}
						
						//Get lists from segments that all existing campaigns were sent to
						$q = 'SELECT seg.list FROM seg LEFT JOIN campaigns ON (seg.id IN (campaigns.segs)) WHERE campaigns.app = '.get_app_info('app').' AND campaigns.segs!="" AND campaigns.to_send = campaigns.recipients';
						$r = mysqli_query($mysqli, $q);
						if ($r && mysqli_num_rows($r) > 0)
						{
						    while($row = mysqli_fetch_array($r))
						    {
								$seg_list = $row['list'];
								$lists .= $seg_list!='' ? $seg_list.',' : '';
						    }  
						}
						
						$lists = substr($lists, 0, -1);
						$lists_explode = explode(',', $lists);
						$lists_array = array_unique($lists_explode);
						$lists_implode = implode(',', $lists_array);
						
						//Load lists
						$total_lists = 0;
						$q = '  SELECT lists.id, lists.name FROM lists 
								LEFT JOIN campaigns ON (lists.id IN ('.$lists_implode.')) 
								WHERE campaigns.app = '.get_app_info('app').' AND campaigns.to_send = campaigns.recipients 
								GROUP BY lists.id 
								ORDER BY name ASC';
						$r = mysqli_query($mysqli, $q);
						if (mysqli_num_rows($r) != 0):
						$total_lists = mysqli_num_rows($r);
					  ?>
	                  <th><?php echo _('List');?></th>
	                  <th><?php echo _('Status');?></th>
	                  <th><?php echo _('Did not open');?></th>
	                  <th><?php echo _('Did not click');?></th>
	                  <?php endif;?>
	                </tr>
	              </thead>
	              <tbody>
	                	<?php 	
		                	$limit = 10;
							$total_pages = ceil($total_lists/$limit);
							$offset = $p!=null ? ($p-1) * $limit : 0;
		                		                	
		                	$q = 'SELECT lists.id, lists.name FROM lists 
								LEFT JOIN campaigns ON (lists.id IN ('.$lists_implode.')) 
								WHERE campaigns.app = '.get_app_info('app').' AND campaigns.to_send = campaigns.recipients 
								GROUP BY lists.id 
								ORDER BY name ASC 
								LIMIT '.$offset.','.$limit;
		                	$r = mysqli_query($mysqli, $q);
		                	if ($r && mysqli_num_rows($r) > 0)
		                	{
		                	    while($row = mysqli_fetch_array($r))
		                	    {
		                			$lid = $row['id'];
		                			$list_name = $row['name'];
		                			
		                			$subscriber_count_notopened = '<span id="count-'.$lid.'-notopened"><img src="'.get_app_info('path').'/img/loader.gif" style="width:16px;"/></span>';
		                			$subscriber_count_notclicked = '<span id="count-'.$lid.'-notclicked"><img src="'.get_app_info('path').'/img/loader.gif" style="width:16px;"/></span>';
		                			
		                			echo '
		                			<tr id="uc-'.$lid.'">
			                			<td><a href="'.get_app_info('path').'/subscribers?i='.get_app_info('app').'&l='.$lid.'">'.$list_name.' <span class="badge badge-success" id="total-'.$lid.'">'.get_totals('a', '', $lid).'</span></a></td>
			                			<td><span class="label">'._('Inactive').'</span></td>
			                			<td>
			                				'.$subscriber_count_notopened.'
			                			</td>
			                			<td>
			                				'.$subscriber_count_notclicked.'
			                			</td>
			                			
			                			<script type="text/javascript">
			                			$(document).ready(function() {
				                			
				                			//Get no opens and clicks figures
				                			$.post("'.get_app_info('path').'/includes/subscribers/housekeeping-no-opens.php", { lid: '.$lid.', app: '.get_app_info('app').' }, function(data) { 
					                			if(data) 
					                			{
						                			$("#count-'.$lid.'-notopened").text(data); 
						                			if(data!=0)
						                			{
							                			$("#count-'.$lid.'-notopened").before("<a href=\"javascript:void(0)\" title=\"'._('Remove subscribers who did not open any campaign ever sent to them?').'\" id=\"delete-btn-'.$lid.'-notopened\" class=\"delete-list\"><i class=\"icon icon-trash\"></i></a> ");
							                			$("#delete-btn-'.$lid.'-notopened").click(function(e){
												        c = "'._('Confirm permanently remove subscribers?').'"
												        if(confirm(c))
												        {
													        $("#count-'.$lid.'-notopened").html("<img src=\''.get_app_info('path').'/img/loader.gif\' style=\'width:16px;\'/>");
													        e.preventDefault(); 
															$.post("includes/subscribers/delete-inactive.php", { lid: '.$lid.', app: '.get_app_info('app').', action: "1"},
															  function(data) {
															      if(!data)
															      	alert("'._('Sorry, unable to remove subscribers. Please try again later!').'");
															      else
															      {
																    data_array = data.split(":");
															      	$("#count-'.$lid.'-notopened").html("0");
															      	$("#total-'.$lid.'").html(data_array[0]);
															      	$("#count-'.$lid.'-notclicked").html(data_array[1]);
															      	$("#delete-btn-'.$lid.'-notopened").remove();
															      	if(data_array[1]==0)
																      	$("#delete-btn-'.$lid.'-notclicked").remove();
															      }
															      	
															  }
															);
														}
													});
							                		}
					                			}
				                			});
				                			$.post("'.get_app_info('path').'/includes/subscribers/housekeeping-no-clicks.php", { lid: '.$lid.', app: '.get_app_info('app').' }, function(data) { 
				                				if(data) 
				                				{
				                					$("#count-'.$lid.'-notclicked").text(data); 
				                					if(data!=0)
				                					{
					                					$("#count-'.$lid.'-notclicked").before("<a href=\"javascript:void(0)\" title=\"'._('Remove subscribers who did not click any links from any campaign ever sent to them?').'\" id=\"delete-btn-'.$lid.'-notclicked\" class=\"delete-list\"><i class=\"icon icon-trash\"></i></a> "); 
					                					$("#delete-btn-'.$lid.'-notclicked").click(function(e){
												        c = "'._('Confirm permanently remove subscribers?').'"
												        if(confirm(c))
												        {
													        $("#count-'.$lid.'-notclicked").html("<img src=\''.get_app_info('path').'/img/loader.gif\' style=\'width:16px;\'/>");
													        e.preventDefault(); 
															$.post("includes/subscribers/delete-inactive.php", { lid: '.$lid.', app: '.get_app_info('app').', action: "2"},
															  function(data) {
															      if(!data)
															      	alert("'._('Sorry, unable to remove subscribers. Please try again later!').'");
															      else
															      {
															      	data_array = data.split(":");
															      	$("#count-'.$lid.'-notclicked").html("0");
															      	$("#total-'.$lid.'").html(data_array[0]);
															      	$("#count-'.$lid.'-notopened").html(data_array[1]);
															      	$("#delete-btn-'.$lid.'-notclicked").remove();
															      	if(data_array[1]==0)
																      	$("#delete-btn-'.$lid.'-notopened").remove();
															      }
															  }
															);
														}
													});
					                				}
				                				}
				                			});
										});
										</script>
			                			
		                			</tr>
		                			';
		                	    }  
		                	}	
		                	else
		                	{
			                	echo '
			                	<tr>
			                		<td colspan="5">'._('No housekeeping needed as no campaigns were sent to any of your available lists yet.').'</td>
			                	</tr>
			                	';
		                	}
	                	?>                
	              </tbody>
	            </table>
	            <?php pagination_housekeeping('inactive', $total_lists, $limit, get_app_info('app'));?>
			</div>
	    </div>
    </div>
</div>

<?php include('includes/footer.php');?>
