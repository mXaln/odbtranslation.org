<?php
use \Helpers\Constants\EventMembers;
use \Helpers\Parsedown;

if(isset($data["error"])) return;
?>
<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo __("step_num", ["step_number" => 3]) . ": " . __("blind-draft_tn")?></div>
    </div>

    <div class="row" style="position: relative">
        <div class="main_content col-sm-9">
            <form action="" id="main_form" method="post">
                <div class="main_content_text">

                <h4><?php echo $data["event"][0]->sLang." - "
                            .__($data["event"][0]->bookProject)." - "
                            .($data["event"][0]->abbrID <= 39 ? __("old_test") : __("new_test"))." - "
                            ."<span class='book_name'>".$data["event"][0]->name." "
                                .($data["currentChapter"] > 0 ? $data["currentChapter"].":"
                                .(!$data["no_chunk_source"]
                                    ? ($data["chunk"][0] != $data["chunk"][sizeof($data["chunk"])-1]
                                        ? $data["chunk"][0]."-".$data["chunk"][sizeof($data["chunk"])-1]
                                        : $data["chunk"][0])
                                    : " ".__("intro")) : __("front"))."</span>"?></h4>

                    <?php if(isset($data["no_chunk_source"]) && !$data["no_chunk_source"]): ?>
                        <div class="scripture_chunk" dir="<?php echo $data["event"][0]->sLangDir ?>">
                            <?php foreach($data["text"] as $verse => $text): ?>
                                <p>
                                    <strong><sup><?php echo $verse ?></sup></strong>
                                    <?php echo $text; ?>
                                </p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <ul class="nav nav-tabs">
                        <li role="presentation" id="my_read_chunk" class="my_tab">
                            <a href="#"><?php echo __("read_chunk") ?></a>
                        </li>
                        <li role="presentation" id="my_translate_chunk" class="my_tab">
                            <a href="#"><?php echo __("translate_chunk") ?></a>
                        </li>
                    </ul>

                    <div id="my_read_chunk_content" class="my_content shown" dir="<?php echo $data["event"][0]->resLangDir ?>">
                        <?php foreach($data["notes"] as $note): ?>
                            <div class="note_content" id="read_chunk_verse">
                                <?php echo preg_replace(
                                    "/(\[\[[a-z:\/\-]+\]\])/",
                                    "<span class='uwlink' title='".__("leaveit")."'>$1</span>",
                                    $note) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>


                    <div id="my_translate_chunk_content" class="my_content" dir="<?php echo $data["event"][0]->tLangDir ?>">
                        <?php $key=$data["chunk"][0]; //foreach($data["notes"] as $key => $note): ?>
                            <div class="notes_editor font_<?php echo $data["event"][0]->targetLang ?>"
                                 dir="<?php echo $data["event"][0]->tLangDir ?>">
                                <?php
                                $parsedown = new Parsedown();
                                $text = isset($data["translation"])
                                    ? preg_replace(
                                        "/(\[\[[a-z:\/\-]+\]\])/",
                                        "<span class='uwlink' title='".__("leaveit")."'>$1</span>",
                                        $parsedown->text($data["translation"]))
                                    : "";
                                $text = isset($_POST["draft"]) && isset($_POST["draft"])
                                    ? $_POST["draft"]
                                    : $text
                                ?>
                                <textarea
                                        name="draft"
                                        class="add_notes_editor blind_ta" data-key="verse"><?php echo $text ?></textarea>
                            </div>
                        <?php //endforeach; ?>
                    </div>
                </div>

                <div class="main_content_footer row">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo __("confirm_yes")?></label>
                    </div>
                    <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo __("next_step")?></button>
                    <img src="<?php echo template_url("img/saving.gif") ?>" class="unsaved_alert" style="float:none">
                </div>
            </form>
            <div class="step_right alt"><?php echo __("step_num", ["step_number" => 3])?></div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_float">
                <div class="help_info_steps<?php echo $data["isCheckerPage"] ? " is_checker_page_help" : "" ?>">
                    <div class="help_hide toggle-help glyphicon glyphicon-eye-close" title="<?php echo __("hide_help") ?>"></div>
                    <div class="help_title_steps"><?php echo __("help") ?></div>

                    <div class="clear"></div>

                    <div class="help_name_steps"><span><?php echo __("step_num", ["step_number" => 3])?>: </span><?php echo __("blind-draft_tn")?></div>
                    <div class="help_descr_steps">
                        <ul><?php echo __("blind-draft_tn_desc")?></ul>
                        <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div>
                    </div>
                </div>

                <div class="event_info<?php echo $data["isCheckerPage"] ? " is_checker_page_help" : "" ?>">
                    <div class="participant_info">
                        <div class="additional_info">
                            <a href="/events/information-tn/<?php echo $data["event"][0]->eventID ?>"><?php echo __("event_info") ?></a>
                        </div>
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
            <img src="<?php echo template_url("img/steps/icons/blind-draft.png") ?>" width="100px" height="100px">
            <img src="<?php echo template_url("img/steps/big/blind-draft.png") ?>" width="280px" height="280px">
            <div class="hide_tutorial">
                <label>
                    <input id="hide_tutorial" 
                        data="<?php echo $data["event"][0]->step ?>" 
                        type="checkbox" value="0" /> 
                            <?php echo __("do_not_show_tutorial")?>
                </label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo __("blind-draft_tn")?></h3>
            <ul><?php echo __("blind-draft_tn_desc")?></ul>
        </div>
    </div>
</div>

<script>
    $(function() {
        $("#my_translate_chunk").click(function () {
            $(".add_notes_editor").each(function() {
                var key = $(this).data("key");
                var noteContent = $("#read_chunk_" + key);
                var height = noteContent.actual("height");
                var parent = $(this).parents(".notes_editor");

                setTimeout(function () {
                    parent.css("min-height", height);
                }, 10);
            })
        });
    })
</script>