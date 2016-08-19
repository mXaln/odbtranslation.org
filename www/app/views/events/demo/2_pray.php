<?php
use Core\Language;
?>

<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo Language::show("pray", "Events")?></div>
        <div class="demo_title">Demo</div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <div class="main_content_text">
                <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/big/pray.png">
                <br>
                <div><?php echo Language::show("pray_text", "Events")?></div>
            </div>

            <div class="main_content_footer row">
                <form action="" method="post">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo Language::show("confirm_finished", "Events")?></div>
                        <label><input name="confirm_step" id="confirm_step" value="1" type="checkbox"> <?php echo Language::show("confirm_yes", "Events")?></label>
                    </div>

                    <button id="next_step" onclick="window.location.href='<?php echo DIR ?>events/demo/consume'; return false;" class="btn btn-primary" disabled="disabled"><?php echo Language::show("next_step", "Events")?></button>
                </form>
            </div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps">
                <div class="help_title_steps">HELP</div>

                <div class="clear"></div>

                <div class="help_name_steps"><span><?php echo Language::show("pray", "Events")?></span></div>
                <div class="help_descr_steps">
                    <ul><?php echo Language::show("pray_desc", "Events")?></ul>
                </div>
            </div>

            <div class="event_info">
                <div class="participant_info">
                    <div class="participant_name">
                        <span><?php echo Language::show("your_partner", "Events") ?>:</span>
                        <span>Gen2Pet</span>
                    </div>
                    <div class="participant_name">
                        <span><?php echo Language::show("your_checker", "Events") ?>:</span>
                        <span>N/A</span>
                    </div>
                    <div class="additional_info">
                        <a href="#"><?php echo Language::show("event_info", "Events") ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>