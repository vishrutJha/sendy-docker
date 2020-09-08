<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/subscribers/main.php');?>
<?php include('includes/helpers/short.php');?>
<?php
	//IDs
	$lid = isset($_GET['l']) && is_numeric($_GET['l']) ? mysqli_real_escape_string($mysqli, $_GET['l']) : exit;
			
	if(get_app_info('is_sub_user')) 
	{
		if(get_app_info('app')!=get_app_info('restricted_to_app'))
		{
			echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/list?i='.get_app_info('restricted_to_app').'"</script>';
			exit;
		}
		$q = 'SELECT app FROM lists WHERE id = '.$lid;
		$r = mysqli_query($mysqli, $q);
		if ($r)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				$a = $row['app'];
		    }  
		    if($a!=get_app_info('restricted_to_app'))
		    {
			    echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/list?i='.get_app_info('restricted_to_app').'"</script>';
				exit;
		    }
		}
	}
	
	//vars
	$s = isset($_GET['s']) ? htmlentities(trim($_GET['s'])) : '';
	if(isset($_GET['c'])) $c = is_numeric($_GET['c']) ? $_GET['c'] : exit;
	else $c = '';
	if(isset($_GET['p'])) $p = is_numeric($_GET['p']) ? $_GET['p'] : exit;
	else $p = '';
	if(isset($_GET['a'])) $a = is_numeric($_GET['a']) ? $_GET['a'] : exit;
	else $a = '';
	if(isset($_GET['u'])) $u = is_numeric($_GET['u']) ? $_GET['u'] : exit;
	else $u = '';
	if(isset($_GET['b'])) $b = is_numeric($_GET['b']) ? $_GET['b'] : exit;
	else $b = '';
	if(isset($_GET['cp'])) $cp = is_numeric($_GET['cp']) ? $_GET['cp'] : exit;
	else $cp = '';
	if(isset($_GET['g'])) $g = is_numeric($_GET['g']) ? $_GET['g'] : exit;
	else $g = '';
?>
<link href="<?php echo get_app_info('path');?>/js/tablesorter/theme.default.min.css" rel="stylesheet">
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/tablesorter/jquery.tablesorter.widgets.min.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$('table').tablesorter({
			widgets        : ['saveSort'],
			usNumberFormat : true,
			sortReset      : true,
			sortRestart    : true,
			headers: { 2: { sorter: false}, 4: {sorter: false}, 5: {sorter: false} }	
		});
	});
</script>
<div class="row-fluid">
    <div class="span2">
        <?php include('includes/sidebar.php');?>
    </div> 
    <div class="span10">
    	<div>
	    	<p class="lead">
		    	<?php if(get_app_info('is_sub_user')):?>
			    	<?php echo get_app_data('app_name');?>
		    	<?php else:?>
			    	<a href="<?php echo get_app_info('path'); ?>/edit-brand?i=<?php echo get_app_info('app');?>" data-placement="right" title="<?php echo _('Edit brand settings');?>"><?php echo get_app_data('app_name');?></a>
		    	<?php endif;?>
		    </p>
    	</div>
    	<h2><?php echo _('Subscriber lists');?></h2> <br/>

    	<button class="btn" onclick="window.location='<?php echo get_app_info('path');?>/update-list?i=<?php echo get_app_info('app');?>&l=<?php echo $lid;?>'"><i class="icon-plus-sign"></i> <?php echo _('Add subscribers');?></button> 
    	<button class="btn" onclick="window.location='<?php echo get_app_info('path');?>/delete-from-list?i=<?php echo get_app_info('app');?>&l=<?php echo $lid;?>'"><i class="icon-minus-sign"></i> <?php echo _('Delete subscribers');?></button> 
    	<button class="btn" onclick="window.location='<?php echo get_app_info('path');?>/unsubscribe-from-list?i=<?php echo get_app_info('app');?>&l=<?php echo $lid;?>'"><i class="icon-ban-circle"></i> <?php echo _('Mass unsubscribe');?></button> 
    	<?php 
    		//export according to which section user is on
    		if($a=='' && $c=='' && $u=='' && $b=='' && $cp=='' && $g=='')
    		{
	    		$filter = '';
	    		$filter_val = '';
	    		$export_title = _('all subscribers');
    		}
    		else if($a!='')
    		{
	    		$filter = 'a';
	    		$filter_val = $a;
	    		$export_title = _('active subscribers');
    		}
    		else if($c!='')
    		{
	    		$filter = 'c';
	    		$filter_val = $c;
	    		$export_title = _('unconfirmed subscribers');
    		}  
    		else if($u!='')
    		{
	    		$filter = 'u';
	    		$filter_val = $u;
	    		$export_title = _('unsubscribers');
    		} 
    		else if($b!='')
    		{
	    		$filter = 'b';
	    		$filter_val = $b;
	    		$export_title = _('bounced subscribers');
    		}
    		else if($cp!='')
    		{
	    		$filter = 'cp';
	    		$filter_val = $cp;
	    		$export_title = _('subscribers who marked your email as spam');
    		}  
    		else if($g!='')
    		{
	    		$filter = 'g';
	    		$filter_val = $g;
	    		$export_title = _('GDPR subscribers');
    		}     	
    	?>
    	<button class="btn" onclick="window.location='<?php echo get_app_info('path');?>/includes/subscribers/export-csv.php?i=<?php echo get_app_info('app');?>&l=<?php echo $lid;?>&<?php echo $filter.'='.$filter_val;?>'"><i class="icon-download-alt"></i> <?php echo _('Export').' '.$export_title;?></button>
		
		<form class="form-search" action="<?php echo get_app_info('path');?>/subscribers" method="GET" style="float:right;">
    		<input type="hidden" name="i" value="<?php echo get_app_info('app');?>">
    		<input type="hidden" name="l" value="<?php echo $lid;?>">
    		<?php if($a!=''):?>
    		<input type="hidden" name="a" value="<?php echo $a;?>">
    		<?php elseif($c!=''):?>
    		<input type="hidden" name="c" value="<?php echo $c;?>">
    		<?php elseif($u!=''):?>
    		<input type="hidden" name="u" value="<?php echo $u;?>">
    		<?php elseif($b!=''):?>
    		<input type="hidden" name="b" value="<?php echo $b;?>">
    		<?php elseif($cp!=''):?>
    		<input type="hidden" name="cp" value="<?php echo $cp;?>">
    		<?php elseif($g!=''):?>
    		<input type="hidden" name="g" value="<?php echo $g;?>">
    		<?php endif;?>
			<input type="text" class="input-medium search-query" name="s">
			<button type="submit" class="btn"><i class="icon-search"></i> <?php echo _('Search');?></button>
		</form>
    	
    	<br/><br/>
    	<p class="well"><?php echo _('List');?>: <a href="<?php echo get_app_info('path');?>/subscribers?i=<?php echo get_app_info('app');?>&l=<?php echo $lid;?>" title=""><span class="label label-info"><?php echo get_lists_data('name', $lid);?></span></a> | <a href="<?php echo get_app_info('path')?>/list?i=<?php echo get_app_info('app');?>" title=""><?php echo _('Back to lists');?></a>
    	<a href="<?php echo get_app_info('path');?>/edit-list?i=<?php echo get_app_info('app');?>&l=<?php echo $lid;?>" style="float:right;"><i class="icon-wrench"></i> <?php echo _('List settings');?></a>
    	<a href="#subscribeform" style="float:right;margin-right:20px;" data-toggle="modal"><i class="icon-list-alt"></i> <?php echo _('Subscribe form');?></a>
    	
    	
    	<span class="badge" style="float:right;margin:0 20px 0 -15px;"><?php echo get_segments_count();?></span>
    	<a href="<?php echo get_app_info('path');?>/segments-list?i=<?php echo get_app_info('app');?>&l=<?php echo $lid;?>" style="float:right;margin-right:20px;"><i class="icon-filter"></i> <?php echo _('Segments');?></a>
    	
    	<?php 
	    	$q = 'SELECT cron_ares FROM login WHERE id = '.get_app_info('main_userID');
	    	$r = mysqli_query($mysqli, $q);
	    	if ($r)
	    	{
	    	    while($row = mysqli_fetch_array($r)) 
	    	    	$cron_ares = $row['cron_ares'];
	    	}	    	
	    	if($cron_ares):
    	?>    	
    	<span class="badge" style="float:right;margin:0 20px 0 -15px;"><?php echo get_autoresponder_count();?></span>
    	<a href="<?php echo get_app_info('path');?>/autoresponders-list?i=<?php echo get_app_info('app');?>&l=<?php echo $lid;?>" style="float:right;margin-right:20px;"><i class="icon-time"></i> <?php echo _('Autoresponders');?></a>
    	<?php else:?>
    	<a href="#ares_cron" style="float:right;margin-right:20px;" data-toggle="modal"><i class="icon-time"></i> <?php echo _('Autoresponders');?></a>
    	<?php endif;?>
    	<span class="badge" style="float:right;margin:0 20px 0 -15px;"><?php echo get_custom_fields_count();?></span>
    	<a href="<?php echo get_app_info('path');?>/custom-fields?i=<?php echo get_app_info('app');?>&l=<?php echo $lid;?>" style="float:right;margin-right:20px;"><i class="icon-list"></i> <?php echo _('Custom fields');?></a>
    	</p><br/>
    	
    	<?php 
	    	//Get gdpr_options
			$q = 'SELECT gdpr_options, custom_domain, custom_domain_protocol, custom_domain_enabled FROM apps WHERE id = '.get_app_info('app');
			$r = mysqli_query($mysqli, $q);
			if ($r) 
			{
				while($row = mysqli_fetch_array($r)) 
				{
					$gdpr_options = $row['gdpr_options'];
					$custom_domain = $row['custom_domain'];
					$custom_domain_protocol = $row['custom_domain_protocol'];
					$custom_domain_enabled = $row['custom_domain_enabled'];
					if($custom_domain!='' && $custom_domain_enabled)
					{
						$parse = parse_url(get_app_info('path'));
						$domain = $parse['host'];
						$protocol = $parse['scheme'];
						$app_path = str_replace($domain, $custom_domain, get_app_info('path'));
						$app_path = str_replace($protocol, $custom_domain_protocol, $app_path);
					}
					else $app_path = get_app_info('path');
				}
			}
    	?>
    	
    	<div id="subscribeform" class="modal hide fade" style="width: <?php echo $gdpr_options ? '900' : '516';?>px; margin-left: <?php echo $gdpr_options ? '-450' : '-258';?>px;">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h3><span class="icon icon-list-alt"></span> <?php echo _('Subscribe form');?></h3>
            </div>
            <div class="modal-body">
	        
	        <div class="subscribeform-left">
		    <p><strong>Ready-to-use subscribe form</strong></p>
	        <p><?php echo _('The following is a \'ready-to-use\' subscription form URL you can immediately use to collect sign ups to this list');?>:</p>
	        
	        <?php 
		        if(version_compare(PHP_VERSION, '5.3.0') >= 0 && function_exists('openssl_encrypt'))
			        $subscription_form_url = $app_path.'/subscription?f='.short('{"brand":"'.get_app_info('app').'", "list":"'.short($lid).'"}');
		        else
			        $subscription_form_url = $app_path.'/subscription?i='.get_app_info('app').'&l='.short($lid);
		    ?>
		        <pre id="form-url"><?php echo $subscription_form_url;?></pre>
	        <br/>
	        
	        <p><strong>Subscribe form HTML code</strong></p>
            <p><?php echo _('The following is an embeddable subscribe form HTML code for this list');?>.</p>
            
            <?php 
	            $q = 'SELECT gdpr_enabled, marketing_permission, what_to_expect FROM lists WHERE id = '.$lid;
				$r = mysqli_query($mysqli, $q);
				if ($r)
				{
				    while($row = mysqli_fetch_array($r))
				    {
						$gdpr_enabled = $row['gdpr_enabled'];
						$marketing_permission = $row['marketing_permission'];
						$what_to_expect = $row['what_to_expect'];
				    } 
				}
            ?>
            
<pre id="form-code"></pre>

<script type="text/javascript">
	$(document).ready(function() {
		$("#form-code, #form-url").mouseover(function(){
			$(this).selectText();
		});
	});
</script>
			<br/>
			<p><?php if(!get_app_info('is_sub_user')): echo _('You can setup reCAPTCHA in the brand settings.'); else: echo _('You can setup reCAPTCHA in the main settings.'); endif;?><br/><?php if(!get_app_info('is_sub_user')): echo _('To subscribe users programmatically, use the API');?> → <a href="https://sendy.co/api?app_path=<?php echo get_app_info('path');?>" style="text-decoration: underline;" target="_blank">https://sendy.co/api</a>.<?php endif;?></p>
			</div>
			
			<?php if($gdpr_options):?>
			<div class="subscribeform-right">
				<p>
					<label class="checkbox">
				      <input type="checkbox" id="enable-gdpr" <?php echo $gdpr_enabled ? 'checked' : ''; ?>> <strong><?php echo _('Enable GDPR fields');?></strong>
				    </label>
				    
				    <div id="gdpr-description" <?php if($gdpr_enabled) echo 'style="display:none;"'; ?>>
					    <?php echo _('The <a href="https://www.eugdpr.org/the-regulation.html" target="_blank" style="text-decoration: underline;">General Data Protection Regulation (GDPR)</a> is a regulation in EU law on data protection and privacy for all individuals within the European Union. The GDPR regulation affects anyone in the world who collect and process the personal data of EU users. If you collect and process data of EU users, consider enabling GDPR fields.');?>
					    <br/><br/>
					    <?php echo _('GDPR fields are supported in both the \'Ready-to-use subscribe form\' and the embeddable \'Subscribe form HTML code\' as seen on the left. When GDPR fields are enabled, an unticked consent checkbox will appear below the subscription form with \'Marketing permission\' and \'What to expect\' texts to explain what they are signing up for and what you’re going to do with the information they submit. Users are required to check the checkbox in order to subscribe.');?>
				    </div>
				    
				    <div id="gdpr-settings" <?php if(!$gdpr_enabled) echo 'style="display:none;"';?>>
					    <label><strong><?php echo _('Marketing permission');?></strong></label>
					    <textarea rows="3" id="marketing-permission"><?php if($marketing_permission==''):?>I give my consent to <?php echo get_app_data('app_name');?> to be in touch with me via email using the information I have provided in this form for the purpose of news, updates and marketing.<?php else: echo $marketing_permission; endif;?></textarea>
					    <label><strong><?php echo _('What to expect');?></strong></label>
					    <textarea rows="5" id="what-to-expect"><?php if($what_to_expect==''):?>If you wish to withdraw your consent and stop hearing from us, simply click the unsubscribe link at the bottom of every email we send or contact us at <?php echo get_app_data('from_email');?>. We value and respect your personal data and privacy. To view our privacy policy, please visit our website. By submitting this form, you agree that we may process your information in accordance with these terms.<?php else: echo $what_to_expect; endif;?></textarea>
					    <a href="javascript:void(0)" class="btn" id="gdpr-save-btn">Save</a>
					    <a href="<?php echo $subscription_form_url;?>" target="_blank" id="preview-form">Preview form →</a>
					    <br/><span id="gdpr-msg"></span>
					    <script type="text/javascript">
						    $(document).ready(function() {
							    load_subscribe_form_code();
							    
							    //Load subscribe form HTML code into <pre>
								function load_subscribe_form_code()
								{
									$.post("includes/subscribers/subscribe-form.php", { lid: <?php echo $lid;?>, app: <?php echo get_app_info('app')?>, from_email: "<?php echo get_app_data('from_email');?>" },
									  function(data) {
									      if(data) $("#form-code").text(data);
									      else alert("Unable to load subscribe form HTML code. Please try again later!");
									  }
									);
								}
								
							    $("#enable-gdpr").click(function(){
								    if($("#enable-gdpr").is(":checked"))
								    {   
									    $("#form-code").text("Loading..");
									    $("#gdpr-description").slideUp();
									    $("#gdpr-settings").slideDown();
									    $.post("includes/subscribers/save-gdpr.php", { lid:<?php echo $lid?>, enable_gdpr:"yes", enabled_disable_only:"yes" },
							    		  function(data) {
							    		      if(data) load_subscribe_form_code();
							    		      else alert("Error saving. Please try again.");
							    		  }
							    		);
								    }
								    else
								    {
									    $("#form-code").text("Loading..");
									    $("#gdpr-description").slideDown();
									    $("#gdpr-settings").slideUp();
									    $.post("includes/subscribers/save-gdpr.php", { lid:<?php echo $lid?>, enable_gdpr:"no", enabled_disable_only:"yes" },
							    		  function(data) {
							    		      if(data) load_subscribe_form_code();
								    		  else alert("Error saving. Please try again.");
							    		  }
							    		);
								    }
							    });
							    $("#gdpr-save-btn").click(function(){
									marketing_permission = $("#marketing-permission").val();
									what_to_expect = $("#what-to-expect").val();
									if($("#enable-gdpr").is(":checked")) enable_gdpr = 'yes';
									else enable_gdpr = 'no';
						    		
						    		$("#gdpr-save-btn").text("Saving..");
						    		$("#form-code").text("Loading..");
						    		$("#gdpr-msg").text("");
						    		
									$.post("includes/subscribers/save-gdpr.php", { lid:<?php echo $lid?>, enable_gdpr:enable_gdpr, marketing_permission:marketing_permission, what_to_expect:what_to_expect },
						    		  function(data) {
						    		      if(data)
						    		      {
							    		      if(data=="saved")
							    		      {
								    		      $("#gdpr-save-btn").text("Save");
								    		      $("#gdpr-msg").css("color", "#56864e");
							    		      	  $("#gdpr-msg").text("GDPR settings have been saved.");
							    		      	  load_subscribe_form_code();
							    		      }
							    		      else if(data=='failed')
							    		      {
								    		      $("#gdpr-save-btn").text("Save");
								    		      $("#gdpr-msg").css("color", "#ac514c");
							    		      	  $("#gdpr-msg").text("Error saving GDPR settings.");
							    		      }
						    		      }
						    		      else
						    		      {
							    		      $("#gdpr-save-btn").text("Save");
							    		      $("#gdpr-msg").css("color", "#ac514c");
						    		      	  $("#gdpr-msg").text("Error saving GDPR settings.");
						    		      }
						    		  }
						    		);
		 
							    });
						    });
					    </script>
				    </div>
				</p>
			</div>
			<?php endif;?>
			
			<script type="text/javascript">
				$(document).ready(function() {
					load_subscribe_form_code();
					
					//Load subscribe form HTML code into <pre>
					function load_subscribe_form_code()
					{
						$.post("includes/subscribers/subscribe-form.php", { lid: <?php echo $lid;?>, app: <?php echo get_app_info('app')?>, from_email: "<?php echo get_app_data('from_email');?>" },
						  function(data) {
						      if(data) $("#form-code").text(data);
						      else alert("Unable to load subscribe form HTML code. Please try again later!");
						  }
						);
					}
				});
			</script>

            </div>
            <div class="modal-footer">
              <a href="#" class="btn btn-inverse" data-dismiss="modal"><i class="icon icon-ok-sign"></i> <?php echo _('Okay');?></a>
            </div>
          </div>
		
		<?php if($s!=''):?>
		<p><?php echo _('Keyword');?>: <span class="label"><?php echo $s;?></span></p><br/>
		<?php endif;?>
		
		<div class="row-fluid">
    		<div class="span12">
	    		<div style="margin: -20px 0px 20px 0px; color: #666666;"><?php echo _('Subscribers activity chart');?> <a href="#last-activity-info" data-toggle="modal" class="last-activity-info" title="<?php echo _('Click to learn more');?>"><i class="icon icon-question-sign" style="color: #666666;"></i></a></div>
		    	<div id="container" style="min-height:200px;margin:0 0 30px 0;"></div>
	    	</div>
	    </div>
	    
	    <?php 
    		//Count number of emails skipped during last import
    		$q = 'SELECT COUNT(*) FROM skipped_emails WHERE list = '.$lid;
    		$r = mysqli_query($mysqli, $q);
    		if ($r) while($row = mysqli_fetch_array($r)) $no_of_skipped_emails = $row['COUNT(*)'];
    		if($no_of_skipped_emails!=0):
		?>
	    <div class="row-fluid" id="skipped-emails">
    		<div class="span12">
	    		<div class="alert">
		    		<span>
			    		<span class="icon icon-info-sign"></span> <?php echo $no_of_skipped_emails;?> <?php echo _('emails were skipped from your last import');?>. <?php echo _('To see the list and reasons, export the CSV.');?> <a href="<?php echo get_app_info('path');?>/includes/list/export-skipped-emails.php?l=<?php echo $lid;?>" title="<?php echo _('Export skipped email addresses from your last import');?>"><span class="icon icon-download-alt"></span></a> 
		    		</span>
		    		<span style="float:right;">
			    		<a href="javascript:void(0)" id="dismiss" title="<?php echo _('Dismiss this notice (CSV will no longer be available for download)');?>"><?php echo _('Dismiss');?> <span class="icon icon-remove"></span></a>
		    		</span>
		    		<script type="text/javascript">
			    		$(document).ready(function() {
				    		$("#dismiss").click(function(){
					    		$.post("<?php echo get_app_info('path');?>/includes/list/dismiss.php", { l: <?php echo $lid;?> },
			    				  function(data) {
			    				      if(data) $("#skipped-emails").fadeOut();
			    				      else alert("Sorry, unable to dismiss. Please try again later!");
			    				  }
			    				);
				    		});
			    		});
		    		</script>
	    		</div>
    		</div>
	    </div>
	    <?php endif;?>
	    
	    <div class="row-fluid">
		    <div class="span12">				
				<ul class="nav nav-tabs">
				  <li><a href="<?php echo get_app_info('path')?>/subscribers?i=<?php echo get_app_info('app')?>&l=<?php echo $lid?>" id="all"><?php echo _('All');?> <span class="badge badge-info"><?php echo get_totals('', '');?></span></a></li>
				  <li><a href="<?php echo get_app_info('path')?>/subscribers?i=<?php echo get_app_info('app')?>&l=<?php echo $lid?>&a=1" id="active"><?php echo _('Active');?> <span class="badge badge-success"><?php echo get_totals('a', '');?></span></a></li>
				  
				  <?php if(get_totals('gdpr', 1)!=0): //Show GDPR tab only when there are GDPR subscribers?>
				  <li><a href="<?php echo get_app_info('path')?>/subscribers?i=<?php echo get_app_info('app')?>&l=<?php echo $lid?>&g=1" id="gdpr"><?php echo _('GDPR');?> <span class="badge badge-warning"><?php echo get_totals('gdpr', 1);?></span></a></li>
				  <?php endif;?>
					  
				  <li><a href="<?php echo get_app_info('path')?>/subscribers?i=<?php echo get_app_info('app')?>&l=<?php echo $lid?>&c=0" id="unconfirmed"><?php echo _('Unconfirmed');?> <span class="badge"><?php echo get_totals('confirmed', 0);?></span></a></li>
				  <li><a href="<?php echo get_app_info('path')?>/subscribers?i=<?php echo get_app_info('app')?>&l=<?php echo $lid?>&u=1" id="unsubscribed"><?php echo _('Unsubscribed');?> <span class="badge badge-important"><?php echo get_totals('unsubscribed', 1);?></span></a></li>
				  <li><a href="<?php echo get_app_info('path')?>/subscribers?i=<?php echo get_app_info('app')?>&l=<?php echo $lid?>&b=1" id="bounced"><?php echo _('Bounced');?> <span class="badge badge-inverse"><?php echo get_totals('bounced', 1);?></span></a></li>
				  <li><a href="<?php echo get_app_info('path')?>/subscribers?i=<?php echo get_app_info('app')?>&l=<?php echo $lid?>&cp=1" id="complaint"><?php echo _('Marked as spam');?> <span class="badge badge-inverse"><?php echo get_totals('complaint', 1);?></span></a></li>
				</ul>
		    </div>
	    </div>
	    <script type="text/javascript">
			$(document).ready(function() {
				<?php if($a=='' && $c=='' && $u=='' && $b=='' && $cp=='' && $g==''):?>
				$("#all").addClass("tab-active");
				<?php elseif($a!=''):?>
				$("#active").addClass("tab-active");
				<?php elseif($c!=''):?>
				$("#unconfirmed").addClass("tab-active");
				<?php elseif($u!=''):?>
				$("#unsubscribed").addClass("tab-active");
				<?php elseif($b!=''):?>
				$("#bounced").addClass("tab-active");
				<?php elseif($cp!=''):?>
				$("#complaint").addClass("tab-active");
				<?php elseif($g!=''):?>
				$("#gdpr").addClass("tab-active");
				<?php endif;?>
				
				$("#single").click(function(){
					$("#opt_in").val("0");
				});
				$("#double").click(function(){
					$("#opt_in").val("1");
				});
			});
		</script>
		
	    <table class="table table-striped table-condensed responsive">
		  <thead>
		    <tr>
		      <th><?php echo _('Name');?></th>
		      <th><?php echo _('Email');?></th>
		      <th><?php echo _('Last activity');?> <a href="#last-activity-info" data-toggle="modal" class="last-activity-info"><i class="icon icon-question-sign"></i></a></th>
		      <th><?php echo _('Status');?></th>
		      <th><?php echo _('Unsubscribe');?></th>
		      <th><?php echo _('Delete');?></th>
		    </tr>
		  </thead>
		  <tbody>
		  	
		  	<?php 	  			
		  		$limit = 20;
				$total_subs = totals($lid);
				$total_pages = ceil($total_subs/$limit);
				$offset = $p!=null ? ($p-1) * $limit : 0;
		  		
		  		$search_line = $s=='' ? '' : 'AND (name LIKE "%'.$s.'%" OR email LIKE "%'.$s.'%" OR custom_fields LIKE "%'.$s.'%" OR notes LIKE "%'.$s.'%")';
		  		
		  		if($a=='' && $c=='' && $u=='' && $b=='' && $cp=='' && $g=='')
					$q = 'SELECT * FROM subscribers WHERE list = '.mysqli_real_escape_string($mysqli, $lid).' '.$search_line.' ORDER BY timestamp DESC LIMIT '.$offset.','.$limit;
				else if($a!='')
					$q = 'SELECT * FROM subscribers WHERE list = '.mysqli_real_escape_string($mysqli, $lid).' AND confirmed = 1 AND unsubscribed = 0 AND bounced = 0 AND complaint = 0 '.$search_line.' ORDER BY timestamp DESC LIMIT '.$offset.','.$limit;
				else if($c!='')
					$q = 'SELECT * FROM subscribers WHERE list = '.mysqli_real_escape_string($mysqli, $lid).' AND confirmed = '.$c.' AND bounced = 0 AND complaint = 0 '.$search_line.' ORDER BY timestamp DESC LIMIT '.$offset.','.$limit;
				else if($u!='')
					$q = 'SELECT * FROM subscribers WHERE list = '.mysqli_real_escape_string($mysqli, $lid).' AND unsubscribed = '.$u.' AND bounced = 0 '.$search_line.' ORDER BY timestamp DESC LIMIT '.$offset.','.$limit;
				else if($b!='')
					$q = 'SELECT * FROM subscribers WHERE list = '.mysqli_real_escape_string($mysqli, $lid).' AND bounced = '.$b.' '.$search_line.' ORDER BY timestamp DESC LIMIT '.$offset.','.$limit;
				else if($cp!='')
					$q = 'SELECT * FROM subscribers WHERE list = '.mysqli_real_escape_string($mysqli, $lid).' AND complaint = '.$cp.' '.$search_line.' ORDER BY timestamp DESC LIMIT '.$offset.','.$limit;
				else if($g!='')
					$q = 'SELECT * FROM subscribers WHERE list = '.mysqli_real_escape_string($mysqli, $lid).' AND unsubscribed = 0 AND bounced = 0 AND complaint = 0 AND confirmed = 1 AND gdpr = '.$g.' '.$search_line.' ORDER BY timestamp DESC LIMIT '.$offset.','.$limit;
			  	$r = mysqli_query($mysqli, $q);
			  	if ($r && mysqli_num_rows($r) > 0)
			  	{
			  	    while($row = mysqli_fetch_array($r))
			  	    {
			  			$id = $row['id'];
			  			$name = stripslashes($row['name']);
			  			$email = stripslashes($row['email']);
			  			$unsubscribed = $row['unsubscribed'];
			  			$bounced = $row['bounced'];
			  			$complaint = $row['complaint'];
			  			$confirmed = $row['confirmed'];
			  			$timestamp = parse_date($row['timestamp'], 'short', true);
			  			if($unsubscribed==0)
			  				$unsubscribed = '<span class="label label-success">'._('Subscribed').'</span>';
			  			else if($unsubscribed==1)
			  				$unsubscribed = '<span class="label label-important">'._('Unsubscribed').'</span>';
			  			if($bounced==1)
				  			$unsubscribed = '<span class="label label-inverse">'._('Bounced').'</span>';
				  		if($complaint==1)
				  			$unsubscribed = '<span class="label label-inverse">'._('Marked as spam').'</span>';
				  		if($confirmed==0)
			  				$unsubscribed = '<span class="label">'._('Unconfirmed').'</span>';
			  			if($confirmed==0 && $bounced==1)
			  				$unsubscribed = '<span class="label label-inverse">'._('Bounced').'</span>';
			  			if($confirmed==0 && $complaint==1)
			  				$unsubscribed = '<span class="label label-inverse">'._('Marked as spam').'</span>';
				  			
				  		if($name=='')
				  			$name = '['._('No name').']';
			  			
			  			echo '
			  			
			  			<tr id="'.$id.'">
			  			  <td><a href="#subscriber-info" data-id="'.$id.'" data-toggle="modal" class="subscriber-info">'.$name.'</a></td>
					      <td><a href="#subscriber-info" data-id="'.$id.'" data-toggle="modal" class="subscriber-info">'.$email.'</a></td>
					      <td>'.$timestamp.'</td>
					      <td id="unsubscribe-label-'.$id.'">'.$unsubscribed.'</td>
					      <td>
					    ';
					    
					    if($row['unsubscribed']==0)
							$action_icon = '
								<a href="javascript:void(0)" title="'._('Unsubscribe').' '.$email.'" data-action'.$id.'="unsubscribe" id="unsubscribe-btn-'.$id.'">
									<i class="icon icon-ban-circle"></i>
								</a>
								';
						else if($row['unsubscribed']==1)
							$action_icon = '
								<a href="javascript:void(0)" title="'._('Resubscribe').' '.$email.'" data-action'.$id.'="resubscribe" id="unsubscribe-btn-'.$id.'">
									<i class="icon icon-ok"></i>
								</a>
							';
						if($row['bounced']==1 || $row['complaint']==1)
							$action_icon = '
								-
							';
						if($row['confirmed']==0 && $row['bounced']==0 && $row['complaint']==0)
							$action_icon = '
								<a href="javascript:void(0)" title="'._('Confirm').' '.$email.'" data-action'.$id.'="confirm" id="unsubscribe-btn-'.$id.'">
									<i class="icon icon-ok"></i>
								</a>
							';
						
						echo $action_icon;
					    
					    echo'
					      </td>
					      <td><a href="#delete-subscriber" title="'._('Delete').' '.$email.'?" data-toggle="modal" id="delete-btn-'.$id.'" class="delete-subscriber"><i class="icon icon-trash"></i></a></td>
					      <script type="text/javascript">
					        $("#delete-btn-'.$id.'").click(function(e){
								e.preventDefault(); 
								$("#delete-subscriber-1, #delete-subscriber-2").attr("data-id", '.$id.');
								$("#email-to-delete").text("'.$email.'");
							});
							$("#unsubscribe-btn-'.$id.'").click(function(e){
								e.preventDefault(); 
								action = $("#unsubscribe-btn-'.$id.'").data("action'.$id.'");
								$.post("includes/subscribers/unsubscribe.php", { subscriber_id: '.$id.', action: action},
								  function(data) {
								      if(data)
								      {
								      	if($("#unsubscribe-label-'.$id.'").text()=="'._('Subscribed').'")
								      	{
								      		$("#unsubscribe-btn-'.$id.'").html("<li class=\'icon icon-ok\'></li>");
								      		$("#unsubscribe-btn-'.$id.'").data("action'.$id.'", "resubscribe");
									      	$("#unsubscribe-label-'.$id.'").html("<span class=\'label label-important\'>'._('Unsubscribed').'</span>");
									    }
									    else
									    {
									    	$("#unsubscribe-btn-'.$id.'").html("<li class=\'icon icon-ban-circle\'></li>");
								      		$("#unsubscribe-btn-'.$id.'").data("action'.$id.'", "unsubscribe");
									      	$("#unsubscribe-label-'.$id.'").html("<span class=\'label label-success\'>'._('Subscribed').'</span>");
									    }
									    if($("#unsubscribe-label-'.$id.'").text()=="'._('Unconfirmed').'")
									    {
									    	$("#unsubscribe-btn-'.$id.'").html("<li class=\'icon icon-ban-circle\'></li>");
								      		$("#unsubscribe-btn-'.$id.'").data("action'.$id.'", "confirm");
									      	$("#unsubscribe-label-'.$id.'").html("<span class=\'label label-success\'>'._('Subscribed').'</span>");
									    }
								      }
								      else
								      {
								      	alert("'._('Sorry, unable to unsubscribe. Please try again later!').'");
								      }
								  }
								);
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
			  				<td>'._('No subscribers found.').'</td>
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
		<?php pagination($limit);?>
    </div>   
</div>

<!-- Delete -->
<div id="delete-subscriber" class="modal hide fade">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3><?php echo _('Delete subscriber');?></h3>
  </div>
  <div class="modal-body">
    <p><?php echo _('Delete <span id="email-to-delete" style="font-weight:bold;"></span> from \'this list only\' or \'ALL lists\' in this brand?');?></p>
  </div>
  <div class="modal-footer">
    <a href="javascript:void(0)" id="delete-subscriber-1" data-id="" class="btn"><?php echo _('This list only');?></a>
    <a href="javascript:void(0)" id="delete-subscriber-2" data-id="" class="btn btn-primary"><?php echo _('ALL lists in this brand');?></a>
  </div>
</div>

<!-- Subscriber info card -->
<div id="subscriber-info" class="modal hide fade">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal">&times;</button>
      <h3><?php echo _('Subscriber info');?></h3>
    </div>
    <div class="modal-body">
	    <p id="subscriber-text"></p>
    </div>
    <div class="modal-footer">
      <a href="#" class="btn btn-inverse" data-dismiss="modal"><i class="icon icon-ok-sign" style="margin-top: 5px;"></i> <?php echo _('Close');?></a>
    </div>
  </div>
<script type="text/javascript">
	$("#delete-subscriber-1").click(function(e){
		e.preventDefault(); 
		$.post("includes/subscribers/delete.php", { subscriber_id: $(this).attr("data-id"), option: 1, app: <?php echo get_app_info('app')?> },
		  function(data) {
		      if(data) 
		      {
			      $("#delete-subscriber").modal('hide');
			      $("#"+$("#delete-subscriber-1").attr("data-id")).fadeOut(); 
			  }
		      else alert("<?php echo _('Sorry, unable to delete. Please try again later!')?>");
		  }
		);
	});
	$("#delete-subscriber-2").click(function(e){
		e.preventDefault(); 
		$.post("includes/subscribers/delete.php", { subscriber_id: $(this).attr("data-id"), option: 2, app: <?php echo get_app_info('app')?> },
		  function(data) {
		      if(data) 
		      {
			      $("#delete-subscriber").modal('hide');
			      $("#"+$("#delete-subscriber-2").attr("data-id")).fadeOut(); 
			  }
		      else alert("<?php echo _('Sorry, unable to delete. Please try again later!')?>");
		  }
		);
	});
	$(".subscriber-info").click(function(){
		s_id = $(this).data("id");
		$("#subscriber-text").html("<?php echo _('Fetching');?>..");
		
		$.post("<?php echo get_app_info('path');?>/includes/subscribers/subscriber-info.php", { id: s_id, app:<?php echo get_app_info('app');?> },
		  function(data) {
		      if(data)
		      {
		      	$("#subscriber-text").html(data);
		      }
		      else
		      {
		      	$("#subscriber-text").html("<?php echo _('Oops, there was an error getting the subscriber\'s info. Please try again later.');?>");
		      }
		  }
		);
	});
</script>

<!-- Last activity explanation modal window -->
<div id="last-activity-info" class="modal hide fade">
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h3><?php echo _('What changes a subscriber\'s \'Last activity\' timestamp?');?></h3>
</div>
<div class="modal-body">
    <p><?php echo _('A subscriber\'s \'Last activity\' timestamp is updated when the user:');?></p>
	    <pre><?php echo _('• opened a campaign or autoresponder
• clicked a link in a campaign or autoresponder
• subscribed to a list
• unsubscribed from a list
• bounced
• marked as spam
• confirmed a double opt-in subscription');?></pre>
	</p>
	<p><?php echo _('Subscribers are sorted by \'Last activity\' in the list. Therefore, subscribers who did any of the above activity will float to the top of the subscriber list. You will also see changes in the \'Subscribers activity chart\'.');?></p>
</div>
<div class="modal-footer">
  <a href="#" class="btn btn-inverse" data-dismiss="modal"><i class="icon icon-ok-sign" style="margin-top: 5px;"></i> <?php echo _('Close');?></a>
</div>
</div>

<?php 
	if(!$cron_ares):
	$server_path_array = explode('subscribers.php', $_SERVER['SCRIPT_FILENAME']);
    $server_path = $server_path_array[0];
?>
<!-- Autoresponder cron instructions -->
<div id="ares_cron" class="modal hide fade">
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h3><i class="icon icon-time" style="margin-top: 5px;"></i> <?php echo _('Add a cron job');?></h3>
</div>
<div class="modal-body">
<p><?php echo _('To activate autoresponders, add a');?> <a href="http://en.wikipedia.org/wiki/Cron" target="_blank" style="text-decoration:underline"><?php echo _('cron job');?></a> <?php echo _('with the following command.');?></p>
<h3><?php echo _('Time Interval');?></h3>
<pre id="command">*/1 * * * * </pre>
<h3><?php echo _('Command');?></h3>
<pre id="command">php <?php echo $server_path;?>autoresponders.php > /dev/null 2>&amp;1</pre>
<p><?php echo _('This command needs to be run every minute in order to check the database for any autoresponder emails to send.');?> <br/><em>(<?php echo _('Note that adding cron jobs vary from hosts to hosts, most offer a UI to add a cron job easily. Check your hosting control panel or consult your host if unsure.');?>)</em>.</p>
<p><?php echo _('Once added, wait one minute. If your cron job is functioning correctly, you\'ll see the autoresponder options instead of this modal window when you click on the "Autoresponders" button.');?></p>
</div>
<div class="modal-footer">
  <a href="#" class="btn btn-inverse" data-dismiss="modal"><i class="icon icon-ok-sign"></i> <?php echo _('Okay');?></a>
</div>
</div>
<script type="text/javascript">
	$(document).ready(function() {
		$("#command, #cronjob").click(function(){
			$(this).selectText();
		});
	});
</script>
<?php endif;?>

<script src="js/highcharts/highcharts.js"></script>
		<script type="text/javascript">
			var month=new Array();
			month[0]="Jan";month[1]="Feb";month[2]="Mar";month[3]="Apr";month[4]="May";month[5]="Jun";month[6]="Jul";month[7]="Aug";month[8]="Sep";month[9]="Oct";month[10]="Nov";month[11]="Dec";
		
			var chart;
			$(document).ready(function() {
				Highcharts.setOptions({
			        colors: ['#579fc8', '#ce5c56', '#333333']
			    });
				chart = new Highcharts.Chart({
					chart: {
						renderTo: 'container',
						type: 'areaspline',
						marginBottom: 25
					},
					title: {
						text: false
					},
					subtitle: {
						text: false
					},
					xAxis: {
						categories: [
						<?php 
							$month_array = array();
							$year_array = array();
							$q = 'SELECT MAX(timestamp) FROM subscribers use index (s_list) WHERE list = '.$lid;
							$r = mysqli_query($mysqli, $q);
							if ($r && mysqli_num_rows($r) > 0)
							{
							    while($row = mysqli_fetch_array($r))
							    {
							    	$month_max = $row['MAX(timestamp)'];
							    	
							    	if($month_max=='')
								    	$month_max = time();
								    	
							    	$month = strftime('%m', $month_max)-1;
									$year = strftime('%y', $month_max);
							    }  
							}
							
							for($i=0;$i<12;$i++)
							{
								array_push($month_array, $month);
								array_push($year_array, $year);
								$month--;
								if($month<0)
								{
									$month = 11;
									$year--;
								}
							}
							
							$month_array = array_reverse($month_array);
							$year_array = array_reverse($year_array);
							
							for($i=0;$i<12;$i++)
							{
								echo 'month['.$month_array[$i].']'.'+" '.$year_array[$i].'"';
								if($i<11)
									echo ',';
							}
						?>
						]
					},
					yAxis: {
						title: {
							text: false
						},
						plotLines: [{
							value: 0,
							width: 1,
							color: '#808080'
						}]
					},
					plotOptions: {
						line: {
							stacking: 'normal'
						},
						series: {
			                marker: {
			                    enabled: false
			                }
			            }
					},
					tooltip: {
						formatter: function() {
								return '<b>'+ this.series.name +'</b><br/>'+
								this.x +': '+ this.y;
						}
					},
					legend: {
						enabled: false
					},
					credits: {
		                enabled: false
		            },
					series: [{
		                name: 'Subscribers',
		                data: [
		                <?php 
/*
		                	$graph_array = array();
		                	$onemonth = 2629746;
		                	$maxmonth = $month_max;
			                for($i=0;$i<12;$i++)
			                {
				                $q = 'SELECT timestamp FROM subscribers WHERE timestamp <= '.$maxmonth.' AND list = '.$lid.' AND unsubscribed=0 AND bounced = 0 AND complaint = 0 AND confirmed = 1';
				                $r = mysqli_query($mysqli, $q);
				                if ($r && mysqli_num_rows($r) > 0)
				                {
				                    array_push($graph_array, mysqli_num_rows($r));
				                }
				                else
				                	array_push($graph_array, '0');
				                
				                $maxmonth = $maxmonth - $onemonth;
			                }
			                
			                $graph_array = array_reverse($graph_array);
			                
			                for($i=0;$i<12;$i++)
			                {
				                echo $graph_array[$i];
				                
				                if($i<11)
									echo ',';
			                }
*/
							//Contributed by Rob Henley
							$zeros = '0,0,0,0,0,0,0,0,0,0,0,0';
							
							$q = 'SELECT SUM( IF( s.timestamp <= UNIX_TIMESTAMP(NOW() - INTERVAL 11 MONTH), 1, 0 ) ),
	 						SUM( IF( s.timestamp <= UNIX_TIMESTAMP(NOW() - INTERVAL 10 MONTH), 1, 0 ) ),
	 						SUM( IF( s.timestamp <= UNIX_TIMESTAMP(NOW() - INTERVAL 9 MONTH), 1, 0 ) ),
	 						SUM( IF( s.timestamp <= UNIX_TIMESTAMP(NOW() - INTERVAL 8 MONTH), 1, 0 ) ),
	 						SUM( IF( s.timestamp <= UNIX_TIMESTAMP(NOW() - INTERVAL 7 MONTH), 1, 0 ) ),
	 						SUM( IF( s.timestamp <= UNIX_TIMESTAMP(NOW() - INTERVAL 6 MONTH), 1, 0 ) ),
	 						SUM( IF( s.timestamp <= UNIX_TIMESTAMP(NOW() - INTERVAL 5 MONTH), 1, 0 ) ),
	 						SUM( IF( s.timestamp <= UNIX_TIMESTAMP(NOW() - INTERVAL 4 MONTH), 1, 0 ) ),
	 						SUM( IF( s.timestamp <= UNIX_TIMESTAMP(NOW() - INTERVAL 3 MONTH), 1, 0 ) ),
	 						SUM( IF( s.timestamp <= UNIX_TIMESTAMP(NOW() - INTERVAL 2 MONTH), 1, 0 ) ),
	 						SUM( IF( s.timestamp <= UNIX_TIMESTAMP(NOW() - INTERVAL 1 MONTH), 1, 0 ) ),
	 						SUM( IF( s.timestamp <= UNIX_TIMESTAMP(NOW()), 1, 0 ) )
	 
	 						FROM subscribers s use index (s_list) WHERE list = '.intval($lid).' AND unsubscribed=0 AND bounced = 0 AND complaint = 0 AND confirmed = 1';
	 						
	 			            $r = mysqli_query($mysqli, $q);
			 				if ($r && mysqli_num_rows($r)  > 0)
			 				{
			 				    $graph = implode(",", mysqli_fetch_array($r, MYSQLI_NUM));
			 				    echo trim($graph)==',,,,,,,,,,,' ? $zeros : $graph;
			 				}
			 				else 
			 				{
			 					echo $zeros;
			 				}
			                
		                ?>
		                ]
		            }]
				});
			});
		</script>

<?php include('includes/footer.php');?>
