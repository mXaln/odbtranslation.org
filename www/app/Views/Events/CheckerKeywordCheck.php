<?php
/**
 * Created by PhpStorm.
 * User: Maxim
 * Date: 12 Apr 2016
 * Time: 17:30
 */
use Helpers\Constants\EventSteps;
use Helpers\Tools;
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
        <div class="main_content_title">
            <div><?php echo __("step_num", [7]). ": " . __("keyword-check")?></div>
            <div class="action_type type_checking"><?php echo __("type_checking"); ?></div>
        </div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <div class="main_content_text row" dir="<?php echo $data["event"][0]->sLangDir ?>">
                <h4><?php echo $data["event"][0]->tLang." - "
                        .__($data["event"][0]->bookProject)." - "
                        .($data["event"][0]->abbrID <= 39 ? __("old_test") : __("new_test"))." - "
                        ."<span class='book_name'>".$data["event"][0]->bookName." ".$data["currentChapter"].":1-".$data["totalVerses"]."</span>"?></h4>

                <div class="col-sm-12 one_side_content">
                    <?php foreach($data["chunks"] as $key => $chunk) : ?>
                        <div class="chunk_block">
                            <div style="padding-right: 15px" class="chunk_verses" >
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
                <div class="step_right chk"><?php echo __("step_num", [7])?></div>
            </div>
        </div>

        <div class="content_help col-sm-3">
            <?php if($data["event"][0]->sourceBible == "ulb"): ?>
                <div class="keywords_list">
                    <h3><?php echo __("show_keywords") ?></h3>
                    <div class="labels_list">
                        <?php if(isset($data["keywords"])): ?>
                            <?php foreach ($data["keywords"] as $keyword): ?>
                                <label><?php echo __("verses")." ".$keyword["id"]?>
                                    <ul>
                                        <?php foreach ($keyword["terms"] as $term):?>
                                            <li>
                                                <div class="word_term"><?php echo $term["word"]; ?></div>
                                                <div class="word_def"><?php echo $term["def"]; ?></div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="word_def_popup">
                        <div class="word_def-close glyphicon glyphicon-remove"></div>

                        <div class="word_def_title"></div>
                        <div class="word_def_content"></div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="help_info_steps <?php echo $data["isCheckerPage"] ? " is_checker_page_help" : "" ?>">
                <div class="help_title_steps"><?php echo __("help") ?></div>

                <div class="clear"></div>

                <div class="help_name_steps"><span><?php echo __("step_num", [7])?>: </span> <?php echo __("keyword-check")?></div>
                <div class="help_descr_steps">
                    <ul><?php echo __("keyword-check_checker_desc")?></ul>
                    <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div>
                </div>
            </div>

            <div class="event_info <?php echo $data["isCheckerPage"] ? " is_checker_page_help" : "" ?>">
                <div class="participant_info">
                    <div class="participant_name">
                        <span><?php echo __("your_translator") ?>:</span>
                        <span><?php echo $data["event"][0]->firstName . " " . mb_substr($data["event"][0]->lastName, 0, 1)."." ?></span>
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
            <img src="<?php echo template_url("img/steps/icons/keyword-check.png") ?>" width="100" height="100">
            <img src="<?php echo template_url("img/steps/big/keyword-check.png") ?>" width="280" height="280">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="<?php echo $data["event"][0]->step."_checker" ?>" data2="checker" type="checkbox" value="0" /> <?php echo __("do_not_show_tutorial")?></label>
            </div>
        </div>

        <div class="tutorial_content <?php echo $data["isCheckerPage"] ? " is_checker_page_help" : "" ?>">
            <h3><?php echo __("keyword-check")?></h3>
            <ul><?php echo __("keyword-check_checker_desc")?></ul>
        </div>
    </div>
</div>

<script>
    isChecker = true;

    $(document).ready(function() {
        $("#next_step").click(function (e) {
            renderConfirmPopup(Language.checkerConfirmTitle, Language.checkerConfirm,
                function () {
                    $("#checker_submit").submit();
                },
                function () {
                    $("#confirm_step").prop("checked", false);
                    $("#next_step").prop("disabled", true);
                    $( this ).dialog("close");
                },
                function () {
                    $("#confirm_step").prop("checked", false);
                    $("#next_step").prop("disabled", true);
                    $( this ).dialog("close");
                });

            e.preventDefault();
        });
    });
</script>
<?php endif; ?>

<script src="<?php echo template_url("js/jquery.mark.min.js")?>" type="text/javascript"></script>
