<div class="blocks <?php echo $region; ?>-blocks">
	<?php
		$blocks = query("blocks")->condition("blocks.uris_id", $response->id)->condition("blocks.region", $region)->sort("position");
		foreach ($blocks as $block) {
			$this->render_display("BlockDisplay", array("block" => $block));
		}
	?>
</div>
