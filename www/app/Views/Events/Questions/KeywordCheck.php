<?php
use \Helpers\Constants\EventMembers;
use \Helpers\Parsedown;

if(isset($data["error"])) return;

$parsedown = new Parsedown();
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
            <?php echo __("step_num", ["step_number" => 1]) . ": " . __("keyword-check")?>
        </div>
    </div>

    <div class="" style="position: relative">
        <div class="main_content">
            <form action="" id="main_form" method="post">
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
                            <div class="flex_middle notes_editor font_<?php echo $data["event"][0]->targetLang ?>"
                                 dir="<?php echo $data["event"][0]->tLangDir ?>"
                                 data-chunkno="<?php echo $chunkNo ?>">
                                <?php
                                $text = isset($data["translation"][$chunkNo]) && isset($data["translation"][$chunkNo][EventMembers::CHECKER])
                                    && !empty($data["translation"][$chunkNo][EventMembers::CHECKER]["verses"])
                                        ? $parsedown->text($data["translation"][$chunkNo][EventMembers::CHECKER]["verses"])
                                        : $parsedown->text($data["translation"][$chunkNo][EventMembers::TRANSLATOR]["verses"]);

                                $text = preg_replace(
                                    "/(\[\[[a-z:\/\-]+\]\])/",
                                    "<span class='uwlink' title='".__("leaveit")."'>$1</span>",
                                    $text);
                                ?>
                                <textarea
                                        name="chunks[<?php echo $chunkNo ?>]"
                                        class="add_notes_editor"><?php echo $text ?></textarea>
                            </div>
                            <div class="flex_right">
                                <?php
                                $hasComments = array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($chunkNo, $data["comments"][$data["currentChapter"]]);
                                ?>
                                <div class="comments_number tncomm flex_commn_number <?php echo $hasComments ? "hasComment" : "" ?>">
                                    <?php echo $hasComments ? sizeof($data["comments"][$data["currentChapter"]][$chunkNo]) : ""?>
                                </div>
                                <span class="editComment mdi mdi-lead-pencil"
                                      data="<?php echo $data["currentChapter"].":".$chunkNo ?>"
                                      title="<?php echo __("write_note_title", [""])?>"></span>

                                <div class="comments">
                                    <?php if(array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($chunkNo, $data["comments"][$data["currentChapter"]])): ?>
                                        <?php foreach($data["comments"][$data["currentChapter"]][$chunkNo] as $comment): ?>
                                            <?php if($comment->memberID == $data["event"][0]->myChkMemberID && $comment->level == 2): ?>
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

            <div class="main_content_footer row">
                <div class="form-group">
                    <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                    <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo __("confirm_yes")?></label>
                </div>

                <input type="hidden" name="chk" value="1">
                <input type="hidden" name="level" value="tqContinue">
                <input type="hidden" name="chapter" value="<?php echo $data["event"][0]->currentChapter ?>">
                <input type="hidden" name="memberID" value="<?php echo $data["event"][0]->memberID ?>">

                <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo __("next_step")?></button>
                <img src="<?php echo template_url("img/saving.gif") ?>" class="unsaved_alert" style="float:none">
            </div>
            </form>
            <div class="step_right alt"><?php echo __("step_num", ["step_number" => 1])?></div>
        </div>
    </div>
</div>

<div class="content_help closed">
    <div id="help_hide" class="glyphicon glyphicon-chevron-down"> <?php echo __("help") ?></div>

    <div class="help_float">
        <div class="help_info_steps is_checker_page_help">
            <div class="help_name_steps">
                <span><?php echo __("step_num", ["step_number" => 1])?>: </span>
                <?php echo __("keyword-check")?>
            </div>
            <div class="help_descr_steps">
                <ul><?php echo __("keyword-check_tq_desc")?></ul>
                <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div>
            </div>
        </div>

        <div class="event_info is_checker_page_help">
            <div class="participant_info">
                <div class="additional_info">
                    <a href="/events/information-tq/<?php echo $data["event"][0]->eventID ?>"><?php echo __("event_info") ?></a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="tutorial_container">
    <div class="tutorial_popup">
        <div class="tutorial-close glyphicon glyphicon-remove"></div>
        <div class="tutorial_pic">
            <img src="<?php echo template_url("img/steps/icons/keyword-check.png") ?>" width="100px" height="100px">
            <img src="<?php echo template_url("img/steps/big/keyword-check.png") ?>" width="280px" height="280px">

        </div>

        <div class="tutorial_content">
            <h3><?php echo __("keyword-check")?></h3>
            <ul><?php echo __("keyword-check_tq_desc")?></ul>
        </div>
    </div>
</div>