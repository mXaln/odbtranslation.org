<?php
use Helpers\Constants\EventMembers;

if(isset($data["error"])) return;
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
        <div class="main_content_title"><?php echo __("step_num", ["step_number" => 2]). ": " . __("self-check")?></div>
    </div>

    <div class="">
        <div class="main_content">
            <form action="" method="post" id="main_form">
                <div class="main_content_text row" style="padding-left: 15px" dir="<?php echo $data["event"][0]->sLangDir ?>">
                    <h4 dir="<?php echo $data["event"][0]->sLangDir ?>"><?php echo $data["event"][0]->tLang." - "
                            .__($data["event"][0]->bookProject)." - "
                            .($data["event"][0]->abbrID <= 39 ? __("old_test") : __("new_test"))." - "
                            ."<span class='book_name'>".$data["event"][0]->name." ".$data["currentChapter"].":1-".$data["chunks"][sizeof($data["chunks"])-1][0]."</span>"?></h4>

                    <div class="flex_container no_padding">
                        <div class="flex_left">
                            <?php foreach($data["text"] as $verse => $text): ?>
                                <p style="margin: 0 0 10px;" class="verse_p" data-verse="<?php echo $verse ?>"><?php echo "<strong><sup>".$verse."</sup></strong> ".$text; ?></p>
                            <?php endforeach; ?>
                        </div>

                        <div class="flex_middle lang_input_list">
                            <?php for($verse=1; $verse <= $data["translation"][sizeof($data["translation"])-1]["firstvs"]; $verse++): ?>
                                <?php
                                $text = "";
                                $id = 0;
                                $chunk = $verse-1;
                                foreach ($data["translation"] as $verses)
                                {
                                    if($verses["firstvs"] == $verse)
                                    {
                                        $text = $verses["translator"]["verses"][$verse];
                                        $id = $verses["tID"];
                                        break;
                                    }
                                }
                                ?>
                                <div class="lang_input_verse flex_chunk" data-verse="<?php echo $verse ?>" data-id="<?php echo $id ?>">
                                    <textarea
                                            name="verses[<?php echo $verse ?>]"
                                            class="textarea lang_input_ta"><?php echo $text ?></textarea>
                                    <span class="vn"><?php echo $verse ?></span>

                                    <div class="notes_tools">
                                        <span class="editComment mdi mdi-lead-pencil"
                                              data="<?php echo $data["currentChapter"].":".$chunk ?>"
                                              title="<?php echo __("write_note_title", [""])?>"></span>

                                        <div class="comments">
                                            <?php if(array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($chunk, $data["comments"][$data["currentChapter"]])): ?>
                                                <?php foreach($data["comments"][$data["currentChapter"]][$chunk] as $comment): ?>
                                                    <?php if($comment->memberID == $data["event"][0]->myMemberID): ?>
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

                                        <span class="editFootNote mdi mdi-bookmark" title="<?php echo __("write_footnote_title") ?>"></span>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <div class="main_content_footer row">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo __("confirm_yes")?></label>
                    </div>

                    <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo __("next_step")?></button>
                    <img src="<?php echo template_url("img/saving.gif") ?>" class="unsaved_alert">
                </div>
            </form>
            <div class="step_right alt"><?php echo __("step_num", ["step_number" => 2])?></div>
        </div>
    </div>
</div>

<div class="content_help closed">
    <div id="help_hide" class="glyphicon glyphicon-chevron-down"> <?php echo __("help") ?></div>

    <div class="help_float">
        <div class="help_info_steps">
            <div class="help_name_steps"><span><?php echo __("step_num", ["step_number" => 2])?>:</span> <?php echo __("self-check")?></div>
            <div class="help_descr_steps">
                <ul><?php echo __("self-check_lang_input_desc")?></ul>
                <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div>
            </div>
        </div>

        <div class="event_info">
            <div class="participant_info">
                <div class="additional_info">
                    <a href="/events/information/<?php echo $data["event"][0]->eventID ?>"><?php echo __("event_info") ?></a>
                </div>
            </div>
        </div>
    </div>
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
            <img src="<?php echo template_url("img/steps/icons/self-check.png") ?>" width="100" height="100">
            <img src="<?php echo template_url("img/steps/big/self-check.png") ?>" width="280" height="280">
            
        </div>

        <div class="tutorial_content">
            <h3><?php echo __("self-check")?></h3>
            <ul><?php echo __("self-check_lang_input_desc")?></ul>
        </div>
    </div>
</div>


<script>
    <?php if(isset($data["words"])): ?>
        var almaWords = <?php echo $data["words"] ?>;
    <?php endif; ?>

    $(document).ready(function() {
        setTimeout(function() {
            equal_verses_height();
        }, 3000);

        $(".lang_input_ta").blur(function() {
            equal_verses_height();
        });

        $(".lang_input_ta").highlightWithinTextarea({
            highlight: /\\f\s[+-]\s(.*?)\\f\*/gi
        });
    });

    function equal_verses_height() {
        $(".verse_p").each(function() {
            var verse = $(this).data("verse");
            var p_height = $(this).outerHeight();
            var ta = $(".lang_input_verse[data-verse="+verse+"] textarea");

            if(ta.length > 0) {
                var t_height = ta.outerHeight();
                ta.outerHeight(Math.max(p_height, t_height));
                $(this).outerHeight(Math.max(p_height, t_height));
            }
        });
    }
</script>