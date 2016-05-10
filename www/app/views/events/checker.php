<?php
/**
 * Created by PhpStorm.
 * User: Maxim
 * Date: 12 Apr 2016
 * Time: 17:30
 */
use \Core\Language;
use \Helpers\Constants\EventSteps;

if(empty($error) && empty($data["success"])):

    $current = $data["event"][0]->step;
    $step_num = $current == EventSteps::KEYWORD_CHECK ? 7 : 8;
?>

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
    <div class="row">
        <div class="main_content_title"><?php echo Language::show($current, "Events")?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <div class="main_content_text row">
                <h4><?php echo $data["event"][0]->sLang." - "
                        .Language::show($data["event"][0]->bookProject, "Events")." - "
                        .($data["event"][0]->abbrID <= 39 ? Language::show("old_test", "Events") : Language::show("new_test", "Events"))." - "
                        .$data["event"][0]->bookName." ".$data["currentChapter"].":1-".$data["totalVerses"]?></h4>

                <?php if($data["event"][0]->step == EventSteps::CONTENT_REVIEW): ?>
                <div class="row">
                    <div class="col-sm-12 side_by_side_toggle">
                        <label><input type="checkbox" id="side_by_side_toggle" value="0" /> <?php echo Language::show("side_by_side_toggle", "Events") ?></label>
                    </div>
                </div>

                <div class="col-sm-12 side_by_side_content">
                    <?php $i=2; foreach($data["translation"] as $key => $chunk): ?>
                        <?php
                        $count = 0;
                        foreach($chunk["translator"]["verses"] as $verse => $text):
                            $verses = explode("-", $data["text"][$i-1]);
                            $comment = $chunk["translator"]["comments"][$verse];
                            $commentAlt = $chunk["translator"]["comments_alt"][$verse];
                            ?>
                            <?php if($count == 0): ?>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <p><strong><sup><?php echo $data["text"][$i-1]; ?></sup></strong> <?php echo $data["text"][$i]; ?></p>
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
                                        <span class="commentAltText"><?php echo $commentAlt; ?></span>
                                        <input type="hidden" class="tID" value="<?php echo $chunk["tID"]; ?>">
                                        <input type="hidden" class="verseNum" value="<?php echo $verse; ?>">
                                    </div>
                                </div>
                        <?php endif; endforeach; ?>
                        <div class="chunk_divider col-sm-12"></div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="col-sm-12 one_side_content">
                    <?php $i=2; foreach($data["translation"] as $key => $chunk): ?>
                        <?php
                        $count = 0;
                        foreach($chunk["translator"]["verses"] as $verse => $text):
                            $verses = explode("-", $data["text"][$i-1]);
                            $comment = $chunk["translator"]["comments"][$verse];
                            $commentAlt = $chunk["translator"]["comments_alt"][$verse];
                            ?>
                            <?php if($count == 0): ?>
                            <div class="source_content verse_with_note">
                                <div style="padding-right: 15px"><strong><sup><?php echo $data["text"][$i-1]; ?></sup></strong> <?php echo $data["text"][$i]; ?></div>
                                <?php if(trim($comment != "")): ?>
                                    <img class="showComment" data-toggle="tooltip" data-placement="left" title="<?php echo $comment; ?>" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/note.png">
                                <?php endif;?>
                                <img class="editCommentAlt" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note"/>
                                <span class="commentAltText"><?php echo $commentAlt; ?></span>
                                <input type="hidden" class="tID" value="<?php echo $chunk["tID"]; ?>">
                                <input type="hidden" class="verseNum" value="<?php echo $verse; ?>">
                            </div>
                            <?php
                            endif;
                            $count++;
                            if($count == sizeof($verses)) {
                                $i+=2;
                                $count = 0;
                            }
                        endforeach;
                    endforeach; ?>
                </div>
            </div>

            <?php //if(empty($error)):?>
            <div class="main_content_footer row">
                <form action="" method="post">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo Language::show("confirm_finished", "Events")?></div>
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo Language::show("confirm_yes", "Events")?></label>
                    </div>

                    <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo Language::show("next_step", "Events")?></button>
                </form>
            </div>
            <?php //endif; ?>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps">
                <div class="help_title_steps">HELP</div>

                <div class="clear"></div>

                <div class="help_name_steps"><span><?php echo Language::show("step_num", "Events", array($step_num))?></span> <?php echo Language::show($current, "Events")?></div>
                <div class="help_descr_steps">
                    <ul><?php echo mb_substr(Language::show($current . "_checker_desc", "Events"), 0, 300)?>... <div class="show_tutorial_popup"> >>> <?php echo Language::show("show_more", "Events")?></div></ul>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="tutorial_container">
    <div class="tutorial_popup">
        <div class="tutorial-close glyphicon glyphicon-remove"></div>
        <div class="tutorial_pic">
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/icons/<?php echo $current ?>.png" width="100px">
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/big/<?php echo $current ?>.png" width="280px">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="<?php echo $data["event"][0]->step."_checker" ?>" data2="checker" type="checkbox" value="0" /> <?php echo Language::show("do_not_show_tutorial", "Events")?></label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo Language::show($current, "Events")?></h3>
            <ul><?php echo Language::show($current . "_checker_desc", "Events")?></ul>
        </div>
    </div>
</div>
<?php endif; ?>