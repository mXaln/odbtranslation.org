<?php
use \Helpers\Constants\EventMembers;
use \Helpers\Parsedown;

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
        <div class="main_content_title"><?php echo __("step_num", [3]) . ": " . __("self-check_tn_chk")?></div>
    </div>

    <div class="row" style="position: relative">
        <div class="main_content col-sm-9">
            <form action="" id="main_form" method="post">
            <div class="main_content_text" dir="<?php echo $data["event"][0]->sLangDir ?>">
            
            <h4><?php echo $data["event"][0]->tLang." - "
                        .__($data["event"][0]->bookProject)." - "
                    .($data["event"][0]->abbrID <= 39 ? __("old_test") : __("new_test"))." - "
                    ."<span class='book_name'>".$data["event"][0]->name." ".
                    (!$data["nosource"] 
                        ? $data["currentChapter"].":1-".$data["totalVerses"]
                        : __("front"))."</span>"?></h4>

                <?php if(!$data["nosource"]): ?>
                <ul class="nav nav-tabs">
                    <li role="presentation" id="my_scripture" class="my_tab">
                        <a href="#"><?php echo __("bible_mode") ?></a>
                    </li>
                    <li role="presentation" id="my_notes" class="my_tab">
                        <a href="#"><?php echo __("notes_mode") ?></a>
                    </li>
                </ul>

                <div id="my_scripture_content" class="my_content shown">
                    <?php foreach($data["text"] as $chunk => $content): ?>
                        <div class="note_chunk">
                            <?php foreach($content as $verse => $text): ?>
                                <p>
                                    <strong><sup><?php echo $verse ?></sup></strong>    
                                    <?php echo $text; ?>
                                </p>
                            <?php endforeach; ?>
                        </div>    
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div id="my_notes_content" class="my_content">
                    <?php foreach($data["chunks"] as $chunkNo => $chunk): $fv = $chunk[0]; ?>
                    <div class="row note_chunk">
                        <div class="col-md-6">
                            <div class="note_chunk_verses">
                                <?php 
                                if(!$data["nosource"] && isset($data["text"][$fv]))
                                {
                                    $verses = array_keys($data["text"][$fv]);
                                    if($verses[0] != $verses[sizeof($verses)-1])
                                        echo __("chunk_verses", $verses[0] . "-" . $verses[sizeof($verses)-1]);
                                    else
                                        echo __("chunk_verses", $verses[0]);
                                }
                                else 
                                {
                                    echo __("intro");
                                }
                                ?>
                            </div>
                            <?php foreach($data["notes"][$fv] as $note): ?>
                                <div class="note_content">
                                    <?php echo preg_replace(
                                        "/(\[\[[a-z:\/\-]+\]\])/", 
                                        "<span class='uwlink' title='".__("leaveit")."'>$1</span>", 
                                        $note) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="col-md-6 notes_editor" 
                            data-chunkno="<?php echo $chunkNo ?>">
                            <?php 
                            $parsedown = new Parsedown();
                            $text = isset($data["translation"][$chunkNo]) && isset($data["translation"][$chunkNo][EventMembers::CHECKER])
                                && !empty($data["translation"][$chunkNo][EventMembers::CHECKER]["verses"])
                                ? $parsedown->text($data["translation"][$chunkNo][EventMembers::CHECKER]["verses"])
                                : $parsedown->text($data["translation"][$chunkNo][EventMembers::TRANSLATOR]["verses"]);
                            $text = isset($_POST["chunks"]) && isset($_POST["chunks"][$chunkNo]) 
                                ? $_POST["chunks"][$chunkNo] 
                                : $text;
                            $text = preg_replace(
                                "/(\[\[[a-z:\/\-]+\]\])/", 
                                "<span class='uwlink' title='".__("leaveit")."'>$1</span>", 
                                $text);
                            ?>
                            <textarea 
                                name="chunks[<?php echo $chunkNo ?>]" 
                                class="add_notes_editor"><?php echo $text ?></textarea>
                        
                            <?php 
                            $hasComments = array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($chunkNo, $data["comments"][$data["currentChapter"]]); 
                            ?>
                            <div class="comments_number tncomm <?php echo $hasComments ? "hasComment" : "" ?>">
                                <?php echo $hasComments ? sizeof($data["comments"][$data["currentChapter"]][$chunkNo]) : ""?>
                            </div>
                            <img class="editComment tncomm" data="<?php echo $data["currentChapter"].":".$chunkNo ?>" width="16" src="<?php echo template_url("img/edit.png") ?>" title="<?php echo __("write_note_title", [""])?>"/>

                            <div class="comments">
                                <?php if(array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($chunkNo, $data["comments"][$data["currentChapter"]])): ?>
                                    <?php foreach($data["comments"][$data["currentChapter"]][$chunkNo] as $comment): ?>
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
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="main_content_footer row">
                <div class="form-group">
                    <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                    <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo __("confirm_yes")?></label>
                </div>
                <input type="hidden" name="chk" value="1">
                <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo __("next_step")?></button>
                <img src="<?php echo template_url("img/alert.png") ?>" class="unsaved_alert" style="float:none">
            </div>
            </form>
            <div class="step_right alt"><?php echo __("step_num", [3])?></div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps<?php echo $data["isCheckerPage"] ? " is_checker_page_help" : "" ?>">
                <div class="help_hide toggle-help glyphicon glyphicon-eye-close" title="<?php echo __("hide_help") ?>"></div>
                <div class="help_title_steps"><?php echo __("help") ?></div>

                <div class="clear"></div>

                <div class="help_name_steps">
                    <span><?php echo __("step_num", [3])?>: </span>
                    <?php echo __("self-check_tn_chk")?>
                </div>
                <div class="help_descr_steps">
                    <ul><?php echo __("self-check_tn_chk_desc")?></ul>
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

    <div class="help_show toggle-help glyphicon glyphicon-question-sign" title="<?php echo __("show_help") ?>"></div>
</div>

<div class="tutorial_container">
    <div class="tutorial_popup">
        <div class="tutorial-close glyphicon glyphicon-remove"></div>
        <div class="tutorial_pic">
            <img src="<?php echo template_url("img/steps/icons/self-check.png") ?>" width="100px" height="100px">
            <img src="<?php echo template_url("img/steps/big/self-check.png") ?>" width="280px" height="280px">
            <div class="hide_tutorial">
                <label>
                    <input id="hide_tutorial" 
                        data="<?php echo $data["event"][0]->step ?>" 
                        type="checkbox" value="0" /> 
                            <?php echo __("do_not_show_tutorial")?>
                </label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo __("self-check_tn_chk")?></h3>
            <ul><?php echo __("self-check_tn_chk_desc")?></ul>
        </div>
    </div>
</div>