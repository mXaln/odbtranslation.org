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
    <textarea dir="<?php echo $data["event"][0]->sLangDir ?>" style="overflow-x: hidden; word-wrap: break-word; overflow-y: visible;" class="textarea textarea_editor"></textarea>
    <div class="other_comments_list"></div>
    <img src="<?php echo template_url("img/loader.gif") ?>" class="commentEditorLoader">
</div>


<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title">
            <div><?php echo __("step_num", ["step_number" => 5]). ": " . __("peer-review_tn")?></div>
            <div class="action_type type_checking <?php echo isset($data["isPeerPage"]) ? "isPeer" : "" ?>">
            <?php echo __("type_checking"); ?></div>
        </div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <div class="main_content_text">
                <h4><?php echo $data["event"][0]->tLang." - "
                        .__($data["event"][0]->bookProject)." - "
                        .($data["event"][0]->abbrID <= 39 ? __("old_test") : __("new_test"))." - "
                        ."<span class='book_name'>".$data["event"][0]->name." ".
                        (!$data["nosource"]
                            ? $data["currentChapter"].":1-".$data["totalVerses"]
                            : __("front"))."</span>"?></h4>

                <div id="my_notes_content" class="my_content">
                    <?php foreach($data["chunks"] as $chunkNo => $chunk): $fv = $chunk[0]; ?>
                    <div class="row note_chunk">
                        <div class="row scripture_chunk" dir="<?php echo $data["event"][0]->sLangDir ?>">
                            <?php if(!$data["nosource"] && isset($data["text"][$fv])): ?>
                                <?php foreach(array_values($chunk) as $verse): ?>
                                    <div class="chunk_verses">
                                        <strong><sup><?php echo $verse ?></sup></strong>
                                        <div class="<?php echo "kwverse_".$data["currentChapter"]."_".$chunkNo."_".$verse ?>">
                                            <?php echo isset($data["text"][$verse]) ? $data["text"][$verse] : ""; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="compare_notes">
                            <label>
                                <?php echo __("compare"); ?>
                                <input type="checkbox" checked data-toggle="toggle"
                                       data-on="<?php echo __("on") ?>"
                                       data-off="<?php echo __("off") ?>">
                            </label>
                        </div>
                        <div class="col-md-6" dir="<?php echo $data["event"][0]->resLangDir ?>">
                            <?php foreach(array_values($chunk) as $verse): ?>
                                <div class="note_content">
                                    <?php if (isset($data["notes"][$verse])): ?>
                                        <?php foreach ($data["notes"][$verse] as $note): ?>
                                            <?php echo $note ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="col-md-6 vnote font_<?php echo $data["event"][0]->targetLang ?>"
                             dir="<?php echo $data["event"][0]->tLangDir ?>">
                            <?php 
                            $parsedown = new Parsedown();
                            $text = isset($data["translation"][$chunkNo]) 
                                ? $parsedown->text($data["translation"][$chunkNo][EventMembers::CHECKER]["verses"])
                                : "";
                            $text = preg_replace('/( title=".*")/', '', $text);
                            ?>
                            <div class="notes_target"><?php echo $text ?></div>
                            <div class="notes_target_compare"></div>

                            <?php $hasComments = array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($chunkNo, $data["comments"][$data["currentChapter"]]); ?>
                            <div class="comments_number tncommpeer <?php echo $hasComments ? "hasComment" : "" ?>">
                                <?php echo $hasComments ? sizeof($data["comments"][$data["currentChapter"]][$chunkNo]) : ""?>
                            </div>
                            <img class="editComment tncommpeer" data="<?php echo $data["currentChapter"].":".$chunkNo ?>" width="16" src="<?php echo template_url("img/edit.png") ?>" title="<?php echo __("write_note_title", [""])?>"/>

                            <div class="comments">
                                <?php if(array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($chunkNo, $data["comments"][$data["currentChapter"]])): ?>
                                    <?php foreach($data["comments"][$data["currentChapter"]][$chunkNo] as $comment): ?>
                                        <?php if($comment->memberID == Session::get("memberID") && $comment->level == 2): ?>
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
                        <div class="notes_translator">
                            <?php 
                            $text = isset($data["translation"][$chunkNo])
                                ? $parsedown->text($data["translation"][$chunkNo][EventMembers::TRANSLATOR]["verses"])
                                : "";
                            echo preg_replace('/( title=".*")/', '', $text);
                            ?>
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
                <div class="step_right chk"><?php echo __("step_num", ["step_number" => 5])?></div>
            </div>
            <?php //endif; ?>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_float">
                <div class="help_info_steps
                    <?php echo $data["isCheckerPage"] ? " is_checker_page_help".
                        (isset($data["isPeerPage"]) ? " isPeer" : "") : "" ?>">
                    <div class="help_hide toggle-help glyphicon glyphicon-eye-close" title="<?php echo __("hide_help") ?>"></div>
                    <div class="help_title_steps"><?php echo __("help") ?></div>

                    <div class="clear"></div>

                    <div class="help_name_steps"><span><?php echo __("step_num", ["step_number" => 5])?>: </span> <?php echo __("peer-review_tn")?></div>
                    <div class="help_descr_steps">
                        <ul><?php echo __("peer-review_tn_chk_desc")?></ul>
                        <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div>
                    </div>
                </div>

                <div class="event_info <?php echo $data["isCheckerPage"] ? " is_checker_page_help".
                    (isset($data["isPeerPage"]) ? " isPeer" : "") : "" ?>">
                    <div class="participant_info">
                        <div class="participant_name">
                            <span><?php echo __("your_partner") ?>:</span>
                            <span><?php echo $data["event"][0]->checkerFName . " " . mb_substr($data["event"][0]->checkerLName, 0, 1)."." ?></span>
                        </div>
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
            <img src="<?php echo template_url("img/steps/icons/peer-review.png") ?>" width="100" height="100">
            <img src="<?php echo template_url("img/steps/big/peer-review.png") ?>" width="280" height="280">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="<?php echo "peer-review_checker" ?>" data2="checker" type="checkbox" value="0" /> <?php echo __("do_not_show_tutorial")?></label>
            </div>
        </div>

        <div class="tutorial_content<?php echo $data["isCheckerPage"] ? " is_checker_page_help" .
            (isset($data["isPeerPage"]) ? " isPeer" : ""): "" ?>">
            <h3><?php echo __("peer-review_tn")?></h3>
            <ul><?php echo __("peer-review_tn_chk_desc")?></ul>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?php echo template_url("js/diff_match_patch.js?2")?>"></script>
<script type="text/javascript" src="<?php echo template_url("js/diff.js?7")?>"></script>
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

        $(".note_chunk").each(function(i, v) {
            var elm1 = $(".notes_translator", this).html();
            var elm2 = $(".notes_target", this).html();
            var out = $(".notes_target_compare", this);

            if(typeof elm1 == "undefined") return true;

            diff_plain(htmlToText(elm1), htmlToText(elm2), out);
        });

        $(".compare_notes input").change(function () {
            var parent = $(this).parents(".note_chunk");
            var active = $(this).prop('checked');

            if (active) {
                $(".notes_target", parent).hide();
                $(".notes_target_compare", parent).show();
            } else {
                $(".notes_target_compare", parent).hide();
                $(".notes_target", parent).show();
            }
        });
    });

    
</script>
<?php endif; ?>