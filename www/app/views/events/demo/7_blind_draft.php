<?php
use Core\Language;
?>

<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo Language::show("step_num", "Events", array(5)) . Language::show("blind-draft", "Events")?></div>
        <div class="demo_title"><?php echo Language::show("demo", "Events") ?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <form action="" method="post" id="main_form">
                <div class="main_content_text">
                    <div class="row">
                        <h4>English - Unlocked Literal Bible - New Testament - <span class='book_name'>2 Timothy 4:1-6</span></h4>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <textarea style="overflow: hidden; word-wrap: break-word; height: 328px;" name="draft" rows="10" class="col-sm-6 blind_ta textarea"></textarea>
                        </div>
                    </div>
                </div>

                <div class="main_content_footer row">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo Language::show("confirm_finished", "Events")?></div>
                        <label><input name="confirm_step" id="confirm_step" value="1" type="checkbox"> <?php echo Language::show("confirm_yes", "Events")?></label>
                    </div>

                    <button id="next_step" onclick="window.location.href='<?php echo DIR ?>events/demo/self_check'; return false;" class="btn btn-primary" disabled="disabled"><?php echo Language::show("next_step", "Events")?></button>
                </div>
            </form>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps">
                <div class="help_title_steps"><?php echo Language::show("help", "Events") ?></div>

                <div class="clear"></div>

                <div class="help_name_steps"><span><?php echo Language::show("step_num", "Events", array(5))?></span> <?php echo Language::show("blind-draft", "Events")?></div>
                <div class="help_descr_steps">
                    <ul><?php echo mb_substr(Language::show("blind-draft_desc", "Events"), 0, 300)?>... <div class="show_tutorial_popup"> >>> <?php echo Language::show("show_more", "Events")?></div></ul>
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


<div class="tutorial_container">
    <div class="tutorial_popup">
        <div class="tutorial-close glyphicon glyphicon-remove"></div>
        <div class="tutorial_pic">
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/icons/blind-draft.png" height="100px" width="100px">
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/big/blind-draft.png" height="280px" width="280px">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="blind-draft" value="0" type="checkbox"> <?php echo Language::show("do_not_show_tutorial", "Events")?></label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo Language::show("blind-draft", "Events")?></h3>
            <ul><?php echo Language::show("blind-draft_desc", "Events")?></ul>
        </div>
    </div>
</div>