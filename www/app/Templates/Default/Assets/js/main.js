let currentChunk = -1;
let firstVerse = 0;
let lastVerse = 0;
let chunks = [];
let lastCommentEditor;
let lastCommentAltEditor;
let hasChangesOnPage = false;
let hasLangInputChangesOnPage = false;
let autosaveTimer;
let autosaveRequest;
let mouseTimeout;
let footnoteCallback;
let isHighlighting = false; // Fix for mobile devices
let searchTimeout;

const EventSteps = {
    NONE: "none",
    PRAY: "pray",
    CONSUME: "consume",
    HIGHLIGHT: "highlight",
    VERBALIZE: "verbalize",
    CHUNKING: "chunking",
    READ_CHUNK: "read-chunk",
    BLIND_DRAFT: "blind-draft",
    MULTI_DRAFT: "multi-draft",
    REARRANGE: "rearrange",
    SYMBOL_DRAFT : "symbol-draft",
    THEO_CHECK: "theo-check",
    BT_CHECK : "bt-check",
    SELF_CHECK: "self-check",
    PEER_REVIEW: "peer-review",
    KEYWORD_CHECK: "keyword-check",
    CONTENT_REVIEW: "content-review",
    FINAL_REVIEW: "final-review",
    FINISHED: "finished",
};

const EventCheckSteps = {
    NONE: "none",
    PRAY: "pray",
    CONSUME: "consume",
    FST_CHECK: "fst-check",
    SND_CHECK: "snd-check",
    PEER_REVIEW_L2: "peer-review-l2",
    KEYWORD_CHECK_L2: "keyword-check-l2",
    PEER_REVIEW_L3: "peer-review-l3",
    PEER_EDIT_L3: "peer-edit-l3",
    FINISHED: "finished",
};

const EventMembers = {
    TRANSLATOR: "translator",
    L2_CHECKER: "checker_l2",
    L3_CHECKER: "checker_l3",
};

$(document).ready(function() {

    const body = $("body");

    Offline.options = {
        checks: {
            xhr: {
                url: "/events/rpc/check_internet"
            }
        }
    }

    Offline.on("confirmed-up", function () {
        //console.log("internet is up");
    });

    Offline.on("confirmed-down", function () {
        console.log("internet is down");
    });

    $(window).bind('beforeunload', function(){
        localStorage.setItem("prev", window.location.href);
    });

    $("a, button").click(function (e) {
        if(Offline.state === "up")
            return true;

        renderPopup(Language.connectionLostMessage);
        return false;
    });

    setTimeout(function () {
        $('[data-toggle="tooltip"]').tooltip();

        if(typeof autosize == "function")
            autosize.update($('textarea'));
    }, 2000);

    if(typeof autosize == "function")
        autosize($('textarea'));

    animateIntro();

    $.widget( "custom.iconselectmenu", $.ui.selectmenu, {
        _renderItem: function( ul, item ) {
            const li = $( "<li>" ),
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
                const lang = getCookie("lang") || "en";
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
        $('#sof_modal').modal('show');
        return false;
    });

    $("#sof_agree").click(function() {
        $("#sof").prop('checked', true);
        $('#sof_modal').modal('hide');
        $("#sof").closest("label").popover('destroy');
    });

    $("#sof_cancel").click(function() {
        $("#sof").prop('checked', false);
        $('#sof_modal').modal('hide');
    });

    // Terms of use block
    $("#tou").click(function() {
        $('#tou_modal').modal('show');
        return false;
    });

    $("#tou_agree").click(function() {
        $("#tou").prop('checked', true);
        $("#tou").closest("label").popover('destroy');
        $('#tou_modal').modal('hide');
    });

    $("#tou_cancel").click(function() {
        $("#tou").prop('checked', false);
        $('#tou_modal').modal('hide');
    });

    // Old/New Testament Tabs
    $("a[href=#new_test]").click(function(e) {
        $("#old_test").hide();
        $("a[href=#old_test]").parent().removeClass("active");

        $("#new_test").show();
        $(this).parent().addClass("active");
        setCookie("testTab", "new_test");
        return false;
    });

    $("a[href=#old_test]").click(function() {
        $("#old_test").show();
        $(this).parent().addClass("active");

        $("a[href=#new_test]").parent().removeClass("active");
        $("#new_test").hide();
        setCookie("testTab", "old_test");
        return false;
    });

    const testTab = getCookie("testTab");
    if(typeof testTab != "undefined")
    {
        $("a[href=#"+testTab+"]").click();
    }

    // Apply for event as translator/checker
    // Start event
    $(".applyEvent").click(function(e) {
        if($(this).hasClass("eventFull"))
        {
            renderPopup(Language.eventClosed);
            return false;
        }

        const eventID = $(this).attr("data");
        const bookName = $(this).attr("data2");
        const stage = $(this).attr("data3");

        $("#applyEvent").trigger("reset");
        $(".errors").html("");
        $("label").removeClass("label_error");
        $(".bookName").text(bookName);
        $("#eventID").val(eventID);

        if(stage === "d1")
        {
            $(".checker_info").hide();
            $(".ftr").show();
            $(".fl2, .fl3").hide();
            $("input[name=userType]").val("translator");

            renderConfirmPopup(Language.applyForEventConfirmTitle, Language.applyForEventConfirm, function () {
                $(this).dialog("close");
                $("#applyEvent").submit();
            }, function () {
                $(this).dialog("close");
            });
        }
        else
        {
            $(".panel-title.applyForm").text(bookName);
            if(stage === "l2")
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

        e.preventDefault();
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
                    renderPopup(data.success, function () {
                        window.location = "/events";
                    });
                }
                else
                {
                    renderPopup(data.error);
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
        $(this).closest(".form-panel").css("left", -9999);
    });


    // ------------------- Translation Flow ---------------------- //

    // Hide steps panel on small screens or if it was closed manually
    const panelClosed = getCookie("close_left_panel");
    if(typeof panelClosed != "undefined" && panelClosed === "true")
    {
        $("#translator_steps").removeClass("open")
            .addClass("closed");
        $("#translator_steps").animate({left: "-300px"}, 50, function() {
            $("#tr_steps_hide").removeClass("glyphicon-chevron-left")
                .addClass("glyphicon-chevron-right");
        });
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

    $(".peer_verse_ta, .blind_ta, .verse_ta").on("keyup paste", function() {
        hasChangesOnPage = true;
        $(".unsaved_alert").show();
    });

    $(".verse_ta:first").focus();

    $(".my_comment").each(function() {
        const pencil = $(this).parent(".comments").prev(".editComment");

        if(pencil.length > 0)
        {
            if($(this).text() === "")
            {
                pencil.css("color", "black");
            }
            else
            {
                pencil.css("color", "#a52f20");
            }
        }
    });

    if(typeof step != "undefined")
    {
        if(step === EventSteps.BLIND_DRAFT || step === EventSteps.SELF_CHECK ||
            step === EventSteps.PEER_REVIEW || step === EventSteps.KEYWORD_CHECK ||
            step === EventSteps.CONTENT_REVIEW || step === EventSteps.MULTI_DRAFT ||
            step === EventSteps.SYMBOL_DRAFT || step === EventSteps.REARRANGE || step === EventSteps.THEO_CHECK || // For SUN
            step === EventCheckSteps.FST_CHECK || step === EventCheckSteps.SND_CHECK || // For Level 2 Check
            step === EventCheckSteps.PEER_REVIEW_L2 ||
            step === EventCheckSteps.PEER_EDIT_L3)
        {
            autosaveTimer = setInterval(function() {
                if(typeof isDemo != "undefined" && isDemo)
                {
                    hasChangesOnPage = false;
                    $(".unsaved_alert").hide();
                }

                if(hasChangesOnPage)
                {
                    let postData = $("#main_form").serializeArray();
                    postData.push({"name":"eventID","value":eventID});

                    autosaveRequest = $.ajax({
                        url: "/events/rpc/autosave_chunk",
                        method: "post",
                        data: postData,
                        dataType: "json",
                        beforeSend: function() {

                        }
                    })
                        .done(function(data) {
                            if(data.success)
                            {
                                $(".unsaved_alert").hide();
                                hasChangesOnPage = false;
                                //localStorage.removeItem(item);
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
                                            window.location.href = "/";
                                            break;

                                        case "noChange":
                                            $(".unsaved_alert").hide();
                                            hasChangesOnPage = false;
                                            break;

                                        case "checkDone":
                                            $(".unsaved_alert").hide();
                                            hasChangesOnPage = false;
                                            renderPopup(data.error);
                                            break;

                                        case "json":
                                            renderPopup(data.error);
                                            break;
                                    }
                                }
                                console.log(data.error);
                            }
                        })
                        .error(function (xhr, status, error) {
                            debug(status);
                            debug(error);
                            //localStorage.setItem(item, $("#main_form").serialize());
                            //hasChangesOnPage = false;
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
        setInterval(function() {
            let tm = typeof tMode != "undefined" && $.inArray(tMode, ["sun"]) > -1 ? "-" + tMode : "";

            if(typeof isOdb != "undefined") tm = "-odb" + tm;

            const mm = typeof manageMode != "undefined" ? "-"+manageMode : "";

            $.ajax({
                url: "/events/information" + tm + mm + "/" + eventID,
                method: "get",
                dataType: "json",
            })
                .done(function(data) {
                    if(data.success)
                    {
                        // Update overall progress value
                        if(data.progress > 0) {
                            $(".progress_all").removeClass("zero");
                            $(".progress_all .progress-bar").css('width', Math.floor(data.progress)+"%")
                                .attr('aria-valuenow', Math.floor(data.progress))
                                .text(Math.floor(data.progress)+"%");
                        }

                        // Update members list
                        $.each(data.members, function (memberID, member) {
                            if(isNaN(parseInt(memberID))) return true;

                            memberID = parseInt(memberID);

                            if($(".members_list .member_item[data="+memberID+"]").length <= 0)
                            {
                                const memberItem = $("<div></div>").appendTo(".members_list")
                                    .addClass("member_item")
                                    .attr("data", memberID);

                                memberItem.append('<span class="online_indicator glyphicon glyphicon-record">&nbsp;</span>');
                                memberItem.append('<span class="member_uname">'+member.name+'</span> ');
                                memberItem.append('<span class="member_admin">'+(data.admins.indexOf(memberID) > -1 ? "("+Language.facilitator+") " : "")+'</span>');
                                memberItem.append('<span class="online_status">'+Language.statusOnline+'</span>');
                                memberItem.append('<span class="offline_status">'+Language.statusOffline+'</span>');
                            }
                        });

                        // Update chapters list
                        let openedItems = [];
                        $.each($(".section_header"), function () {
                            const isCollapsed = $(".section_arrow", $(this)).hasClass("glyphicon-triangle-right");
                            if(!isCollapsed)
                                openedItems.push($(this).attr("data"));
                        });

                        $(".chapter_list").html(data.html);

                        $.each(openedItems, function (i,v) {
                            const section = $(".section_header[data="+v+"]");
                            const content = section.next(".section_content");
                            content.show(0);
                            $(".section_arrow", section)
                                .removeClass("glyphicon-triangle-right")
                                .addClass("glyphicon-triangle-bottom");
                            $(".section_title", section).css("font-weight", "bold");

                        });
                        openedItems = [];
                        localizeDate();
                    }
                    else
                    {
                        switch (data.errorType)
                        {
                            case "logout":
                                window.location.href = "/members/login";
                                break;

                            case "not_started":
                            case "empty_no_permission":
                                window.location.href = "/";
                                break;
                        }
                    }
                })
                .always(function() {

                });
        }, 60000);
    }

    body.on("mouseover", ".more_chunks", function (e) {
        e.stopPropagation();

        const blind = $(this).find(".chunks_blind");
        const parent = $(this);

        if(blind.length <= 0) return false;

        if(typeof $(this).data("e") != "undefined"
            && $(this).data("e").currentTarget === e.currentTarget)
            clearTimeout(mouseTimeout);
        parent.css("height", 140).css("overflow-y", "auto");
        blind.css("background-image", "none")
            .removeClass("glyphicon-triangle-bottom");

        $(this).removeData("e");
    });

    body.on("mouseout", ".more_chunks", function (e) {
        e.stopPropagation();

        const blind = $(this).find(".chunks_blind");
        const parent = $(this);

        if(blind.length <= 0) return false;
        $(this).data("e", e);

        mouseTimeout = setTimeout(function () {
            parent.css("height", 70).css("overflow", "hidden");
            parent[0].scrollTop = 0;
            blind.css("background-image", "linear-gradient(to bottom, rgba(255, 255, 255, 0.3) 0px, rgba(255, 255, 255, 0.9) 100%)")
                .addClass("glyphicon-triangle-bottom");
        }, 100);
    });

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
                setCookie("close_left_panel", false, {expires: 365*24*60*60, path: "/"});
            });
        }
    });

    $("form #confirm_step").prop("checked", false);
    $("form #next_step").prop("disabled", true);

    // Confirm to go to the next step
    $("#confirm_step").change(function() {
        if($(this).is(":checked"))
            $("#next_step").prop("disabled", false);
        else
            $("#next_step").prop("disabled", true);
    });

    $("#next_step").click(function(e) {
        $this = $(this);

        if(hasChangesOnPage || hasLangInputChangesOnPage)
        {
            renderPopup(Language.saveChangesConfirm, function () {
                $(this).dialog("close");
            }, function () {
                $( this ).dialog("close");
            });

            e.preventDefault();
            return false;
        }

        const sending = $this.data("sending");
        if(sending)
        {
            setTimeout(function() {$this.data("sending", false);}, 5000)
            e.preventDefault();
        }
        else
        {
            $this.data("sending", true);
            return true;
        }
    });

    $("#checker_submit").submit(function(e) {
        e.preventDefault();

        // Used for Level 3 check
        const checkerStep = $("input[name=step]");

        if(window.opener != null)
        {
            window.opener.$(".check1 .event_link a[data="+eventID+"_"+chkMemberID+"]")
                .closest(".event_block").remove();
        }

        $.ajax({
            method: "post",
            data: $("#checker_submit").serialize(),
            dataType: "json",
            beforeSend: function() {
                if($(".checkerLoader").length <= 0) {
                    $(".ui-dialog-buttonset")
                        .prepend('<img src="/templates/default/assets/img/loader.gif" style="margin-right:10px" width="32" class="checkerLoader">');
                }
                $(".checkerLoader").show();
                $(".ui-dialog-buttonset button").prop("disabled", true);
            }
        })
            .done(function(data) {
                if(data.success)
                {
                    const msg = {
                        type: "checkDone",
                        eventID: eventID,
                        chkMemberID: chkMemberID,
                    };
                    socket.emit("system message", msg);

                    if(window.opener != null)
                    {
                        if(checkerStep.length > 0 && checkerStep.val() === EventCheckSteps.PEER_REVIEW_L3)
                        {
                            window.location.reload();
                        }
                        else
                        {
                            window.opener.$(".check1 .event_link a[data="+eventID+"_"+chkMemberID+"]").parent(".event_block").remove();
                            window.close();
                        }
                    }
                    else
                    {
                        window.location = "/events";
                    }
                }
                else
                {
                    let message = "";
                    $.each(data.errors, function(i, v) {
                        message += v + "<br>";
                    });

                    if (typeof data.kw_exist != "undefined") {
                        renderConfirmPopup(Language.skip_keywords, Language.skip_keywords_message,
                            function () {
                                $("input[name=skip_kw]").val(1);
                                $("input[name=confirm_step]").prop("checked", true);
                                $("#checker_submit").submit();
                                $( this ).dialog("close");
                            },
                            function () {
                                $("#confirm_step").prop("checked", false);
                                $("#next_step").prop("disabled", true);
                                $( this ).dialog("close");
                            },
                            function () {
                                $("#confirm_step").prop("checked", false);
                                $("#next_step").prop("disabled", true);
                                $( this ).dialog("close");
                            })
                    } else {
                        renderPopup(message);
                    }
                    console.log(data);
                }
            })
            .always(function() {
                $(".checkerLoader").hide();
                $(".ui-dialog-buttonset button").prop("disabled", false);
            });

        return false;
    });

    setTimeout(function () {
        $(".verse").each(function() {
            const verseTa = $(".verse_ta, .peer_verse_ta", $(this).parent());
            const height = $(this).height()/verseTa.length;
            verseTa.css("min-height", height);
        });
    }, 300);

    // Add verse to chunk
    $(document).on("click", ".verse_number", function(e) {
        const p = $(this).parent().parent();
        const createChunkBtn = $(".clone.create_chunk").clone();
        const resetBtn = $(".clone.chunks_reset").clone();

        createChunkBtn.removeClass("clone");
        resetBtn.removeClass("clone");

        const verses = parseCombinedVerses($(this).val());
        let verse;

        if($(this).is(":checked")) // Select verse
        {
            for(let i=0; i<verses.length; i++)
            {
                verse = verses[i];

                // Do not check if there is no checked verses and one checks any verse after first
                // Do not check if one skips verse(s)
                if((verse > 1 && chunks.length <= 0) ||
                    verse > (lastVerse + 1))
                {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }

                if(currentChunk < 0) // Create first chunk
                {
                    chunks[0] = [];
                    chunks[0].push(verse);
                    currentChunk = 0;
                    firstVerse = verse;

                    //$(".chunks_reset").show();
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
            $(".verse_p .create_chunk").remove();
            $(".verse_p .chunks_reset").remove();

            p.append(createChunkBtn);
            p.append(resetBtn);
        }
        else                     // Deselect verse from the end
        {
            const prep_p = p.prev(".verse_p");

            for(let i=verses.length - 1; i>=0; i--)
            {
                verse = verses[i];

                if(verse != lastVerse)
                {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }

                if(typeof chunks[currentChunk] == "undefined")
                {
                    $(".chunk_divider:last").remove();
                    if(typeof chunks[currentChunk-1] != "undefined")
                        firstVerse = chunks[currentChunk-1][0];
                    currentChunk--;
                }

                const chunk = typeof chunks[currentChunk] != "undefined" ? currentChunk : currentChunk-1;
                const vIndex = chunks[chunk].indexOf(verse);
                chunks[chunk].splice(vIndex, 1);

                lastVerse = verse-1;

                if(chunks[chunk].length <= 0)
                {
                    chunks.splice(chunk, 1);
                }

                if(chunks.length <= 0)
                {
                    firstVerse = 0;
                    //$(".chunks_reset").hide();
                }
            }

            $(".verse_p .create_chunk").remove();
            $(".verse_p .chunks_reset").remove();

            if(prep_p.length > 0)
            {
                prep_p.append(createChunkBtn);
                prep_p.append(resetBtn);
            }
        }

        $("#chunks_array").val(JSON.stringify(chunks));

        fv = firstVerse < 10 ? "0"+firstVerse : firstVerse;
        lv = lastVerse < 10 ? "0"+lastVerse : lastVerse;
        $(".verse_p .create_chunk").text(fv+"-"+lv).attr("title", Language.makeChunk+" "+fv+"-"+lv).show();
        $(".verse_p .chunks_reset").show();
    });

    // Start new chunk
    $(document).on("click", ".verse_p .create_chunk", function() {
        currentChunk++;
        $(".verse_p .create_chunk").parent().after('<div class="chunk_divider col-sm-12"></div>');
        $(".verse_p .create_chunk").remove();
        $(".verse_p .chunks_reset").css("top", -15);
    });

    // Reset chunks
    $(document).on("click", ".verse_p .chunks_reset", function() {
        chunks = [];
        currentChunk = -1;
        firstVerse = 0;
        lastVerse = 0;

        $(this).remove();
        $(".verse_p .create_chunk").remove();
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
            const verseTa = $(".verse_ta, .peer_verse_ta", $(this).parent());
            const height = $(this).height()/verseTa.length;
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

    $(document).on("click", ".notifa", function(e) {
        e.preventDefault();

        const $this = $(this);

        renderConfirmPopup(Language.checkBookConfirmTitle, Language.checkBookConfirm, function () {
            $this.remove();
            let notifs = parseInt($(".notif_count").text());
            notifs--;
            if(notifs <= 0)
            {
                $(".notif_count").remove();
                const notifBlock = '' +
                    '<div class="no_notif">'+Language.noNotifsMsg+'</div>' +
                    '<div class="all_notifs">' +
                    '<a href="/events/notifications">'+Language.seeAll+'</a>' +
                    '</div>';
                $(".notif_block").html(notifBlock);
            }
            else
            {
                $(".notif_count").text(notifs);
            }

            $( this ).dialog( "close" );
            window.open($this.attr("href"));
        }, function () {
            $( this ).dialog( "close" );
        });
    });

    $(".check1 .event_link a, .check3 .event_link a").click(function (e) {
        window.open($(this).attr("href"));
        e.preventDefault();
    });

    // Show/Hide Comment Textarea
    $(document).on("click", ".editComment", function() {
        $(".comment_div").hide();

        comments = $(this).next(".comments");
        const comment = $(".my_comment", comments).text();

        const top = $(this).offset().top - 80;
        $(".comment_div").css("top", top).show();

        $("textarea", $(".comment_div")).val(comment).focus();

        lastCommentEditor = $(this);
        autosize.update($('textarea'));

        chapchunk = lastCommentEditor.attr("data").split(":");

        $(".other_comments_list").html("");
        $(".other_comments", comments).each(function() {
            $("<div />").addClass("other_comments").html($(this).html()).appendTo(".other_comments_list");
        });
    });

    $(".xbtn").click(function () {
        $(".comment_div").hide();
    });

    $(".editor-close").click(function() {
        comments = lastCommentEditor.next(".comments");

        let comment = $(".my_comment", comments);
        const text = $("textarea", $(".comment_div")).val().trim();
        const level = $(this).data("level") || 1;

        chapchunk = lastCommentEditor.attr("data").split(":");

        if(comment.length <= 0)
            comment = $("<div />").addClass("my_comment").appendTo(comments);

        if(comment.text() !== text)
        {
            if(typeof isDemo != "undefined" && isDemo) {
                $(".comment_div").hide();
            } else {
                $.ajax({
                    url: "/events/rpc/save_comment",
                    method: "post",
                    data: {
                        eventID: eventID,
                        chapter: chapchunk[0],
                        chunk: chapchunk[1],
                        comment: text,
                        level: level},
                    dataType: "json",
                    beforeSend: function() {
                        $(".commentEditorLoader").show();
                    }
                })
                    .done(function(data) {
                        if(data.success)
                        {
                            $(".comment_div").hide();

                            data.text = unEscapeStr(data.text);

                            if(data.text.trim() === "")
                            {
                                lastCommentEditor.css("color", "black")
                                comment.remove();
                            }
                            else
                            {
                                lastCommentEditor.css("color", "#a52f20");
                                comment.text(data.text);
                            }

                            num = comments.children().length > 0 ? comments.children().length : "";
                            lastCommentEditor.prev(".comments_number").addClass("hasComment").text(num);
                            if(num <= 0) lastCommentEditor.prev(".comments_number").removeClass("hasComment");

                            const msg = {
                                type: "comment",
                                eventID: eventID,
                                verse: lastCommentEditor.attr("data"),
                                text: data.text,
                                level: level
                            };
                            socket.emit("system message", msg);
                        }
                        else
                        {
                            if(typeof data.error != "undefined")
                            {
                                renderPopup(data.error, function () {
                                    window.location.reload(true);
                                });
                            }
                        }
                    })
                    .error(function (xhr, status, error) {
                        renderPopup(Language.commonError);
                    })
                    .always(function() {
                        $(".commentEditorLoader").hide();
                    });
            }
        }
        else
        {
            $(".comment_div").hide();
        }
    });

    $(".comment_div, .footnote_editor").draggable({snap: 'inner', handle: '.panel-heading'});

    $(document).on("click", ".editFootNote", function() {
        let textarea = $(this).closest(".lang_input_verse").find(".peer_verse_ta, .lang_input_ta");

        if(textarea.length === 0)
            textarea = $(this).closest(".flex_chunk, .flex_container").find(".peer_verse_ta, .lang_input_ta");

        if(textarea.length > 0) {
            let startPosition = textarea.prop("selectionStart");
            let endPosition = textarea.prop("selectionEnd");
            let text = textarea.val();
            let matchedNote = "";
            const matches = text.match(/\\f\s[+-]\s(.*?)\\f\*/gi);
            const ranges = [];

            if(matches != null && matches.length > 0) {
                for(let i=0; i<matches.length; i++) {
                    ranges.push([
                        text.indexOf(matches[i]),
                        text.indexOf(matches[i]) + matches[i].length
                    ])
                }
            }

            // define if cursor is in the range of an existent footnote
            for (let i=0; i<ranges.length; i++) {
                if(startPosition >= ranges[i][0] && startPosition <= ranges[i][1]) {
                    startPosition = ranges[i][0];
                    endPosition = ranges[i][1];
                    matchedNote = matches[i];
                }
            }

            openFootnoteEditor(startPosition, endPosition, matchedNote, $(this).offset().top - 80);

            footnoteCallback = function(footnote) {
                if(footnote.trim() === "\\f + \\f*")
                    footnote = "";

                text = text.substring(0, startPosition)
                    + footnote
                    + text.substring(endPosition, text.length);
                textarea.val(text);

                $(".peer_verse_ta, .lang_input_ta").highlightWithinTextarea("update");
                autosize.update($('textarea'));
                $(".peer_verse_ta, .lang_input_ta").keyup();
                $(".footnote_editor").hide();
            }
        }
    });

    $(".xbtnf").click(function () {
        $(".footnote_editor").hide();
    });

    function openFootnoteEditor(startPos, endPos, footnote, offset) {
        $(".footnote_editor").hide();
        $(".footnote_editor .fn_builder").html("");
        $(".footnote_editor .fn_preview").text("");

        const footnoteHtml = parseFootnote(footnote);
        $(".footnote_editor .fn_builder").html(footnoteHtml);
        renderFootnotesPreview();

        $(".footnote_editor").css("top", offset).show();
    }

    $(document).on("DOMSubtreeModified", ".fn_builder", function () {
        renderFootnotesPreview();
    });

    $(document).on("keyup", ".fn_builder", function () {
        renderFootnotesPreview();
    });

    $(".footnote-editor-close").click(function () {
        const footnote = $(".footnote_editor .fn_preview").text();
        if(footnoteCallback) {
            footnoteCallback(footnote);
            footnoteCallback = null;
        }
    });

    function renderFootnotesPreview() {
        let footnote = "\\f + ";

        $(".footnote_editor .fn_builder input").each(function() {
            if($(this).val().trim() !== "")
                footnote += "\\" + $(this).data("fn") + " " + $(this).val() + " ";
        });

        footnote += "\\f*";

        $(".footnote_editor .fn_preview").text(footnote);
    }

    function parseFootnote(footnote) {
        if(footnote === "") {
            return "";
        }

        const tags = ["fr","ft","fq","fqa","fk","fl"];
        let html = "";

        footnote = footnote
            .replace(/\\f\s[+-]\s/gi, "")
            .replace(/\\f\*/gi, "")
            .replace(/\\fqa\*/gi, "");

        const parts = footnote.split(/\\(f(?:r|t|qa|q|k|l))/gi);
        const map = [];
        let prevTag = "";
        if(parts.length > 1) {
            for(let i=0; i<parts.length; i++) {
                if(i === 0) continue;

                if(tags.includes(parts[i])) { // tag
                    if(prevTag !== parts[i]) {
                        prevTag = parts[i];
                    }
                } else {
                    map.push([prevTag, parts[i]]); // content
                }
            }
        }

        for(let i=0; i<map.length; i++) {
            html += "<label class='fn_lm'>"
                + map[i][0]
                + ": <input data-fn='" + map[i][0] + "' class='form-control' value='" + escapeQuotes(map[i][1].trim()) + "' />"
                + "<span class='glyphicon glyphicon-remove fn-remove'></span></label>"
                + "</label>";
        }

        return html;
    }

    $(document).on("click", ".fn_buttons button", function () {
        const tag = $(this).data("fn");

        if(tag === "link") {
            window.open("https://ubsicap.github.io/usfm/notes_basic/fnotes.html");
            return;
        }

        $(".fn_builder").append(
            "<label class='fn_lm'>" + tag + ": <input data-fn='" + tag + "' class='form-control' />" +
            "<span class='glyphicon glyphicon-remove fn-remove'></span></label>"
        );
    })

    $(document).on("click", ".fn-remove", function () {
        $(this).closest("label").remove();
    });

    // Show/Hide Tutorial popup
    $(".tutorial-close").click(function() {
        $(".tutorial_container").hide();
        body.css("overflow", "auto");
    });

    $(".show_tutorial_popup").click(function() {
        $(".tutorial_container").show();
        body.css("overflow", "hidden");

        const step = ""; //step;
        const role = $("#hide_tutorial").attr("data2");

        const cookie = typeof role != "undefined" && role === "checker" ?
            getCookie(step + "_checker_tutorial") : getCookie(step + "_tutorial");

        if(typeof cookie != "undefined")
        {
            $("#hide_tutorial").prop("checked", true);
        }
    });

    $("#hide_tutorial").change(function() {
        const step = ""; //$(this).attr("data");
        if($(this).is(":checked"))
        {
            setCookie(step + "_tutorial", true, {expires: 365*24*60*60, path: "/"});
        }
        else
        {
            deleteCookie(step + "_tutorial");
        }
    });


    // Show/Hide Video popup
    $(".video-close").click(function() {
        body.css("overflow", "auto");
        $(".video_container").hide();
        if(typeof player != "undefined")
        {
            player.seekTo(0);
            player.pauseVideo();
        }
    });

    $(".demo_video a").click(function () {
        $(".video_container").show();
        return false;
    });

    $("#finalReview").submit(function () {
        $(".vnote").each(function () {
            const content = $(".textWithBubbles", $(this));

            $(".bubble", content).each(function () {
                const newText = "|"+$(this).text()+"|";
                $(this).text(newText);
            });

            const textarea = $(".peer_verse_ta", $(this));

            textarea.val(content.text());
        });

        return true;
    });

    // Save keywords
    body.on("mouseup touchend", "div[class^=kwverse]", function () {
        if(!isChecker) return;
        if(typeof disableHighlight != "undefined") return;
        if(typeof isInfoPage != "undefined") return;
        if(typeof step == "undefined"
            || (step !== EventSteps.KEYWORD_CHECK
                && step !== EventSteps.THEO_CHECK
                && step !== EventSteps.HIGHLIGHT)) return;

        if (window.getSelection) {
            const verseID = $(this).attr("class");
            const sel = window.getSelection();
            let text;

            if(typeof sel != "undefined")
            {
                if(!sel.isCollapsed)    // Skip caret insert (zero characters select)
                {
                    //sel.modify("extend", "forward", "word");
                    //sel.modify("extend", "backward", "word");
                    const range = sel.getRangeAt(0);

                    // Exclude previous tags from select
                    if(sel.anchorNode.parentNode.nodeName === "SUP" || sel.anchorNode.parentNode.className === "chunk_verses")
                        range.setStart(sel.focusNode, 0);
                    else if(sel.focusNode.parentNode.nodeName === "B") // Exclude highlighted words from select
                        range.setEnd(sel.anchorNode, sel.anchorNode.data.length);

                    text = range.toString();

                    // Remove left and right space from select
                    if(text[text.length-1] === " ")
                        range.setEnd(range.endContainer, range.endOffset-1);
                    if(text[0] === " ")
                        range.setStart(range.startContainer, range.startOffset+1);

                    sel.removeAllRanges();
                    sel.addRange(range);

                    // Allow selection within one verse only
                    if(/^kwverse/.test(range.commonAncestorContainer.parentNode.className))
                    {
                        text = range.toString();

                        // Allow non-empty text and text that doesn't contain reserved words
                        if(text.length > 0 && !/^(?:b|d|a|t|data|dat|at|ata|ta)$/.test(text))
                        {
                            let wText = sel.anchorNode.textContent;
                            let sibling = sel.anchorNode.previousSibling;
                            while(sibling != null)
                            {
                                wText = sibling.textContent + wText;
                                sibling = sibling.previousSibling;
                            }

                            const diff = wText.length - sel.anchorNode.textContent.length;

                            // Find all occurenses of text in the verse
                            const regex = new RegExp("("+text+")", "gmi");
                            const search = [];
                            while (regex.exec(wText) !== null) {
                                search.push(regex.lastIndex);
                            }

                            if(search.length > 0)
                            {
                                // Find index of that text in the verse
                                const offset = Math.max(sel.focusOffset, sel.anchorOffset);
                                const index = search.indexOf(offset + diff);

                                sel.removeAllRanges();
                                isHighlighting = true;
                                setTimeout(function () {
                                    isHighlighting = false;
                                }, 500);

                                saveOrRemoveKeyword(verseID, text, index, false);
                            }
                            else
                            {
                                sel.removeAllRanges();
                            }
                        }
                        else
                        {
                            renderPopup(Language.highlightReservedWarning);
                            sel.removeAllRanges();
                        }
                    }
                    else
                    {
                        sel.removeAllRanges();
                        renderPopup(Language.highlightMultipleVerses);
                    }
                }
            }
        }
    });


    body.on("mouseover", ".chunk_verses b", function (e) {
        if(!isChecker && ["tn"].indexOf(tMode) === -1) return;
        if(typeof disableHighlight != "undefined") return;
        if(typeof step == "undefined"
            || (step !== EventSteps.KEYWORD_CHECK
                && step !== EventSteps.THEO_CHECK
                && step !== EventSteps.HIGHLIGHT)) return;

        if($(".remove_kw_tip").length <= 0)
        {
            body.append("<div class='remove_kw_tip'>"+Language.delKeywordTip+"</div>");
            $(".remove_kw_tip").css("left", $(this).offset().left + 20)
                .css("top", $(this).offset().top - 30);
        }
    });

    body.on("mouseout", ".chunk_verses b", function (e) {
        if(isChecker || ["tn"].indexOf(tMode) > -1)
            $(".remove_kw_tip").remove();
    });

    // Delete keyword
    body.on("click", ".chunk_verses b", function () {
        if(typeof isInfoPage != "undefined") return;
        if(typeof disableHighlight != "undefined") return;
        if(isHighlighting) return;

        if(!window.getSelection().isCollapsed) return;

        const isL2Checker = typeof isLevel2 != "undefined" && !isChecker;

        if((isChecker || isL2Checker)
            && (step === EventSteps.KEYWORD_CHECK
                || step === EventSteps.HIGHLIGHT
                || step === EventSteps.THEO_CHECK
                || step === EventCheckSteps.KEYWORD_CHECK_L2
                || step === EventCheckSteps.PEER_REVIEW_L2))
        {
            const $this = $(this);
            const text = $(this).text();

            let titleTxt;
            let confirmTxt;

            if(typeof isLevel2 != "undefined")
            {
                titleTxt = Language.delKeywordL2Title;
                confirmTxt = Language.delKeywordL2;
            }
            else
            {
                titleTxt = Language.delKeywordTitle;
                confirmTxt = Language.delKeyword;
            }

            renderConfirmPopup(titleTxt, confirmTxt + ' <strong>"'+text.trim()+'"</strong>', function () {
                $(this).dialog("close");

                const parent = $this.closest("div[class^=kwverse]");
                const verseID = parent.attr("class");
                const index = $this.data("index");

                saveOrRemoveKeyword(verseID, text, index, true);
            });
        }
    });

    if(typeof isDemo == "undefined")
        loadKeywordsIntoSourse();

    function loadKeywordsIntoSourse() {
        if(typeof eventID == "undefined") return;
        if(typeof step == "undefined"
            || (step !== EventSteps.KEYWORD_CHECK
                && step !== EventSteps.CONTENT_REVIEW
                && step !== EventSteps.FINAL_REVIEW
                && step !== EventSteps.HIGHLIGHT
                && step !== EventSteps.PEER_REVIEW
                && step !== EventCheckSteps.KEYWORD_CHECK_L2
                && step !== EventSteps.THEO_CHECK
                && step !== EventCheckSteps.PEER_REVIEW_L2)) return;

        if(step === EventSteps.PEER_REVIEW &&
            typeof disableHighlight == "undefined") return;

        /*$("div[class^=kwverse]").html(function() {
            return $(this).text();
        });*/

        $.ajax({
            url: "/events/rpc/get_keywords",
            method: "post",
            data: {
                eventID: eventID,
                chapter: myChapter
            },
            dataType: "json",
            beforeSend: function() {
                //$(".commentEditorLoader").show();
            }
        })
            .done(function(data) {
                if(data.success)
                {
                    $.each(data.text, function (i,v) {
                        const verseID = "kwverse_"+v.chapter+"_"+v.chunk+"_"+v.verse;
                        highlightKeyword(verseID, unEscapeStr(v.text), v.indexOrder);
                    });
                }
                else
                {
                    if(typeof data.error != "undefined")
                    {
                        renderPopup(data.error, function () {
                            window.location.reload(true);
                        });
                    }
                }
            })
            .always(function() {
                //$(".commentEditorLoader").hide();
            });
    }

    setTimeout(function () {
        $(".words_block, .chunk_block").each(function() {
            if($(this).hasClass("no_autosize")) return;

            const h1 = $(".chunk_verses", this).height();
            const h2 = $(".editor_area", this).height();

            $(".chunk_verses textarea", this).css("min-height", Math.max(h1, h2));
            $(".editor_area textarea", this).css("min-height", Math.max(h1, h2));
        });
    },2100);

    // Show/Hide help window
    $("#help_hide").click(function() {
        const $this = $(this).parents(".content_help");

        if($this.hasClass("open"))
        {
            $this.css("z-index", 102);
            $this.removeClass("open")
                .addClass("closed");
            $this.animate({right: -275}, 500, function() {
                $("#help_hide").removeClass("glyphicon-chevron-right")
                    .addClass("glyphicon-chevron-left");
            });
        }
        else
        {
            $this.css("z-index", 103);
            $this.removeClass("closed")
                .addClass("open");
            $this.animate({right: 0}, 500, function() {
                $("#help_hide").removeClass("glyphicon-chevron-left")
                    .addClass("glyphicon-chevron-right");
            });
        }
    });

    // ---------------------  Verse markers setting start -------------------- //
    const bindDraggables = function() {
        const bubble = $('.bubble');
        bubble.attr("contenteditable", false).attr("draggable", true);
        bubble.off('dragstart').on('dragstart', function(e) {
            if (!e.target.id)
                e.target.id = (new Date()).getTime();

            e.originalEvent.dataTransfer.setData('text', e.target.outerHTML);
            const parent = $(e.target).closest(".vnote");
            body.data("bubble", $(e.target));
            body.data("focused", $(".textWithBubbles", parent));
        });
    };

    $('.textWithBubbles').keydown(function (e) {
        e.preventDefault();
        return false;
    });

    $('.textWithBubbles, .bubble').on("selectstart", function (e) {
        e.preventDefault();
        return false;
    });

    $('.textWithBubbles, .bubble').on("contextmenu", function (e) {
        e.preventDefault();
        return false;
    });

    $('.textWithBubbles').on('drop', function(e) {
        e.preventDefault();

        const event = e.originalEvent;
        const bubble = body.data("bubble");
        const focused = body.data("focused");
        const txt = $(event.target).text();
        // Check if text has Chinese/Japanese/Myanmar/Lao characters and SUN
        const hasCJLM = /[\u0e80-\u0eff\u3040-\u309f\u30a0-\u30ff\u4e00-\u9faf\u1000-\u109f\ue000-\uf8ff]/.test(txt);
        body.data("hasCJLM", hasCJLM);

        if(!event.target || event.target.className !== "splword" // drop only before words
            || !$(event.target.parentElement).is(focused) // don't drop into other chunk
            || (!hasCJLM && $(event.target).prev().is(".bubble")) // don't drop when there is verse marker before word
        )
        {
            bindDraggables();
            return false;
        }

        bubble.addClass('dragged');

        const content = event.dataTransfer.getData('text');

        $(this).get(0).focus();

        pasteHtmlAtCaret(event, content);
        bindDraggables();
        $('.dragged').remove();

        return false;
    });

    bindDraggables();

    function pasteHtmlAtCaret(e, html) {

        if (window.getSelection) {
            const sel = window.getSelection();
            let range;

            if (sel.getRangeAt) {
                hasCJLM = body.data("hasCJLM");

                if(!hasCJLM)
                {
                    range = document.createRange();
                    range.selectNode(e.target);
                }
                else
                {
                    if (e.rangeParent) {
                        range = document.createRange();
                        range.setStart(e.rangeParent, e.rangeOffset);
                    }
                }

                const el = document.createElement("div");
                el.innerHTML = html;

                const frag = document.createDocumentFragment();
                let node, lastNode;
                while ( (node = el.firstChild) ) {
                    lastNode = frag.appendChild(node);
                }
                range.insertNode(frag);
            }
        }
    }


    // ---------------------  Verse markers setting end -------------------- //

    // Profile form

    $(".avatar_btn").click(function () {
        const gender = $(this).attr("id");
        const current = $("input[name=avatar]").val();

        if(gender === "avatarMales")
        {
            $(".genderMale").show();
            $(".genderFemale").hide();
        }
        else
        {
            $(".genderMale").hide();
            $(".genderFemale").show();
        }

        $(".avatar_block img").removeClass("active");
        $("#"+current).addClass("active");
        $(".avatar_container").css("left", 0);

        return false;
    });

    $(".genderMale img, .genderFemale img").click(function () {
        const id = $(this).attr("id");
        const src = $(this).attr("src");

        $("input[name=avatar]").val(id);
        $(".avatar_control img").attr("src", src);

        $(".avatar_container").css("left", "-9999px");
    });

    $(".avatar-close").click(function() {
        $(".avatar_container").css("left", "-9999px");
    });


    $("input[name^=prefered_roles]").change(function () {
        const role = $(this).val();
        const thisChecked = $(this).is(":checked");
        const trChecked = $(".tr_role").is(":checked");
        const fcChecked = $(".fc_role").is(":checked");
        const chChecked = $(".ch_role").is(":checked");

        if(thisChecked)
        {
            if(role === "facilitator")
            {
                $(".facilitator_section").show();
                $(".checker_section").show();
            }
            else
            {
                $(".checker_section").show();
            }
        }
        else
        {
            if(role === "facilitator")
            {
                $(".facilitator_section").hide();
                if(!trChecked && !fcChecked)
                    $(".checker_section").hide();
            }
            else
            {
                if(!trChecked && !fcChecked && !chChecked)
                    $(".checker_section").hide();
            }
        }
    });

    const langs = $(".langs option:selected");
    if(langs.length > 0)
        $(".langs").prop("disabled", false);

    $(".language").change(function() {
        const parent = $(this).closest(".language_block");
        if($(this).val() !== "")
        {
            $(".fluency", parent).prop("name", "lang["+$(this).val()+"][fluency]").prop("disabled", false);
            //$(".geo_years", parent).prop("name", "lang["+$(this).val()+"][geo_years]").prop("disabled", false);
        }
        else
        {
            $(".fluency", parent).prop("name", "").prop("disabled", true);
            //$(".geo_years", parent).prop("name", "").prop("disabled", true);
        }
    });

    $(".language-close").click(function() {
        $(".language_container").css("left", "-9999px");
    });

    $(".language_add").click(function() {
        $(".language_container").css("left", 0);

        $(".language").val("").trigger('chosen:updated');
        $(".fluency").prop("checked", false).prop("disabled", true);
        //$(".geo_years").prop("checked", false).prop("disabled", true);
        $(".fluency/*, .geo_years*/").trigger("change");
        $(".language").chosen();
    });


    $(".add_lang").click(function() {
        const lang = $(".language").val();
        const langName = $(".language option:selected").text();
        const fluency = $(".fluency:checked").val();
        //const geo_years = $(".geo_years:checked").val();
        let option = $(".langs option[value^='"+lang+":']");

        if(option.length <= 0) {
            $(".langs").append("<option value='"+lang+":"+fluency+/*":"+geo_years+*/"'>" + langName + "</option>");
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

    $(".fluency/*, .geo_years*/").change(function() {
        const fluency = $(".fluency:checked").val();
        //const geo_years = $(".geo_years:checked").val();

        if(typeof fluency != "undefined"/* && typeof geo_years != "undefined"*/)
        {
            $(".add_lang").prop("disabled", false);
        }
        else
        {
            $(".add_lang").prop("disabled", true);
        }
    });

    $(".langs option").each(function() {
        const val = $(this).val();
        const langs = val.split(":");

        if(langs.length !== 3) {
            $(this).remove();
            return true
        }

        $(this).text($(".language option[value="+langs[0]+"]").text());
    });

    // Translation events number test
    $("input[name=mast_evnts]").change(function() {
        if($(this).val() > 1)
        {
            $("input[name^='mast_role']").prop("disabled", false);
        }
        else
        {
            $("input[name^='mast_role']").prop("disabled", true);
        }
    });

    if($("input[name=mast_evnts]:checked").val() > 1)
    {
        $("input[name^='mast_role']").prop("disabled", false);
    }
    else
    {
        $("input[name^='mast_role']").prop("disabled", true);
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
            $("input[name=org]").prop("disabled", true);
            $("input[name=ref_person]").prop("disabled", true);
            $("input[name=ref_email]").prop("disabled", true);
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
        $("input[name=org]").prop("disabled", true);
        $("input[name=ref_person]").prop("disabled", true);
        $("input[name=ref_email]").prop("disabled", true);
    }

    // Event information accordion
    $(document).on("click", ".section_header", function() {
        const content = $(this).next(".section_content");
        const isCollapsed = $(".section_arrow", $(this)).hasClass("glyphicon-triangle-right");
        const opened = $(".chapter_list").data("opened") || 0;

        if(!isCollapsed)
        {
            content.hide("blind", {direction: "vertical"}, 300);
            $(".section_arrow", $(this))
                .removeClass("glyphicon-triangle-bottom")
                .addClass("glyphicon-triangle-right");

            $(".chapter_list").data("opened", opened - 1);

            if($(".chapter_list").data("opened") <= 0)
                $(".members_list").show("blind", {direction: "right"}, 300);

        }
        else
        {
            content.show("blind", {direction: "vertical"}, 300);
            $(".section_arrow", $(this))
                .removeClass("glyphicon-triangle-right")
                .addClass("glyphicon-triangle-bottom");

            $(".chapter_list").data("opened", opened + 1);

            $(".members_list").hide("blind", {direction: "right"}, 300);
        }
    });

    $("#add_checker").keyup(function (event) {
        const $this = $(this);
        $("#checker_value").val("");
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function () {
            const name = $this.val();
            if(name.trim() === "")
            {
                $(".user_checkers").html("");
                return;
            }

            $.ajax({
                url: "/members/search",
                method: "post",
                data: {
                    name: name,
                    ext: true,
                },
                dataType: "json",
                beforeSend: function() {
                    $(".membersSearch").show();
                }
            })
                .done(function(data) {
                    $(".user_checkers").html("");
                    if(data.success)
                    {
                        if(data.count > 0)
                            $(".user_checkers").show();
                        else
                            $(".user_checkers").hide();

                        $.each(data.members, function () {
                            if(this.blocked == "1") return true;

                            const li = '<li>' +
                                '<label>' +
                                '<div class="chk_member" data="'+this.memberID+'">'+ this.firstName + ' ' + this.lastName +' ('+this.userName+')</div>' +
                                '</label>' +
                                '</li>';

                            $(".user_checkers").append(li);
                        });
                    }
                    else
                    {
                        debug(data.error);
                    }
                })
                .always(function () {
                    $(".membersSearch").hide();
                });
        }, 500);
    });

    $(document).on("click", ".chk_member", function () {
        const memberID = $(this).attr("data");
        $("#checker_value").val(memberID);
        $("#add_checker").val($(this).text());
        $(".user_checkers").hide();
    });

    $(".add_checker_btn").click(function () {
        const memberID = $("#checker_value").val();
        const chkName = $("#add_checker").val();

        if(chkName.trim() === "") return false;

        $.ajax({
            url: "/events/rpc/apply_verb_checker",
            method: "post",
            data: {
                eventID: eventID,
                chkName: chkName,
                chkID: memberID,
            },
            dataType: "json",
            beforeSend: function() {
                $(".membersSearch").show();
            }
        })
            .done(function(data) {
                $(".user_checkers").html("");
                if(data.success)
                {
                    $(".checker_name_span").text(data.chkName);
                    $(".add_cheker").remove();
                }
                else
                {
                    if(typeof isDemo != "undefined" && isDemo)
                    {
                        $(".checker_name_span").text(chkName.replace(/\(.*\)/, ""));
                        $(".add_cheker").remove();
                    }
                    else
                    {
                        debug(data.error);
                        renderPopup(data.error);
                    }
                }
            })
            .always(function () {
                $(".membersSearch").hide();
            });
    });

    // Check if event has been started
    if($("#evnt_state_checker").val() === "error")
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

    function localizeDate() {
        $.each($(".datetime"), function () {
            const dateStr = $(this).attr("data");
            if(dateStr === "" || dateStr === "----/--/--" || dateStr === "--:--") return true;

            const date_options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                weekday: 'short',
                timezone: 'UTC',
            };

            const time_options = {
                hour: 'numeric',
                minute: 'numeric',
                timezone: 'UTC',
            };

            if($(".event_time_time").length <= 0) // Combine date and time options for full result
            {
                date_options.hour = 'numeric';
                date_options.minute = 'numeric';
            }

            const lang = typeof getCookie("lang") != "undefined" ? getCookie("lang") : "en";

            const date = new Date(dateStr + " UTC");
            $(this).text(date.toLocaleString(lang, date_options));
            $(this).next(".event_time_time").text(date.toLocaleString(lang, time_options));
        });
    }

    localizeDate();

    // Dashboard tabs switch
    $(".my_tab").click(function () {
        const id = $(this).attr("id");

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


    // Show contact facilitator form
    $(".facil_names a").click(function() {
        const adminID = $(this).attr("data");
        const adminName = $(this).text();

        $(".mailer_name span").text(adminName);
        $(".adm_id").val(adminID);
        $(".mailer_container").css("left", 0);
        $(".mailer_form")[0].reset();

        return false;
    });

    $(".mailer-close").click(function() {
        $(".mailer_container").css("left", "-9999px");
    });

    $(".mailer_form").submit(function () {
        const form = $(this);

        $.ajax({
            url: "/members/rpc/send_message",
            method: "post",
            data: form.serialize(),
            dataType: "json",
            beforeSend: function() {
                $(".mailer_button button")
                    .text(Language.sending)
                    .prop("disabled", true);
            }
        })
            .done(function(data) {
                if(data.success)
                {
                    $(".mailer_container").css("left", "-9999px");
                    renderPopup(Language.messageSendSuccess);
                }
                else
                {
                    if(typeof data.errorType != "undefined")
                    {
                        switch (data.errorType)
                        {
                            case "logout":
                            case "demo":
                            case "profile":
                                window.location.reload();
                                break;
                        }

                        if(typeof data.error != "undefined")
                            renderPopup(data.error);
                    }
                    else
                    {
                        $(".mailer_container").css("left", "-9999px");
                    }
                }
            })
            .error(function(xhr, status, error ) {
                renderPopup(status + ": " + error);
            })
            .always(function() {
                $(".mailer_button button")
                    .text(Language.send)
                    .prop("disabled", false);
            });

        return false;
    });

    $(window).scroll(function () {

        let $this = $(this);

        if($(".save_profile_container").length > 0)
        {
            if($(this).scrollTop() > 70)
                $(".save_profile_container").removeClass("unlinked");
            else
                $(".save_profile_container").addClass("unlinked");
        }

        if($('.saildict_panel').length > 0)
        {
            const elementTop = $('.saildict_panel').offset().top;
            const elementBottom = elementTop + $('.saildict_panel').outerHeight();
            const viewportTop = $(window).scrollTop();
            const viewportBottom = viewportTop + $(window).height();

            if(viewportTop > elementTop)
                $(".saildict_panel").css("top", $this.scrollTop());
            else if(viewportBottom < elementBottom)
                $(".saildict_panel").css("top", elementTop - 150);
        }

        if($('.ttools_panel').length > 0)
        {
            $('.ttools_panel').each(function () {
                if($(this).is(":visible"))
                {
                    const elementTop = $(this).offset().top;
                    const elementBottom = elementTop + $(this).outerHeight();
                    const viewportTop = $(window).scrollTop();
                    const viewportBottom = viewportTop + $(window).height();

                    if(viewportTop > elementTop)
                        $(this).css("top", $this.scrollTop());
                    else if(viewportBottom < elementBottom)
                        $(this).css("top", elementTop - 150);
                }
            });
        }

        if($(".help_float").length > 0)
        {
            if($(this).scrollTop() > 150)
                $(".help_float").css("top", 10);
            else
                $(".help_float").css("top", 170 - $this.scrollTop());
        }
    });

    $("#refresh").click(function() {
        window.location.reload();
    });

    $(".demo_link, #demo_link").click(function(e) {
        if($(".demo_options").is(":visible"))
        {
            $(".demo_options").hide(200);
        }
        else
        {
            $(".demo_options").show(200);
        }

        e.stopPropagation();
    });

    $(document).click(function(e) {
        if(!e.target.classList.contains("demo_options"))
            $(".demo_options").hide(200);
    });

    // Sail dictionary
    body.on("keyup", "#sailfilter", function () {
        const w = escape($(this).val());
        const isGlobal = $("#sailfilter_global").is(":checked");
        const pattern = (!isGlobal ? "^" : "") + w;
        const re = new RegExp(pattern, "ig");

        $(".sail_list li").hide();
        $(".sail_list li").filter(function () {
            return this.id.match(re);
        }).show();
    });

    body.on("click", ".sail_list li", function () {
        const symbol = $("input", this);
        symbol.select();

        try
        {
            document.execCommand("Copy");
            $(".copied_tooltip").show().fadeOut(2000);
        }
        catch (error)
        {
            alert(error);
        }
    });

    // Faq filter
    body.on("keyup", "#faqfilter", function () {
        const w = $(this).val();
        const re = new RegExp(w, "ig");

        $(".faq_list li").hide();
        $(".faq_list li").filter(function () {
            return $(this).text().match(re);
        }).show();
    });

    // Translation tools
    $(".ttools").click(function (e) {
        $this = $(this);
        const tool = $(this).data("tool");

        if(tool === "sunbible")
        {
            const win = window.open("/translations/en/sun/ulb/", "_blank");
            win.focus();
            return;
        }

        let container = $(".ttools_panel."+tool+"_tool");

        const bookCode = $("input#bookCode").val();
        const chapter = $("input#chapter").val();
        const lang = $("input#"+tool+"_lang").val();
        const targetLang = $("input#targetLang").val();
        const totalVerses = $("input#totalVerses").val();

        if (container.length <= 0) {
            const query_params = (["tq","tn","tw"].indexOf(tool) > -1
                ? bookCode + "/" + chapter + "/" + lang
                : (tool === "rubric" ? targetLang : ""))
                + (tool === "tn" ? "/" + totalVerses : "");

            $.ajax({
                url: "/events/rpc/get_" + tool + "/" + query_params,
                beforeSend: function() {
                    $this.prop("disabled", true);
                }
            })
                .done(function(data) {
                    $(".container_block").append(data);

                    container = $(".ttools_panel."+tool+"_tool");
                    if (container.length <= 0)
                    {
                        renderPopup(Language.resource_not_found);
                        return;
                    }

                    $(".ttools_panel").draggable({snap: 'inner', handle: '.panel-title'});
                    container.css("top", $(window).scrollTop() + 50).show();
                })
                .error(function(xhr, status, error ) {
                    renderPopup(status + ": " + error);
                })
                .always(function() {
                    $this.prop("disabled", false);
                });
        }
        else
        {
            $(".ttools_panel").draggable({snap: 'inner', handle: '.panel-title'});
            container.css("top", $(window).scrollTop() + 50).show();
        }

        e.preventDefault();
    });

    // Show/Hide Keyword Definition
    body.on("click", ".word_term", function () {
        const word = $("span", this).text();
        const def = $(this).next(".word_def").html();
        const parent = $(this).closest(".ttools_content");

        $(".word_def_title", parent).text(word);
        $(".word_def_content", parent).html(def);

        //$(".labels_list").children().hide();
        $(".word_def_popup", parent).show("slide", {direction: "left"}, 300);
    });

    body.on("click", ".word_def-close", function() {
        $(".labels_list").children().show();
        const parent = $(this).closest(".ttools_content");

        $(".word_def_content", parent)[0].scrollTop = 0;
        $(".word_def_popup", parent).hide("slide", {direction: "left"}, 300);
    });

    // Show/hide original/english content of a rubric
    body.on("click", ".read_rubric_tabs li", function(e) {
        e.preventDefault();
        const id = $(this).attr("id");
        $(this).addClass("active");

        if(id === "tab_orig")
        {
            $("#tab_eng").removeClass("active");
            $(".read_rubric_qualities .orig").show();
            $(".read_rubric_qualities .eng").hide();
        }
        else
        {
            $("#tab_orig").removeClass("active");
            $(".read_rubric_qualities .orig").hide();
            $(".read_rubric_qualities .eng").show();
        }
    });

    body.on("click", ".ttools_panel .panel-close", function () {
        const tool = $(this).data("tool");

        switch (tool) {
            case "tn":
                $(".ttools_panel.tn_tool").hide();
                break;
            case "tq":
                $(".ttools_panel.tq_tool").hide();
                break;
            case "tw":
                $(".ttools_panel.tw_tool").hide();
                break;
            case "rubric":
                $(".ttools_panel.rubric_tool").hide();
                break;
            case "saildict":
                $(".ttools_panel.saildict_tool").hide();
                break;
        }
    });

    // Super admin local login
    $("#isSuperAdmin").change(function () {
        if($(this).is(":checked"))
        {
            $("#password").val("");
            $(".password_group").show();
        }
        else
        {
            $("#password").val("default");
            $(".password_group").hide();
        }
    });

    // ------------- Language Input ------------- //

    // Delete verse block and verse from database if it exists
    body.on("click", "button.delete_verse_ta", function (e) {
        const $this = $(this);
        const parent = $this.closest(".lang_input_verse");
        const id = parent.data("id"); // id of the verse from database

        if(id)
        {
            $.ajax({
                url: "/events/rpc/delete_li_verse",
                method: "post",
                data: {
                    eventID: eventID,
                    tID: id
                },
                dataType: "json",
                beforeSend: function() {
                    $this.prop("disabled", true);
                }
            })
                .done(function(data) {
                    if(data.success)
                    {
                        parent.remove();
                    }
                })
                .always(function() {
                    $this.prop("disabled", false);
                });
        }
        else
        {
            parent.remove();
        }

        e.preventDefault();
    });

    // Add empty verse block
    $(".add_verse_ta").click(function (e) {
        const lastVerseBlock = $(".lang_input_verse:last-of-type");
        let newVerse = 1;

        if(lastVerseBlock.length > 0)
            newVerse = parseInt(lastVerseBlock.data("verse"))+1;

        const newVerseHtml = "" +
            "<div class=\"lang_input_verse\" data-verse=\""+newVerse+"\">" +
            "<textarea name=\"verses["+newVerse+"]\" class=\"textarea lang_input_ta\"></textarea>" +
            "<span class=\"vn\">"+newVerse+"</span>" +
            "<button class=\"delete_verse_ta btn btn-danger glyphicon glyphicon-remove\" />" +
            "</div>";

        $(this).before(newVerseHtml);
        autosize($('.lang_input_list textarea'));

        e.preventDefault();
    });

    // Autosave verse in language input mode
    body.on("keyup paste", ".lang_input_ta", function() {
        hasLangInputChangesOnPage = true;
        $(".unsaved_alert").show();
    });

    setInterval(function() {
        if(typeof isDemo != "undefined" && isDemo)
        {
            hasLangInputChangesOnPage = false;
            $(".unsaved_alert").hide();
        }

        if(hasLangInputChangesOnPage)
        {
            let postData = $("#main_form").serializeArray();
            postData.push({"name":"eventID","value":eventID});

            $.ajax({
                url: "/events/rpc/autosave_li_verse",
                method: "post",
                data: postData,
                dataType: "json"
            })
                .done(function(data) {
                    if(data.success)
                    {
                        $(".unsaved_alert").hide();
                        hasLangInputChangesOnPage = false;

                        if(data.ids)
                        {
                            $.each(data.ids, function(k, v) {
                                $(".lang_input_verse[data-verse="+k+"]").data("id", v);
                            });
                        }
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

                                case "json":
                                    hasLangInputChangesOnPage = false;
                                    renderPopup(data.error);
                                    break;
                            }
                        }
                        console.log(data.error);
                    }
                })
                .error(function (xhr, status, error) {
                    debug(status);
                    debug(error);
                    //localStorage.setItem(item, $("#main_form").serialize());
                    hasLangInputChangesOnPage = false;
                })
                .always(function() {

                });
        }
    }, 3000);

    // Export menu
    $("#upload_menu span").click(function(e) {
        e.stopPropagation();
        if(!$("#upload_menu ul").is(":visible")) {
            $("#upload_menu ul").slideDown(200);
        } else {
            $("#upload_menu ul").slideUp(200);
        }
    });

    body.click(function() {
        if($("#upload_menu ul").is(":visible")) {
            $("#upload_menu ul").slideUp(200);
        }
    });

    $(".export_cloud a").click(function(e) {
        const url = $(this).prop("href");

        exportToCloud(url);

        e.preventDefault();
    });

    $(".login_cloud_server button[name=cloudLogin]").on("click", function(e) {
        const username = $("#cloud_username").val();
        const password = $("#cloud_password").val();
        const otp = $("#cloud_otp_code").val();
        const server = $("#cloudServer").val();
        const cloudUrl = $("#cloudUrl").val();

        $(".login_cloud_server .cloudError").text("");
        $(".cloudLoginLoader").show();

        if(username !== "" && password !== "") {
            $.ajax({
                url: "/members/rpc/cloud_login",
                method: "post",
                dataType: "json",
                data: {
                    server: server,
                    username: username,
                    password: password,
                    otp: otp
                }
            })
                .done(function(data) {
                    if(data.success) {
                        $(".login_cloud_server").css("left", -9999);
                        exportToCloud(cloudUrl);
                    } else {
                        $(".cloudError").text("Could not login. Please try again!");
                    }
                })
                .error(function (xhr, status, error) {
                    debug(status);
                    debug(error);
                })
                .always(function() {
                    $(".cloudLoginLoader").hide();
                });
        } else {
            alert("Some fields are empty");
        }

        e.preventDefault();
    });

    $("#cloud_otp").on("click", function() {
        if($(this).is(":checked")) {
            $(".cloud_otp_code_group").show();
        } else {
            $(".cloud_otp_code_group").hide();
            $(".cloud_otp_code_group #cloud_otp_code").val("");
        }
    });
});

function exportToCloud(url) {
    const dialog = renderPopup(Language.sending, null, null, false);

    $.ajax({
        url: url,
        method: "get",
        dataType: "json"
    })
        .done(function(data) {
            if(data.success) {
                dialog.dialog("destroy");
                renderPopup(Language.pushSuccess + " <a href='"+data.url+"' target='_blank'>"+data.url+"</a>");
            } else {
                dialog.dialog("destroy");
                if(typeof data.authenticated != "undefined" && !data.authenticated) {
                    const createLink = data.server === "wacs" ?
                        "https://wacs.bibletranslationtools.org/user/sign_up" :
                        "https://git.door43.org/user/sign_up";

                    $(".login_cloud_server .panel-title span.cloud_server_name").text(data.server.toUpperCase());
                    $(".login_cloud_server .page-content a.create_login_link").prop("href", createLink);
                    $(".login_cloud_server .page-content #cloudServer").val(data.server);
                    $(".login_cloud_server .page-content #cloudUrl").val(url);
                    $(".login_cloud_server .cloudError").text("");

                    $(".cloud_otp_code_group #cloud_otp_code").val("");
                    $("#cloud_otp").prop("checked", false);
                    $(".cloud_otp_code_group").hide();

                    $(".login_cloud_server").css("left", 0);
                } else if(typeof data.error != "undefined") {
                    renderPopup(data.error);
                }
            }
        })
        .error(function (xhr, status, error) {
            debug(status);
            debug(error);
        })
        .always(function() {

        });
}

function animateIntro() {
    if (location.pathname !== "/") return;

    const grl=$( "#ground-left" );
    const grr=$( "#ground-right" );
    const grc=$( "#ground-center" );
    const cll=$( "#cloud-left" );
    const clr=$( "#cloud-right" );

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
 * @param name
 * @returns {*}
 */
function getCookie(name) {
    const matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([.$?*|{}()\[\]\\\/+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}

/**
 * Set cookie value by name
 * @param name
 * @param value
 * @param options
 */
function setCookie(name, value, options) {
    options = options || {};

    let expires = options.expires;

    if (typeof expires == "number" && expires) {
        const d = new Date();
        d.setTime(d.getTime() + expires * 1000);
        expires = options.expires = d;
    }
    if (expires && expires.toUTCString) {
        options.expires = expires.toUTCString();
    }

    value = encodeURIComponent(value);

    let updatedCookie = name + "=" + value;

    for (const propName in options) {
        updatedCookie += "; " + propName;
        const propValue = options[propName];
        if (propValue != true) {
            updatedCookie += "=" + propValue;
        }
    }

    document.cookie = updatedCookie;
}

/**
 * Delete cookie by name (make it expired)
 * @param name
 * @param options
 */
function deleteCookie(name, options) {
    options = options || {
        expires: -1,
        path: "/"
    };
    setCookie(name, "", options)
}

function parseCombinedVerses(verse)
{
    const versesArr = [];
    const verses = verse.split("-");

    if(verses.length < 2)
    {
        versesArr.push(parseInt(verse));
        return versesArr;
    }

    const fv = parseInt(verses[0]);
    const lv = parseInt(verses[1]);

    for(let i=fv; i <= lv; i++)
    {
        versesArr.push(i);
    }

    return versesArr;
}

function unEscapeStr(string) {
    return $('<div/>').html(string).text();
}

function escapeQuotes(str) {
    return str.replace(/"/g, "&quot;").replace(/'/g, "&apos;");
}

/**
 * Renders and shows dialog window with OK button
 * @param message
 * @param onOK Ok button callback
 * @param onClose Close button callback
 * @param closable Is it closable
 * @returns {boolean}
 */
function renderPopup(message, onOK, onClose, closable) {
    let buttons = {};
    let dialogClass = "no-close";

    closable = closable || true;

    if(closable) {
        onOK = onOK || function(){
            $( this ).dialog( "close" );
        };

        onClose = onClose || function(){
            $( this ).dialog( "close" );
            return false;
        };

        buttons = {
            Ok: onOK
        };

        dialogClass = "";
    }

    $(".alert_message").html(message);

    return $( "#dialog-message" ).dialog({
        dialogClass: dialogClass,
        modal: true,
        resizable: false,
        draggable: false,
        width: 500,
        buttons: buttons,
        close: onClose,
        beforeClose: function(){
            return closable;
        }
    });
}

/**
 * Renders and shows confirm dialog popup
 * @param title
 * @param message
 * @param onAnswerYes positive answer callback
 * @param onAnswerNo Negative answer callback
 * @param onClose close dialog callback
 */
function renderConfirmPopup(title, message, onAnswerYes, onAnswerNo, onClose) {
    onAnswerYes = onAnswerYes || function(){
        $( this ).dialog( "close" );
        return true;
    };
    onAnswerNo = onAnswerNo || function(){
        $( this ).dialog( "close" );
        return false;
    };
    onClose = onClose || function(){
        $( this ).dialog( "close" );
        return false;
    };

    const yes = Language.yes;
    const no = Language.no;

    const btns = {};
    btns[yes] = onAnswerYes;
    btns[no] = onAnswerNo;

    $(".confirm_message").html(message);
    $( "#check-book-confirm" ).dialog({
        resizable: false,
        draggable: false,
        title: title,
        height: "auto",
        width: 500,
        modal: true,
        buttons: btns,
        close: onClose,
    });
}


function saveOrRemoveKeyword(verseID, text, index, remove) {
    remove = remove || false;
    const verseData = verseID.split("_");

    const chapter = verseData[1];
    const chunk = verseData[2];
    const verse = verseData[3];

    if(typeof isDemo != "undefined")
    {
        highlightKeyword(verseID, text, index, remove);
        return false;
    }

    $.ajax({
        url: "/events/rpc/save_keyword",
        method: "post",
        data: {
            eventID: eventID,
            chapter: chapter,
            chunk: chunk,
            verse: verse,
            index: index,
            text: text,
            remove: remove
        },
        dataType: "json",
        beforeSend: function() {
            $(".chunk_verses").css("cursor", "wait");
            $(".chunk_verses b").css("cursor", "wait");
        }
    })
        .done(function(data) {
            if(data.success)
            {
                const msg = {
                    type: "keyword",
                    remove: remove,
                    eventID: eventID,
                    chkMemberID: chkMemberID,
                    verseID: verseID,
                    text: text,
                    index: index,
                };

                highlightKeyword(verseID, text, index, remove);
                socket.emit("system message", msg);
            }
            else
            {
                if(typeof data.error != "undefined")
                {
                    renderPopup(data.error, function () {
                        window.location.reload(true);
                    });
                }
            }
        })
        .always(function() {
            $(".chunk_verses").css("cursor", "auto");
            $(".chunk_verses b").css("cursor", "pointer");
        });
}

/**
 * Highlight or remove selection of the given keyword
 * @param verseID   Verse element
 * @param text      Keyword text
 * @param index     Order number of the keyword in the verse
 * @param remove    Whether to remove selection or not
 */
function highlightKeyword(verseID, text, index, remove) {
    remove = remove || false;
    const verseEl = $("."+verseID);

    if(verseEl.length <= 0) return;

    text = unEscapeStr(text);

    if(remove)
    {
        $("b[data-index="+index+"]", verseEl)
            .filter(function() {
                return $(this).text() === text;
            })
            .contents()
            .unwrap();

        verseEl.html(function () {
            return $(this).html();
        })
    }
    else
    {
        const verseText = verseEl.html().trim();
        const regex = new RegExp("("+text+")", "gmi");

        let nth = -1;
        index = parseInt(index) || 0;
        const html = verseText.replace(regex, function(match, i, orig) {
            nth++;
            return (nth === index)
                ? "<b data-index='"+index+"'>"+match+"</b>"
                : match;
        });
        verseEl.html(html);
    }
}

function downloadCSV(csv, filename) {
    // CSV file
    const csvFile = new Blob([csv], {type: "application/csv"});

    // Download link
    const downloadLink = document.createElement("a");

    // File name
    downloadLink.download = filename;

    // Create a link to the file
    downloadLink.href = window.URL.createObjectURL(csvFile);

    // Hide download link
    downloadLink.style.display = "none";

    // Add the link to DOM
    document.body.appendChild(downloadLink);

    // Click download link
    downloadLink.click();
}

function exportTableToCSV(parent, separator) {
    const csv = [];
    const rows = parent.querySelectorAll("table tr");

    for (let i = 0; i < rows.length; i++) {
        const row = [], cols = rows[i].querySelectorAll("td, th");

        for (let j = 0; j < cols.length; j++)
            row.push(cols[j].innerText);

        csv.push(row.join(separator));
    }

    // return CSV content
    return csv.join("\n");
}

/**
 * Detect if current browser is Internet Explorer/Edge
 * @returns {boolean}
 */
function isIE() {
    if (/MSIE 10/i.test(navigator.userAgent)) {
        // this is internet explorer 10
        return true;
    }

    if(/MSIE 9/i.test(navigator.userAgent) || /rv:11.0/i.test(navigator.userAgent)){
        // this is internet explorer 9 and 11
        return true;
    }

    if(/Edge\/12./i.test(navigator.userAgent)){
        // this is Microsoft Edge
        return true;
    }

    return false;
}

function debug(obj, stop) {
    stop = stop || false;
    console.log(obj);
    if(stop)
        throw new Error("debug stop!");
}

jQuery.fn.deserialize = function (data) {
    const f = this,
        map = {},
        find = function (selector) { return f.is("form") ? f.find(selector) : f.filter(selector); };
    //Get map of values
    jQuery.each(data.split("&"), function () {
        const nv = this.split("="),
            n = decodeURIComponent(nv[0]),
            v = nv.length > 1 ? decodeURIComponent(nv[1]) : null;
        if (!(n in map)) {
            map[n] = [];
        }
        map[n].push(v);
    })
    //Set values for all form elements in the data
    jQuery.each(map, function (n, v) {
        find("[name='" + n + "']").val(v);
    })
    //Clear all form elements not in form data
    find("input:text,select,textarea").each(function () {
        if (!(jQuery(this).attr("name") in map)) {
            jQuery(this).val("");
        }
    })
    find("input:checkbox:checked,input:radio:checked").each(function () {
        if (!(jQuery(this).attr("name") in map)) {
            this.checked = false;
        }
    })
    return this;
};
