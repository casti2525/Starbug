<?php
class hook_store_datetime extends QueryHook {
	function validate(&$query, $key, $value, $column, $argument) {
		return (empty($value)) ? $value : date('Y-m-d H:i:s', strtotime($value));
	}
}
?>
