<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php require_once('../helpers/EmailAddressValidator.php');?>
<?php require_once('../helpers/parsecsv.php');?>
<?php

/********************************/
//Validate imported CSV file
$csvfile = $_FILES['csv_file']['tmp_name'];
$csvfilename = $_FILES['csv_file']['name'];
$csvfilename_array = explode('.', $csvfilename);
$csvfile_ext1 = $csvfilename_array[count($csvfilename_array)-2];
$csvfile_ext2 = $csvfilename_array[count($csvfilename_array)-1];
if($csvfile_ext1=='php' || $csvfile_ext2!='csv' || $csvfilename=='.htaccess') exit;

$csv = new parseCSV();
$csv->heading = false;
$csv->auto($csvfile);
$databasetable = "suppression_list";
$fieldseparator = ",";
$lineseparator = "\n";
$app = isset($_POST['app']) && is_numeric($_POST['app']) ? mysqli_real_escape_string($mysqli, (int)$_POST['app']) : exit;
$time = time();
/********************************/

//get comma separated lists belonging to this app
$q2 = 'SELECT id FROM lists WHERE app = '.$app;
$r2 = mysqli_query($mysqli, $q2);
if ($r2)
{
	$all_lists = '';
    while($row = mysqli_fetch_array($r2)) $all_lists .= $row['id'].',';
    $all_lists = substr($all_lists, 0, -1);
}

if(!file_exists($csvfile)) {
	header("Location: ".get_app_info('path').'/blacklist-suppression?i='.$app.'&e=2'); 
	exit;
}

$file = fopen($csvfile,"r");

if(!$file) {
	echo _('Error opening data file.');
	echo ".\n";
	exit;
}

$size = filesize($csvfile);

if(!$size) {
	echo _('File is empty.');
	echo "\n";
	exit;
}

$csvcontent = fread($file,$size);

fclose($file);

$linearray = array();

foreach(explode($lineseparator,$csvcontent) as $line)
{
	//cleanup line
	$line = trim($line," \t");
	$line = str_replace("\r","",$line);
	$line = str_replace('"','',$line);
	$line = str_replace("'",'',$line);
	
	//get the columns
	$linearray = explode($fieldseparator,$line);
	$columns = count($linearray);
	
	//check if there's more than 1 column
	if($columns>1)
	{
		header("Location: ".get_app_info('path').'/blacklist-suppression?i='.$app.'&e=1'); 
		exit;
	}
	
	//Check if email is valid
	$validator = new EmailAddressValidator;
	if ($validator->check_email_address(trim($line)))
	{
		//Import immediately. Start by checking for duplicates
		$q = 'SELECT id FROM '.$databasetable.' WHERE app = '.$app.' AND email = "'.$line.'"';
		$r = mysqli_query($mysqli, $q);
		if (mysqli_num_rows($r) == 0)
		{			
			//insert email into database
			$q = 'INSERT INTO '.$databasetable.' (app, email, timestamp) values('.$app.', "'.trim($line).'", '.$time.')';
			mysqli_query($mysqli, $q);
		}
		
		//delete email from any existing lists within the brand
		$q2 = 'DELETE FROM subscribers WHERE list IN ('.$all_lists.') AND email = "'.trim($line).'"';
		mysqli_query($mysqli, $q2);
	}
}

//Once everything is imported, remove CSV
//delete CSV file
unlink($csvfile);

//return
header("Location: ".get_app_info('path').'/blacklist-suppression?i='.$app); 

?>