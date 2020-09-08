<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	$id = isset($_POST['id']) && is_numeric($_POST['id']) ? mysqli_real_escape_string($mysqli, (int)$_POST['id']) : exit;
	
	//delete links
	$q = 'SELECT id FROM campaigns WHERE app = '.$id;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$campaign_id = $row['id'];
			
			$q2 = 'DELETE FROM links WHERE campaign_id = '.$campaign_id;
			mysqli_query($mysqli, $q2);
	    }  
	}
	
	//Delete campaigns
	$q3 = 'DELETE FROM campaigns WHERE app = '.$id;
	$r3 = mysqli_query($mysqli, $q3);
	
	//Delete subscribers, ARs and Segs
	$q = 'SELECT id FROM lists WHERE app = '.$id;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0) 
	{
		while($row = mysqli_fetch_array($r)) 
		{
			$list_id = $row['id'];
			
			//Delete subscribers
			$q2 = 'DELETE FROM subscribers WHERE list = '.$list_id;
			mysqli_query($mysqli, $q2);
			
			//Delete autoresponders
			$q2 = 'SELECT id FROM ares WHERE list = '.$list_id;
			$r2 = mysqli_query($mysqli, $q2);
			if ($r2 && mysqli_num_rows($r2) > 0)
			{
			    while($row = mysqli_fetch_array($r2))
			    {
					$ares_id = $row['id'];
					
					$q2 = 'DELETE FROM ares_emails WHERE ares_id = '.$ares_id;
					mysqli_query($mysqli, $q2);
			    }  
			    
			    $q2 = 'DELETE FROM ares WHERE list = '.$list_id;
				mysqli_query($mysqli, $q2);
			}
			
			//Delete segments
			$q2 = 'SELECT id FROM seg WHERE list = '.$list_id;
			$r2 = mysqli_query($mysqli, $q2);
			if ($r2 && mysqli_num_rows($r2) > 0)
			{
			    while($row = mysqli_fetch_array($r2))
			    {
					$seg_id = $row['id'];
					
					$q3 = 'DELETE FROM seg_cons WHERE seg_id = '.$seg_id;
					mysqli_query($mysqli, $q3);
					
					$q4 = 'DELETE FROM subscribers_seg WHERE seg_id = '.$seg_id;
					mysqli_query($mysqli, $q4);
			    }  
			    
			    $q5 = 'DELETE FROM seg WHERE list = '.$list_id;
				mysqli_query($mysqli, $q5);
			}
		}
	}
	
	//Delete lists
	$q3 = 'DELETE FROM lists WHERE app = '.$id;
	mysqli_query($mysqli, $q3);
	
	
	//Delete login
	$q = 'DELETE FROM login WHERE app = '.$id;
	mysqli_query($mysqli, $q);
	
	//Delete templates
	$q = 'DELETE FROM template WHERE app = '.$id;
	mysqli_query($mysqli, $q);
	
	//Delete zapier
	$q = 'DELETE FROM zapier WHERE app = '.$id;
	mysqli_query($mysqli, $q);
	
	//Delete blocked_domains
	$q = 'DELETE FROM blocked_domains WHERE app = '.$id;
	mysqli_query($mysqli, $q);
	
	//Delete suppression_list
	$q = 'DELETE FROM suppression_list WHERE app = '.$id;
	mysqli_query($mysqli, $q);
	
	//Delete skipped_emails
	$q = 'DELETE FROM skipped_emails WHERE app = '.$id;
	mysqli_query($mysqli, $q);
	
	//Delete app
	$q = 'DELETE FROM apps WHERE id = '.$id;
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
	    echo true;
	}
	
?>