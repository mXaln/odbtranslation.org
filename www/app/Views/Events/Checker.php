<?php
/**
 * Created by PhpStorm.
 * User: Maxim
 * Date: 12 Apr 2016
 * Time: 17:30
 */
use Helpers\Constants\EventSteps;
use Helpers\Tools;

if(empty($error) && empty($data["success"])):

    $current = $data["event"][0]->step;
    $step_num = $current == EventSteps::KEYWORD_CHECK ? 7 : 8;
?>

<div class="comment_div panel panel-default">
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
        <div class="main_content_title"><?php echo __("step_num", ["step_number" => $step_num]). ": " . __($current)?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <div class="main_content_text row">
                <h4><?php echo $data["event"][0]->sLang." - "
                        .__($data["event"][0]->bookProject)." - "
                        .($data["event"][0]->abbrID <= 39 ? __("old_test") : __("new_test"))." - "
                        ."<span class='book_name'>".$data["event"][0]->bookName." ".$data["currentChapter"].":1-".$data["totalVerses"]."</span>"?></h4>

                <?php if($data["event"][0]->step == EventSteps::CONTENT_REVIEW): ?>
                <div class="row">
                    <div class="col-sm-12 side_by_side_toggle">
                        <label><input type="checkbox" id="side_by_side_toggle" value="0" /> <?php echo __("side_by_side_toggle") ?></label>
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

                                            <img class="editComment" data="<?php echo $data["currentChapter"].":".$verse ?>" width="16" src="<?php echo template_url("img/edit.png") ?>" title="<?php echo __("write_note_title", array($verse))?>"/>

                                            <div class="comments">
                                                <?php if($hasComments): ?>
                                                    <?php foreach($data["comments"][$data["currentChapter"]][$verse] as $comment): ?>
                                                        <?php if($comment->memberID == $data["event"][0]->checkerID): ?>
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
                                    <img class="editComment" style="<?php echo $count>0 ? "top:".($count*5+5)."px" : "" ?>" data="<?php echo $data["currentChapter"].":".$verse ?>" width="16" src="<?php echo template_url("img/edit.pn") ?>g" title="<?php echo __("write_note_title", array($verse))?>"/>

                                    <div class="comments">
                                        <?php if($hasComments): ?>
                                            <?php foreach($data["comments"][$data["currentChapter"]][$verse] as $comment): ?>
                                                <?php if($comment->memberID == $data["event"][0]->checkerID): ?>
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
                        <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo __("confirm_yes")?></label>
                    </div>

                    <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo __("continue")?></button>
                </form>
            </div>
            <?php //endif; ?>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_float">
                <div class="help_info_steps <?php echo $data["isCheckerPage"] ? " is_checker_page_help" : "" ?>">
                    <div class="help_title_steps"><?php echo __("help") ?></div>

                    <div class="clear"></div>

                    <div class="help_name_steps"><span><?php echo __("step_num", ["step_number" => $step_num])?>: </span> <?php echo __($current)?></div>
                    <div class="help_descr_steps">
                        <ul><?php echo mb_substr(__($current . "_checker_desc"), 0, 300)?>... <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div></ul>
                    </div>
                </div>

                <div class="event_info <?php echo $data["isCheckerPage"] ? " is_checker_page_help" : "" ?>">
                    <div class="participant_info">
                        <div class="participant_name">
                            <span><?php echo __("your_translator") ?>:</span>
                            <span><?php echo $data["event"][0]->userName ?></span>
                        </div>
                        <div class="additional_info">
                            <a href="/events/information/<?php echo $data["event"][0]->eventID ?>"><?php echo __("event_info") ?></a>
                        </div>
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
            <img src="<?php echo template_url("img/steps/icons/".$current.".png") ?>" width="100" height="100">
            <img src="<?php echo template_url("img/steps/big/".$current.".png") ?>" width="280" height="280">
            
        </div>

        <div class="tutorial_content <?php echo $data["isCheckerPage"] ? " is_checker_page_help" : "" ?>">
            <h3><?php echo __($current)?></h3>
            <ul><?php echo __($current . "_checker_desc")?></ul>
        </div>
    </div>
</div>

<script>
    isChecker = true;
</script>
<?php endif; ?>