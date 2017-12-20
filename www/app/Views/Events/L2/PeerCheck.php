<?php
if(isset($data["error"])) return;

use Helpers\Constants\EventMembers;
?>
<div class="editor">
    <div class="comment_div panel panel-default" dir="<?php echo $data["event"][0]->tLangDir ?>">
        <div class="panel-heading">
            <h1 class="panel-title"><?php echo __("write_note_title")?></h1>
            <span class="editor-close btn btn-success" data-level="2"><?php echo __("save") ?></span>
        </div>
        <textarea class="textarea textarea_editor"></textarea>
        <div class="other_comments_list <?php echo $data["event"][0]->tLangDir?>"></div>
        <img src="<?php echo template_url("img/loader.gif") ?>" class="commentEditorLoader">
    </div>
</div>

<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo __("step_num", array(5)) . ": " . __("peer-review-l2")?></div>
    </div>

    <div class="row" style="position: relative">
        <button class="btn btn-warning toggle-help" data-mode="l2alt"><?php echo __("hide_help") ?></button>
        <div class="main_content col-sm-9">
            <?php if($data["event"][0]->peer == 1 && $data["event"][0]->checkerID == 0): ?>
                <div class="alert alert-success check_request"><?php echo __("check_request_sent_success") ?></div>
            <?php endif; ?>
            <form action="" id="<?php echo $data["event"][0]->peer == 1 ? "main_form" : "checker_submit" ?>" method="post" >
                <div class="main_content_text" dir="<?php echo $data["event"][0]->sLangDir ?>">
                    <h4><?php echo $data["event"][0]->tLang." - "
                            .__($data["event"][0]->bookProject)." - "
                        .($data["event"][0]->abbrID <= 39 ? __("old_test") : __("new_test"))." - "
                        ."<span class='book_name'>".$data["event"][0]->name." ".$data["currentChapter"].":1-".$data["totalVerses"]."</span>"?></h4>

                    <ul class="nav nav-tabs">
                        <li role="presentation" id="source_scripture" class="my_tab">
                            <a href="#"><?php echo __("source_text") ?></a>
                        </li>
                        <li role="presentation" id="target_scripture" class="my_tab">
                            <a href="#"><?php echo __("target_text") ?></a>
                        </li>
                    </ul>

                    <div id="source_scripture_content" class="my_content shown">
                        <?php foreach($data["chunks"] as $key => $chunk) : ?>
                        <div class=" chunk_block">
                            <div class="chunk_verses" dir="<?php echo $data["event"][0]->sLangDir ?>">
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
                                <div>
                                    <strong dir="<?php echo $data["event"][0]->sLangDir ?>"
                                            class="<?php echo $data["event"][0]->sLangDir ?>">
                                        <sup><?php echo $verse; ?></sup>
                                    </strong>
                                    <div class="<?php echo "kwverse_".$data["currentChapter"]."_".$key."_".$verse ?>"
                                         dir="<?php echo $data["event"][0]->sLangDir ?>">
                                        <?php echo $data["text"][$verse]; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div id="target_scripture_content" class="my_content">
                        <div class="no_padding">
                            <?php foreach($data["chunks"] as $key => $chunk) : ?>
                                <div class="row chunk_block">
                                    <div class="chunk_verses col-sm-6" dir="<?php echo $data["event"][0]->sLangDir ?>">
                                        <?php $verses = $data["translation"][$key][EventMembers::TRANSLATOR]["verses"]; ?>
                                        <?php foreach ($verses as $verse => $text): ?>
                                        <p>
                                            <strong class="<?php echo $data["event"][0]->sLangDir ?>">
                                                <sup><?php echo $verse; ?></sup>
                                            </strong>
                                            <span class="orig_text" data-orig-verse="<?php echo $verse ?>">
                                                <?php echo $text; ?>
                                            </span>
                                        </p>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="col-sm-6 editor_area" dir="<?php echo $data["event"][0]->tLangDir ?>">
                                        <?php
                                        $verses = $data["translation"][$key][EventMembers::L2_CHECKER]["verses"];
                                        ?>
                                        <div class="vnote">
                                            <?php foreach($verses as $verse => $text): ?>
                                                <div class="verse_block">
                                                    <?php if($data["event"][0]->peer == 1): ?>
                                                        <span class="verse_number_l2"><?php echo $verse?></span>
                                                        <textarea name="chunks[<?php echo $key ?>][<?php echo $verse ?>]"
                                                                  class="peer_verse_ta textarea"
                                                                  data-orig-verse="<?php echo $verse ?>"  >
                                                        <?php echo $text; ?></textarea>
                                                    <?php else: ?>
                                                        <p>
                                                            <strong><sup><?php echo $verse?></sup></strong>
                                                            <span class="targetVerse" data-orig-verse="<?php echo $verse ?>"><?php echo $text; ?></span>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>

                                            <?php $hasComments = array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($key, $data["comments"][$data["currentChapter"]]); ?>
                                            <div class="comments_number <?php echo $hasComments ? "hasComment" : "" ?>">
                                                <?php echo $hasComments ? sizeof($data["comments"][$data["currentChapter"]][$key]) : ""?>
                                            </div>
                                            <img class="editComment" data="<?php echo $data["currentChapter"].":".$key ?>" width="16" src="<?php echo template_url("img/edit.png") ?>" title="<?php echo __("write_note_title", [""])?>"/>

                                            <div class="comments">
                                                <?php if(array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($key, $data["comments"][$data["currentChapter"]])): ?>
                                                    <?php foreach($data["comments"][$data["currentChapter"]][$key] as $comment): ?>
                                                        <?php if($comment->memberID == $data["event"][0]->myChkMemberID
                                                            && $comment->level == 2): ?>
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
                                </div>
                                <div class="chunk_divider col-sm-12"></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="main_content_footer">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo __("confirm_yes")?></label>
                    </div>

                    <input type="hidden" name="level" value="l2continue">
                    <input type="hidden" name="chapter" value="<?php echo $data["event"][0]->currentChapter ?>">
                    <input type="hidden" name="memberID" value="<?php echo $data["event"][0]->l2memberID ?>">
                    <button id="next_step" type="submit" name="submit_chk" class="btn btn-primary" disabled><?php echo __("next_step")?></button>
                </div>
            </form>
            <div class="step_right alt"><?php echo __("step_num", [5])?></div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps">
                <div class="help_title_steps"><?php echo __("help") ?></div>

                <div class="clear"></div>

                <div class="help_name_steps">
                    <span><?php echo __("step_num", [5])?>: </span>
                    <?php echo __("peer-review-l2")?>
                </div>
                <div class="help_descr_steps">
                    <ul><?php echo __("peer-review-l2_desc")?></ul>
                    <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div>
                </div>
            </div>

            <div class="event_info">
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
                        <a href="/events/information-l2/<?php echo $data["event"][0]->eventID ?>"><?php echo __("event_info") ?></a>
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
            <img src="<?php echo template_url("img/steps/icons/peer-review.png") ?>" width="100px" height="100px">
            <img src="<?php echo template_url("img/steps/big/peer-review.png") ?>" width="280px" height="280px">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="<?php echo $data["event"][0]->step ?>" type="checkbox" value="0" /> <?php echo __("do_not_show_tutorial")?></label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo __("peer-review-l2")?></h3>
            <ul><?php echo __("peer-review-l2_desc")?></ul>
        </div>
    </div>
</div>

<script type="text/javascript" src="<?php echo template_url("js/diff_match_patch.js")?>"></script>
<script type="text/javascript" src="<?php echo template_url("js/diff.js?4")?>"></script>
<script>
    (function() {
        $(".my_tab").click(function () {
            var inter = setInterval(function() {
                if($("#target_scripture_content").is(":visible"))
                {
                    if(typeof autosize == "function")
                        autosize.update($('textarea'));
                    clearInterval(inter);
                }
            }, 10);
            return false;
        });

        $(".orig_text").each(function() {
            var verse = $(this).data("orig-verse");
            var chkVersion = $("textarea[data-orig-verse='"+verse+"']");
            var chkText;
            if(chkVersion.length == 0)
            {
                chkVersion = $("span.targetVerse[data-orig-verse='"+verse+"']");
                chkText = chkVersion.text();
            }
            else
            {
                chkText = chkVersion.val();
            }

            diff_plain($(this).text(), chkText, $(this));
        });
    })();

    isLevel2 = true;
</script>
<?php if($data["event"][0]->peer == 2): ?>
<script>
    isChecker = true;
    disableHighlight = true;
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
</script>
<?php endif; ?>