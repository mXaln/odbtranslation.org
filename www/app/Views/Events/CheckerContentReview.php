<?php
/**
 * Created by PhpStorm.
 * User: Maxim
 * Date: 12 Apr 2016
 * Time: 17:30
 */
use Helpers\Constants\EventMembers;

if(empty($error) && empty($data["success"])):
?>

<div class="editor">
    <div class="comment_div panel panel-default" dir="<?php echo $data["event"][0]->tLangDir ?>">
        <div class="panel-heading">
            <h1 class="panel-title"><?php echo __("write_note_title")?></h1>
            <span class="editor-close glyphicon glyphicon-floppy-disk"></span>
        </div>
        <textarea class="textarea textarea_editor"></textarea>
        <div class="other_comments_list <?php echo $data["event"][0]->tLangDir?>"></div>
        <img src="<?php echo template_url("img/loader.gif") ?>" class="commentEditorLoader">
    </div>
</div>


<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo __("step_num", [8]). ": " . __("content-review")?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <div class="main_content_text row">
                <h4 dir="<?php echo $data["event"][0]->sLangDir ?>"><?php echo $data["event"][0]->tLang." - "
                        .__($data["event"][0]->bookProject)." - "
                        .($data["event"][0]->abbrID <= 39 ? __("old_test") : __("new_test"))." - "
                        ."<span class='book_name'>".$data["event"][0]->bookName." ".$data["currentChapter"].":1-".$data["totalVerses"]."</span>"?></h4>

                <div class="row">
                    <div class="col-sm-12 side_by_side_toggle">
                        <label><input type="checkbox" id="side_by_side_toggle" value="0" /> <?php echo __("side_by_side_toggle") ?></label>
                    </div>
                </div>

                <div class="col-sm-12 side_by_side_content">
                    <?php foreach($data["chapters"][$data["currentChapter"]]["chunks"] as $key => $chunk) : ?>
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
                                <?php $text = $data["translation"][$key][EventMembers::TRANSLATOR]["blind"]; ?>
                                <div class="vnote">
                                    <?php echo $text; ?>

                                    <?php $hasComments = array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($key, $data["comments"][$data["currentChapter"]]); ?>
                                    <div class="comments_number <?php echo $hasComments ? "hasComment" : "" ?>">
                                        <?php echo $hasComments ? sizeof($data["comments"][$data["currentChapter"]][$key]) : ""?>
                                    </div>
                                    <img class="editComment" data="<?php echo $data["currentChapter"].":".$key ?>" width="16" src="<?php echo template_url("img/edit.png") ?>" title="<?php echo __("write_note_title", [""])?>"/>

                                    <div class="comments">
                                        <?php if(array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($key, $data["comments"][$data["currentChapter"]])): ?>
                                            <?php foreach($data["comments"][$data["currentChapter"]][$key] as $comment): ?>
                                                <?php if($comment->memberID == $data["event"][0]->checkerID): ?>
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

                <div class="col-sm-12 one_side_content" dir="<?php echo $data["event"][0]->tLangDir ?>">
                    <?php foreach($data["chapters"][$data["currentChapter"]]["chunks"] as $key => $chunk) : ?>
                        <div class="chunk_block">
                            <div style="padding-right: 15px" class="chunk_verses">
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
                            <div class="vnote">
                                <?php $hasComments = array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($key, $data["comments"][$data["currentChapter"]]); ?>
                                <div class="comments_number <?php echo $hasComments ? "hasComment" : "" ?>">
                                    <?php echo $hasComments ? sizeof($data["comments"][$data["currentChapter"]][$key]) : ""?>
                                </div>
                                <img class="editComment" data="<?php echo $data["currentChapter"].":".$key ?>" width="16" src="<?php echo template_url("img/edit.png") ?>" title="<?php echo __("write_note_title", [""])?>"/>

                                <div class="comments">
                                    <?php if(array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($key, $data["comments"][$data["currentChapter"]])): ?>
                                        <?php foreach($data["comments"][$data["currentChapter"]][$key] as $comment): ?>
                                            <?php if($comment->memberID == $data["event"][0]->checkerID): ?>
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
                        <div class="chunk_divider col-sm-12"></div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="main_content_footer row">
                <form action="" method="post" id="checker_submit">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo __("confirm_yes")?></label>
                    </div>

                    <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo __("continue")?></button>
                </form>
            </div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps <?php echo $data["isCheckerPage"] ? " is_checker_page_help" : "" ?>">
                <div class="help_title_steps"><?php echo __("help") ?></div>

                <div class="clear"></div>

                <div class="help_name_steps"><span><?php echo __("step_num", [8])?>: </span> <?php echo __("content-review")?></div>
                <div class="help_descr_steps">
                    <ul><?php echo mb_substr(__("content-review_checker_desc"), 0, 300)?>... <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div></ul>
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


<div class="tutorial_container">
    <div class="tutorial_popup">
        <div class="tutorial-close glyphicon glyphicon-remove"></div>
        <div class="tutorial_pic">
            <img src="<?php echo template_url("img/steps/icons/content-review.png") ?>" width="100" height="100">
            <img src="<?php echo template_url("img/steps/big/content-review.png") ?>" width="280" height="280">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="<?php echo "content-review_checker" ?>" data2="checker" type="checkbox" value="0" /> <?php echo __("do_not_show_tutorial")?></label>
            </div>
        </div>

        <div class="tutorial_content <?php echo $data["isCheckerPage"] ? " is_checker_page_help" : "" ?>">
            <h3><?php echo __("content-review")?></h3>
            <ul><?php echo __("content-review_checker_desc")?></ul>
        </div>
    </div>
</div>

<script>
    isChecker = true;
</script>
<?php endif; ?>

<script src="<?php echo template_url("js/jquery.mark.min.js")?>" type="text/javascript"></script>