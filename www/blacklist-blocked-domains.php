<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/subscribers/main.php');?>
<?php include('includes/subscribers/housekeeping.php');?>

<?php	
	if(get_app_info('is_sub_user')) 
	{
		if(get_app_info('app')!=get_app_info('restricted_to_app'))
		{
			echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/blacklist-blocked-domains?i='.get_app_info('restricted_to_app').'"</script>';
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
	$s = isset($_GET['s']) ? htmlentities(trim($_GET['s'])) : '';
	$err = isset($_GET['e']) ? $_GET['e'] : '';
	$total_subs = suppression_blocked_domain_list_total(get_app_info('app'));
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
		    	<h2><?php echo _('Blacklist');?></h2>
				<br/>
		    	<div class="well">
			    	<div class="btn-group" data-toggle="buttons-radio">
					  <a href="<?php echo get_app_info('path');?>/blacklist-suppression?i=<?php echo get_app_info('app');?>" title="" class="btn"><i class="icon icon-remove-circle"></i> <?php echo _('Suppression list');?></a>
					  <a href="javascript:void(0)" title="" class="btn active"><i class="icon icon-minus-sign"></i> <?php echo _('Blocked domains');?></a>
					</div>
		    	</div>
		    	<div class="alert alert-info">
					<p><i class="icon icon-info-sign"></i> <?php echo _('If you\'d like block email addresses that belong to certain domains (eg. disposable emails) from ever being imported into any lists in this brand, import them into this list. Any email addresses you import via CSV or API into any list in this brand in future will not be imported if they appear in this list.');?></p>
				</div>
	    	</div>
	    </div>
	    
	    <br/>
	    
	    <div class="row-fluid">
		    <div class="span12">
		    	<h3><?php echo _('Blocked domains');?> <span class="badge badge-inverse" style="position: relative; top:-2px;"><?php echo $total_subs;?></span></h3>
		    	
		    	<hr/>
		    </div>
	    </div>
	    
	    <div class="row-fluid">
			<div class="span12">
		    	<?php if($total_subs!=0 || isset($_GET['s'])):?>
	    		<div style="float:left; width: 100%; margin-bottom: 20px;">
			    	<div style="float:left;">
				    	<button class="btn" id="import-btn"><i class="icon-plus-sign"></i> <?php echo _('Import list of domains to block');?></button> 
				    	<button class="btn" id="export-btn"><i class="icon-download-alt"></i> <?php echo _('Export');?></button>
				    </div>
				    <div style="float:right;">
					    <form class="form-search" action="<?php echo get_app_info('path');?>/blacklist-blocked-domains" method="GET" style="float:right;">
				    		<input type="hidden" name="i" value="<?php echo get_app_info('app');?>">
							<input type="text" class="input-medium search-query" name="s" value="<?php echo $s;?>">
							<button type="submit" class="btn"><i class="icon-search"></i> <?php echo _('Search');?></button>
						</form>
					</div>
				</div>
			</div>
			<?php endif;?>
	    </div>
			
		<div class="row-fluid well" style="float:left; margin:-20px 0 40px 0; <?php if(isset($_GET['e'])) echo 'display:block;';?>" id="import-blocked-domain-list-csv">
			<div class="span6">
				<div>
					<h2><?php echo _('Import via CSV file');?></h2><br/>
					
				    <form action="<?php echo get_app_info('path')?>/includes/subscribers/import-blocked-domain-list.php" method="POST" accept-charset="utf-8" class="form-vertical" enctype="multipart/form-data" id="import-update-form">
				        
				        <?php if($err==1):?>
						<div class="alert alert-error blacklist-alerts">
						  <button type="button" class="close" data-dismiss="alert">×</button>
						  <strong><?php echo _('Please ensure there is only one column in your CSV containing only domains.');?></strong>
						</div>
						<?php elseif($err==2):?>
						<div class="alert alert-error blacklist-alerts">
						  <button type="button" class="close" data-dismiss="alert">×</button>
						  <p><strong><?php echo _('Please upload a CSV file.');?></strong></p>
						  <p><?php echo _('If you are uploading a huge CSV file, Try increasing the following values in your server\'s php.ini to larger numbers. Contact your hosting support if you\'re unsure how to do this.');?></p>
						  <ul>
						  	<li><code>upload_max_filesize</code></li>
							<li><code>post_max_size</code></li>
							<li><code>memory_limit</code></li>
							<li><code>max_input_time</code></li>
							<li><code>max_execution_time</code> <?php echo _('(set to 0 so that execution won\'t time out indefinitely)');?></li>
						  </ul>
						  <p><?php echo _('Alternatively, try splitting your huge CSV file into several smaller sized CSV files and import them one after another.');?></p>
						</div>
						<?php endif;?>
						
				        <label class="control-label" for="csv_file"><em><?php echo _('CSV format');?>:</em></label>
				        	<ul class="blacklist-alerts">
				        		<li><?php echo _('Your CSV should only contain one column containing only domains');?></li>
				        		<li><?php echo _('Your CSV columns should be separated by commas, not semi-colons or any other characters');?></li>
				        	</ul>
				        <table class="table table-bordered table-striped table-condensed" style="width: 300px;">
						  <tbody>
						  	<tr><th><?php echo _('Domain');?></th></tr>
						    <tr><td>mailinator.com</td></tr>
						    <tr><td>guerrillamail.com</td></tr>
						  </tbody>
						</table>
				        <div class="control-group">
					    	<div class="controls">
				              <input type="file" class="input-xlarge" id="csv_file" name="csv_file">
				            </div>
				        </div>
				        
				        <input type="hidden" name="app" value="<?php echo $app;?>">
				        
				        <p class="alert alert-info blacklist-alerts">
					        <i class="icon icon-info-sign"></i> <?php echo _('Email addresses with domains matching any records in your CSV will also be removed from all lists in this brand.');?>
				        </p>
				        <a href="javascript:void(0)" id="submit-btn" class="btn btn-inverse"><i class="icon icon-double-angle-down"></i> <?php echo _('Import and delete matching records from all lists');?></a>
				        <a href="javascript:void(0)" class="close-import-btn"><span class="icon icon-remove-sign"></span> <?php echo _('Cancel');?></a>
				    </form>
				</div>
			</div>
		    	
		    	<div class="span6">
			    <div>
					<h2><?php echo _('Import domains per line');?></h2><br/>
					
				    <form action="<?php echo get_app_info('path')?>/includes/subscribers/import-blocked-domain-list2.php" method="POST" accept-charset="utf-8" class="form-vertical" enctype="multipart/form-data" id="import-update-form2">
				        
				        <?php if($err==3):?>
						<div class="alert alert-error blacklist-alerts">
						  <button type="button" class="close" data-dismiss="alert">×</button>
						  <strong><?php echo _('Sorry, we didn\'t receive any input.');?></strong>
						</div>
						<?php endif;?>
						
				        <label class="control-label" for="line"><?php echo _('Domains');?></label>
			            <div class="control-group">
					    	<div class="controls">
				              <textarea class="input-xlarge" id="line" name="line" rows="10" style="width: 300px; height: 190px;" placeholder="mailinator.com"></textarea>
				            </div>
				        </div>				        
				        
				        <input type="hidden" name="app" value="<?php echo $app;?>">
				        
				        <p class="alert alert-info blacklist-alerts">
					        <i class="icon icon-info-sign"></i> <?php echo _('Email addresses with domains matching any domains you\'ve entered above will also be removed from all lists in this brand.');?>
				        </p>
				        <a href="javascript:void(0)" id="submit-btn2" class="btn btn-inverse"><i class="icon icon-double-angle-down"></i> <?php echo _('Import and delete matching records from all lists');?></a>
				        <a href="javascript:void(0)" class="close-import-btn"><span class="icon icon-remove-sign"></span> <?php echo _('Cancel');?></a>
				    </form>
				</div>
		    </div>
		</div>
		    	
		<div class="row-fluid">
			<div class="span12">	
		    	<?php if($total_subs!=0 || isset($_GET['s'])):?>
				<table class="table table-striped table-condensed responsive">
	              <thead>
	                <tr>
		                  <th><?php echo _('Domain');?></th>
		                  <th><?php echo _('Status');?></th>
		                  <th><?php echo _('Last update');?></th>
		                  <th><?php echo _('Block attempts');?></th>
		                  <th><?php echo _('Delete');?></th>
	                </tr>
	              </thead>
	              <tbody>
					  	<?php 	  			
					  		$limit = 10;
							$total_pages = ceil($total_subs/$limit);
							$offset = $p!=null ? ($p-1) * $limit : 0;
					  		
					  		//search line
					  		$search_line = $s=='' ? '' : 'AND domain LIKE "%'.$s.'%"';
					  		
							$q = 'SELECT * FROM blocked_domains WHERE app = '.get_app_info('app').' '.$search_line.' ORDER BY block_attempts DESC, timestamp DESC LIMIT '.$offset.','.$limit;
						  	$r = mysqli_query($mysqli, $q);
						  	if ($r && mysqli_num_rows($r) > 0)
						  	{
						  	    while($row = mysqli_fetch_array($r))
						  	    {
						  			$id = $row['id'];
						  			$domain = stripslashes($row['domain']);
						  			$timestamp = parse_date($row['timestamp'], 'short', true);
						  			$block_attempts = $row['block_attempts'];
						  			
						  			echo '
						  			
						  			<tr id="'.$id.'">
								      <td>'.$domain.'</td>
								      <td><span class="label label-inverse">Blocked</span></td>
								      <td>'.$timestamp.'</td>
								      <td><span class="badge badge-success">'.$block_attempts.'</span></td>
								      <td><a href="#delete-subscriber" title="'._('Delete').' '.$domain.'?" id="delete-btn-'.$id.'" class="delete-subscriber"><i class="icon icon-trash"></i></a></td>
								      <script type="text/javascript">
								        $("#delete-btn-'.$id.'").click(function(e){
											e.preventDefault(); 
											c = confirm("'._('Confirm delete').' '.$domain.'?");
											if(c)
											{
												$.post("includes/subscribers/delete-blocked-domain.php", { app: '.get_app_info('app').', domain_id: '.$id.' },
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
								    </tr>
									
						  			';
						  	    }  
						  	}
						  	else
						  	{
						  		echo '
						  			<tr>
						  				<td colspan="5">'._('No domain found.').'</td>
						  			</tr>
						  		';
						  	}
					  	?>
					    
					  </tbody>
	            </table>
	            <?php pagination_blacklist('blocked-domains', $limit, get_app_info('app'));?>
	            <?php else:?>
	            <script type="text/javascript">
		            $(document).ready(function() {
		            	$("#import-blocked-domain-list-csv").show();
		            	$(".close-import-btn").hide();
		            });
	            </script>
	            <?php endif;?>
			</div>
	    </div>
    </div>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		$("#import-btn").click(function(){
			$("#import-blocked-domain-list-csv").slideDown();
		});
		$(".close-import-btn").click(function(){
			$("#import-blocked-domain-list-csv").slideUp();
		});
		$("#submit-btn").click(function(){
			c = confirm("<?php echo _('Please note that email addresses with domains matching any records in your CSV will also be removed from all lists in this brand after being imported into this \'Blocked domains\' list. Do you want to proceed?');?>");
			if(c) $("#import-update-form").submit();
		});
		$("#submit-btn2").click(function(){
			c = confirm("<?php echo _('Please note that email addresses with domains matching any records you\'ve provided will also be removed from all lists in this brand after being imported into this \'Blocked domains\' list. Do you want to proceed?');?>");
			if(c) $("#import-update-form2").submit();
		});
		$("#export-btn").click(function(){
			window.location = "<?php echo get_app_info('path');?>/includes/subscribers/export-blocked-domains.php?i=<?php echo get_app_info('app');?>";
		});
	});
</script>

<?php include('includes/footer.php');?>
