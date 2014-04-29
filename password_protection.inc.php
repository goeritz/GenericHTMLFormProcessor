<?php
/* PHP template for password authentication using a password array 

1. paste contents of this file after the <?php tag in Generic HTML Form Processor 

2. put new usernames & corresponding password separated by : in the password array below

3. paste next line in HTML form that is to be validated 
<input name="source" type="hidden" value="1">

4. Done!

*/


if ($_POST[source] == "1") //validated HTML form contains variable "source" with value "1" 

{$access = array('heart:blue','love:green'); //put username & corresponding password separated by : in here

	foreach ($access as $line) 
	{
	list($user, $pw) = explode(':', $line); 
		if (($user == $_POST[username]) && ($pw == $_POST[password])) 
		{$auth = true; 
		break; 
		} 
	} 
	
	if (!$auth)  //username and/or password was left blank or did not correspond to each other
	{echo "You need to enter a valid username and password. They are case-sensitive.";
	echo "<br><br><a href=\"javascript:history.back()\">back</a>";
	exit;
	}
}