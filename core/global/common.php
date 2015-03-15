<?php
# Copyright (C) 2008-2010 Ali Gangji
# Distributed under the terms of the GNU General Public License v3
/**
 * This file is part of StarbugPHP
 * @file core/global/common.php
 * @author Ali Gangji <ali@neonrain.com>
 * @ingroup common
 */
/**
 * @defgroup common
 * global functions
 * @ingroup global
 */
/**
 * set a variable only if it is empty
 * @ingroup common
 * @param mixed $val the variable to set, passed by reference
 * @param mixed $default the value to set the variable to if it turns out to be empty
 */
function efault(&$val, $default="") {if(empty($val)) $val = $default;return $val;}
?>
