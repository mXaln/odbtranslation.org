<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title">
            <div class="demo_title"><?php echo __("demo") . " (".__("8steps_vmast").")" ?></div>
            <div><?php echo __("step_num", ["step_number" => 4]) . ": "   . __("read-chunk")?></div>
        </div>
        <div class="demo_video">
            <span class="glyphicon glyphicon-play"></span>
            <a href="#"><?php echo __("demo_video"); ?></a>
        </div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <div class="main_content_text">
                <h4>English - <?php echo __("ulb") ?> - <?php echo __("new_test") ?> - <span class="book_name">2 Timothy 2:1-3</span></h4>

                <p><strong><sup>1</sup></strong> You therefore, my child, be strengthened in the grace that is in Christ Jesus.</p>
                <p><strong><sup>2</sup></strong> And the things you heard from me among many witnesses, entrust them to faithful people who will be able to teach others also.</p>
                <p><strong><sup>3</sup></strong> Suffer hardship with me, as a good soldier of Christ Jesus.</p>
            </div>

            <div class="main_content_footer row">
                <form action="" method="post">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                        <label><input name="confirm_step" id="confirm_step" value="1" type="checkbox"> <?php echo __("confirm_yes")?></label>
                    </div>

                    <button id="next_step" class="btn btn-primary" disabled="disabled"><?php echo __("next_step")?></button>
                </form>
                <div class="step_right"><?php echo __("step_num", ["step_number" => 4])?></div>
            </div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_float">
                <div class="help_info_steps">
                    <div class="help_hide toggle-help glyphicon glyphicon-eye-close" title="<?php echo __("hide_help") ?>"></div>
                    <div class="help_title_steps"><?php echo __("help") ?></div>

                    <div class="clear"></div>

                    <div class="help_name_steps"><span><?php echo __("step_num", ["step_number" => 4])?>:</span> <?php echo __("read-chunk")?></div>
                    <div class="help_descr_steps">
                        <ul><?php echo __("read-chunk_desc")?></ul>
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

                <div class="tr_tools">
                    <button class="btn btn-warning ttools" data-tool="rubric"><?php echo __("show_rubric") ?></button>
                </div>
            </div>
        </div>
    </div>

    <div class="help_show toggle-help glyphicon glyphicon-question-sign" title="<?php echo __("show_help") ?>"></div>
</div>

<div class="ttools_panel rubric_tool panel panel-default" draggable="true">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo __("show_rubric") ?></h1>
        <span class="panel-close glyphicon glyphicon-remove" data-tool="rubric"></span>
    </div>

    <div class="ttools_content page-content panel-body">
        <ul class="nav nav-tabs nav-justified read_rubric_tabs">
            <li role="presentation" id="tab_orig" class="active"><a href="#">English demo1</a></li>
            <li role="presentation" id='tab_eng'><a href="#">English</a></li>
        </ul>
        <div class="read_rubric_qualities">
            <br>
            <div class="read_rubric_quality orig" dir="ltr"> 1. Accessible </div>
            <div class="read_rubric_quality eng" style="display: none;"> </div>
            <div class="read_rubric_defs">
                <div class="read_rubric_def orig" dir="ltr"> 1. Created in necessary formats. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Is it created in necessary formats? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 2. Easily reproduced and distributed. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Is it easily reproduced? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                    <div class="read_rubric_measurement orig" dir="ltr"> 2. Is it easily distributed? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 3. Appropriate font, size and layout. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Is it in the appropriate font, size and layout? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 4. Editable. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Is it editable? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
            </div>
            <div class="read_rubric_quality orig" dir="ltr"> 2. Faithful </div>
            <div class="read_rubric_quality eng" style="display: none;"> </div>
            <div class="read_rubric_defs">
                <div class="read_rubric_def orig" dir="ltr"> 1. Reflects Original Text. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Does in reflect original text? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 2. True to Greek and Hebrew. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Is it true to Greek and Hebrew? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 3. Does not have additions or deletions. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Does it have additions or deletions? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 4. Names of God retained. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Are the names of God retained? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 5. Accurate key terms/key words. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Are key terms/words accurate? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
            </div>
            <div class="read_rubric_quality orig" dir="ltr"> 3. Culturally Relevant </div>
            <div class="read_rubric_quality eng" style="display: none;"> </div>
            <div class="read_rubric_defs">
                <div class="read_rubric_def orig" dir="ltr"> 1. Idioms are understandable </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Are idioms understandable? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 2. Words and expressions appropriate for local culture. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Are words and expressions appropriate for local culture? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 3. Reflects original language artistry. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Does it reflect original language artistry? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 4. Captures literary genres. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Are literary genres captured accurately? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
            </div>
            <div class="read_rubric_quality orig" dir="ltr"> 4. Clear </div>
            <div class="read_rubric_quality eng" style="display: none;"> </div>
            <div class="read_rubric_defs">
                <div class="read_rubric_def orig" dir="ltr"> 1. Meaning is clear. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Is the meaning clear? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 2. Uses common language. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Does it use common language? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 3. Easily understood by wide audience. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Is it easily understood by a wide audience? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
            </div>
            <div class="read_rubric_quality orig" dir="ltr"> 5. Proper Grammar </div>
            <div class="read_rubric_quality eng" style="display: none;"> </div>
            <div class="read_rubric_defs">
                <div class="read_rubric_def orig" dir="ltr"> 1. Follows grammar norms. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Does it follow grammar norms? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 2. Correct punctuation. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Is correct punctuation used? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
            </div>
            <div class="read_rubric_quality orig" dir="ltr"> 6. Consistent </div>
            <div class="read_rubric_quality eng" style="display: none;"> </div>
            <div class="read_rubric_defs">
                <div class="read_rubric_def orig" dir="ltr"> 1. Translation reflects contextual meaning. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Does the translation reflect contextual meaning? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 2. Does not contradict itself. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Does the text contradict itself? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 3. Writing style consistent. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Is the writing style consistent? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
            </div>
            <div class="read_rubric_quality orig" dir="ltr"> 7. Historically Accurate </div>
            <div class="read_rubric_quality eng" style="display: none;"> </div>
            <div class="read_rubric_defs">
                <div class="read_rubric_def orig" dir="ltr"> 1. All names, dates, places, events are accurately represented. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Are all names accurately represented? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                    <div class="read_rubric_measurement orig" dir="ltr"> 2. Are all dates accurately represented? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                    <div class="read_rubric_measurement orig" dir="ltr"> 3. Are all places accurately represented? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                    <div class="read_rubric_measurement orig" dir="ltr"> 4. Are all events accurately represented? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
            </div>
            <div class="read_rubric_quality orig" dir="ltr"> 8. Natural </div>
            <div class="read_rubric_quality eng" style="display: none;"> </div>
            <div class="read_rubric_defs">
                <div class="read_rubric_def orig" dir="ltr"> 1. Translation uses common and natural language. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Does the translation use common and natural language? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 2. Pleasant to read/listen to. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. It is pleasant to read/listen to? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 3. Easy to read. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Is it easy to read? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
            </div>
            <div class="read_rubric_quality orig" dir="ltr"> 9. Objective </div>
            <div class="read_rubric_quality eng" style="display: none;"> </div>
            <div class="read_rubric_defs">
                <div class="read_rubric_def orig" dir="ltr"> 1. Translation does not explain or commentate. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Does the translation explain or commentate? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 2. Translation is free of political, social, denominational bias. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Is translation is free of political bias? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                    <div class="read_rubric_measurement orig" dir="ltr"> 2. Is translation is free of social bias? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                    <div class="read_rubric_measurement orig" dir="ltr"> 3. Is translation is free of denominational bias? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
            </div>
            <div class="read_rubric_quality orig" dir="ltr"> 10. Widely Accepted </div>
            <div class="read_rubric_quality eng" style="display: none;"> </div>
            <div class="read_rubric_defs">
                <div class="read_rubric_def orig" dir="ltr"> 1. Translation is widely accepted by local church. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Is translation widely accepted by the local church? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
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
            if(!hasChangesOnPage) window.location.href = '/events/demo/blind_draft';

            return false;
        });
    });
</script>