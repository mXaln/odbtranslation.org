<?php
use Helpers\Session;
?>
<div class="members_login">
	<h1><?php echo __('login_title'); ?></h1>
	<p><?php echo __('dont_have_account'); ?> <a href='<?php echo SITEURL;?>members/signup'><?php echo __('signup'); ?></a>

	<?php
	echo Error::display($error);
	?>

	<form action='' method='post' style="width: 500px">
		<div class="form-group">
			<label for="email">Email / <?php echo __('userName'); ?></label>
			<input type="text" class="form-control" id="email" name="email" placeholder="Email" value="<?php echo isset($_POST["email"]) ? $_POST["email"] : ""?>">
		</div>

		<div class="form-group">
			<label for="password"><?php echo __('password'); ?></label>
			<input type="password" class="form-control" id="password" name="password" placeholder="<?php echo __('password'); ?>" value="">
		</div>

		<input type="hidden" name="csrfToken" value="<?php echo $data['csrfToken']; ?>" />

		<?php if(Session::get('loginTry')>=3):?>
			<div class="form-group">
				<div class="g-recaptcha" data-sitekey="<?php echo ReCaptcha::getSiteKey() ?>"></div>
			</div>
		<?php endif;?>

		<button type="submit" name="submit" class="btn btn-primary"><?php echo __('login'); ?></button>
		<a href="<?php echo SITEURL?>members/passwordreset" class="btn btn-link"><?php echo __('forgot_password'); ?></a>
	</form>
</div>

<script src="https://www.google.com/recaptcha/api.js?hl=<?php echo Language::code()?>" async defer></script>