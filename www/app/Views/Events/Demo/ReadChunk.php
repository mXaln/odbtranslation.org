<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo __("step_num", [4]) . ": "   . __("read-chunk")?></div>
        <div class="demo_title"><?php echo __("demo") ?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <div class="main_content_text">
                <h4>English - <?php echo __("ulb") ?> - <?php echo __("new_test") ?> - <span class="book_name">2 Timothy 2:1-7</span></h4>

                <p><strong><sup>1</sup></strong> You therefore, my child, be strengthened in the grace that is in Christ Jesus.</p>
                <p><strong><sup>2</sup></strong> And the things you heard from me among many witnesses, entrust them to faithful people who will be able to teach others also.</p>
                <p><strong><sup>3</sup></strong> Suffer hardship with me, as a good soldier of Christ Jesus.</p>
                <p><strong><sup>4</sup></strong> No soldier serves while entangled in the affairs of this life, so that he may please his superior officer.</p>
                <p><strong><sup>5</sup></strong> Also, if someone competes as an athlete, he is not crowned unless he competes by the rules.</p>
                <p><strong><sup>6</sup></strong> It is necessary that the hardworking farmer receive his share of the crops first.</p>
                <p><strong><sup>7</sup></strong> Think about what I am saying, for the Lord will give you understanding in everything.</p>
            </div>

            <div class="main_content_footer row">
                <form action="" method="post">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                        <label><input name="confirm_step" id="confirm_step" value="1" type="checkbox"> <?php echo __("confirm_yes")?></label>
                    </div>

                    <button id="next_step" class="btn btn-primary" disabled="disabled"><?php echo __("next_step")?></button>
                </form>
            </div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps">
                <div class="help_title_steps"><?php echo __("help") ?></div>

                <div class="clear"></div>

                <div class="help_name_steps"><span><?php echo __("step_num", [4])?>:</span> <?php echo __("read-chunk")?></div>
                <div class="help_descr_steps">
                    <ul><?php echo mb_substr(__("read-chunk_desc"), 0, 300)?>... <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div></ul>
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


<div class="tutorial_container">
    <div class="tutorial_popup">
        <div class="tutorial-close glyphicon glyphicon-remove"></div>
        <div class="tutorial_pic">
            <img src="<?php echo template_url("img/steps/icons/blind-draft.png") ?>" height="100px" width="100px">
            <img src="<?php echo template_url("img/steps/big/blind-draft.png") ?>" height="280px" width="280px">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="read-chunk" value="0" type="checkbox"> <?php echo __("do_not_show_tutorial")?></label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo __("read-chunk")?></h3>
            <ul><?php echo __("read-chunk_desc")?></ul>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $("#next_step").click(function (e) {
            e.preventDefault();

            deleteCookie("temp_tutorial");
            window.location.href = '/events/demo/blind_draft';

            return false;
        });
    });
</script>