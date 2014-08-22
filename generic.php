<?php
/* Copyright Anja S. Göritz, 2005, all rights reserved.
This program may be used freely for educational and other noncommercial scholarly uses.
You may copy and modify this program, as long as you copy this copyright notice.
If you do use it, please cite this article in works that benefitted from its use.
Software is "as is," no guarantees or warranties can be made.

This script parses the input from any HTML form. This script creates a MySQL DB and one table in it
(if not yet present) containing columns that are named according to the variables that were submitted with the HTML form.
For easier identification of projects (especially if several projects use the same script),
unless the browser is configured to omit the referer info it is indicated which HTML form sent the data.

Authors: Anja S. Göritz <anja /at\ goeritz.net>, Jan Vogt <jan.vogt /at\ me.com>
*/

//Never delete the following line
session_start();

//the following line should be removed (e.g., simply put // in front) for productive use.
require_once "config.php";

//the following line should be removed if you dont need password protection (e.g., simply put // in front). Otherwise you need
//to put the customized file 'password_protection.inc.php' in the same folder as this file.
require_once "password_protection.inc.php";

//the following line should be removed (e.g., simply put // in front) if you dont need input validation. Otherwise you need
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

// Productive mode:
// Set to true if the study fully developed and at least all possible branches
// are run once with all variables set.
// Setting this to true disables all debug information and increases security
// by disallowing the creation of new columns in tables.
$productive = false; // Set to true when collecting data

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

// Only print $reason if not in productive mode
function contextDie($reason = 'Unspecified Error!') {
  if($productive) {
    // Print generic Error Message
    die('Unexpected Error!');
  } else {
    die($reason);
  }
}


/**
 * Prepares row data in the form of column=>value for insertion or update via
 * prepared expressions.
 */
class RowData {
  private $dataArray;

  /**
   * contruct with data array in the form column=>value
   */
  function __construct($dataArray) {
    $this->dataArray = $dataArray;
  }

  /**
   * Call to insert the row data represented in this object into the given $table
   * using $mysql as the database connection. Returns only if successful.
   * Invalidates this object to prevent douple use.
   * Adds general information to row.
   */
  public function insertInto($table, $mysql) {
    $this->dataArray['GHFPvar_page1'] = $_SERVER['HTTP_REFERER'];
    $this->dataArray['GHFPvar_participation_date'] = date("Y-m-d");
    $this->dataArray['GHFPvar_time_submit1'] = date("G:i:s");
    $this->dataArray['GHFPvar_ip_number'] = $_SERVER['REMOTE_ADDR'];
    $this->dataArray['GHFPvar_browser'] = $_SERVER['HTTP_USER_AGENT'];
    $first = true;
    foreach ($this->dataArray as $key => $v) {
      $escapedKey = $mysql->real_escape_string($key);
      if ($first) {
        $columnstr = "`$escapedKey`";
        $valueStr = '?';
        $first = false;
      } else {
        $columnstr .= ", `$escapedKey`";
        $valueStr .= ', ?';
      }
    }
    $this->runStatement("INSERT INTO $table ($columnstr) VALUES ($valueStr)",
                        $mysql,
                        'Unable to insert into table');
  }

  /**
   * Call to update the row with $id in $table with the data represented in this
   * object using $mysql as database connection.  Returns only if successful.
   * Invalidates this object to prevent douple use.
   */
  public function updateWhere($id, $table, $mysql) {
    $this->hasData();
    $first = true;
    foreach ($this->dataArray as $key => $v) {
      $escapedKey = $mysql->real_escape_string($key);
      if ($first) {
        $setStr = "`$escapedKey` = ?";
        $first = false;
      } else {
        $setStr .= ", `$escapedKey` = ?";
      }
    }
    $this->runStatement("UPDATE $table SET $setStr WHERE GHFPvar_id=$id",
                        $mysql,
                        'Unable to update table');
  }

  // prepares the parameter array for call_user_function_array
  private function parameterArray() {
    $typeStr = $this->typeStr();
    $parameterArray = array();
    $parameterArray[] = &$typeStr;
    foreach ($this->dataArray as $key => &$value) {
      $parameterArray[] = &$value;
    }
    return $parameterArray;
  }

  // prepares the type string for parameter binding
  private function typeStr() {
    return str_repeat('s', count($this->dataArray));
  }

  // prepares $sql as statement using $dbConnection and runs the query with
  // the data stored in this object. Dies on Failure with $errorStr.
  private function runStatement($sql, $dbConnection, $errorStr = NULL) {
    $insertStmt = $dbConnection->prepare($sql);
    call_user_func_array(array($insertStmt, 'bind_param'), $this->parameterArray());
    if (!$insertStmt->execute()) {
      if (empty($errorStr)) $errorStr = 'Unable to run statement' . $sql;
      contextDie($errorStr . ' (' . $insertStmt->errno . '): ' . $insertStmt->error);
    }
    $this->dataArray = NULL;
  }

  // Tests if there is data to store
  private function hasData() {
    if (empty($this->dataArray)) {
      contextDie('Trying to insert, but no data availiable! Are you trying to use RowData class twice?');
    }
  }
}

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

//determine whether this was the last page of the questionnaire
if (isset($unsafe_control_variables['GHFPvar_next_page'])) {
  $next_page = $unsafe_control_variables['GHFPvar_next_page'];
}

//if no data have been sent
if (empty ($unsafe_variables) && empty($next_page)) {echo "There is no form input to be processed."; exit; }

//input validation: for each line in the array of submitted variables do the following
if ($allfieldsfull) {
  foreach ($unsafe_variables as $name=>$value) {
    if ($value == "") {
      echo $errormessage;
      echo '<br><br><a href="javascript:history.back()">&lt;---</a>';
      exit;
    }
  }
}

//counter for dynamic timestamp and next_page
if (!isset($_SESSION['counter'])) $_SESSION['counter'] = 0;
$counter_page = ++$_SESSION['counter']+1;

//Add meta-data (page address and time of submit) for subsequent pages in multi page questionaries.
if (isset($next_page)) $unsafe_variables['page'.$counter_page] = $next_page;
if (isset($_SESSION['counter'])) {
  $unsafe_variables['time_submit'.$_SESSION['counter']] = date("G:i:s");
}

//sorts keys in array in numerical and alphabetical order
if ($order) ksort ($unsafe_variables);

// Establish mysql connection
$mysql = new mysqli($host, $user, $password);
if ($mysql->connect_error) {
  contextDie('Could not connect to database: ' . $mysql->connect_error);
}

// Escape all input keys and values and build description for columns to prevent SQL injections
$column_def = array();
foreach ($unsafe_variables as $key => $value) {
  $column_def[$key] = sprintf('`%s` VARCHAR(255)', $mysql->real_escape_string($key));
}

// Try to get information about table to use. If unsuccessful and not in productive mode create DB and table.
$res = $mysql->query("SHOW COLUMNS FROM $table FROM $database");
if ($mysql->errno != 0) {
  if (!$productive and $mysql->errno == 1146) {
    // Table and or Database doesnt exist. Let's create it.
    $mysql->query("CREATE DATABASE IF NOT EXISTS $database") or
      contextDie('Could not create database (' . $mysql->errno . '): ' . $mysql->error);
    $mysql->select_db($database);

    if (count($column_def) > 0) {
      $columns = implode(', ', $column_def) . ', ';
    } else {
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
      contextDie('Could not create table (' . $mysql->errno . '): ' . $mysql->error);
    $res = $mysql->query("SHOW COLUMNS FROM $table");
  } else {
    // Set to productive mode, but table does not exist or unexpected error.
    contextDie('Unable to get information about table (' . $mysql->errno . '): ' . $mysql->error);
  }
} else {
  $mysql->select_db($database);
}

// Get all Columns from DB
$known_keys = array();
while ($row = $res->fetch_assoc()) { // Collect columnnames from database
  $known_keys[] = $row['Field'];
}

// Change Table if not in productive mode
if ($productive) {
  // Only use Columns we know
  $known_variables = array_intersect_key($unsafe_variables, array_flip($known_keys));
} else {
  // Collect column definitions for keys not yet in DB
  $new_columns = array_diff_key($column_def, array_flip($known_keys));
  if (count($new_columns) > 0) {
    $columns = implode(', ', $new_columns);
    $mysql->query("ALTER TABLE $table ADD ($columns)") or
      contextDie('Could not alter table (' . $mysql->errno . '): ' . $mysql->error);
  }
  $known_variables = $unsafe_variables;
}

// Store Data
$row = new RowData($known_variables);
if (!isset ($_SESSION['identification'])) {
  $row->insertInto($table, $mysql);
  //grab last value of auto-increment variable "GHFPvar_id" to be used as identifier
  $_SESSION['identification'] = $mysql->insert_id;
} else if (count($known_variables) > 0) {
  $row->updateWhere($_SESSION['identification'], $table, $mysql);
} // else: no data to store

// Close DB Connection
$mysql->close();

//if this is the last html page: feedback for the participant
if (!isset ($next_page)) {
  session_destroy();
  echo $thank_you_text;
} else {
  //if questionnaire consists of still another html page
  //call up next HTML page and pass on ID and counter
  echo "<html><head></head><body onLoad=\"javascript:location.replace('".$next_page."')\">
<a href=\"".$next_page."\">Next Page</a></body></html>";
  //move on by clicking
  //echo "<html><head></head><body><a href=\"".$next_page."\">Next Page</a></body></html>";
}