#!/usr/bin/php
<?php
/**
* FILE: etc/install.php
* PURPOSE: This is the installation file
* NOTE: you should run this from the command line, and then delete it.
* 			If you need to reinstall, you can get a copy later.
*
* This file is part of StarbugPHP
*
* StarbugPHP - website development kit
* Copyright (C) 2008-2009 Ali Gangji
*
* StarbugPHP is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* StarbugPHP is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with StarbugPHP.  If not, see <http://www.gnu.org/licenses/>.
*/
	define('BASE_DIR', str_replace("/install", "", dirname(__FILE__)));
	set_include_path(get_include_path().PATH_SEPARATOR.BASE_DIR);
	define("STDOUT", fopen("php://stdout", "wb"));
	define("STDIN", fopen("php://stdin", "r"));
	
	//CREATE FOLDERS & SET FILE PERMISSIONS
	exec("chmod a+x script/generate");
	exec("chmod ug+s script/cgenerate");
	exec("mkdir var var/hooks var/xml app/public/uploads app/public/thumbnails");
	exec("chmod -R a+w var app/public/uploads app/public/thumbnails");

	//INIT TABLES
	include(BASE_DIR."/etc/Etc.php");
	include(BASE_DIR."/core/init.php");
	include(BASE_DIR."/core/db/Schemer.php");
	global $schemer;
	$schemer = new Schemer($sb->db);
	include(BASE_DIR."/etc/schema.php");
	$schemer->migrate(1, 0);
	
	//SUSBSCRIBE HOOKS
	$sb->import("util/subscribe");
	$sb->subscribe("header", "global", 10, "sb::load", "core/app/hooks/header");
	$sb->subscribe("footer", "global", 10, "sb::load", "core/app/hooks/footer");
	$sb->subscribe("footer", "dojo", 10, "sb::load", "core/app/hooks/dojo.footer");
?>
