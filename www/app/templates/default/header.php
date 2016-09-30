<?php
/**
 * Sample layout
 */

use Helpers\Assets;
use Helpers\Url;
use Helpers\Hooks;
use \Core\Language;
use \Helpers\Constants\EventSteps;
use Helpers\Session;

//initialise hooks
$hooks = Hooks::get();
$code = isset($_COOKIE['lang']) ? $_COOKIE['lang'] : "en";
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

	<link rel="icon" href="/favicon.ico" type="image/x-icon" />

	<!-- CSS -->
	<?php
	Assets::css(array(
		Url::templatePath() . 'css/bootstrap.min.css',
		Url::templatePath() . 'css/bootstrap-theme.min.css',
		Url::templatePath() . 'css/style.css',
		Url::templatePath() . 'css/jquery-ui.min.css',
		Url::templatePath() . 'css/jquery-ui.structure.min.css',
		Url::templatePath() . 'css/jquery-ui.theme.min.css',
	));

	//hook for plugging in css
	$hooks->run('css');
	?>

	<!-- JS -->
	<?php
	Assets::js(array(
		Url::templatePath() . 'js/jquery.js',
		Url::templatePath() . 'js/languages/'.$code.'.js',
		Url::templatePath() . 'js/main.js',
		(Session::get("isAdmin") ?  Url::templatePath() . 'js/mainAdmin.js' : ''),
		Url::templatePath() . 'js/bootstrap.min.js',
		//Url::templatePath() . 'js/jquery.elastic.source.js'
		Url::templatePath() . 'js/autosize.min.js',
		Url::templatePath() . 'js/jquery-ui.min.js',
	));

	//hook for plugging in javascript
	$hooks->run('js');
	?>

</head>
<body class="<?php echo isset($data["isMain"]) ? "welcome_bg" : "header_bg"?>">
<?php
//hook for running code after body tag
$hooks->run('afterBody');
?>

<div class="container">

	<div class="header page-header row <?php echo Session::get("loggedin") ? "loggedin" : ""?>">

		<div class="col-sm-8 row header_menu_left">
			<a href="<?php echo DIR?>" class="col-sm-4 logo"><img src="<?php echo Url::templatePath() ?>img/logo.png" height="40" /></a>

			<ul class="nav nav-pills col-sm-8" role="tablist">
			<?php if(Session::get('loggedin')): ?>
				<li <?php if($data['menu'] == 1):?>class="active"<?php endif?> role="presentation"><a href="<?php echo DIR?>"><?php echo Language::show('home', 'Main')?></a></li>
				<li <?php if($data['menu'] == 4):?>class="active"<?php endif?> role="presentation"><a href="<?php echo DIR?>events"><?php echo Language::show('events_title', 'Main')?></a></li>
				<li <?php if($data['menu'] == 3):?>class="active"<?php endif?> role="presentation"><a href="<?php echo DIR?>translations"><?php echo Language::show('translations_title', 'Main')?></a></li>
				<!--<li <?php if($data['menu'] == 5):?>class="active"<?php endif?> role="presentation"><a href="<?php echo DIR?>contact"><?php echo Language::show('contact_us_title', 'Main')?></a></li>
				<li <?php if($data['menu'] == 6):?>class="active"<?php endif?> role="presentation"><a href="<?php echo DIR?>about"><?php echo Language::show('about_title', 'Main')?></a></li>-->
			<?php endif; ?>
			</ul>
		</div>

		<ul class="list-inline col-sm-4 header_menu_right">
			<?php if(Session::get('loggedin')): ?>
			<li class="notifications">
				<a class="btn btn-link" id="notifications" href="#">
					<span class="notif_title" title="<?php echo Language::show("notifications", "Events") ?>"><img src="<?php echo Url::templatePath() ?>img/notif.png"></span>
					<?php echo !empty($data["notifications"]) ? '<span class="notif_count">'.sizeof($data["notifications"]).'</span>' : ""; ?>
				</a>
				<ul class="notif_block">
				<?php if(!empty($data["notifications"])):?>
					<?php foreach ($data["notifications"] as $notification):?>
						<?php
						$type = $notification->step == EventSteps::KEYWORD_CHECK ? "kw_checker" : "cont_checker";
						$text = Language::show('checker_apply', 'Events', array(
							$notification->userName,
							Language::show($notification->step, "Events"),
							$notification->bookName,
							$notification->currentChapter,
							$notification->tLang,
							Language::show($notification->bookProject, "Events"),
						));
						?>
						<?php if(!isset($data["isDemo"])): ?>
						<a class="notifa" href="/events/checker/<?php echo $notification->eventID."/".$notification->memberID; ?>/apply"
						   data="check:<?php echo $notification->eventID.":".$notification->memberID ?>" target="_blank">
							<li><?php echo $text; ?></li>
						</a>
						<?php else: ?>
						<a class="notifa" href="/events/demo/keyword_check_checker" target="_blank">
							<li><?php echo $text; ?></li>
						</a>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php else: ?>
					<div class='no_notif'><?php echo Language::show("no_notifs_msg", "Events") ?></div>
				<?php endif; ?>
					<div class="all_notifs"><a href="<?php echo DIR?>events/notifications"><?php echo Language::show("see_all", "Events") ?></a></div>
				</ul>
			</li>
			<li>
				<div class="profile-select">
					<div class="dropdown-toggle" id="profile-select" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
						<div class="uName"><?php echo Session::get("userName")?></div>
						<span class="caret"></span>
					</div>
					<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="profile-select">
						<li><a href="/members/profile"><?php echo Language::show("profile_message", "Members") ?></a></li>
						<?php if(Session::get("isAdmin")): ?>
                        <li><a href="/admin"><?php echo Language::show('admin', 'Members')?></a></li>
                        <?php endif; ?>
                        <li><a href="/members/logout"><?php echo Language::show('logout', 'Members')?></a></li>
					</ul>
				</div>
			</li>
			<?php else: ?>
			<li><a href="/members/signup" class="btn_signup btn-success"><?php echo Language::show("signup", "Members") ?></a></li>
			<li><a href="/members/login" class="btn_signin btn-primary"><?php echo Language::show("login", "Members") ?></a></li>
			<li>
                <div class="dropdown flangs">
                    <div class="dropdown-toggle" id="footer_langs" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img src="/app/templates/default/img/<?php echo $code?>.png">
                        <span class="caret"></span>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="footer_langs">
                        <li><a href="/lang/en"><img src="/app/templates/default/img/en.png"> English</a></li>
                        <li><a href="/lang/ru"><img src="/app/templates/default/img/ru.png"> Русский</a></li>
                    </ul>
                </div>
			</li>
			<?php endif?>
		</ul>

	</div>

	<div class="container_block <?php echo !isset($data["isMain"]) ? "isloggedin" : "" ?>">


	<!-- dialog windows -->
	<div id="check-book-confirm" title="" style="display: none">
		<br>
		<p>
			<span class="ui-icon ui-icon-alert" style="float:left; margin:3px 12px 20px 0;"></span>
			<span class="confirm_message"><?php echo Language::show("check_book_confirm", "Events") ?></span>
		</p>
	</div>

	<div id="dialog-message" title="<?php echo Language::show("alert_message", "Events") ?>" style="display: none">
		<br>
		<p>
			<span class="ui-icon ui-icon-alert" style="float:left; margin:3px 7px 30px 0;"></span>
			<span class="alert_message"></span>
		</p>
	</div>