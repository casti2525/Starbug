<?php
# Copyright (C) 2008-2010 Ali Gangji
# Distributed under the terms of the GNU General Public License v3
/**
 * This file is part of StarbugPHP
 * @file script/query.php
 * @author Ali Gangji <ali@neonrain.com>
 * @ingroup script
 */
$name = array_shift($argv);
$records = raw_query("DESCRIBE `".P($name)."`")->fetchAll(PDO::FETCH_ASSOC);
cli::table($records);
?>
