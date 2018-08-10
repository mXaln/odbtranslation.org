<?php
use Helpers\Constants\EventMembers;
use Helpers\Parsedown;

if(isset($data["error"])) return;
?>

<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo __("step_num", [1]). ": " . __("multi-draft")?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <form action="" method="post" id="main_form">
                <div class="main_content_text row" style="padding-left: 15px">
                    <h4 dir="<?php echo $data["event"][0]->sLangDir ?>"><?php echo $data["event"][0]->tLang." - "
                            .__($data["event"][0]->bookProject)." - "
                            .($data["event"][0]->abbrID <= 39 ? __("old_test") : __("new_test"))." - "
                            ."<span class='book_name'>".$data["event"][0]->name
                            ." [".$data["group"][0]."...".$data["group"][sizeof($data["group"])-1]."]</span>"?></h4>

                    <div class="col-sm-12 no_padding questions_bd">
                        <div style="color: red; font-size: 22px; font-weight: bold; margin: 20px 0">
                            <?php echo __("tw_translate_hint") ?>
                        </div>

                        <?php foreach($data["chunks"] as $chunkNo => $chunk): ?>
                        <div class="parent_q questions_chunk"
                             data-verse="<?php echo $chunkNo ?>"
                             data-chapter="<?php echo $data["currentChapter"] ?>"
                             data-event="<?php echo $data["event"][0]->eventID ?>">
                            <div class="row">
                                <div class="row buttons_chunk">
                                    <div class="col-md-4" style="color: #00a74d; font-weight: bold;">
                                        <?php //echo $data["words"][$chunkNo]["word"] ?>
                                    </div>
                                    <div class="col-md-8">
                                        <label><?php echo __("consume") ?>
                                            <input class="consume_q" type="checkbox"></label>
                                        &nbsp;&nbsp;
                                        <label><?php echo __("verbalize") ?>
                                            <input class="verbalize_q" type="checkbox" disabled></label>
                                        &nbsp;&nbsp;
                                        <label><?php echo __("draft") ?>
                                            <input class="draft_q" type="checkbox" disabled></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 question_content" dir="<?php echo $data["event"][0]->resLangDir ?>">
                                <?php
                                $source = $data["words"][$chunkNo]["text"];
                                $source = preg_replace("/(title=\"([^\"]+)\")/", "", $source);
                                $source = preg_replace("/(href=\"([^\"]+)\")/", "$1 title='$2'", $source);
                                echo $source
                                ?>
                            </div>
                            <div class="col-md-6 questions_editor font_<?php echo $data["event"][0]->targetLang ?>"
                                 dir="<?php echo $data["event"][0]->tLangDir ?>" style="min-height: 100px">
                                <?php
                                $parsedown = new Parsedown();
                                $text = isset($data["translation"][$chunkNo])
                                    ? $parsedown->text($data["translation"][$chunkNo][EventMembers::TRANSLATOR]["verses"])
                                    : "";
                                $text = isset($_POST["chunks"]) && isset($_POST["chunks"][$chunkNo])
                                    ? $_POST["chunks"][$chunkNo]
                                    : $text;
                                ?>
                                <textarea
                                        name="chunks[<?php echo $chunkNo ?>]"
                                        class="add_questions_editor blind_ta draft_question"><?php echo $text ?></textarea>
                            </div>
                            <div class="chunk_divider col-sm-12" style="margin-top: 10px"></div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                </div>

                <div class="main_content_footer row">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo __("confirm_yes")?></label>
                    </div>

                    <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo __("next_step")?></button>
                </div>
            </form>
            <div class="step_right alt"><?php echo __("step_num", [1])?></div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps">
                <div class="help_hide toggle-help glyphicon glyphicon-eye-close" title="<?php echo __("hide_help") ?>"></div>
                <div class="help_title_steps"><?php echo __("help") ?></div>

                <div class="clear"></div>

                <div class="help_name_steps"><span><?php echo __("step_num", [1])?>:</span> <?php echo __("multi-draft")?></div>
                <div class="help_descr_steps">
                    <ul><?php echo __("multi-draft_tw_desc")?></ul>
                    <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div>
                </div>
            </div>

            <div class="event_info">
                <div class="participant_info">
                    <div class="additional_info">
                        <a href="/events/information-tw/<?php echo $data["event"][0]->eventID ?>"><?php echo __("event_info") ?></a>
                    </div>
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
            <img src="<?php echo template_url("img/steps/icons/content-review.png") ?>" width="100" height="100">
            <img src="<?php echo template_url("img/steps/big/content-review.png") ?>" width="280" height="280">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="<?php echo $data["event"][0]->step ?>" type="checkbox" value="0" /> <?php echo __("do_not_show_tutorial")?></label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo __("multi-draft")?></h3>
            <ul><?php echo __("multi-draft_tw_desc")?></ul>
        </div>
    </div>
</div>

<script>
    (function () {
        $("#main_form").submit(function (e) {
            var drafts = $(".draft_q:not(:checked)");

            if(drafts.length > 0)
            {
                renderPopup(Language.tq_drafts_not_checked);

                e.preventDefault();
                return false;
            }

            return true;
        });
    })()
</script>