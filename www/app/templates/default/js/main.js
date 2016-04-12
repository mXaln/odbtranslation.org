var currentChunk = -1;
var firstVerse = 0;
var lastVerse = 0;
var chunks = [];

jQuery(function($) {

    // Statement of faith block
    $("#sof").click(function() {
        $(".sof_block").show();
        return false;
    });

    $("#sof_agree").click(function() {
        $("#sof").prop('checked', true);
        $(".sof_block").hide();
    });

    $("#sof_cancel").click(function() {
        $("#sof").prop('checked', false);
        $(".sof_block").hide();
    });

    // Terms of use block
    $("#tou").click(function() {
        $(".tou_block").show();
        return false;
    });

    $("#tou_agree").click(function() {
        $("#tou").prop('checked', true);
        $(".tou_block").hide();
    });

    $("#tou_cancel").click(function() {
        $("#tou").prop('checked', false);
        $(".tou_block").hide();
    });

    // Old/New Testament Tabs
    $("a[href=#new_test]").click(function() {
        $("#old_test").hide();
        $("a[href=#old_test]").parent().removeClass("active");

        $("#new_test").show();
        $(this).parent().addClass("active");
        return false;
    });

    $("a[href=#old_test]").click(function() {
        $("#old_test").show();
        $(this).parent().addClass("active");

        $("a[href=#new_test]").parent().removeClass("active");
        $("#new_test").hide();
        return false;
    });

    // Apply for event as translator/checker
    // Start event
    $(".applyEvent").click(function() {
        var bookCode = $(this).attr("data");
        var bookName = $(this).attr("data2");
        var stage = $(this).attr("data3");

        $("#applyEvent").trigger("reset");
        $(".errors").html("");
        $(".bookName").text(bookName);
        $(".panel-title").text(bookName);
        $("#bookCode").val(bookCode);

        if(stage == "d1")
        {
            $(".checker_info").hide();
            $(".ftr").show();
            $(".fl2, .fl3").hide();
            $("input[name=userType]").val("translator");
        }
        else
        {
            if(stage == "l2")
            {
                $(".ftr, .fl3").hide();
                $(".fl2").show();
                $("input[name=userType]").val("checker_l2");
            }
            else
            {
                $(".ftr, .fl2").hide();
                $(".fl3").show();
                $("input[name=userType]").val("checker_l3");
            }
            $(".checker_info").show();
        }

        $(".event-content").show();
    });

    $(".panel-close").click(function() {
        $(this).parents(".form-panel").hide();
    });


    // ------------------- Translation Flow ---------------------- //

    // Hide steps panel on small screens
    $(document).ready(function() {
        if($(window).width() < 1800)
        {
            $("#tr_steps_hide")[0].click();
        }
    });

    // Show/Hide Steps Panel
    $("#tr_steps_hide").click(function () {
        if($("#translator_steps").hasClass("open"))
        {
            $("#translator_steps").removeClass("open")
                .addClass("closed");
            $("#translator_steps").animate({left: "-250px"}, 500, function() {
                $("#tr_steps_hide").removeClass("glyphicon-chevron-left")
                    .addClass("glyphicon-chevron-right");
            });
        }
        else
        {
            $("#translator_steps").removeClass("closed")
                .addClass("open");
            $("#translator_steps").animate({left: 0}, 500, function() {
            $("#tr_steps_hide").removeClass("glyphicon-chevron-right")
                .addClass("glyphicon-chevron-left");
        });
        }
    });


    // Confirm to go to the next step
    $("#confirm_step").change(function() {
        if($(this).is(":checked"))
            $("#next_step").prop("disabled", false);
        else
            $("#next_step").prop("disabled", true);
    });

    // Submit apply event form
    $("#applyEvent").submit(function(e) {

        $.ajax({
                url: $("#applyEvent").prop("action"),
                method: "post",
                data: $("#applyEvent").serialize(),
                dataType: "json",
                beforeSend: function() {
                    $(".applyEventLoader").show();
                }
            })
            .done(function(data) {
                if(data.success)
                {
                    alert(data.success);
                    location.reload();
                }
                else
                {
                    $(".errors").html(data.error);
                }
            })
            .always(function() {
                $(".applyEventLoader").hide();
            });

        e.preventDefault();
    });

    $(".verse").each(function(index) {
        $(this).next().css("height", $(this).css("height"));
    });

    autosize($('textarea'));

    // Add verse to chunk
    $(document).on("click", ".verse_number", function(e) {
        var verse = parseInt($(this).val());
        var p = $(this).parent().parent();
        var createChunkBtn = $(".create_chunk");

        if((verse > 1 && chunks.length <= 0) ||
            !$(this).is(":checked") ||
            (verse > (lastVerse + 1)))
        {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }

        if(currentChunk < 0)
        {
            chunks[0] = [];
            chunks[0].push(verse);
            currentChunk = 0;
            firstVerse = verse;

            $(".chunks_reset").show();
        }
        else
        {
            if(typeof chunks[currentChunk] == "undefined")
            {
                chunks[currentChunk] = [];
                firstVerse = verse;
            }
            chunks[currentChunk].push(verse);
        }

        $("#chunks_array").val(JSON.stringify(chunks));

        $(".verse_p .create_chunk").remove();
        p.append(createChunkBtn);

        fv = firstVerse < 10 ? "0"+firstVerse : firstVerse;
        lv = verse < 10 ? "0"+verse : verse;
        $(".verse_p .create_chunk").text("Make chunk "+fv+"-"+lv).show();

        lastVerse = verse;
    });

    // Start new chunk
    $(document).on("click", ".verse_p .create_chunk", function() {
        currentChunk++;
        $(".verse_p .create_chunk").hide();
        $(".verse_p .create_chunk").parent().after('<div class="chunk_divider col-sm-12"></div>');
    });

    // Reset chunks
    $(".chunks_reset").click(function() {
        chunks = [];
        currentChunk = -1;
        firstVerse = 0;
        lastVerse = 0

        $(this).hide();
        $(".create_chunk").hide();
        $(".chunk_divider").remove();
        $(".verse_number").prop("checked", false);
        $("#chunks_array").val("[]");
    });

    // Switching translation tabs on peer review step
    $("a[href=#cotr_tab]").click(function() {
        $(".tr_main_content").hide();
        $("a[href=#tr_tab]").parent().removeClass("active");

        $(".cotr_main_content").show();
        $(this).parent().addClass("active");
        return false;
    });

    $("a[href=#tr_tab]").click(function() {
        $(".tr_main_content").show();
        $(this).parent().addClass("active");

        $("a[href=#cotr_tab]").parent().removeClass("active");
        $(".cotr_main_content").hide();

        autosize.update($('textarea'));
        return false;
    });

    // Show/Hide notifications
    $(".notifications").click(function() {

        if(!$(".notif_block").is(":visible"))
        {
            $(".notif_block").show();
            return false;
        }
        else
        {
            $(".notif_block").hide();
        }
    });

    $(document).click(function() {
        $(".notif_block").hide();
    });
});


// Cookie Helpers
/**
 * Get cookie by name
 * @param string name
 * @returns {*}
 */
function getCookie(name) {
    var matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}

/**
 * Set cookie value by name
 * @param string name
 * @param string value
 * @param object options
 */
function setCookie(name, value, options) {
    options = options || {};

    var expires = options.expires;

    if (typeof expires == "number" && expires) {
        var d = new Date();
        d.setTime(d.getTime() + expires * 1000);
        expires = options.expires = d;
    }
    if (expires && expires.toUTCString) {
        options.expires = expires.toUTCString();
    }

    value = encodeURIComponent(value);

    var updatedCookie = name + "=" + value;

    for (var propName in options) {
        updatedCookie += "; " + propName;
        var propValue = options[propName];
        if (propValue !== true) {
            updatedCookie += "=" + propValue;
        }
    }

    document.cookie = updatedCookie;
}

/**
 * Delete cookie by name (make it expired)
 * @param string name
 */
function deleteCookie(name) {
    setCookie(name, "", {
        expires: -1
    })
}