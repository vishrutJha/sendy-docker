<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/subscribers/main.php');?>
<?php include('includes/subscribers/housekeeping.php');?>

<?php 	
	if(get_app_info('is_sub_user')) 
	{
		if(get_app_info('app')!=get_app_info('restricted_to_app'))
		{
			echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/housekeeping-unconfirmed?i='.get_app_info('restricted_to_app').'"</script>';
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
					  <a href="javascript:void(0)" title="" class="btn active"><i class="icon icon-meh"></i> <?php echo _('Unconfirmed subscribers');?></a>
					  <a href="<?php echo get_app_info('path');?>/housekeeping-inactive?i=<?php echo get_app_info('app');?>" title="" class="btn"><i class="icon icon-moon"></i> <?php echo _('Inactive subscribers');?></a>
					</div>
		    	</div>
		    	<div class="alert alert-info">
					<p><i class="icon icon-info-sign"></i> <?php echo _('Housekeeping for \'Unconfirmed subscribers\' allows you to bulk remove subscribers who signed up to double opt-in lists but did not click the confirmation link to confirm their subscription.');?></p>
				</div>
	    	</div>
	    </div>
	    
	    <br/>
	    
	    <div class="row-fluid">
		    <div class="span12">
		    	<h3><?php echo _('Unconfirmed subscribers');?></h3><hr/>
				<table class="table table-striped responsive">
	              <thead>
	                <tr>
		              <?php 
			              //Check if there are any double opt-in lists, if not, hide the table headers
			              $q = 'SELECT id FROM lists WHERE app = '.get_app_info('app').' AND opt_in = 1';
			              $r = mysqli_query($mysqli, $q);
			              if (mysqli_num_rows($r) != 0):
			              $total_lists = mysqli_num_rows($r);
		              ?>
		                  <th><?php echo _('List');?></th>
		                  <th><?php echo _('Status');?></th>
		                  <th><a href="javascript:void(0)" title="<?php echo _('Remove subscribers from all lists who did not confirm their subscription for 1 week?');?>" id="delete-btn-1week" class="delete-list"><i class="icon icon-trash"></i></a> <?php echo _('1 week');?></th>
		                  <th><a href="javascript:void(0)" title="<?php echo _('Remove subscribers from all lists who did not confirm their subscription for more than 1 week?');?>" id="delete-btn-1weekmore" class="delete-list"><i class="icon icon-trash"></i></a> <?php echo _('> 1 week');?></th>
		                  <th><a href="javascript:void(0)" title="<?php echo _('Remove subscribers from all lists who did not confirm their subscription for more than 2 weeks?');?>" id="delete-btn-2weeks" class="delete-list"><i class="icon icon-trash"></i></a> <?php echo _('> 2 weeks');?></th>
		                  <th><a href="javascript:void(0)" title="<?php echo _('Remove subscribers from all lists who did not confirm their subscription?');?>" id="delete-btn-all" class="delete-list"><i class="icon icon-trash"></i></a> <?php echo _('All');?></th>
		                  <th><?php echo _('Export');?></th>
		              <?php endif;?>
	                  
	                  <script type="text/javascript">
				        $("#delete-btn-1week, #delete-btn-1weekmore, #delete-btn-2weeks, #delete-btn-all").click(function(e){
					        c = "<?php echo _('Confirm permanently remove subscribers?');?>"
					        if(confirm(c))
					        {
						        e.preventDefault(); 
						        
						        var action = "";
						        if(e.currentTarget.id=="delete-btn-1week") action = "1";
						        else if(e.currentTarget.id=="delete-btn-1weekmore") action = "2";
						        else if(e.currentTarget.id=="delete-btn-2weeks") action = "3";
						        else if(e.currentTarget.id=="delete-btn-all") action = "0";
						        
								$.post("includes/subscribers/delete-unconfirmed.php", { app: <?php echo get_app_info('app');?>, action: action},
								  function(data) {
								      if(data)
								      	window.location = "<?php echo get_app_info('path');?>/housekeeping-unconfirmed?i=<?php echo get_app_info('app');?>";
								      else
								      	alert("<?php echo _('Sorry, unable to remove subscribers. Please try again later!');?>");
								  }
								);
							}
						});
						</script>
	                </tr>
	              </thead>
	              <tbody>
	                	<?php 	
		                	$limit = 10;
							$total_pages = ceil($total_lists/$limit);
							$offset = $p!=null ? ($p-1) * $limit : 0;
								                	
		                	$q = 'SELECT id, name FROM lists WHERE app = '.get_app_info('app').' AND userID = '.get_app_info('main_userID').' AND opt_in = 1 ORDER BY name ASC LIMIT '.$offset.','.$limit;
		                	$r = mysqli_query($mysqli, $q);
		                	if ($r && mysqli_num_rows($r) > 0)
		                	{
		                	    while($row = mysqli_fetch_array($r))
		                	    {
		                			$lid = $row['id'];
		                			$list_name = $row['name'];
		                			
		                			$unconfirmed_count_1 = get_unconfirmed_total($lid, 1);
		                			$unconfirmed_count_2 = get_unconfirmed_total($lid, 2);
		                			$unconfirmed_count_3 = get_unconfirmed_total($lid, 3);
		                			$unconfirmed_count_4 = get_unconfirmed_total($lid, 0);
		                			
		                			$unconfirmed_delete_btn1 = !$unconfirmed_count_1 ? '' : '<a href="javascript:void(0)" title="'._('Remove subscribers who did not confirm their subscription for 1 week?').'" id="delete-btn-'.$lid.'-1week" class="delete-list"><i class="icon icon-trash"></i></a>';
		                			$unconfirmed_delete_btn2 = !$unconfirmed_count_2 ? '' : '<a href="javascript:void(0)" title="'._('Remove subscribers who did not confirm their subscription for more than 1 week?').'" id="delete-btn-'.$lid.'-1weekmore" class="delete-list"><i class="icon icon-trash"></i></a>';
		                			$unconfirmed_delete_btn3 = !$unconfirmed_count_3 ? '' : '<a href="javascript:void(0)" title="'._('Remove subscribers who did not confirm their subscription for more than 2 weeks?').'" id="delete-btn-'.$lid.'-2weeks" class="delete-list"><i class="icon icon-trash"></i></a>';
		                			$unconfirmed_delete_btn4 = !$unconfirmed_count_4 ? '' : '<a href="javascript:void(0)" title="'._('Remove ALL subscribers who did not confirm their subscription?').'" id="delete-btn-'.$lid.'-all" class="delete-list"><i class="icon icon-trash"></i></a>';
		                			
		                			$subscriber_count_1week = '<span id="count-'.$lid.'-1week">'.$unconfirmed_count_1.'</span>';
		                			$subscriber_count_2week = '<span id="count-'.$lid.'-1weekmore">'.$unconfirmed_count_2.'</span>';
		                			$subscriber_count_2week_more = '<span id="count-'.$lid.'-2weeks">'.$unconfirmed_count_3.'</span>';
		                			$subscriber_count_all = '<span id="count-'.$lid.'-all">'.$unconfirmed_count_4.'</span>';
		                			
		                			echo '
		                			<tr id="uc-'.$lid.'">
			                			<td><a href="'.get_app_info('path').'/subscribers?i='.get_app_info('app').'&l='.$lid.'">'.$list_name.' <span class="badge badge-success">'.get_totals('a', '', $lid).'</span></a></td>
			                			<td><span class="label"><a href="'.get_app_info('path').'/subscribers?i='.get_app_info('app').'&l='.$lid.'&c=0">'._('Unconfirmed').'</a></span></td>
			                			<td>'.$unconfirmed_delete_btn1.' '.$subscriber_count_1week.'</td>
			                			<td>'.$unconfirmed_delete_btn2.' '.$subscriber_count_2week.'</td>
			                			<td>'.$unconfirmed_delete_btn3.' '.$subscriber_count_2week_more.'</td>
			                			<td>'.$unconfirmed_delete_btn4.' '.$subscriber_count_all.'</td>
			                			<td><a href="'.get_app_info('path').'/includes/subscribers/export-csv.php?i='.get_app_info('app').'&l='.$lid.'&c=0" title="Export all unconfirmed subscribers"><i class="icon icon-download-alt"></i></a></td>
			                			
			                			<script type="text/javascript">
								        $("#delete-btn-'.$lid.'-1week").click(function(e){
									        c = "'._('Confirm permanently remove subscribers?').'"
									        if(confirm(c))
									        {
										        e.preventDefault(); 
										        $("#count-'.$lid.'-1week").html("<img src=\''.get_app_info('path').'/img/loader.gif\' style=\'width:16px;\'/>");
												$.post("includes/subscribers/delete-unconfirmed.php", { lid: '.$lid.', action: "1"},
												  function(data) {
												      if(data)
												      {
												      	$("#count-'.$lid.'-1week").text("0");
												      	$("#count-'.$lid.'-all").text(data);
												      	$("#delete-btn-'.$lid.'-1week").remove();
												      	if(data==0) $("#delete-btn-'.$lid.'-all").remove();
												      }
												      else
												      	alert("'._('Sorry, unable to remove subscribers. Please try again later!').'");
												  }
												);
											}
										});
										$("#delete-btn-'.$lid.'-1weekmore").click(function(e){
									        c = "'._('Confirm permanently remove subscribers?').'"
									        if(confirm(c))
									        {
										        e.preventDefault(); 
										        $("#count-'.$lid.'-1weekmore").html("<img src=\''.get_app_info('path').'/img/loader.gif\' style=\'width:16px;\'/>");
												$.post("includes/subscribers/delete-unconfirmed.php", { lid: '.$lid.', action: "2"},
												  function(data) {
												      if(data)
												      {
												      	$("#count-'.$lid.'-1weekmore").text("0");
												      	$("#count-'.$lid.'-all").text(data);
												      	$("#delete-btn-'.$lid.'-1weekmore").remove();
												      	if(data==0) $("#delete-btn-'.$lid.'-all").remove();
												      }
												      else
												      	alert("'._('Sorry, unable to remove subscribers. Please try again later!').'");
												  }
												);
											}
										});
										$("#delete-btn-'.$lid.'-2weeks").click(function(e){
									        c = "'._('Confirm permanently remove subscribers?').'"
									        if(confirm(c))
									        {
										        e.preventDefault(); 
										        $("#count-'.$lid.'-2weeks").html("<img src=\''.get_app_info('path').'/img/loader.gif\' style=\'width:16px;\'/>");
												$.post("includes/subscribers/delete-unconfirmed.php", { lid: '.$lid.', action: "3"},
												  function(data) {
												      if(data)
												      {
												      	$("#count-'.$lid.'-2weeks").text("0");
												      	$("#count-'.$lid.'-all").text(data);
												      	$("#delete-btn-'.$lid.'-2weeks").remove();
												      	if(data==0) $("#delete-btn-'.$lid.'-all").remove();
												      }
												      else
												      	alert("'._('Sorry, unable to remove subscribers. Please try again later!').'");
												  }
												);
											}
										});
										$("#delete-btn-'.$lid.'-all").click(function(e){
									        c = "'._('Confirm permanently remove subscribers?').'"
									        if(confirm(c))
									        {
										        e.preventDefault(); 
										        $("#count-'.$lid.'-all").html("<img src=\''.get_app_info('path').'/img/loader.gif\' style=\'width:16px;\'/>");
												$.post("includes/subscribers/delete-unconfirmed.php", { lid: '.$lid.', action: "0"},
												  function(data) {
												      if(data)
												      {
												      	$("#count-'.$lid.'-all").text("0");
												      	$("#count-'.$lid.'-1week").text("0");
												      	$("#count-'.$lid.'-1weekmore").text("0");
												      	$("#count-'.$lid.'-2weeks").text("0");
												      	$("#delete-btn-'.$lid.'-1week").remove();
												      	$("#delete-btn-'.$lid.'-1weekmore").remove();
												      	$("#delete-btn-'.$lid.'-2weeks").remove();
												      	$("#delete-btn-'.$lid.'-all").remove();
												      }
												      else
												      	alert("'._('Sorry, unable to remove subscribers. Please try again later!').'");
												  }
												);
											}
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
			                		<td colspan="7">'._('No housekeeping needed as no double opt-in lists are found.').'</td>
			                	</tr>
			                	';
		                	}
	                	?>                
	              </tbody>
	            </table>
	            <?php pagination_housekeeping('unconfirmed', $total_lists, $limit, get_app_info('app'));?>
			</div>
	    </div>
    </div>
</div>

<?php include('includes/footer.php');?>
