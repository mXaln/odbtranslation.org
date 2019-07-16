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

<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo __("step_num", ["step_number" => 2]). ": " . __("self-check")?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <form action="" method="post" id="main_form">
                <div class="main_content_text row" style="padding-left: 15px">
                    <h4 dir="<?php echo $data["event"][0]->sLangDir ?>"><?php echo $data["event"][0]->tLang." - "
                            .__($data["event"][0]->bookProject)." - "
                            .($data["event"][0]->abbrID <= 39 ? __("old_test") : __("new_test"))." - "
                            ."<span class='book_name'>".$data["event"][0]->name." ".$data["currentChapter"].":1-".$data["chunks"][sizeof($data["chunks"])-1][0]."</span>"?></h4>

                    <div class="row no_padding">
                        <div class="col-sm-6">
                            <?php foreach($data["text"] as $verse => $text): ?>
                                <p style="margin: 0 0 5px;" class="verse_p" data-verse="<?php echo $verse ?>"><?php echo "<strong><sup>".$verse."</sup></strong> ".$text; ?></p>
                            <?php endforeach; ?>
                        </div>

                        <div class="col-sm-6 lang_input_list">
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
                                $text = isset($_POST["verses"]) && isset($_POST["verses"][$verse]) ? $_POST["verses"][$verse] : $text;
                                ?>
                                <div class="lang_input_verse" data-verse="<?php echo $verse ?>" data-id="<?php echo $id ?>">
                                    <textarea
                                            name="verses[<?php echo $verse ?>]"
                                            class="textarea lang_input_ta"
                                            style="width: 100%;"><?php echo $text ?></textarea>
                                    <span><?php echo $verse ?></span>

                                    <img class="editComment"
                                         data="<?php echo $data["currentChapter"].":".$chunk ?>"
                                         width="16"
                                         src="<?php echo template_url("img/edit.png") ?>"
                                         title="<?php echo __("write_note_title", [""])?>"
                                    />

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

        <div class="content_help col-sm-3">
            <div class="help_float">
                <div class="help_info_steps">
                    <div class="help_hide toggle-help glyphicon glyphicon-eye-close" title="<?php echo __("hide_help") ?>"></div>
                    <div class="help_title_steps"><?php echo __("help") ?></div>

                    <div class="clear"></div>

                    <div class="help_name_steps"><span><?php echo __("step_num", ["step_number" => 2])?>:</span> <?php echo __("self-check")?></div>
                    <div class="help_descr_steps">
                        <ul><?php echo __("self-check_desc")?></ul>
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
    </div>

    <div class="help_show toggle-help glyphicon glyphicon-question-sign" title="<?php echo __("show_help") ?>"></div>
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
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="<?php echo $data["event"][0]->step ?>" type="checkbox" value="0" /> <?php echo __("do_not_show_tutorial")?></label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo __("self-check")?></h3>
            <ul><?php echo __("self-check_desc")?></ul>
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