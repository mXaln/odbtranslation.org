<h2 style="font-weight: bold;"><?php echo __("faq_tools") ?></h2>

<div class="faq_filter" style="width: 700px">
    <div class="form-group">
        <label for="faqfilterpage" class="sr-only"><?php echo __("filter_by_search") ?></label>
        <input type="text" class="form-control" id="faqfilterpage" placeholder="<?php echo __("filter_by_search") ?>" value="">
    </div>
</div>

<div class="faqs_page">
    <?php if(!empty($data["faqs"])): ?>
        <?php foreach($data["faqs"] as $faq): ?>
            <div class="faq">
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
            $(this).next().toggle(300);
            $("span", this).toggleClass("glyphicon-triangle-bottom glyphicon-triangle-right");
        });

        $("body").on("keyup", "#faqfilterpage", function () {
            var w = $(this).val();
            var re = new RegExp(w, "ig");

            $(".faqs_page").children().hide();
            $(".faqs_page").children().filter(function () {
                return $(this).text().match(re);
            }).show();
        });
    });
</script>