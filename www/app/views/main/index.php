<?php
use Core\Language;
?>

<div>
	<h1><?php echo $data['title'] ?></h1>
</div>

<p class="well"><?php echo $data['welcome_message'] ?></p>

<a class="btn btn-link" href="<?php echo DIR;?>members">
	<?php echo Language::show('signup', 'Members'); ?>
</a>

|

<a class="btn btn-link" href="<?php echo DIR;?>members/login">
    <?php echo Language::show('login', 'Members'); ?>


