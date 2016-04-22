<?php
use \Core\Language;
use \Helpers\Constants\EventSteps;
?>

<div id="translator_contents" class="row panel-body">
    <div class="row">
        <?php $apx = $data["event"][0]->gwLang == $data["event"][0]->targetLang ? "_gl" : "" ?>
        <div class="main_content_title"><?php echo Language::show(EventSteps::SELF_CHECK.$apx, "Events")?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <form action="" method="post">
                <div class="main_content_text row">
                    <div class="row">
                        <h4><?php echo $data["event"][0]->sLang." - "
                                .Language::show($data["event"][0]->bookProject, "Events")." - "
                                .($data["event"][0]->abbrID <= 39 ? Language::show("old_test", "Events") : Language::show("new_test", "Events"))." - "
                                .$data["event"][0]->name." ".$data["currentChapter"].":".$data["chunk"][0]."-".$data["chunk"][sizeof($data["chunk"])-1]?></h4>

                        <!-- Show blind draft text if it is a translation to other language -->
                        <?php if($data["event"][0]->gwLang != $data["event"][0]->targetLang):?>
                        <div class="col-sm-12">
                            <textarea readonly class="readonly blind_ta"><?php echo $data["blindDraftText"]; ?></textarea>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <?php for($i=0; $i < sizeof($data["text"]); $i++): ?>
                            <div class="row chunk_verse">
                                <p class="col-sm-6 verse"><?php echo "<strong><sup>".$data["text"][$i]["verse"]."</sup></strong> " . $data["text"][$i]["content"]; ?></p>
                                <p class="col-sm-6">
                                <?php
                                $verses = explode("-", $data["text"][$i]["verse"]);
                                foreach ($verses as $verse):?>
                                    <textarea name="verses[]" class="verse_ta"><?php echo $_POST["verses"][$i-1] ?></textarea>
                                    <textarea style="display: none" name="comments[]" class="comment_ta"></textarea>
                                <?php endforeach; ?>
                                </p>
                            </div>
                            <?php endfor; ?>
                        </div>
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

                <div class="help_name_steps"><span>Step 6:</span> <?php echo Language::show(EventSteps::SELF_CHECK.$apx, "Events")?></div>
                <div class="help_descr_steps"><?php echo Language::show(EventSteps::SELF_CHECK.$apx."_desc", "Events")?></div>
            </div>
        </div>
    </div>
</div>