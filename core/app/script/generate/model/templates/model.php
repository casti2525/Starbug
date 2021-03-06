<?php extract($config->get($model, "json")); echo '<?php'."\n"; ?>
/**
 * <?php echo $name; ?> model
 * @ingroup models
 */
class <?php echo ucwords($name); ?> {

	function create($<?php echo $singular; ?>) {
		$this->store($<?php echo $singular; ?>);
	}

  /******************************************************************
	 * Query functions
	 *****************************************************************/

  function query_admin($query, &$ops) {
    $query = parent::query_admin($query, $ops);
    return $query;
  }

  function query_filters($action, $query, $ops) {
      if (!$this->user->loggedIn("root") && !$this->user->loggedIn("admin")) $query->action("read");
      return $query;
  }

	/******************************************************************
	 * Display functions
	 *****************************************************************/

	function display_admin($display, $ops) {
	<?php if (!empty($base)) { ?>
    $this->models->get($this->base)->display_admin($display, $ops);
  <?php } else { ?>
    $display->add("id");
  <?php } ?>
	}

}
<?php echo '?>'; ?>
