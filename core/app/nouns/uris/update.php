<?php $id = next($this->uri); $_POST['uris'] = $sb->get("uris")->find("*", "id='$id'")->fields(); ?>
<h2>Update uri</h2>
<?php $formid = "edit_uri_form"; $action = "create"; $submit_to = uri("models/show/").$id; include("core/app/nouns/uris/uris_form.php"); ?>
