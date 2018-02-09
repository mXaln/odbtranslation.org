<?php
use Helpers\Constants\EventMembers;
?>
<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title">
            <div class="demo_title"><?php echo __("demo") . " (".__("vsail").")" ?></div>
            <div><?php echo __("step_num", [3]). ": " . __("rearrange")?></div>
        </div>
        <div class="demo_video">
            <span class="glyphicon glyphicon-play"></span>
            <a href="#"><?php echo __("demo_video"); ?></a>
        </div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <form action="" method="post" id="main_form">
                <div class="main_content_text row" style="padding-left: 15px">
                    <h4 dir="ltr">English - <?php echo __("sun") ?> - <?php echo __("new_test") ?> - <span class="book_name">2 Timothy 2:1-3</span> </h4>

                    <div class="col-sm-12 no_padding">
                        <div class="row chunk_block words_block">
                            <div class="chunk_verses col-sm-6" dir="ltr">
                                <p><strong><sup>1</sup></strong> You therefore, my child, be strengthened in the grace that is in Christ Jesus.</p>
                                <p><strong><sup>2</sup></strong> The things you heard from me among many witnesses, entrust them to faithful people who will be able to teach others also.</p>
                                <p><strong><sup>3</sup></strong> Suffer hardship with me, as a good soldier of Christ Jesus.</p>
                            </div>
                            <div class="col-sm-6 editor_area" dir="ltr">
                                <textarea name="draft"
                                          class="col-sm-6 peer_verse_ta textarea"
                                          style="overflow: hidden; word-wrap: break-word; height: 80px; min-height: 150px;"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="main_content_footer row">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo __("confirm_yes")?></label>
                    </div>

                    <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo __("next_step")?></button>
                    <img src="<?php echo template_url("img/alert.png") ?>" class="unsaved_alert" style="float:none">
                </div>
            </form>
            <div class="step_right alt"><?php echo __("step_num", [3])?></div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps">
                <div class="help_hide toggle-help glyphicon glyphicon-eye-close" title="<?php echo __("hide_help") ?>"></div>
                <div class="help_title_steps"><?php echo __("help") ?></div>

                <div class="clear"></div>

                <div class="help_name_steps"><span><?php echo __("step_num", [3])?>:</span> <?php echo __("rearrange")?></div>
                <div class="help_descr_steps">
                    <ul><?php echo __("rearrange_desc")?></ul>
                    <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div>
                </div>
            </div>

            <div class="event_info">
                <div class="participant_info">
                    <div class="additional_info">
                        <a href="/events/demo-sun/information"><?php echo __("event_info") ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="help_show toggle-help glyphicon glyphicon-question-sign" title="<?php echo __("show_help") ?>"></div>
</div>


<div class="tutorial_container">
    <div class="tutorial_popup">
        <div class="tutorial-close glyphicon glyphicon-remove"></div>
        <div class="tutorial_pic">
            <img src="<?php echo template_url("img/steps/icons/rearrange.png") ?>" width="100" height="100">
            <img src="<?php echo template_url("img/steps/big/rearrange.png") ?>" width="280" height="280">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="rearrange" type="checkbox" value="0" /> <?php echo __("do_not_show_tutorial")?></label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo __("rearrange")?></h3>
            <ul><?php echo __("rearrange_desc")?></ul>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $("#next_step").click(function (e) {
            e.preventDefault();

            deleteCookie("temp_tutorial");
            window.location.href = '/events/demo-sun/symbol-draft';

            return false;
        });
    });
</script>