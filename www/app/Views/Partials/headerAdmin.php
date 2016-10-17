<?php
/**
 * Sample layout
 */

use Helpers\Assets;
use Helpers\Url;
use Helpers\Hooks;
use Core\Language;
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

    <!-- CSS -->
    <?php
    Assets::css(array(
        Url::templatePath() . 'css/bootstrap.min.css',
        Url::templatePath() . 'css/bootstrap-theme.min.css',
        Url::templatePath() . 'css/jquery-ui.min.css',
        Url::templatePath() . 'css/jquery-ui.structure.min.css',
        Url::templatePath() . 'css/jquery-ui.theme.min.css',
        Url::templatePath() . 'css/style.css',
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
        Url::templatePath() . 'js/mainAdmin.js',
        Url::templatePath() . 'js/bootstrap.min.js',
        Url::templatePath() . 'js/jquery-ui.min.js',
    ));

    //hook for plugging in javascript
    $hooks->run('js');
    ?>

</head>
<body class="header_bg">
<?php
//hook for running code after body tag
$hooks->run('afterBody');
?>

<div class="container">

    <div class="header page-header row <?php echo Session::get("loggedin") ? "loggedin" : ""?>">
        <div class="col-sm-8 row header_menu_left">
            <a href="<?php echo SITEURL?>" class="col-sm-4 logo"><img src="<?php echo Url::templatePath() ?>img/logo.png" height="40" /></a>

            <ul class="nav nav-pills col-sm-8" role="tablist">
                <li <?php if($data['menu'] == 1):?>class="active"<?php endif?> role="presentation"><a href="<?php echo SITEURL?>admin"><?php echo \Core\Language::show('home', 'Main')?></a></li>
            </ul>
        </div>

        <ul class="list-inline col-sm-4 header_menu_right">
            <li>
                <div class="profile-select">
                    <div class="dropdown-toggle" id="profile-select" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <div class="uName"><?php echo Session::get("userName")?></div>
                        <span class="caret"></span>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="profile-select">
                        <li><a href="/members/profile"><?php echo Language::show("profile_message", "Members") ?></a></li>
                        <li><a href="/admin"><?php echo Language::show('admin', 'Members')?></a></li>
                        <li><a href="/members/logout"><?php echo Language::show('logout', 'Members')?></a></li>
                    </ul>
                </div>
            </li>
        </ul>
    </div>

    <div class="container_block <?php echo !isset($data["isMain"]) ? "isloggedin" : "" ?>">

    <!-- dialog windows -->
    <div id="dialog-message" title="<?php echo Language::show("alert_message", "Events") ?>" style="display: none">
        <br>
        <p>
            <span class="ui-icon ui-icon-alert" style="float:left; margin:3px 7px 30px 0;"></span>
            <span class="alert_message"></span>
        </p>
    </div>