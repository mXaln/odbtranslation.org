<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title">
            <div class="demo_title"><?php echo __("demo") . " (".__("8steps_vmast").")" ?></div>
            <div><?php echo __("step_num", [4]) . ": " . __("blind-draft")?></div>
        </div>
        <div class="demo_video">
            <span class="glyphicon glyphicon-play"></span>
            <a href="#"><?php echo __("demo_video"); ?></a>
        </div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <form action="" method="post" id="main_form">
                <div class="main_content_text">
                    <div class="row">
                        <h4>English - <?php echo __("ulb") ?> - <?php echo __("new_test") ?> - <span class='book_name'>2 Timothy 2:1-3</span></h4>
                        <!--
                        &nbsp;&nbsp;&nbsp;
                        <button class="spec_char" data="D̃">D̃</button>
                        <button class="spec_char" data="d̃">d̃</button>&nbsp;&nbsp;
                        <button class="spec_char" data="Õ">Õ</button>
                        <button class="spec_char" data="õ">õ</button>&nbsp;&nbsp;
                        <button class="spec_char" data="T̃">T̃</button>
                        <button class="spec_char" data="t̃">t̃</button>&nbsp;&nbsp;
                        <button class="spec_char" data="Ṽ">Ṽ</button>
                        <button class="spec_char" data="ṽ">ṽ</button>&nbsp;&nbsp;
                        <button class="spec_char" data="W̃">W̃</button>
                        <button class="spec_char" data="w̃">w̃</button>-->
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

                    <button id="next_step" class="btn btn-primary" disabled="disabled"><?php echo __("next_step")?></button>
                </div>
            </form>
            <div class="step_right alt"><?php echo __("step_num", [4])?></div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_float">
                <div class="help_info_steps">
                    <div class="help_hide toggle-help glyphicon glyphicon-eye-close" title="<?php echo __("hide_help") ?>"></div>
                    <div class="help_title_steps"><?php echo __("help") ?></div>

                    <div class="clear"></div>

                    <div class="help_name_steps"><span><?php echo __("step_num", [4])?>:</span> <?php echo __("blind-draft")?></div>
                    <div class="help_descr_steps">
                        <ul><?php echo __("blind-draft_desc")?></ul>
                        <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div>
                    </div>
                </div>

                <div class="event_info">
                    <div class="participant_info">
                        <div class="additional_info">
                            <a href="/events/demo/information"><?php echo __("event_info") ?></a>
                        </div>
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

<script>
    $(document).ready(function () {
        $("#next_step").click(function (e) {
            e.preventDefault();

            deleteCookie("temp_tutorial");
            window.location.href = '/events/demo/self_check';

            return false;
        });
        
        $(".spec_char").click(function(e) {
            e.preventDefault();
            var char = $(this).attr("data");
            
            var textArea = $("textarea[name=draft]");
	    var caretPos = textArea[0].selectionStart;
            var textAreaTxt = textArea.val();
            textArea.val(textAreaTxt.substring(0, caretPos) + char + textAreaTxt.substring(caretPos));
        });
    });
</script>