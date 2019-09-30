<?php
/**
 * Created by PhpStorm.
 * User: Maxim
 * Date: 12 Apr 2016
 * Time: 17:30
 */
use Helpers\Constants\EventMembers;
use Helpers\Session;
use Helpers\Parsedown;

if(empty($error) && empty($data["success"])):
?>

<div class="comment_div panel panel-default font_<?php echo $data["event"][0]->targetLang ?>"
     dir="<?php echo $data["event"][0]->tLangDir ?>">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo __("write_note_title")?></h1>
        <span class="editor-close btn btn-success" data-level="2"><?php echo __("save") ?></span>
        <span class="xbtn glyphicon glyphicon-remove"></span>
    </div>
    <textarea style="overflow-x: hidden; word-wrap: break-word; overflow-y: visible;" class="textarea textarea_editor"></textarea>
    <div class="other_comments_list"></div>
    <img src="<?php echo template_url("img/loader.gif") ?>" class="commentEditorLoader">
</div>


<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title">
            <div><?php echo __("step_num", ["step_number" => 2]). ": " . __("peer-review_tq")?></div>
        </div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <div class="main_content_text">
                <h4><?php echo $data["event"][0]->tLang." - "
                        .__($data["event"][0]->bookProject)." - "
                        .($data["event"][0]->abbrID <= 39 ? __("old_test") : __("new_test"))." - "
                        ."<span class='book_name'>".$data["event"][0]->name." "
                        .$data["currentChapter"]."</span>"?></h4>

                <div id="my_notes_content" class="my_content">
                    <?php foreach($data["chunks"] as $chunkNo => $chunk): $verse = $chunk[0]; ?>
                    <div class="row note_chunk">
                        <div class="row">
                            <div class="col-md-4" style="color: #00a74d; font-weight: bold;">
                                <?php echo __("verse_number", $verse) ?>
                            </div>
                        </div>
                        <div class="flex_container">
                            <div class="flex_left" dir="<?php echo $data["event"][0]->resLangDir ?>">
                                <div class="note_content">
                                    <?php $lv = isset($data["chunks"][$chunkNo+1]) ? $data["chunks"][$chunkNo+1][0] : $data["totalVerses"]+1; ?>
                                    <?php for ($i=$verse; $i<$lv; $i++): ?><?php if (isset($data["questions"][$i])): ?>
                                        <?php foreach ($data["questions"][$i] as $question): ?>
                                            <?php echo $question ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="flex_middle vnote font_<?php echo $data["event"][0]->targetLang ?>"
                                 dir="<?php echo $data["event"][0]->tLangDir ?>">
                                <?php
                                $parsedown = new Parsedown();
                                $text = isset($data["translation"][$chunkNo])
                                    ? $parsedown->text($data["translation"][$chunkNo][EventMembers::CHECKER]["verses"])
                                    : "";
                                ?>
                                <div class="questions_target"><?php echo $text ?></div>
                            </div>
                            <div class="flex_right">
                                <?php $hasComments = array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($chunkNo, $data["comments"][$data["currentChapter"]]); ?>
                                <div class="comments_number tncommpeer flex_commn_number <?php echo $hasComments ? "hasComment" : "" ?>">
                                    <?php echo $hasComments ? sizeof($data["comments"][$data["currentChapter"]][$chunkNo]) : ""?>
                                </div>
                                <img class="editComment tncommpeer flex_commn_img" data="<?php echo $data["currentChapter"].":".$chunkNo ?>" width="16" src="<?php echo template_url("img/edit.png") ?>" title="<?php echo __("write_note_title", [""])?>"/>

                                <div class="comments">
                                    <?php if(array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($chunkNo, $data["comments"][$data["currentChapter"]])): ?>
                                        <?php foreach($data["comments"][$data["currentChapter"]][$chunkNo] as $comment): ?>
                                            <?php if($comment->memberID == Session::get("memberID") && $comment->level == 2): ?>
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
                    <?php endforeach; ?>
                </div>
            </div>

            <?php //if(empty($error)):?>
            <div class="main_content_footer row">
                <form action="" method="post" id="checker_submit">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo __("confirm_yes")?></label>
                    </div>

                    <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo __("continue")?></button>
                </form>
                <div class="step_right chk"><?php echo __("step_num", ["step_number" => 2])?></div>
            </div>
            <?php //endif; ?>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_float">
                <div class="help_info_steps is_checker_page_help isPeer">
                    <div class="help_hide toggle-help glyphicon glyphicon-eye-close" title="<?php echo __("hide_help") ?>"></div>
                    <div class="help_title_steps"><?php echo __("help") ?></div>

                    <div class="clear"></div>

                    <div class="help_name_steps">
                        <span><?php echo __("step_num", ["step_number" => 2])?>: </span>
                        <?php echo __("peer-review_tq")?>
                    </div>
                    <div class="help_descr_steps">
                        <ul><?php echo __("peer-review_tq_chk_desc")?></ul>
                        <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div>
                    </div>
                </div>

                <div class="event_info is_checker_page_help isPeer">
                    <div class="participant_info">
                        <div class="participant_name">
                            <span><?php echo __("your_partner") ?>:</span>
                            <span><?php echo $data["event"][0]->checkerFName . " " . mb_substr($data["event"][0]->checkerLName, 0, 1)."." ?></span>
                        </div>
                        <div class="additional_info">
                            <a href="/events/information-tq/<?php echo $data["event"][0]->eventID ?>"><?php echo __("event_info") ?></a>
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
            <img src="<?php echo template_url("img/steps/icons/peer-review.png") ?>" width="100" height="100">
            <img src="<?php echo template_url("img/steps/big/peer-review.png") ?>" width="280" height="280">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="<?php echo "keyword-check_checker" ?>" data2="checker" type="checkbox" value="0" /> <?php echo __("do_not_show_tutorial")?></label>
            </div>
        </div>

        <div class="tutorial_content is_checker_page_help">
            <h3><?php echo __("peer-review_tq")?></h3>
            <ul><?php echo __("peer-review_tq_chk_desc")?></ul>
        </div>
    </div>
</div>

<script>
    var isChecker = true;
    var disableHighlight = true;

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