<?php

/* PHP template for fetching data base data that the participant submitted beforehand

1. paste the following code at the very beginning of the questionnaire page that is to re-use information from the database

<?php
	require_once('fetch_data.inc.php')
?>

2. assign extension .php to that questionnaire page

3. edit db connection details below and copy this file into the same folder as the questionaire page is located

4. edit names of variables that are fetched from db below

5. re-use fetched variables as you please in the questionaire page: display them, make calculations, build them into control structures such as loops or if-else, use them for random assignment to conditions, use them in skip patterns, ...

6. within php code you can call up a fetched variable directly, e.g., $answer1

7. within html code you can call up a fetched variable by opening a php block, e.g., <?php echo $answer1 ?>

8. Done!

*/

session_start();

//the following line should be removed for productive use.
require_once "config.php";

//three lines you need to edit;
//please replace MYSQL_USER, MYSQL_PASSWORD, MYSQL_DB with the correct values for your database
//do not forget to quote them, e.g. replace MYSQL_USER with "your_user_name"
$user=MYSQL_USER;   //the username for the database (db) (if any), e.g. "user"
$password=MYSQL_PASSWORD; //the password for the db (if any), e.g. "password"
$database=MYSQL_DB; //name to be given to the db, e.g. "generic"

//in most instances, you can leave the following 2 lines as they are
$host="localhost"; //the host or IP address where the db is located
$table="generic"; //name to be given to the table within the db

//connect to db server and db
mysql_connect($host,$user,$password);
mysql_select_db($database) or die('Unable to select database '.mysql_error());

//grab data from db where id is id from query string
//edit: variable names
$result=mysql_query("SELECT GHFPvar_page1, GHFPvar_ip_number FROM $table WHERE GHFPvar_id=$_GET[op56]") or die ('Select failed! '.mysql_error());

//get results as an array
$row = mysql_fetch_row($result);

//assign first elements of array to $answer, array numbering starts from 0
$answer1 = $row[0];
$answer2 = $row[1];

//close db connection
mysql_close();
