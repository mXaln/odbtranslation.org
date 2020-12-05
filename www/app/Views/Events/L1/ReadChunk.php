<?php
if(isset($data["error"])) return;
?>
<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo __("step_num", ["step_number" => 4]). ": " . __("read-chunk")?></div>
    </div>

    <div class="">
        <div class="main_content">
            <div class="main_content_text" dir="<?php echo $data["event"][0]->sLangDir ?>">
                <h4><?php echo $data["event"][0]->sLang." - "
                        .__($data["event"][0]->bookProject)." - "
                        .($data["event"][0]->abbrID <= 39 ? __("old_test") : __("new_test"))." - "
                        ."<span class='book_name'>".$data["event"][0]->name." ".$data["currentChapter"].":".$data["chunk"][0]."-".$data["chunk"][sizeof($data["chunk"])-1]."</span>"?></h4>

                <?php foreach($data["text"] as $verse => $text): ?>
                    <p><?php echo "<strong><sup>".$verse."</sup></strong> ".$text; ?></p>
                <?php endforeach; ?>
            </div>

            <div class="main_content_footer row">
                <form action="" method="post">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo __("confirm_yes")?></label>
                    </div>

                    <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo __("next_step")?></button>
                </form>
                <div class="step_right"><?php echo __("step_num", ["step_number" => 4])?></div>
            </div>
        </div>
    </div>
</div>

<div class="content_help closed">
    <div id="help_hide" class="glyphicon glyphicon-chevron-left"> <?php echo __("help") ?></div>

    <div class="help_float">
        <div class="help_info_steps">
            <div class="help_name_steps"><span><?php echo __("step_num", ["step_number" => 4])?>: </span> <?php echo __("read-chunk")?></div>
            <div class="help_descr_steps">
                <ul><?php echo __("read-chunk_desc")?></ul>
                <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div>
            </div>
        </div>

        <div class="event_info">
            <div class="participant_info">
                <div class="additional_info">
                    <a href="/events/information/<?php echo $data["event"][0]->eventID ?>"><?php echo __("event_info") ?></a>
                </div>
            </div>
        </div>

        <div class="tr_tools">
            <button class="btn btn-warning ttools" data-tool="rubric"><?php echo __("show_rubric") ?></button>
        </div>
    </div>
</div>

<!-- Data for tools -->
<input type="hidden" id="targetLang" value="<?php echo $data["event"][0]->targetLang ?>">


<div class="tutorial_container">
    <div class="tutorial_popup">
        <div class="tutorial-close glyphicon glyphicon-remove"></div>
        <div class="tutorial_pic">
            <img src="<?php echo template_url("img/steps/icons/blind-draft.png") ?>" width="100px" height="100px">
            <img src="<?php echo template_url("img/steps/big/blind-draft.png") ?>" width="280px" height="280px">

        </div>

        <div class="tutorial_content">
            <h3><?php echo __("read-chunk")?></h3>
            <ul><?php echo __("read-chunk_desc")?></ul>
        </div>
    </div>
</div>