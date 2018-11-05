<?php
if(isset($data["error"])) return;
?>
<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo __("step_num", [4]). ": " . __("blind-draft")?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <form action="" method="post" id="main_form">
                <div class="main_content_text">
                    <div class="row">
                        <h4 dir="<?php echo $data["event"][0]->sLangDir ?>"><?php echo $data["event"][0]->tLang." - "
                                .__($data["event"][0]->bookProject)." - "
                                .($data["event"][0]->abbrID <= 39 ? __("old_test") : __("new_test"))." - "
                                ."<span class='book_name'>".$data["event"][0]->name." ".$data["currentChapter"].":".$data["chunk"][0]."-".$data["chunk"][sizeof($data["chunk"])-1]."</span>"?></h4>
                    </div>

                    <div class="row">
                        <div class="col-sm-12" dir="<?php echo $data["event"][0]->tLangDir ?>">
                            <textarea name="draft" rows="10" class="col-sm-6 blind_ta textarea"><?php
                                echo isset($_POST["draft"]) ? $_POST["draft"] : (isset($data["blind"]) ? $data["blind"] : "")
                            ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="main_content_footer row">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo __("confirm_yes")?></label>
                    </div>

                    <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo __("next_step")?></button>
                </div>
            </form>
            <div class="step_right alt"><?php echo __("step_num", [4])?></div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_float">
                <div class="help_info_steps">
                    <div class="help_hide toggle-help glyphicon glyphicon-eye-close" title="<?php echo __("hide_help") ?>"></div>
                    <div class="help_title_steps"><?php echo __("help") ?></div>

                    <div class="clear"></div>

                    <div class="help_name_steps"><span><?php echo __("step_num", [4])?>: </span> <?php echo __("blind-draft")?></div>
                    <div class="help_descr_steps">
                        <ul><?php echo __("blind-draft_desc")?></ul>
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
            <img src="<?php echo template_url("img/steps/icons/blind-draft.png") ?>" width="100px" height="100px">
            <img src="<?php echo template_url("img/steps/big/blind-draft.png") ?>" width="280px" height="280px">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="<?php echo $data["event"][0]->step ?>" type="checkbox" value="0" /> <?php echo __("do_not_show_tutorial")?></label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo __("blind-draft")?></h3>
            <ul><?php echo __("blind-draft_desc")?></ul>
        </div>
    </div>
</div>