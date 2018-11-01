<?php
if(isset($data["error"])) return;
?>
<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo __("step_num", [3]) . ": " . __("chunking")?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <div class="main_content_text" dir="<?php echo $data["event"][0]->sLangDir ?>">
                <h4><?php echo $data["event"][0]->tLang." - "
                        .__($data["event"][0]->bookProject)." - "
                        .($data["event"][0]->abbrID <= 39 ? __("old_test") : __("new_test"))." - "
                        ."<span class='book_name'>".$data["event"][0]->name." ".$data["currentChapter"].":1-".$data["totalVerses"]."</span>"?></h4>

                <?php foreach($data["text"] as $verse => $text): ?>
                    <p class="verse_p">
                        <label class="verse_number_label">
                            <input type="checkbox" name="verse" class="verse_number" value="<?php echo $verse; ?>">
                            <?php echo "<strong><sup>".$verse."</sup></strong> ".$text; ?>
                        </label>
                    </p>
                <?php endforeach; ?>
            </div>

            <div class="main_content_footer row">
                <form action="" method="post">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo __("confirm_yes")?></label>
                        <input type="hidden" name="chunks_array" id="chunks_array" value="[]">
                    </div>

                    <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo __("continue")?></button>
                </form>
                <div class="step_right"><?php echo __("step_num", [3])?></div>
            </div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_float">
                <div class="help_info_steps">
                    <div class="help_hide toggle-help glyphicon glyphicon-eye-close" title="<?php echo __("hide_help") ?>"></div>
                    <div class="help_title_steps"><?php echo __("help") ?></div>

                    <div class="clear"></div>

                    <div class="help_name_steps"><span><?php echo __("step_num", [3])?>: </span> <?php echo __("chunking")?></div>
                    <div class="help_descr_steps">
                        <ul><?php echo __("chunking_desc")?></ul>
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
    </div>

    <div class="help_show toggle-help glyphicon glyphicon-question-sign" title="<?php echo __("show_help") ?>"></div>
</div>

<span class="clone create_chunk" title="<?php echo __("make_chunk") ?>">Make chunk</span>
<span class="clone chunks_reset glyphicon glyphicon-ban-circle" title="<?php echo __("reset_chunks") ?>"></span>

<?php if (!empty($data["rubric"])): ?>
<div class="ttools_panel rubric_tool panel panel-default" draggable="true">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo __("show_rubric") ?></h1>
        <span class="panel-close glyphicon glyphicon-remove" data-tool="rubric"></span>
    </div>

    <div class="ttools_content page-content panel-body">
        <ul class="nav nav-tabs nav-justified read_rubric_tabs">
            <li role="presentation" id="tab_orig" class="active"><a href="#"><?php echo $data["rubric"]->language->langName ?></a></li>
            <li role="presentation" id='tab_eng'><a href="#">English</a></li>
        </ul>
        <div class="read_rubric_qualities">
            <br>
            <?php $tr=1; foreach($data["rubric"]->qualities as $quality): ?>
                <div class="read_rubric_quality orig" dir="<?php echo $data['rubric']->language->direction ?>">
                    <?php echo !empty($quality->content) ? sprintf('%01d', $tr) . ". " .  $quality->content : ""; ?>
                </div>
                <div class="read_rubric_quality eng">
                    <?php echo !empty($quality->eng) ? sprintf('%01d', $tr) . ". " . $quality->eng : ""; ?>
                </div>

                <div class="read_rubric_defs">
                    <?php $df=1; foreach($quality->defs as $def): ?>
                        <div class="read_rubric_def orig" dir="<?php echo $data['rubric']->language->direction ?>">
                            <?php echo !empty($def->content) ? sprintf('%01d', $df) . ". " . $def->content : ""; ?>
                        </div>
                        <div class="read_rubric_def eng">
                            <?php echo !empty($def->eng) ? sprintf('%01d', $df) . ". " . $def->eng : ""; ?>
                        </div>

                        <div class="read_rubric_measurements">
                            <?php $ms=1; foreach($def->measurements as $measurement): ?>
                                <div class="read_rubric_measurement orig" dir="<?php echo $data['rubric']->language->direction ?>">
                                    <?php echo !empty($measurement->content) ? sprintf('%01d', $ms) . ". " . $measurement->content : ""; ?>
                                </div>
                                <div class="read_rubric_measurement eng">
                                    <?php echo !empty($measurement->eng) ? sprintf('%01d', $ms) . ". " . $measurement->eng : ""; ?>
                                </div>
                                <?php $ms++; endforeach; ?>
                        </div>
                        <?php $df++; endforeach; ?>
                </div>
                <?php $tr++; endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="tutorial_container">
    <div class="tutorial_popup">
        <div class="tutorial-close glyphicon glyphicon-remove"></div>
        <div class="tutorial_pic">
            <img src="<?php echo template_url("img/steps/icons/chunking.png") ?>" width="100px" height="100px">
            <img src="<?php echo template_url("img/steps/big/chunking.png") ?>" width="280px" height="280px">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="<?php echo $data["event"][0]->step ?>" type="checkbox" value="0" /> <?php echo __("do_not_show_tutorial")?></label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo __("chunking")?></h3>
            <ul><?php echo __("chunking_desc")?></ul>
        </div>
    </div>
</div>