/**
 * Created by Maxim on 05 Feb 2016.
 */

// ------------------ Jquery Events --------------------- //

$(function () {

    $("#cregwpr").click(function () {
        $("#gwProject").trigger("reset");
        $(".errors").html("");
        $(".main-content").css("left", 0);
        $("#adminsSelect").empty().trigger("chosen:updated");
        $("#gwLang").prop("disabled", false);
        $("#gwProjectAction").val("create");
        $("button[name=gwProject]").text(buttonCreate);
    });

    if($().ajaxChosen)
    {
        $("#adminsSelect").ajaxChosen({
                type: 'post',
                url: '/admin/rpc/get_members',
                dataType: 'json',
                minTermLength: 1,
                afterTypeDelay: 500,
                jsonTermKey: "search",
                lookingForMsg: "searching for"
            },
            function (data)
            {
                var terms = {};

                $.each(data, function (i, val) {
                    terms[i] = val;
                });

                return terms;
            });
    }

    $(".panel-close").click(function() {
        $(this).parents(".form-panel").css("left", "-9999px");
    });

    // Submit gwProject form
    $("#gwProject").submit(function(e) {

        $.ajax({
            url: $("#gwProject").prop("action"),
            method: "post",
            data: $("#gwProject").serialize(),
            dataType: "json",
            beforeSend: function() {
                $(".gwProjectLoader").show();
            }
        })
            .done(function(data) {
                if(data.success)
                {
                    $(".alert_message").text(data.success);
                    $( "#dialog-message" ).dialog({
                        modal: true,
                        resizable: false,
                        draggable: false,
                        width: 500,
                        buttons: {
                            Ok: function() {
                                $( this ).dialog( "close" );
                            }
                        },
                        close: function( event, ui ) {
                            location.reload();
                        }
                    });
                }
                else
                {
                    $(".errors").html(data.error);
                }
            })
            .always(function() {
                $(".gwProjectLoader").hide();
            });

        e.preventDefault();
    });

    $(".main-edit").click(function() {
        var gwLang = $(this).attr("data");

        $("#gwProject").trigger("reset");
        $(".errors").html("");
        $("#gwProjectAction").val("edit");
        $("button[name=gwProject]").text(buttonEdit);
        $("#adminsSelect").empty();

        $.ajax({
                url: "/admin/rpc/get_gw_project",
                method: "post",
                data: {gwLang: gwLang},
                dataType: "json",
                beforeSend: function() {
                    $(".gwProjectLoader").show();
                }
            })
            .done(function(data) {
                if(data.length <= 0) return false;

                if(typeof data.login != "undefined")
                    location.reload();

                if(data.length > 0)
                {
                    $("#gwLang").val(data[0].langID);
                    var admins = data[0].admins;

                    var content = "";

                    $.each(admins, function(k, v) {
                        content += '' +
                            '<option value="' + k + '" selected>' + v + '</option>';
                    });

                    $("#adminsSelect").prepend(content);
                    $("#adminsSelect").trigger("chosen:updated");
                }
            })
            .always(function() {
                $(".gwProjectLoader").hide();
                $(".main-content").css("left", 0);
            });
    });


    // Sub Event Form

    $("select[name=sourceTranslation]").change(function() {
        if($(this).val() != "" && $(this).val() != "udb|en" && $(this).val() != "ulb|en")
        {
            $(".projectType").removeClass("hidden");
        }
        else
        {
            $(".projectType").addClass("hidden");
        }
    });

    $("#crepr").click(function () {
        $("#project").trigger("reset");
        $(".subErrors").html("");
        $(".sub-content").css("left", 0);
    });

    $("#subGwLangs").change(function() {
        var tlOptions = "<option value=''>-- Choose Target Language --</option>";

        if($(this).val() == "") {
            $("#targetLangs").html(tlOptions);
            return;
        }

        $.ajax({
                url: "/admin/rpc/get_target_languages",
                method: "post",
                data: {gwLang: $("#subGwLangs").val()},
                dataType: "json",
                beforeSend: function() {
                    $(".subGwLoader").show();
                }
            })
            .done(function(data) {
                if(data.length <= 0) return false;

                if(typeof data.login != "undefined")
                    location.reload();

                $.each(data.targetLangs, function (i, v) {
                    tlOptions += '<option value="'+ v.langID+'">'+ v.langName+'</option>';
                });
                $("#targetLangs").html(tlOptions);
            })
            .always(function() {
                $(".subGwLoader").hide();
            });
    });

    // Submit project form
    $("#project").submit(function(e) {
        $.ajax({
                url: $("#project").prop("action"),
                method: "post",
                data: $("#project").serialize(),
                dataType: "json",
                beforeSend: function() {
                    $(".projectLoader").show();
                }
            })
            .done(function(data) {
                if(typeof data.login != "undefined")
                {
                    location.reload();
                    return false;
                }

                if(data.success)
                {
                    $(".alert_message").text(data.success);
                    $( "#dialog-message" ).dialog({
                        modal: true,
                        resizable: false,
                        draggable: false,
                        width: 500,
                        buttons: {
                            Ok: function() {
                                $( this ).dialog( "close" );
                            }
                        },
                        close: function( event, ui ) {
                            location.reload();
                        }
                    });
                }
                else
                {
                    $(".subErrors").html(data.error);
                }
            })
            .always(function() {
                $(".projectLoader").hide();
            });

        e.preventDefault();
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

    // Show list of members of event
    $("a[href=#translators]").click(function() {
        var eventId = $(this).attr("data");

        alert("list of translators for event #" + eventId + ". Not implemented.");
        return false;
    });

    $("a[href=#checkers_draft]").click(function() {
        var eventId = $(this).attr("data");

        alert("list of checkers_draft for event #" + eventId + ". Not implemented.");
        return false;
    });

    $("a[href=#checkers_l2]").click(function() {
        var eventId = $(this).attr("data");

        alert("list of checkers_l2 for event #" + eventId + ". Not implemented.");
        return false;
    });

    $("a[href=#checkers_l3]").click(function() {
        var eventId = $(this).attr("data");

        alert("list of checkers_l3 for event #" + eventId + ". Not implemented.");
        return false;
    });

    // Start event
    $(".startEvnt").click(function() {
        var bookCode = $(this).attr("data");
        var bookName = $(this).attr("data2");
        var chapterNum = $(this).attr("data3");
        var sourceLangID = $("#sourceLangID").val();
        var bookProject = $("#bookProject").val();

        $("#startEvent").trigger("reset");
        $(".errors").html("");
        $(".bookName").text(bookName);
        $("#bookCode").val(bookCode);
        $(".event-content").css("left", 0);

        $(".book_info_content").html(
            '<div><strong>Chapters:</strong> '+chapterNum+'</div>'
        );

        // Get source text
        /*$.ajax({
                url: "/admin/rpc/get_source",
                method: "post",
                data: {
                    bookCode: bookCode,
                    sourceLangID: sourceLangID,
                    bookProject: bookProject
                },
                dataType: "json",
                beforeSend: function() {
                    $(".book_info_content").html("");
                    $(".bookInfoLoader").show();
                }
            })
            .done(function(data) {
                if(!$.isEmptyObject(data))
                {
                    $(".book_info_content").html(
                        '<div><strong>Chapters:</strong> '+data.chaptersNum+'</div>' +
                        '<div><strong>Verses:</strong> '+data.versesNum+'</div>'
                    );
                }
                else
                {
                    $(".book_info").append(
                        'Book source not found'
                    );
                }
            })
            .always(function() {
                $(".bookInfoLoader").hide();
            });
        */
    });

    $( "#cal_from" ).datepicker({
        changeMonth: true,
        numberOfMonths: 1,
        onClose: function( selectedDate ) {
            $( "#cal_to" ).datepicker( "option", "minDate", selectedDate );
        }
    });
    $( "#cal_from" ).datepicker("option", "dateFormat", "yy-mm-dd");

    $( "#cal_to" ).datepicker({
        defaultDate: "+1w",
        changeMonth: true,
        numberOfMonths: 1,
        onClose: function( selectedDate ) {
            $( "#cal_from" ).datepicker( "option", "maxDate", selectedDate );
        }
    });
    $( "#cal_to" ).datepicker("option", "dateFormat", "yy-mm-dd");

    // Submit create event form
    $("#startEvent").submit(function(e) {

        $.ajax({
                url: $("#startEvent").prop("action"),
                method: "post",
                data: $("#startEvent").serialize(),
                dataType: "json",
                beforeSend: function() {
                    $(".startEventLoader").show();
                }
            })
            .done(function(data) {
                if(data.success)
                {
                    $(".alert_message").text(data.success);
                    $( "#dialog-message" ).dialog({
                        modal: true,
                        resizable: false,
                        draggable: false,
                        width: 500,
                        buttons: {
                            Ok: function() {
                                $( this ).dialog( "close" );
                            }
                        },
                        close: function( event, ui ) {
                            location.reload();
                        }
                    });
                }
                else
                {
                    $(".errors").html(data.error);
                }
            })
            .always(function() {
                $(".startEventLoader").hide();
            });

        e.preventDefault();
    });

    // Show assign chapter dialog
    $(".add_person_chapter").click(function() {
        $(".chapter_members_div .panel-title span").text($(this).attr("data"));

        $(".chapter_members").show();

        $('html, body').css({
            'overflow': 'hidden',
            'height': '100%'
        });
    });


    // Close assign chapter dialog
    $(".chapter-members-close").click(function() {
        $(".chapter_members").hide();

        $('html, body').css({
            'overflow': 'auto',
            'height': 'auto'
        });
    });

    // Assign chapter to translator
    $(document).on("click", ".assign_chapter", function() {
        var data = {};
        data.eventID = $("#eventID").val();
        data.chapter = $(".chapter_members_div .panel-title span").text();
        data.memberID = $(this).attr("data");
        data.memberName = $(this).prev(".member_usname").children(".divname").text();

        assignChapter(data, "add");
    });

    $(document).on("click", ".member_chapters span b", function() {
        var chapter = $(this).text();
        $(".chapter_"+chapter+" .uname_delete").trigger("click");
    });

    // Remove chapter from translator's chapter list
    $(document).on("click", ".uname_delete", function() {
        var parent = $(this).parents(".manage_chapters_user");
        var $this = $(this);

        if(!removeChapterConfirm)
        {
            var yes = Language.yes;
            var no = Language.no;

            var btns = {};
            btns[yes] = function(){
                removeChapterConfirm = true;
                $( this ).dialog( "close" );
                $this.click();
            };
            btns[no] = function(){
                $( this ).dialog( "close" );
                return false;
            };

            $(".confirm_message").text(Language.deleteChapterConfirm);
            $( "#check-book-confirm" ).dialog({
                resizable: false,
                draggable: false,
                title: Language.deleteChapterConfirmTitle,
                height: "auto",
                width: 500,
                modal: true,
                buttons: btns,
            });

            return false;
        }

        removeChapterConfirm = false;

        var data = {};
        data.eventID = $("#eventID").val();
        data.chapter = $(".add_person_chapter", parent).attr("data");
        data.memberID = $(this).attr("data");
        data.memberName = $(this).prev(".uname").text();

        assignChapter(data, "delete");
    });

    // Start interval to check new applyed translators
    var getMembersInterval = setInterval(function() {
        var eventID = $("#eventID").val();
        var ids = [];

        if(typeof eventID == "undefined") return;

        $.each($(".assign_chapter"), function() {
            ids.push($(this).attr("data"));
        });

        $.ajax({
            url: "/events/rpc/get_event_members",
            method: "post",
            data: {eventID: eventID, memberIDs: ids},
            dataType: "json"
        })
            .done(function(data) {
                if(data.success)
                {
                    var newUsers = [];
                    $.each(data.members, function(index, value) {
                        var hiddenListLi = '<li>'+
                            '<div class="member_usname userlist chapter_ver">'+
                                '<div class="divname">'+value.userName+'</div>'+
                                '<div class="divvalue">(<span>0</span>)</div>'+
                            '</div>'+
                            '<button class="btn btn-success assign_chapter" data="'+value.memberID+'">'+Language.assign+'</button>'+
                            '<div class="clear"></div>'+
                        '</li>';
                        $(".chapter_members_div ul").append(hiddenListLi);

                        var shownListLi = '<li>'+
                            '<div class="member_usname" data="'+value.memberID+'">'+value.userName+' (<span>0</span>)</div>'+
                            '<div class="member_chapters">'+
                                Language.chapters+': <span></span>'+
                            '</div>'+
                        '</li>';
                        $(".manage_members ul").append(shownListLi);

                        var pairListLi = '<li>'+
                            '<div class="member_usname userlist">'+value.userName+'</div>'+
                                '<input type="checkbox" class="checkForPair" id="checkForPair" data="'+value.memberID+'">'+
                            '<div class="clear"></div>'+
                        '</li>';
                        $(".pair_members_div ul").append(pairListLi);

                        newUsers.push(value.userName);
                    });

                    if(newUsers.length > 0)
                    {
                        var mNum = parseInt($(".manage_members h3 span").text()); // number of current members
                        mNum += newUsers.length;
                        $(".manage_members h3 span").text(mNum);

                        var pNum = parseInt($(".manage_pairs h3 span").text()); // number of current pairs
                        var pNumNew = Math.floor(mNum/2); // New number of pairs
                        var pNumAdd = pNumNew - pNum; // pair blocks to create

                        for(var i=1; i <= pNumAdd; i++)
                        {
                            var pair = pNum + i;
                            var li = '<li>'+
                                '<div class="pair_block pair_'+pair+'">'+
                                    '<div class="assignPairLoader inline_f" data="'+pair+'">'+
                                        '<img src="/app/templates/default/img/loader_alt.gif">'+
                                    '</div>'+
                                    '<h4>'+Language.pair_number+' '+pair+'</h4>'+
                                    '<div class="pair_member1"></div>'+
                                    '<div class="pair_member2"></div>'+
                                    '<div class="create_pair_block pair_button">'+
                                        '<button class="btn btn-success add_person_pair" data="'+pair+'">'+Language.assign_pair_title+'</button>'+
                                    '</div>'+
                                    '<div class="reset_pair_block pair_button">'+
                                        '<button class="btn btn-danger reset_pair" data="'+pair+'">'+Language.reset_pair_title+'</button>'+
                                    '</div>'+
                                '</div>'+
                            '</li>';

                            $(".manage_pairs ul").append(li);
                        }
                        $(".manage_pairs h3 span").text(pNumNew);

                        $(".alert_message").text(Language.newUsersApplyed+": "+newUsers.join(", "));
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
                    }
                }
                else
                {
                    console.log(data.error);
                }
            });

    }, 30000);


    // Show assign pair dialog
    $(document).on("click", ".add_person_pair", function() {
        $(".pair_members_div .panel-title span").text($(this).attr("data"));

        pairMemberIds = [];

        $(".pair_members").show();

        $('html, body').css({
            'overflow': 'hidden',
            'height': '100%'
        });

        pairMemberIds = [];
        $(".checkForPair").prop("checked", false).prop("disabled", false);
        $(".assign_pair").prop("disabled", true);
        $(".assigned_members").html("");
    });


    // Close assign pair dialog
    $(".pair-members-close").click(function() {
        $(".pair_members").hide();

        $('html, body').css({
            'overflow': 'auto',
            'height': 'auto'
        });

        pairMemberIds = [];
        $(".checkForPair").prop("checked", false).prop("disabled", false);
        $(".assign_pair").prop("disabled", true);
        $(".assigned_members").html("");
    });


    // Check member to create pair
    $(document).on("click", ".pair_members_div .member_usname", function () {
        $(this).next(".checkForPair").trigger("click");
    });
    
    $(document).on("change", ".checkForPair", function () {
        var memberId = $(this).attr("data");
        var userName = $(this).prev(".member_usname").text();

        if($(this).is(":checked"))
        {
            pairMemberIds.push(memberId);
            if(pairMemberIds.length >= 2)
            {
                $(".checkForPair:not(:checked)").prop("disabled", true);
                $(".assign_pair").prop("disabled", false);
            }
            $(".assigned_members").append("<div class='member_usname mid"+memberId+"'>"+userName+"</div>");
        }
        else
        {
            pairMemberIds = $.grep(pairMemberIds, function( a ) {
                return a !== memberId;
            });
            $(".checkForPair:not(:checked)").prop("disabled", false);
            $(".assign_pair").prop("disabled", true);
            $(".assigned_members .mid"+memberId).remove();
        }
    });

    // Save pair on server
    $(".assign_pair").click(function() {
        var data = {};
        data.eventID = $("#eventID").val();
        data.pairOrder = $(".pair_members_div .panel-title span").text();
        data.memberUnames = [];

        $.each($(".assigned_members .member_usname"), function () {
            data.memberUnames.push($(this).text());
        });

        assignPair(data, "create");
    });

    $(document).on("click", ".reset_pair", function () {
        var data = {};
        data.eventID = $("#eventID").val();
        data.pairOrder = $(this).attr("data");

        var $this = $(this);

        if(!resetPairConfirm)
        {
            var yes = Language.yes;
            var no = Language.no;

            var btns = {};
            btns[yes] = function(){
                resetPairConfirm = true;
                $( this ).dialog( "close" );
                $this.click();
            };
            btns[no] = function(){
                $( this ).dialog( "close" );
                return false;
            };

            $(".confirm_message").text(Language.resetPairConfirm);
            $( "#check-book-confirm" ).dialog({
                resizable: false,
                draggable: false,
                title: Language.resetPairConfirmTitle,
                height: "auto",
                width: 500,
                modal: true,
                buttons: btns,
            });

            return false;
        }

        resetPairConfirm = false;

        assignPair(data, "reset");
    });
    
    $("#startTranslation").click(function () {
        var $this = $(this);

        if(!startTranslationConfirm)
        {
            var yes = Language.yes;
            var no = Language.no;

            var btns = {};
            btns[yes] = function(){
                startTranslationConfirm = true;
                $( this ).dialog( "close" );
                $this.click();
            };
            btns[no] = function(){
                $( this ).dialog( "close" );
                return false;
            };

            $(".confirm_message").text(Language.start_translation_confirm);
            $( "#check-book-confirm" ).dialog({
                resizable: false,
                draggable: false,
                title: Language.start_translation,
                height: "auto",
                width: 500,
                modal: true,
                buttons: btns,
            });

            return false;
        }

        startTranslationConfirm = false;
    });
});


// --------------- Variables ---------------- //
var removeChapterConfirm = false;
var resetPairConfirm = false;
var startTranslationConfirm = false;
var pairMemberIds = [];



// --------------- Functions ---------------- //
function assignChapter(data, action)
{
    $(".alert.alert-danger, .alert.alert-success").remove();

    $.ajax({
        url: "/events/rpc/assign_chapter",
        method: "post",
        data: {
            eventID: data.eventID,
            chapter: data.chapter,
            memberID: data.memberID,
            action: action
        },
        dataType: "json",
        beforeSend: function() {
            $(".assignChapterLoader.dialog_f").show();
            $(".assignChapterLoader[data="+data.chapter+"]").show();
        }
    })
        .done(function(response) {
            if(response.success)
            {
                $(".chapter_members").hide();

                $('html, body').css({
                    'overflow': 'auto',
                    'height': 'auto'
                });

                // Update chapters block
                var chapterBlock = $(".chapter_"+data.chapter);
                if(action == "add")
                {
                    $("button", chapterBlock).hide();
                    $(".manage_username", chapterBlock).show();
                    $(".manage_username .uname", chapterBlock).text(data.memberName);
                    $(".manage_username .uname_delete", chapterBlock).attr("data", data.memberID);
                }
                else
                {
                    $("button", chapterBlock).show();
                    $(".manage_username", chapterBlock).hide();
                    $(".manage_username .uname", chapterBlock).text("");
                    $(".manage_username .uname_delete", chapterBlock).attr("data", "");
                }


                // Update members block
                var memberBlock = $(".manage_members .member_usname[data="+data.memberID+"]").parent("li");
                var arr = $(".member_chapters span", memberBlock).text().split(", ");
                var currentChapNum = parseInt($(".member_usname span", memberBlock).text());

                if(action == "add")
                {
                    if(arr[0] == "")
                        arr[0] = data.chapter;
                    else
                        arr.push(data.chapter);

                    arr.sort(function(a, b){return a-b});

                    currentChapNum++;
                }
                else
                {
                    arr = $.grep(arr, function( a ) {
                        return a !== data.chapter;
                    });
                    currentChapNum--;
                }

                $(".member_usname span", memberBlock).text(currentChapNum);
                $(".assign_chapter[data="+data.memberID+"]").prev(".member_usname").children(".divvalue").children("span").text(currentChapNum);

                if(arr.length > 0)
                {
                    $(".member_chapters span", memberBlock).html("<b>"+arr.join("</b>, <b>")+"</b>");
                    $(".member_chapters", memberBlock).show();
                }
                else
                {
                    $(".member_chapters span", memberBlock).html("");
                    $(".member_chapters", memberBlock).hide();
                }
            }
            else
            {
                $(".alert_message").text(response.error);
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
            }
        })
        .always(function() {
            $(".assignChapterLoader.dialog_f").hide();
            $(".assignChapterLoader[data="+data.chapter+"]").hide();
        });
}

function assignPair(data, action)
{
    $(".alert.alert-danger, .alert.alert-success").remove();

    $.ajax({
        url: "/events/rpc/assign_pair",
        method: "post",
        data: {
            eventID: data.eventID,
            pairOrder: data.pairOrder,
            members: pairMemberIds,
            action: action
        },
        dataType: "json",
        beforeSend: function() {
            $(".assignPairLoader.dialog_f").show();
            $(".assignPairLoader[data="+data.pairOrder+"]").show();
        }
    })
        .done(function(response) {
            if(response.success)
            {
                $(".pair_members").hide();

                $('html, body').css({
                    'overflow': 'auto',
                    'height': 'auto'
                });

                // Update pair block
                var pairBlock = $(".pair_"+data.pairOrder);

                if(action == "create")
                {
                    $(".pair_member1", pairBlock).text(data.memberUnames[0]);
                    $(".pair_member2", pairBlock).text(data.memberUnames[1]);
                    $(".create_pair_block", pairBlock).hide();
                    $(".reset_pair_block", pairBlock).show();
                    
                    $.each(response.members, function (i, v) {
                        $(".pair_members_div .checkForPair[data="+v.memberID+"]").parent("li").remove();
                    });
                }
                else
                {
                    $(".pair_member1", pairBlock).text("");
                    $(".pair_member2", pairBlock).text("");
                    $(".create_pair_block", pairBlock).show();
                    $(".reset_pair_block", pairBlock).hide();

                    $.each(response.members, function (i, v) {
                        var pairListLi = '<li>'+
                            '<div class="member_usname userlist">'+v.userName+'</div>'+
                            '<input type="checkbox" class="checkForPair" id="checkForPair" data="'+v.memberID+'">'+
                            '<div class="clear"></div>'+
                            '</li>';
                        $(".pair_members_div ul").append(pairListLi);
                    });
                }
            }
            else
            {
                $(".alert_message").text(response.error);
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
            }
        })
        .always(function() {
            $(".assignPairLoader.dialog_f").hide();
            $(".assignPairLoader[data="+data.pairOrder+"]").hide();
        });
}
