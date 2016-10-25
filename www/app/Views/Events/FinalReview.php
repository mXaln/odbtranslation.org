<?php
use Helpers\Constants\EventMembers;
?>

<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo __("final-review")?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <form action="" method="post" id="finalReview">
                <div class="main_content_text">
                    <h4><?php echo $data["event"][0]->sLang." - "
                            .__($data["event"][0]->bookProject)." - "
                            .($data["event"][0]->abbrID <= 39 ? __("old_test") : __("new_test"))." - "
                            ."<span class='book_name'>".$data["event"][0]->name." ".$data["currentChapter"].":1-".$data["totalVerses"]."</span>"?></h4>

                    <div class="col-sm-12">
                        <?php foreach($data["chapters"][$data["currentChapter"]]["chunks"] as $key => $chunk) : ?>
                            <div class="row chunk_block">
                                <div class="chunk_verses col-sm-6" style="padding: 0 15px 0 0;">
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
                                        <strong><sup><?php echo $verse; ?></sup></strong><?php echo $data["text"][$verse]; ?>
                                    <?php endforeach; ?>
                                </div>
                                <div class="col-sm-6 editor_area" style="padding: 0;">
                                    <?php $text = $data["translation"][$key][EventMembers::TRANSLATOR]["blind"];?>
                                    <div class="vnote">
                                        <div class="bubblesReset"><img src="<?php echo template_url("img/reset.png") ?>" width="40" title="<?php echo __("reset_markers") ?>" ></div>
                                        <div class="markerBubbles noselect">
                                            <?php foreach ($chunk as $verse): ?>
                                                <?php
                                                if(!empty($_POST) && isset($_POST["chunks"][$key]))
                                                {
                                                    if(preg_match("/\|".$verse."\|/", $_POST["chunks"][$key]))
                                                        continue;
                                                }
                                                ?>
                                                <div class="bubble"><?php echo $verse ?></div>
                                            <?php endforeach; ?>
                                        </div>

                                        <?php
                                        if(!empty($_POST) && isset($_POST["chunks"][$key]))
                                            $text = preg_replace("/\|([0-9]+)\|/", "<div class=\"bubble\">$1</div>", $_POST["chunks"][$key]);
                                        ?>
                                        <div class="textWithBubbles noselect" contentEditable="true">
                                            <?php echo $text ?>
                                        </div>

                                        <textarea name="chunks[]" class="col-sm-6 peer_verse_ta textarea ta_hidden"></textarea>

                                        <?php $hasComments = array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($key, $data["comments"][$data["currentChapter"]]); ?>
                                        <div class="comments_number <?php echo $hasComments ? "hasComment" : "" ?>">
                                            <?php echo $hasComments ? sizeof($data["comments"][$data["currentChapter"]][$key]) : ""?>
                                        </div>
                                        <img class="editComment" data="<?php echo $data["currentChapter"].":".$key ?>" width="16" src="<?php echo template_url("img/edit.png") ?>" title="<?php echo __("write_note_title", [""])?>"/>

                                        <div class="comments">
                                            <?php if(array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($key, $data["comments"][$data["currentChapter"]])): ?>
                                                <?php foreach($data["comments"][$data["currentChapter"]][$key] as $comment): ?>
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
            <div class="help_info_steps">
                <div class="help_title_steps"><?php echo __("help") ?></div>

                <div class="clear"></div>

                <div class="help_name_steps"><span><?php echo __("final-review")?></span></div>
                <div class="help_descr_steps">
                    <ul><?php echo __("final-review_desc")?></ul>
                </div>
            </div>

            <div class="event_info">
                <div class="participant_info">
                    <div class="additional_info">
                        <a href="/events/information/<?php echo $data["event"][0]->eventID ?>"><?php echo __("event_info") ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>