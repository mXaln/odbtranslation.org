<?php
echo \Helpers\Session::message();
echo Error::display($error);
?>

<br><br>

<a class="btn btn-link" href="/members">
    <?php echo __('home'); ?>
</a>

<?php if(empty($error)): ?>
|
<a class="btn btn-link" href="/members/activate/resend/<?php echo $data[0]->email ?>">
    <?php echo __('resend_activation_code'); ?>
</a>
<?php endif; ?>
