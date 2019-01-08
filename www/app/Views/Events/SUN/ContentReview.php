<?php
use Helpers\Constants\EventMembers;

if(isset($data["error"])) return;
?>

<div class="comment_div panel panel-default font_sun">
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
        <div class="main_content_title"><?php echo __("step_num", [7]). ": " . __("content-review")?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <form action="" method="post" id="main_form">
                <div class="main_content_text row" style="padding-left: 15px">
                    <h4 dir="<?php echo $data["event"][0]->sLangDir ?>"><?php echo $data["event"][0]->tLang." - "
                            .__($data["event"][0]->bookProject)." - "
                            .($data["event"][0]->abbrID <= 39 ? __("old_test") : __("new_test"))." - "
                            ."<span class='book_name'>".$data["event"][0]->name." ".$data["currentChapter"].":1-".$data["totalVerses"]."</span>"?></h4>

                    <ul class="nav nav-tabs">
                        <li role="presentation" id="source_scripture" class="my_tab">
                            <a href="#"><?php echo __("source_text") ?></a>
                        </li>
                        <li role="presentation" id="rearrange" class="my_tab">
                            <a href="#"><?php echo __("rearrange") ?></a>
                        </li>
                    </ul>

                    <div id="source_scripture_content" class="col-sm-12 no_padding my_content shown">
                        <?php foreach($data["chunks"] as $key => $chunk) : ?>
                            <div class="row chunk_block">
                                <div class="chunk_verses col-sm-6" dir="<?php echo $data["event"][0]->sLangDir ?>">
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
                                <div class="col-sm-6 editor_area" dir="<?php echo $data["event"][0]->tLangDir ?>">
                                    <?php $text = $data["translation"][$key][EventMembers::TRANSLATOR]["symbols"]; ?>
                                    <div class="vnote">
                                        <textarea name="chunks[]" class="col-sm-6 verse_ta textarea sun_content"><?php
                                            echo isset($_POST["chunks"]) && isset($_POST["chunks"][$key]) ? $_POST["chunks"][$key] : $text
                                        ?></textarea>

                                        <?php $hasComments = array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($key, $data["comments"][$data["currentChapter"]]); ?>
                                        <div class="comments_number <?php echo $hasComments ? "hasComment" : "" ?>">
                                            <?php echo $hasComments ? sizeof($data["comments"][$data["currentChapter"]][$key]) : ""?>
                                        </div>
                                        <img class="editComment" data="<?php echo $data["currentChapter"].":".$key ?>" width="16" src="<?php echo template_url("img/edit.png") ?>" title="<?php echo __("write_note_title", [""])?>"/>

                                        <div class="comments">
                                            <?php if(array_key_exists($data["currentChapter"], $data["comments"]) && array_key_exists($key, $data["comments"][$data["currentChapter"]])): ?>
                                                <?php foreach($data["comments"][$data["currentChapter"]][$key] as $comment): ?>
                                                    <?php if($comment->memberID == $data["event"][0]->myChkMemberID): ?>
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

                    <div id="rearrange_content" class="my_content">
                        <?php foreach($data["chunks"] as $key => $chunk) : ?>
                            <div class="row chunk_block">
                                <div class="chunk_verses col-sm-12" dir="<?php echo $data["event"][0]->sLangDir ?>">
                                    <?php
                                    $verse = "";
                                    if(is_array($chunk) && !empty($chunk))
                                    {
                                        $verse = $chunk[0];
                                        if($chunk[sizeof($chunk)-1] != $verse)
                                            $verse .= "-".$chunk[sizeof($chunk)-1];
                                    }
                                    ?>
                                    <strong class="<?php echo $data["event"][0]->sLangDir ?>">
                                        <sup><?php echo $verse; ?></sup>
                                    </strong>
                                    <?php echo $data["translation"][$key][EventMembers::TRANSLATOR]["words"]; ?>
                                </div>
                            </div>
                            <div class="chunk_divider col-sm-12"></div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="main_content_footer row">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo __("confirm_yes")?></label>
                    </div>

                    <input type="hidden" name="level" value="sunContinue">
                    <input type="hidden" name="chapter" value="<?php echo $data["event"][0]->currentChapter ?>">
                    <input type="hidden" name="memberID" value="<?php echo $data["event"][0]->memberID ?>">

                    <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo __("next_step")?></button>
                    <img src="<?php echo template_url("img/saving.gif") ?>" class="unsaved_alert" style="float:none">
                </div>
            </form>
            <div class="step_right alt"><?php echo __("step_num", [7])?></div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_float">
                <div class="help_info_steps is_checker_page_help">
                    <div class="help_hide toggle-help glyphicon glyphicon-eye-close" title="<?php echo __("hide_help") ?>"></div>
                    <div class="help_title_steps"><?php echo __("help") ?></div>

                    <div class="clear"></div>

                    <div class="help_name_steps"><span><?php echo __("step_num", [7])?>:</span> <?php echo __("content-review")?></div>
                    <div class="help_descr_steps">
                        <ul><?php echo __("content-review_sun_desc")?></ul>
                        <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div>
                    </div>
                </div>

                <div class="event_info is_checker_page_help">
                    <div class="participant_info">
                        <div class="additional_info">
                            <a href="/events/information-sun/<?php echo $data["event"][0]->eventID ?>"><?php echo __("event_info") ?></a>
                        </div>
                    </div>
                </div>

                <div class="tr_tools">
                    <button class="btn btn-warning show_saildict"><?php echo __("show_dictionary") ?></button>
                    <button class="btn btn-primary ttools" data-tool="tn"><?php echo __("show_notes") ?></button>
                    <button class="btn btn-primary ttools" data-tool="tw"><?php echo __("show_keywords") ?></button>
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

        <div class="tutorial_content is_checker_page_help">
            <h3><?php echo __("content-review")?></h3>
            <ul><?php echo __("content-review_sun_desc")?></ul>
        </div>
    </div>
</div>

<div class="saildict_panel panel panel-default" draggable="true">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo __("sail_dictionary") ?></h1>
        <span class="panel-close glyphicon glyphicon-remove"></span>
    </div>

    <div class="sun_content saildict page-content panel-body">
        <div class="sail_filter">
            <div class="form-group">
                <label for="sailfilter" class="sr-only">Filter</label>
                <input type="text" class="form-control input-lg" id="sailfilter" placeholder="<?php echo __("filter_by_word") ?>" value="">
            </div>
        </div>
        <div class="sail_list">
            <ul>
                <?php foreach ($data["saildict"] as $word): ?>
                    <li id="<?php echo $word->word ?>" title="<?php echo __("copy_symbol_tip") ?>">
                        <div class="sail_word"><?php echo $word->word ?></div>
                        <div class="sail_symbol"><?php echo $word->symbol ?></div>
                        <input type="text" value="<?php echo $word->symbol ?>" />
                        <div class="clear"></div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="copied_tooltip"><?php echo __("copied_tip") ?></div>
</div>

<?php if(!empty($data["notes"])): ?>
    <div class="ttools_panel tn_tool panel panel-default" draggable="true">
        <div class="panel-heading">
            <h1 class="panel-title"><?php echo __("tn") ?></h1>
            <span class="panel-close glyphicon glyphicon-remove" data-tool="tn"></span>
        </div>

        <div class="ttools_content page-content panel-body">
            <div class="labels_list">
                <?php if(isset($data["notes"])): ?>
                    <?php foreach ($data["notes"] as $verse => $notes): ?>
                        <?php $chunkVerses = $data["notesVerses"][$verse]; ?>
                        <label>
                            <ul>
                                <li>
                                    <div class="word_term">
                            <span style="font-weight: bold;">
                                <?php echo $chunkVerses > 0 ? __("verse_number", $chunkVerses) :
                                    __("intro")?>
                            </span>
                                    </div>
                                    <div class="word_def">
                                        <?php foreach ($notes as $note): ?>
                                            <?php echo  preg_replace('#<a.*?>(.*?)</a>#i', '<b>\1</b>', $note) ?>
                                        <?php endforeach; ?>
                                    </div>
                                </li>
                            </ul>
                        </label>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="word_def_popup">
                <div class="word_def-close glyphicon glyphicon-remove"></div>

                <div class="word_def_title"></div>
                <div class="word_def_content"></div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if(!empty($data["keywords"]) && !empty($data["keywords"]["words"])): ?>
    <div class="ttools_panel tw_tool panel panel-default" draggable="true">
        <div class="panel-heading">
            <h1 class="panel-title"><?php echo __("tw") ?></h1>
            <span class="panel-close glyphicon glyphicon-remove" data-tool="tw"></span>
        </div>

        <div class="ttools_content page-content panel-body">
            <div class="labels_list">
                <?php if(isset($data["keywords"]) && isset($data["keywords"]["words"])): ?>
                    <?php foreach ($data["keywords"]["words"] as $title => $tWord): ?>
                        <?php if(!isset($tWord["text"])) continue; ?>
                        <label>
                            <ul>
                                <li>
                                    <div class="word_term">
                                        <span style="font-weight: bold;"><?php echo ucfirst($title) ?> </span>
                                        (<?php echo strtolower(__("verses").": ".join(", ", $tWord["range"])); ?>)
                                    </div>
                                    <div class="word_def"><?php echo  preg_replace('#<a.*?>(.*?)</a>#i', '<b>\1</b>', $tWord["text"]); ?></div>
                                </li>
                            </ul>
                        </label>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="word_def_popup">
                <div class="word_def-close glyphicon glyphicon-remove"></div>

                <div class="word_def_title"></div>
                <div class="word_def_content"></div>
            </div>
        </div>
    </div>
<?php endif; ?>