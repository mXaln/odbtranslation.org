<?php
/**
 * Created by PhpStorm.
 * User: Maxim
 * Date: 12 Apr 2016
 * Time: 17:30
 */
use \Core\Language;
use \Helpers\Constants\EventSteps;

if(empty($error) && empty($data["success"])):
?>

<div id="translator_contents" class="row panel-body">
    <div class="row">
        <div class="main_content_title"><?php echo $data["event"][0]->step == EventSteps::KEYWORD_CHECK ? Language::show("kw_checker", "Events") : Language::show("cont_checker", "Events")?></div>
</div>

<div class="row">
    <div class="main_content col-sm-9">
        <div class="main_content_text">
            <h4><?php echo $data["event"][0]->sLang." - "
                    .Language::show($data["event"][0]->bookProject, "Events")." - "
                    .($data["event"][0]->abbrID <= 39 ? Language::show("old_test", "Events") : Language::show("new_test", "Events"))." - "
                    .$data["event"][0]->bookName." ".$data["currentChapter"].":1-".$data["totalVerses"]?></h4>

            <?php for($i=2; $i <= sizeof($data["text"]); $i+=2): ?>
                <p><?php echo "<strong><sup>".($i/2)."</sup></strong> ".$data["text"][$i]; ?></p>
            <?php endfor; ?>
        </div>

        <?php //if(empty($error)):?>
        <div class="main_content_footer row">
            <form action="" method="post">
                <div class="form-group">
                    <div class="main_content_confirm_desc">Please confirm that you finished this step</div>
                    <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> Yes, I did</label>
                </div>

                <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled>Next step</button>
            </form>
        </div>
        <?php //endif; ?>
    </div>

    <div class="content_help col-sm-3">
        <div class="help_info_steps">
            <div class="help_title_steps">HELP</div>

            <div class="clear"></div>

            <div class="help_name_steps"><span>Step 2:</span> <?php echo Language::show("kw_checker_help", "Events")?></div>
            <div class="help_descr_steps"><?php echo Language::show("kw_checker_help_desc", "Events")?></div>
        </div>
    </div>
</div>
</div>
<?php endif; ?>