<?php $app = isset($_GET['i']) && is_numeric($_GET['i']) ? get_app_info('app') : get_app_info('restricted_to_app'); ?>

<div class="sidebar-nav sidebar-box">
	
	<?php if(get_app_info('campaigns_only')==0):?>
	    <ul class="nav nav-list">
	        <li class="nav-header" style="margin-top: 0px;"><?php echo _('Campaigns');?></li>
	        <li <?php if(currentPage()=='app.php'){echo 'class="active"';}?>><a href="<?php echo get_app_info('path').'/app?i='.$app;?>"><i class="icon-home <?php if(currentPage()=='app.php'){echo 'icon-white';}?>"></i> <?php echo _('All campaigns');?></a></li>
	        <li <?php if(currentPage()=='create.php' || currentPage()=='send-to.php' || currentPage()=='edit.php'){echo 'class="active"';}?>><a href="<?php echo get_app_info('path').'/create?i='.$app;?>"><i class="icon-edit  <?php if(currentPage()=='create.php' || currentPage()=='send-to.php' || currentPage()=='edit.php'){echo 'icon-white';}?>"></i> <?php echo _('New campaign');?></a></li>
	    </ul>	
	<?php endif;?>
	
	<?php if(get_app_info('templates_only')==0):?>
	    <ul class="nav nav-list">
	        <li class="nav-header"><?php echo _('Templates');?></li>
	        <li <?php if(currentPage()=='templates.php' || currentPage()=='edit-template.php' || currentPage()=='create-template.php'){echo 'class="active"';}?>><a href="<?php echo get_app_info('path').'/templates?i='.$app;?>"><i class="icon-envelope <?php if(currentPage()=='templates.php' || currentPage()=='edit-template.php' || currentPage()=='create-template.php'){echo 'icon-white';}?>"></i> <?php echo _('All templates');?></a></li>
	    </ul>
	<?php endif;?>
    
    <?php if(get_app_info('lists_only')==0):?>
	    <ul class="nav nav-list">
	        <li class="nav-header"><?php echo _('Lists & subscribers');?></li>
	        <li <?php if(currentPage()=='list.php' || currentPage()=='subscribers.php' || currentPage()=='new-list.php' || currentPage()=='update-list.php' || currentPage()=='delete-from-list.php' || currentPage()=='edit-list.php' || currentPage()=='custom-fields.php' || currentPage()=='autoresponders-list.php' || currentPage()=='autoresponders-create.php' || currentPage()=='autoresponders-emails.php' || currentPage()=='autoresponders-edit.php' || currentPage()=='autoresponders-report.php' || currentPage()=='search-all-lists.php' || currentPage()=='segments-list.php' || currentPage()=='segment.php'){echo 'class="active"';}?>><a href="<?php echo get_app_info('path').'/list?i='.$app;?>"><i class="icon-align-justify  <?php if(currentPage()=='list.php' || currentPage()=='subscribers.php' || currentPage()=='new-list.php' || currentPage()=='update-list.php' || currentPage()=='delete-from-list.php' || currentPage()=='edit-list.php' || currentPage()=='custom-fields.php' || currentPage()=='autoresponders-list.php' || currentPage()=='autoresponders-create.php' || currentPage()=='autoresponders-emails.php' || currentPage()=='autoresponders-edit.php' || currentPage()=='autoresponders-report.php' || currentPage()=='search-all-lists.php' || currentPage()=='segments-list.php' || currentPage()=='segment.php'){echo 'icon-white';}?>"></i> <?php echo _('View all lists');?></a></li>
	        <li <?php if(currentPage()=='housekeeping-unconfirmed.php' || currentPage()=='housekeeping-inactive.php'){echo 'class="active"';}?>><a href="<?php echo get_app_info('path').'/housekeeping-unconfirmed?i='.$app;?>"><i class="icon-leaf" <?php if(currentPage()=='housekeeping-unconfirmed.php' || currentPage()=='housekeeping-unconfirmed.php'){echo 'icon-white';}?>></i> <?php echo _('Housekeeping');?></a></li>
	        <li <?php if(currentPage()=='blacklist-suppression.php' || currentPage()=='blacklist-blocked-domains.php'){echo 'class="active"';}?>><a href="<?php echo get_app_info('path').'/blacklist-suppression?i='.$app;?>"><i class="icon-ban-circle" <?php if(currentPage()=='blacklist-suppression.php' || currentPage()=='blacklist-blocked-domains.php'){echo 'icon-white';}?>></i> <?php echo _('Blacklist');?></a></li>
	    </ul>
	<?php endif;?>
    
    <?php if(get_app_info('reports_only')==0):?>
	    <ul class="nav nav-list">
	        <li class="nav-header"><?php echo _('Reports');?></li>
	        <li <?php if(currentPage()=='report.php' || currentPage()=='reports.php'){echo 'class="active"';}?>><a href="<?php echo get_app_info('path').'/reports?i='.$app;?>"><i class="icon-bar-chart  <?php if(currentPage()=='report.php' || currentPage()=='reports.php'){echo 'icon-white';}?>"></i> <?php echo _('See reports');?></a></li>
	    </ul>
	<?php endif;?>
    
</div>