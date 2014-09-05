<?php
/*
Copyright Anja S. Göritz, 2005, all rights reserved.
This program may be used freely for educational and other noncommercial scholarly uses.
You may copy and modify this program, as long as you copy this copyright notice.
If you do use it, please cite this article in works that benefitted from its use.
Software is "as is," no guarantees or warranties can be made.

GenericHTMLFormProcessor Configuration
================================================================================

This is an example config file for GenericHTMLFormProcessor.

1.) Set the following parameters according to your project and environment.
2.) Rename this file to 'config.php' and upload it alongside generic.php to your web space.

The GenericHTMLFormProcessor parses the input from any HTML form. This script creates a MySQL DB and one table in it
(if not yet present) containing columns that are named according to the variables that were submitted with the HTML form.
For easier identification of projects (especially if several projects use the same script),
unless the browser is configured to omit the referer info it is indicated which HTML form sent the data.

Authors: Anja S. Göritz <anja /at\ goeritz.net>, Jan Vogt <jan.vogt /at\ me.com>
*/

//the following line should be removed if you dont need password protection (e.g., simply put // in front). Otherwise you need
//to put the customized file 'password_protection.inc.php' in the same folder as this file.
require_once "password_protection.inc.php";

//the following line should be removed (e.g., simply put // in front) if you dont need customized input validation. Otherwise you need
//to put the customized file 'input_validation.inc.php' in the same folder as this file.
require_once "input_validation.inc.php";

//three lines you need to edit;
//please enter the correct values for your database
define('MYSQL_USER', 'username');  //the username for the database (db) (if any), e.g. 'user'
define('MYSQL_PASSWORD', 'password'); //the password for the db (if any), e.g. 'password'
define('MYSQL_DB', 'generic_db'); //name to be given to the db, e.g. 'generic_db'

//in most instances, you can leave the following 2 lines as they are
define('MYSQL_HOST', 'localhost'); //the host or IP address where the db is located
define('MYSQL_TABLE', 'generic'); //name to be given to the table within the db

// Productive mode:
// Set the value to "true" if the study is fully developed and all possible branches
// have been run once with all variables set.
// Setting this to true disables all debug information and increases security
// by disallowing the creation of new columns in tables.
define('PRODUCTIVE_MODE', false); // Set to true when collecting data

/* Set the value to "true" if you like to perform a validation on every submitted form element to make sure that it is not blank.
If you do not wish any built-in validation leave value at "false" */
define('ALL_FIELDS_FULL', false);

/* if you have chosen "true" with ALL_FIELDS_FULL, edit the message to be
printed out if any field is left blank by the participant*/
define('ERROR_MESSAGE', 'Please fill in all the fields!');

/* Set the value to "false" if you like to write the submitted form variables in chronological order, 
that is, in the order they were in the html-form
Set the value to "true" if you  like to write the submitted form variables in alphabetical/numerical order; 
this is indispensable for use with SurveyWiz and FactorWiz*/
define('ORDER', true);

//edit the thank you text that is shown after participants have submitted the last study page
define('THANK_YOU_TEXT', 'Thank you! Your answers have been saved.');
