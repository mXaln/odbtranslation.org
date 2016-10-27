<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo __("pray")?></div>
        <div class="demo_title">Demo</div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <div class="main_content_text">
                <img src="<?php echo template_url("img/steps/big/pray.png") ?>">
                <br>
                <div><?php echo __("pray_text")?></div>
            </div>

            <div class="main_content_footer row">
                <form action="" method="post">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                        <label><input name="confirm_step" id="confirm_step" value="1" type="checkbox"> <?php echo __("confirm_yes")?></label>
                    </div>

                    <button id="next_step" onclick="window.location.href='<?php echo SITEURL ?>events/demo/consume'; return false;" class="btn btn-primary" disabled="disabled"><?php echo __("next_step")?></button>
                </form>
            </div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps">
                <div class="help_title_steps">HELP</div>

                <div class="clear"></div>

                <div class="help_name_steps"><span><?php echo __("pray")?></span></div>
                <div class="help_descr_steps">
                    <ul><?php echo __("pray_desc")?></ul>
                </div>
            </div>

            <div class="event_info">
                <div class="participant_info">
                    <div class="additional_info">
                        <a href="#"><?php echo __("event_info") ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>