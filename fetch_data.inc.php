<?php

/* PHP template for fetching data base data that the participant submitted beforehand

1. paste contents of this file into the body of the questionnaire page that is to re-use information from the database

2. assign extension .php to that questionnaire page

3. edit db connection details below

4. edit names of variables that are fetched from db below

5. re-use fetched variables as you please: display them, make calculations, build them into control structures such as loops or if-else, use them for random assignment to conditions, use them in skip patterns, ...

6. within php code you can call up a fetched variable directly, e.g., $answer1

7. within html code you can call up a fetched variable by opening a php block, e.g., <?php echo $answer1 ?>

8. Done!

*/


//edit: connection details
$user='username';
$password='password';
$host='localhost';
$database='generic';
$table='generic';

//connect to db server and db
mysql_connect($host,$user,$password);
mysql_select_db($database) or die('Unable to select database '.mysql_error());

//grab data from db where id is id from query string
//edit: variable names
$result=mysql_query("SELECT page1, ip_number FROM $table WHERE identification=$_GET[op56]") or die ('Select failed! '.mysql_error());

//get results as an array
$row = mysql_fetch_row($result);

//assign first elements of array to $answer, array numbering starts from 0
$answer1 = $row[0];
$answer2 = $row[1];

//close db connection
mysql_close();
