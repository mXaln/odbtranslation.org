<?php
use \Core\Language;
?>

<div class="editor">
    <div class="comment_div panel panel-default">
        <div class="panel-heading">
            <h1 class="panel-title"><?php echo Language::show("write_note_title", "Events")?></h1>
            <span class="editor-close glyphicon glyphicon-ok-sign"></span>
        </div>
        <textarea class="textarea textarea_editor"></textarea>
    </div>
</div>

<div id="translator_contents" class="row panel-body">
    <div class="row">
        <div class="main_content_title"><?php echo Language::show("content-review", "Events")?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <form action="" method="post">
                <div class="main_content_text row">
                    <?php if($data["event"][0]->checkerID == 0): ?>
                        <div class="alert alert-success"><?php echo Language::show("check_request_sent_success", "Events") ?></div>
                    <?php endif; ?>

                    <h4><?php echo $data["event"][0]->sLang." - "
                            .Language::show($data["event"][0]->bookProject, "Events")." - "
                            .($data["event"][0]->abbrID <= 39 ? Language::show("old_test", "Events") : Language::show("new_test", "Events"))." - "
                            .$data["event"][0]->name." ".$data["currentChapter"].":1-".$data["totalVerses"]?></h4>

                    <div class="col-sm-12">
                        <?php $i=2; foreach($data["translation"] as $key => $chunk) : ?>
                            <?php $k=0; foreach($chunk["translator"]["verses"] as $verse => $text):
                                $comment = $chunk["translator"]["comments"][$verse];
                                $commentAlt = $chunk["translator"]["comments_alt"][$verse];
                                ?>
                                <div class="row chunk_verse editor_area">
                                    <div class="p_verse_num"><strong><sup><?php echo $verse ?></sup></strong></div>
                                    <textarea name="chunks[<?php echo $key; ?>][verses][]" class="col-sm-9 peer_verse_ta verse_right textarea"><?php echo $_POST["chunks"][$key]["verses"][$k] != "" ? $_POST["chunks"][$key]["verses"][$k] : $text ?></textarea>
                                    <img class="editComment" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note"/>
                                    <textarea name="chunks[<?php echo $key; ?>][comments][]" class="comment_ta textarea"><?php echo $_POST["chunks"][$key]["comments"][$k] != "" ? $_POST["chunks"][$key]["comments"][$k] : $comment; ?></textarea>
                                    <?php if(trim($commentAlt != "")): ?>
                                        <img class="showComment" data-toggle="tooltip" data-placement="left" title="<?php echo $commentAlt; ?>" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/note.png"/>
                                    <?php endif;?>
                                </div>
                                <?php $k++; $i+=2; endforeach; ?>
                            <div class="chunk_divider col-sm-12"></div>
                            <div class="clear"></div>
                        <?php endforeach; ?>
                    </div>

                    <div class="col-sm-12">
                        <button id="save_step" type="submit" name="save" value="1" class="btn btn-primary">Save</button>
                    </div>
                </div>

                <div class="main_content_footer row">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo Language::show("confirm_finished", "Events")?></div>
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo Language::show("confirm_yes", "Events")?></label>
                    </div>

                    <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo Language::show("next_step", "Events")?></button>
                </div>
            </form>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps">
                <div class="help_title_steps">HELP</div>

                <div class="clear"></div>

                <div class="help_name_steps"><span>Step 8:</span> <?php echo Language::show("content-review", "Events")?></div>
                <div class="help_descr_steps">
                    <ul><?php echo Language::show("content-review_desc", "Events")?></ul>
                </div>
            </div>
        </div>
    </div>
</div>