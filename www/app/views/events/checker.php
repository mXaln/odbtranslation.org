<?php
/**
 * Created by PhpStorm.
 * User: Maxim
 * Date: 12 Apr 2016
 * Time: 17:30
 */
use \Core\Language;
use \Helpers\Constants\EventSteps;
use Helpers\Tools;

if(empty($error) && empty($data["success"])):

    $current = $data["event"][0]->step;
    $step_num = $current == EventSteps::KEYWORD_CHECK ? 7 : 8;
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
        <div class="main_content_title"><?php echo Language::show("step_num", "Events", array($step_num)) . Language::show($current, "Events")?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <div class="main_content_text row">
                <div class="keywords_show" style="<?php echo $current == EventSteps::CONTENT_REVIEW ? "display:none;" : "" ?>"><?php echo Language::show("show_keywords", "Events"); ?></div>

                <div class="keywords_list_container">
                    <div class="keywords_list">
                        <div class="keywords-list-close glyphicon glyphicon-remove"></div>
                        <div class="labels_list">
                            <?php if(isset($data["keywords"])): ?>
                                <?php foreach ($data["keywords"] as $keyword): ?>
                                    <label><?php echo Language::show("verses", "Events")." ".$keyword["id"]?>
                                        <ul>
                                        <?php foreach ($keyword["terms"] as $term):?>
                                            <li><?php echo $term; ?></li>
                                        <?php endforeach; ?>
                                        </ul>
                                    </label>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <h4><?php echo $data["event"][0]->sLang." - "
                        .Language::show($data["event"][0]->bookProject, "Events")." - "
                        .($data["event"][0]->abbrID <= 39 ? Language::show("old_test", "Events") : Language::show("new_test", "Events"))." - "
                        ."<span class='book_name'>".$data["event"][0]->bookName." ".$data["currentChapter"].":1-".$data["totalVerses"]."</span>"?></h4>

                <?php if($data["event"][0]->step == EventSteps::CONTENT_REVIEW): ?>
                <div class="row">
                    <div class="col-sm-12 side_by_side_toggle">
                        <label><input type="checkbox" id="side_by_side_toggle" value="0" /> <?php echo Language::show("side_by_side_toggle", "Events") ?></label>
                    </div>
                </div>

                <div class="col-sm-12 side_by_side_content">
                    <?php $sourceVerses = array_keys($data["text"]) ?>
                    <?php $i=0; foreach($data["translation"] as $key => $chunk): ?>
                        <?php
                        $count = 0;
                        foreach($chunk["translator"]["verses"] as $verse => $text):
                            $verses = Tools::parseCombinedVerses($sourceVerses[$i]);
                            ?>
                            <?php if($count == 0): ?>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <p><strong><sup><?php echo $sourceVerses[$i]; ?></sup></strong> <?php echo $data["text"][$sourceVerses[$i]]; ?></p>
                                    </div>

                                    <div class="col-sm-6 verse_with_note">
                            <?php endif; ?>
                                        <div class="vnote">
                                            <strong><sup><?php echo $verse; ?></sup></strong>
                                            <?php echo $text; ?>

                                            <?php $hasComments = array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($verse, $data["comments"][$data["currentChapter"]]); ?>
                                            <div class="comments_number <?php echo $hasComments ? "hasComment" : "" ?>">
                                                <?php echo $hasComments ? sizeof($data["comments"][$data["currentChapter"]][$verse]) : ""?>
                                            </div>

                                            <img class="editComment" data="<?php echo $data["currentChapter"].":".$verse ?>" width="16" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="<?php echo Language::show("write_note_title", "Events", array($verse))?>"/>

                                            <div class="comments">
                                                <?php if($hasComments): ?>
                                                    <?php foreach($data["comments"][$data["currentChapter"]][$verse] as $comment): ?>
                                                        <?php if($comment->memberID == $data["event"][0]->checkerID): ?>
                                                            <div class="my_comment"><?php echo $comment->text; ?></div>
                                                        <?php else: ?>
                                                            <div class="other_comments"><?php echo "<span>".$comment->userName.":</span> ".$comment->text; ?></div>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <div class="my_comment" data="<?php echo $data["currentChapter"].":".$verse ?>"></div>
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
                </div>
                <?php endif; ?>

                <div class="col-sm-12 one_side_content">
                    <?php $sourceVerses = array_keys($data["text"]) ?>
                    <?php $i=0; foreach($data["translation"] as $key => $chunk): ?>
                        <?php
                        $count = 0;
                        foreach($chunk["translator"]["verses"] as $verse => $text):
                            $verses = Tools::parseCombinedVerses($sourceVerses[$i]);
                            ?>
                            <?php if($count == 0): ?>
                            <div class="source_content verse_with_note row">

                                <div style="padding-right: 15px" class="verse_line col-sm-11"><strong><sup><?php echo $sourceVerses[$i]; ?></sup></strong> <?php echo $data["text"][$sourceVerses[$i]]; ?></div>
                                <div class="col-sm-1 editor_area">
                            <?php endif; ?>
                            <div class="vnote">
                                    <?php $hasComments = array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($verse, $data["comments"][$data["currentChapter"]]); ?>
                                    <div class="comments_number <?php echo $hasComments ? "hasComment" : "" ?>" style="<?php echo $count>0 ? "top:".($count*5)."px" : "" ?>">
                                        <?php echo $hasComments ? sizeof($data["comments"][$data["currentChapter"]][$verse]) : ""?>
                                    </div>
                                    <img class="editComment" style="<?php echo $count>0 ? "top:".($count*5+5)."px" : "" ?>" data="<?php echo $data["currentChapter"].":".$verse ?>" width="16" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="<?php echo Language::show("write_note_title", "Events", array($verse))?>"/>

                                    <div class="comments">
                                        <?php if($hasComments): ?>
                                            <?php foreach($data["comments"][$data["currentChapter"]][$verse] as $comment): ?>
                                                <?php if($comment->memberID == $data["event"][0]->checkerID): ?>
                                                    <div class="my_comment"><?php echo $comment->text; ?></div>
                                                <?php else: ?>
                                                    <div class="other_comments"><?php echo "<span>".$comment->userName.":</span> ".$comment->text; ?></div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="my_comment" data="<?php echo $data["currentChapter"].":".$verse ?>"></div>
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
                            <?php
                            endif;
                        endforeach;
                    endforeach; ?>
                </div>
            </div>

            <?php //if(empty($error)):?>
            <div class="main_content_footer row">
                <form action="" method="post" id="checker_submit">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo Language::show("confirm_finished", "Events")?></div>
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo Language::show("confirm_yes", "Events")?></label>
                    </div>

                    <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo Language::show("continue", "Events")?></button>
                </form>
            </div>
            <?php //endif; ?>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps <?php echo $data["isCheckerPage"] ? " is_checker_page_help" : "" ?>">
                <div class="help_title_steps"><?php echo Language::show("help", "Events") ?></div>

                <div class="clear"></div>

                <div class="help_name_steps"><span><?php echo Language::show("step_num", "Events", array($step_num))?></span> <?php echo Language::show($current, "Events")?></div>
                <div class="help_descr_steps">
                    <ul><?php echo mb_substr(Language::show($current . "_checker_desc", "Events"), 0, 300)?>... <div class="show_tutorial_popup"> >>> <?php echo Language::show("show_more", "Events")?></div></ul>
                </div>
            </div>

            <div class="event_info <?php echo $data["isCheckerPage"] ? " is_checker_page_help" : "" ?>">
                <div class="participant_info">
                    <div class="participant_name">
                        <span><?php echo Language::show("your_translator", "Events") ?>:</span>
                        <span><?php echo $data["event"][0]->userName ?></span>
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
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/icons/<?php echo $current ?>.png" width="100" height="100">
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/big/<?php echo $current ?>.png" width="280" height="280">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="<?php echo $data["event"][0]->step."_checker" ?>" data2="checker" type="checkbox" value="0" /> <?php echo Language::show("do_not_show_tutorial", "Events")?></label>
            </div>
        </div>

        <div class="tutorial_content <?php echo $data["isCheckerPage"] ? " is_checker_page_help" : "" ?>">
            <h3><?php echo Language::show($current, "Events")?></h3>
            <ul><?php echo Language::show($current . "_checker_desc", "Events")?></ul>
        </div>
    </div>
</div>

<script>
    isChecker = true;
    keywords = [];

    <?php if($data["event"][0]->step == EventSteps::KEYWORD_CHECK): ?>
        <?php if(isset($data["keywords"])): ?>
            <?php foreach ($data["keywords"] as $keyword): ?>
                <?php foreach ($keyword["terms"] as $term):?>
                    <?php $kws = explode(", ", $term) ?>
                    <?php foreach ($kws as $item):?>
                        if($.inArray('<?php echo addslashes ($item); ?>', keywords) <= -1)
                            keywords.push('<?php echo addslashes ($item); ?>');
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</script>
<?php endif; ?>

<script src="<?php echo \Helpers\Url::templatePath()?>js/jquery.mark.min.js" type="text/javascript"></script>
