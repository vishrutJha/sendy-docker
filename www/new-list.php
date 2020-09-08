<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/create/main.php');?>

<!-- Validation -->
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/validate.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$("#list-form2").validate({
			rules: {
				list_name: {
					required: true	
				}
			},
			messages: {
				list_name: "<?php echo addslashes(_('List name is required'));?>"
			}
		});
		$("#list_name").focus();
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
    	<h2><?php echo _('Add a new list');?></h2><br/>
	    <form action="<?php echo get_app_info('path')?>/includes/subscribers/import-add.php" method="POST" accept-charset="utf-8" class="form-vertical" enctype="multipart/form-data" id="list-form2">
	    	
	    	<label class="control-label" for="list_name"><?php echo _('List name');?></label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="list_name" name="list_name" placeholder="<?php echo _('The name of your new list');?>">
	            </div>
	        </div>
	        
	        <input type="hidden" name="app" value="<?php echo get_app_info('app');?>">
	        
	        <button type="submit" class="btn btn-inverse"><i class="icon icon-plus"></i> <?php echo _('Add');?></button>
	    </form>
    </div>   
</div>
<?php include('includes/footer.php');?>
