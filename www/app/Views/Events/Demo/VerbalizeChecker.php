<div class="editor">
    <div class="comment_div panel panel-default">
        <div class="panel-heading">
            <h1 class="panel-title"><?php echo __("write_note_title")?></h1>
            <span class="editor-close glyphicon glyphicon-floppy-disk"></span>
        </div>
        <textarea class="textarea textarea_editor" style="overflow-x: hidden; word-wrap: break-word; overflow-y: visible;"></textarea>
        <div class="other_comments_list"></div>
        <img src="<?php echo template_url("img/loader.gif") ?>" class="commentEditorLoader">
    </div>
</div>


<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo __("step_num", [2]) . ": " . __("verbalize")?></div>
        <div class="demo_title"><?php echo __("demo") ?></div>
        <div class="demo_sep"> | </div>
        <div class="demo_video"><a href="#"><?php echo __("demo_video"); ?></a></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <div class="main_content_text row">
                <div class="keywords_show" style=""><?php echo __("show_keywords"); ?></div>

                <h4>English - <?php echo __("ulb") ?> - <?php echo __("new_test") ?> - <span class='book_name'>2 Timothy 2:1-26</span></h4>

                <div class="col-sm-12">
                    <img src="<?php echo template_url("img/steps/big/verbalize.png") ?>">
                </div>
            </div>

            <div class="main_content_footer row">
                <form action="" method="post" id="checker_submit">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                        <label><input name="confirm_step" id="confirm_step" value="1" type="checkbox"> <?php echo __("confirm_yes")?></label>
                    </div>

                    <button id="next_step" class="btn btn-primary" disabled=""><?php echo __("continue")?></button>
                </form>
            </div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps is_checker_page_help">
                <div class="help_title_steps"><?php echo __("help") ?></div>

                <div class="clear"></div>

                <div class="help_name_steps"><span><?php echo __("step_num", [2])?>:</span> <?php echo __("verbalize")?></div>
                <div class="help_descr_steps">
                    <ul><?php echo __("verbalize" . "_checker_desc")?></ul>
                    <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div>
                </div>
            </div>

            <div class="event_info is_checker_page_help">
                <div class="participant_info">
                    <div class="participant_name">
                        <span><?php echo __("your_translator") ?>:</span>
                        <span>mpat1977</span>
                    </div>
                    <div class="additional_info">
                        <a href="/events/demo/information"><?php echo __("event_info") ?></a>
                    </div>
                </div>
            </div>

            <div class="checker_view">
                <a href="<?php echo SITEURL ?>events/demo/verbalize"><?php echo __("translator_view") ?></a>
            </div>
        </div>
    </div>
</div>


<div class="tutorial_container">
    <div class="tutorial_popup">
        <div class="tutorial-close glyphicon glyphicon-remove"></div>
        <div class="tutorial_pic">
            <img src="<?php echo template_url("img/steps/icons/verbalize.png") ?>" width="100px" height="100px">
            <img src="<?php echo template_url("img/steps/big/verbalize.png") ?>" width="280px" height="280px">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="verbalize_checker" data2="checker" type="checkbox" value="0"> <?php echo __("do_not_show_tutorial")?></label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo __("verbalize")?></h3>
            <ul><?php echo __("verbalize_checker_desc")?></ul>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $("#next_step").click(function (e) {
            e.preventDefault();

            deleteCookie("temp_tutorial");
            window.location.href = '/events/demo/chunking';

            return false;
        });
    });
</script>