<?php
use Helpers\Session;
use Shared\Legacy\Error;
?>
<div class="members_login center-block"><br />
	<h1><?php echo __('login_title'); ?></h1>
	
	<?php
	echo Error::display($error);
    echo Session::message();
	?>

	<form action='' method='post' style="width: 500px">
		<div class="form-group">
			<label for="email" class="sr-only">Email / <?php echo __('userNameOrEmail'); ?></label>
			<input type="text" class="form-control input-lg" id="email" name="email" placeholder="<?php echo __('userNameOrEmail'); ?>" required="" value="<?php echo isset($_POST["email"]) ? $_POST["email"] : ""?>">
		</div>

		<div class="form-group">
			<label for="password" class="sr-only"><?php echo __('password'); ?></label>
			<input type="password" class="form-control input-lg" id="password" name="password" required="" placeholder="<?php echo __('password'); ?>" value="">
		</div>

		<input type="hidden" name="csrfToken" value="<?php echo $data['csrfToken']; ?>" />

		<?php if(Config::get("app.type") == "remote" && Session::get('loginTry')>=3):?>
			<div class="form-group">
				<div class="g-recaptcha" data-sitekey="<?php echo ReCaptcha::getSiteKey() ?>"></div>
			</div>
		<?php endif;?>
    <div class="row">
      <div class="col-sm-4" style="border-right:1px solid #ccc">
  		  <button type="submit" name="submit" class="btn btn-primary btn-lg" style="width: 8em;"><?php echo __('login'); ?></button>
      </div>
      <div class="col-sm-8">
	      <a href="<?php echo SITEURL?>members/passwordreset" class=""><?php echo __('forgot_password'); ?></a><br />
	      <?php echo __('dont_have_account'); ?> <a href='<?php echo SITEURL;?>members/signup'><?php echo __('signup'); ?></a>
      </div>
    </div>
	</form>
</div>

<?php if(Config::get("app.type") == "remote"): ?>
<script src="https://www.google.com/recaptcha/api.js?hl=<?php echo Language::code()?>" async defer></script>
<?php endif; ?>
