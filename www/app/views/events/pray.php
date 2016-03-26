<?php
use \Core\Language;
?>

<div id="translator_contents" class="row panel-body">
    <div class="row">
        <div class="main_content_title"><?php echo Language::show("prayer_focus", "Events")?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <div class="main_content_text">
                sdrgkh sbkrghsbkhgk hegk sheg ksheb gkshvb vsghsueh gsuehkugskeu gksueg kusekgusefuseoui gsleu gslueg sueg fksuegk fusegku fgskeug fksueg fkusegfkuseg kfusgek fugseku
            </div>

            <div class="main_content_footer row">
                <form action="" method="post">
                    <div class="form-group">
                        <div class="main_content_confirm_desc">Please confirm that you finished this step</div>
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> Yes, I did</label>
                    </div>

                    <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled>Next step</button>
                </form>
            </div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps">
                <div class="help_title_steps">HELP</div>

                <div class="clear"></div>

                <div class="help_name_steps"><span>Step 1:</span> <?php echo Language::show("prayer_focus", "Events")?></div>
                <div class="help_descr_steps"><?php echo Language::show("prayer_focus_desc", "Events")?></div>
            </div>
        </div>
    </div>
</div>