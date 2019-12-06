<?php
/**
 * Created by PhpStorm.
 * User: Maxim
 * Date: 12 Apr 2016
 * Time: 17:30
 */
use Helpers\Constants\EventMembers;

if(empty($error) && empty($data["success"])):
?>

<div class="comment_div panel panel-default">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo __("write_note_title")?></h1>
        <span class="editor-close btn btn-success"><?php echo __("save") ?></span>
        <span class="xbtn glyphicon glyphicon-remove"></span>
    </div>
    <textarea dir="<?php echo $data["event"][0]->sLangDir ?>" style="overflow-x: hidden; word-wrap: break-word; overflow-y: visible;" class="textarea textarea_editor"></textarea>
    <div class="other_comments_list"></div>
    <img src="<?php echo template_url("img/loader.gif") ?>" class="commentEditorLoader">
</div>


<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title">
            <div><?php echo __("step_num", ["step_number" => 8]). ": " . __("content-review")?></div>
            <div class="action_type type_checking"><?php echo __("type_checking"); ?></div>
        </div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <div class="main_content_text row">
                <h4 dir="<?php echo $data["event"][0]->sLangDir ?>"><?php echo $data["event"][0]->tLang." - "
                        .__($data["event"][0]->bookProject)." - "
                        .($data["event"][0]->abbrID <= 39 ? __("old_test") : __("new_test"))." - "
                        ."<span class='book_name'>".$data["event"][0]->bookName." ".$data["currentChapter"].":1-".$data["totalVerses"]."</span>"?></h4>

                <div class="row">
                    <div class="col-sm-12 side_by_side_toggle">
                        <label><input type="checkbox" id="side_by_side_toggle" value="0" /> <?php echo __("side_by_side_toggle") ?></label>
                    </div>
                </div>

                <div class="col-sm-12 side_by_side_content">
                    <?php foreach($data["chunks"] as $key => $chunk) : ?>
                        <div class="row chunk_block">
                            <div class="flex_container">
                                <div class="chunk_verses flex_left" dir="<?php echo $data["event"][0]->sLangDir ?>">
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
                                        <strong dir="<?php echo $data["event"][0]->sLangDir ?>" class="<?php echo $data["event"][0]->sLangDir ?>"><sup><?php echo $verse; ?></sup></strong><div class="<?php echo "kwverse_".$data["currentChapter"]."_".$key."_".$verse ?>" dir="<?php echo $data["event"][0]->sLangDir ?>"><?php echo $data["text"][$verse]; ?></div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="flex_middle editor_area" style="padding: 0;" dir="<?php echo $data["event"][0]->tLangDir ?>">
                                    <?php $text = $data["translation"][$key][EventMembers::TRANSLATOR]["blind"]; ?>
                                    <div class="vnote">
                                        <div><?php echo $text; ?></div>
                                    </div>
                                </div>
                                <div class="flex_right">
                                    <?php $hasComments = array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($key, $data["comments"][$data["currentChapter"]]); ?>
                                    <div class="comments_number flex_commn_number <?php echo $hasComments ? "hasComment" : "" ?>">
                                        <?php echo $hasComments ? sizeof($data["comments"][$data["currentChapter"]][$key]) : ""?>
                                    </div>

                                    <span class="editComment mdi mdi-lead-pencil"
                                          data="<?php echo $data["currentChapter"].":".$key ?>"
                                          title="<?php echo __("write_note_title", [""])?>"></span>

                                    <div class="comments">
                                        <?php if(array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($key, $data["comments"][$data["currentChapter"]])): ?>
                                            <?php foreach($data["comments"][$data["currentChapter"]][$key] as $comment): ?>
                                                <?php if($comment->memberID == $data["event"][0]->checkerID): ?>
                                                    <div class="my_comment"><?php echo $comment->text; ?></div>
                                                <?php else: ?>
                                                    <div class="other_comments">
                                                        <?php echo
                                                            "<span>".$comment->firstName." ".mb_substr($comment->lastName, 0, 1).". 
                                                                    - L".$comment->level.":</span> 
                                                                ".$comment->text; ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="chunk_divider"></div>
                    <?php endforeach; ?>
                </div>

                <div class="col-sm-12 one_side_content" dir="<?php echo $data["event"][0]->sLangDir ?>">
                    <?php foreach($data["chunks"] as $key => $chunk) : ?>
                        <div class="chunk_block">
                            <div class="flex_container">
                                <div class="chunk_verses flex_middle">
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
                                        <strong dir="<?php echo $data["event"][0]->sLangDir ?>" class="<?php echo $data["event"][0]->sLangDir ?>"><sup><?php echo $verse; ?></sup></strong><div class="<?php echo "kwverse_".$data["currentChapter"]."_".$key."_".$verse ?>" dir="<?php echo $data["event"][0]->sLangDir ?>"><?php echo $data["text"][$verse]; ?></div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="vnote flex_right">
                                    <?php $hasComments = array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($key, $data["comments"][$data["currentChapter"]]); ?>
                                    <div class="comments_number flex_commn_number <?php echo $hasComments ? "hasComment" : "" ?>">
                                        <?php echo $hasComments ? sizeof($data["comments"][$data["currentChapter"]][$key]) : ""?>
                                    </div>

                                    <span class="editComment mdi mdi-lead-pencil"
                                          data="<?php echo $data["currentChapter"].":".$key ?>"
                                          title="<?php echo __("write_note_title", [""])?>"></span>

                                    <div class="comments">
                                        <?php if(array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($key, $data["comments"][$data["currentChapter"]])): ?>
                                            <?php foreach($data["comments"][$data["currentChapter"]][$key] as $comment): ?>
                                                <?php if($comment->memberID == $data["event"][0]->checkerID): ?>
                                                    <div class="my_comment"><?php echo $comment->text; ?></div>
                                                <?php else: ?>
                                                    <div class="other_comments">
                                                        <?php echo
                                                            "<span>".$comment->firstName." ".mb_substr($comment->lastName, 0, 1).". 
                                                                    - L".$comment->level.":</span> 
                                                                ".$comment->text; ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="chunk_divider"></div>
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
                <div class="step_right chk"><?php echo __("step_num", ["step_number" => 8])?></div>
            </div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_float">
                <div class="help_info_steps <?php echo $data["isCheckerPage"] ? " is_checker_page_help" : "" ?>">
                    <div class="help_hide toggle-help glyphicon glyphicon-eye-close" title="<?php echo __("hide_help") ?>"></div>
                    <div class="help_title_steps"><?php echo __("help") ?></div>

                    <div class="clear"></div>

                    <div class="help_name_steps"><span><?php echo __("step_num", ["step_number" => 8])?>: </span> <?php echo __("content-review")?></div>
                    <div class="help_descr_steps">
                        <ul><?php echo __("content-review_checker_desc")?></ul>
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
                    <button class="btn btn-primary ttools" data-tool="tn"><?php echo __("show_notes") ?></button>
                    <button class="btn btn-primary ttools" data-tool="tq"><?php echo __("show_questions") ?></button>
                    <button class="btn btn-primary ttools" data-tool="tw"><?php echo __("show_keywords") ?></button>
                    <button class="btn btn-warning ttools" data-tool="rubric"><?php echo __("show_rubric") ?></button>
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
            <img src="<?php echo template_url("img/steps/icons/content-review.png") ?>" width="100" height="100">
            <img src="<?php echo template_url("img/steps/big/content-review.png") ?>" width="280" height="280">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="<?php echo "content-review_checker" ?>" data2="checker" type="checkbox" value="0" /> <?php echo __("do_not_show_tutorial")?></label>
            </div>
        </div>

        <div class="tutorial_content <?php echo $data["isCheckerPage"] ? " is_checker_page_help" : "" ?>">
            <h3><?php echo __("content-review")?></h3>
            <ul><?php echo __("content-review_checker_desc")?></ul>
        </div>
    </div>
</div>

<!-- Data for tools -->
<input type="hidden" id="bookCode" value="<?php echo $data["event"][0]->bookCode ?>">
<input type="hidden" id="chapter" value="<?php echo $data["event"][0]->currentChapter ?>">
<input type="hidden" id="tn_lang" value="<?php echo $data["event"][0]->tnLangID ?>">
<input type="hidden" id="tq_lang" value="<?php echo $data["event"][0]->tqLangID ?>">
<input type="hidden" id="tw_lang" value="<?php echo $data["event"][0]->twLangID ?>">
<input type="hidden" id="totalVerses" value="<?php echo $data["totalVerses"] ?>">
<input type="hidden" id="targetLang" value="<?php echo $data["event"][0]->targetLang ?>">

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