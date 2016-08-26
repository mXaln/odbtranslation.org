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
		(Session::get("isAdmin") ? Url::templatePath() . 'js/mainAdmin.js' : ''),
		Url::templatePath() . 'js/bootstrap.min.js',
		//Url::templatePath() . 'js/jquery.elastic.source.js'
		Url::templatePath() . 'js/autosize.min.js',
		Url::templatePath() . 'js/jquery-ui.min.js',
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

		<ul class="nav nav-pills col-md-4" role="tablist">
			<li <?php if($data['menu'] == 1):?>class="active"<?php endif?> role="presentation"><a href="<?php echo DIR?>"><?php echo Language::show('home', 'Main')?></a></li>
			<li <?php if($data['menu'] == 3):?>class="active"<?php endif?> role="presentation"><a href="<?php echo DIR?>translations"><?php echo Language::show('translations_title', 'Main')?></a></li>
			<li <?php if($data['menu'] == 4):?>class="active"<?php endif?> role="presentation"><a href="<?php echo DIR?>events"><?php echo Language::show('events_title', 'Main')?></a></li>
			<!--<li <?php if($data['menu'] == 5):?>class="active"<?php endif?> role="presentation"><a href="<?php echo DIR?>contact"><?php echo Language::show('contact_us_title', 'Main')?></a></li>
			<li <?php if($data['menu'] == 6):?>class="active"<?php endif?> role="presentation"><a href="<?php echo DIR?>about"><?php echo Language::show('about_title', 'Main')?></a></li>-->
		</ul>

		<ul class="list-inline col-md-8" style="text-align: right">
			<li><a class="btn btn-link" href="<?php echo DIR?>lang/en">English</a></li>
			<li><a class="btn btn-link" href="<?php echo DIR?>lang/ru">Русский</a></li>
			<?php if(\Helpers\Session::get('loggedin')): ?>
			| <li class="notifications">
					<a class="btn btn-link" id="notifications" href="#">
                        <span class="notif_title"><?php echo Language::show("notifications", "Events") ?></span>
                        <?php echo !empty($data["notifications"]) ? '<span class="notif_count">'.sizeof($data["notifications"]).'</span>' : ""; ?>
                    </a>
					<ul class="notif_block">
					<?php if(!empty($data["notifications"])):?>
						<?php foreach ($data["notifications"] as $notification):?>
							<?php
                            $type = $notification->step == EventSteps::KEYWORD_CHECK ? "kw_checker" : "cont_checker";
                            $text = Language::show('checker_apply', 'Events', array(
                                $notification->userName,
                                $notification->bookName,
                                $notification->currentChapter,
                                $notification->tLang,
                                Language::show($notification->bookProject, "Events"),
                            ));
                            ?>
							<?php if(!isset($data["isDemo"])): ?>
							<a class="notifa" href="/events/checker/<?php echo $notification->eventID."/".$notification->memberID; ?>/apply"
                               data="check:<?php echo $notification->eventID.":".$notification->memberID ?>">
                                <li><?php echo $text; ?></li>
                            </a>
							<?php else: ?>
							<a class="notifa" href="/events/demo/keyword_check_checker">
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
			| <li><a class="btn btn-link" href="<?php echo DIR?>members/logout"><?php echo Language::show('logout', 'Members')?></a></li>
				<li><a class="btn btn-link" href="<?php echo DIR?>members/profile"><?php echo \Helpers\Session::get("userName")?></a></li>
			<?php endif?>
		</ul>

	</div>


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