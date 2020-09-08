<?php 
	include('../functions.php');
	include('../login/auth.php');
	
	//Init
	$app = isset($_GET['app']) && is_numeric($_GET['app']) ? mysqli_real_escape_string($mysqli, (int)$_GET['app']) : exit;
	$file = $_FILES['upload']['tmp_name'];
	$file_name = $_FILES['upload']['name'];
	$extension_explode = explode('.', $file_name);
	$extension = $extension_explode[count($extension_explode)-1];
	$extension2 = $extension_explode[count($extension_explode)-2];
	if($extension2=='php' || $file_name=='.htaccess') exit;
	
	$time = time();
	chmod("../../uploads",0777);
	
	//Check filetype
	$allowed = array("jpeg", "jpg", "gif", "png");
	if(in_array(strtolower($extension), $allowed)) //if file is an image, allow upload
	{
		//Upload file
		move_uploaded_file($file, '../../uploads/'.$time.'.'.$extension);
		
		//return result
		//echo 'Image uploaded successfully!';
		
		// Required: anonymous function reference number as explained above.
		$funcNum = (int)$_GET['CKEditorFuncNum'] ;
		// Optional: instance name (might be used to load a specific configuration file or anything else).
		$CKEditor = $_GET['CKEditor'] ;
		// Optional: might be used to provide localized messages.
		$langCode = $_GET['langCode'] ;
		
		//get smtp settings
		$q = 'SELECT custom_domain, custom_domain_protocol, custom_domain_enabled FROM apps WHERE id = '.$app;
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				$custom_domain = $row['custom_domain'];
				$custom_domain_protocol = $row['custom_domain_protocol'];
				$custom_domain_enabled = $row['custom_domain_enabled'];
				if($custom_domain!='' && $custom_domain_enabled)
				{
					$parse = parse_url(APP_PATH);
					$domain = $parse['host'];
					$protocol = $parse['scheme'];
					$app_path = str_replace($domain, $custom_domain, APP_PATH);
					$app_path = str_replace($protocol, $custom_domain_protocol, $app_path);
				}
				else $app_path = APP_PATH;
		    }  
		}
		 
		// Check the $_FILES array and save the file. Assign the correct path to a variable ($url).
		$url = $app_path.'/uploads/'.$time.'.'.$extension;
		// Usually you will only assign something here if the file could not be uploaded.
		$message = '';
		
		echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', '$message');</script>";
	}
	else exit;
?>