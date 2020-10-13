<div id="tools">
    <ul class="nav nav-tabs">
        <li role="presentation" class="url_tab">
            <a href="/admin/tools"><?php echo __("common_tools") ?></a>
        </li>
        <li role="presentation" class="url_tab">
            <a href="/admin/tools/source"><?php echo __("source") ?></a>
        </li>
        <li role="presentation" class="url_tab">
            <a href="/admin/tools/vsun"><?php echo __("sun_tools") ?></a>
        </li>
        <li role="presentation" class="url_tab active">
            <a href="/admin/tools/faq"><?php echo __("faq_tools") ?></a>
        </li>
        <li role="presentation" class="url_tab">
            <a href="/admin/tools/news"><?php echo __("news") ?></a>
        </li>
    </ul>

    <div id="tools_content" class="tools_content shown">
        <div class="tools_left">
            <div class="faq_create faq_content">
                <div class="form-group">
                    <label for="faq_question" class="sr-only">Question</label>
                    <textarea class="form-control textarea" id="faq_question" placeholder="<?php echo __("faq_enter_question") ?>"></textarea>
                </div>
                <div class="form-group">
                    <label for="faq_answer" class="sr-only">Answer</label>
                    <textarea class="form-control" id="faq_answer" placeholder="<?php echo __("faq_enter_answer") ?>"></textarea>
                </div>
                <div class="form-group">
                    <select class="form-control" id="faq_category" name="category">
                        <option value="" hidden><?php echo __('select_news_category'); ?></option>
                        <option value="common"><?php echo __("common") ?></option>
                        <option value="mast"><?php echo __("8steps_mast") ?></option>
                        <option value="vsail"><?php echo __("vsail") ?></option>
                        <option value="level2"><?php echo __("l2_3_events", ["level" => 2]) ?></option>
                        <option value="level3"><?php echo __("l2_3_events", ["level" => 3]) ?></option>
                        <option value="notes"><?php echo __("tn") ?></option>
                        <option value="questions"><?php echo __("tq") ?></option>
                        <option value="words"><?php echo __("tw") ?></option>
                        <option value="lang_input"><?php echo __("lang_input") ?></option>
                    </select>
                </div>
                <button class="btn btn-primary create_faq"><?php echo __("create") ?></button>
                <img id="faq_create_loader" src="<?php echo template_url("img/loader.gif") ?>" style="margin-top: -5px">
            </div>
        </div>

        <div class="tools_right">
            <div class="faq_filter tools">
                <div class="form-group">
                    <label for="faqfilter" class="sr-only">Filter</label>
                    <input type="text" class="form-control" id="faqfilter" placeholder="<?php echo __("filter_by_search") ?>" value="">
                </div>
            </div>

            <hr>

            <div class="faq_list tools">
                <ul>
                    <?php foreach ($data["faqs"] as $faq): ?>
                        <li class="faq_content" id="<?php echo $faq->id ?>">
                            <div class="tools_delete_faq">
                                <span><?php echo __("delete") ?></span>
                                <img src="<?php echo template_url("img/loader.gif") ?>">
                            </div>

                            <div class="faq_question"><?php echo $faq->title ?></div>
                            <div class="faq_answer"><?php echo $faq->text ?></div>
                            <div class="faq_cat"><?php echo __($faq->category) ?></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        autosize($("textarea"));
    });
</script>
