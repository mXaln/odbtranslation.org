<div id="tools">
    <div class="tools_left">
        <div class="update_langs">
            <div class="tools_title"><?php echo __("update_lang_db"); ?></div>
            <button class="btn btn-warning"><?php echo __("go"); ?></button>
            <img src="<?php echo template_url("img/loader.gif") ?>">
        </div>

        <hr>

        <div class="create_users">
            <div class="tools_title"><?php echo __("create_multiple_users"); ?></div>

            <div class="form-group">
                <label for="amount" class=""><?php echo __("tools_quantity_members"); ?>:</label>
                <input type="text" class="form-control" id="amount" name="amount" placeholder="<?php echo __('enter_value'); ?>" value="">
            </div>

            <div class="form-group">
                <label for="langs" class=""><?php echo __("tools_member_language"); ?>:</label>
                <input type="text" class="form-control" id="langs" name="langs" placeholder="<?php echo __('enter_lang_codes'); ?>" value="">
            </div>

            <div class="form-group">
                <label for="password" class=""><?php echo __("password"); ?>:</label>
                <input type="text" class="form-control" id="password" name="password" placeholder="<?php echo __('enter_value'); ?>" value="">
            </div>

            <button class="btn btn-warning"><?php echo __("go"); ?></button>
            <img src="<?php echo template_url("img/loader.gif") ?>">
        </div>

        <hr>

        <div class="create_news">
            <div class="tools_title"><?php echo __("create_news"); ?></div>

            <div class="form-group">
                <label for="title" class=""><?php echo __("tools_news_title"); ?>:</label>
                <input type="text" class="form-control" id="title" name="title" placeholder="<?php echo __('enter_news_title'); ?>" value="">
            </div>

            <div class="form-group">
                <label for="category" class=""><?php echo __("tools_news_category"); ?>:</label>
                <select class="form-control" id="category" name="category">
                    <option value="" hidden><?php echo __('select_news_category'); ?></option>
                    <option value="common"><?php echo __("common") ?></option>
                    <option value="vmast"><?php echo __("8steps_vmast") ?></option>
                    <option value="vsail"><?php echo __("vsail") ?></option>
                    <option value="level2"><?php echo __("l2_3_events", [2]) ?></option>
                    <option value="notes"><?php echo __("tn") ?></option>
                </select>
            </div>

            <div class="form-group">
                <label for="text" class=""><?php echo __("tools_news_text"); ?>:</label>
                <textarea rows="4" class="form-control" id="text" name="text" placeholder="<?php echo __('enter_news_text'); ?>"></textarea>
            </div>

            <button class="btn btn-warning"><?php echo __("go"); ?></button>
            <img src="<?php echo template_url("img/loader.gif") ?>">
        </div>
    </div>

    <div class="tools_right">
        <div class="saildict tools">
            <div class="tools_title"><?php echo __("sail_dictionary_editor"); ?></div>

            <div class="sail_filter tools">
                <div class="form-group">
                    <label for="sailfilter" class="sr-only">Filter</label>
                    <input type="text" class="form-control" id="sailfilter" placeholder="<?php echo __("filter_by_word") ?>" value="">
                </div>
            </div>

            <div class="sail_create form-inline sun_content">
                <div class="form-group">
                    <label for="sailword" class="sr-only">Word</label>
                    <input type="text" class="form-control input" id="sailword" placeholder="<?php echo __("sail_enter_word") ?>" value="">
                </div>
                <div class="form-group" style="">
                    <label for="sailsymbol" class="sr-only">Symbol</label>
                    <input type="text" class="form-control input" id="sailsymbol" placeholder="<?php echo __("sail_enter_symbol") ?>" value="">
                </div>
                <button class="btn btn-primary add_word" style="margin-top: -5px"><?php echo __("add") ?></button>
                <img id="sail_create_loader" src="<?php echo template_url("img/loader.gif") ?>" style="margin-top: -5px">
            </div>

            <div class="sail_list tools">
                <ul>
                    <?php foreach ($data["saildict"] as $word): ?>
                        <li class="sun_content" id="<?php echo $word->word ?>">
                            <div class="tools_delete_word glyphicon glyphicon-remove" title="<?php echo __("delete") ?>">
                                <img src="<?php echo template_url("img/loader.gif") ?>">
                            </div>

                            <div class="sail_word"><?php echo $word->word ?></div>
                            <div class="sail_symbol"><?php echo $word->symbol ?></div>
                            <input type="text" value="<?php echo $word->symbol ?>" />
                            <div class="clear"></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="copied_tooltip" style="font-size: 16px"><?php echo __("copied_tip") ?></div>
        </div>
    </div>

    <div class="clear"></div>
</div>