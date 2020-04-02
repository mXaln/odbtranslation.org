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
    <textarea dir="<?php echo $data["event"][0]->sLangDir ?>" style="overflow-x: hidden; word-wrap: break-word; overflow-y: visible;" class="textarea textarea_editor"></textarea>
    <div class="other_comments_list"></div>
    <img src="<?php echo template_url("img/loader.gif") ?>" class="commentEditorLoader">
</div>

<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo __("step_num", ["step_number" => 3]). ": " . __("self-check")?></div>
    </div>

    <div class="">
        <div class="main_content">
            <form action="" method="post" id="main_form">
                <div class="main_content_text row" style="padding-left: 15px">
                    <h4 dir="<?php echo $data["event"][0]->sLangDir ?>"><?php echo $data["event"][0]->tLang." - "
                            .__($data["event"][0]->bookProject)." - "
                            ."<span class='book_name'>".$data["event"][0]->name." - ".$data["text"][1]."</span>"?></h4>

                    <div class="col-sm-12 no_padding">
                        <?php foreach($data["chunks"] as $key => $chunk) : ?>
                            <div class="row flex_container chunk_block no_autosize">
                                <div class="chunk_verses flex_left" dir="<?php echo $data["event"][0]->sLangDir ?>">
                                    <?php foreach ($chunk as $verse): ?>
                                        <div class="verse_text" data-verse="<?php echo $verse; ?>">
                                            <?php
                                            $source = __("no_source_error");
                                            if(isset($data["text"][$verse]))
                                            {
                                                if(!is_array($data["text"][$verse]))
                                                {
                                                    $source = "<p class='verse_text_1'>{$data["text"][$verse]}</p>";
                                                }
                                                else
                                                {
                                                    $source = "<p class='verse_text_1'><strong>{$data["text"][$verse]["name"]}</strong></p>";
                                                    $source .= "<p class='verse_text_2'>{$data["text"][$verse]["text"]}</p>";
                                                }
                                            }
                                            ?>
                                            <?php echo $source; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="flex_middle editor_area" dir="<?php echo $data["event"][0]->tLangDir ?>">
                                    <div class="vnote verse_block" data-verse="<?php echo $verse; ?>">
                                        <?php
                                        $text1 = "";
                                        $text2 = "";
                                        $isTitle = $key == 0;
                                        if(isset($data["translation"][$key]))
                                        {
                                            if(!is_array($data["translation"][$key][EventMembers::TRANSLATOR]["verses"]))
                                            {
                                                $text1 = $data["translation"][$key][EventMembers::TRANSLATOR]["verses"];
                                            }
                                            else
                                            {
                                                $text1 = $data["translation"][$key][EventMembers::TRANSLATOR]["verses"]["name"];
                                                $text2 = $data["translation"][$key][EventMembers::TRANSLATOR]["verses"]["text"];
                                            }
                                        }
                                        ?>
                                        <?php if($isTitle): ?>
                                            <textarea name="chunks[<?php echo $key ?>]" class="peer_verse_ta textarea verse_text_1"><?php echo $text1 ?></textarea>
                                        <?php else: ?>
                                            <textarea name="chunks[<?php echo $key ?>][name]" class="peer_verse_ta textarea verse_text_1"><?php echo $text1 ?></textarea>
                                            <textarea name="chunks[<?php echo $key ?>][text]" class="peer_verse_ta textarea verse_text_2"><?php echo $text2 ?></textarea>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="flex_right">
                                    <div class="notes_tools">
                                        <span class="editComment mdi mdi-lead-pencil"
                                              data="<?php echo $data["currentChapter"].":".$key ?>"
                                              title="<?php echo __("write_note_title", [""])?>"></span>

                                        <div class="comments">
                                            <?php if(array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($key, $data["comments"][$data["currentChapter"]])): ?>
                                                <?php foreach($data["comments"][$data["currentChapter"]][$key] as $comment): ?>
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
                                </div>
                            </div>
                            <div class="chunk_divider"></div>
                        <?php endforeach; ?>
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
            <div class="step_right alt"><?php echo __("step_num", ["step_number" => 3])?></div>
        </div>
    </div>
</div>

<div class="content_help">
    <div id="help_hide" class="glyphicon glyphicon-chevron-down"> <?php echo __("help") ?></div>

    <div class="help_float">
        <div class="help_info_steps">
            <div class="help_name_steps"><span><?php echo __("step_num", ["step_number" => 3])?>:</span> <?php echo __("self-check")?></div>
            <div class="help_descr_steps">
                <ul><?php echo __("self-check_rad_desc")?></ul>
                <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div>
            </div>
        </div>

        <div class="event_info">
            <div class="participant_info">
                <div class="additional_info">
                    <a href="/events/information-rad/<?php echo $data["event"][0]->eventID ?>"><?php echo __("event_info") ?></a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="tutorial_container">
    <div class="tutorial_popup">
        <div class="tutorial-close glyphicon glyphicon-remove"></div>
        <div class="tutorial_pic">
            <img src="<?php echo template_url("img/steps/icons/self-check.png") ?>" width="100" height="100">
            <img src="<?php echo template_url("img/steps/big/self-check.png") ?>" width="280" height="280">
            
        </div>

        <div class="tutorial_content">
            <h3><?php echo __("self-check")?></h3>
            <ul><?php echo __("self-check_rad_desc")?></ul>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        setTimeout(function() {
            equal_verses_height();
        }, 2000);

        function equal_verses_height() {
            $(".verse_text").each(function() {
                var verse = $(this).data("verse");
                var source1 = $(this).find(".verse_text_1");
                var source2 = $(this).find(".verse_text_2");

                var target = $(".verse_block[data-verse="+verse+"]");
                var target1 = target.find(".verse_text_1");
                var target2 = target.find(".verse_text_2");

                if(source1.length > 0 && target1.length > 0) {
                    var source1_height = source1.outerHeight();
                    var target1_height = target1.outerHeight();
                    target1.outerHeight(Math.max(source1_height, target1_height));
                    source1.outerHeight(Math.max(source1_height, target1_height));
                }
                if(source2.length > 0 && target2.length > 0) {
                    var source2_height = source2.outerHeight();
                    var target2_height = target2.outerHeight();
                    target2.outerHeight(Math.max(source2_height, target2_height));
                    source2.outerHeight(Math.max(source2_height, target2_height));
                }
            });
        }
    });
</script>