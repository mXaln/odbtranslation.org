<?php
/**
 * Created by PhpStorm.
 * User: Maxim
 * Date: 12 Apr 2016
 * Time: 17:30
 */
use Helpers\Constants\EventSteps;
use Helpers\Tools;
use Helpers\Constants\EventMembers;

if(empty($error) && empty($data["success"])):
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
            <div><?php echo __("step_num", [7]). ": " . __("keyword-check")?></div>
            <div class="action_type type_checking"><?php echo __("type_checking"); ?></div>
        </div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <div class="main_content_text row" dir="<?php echo $data["event"][0]->sLangDir ?>">
                <h4><?php echo $data["event"][0]->tLang." - "
                        .__($data["event"][0]->bookProject)." - "
                        .($data["event"][0]->abbrID <= 39 ? __("old_test") : __("new_test"))." - "
                        ."<span class='book_name'>".$data["event"][0]->bookName." ".$data["currentChapter"].":1-".$data["totalVerses"]."</span>"?></h4>

                <div class="col-sm-12 one_side_content">
                    <?php foreach($data["chunks"] as $key => $chunk) : ?>
                        <div class="chunk_block">
                            <div style="padding-right: 15px" class="chunk_verses" >
                                <?php $firstVerse = 0; ?>
                                <?php foreach ($chunk as $verse): ?>
                                    <?php
                                    // process combined verses
                                    if (!isset($data["text"][$verse]))
                                    {
                                        if($firstVerse == 0)
                                        {
                                            $firstVerse = $verse;
                                            continue;
                                        }
                                        $combinedVerse = $firstVerse . "-" . $verse;

                                        if(!isset($data["text"][$combinedVerse]))
                                            continue;
                                        $verse = $combinedVerse;
                                    }
                                    ?>
                                    <strong dir="<?php echo $data["event"][0]->sLangDir ?>" 
                                        class="<?php echo $data["event"][0]->sLangDir ?>">
                                        <sup><?php echo $verse; ?></sup>
                                    </strong>
                                    <div class="<?php echo "kwverse_".$data["currentChapter"]."_".$key."_".$verse ?>" 
                                        dir="<?php echo $data["event"][0]->sLangDir ?>">
                                        <?php echo $data["text"][$verse]; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="vnote">
                                <?php $hasComments = array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($key, $data["comments"][$data["currentChapter"]]); ?>
                                <div class="comments_number <?php echo $hasComments ? "hasComment" : "" ?>">
                                    <?php echo $hasComments ? sizeof($data["comments"][$data["currentChapter"]][$key]) : ""?>
                                </div>
                                <img class="editComment" data="<?php echo $data["currentChapter"].":".$key ?>" width="16" src="<?php echo template_url("img/edit.png") ?>" title="<?php echo __("write_note_title", [""])?>"/>

                                <div class="comments">
                                    <?php if(array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($key, $data["comments"][$data["currentChapter"]])): ?>
                                        <?php foreach($data["comments"][$data["currentChapter"]][$key] as $comment): ?>
                                            <?php if($comment->memberID == $data["event"][0]->checkerID): ?>
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
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="main_content_footer row">
                <form action="" method="post" id="checker_submit">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo __("confirm_yes")?></label>
                    </div>

                    <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo __("continue")?></button>
                </form>
                <div class="step_right chk"><?php echo __("step_num", [7])?></div>
            </div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_float">
                <div class="help_info_steps <?php echo $data["isCheckerPage"] ? " is_checker_page_help" : "" ?>">
                    <div class="help_hide toggle-help glyphicon glyphicon-eye-close" title="<?php echo __("hide_help") ?>"></div>
                    <div class="help_title_steps"><?php echo __("help") ?></div>

                    <div class="clear"></div>

                    <div class="help_name_steps"><span><?php echo __("step_num", [7])?>: </span> <?php echo __("keyword-check")?></div>
                    <div class="help_descr_steps">
                        <ul><?php echo __("keyword-check_checker_desc")?></ul>
                        <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div>
                    </div>
                </div>

                <div class="event_info <?php echo $data["isCheckerPage"] ? " is_checker_page_help" : "" ?>">
                    <div class="participant_info">
                        <div class="participant_name">
                            <span><?php echo __("your_translator") ?>:</span>
                            <span><?php echo $data["event"][0]->firstName . " " . mb_substr($data["event"][0]->lastName, 0, 1)."." ?></span>
                        </div>
                        <div class="additional_info">
                            <a href="/events/information/<?php echo $data["event"][0]->eventID ?>"><?php echo __("event_info") ?></a>
                        </div>
                    </div>
                </div>

                <div class="tr_tools">
                    <button class="btn btn-primary ttools" data-tool="tw"><?php echo __("show_keywords") ?></button>
                    <button class="btn btn-warning ttools" data-tool="rubric"><?php echo __("show_rubric") ?></button>
                </div>
            </div>
        </div>
    </div>

    <div class="help_show toggle-help glyphicon glyphicon-question-sign" title="<?php echo __("show_help") ?>"></div>
</div>

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
            <img src="<?php echo template_url("img/steps/icons/keyword-check.png") ?>" width="100" height="100">
            <img src="<?php echo template_url("img/steps/big/keyword-check.png") ?>" width="280" height="280">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="<?php echo $data["event"][0]->step."_checker" ?>" data2="checker" type="checkbox" value="0" /> <?php echo __("do_not_show_tutorial")?></label>
            </div>
        </div>

        <div class="tutorial_content <?php echo $data["isCheckerPage"] ? " is_checker_page_help" : "" ?>">
            <h3><?php echo __("keyword-check")?></h3>
            <ul><?php echo __("keyword-check_checker_desc")?></ul>
        </div>
    </div>
</div>

<script>
    isChecker = true;

    $(document).ready(function() {
        $("#next_step").click(function (e) {
            renderConfirmPopup(Language.checkerConfirmTitle, Language.checkerConfirm,
                function () {
                    $("#checker_submit").submit();
                },
                function () {
                    $("#confirm_step").prop("checked", false);
                    $("#next_step").prop("disabled", true);
                    $( this ).dialog("close");
                },
                function () {
                    $("#confirm_step").prop("checked", false);
                    $("#next_step").prop("disabled", true);
                    $( this ).dialog("close");
                });

            e.preventDefault();
        });
    });
</script>
<?php endif; ?>