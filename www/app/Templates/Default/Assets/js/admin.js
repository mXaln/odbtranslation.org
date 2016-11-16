/**
 * Created by Maxim on 05 Feb 2016.
 */

// ------------------ Jquery Events --------------------- //

$(function () {

    // Open gateway project form
    $("#cregwpr").click(function () {
        $("#gwProject").trigger("reset");
        $(".errors").html("");
        $(".main-content").css("left", 0);
    });

    $(".panel-close").click(function() {
        $(this).parents(".form-panel").css("left", "-9999px");
    });

    // Submit gateway project form
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

    // Open project Form
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

    // Get list of target languages for gateway language
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


    // Event options
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

    if($().ajaxChosen)
    {
        $("#adminsSelect").ajaxChosen({
                type: 'post',
                url: '/admin/rpc/get_members',
                dataType: 'json',
                minTermLength: 1,
                afterTypeDelay: 500,
                jsonTermKey: "search",
                lookingForMsg: Language.searchingFor,
            },
            function (data)
            {
                var terms = {};

                $.each(data, function (i, val) {
                    terms[i] = val;
                });

                return terms;
            },
            {
                no_results_text: Language.noResultText
            });
    }

    // Open event form
    $(".startEvnt").click(function() {
        var bookCode = $(this).attr("data");
        var bookName = $(this).attr("data2");
        var chapterNum = $(this).attr("data3");
        var sourceLangID = $("#sourceLangID").val();
        var bookProject = $("#bookProject").val();

        $("button[name=startEvent]").text(Language.create);
        $("#startEvent").trigger("reset");
        $(".errors").html("");
        $(".bookName").text(bookName);
        $("#bookCode").val(bookCode);
        $(".event-content").css("left", 0);
        $("#adminsSelect").empty().trigger("chosen:updated");

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

    // Submit event form
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

    // Edit event form
    $(".editEvnt").click(function () {
        var bookCode = $(this).attr("data");
        var eventID = $(this).attr("data2");

        $("#startEvent").trigger("reset");
        $(".errors").html("");
        $("#eventAction").val("edit");
        $("#adminsSelect").empty();
        $("#bookCode").val(bookCode);

        $.ajax({
            url: "/admin/rpc/get_event",
            method: "post",
            data: {eventID: eventID},
            dataType: "json",
            beforeSend: function() {
                //$(".eventLoader").show();
                $(".editEvnt").prop("disabled", true);
            }
        })
            .done(function(data) {
                if(data.success)
                {
                    $("button[name=startEvent]").text(Language.save);

                    $("#translators").val(data.event.translatorsNum);
                    $("#checkers_l2").val(data.event.l2CheckersNum);
                    $("#checkers_l3").val(data.event.l3CheckersNum);

                    $('#cal_from').datepicker('setDate', new Date(data.event.dateFrom + " UTC"));
                    $('#cal_to').datepicker('setDate', new Date(data.event.dateTo + " UTC"));

                    $(".bookName").text(data.event.name);
                    $(".book_info_content").html(
                        '(<strong>'+Language.chaptersNum+':</strong> '+data.event.chaptersNum+')'
                    );

                    var admins = data["event"].admins;
                    var content = "";
                    $.each(admins, function(k, v) {
                        content += '' +
                            '<option value="' + k + '" selected>' + v + '</option>';
                    });

                    $("#adminsSelect").prepend(content);

                    $(".event-content").css("left", 0);
                }
                else
                {
                    if(typeof data.error != "undefined")
                    {
                        if(data.error == "login" || data.error == "admin")
                            window.location.href = "/members/login";
                        else
                        {
                            renderPopup(data.error);
                        }
                    }
                }
            })
            .always(function() {
                //$(".eventLoader").hide();
                $(".editEvnt").prop("disabled", false);
                $("#adminsSelect").trigger("chosen:updated");
            });
    });

    // Activate/Verify member
    $(".verifyMember").click(function (e) {
        e.preventDefault();

        var memberID = $(this).attr("data");
        var parent = $(this).parents("tr");
        var activated = $(".activateMember", parent).is(":checked");
        var verify = $(this).is(":checked");

        if(!verify)
        {
            window.location.reload();
            return false;
        }

        var msg = "";
        if(!activated)
            msg += Language.notActivatedWarning + " ";
        msg += Language.verifyMessage;

        renderConfirmPopup(Language.verifyTitle, msg, function () {
            $(this).dialog("close");

            $.ajax({
                url: "/admin/rpc/verify_member",
                method: "post",
                data: {
                    memberID: memberID,
                },
                dataType: "json",
                beforeSend: function() {
                    //$(".commentEditorLoader").show();
                }
            })
                .done(function(data) {
                    if(data.success)
                    {
                        renderPopup(Language.verifySuccess);
                        parent.remove();
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
        });
    });
});


// --------------- Variables ---------------- //



// --------------- Functions ---------------- //
