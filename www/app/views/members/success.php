<?php
use Helpers\Session;

echo \Helpers\Session::message();
?>

<br><br>

<a class="btn btn-link" href="<?php echo DIR;?>members">
    <?php echo \Core\Language::show('home', 'Main'); ?>
</a>

<?php if(Session::get("activation_email") !== null): ?>
|
<a class="btn btn-link" href="<?php echo DIR;?>members/activate/resend/<?php echo Session::get("activation_email") ?>">
    <?php echo \Core\Language::show('resend_activation_code', 'Members'); ?>
</a>
<?php endif; ?>