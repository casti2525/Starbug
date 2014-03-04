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
$params = join("  ", $argv);
$records = query($name, $params);
echo "query $name $params\n";
$params = star($params);
if (!empty($params['limit']) && $params['limit'] == 1) $records = array($records);
else $records = $records->execute();
cli::table($records);
?>
