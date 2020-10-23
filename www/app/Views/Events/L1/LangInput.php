<?php

use Helpers\Constants\ChunkSections;
use Helpers\Constants\EventMembers;

if(isset($data["error"])) return;
?>
<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo __("step_num", ["step_number" => 1]) . ": " . __("multi-draft_lang_input")?></div>
    </div>

    <div class="">
        <div class="main_content">
            <form action="" method="post" id="main_form">
                <div class="main_content_text" dir="<?php echo $data["event"][0]->sLangDir ?>">
                    <h4><?php echo $data["event"][0]->tLang." - "
                            .__($data["event"][0]->bookProject)." - "
                        .($data["event"][0]->abbrID <= 39 ? __("old_test") : __("new_test"))." - "
                        ."<span class='book_name'>".$data["event"][0]->name." ".$data["currentChapter"]."</span>"?></h4>

                    <div class="row no_padding flex_container">
                        <div class="flex_left">
                            <?php foreach($data["text"] as $verse => $text): ?>
                                <p style="margin: 0 0 10px;" class="verse_p" data-verse="<?php echo $verse ?>"><?php echo "<strong><sup>".$verse."</sup></strong> ".$text; ?></p>
                            <?php endforeach; ?>
                        </div>

                        <div class="flex_middle lang_input_list">
                            <?php if(empty($data["translation"])): ?>
                                <?php foreach($data["text"] as $verse => $text): ?>
                                    <div class="lang_input_verse" data-verse="<?php echo $verse ?>">
                                        <textarea style="min-width: 400px;" name="verses[<?php echo $verse ?>]" class="textarea lang_input_ta"></textarea>
                                        <span class="vn"><?php echo $verse ?></span>
                                        <button class="delete_verse_ta btn btn-danger glyphicon glyphicon-remove" />
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php for($verse=1; $verse <= $data["translation"][sizeof($data["translation"])-1]["firstvs"]; $verse++): ?>
                                    <?php
                                    $text = "";
                                    $id = 0;
                                    foreach ($data["translation"] as $verses)
                                    {
                                        if($verses["firstvs"] == $verse)
                                        {
                                            $text = $verses[EventMembers::TRANSLATOR][ChunkSections::VERSES][$verse];
                                            $id = $verses["tID"];
                                            break;
                                        }
                                    }
                                    ?>
                                    <div class="lang_input_verse" data-verse="<?php echo $verse ?>" data-id="<?php echo $id ?>">
                                        <textarea style="min-width: 400px;" name="verses[<?php echo $verse ?>]" class="textarea lang_input_ta"><?php echo $text ?></textarea>
                                        <span class="vn"><?php echo $verse ?></span>
                                        <button class="delete_verse_ta btn btn-danger glyphicon glyphicon-remove" />
                                    </div>
                                <?php endfor; ?>
                            <?php endif; ?>

                            <button class="add_verse_ta btn btn-success glyphicon glyphicon-plus" />
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
            <div class="step_right alt"><?php echo __("step_num", ["step_number" => 1])?></div>
        </div>
    </div>
</div>

<div class="content_help closed">
    <div id="help_hide" class="glyphicon glyphicon-chevron-down"> <?php echo __("help") ?></div>

    <div class="help_float">
        <div class="help_info_steps">
            <div class="help_name_steps"><span><?php echo __("step_num", ["step_number" => 1])?>: </span><?php echo __("multi-draft_lang_input")?></div>
            <div class="help_descr_steps">
                <ul><?php echo __("multi-draft_lang_input_desc")?></ul>
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

        <div class="tr_tools"></div>
    </div>
</div>

<!-- Data for tools -->
<input type="hidden" id="targetLang" value="<?php echo $data["event"][0]->targetLang ?>">

<div class="tutorial_container">
    <div class="tutorial_popup">
        <div class="tutorial-close glyphicon glyphicon-remove"></div>
        <div class="tutorial_pic">
            <img src="<?php echo template_url("img/steps/icons/content-review.png") ?>" width="100px" height="100px">
            <img src="<?php echo template_url("img/steps/big/content-review.png") ?>" width="280px" height="280px">
            
        </div>

        <div class="tutorial_content">
            <h3><?php echo __("multi-draft_lang_input")?></h3>
            <ul><?php echo __("multi-draft_lang_input_desc")?></ul>
        </div>
    </div>
</div>

<script>
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