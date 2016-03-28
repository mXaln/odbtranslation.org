<?php
use \Core\Language;
use \Helpers\Session;
?>

<div id="translator_contents" class="row panel-body">
    <div class="row">
        <div class="main_content_title"><?php echo Language::show("blind_drafting", "Events")?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <form action="" method="post">
                <div class="main_content_text">
                    <div class="row">
                        <h4><?php echo $data["event"][0]->sLang." - "
                                .Language::show($data["event"][0]->bookProject, "Events")." - "
                                .($data["event"][0]->abbrID <= 39 ? Language::show("old_test", "Events") : Language::show("new_test", "Events"))." - "
                                .$data["event"][0]->name." ".$data["currentChapter"].":".$data["totalVerses"]?></h4>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <textarea name="draft" rows="10" class="col-sm-6 blind_ta"><?php echo $_POST["draft"]?></textarea>
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

                <div class="help_name_steps"><span>Step 6:</span> <?php echo Language::show("blind_drafting", "Events")?></div>
                <div class="help_descr_steps"><?php echo Language::show("blind_drafting_desc", "Events")?></div>
            </div>
        </div>
    </div>
</div>