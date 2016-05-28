var currentChunk = -1;
var firstVerse = 0;
var lastVerse = 0;
var chunks = [];
var lastCommentEditor;
var lastCommentAltEditor;
var hasChangesOnPage = false;

$(document).ready(function() {

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
        $("label").removeClass("label_error");
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
                $("label").removeClass("label_error");

                if(data.success)
                {
                    alert(data.success);
                    window.location = "/members";
                }
                else
                {
                    $(".errors").html(data.error);
                    if(typeof data.errors != "undefined")
                    {
                        $.each(data.errors, function(k, v) {
                            $("label."+k).addClass("label_error");
                        });
                    }
                }
            })
            .always(function() {
                $(".applyEventLoader").hide();
            });

        e.preventDefault();
    });

    $(".panel-close").click(function() {
        $(this).parents(".form-panel").hide();
    });


    // ------------------- Translation Flow ---------------------- //

    // Hide steps panel on small screens or if it was closed manually
    var panelClosed = getCookie("close_left_panel");

    if(typeof panelClosed != "undefined" && panelClosed == "true")
    {
        $("#translator_steps").removeClass("open")
            .addClass("closed");
        $("#translator_steps").animate({left: "-250px"}, 50, function() {
            $("#tr_steps_hide").removeClass("glyphicon-chevron-left")
                .addClass("glyphicon-chevron-right");
        });
    }
    else if($(window).width() < 1800)
    {
        $("#translator_steps").removeClass("open")
            .addClass("closed");
        $("#translator_steps").animate({left: "-250px"}, 500, function() {
            $("#tr_steps_hide").removeClass("glyphicon-chevron-left")
                .addClass("glyphicon-chevron-right");
        });
    }

    $(".comment_ta, .peer_verse_ta").change(function() {
        hasChangesOnPage = true;
        $(".unsaved_alert").show();
    });

    $(".verse_ta:first").focus();

    $(".comment_ta").each(function() {
        var img = $(".editComment", $(this).parents(".editor_area"));

        if(img.length > 0)
        {
            var src = img.attr("src");

            if($(this).val().trim() == "")
            {
                src = src.replace(/edit_done.png/, "edit.png");
                img.attr("src", src);
            }
            else
            {
                src = src.replace(/edit.png/, "edit_done.png");
                img.attr("src", src);
            }
        }
    });

    $(".commentAltText").each(function() {
        var img = $(".editCommentAlt", $(this).parents(".verse_with_note"));

        if(img.length > 0)
        {
            var src = img.attr("src");

            if($(this).text().trim() == "")
            {
                src = src.replace(/edit_done.png/, "edit.png");
                img.attr("src", src);
            }
            else
            {
                src = src.replace(/edit.png/, "edit_done.png");
                img.attr("src", src);
            }
        }
    });

    if(typeof step != "undefined")
    {
        var role = $("#hide_tutorial").attr("data2");
        var tutorialCookie = typeof role != "undefined" && role == "checker" ?
            getCookie(step + "_checker_tutorial") : getCookie(step + "_tutorial");

        if(typeof tutorialCookie == "undefined")
        {
            var tempTutorialCookie = getCookie("temp_tutorial");
            if(typeof tempTutorialCookie == "undefined")
            {
                $(".tutorial_container").show();
                setCookie("temp_tutorial", true, {expires: 365*24*60*60})
            }
        }
    }


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
            setCookie("close_left_panel", true, {expires: 365*24*60*60});
        }
        else
        {
            $("#translator_steps").removeClass("closed")
                .addClass("open");
            $("#translator_steps").animate({left: 0}, 500, function() {
            $("#tr_steps_hide").removeClass("glyphicon-chevron-right")
                .addClass("glyphicon-chevron-left");
            deleteCookie("close_left_panel");
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

    $("#next_step").click(function() {
        if(hasChangesOnPage)
        {
            if(confirm("There are some changes made on this page. Do you want to continue without saving?"))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return true;
        }
    });

    $(".verse").each(function(index) {
        var verseTa = $(".verse_ta, .peer_verse_ta", $(this).parent());
        var height = parseInt($(this).css("height"))/verseTa.length;
        verseTa.css("min-height", height);
    });

    autosize($('textarea'));

    // Add verse to chunk
    $(document).on("click", ".verse_number", function(e) {
        var p = $(this).parent().parent();
        var createChunkBtn = $(".create_chunk");

        var verses = $(this).val().split("-");

        for(var i=0; i<verses.length; i++)
        {
            var verse = parseInt(verses[i]);

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

            lastVerse = verse;
        }

        $("#chunks_array").val(JSON.stringify(chunks));

        $(".verse_p .create_chunk").remove();
        p.append(createChunkBtn);

        fv = firstVerse < 10 ? "0"+firstVerse : firstVerse;
        lv = verse < 10 ? "0"+verse : verse;
        $(".verse_p .create_chunk").text("Make chunk "+fv+"-"+lv).show();
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

    // Toggle Side by Side view on content review page (checker)
    $("#side_by_side_toggle").click(function() {
        if($(this).is(":checked"))
        {
            $(".side_by_side_content").show();
            $(".one_side_content").hide();
        }
        else
        {
            $(".side_by_side_content").hide();
            $(".one_side_content").show();
        }
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


    // Show/Hide Comment Textarea
    $(".editComment").click(function() {
        var comment = $(this).next(".comment_ta").val();
        $(".editor").show();
        $("textarea", $(".comment_div")).val(comment).focus();
        lastCommentEditor = $(this);
        autosize.update($('textarea'));
    });

    $(".editor").click(function(e) {
        if(e.target.className == "comment_div" || e.target.className == "editor")
        {
            $(".editor").hide();
        }
    });

    $(".editor-close").click(function() {
        $(".editor").hide();

        var text = $("textarea", $(".comment_div")).val();
        var src = lastCommentEditor.attr("src");

        if(text.trim() == "")
        {
            src = src.replace(/edit_done.png/, "edit.png");
            lastCommentEditor.attr("src", src);
            if(lastCommentEditor.next("textarea").val() != text)
                lastCommentEditor.next("textarea").val("").trigger("change");
            else
                lastCommentEditor.next("textarea").val("");
        }
        else
        {
            src = src.replace(/edit.png/, "edit_done.png");
            lastCommentEditor.attr("src", src);
            if(lastCommentEditor.next("textarea").val() != text)
                lastCommentEditor.next("textarea").val(text).trigger("change");
            else
                lastCommentEditor.next("textarea").val(text);
        }
    });


    // Show/Hide Checker Comment Textarea
    $(document).on("click", ".editCommentAlt", function() {
        var commentAlt = $(this).next(".commentAltText").text();
        $(".alt_editor").show();
        $("textarea", $(".alt_comment_div")).val(commentAlt).focus();
        lastCommentAltEditor = $(this);
        autosize.update($('textarea'));
    });

    $(".alt_editor").click(function(e) {
        if(e.target.className == "alt_comment_div" || e.target.className == "alt_editor")
        {
            $(".alt_editor").hide();
        }
    });

    $(".alt_editor-close").click(function() {
        var text = $("textarea", $(".alt_comment_div")).val().trim();

        if(lastCommentAltEditor.next("span").text() != text)
        {
            var tID = lastCommentAltEditor.nextAll(".tID").val();
            var verse = lastCommentAltEditor.nextAll(".verseNum").val();

            $.ajax({
                    url: "/events/rpc/save_comment_alt",
                    method: "post",
                    data: {tID: tID, verse: verse, comment: text.trim()},
                    dataType: "json",
                    beforeSend: function() {
                        $(".commentEditorLoader").show();
                    }
                })
                .done(function(data) {
                    if(data.success)
                    {
                        $(".alt_editor").hide();
                        var src = lastCommentAltEditor.attr("src");

                        if(data.text == "")
                        {
                            src = src.replace(/edit_done.png/, "edit.png");
                            lastCommentAltEditor.next("span").text("");
                        }
                        else
                        {
                            src = src.replace(/edit.png/, "edit_done.png");
                            lastCommentAltEditor.next("span").text(data.text);
                        }
                        lastCommentAltEditor.attr("src", src);
                    }
                    else
                    {
                        if(typeof data.error != "undefined")
                        {
                            alert(data.error);
                        }
                    }
                })
                .always(function() {
                    $(".commentEditorLoader").hide();
                });
        }
        else
        {
            $(".alt_editor").hide();
        }
    });

    // Show/Hide Tutorial popup
    $(".tutorial-close").click(function() {
        $(".tutorial_container").hide();
    });

    $(".show_tutorial_popup").click(function() {
        $(".tutorial_container").show();

        var role = $("#hide_tutorial").attr("data2");

        var cookie = typeof role != "undefined" && role == "checker" ?
            getCookie(step + "_checker_tutorial") : getCookie(step + "_tutorial");

        if(typeof cookie != "undefined")
        {
            $("#hide_tutorial").prop("checked", true);
        }
    });

    $("#hide_tutorial").change(function() {
        var step = $(this).attr("data");
        if($(this).is(":checked"))
        {
            setCookie(step + "_tutorial", true, {expires: 365*24*60*60});
        }
        else
        {
            deleteCookie(step + "_tutorial");
        }
    });


    // Profile form
    var langs = $(".langs option:selected");
    if(langs.length > 0)
        $(".langs").prop("disabled", false);

    $(".language").change(function() {
        var parent = $(this).parents(".language_block");
        if($(this).val() != "")
        {
            $(".fluency", parent).prop("name", "lang["+$(this).val()+"][fluency]").prop("disabled", false);
            $(".geo_years", parent).prop("name", "lang["+$(this).val()+"][geo_years]").prop("disabled", false);
        }
        else
        {
            $(".fluency", parent).prop("name", "").prop("disabled", true);
            $(".geo_years", parent).prop("name", "").prop("disabled", true);
        }
    });

    $(".language-close").click(function() {
        $(".language_container").css("left", "-9999px");
    });

    $(".language_add").click(function() {
        $(".language_container").css("left", 0);

        $(".language").val("");
        $(".fluency").prop("checked", false).prop("disabled", true);
        $(".geo_years").prop("checked", false).prop("disabled", true);
        $(".fluency, .geo_years").trigger("change");
        $(".language").trigger("chosen:updated");
    });

    $(".add_lang").click(function() {
        var lang = $(".language").val();
        var langName = $(".language option:selected").text();
        var fluency = $(".fluency:checked").val();
        var geo_years = $(".geo_years:checked").val();
        var option = $(".langs option[value^='"+lang+":']");

        if(option.length <= 0) {
            $(".langs").append("<option value='"+lang+":"+fluency+":"+geo_years+"'>" + langName + "</option>");
            option = $(".langs option[value^='"+lang+":']");
        }
        else
        {
            option.val(lang+":"+fluency+":"+geo_years)
        }

        option.prop("selected", true);

        $(".language_container").css("left", "-9999px");

        $(".langs").prop("disabled", false).trigger("chosen:updated");
    });

    $(".fluency, .geo_years").change(function() {
        var fluency = $(".fluency:checked").val();
        var geo_years = $(".geo_years:checked").val();

        if(typeof fluency != "undefined" && typeof geo_years != "undefined")
        {
            $(".add_lang").prop("disabled", false);
        }
        else
        {
            $(".add_lang").prop("disabled", true);
        }
    });

    $(".langs option").each(function() {
        var val = $(this).val();
        var langs = val.split(":");

        if(langs.length != 3) {
            $(this).remove();
            return true
        }

        $(this).text($(".language option[value="+langs[0]+"]").text());
    });

    // Mast events number test
    $("input[name=mast_evnts]").change(function() {
        if($(this).val() > 1)
        {
            $("input[name^='mast_role']").prop("disabled", false);
        }
        else
        {
            $("input[name^='mast_role']").prop("disabled", true); //.prop("checked", false);
        }
    });

    if($("input[name=mast_evnts]:checked").val() > 1)
    {
        $("input[name^='mast_role']").prop("disabled", false);
    }
    else
    {
        $("input[name^='mast_role']").prop("disabled", true); //.prop("checked", false);
    }


    // Facilitator test
    $("input[name=mast_facilitator]").change(function() {
        if($(this).val() == 1)
        {
            $("input[name=org]").prop("disabled", false);
            $("input[name=ref_person]").prop("disabled", false);
            $("input[name=ref_email]").prop("disabled", false);
        }
        else
        {
            $("input[name=org]").prop("disabled", true); //.prop("checked", false);
            $("input[name=ref_person]").prop("disabled", true); //.val("");
            $("input[name=ref_email]").prop("disabled", true); //.val("");
        }
    });

    if($("input[name=mast_facilitator]:checked").val() > 0)
    {
        $("input[name=org]").prop("disabled", false);
        $("input[name=ref_person]").prop("disabled", false);
        $("input[name=ref_email]").prop("disabled", false);
    }
    else
    {
        $("input[name=org]").prop("disabled", true); //.prop("checked", false);
        $("input[name=ref_person]").prop("disabled", true); //.val("");
        $("input[name=ref_email]").prop("disabled", true); //.val("");
    }

    // Event information accordion
    $(".section_header").click(function() {
        var content = $(this).next(".section_content");
        var isCollapsed = $(".section_arrow", $(this)).hasClass("glyphicon-triangle-right");

        if(!isCollapsed)
        {
            content.hide(300);
            $(".section_arrow", $(this))
                .removeClass("glyphicon-triangle-bottom")
                .addClass("glyphicon-triangle-right");
            $(".section_title", $(this)).css("font-weight", "normal");
        }
        else
        {
            content.show(300);
            $(".section_arrow", $(this))
                .removeClass("glyphicon-triangle-right")
                .addClass("glyphicon-triangle-bottom");
            $(".section_title", $(this)).css("font-weight", "bold");
        }
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