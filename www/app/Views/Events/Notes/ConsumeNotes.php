<?php
if(isset($data["error"])) return;
?>
<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo __("step_num", ["step_number" => 2]) . ": " . __("verbalize_tn")?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <div class="main_content_text" dir="<?php echo $data["event"][0]->sLangDir ?>">
                <h4><?php echo $data["event"][0]->tLang." - "
                        .__($data["event"][0]->bookProject)." - "
                    .($data["event"][0]->abbrID <= 39 ? __("old_test") : __("new_test"))." - "
                    ."<span class='book_name'>".$data["event"][0]->name." ".
                    (!$data["nosource"] 
                        ? $data["currentChapter"].":1-".$data["totalVerses"]
                        : "(".__("intro").")")."</span>"?></h4>

                <?php foreach($data["notes"] as $chunkNo => $chunk): ?>
                    <div class="note_chunk">
                        <div class="note_chunk_verses">
                            <?php 
                            if(!$data["nosource"])
                            {
                                $verses = array_keys($data["text"][$chunkNo]);
                                if($verses[0] != $verses[sizeof($verses)-1])
                                    echo __("chunk_verses", $verses[0] . "-" . $verses[sizeof($verses)-1]);
                                else
                                    echo __("chunk_verses", $verses[0]);
                            }
                            ?>
                        </div>
                        <?php foreach($chunk as $note): ?>
                            <div class="note_content">
                                <?php echo $note ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php //if(empty($error)):?>
            <div class="main_content_footer row">
                <form action="" method="post">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo __("confirm_yes")?></label>
                    </div>

                    <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo __("next_step")?></button>
                </form>
                <div class="step_right"><?php echo __("step_num", ["step_number" => 2])?></div>
            </div>
            <?php //endif; ?>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_float">
                <div class="help_info_steps<?php echo $data["isCheckerPage"] ? " is_checker_page_help" : "" ?>">
                    <div class="help_hide toggle-help glyphicon glyphicon-eye-close" title="<?php echo __("hide_help") ?>"></div>
                    <div class="help_title_steps"><?php echo __("help") ?></div>

                    <div class="clear"></div>

                    <div class="help_name_steps"><span><?php echo __("step_num", ["step_number" => 2])?>: </span><?php echo __("verbalize_tn")?></div>
                    <div class="help_descr_steps">
                        <ul><?php echo __("consume_desc")?></ul>
                        <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div>
                    </div>
                </div>

                <div class="event_info<?php echo $data["isCheckerPage"] ? " is_checker_page_help" : "" ?>">
                    <div class="participant_info">
                        <div class="additional_info">
                            <a href="/events/information-tn/<?php echo $data["event"][0]->eventID ?>"><?php echo __("event_info") ?></a>
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
            <img src="<?php echo template_url("img/steps/icons/consume.png") ?>" width="100px" height="100px">
            <img src="<?php echo template_url("img/steps/big/consume.png") ?>" width="280px" height="280px">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="<?php echo $data["event"][0]->step ?>" type="checkbox" value="0" /> <?php echo __("do_not_show_tutorial")?></label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo __("verbalize_tn")?></h3>
            <ul><?php echo __("consume_desc")?></ul>
        </div>
    </div>
</div>