<h2 style="font-weight: bold;"><?php echo __("faq_tools") ?></h2>

<div class="faq_filter form-inline" style="width: 700px">
    <div class="form-group">
        <label for="faqfilterpage" class="sr-only"><?php echo __("filter_by_search") ?></label>
        <input type="text" class="form-control" size="60" id="faqfilterpage" placeholder="<?php echo __("filter_by_search") ?>" value="">
    </div>
    <div class="form-group">
        <select class="faq_cat form-control" name="category">
            <option value="" hidden><?php echo __('filter_by_category'); ?></option>
            <option value="common"><?php echo __("common") ?></option>
            <option value="vmast"><?php echo __("8steps_vmast") ?></option>
            <option value="vsail"><?php echo __("vsail") ?></option>
            <option value="level2"><?php echo __("l2_3_events", ["level" => 2]) ?></option>
            <option value="level3"><?php echo __("l2_3_events", ["level" => 3]) ?></option>
            <option value="notes"><?php echo __("tn") ?></option>
            <option value="questions"><?php echo __("tq") ?></option>
            <option value="words"><?php echo __("tw") ?></option>
            <option value="lang_input"><?php echo __("lang_input") ?></option>
        </select>
    </div>
    <div class="form-group">
        <button class="reset_faq_filter btn btn-danger"><?php echo __("clear_filter") ?></button>
    </div>
</div>

<div class="faqs_page">
    <?php if(!empty($data["faqs"])): ?>
        <?php foreach($data["faqs"] as $faq): ?>
            <div class="faq" data-category="<?php echo $faq->category ?>">
                <div class="faq_question_header">
                    <span class="glyphicon glyphicon-triangle-right"></span>
                    <?php echo $faq->title ?>
                </div>
                <div class="faq_answer_content">
                    <div class="faq_text"><?php echo preg_replace("/\n/", "<br>", $faq->text) ?></div>
                    <div class="faq_category"><?php echo __($faq->category) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
    <div><?php echo "No FAQ. <a href='/admin/tools'>Create</a>." ?></div>
    <?php endif; ?>
</div>

<script>
    $(function() {
        $(".faq_question_header").click(function () {
            $(this).next().slideToggle(300);
            $("span", this).toggleClass("glyphicon-triangle-bottom glyphicon-triangle-right");
        });

        $("body").on("keyup", "#faqfilterpage", function () {
            var w = $(this).val();
            var re = new RegExp(w, "ig");
            var cat = $(".faq_cat").val();

            $(".faqs_page").children().hide();
            $(".faqs_page").children().filter(function () {
                return $(this).text().match(re)
                    && (cat != "" ? $(this).data("category") == cat : true);
            }).show();
        });

        $("body").on("change", ".faq_cat", function() {
            var w = $("#faqfilterpage").val();
            var re = new RegExp(w, "ig");
            var cat = $(this).val();

            $(".faqs_page").children().hide();
            $(".faqs_page").children().filter(function () {
                return $(this).text().match(re)
                    && (cat != "" ? $(this).data("category") == cat : true);
            }).show();
        });

        $(".reset_faq_filter").click(function() {
            $("#faqfilterpage").val("");
            $(".faq_cat").val("");
            $(".faqs_page").children().show();
        });
    });
</script>