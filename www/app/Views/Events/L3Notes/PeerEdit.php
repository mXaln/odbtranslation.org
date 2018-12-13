<?php
if(isset($data["error"])) return;

use \Helpers\Constants\EventMembers;
use Helpers\Parsedown;
use Helpers\Session;

$parsedown = new Parsedown();
?>
<div class="comment_div panel panel-default font_<?php echo $data["event"][0]->targetLang ?>"
     dir="<?php echo $data["event"][0]->tLangDir ?>">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo __("write_note_title")?></h1>
        <span class="editor-close btn btn-success" data-level="3"><?php echo __("save") ?></span>
        <span class="xbtn glyphicon glyphicon-remove"></span>
    </div>
    <textarea style="overflow-x: hidden; word-wrap: break-word; overflow-y: visible;" class="textarea textarea_editor"></textarea>
    <div class="other_comments_list"></div>
    <img src="<?php echo template_url("img/loader.gif") ?>" class="commentEditorLoader">
</div>

<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo __("peer-edit-l3_full")?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <form action="" id="main_form" method="post">
                <div class="main_content_text">
                    <?php if($data["event"][0]->checkerFName == null): ?>
                        <div class="alert alert-success check_request"><?php echo __("check_request_sent_success") ?></div>
                    <?php endif; ?>

                    <h4><?php echo $data["event"][0]->tLang." - "
                            .__($data["event"][0]->bookProject)." - "
                            .($data["event"][0]->abbrID <= 39 ? __("old_test") : __("new_test"))." - "
                            ."<span class='book_name'>".$data["event"][0]->name." ".
                            ($data["currentChapter"] > 0
                                ? $data["currentChapter"].":1-".$data["totalVerses"]
                                : __("front"))."</span>"?></h4>

                        <div id="my_notes_content" class="my_content">
                        <?php foreach($data["chunks"] as $chunkNo => $chunk): $fv = $chunk[0]; ?>
                            <div class="note_chunk l3">
                                <?php if($fv > 0): ?>
                                    <div class="compare_scripture">
                                        <label>
                                            <input type="checkbox" checked data-toggle="toggle"
                                                   data-on="<?php echo __("on") ?>"
                                                   data-off="<?php echo __("off") ?>">
                                            <?php echo __("compare"); ?>
                                        </label>
                                    </div>
                                <?php endif; ?>
                                <div class="scripture_l3">
                                    <?php if(!empty($data["ulb_translation"]["l3"])): ?>
                                        <?php foreach(array_values($chunk) as $verse): ?>
                                            <?php if($verse <= 0) continue; ?>
                                            <?php echo isset($data["ulb_translation"]["l3"][$verse])
                                                ? $verse . ". <span data-verse=\"$verse\">" . $data["ulb_translation"]["l3"][$verse]."</span>" : ""; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="scripture_l2">
                                    <?php if(!empty($data["ulb_translation"]["l2"])): ?>
                                        <?php foreach(array_values($chunk) as $verse): ?>
                                            <?php if($verse <= 0) continue; ?>
                                            <?php echo isset($data["ulb_translation"]["l2"][$verse])
                                                ? $verse . ". <span data-verse=\"$verse\">" . $data["ulb_translation"]["l2"][$verse]."</span>" : ""; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="scripture_compare">
                                    <?php if(!empty($data["ulb_translation"]["l3"])): ?>
                                        <?php foreach(array_values($chunk) as $verse): ?>
                                            <?php if($verse <= 0) continue; ?>
                                            <?php echo isset($data["ulb_translation"]["l3"][$verse])
                                                ? $verse . ". <span data-verse=\"$verse\"></span>" : ""; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="vnote l3 notes_editor font_<?php echo $data["event"][0]->targetLang ?>"
                                     dir="<?php echo $data["event"][0]->tLangDir ?>">
                                    <?php
                                    $text = empty($data["translation"][$chunkNo][EventMembers::L3_CHECKER]["verses"]) ?
                                        $data["translation"][$chunkNo][EventMembers::CHECKER]["verses"] :
                                        $data["translation"][$chunkNo][EventMembers::L3_CHECKER]["verses"];
                                    $text = $parsedown->text($text);

                                    $text = isset($_POST["chunks"]) && isset($_POST["chunks"][$chunkNo])
                                        ? $_POST["chunks"][$chunkNo]
                                        : $text;

                                    $text = preg_replace(
                                        "/(\[\[[0-9a-z:\/\-]+\]\])/",
                                        "<span class='uwlink' title='".__("leaveit")."'>$1</span>",
                                        $text);
                                    ?>

                                    <textarea
                                            name="chunks[<?php echo $chunkNo ?>]"
                                            class="add_notes_editor"><?php echo $text ?></textarea>

                                    <?php $hasComments = array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($chunkNo, $data["comments"][$data["currentChapter"]]); ?>
                                    <div class="comments_number tncomml3 <?php echo $hasComments ? "hasComment" : "" ?>">
                                        <?php echo $hasComments ? sizeof($data["comments"][$data["currentChapter"]][$chunkNo]) : ""?>
                                    </div>
                                    <img class="editComment tncomml3" data="<?php echo $data["currentChapter"].":".$chunkNo ?>" width="16" src="<?php echo template_url("img/edit.png") ?>" title="<?php echo __("write_note_title", [""])?>"/>

                                    <div class="comments">
                                        <?php if(array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($chunkNo, $data["comments"][$data["currentChapter"]])): ?>
                                            <?php foreach($data["comments"][$data["currentChapter"]][$chunkNo] as $comment): ?>
                                                <?php if($comment->memberID == Session::get("memberID")
                                                    && $comment->level == 3): ?>
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
                                        <?php endif; ?>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                </div>

                <div class="main_content_footer row">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo __("confirm_yes")?></label>
                    </div>

                    <input type="hidden" name="step" value="<?php echo $data["event"][0]->step ?>">
                    <input type="hidden" name="level" value="l3">

                    <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo __("next_step")?></button>
                </div>
            </form>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_float">
                <div class="help_info_steps <?php echo isset($data["isCheckerPage"]) ? "is_checker_page_help" : "is_checker_page_help isPeer" ?>">
                    <div class="help_hide toggle-help glyphicon glyphicon-eye-close" title="<?php echo __("hide_help") ?>"></div>
                    <div class="help_title_steps"><?php echo __("help") ?></div>

                    <div class="clear"></div>

                    <div class="help_name_steps"><span><?php echo __("peer-edit-l3")?></span></div>
                    <div class="help_descr_steps">
                        <ul><?php echo __("peer-edit-l3_desc")?></ul>
                        <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div>
                    </div>
                </div>

                <div class="event_info <?php echo isset($data["isCheckerPage"]) ? "is_checker_page_help" : "is_checker_page_help isPeer" ?>">
                    <div class="participant_info">
                        <div class="participant_name">
                            <span><?php echo __("your_checker") ?>:</span>
                            <span class="checker_name_span">
                                <?php echo $data["event"][0]->checkerFName !== null
                                    ? $data["event"][0]->checkerFName . " "
                                    . mb_substr($data["event"][0]->checkerLName, 0, 1)."."
                                    : __("not_available") ?>
                            </span>
                        </div>
                        <div class="additional_info">
                            <a href="/events/information-tn-l3/<?php echo $data["event"][0]->eventID ?>"><?php echo __("event_info") ?></a>
                        </div>
                    </div>
                </div>

                <div class="tr_tools">
                    <button class="btn btn-primary ttools" data-tool="tn"><?php echo __("show_notes") ?></button>
                </div>
            </div>
        </div>
    </div>

    <div class="help_show toggle-help glyphicon glyphicon-question-sign" title="<?php echo __("show_help") ?>"></div>
</div>

<div class="tutorial_container">
    <div class="tutorial_popup">
        <div class="tutorial-close glyphicon glyphicon-remove"></div>
        <div class="tutorial_pic">
            <img src="<?php echo template_url("img/steps/icons/peer-review.png") ?>" width="100px" height="100px">
            <img src="<?php echo template_url("img/steps/big/peer-review.png") ?>" width="280px" height="280px">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="<?php echo $data["event"][0]->step ?>" type="checkbox" value="0" /> <?php echo __("do_not_show_tutorial")?></label>
            </div>
        </div>

        <div class="tutorial_content <?php echo "is_checker_page_help" ?>">
            <h3><?php echo __("peer-edit-l3_full")?></h3>
            <ul><?php echo __("peer-edit-l3_desc")?></ul>
        </div>
    </div>
</div>

<?php if(!empty($data["notes"])): ?>
    <div class="ttools_panel tn_tool panel panel-default" draggable="true">
        <div class="panel-heading">
            <h1 class="panel-title"><?php echo __("tn") ?></h1>
            <span class="panel-close glyphicon glyphicon-remove" data-tool="tn"></span>
        </div>

        <div class="ttools_content page-content panel-body">
            <div class="labels_list">
                <?php if(isset($data["notes"])): ?>
                    <?php foreach ($data["notes"] as $verse => $notes): ?>
                        <?php $chunkVerses = $data["notesVerses"][$verse]; ?>
                        <label>
                            <ul>
                                <li>
                                    <div class="word_term">
                                <span style="font-weight: bold;">
                                    <?php echo $chunkVerses > 0 ? __("verse_number", $chunkVerses) :
                                        __("intro")?>
                                </span>
                                    </div>
                                    <div class="word_def">
                                        <?php foreach ($notes as $note): ?>
                                            <?php echo  preg_replace('#<a.*?>(.*?)</a>#i', '<b>\1</b>', $note) ?>
                                        <?php endforeach; ?>
                                    </div>
                                </li>
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
    </div>
<?php endif; ?>

<script type="text/javascript" src="<?php echo template_url("js/diff_match_patch.js?2")?>"></script>
<script type="text/javascript" src="<?php echo template_url("js/diff.js?7")?>"></script>
<script>
    var disableHighlight = true;

    <?php if($data["isChecker"]): ?>
    $("#next_step").click(function (e) {
        renderConfirmPopup(Language.checkerConfirmTitle, Language.checkerConfirm,
            function () {
                $("#checker_submit").submit();
                $( this ).dialog("close");
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
    <?php endif; ?>

    $(document).ready(function() {
        $(".note_chunk").each(function(i, v) {
            $(".scripture_l2 span", this).each(function (i, v) {
                var verse = $(v).data("verse");

                var elm1 = $(v).text();
                var elm2 = $(".scripture_l3 span[data-verse="+verse+"]").text();
                var out = $(".scripture_compare span[data-verse="+verse+"]");

                if(typeof elm1 == "undefined") return true;

                diff_plain(elm1, elm2, out);
            });
        });

        $(".compare_scripture input").change(function () {
            var parent = $(this).parents(".note_chunk");
            var active = $(this).prop('checked');

            if (active) {
                $(".scripture_l3", parent).hide();
                $(".scripture_compare", parent).show();
            } else {
                $(".scripture_compare", parent).hide();
                $(".scripture_l3", parent).show();
            }
        });
    });
</script>