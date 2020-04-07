<?php
use Helpers\Constants\EventMembers;
use Helpers\Session;

if(isset($error)) return;
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
        <div class="main_content_title">
            <div><?php echo __("step_num", ["step_number" => 4]). ": " . __("peer-review")?></div>
            <div class="action_type type_checking <?php echo isset($data["isPeerPage"]) ? "isPeer" : "" ?>">
                <?php echo __("type_checking2"); ?>
            </div>
        </div>
    </div>

    <div class="">
        <div class="main_content">
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

                                    $translation = isset($data["translation"][$key][EventMembers::CHECKER])
                                    && !empty($data["translation"][$key][EventMembers::CHECKER]["verses"])
                                        ? $data["translation"][$key][EventMembers::CHECKER]["verses"]
                                        : $data["translation"][$key][EventMembers::TRANSLATOR]["verses"];

                                    if(!is_array($translation))
                                    {
                                        $text1 = $translation;
                                    }
                                    else
                                    {
                                        $text1 = $translation["name"];
                                        $text2 = $translation["text"];
                                    }
                                    ?>
                                    <?php if($isTitle): ?>
                                        <p class="verse_text_1"><?php echo $text1 ?></p>
                                    <?php else: ?>
                                        <p class="verse_text_1"><strong><?php echo $text1 ?></strong></p>
                                        <p class="verse_text_2"><?php echo $text2 ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex_right">
                                <div class="notes_tools">
                                    <?php
                                    $hasComments = array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($key, $data["comments"][$data["currentChapter"]]);
                                    ?>
                                    <div class="comments_number tncomm flex_commn_number <?php echo $hasComments ? "hasComment" : "" ?>">
                                        <?php echo $hasComments ? sizeof($data["comments"][$data["currentChapter"]][$key]) : ""?>
                                    </div>
                                    <span class="editComment mdi mdi-lead-pencil"
                                          data="<?php echo $data["currentChapter"].":".$key ?>"
                                          title="<?php echo __("write_note_title", [""])?>"></span>

                                    <div class="comments">
                                        <?php if(array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($key, $data["comments"][$data["currentChapter"]])): ?>
                                            <?php foreach($data["comments"][$data["currentChapter"]][$key] as $comment): ?>
                                                <?php if($comment->memberID == Session::get("memberID")): ?>
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
                <form action="" method="post" id="checker_submit">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo __("confirm_yes")?></label>
                    </div>

                    <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo __("continue")?></button>
                </form>
                <div class="step_right chk"><?php echo __("step_num", ["step_number" => 4])?></div>
            </div>
        </div>
    </div>
</div>

<div class="content_help">
    <div id="help_hide" class="glyphicon glyphicon-chevron-down"> <?php echo __("help") ?></div>

    <div class="help_float">
        <div class="help_info_steps is_checker_page_help isPeer">
            <div class="help_name_steps"><span><?php echo __("step_num", ["step_number" => 4])?>:</span> <?php echo __("peer-review")?></div>
            <div class="help_descr_steps">
                <ul><?php echo __("peer-review_rad_chk_desc")?></ul>
                <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div>
            </div>
        </div>

        <div class="event_info is_checker_page_help isPeer">
            <div class="participant_info">
                <div class="participant_name">
                    <span><?php echo __("your_translator") ?>:</span>
                    <span><?php echo $data["event"][0]->firstName . " " . mb_substr($data["event"][0]->lastName, 0, 1)."." ?></span>
                </div>
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
            <img src="<?php echo template_url("img/steps/icons/peer-review.png") ?>" width="100" height="100">
            <img src="<?php echo template_url("img/steps/big/peer-review.png") ?>" width="280" height="280">
            
        </div>

        <div class="tutorial_content">
            <h3><?php echo __("peer-review")?></h3>
            <ul><?php echo __("peer-review_rad_chk_desc")?></ul>
        </div>
    </div>
</div>

<script>
    isChecker = true;

    $(document).ready(function () {
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