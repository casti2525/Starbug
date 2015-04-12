<?php
# Copyright (C) 2008-2010 Ali Gangji
# Distributed under the terms of the GNU General Public License v3
/**
 * This file is part of StarbugPHP
 * @file core/global/data.php
 * @author Ali Gangji <ali@neonrain.com>
 * @ingroup data
 */
/**
 * @defgroup data
 * global functions
 * @ingroup global
 */
/**
 * @copydoc db::query
 * @ingroup data
 */
function query($froms, $args="", $replacements = array()) {
	global $sb;
	return $sb->db->query($froms, $args, $replacements);
}
/**
 * get records or columns
 * @ingroup data
 * @param string $model the name of the model
 * @param string $id the id of the record
 * @param string $column optional column name
 */
function get() {
	$args = func_get_args();
	return call_user_func_array(array(sb()->db, "get"), $args);
}
/**
 * perform a raw query
 * @ingroup data
 * @param string $query the sql query string
 */
function raw_query($query) {
	global $sb;
	$start = strtolower(substr(trim($query), 0, 6));
	if ($start == "select" || $start == "descri") return $sb->db->pdo->query($query);
	else return $sb->db->exec($query);
}
/**
 * @copydoc db::store
 * @ingroup data
 */
function store($name, $fields = array(), $from = "auto") {
	global $sb;
	return $sb->db->store($name, $fields, $from);
}
/**
 * @copydoc db::queue
 * @ingroup data
 */
function queue($name, $fields, $from = "auto") {
	global $sb;
	return $sb->db->queue($name, $fields, $from);
}
/**
 * @copydoc db::store_queue
 * @ingroup data
 */
function store_queue() {
	global $sb;
	$sb->db->store_queue();
}
/**
 * store only if a record with matching fields does not exist
 * @ingroup data
 * @copydoc db::store
 */
function store_once($name, $fields, $from = "auto") {
	if (!is_array($fields)) $fields = star($fields);
	$where = "";
	$values = array();
	foreach ($fields as $k => $v) {
		if (!empty($where)) $where .= " && ";
		$where .= "$k=?";
		$values[] = $v;
	}
	$records = query($name, "where:$where", $values);
	if (!$records) {
		store($name, $fields, $from);
	} else return false;
}
/**
 * @copydoc db::remove
 * @ingroup data
 */
function remove($from, $where) {
	global $sb;
	return $sb->db->remove($from, $where);
}
?>
