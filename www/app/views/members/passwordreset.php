<?php
use Core\Language;
?>

<h1><?php echo Language::show('passwordreset_title', 'Members'); ?></h1>

<?php
echo \Core\Error::display($error);
?>

<form action='' method='post' style="width: 500px">
    <div class="form-group">
        <label for="email"><?php echo Language::show('enter_email', 'Members') ?></label>
        <input type="text" class="form-control" id="email" name="email"
               placeholder="<?php echo Language::show('enter_email', 'Members') ?>" value="">
    </div>

    <input type="hidden" name="csrf_token" value="<?php echo $data['csrf_token']; ?>"/>

    <div class="form-group">
        <div class="g-recaptcha" data-sitekey="6LdVdhYTAAAAANFr6KVgyhOrerL8rGMyu2N8d0H2"></div>
    </div>

    <button type="submit" name="submit"
            class="btn btn-primary"><?php echo Language::show('continue', 'Members'); ?></button>
</form>

<script src="https://www.google.com/recaptcha/api.js?hl=<?php echo $data['lang']?>" async defer></script>
