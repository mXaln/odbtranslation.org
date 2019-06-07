<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title">
            <div class="demo_title"><?php echo __("demo") . " (".__("tn").")" ?></div>
            <div><?php echo __("step_num", ["step_number" => 2]) . ": " . __("read-chunk_tn")?></div>
        </div>
        <div class="demo_video">
            <span class="glyphicon glyphicon-play"></span>
            <a href="#"><?php echo __("demo_video"); ?></a>
        </div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <div class="main_content_text" dir="ltr">
                <h4>Bahasa Indonesia - <?php echo __("tn") ?> - <?php echo __("new_test") ?> - <span class="book_name">Acts 1:1-3</span></h4>

                <p></p>

                <div class="note_content">
                    <h1>The former book I wrote</h1>
                    <p>The former book is the Gospel of Luke.</p>
                    <h1>Theophilus</h1>
                    <p>Luke wrote this book to a man named Theophilus. Some translations follow their own culture's way of addressing a letter and write "Dear Theophilus" at the beginning of the sentence. Theophilus means "friend of God" (See: [[rc://en/ta/man/translate/translate-names]])</p>
                    <h1>until the day that he was taken up</h1>
                    <p>This refers to Jesus' ascension into heaven. AT: "until the day on which God took him up to heaven" or "until the day that he ascended into heaven" (See: [[rc://en/ta/man/translate/figs-activepassive]])</p>
                    <h1>commands through the Holy Spirit</h1>
                    <p>The Holy Spirit led Jesus to instruct his apostles on certain things.</p>
                    <h1>After his suffering</h1>
                    <p>This refers to Jesus' suffering and death on the cross.</p>
                    <h1>he presented himself alive to them</h1>
                    <p>Jesus appeared to his apostles and to many other disciples.</p>
                    <h1>translationWords</h1>
                    <ul>
                        <li>[[rc://en/tw/dict/bible/kt/jesus]]</li>
                        <li>[[rc://en/tw/dict/bible/kt/command]]</li>
                        <li>[[rc://en/tw/dict/bible/kt/holyspirit]]</li>
                        <li>[[rc://en/tw/dict/bible/kt/apostle]]</li>
                        <li>[[rc://en/tw/dict/bible/other/suffer]]</li>
                        <li>[[rc://en/tw/dict/bible/kt/kingdomofgod]]</li>
                    </ul>
                </div>
            </div>

            <div class="main_content_footer row">
                <form action="" method="post">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                        <label><input name="confirm_step" id="confirm_step" value="1" type="checkbox"> <?php echo __("confirm_yes")?></label>
                    </div>

                    <button id="next_step" class="btn btn-primary" disabled="disabled"><?php echo __("next_step")?></button>
                </form>
                <div class="step_right"><?php echo __("step_num", ["step_number" => 2])?></div>
            </div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_float">
                <div class="help_info_steps">
                    <div class="help_hide toggle-help glyphicon glyphicon-eye-close" title="<?php echo __("hide_help") ?>"></div>
                    <div class="help_title_steps"><?php echo __("help") ?></div>

                    <div class="clear"></div>

                    <div class="help_name_steps"><span><?php echo __("step_num", ["step_number" => 2])?>:</span> <?php echo __("read-chunk_tn")?></div>
                    <div class="help_descr_steps">
                        <ul><?php echo __("read-chunk_tn_desc")?></ul>
                        <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div>
                    </div>
                </div>

                <div class="event_info">
                    <div class="participant_info">
                        <div class="additional_info">
                            <a href="/events/demo-tn/information"><?php echo __("event_info") ?></a>
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
                <label><input id="hide_tutorial" data="consume" value="0" type="checkbox"> <?php echo __("do_not_show_tutorial")?></label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo __("read-chunk_tn")?></h3>
            <ul><?php echo __("read-chunk_tn_desc")?></ul>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $("#next_step").click(function (e) {
            e.preventDefault();

            deleteCookie("temp_tutorial");
            window.location.href = '/events/demo-tn/blind_draft';

            return false;
        });
    });
</script>