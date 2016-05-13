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
        <div class="main_content_title"><?php echo Language::show("keyword-check", "Events")?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <form action="" method="post">
                <div class="main_content_text row">
                    <?php if($data["event"][0]->checkerID == 0): ?>
                    <div class="alert alert-success check_request"><?php echo Language::show("check_request_sent_success", "Events") ?></div>
                    <?php endif; ?>

                    <h4><?php echo $data["event"][0]->sLang." - "
                            .Language::show($data["event"][0]->bookProject, "Events")." - "
                            .($data["event"][0]->abbrID <= 39 ? Language::show("old_test", "Events") : Language::show("new_test", "Events"))." - "
                            .$data["event"][0]->name." ".$data["currentChapter"].":1-".$data["totalVerses"]?></h4>

                    <div class="col-sm-12 no_padding">
                        <?php $i=2; foreach($data["translation"] as $key => $chunk) : ?>
                            <?php
                            $k=0;
                            $count = 0;
                            foreach($chunk["translator"]["verses"] as $verse => $text):
                                $verses = explode("-", $data["text"][$i-1]);
                                $comment = $chunk["translator"]["comments"][$verse];
                                $commentAlt = $chunk["translator"]["comments_alt"][$verse];
                                ?>
                                <?php if($count == 0): ?>
                                <div class="row chunk_verse">
                                    <div class="col-sm-6 verse"><strong><sup><?php echo $data["text"][$i-1]; ?></sup></strong> <?php echo $data["text"][$i]; ?></div>
                                    <div class="col-sm-6 editor_area">
                                <?php endif; ?>
                                    <textarea name="chunks[<?php echo $key; ?>][verses][]" class="col-sm-6 peer_verse_ta textarea"><?php echo $_POST["chunks"][$key]["verses"][$k] != "" ? $_POST["chunks"][$key]["verses"][$k] : $text ?></textarea>
                                    <img class="editComment" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note"/>
                                    <textarea name="chunks[<?php echo $key; ?>][comments][]" class="comment_ta textarea"><?php echo $_POST["chunks"][$key]["comments"][$k] != "" ? $_POST["chunks"][$key]["comments"][$k] : preg_replace("/^@[a-z]+[a-z0-9]*:\s/", "", $comment); ?></textarea>
                                    <?php if(trim($commentAlt != "")): ?>
                                    <img class="showComment" data-toggle="tooltip" data-placement="left" title="<?php echo $commentAlt; ?>" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/note.png"/>
                                    <?php endif;?>
                                <?php
                                $k++;
                                $count++;

                                if($count == sizeof($verses)) :
                                    $i+=2;
                                    $count = 0; ?>
                                    </div>
                                    </div>
                                    <?php
                                endif;
                            endforeach;
                            ?>
                            <div class="chunk_divider col-sm-12"></div>
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

                <div class="help_name_steps"><span><?php echo Language::show("step_num", "Events", array(7))?></span> <?php echo Language::show("keyword-check", "Events")?></div>
                <div class="help_descr_steps">
                    <ul><?php echo mb_substr(Language::show("keyword-check_desc", "Events"), 0, 300)?>... <div class="show_tutorial_popup"> >>> <?php echo Language::show("show_more", "Events")?></div></ul>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="tutorial_container">
    <div class="tutorial_popup">
        <div class="tutorial-close glyphicon glyphicon-remove"></div>
        <div class="tutorial_pic">
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/icons/keyword-check.png" width="100px" height="100px">
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/big/keyword-check.png" width="280px" height="280px">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="<?php echo $data["event"][0]->step ?>" type="checkbox" value="0" /> <?php echo Language::show("do_not_show_tutorial", "Events")?></label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo Language::show("keyword-check", "Events")?></h3>
            <ul><?php echo Language::show("keyword-check_desc", "Events")?></ul>
        </div>
    </div>
</div>