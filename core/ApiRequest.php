<?php
# Copyright (C) 2008-2010 Ali Gangji
# Distributed under the terms of the GNU General Public License v3
/**
 * @file core/ApiRequest.php
 * @author Ali Gangji <ali@neonrain.com>
 * @ingroup core
 */
/**
 * ApiRequest
 * @ingroup core
 */
 $sb->provide("core/ApiRequest");
class ApiRequest {

	var $types = array(
		"xml" => "text/xml",
		"json" => "application/json",
		"jsonp" => "application/x-javascript",
		"csv" => "text/csv"
	);
	var $result = "";
	var $whitelisting = false;
	var $query = true;
	var $headers = true;
	var $model;
	var $action;

	/**
	 * API Request constructor
	 * @param string $what an api request string in the format '[object].[format]'
	 * 										 where object is an API function or set of models to query, and format is the desired output format (json, jsonp, xml)
	 * @param star $ops additional options, query paramaters if [object] is a model or group of models
	 */
	function __construct($what, $ops="", $headers=true) {
		global $sb;
		if (defined("ETC::API_WHITELIST")) {
			if (in_array($_SERVER['REMOTE_ADDR'], explode(",", Etc::API_WHITELIST)) && !sb()->user) {
				$this->whitelisting = true;
				//UN-COMMENT THIS LINE TO ALLOW WHITE LISTING, ENABLE IT AT YOUR OWN RISK
				//sb()->user = array("id" => 1, "memberships" => 1);
			}
		}
		$this->headers = $headers;
		$format = end(explode(".", $what));
		$parts = explode("/", str_replace(".$format", "", $what));
		$call = reset($parts);
		$action = next($parts);
		$ops = array_merge(array("action" => "read", "where" => array(), "params" => array()), star($ops));
		if (!is_array($ops['where'])) $ops['where'] = array($ops['where']);
		if ($this->headers) {
			header("Content-Type: ".$this->types[$format]);
			header("Cache-Control: no-store, no-cache");
		}
		$this->action = $action;
		$this->result = call_user_func(array($this, $call), $action, $format, $ops);
	}

	/**
	 * API query function - outputs records from the DB
	 * @param string $call function name passed by constructor
	 * @param string $format the desired output format: json, jsonp or xml
	 * @param star $ops additional options
	 */
	function __call($model, $args) {
		global $sb;
		list($action, $format, $ops) = $args;
		$this->model = $model;
		$query = entity_query($model);
		if ((!empty($_POST['action'][$model])) && (empty($sb->errors[$model]))) {
			$id = (!empty($_POST[$model]['id'])) ? $_POST[$model]['id'] : sb($model)->insert_id;
			$query->condition($model.".id", $id);
		}
		if (!empty($_GET['keywords'])) $query->search($_GET['keywords']);

		//paging
		if (isset($_SERVER['HTTP_RANGE'])) {
			list($start, $finish) = explode("-", end(explode("=", $_SERVER['HTTP_RANGE'])));
			//$start = max((int) $start, 1);
			$ops['paged'] = true;
			$ops['limit'] = 1 + (int) $finish - (int) $start;
			$ops['page'] = 1 + (int) $start/$ops['limit'];
		}
		$action_name = "query_".$action;
		$query = sb($model)->query_filters($action, $query, $ops);
		$query = sb($model)->$action_name($query, $ops);

		if ($ops['paged'] && $ops['limit']) {
			$query->limit($ops['limit']);
			$pager = $query->pager($ops['page']);
		} else if ($ops['limit']) {
      $ops['paged'] = true;
      $ops['page'] = $ops['skip'] ? intval($ops['skip'])/intval($ops['limit']) : 1;
      $query->limit($ops['limit']);
      $pager = $query->pager($ops['page']);
    }

		$data = (is_array($query) && isset($query['data'])) ? $query['data'] : $query->all();
		$f = strtoupper($format);
		$error = $f."errors";
		if (empty($sb->errors[$model])) {
			if (!empty($data)) {
				$add = (isset($pager) && $pager->start > 0) ? 1 : 0;
				if (isset($ops['paged'])) header("Content-Range: items ".$start.'-'.min($pager->count, $finish).'/'.$pager->count);
				else {
					$count = count($data);
					header("Content-Range: items 0-$count/$count");
				}
				if (!isset($ops['headers'])) $ops['headers'] = true;
				switch ($format) {
					case "xml": return $this->getXML($model, $data); break;
					case "json": return $this->getJSON("id", $data); break;
					case "jsonp": return $this->getJSONP($ops['pad'], $data); break;
					case "csv": return $this->getCSV($data, $ops['headers']); break;
				}
			} else {
				if (isset($ops['paged'])) header("Content-Range: items ".$start.'-'.min($pager->count, $finish).'/'.$pager->count);
			}
		} else return $this->$error($model);
	}

	/**
	 * groups API function - outputs a list of groups
	 * @param string $call function name passed by constructor
	 * @param string $format the desired output format: json, jsonp or xml
	 * @param star $ops additional options
	 */
	protected function groups($call, $format, $ops) {
		$groups = config("groups");
		$data = array();
		foreach ($groups as $name => $number) $data[] = array("label" => $name, "id" => $number);
		if (!empty($data)) {
			switch ($format) {
				case "xml": return $this->getXML("groups", $data); break;
				case "json": return $this->getJSON("id", $data); break;
				case "jsonp": return $this->getJSONP($ops['pad'], $data); break;
				case "csv": return $this->getCSV($data); break;
			}
		}
	}

	/**
	 * statuses API function - outputs a list of statuses
	 * @param string $call function name passed by constructor
	 * @param string $format the desired output format: json, jsonp or xml
	 * @param star $ops additional options
	 */
	protected function statuses($call, $format, $ops) {
		$statuses = config("statuses");
		$data = array();
		foreach ($statuses as $name => $number) $data[] = array("label" => $name, "id" => $number);
		if (!empty($data)) {
			switch ($format) {
				case "xml": return $this->getXML("statuses", $data); break;
				case "json": return $this->getJSON("id", $data); break;
				case "jsonp": return $this->getJSONP($ops['pad'], $data); break;
				case "csv": return $this->getCSV($data); break;
			}
		}
	}

	/**
	 * get a json formatted recordset
	 * @param array $data an array of data
	 * @return string json output of records
	 */
	protected function getJSON($identifier, $data) {
		$json = ($this->query) ? '[' : '';
		foreach($data as $row) $json .= json_encode(sb($this->model)->filter($row, $this->action)).", ";
		return rtrim($json, ", ").(($this->query) ? ']' : '');
	}

	/**
	 * get an padded json formatted recordset
	 * @param array $data the data
	 * @param string $pad the string to pad with
	 * @return string padded json string of records
	 */
	protected function getJSONP($pad, $data) {
		return $pad."(".json_encode($data).");";
	}

	/**
	 * Get XML formatted recordset
	 * @param string $root the root node name
	 * @param array $data the data
	 */
	protected function getXML($root, $data) {
		$xml = new XmlWriter();
		$xml->openMemory();
		$xml->startDocument('1.0', 'UTF-8');
		$xml->startElement($root);
		foreach($data as $row) {
			$xml->startElement("item");
			$this->write($xml, $row);
			$xml->endElement();
		}
		$xml->endElement();
		return $xml->outputMemory(true);
	}

 	/**
 	 * get a CSV formatted recordset
 	 * @param array $data an array of data
 	 * @return string json output of records
 	 */
 	protected function getCSV($data, $headers=true) {
 		if ($this->headers) header('Content-Disposition: attachment; filename="'.$this->model.'.csv"');
 		foreach ($data as $idx => $row) $data[$idx] = sb($this->model)->filter($row, $this->action);
		$display = build_display("list", $this->model, $this->action, array("template" => "csv"));
		$display->items = $data;
		return $display->capture(false);
	}

	/**
	 * Recursive XML tag writer
	 * @param XMLWriter $xml the XMLWriter instance
	 * @param array $data the data
	 */
	protected function write(XMLWriter $xml, $data){
		foreach($data as $key => $value){
			if(is_array($value)){
				$xml->startElement($key);
				$this->write($xml, $value);
				$xml->endElement();
				continue;
			}
			$xml->writeElement($key, $value);
		}
	}

	/**
	 * get json formatted errors
	 * @param string $model the model
	 * @return string json output of errors
	 */
	protected function JSONerrors($model) {
		global $sb;
		$schema = schema($model.".fields");
		if (empty($schema)) $schema = array();
		$json = '{ "errors" : [';
		foreach($sb->errors[$model] as $k => $v) {
			if (!empty($schema[$k]) && !empty($schema[$k]['label'])) $k = $schema[$k]['label'];
			$json .= '{ "field":"'.$k.'", "errors": [ ';
			foreach ($v as $e) $json .= '"'.$e.'", ';
			$json = rtrim($json, ", ")." ] }, ";
		}
		return rtrim($json, ", ")." ] }";
	}

}
?>
