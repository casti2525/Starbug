<?php
/**
 * This file is part of StarbugPHP
 * @file core/init.php the standard init file. provides application wide functionality
 * @author Ali Gangji <ali@neonrain.com>
 * @ingroup core
 */
date_default_timezone_set('UTC');
error_reporting(E_ALL ^ E_NOTICE);
/**
 * set a variable only if it is empty or not numeric
 * @ingroup core
 * @param mixed $val the variable to set, passed by reference
 * @param mixed $default the value to set the variable to if it turns out to be empty or not numeric
 */
function empty_nan(&$val, $default="") {if(!isset($val) || !is_numeric($val)) $val = $default;}
/**
 * set a variable only if it is not set
 * @ingroup core
 * @param mixed $val the variable to set, passed by reference
 * @param mixed $default the value to set the variable to if it turns out to not unset
 */
function dfault(&$val, $default="") {if(!isset($val)) $val = $default;return $val;}
/**
 * set a variable only if it is empty
 * @ingroup core
 * @param mixed $val the variable to set, passed by reference
 * @param mixed $default the value to set the variable to if it turns out to be empty
 */
function efault(&$val, $default="") {if(empty($val)) $val = $default;return $val;}
/**
 * just returns back a variable
 * @ingroup core
 * @param mixed $val the value to return
 * @return mixed $val
 */
function return_it($val) {return $val;}
/**
 * prefix a variable with the site prefix
 * @ingroup core
 * @param string $var the value to prefix
 * @return string the prefixed value
 */
function P($var) {return Etc::PREFIX.$var;}
/**
 * get an absolute URI from a relative path
 * @ingroup core
 * @param string $path the relative path
 * @param string $flags modification flag such as 's' for secure or 'f' for friendly
 * @return string the absolute path
 */
function uri($path="", $flags="") {
	if ($flags == "s") $prefix = "https://";
	else if ($flags == "f") $prefix = "";
	else $prefix = "http://";
	return $prefix.Etc::WEBSITE_URL.$path;
}

// include the db class
include(BASE_DIR."/core/db/db.php");

// include the sb class
include(BASE_DIR."/core/sb.php");

// include the Table class
include(BASE_DIR."/core/db/Table.php");

// include the starr class
include(BASE_DIR."/util/starr.php");

/**
 * global instance of the sb class
 * @ingroup core
 */
global $sb;
$sb = new sb();
if (!is_array($autoload)) include(BASE_DIR."/etc/autoload.php");
call_user_func_array(array($sb, "import"), $autoload);
?>
