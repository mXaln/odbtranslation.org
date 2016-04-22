<?php
use \Core\Language;
?>

<div id="translator_contents" class="row panel-body">
    <div class="row">
        <div class="main_content_title"><?php echo Language::show("pray", "Events")?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <div class="main_content_text">
                Pray - God created all the languages of the world and has given us the ability to learn and use them. He has also given us His spirit to help us i everything we do. Therefore, begin this exercise with some time in prayer, exalting the Lord and asking that He will grant the wisdom and guidance necessary to enable you to faithfully and accurately translate His holy Word. Pray early and pray often.


            </div>

            <div class="main_content_footer row">
                <form action="" method="post">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo Language::show("confirm_finished", "Events")?></div>
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo Language::show("confirm_yes", "Events")?></label>
                    </div>

                    <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo Language::show("next_step", "Events")?></button>
                </form>
            </div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps">
                <div class="help_title_steps">HELP</div>

                <div class="clear"></div>

                <div class="help_name_steps"><span>Step 1:</span> <?php echo Language::show("pray", "Events")?></div>
                <div class="help_descr_steps"><?php echo Language::show("pray_desc", "Events")?></div>
            </div>
        </div>
    </div>
</div>