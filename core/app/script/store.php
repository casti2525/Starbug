<?php
# Copyright (C) 2008-2010 Ali Gangji
# Distributed under the terms of the GNU General Public License v3
/**
 * This file is part of StarbugPHP
 * @file script/store.php
 * @author Ali Gangji <ali@neonrain.com>
 * @ingroup script
 */
namespace Starbug\Core;
class StoreCommand {
	public function __construct(ModelFactoryInterface $models) {
		$this->models = $models;
	}
	public function run($argv) {
		$name = array_shift($argv);
		$params = join("  ", $argv);
		$params = star($params);
		$instance = $this->models->get($name);
		$instance->store($params);
		if (!$instance->errors()) {
			$id = (empty($params['id'])) ? $instance->insert_id : $params['id'];
			$records = $instance->query()->condition($name.".id", $id)->all();
			$result = array();
  		foreach ($records as $record) $result[] = array_values($record);
  		$table = new \cli\Table();
  		$table->setHeaders(array_keys($records[0]));
  		$table->setRows($result);
  		$table->display();
		} else {
			$errors = $instance->errors("", true);
			$result = array();
  		foreach ($records as $record) $result[] = array_values($record);
			foreach($errors as $col => $arr) {
				foreach($arr as $e => $m) {
					$result[] = array($col, $m);
				}
			}
			$table = new \cli\Table();
  		$table->setHeaders(array("field", "message"));
  		$table->setRows($result);
  		$table->display();
		}
	}
}
?>
