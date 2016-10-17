<?php
use \Helpers\Constants\EventSteps;
use \Helpers\Tools;
?>

<div class="editor">
    <div class="comment_div panel panel-default">
        <div class="panel-heading">
            <h1 class="panel-title"><?php echo __("write_note_title", array(""))?><span></span></h1>
            <span class="editor-close glyphicon glyphicon-floppy-disk"></span>
        </div>
        <textarea class="textarea textarea_editor"></textarea>
        <img src="<?php echo template_url("img/loader.gif") ?>" class="commentEditorLoader">
    </div>
</div>

<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <?php $apx = $data["event"][0]->gwLang == $data["event"][0]->targetLang ? "_gl" : "" ?>
        <?php $step = $data["event"][0]->gwLang == $data["event"][0]->targetLang ? 4 : 5 ?>
        <div class="main_content_title"><?php echo __("step_num", array($step)) . __(EventSteps::SELF_CHECK.$apx)?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <form action="" method="post" id="main_form">
                <div class="main_content_text row">
                    <div class="row" style="padding-left: 15px">
                        <h4><?php echo $data["event"][0]->sLang." - "
                                .__($data["event"][0]->bookProject)." - "
                                .($data["event"][0]->abbrID <= 39 ? __("old_test") : __("new_test"))." - "
                                ."<span class='book_name'>".$data["event"][0]->name." ".$data["currentChapter"].":".$data["chunk"][0]."-".$data["chunk"][sizeof($data["chunk"])-1]."</span>"?></h4>

                        <!-- Show blind draft text if it is a translation to other language -->
                        <?php if($data["event"][0]->gwLang != $data["event"][0]->targetLang):?>
                        <div class="col-sm-12">
                            <textarea readonly class="readonly blind_ta textarea"><?php echo $data["blindDraftText"]; ?></textarea>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <?php $i = 0; foreach($data["text"] as $verse => $text): ?>
                            <div class="row chunk_verse">
                                <div class="col-sm-6 verse"><?php echo "<strong><sup>".$verse."</sup></strong> " . $text; ?></div>
                                <div class="col-sm-6 editor_area">
                                <?php
                                $verses = Tools::parseCombinedVerses($verse);
                                foreach ($verses as $verse):?>
                                    <div class="vnote">
                                        <textarea name="verses[]" class="verse_ta textarea"><?php echo isset($_POST["verses"][$i]) ? $_POST["verses"][$i] : (isset($data["verses"][$verse]) ? $data["verses"][$verse] : "") ?></textarea>
                                        <img class="editComment" data="<?php echo $data["currentChapter"].":".$verse ?>" width="16" src="<?php echo template_url("img/edit.png") ?>" title="<?php echo __("write_note_title", array($verse))?>"/>

                                        <div class="comments">
                                        <?php if(array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($verse, $data["comments"][$data["currentChapter"]])): ?>
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
                                <?php endforeach; ?>
                                </div>
                            </div>
                            <?php $i++; endforeach; ?>
                        </div>
                    </div>
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

                <div class="help_name_steps"><span><?php echo __("step_num", array($step))?></span> <?php echo __(EventSteps::SELF_CHECK.$apx)?></div>
                <div class="help_descr_steps">
                    <ul><?php echo mb_substr(__(EventSteps::SELF_CHECK.$apx."_desc"), 0, 300)?>... <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div></ul>
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
            <img src="<?php echo template_url("img/steps/icons/self-check.png") ?>" width="100" height="100">
            <img src="<?php echo template_url("img/steps/big/self-check.png") ?>" width="280" height="280">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="<?php echo $data["event"][0]->step ?>" type="checkbox" value="0" /> <?php echo __("do_not_show_tutorial")?></label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo __(EventSteps::SELF_CHECK.$apx)?></h3>
            <ul><?php echo __(EventSteps::SELF_CHECK.$apx."_desc")?></ul>
        </div>
    </div>
</div>