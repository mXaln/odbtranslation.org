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
                    <ul class="nav nav-tabs">
                        <li role="presentation" class="translation_tab active"><a href="#cotr_tab">Partner's translation</a></li>
                        <li role="presentation" class="translation_tab"><a href="#tr_tab">Your translation</a></li>
                    </ul>

                    <div class="cotr_main_content row">
                        <h4><?php echo $data["event"][0]->sLang." - "
                                .Language::show($data["event"][0]->bookProject, "Events")." - "
                                .($data["event"][0]->abbrID <= 39 ? Language::show("old_test", "Events") : Language::show("new_test", "Events"))." - "
                                .$data["event"][0]->name." ".$data["cotrData"]["currentChapter"].":1-".$data["cotrData"]["totalVerses"]?></h4>

                        <div class="col-sm-12">
                            <?php if($data["event"][0]->cotrStep == \Helpers\Constants\EventSteps::PEER_REVIEW && !empty($data["cotrData"]["translation"])): ?>
                                <?php $i=2; foreach($data["cotrData"]["translation"] as $key => $chunk): ?>
                                    <?php foreach($chunk["translator"]["verses"] as $verse => $text): ?>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <p><strong><sup><?php echo $i/2; ?></sup></strong> <?php echo $data["cotrData"]["text"][$i]; ?></p>
                                            </div>

                                            <div class="col-sm-6">
                                                <p>
                                                    <strong><sup><?php echo $verse; ?></sup></strong>
                                                    <?php echo $text; ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php $i+=2; endforeach; ?>
                                    <div class="chunk_divider col-sm-12"></div>
                                <?php endforeach; ?>
                            <?php else: ?>
                            <div class="row">
                                <div class="col-sm-12 cotr_not_ready" style="color: #ff0000;">Your co-translator is not ready for this step. Please wait. This page will be reloaded automatically when your partner is ready.</div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="tr_main_content row">
                        <h4><?php echo $data["event"][0]->sLang." - "
                                .Language::show($data["event"][0]->bookProject, "Events")." - "
                                .($data["event"][0]->abbrID <= 39 ? Language::show("old_test", "Events") : Language::show("new_test", "Events"))." - "
                                .$data["event"][0]->name." ".$data["currentChapter"].":1-".$data["totalVerses"]?></h4>

                        <div class="col-sm-12">
                            <?php $i=2; foreach($data["translation"] as $key => $chunk) : ?>
                                <?php foreach($chunk["translator"]["verses"] as $verse => $text): ?>
                                    <div class="row chunk_verse">
                                        <p class="col-sm-6 verse"><strong><sup><?php echo $i/2; ?></sup></strong> <?php echo $data["text"][$i]; ?></p>
                                        <textarea name="chunks[<?php echo $key; ?>][verses][]" class="col-sm-6 peer_verse_ta"><?php echo $_POST["chunks"]["verses"][$key][$i-1] != "" ? $_POST["chunks"][$key]["verses"][$i-1] : $text ?></textarea>
                                        <textarea style="display: none" name="chunks[<?php echo $key; ?>][comments][]" class="col-sm-6 comment_ta"></textarea>
                                    </div>
                                <?php $i+=2; endforeach; ?>
                                <div class="chunk_divider col-sm-12"></div>
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