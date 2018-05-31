<?php
use Helpers\Session;

echo Session::message();
?>

<br><br>

<a class="btn btn-link" href="/members">
    <?php echo __('home'); ?>
</a>

<?php if(Session::get("activation_email") !== null): ?>
|
<a class="btn btn-link" href="<?php echo SITEURL;?>members/activate/resend/<?php echo Session::get("activation_email") ?>">
    <?php echo __('resend_activation_code'); ?>
</a>
<?php endif; ?>