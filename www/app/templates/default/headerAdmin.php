<?php
/**
 * Sample layout
 */

use Helpers\Assets;
use Helpers\Url;
use Helpers\Hooks;
use Core\Language;

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
        Url::templatePath() . 'js/mainAdmin.js',
        Url::templatePath() . 'js/bootstrap.min.js',
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

        <ul class="nav nav-pills col-md-8" role="tablist">
            <li <?php if($data['menu'] == 1):?>class="active"<?php endif?> role="presentation"><a href="<?php echo DIR?>admin"><?php echo \Core\Language::show('home', 'Main')?></a></li>
            <li <?php if($data['menu'] == 4):?>class="active"<?php endif?> role="presentation"><a href="<?php echo DIR?>contact"><?php echo \Core\Language::show('contact_us_title', 'Main')?></a></li>
            <li <?php if($data['menu'] == 5):?>class="active"<?php endif?> role="presentation"><a href="<?php echo DIR?>about"><?php echo \Core\Language::show('about_title', 'Main')?></a></li>
        </ul>

        <ul class="list-inline col-md-4">
            <li><a class="btn btn-link" href="<?php echo DIR?>lang/en">English</a></li>
            <li><a class="btn btn-link" href="<?php echo DIR?>lang/ru">Русский</a></li>
            <?php if(\Helpers\Session::get('loggedin')): ?>
                | <li><a class="btn btn-link" href="<?php echo DIR?>members/logout"><?php echo \Core\Language::show('logout', 'Members')?></a></li>
            <?php endif?>
        </ul>

    </div>

    <!-- dialog windows -->
    <div id="dialog-message" title="<?php echo Language::show("alert_message", "Events") ?>" style="display: none">
        <br>
        <p>
            <span class="ui-icon ui-icon-alert" style="float:left; margin:3px 7px 30px 0;"></span>
            <span class="alert_message"></span>
        </p>
    </div>