<?php
use Core\Language;
?>

<h1><?php echo Language::show('login_title', 'Members'); ?></h1>
<p><?php echo Language::show('dont_have_account', 'Members'); ?> <a href='<?php echo DIR;?>members'><?php echo Language::show('signup', 'Members'); ?></a>

<?php
echo \Core\Error::display($error);
?>

<form action='' method='post' style="width: 500px">
	<div class="form-group">
		<label for="email">Email / <?php echo Language::show('userName', 'Members'); ?></label>
		<input type="text" class="form-control" id="email" name="email" placeholder="Email" value="">
	</div>

	<div class="form-group">
		<label for="password"><?php echo Language::show('password', 'Members'); ?></label>
		<input type="password" class="form-control" id="password" name="password" placeholder="<?php echo Language::show('password', 'Members'); ?>" value="">
	</div>

    <input type="hidden" name="csrf_token" value="<?php echo $data['csrf_token']; ?>" />

	<?php if(\Helpers\Session::get('loginTry')>=3):?>
		<?php //local: 6Lf_dBYTAAAAAEBrMuGNitfGTsGpcuWh_6G236qr ?>
		<?php //remote: 6LdVdhYTAAAAANFr6KVgyhOrerL8rGMyu2N8d0H2 ?>
		<?php //remote test: 6LebmSgTAAAAAMOxVD-HIOOEufogdDmb8Qpiu6Rq ?>
		<div class="form-group">
			<div class="g-recaptcha" data-sitekey="6LdVdhYTAAAAANFr6KVgyhOrerL8rGMyu2N8d0H2"></div>
		</div>
	<?php endif;?>

    <button type="submit" name="submit" class="btn btn-primary"><?php echo Language::show('login', 'Members'); ?></button>
    <a href="<?php echo DIR?>members/passwordreset" class="btn btn-link"><?php echo Language::show('forgot_password', 'Members'); ?></a>
</form>

<script src="https://www.google.com/recaptcha/api.js?hl=<?php echo $data['lang']?>" async defer></script>