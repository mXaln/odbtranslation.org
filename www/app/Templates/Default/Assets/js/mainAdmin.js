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
                    $(".form-panel").css("left", "-9999px");

                    renderPopup(data.success, function () {
                        location.reload();
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
                $("#gwLang").trigger("chosen:updated");
            });
    });


    // Sub Event Form
    $("select[name=sourceTranslation]").change(function() {
        if($(this).val() != "" && $(this).val() != "udb|en" && $(this).val() != "ulb|en")
        {
            $(".projectType").removeClass("hidden");
            $("#projectType").chosen();
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
        $(".projectType").addClass("hidden");
        $("#project select").val('').trigger("chosen:updated");
    });

    $("#subGwLangs").change(function() {
        var tlOptions = "<option value=''></option>";

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
                $("#project select").trigger("chosen:updated");
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
                    $(".form-panel").css("left", "-9999px");

                    renderPopup(data.success, function () {
                        location.reload();
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

    $("#translators").spinner({
        min: 1,
        step: 1,
        start: 1
    });

    $("#checkers_l2, #checkers_l3").spinner({
        min: 1,
        step: 1,
        start: 1
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
            '(<strong>'+Language.chaptersNum+':</strong> '+chapterNum+')'
        );
    });

    if(typeof $.fn.chosen == "function")
        $("#subGwLangs, #targetLangs, #sourceTranslation, #gwLang").chosen();

    // ------------------ DateTimePicker functionality ------------------- //
    if(typeof $.datepicker != "undefined" && typeof $.timepicker != "undefined")
    {
        var timeFormat;
        var timezoneList = [
            { value: -720, label: '(UTC-12:00) International Date Line West'},
            { value: -660, label: '(UTC-11:00) Midway Island, Samoa' },
            { value: -600, label: '(UTC-10:00) Hawaii' },
            { value: -540, label: '(UTC-09:00) Alaska' },
            { value: -480, label: '(UTC-08:00) Pacific Time (US and Canada); Tijuana' },
            { value: -420, label: '(UTC-07:00) Mountain Time (US and Canada), Chihuahua, La Paz, Mazatlan' },
            { value: -360, label: '(UTC-06:00) Central Time (US and Canada), Guadalajara, Mexico City, Monterrey' },
            { value: -300, label: '(UTC-05:00) Eastern Time (US and Canada), Bogota, Lima, Quito' },
            { value: -240, label: '(UTC-04:00) Atlantic Time (Canada), Caracas, La Paz' },
            { value: -210, label: '(UTC-04:30) Newfoundland and Labrador' },
            { value: -180, label: '(UTC-03:00) Buenos Aires, Georgetown, Greenland' },
            { value: -120, label: '(UTC-02:00) Mid-Atlantic' },
            { value: -60, label: '(UTC-01:00) Azores, Cape Verde Islands' },
            { value: 0, label: '(UTC+00:00) Greenwich Mean Time: Dublin, Edinburgh, Lisbon, London' },
            { value: 60, label: '(UTC+01:00) Belgrade, Sarajevo, Brussels, Madrid, Paris, Berlin, Rome, West Central Africa' },
            { value: 120, label: '(UTC+02:00) Bucharest, Cairo, Helsinki, Kiev, Tallinn, Athens, Istanbul, Jerusalem' },
            { value: 180, label: '(UTC+03:00) Moscow, Volgograd, Kuwait, Nairobi, Baghdad' },
            { value: 210, label: '(UTC+03:30) Tehran' },
            { value: 240, label: '(UTC+04:00) Abu Dhabi, Baku, Tbilisi, Yerevan' },
            { value: 270, label: '(UTC+04:30) Kabul' },
            { value: 300, label: '(UTC+05:00) Ekaterinburg, Islamabad, Karachi, Tashkent' },
            { value: 330, label: '(UTC+05:30) Kolkata, Mumbai, New Delhi' },
            { value: 345, label: '(UTC+05:45) Kathmandu' },
            { value: 360, label: '(UTC+06:00) Astana, Dhaka, Almaty, Novosibirsk' },
            { value: 390, label: '(UTC+06:30) Yangon Rangoon' },
            { value: 420, label: '(UTC+07:00) Bangkok, Hanoi, Jakarta, Krasnoyarsk' },
            { value: 480, label: '(UTC+08:00) Beijing, Hong Kong SAR, Kuala Lumpur, Singapore, Irkutsk' },
            { value: 540, label: '(UTC+09:00) Seoul, Osaka, Tokyo, Yakutsk' },
            { value: 570, label: '(UTC+09:30) Darwin, Adelaide' },
            { value: 600, label: '(UTC+10:00) Canberra, Melbourne, Sydney, Brisbane, Vladivostok, Guam' },
            { value: 660, label: '(UTC+11:00) Magadan, Solomon Islands, New Caledonia' },
            { value: 720, label: '(UTC+12:00) Fiji Islands, Kamchatka, Marshall Islands, Auckland, Wellington' },
        ];
        var lang = typeof getCookie("lang") != "undefined" ? getCookie("lang") : "en";

        switch (lang)
        {
            case "ru":
                timeFormat = "HH:mm z";
                break;

            default:
                timeFormat = "hh:mm TT z";
                break;
        }

        $( "#cal_from" ).datetimepicker({
            timeFormat: timeFormat,
            timezoneList: timezoneList,
            minDate: new Date(),
            onClose: function( selectedDate ) {
                if(selectedDate != "")
                    $( "#cal_to" ).datepicker( "option", "minDate", selectedDate );
            },
        });

        $( "#cal_to" ).datetimepicker({
            //defaultDate: "+1w",
            timeFormat: timeFormat,
            timezoneList: timezoneList,
            minDate: new Date(),
            onClose: function( selectedDate ) {
                if(selectedDate != "")
                    $( "#cal_from" ).datepicker( "option", "maxDate", selectedDate );
            }
        });
    }

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
                    renderPopup(data.success, function () {
                        location.reload();
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

        renderConfirmPopup(Language.deleteChapterConfirmTitle, Language.deleteChapterConfirm, function () {
            $( this ).dialog( "close" );

            var data = {};
            data.eventID = $("#eventID").val();
            data.chapter = $(".add_person_chapter", parent).attr("data");
            data.memberID = $this.attr("data");
            data.memberName = $this.prev(".uname").text();

            assignChapter(data, "delete");
        }, function () {
            $( this ).dialog( "close" );
        });
    });

    // Remove member from event
    $(document).on("click", ".delete_user", function() {
        $this = $(this);

        renderConfirmPopup(Language.removeFromEvent, Language.deleteMemberConfirm, function () {
            $( this ).dialog( "close" );

            var memberID = $this.parents(".member_usname").attr("data");
            var eventID = $("#eventID").val();

            $.ajax({
                url: "/events/rpc/delete_event_member",
                method: "post",
                data: {eventID: eventID, memberID: memberID},
                dataType: "json"
            })
                .done(function(data) {
                    if(data.success)
                    {
                        $(".member_usname[data="+memberID+"]").parents("li").remove();
                        $(".assign_chapter[data="+memberID+"]").parents("li").remove();

                        var mNum = parseInt($(".manage_members h3 span").text()); // number of current members
                        mNum -= 1;
                        $(".manage_members h3 span").text(mNum);
                    }
                    else
                    {
                        if(typeof data.error != "undefined")
                        {
                            renderPopup(data.error);
                        }
                        console.log(data.error);
                    }
                });
        }, function () {
            $( this ).dialog( "close" );
        });
    });

    // Start interval to check new applied translators
    var getMembersInterval = setInterval(function() {
        var eventID = $("#eventID").val();
        var ids = [];

        if(typeof isManagePage == "undefined") return false;
        if(typeof eventID == "undefined" || eventID == "") return false;

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
                        '   <div class="member_usname userlist chapter_ver">'+
                        '       <div class="divname">'+value.userName+'</div>'+
                        '       <div class="divvalue">(<span>0</span>)</div>'+
                        '   </div>'+
                        '   <button class="btn btn-success assign_chapter" data="'+value.memberID+'">'+Language.assign+'</button>'+
                        '   <div class="clear"></div>'+
                        '</li>';
                        $(".chapter_members_div ul").append(hiddenListLi);

                        var shownListLi = '<li>'+
                        '   <div class="member_usname" data="'+value.memberID+'">'+
                                value.userName+' (<span>0</span>)'+
                        '   <div class="glyphicon glyphicon-remove delete_user" title="'+Language.removeFromEvent+'"></div>'+
                        '   </div>'+
                        '   <div class="member_chapters">'+
                                Language.chapters+': <span></span>'+
                        '   </div>'+
                        '</li>';
                        $(".manage_members ul").append(shownListLi);

                        newUsers.push(value.userName);
                    });

                    if(newUsers.length > 0)
                    {
                        var mNum = parseInt($(".manage_members h3 span").text()); // number of current members
                        mNum += newUsers.length;
                        $(".manage_members h3 span").text(mNum);

                        renderPopup(Language.newUsersApplyed+": "+newUsers.join(", "));
                    }
                }
                else
                {
                    console.log(data.error);
                }
            });

    }, 30000);

    $("#startTranslation").click(function (e) {
        var $this = $(this);

        renderConfirmPopup(Language.startTranslation, Language.startTranslationConfirm, function () {
            $(this).dialog( "close" );
            $this.data("yes", true).click();
        }, function () {
            $(this).dialog("close");
        });

        if(typeof $this.data("yes") == "undefined")
            e.preventDefault();
    });

    // Show info tip
    $(".create_info_tip a").click(function () {
        renderPopup($(".create_info_tip span").text());
        return false;
    });
});


// --------------- Variables ---------------- //



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
                renderPopup(response.error);
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
                renderPopup(response.error);
            }
        })
        .always(function() {
            $(".assignPairLoader.dialog_f").hide();
            $(".assignPairLoader[data="+data.pairOrder+"]").hide();
        });
}
