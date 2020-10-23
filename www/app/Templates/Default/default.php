<?php
/**
 * Default Layout - a Layout similar with the classic Header and Footer files.
 */

use Helpers\Session;
use Helpers\Url;
use Config\Config;

$language = ucfirst(Language::code());
$languages = Config::get('languages');
setcookie("lang", $language, time() + 365*24*3600, "/");
$languageFull = "";

switch ($language) {
    case 'Ru':
        $languageFull = "ru-RU";
        break;
    
    case 'Id':
        $languageFull = "id-ID";
        break;

    case 'Fr':
        $languageFull = "fr-FR";
        break;

    default:
        $languageFull = "en-US";
        break;
}

// Load proper locale json file
try {
    $jsonLocale = include '../app/Language/'.$language.'/frontend.php';
} catch (Exception $e) {
    $jsonLocale = "{}";
}

if(!isset($data)) $data = ["menu" => 1];
?>
<!DOCTYPE html>
<html lang="<?=$language; ?>">
<head>
    <meta charset="utf-8">
    <title><?= $title .' - ' .Config::get('app.name', SITETITLE); ?></title>

    <link rel="icon" href="<?php echo template_url("favicon.ico") ?>" type="image/x-icon" />

    <script>
        var Language = <?php echo json_encode($jsonLocale) ?>;
    </script>

    <script src="https://browser.sentry-cdn.com/5.15.5/bundle.min.js" integrity="<?php echo Config::get("sentry.integrity") ?>" crossorigin="anonymous"></script>

    <script>
        Sentry.init({
            dsn: '<?php echo Config::get("sentry.dsn_js") ?>',
            release: '<?php echo Config::get("version.release") ?>',
            environment: '<?php echo ENVIRONMENT ?>'
        });
    </script>

    <!-- LogRocket initialization -->
    <?php if(Config::get("app.type") == "remote"): ?>
        <script src="https://cdn.lr-ingest.io/LogRocket.min.js" crossorigin="anonymous"></script>
        <script>
            window.LogRocket && window.LogRocket.init('<?php echo Config::get("logrocket.project") ?>', {
                release: '<?php echo Config::get("version.release") ?>',
                dom: {
                    baseHref: 'https://odbtranslation.org/',
                },
            });
        </script>

        <?php if(Session::get("userName")): ?>
            <script>
                LogRocket.identify('<?php echo Session::get("userName") ?>');
                LogRocket.getSessionURL(sessionURL => {
                    Sentry.configureScope(scope => {
                        scope.setExtra("sessionURL", sessionURL);
                    });
                });
            </script>
        <?php endif; ?>
    <?php endif; ?>
<?php
echo isset($meta) ? $meta : ''; // Place to pass data / plugable hook zone

Assets::css([
    template_url('css/bootstrap.min.css'),
    template_url('css/style.css?112'),
    template_url('css/jquery-ui.min.css'),
    template_url('css/jquery-ui.structure.min.css'),
    template_url('css/jquery-ui.theme.min.css'),
    template_url('css/summernote.css'),
    template_url('css/bootstrap-toggle.min.css'),
    template_url('css/jquery.highlight-within-textarea.css'),
    template_url('css/materialdesignicons.min.css'),
]);

echo isset($css) ? $css : ''; // Place to pass data / plugable hook zone

Assets::js([
    template_url('js/jquery.js'),
    template_url('js/jquery.actual.min.js'),
    template_url('js/main.js?106', 'Default'),
    (Session::get("isAdmin") || Session::get("isSuperAdmin") ?  template_url('js/facilitator.js?34') : ''),
    (Session::get("isSuperAdmin") ? template_url('js/admin.js?52') : ''),
    template_url('js/bootstrap.min.js'),
    template_url('js/autosize.min.js?2'),
    template_url('js/jquery-ui.min.js'),
    template_url('js/offline.min.js'),
    template_url('js/dragdroptouch.js'),
    template_url('js/summernote/summernote.min.js'),
    template_url('js/bootstrap-toggle.min.js'),
    template_url('js/jquery.highlight-within-textarea.js'),
    ($languageFull != "en-US" ? template_url('js/i18n/summernote-'.$languageFull.'.js') : ""),
]);

echo isset($js) ? $js : ''; // Place to pass data / plugable hook zone
?>
<script>
    var siteLang = '<?php echo $languageFull ?>';
</script>
</head>
<body class="<?php echo isset($data["isMain"]) ? "welcome_bg" : "header_bg"?>">

<?= isset($afterBody) ? $afterBody : ''; // Place to pass data / plugable hook zone ?>

<div class="container">

    <div class="header page-header <?php echo Session::get("loggedin") ? "loggedin" : ""?>">

        <div class="header_menu_left">
            <a href="/" class="logo"><img src="<?php echo template_url("img/logo.png") ?>" height="40" /></a>

            <ul class="nav nav-pills" role="tablist">
                <?php if(Session::get('loggedin')): ?>
                    <li <?php if($data['menu'] == 1):?>class="active"<?php endif?> role="presentation">
                        <a href="/"><?php echo __('home')?></a>
                    </li>
                    <li <?php if($data['menu'] == 3):?>class="active"<?php endif?> role="presentation">
                        <a href="/translations"><?php echo __('translations_title')?></a>
                    </li>

                    <?php if(Session::get("isAdmin")): ?>
                    <li <?php if($data['menu'] == 4):?>class="active"<?php endif?> role="presentation">
                        <a href="/members/search"><?php echo __('members')?></a>
                    </li>
                    <?php endif; ?>

                    <li <?php if($data['menu'] == 6):?>class="active"<?php endif?> role="presentation">
                        <a id="news" href="/events/news">
                            <span class="topnews_title"><?php echo __("news") ?></span>
                            <?php echo isset($data["newNewsCount"]) && $data["newNewsCount"] > 0 ? '<span class="news_count">'.$data["newNewsCount"].'</span>' : ""; ?>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if(!Session::get('loggedin') || Session::get("isDemo")): ?>
                    <li id="demo_link" class="<?php echo isset($data["menu"]) && $data["menu"] == 5 ? "active" : "" ?>" role="presentation">
                        <a href="#"><?php echo __('demo')?></a>
                        <div class="demo_options menu_link">
                            <ul>
                                <a href="/events/demo"><li><?php echo __("8steps_mast") ?></li></a>
                                <a href="/events/demo-scripture-input"><li><?php echo __("lang_input") ?></li></a>
                                <a href="/events/demo-l2"><li><?php echo __("l2_l3_mast", ["level" => 2]); ?></li></a>
                                <a href="/events/demo-l3"><li><?php echo __("l2_l3_mast", ["level" => 3]); ?></li></a>
                                <a href="/events/demo-sun"><li><?php echo __("vsail") ?></li></a>
                                <a href="/events/demo-sun-odb"><li><?php echo __("odb") . " (".__("vsail").")" ?></li></a>
                            </ul>
                        </div>
                    </li>
                    <li id="faq_link" class="<?php echo isset($data["menu"]) && $data["menu"] == 0 ? "active" : "" ?>" role="presentation">
                        <a href="/events/faq"><?php echo __('faq')?></a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>

        <ul class="list-inline header_menu_right">
            <?php if((isset($data["isDemo"]) && $data["isDemo"]) || Session::get('loggedin')): ?>
                <li class="notifications">
                    <a class="btn btn-link" id="notifications">
                        <span class="notif_title" title="<?php echo __("notifications") ?>"><img src="<?php echo Url::templatePath() ?>img/notif.png"></span>
                        <?php echo !empty($data["notifications"]) ? '<span class="notif_count">'.sizeof($data["notifications"]).'</span>' : ""; ?>
                    </a>
                    <ul class="notif_block">
                        <?php if(!empty($data["notifications"])):?>
                            <?php foreach ($data["notifications"] as $notification):?>
                                <?php
                                $demoType = "demo";
                                $type = $notification->step . "_checker";
                                $text_data = array(
                                    "name" => $notification->firstName . " " . mb_substr($notification->lastName, 0, 1).".",
                                    "step" => ($notification->step != "other" ? "(".__($notification->step .
                                            ($notification->sourceBible == "odb"
                                                ? "_odb" : "")).")" : ""),
                                    "book" => $notification->bookName,
                                    "chapter" => ($notification->currentChapter == 0
                                        ? __("intro")
                                        : $notification->currentChapter),
                                    "language" => $notification->tLang,
                                    "project" => ($notification->sourceBible == "odb"
                                        ?__($notification->sourceBible)
                                        : __($notification->bookProject))
                                );

                                $text = __('checker_apply', $text_data);

                                if(!isset($data["isDemo"]))
                                {
                                    $link = "/events/checker".(isset($notification->manageMode)
                                        && in_array($notification->manageMode, ["sun","odb"]) ? "-".$notification->manageMode : "")
                                        ."/".$notification->eventID."/"
                                        .$notification->memberID."/"
                                        .$notification->step."/"
                                        .(isset($notification->manageMode) ? $notification->currentChapter."/" : "")
                                        ."apply";
                                }
                                else
                                {
                                    $ltype = $type;
                                    if(isset($notification->manageMode) && $notification->manageMode == "l2")
                                    {
                                        $demoType = "demo-l2";
                                        $ltype = preg_replace("/_checker/", "", $type);
                                    }
                                    elseif (isset($notification->manageMode) && in_array($notification->manageMode, ["sun","sun-odb"]))
                                    {
                                        $demoType = "demo-" . $notification->manageMode;
                                        $ltype = preg_replace("/other_checker/", "pray_chk", $type);
                                    }
                                }

                                ?>
                                <?php if(!isset($data["isDemo"])): ?>
                                    <a class="notifa" href="<?php echo $link ?>"
                                       data="check:<?php echo $notification->eventID.":".$notification->memberID ?>" target="_blank">
                                        <li class="<?php echo $type?>"><?php echo $text; ?></li>
                                    </a>
                                <?php else: ?>
                                    <a class="notifa" href="/events/<?php echo $demoType ?>/<?php echo preg_replace("/-/", "_", $ltype) ?>" target="_blank">
                                        <li class="<?php echo $type?>"><?php echo $text; ?></li>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class='no_notif'><?php echo __("no_notifs_msg") ?></div>
                        <?php endif; ?>
                        <div class="all_notifs"><a href="/events/notifications"><?php echo __("see_all") ?></a></div>
                    </ul>
                </li>
            <?php endif; ?>
            <?php if(Session::get('loggedin')): ?>
                <li>
                    <div class="profile-select">
                        <div class="dropdown-toggle" id="profile-select" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                            <div class="uName"><?php echo Session::get("userName")?></div>
                            <span class="caret"></span>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="profile-select">
                            <li><a href="/members/profile"><?php echo __("profile_message") ?></a></li>
                            <?php if(Session::get("isSuperAdmin")): ?>
                                <li><a href="/admin"><?php echo __('admin')?></a></li>
                            <?php endif; ?>
                            <li><a href="/events/faq"><?php echo __('FAQ')?></a></li>
                            <li><a href="/contact"><?php echo __('Helpdesk')?></a></li>
                            <li><a href="/members/logout"><?php echo __('logout')?></a></li>
                        </ul>
                    </div>
                </li>
            <?php else: ?>
                <li><a href="/members/signup" class="btn btn-success"><?php echo __("signup") ?></a></li>
                <li><a href="/members/login" class="btn btn-primary"><?php echo __("login") ?></a></li>
                <li>
                    <div class="dropdown flangs">
                        <div class="dropdown-toggle" id="footer_langs" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img src="<?php echo template_url("img/" . $language . ".png", "Default"); ?>">
                            <span class="caret"></span>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="footer_langs">
                            <?php foreach ($languages as $code => $lang): ?>
                            <li>
                                <a href="/language/<?php echo $code ?>" title="<?php echo $lang['info']; ?>">
                                    <img src="<?php echo template_url("img/".$code.".png", "Default") ?>"> <?php echo $lang['name']; ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
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
                <span class="glyphicon glyphicon glyphicon-alert" style="float:left; margin: 7px 25px 60px 0; font-size: 30px; color: #f00;"></span>
                <span class="confirm_message"><?php echo __("check_book_confirm") ?></span>
            </p>
        </div>

        <div id="dialog-message" title="<?php echo __("alert_message") ?>" style="display: none">
            <br>
            <p>
                <span class="ui-icon ui-icon-alert" style="float:left; margin:3px 7px 30px 0;"></span>
                <span class="alert_message"></span>
            </p>
        </div>

        <?= $content; ?>
    </div>

    <footer class="footer">
        <div class="container-fluid">
            <div class="footer_container">
                <div>
                    <p class="text-muted">Copyright &copy; <?php echo date('Y'); ?> Our Daily Bread Ministries. <?php echo Config::get("version.release") ?></p>
                </div>
                <div>
                    <p class="text-muted pull-right">
                        <?php if(Config::get('app.debug')) { ?>
                        <small><!-- DO NOT DELETE! - Profiler --></small>
                        <?php } ?>
                    </p>
                </div>
                <div class="footer_langs">
                    <?php if(Session::get("loggedin")): ?>
                        <div class="dropup flangs">
                            <div class="dropdown-toggle" id="footer_langs" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img src="<?php echo template_url("img/".$language.".png") ?>">
                                <span class="caret"></span>
                            </div>
                            <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="footer_langs">
                                <?php foreach ($languages as $code => $lang): ?>
                                    <li>
                                        <a href="/language/<?php echo $code ?>" title="<?php echo $lang['info']; ?>">
                                            <img src="<?php echo template_url("img/".$code.".png") ?>"> <?php echo $lang['name']; ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </footer>

<?php
echo isset($footer) ? $footer : ''; // Place to pass data / plugable hook zone
?>
</div>

<!-- DO NOT DELETE! - Forensics Profiler -->

</body>
</html>
