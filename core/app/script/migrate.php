<?php
# Copyright (C) 2008-2010 Ali Gangji
# Distributed under the terms of the GNU General Public License v3
/**
 * This file is part of StarbugPHP
 * @file script/migrate.php
 * @author Ali Gangji <ali@neonrain.com>
 * @ingroup script
 */
namespace Starbug\Core;
use \Interop\Container\ContainerInterface;
class MigrateCommand {
	function __construct(Schemer $schemer, DatabaseInterface $db, ContainerInterface $container) {
		$this->schemer = $schemer;
		$this->db = $db;
		$this->container = $container;
	}
	public function run($argv) {
		$this->schemer->fill();
		//default options
		$options = array(
			"t" => false
		);

		//parse option flags
		$args = array();
		foreach ($argv as $i => $arg) {
			if (0 === strpos($arg, "-")) {
				$arg = str_replace("-", "", $arg);
				$parts = (false !== strpos($arg, "=")) ? explode("=", $arg, 2) : array($arg, true);
				$options[$parts[0]] = $parts[1];
			} else {
				$args[] = $arg;
			}
		}
		$argv = $args;

		//select database
		$next = array_shift($argv);
		if ((!empty($next)) && (0 !== $next)) {
			//TODO: add support for changing databases to Database
			//			and remove the dependency on the container from this file
			$this->container->set("database_name", $next);
			$this->db->setDatabase($next, $this->container->get("databases.".$next));
		}

		//test mode
		if ($options["t"]) {
			$this->schemer->testMode();
		}

		//migrate
		$this->schemer->migrate();
	}
}
?>
