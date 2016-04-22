<?php
use \Core\Language;
?>

<div id="translator_contents" class="row panel-body">
    <div class="row">
        <div class="main_content_title"><?php echo Language::show("keyword-check", "Events")?></div>
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
                            <?php
                            $k=0;
                            $count = 0;
                            foreach($chunk["translator"]["verses"] as $verse => $text):
                                $verses = explode("-", $data["text"][$i-1]);?>
                                <?php if($count == 0): ?>
                                <div class="row chunk_verse">
                                    <p class="col-sm-6 verse"><strong><sup><?php echo $data["text"][$i-1]; ?></sup></strong> <?php echo $data["text"][$i]; ?></p>
                                    <p class="col-sm-6">
                                <?php endif; ?>
                                    <textarea name="chunks[<?php echo $key; ?>][verses][]" class="col-sm-6 peer_verse_ta"><?php echo $_POST["chunks"][$key]["verses"][$k] != "" ? $_POST["chunks"][$key]["verses"][$k] : $text ?></textarea>
                                    <textarea style="display: none" name="chunks[<?php echo $key; ?>][comments][]" class="col-sm-6 comment_ta"></textarea>
                                <?php
                                $k++;
                                $count++;

                                if($count == sizeof($verses)) :
                                    $i+=2;
                                    $count = 0; ?>
                                    </p>
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

                <div class="help_name_steps"><span>Step 8:</span> <?php echo Language::show("keyword-check", "Events")?></div>
                <div class="help_descr_steps"><?php echo Language::show("keyword-check_desc", "Events")?></div>
            </div>
        </div>
    </div>
</div>