<?php
/* Copyright Anja S. Göritz, 2005, all rights reserved. 
This program may be used freely for educational and other noncommercial scholarly uses. 
You may copy and modify this program, as long as you copy this copyright notice. 
If you do use it, please cite this article in works that benefitted from its use. 
Software is "as is," no guarantees or warranties can be made. 

This script parses the input from any HTML form. Among others it can process input from forms that were created using SurveyWiz (Copyright: Michael Birnbaum). 
This script creates a MySQL DB and one table in it (if not yet present) containing columns that are named according to the variables that were submitted with the HTML form. 
The table columns and later their input are created/entered in alphabetical/numerical order. For easier identification of users (especially if several people use the same script) 
the referer variable indicates which HTML form sent the data.

Authors: Anja Göritz <goeritz /at\ psychologie.uni-freiburg.de>, Jan Vogt <jan.vogt /at\ me.com>
*/

//Never delete the following line
session_start();

//the following line should be removed for productive use.
require_once "config.php";

//the following line should be removed if you dont need password protection. Otherwise you have
//to put the customized file 'password_protection.inc.php' in the same folder as this file.
require_once "password_protection.inc.php";

//the following line should be removed if you dont need input validation. Otherwise you have
//to put the customized file 'input_validation.inc.php' in the same folder as this file.
require_once "input_validation.inc.php";

//three lines you need to edit;
//please replace MYSQL_USER, MYSQL_PASSWORD, MYSQL_DB with the correct values for your database
//do not forget to quote them, e.g. replace MYSQL_USER with "your_user_name"
$user=MYSQL_USER;  //the username for the database (db) (if any), e.g. "user"
$password=MYSQL_PASSWORD; //the password for the db (if any), e.g. "password"
$database=MYSQL_DB; //name to be given to the db, e.g. "generic"

//in most instances, you can leave the following 2 lines as they are
$host="localhost"; //the host or IP address where the db is located
$table="generic"; //name to be given to the table within the db

/* Set the value of the following allfieldsfull-variable to "true" if you would 
like to perform a validation on every submitted form element to make sure that it is not blank. 
If you do not wish any validation leave value at "false" */
$allfieldsfull=false; 

/* if you have chosen "true" at the above option, edit the message to be 
printed out if any field was left blank by the participant*/
$errormessage='Please fill in all the fields!';

/* Set the value of the order-variable to "false" if you would like to write the 
submitted form variables in chronological order, that is, in the order they were in the html-form
Set the value of the order-variable to "true" if you would like to write the 
submitted form variables in alphabetical/numerical order; this is indispensable for use with 
SurveyWiz and FactorWiz*/
$order=true; 

//edit the thank you text that is shown after participants have submitted the last survey page
$thank_you_text='Thank you! Your answers have been saved.';

//leave the rest from here on unchanged
//#####################################################################
//Load the $unsafe_variables associative array from appropriate array (either POST or GET)
$unsafe_data = array_merge($_GET, $_POST);
$unsafe_control_keys = array_filter(array_keys($unsafe_data), function($key){
	return strpos($key, 'GHFPvar_') === 0;
});
$unsafe_variables_keys = array_filter(array_keys($unsafe_data), function($key){
	return strpos($key, 'GHFPvar_') !== 0;
});
$unsafe_control_variables = array_intersect_key($unsafe_data, array_flip($unsafe_control_keys));
$unsafe_variables = array_intersect_key($unsafe_data, array_flip($unsafe_variables_keys));

//Backward compatibility for non-prefixed control variables:
foreach(array('next_page', 'identification', 'counter') as $value){ // Put all old non-prefixed variables in this array
	if(!isset($unsafe_control_variables['GHFPvar_' . $value]) && isset($unsafe_variables[$value])){
		$unsafe_control_variables['GHFPvar_' . $value] = $unsafe_variables[$value];
	}
}

//determine whether this was the last page of the questionnaire
$next_page = $unsafe_control_variables['GHFPvar_next_page'];

//if no data have been sent
if (empty ($unsafe_variables) && empty($next_page)) {echo "There is no form input to be processed."; exit; }

//if this is the first page of the survey, i.e., there is no $_SESSION['identification']
if (!isset ($_SESSION['identification']))
	{
	$referer=$_SERVER['HTTP_REFERER'];
	}

//input validation: for each line in the array of submitted variables do the following
if ($allfieldsfull) 
	{	
foreach($unsafe_variables as $name=>$value)
		{ if ($value == "") 
		 	{ 	echo $errormessage;
		 						echo '<br><br><a href="javascript:history.back()">&lt;---</a>';
		 						exit;	
			}
		}					
	}

//counter for dynamic timestamp and next_page
$counter_page = ++$_SESSION['counter']+1;

//Add meta-data (page address and time of submit) for subsequent pages in multi page questionaries.
if(isset($next_page)){
	$unsafe_variables['page'.$counter_page] = $next_page;
}
if(isset($_SESSION['counter'])){
	$unsafe_variables['time_submit'.$_SESSION['counter']] = date("G:i:s");
}

//sorts keys in array in numerical and alphabetical order
if ($order){
	ksort ($unsafe_variables);
}

// Establish mysql connection
$mysql = new mysqli($host, $user, $password);
if($mysql->connect_error){
	die('Could not connect to database: ' . $mysql->connect_error);
}

// Escape all input keys and values and build description for columns to prevent SQL injections
$escaped_values = array();
$escaped_keys = array();
$column_def = array();
foreach ($unsafe_variables as $key => $value) {
	$escaped_values[$key] = $mysql->real_escape_string($value);
	$escaped_keys[$key] = $mysql->real_escape_string($key);
	$column_def[$key] = sprintf('`%s` VARCHAR(255)', $mysql->real_escape_string($key));
}

// Try to get information about table to use. If unsuccessful create DB and table.
$res = $mysql->query("SHOW COLUMNS FROM $table FROM $database");
if($mysql->errno != 0){
	if($mysql->errno == 1146){ // Table and or Database doesnt exist. Let's create it.
		$mysql->query("CREATE DATABASE IF NOT EXISTS $database") or
			die('Could not create database (' . $mysql->errno . '): ' . $mysql->error);
		$mysql->select_db($database);

		if(count($column_def) > 0){
			$columns = implode(', ', $column_def) . ', ';
		}
		else{
			$columns = '';
		}
		$mysql->query("CREATE TABLE $table (`GHFPvar_id` int(6) NOT NULL auto_increment,
											`GHFPvar_page1` LONGTEXT,
											`GHFPvar_participation_date` DATE,
											`GHFPvar_time_submit1` VARCHAR(100),
											`GHFPvar_ip_number` VARCHAR(255),
											`GHFPvar_browser` VARCHAR(255),
											$columns
											PRIMARY KEY (`GHFPvar_id`))
							  ENGINE=MyISAM") or
			die('Could not create table (' . $mysql->errno . '): ' . $mysql->error);
	}
	else{ // Unknown Error
		die('Unable to get information about table (' . $mysql->errno . '): ' . $mysql->error);
	}
}
else{ // Table exists but might need changes
	$mysql->select_db($database);

	$known_keys = array();
	while($row = $res->fetch_assoc()){ // Collect columnnames from database
		$known_keys[] = $row['Field'];
	}
	$new_columns = array_diff_key($column_def, array_flip($known_keys)); // Collect column definitions for keys not yet in DB
	if(count($new_columns) > 0){
		$columns = implode(', ', $new_columns);
		$mysql->query("ALTER TABLE $table ADD ($columns)") or
			die('Could not alter table (' . $mysql->errno . '): ' . $mysql->error);
	}
}

if (!isset ($_SESSION['identification'])){
	//insert new record into db table (into the referer field) and thus generate identifcation (new record)
	if(count($unsafe_variables) > 0){
		$columnnames = ', ' . implode(', ', $escaped_keys);
		$values = ', \'' . implode('\', \'', $escaped_values) . '\'';
	}
	$mysql->query("INSERT INTO $table (GHFPvar_page1,
									   GHFPvar_participation_date,
									   GHFPvar_time_submit1,
									   GHFPvar_ip_number,
									   GHFPvar_browser
									   $columnnames
									  )
						  VALUES ('" . $mysql->real_escape_string($referer) . "',
								  '" . date("Y-m-d") . "',
								  '" . date("G:i:s") . "',
								  '" . $mysql->real_escape_string($_SERVER['REMOTE_ADDR']) . "',
								  '" . $mysql->real_escape_string($_SERVER['HTTP_USER_AGENT']) . "'
								  $values
								 )") or
		die('Unable to insert into table (' . $mysql->errno . '): ' . $mysql->error);
	//grab last value of auto-increment variable "GHFPvar_id" to be used as identifier
	$_SESSION['identification'] = $mysql->insert_id;
}
else if(count($unsafe_variables) > 0){
	//Generate SET string
	$expressions = array();
	foreach($unsafe_variables as $key => $value){
		$expressions[] = sprintf('%s=\'%s\'', $escaped_keys[$key], $escaped_values[$key]);
	}
	$expressions_str = implode(', ', $expressions);
	$mysql->query("UPDATE $table SET $expressions_str WHERE GHFPvar_id=" . $_SESSION['identification']);
}

$mysql->close();

//if this is the last html page: feedback for the participant
if (!isset ($next_page)) {
	session_destroy();
	echo $thank_you_text; 
}

//if questionnaire consists of still another html page
else { //call up next HTML page and pass on ID and counter
	$_SESSION['counter'] = $_SESSION['counter'];
	echo "<html><head></head><body onLoad=\"javascript:location.replace('".$next_page."?op56=".$_SESSION['identification']."&nr93=".$_SESSION['counter']."')\">
<a href=\"".$next_page."?op56=".$_SESSION['identification']."&nr93=".$_SESSION['counter']."\">Next Page</a></body></html>";
	//manuelles Weiterklicken
	//echo "<html><head></head><body><a href=\"".$next_page."?op56=".$_SESSION['identification']."&nr93=".$_SESSION['counter']."\">Next Page</a></body></html>"; 
}
?>

