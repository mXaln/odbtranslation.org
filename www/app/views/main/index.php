<?php
use Core\Language;
?>

<div class="welcome_content">
	<h1><?php echo Language::show("welcome_text", "Main") ?></h1>
	<h3><?php echo Language::show("welcome_hint", "Main") ?></h3>

	<div class="text"><?php echo Language::show("welcome_message", "Main")?></div>

	<div id="ground-center" class="hide_img"><img src="<?php echo \Helpers\Url::templatePath() ?>img/ground-center.png"></div>
	<div id="ground-left" class="hide_img"><img src="<?php echo \Helpers\Url::templatePath() ?>img/ground-left.png"></div>
	<div id="ground-right" class="hide_img"><img src="<?php echo \Helpers\Url::templatePath() ?>img/ground-right.png"></div>
	<div id="cloud-left" class="hide_img"><img src="<?php echo \Helpers\Url::templatePath() ?>img/cloud-left.png"></div>
	<div id="cloud-right" class="hide_img"><img src="<?php echo \Helpers\Url::templatePath() ?>img/cloud-right.png"></div>
</div>


