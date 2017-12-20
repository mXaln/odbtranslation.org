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
        <div class="main_content_title"><?php echo __("step_num", array(3)) . ": " . __("snd-check")?></div>
    </div>

    <div class="row" style="position: relative">
        <button class="btn btn-warning toggle-help" data-mode="l2alt"><?php echo __("hide_help") ?></button>
        <div class="main_content col-sm-9">
            <form action="" id="main_form" method="post" >
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
                        <?php foreach($data["text"] as $verse => $text): ?>
                            <p><?php echo "<strong><sup>".$verse."</sup></strong> ".$text; ?></p>
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
                                        if(!empty($_POST["chunks"][$key]))
                                            $verses = $_POST["chunks"][$key];
                                        else
                                            $verses = $data["translation"][$key][EventMembers::L2_CHECKER]["verses"];
                                        ?>
                                        <div class="vnote">
                                            <?php foreach($verses as $verse => $text): ?>
                                                <div class="verse_block">
                                                    <span class="verse_number_l2"><?php echo $verse?></span>
                                                    <textarea name="chunks[<?php echo $key ?>][<?php echo $verse ?>]"
                                                              class="peer_verse_ta textarea"
                                                              data-orig-verse="<?php echo $verse ?>"  >
                                                    <?php echo $text; ?></textarea>
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
                    <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo __("next_step")?></button>
                </div>
            </form>
            <div class="step_right alt"><?php echo __("step_num", [3])?></div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps">
                <div class="help_title_steps"><?php echo __("help") ?></div>

                <div class="clear"></div>

                <div class="help_name_steps"><span><?php echo __("step_num", [3])?>: </span><?php echo __("snd-check")?></div>
                <div class="help_descr_steps">
                    <ul><?php echo __("snd-check_desc")?></ul>
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
        </div>
    </div>
</div>

<div class="tutorial_container">
    <div class="tutorial_popup">
        <div class="tutorial-close glyphicon glyphicon-remove"></div>
        <div class="tutorial_pic">
            <img src="<?php echo template_url("img/steps/icons/consume.png") ?>" width="100px" height="100px">
            <img src="<?php echo template_url("img/steps/big/consume.png") ?>" width="280px" height="280px">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="<?php echo $data["event"][0]->step ?>" type="checkbox" value="0" /> <?php echo __("do_not_show_tutorial")?></label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo __("snd-check")?></h3>
            <ul><?php echo __("snd-check_desc")?></ul>
        </div>
    </div>
</div>

<script type="text/javascript" src="<?php echo template_url("js/diff_match_patch.js")?>"></script>
<script type="text/javascript" src="<?php echo template_url("js/diff.js?3")?>"></script>
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

            diff_plain($(this).text(), chkVersion.val(), $(this));
        });
    })();
</script>