<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php include('../reports/main.php');?>
<?php 
	//POST variables
	$id = isset($_POST['id']) && is_numeric($_POST['id']) ? mysqli_real_escape_string($mysqli, (int)$_POST['id']) : exit;
	$app = isset($_POST['app']) && is_numeric($_POST['app']) ? mysqli_real_escape_string($mysqli, (int)$_POST['app']) : exit;

	//get subscriber data
	$q = 'SELECT * FROM subscribers WHERE id = '.$id;
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$name = $row['name'];
			$email = $row['email'];
			$list_id = $row['list'];
			$unsubscribed = $row['unsubscribed'];
			$bounced = $row['bounced'];
			$complaint = $row['complaint'];
			$confirmed = $row['confirmed'];
			$last_campaign = $row['last_campaign'];
			$join_date = $row['join_date'];
			$last_activity = $row['timestamp'];
			$ip = $row['ip']=='' ? 'No data' : $row['ip'];
			$signedup_country = $row['country']=='' ? 'No data' : $row['country'];
			$referrer = $row['referrer'];
			$gdpr = $row['gdpr'];
			$gdpr_status = $gdpr ? 'Yes' : 'No';
			$notes = $row['notes'];
			$notes_br = nl2br($notes);
			
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
					$added_via = 'Application interface';
				else if($added_via==2 || ($join_date!='' && $ip=='No data' && $signedup_country=='No data'))
					$added_via = 'API';
				else if($added_via==3)
					$added_via = 'Standard subscribe form';
			}
			
			if($unsubscribed==0)
  				$status = '<span class="label label-success">'._('Subscribed').'</span>';
  			else if($unsubscribed==1)
  				$status = '<span class="label label-important">'._('Unsubscribed').'</span>';
  			if($bounced==1)
	  			$status = '<span class="label label-inverse">'._('Bounced').'</span>';
	  		if($complaint==1)
	  			$status = '<span class="label label-inverse">'._('Marked as spam').'</span>';
  			if($confirmed!=1)
	  			$status = '<span class="label">'._('Unconfirmed').'</span>';
	  			
	  		//check if name is set
	  		if($name=='')
	  			$name = '<em>'._('not set').'</em>';
	    }  
	}
	
	//get list name
	$q = 'SELECT name FROM lists WHERE id = '.$list_id;
	$r = mysqli_query($mysqli, $q);
	if ($r) while($row = mysqli_fetch_array($r)) $list = $row['name'];
?>
<div class="row-fluid">
	<div class="span1">
		<img src="<?php echo get_gravatar($email);?>" class="gravatar" style="margin-bottom: 5px;"/>
		<?php if($gdpr):?>
		<br/>
		<span class="label label-warning" style="margin: 5px 0 0 0px;">GDPR</span>
		<?php endif;?>
	</div>
    <div class="span5">	 		   	
    	<strong><?php echo _('Name');?>: </strong>
    	<span id="edit-name"><?php echo $name;?></span>
    	<input type="text" name="name" id="name" value="<?php echo strip_tags($name);?>" style="width: 70%; margin-top: 5px; display:none;"/><br/>
    	<script type="text/javascript">
    		$(document).ready(function() {
    			$("#edit-name").mouseover(function(){
	    			$(this).css("text-decoration", "underline");
    			});
    			$("#edit-name").mouseout(function(){
	    			$(this).css("text-decoration", "none");
    			});
    			$("#edit-name").click(function(){
		    		$(this).hide();
		    		$("#name").show();
		    		$("#name").focus();
	    		});
	    		$("#name").blur(function(){
		    		$(this).hide();
		    		$("#edit-name").show();
	    		});
	    		$("#name").keypress(function(e){if(e.which == 13){update_name();}});
				$("#name").focusout(function(){update_name();});
				function update_name()
				{
					$("#edit-name").show(0, function(){
						if($("#name").val() != $(this).text())
						$.post("<?php echo get_app_info('path')?>/includes/subscribers/edit.php", { sid: <?php echo $id;?>, name: $("#name").val() },
						  function(data) {
						      if(data != 1)
						      {
						      	$("#edit-name").text($("#edit-name").text());
						      	alert("<?php echo _('Sorry, unable to save. Please try again later!');?>");
						      }
						      else
						      {
							      $("#edit-name").text($("#name").val());
						      }
						  }
						);
					});
		    		$("#name").hide();
				}
    		});
    	</script>
    	
		<strong><?php echo _('Email');?>: </strong>
		<span id="edit-email"><?php echo $email;?></span>
		<input type="text" name="email" id="email" value="<?php echo $email;?>" style="width: 70%; margin-top: 5px; display:none;"/><br/>
		<script type="text/javascript">
    		$(document).ready(function() {
    			$("#edit-email").click(function(){
		    		$(this).hide();
		    		$("#email").show();
		    		$("#email").focus();
	    		});
	    		$("#edit-email").mouseover(function(){
	    			$(this).css("text-decoration", "underline");
    			});
    			$("#edit-email").mouseout(function(){
	    			$(this).css("text-decoration", "none");
    			});
	    		$("#email").blur(function(){
		    		$(this).hide();
		    		$("#edit-email").show();
	    		});
	    		$("#email").keypress(function(e){if(e.which == 13){update_email();}});
	    		$("#email").focusout(function(){update_email();});
				function update_email()
				{			
					$("#edit-email").show(0, function(){
						if($("#email").val() != $(this).text())
						$.post("<?php echo get_app_info('path')?>/includes/subscribers/edit.php", { sid: <?php echo $id;?>, email: $("#email").val(), app: <?php echo $app;?> },
						  function(data) {
						      if(data != 1)
						      {
						      	 $("#edit-email").text($("#edit-email").text());
						      	 alert(data);
						      }
						      else
						      {
								  $("#edit-email").text($("#email").val());
						      }
						  }
						);
						$("#email").hide();
					});
				}
    		});
    	</script>
    	
    	<?php if($join_date!=''):?>
    	<strong><?php echo _('Joined');?>: </strong>
    	<span><?php echo parse_date($join_date, 'modal', false)?></span>
    	<br/>
    	<?php endif;?>
    	
    	<?php if($last_activity!=''):?>
    	<strong><?php echo _('Last activity');?>: </strong>
    	<span><?php echo parse_date($last_activity, 'modal', false)?></span>
    	<br/>
    	<?php endif;?>
    	
    	<?php if($optin_method!=''):?>
    	<strong><?php echo _('Opt-in method');?>: </strong>
    	<span><?php echo $optin_method;?></span>
    	<br/>
    	<?php endif;?>
    	
    	<?php if($confirmed):?>
    	<strong><?php echo _('Added via');?>: </strong>
    	<span><?php echo $added_via;?></span>
    	<br/>
    	<?php endif;?>
    	
    	<?php if($ip!='' && $ip!='No data'):?>
    	<strong><?php echo _('IP address');?>: </strong>
    	<span><?php echo $ip;?></span>
    	<br/>
    	<?php endif;?>
    	
    	<?php if($signedup_country!='' && $signedup_country!='No data'):?>
    	<strong><?php echo _('Country');?>: </strong>
    	<span><?php echo country_code_to_country($signedup_country).' ('.$signedup_country.')';?></span>
    	<br/>
    	<?php endif;?>
    	
    	<?php if($referrer!=''):?>
    	<strong><?php echo _('Signed up from');?>: </strong>
    	<span><a href="<?php echo $referrer;?>" title="<?php echo $referrer;?>" target="_blank"><?php echo strlen($referrer)>30 ? substr($referrer, 0, 30).'..' : $referrer;?></a></span>
    	<br/>
    	<?php endif;?>
		
		<?php 
			//get custom fields and values
			$q = 'SELECT lists.custom_fields, subscribers.custom_fields AS custom_values FROM lists, subscribers WHERE subscribers.id = '.$id.' AND lists.id = subscribers.list';
			$r = mysqli_query($mysqli, $q);
			if ($r)
			{
			    while($row = mysqli_fetch_array($r))
			    {
					$custom_fields = $row['custom_fields'];
					$custom_values = $row['custom_values'];
			    }
			    
			    //if there's custom fields for this list, show custom fields and their values
			    if($custom_fields!='')
			    {
				    echo '<hr/><h4 style="margin: -15px 0 -15px 0;">Custom fields</h4><hr style="margin-bottom: 5px;"/>';
				    $custom_fields_array = explode('%s%', $custom_fields);
				    
				    $i = 0;
				    foreach($custom_fields_array as $cf)
				    {
					    $cf_array = explode(':', $cf);
					    $cf_field = $cf_array[0];
					    $cf_format = $cf_array[1];
					    $cf_value_array = explode('%s%', $custom_values);
					    $cf_field_without_dash = str_replace('-', '_dash_', $cf_field);
					    $cf_field_without_dash = str_replace('?', '_question_', $cf_field_without_dash);
					    
					    //format date if format is date
					    if($cf_format=='Date' && $cf_value_array[$i]!='')
					    {
						    $cf_value_array[$i] = strftime("%b %d, %Y", $cf_value_array[$i]);
					    }
					    
					    //check if value is empty
					    if($cf_value_array[$i]=='')
					    	$cf_value_array[$i] = '<em>not set</em>';
					    
					    echo '<strong>'.$cf_field.': </strong>';
					    
					    $cf_field_without_dash = str_replace(" ", "", $cf_field_without_dash);
					    
					    //Check if custom field name begins with a number, if so, append 'ncf_' to custom field name
					    $cf_field_without_dash_first_number = is_numeric(substr($cf_field_without_dash, 0, 1)) ? true : false;
					    if($cf_field_without_dash_first_number) $cf_field_without_dash = 'ncf_'.$cf_field_without_dash;
					    
					    echo'
					    <span id="edit-'.$cf_field_without_dash.'">'.$cf_value_array[$i].'</span>
					    <input type="text" name="'.$cf_field_without_dash.'" id="'.$cf_field_without_dash.'" value="'.strip_tags($cf_value_array[$i]).'" style="width: 70%; margin-top: 5px; display:none;"/>
					    <br/>';
					    ?>
					    
					    <script type="text/javascript">
				    		$(document).ready(function() {
				    			$("#edit-<?php echo $cf_field_without_dash;?>").click(function(){
						    		$(this).hide();
						    		$("#<?php echo $cf_field_without_dash;?>").show();
						    		$("#<?php echo $cf_field_without_dash;?>").focus();
					    		});
					    		$("#edit-<?php echo $cf_field_without_dash;?>").mouseover(function(){
					    			$(this).css("text-decoration", "underline");
				    			});
				    			$("#edit-<?php echo $cf_field_without_dash;?>").mouseout(function(){
					    			$(this).css("text-decoration", "none");
				    			});
					    		$("#<?php echo $cf_field_without_dash;?>").blur(function(){
						    		$(this).hide();
						    		$("#edit-<?php echo $cf_field_without_dash;?>").show();
					    		});
					    		$("#<?php echo $cf_field_without_dash;?>").keypress(function(e){
								    if(e.which == 13)
								    {
								    	update_<?php echo $cf_field_without_dash;?>();
								    }
								});
								$("#<?php echo $cf_field_without_dash;?>").focusout(function(){update_<?php echo $cf_field_without_dash;?>();});
								function update_<?php echo $cf_field_without_dash;?>()
								{			
									$("#edit-<?php echo $cf_field_without_dash;?>").show(0, function(){
										if($("#<?php echo $cf_field_without_dash;?>").val() != $(this).text())
										$.post("<?php echo get_app_info('path')?>/includes/subscribers/edit.php", { sid: <?php echo $id;?>, <?php echo $cf_field_without_dash;?>: $("#<?php echo $cf_field_without_dash;?>").val() },
										  function(data) {
										      if(data != 1)
										      {
										      	 $("#edit-<?php echo $cf_field_without_dash;?>").text($("#edit-<?php echo $cf_field_without_dash;?>").text());
										      	 alert(data);
										      }
										      else
										      {
												  $("#edit-<?php echo $cf_field_without_dash;?>").text($("#<?php echo $cf_field_without_dash;?>").val());
										      }
										  }
										);
										$("#<?php echo $cf_field_without_dash;?>").hide();
									});
								}
				    		});
				    	</script>
					    
					    <?php
					    
					    $i++;
				    }
				}
			}
		?>
    </div>
    <div class="span6">
		<strong><?php echo _('List');?></strong>: <span class="label label-info"><?php echo $list;?></span><br/>
		<strong><?php echo _('Status');?></strong>: <?php echo $status;?><br/>
		<strong><?php echo _('Notes');?>:</strong>
		<br/>
		<div id="subscriber-notes" class="alert alert-info">
			<div id="edit-note"><?php echo $notes=='' ? _('Click to add a note for this subscriber.') : $notes_br;?></div>
			<textarea id="note" style="display:none; width: 100%; height: 190px;"><?php echo $notes;?></textarea>
		</div>
		<script type="text/javascript">
			$("#edit-note").mouseover(function(){
    			$(this).css("text-decoration", "underline");
			});
			$("#edit-note").mouseout(function(){
    			$(this).css("text-decoration", "none");
			});
			$("#edit-note").click(function(){
	    		$(this).hide();
	    		$("#note").show();
	    		$("#note").focus();
    		});
    		$("#note").blur(function(){
	    		$(this).hide();
	    		$("#edit-note").show();
	    		$("#edit-note").html("<?php echo _('Saving..')?>");
    		});
			$("#note").focusout(function(){update_note();});
			$("#note").keypress(function(e){if((e.metaKey || e.ctrlKey) && e.which == 13){$(this).blur();}});
			function update_note()
			{
				$("#edit-note").show(1, function(){
					if($("#note").val() != $(this).text())
					$.post("<?php echo get_app_info('path')?>/includes/subscribers/edit.php", { sid: <?php echo $id;?>, note: $("#note").val() },
					  function(data) {
					      if(data != 1)
					      {
					      	$("#edit-note").text($("#edit-note").text());
					      	alert("<?php echo _('Sorry, unable to save. Please try again later!');?>");
					      }
					      else
					      {
						      if($("#note").val()=="")
						      	  $("#edit-note").html("<?php echo _('Click to add a note for this subscriber.');?>");
						      else
							      $("#edit-note").html(nl2br($("#note").val()));
					      }
					  }
					);
				});
	    		$("#note").hide();
			}
			function nl2br (str, is_xhtml) {   
			    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';    
			    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ breakTag +'$2');
			}
		</script>
    </div>
</div>
<hr>
<h4><?php echo _('Campaign activity');?></h4><br/>
<table class="table table-striped table-condensed responsive">
  <thead>
    <tr>
      <th><?php echo _('Campaign');?></th>
      <th><?php echo _('Opens');?></th>
      <th><?php echo _('Clicks');?></th>
      <th><?php echo _('Country');?></th>
    </tr>
  </thead>
  <tbody>
  	<?php 
	  	$q = 'SELECT * FROM campaigns WHERE userID = '.get_app_info('main_userID').' AND app = '.$app.' ORDER BY sent DESC LIMIT 20';
	  	$r = mysqli_query($mysqli, $q);
	  	if ($r && mysqli_num_rows($r) > 0)
	  	{
	  		$has_activity = false;
	  	    while($row = mysqli_fetch_array($r))
	  	    {
	  	    	//opens and country data
	  	    	$country = '';
	  	    	$s_id = '';
	  	    	$open_count = 0;
	  	    	$campaign_id = $row['id'];
	  			$title = $row['title'];
	  			$opens = $row['opens'];
	  			$opens_array = explode(',', $opens);
	  			$links_clicked = '';
	  			for($z=0;$z<count($opens_array);$z++)
	  	    	{
		  			$subscriber_id = explode(':', $opens_array[$z]);
		  			if($subscriber_id[0]==$id)
		  			{
		  				$s_id = $subscriber_id[0];
		  				$country = $subscriber_id[1];
		  				$has_activity = true;
		  				$open_count += 1;
		  			}
		  		}
		  		
		  		//get links data
		  		$q2 = 'SELECT link, clicks FROM links WHERE campaign_id = '.$campaign_id;
		  		$r2 = mysqli_query($mysqli, $q2);
		  		if ($r2 && mysqli_num_rows($r2) > 0)
		  		{
		  			$click_count = 0;
		  			$link_array = array();
		  		    while($row = mysqli_fetch_array($r2))
		  		    {
		  				$clicks = $row['clicks'];
		  				$link = $row['link'];
		  				$clicks_array = explode(',', $clicks);
		  				foreach($clicks_array as $ca)
		  				if($ca==$id)
		  				{
		  					$click_count++;
		  					if(!in_array($link, $link_array))
			  					array_push($link_array, $link);
		  				}
		  		    }  
		  		    for($y=0;$y<count($link_array);$y++)
		  		    {		  		    	
		  		    	$links_clicked .= strlen($link_array[$y])>32 ? substr($link_array[$y], 0, 32).'..<br/>' : $link_array[$y].'<br/>';
		  		    }
		  		}
		  		
		  		if($s_id!='')
		  		{
		  			$cty = country_code_to_country($country);
		  			if($cty=='')
		  				$cty = _('Not detected');
		  				
		  			echo '<tr>';
		  			
		  			if(!get_app_info('is_sub_user') || (get_app_info('is_sub_user') && get_app_info('reports_only')==0))
				  		echo '<td><a href="'.get_app_info('path').'/report?i='.$app.'&c='.$campaign_id.'" title="'._('View report for').' '.$title.'">'.$title.'</a></td>';
				  	else
				  		echo '<td>'.$title.'</td>';
				    
				    echo '<td>'.$open_count.'</td>';
				    
				    if(count($link_array)==0)
				    	echo '<td>0</td>';
				    else
					    echo '<td><a href="javascript:void(0)" title="'._('Links clicked').'" data-content="'.$links_clicked.'" id="click-links-'.$campaign_id.'" style="text-decoration:underline;">'.$click_count.'</a></td>';
				    
				    echo '
				      <td>'.$cty.'</td>
				      <script type="text/javascript">
							$(document).ready(function() {
								$("#click-links-'.$campaign_id.'").popover({placement:"left"})
							});	
						</script>
				    </tr>
				  	';
		  		}
	  	    }  
	  	    if(!$has_activity)
	  	    {
		  	    echo '
			  	<tr>
			      <td>'._('No activity.').'</td>
			      <td></td>
			      <td></td>
			      <td></td>
			    </tr>
			  	';
	  	    }
	  	}
	  	else
	  	{
		  	echo '
		  	<tr>
		      <td>'._('No activity.').'</td>
		      <td></td>
		      <td></td>
		      <td></td>
		    </tr>
		  	';
	  	}
  	?>
  </tbody>
</table>