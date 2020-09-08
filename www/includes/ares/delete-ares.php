<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	$id = isset($_POST['id']) && is_numeric($_POST['id']) ? mysqli_real_escape_string($mysqli, (int)$_POST['id']) : exit;
	
	$q = 'DELETE FROM ares WHERE id = '.$id;
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
		$q3 = 'SELECT id FROM ares_emails WHERE ares_id = '.$id;
		$r3 = mysqli_query($mysqli, $q3);
		if ($r3 && mysqli_num_rows($r3) > 0)
		{
		    while($row = mysqli_fetch_array($r3))
		    {
				$ares_email_id = $row['id'];
				
				if(file_exists('../../uploads/attachments/a'.$ares_email_id))
				{
					$files = glob('../../uploads/attachments/a'.$ares_email_id.'/*'); // get all file names
					foreach($files as $file){
					    unlink($file); 
					}
					rmdir('../../uploads/attachments/a'.$ares_email_id);
				}
				
				//Delete links
				$q4 = 'DELETE FROM links WHERE ares_emails_id = '.$ares_email_id;
				mysqli_query($mysqli, $q4);
		    }  
		}
		
		//Delete autoresponder emails
		$q2 = 'DELETE FROM ares_emails WHERE ares_id = '.$id;
		mysqli_query($mysqli, $q2);
		
		echo true;
	}
	
?>