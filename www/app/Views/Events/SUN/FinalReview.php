<?php
use Helpers\Constants\EventMembers;

if(isset($data["error"])) return;
?>

<div class="comment_div panel panel-default font_sun">
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
        <div class="main_content_title"><?php echo __("final-review")?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <form action="" method="post" id="finalReview">
                <div class="main_content_text">
                    <h4 dir="<?php echo $data["event"][0]->sLangDir ?>"><?php echo $data["event"][0]->tLang." - "
                            .__($data["event"][0]->bookProject)." - "
                            .($data["event"][0]->abbrID <= 39 ? __("old_test") : __("new_test"))." - "
                            ."<span class='book_name'>".$data["event"][0]->name." ".$data["currentChapter"].":1-".$data["totalVerses"]."</span>"?></h4>

                    <div class="col-sm-12">
                        <?php foreach($data["chunks"] as $key => $chunk) : ?>
                            <div class="row chunk_block">
                                <div class="chunk_verses col-sm-6" style="padding: 0 15px 0 0;" dir="<?php echo $data["event"][0]->sLangDir ?>">
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
                                <div class="col-sm-6 editor_area" style="padding: 0;" dir="<?php echo $data["event"][0]->tLangDir ?>">
                                    <?php $text = $data["translation"][$key][EventMembers::TRANSLATOR]["symbols"];?>
                                    <div class="vnote">
                                        <div class="markerBubbles noselect">
                                            <?php
                                            foreach ($chunk as $verse)
                                            {
                                                if(!empty($_POST) && isset($_POST["chunks"][$key]))
                                                {
                                                    if(preg_match("/\|".$verse."\|/", $_POST["chunks"][$key]))
                                                        continue;
                                                }
                                                echo '<div class="bubble">'.$verse.'</div>';
                                            }
                                            ?>
                                        </div>

                                        <?php
                                        if(!empty($_POST) && isset($_POST["chunks"][$key]))
                                            $text = $_POST["chunks"][$key];
                                        ?>
                                        <div class="textWithBubbles noselect sun_content"
                                             contentEditable="true">
                                            <?php
                                            $wordverse = preg_split("/\|([0-9]+)\|/", $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
                                            foreach ($wordverse as $item)
                                            {
                                                if(preg_match("/^[0-9]+$/", $item))
                                                {
                                                    echo "<div class=\"bubble\">{$item}</div>";
                                                }
                                                else
                                                {
                                                    $words = preg_split("/ /", $item);
                                                    foreach ($words as $word) {
                                                        echo "<div class='splword' contenteditable='true'>{$word}</div> ";
                                                    }
                                                }
                                            }
                                            ?>
                                        </div>

                                        <textarea name="chunks[]" class="col-sm-6 peer_verse_ta textarea ta_hidden"></textarea>

                                        <?php $hasComments = array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($key, $data["comments"][$data["currentChapter"]]); ?>
                                        <div class="comments_number <?php echo $hasComments ? "hasComment" : "" ?>">
                                            <?php echo $hasComments ? sizeof($data["comments"][$data["currentChapter"]][$key]) : ""?>
                                        </div>
                                        <img class="editComment" data="<?php echo $data["currentChapter"].":".$key ?>" width="16" src="<?php echo template_url("img/edit.png") ?>" title="<?php echo __("write_note_title")?>"/>

                                        <div class="comments">
                                            <?php if(array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($key, $data["comments"][$data["currentChapter"]])): ?>
                                                <?php foreach($data["comments"][$data["currentChapter"]][$key] as $comment): ?>
                                                    <?php if($comment->memberID == $data["event"][0]->myChkMemberID): ?>
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
                            </div>
                            <div class="chunk_divider col-sm-12"></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="clear"></div>
                </div>

                <div class="main_content_footer row">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo __("confirm_yes")?></label>
                    </div>

                    <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo __("next_step")?></button>
                </div>
            </form>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_float">
                <div class="help_info_steps is_checker_page_help">
                    <div class="help_hide toggle-help glyphicon glyphicon-eye-close" title="<?php echo __("hide_help") ?>"></div>
                    <div class="help_title_steps"><?php echo __("help") ?></div>

                    <div class="clear"></div>

                    <div class="help_name_steps"><span><?php echo __("final-review")?></span></div>
                    <div class="help_descr_steps">
                        <ul><?php echo __("final-review_desc")?></ul>
                        <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div>
                    </div>
                </div>

                <div class="event_info is_checker_page_help">
                    <div class="participant_info">
                        <div class="additional_info">
                            <a href="/events/information/<?php echo $data["event"][0]->eventID ?>"><?php echo __("event_info") ?></a>
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
            <img src="<?php echo template_url("img/steps/icons/final-review.png") ?>" height="100px" width="100px">
            <img src="<?php echo template_url("img/steps/big/final-review.png") ?>" height="280px" width="280px">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="read-chunk" value="0" type="checkbox"> <?php echo __("do_not_show_tutorial")?></label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo __("final-review")?></h3>
            <ul><?php echo __("final-review_desc")?></ul>
        </div>
    </div>
</div>