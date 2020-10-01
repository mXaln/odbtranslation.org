<?php
/**
 * Custom Layout - a Layout similar with the classic Header and Footer files.
 */

use Helpers\Url;
use Helpers\Session;
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
            dsn: '<?php echo Config::get("sentry.dsn") ?>',
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
                    baseHref: 'https://v-mast.com/',
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
    template_url('css/bootstrap.min.css', 'Default'),
    template_url('css/style.css?110', 'Default'),
    template_url('css/jquery-ui.min.css', 'Default'),
    template_url('css/jquery-ui.structure.min.css', 'Default'),
    template_url('css/jquery-ui.theme.min.css', 'Default'),
    template_url('css/summernote.css', 'Default'),
]);

echo isset($css) ? $css : ''; // Place to pass data / plugable hook zone

Assets::js([
    template_url('js/jquery.js', 'Default'),
    template_url('js/jquery.actual.min.js', 'Default'),
    template_url('js/main.js?105', 'Default'),
    template_url('js/facilitator.js?32', 'Default'),
    template_url('js/autosize.min.js?2', 'Default'),
    template_url('js/admin.js?50', 'Default'),
    template_url('js/bootstrap.min.js', 'Default'),
    template_url('js/jquery-ui.min.js', 'Default'),
    template_url('js/offline.min.js'),
    template_url('js/summernote/summernote.min.js'),
    ($languageFull != "en-US" ? template_url('js/i18n/summernote-'.$languageFull.'.js') : ""),
]);

echo isset($js) ? $js : ''; // Place to pass data / plugable hook zone
?>
</head>
<body class="header_bg">

<?= isset($afterBody) ? $afterBody : ''; // Place to pass data / plugable hook zone ?>

<div class="container">

    <div class="header page-header <?php echo Session::get("loggedin") ? "loggedin" : ""?>">
        <div class="header_menu_left">
            <a href="/" class="logo"><img src="<?php echo Url::templatePath() ?>img/logo.png" height="40" /></a>

            <ul class="nav nav-pills col-sm-8" role="tablist">
                <li <?php if($data['menu'] == 0):?>class="active"<?php endif?> role="presentation"><a href="/"><?php echo __("home")?></a></li>
                <li <?php if($data['menu'] == 1):?>class="active"<?php endif?> role="presentation"><a href="/admin"><?php echo __("admin")?></a></li>
                <li <?php if($data['menu'] == 2):?>class="active"<?php endif?> role="presentation"><a href="/admin/members"><?php echo __("members")?></a></li>
                <li <?php if($data['menu'] == 3):?>class="active"<?php endif?> role="presentation"><a href="/admin/tools"><?php echo __("tools")?></a></li>
                <li role="presentation">
                    <a id="news" href="/events/news">
                        <span class="topnews_title"><?php echo __("news") ?></span>
                        <?php echo isset($data["newNewsCount"]) && $data["newNewsCount"] > 0 ? '<span class="news_count">'.$data["newNewsCount"].'</span>' : ""; ?>
                    </a>
                </li>
            </ul>
        </div>

        <ul class="list-inline header_menu_right admin">
            <li>
                <div class="profile-select">
                    <div class="dropdown-toggle" id="profile-select" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <div class="uName"><?php echo Session::get("userName")?></div>
                        <span class="caret"></span>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="profile-select">
                        <li><a href="/members/profile"><?php echo __("profile_message") ?></a></li>
                        <li><a href="/admin"><?php echo __('admin')?></a></li>
                        <li><a href="/events/faq"><?php echo __('FAQ')?></a></li>
                        <li><a href="/contact"><?php echo __('Helpdesk')?></a></li>
                        <li><a href="/members/logout"><?php echo __('logout')?></a></li>
                    </ul>
                </div>
            </li>
        </ul>
    </div>

    <div class="container_block <?php echo !isset($data["isMain"]) ? "isloggedin" : "" ?>">
        <!-- dialog windows -->
        <div id="check-book-confirm" title="" style="display: none">
            <br>
            <p>
                <span class="ui-icon ui-icon-alert" style="float:left; margin:3px 12px 20px 0;"></span>
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
            <div style="margin: 15px 0 0; display: flex">
                <div class="col-lg-3">
                    <p class="text-muted">Copyright &copy; <?php echo date('Y'); ?> Wycliffe Associates. <?php echo Config::get("version.release") ?></p>
                </div>
                <div class="col-lg-7">
                    <p class="text-muted pull-right">
                        <?php if(Config::get('app.debug')) { ?>
                        <small><!-- DO NOT DELETE! - Profiler --></small>
                        <?php } ?>
                    </p>
                </div>
                <div class="col-sm-2 footer_langs">
                    <?php if(Session::get("loggedin")): ?>
                        <div class="dropup flangs">
                            <div class="dropdown-toggle" id="footer_langs" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img src="<?php echo template_url("img/".$language.".png", "Default") ?>">
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
