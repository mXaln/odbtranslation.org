<h1><?php echo \Core\Language::show('welcome_title', 'Members'); ?> <?php echo \Helpers\Session::get('firstName')." ".\Helpers\Session::get('lastName');?></h1>

<?php if(\Helpers\Session::get('loggedin')):?>

<?php endif?>
