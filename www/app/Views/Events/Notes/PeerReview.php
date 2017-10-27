<?php
use \Helpers\Constants\EventMembers;
use \Helpers\Parsedown;

if(isset($data["error"])) return;
?>
<div class="editor">
    <div class="comment_div panel panel-default" dir="<?php echo $data["event"][0]->tLangDir ?>">
        <div class="panel-heading">
            <h1 class="panel-title"><?php echo __("write_note_title")?></h1>
            <span class="editor-close glyphicon glyphicon-floppy-disk"></span>
        </div>
        <textarea class="textarea textarea_editor"></textarea>
        <div class="other_comments_list <?php echo $data["event"][0]->tLangDir?>"></div>
        <img src="<?php echo template_url("img/loader.gif") ?>" class="commentEditorLoader">
    </div>
</div>

<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo __("step_num", [5]) . ": " . __("peer-review_tn")?></div>
    </div>

    <div class="row" style="position: relative">
        <button class="btn btn-warning toggle-help"><?php echo __("hide_help") ?></button>
        <div class="main_content col-sm-9">
            <form action="" id="main_form" method="post">
            <div class="main_content_text" dir="<?php echo $data["event"][0]->sLangDir ?>">
            
                <?php if($data["event"][0]->checkerID == 0): ?>
                    <div class="alert alert-success check_request"><?php echo __("check_request_sent_success") ?></div>
                <?php endif; ?>

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
                    <?php $key = 0; foreach($data["text"] as $chunk => $content): ?>
                        <div class="note_chunk chunk_verses">
                            <?php foreach($content as $verse => $text): ?>
                            <strong><sup><?php echo $verse; ?></sup></strong>
                            <div class="<?php echo "kwverse_".$data["currentChapter"]."_".$key."_".$verse ?>">
                                <?php echo $text; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>    
                    <?php $key++; endforeach; ?>
                </div>
                <?php endif; ?>

                <div id="my_notes_content" class="my_content">
                    <?php $chunkNo = 0; foreach($data["notes"] as $fv => $chunk): ?>
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
                            <?php foreach($chunk as $note): ?>
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
                            $text = isset($data["translation"][$chunkNo]) 
                                ? $parsedown->text($data["translation"][$chunkNo][EventMembers::CHECKER]["verses"])
                                : "";
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
                                            <div class="other_comments"><?php echo "<span>".$comment->userName.":</span> ".$comment->text; ?></div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                <?php $chunkNo++; endforeach; ?>
                </div>
            </div>

            <div class="main_content_footer row">
                <div class="form-group">
                    <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                    <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo __("confirm_yes")?></label>
                </div>
                <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo __("next_step")?></button>
                <img src="<?php echo template_url("img/alert.png") ?>" class="unsaved_alert" style="float:none">
            </div>
            </form>
            <div class="step_right alt"><?php echo __("step_num", [5])?></div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps">
                <div class="help_title_steps"><?php echo __("help") ?></div>

                <div class="clear"></div>

                <div class="help_name_steps">
                    <span><?php echo __("step_num", [5])?>: </span>
                    <?php echo __("peer-review_tn_chk")?>
                </div>
                <div class="help_descr_steps">
                    <ul><?php echo __("keyword-check_desc")?></ul>
                    <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div>
                </div>
            </div>

            <div class="event_info">
                <div class="participant_name">
                    <span><?php echo __("your_checker") ?>:</span>
                    <span class="checker_name_span"><?php echo $data["event"][0]->checkerFName !== null ? $data["event"][0]->checkerFName . " " . mb_substr($data["event"][0]->checkerLName, 0, 1)."." : __("not_available") ?></span>
                </div>
                <div class="participant_info">
                    <div class="additional_info">
                        <a href="/events/information-tn/<?php echo $data["event"][0]->eventID ?>"><?php echo __("event_info") ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="tutorial_container">
    <div class="tutorial_popup">
        <div class="tutorial-close glyphicon glyphicon-remove"></div>
        <div class="tutorial_pic">
            <img src="<?php echo template_url("img/steps/icons/peer-review.png") ?>" width="100px" height="100px">
            <img src="<?php echo template_url("img/steps/big/peer-review.png") ?>" width="280px" height="280px">
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
            <h3><?php echo __("peer-review_tn")?></h3>
            <ul><?php echo __("peer-review_desc")?></ul>
        </div>
    </div>
</div>

<script>
    var disableHighlight = true;
</script>