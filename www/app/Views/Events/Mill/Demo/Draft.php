<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title">
            <div class="demo_title">
                <?php echo __("demo") . " (".__("mill").")" ?>
            </div>
            <div><?php echo __("step_num", ["step_number" => 3]). ": " . __("blind-draft_mill")?></div>
        </div>
    </div>

    <div class="">
        <div class="main_content">
            <form action="" method="post" id="main_form">
                <div class="main_content_text row" style="padding-left: 15px">
                    <h4>Español - <?php echo __("mill") ?> - <span class="book_name">Biblical Foundations 1: Manuscript 3 : 1</span></h4>

                    <div class="col-sm-12 no_padding">
                        <div class="row chunk_block words_block">
                            <div class="chunk_verses col-sm-6" dir="ltr">
                                <div class="mill-heading ltr">
                                    <strong><sup>1</sup></strong>
                                    <h2>KINDS</h2>
                                </div>
                            </div>
                            <div class="col-sm-6 editor_area" dir="ltr">
                                <textarea name="draft" class="col-sm-6 verse_ta textarea" style="overflow: hidden; overflow-wrap: break-word; height: 80px; min-height: 90px;"></textarea>
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
                    <img src="<?php echo template_url("img/saving.gif") ?>" class="unsaved_alert" style="float:none">
                </div>
            </form>
            <div class="step_right alt"><?php echo __("step_num", ["step_number" => 3])?></div>
        </div>
    </div>
</div>

<div class="content_help closed">
    <div id="help_hide" class="glyphicon glyphicon-chevron-left"> <?php echo __("help") ?></div>

    <div class="help_float">
        <div class="help_info_steps">
            <div class="help_name_steps"><span><?php echo __("step_num", ["step_number" => 3])?>:</span> <?php echo __("blind-draft_odb")?></div>
            <div class="help_descr_steps">
                <ul><?php echo __("blind-draft_mill_desc")?></ul>
                <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div>
            </div>
        </div>

        <div class="event_info">
            <div class="participant_info">
                <div class="additional_info">
                    <a href="/events/demo-mill/information"><?php echo __("event_info") ?></a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="tutorial_container">
    <div class="tutorial_popup">
        <div class="tutorial-close glyphicon glyphicon-remove"></div>
        <div class="tutorial_pic">
            <img src="<?php echo template_url("img/steps/icons/blind-draft.png") ?>" width="100" height="100">
            <img src="<?php echo template_url("img/steps/big/blind-draft.png") ?>" width="280" height="280">
            
        </div>

        <div class="tutorial_content">
            <h3><?php echo __("blind-draft_mill")?></h3>
            <ul><?php echo __("blind-draft_mill_desc")?></ul>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $("#next_step").click(function (e) {
            e.preventDefault();

            if(!hasChangesOnPage) window.location.href = '/events/demo-mill/self-check';

            return false;
        });
    });
</script>