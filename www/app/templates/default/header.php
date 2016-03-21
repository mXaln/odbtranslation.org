<?php
/**
 * Sample layout
 */

use Helpers\Assets;
use Helpers\Url;
use Helpers\Hooks;

//initialise hooks
$hooks = Hooks::get();
?>
<!DOCTYPE html>
<html lang="<?php echo LANGUAGE_CODE; ?>">
<head>

	<!-- Site meta -->
	<meta charset="utf-8">
	<?php
	//hook for plugging in meta tags
	$hooks->run('meta');
	?>
	<title><?php echo $data['title'].' | '.SITETITLE; ?></title>

	<!-- CSS -->
	<?php
	Assets::css(array(
		Url::templatePath() . 'css/bootstrap.min.css',
		Url::templatePath() . 'css/bootstrap-theme.min.css',
		Url::templatePath() . 'css/style.css',
	));

	//hook for plugging in css
	$hooks->run('css');
	?>

	<!-- JS -->
	<?php
	Assets::js(array(
		Url::templatePath() . 'js/jquery.js',
		Url::templatePath() . 'js/main.js',
		Url::templatePath() . 'js/bootstrap.min.js'
	));

	//hook for plugging in javascript
	$hooks->run('js');
	?>

</head>
<body>
<?php
//hook for running code after body tag
$hooks->run('afterBody');
?>

<div class="container">

	<div class="header page-header row">

		<ul class="nav nav-pills col-md-8" role="tablist">
			<li <?php if($data['menu'] == 1):?>class="active"<?php endif?> role="presentation"><a href="<?php echo DIR?>"><?php echo \Core\Language::show('home', 'Main')?></a></li>
			<li <?php if($data['menu'] == 2):?>class="active"<?php endif?> role="presentation"><a href="<?php echo DIR?>books">Books</a></li>
			<li <?php if($data['menu'] == 3):?>class="active"<?php endif?> role="presentation"><a href="<?php echo DIR?>translations">Translations</a></li>
			<li <?php if($data['menu'] == 4):?>class="active"<?php endif?> role="presentation"><a href="<?php echo DIR?>events">Events</a></li>
			<li <?php if($data['menu'] == 5):?>class="active"<?php endif?> role="presentation"><a href="<?php echo DIR?>contact"><?php echo \Core\Language::show('contact_us_title', 'Main')?></a></li>
			<li <?php if($data['menu'] == 6):?>class="active"<?php endif?> role="presentation"><a href="<?php echo DIR?>about"><?php echo \Core\Language::show('about_title', 'Main')?></a></li>
		</ul>

		<ul class="list-inline col-md-4">
			<li><a class="btn btn-link" href="<?php echo DIR?>lang/en">English</a></li>
			<li><a class="btn btn-link" href="<?php echo DIR?>lang/ru">Русский</a></li>
			<?php if(\Helpers\Session::get('loggedin')): ?>
			| <li><a class="btn btn-link" href="<?php echo DIR?>members/logout"><?php echo \Core\Language::show('logout', 'Members')?></a></li>
			<?php endif?>
		</ul>

	</div>