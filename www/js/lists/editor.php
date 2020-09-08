<script type="text/javascript">
	$(document).ready(function() {
		CKEDITOR.replace( 'thankyou_message', {
			fullPage: true,
			allowedContent: true,
			filebrowserUploadUrl: 'includes/create/upload.php?app=<?php echo get_app_info('app');?>',
			height: '350px',
			extraPlugins: 'codemirror,dragresize'
			
		});
		CKEDITOR.replace( 'goodbye_message', {
			fullPage: true,
			allowedContent: true,
			filebrowserUploadUrl: 'includes/create/upload.php?app=<?php echo get_app_info('app');?>',
			height: '350px',
			extraPlugins: 'codemirror'
		});
		CKEDITOR.replace( 'confirmation_email', {
			fullPage: true,
			allowedContent: true,
			filebrowserUploadUrl: 'includes/create/upload.php?app=<?php echo get_app_info('app');?>',
			height: '350px',
			extraPlugins: 'codemirror'
		});
	});
</script>