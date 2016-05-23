<?php
use \Core\Language;
?>

<div class="editor">
    <div class="comment_div panel panel-default">
        <div class="panel-heading">
            <h1 class="panel-title"><?php echo Language::show("write_note_title", "Events")?></h1>
            <span class="editor-close glyphicon glyphicon-ok-sign"></span>
        </div>
        <textarea class="textarea textarea_editor"></textarea>
    </div>
</div>

<div class="alt_editor">
    <div class="alt_comment_div panel panel-default">
        <div class="panel-heading">
            <h1 class="panel-title"><?php echo Language::show("write_note_title", "Events")?></h1>
            <span class="alt_editor-close glyphicon glyphicon-floppy-disk"></span>
        </div>
        <textarea class="textarea textarea_editor"></textarea>
        <img src="<?php echo \Helpers\Url::templatePath() ?>img/loader.gif" class="commentEditorLoader">
    </div>
</div>

<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo Language::show("peer-review", "Events")?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <form action="" method="post">
                <div class="main_content_text row">
                    <ul class="nav nav-tabs">
                        <?php if(!$data["event"][0]->cotrTranslateDone): ?>
                        <li role="presentation" class="translation_tab active"><a href="#cotr_tab">Partner's translation</a></li>
                        <?php endif; ?>

                        <?php if(!$data["event"][0]->translateDone): ?>
                        <li role="presentation" class="translation_tab <?php echo $data["event"][0]->cotrTranslateDone ? "active" : ""?>"><a href="#tr_tab">Your translation</a></li>
                        <?php endif; ?>
                    </ul>

                    <?php if(!$data["event"][0]->cotrTranslateDone): ?>
                    <div class="cotr_main_content row">
                        <h4><?php echo $data["event"][0]->sLang." - "
                                .Language::show($data["event"][0]->bookProject, "Events")." - "
                                .($data["event"][0]->abbrID <= 39 ? Language::show("old_test", "Events") : Language::show("new_test", "Events"))." - "
                                .$data["event"][0]->name." ".$data["cotrData"]["currentChapter"].":1-".$data["cotrData"]["totalVerses"]?></h4>

                        <div class="col-sm-12 cotrData">
                            <?php if($data["cotrData"]["cotrReady"]): ?>
                                <?php $i=2; foreach($data["cotrData"]["translation"] as $key => $chunk): ?>
                                    <?php
                                    $count = 0;
                                    foreach($chunk["translator"]["verses"] as $verse => $text):
                                        $verses = explode("-", $data["cotrData"]["text"][$i-1]);
                                        $comment = $chunk["translator"]["comments"][$verse];
                                        $commentAlt = $chunk["translator"]["comments_alt"][$verse];
                                    ?>
                                        <?php if($count == 0): ?>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <p><strong><sup><?php echo $data["cotrData"]["text"][$i-1]; ?></sup></strong> <?php echo $data["cotrData"]["text"][$i]; ?></p>
                                            </div>

                                            <div class="col-sm-6 verse_with_note">
                                                <p>
                                        <?php endif; ?>
                                                    <strong><sup><?php echo $verse; ?></sup></strong>
                                                    <?php echo $text; ?>
                                        <?php
                                        $count++;

                                        if($count == sizeof($verses)) :
                                            $i+=2;
                                            $count = 0; ?>
                                                </p>
                                                <?php if(trim($comment != "")): ?>
                                                <img class="showComment" data-toggle="tooltip" data-placement="left" title="<?php echo $comment; ?>" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/note.png">
                                                <?php endif;?>
                                                <img class="editCommentAlt" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note"/>
                                                <span class="commentAltText"><?php echo preg_replace("/^@[a-z]+[a-z0-9]*:\s/", "", $commentAlt); ?></span>
                                                <input type="hidden" class="tID" value="<?php echo $chunk["tID"]; ?>">
                                                <input type="hidden" class="verseNum" value="<?php echo $verse; ?>">
                                            </div>
                                        </div>
                                    <?php endif; endforeach; ?>
                                    <div class="chunk_divider col-sm-12"></div>
                                <?php endforeach; ?>
                            <?php else: ?>
                            <div class="row">
                                <div class="col-sm-12 cotr_not_ready" style="color: #ff0000;"><?php echo Language::show("partner_not_ready", "Events")?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if(!$data["event"][0]->translateDone): ?>
                    <div class="tr_main_content row" <?php echo $data["event"][0]->cotrTranslateDone ? "style='display: block !important;'" : ""?>>
                        <h4><?php echo $data["event"][0]->sLang." - "
                                .Language::show($data["event"][0]->bookProject, "Events")." - "
                                .($data["event"][0]->abbrID <= 39 ? Language::show("old_test", "Events") : Language::show("new_test", "Events"))." - "
                                .$data["event"][0]->name." ".$data["currentChapter"].":1-".$data["totalVerses"]?></h4>

                        <div class="col-sm-12">
                            <?php $i=2; foreach($data["translation"] as $key => $chunk) : ?>
                                <?php
                                $k=0;
                                $count = 0;
                                foreach($chunk["translator"]["verses"] as $verse => $text):
                                    $verses = explode("-", $data["text"][$i-1]);
                                    $comment = $chunk["translator"]["comments"][$verse];
                                    $commentAlt = $chunk["translator"]["comments_alt"][$verse];
                                    ?>
                                    <?php if($count == 0): ?>
                                    <div class="row chunk_verse">
                                        <div class="col-sm-6 verse"><strong><sup><?php echo $data["text"][$i-1]; ?></sup></strong> <?php echo $data["text"][$i]; ?></div>
                                        <div class="col-sm-6 editor_area">
                                    <?php endif; ?>
                                            <textarea name="chunks[<?php echo $key; ?>][verses][]" class="peer_verse_ta textarea"><?php echo $_POST["chunks"][$key]["verses"][$k] != "" ? $_POST["chunks"][$key]["verses"][$k] : $text ?></textarea>
                                            <img class="editComment" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note"/>
                                            <textarea name="chunks[<?php echo $key; ?>][comments][]" class="comment_ta textarea"><?php echo $_POST["chunks"][$key]["comments"][$k] != "" ? $_POST["chunks"][$key]["comments"][$k] : preg_replace("/^@[a-z]+[a-z0-9]*:\s/", "", $comment); ?></textarea>
                                            <?php if(trim($commentAlt != "")): ?>
                                            <img class="showComment" data-toggle="tooltip" data-placement="left" title="<?php echo $commentAlt; ?>" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/note.png"/>
                                            <?php endif;?>
                                <?php
                                $k++;
                                $count++;

                                if($count == sizeof($verses)) :
                                    $i+=2;
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
                            <button id="save_step" type="submit" name="save" value="1" class="btn btn-primary">Save</button>
                        </div>
                    </div>
                    <?php endif; ?>
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
                <div class="help_title_steps">HELP</div>

                <div class="clear"></div>

                <div class="help_name_steps"><span><?php echo Language::show("step_num", "Events", array(6))?></span> <?php echo Language::show("peer-review", "Events")?></div>
                <div class="help_descr_steps">
                    <ul><?php echo mb_substr(Language::show("peer-review_desc", "Events"), 0, 300)?>... <div class="show_tutorial_popup"> >>> <?php echo Language::show("show_more", "Events")?></div></ul>
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
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/icons/peer-review.png" width="100px" height="100px">
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/big/peer-review.png" width="280px" height="280px">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="<?php echo $data["event"][0]->step ?>" type="checkbox" value="0" /> <?php echo Language::show("do_not_show_tutorial", "Events")?></label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo Language::show("peer-review", "Events")?></h3>
            <ul><?php echo Language::show("peer-review_desc", "Events")?></ul>
        </div>
    </div>
</div>