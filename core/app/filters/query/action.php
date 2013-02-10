<?php
	if ((!empty($args['action'])) && !logged_in("root")) {
		$roles = "(permits.role='everyone' || (permits.role='user' && permits.who='".sb()->user['id']."') || (permits.role='group' && (('".sb()->user['memberships']."' & permits.who)=permits.who))";
		if ((!empty($args['priv_type'])) && ($args['priv_type'] == "table")) {
			$args['select'] = "*";
			$args['from'] = P("permits")." AS permits";
			$permit_type = "permits.priv_type='table'";
		} else {
			$args['from'] .= " INNER JOIN ".P("permits")." AS permits";
			$permit_type = "(permits.priv_type='global' || (permits.priv_type='object' && permits.related_id=".$first.".id))"." && ((permits.status & ".$first.".status)=".$first.".status)";
			$roles .= " || (permits.role='owner' && ".$first.".owner='".sb()->user['id']."') || (permits.role='collective' && ((('".sb()->user['memberships']."' & ".$first.".collective)>'0') || (('".sb()->user['memberships']."' & ".$first.".collective)=$first.collective)))";
		}
		$args['where'] = "permits.related_table='".P($first)."'"
		." && permits.action='$args[action]'"
		." && ".$permit_type
		." && ".$roles.")"
		.((empty($args['where'])) ? "" : " && ".$args['where']);
	}
?>
