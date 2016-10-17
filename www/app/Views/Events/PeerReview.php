<?php
use \Helpers\Constants\EventMembers;
use \Helpers\Tools;
?>

<div class="editor">
    <div class="comment_div panel panel-default">
        <div class="panel-heading">
            <h1 class="panel-title"><?php echo __("write_note_title", array(""))?><span></span></h1>
            <span class="editor-close glyphicon glyphicon-floppy-disk"></span>
        </div>
        <textarea class="textarea textarea_editor"></textarea>
        <div class="other_comments_list"></div>
        <img src="<?php echo template_url("img/loader.gif") ?>" class="commentEditorLoader">
    </div>
</div>

<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo __("step_num", array(6)) . __("peer-review")?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <form action="" method="post" id="main_form">
                <div class="main_content_text row">
                    <ul class="nav nav-tabs">
                        <?php if(!$data["event"][0]->cotrTranslateDone): ?>
                        <li role="presentation" class="translation_tab active"><a href="#cotr_tab"><?php echo __("partner_translation") ?></a></li>
                        <?php endif; ?>

                        <?php if(!$data["event"][0]->translateDone): ?>
                        <li role="presentation" class="translation_tab <?php echo $data["event"][0]->cotrTranslateDone ? "active" : ""?>"><a href="#tr_tab"><?php echo __("your_translation") ?></a></li>
                        <?php endif; ?>
                    </ul>

                    <?php if(!$data["event"][0]->cotrTranslateDone): ?>
                    <div class="cotr_main_content row">
                        <h4><?php echo $data["event"][0]->sLang." - "
                                .__($data["event"][0]->bookProject)." - "
                                .($data["event"][0]->abbrID <= 39 ? __("old_test") : __("new_test"))." - "
                                ."<span class='book_name'>".$data["event"][0]->name." ".$data["cotrData"]["currentChapter"].":1-".$data["cotrData"]["totalVerses"]."</span>"?></h4>

                        <div class="col-sm-12 cotrData">
                            <?php if($data["cotrData"]["cotrReady"]): ?>
                                <?php $sourceVerses = array_keys($data["cotrData"]["text"]); ?>
                                <?php $i=0; foreach($data["cotrData"]["translation"] as $key => $chunk): ?>
                                    <?php
                                    $count = 0;
                                    if($chunk[EventMembers::TRANSLATOR]["verses"] == null) continue;
                                    foreach($chunk[EventMembers::TRANSLATOR]["verses"] as $verse => $text):
                                        $verses = Tools::parseCombinedVerses($sourceVerses[$i]);
                                    ?>
                                        <?php if($count == 0): ?>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <p><strong><sup><?php echo $sourceVerses[$i]; ?></sup></strong> <?php echo $data["cotrData"]["text"][$sourceVerses[$i]]; ?></p>
                                            </div>

                                            <div class="col-sm-6 verse_with_note">
                                        <?php endif; ?>
                                                <div class="vnote">
                                                    <strong><sup><?php echo $verse; ?></sup></strong>
                                                    <?php echo $text; ?>

                                                    <?php $hasCotrComments = array_key_exists($data["cotrData"]["currentChapter"], $data["comments_cotr"]) && array_key_exists($verse, $data["comments_cotr"][$data["cotrData"]["currentChapter"]]); ?>
                                                    <div class="comments_number <?php echo $hasCotrComments ? "hasComment" : "" ?>">
                                                        <?php echo $hasCotrComments ? sizeof($data["comments_cotr"][$data["cotrData"]["currentChapter"]][$verse]) : ""?>
                                                    </div>
                                                    <img class="editComment" data="<?php echo $data["cotrData"]["currentChapter"].":".$verse ?>" width="16" src="<?php echo template_url("img/edit.png") ?>" title="<?php echo __("write_note_title", array($verse))?>"/>

                                                    <div class="comments">
                                                        <?php if($hasCotrComments): ?>
                                                            <?php foreach($data["comments_cotr"][$data["cotrData"]["currentChapter"]][$verse] as $comment): ?>
                                                                <?php if($comment->memberID == $data["event"][0]->myMemberID): ?>
                                                                    <div class="my_comment" data="<?php echo $data["cotrData"]["currentChapter"].":".$verse ?>"><?php echo $comment->text; ?></div>
                                                                <?php else: ?>
                                                                    <div class="other_comments"><?php echo "<span>".$comment->userName.":</span> ".$comment->text; ?></div>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="clear"></div>
                                                </div>


                                        <?php
                                        $count++;

                                        if($count == sizeof($verses)) :
                                            $i+=1;
                                            $count = 0; ?>
                                            </div>
                                        </div>
                                    <?php endif; endforeach; ?>
                                    <div class="chunk_divider col-sm-12"></div>
                                <?php endforeach; ?>
                            <?php else: ?>
                            <div class="row">
                                <div class="col-sm-12 cotr_not_ready" style="color: #ff0000;"><?php echo __("partner_not_ready_message")?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if(!$data["event"][0]->translateDone): ?>
                    <div class="tr_main_content row" <?php echo $data["event"][0]->cotrTranslateDone ? "style='display: block !important;'" : ""?>>
                        <h4><?php echo $data["event"][0]->sLang." - "
                                .__($data["event"][0]->bookProject)." - "
                                .($data["event"][0]->abbrID <= 39 ? __("old_test") : __("new_test"))." - "
                                ."<span class='book_name'>".$data["event"][0]->name." ".$data["currentChapter"].":1-".$data["totalVerses"]."</span>"?></h4>

                        <div class="col-sm-12">
                            <?php $sourceVerses = array_keys($data["text"]) ?>
                            <?php $i=0; foreach($data["translation"] as $key => $chunk) : ?>
                                <?php
                                $k=0;
                                $count = 0;
                                if($chunk[EventMembers::TRANSLATOR]["verses"] == null) continue;
                                foreach($chunk[EventMembers::TRANSLATOR]["verses"] as $verse => $text):
                                    $verses = Tools::parseCombinedVerses($sourceVerses[$i]);
                                    ?>
                                    <?php if($count == 0): ?>
                                    <div class="row chunk_verse">
                                        <div class="col-sm-6 verse"><strong><sup><?php echo $sourceVerses[$i]; ?></sup></strong> <?php echo $data["text"][$sourceVerses[$i]]; ?></div>
                                        <div class="col-sm-6 editor_area">
                                    <?php endif; ?>
                                            <div class="vnote">
                                                <textarea name="chunks[<?php echo $key; ?>][verses][]" class="peer_verse_ta textarea"><?php echo isset($_POST["chunks"]) && $_POST["chunks"][$key]["verses"][$k] != "" ? $_POST["chunks"][$key]["verses"][$k] : $text ?></textarea>

                                                <?php $hasComments = array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($verse, $data["comments"][$data["currentChapter"]]); ?>
                                                <div class="comments_number <?php echo $hasComments ? "hasComment" : "" ?>">
                                                    <?php echo $hasComments ? sizeof($data["comments"][$data["currentChapter"]][$verse]) : ""?>
                                                </div>
                                                <img class="editComment" data="<?php echo $data["currentChapter"].":".$verse ?>" width="16" src="<?php echo template_url("img/edit.png") ?>" title="<?php echo __("write_note_title", array($verse))?>"/>

                                                <div class="comments">
                                                    <?php if($hasComments): ?>
                                                        <?php foreach($data["comments"][$data["currentChapter"]][$verse] as $comment): ?>
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
                                <?php
                                $k++;
                                $count++;

                                if($count == sizeof($verses)) :
                                    $i+=1;
                                    $count = 0; ?>
                                        </div>
                                    </div>
                                <?php
                                endif;
                                endforeach;
                                ?>
                                <div class="chunk_divider col-sm-12"></div>
                            <?php endforeach; ?>
                        </div>

                        <div class="col-sm-12">
                            <button id="save_step" type="submit" name="save" value="1" class="btn btn-primary"><?php echo __("save")?></button>
                            <img src="<?php echo template_url("img/alert.png") ?>" class="unsaved_alert">
                        </div>
                    </div>
                    <?php endif; ?>
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

                <div class="help_name_steps"><span><?php echo __("step_num", array(6))?></span> <?php echo __("peer-review")?></div>
                <div class="help_descr_steps">
                    <ul><?php echo mb_substr(__("peer-review_desc"), 0, 300)?>... <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div></ul>
                </div>
            </div>

            <div class="event_info">
                <div class="participant_info">
                    <div class="participant_name">
                        <span><?php echo __("your_partner") ?>:</span>
                        <span><?php echo $data["event"][0]->pairName ?></span>
                    </div>
                    <div class="participant_name">
                        <span><?php echo __("your_checker") ?>:</span>
                        <span><?php echo $data["event"][0]->checkerName !== null ? $data["event"][0]->checkerName : "N/A" ?></span>
                    </div>
                    <div class="additional_info">
                        <a href="/events/information/<?php echo $data["event"][0]->eventID ?>"><?php echo __("event_info") ?></a>
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
            <img src="<?php echo template_url("img/steps/icons/peer-review.pn") ?>g" width="100" height="100">
            <img src="<?php echo template_url("img/steps/big/peer-review.png") ?>" width="280" height="280">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="<?php echo $data["event"][0]->step ?>" type="checkbox" value="0" /> <?php echo __("do_not_show_tutorial")?></label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo __("peer-review")?></h3>
            <ul><?php echo __("peer-review_desc")?></ul>
        </div>
    </div>
</div>