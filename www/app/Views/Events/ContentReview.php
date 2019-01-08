<?php
use Helpers\Constants\EventMembers;

if(isset($data["error"])) return;
?>

<div class="comment_div panel panel-default">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo __("write_note_title")?></h1>
        <span class="editor-close btn btn-success"><?php echo __("save") ?></span>
        <span class="xbtn glyphicon glyphicon-remove"></span>
    </div>
    <textarea style="overflow-x: hidden; word-wrap: break-word; overflow-y: visible;" class="textarea textarea_editor"></textarea>
    <div class="other_comments_list"></div>
    <img src="<?php echo template_url("img/loader.gif") ?>" class="commentEditorLoader">
</div>

<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title">
            <div><?php echo __("step_num", [8]). ": " . __("content-review")?></div>
            <div class="action_type type_translation"><?php echo __("type_translation"); ?></div>
        </div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <form action="" method="post" id="main_form">
                <div class="main_content_text row">
                    <?php if($data["event"][0]->checkerID == 0): ?>
                        <div class="alert alert-success check_request"><?php echo __("check_request_sent_success") ?></div>
                    <?php endif; ?>

                    <h4 dir="<?php echo $data["event"][0]->sLangDir ?>"><?php echo $data["event"][0]->tLang." - "
                            .__($data["event"][0]->bookProject)." - "
                            .($data["event"][0]->abbrID <= 39 ? __("old_test") : __("new_test"))." - "
                            ."<span class='book_name'>".$data["event"][0]->name." ".$data["currentChapter"].":1-".$data["totalVerses"]."</span>"?></h4>

                    <div class="col-sm-12">
                        <?php foreach($data["translation"] as $key => $chunk) : ?>
                            <div style="color: #0089ff; font-weight: bold;"><?php echo isset($data["chunks"][$key])
                                    ? ($data["chunks"][$key][0] != $data["chunks"][$key][sizeof($data["chunks"][$key])-1]
                                        ? $data["chunks"][$key][0] . "-" . $data["chunks"][$key][sizeof($data["chunks"][$key])-1]
                                        : $data["chunks"][$key][0])
                                    : ""?></div>

                            <div class="chunk_verse editor_area" dir="<?php echo $data["event"][0]->tLangDir ?>">
                                <div class="vnote">
                                    <?php $text = $chunk[EventMembers::TRANSLATOR]["blind"]; ?>
                                    <textarea name="chunks[]" class="peer_verse_ta textarea"><?php
                                        echo isset($_POST["chunks"]) && isset($_POST["chunks"][$key]) ? $_POST["chunks"][$key] : $text
                                    ?></textarea>

                                    <?php $hasComments = array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($key, $data["comments"][$data["currentChapter"]]); ?>
                                    <div class="comments_number <?php echo $hasComments ? "hasComment" : "" ?>">
                                        <?php echo $hasComments ? sizeof($data["comments"][$data["currentChapter"]][$key]) : ""?>
                                    </div>

                                    <img class="editComment" data="<?php echo $data["currentChapter"].":".$key ?>" width="16" src="<?php echo template_url("img/edit.png") ?>" title="<?php echo __("write_note_title")?>"/>

                                    <div class="comments">
                                        <?php if($hasComments): ?>
                                            <?php foreach($data["comments"][$data["currentChapter"]][$key] as $comment): ?>
                                                <?php if($comment->memberID == $data["event"][0]->myMemberID): ?>
                                                    <div class="my_comment"><?php echo $comment->text; ?></div>
                                                <?php else: ?>
                                                    <div class="other_comments">
                                                        <?php echo
                                                            "<span>".$comment->firstName." ".mb_substr($comment->lastName, 0, 1).". 
                                                                    (L".$comment->level."):</span> 
                                                                ".$comment->text; ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </div>

                            <div class="chunk_divider col-sm-12"></div>
                            <div class="clear"></div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="main_content_footer row">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo __("confirm_yes")?></label>
                    </div>

                    <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo __("next_step")?></button>
                    <img src="<?php echo template_url("img/saving.gif") ?>" class="unsaved_alert">
                </div>
            </form>
            <div class="step_right alt"><?php echo __("step_num", [8])?></div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_float">
                <div class="help_info_steps">
                    <div class="help_hide toggle-help glyphicon glyphicon-eye-close" title="<?php echo __("hide_help") ?>"></div>
                    <div class="help_title_steps"><?php echo __("help") ?></div>

                    <div class="clear"></div>

                    <div class="help_name_steps"><span><?php echo __("step_num", [8])?>: </span> <?php echo __("content-review")?></div>
                    <div class="help_descr_steps">
                        <ul><?php echo __("content-review_desc")?></ul>
                        <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div>
                    </div>
                </div>

                <div class="event_info">
                    <div class="participant_info">
                        <div class="participant_name">
                            <span><?php echo __("your_checker") ?>:</span>
                            <span class="checker_name_span"><?php echo $data["event"][0]->checkerFName !== null ? $data["event"][0]->checkerFName . " " . mb_substr($data["event"][0]->checkerLName, 0, 1)."." : __("not_available") ?></span>
                        </div>
                        <div class="additional_info">
                            <a href="/events/information/<?php echo $data["event"][0]->eventID ?>"><?php echo __("event_info") ?></a>
                        </div>
                    </div>
                </div>

                <?php if($data["event"][0]->sourceBible == "ulb"): ?>
                    <div class="tr_tools">
                        <button class="btn btn-primary ttools" data-tool="tn"><?php echo __("show_notes") ?></button>
                        <button class="btn btn-primary ttools" data-tool="tq"><?php echo __("show_questions") ?></button>
                        <button class="btn btn-primary ttools" data-tool="tw"><?php echo __("show_keywords") ?></button>
                        <button class="btn btn-warning ttools" data-tool="rubric"><?php echo __("show_rubric") ?></button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="help_show toggle-help glyphicon glyphicon-question-sign" title="<?php echo __("show_help") ?>"></div>
</div>

<?php if(!empty($data["notes"])): ?>
<div class="ttools_panel tn_tool panel panel-default" draggable="true">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo __("tn") ?></h1>
        <span class="panel-close glyphicon glyphicon-remove" data-tool="tn"></span>
    </div>

    <div class="ttools_content page-content panel-body">
        <div class="labels_list">
            <?php if(isset($data["notes"])): ?>
                <?php foreach ($data["notes"] as $verse => $notes): ?>
                    <?php $chunkVerses = $data["notesVerses"][$verse]; ?>
                    <label>
                        <ul>
                            <li>
                                <div class="word_term">
                                <span style="font-weight: bold;">
                                    <?php echo $chunkVerses > 0 ? __("verse_number", $chunkVerses) :
                                        __("intro")?>
                                </span>
                                </div>
                                <div class="word_def">
                                    <?php foreach ($notes as $note): ?>
                                        <?php echo  preg_replace('#<a.*?>(.*?)</a>#i', '<b>\1</b>', $note) ?>
                                    <?php endforeach; ?>
                                </div>
                            </li>
                        </ul>
                    </label>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="word_def_popup">
            <div class="word_def-close glyphicon glyphicon-remove"></div>

            <div class="word_def_title"></div>
            <div class="word_def_content"></div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if(!empty($data["questions"])): ?>
<div class="ttools_panel tq_tool panel panel-default" draggable="true">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo __("tq") ?></h1>
        <span class="panel-close glyphicon glyphicon-remove" data-tool="tq"></span>
    </div>

    <div class="ttools_content page-content panel-body">
        <div class="labels_list">
            <?php if(isset($data["questions"])): ?>
                <?php foreach ($data["questions"] as $verse => $questions): ?>
                    <label>
                        <ul>
                            <li>
                                <div class="word_term">
                                    <span style="font-weight: bold;"><?php echo __("verse_number", $verse) ?> </span>
                                </div>
                                <div class="word_def">
                                    <?php foreach ($questions as $question): ?>
                                        <?php echo preg_replace('#<a.*?>(.*?)</a>#i', '<b>\1</b>', $question) ?>
                                    <?php endforeach; ?>
                                </div>
                            </li>
                        </ul>
                    </label>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="word_def_popup">
            <div class="word_def-close glyphicon glyphicon-remove"></div>

            <div class="word_def_title"></div>
            <div class="word_def_content"></div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if(!empty($data["keywords"]) && !empty($data["keywords"]["words"])): ?>
<div class="ttools_panel tw_tool panel panel-default" draggable="true">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo __("tw") ?></h1>
        <span class="panel-close glyphicon glyphicon-remove" data-tool="tw"></span>
    </div>

    <div class="ttools_content page-content panel-body">
        <div class="labels_list">
            <?php if(isset($data["keywords"]) && isset($data["keywords"]["words"])): ?>
                <?php foreach ($data["keywords"]["words"] as $title => $tWord): ?>
                    <?php if(!isset($tWord["text"])) continue; ?>
                    <label>
                        <ul>
                            <li>
                                <div class="word_term">
                                    <span style="font-weight: bold;"><?php echo ucfirst($title) ?> </span>
                                    (<?php echo strtolower(__("verses").": ".join(", ", $tWord["range"])); ?>)
                                </div>
                                <div class="word_def"><?php echo  preg_replace('#<a.*?>(.*?)</a>#i', '<b>\1</b>', $tWord["text"]); ?></div>
                            </li>
                        </ul>
                    </label>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="word_def_popup">
            <div class="word_def-close glyphicon glyphicon-remove"></div>

            <div class="word_def_title"></div>
            <div class="word_def_content"></div>
        </div>
    </div>
</div>
<?php endif; ?>

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
            <img src="<?php echo template_url("img/steps/icons/content-review.pn") ?>g" width="100" height="100">
            <img src="<?php echo template_url("img/steps/big/content-review.png") ?>" width="280" height="280">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="<?php echo $data["event"][0]->step ?>" type="checkbox" value="0" /> <?php echo __("do_not_show_tutorial")?></label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo __("content-review")?></h3>
            <ul><?php echo __("content-review_desc");?></ul>
        </div>
    </div>
</div>