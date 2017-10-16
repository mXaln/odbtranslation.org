<?php
use \Helpers\Constants\EventMembers;
use \Helpers\Parsedown;

if(isset($data["error"])) return;
?>
<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo __("step_num", [3]) . ": " . __("blind-draft_tn")?></div>
    </div>

    <div class="row" style="position: relative">
        <button class="btn btn-warning toggle-help">Toggle help</button>
        <div class="main_content col-sm-9">
            <form action="" id="main_form" method="post">
            <div class="main_content_text" dir="<?php echo $data["event"][0]->sLangDir ?>">
            
            <h4><?php echo $data["event"][0]->sLang." - "
                        .__($data["event"][0]->bookProject)." - "
                        .($data["event"][0]->abbrID <= 39 ? __("old_test") : __("new_test"))." - "
                        ."<span class='book_name'>".$data["event"][0]->name." "
                            .($data["currentChapter"] > 0 ? $data["currentChapter"].":"
                            .(!$data["no_chunk_source"] 
                                ? $data["chunk"][0]."-".$data["chunk"][sizeof($data["chunk"])-1]
                                : " ".__("intro")) : __("intro"))."</span>"?></h4>

                <?php if(!$data["nosource"]): ?>
                <ul class="nav nav-tabs">
                    <li role="presentation" id="my_scripture" class="my_tab">
                        <a href="#"><?php echo __("scripture_mode") ?></a>
                    </li>
                    <li role="presentation" id="my_notes" class="my_tab">
                        <a href="#"><?php echo __("notes_mode") ?></a>
                    </li>
                </ul>

                <div id="my_scripture_content" class="my_content shown">
                    <?php foreach($data["text"] as $chunk => $content): ?>
                        <div class="note_chunk">
                            <?php foreach($content as $verse => $text): ?>
                            <p><?php echo "<strong><sup>".$verse."</sup></strong> ".$text; ?></p>
                            <?php endforeach; ?>
                        </div>    
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div id="my_notes_content" class="my_content">
                    <div class="button_copy_notes">
                        <button data-pasted="false" class="btn btn-primary">Copy</button>
                    </div>
                    <?php foreach($data["notes"] as $note): ?>
                        <div class="row note_chunk">
                            <div class="col-md-6">
                                <div class="note_content">
                                    <?php echo $note ?>
                                </div>
                            </div>
                            <div class="col-md-6 notes_editor">
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
                                    class="add_notes_editor blind_ta"><?php echo $text ?></textarea>
                            </div>
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
            <div class="step_right alt"><?php echo __("step_num", [3])?></div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps">
                <div class="help_title_steps"><?php echo __("help") ?></div>

                <div class="clear"></div>

                <div class="help_name_steps"><span><?php echo __("step_num", [3])?>: </span><?php echo __("blind-draft_tn")?></div>
                <div class="help_descr_steps">
                    <ul><?php echo __("consume_desc")?></ul>
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

<div class="tutorial_container">
    <div class="tutorial_popup">
        <div class="tutorial-close glyphicon glyphicon-remove"></div>
        <div class="tutorial_pic">
            <img src="<?php echo template_url("img/steps/icons/consume.png") ?>" width="100px" height="100px">
            <img src="<?php echo template_url("img/steps/big/consume.png") ?>" width="280px" height="280px">
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
            <ul><?php echo __("consume_desc")?></ul>
        </div>
    </div>
</div>