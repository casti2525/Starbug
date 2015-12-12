<?php
namespace Starbug\Intl;
use Starbug\Core\GridDisplay;
class CountriesGrid extends GridDisplay {
	public $model = "countries";
	public $action = "admin";
	function build_display($options) {
		$this->add("name");
		$this->add("code");
	}
}
?>
