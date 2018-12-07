/**
 * Created by Maxim on 05 Feb 2016.
 */

// ------------------ Jquery Events --------------------- //

var importLocked = false;

$(function () {

    if(typeof $.fn.chosen == "function")
        $("#subGwLangs, #targetLangs, "
            + "#sourceTranslation, "
            + "#sourceTranslationNotes, "
            + "#gwLang, #projectMode")
            .chosen();

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

    $("select[name=projectMode]").change(function() {
        if($(this).val() == "bible")
        {
            $("#sourceTranslationNotes").val('').trigger("chosen:updated");
            $(".sourceTranslationNotes").addClass("hidden");
            $("#sourceTranslationQuestions").val('').trigger("chosen:updated");
            $(".sourceTranslationQuestions").addClass("hidden");
            $("#sourceTranslationWords").val('').trigger("chosen:updated");
            $(".sourceTranslationWords").addClass("hidden");
            $(".projectType").removeClass("hidden");
            $(".sourceTranslation").removeClass("hidden");
        }
        else if($(this).val() == "tn")
        {
            $(".sourceTranslationNotes").removeClass("hidden");
            $("#sourceTranslationNotes").chosen();
            $("#sourceTranslationQuestions").val('').trigger("chosen:updated");
            $(".sourceTranslationQuestions").addClass("hidden");
            $("#sourceTranslationWords").val('').trigger("chosen:updated");
            $(".sourceTranslationWords").addClass("hidden");
            $(".projectType").removeClass("hidden");
            $(".sourceTranslation").removeClass("hidden");
        }
        else if($(this).val() == "tq")
        {
            $(".sourceTranslationQuestions").removeClass("hidden");
            $("#sourceTranslationQuestions").chosen();
            $("#sourceTranslationNotes").val('').trigger("chosen:updated");
            $(".sourceTranslationNotes").addClass("hidden");
            $("#sourceTranslationWords").val('').trigger("chosen:updated");
            $(".sourceTranslationWords").addClass("hidden");
            $(".projectType").addClass("hidden");
            $(".sourceTranslation").addClass("hidden");
        }
        else if($(this).val() == "tw")
        {
            $(".sourceTranslationWords").removeClass("hidden");
            $("#sourceTranslationWords").chosen();
            $("#sourceTranslationNotes").val('').trigger("chosen:updated");
            $(".sourceTranslationNotes").addClass("hidden");
            $("#sourceTranslationQuestions").val('').trigger("chosen:updated");
            $(".sourceTranslationQuestions").addClass("hidden");
            $(".projectType").addClass("hidden");
            $(".sourceTranslation").addClass("hidden");
        }
    });
    

    $("#crepr").click(function () {
        $("#project").trigger("reset");
        $(".subErrors").html("");
        $(".sub-content").css("left", 0);
        /*$(".projectType").addClass("hidden");*/
        $("#project select").val('').trigger("chosen:updated");
        $("#projectType").chosen();
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
                    tlOptions += '<option value="'+ v.langID+'">'+
                        '['+v.langID+'] '+v.langName+(v.angName != "" && v.langName != v.angName ? ' ( '+v.angName+' )' : '')+
                    '</option>';
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

    // ------------------ DateTimePicker functionality ------------------- //
    /*if(typeof $.datepicker != "undefined" && typeof $.timepicker != "undefined")
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
            //minDate: new Date(),
            onClose: function( selectedDate ) {
                if(selectedDate != "")
                    $( "#cal_to" ).datepicker( "option", "minDate", selectedDate );
            },
        });

        $( "#cal_to" ).datetimepicker({
            //defaultDate: "+1w",
            timeFormat: timeFormat,
            timezoneList: timezoneList,
            //minDate: new Date(),
            onClose: function( selectedDate ) {
                if(selectedDate != "")
                    $( "#cal_from" ).datepicker( "option", "maxDate", selectedDate );
            }
        });
    }*/

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

    function resetEventForm() {
        $("#startEvent").trigger("reset");

        $(".event_menu").hide();
        $("#adminsSelect").empty().trigger("chosen:updated");
        $(".delinput").hide();
        $(".errors").html("");

        $(".event_links_l1").show();
        $(".event_links_l1 a").attr("href", "#");
        $(".event_links_l2").show();
        $(".event_links_l2 a").attr("href", "#");
        $(".event_links_l3").show();
        $(".event_links_l3 a").attr("href", "#");

        $(".event_imports").hide();
        $("input[name=eventLevel]").prop("disabled", false);

        setImportLinks("l1", ImportStates.DEFAULT);
        setImportLinks("l2", ImportStates.DEFAULT);
        setImportLinks("l3", ImportStates.DEFAULT);
        setImportLinks("tn", ImportStates.DEFAULT);
        setImportLinks("tq", ImportStates.DEFAULT);
        setImportLinks("tw", ImportStates.DEFAULT);

        $("#eventAction").val("create");
        $("button[name=startEvent]").text(Language.create);
    }

    // Open event form
    $(".startEvnt").click(function() {
        $(".event-content").css("left", 0);

        resetEventForm();

        var bookCode = $(this).data("bookcode");
        var bookName = $(this).data("bookname");
        var chaptersNum = $(this).data("chapternum");

        $(".bookName").text(bookName);
        $("#bookCode").val(bookCode);

        $(".book_info_content").html(
            '(<strong>'+Language.chaptersNum+':</strong> '+chaptersNum+')'
        );
    });


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
                    location.reload();
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
        resetEventForm();

        var bookCode = $(this).data("bookcode");
        var eventID = $(this).data("eventid");
        var abbrID = $(this).data("abbrid");

        $("#eID").val(eventID);
        $("#abbrID").val(abbrID);
        $("#bookCode").val(bookCode);

        $("#eventAction").val("edit");
        $(".event_menu").show();

        $.ajax({
            url: "/admin/rpc/get_event",
            method: "post",
            data: {eventID: eventID},
            dataType: "json",
            beforeSend: function() {
                $(".editEvnt").prop("disabled", true);
            }
        })
            .done(function(data) {
                if(data.success)
                {
                    setImportComponent(data.event);
                    setEventMenu(data.event);
                    setStartEventButton(data.event);

                    // Set the status of ulb translation
                    if(typeof data.ulb != "undefined")
                        setImportLinksUlb(data.ulb.state, data.event.state);

                    $(".bookName").text(data.event.name);
                    $(".book_info_content").html(
                        '(<strong>'+Language.chaptersNum+':</strong> '+data.event.chaptersNum+')'
                    );

                    var admins = data["admins"];
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
                $(".editEvnt").prop("disabled", false);
                $("#adminsSelect").trigger("chosen:updated");
            });
    });

    $(".event_menu .glyphicon-menu-hamburger").click(function(e) {
        if ($(".event_menu ul").is(":visible"))
            $(".event_menu ul").hide();
        else
            $(".event_menu ul").show();

        e.stopPropagation();
    });

    $(document).click(function(e) {
        if(!e.target.classList.contains("option_group"))
            $(".event_menu ul").hide();
    });

    $(".event_menu .clearCache").click(function () {
        var abbrID = $("#abbrID").val();
        var bookCode = $("#bookCode").val();
        var sourceLangID = $("#sourceLangID").val();
        var sourceBible = $("#sourceBible").val();

        $(".event_menu ul").hide();

        $.ajax({
            url: "/admin/rpc/clear_cache",
            method: "post",
            data: {
                abbrID: abbrID,
                bookCode: bookCode,
                sourceLangID: sourceLangID,
                sourceBible: sourceBible
            },
            dataType: "json",
            beforeSend: function() {
                $(".startEventLoader").show();
            }
        })
            .done(function(data) {
                if(data.success)
                {
                    renderPopup(Language.cacheUpdated);
                }
                else
                    renderPopup(Language.commonError, function () {
                        window.location.reload();
                    }, function () {
                        window.location.reload();
                    });
            })
            .always(function() {
                $(".startEventLoader").hide();
            });
    });

    $(".event_menu .deleteEvent").click(function () {
        $(".event_menu ul").hide();
        $(".delinput").show();
    });

    $("input[name=eventLevel]").change(function () {
        var level = $("input[name=eventLevel]:checked").val();
        var initialLevel = $("#initialLevel").val();

        if(level > initialLevel)
        {
            $("button[name=startEvent]").text(Language.create);
            $("#eventAction").val("create");
        }
        else
        {
            $("button[name=startEvent]").text(Language.save);
            $("#eventAction").val("edit");
        }
    });

    $("button[name=deleteEvent]").click(function (e) {
        var bookName = $(".bookName").text();
        var delName = $("#delevnt").val();

        if($(".delinput").is(":visible") && bookName == delName)
            $("#eventAction").val("delete");
        else
            e.preventDefault();
    });

    $("button[name=updateAllCache]").click(function (e) {
        var $this = $(this);
        var sourceLangID = $this.data("sourcelangid");
        var sourceBible = $this.data("sourcebible");

        $.ajax({
            url: "/admin/rpc/update_all_cache",
            method: "post",
            data: {
                sourceLangID: sourceLangID,
                sourceBible: sourceBible
            },
            dataType: "json",
            beforeSend: function() {
                $(".cacheLoader").show();
                $this.prop("disabled", true);
            }
        })
            .done(function(data) {
                if(data.success)
                {
                    renderPopup(Language.cacheUpdated + ": " + data.booksUpdated + " " + Language.books);
                }
                else
                    renderPopup(Language.commonError, function () {
                        window.location.reload();
                    }, function () {
                        window.location.reload();
                    });
            })
            .always(function() {
                $(".cacheLoader").hide();
                $this.prop("disabled", false);
            });

        e.preventDefault();
    });

    $("input[name=eventLevel]").change(function () {
        var level = $("input[name=eventLevel]:checked").val();
        var bookProject = $("#bookProject").val();

        switch (level) {
            case "1":
                $(".event_imports").hide();
                break;
            case "2":
                if(["ulb","udb"].indexOf(bookProject) > -1)
                {
                    $(".l1_import").show();
                    $(".l2_import").hide();
                    $(".event_imports").hide().slideDown(200);
                }
                else
                    $(".event_imports").hide();
                break;
            case "3":
                if(["ulb","udb"].indexOf(bookProject) > -1)
                {
                    $(".l1_import").hide();
                    $(".l2_import").show();
                }
                $(".event_imports").hide().slideDown(200);
                break;
        }
    });

    $(".import_link").click(function (e) {
        var source = $(this).data("source");
        var bookProject = $("#bookProject").val();

        switch (source) {
            case "tq":
            case "tn":
            case "tw":
                $("li[data-type=usfm]").hide();
                $("li[data-type=ts]").hide();
                $("li[data-type=zip]").show();
                $("#importLevel").val(2);

                $("#importProject").val(bookProject);
                break;

            case "l1":
            case "l2":
            case "l3":
                if(["tn","tq","tw"].indexOf(bookProject) > -1)
                {
                    $("#importProject").val("ulb");

                    if(source == "l3")
                    {
                        var l2_import = $(".l2_import .import_done");
                        if(!l2_import.hasClass("done") || !l2_import.is(":visible"))
                        {
                            renderPopup(Language.import_l2_warning);
                            return;
                        }
                    }
                }

                $("#importLevel").val(source.replace( /^\D+/g, ''));

                $("li[data-type=usfm]").show();
                $("li[data-type=ts]").show();
                $("li[data-type=zip]").hide();
                break;
        }

        $(".event-content").css("left", -9000);
        $(".import_menu_content").css("left", 0);

        e.preventDefault();
    });

    $(".import_menu ul li:last-child").click(function () {
        if(importLocked) return false;
        $(".import_menu_content").css("left", -9000);
        $(".event-content").css("left", 0);
    });

    $(".dcs_import_menu ul li:last-child").click(function () {
        if(importLocked) return false;
        $(".dcs_import_menu_content").css("left", -9000);
        $(".import_menu_content").css("left", 0);
    });

    $(".import_menu label").click(function () {
        if(importLocked) return false;
        return true;
    });

    $("#dcs_form").submit(function (e) {
        e.preventDefault();
        return false;
    });

    $(".import_menu input[name=import]").change(function () {
        if(importLocked) return false;

        var input = $(this);
        var form = $(this).parents("form");
        var formData = null;
        var projectID = $("#projectID").val();
        var eventID = $("#eID").val();
        var bookCode = $("#bookCode").val();
        var bookProject = $("#bookProject").val();
        var importLevel = $("#importLevel").val();
        var importProject = $("#importProject").val();

        if (window.FormData){
            formData = new FormData(form[0]);
            formData.append("projectID", projectID);
            formData.append("eventID", eventID);
            formData.append("bookCode", bookCode);
            formData.append("bookProject", bookProject);
            formData.append("importLevel", importLevel);
            formData.append("importProject", importProject);
        }

        $.ajax({
            url         : '/admin/rpc/import',
            data        : formData ? formData : form.serialize(),
            cache       : false,
            contentType : false,
            processData : false,
            type        : 'POST',
            dataType    : "json",
            beforeSend: function() {
                importLocked = true;
                $(".importLoader").show();
            }
        })
            .done(function(response) {
                if(response.success)
                {
                    var message = typeof response.warning != "undefined"
                        ? response.message + " " + "(with warning: We couldn't define the related scripture. Contact administrator.)"
                        : response.message;

                    renderPopup(response.message, function () {
                        location.reload();
                    }, function () {
                        location.reload();
                    });
                }
                else
                {
                    renderPopup(response.error);
                }
            })
            .always(function() {
                input.val("");
                importLocked = false;
                $(".importLoader").hide();
            });
    });

    $(".import_menu ul li").click(function () {
        var type = $(this).data("type");
        if(type == "dcs")
        {
            $(".dcs_list tbody").html("");
            $("input[name=dcs_repo_name]").val("");
            $(".import_menu_content").css("left", -9000);
            $(".dcs_import_menu_content").css("left", 0);
        }
    });


    var dcs_timeout = null;
    $("body").on("keyup", "input[name=dcs_repo_name]", function () {
        if(importLocked) return false;

        var q = $(this).val();

        clearTimeout(dcs_timeout);
        dcs_timeout = setTimeout(function() {
            $.ajax({
                url: "/admin/rpc/repos_search/" + q,
                method: "get",
                dataType: "json",
                beforeSend: function() {
                    $(".importLoader").show();
                }
            })
                .done(function(response) {
                    $(".dcs_list tbody").html("");
                    if(response.data.length > 0)
                    {
                        $.each(response.data, function (i, v) {
                            var ts = Date.parse(v.updated_at);
                            var date = new Date(ts);

                            var list = "<tr data-url='"+ v.clone_url +"'>";
                            list += "<td>"+ v.owner.login +"</td>";
                            list += "<td>" + v.name +"</td>";
                            list += "<td>" + date.toLocaleString() +"</td>";
                            list += "</tr>";
                            $(".dcs_list tbody").append(list);
                        });
                    }
                    else
                    {
                        // TODO show "nothing found" message
                    }
                })
                .always(function() {
                    $(".importLoader").hide();
                });
        }, 1000);
    });

    $("body").on("click", ".dcs_list tr", function() {
        if(importLocked) return false;

        var repo_url = $(this).data("url");
        var projectID = $("#projectID").val();
        var eventID = $("#eID").val();
        var bookCode = $("#bookCode").val();
        var bookProject = $("#bookProject").val();
        var importLevel = $("#importLevel").val();
        var importProject = $("#importProject").val();

        $.ajax({
            url: "/admin/rpc/import",
            method: "post",
            data: {
                import: repo_url,
                type: "dcs",
                projectID: projectID,
                eventID: eventID,
                bookCode: bookCode,
                bookProject: bookProject,
                importLevel: importLevel,
                importProject: importProject
            },
            dataType: "json",
            beforeSend: function() {
                importLocked = true;
                $(".importLoader").show();
            }
        })
            .done(function(response) {
                if(response.success)
                {
                    renderPopup(response.message, function () {
                        location.reload();
                    }, function () {
                        location.reload();
                    });
                }
                else
                {
                    renderPopup(response.error);
                }
            })
            .always(function() {
                importLocked = false;
                $(".importLoader").hide();
            });
    });
    

    // Show event contributors
    $(".showContributors").click(function () {
        var eventID = $(this).data("eventid");
        var level = $(this).data("level");
        var mode = $(this).data("mode");

        $.ajax({
            url: "/admin/rpc/get_event_contributors",
            method: "post",
            data: {eventID: eventID, level: level, mode: mode},
            dataType: "json",
            beforeSend: function() {
                $(".showContributors").prop("disabled", true);
            }
        })
            .done(function(data) {
                if(data.success)
                {
                    var html = "";

                    // Render facilitators
                    html += "<div class='admins_list'>" +
                        "<div class='contrib_title'>"+Language.facilitators+":</div>";
                    $.each(data.admins, function (i,v) {
                        html += "<div>" +
                            "<a href='/members/profile/"+i+"'>"+v.userName+" ("+v.name+")</a>" +
                            "</div>";
                    });
                    html += "</div>";

                    // Render translators
                    if(Object.keys(data.translators).length > 0)
                    {
                        html += "<div class='translators_list'>" +
                            "<div class='contrib_title'>"+Language.translators+":</div>";
                        $.each(data.translators, function (i,v) {
                            html += "<div>" +
                                "<a href='/members/profile/"+i+"'>"+v.userName+" ("+v.name+")</a>" +
                                "</div>";
                        });
                        html += "</div>";
                    }

                    // Render checkers
                    html += "<div class='checkers_list'>" +
                        "<div class='contrib_title'>"+Language.checkers+":</div>";
                    $.each(data.checkers, function (i,v) {
                        html += "<div>" +
                            "<a href='/members/profile/"+i+"'>"+v.userName+" ("+v.name+")</a>" +
                            "</div>";
                    });
                    html += "</div>";

                    $(".contributors_content").html(html);
                    $(".contributors_container").css("left", 0);
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
                $(".showContributors").prop("disabled", false);
            });
    });

    $(".contributors-close").click(function () {
        $(".contributors_container").css("left", -9999);
    });


    // Show event contributors
    $(".showAllContibutors").click(function () {
        var projectID = $(this).data("projectid");

        $.ajax({
            url: "/admin/rpc/get_project_contributors",
            method: "post",
            data: {projectID: projectID},
            dataType: "json",
            beforeSend: function() {
                $(".contibLoader").show();
            }
        })
            .done(function(data) {
                if(data.success)
                {
                    var html = "<ul>";

                    $.each(data.contributors, function () {
                        html += "<li>"+this+"</li>";
                    });

                    html += "</ul>";

                    $(".contributors_title").hide();
                    $(".contributors_title.proj").show();
                    $(".contributors_content").html(html);
                    $(".contributors_container").css("left", 0);
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
                $(".contibLoader").hide();
            });
    });

    $(".contributors-close").click(function () {
        $(".contributors_container").css("left", -9999);
        $(".contributors_title").show();
        $(".contributors_title.proj").hide();
    });


    // Activate/Verify member
    $(".verifyMember").click(function (e) {
        e.preventDefault();

        var memberID = $(this).attr("data");
        var parent = $(this).parents("tr");
        var activated = $(".activateMember", parent).is(":checked");

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
                        // renderPopup(Language.verifySuccess);
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

        return false;
    });


    // Block/Unblock member
    $("body").on("click", ".blockMember", function (e) {
        e.preventDefault();

        var $this = $(this);
        var memberID = $this.attr("data");

        $.ajax({
            url: "/admin/rpc/block_member",
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
                    if(data.blocked)
                    {
                        renderPopup(Language.blockedSuccess);
                        $this.removeClass("btn-danger")
                            .addClass("btn-primary")
                            .text(Language.unblock);
                    }
                    else
                    {
                        renderPopup(Language.unblockedSuccess);
                        $this.removeClass("btn-primary")
                            .addClass("btn-danger")
                            .text(Language.block);
                    }
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

    // Members tabs switch
    $(".mems_tab").click(function () {
        var id = $(this).attr("id");

        $(".members_content").removeClass("shown");
        $(".mems_tab").removeClass("active");

        $(this).addClass("active");
        $("#"+id+"_content").addClass("shown");

        return false;
    });

    if ($("select.mems_language").length > 0) {
        $("select.mems_language").chosen();
    }

    // Submit Filter form
    $(".filter_apply button").click(function () {
        var button = $(this);
        button.prop("disabled", true);
        $(".filter_page").val(1);

        $.ajax({
            url: "/admin/rpc/search_members",
            method: "post",
            data: $("#membersFilter").serialize(),
            dataType: "json",
            beforeSend: function() {
                $(".filter_loader").show();
            }
        })
            .done(function(data) {
                if(data.success)
                {
                    if(data.members.length > 0)
                    {
                        $("#all_members_table").show();
                        $(".filter_page").val(1);

                        // if it has more results to show draw "more" button
                        if(data.members.length < parseInt(data.count))
                        {
                            if($("#search_more").length <= 0)
                            {
                                $('<div id="search_more"></div>').appendTo("#all_members_content")
                                    .text(Language.searchMore);
                            }
                            $(".filter_page").val(2);
                        }
                        else
                        {
                            $("#search_more").remove();
                        }

                        $("#search_empty").remove();
                    }
                    else
                    {
                        $("#all_members_table").hide();
                        if($("#search_empty").length <= 0)
                            $('<div id="search_empty"></div>').appendTo("#all_members_content")
                                .text(Language.searchEmpty);
                        $('#search_more').remove();
                    }

                    $("#all_members_table tbody").html("");
                    $.each(data.members, function (i, v) {
                        var row = "<tr>" +
                            "<td><a href='/members/profile/"+v.memberID+"'>"+v.userName+"</a></td>" +
                            "<td>"+v.firstName+" "+v.lastName+"</td>" +
                            "<td>"+v.email+"</td>" +
                            "<td>"+(v.projects ? JSON.parse(v.projects).map(function (proj) {
                                return Language[proj];
                            }).join(", ") : "")+"</td>" +
                            "<td>"+(v.proj_lang ? "["+v.langID+"] "+v.langName +
                                (v.angName != "" && v.angName != v.langName ? " ("+v.angName+")" : "") : "")+"</td>" +
                            "<td><input type='checkbox' "+(parseInt(v.complete) ? "checked" : "")+" disabled></td>" +
                            "<td class=\"block_btn\"><button class='blockMember btn "+(v.blocked == 1 ? "btn-primary" : "btn-danger")+"' data='"+v.memberID+"'>" +
                                (v.blocked == 1 ? Language.unblock : Language.block)+"</button></td>" +
                            "</tr>";
                        $("#all_members_table tbody").append(row);
                    });
                }
                else
                {
                    if(typeof data.error != "undefined")
                    {
                        renderPopup(data.error);
                    }
                }
            })
            .always(function() {
                $(".filter_loader").hide();
                button.prop("disabled", false);
            });

        return false;
    });

    $("body").on("click", "#search_more", function () {
        var button = $(this);

        if(button.hasClass("disabled")) return false;

        button.addClass("disabled");
        var page = parseInt($(".filter_page").val());

        $.ajax({
            url: "/admin/rpc/search_members",
            method: "post",
            data: $("#membersFilter").serialize(),
            dataType: "json",
            beforeSend: function() {
                $(".filter_loader").show();
            }
        })
            .done(function(data) {
                if(data.success)
                {
                    if(data.members.length > 0)
                    {
                        $(".filter_page").val(page+1);
                        $.each(data.members, function (i, v) {
                            var row = "<tr>" +
                                "<td><a href='/members/profile/"+v.memberID+"'>"+v.userName+"</a></td>" +
                                "<td>"+v.firstName+" "+v.lastName+"</td>" +
                                "<td>"+v.email+"</td>" +
                                "<td>"+(v.projects ? JSON.parse(v.projects).map(function (proj) {
                                    return Language[proj];
                                }).join(", ") : "")+"</td>" +
                                "<td>"+(v.proj_lang ? "["+v.langID+"] "+v.langName +
                                    (v.angName != "" && v.angName != v.langName ? " ("+v.angName+")" : "") : "")+"</td>" +
                                "<td><input type='checkbox' "+(parseInt(v.complete) ? "checked" : "")+" disabled></td>" +
                                "<td class=\"block_btn\"><button class='blockMember btn "+(v.blocked == 1 ? "btn-primary" : "btn-danger")+"' data='"+v.memberID+"'>" +
                                (v.blocked == 1 ? Language.unblock : Language.block)+"</button></td>" +
                                "</tr>";
                            $("#all_members_table tbody").append(row);
                        });

                        var results = parseInt($("#all_members_table tbody tr").length);
                        if(results >= parseInt(data.count))
                            $('#search_more').remove();
                    }
                    else
                    {
                        $('#search_more').remove();
                    }
                }
                else
                {
                    if(typeof data.error != "undefined")
                    {
                        renderPopup(data.error);
                    }
                }
            })
            .always(function() {
                $(".filter_loader").hide();
                button.removeClass("disabled");
            });
    });

    // Clear members filter
    $(".filter_clear").click(function () {
        $("#membersFilter")[0].reset();
        $(".mems_language").val('').trigger("chosen:updated");
        return false;
    });

    // Admin tools

    // Update languages database
    $(".update_langs button").click(function () {
        $.ajax({
            url: "/admin/rpc/update_languages",
            method: "post",
            dataType: "json",
            beforeSend: function() {
                $(".update_langs img").show();
            }
        })
            .done(function(data) {
                if(data.success)
                {
                    renderPopup("Updated!");
                }
                else
                {
                    if(typeof data.error != "undefined")
                    {
                        renderPopup(data.error);
                    }
                }
            })
            .always(function() {
                $(".update_langs img").hide();
            });
    });

    // Create multiple users
    $(".create_users button").click(function () {
        var amount = $(".create_users #amount").val();
        var langs = $(".create_users #langs").val();
        var password = $(".create_users #password").val();

        $.ajax({
            url: "/admin/rpc/create_multiple_users",
            method: "post",
            data: {amount: amount, langs: langs, password: password},
            dataType: "json",
            beforeSend: function() {
                $(".create_users img").show();
            }
        })
            .done(function(data) {
                if(data.success)
                {
                    $(".create_users #amount").val("");
                    $(".create_users #langs").val("");
                    $(".create_users #password").val("");

                    renderPopup(data.msg);
                }
                else
                {
                    if(typeof data.error != "undefined")
                    {
                        renderPopup(data.error);
                    }
                }
            })
            .always(function() {
                $(".create_users img").hide();
            });
    });


    // Managing SAIL Dictionary

    // Delete word
    $("body").on("click", ".tools_delete_word", function (e) {
        var li = $(this).parent("li");
        var word = li.attr("id");

        renderConfirmPopup(Language.attention, Language.delSailword + word + "?", function () {
            $( this ).dialog( "close" );
            $.ajax({
                url: "/admin/rpc/delete_sail_word",
                method: "post",
                data: {word: word},
                dataType: "json",
                beforeSend: function() {
                    $("img", li).show();
                }
            })
                .done(function(data) {
                    if(data.success)
                    {
                        li.remove();
                    }
                    else
                    {
                        if(typeof data.error != "undefined")
                        {
                            renderPopup(data.error);
                        }
                    }
                })
                .always(function() {
                    $("img", li).hide();
                });
        });

        e.preventDefault();
        return false;
    });

    // Create word
    $("body").on("click", ".sail_create .add_word", function (e) {
        var word = $("#sailword").val();
        var symbol = $("#sailsymbol").val();

        $.ajax({
            url: "/admin/rpc/create_sail_word",
            method: "post",
            data: {word: word, symbol: symbol},
            dataType: "json",
            beforeSend: function() {
                $("#sail_create_loader").show();
            }
        })
            .done(function(data) {
                if(data.success)
                {
                    $("#sailword").val("");
                    $("#sailsymbol").val("");
                    $(".sail_list.tools ul").append(data.li);
                }
                else
                {
                    if(typeof data.error != "undefined")
                    {
                        renderPopup(data.error);
                    }
                }
            })
            .always(function() {
                $("#sail_create_loader").hide();
            });

        e.preventDefault();
        return false;
    });


    // Create News
    $(".create_news button").click(function (e) {
        var title = $("#title").val();
        var category = $("#category").val();
        var text = $("#text").val();

        $.ajax({
            url: "/admin/rpc/create_news",
            method: "post",
            data: {title: title, category: category, text: text},
            dataType: "json",
            beforeSend: function() {
                $(".create_news img").show();
            }
        })
            .done(function(data) {
                if(data.success)
                {
                    $("#title").val("");
                    $("#category").val("");
                    $("#text").val("");

                    renderPopup(data.msg);
                }
                else
                {
                    if(typeof data.error != "undefined")
                    {
                        renderPopup(data.error);
                    }
                }
            })
            .always(function() {
                $(".create_news img").hide();
            });

        e.preventDefault();
        return false;
    });
});


// --------------- Variables ---------------- //
var EventStates = {
    states: {
        "started": 0,
        "translating": 1,
        "translated": 2,
        "l2_recruit": 3,
        "l2_check": 4,
        "l2_checked": 5,
        "l3_recruit": 6,
        "l3_check": 7,
        "complete": 8
    }
};

var ImportStates = {
    DEFAULT: 0,
    PROGRESS: 1,
    DONE: 2
};


// --------------- Functions ---------------- //
function setImportLinks(project, importState)
{
    switch (importState) {
        case ImportStates.PROGRESS:
            $("."+project+"_import .import_done").removeClass("done").hide();
            $("."+project+"_import .import_progress").show();
            break;
        case ImportStates.DONE:
            $("."+project+"_import .import_done").addClass("done").show();
            $("."+project+"_import .import_progress").hide();
            break;
        default:
            $("."+project+"_import .import_done").removeClass("done").show();
            $("."+project+"_import .import_progress").hide();
    }
}

function setImportLinksUlb(ulbState, eventState) {
    switch (EventStates.states[ulbState]) {
        case EventStates.states.l2_recruit:
        case EventStates.states.l2_check:
            setImportLinks("l2", ImportStates.PROGRESS);
            break;
        case EventStates.states.l2_checked:
            setImportLinks("l2", ImportStates.DONE);
            break;

        case EventStates.states.l3_recruit:
        case EventStates.states.l3_check:
            setImportLinks("l2", ImportStates.DONE);
            setImportLinks("l3", ImportStates.PROGRESS);
            break;
        case EventStates.states.complete:
            setImportLinks("l2", ImportStates.DONE);
            setImportLinks("l3", ImportStates.DONE);
            break;
    }
}

function setImportComponent(event) {
    switch (EventStates.states[event.state]) {
        case EventStates.states.started:
        case EventStates.states.translating:
            if(["ulb","udb"].indexOf(event.bookProject) > -1)
            {
                $(".event_l_1").prop("checked", true);
                setImportLinks("l1", ImportStates.PROGRESS);
            }
            else
            {
                $(".event_l_2").prop("checked", true);
                setImportLinks(event.bookProject, ImportStates.PROGRESS);
            }
            break;
        case EventStates.states.translated:
            $(".event_l_1").prop("disabled", true);

            if(["ulb","udb"].indexOf(event.bookProject) > -1)
            {
                $(".event_l_2").prop("checked", true);
                setImportLinks("l1", ImportStates.DONE);
                $(".l2_import").hide();
            }
            else
            {
                $(".event_l_2").prop("disabled", true);
                $(".event_l_3").prop("checked", true);
                setImportLinks(event.bookProject, ImportStates.DONE);
            }
            $(".event_imports").show();
            break;

        case EventStates.states.l2_recruit:
        case EventStates.states.l2_check:
            $(".event_l_1").prop("disabled", true);
            $(".event_l_2").prop("checked", true);
            setImportLinks("l1", ImportStates.DONE);
            setImportLinks("l2", ImportStates.PROGRESS);
            $(".l1_import").show();
            $(".l2_import").hide();
            $(".event_imports").show();
            break;
        case EventStates.states.l2_checked:
            // Notes, Questions and Words don't run through these states
            $(".event_l_1").prop("disabled", true);
            $(".event_l_2").prop("disabled", true);
            $(".event_l_3").prop("checked", true);

            $(".l1_import").hide();
            $(".l2_import").show();
            setImportLinks("l1", ImportStates.DONE);
            setImportLinks("l2", ImportStates.DONE);

            $(".event_imports").show();
            break;

        case EventStates.states.l3_recruit:
        case EventStates.states.l3_check:
        case EventStates.states.complete:
            $(".event_l_1").prop("disabled", true);
            $(".event_l_2").prop("disabled", true);
            $(".event_l_3").prop("checked", true);

            if(["ulb","udb"].indexOf(event.bookProject) > -1)
            {
                $(".l1_import").hide();
                $(".l2_import").show();

                setImportLinks("l2", ImportStates.DONE);
            }
            else
            {
                setImportLinks(event.bookProject, ImportStates.DONE);
            }
            $(".event_imports").show();
            break;
    }
}

function setEventMenuLinks(event, level) {
    $("#initialLevel").val(level);

    switch (event.bookProject) {
        case "ulb":
        case "udb":
            $(".event_links_l1").show();
            $(".event_links_l1 .event_progress a").attr("href", "/events/information/"+event.eventID);
            $(".event_links_l1 .event_manage a").attr("href", "/events/manage/"+event.eventID);
            $(".event_links_l2").hide();
            $(".event_links_l3").hide();

            if(level > 1)
            {
                for(var i=2;i<=level;i++) {
                    $(".event_links_l" + i).show();
                    $(".event_links_l" + i + " .event_progress a")
                        .attr("href", "/events/information-l" + i + "/" + event.eventID);
                    $(".event_links_l" + i + " .event_manage a")
                        .attr("href", "/events/manage-l" + i + "/" + event.eventID);
                }
            }
            break;
        case "tn":
        case "tq":
        case "tw":
            $(".event_links_l1").hide();
            $(".event_links_l2").show();
            $(".event_links_l2 .event_progress a")
                .attr("href", "/events/information-" + event.bookProject + "/"+event.eventID);
            $(".event_links_l2 .event_manage a")
                .attr("href", "/events/manage/"+event.eventID);
            $(".event_links_l3").hide();

            if(level == 3)
            {
                $(".event_links_l3").show();
                $(".event_links_l3 .event_progress a")
                    .attr("href", "/events/information-" + event.bookProject + "-l3/"+event.eventID);
                $(".event_links_l3 .event_manage a")
                    .attr("href", "/events/manage-l3/"+event.eventID);
            }

            if(event.bookProject == "tw")
            {
                $(".event_links_l2 .event_manage a")
                    .attr("href", "/events/manage-tw/"+event.eventID);

                if(level == 3)
                    $(".event_links_l3 .event_manage a")
                        .attr("href", "/events/manage-tw-l3/"+event.eventID);
            }
            break;
        case "sun":
            $(".event_links_l1").hide();
            $(".event_links_l2").hide();
            $(".event_links_l3").show();

            $(".event_links_l3 .event_progress a").attr("href", "/events/information-sun/"+event.eventID);
            $(".event_links_l3 .event_manage a").attr("href", "/events/manage/"+event.eventID);
            break;
    }
}


function setEventMenu(event) {
    switch (EventStates.states[event.state]) {
        case EventStates.states.started:
        case EventStates.states.translating:
        case EventStates.states.translated:
            if(["ulb","udb"].indexOf(event.bookProject) > -1)
                setEventMenuLinks(event, 1);
            else if(["tn","tq","tw"].indexOf(event.bookProject) > -1)
                setEventMenuLinks(event, 2);
            else
                setEventMenuLinks(event, 3);
            break;

        case EventStates.states.l2_recruit:
        case EventStates.states.l2_check:
        case EventStates.states.l2_checked:
            setEventMenuLinks(event, 2);
            break;

        case EventStates.states.l3_recruit:
        case EventStates.states.l3_check:
        case EventStates.states.complete:
            setEventMenuLinks(event, 3);
            break;
    }
}

function setStartEventButton(event) {
    switch (EventStates.states[event.state]) {
        case EventStates.states.translated:
        case EventStates.states.l2_checked:
            $("button[name=startEvent]").text(Language.create);
            $("#eventAction").val("create");
            break;
        default:
            $("button[name=startEvent]").text(Language.save);
            $("#eventAction").val("edit");
    }
}
