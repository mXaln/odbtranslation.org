<?php
if(isset($data["error"])) return;
?>
<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo __("pray")?></div>
    </div>

    <div class="row">
        <div class="main_content">
            <div class="main_content_text">
                <div class="pray_left">
                    <img src="<?php echo template_url("img/steps/icons/pray.png") ?>" width="80">
                    <br><br>
                    <img src="<?php echo template_url("img/steps/big/".(!$data["event"][0]->justStarted ? "guys" : "pray").".png") ?>" width="300">
                </div>
                <div class="pray_right">
                    <?php echo __((!$data["event"][0]->justStarted ? "prep_" : "")."pray_text")?>
                </div>
                <div class="clear"></div>
            </div>

            <div class="main_content_footer row">
                <form action="" method="post">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo __("confirm_yes")?></label>
                    </div>

                    <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo __("next_step")?></button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="content_help closed">
    <div id="help_hide" class="glyphicon glyphicon-chevron-left"> <?php echo __("help") ?></div>

    <div class="help_float">
        <div class="help_info_steps">
            <div class="help_name_steps"><span><?php echo __("pray")?></span></div>
            <div class="help_descr_steps">
                <ul><?php echo __("pray_desc")?></ul>
            </div>
        </div>

        <div class="event_info">
            <div class="participant_info">
                <div class="additional_info">
                    <a href="/events/information-sun/<?php echo $data["event"][0]->eventID ?>"><?php echo __("event_info") ?></a>
                </div>
            </div>
        </div>
    </div>
</div>