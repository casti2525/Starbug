<?php $what = next($this->uri); if (file_exists("core/app/nouns/Ajax/models/get/$what.php")) include("core/app/nouns/Ajax/models/get/$what.php"); ?>