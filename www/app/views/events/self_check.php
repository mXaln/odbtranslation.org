<?php
use \Core\Language;
use \Helpers\Constants\EventSteps;
?>

<div class="editor">
    <div class="comment_div panel panel-default">
        <div class="panel-heading">
            <h1 class="panel-title"><?php echo Language::show("write_note_title", "Events")?></h1>
            <span class="editor-close glyphicon glyphicon-floppy-disk"></span>
        </div>
        <textarea class="textarea textarea_editor"></textarea>
        <img src="<?php echo \Helpers\Url::templatePath() ?>img/loader.gif" class="commentEditorLoader">
    </div>
</div>

<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <?php $apx = $data["event"][0]->gwLang == $data["event"][0]->targetLang ? "_gl" : "" ?>
        <div class="main_content_title"><?php echo Language::show("step_num", "Events", array(5)) . Language::show(EventSteps::SELF_CHECK.$apx, "Events")?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <form action="" method="post" id="main_form">
                <div class="main_content_text row">
                    <div class="row">
                        <h4><?php echo $data["event"][0]->sLang." - "
                                .Language::show($data["event"][0]->bookProject, "Events")." - "
                                .($data["event"][0]->abbrID <= 39 ? Language::show("old_test", "Events") : Language::show("new_test", "Events"))." - "
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
                            <?php for($i=0; $i < sizeof($data["text"]); $i++): ?>
                            <div class="row chunk_verse">
                                <div class="col-sm-6 verse"><?php echo "<strong><sup>".$data["text"][$i]["verse"]."</sup></strong> " . $data["text"][$i]["content"]; ?></div>
                                <div class="col-sm-6 editor_area">
                                <?php
                                $verses = explode("-", $data["text"][$i]["verse"]);
                                foreach ($verses as $verse):?>
                                    <textarea name="verses[]" class="verse_ta textarea"><?php echo isset($_POST["verses"][$i]) ? $_POST["verses"][$i] : (isset($data["verses"][$verse]) ? $data["verses"][$verse] : "") ?></textarea>
                                    <img class="editComment" data="<?php echo $data["currentChapter"].":".$verse ?>" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note"/>

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
                                <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <div class="main_content_footer row">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo Language::show("confirm_finished", "Events")?></div>
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo Language::show("confirm_yes", "Events")?></label>
                    </div>

                    <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo Language::show("next_step", "Events")?></button>
                </div>
            </form>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps">
                <div class="help_title_steps"><?php echo Language::show("help", "Events") ?></div>

                <div class="clear"></div>

                <div class="help_name_steps"><span><?php echo Language::show("step_num", "Events", array(5))?></span> <?php echo Language::show(EventSteps::SELF_CHECK.$apx, "Events")?></div>
                <div class="help_descr_steps">
                    <ul><?php echo mb_substr(Language::show(EventSteps::SELF_CHECK.$apx."_desc", "Events"), 0, 300)?>... <div class="show_tutorial_popup"> >>> <?php echo Language::show("show_more", "Events")?></div></ul>
                </div>
            </div>

            <div class="event_info">
                <div class="participant_info">
                    <div class="participant_name">
                        <span><?php echo Language::show("your_partner", "Events") ?>:</span>
                        <span><?php echo $data["event"][0]->pairName ?></span>
                    </div>
                    <div class="participant_name">
                        <span><?php echo Language::show("your_checker", "Events") ?>:</span>
                        <span><?php echo $data["event"][0]->checkerName !== null ? $data["event"][0]->checkerName : "N/A" ?></span>
                    </div>
                    <div class="additional_info">
                        <a href="/events/information/<?php echo $data["event"][0]->eventID ?>"><?php echo Language::show("event_info", "Events") ?></a>
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
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/icons/self-check.png" width="100px" height="100px">
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/big/self-check.png" width="280px" height="280px">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="<?php echo $data["event"][0]->step ?>" type="checkbox" value="0" /> <?php echo Language::show("do_not_show_tutorial", "Events")?></label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo Language::show(EventSteps::SELF_CHECK.$apx, "Events")?></h3>
            <ul><?php echo Language::show(EventSteps::SELF_CHECK.$apx."_desc", "Events")?></ul>
        </div>
    </div>
</div>