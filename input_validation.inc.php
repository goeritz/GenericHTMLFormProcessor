<?php
/* PHP template for input validation 

1. paste contents of this file after the <?php tag in Generic HTML Form Processor 

2. paste next line in HTML form that is to be validated 
<input name="from_page" type="hidden" value="1">

3. delete unwanted checks on $_POST[] variables below 

4. Done! The remaining steps are optional for advanced users

4a. to validate input from several HTML forms, paste the next line in each HTML form that is to be validated and adjust the value of the hidden variable
<input name="from_page" type="hidden" value="2">

4b. you may add your own input checks below

*/

if ($_POST[from_page] == "1") //HTML form to be validated contains hidden variable "from_page" with value "1" 
{
	if ($_POST[apples] < "1")   //value of input field is less than 1
	{
	echo "Please tell us whether you like apples.";
	echo "<br><br><a href=\"javascript:history.back()\">back</a>";
	exit;
	}

	if ($_POST[peaches] == "" || $_POST[age] == "") 	//one or both input fields are empty
	{
	echo "Please tell us your age and whether you like peaches!";
	echo "<br><br><a href=\"javascript:history.back()\">back</a>";
	exit;
	}

	if (!is_numeric ($_POST[age]))  	//age is not numeric
	{
	echo "Please indicate your age in full years using numbers only!";
	echo "<br><br><a href=\"javascript:history.back()\">back</a>";
	exit;
	}

	if (strlen($_POST[age]) < 2)  	//age is less than 2 characters long
	{
	echo "Are you really that young?";
	echo "<br><br><a href=\"javascript:history.back()\">back</a>";
	exit;
	}
}


