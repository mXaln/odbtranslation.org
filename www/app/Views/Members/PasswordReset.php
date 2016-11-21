<div class="members_login">
    <h1><?php echo __('passwordreset_title'); ?></h1>

    <?php
    echo Error::display($error);
    ?>

    <form action='' method='post' style="width: 500px">
        <div class="form-group">
            <label for="email"><?php echo __('enter_email') ?></label>
            <input type="text" class="form-control" id="email" name="email"
                   placeholder="<?php echo __('enter_email') ?>" value="">
        </div>

        <input type="hidden" name="csrfToken" value="<?php echo $data['csrfToken']; ?>"/>

        <div class="form-group">
            <div class="g-recaptcha" data-sitekey="<?php echo ReCaptcha::getSiteKey() ?>"></div>
        </div>

        <button type="submit" name="submit"
                class="btn btn-primary"><?php echo __('continue'); ?></button>
    </form>
</div>

<script src="https://www.google.com/recaptcha/api.js?hl=<?php echo Language::code()?>" async defer></script>
