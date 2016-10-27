<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo __("step_num", [4]) . ": " . __("blind-draft")?></div>
        <div class="demo_title"><?php echo __("demo") ?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <form action="" method="post" id="main_form">
                <div class="main_content_text">
                    <div class="row">
                        <h4>English - <?php echo __("ulb") ?> - <?php echo __("new_test") ?> - <span class='book_name'>2 Timothy 2:1-7</span></h4>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <textarea style="overflow: hidden; word-wrap: break-word; height: 328px;" name="draft" rows="10" class="col-sm-6 blind_ta textarea"></textarea>
                        </div>
                    </div>
                </div>

                <div class="main_content_footer row">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                        <label><input name="confirm_step" id="confirm_step" value="1" type="checkbox"> <?php echo __("confirm_yes")?></label>
                    </div>

                    <button id="next_step" onclick="window.location.href='<?php echo SITEURL ?>events/demo/self_check'; return false;" class="btn btn-primary" disabled="disabled"><?php echo __("next_step")?></button>
                </div>
            </form>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps">
                <div class="help_title_steps"><?php echo __("help") ?></div>

                <div class="clear"></div>

                <div class="help_name_steps"><span><?php echo __("step_num", [4])?>:</span> <?php echo __("blind-draft")?></div>
                <div class="help_descr_steps">
                    <ul><?php echo mb_substr(__("blind-draft_desc"), 0, 300)?>... <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div></ul>
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


<div class="tutorial_container">
    <div class="tutorial_popup">
        <div class="tutorial-close glyphicon glyphicon-remove"></div>
        <div class="tutorial_pic">
            <img src="<?php echo template_url("img/steps/icons/blind-draft.png") ?>" height="100px" width="100px">
            <img src="<?php echo template_url("img/steps/big/blind-draft.png") ?>" height="280px" width="280px">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="blind-draft" value="0" type="checkbox"> <?php echo __("do_not_show_tutorial")?></label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo __("blind-draft")?></h3>
            <ul><?php echo __("blind-draft_desc")?></ul>
        </div>
    </div>
</div>