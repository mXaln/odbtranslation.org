<?php
echo \Helpers\Session::message();
echo \Core\Error::display($error);
?>

<br><br>

<a class="btn btn-link" href="<?php echo DIR;?>members">
    <?php echo \Core\Language::show('home', 'Main'); ?>
</a>

<?php if(!isset($error)): ?>
|
<a class="btn btn-link" href="<?php echo DIR;?>members/activate/resend/<?php echo $data["member"][0]->email ?>">
    <?php echo \Core\Language::show('resend_activation_code', 'Members'); ?>
</a>
<?php endif; ?>
