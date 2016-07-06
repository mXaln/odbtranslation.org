<?php
/**
 * Sample layout
 */

use Helpers\Assets;
use Helpers\Url;
use Helpers\Hooks;
use \Core\Language;
use \Helpers\Constants\EventSteps;

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

	<link rel="icon" href="/favicon.ico" type="image/x-icon" />

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
		Url::templatePath() . 'js/bootstrap.min.js',
		//Url::templatePath() . 'js/jquery.elastic.source.js'
		Url::templatePath() . 'js/autosize.min.js'
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

		<ul class="nav nav-pills col-md-6" role="tablist">
			<li <?php if($data['menu'] == 1):?>class="active"<?php endif?> role="presentation"><a href="<?php echo DIR?>"><?php echo Language::show('home', 'Main')?></a></li>
			<li <?php if($data['menu'] == 3):?>class="active"<?php endif?> role="presentation"><a href="<?php echo DIR?>translations"><?php echo Language::show('translations_title', 'Main')?></a></li>
			<li <?php if($data['menu'] == 4):?>class="active"<?php endif?> role="presentation"><a href="<?php echo DIR?>events"><?php echo Language::show('events_title', 'Main')?></a></li>
			<!--<li <?php if($data['menu'] == 5):?>class="active"<?php endif?> role="presentation"><a href="<?php echo DIR?>contact"><?php echo Language::show('contact_us_title', 'Main')?></a></li>
			<li <?php if($data['menu'] == 6):?>class="active"<?php endif?> role="presentation"><a href="<?php echo DIR?>about"><?php echo Language::show('about_title', 'Main')?></a></li>-->
		</ul>

		<ul class="list-inline col-md-6">
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
							<a href="/events/checker/<?php echo $notification->eventID."/".$notification->memberID; ?>/apply"
                               data="check:<?php echo $notification->eventID.":".$notification->memberID ?>"
                                target="_blank">
                                <li><?php echo $text; ?></li>
                            </a>
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