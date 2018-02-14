<div id="tools">
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

        <button class="btn btn-warning"><?php echo __("go"); ?></button>
        <img src="<?php echo template_url("img/loader.gif") ?>">
    </div>
</div>