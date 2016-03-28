<?php
use \Core\Language;
?>

<div id="translator_contents" class="row panel-body">
    <div class="row">
        <div class="main_content_title"><?php echo Language::show("peer_review", "Events")?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <form action="" method="post">
                <div class="main_content_text row">
                    <h4><?php echo $data["event"][0]->sLang." - "
                            .Language::show($data["event"][0]->bookProject, "Events")." - "
                            .($data["event"][0]->abbrID <= 39 ? Language::show("old_test", "Events") : Language::show("new_test", "Events"))." - "
                            .$data["event"][0]->name." ".$data["cotrData"]["currentChapter"].":".$data["cotrData"]["totalVerses"]?></h4>

                    <div class="cotr_main_content row">
                        <div class="col-sm-12">
                            <div class="row">
                                <div class="col-sm-6"><h3>Source</h3></div>
                                <div class="col-sm-6"><h3>Translation</h3></div>
                            </div>
                            <?php $i=1; foreach($data["cotrData"]["translation"]["translator"]["verses"] as $verse => $text): ?>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <p><?php echo $data["cotrData"]["text"][$i]; ?></p>
                                    </div>

                                    <div class="col-sm-6">
                                        <p>
                                            <strong><sup><?php echo $verse; ?></sup></strong>
                                            <?php echo $text; ?>
                                        </p>
                                    </div>
                                </div>
                            <?php $i++; endforeach; ?>
                        </div>
                    </div>

                    <br><br>

                    <div class="row">
                        <h4><?php echo $data["event"][0]->sLang." - "
                                .Language::show($data["event"][0]->bookProject, "Events")." - "
                                .($data["event"][0]->abbrID <= 39 ? Language::show("old_test", "Events") : Language::show("new_test", "Events"))." - "
                                .$data["event"][0]->name." ".$data["currentChapter"].":".$data["totalVerses"]?></h4>

                        <div class="col-sm-12">
                            <?php foreach($data["translation"]["translator"]["verses"] as $verse => $text): ?>
                                <div class="row chunk_verse">
                                    <textarea name="verses[]" class="col-sm-12 peer_verse_ta"><?php echo $_POST["verses"][$i-1] != "" ? $_POST["verses"][$i-1] : $text ?></textarea>
                                    <textarea style="display: none" name="comments[]" class="col-sm-6 comment_ta"></textarea>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="main_content_footer row">
                    <div class="form-group">
                        <div class="main_content_confirm_desc">Please confirm that you finished this step</div>
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> Yes, I did</label>
                    </div>

                    <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled>Next step</button>
                </div>
            </form>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps">
                <div class="help_title_steps">HELP</div>

                <div class="clear"></div>

                <div class="help_name_steps"><span>Step 7:</span> <?php echo Language::show("peer_review", "Events")?></div>
                <div class="help_descr_steps"><?php echo Language::show("peer_review_desc", "Events")?></div>
            </div>
        </div>
    </div>
</div>