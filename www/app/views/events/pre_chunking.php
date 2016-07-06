<?php
use \Core\Language;
?>

<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo Language::show("step_num", "Events", array(4)) . Language::show("pre-chunking", "Events")?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <div class="main_content_text">
                <h4><?php echo $data["event"][0]->sLang." - "
                        .Language::show($data["event"][0]->bookProject, "Events")." - "
                        .($data["event"][0]->abbrID <= 39 ? Language::show("old_test", "Events") : Language::show("new_test", "Events"))." - "
                        ."<span class='book_name'>".$data["event"][0]->name." ".$data["currentChapter"].":1-".$data["totalVerses"]."</span>"?></h4>

                <?php for($i=2; $i <= sizeof($data["text"]); $i+=2): ?>
                    <p class="verse_p">
                        <label class="verse_number_label">
                            <input type="checkbox" name="verse" class="verse_number" value="<?php echo $data["text"][$i-1]; ?>">
                            <?php echo "<strong><sup>".$data["text"][$i-1]."</sup></strong> ".$data["text"][$i]; ?>
                        </label>
                    </p>
                <?php endfor; ?>
                <div class="chunks_reset">Reset chunks</div>
            </div>

            <div class="main_content_footer row">
                <form action="" method="post">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo Language::show("confirm_finished", "Events")?></div>
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo Language::show("confirm_yes", "Events")?></label>
                        <input type="hidden" name="chunks_array" id="chunks_array" value="[]">
                    </div>

                    <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo Language::show("continue", "Events")?></button>
                </form>
            </div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps">
                <div class="help_title_steps"><?php echo Language::show("help", "Events") ?></div>

                <div class="clear"></div>

                <div class="help_name_steps"><span><?php echo Language::show("step_num", "Events", array(4))?></span> <?php echo Language::show("pre-chunking", "Events")?></div>
                <div class="help_descr_steps">
                    <ul><?php echo mb_substr(Language::show("pre-chunking_desc", "Events"), 0, 300)?>... <div class="show_tutorial_popup"> >>> <?php echo Language::show("show_more", "Events")?></div></ul>
                </div>
            </div>

            <div class="event_info">
                <div class="participant_info">
                    <div class="participant_name">
                        <span><?php echo Language::show("your_partner", "Events") ?>:</span>
                        <span><?php echo $data["event"][0]->pairName ?></span>
                    </div>
                    <div class="participant_name">
                        <span><?php echo Language::show("your_checker", "Events") ?>:</span>
                        <span><?php echo $data["event"][0]->checkerName !== null ? $data["event"][0]->checkerName : "N/A" ?></span>
                    </div>
                    <div class="additional_info">
                        <a href="/events/information/<?php echo $data["event"][0]->eventID ?>"><?php echo Language::show("event_info", "Events") ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<span class="create_chunk">Make chunk</span>


<div class="tutorial_container">
    <div class="tutorial_popup">
        <div class="tutorial-close glyphicon glyphicon-remove"></div>
        <div class="tutorial_pic">
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/icons/chunking.png" width="100px" height="100px">
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/big/chunking.png" width="280px" height="280px">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="<?php echo $data["event"][0]->step ?>" type="checkbox" value="0" /> <?php echo Language::show("do_not_show_tutorial", "Events")?></label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo Language::show("pre-chunking", "Events")?></h3>
            <ul><?php echo Language::show("pre-chunking_desc", "Events")?></ul>
        </div>
    </div>
</div>