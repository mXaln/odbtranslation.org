<?php
if(isset($data["error"])) return;

use Helpers\Constants\EventMembers;
?>
<div class="comment_div panel panel-default">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo __("write_note_title")?></h1>
        <span class="editor-close btn btn-success" data-level="2"><?php echo __("save") ?></span>
        <span class="xbtn glyphicon glyphicon-remove"></span>
    </div>
    <textarea dir="<?php echo $data["event"][0]->sLangDir ?>" style="overflow-x: hidden; word-wrap: break-word; overflow-y: visible;" class="textarea textarea_editor"></textarea>
    <div class="other_comments_list"></div>
    <img src="<?php echo template_url("img/loader.gif") ?>" class="commentEditorLoader">
</div>

<div class="footnote_editor panel panel-default">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo __("write_footnote_title")?></h1>
        <span class="footnote-editor-close btn btn-success"><?php echo __("save") ?></span>
        <span class="xbtnf glyphicon glyphicon-remove"></span>
    </div>
    <div class="footnote_window">
        <div class="fn_preview"></div>
        <div class="fn_buttons" dir="<?php echo $data["event"][0]->sLangDir ?>">
            <!--<button class="btn btn-default" data-fn="fr" title="footnote text">fr</button>-->
            <button class="btn btn-default" data-fn="ft" title="footnote text">ft</button>
            <!--<button class="btn btn-default" data-fn="fq" title="footnote translation quotation">fq</button>-->
            <button class="btn btn-default" data-fn="fqa" title="footnote alternate translation">fqa</button>
            <!--<button class="btn btn-default" data-fn="fk" title="footnote keyword">fk</button>-->
            <!--<button class="btn btn-default" data-fn="fl" title="footnote label text">fl</button>-->
            <!--<button class="btn btn-link" data-fn="link">Footnotes Specification</button>-->
        </div>
        <div class="fn_builder"></div>
    </div>
</div>

<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo __("step_num", ["step_number" => 2]) . ": " . __("fst-check_full")?></div>
    </div>

    <div class="row" style="position: relative">
        <div class="main_content col-sm-9">
            <form action="" id="main_form" method="post" >
                <div class="main_content_text" dir="<?php echo $data["event"][0]->sLangDir ?>">
                    <h4><?php echo $data["event"][0]->tLang." - "
                            .__($data["event"][0]->bookProject)." - "
                        .($data["event"][0]->abbrID <= 39 ? __("old_test") : __("new_test"))." - "
                        ."<span class='book_name'>".$data["event"][0]->name." ".$data["currentChapter"].":1-".$data["totalVerses"]."</span>"?></h4>

                    <div class="no_padding">
                        <?php foreach($data["chunks"] as $key => $chunk) : ?>
                            <div class="row chunk_block no_autosize">
                                <div class="flex_container">
                                    <div class="chunk_verses flex_left" dir="<?php echo $data["event"][0]->sLangDir ?>">
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
                                            <p class="verse_text" data-verse="<?php echo $verse ?>">
                                                <strong class="<?php echo $data["event"][0]->sLangDir ?>">
                                                    <sup><?php echo $verse; ?></sup>
                                                </strong>
                                                <?php echo $data["text"][$verse]; ?>
                                            </p>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="flex_middle editor_area" dir="<?php echo $data["event"][0]->tLangDir ?>">
                                        <?php
                                        if(!empty($_POST["chunks"][$key]))
                                            $verses = $_POST["chunks"][$key];
                                        else if(!empty($data["translation"][$key][EventMembers::L2_CHECKER]["verses"]))
                                            $verses = $data["translation"][$key][EventMembers::L2_CHECKER]["verses"];
                                        else
                                            $verses = $data["translation"][$key][EventMembers::TRANSLATOR]["verses"];
                                        ?>
                                        <div class="vnote">
                                            <?php foreach($verses as $verse => $text): ?>
                                                <div class="verse_block flex_container" data-verse="<?php echo $verse ?>">
                                                    <span class="verse_number_l2"><?php echo $verse?></span>
                                                    <textarea name="chunks[<?php echo $key ?>][<?php echo $verse ?>]"
                                                              class="peer_verse_ta textarea" style="min-width: 400px"><?php echo $text; ?></textarea>

                                                    <span class="editFootNote mdi mdi-bookmark"
                                                          style="margin-top: -5px"
                                                          title="<?php echo __("write_footnote_title") ?>"></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="flex_right">
                                        <div class="notes_tools">
                                            <?php $hasComments = array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($key, $data["comments"][$data["currentChapter"]]); ?>
                                            <div class="comments_number flex_commn_number <?php echo $hasComments ? "hasComment" : "" ?>">
                                                <?php echo $hasComments ? sizeof($data["comments"][$data["currentChapter"]][$key]) : ""?>
                                            </div>
                                            <span class="editComment mdi mdi-lead-pencil"
                                                  data="<?php echo $data["currentChapter"].":".$key ?>"
                                                  title="<?php echo __("write_note_title", [""])?>"></span>

                                            <div class="comments">
                                                <?php if(array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($key, $data["comments"][$data["currentChapter"]])): ?>
                                                    <?php foreach($data["comments"][$data["currentChapter"]][$key] as $comment): ?>
                                                        <?php if($comment->memberID == $data["event"][0]->memberID
                                                            && $comment->level == 2): ?>
                                                            <div class="my_comment"><?php echo $comment->text; ?></div>
                                                        <?php else: ?>
                                                            <div class="other_comments">
                                                                <?php echo
                                                                    "<span>".$comment->firstName." ".mb_substr($comment->lastName, 0, 1).". 
                                                                    - L".$comment->level.":</span> 
                                                                ".$comment->text; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="chunk_divider"></div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="main_content_footer">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo __("confirm_yes")?></label>
                    </div>

                    <input type="hidden" name="level" value="l2">
                    <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo __("continue")?></button>
                    <img src="<?php echo template_url("img/saving.gif") ?>" class="unsaved_alert">
                </div>
            </form>
            <div class="step_right alt"><?php echo __("step_num", ["step_number" => 2])?></div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_float">
                <div class="help_info_steps">
                    <div class="help_hide toggle-help glyphicon glyphicon-eye-close"
                         title="<?php echo __("hide_help") ?>"></div>
                    <div class="help_title_steps"><?php echo __("help") ?></div>

                    <div class="clear"></div>

                    <div class="help_name_steps"><span><?php echo __("step_num", ["step_number" => 2])?>: </span><?php echo __("fst-check")?></div>
                    <div class="help_descr_steps">
                        <ul><?php echo __("fst-check_desc")?></ul>
                        <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div>
                    </div>
                </div>

                <div class="event_info">
                    <div class="participant_info">
                        <div class="additional_info">
                            <a href="/events/information-l2/<?php echo $data["event"][0]->eventID ?>"><?php echo __("event_info") ?></a>
                        </div>
                    </div>
                </div>

                <div class="tr_tools">
                    <button class="btn btn-primary ttools" data-tool="tn"><?php echo __("show_notes") ?></button>
                    <button class="btn btn-primary ttools" data-tool="tq"><?php echo __("show_questions") ?></button>
                    <button class="btn btn-primary ttools" data-tool="tw"><?php echo __("show_keywords") ?></button>
                    <button class="btn btn-warning ttools" data-tool="rubric"><?php echo __("show_rubric") ?></button>
                </div>
            </div>
        </div>
    </div>

    <div class="help_show toggle-help glyphicon glyphicon-question-sign"
         data-mode="l2"
         title="<?php echo __("show_help") ?>"></div>
</div>

<!-- Data for tools -->
<input type="hidden" id="bookCode" value="<?php echo $data["event"][0]->bookCode ?>">
<input type="hidden" id="chapter" value="<?php echo $data["event"][0]->currentChapter ?>">
<input type="hidden" id="tn_lang" value="<?php echo $data["event"][0]->tnLangID ?>">
<input type="hidden" id="tq_lang" value="<?php echo $data["event"][0]->tqLangID ?>">
<input type="hidden" id="tw_lang" value="<?php echo $data["event"][0]->twLangID ?>">
<input type="hidden" id="totalVerses" value="<?php echo $data["totalVerses"] ?>">
<input type="hidden" id="targetLang" value="<?php echo $data["event"][0]->targetLang ?>">

<div class="tutorial_container">
    <div class="tutorial_popup">
        <div class="tutorial-close glyphicon glyphicon-remove"></div>
        <div class="tutorial_pic">
            <img src="<?php echo template_url("img/steps/icons/fst-check.png") ?>" width="100px" height="100px">
            <img src="<?php echo template_url("img/steps/big/consume.png") ?>" width="280px" height="280px">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="<?php echo $data["event"][0]->step ?>" type="checkbox" value="0" /> <?php echo __("do_not_show_tutorial")?></label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo __("fst-check_full")?></h3>
            <ul><?php echo __("fst-check_desc")?></ul>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        setTimeout(function() {
            equal_verses_height();
        }, 3000);

        $(".peer_verse_ta").blur(function() {
            equal_verses_height();
        });

        function equal_verses_height() {
            $(".verse_text").each(function() {
                var verse = $(this).data("verse");
                var p_height = $(this).outerHeight();
                var ta = $(".verse_block[data-verse="+verse+"] textarea");

                if(ta.length > 0) {
                    var t_height = ta.outerHeight();
                    ta.outerHeight(Math.max(p_height, t_height));
                    $(this).outerHeight(Math.max(p_height, t_height));
                }
            });
        }

        $(".peer_verse_ta").highlightWithinTextarea({
            highlight: /\\f\s[+-]\s(.*?)\\f\*/gi
        });
    });
</script>