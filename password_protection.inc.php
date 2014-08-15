<?php
/* PHP template for password authentication using a password array 

1. uncomment the line

   require_once "password_protection.inc.php";

   in Generic HTML Form Processor and put this file in the same directory.

2. put new usernames & corresponding password in the associative password array below

3. Make sure the first HTML Form sends a username in a variable named "GHFPvar_user" and
   a password in a variable named "GHFPvar_password". See password.htm as an example.

4. Done!

*/

$access = array(
	'heart' => 'blue',  //replace with list of your usernames as keys and corresponding passwords as values
	'love' => 'green'
);

// Get user and password for backward compatibility
if(isset($_POST['GHFPvar_user'])){
	$user = $_POST['GHFPvar_user'];
}
else{
	$user = null;
}
if(isset($_POST['GHFPvar_password'])){
	$pass = $_POST['GHFPvar_password'];
}
else{
	$pass = null;
}

if($user && $pass){
	if(isset($access[$user]) && $access[$user] == $pass){
		$_SESSION['auth'] = $user;
	}
}

if(!isset($_SESSION['auth']) || !$_SESSION['auth']){
	echo "You need to enter a valid username and password. They are case-sensitive.";
	echo "<br><br><a href=\"javascript:history.back()\">back</a>";
	exit(1);
}