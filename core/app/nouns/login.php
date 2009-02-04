<?php if (!empty($_SESSION[P('id')])) { ?>
		<form id="logout_form" action="<?php echo htmlentities($_SERVER['REQUEST_URI']); ?>" method="post">
		<input id="action['users']" name="action[users]" type="hidden" value="logout" />
		<input class="button" type="submit" value="Log Out" />
	</form>
<?php } else { ?>
	<form id="login_form" action="<?php echo htmlentities($_SERVER['REQUEST_URI']); ?>" method="post">
		<input id="action[users]" name="action[users]" type="hidden" value="login"/>
		<div class="field">
			<label for="email">Email</label>
			<?php if (!empty($this->errors['users']['loginMatchError'])) { ?><span class="error">That email and password combination was not found.</span><?php } ?>
			<input id="email" name="users[email]" type="text"<?php if (!empty($_POST['users']['email'])) { ?> value="<?php echo $_POST['users']['email']; ?>"<?php } ?> maxlength="64" />
		</div>
		<div class="field">
			<label for="password">Password</label>
			<input id="password" name="users[password]" type="password" />
		</div>
		<input class="button" type="submit" value="Log In" />
	</form>
<?php } ?>