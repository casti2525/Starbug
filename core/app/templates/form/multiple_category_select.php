<div class="multiple_category_select">
	<?php if (empty($value)) $value = array(); foreach ($terms as $term) { ?>
		<div class="form-group checkbox" style="padding-left:<?php echo $term['depth']*15; ?>px">
			<label><input <?php html_attributes("type:checkbox  class:left checkbox  name:".$name."[]  value:$term[id]".((in_array($term['id'], $value)) ? "  checked:checked" : "")); ?>/><?php echo $term['term']; ?></label>
		</div>
	<?php } ?>
	<input <?php html_attributes("type:hidden  name:".$name."[]  value:-~"); ?>/>
</div>
<?php if ($writable) { ?>
	<div id="<?php echo $id; ?>_new_category"<?php if ($value != -1) echo ' style="display:none"'; ?>>
		<?php echo $form->text($field."_new_category  label:New Category"); ?>
		<br class="clear"/>
	</div>
<?php } ?>
