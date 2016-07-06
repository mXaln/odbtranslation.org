<?php
use \Core\Language;
?>

<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo Language::show("step_num", "Events", array(1)) . Language::show("pray", "Events")?></div>
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
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo Language::show("confirm_yes", "Events")?></label>
                    </div>

                    <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo Language::show("next_step", "Events")?></button>
                </form>
            </div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps">
                <div class="help_title_steps"><?php echo Language::show("help", "Events") ?></div>

                <div class="clear"></div>

                <div class="help_name_steps"><span><?php echo Language::show("step_num", "Events", array(1)) ?></span><?php echo Language::show("pray", "Events")?></div>
                <div class="help_descr_steps">
                    <ul><?php echo Language::show("pray_desc", "Events")?></ul>
                </div>
            </div>

            <div class="event_info">
                <div class="participant_info">
                    <div class="participant_name">
                        <span><?php echo Language::show("your_partner", "Events") ?>:</span>
                        <span><?php echo $data["event"][0]->pairName ?></span>
                    </div>
                    <div class="participant_name">
                        <span><?php echo Language::show("your_checker", "Events") ?>:</span>
                        <span><?php echo $data["event"][0]->checkerName !== null ? $data["event"][0]->checkerName : "N/A" ?></span>
                    </div>
                    <div class="additional_info">
                        <a href="/events/information/<?php echo $data["event"][0]->eventID ?>"><?php echo Language::show("event_info", "Events") ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>