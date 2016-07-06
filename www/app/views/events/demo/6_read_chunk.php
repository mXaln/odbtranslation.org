<?php
use Core\Language;
?>

<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo Language::show("step_num", "Events", array(4)) . Language::show("chunking", "Events")?></div>
        <div class="demo_title"><?php echo Language::show("demo", "Events") ?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <div class="main_content_text">
                <h4>English - Unlocked Literal Bible - New Testament - <span class='book_name'>2 Timothy 4:1-6</span></h4>

                                    <p><strong><sup>1</sup></strong> I 
give this solemn command before God and Christ Jesus, who will judge the
 living and the dead, and because of his appearing and his kingdom:

  </p>
                                    <p><strong><sup>2</sup></strong> Preach the Word. Be ready when it is convenient and when it is not. Reprove, rebuke, exhort, with all patience and teaching.
  </p>
                                    <p><strong><sup>3</sup></strong> For
 the time will come when people will not endure sound teaching. Instead,
 they will heap up for themselves teachers according to their own 
desires. They will be tickling their hearing.

  </p>
                                    <p><strong><sup>4</sup></strong> They will turn their hearing away from the truth, and they will turn aside to myths.

  </p>
                                    <p><strong><sup>5</sup></strong> But you, be sober-minded in all things. Suffer hardship; do the work of an evangelist; fulfill your service.
  </p>
                                    <p><strong><sup>6</sup></strong> For I am already being poured out. The time of my departure has come.

  </p>
                                    <p><strong><sup></sup></strong> </p>
                            </div>

            <div class="main_content_footer row">
                <form action="" method="post">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo Language::show("confirm_finished", "Events")?></div>
                        <label><input name="confirm_step" id="confirm_step" value="1" type="checkbox"> <?php echo Language::show("confirm_yes", "Events")?></label>
                    </div>

                    <button id="next_step" class="btn btn-primary" disabled="disabled"><?php echo Language::show("next_step", "Events")?></button>
                </form>
            </div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps">
                <div class="help_title_steps"><?php echo Language::show("help", "Events") ?></div>

                <div class="clear"></div>

                <div class="help_name_steps"><span><?php echo Language::show("step_num", "Events", array(4))?></span> <?php echo Language::show("chunking", "Events")?></div>
                <div class="help_descr_steps">
                    <ul><?php echo mb_substr(Language::show("chunking_desc", "Events"), 0, 300)?>... <div class="show_tutorial_popup"> >>> <?php echo Language::show("show_more", "Events")?></div></ul>
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
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/icons/chunking.png" height="100px" width="100px">
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/big/chunking.png" height="280px" width="280px">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="chunking" value="0" type="checkbox"> <?php echo Language::show("do_not_show_tutorial", "Events")?></label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo Language::show("chunking", "Events")?></h3>
            <ul><?php echo Language::show("chunking_desc", "Events")?></ul>
        </div>
    </div>
</div>

<script>
    (function($) {
        $("#next_step").click(function() {

            var url = '<?php echo DIR."events/demo/" . (!isset($_COOKIE["demo_mode"]) || $_COOKIE["demo_mode"] == "gl" ? "draft_self_check" : "blind_draft") ?>';

            window.location.href=url;

            return false;
        });
    }(jQuery));
</script>