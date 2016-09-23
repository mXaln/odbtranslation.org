<?php
use \Core\Language;
use Helpers\Tools;
?>

<div class="editor">
    <div class="comment_div panel panel-default">
        <div class="panel-heading">
            <h1 class="panel-title"><?php echo Language::show("write_note_title", "Events", array(""))?><span></span></h1>
            <span class="editor-close glyphicon glyphicon-floppy-disk"></span>
        </div>
        <textarea class="textarea textarea_editor"></textarea>
        <div class="other_comments_list"></div>
        <img src="<?php echo \Helpers\Url::templatePath() ?>img/loader.gif" class="commentEditorLoader">
    </div>
</div>

<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo Language::show("step_num", "Events", array(5)) . Language::show("self-check-full", "Events")?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <form action="" method="post" id="main_form">
                <div class="main_content_text row" style="padding-left: 15px">
                    <h4><?php echo $data["event"][0]->sLang." - "
                            .Language::show($data["event"][0]->bookProject, "Events")." - "
                            .($data["event"][0]->abbrID <= 39 ? Language::show("old_test", "Events") : Language::show("new_test", "Events"))." - "
                            ."<span class='book_name'>".$data["event"][0]->name." ".$data["currentChapter"].":1-".$data["totalVerses"]."</span>"?></h4>

                    <div class="col-sm-12 no_padding">
                        <?php $sourceVerses = array_keys($data["text"]); ?>
                        <?php $i=0; foreach($data["translation"] as $key => $chunk) : ?>
                            <?php
                            $k=0;
                            $count = 0;
                            foreach($chunk["translator"]["verses"] as $verse => $text):
                                $verses = Tools::parseCombinedVerses($sourceVerses[$i]);
                                ?>
                                <?php if($count == 0): ?>
                                <div class="row chunk_verse">
                                    <div class="col-sm-6 verse"><strong><sup><?php echo $sourceVerses[$i]; ?></sup></strong> <?php echo $data["text"][$sourceVerses[$i]]; ?></div>
                                    <div class="col-sm-6 editor_area">
                                <?php endif; ?>
                                        <div class="vnote">
                                            <textarea name="chunks[<?php echo $key; ?>][verses][]" class="col-sm-6 peer_verse_ta textarea"><?php echo $_POST["chunks"][$key]["verses"][$k] != "" ? $_POST["chunks"][$key]["verses"][$k] : $text ?></textarea>

                                            <img class="editComment" data="<?php echo $data["currentChapter"].":".$verse ?>" width="16" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="<?php echo Language::show("write_note_title", "Events", array($verse))?>"/>

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
                        <button id="save_step" type="submit" name="save" value="1" class="btn btn-primary"><?php echo Language::show("save", "Events")?></button>
                        <img src="<?php echo \Helpers\Url::templatePath() ?>img/alert.png" class="unsaved_alert">
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

                <div class="help_name_steps"><span><?php echo Language::show("step_num", "Events", array(5))?></span> <?php echo Language::show("self-check-full", "Events")?></div>
                <div class="help_descr_steps">
                    <ul><?php echo mb_substr(Language::show("keyword-check_desc", "Events"), 0, 300)?>... <div class="show_tutorial_popup"> >>> <?php echo Language::show("show_more", "Events")?></div></ul>
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
                        <span class="checker_name_span"><?php echo $data["event"][0]->checkerName !== null ? $data["event"][0]->checkerName : "N/A" ?></span>
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
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/icons/self-check.png" width="100" height="100">
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/big/self-check.png" width="280" height="280">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="<?php echo $data["event"][0]->step ?>" type="checkbox" value="0" /> <?php echo Language::show("do_not_show_tutorial", "Events")?></label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo Language::show("self-check-full", "Events")?></h3>
            <ul><?php echo Language::show("self-check-full_desc", "Events")?></ul>
        </div>
    </div>
</div>