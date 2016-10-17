var currentChunk = -1;
var firstVerse = 0;
var lastVerse = 0;
var chunks = [];
var lastCommentEditor;
var lastCommentAltEditor;
var hasChangesOnPage = false;
var autosaveTimer;

var eventSteps = {
    PRAY: "pray",
    CONSUME: "consume",
    DISCUSS: "discuss",
    PRE_CHUNKING: "pre-chunking",
    CHUNKING: "chunking",
    BLIND_DRAFT: "blind-draft",
    SELF_CHECK: "self-check",
    SELF_CHECK_FULL: "self-check-full",
    PEER_REVIEW: "peer-review",
    KEYWORD_CHECK: "keyword-check",
    CONTENT_REVIEW: "content-review",
    FINISHED: "finished",
};

$(document).ready(function() {

    $('[data-toggle="tooltip"]').tooltip();

    animateIntro();

    $.widget( "custom.iconselectmenu", $.ui.selectmenu, {
        _renderItem: function( ul, item ) {
            var li = $( "<li>" ),
                wrapper = $( "<div>", { text: item.label } );

            if ( item.disabled ) {
                li.addClass( "ui-state-disabled" );
            }

            $( "<span>", {
                style: item.element.attr( "data-style" ),
                "class": "ui-icon " + item.element.attr( "data-class" )
            })
                .appendTo( wrapper );

            return li.append( wrapper ).appendTo( ul );
        }
    });

    $( "#lang-select" )
        .iconselectmenu({
            width: 60,
            create: function( event, ui ) {
                var lang = getCookie("lang");
                lang = typeof lang != "undefined" ? lang : "en";
                $("#lang-select-button .ui-selectmenu-text").html(
                    "<img src='/app/templates/default/img/" + lang + ".png' width='16' height='12'>"
                );
            },
            select: function( event, ui ) {
                $("#lang-select-button .ui-selectmenu-text").html(
                    "<img src='/app/templates/default/img/" + ui.item.value + ".png' width='16' height='12'>"
                );
                window.location.href = "/lang/" + ui.item.value;
            }
        })
        .iconselectmenu( "menuWidget" )
        .addClass( "ui-menu-icons customicons" );

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
        var eventID = $(this).attr("data");
        var bookName = $(this).attr("data2");
        var stage = $(this).attr("data3");

        $("#applyEvent").trigger("reset");
        $(".errors").html("");
        $("label").removeClass("label_error");
        $(".bookName").text(bookName);
        $(".panel-title").text(bookName);
        $("#eventID").val(eventID);

        if(stage == "d1")
        {
            $(".checker_info").hide();
            $(".ftr").show();
            $(".fl2, .fl3").hide();
            $("input[name=userType]").val("translator");

            var yes = Language.yes;
            var no = Language.no;

            var btns = {};
            btns[yes] = function(){
                $( this ).dialog( "close" );
                $("#applyEvent").submit();
            };
            btns[no] = function(){
                $( this ).dialog( "close" );
                return false;
            };

            $(".confirm_message").text(Language.applyForEventConfirm);
            $( "#check-book-confirm" ).dialog({
                resizable: false,
                draggable: false,
                title: Language.applyForEventConfirmTitle,
                height: "auto",
                width: 500,
                modal: true,
                buttons: btns,
            });
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
            $(".event-content").css("left", 0);
        }

        return false;
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
                    $(".panel-close").click();
                    $(".alert_message").text(data.success);
                    $( "#dialog-message" ).dialog({
                        modal: true,
                        resizable: false,
                        draggable: false,
                        width: 500,
                        buttons: {
                            Ok: function() {
                                $( this ).dialog( "close" );
                                window.location = "/members";
                            }
                        }
                    });
                }
                else
                {
                    $(".alert_message").text(data.error);
                    $( "#dialog-message" ).dialog({
                        modal: true,
                        resizable: false,
                        draggable: false,
                        width: 500,
                        buttons: {
                            Ok: function() {
                                $( this ).dialog( "close" );
                            }
                        }
                    });

                    //$(".errors").html(data.error);

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
        $(this).parents(".form-panel").css("left", -9999);
    });


    // ------------------- Translation Flow ---------------------- //

    // Hide steps panel on small screens or if it was closed manually
    var panelClosed = getCookie("close_left_panel");

    if(typeof panelClosed != "undefined")
    {
        if(panelClosed == "true")
        {
            $("#translator_steps").removeClass("open")
                .addClass("closed");
            $("#translator_steps").animate({left: "-300px"}, 50, function() {
                $("#tr_steps_hide").removeClass("glyphicon-chevron-left")
                    .addClass("glyphicon-chevron-right");
            });
        }
    }
    else if($(window).width() < 1800)
    {
        $("#translator_steps").removeClass("open")
            .addClass("closed");
        $("#translator_steps").animate({left: "-300px"}, 500, function() {
            $("#tr_steps_hide").removeClass("glyphicon-chevron-left")
                .addClass("glyphicon-chevron-right");
        });
    }

    $(".peer_verse_ta, .blind_ta, .verse_ta").change(function() {

    });

    $(".peer_verse_ta, .blind_ta, .verse_ta").keyup(function() {
        hasChangesOnPage = true;
        $(".unsaved_alert").show();
    });

    $(".verse_ta:first").focus();

    $(".my_comment").each(function() {
        var img = $(this).parent(".comments").prev(".editComment");

        if(img.length > 0)
        {
            var src = img.attr("src");

            if($(this).text() == "")
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
                $("body").css("overflow", "hidden");
                setCookie("temp_tutorial", true, {expires: 365*24*60*60, path: "/"})
            }
        }

        if(step == eventSteps.BLIND_DRAFT || step == eventSteps.SELF_CHECK ||
            step == eventSteps.PEER_REVIEW || step == eventSteps.KEYWORD_CHECK ||
            step == eventSteps.CONTENT_REVIEW || step == eventSteps.SELF_CHECK_FULL)
        {
            autosaveTimer = setInterval(function() {
                if(hasChangesOnPage)
                {
                    $.ajax({
                            url: "/events/rpc/autosave_chunk",
                            method: "post",
                            data: {
                                eventID: eventID,
                                formData: $("#main_form").serialize()},
                            dataType: "json",
                            beforeSend: function() {

                            }
                        })
                        .done(function(data) {
                            if(data.success)
                            {
                                $(".unsaved_alert").hide();
                                hasChangesOnPage = false;
                            }
                            else
                            {
                                if(typeof data.errorType != "undefined")
                                {
                                    switch (data.errorType)
                                    {
                                        case "logout":
                                            window.location.href = "/members/login";
                                            break;

                                        case "verify":
                                            window.location.href = "/members";
                                            break;

                                        case "checkDone":
                                            hasChangesOnPage = false;
                                            $(".alert_message").text(data.error);
                                            $( "#dialog-message" ).dialog({
                                                modal: true,
                                                resizable: false,
                                                draggable: false,
                                                width: 500,
                                                buttons: {
                                                    Ok: function() {
                                                        $( this ).dialog( "close" );
                                                    }
                                                }
                                            });
                                            break;
                                    }
                                }
                                console.log(data.error);
                            }
                        })
                        .always(function() {

                        });
                }
            }, 3000);
        }
    }

    // Update information page periodically
    if(typeof isInfoPage != "undefined")
    {
        var infoUpdateTimer = setInterval(function() {
            $.ajax({
                url: "/events/rpc/get_info_update/"+eventID,
                method: "get",
                dataType: "html",
            })
                .done(function(data) {
                    switch (data)
                    {
                        case "login":
                            window.location.href = "/members/login";
                            break;

                        case "profile":
                            window.location.href = "/members/profile";
                            break;

                        case "not_verified":
                        case "not_started":
                        case "empty_no_permission":
                            window.location.href = "/members";
                            break;

                        default:
                            var openedItems = [];
                            $.each($(".section_header"), function () {
                                var isCollapsed = $(".section_arrow", $(this)).hasClass("glyphicon-triangle-right");
                                if(!isCollapsed)
                                    openedItems.push($(this).attr("data"));
                            });

                            $(".chapter_list").html(data);

                            $.each(openedItems, function (i,v) {
                                var section = $(".section_header[data="+v+"]");
                                var content = section.next(".section_content");
                                content.show(0);
                                $(".section_arrow", section)
                                    .removeClass("glyphicon-triangle-right")
                                    .addClass("glyphicon-triangle-bottom");
                                $(".section_title", section).css("font-weight", "bold");

                            });
                            openedItems = [];
                            break;
                    }
                })
                .always(function() {

                });
        }, 60000);
    }


    // Show/Hide Steps Panel
    $("#tr_steps_hide").click(function () {
        if($("#translator_steps").hasClass("open"))
        {
            $("#translator_steps").removeClass("open")
                .addClass("closed");
            $("#translator_steps").animate({left: "-300px"}, 500, function() {
                $("#tr_steps_hide").removeClass("glyphicon-chevron-left")
                    .addClass("glyphicon-chevron-right");
            });
            setCookie("close_left_panel", true, {expires: 365*24*60*60, path: "/"});
        }
        else
        {
            $("#translator_steps").removeClass("closed")
                .addClass("open");
            $("#translator_steps").animate({left: 0}, 500, function() {
            $("#tr_steps_hide").removeClass("glyphicon-chevron-right")
                .addClass("glyphicon-chevron-left");
            //deleteCookie("close_left_panel");
            setCookie("close_left_panel", false, {expires: 365*24*60*60, path: "/"});
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
        if(step == eventSteps.BLIND_DRAFT || step == eventSteps.SELF_CHECK)
            return;

        if(hasChangesOnPage)
        {
            var yes = Language.yes;
            var no = Language.no;

            var btns = {};
            btns[yes] = function(){
                hasChangesOnPage = false;
                $("#next_step").click();
            };
            btns[no] = function(){
                $( this ).dialog( "close" );
                return false;
            };

            $(".confirm_message").text(Language.save_changes_confirm);
            $( "#check-book-confirm" ).dialog({
                resizable: false,
                draggable: false,
                title: Language.save_changes_confirm_title,
                height: "auto",
                width: 500,
                modal: true,
                buttons: btns,
            });

            return false;
        }
        else
        {
            return true;
        }
    });

    $("#checker_submit").submit(function() {
        $.ajax({
            method: "post",
            data: $("#checker_submit").serialize(),
            dataType: "json",
            beforeSend: function() {

            }
        })
            .done(function(data) {
                if(data.success)
                {
                    var data = {
                        type: "checkDone",
                        eventID: eventID,
                        chkMemberID: chkMemberID,
                    };
                    socket.emit("system message", data);
                    window.location = "/members";
                }
                else
                {
                    console.log(data.errors);
                }
            })
            .always(function() {

            });

        return false;
    });

    setTimeout(function () {
        $(".verse").each(function() {
            var verseTa = $(".verse_ta, .peer_verse_ta", $(this).parent());
            var height = $(this).height()/verseTa.length;
            verseTa.css("min-height", height);
        });
    }, 100);

    if(typeof autosize == "function")
        autosize($('textarea'));

    // Add verse to chunk
    $(document).on("click", ".verse_number", function(e) {
        var p = $(this).parent().parent();
        var createChunkBtn = $(".create_chunk");

        //var verses = $(this).val().split("-");
        var verses = parseCombinedVerses($(this).val());

        for(var i=0; i<verses.length; i++)
        {
            var verse = verses[i];

            if((verse > 1 && chunks.length <= 0) ||
                !$(this).is(":checked") ||
                verse > (lastVerse + 1))
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
        $(".verse_p .create_chunk").text(Language.make_chunk+" "+fv+"-"+lv).show();
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

        $(".verse").each(function() {
            var verseTa = $(".verse_ta, .peer_verse_ta", $(this).parent());
            var height = $(this).height()/verseTa.length;
            verseTa.css("min-height", height);
        });
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


    $(document).on("click", ".notifa", function() {
        var item = $(this);

        var yes = Language.yes;
        var no = Language.no;

        var btns = {};
        btns[yes] = function(){
            var notifCount = parseInt($(".notif_count").text());
            notifCount--;

            $(".notif_count").text(notifCount);
            if(notifCount <= 0)
            {
                $(".notif_count").remove();
                $(".notif_block").html('<div class="no_notif">'+Language.no_notifs_msg+'</div>');
            }

            item.remove();
            window.open(item.attr("href"),"_blank");
            $( this ).dialog( "close" );
        };
        btns[no] = function(){
            $( this ).dialog( "close" );
        };

        $(".confirm_message").text(Language.check_book_confirm);
        $( "#check-book-confirm" ).dialog({
            resizable: false,
            draggable: false,
            title: Language.check_book_confirm_title,
            height: "auto",
            width: 500,
            modal: true,
            buttons: btns
        });

        return false;
    });

    // Show/Hide Comment Textarea
    $(document).on("click", ".editComment", function() {
        comments = $(this).next(".comments");
        var comment = $(".my_comment", comments).text();
        $(".editor").show();
        $("textarea", $(".comment_div")).val(comment).focus();
        lastCommentEditor = $(this);
        autosize.update($('textarea'));

        chapverse = lastCommentEditor.attr("data").split(":");
        $(".panel-title span").text(chapverse[1]);

        $(".other_comments_list").html("");

        $(".other_comments", comments).each(function() {
            $("<div />").addClass("other_comments").html($(this).html()).appendTo(".other_comments_list");
        });
    });

    $(".editor").click(function(e) {
        if(e.target.className == "comment_div" || e.target.className == "editor")
        {
            $(".editor").hide();
        }
    });

    $(".editor-close").click(function() {
        comments = lastCommentEditor.next(".comments");
        var comment = $(".my_comment", comments);
        var text = $("textarea", $(".comment_div")).val().trim();

        chapverse = lastCommentEditor.attr("data").split(":");

        if(comment.length <= 0)
            comment = $("<div />").addClass("my_comment").appendTo(comments);

        if(comment.text() != text)
        {
            $.ajax({
                    url: "/events/rpc/save_comment",
                    method: "post",
                    data: {
                        eventID: eventID,
                        chapter: chapverse[0],
                        verse: chapverse[1],
                        comment: text},
                    dataType: "json",
                    beforeSend: function() {
                        $(".commentEditorLoader").show();
                    }
                })
                .done(function(data) {
                    if(data.success)
                    {
                        $(".editor").hide();
                        var src = lastCommentEditor.attr("src");

                        data.text = unEscapeStr(data.text);

                        if(data.text.trim() == "")
                        {
                            src = src.replace(/edit_done.png/, "edit.png");
                            comment.remove();
                        }
                        else
                        {
                            src = src.replace(/edit.png/, "edit_done.png");
                            comment.text(data.text);
                        }
                        lastCommentEditor.attr("src", src);

                        num = comments.children().length > 0 ? comments.children().length : "";
                        lastCommentEditor.prev(".comments_number").addClass("hasComment").text(num);
                        if(num <= 0) lastCommentEditor.prev(".comments_number").removeClass("hasComment");

                        var data = {
                            type: "comment",
                            eventID: eventID,
                            verse: lastCommentEditor.attr("data"),
                            text: data.text
                        };
                        socket.emit("system message", data);
                    }
                    else
                    {
                        if(typeof data.error != "undefined")
                        {
                            $(".alert_message").text(data.error);
                            $( "#dialog-message" ).dialog({
                                modal: true,
                                resizable: false,
                                draggable: false,
                                width: 500,
                                buttons: {
                                    Ok: function() {
                                        $( this ).dialog( "close" );
                                        window.location.reload(true);
                                    }
                                }
                            });
                        }
                    }
                })
                .always(function() {
                    $(".commentEditorLoader").hide();
                });
        }
		else
		{
			$(".editor").hide();
		}
    });

    // Show/Hide Keywords List
    $(".keywords-list-close").click(function() {
        /*$(".keywords_list_container").hide();
        $("body").css("overflow", "auto");*/
    });

    $(".keywords_show").click(function() {
        /*if(keywords.length <= 0) {
            $(".alert_message").text(Language.no_keywords);
            $( "#dialog-message" ).dialog({
                modal: true,
                resizable: false,
                draggable: false,
                width: 500,
                buttons: {
                    Ok: function() {
                        $( this ).dialog( "close" );
                    }
                }
            });
            return;
        }*/

        /*if(!$(this).hasClass("shown"))
        {
            $(".verse_line").mark(keywords, { separateWordSearch: false, "accuracy": {
                "value": "exactly",
                "limiters": [",", "."]
            }});
            $(this).addClass("shown");
        }
        else
        {
            $(".verse_line").unmark();
            $(this).removeClass("shown");
        }*/


        /*$(".keywords_list_container").show();
        $("body").css("overflow", "hidden");*/
    });

    // Show/Hide Tutorial popup
    $(".tutorial-close").click(function() {
        $(".tutorial_container").hide();
        $("body").css("overflow", "auto");
    });

    $(".show_tutorial_popup").click(function() {
        $(".tutorial_container").show();
        $("body").css("overflow", "hidden");

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
            setCookie(step + "_tutorial", true, {expires: 365*24*60*60, path: "/"});
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
        $(".language").chosen();
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
    $(document).on("click", ".section_header", function() {
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

    // Demo mode switch
    $(".gl_ol_mode input[name=mode]").click(function() {
        var mode = $(this).val();

        setCookie("demo_mode", mode, {expires: 365*24*60*60});
        window.location.reload();
    });

    // Check if event has been started
    if($("#evnt_state_checker").val() == "error")
    {
        setInterval(function () {
            $.ajax({
                url: "/events/rpc/check_event",
                method: "post",
                data: {
                    eventID: $("#evntid").val(),
                },
                dataType: "json",
            })
            .done(function(data) {
                if(typeof data.success != "undefined" && data.success)
                {
                    window.location.reload();
                }
                else
                {
                    if(typeof data.error != "undefined")
                    {
                        window.location.href = "/";
                    }
                }
            });
        }, 60000);
    }

    $.each($(".datetime"), function () {
        var dateStr = $(this).attr("data");
        if(dateStr == "") return true;

        var date_options = {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            weekday: 'short',
            timezone: 'UTC',
        };

        var time_options = {
            hour: 'numeric',
            minute: 'numeric',
            timezone: 'UTC',
        };

        if($(".event_time_time").length <= 0) // Combine date and time options for full result
        {
            date_options.hour = 'numeric';
            date_options.minute = 'numeric';
        }

        var lang = getCookie("lang") != "undefined" ? getCookie("lang") : "en";

        var date = new Date(dateStr + " UTC");
        $(this).text(date.toLocaleString(lang, date_options));
        $(this).next(".event_time_time").text(date.toLocaleString(lang, time_options));
    });

    // Dashboard tabs switch
    $(".my_tab").click(function () {
        var id = $(this).attr("id");

        $(".my_content").removeClass("shown");
        $(".my_tab").removeClass("active");

        $(this).addClass("active");
        $("#"+id+"_content").addClass("shown");

        return false;
    });

    if(!$(".my_tab:first").hasClass("active"))
        $(".my_tab:first").addClass("active");

    if(!$(".my_content:first").hasClass("shown"))
        $(".my_content:first").addClass("shown");
});

function animateIntro() {
    var  grl=$( "#ground-left" );
    var  grr=$( "#ground-right" );
    var  grc=$( "#ground-center" );
    var  cll=$( "#cloud-left" );
    var  clr=$( "#cloud-right" );

    grc.delay(0).animate(
        {bottom: 0},1300, "linear");
    grl.delay(400).animate(
        {bottom: 0},1400, "linear");
    grr.delay(400).animate(
        {bottom: 0},1400, "linear"); //easeOutBounce

    cll.delay(1300).animate({  attrRotate: 90 }, {
        step: function(now,fx) {
            tt = (now-90)/90;
            $(this).css('-webkit-transform','rotate('+(tt*28)+'deg)');
            $(this).css('-moz-transform','rotate('+(tt*28)+'deg)');
            $(this).css('-ms-transform','rotate('+(tt*28)+'deg)');
            $(this).css('-o-transform','rotate('+(tt*28)+'deg)');
            $(this).css('transform','rotate('+(tt*28)+'deg)');
            $(this).css('bottom',(-(tt)*(tt)*50)+'%');
            $(this).css('left',(tt*33)+'%');
        },
        duration:1300
    },'linear');
    clr.delay(1300).animate({  attrRotate: 90 }, {
        step: function(now,fx) {
            tt = (now-90)/90;
            $(this).css('-webkit-transform','rotate('+(-tt*28)+'deg)');
            $(this).css('-moz-transform','rotate('+(-tt*28)+'deg)');
            $(this).css('-ms-transform','rotate('+(-tt*28)+'deg)');
            $(this).css('-o-transform','rotate('+(-tt*28)+'deg)');
            $(this).css('transform','rotate('+(-tt*28)+'deg)');
            $(this).css('bottom',(-(tt)*(tt)*50)+'%');
            $(this).css('right',(tt*33)+'%');
        },
        duration:1300
    },'linear');
}

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
function deleteCookie(name, options) {
    setCookie(name, "", {
        expires: -1,
        path: "/"
    })
}

function parseCombinedVerses(verse)
{
    var versesArr = [];
    var verses = verse.split("-");

    if(verses.length < 2)
    {
        versesArr.push(parseInt(verse));
        return versesArr;
    }

    var fv = parseInt(verses[0]);
    var lv = parseInt(verses[1]);

    for(var i=fv; i <= lv; i++)
    {
        versesArr.push(i);
    }

    return versesArr;
}

function unEscapeStr(string) {
    return $('<div/>').html(string).text();
}