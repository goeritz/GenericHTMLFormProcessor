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
*/

//two lines you need to edit; please alter only the words in quotes
$user="username";  //the username for the database (db) (if any)
$password="password"; //the password for the db (if any)

//in most instances, you can leave the following 3 lines as they are
$host="localhost"; //the host or IP address where the db is located
$database="generic"; //name to be given to the db
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
//Load the $variablen associative array from appropriate array (either POST or GET)
$variablen = (!empty($_POST)) ?  $_POST : $_GET;  

//if no data have been sent
if (empty ($variablen)) {echo "There is no form input to be processed."; exit; }

//determine whether this was a consecutive page of the questionnaire
//grab hidden variable
$identification = $variablen['identification'];
$counter = $variablen['counter'];

//if this is the first page of the survey, i.e., there is no $identification
if (!isset ($identification)) 
	{
	$referer=$_SERVER['HTTP_REFERER'];
	//if no referer info available
	if (!isset ($referer)) 
		{	
		//if there is a manually entered referer
		if (isset ($referer_man)) 
			{
		//remove whitespace and other characters from end and beginning of referer
		$referer_man = rtrim ($referer_man, "/ \t\n\r\0\x0B.");
		$referer_man = ltrim ($referer_man, "/ \t\n\r\0\x0B.");

			//if $referer_man is obviously false or empty
			if (($referer_man == "http://www.goeritz.net/brmic/generic.php") 
			OR ($referer_man == "")
			OR (!strrpos ($referer_man, "|")==false)
			OR (!strrpos ($referer_man, "<")==false)
			OR (!strrpos ($referer_man, ">")==false)
			OR (!strrpos ($referer_man, "[")==false)
			OR (!strrpos ($referer_man, "]")==false))
				{
			echo "The referer you entered seems to be wrong. Please make sure you enter the URL
			 (Web address) of the first page of the study! <br>
			Go <a href=\"javascript:history.go(-2)\">back to the first page of the study</a>, copy the URL (Web address), 
			hit the submit button, on the following page paste the complete URL into the field and press \"Proceed\".
			";
			exit;
				}

			//if $referer_man is correct
			else 
				{
			$referer=$referer_man;
			//replace irrelevant array from Referer-Alert-Page with array from last survey page
			$variablen2 = preg_replace("/Q#Q#Q/", "\"", $variablen2);
			$variablen = unserialize ($variablen2);
				}
			}
		//if there is no manually entered referer
		else
			{
		$variablen2 = serialize ($variablen);
		//replace " with Q#Q#Q
		$variablen2 = preg_replace("/\"/", "Q#Q#Q", $variablen2);
		
		echo "Your data cannot be saved because your browser did not send the HTTP \"Referer\". 
		That is the Web address of the questionnaire you have sumitted.
		This can be for several reasons, but most commonly it is because your browser does not know about this header, has been configured not to send one, or is behind a proxy or firewall that strips it out of the request before it reaches us.
		<br><br>You can try one or more of the following to solve this problem:<br>
		- If you know how it is done configure your browser to send the referer header. <br>
		- Use another browser to fill in this form, which is (hopefully) configured to send the referer header. <br>
		- Use another browser, which is not behind a proxy or firewall and therefore (hopefully) does not strip out the referer header. <br>
		- Go <a href=\"javascript:history.back()\">back to the last page</a>, copy the URL (Web address), 
		hit the submit button, paste the complete URL into the field below and press \"Proceed\":
		<br><br><html><head></head><body><form method=\"post\" action=\"generic.php\">
		<input type=\"text\" name=\"referer_man\" size=\"42\">
		<input type=\"hidden\" name=\"variablen2\" value=\"$variablen2\"><input type=\"submit\" value=\"Proceed\">
		</form></body></html> ";
		exit;
			}
		}
	//if referer da
	else 
	{$referer = rtrim ($referer,"/ \t\n\r\0\x0B.");
	}
}

//input validation: for each line in the array of submitted variables do the following
if ($allfieldsfull) 
	{	
foreach($variablen as $name=>$value)
		{ if ($value == "") 
		 	{ 	echo $errormessage;
		 						echo '<br><br><a href="javascript:history.back()">&lt;---</a>';
		 						exit;	
			}
		}					
	}

//sorts keys in array in numerical and alphabetical order 
if ($order) {ksort ($variablen);}

//determine whether this was the last page of the questionnaire
$next_page = $variablen['next_page'];

//counter for dynamic timestamp and next_page
$counter_page = ++$counter+1;

//connect to db server
mysql_connect($host,$user,$password) or die( 'Unable to connect to database server');

//if this is the first page of a survey
if (!isset ($identification)) 
	//create db, if not already there
	{mysql_query("CREATE DATABASE $database"); }

//select db
@mysql_select_db($database) or die( 'Unable to select database');

if (!isset ($identification)) 
	{
//create table, if not already there
mysql_query ("CREATE TABLE $table (`identification` int(6) NOT NULL auto_increment, 
`page1` LONGTEXT, `participation_date` DATE, `time_submit1` VARCHAR(100), `ip_number` VARCHAR(255), 
`browser` VARCHAR(255), PRIMARY KEY  (`identification`)) TYPE=MyISAM");
	}

//change array, so that time_submit and page are renamed dynamically
foreach($variablen as $name=>$value)
	{ 
if ($name == "next_page") { $name = "page".$counter_page; }
elseif ($name == "counter") {$name = "time_submit".$counter; $value = date("G:i:s"); }
$newarray[$name]=$value; 
	} 
$variablen = $newarray; 

//for each line in the array of submitted variables do the following (traverse array)
foreach($variablen as $name=>$value) {	
		//modify table step by step (add colums according to html input)
		mysql_query ("ALTER TABLE $table ADD $name VARCHAR(255)");
	 								}

if (!isset ($identification)) 		{
//insert new record into db table (into the referer field) and thus generate identifcation (new record)
mysql_query("INSERT INTO $table (page1, participation_date, time_submit1, ip_number, browser) 
VALUES ('$referer', '".date("Y-m-d")."', '".date("G:i:s")."', 
'".$_SERVER['REMOTE_ADDR']."', '".$_SERVER['HTTP_USER_AGENT']."')")or die( "Unable to insert into table!"); 
//grab last value of auto-increment variable "identification" to be used as identifier
$identification = mysql_insert_id(); }

//for each line in the array of submitted variables do the following
	foreach($variablen as $name=>$value)	 {
		   //echo $name." - ".$value."<br>"; //spits out array for control purposes
			//update db table step by step
			mysql_query("UPDATE $table SET $name='$value' WHERE identification=$identification") or die( "Unable to update table"); 
											 }
//close connection
mysql_close();

//if this is the last html page: feedback for the participant
if (!isset ($next_page)) {echo $thank_you_text; }

//if questionnaire consists of still another html page
else 	{ //call up next HTML page and pass on ID and counter
echo "<html><head></head><body onLoad=\"javascript:location.replace('".$next_page."?op56=".$identification."&nr93=".$counter."')\">
<a href=\"".$next_page."?op56=".$identification."&nr93=".$counter."\">Next Page</a></body></html>"; 
//manuelles Weiterklicken
//echo "<html><head></head><body><a href=\"".$next_page."?op56=".$identification."&nr93=".$counter."\">Next Page</a></body></html>"; 
		}
?>

