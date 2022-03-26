/**
 * Created by Maxim on 05 Feb 2016.
 */
let importLocked = false;

$(function () {

    const body = $("body");

    if(typeof $.fn.chosen == "function")
        $("#subGwLangs, #targetLangs, "
            + "#sourceTranslation, #superadmins, "
            + "#sourceTranslationNotes, "
            + "#gwLang, #projectMode, "
            + "#src_language, #src_type, #src")
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
        const gwProject = $("#gwProject");
        $.ajax({
            url: gwProject.prop("action"),
            method: "post",
            data: gwProject.serialize(),
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

    $(".gwproj_edit").click(function () {
        const id = $(this).data("id");

        $.ajax({
            url: "/admin/rpc/get_super_admins",
            method: "post",
            data: {gwProjectID: id},
            dataType: "json",
            beforeSend: function() {
                $(this).prop("disabled", true);
                $("#superadmins").val('').trigger("chosen:updated");
            }
        }).done(function(data) {
            if(data.success)
            {
                let content = "";
                $.each(data.admins, function(k, v) {
                    content += '' +
                        '<option value="' + k + '" selected>' + v + '</option>';
                });
                $("#superadmins").prepend(content);

                $("#gwProjectID").val(id);
                $(".admins-content").css("left", 0);
            }
            else
            {
                renderPopup(data.error);
            }
        }).always(function() {
            $(this).prop("disabled", false);
            $("#superadmins").trigger("chosen:updated");
        });
    });


    $("#gwProjectAdmins").submit(function (e) {
        const gwProjectAdmins = $("#gwProjectAdmins");
        $.ajax({
            url: gwProjectAdmins.prop("action"),
            method: "post",
            data: gwProjectAdmins.serialize(),
            dataType: "json",
            beforeSend: function() {
                $(".gwProjectLoader").show();
            }
        })
            .done(function(data) {
                if(data.success)
                {
                    location.reload();
                }
                else
                {
                    renderPopup(data.error);
                }
            })
            .always(function() {
                $(".gwProjectLoader").hide();
            });

        e.preventDefault();
    });


    $("select[name=projectMode]").change(function() {
        $("#projectType").val('').trigger("chosen:updated");

        $(".toolsTn").removeClass("hidden");
        $(".toolsTq").removeClass("hidden");
        $(".toolsTw").removeClass("hidden");
        $("select[name=projectType] option").prop('disabled', false)
        $("select[name=projectType]").trigger("chosen:updated");

        if(["bible","odb"].indexOf($(this).val()) > -1)
        {
            $("#sourceTools").val('').trigger("chosen:updated");
            $(".sourceTools").addClass("hidden");
            $(".projectType").removeClass("hidden");
            $("select[name=projectType] option[value=mill]").prop('disabled', true)
            if(["odb"].indexOf($(this).val()) > -1)
            {
                $(".sourceTranslation").addClass("hidden");
                $("select[name=projectType] option[value=ulb]").prop('disabled', true)
            }
            else
            {
                $(".sourceTranslation").removeClass("hidden");
            }
        } else if(["fnd","bib","theo"].indexOf($(this).val()) > -1) {
            $(".toolsTn").addClass("hidden");
            $(".toolsTq").addClass("hidden");
            $(".toolsTw").addClass("hidden");
            $("select[name=projectType] option[value=odb]").prop('disabled', true)
            $("select[name=projectType] option[value=ulb]").prop('disabled', true)
        }

        $("select[name=projectType]").trigger("chosen:updated");
    });
    

    $("#crepr").click(function () {
        resetProjectForm();
        $(".sub-content").css("left", 0);
    });


    $(".editProject").click(function() {
        const projectID = $(this).data("projectid");
        resetProjectForm();

        $.ajax({
            url: "/admin/rpc/get_project",
            method: "post",
            data: {projectID: projectID},
            dataType: "json",
            beforeSend: function() {
                $(".editProject").prop("disabled", true);
            }
        })
            .done(function(data) {
                if(data.success)
                {
                    $("button[name=project]").text(Language.save);
                    $("#projectAction").val("edit");

                    setProjectForm(data);

                    $(".sub-content").css("left", 0);
                }
                else
                {
                    if(typeof data.error != "undefined")
                    {
                        if(data.error === "login" || data.error === "admin")
                            window.location.href = "/members/login";
                        else
                        {
                            renderPopup(data.error);
                        }
                    }
                }
            })
            .always(function() {
                $(".editProject").prop("disabled", false);
            });
    });

    // Get list of target languages for selected gateway language
    $("#subGwLangs").change(function() {
        let tlOptions = "<option value=''></option>";

        if($(this).val() === "") {
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
                        '['+v.langID+'] '+v.langName+(v.angName !== "" && v.langName !== v.angName ? ' ( '+v.angName+' )' : '')+
                    '</option>';
                });
                $("#targetLangs").html(tlOptions);
                $("#project select").trigger("chosen:updated");
                document.dispatchEvent(new Event("target-langs-updated"));
            })
            .always(function() {
                $(".subGwLoader").hide();
            });
    });

    // Submit project form
    $("#project").submit(function(e) {
        const project = $("#project");
        $.ajax({
                url: project.prop("action"),
                method: "post",
                data: project.serialize(),
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
    if($().ajaxChosen)
    {
        $("#adminsSelect, #superadmins").ajaxChosen({
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
                const terms = {};

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
        $(".event-content").css("left", 0);

        resetEventForm();

        const bookCode = $(this).data("bookcode");
        const bookName = $(this).data("bookname");
        const chaptersNum = $(this).data("chapternum");
        const bookProject = $("#bookProject").val();

        if(bookProject === "ulb") {
            $(".language_input_checkbox").show();
        }

        $(".bookName").text(bookName);
        $("#bookCode").val(bookCode);

        $(".book_info_content").html(
            '(<strong>'+Language.chaptersNum+':</strong> '+chaptersNum+')'
        );
    });


    // Submit event form
    $("#startEvent").submit(function(e) {
        const startEvent = $("#startEvent");
        $.ajax({
                url: startEvent.prop("action"),
                method: "post",
                data: startEvent.serialize(),
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

        const bookCode = $(this).data("bookcode");
        const eventID = $(this).data("eventid");
        const abbrID = $(this).data("abbrid");
        // const bookProject = $("#bookProject").val();

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
                    $("input[name=langInput]")
                        .prop("checked", data.event.langInput === "1")
                        .prop("disabled", true);

                    setImportComponent(data.event);
                    setEventMenu(data.event);
                    setStartEventButton(data.event);

                    // Set the status of ulb translation
                    setImportLinksUlb(data);

                    $(".bookName").text(data.event.name);
                    $(".book_info_content").html(
                        '(<strong>'+Language.chaptersNum+':</strong> '+data.event.chaptersNum+')'
                    );

                    const admins = data["admins"];
                    let content = "";
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
                        if(data.error === "login" || data.error === "admin")
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
        const ul = $(".event_menu ul");
        if (ul.is(":visible"))
            ul.hide();
        else
            ul.show();

        e.stopPropagation();
    });

    $(document).click(function(e) {
        if(!e.target.classList.contains("option_group"))
            $(".event_menu ul").hide();
    });

    $(".event_menu .clearCache").click(function () {
        const abbrID = $("#abbrID").val();
        const bookCode = $("#bookCode").val();
        const sourceLangID = $("#sourceLangID").val();
        const sourceBible = $("#sourceBible").val();

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

    $("button[name=deleteEvent]").click(function (e) {
        const bookName = $(".bookName").text();
        const delName = $("#delevnt").val();

        if($(".delinput").is(":visible") && bookName === delName)
            $("#eventAction").val("delete");
        else
            e.preventDefault();
    });

    $("button[name=updateAllCache]").click(function (e) {
        const $this = $(this);
        const sourceLangID = $this.data("sourcelangid");
        const sourceBible = $this.data("sourcebible");

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
        const level = $("input[name=eventLevel]:checked").val();
        const initialLevel = $("#initialLevel").val();
        const bookProject = $("#bookProject").val();

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

        switch (level) {
            case "1":
                $(".event_imports").hide();
                $(".language_input_checkbox").slideDown(200);
                break;
            case "2":
                if(["ulb"].indexOf(bookProject) > -1)
                {
                    $(".l1_import").show();
                    $(".l2_import").hide();
                    $(".event_imports").hide().slideDown(200);
                    $(".language_input_checkbox").hide();
                }
                else
                    $(".event_imports").hide();
                break;
            case "3":
                if(["ulb"].indexOf(bookProject) > -1)
                {
                    $(".l1_import").hide();
                    $(".l2_import").show();
                    $(".language_input_checkbox").hide();
                }
                $(".event_imports").hide().slideDown(200);
                break;
        }
    });

    $(".import_link").click(function (e) {
        const source = $(this).data("source");
        // const bookProject = $("#bookProject").val();

        switch (source) {
            case "l1":
            case "l2":
            case "l3":
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

    $(".wacs_import_menu ul li:last-child").click(function () {
        if(importLocked) return false;
        $(".wacs_import_menu_content").css("left", -9000);
        $(".import_menu_content").css("left", 0);
    });

    $(".import_menu label").click(function () {
        return !importLocked;
    });

    $("#wacs_form").submit(function (e) {
        e.preventDefault();
        return false;
    });

    $(".import_menu input[name=import]").change(function () {
        if(importLocked) return false;

        const input = $(this);
        const form = $(this).parents("form");
        let formData = null;
        const projectID = $("#projectID").val();
        const eventID = $("#eID").val();
        const bookCode = $("#bookCode").val();
        const bookProject = $("#bookProject").val();
        const importLevel = $("#importLevel").val();
        const importProject = $("#importProject").val();

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
                    if(typeof response.warning != "undefined")
                        renderPopup(response.message + " " + "(with warning: We couldn't define the related scripture. Contact administrator.)");

                    $(".import_done", $(".import.l"+importLevel+"_import")).addClass("done");

                    $(".import_menu_content").css("left", -9000);
                    $(".event-content").css("left", 0);
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
        const type = $(this).data("type");
        if(type === "wacs")
        {
            $(".wacs_list tbody").html("");
            $("input[name=wacs_repo_name]").val("");
            $(".import_menu_content").css("left", -9000);
            $(".wacs_import_menu_content").css("left", 0);
        }
    });


    let wacs_timeout = null;
    body.on("keyup", "input[name=wacs_repo_name]", function () {
        if(importLocked) return false;

        const q = $(this).val();

        clearTimeout(wacs_timeout);
        wacs_timeout = setTimeout(function() {
            $.ajax({
                url: "/admin/rpc/repos_search/" + q,
                method: "get",
                dataType: "json",
                beforeSend: function() {
                    $(".importLoader").show();
                }
            })
                .done(function(response) {
                    $(".wacs_list tbody").html("");
                    if(response.data.length > 0)
                    {
                        $.each(response.data, function (i, v) {
                            const ts = Date.parse(v.updated_at);
                            const date = new Date(ts);

                            let list = "<tr data-url='"+ v.clone_url +"'>";
                            list += "<td>"+ v.owner.login +"</td>";
                            list += "<td>" + v.name +"</td>";
                            list += "<td>" + date.toLocaleString() +"</td>";
                            list += "</tr>";
                            $(".wacs_list tbody").append(list);
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

    body.on("click", ".wacs_list tr", function() {
        if(importLocked) return false;

        const repo_url = $(this).data("url");
        const projectID = $("#projectID").val();
        const eventID = $("#eID").val();
        const bookCode = $("#bookCode").val();
        const bookProject = $("#bookProject").val();
        const importLevel = $("#importLevel").val();
        const importProject = $("#importProject").val();

        $.ajax({
            url: "/admin/rpc/import",
            method: "post",
            data: {
                import: repo_url,
                type: "wacs",
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
        const eventID = $(this).data("eventid");
        const level = $(this).data("level");
        const mode = $(this).data("mode");

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
                    let html = "";

                    // Render facilitators
                    html += "<div class='admins_list'>" +
                        "<div class='contrib_title'>"+Language.facilitators+":</div>";
                    html += "<table class='table table-bordered table-hover' role='grid'>" +
                        "<tr>"+
                            "<th>First Name</th>"+
                            "<th>Last Name</th>"+
                            "<th>UserName</th>"+
                            "<th>Role</th>"+
                            "<th>Email</th>"+
                            "<th>Sign Up Date</th>"+
                            "<th>CC by SA</th>"+
                            "<th>SoF</th>"+
                            "<th>Date Signed</th>"+
                        "</tr>";

                    $.each(data.admins, function (i,v) {
                        let formated = "---";
                        if(v.signup !== "---") {
                            const ts = Date.parse(v.signup);
                            const date = new Date(ts);
                            formated = date.toLocaleDateString();
                        }

                        html += "<tr>" +
                            "<td>"+v.fname+"</td>"+
                            "<td>"+v.lname+"</td>"+
                            "<td><a href='/members/profile/"+i+"'>"+v.uname+"</a></td>"+
                            "<td>"+v.role+"</td>"+
                            "<td>"+v.email+"</td>"+
                            "<td>"+formated+"</td>"+
                            "<td>"+v.tou+"</td>"+
                            "<td>"+v.sof+"</td>"+
                            "<td>"+formated+"</td>"+
                        "</tr>";
                    });
                    html += "</table>";

                    // Render translators
                    if(Object.keys(data.translators).length > 0)
                    {
                        html += "<div class='translators_list'>" +
                            "<div class='contrib_title'>"+Language.translators+":</div>";
                        html += "<table class='table table-bordered table-hover' role='grid'>" +
                            "<tr>"+
                                "<th>First Name</th>"+
                                "<th>Last Name</th>"+
                                "<th>UserName</th>"+
                                "<th>Role</th>"+
                                "<th>Email</th>"+
                                "<th>Sign Up Date</th>"+
                                "<th>CC by SA</th>"+
                                "<th>SoF</th>"+
                                "<th>Date Signed</th>"+
                            "</tr>";
                        $.each(data.translators, function (i,v) {
                            let formated = "---";
                            if(v.signup !== "---") {
                                const ts = Date.parse(v.signup);
                                const date = new Date(ts);
                                formated = date.toLocaleDateString();
                            }

                            html += "<tr>" +
                                "<td>"+v.fname+"</td>"+
                                "<td>"+v.lname+"</td>"+
                                "<td><a href='/members/profile/"+i+"'>"+v.uname+"</a></td>"+
                                "<td>"+v.role+"</td>"+
                                "<td>"+v.email+"</td>"+
                                "<td>"+formated+"</td>"+
                                "<td>"+v.tou+"</td>"+
                                "<td>"+v.sof+"</td>"+
                                "<td>"+formated+"</td>"+
                            "</tr>";
                        });
                        html += "</table>";
                    }

                    // Render checkers
                    html += "<div class='checkers_list'>" +
                        "<div class='contrib_title'>"+Language.checkers+":</div>";
                    html += "<table class='table table-bordered table-hover' role='grid'>" +
                        "<tr>"+
                            "<th>First Name</th>"+
                            "<th>Last Name</th>"+
                            "<th>UserName</th>"+
                            "<th>Role</th>"+
                            "<th>Email</th>"+
                            "<th>Sign Up Date</th>"+
                            "<th>CC by SA</th>"+
                            "<th>SoF</th>"+
                            "<th>Date Signed</th>"+
                        "</tr>";
                    $.each(data.checkers, function (i,v) {
                        let formated = "---";
                        if(v.signup !== "---") {
                            const ts = Date.parse(v.signup);
                            const date = new Date(ts);
                            formated = date.toLocaleDateString();
                        }

                        html += "<tr>" +
                            "<td>"+v.fname+"</td>"+
                            "<td>"+v.lname+"</td>"+
                            "<td><a href='/members/profile/"+i+"'>"+v.uname+"</a></td>"+
                            "<td>"+v.role+"</td>"+
                            "<td>"+v.email+"</td>"+
                            "<td>"+formated+"</td>"+
                            "<td>"+v.tou+"</td>"+
                            "<td>"+v.sof+"</td>"+
                            "<td>"+formated+"</td>"+
                        "</tr>";
                    });
                    html += "</table>";

                    $(".contributors_content").html(html);
                    $(".contributors_container").css("left", 0);
                }
                else
                {
                    if(typeof data.error != "undefined")
                    {
                        if(data.error === "login" || data.error === "admin")
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

    // Show project contributors
    $(".showAllContibutors").click(function () {
        const projectID = $(this).data("projectid");

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
                    let html = "<table class='table table-bordered table-hover' role='grid'>"+
                        "<tr>"+
                            "<th>First Name</th>"+
                            "<th>Last Name</th>"+
                            "<th>UserName</th>"+
                            "<th>Role</th>"+
                            "<th>Email</th>"+
                            "<th>Sign Up Date</th>"+
                            "<th>CC by SA</th>"+
                            "<th>SoF</th>"+
                            "<th>Date Signed</th>"+
                        "</tr>";

                    $.each(data.contributors, function () {
                        let formated = "---";
                        if(this.signup !== "---") {
                            const ts = Date.parse(this.signup);
                            const date = new Date(ts);
                            formated = date.toLocaleDateString();
                        }

                        html += "<tr>"+
                                    "<td>"+this.fname+"</td>"+
                                    "<td>"+this.lname+"</td>"+
                                    "<td>"+this.uname+"</td>"+
                                    "<td>"+this.role+"</td>"+
                                    "<td>"+this.email+"</td>"+
                                    "<td>"+formated+"</td>"+
                                    "<td>"+this.tou+"</td>"+
                                    "<td>"+this.sof+"</td>"+
                                    "<td>"+formated+"</td>"+
                                "</tr>";
                    });

                    html += "</table>";

                    $(".contributors_title").hide();
                    $(".contributors_title.proj").show();
                    $(".contributors_content").html(html);
                    $(".contributors_container").css("left", 0);
                }
                else
                {
                    if(typeof data.error != "undefined")
                    {
                        if(data.error === "login" || data.error === "admin")
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

        const memberID = $(this).attr("data");
        const parent = $(this).parents("tr");
        const activated = $(".activateMember", parent).is(":checked");

        let msg = "";
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
    body.on("click", ".blockMember", function (e) {
        e.preventDefault();

        const $this = $(this);
        const memberID = $this.attr("data");

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
        const id = $(this).attr("id");

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
        const button = $(this);
        button.prop("disabled", true);
        const filterPage = $(".filter_page");
        filterPage.val(1);

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
                        filterPage.val(1);

                        // if it has more results to show draw "more" button
                        if(data.members.length < parseInt(data.count))
                        {
                            if($("#search_more").length <= 0)
                            {
                                $('<div id="search_more"></div>').appendTo("#all_members_content")
                                    .text(Language.searchMore);
                            }
                            filterPage.val(2);
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
                        let row = "<tr>" +
                            "<td><a href='/members/profile/"+v.memberID+"'>"+v.userName+"</a></td>" +
                            "<td>"+v.firstName+" "+v.lastName+"</td>" +
                            "<td>"+v.email+"</td>" +
                            "<td>"+(v.projects ? JSON.parse(v.projects).map(function (proj) {
                                return Language[proj];
                            }).join(", ") : "")+"</td>" +
                            "<td>"+(v.proj_lang ? "["+v.langID+"] "+v.langName +
                                (v.angName !== "" && v.angName !== v.langName ? " ("+v.angName+")" : "") : "")+"</td>" +
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

    body.on("click", "#search_more", function () {
        const button = $(this);

        if(button.hasClass("disabled")) return false;

        button.addClass("disabled");
        const page = parseInt($(".filter_page").val());

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
                            let row = "<tr>" +
                                "<td><a href='/members/profile/"+v.memberID+"'>"+v.userName+"</a></td>" +
                                "<td>"+v.firstName+" "+v.lastName+"</td>" +
                                "<td>"+v.email+"</td>" +
                                "<td>"+(v.projects ? JSON.parse(v.projects).map(function (proj) {
                                    return Language[proj];
                                }).join(", ") : "")+"</td>" +
                                "<td>"+(v.proj_lang ? "["+v.langID+"] "+v.langName +
                                    (v.angName !== "" && v.angName !== v.langName ? " ("+v.angName+")" : "") : "")+"</td>" +
                                "<td><input type='checkbox' "+(parseInt(v.complete) ? "checked" : "")+" disabled></td>" +
                                "<td class=\"block_btn\"><button class='blockMember btn "+(v.blocked == 1 ? "btn-primary" : "btn-danger")+"' data='"+v.memberID+"'>" +
                                (v.blocked == 1 ? Language.unblock : Language.block)+"</button></td>" +
                                "</tr>";
                            $("#all_members_table tbody").append(row);
                        });

                        const results = parseInt($("#all_members_table tbody tr").length);
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
        const downloadAgain = $(".update_langs .download_again").is(":checked");
        $.ajax({
            url: "/admin/rpc/update_languages",
            method: "post",
            data: { download: downloadAgain ? 1 : 0 },
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

    // Update source catalog
    $(".update_catalog button").click(function () {
        const downloadAgain = $(".update_catalog .download_again").is(":checked");
        $.ajax({
            url: "/admin/rpc/update_catalog",
            method: "post",
            data: { download: downloadAgain ? 1 : 0 },
            dataType: "json",
            beforeSend: function() {
                $(".update_catalog img").show();
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
                $(".update_catalog img").hide();
            });
    });

    // Clear all cache
    $(".clear_cache button").click(function () {
        $.ajax({
            url: "/admin/rpc/clear_all_cache",
            method: "post",
            dataType: "json",
            beforeSend: function() {
                $(".clear_cache img").show();
            }
        })
            .done(function(data) {
                if(data.success)
                {
                    renderPopup("Entire cache has been cleared!");
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
                $(".clear_cache img").hide();
            });
    });

    // Create multiple users
    $(".create_users button").click(function () {
        const amount = $(".create_users #amount").val();
        const langs = $(".create_users #langs").val();
        const password = $(".create_users #password").val();

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
    body.on("click", ".tools_delete_word", function (e) {
        const li = $(this).parent("li");
        const word = li.attr("id");

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
    body.on("click", ".sail_create .add_word", function (e) {
        const word = $("#sailword").val();
        const symbol = $("#sailsymbol").val();

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


    // Manage FAQ

    // Delete question
    body.on("click", ".tools_delete_faq", function (e) {
        const li = $(this).parent("li");
        const questionID = li.attr("id");

        renderConfirmPopup(Language.attention, Language.delQuestion, function () {
            $( this ).dialog( "close" );
            $.ajax({
                url: "/admin/rpc/delete_faq",
                method: "post",
                data: {id: questionID},
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

    // Create Question
    body.on("click", ".faq_create .create_faq", function (e) {
        const question = $("#faq_question").val();
        const answer = $("#faq_answer").val();
        const category = $("#faq_category").val();

        $.ajax({
            url: "/admin/rpc/create_faq",
            method: "post",
            data: {
                question: question,
                answer: answer,
                category: category
            },
            dataType: "json",
            beforeSend: function() {
                $("#faq_create_loader").show();
            }
        })
            .done(function(data) {
                if(data.success)
                {
                    $("#faq_question").val("");
                    $("#faq_answer").val("");
                    $("#faq_category").val("");
                    $(".faq_list.tools ul").prepend(data.li);
                    $('#faq_answer').summernote('reset');
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
                $("#faq_create_loader").hide();
            });

        e.preventDefault();
        return false;
    });


    // Create News
    $(".create_news button").click(function (e) {
        const title = $("#title").val();
        const category = $("#category").val();
        const text = $("#text").val();

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

    // Upload SUN font
    $(".sun_font_tools button").click(function (e) {
        const formData = new FormData();
        formData.append("file", $('#sun_upload')[0].files[0]);

        $.ajax({
            url: "/admin/rpc/upload_sun_font",
            method: "post",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            beforeSend: function() {
                $(".sun_font_tools img").show();
            }
        })
            .done(function(data) {
                if(data.success)
                {
                    renderPopup(data.message);
                    $('#sun_upload').val("");
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
                $(".sun_font_tools img").hide();
            });

        e.preventDefault();
        return false;
    });

    // Upload SUN dictionary (.csv)
    $(".saildict_upload button").click(function (e) {
        const formData = new FormData();
        formData.append("file", $('#saildic_upload')[0].files[0]);

        $.ajax({
            url: "/admin/rpc/upload_sun_dict",
            method: "post",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            beforeSend: function() {
                $(".saildict_upload img").show();
            }
        })
            .done(function(data) {
                if(data.success)
                {
                    renderPopup(data.message);
                    $('#saildic_upload').val("");
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
                $(".saildict_upload img").hide();
            });

        e.preventDefault();
        return false;
    });

    $(".event_column.progress").each(function () {
        const $this = $(this);
        const eventID = $this.data("eventid");

        if(typeof eventID == "string" || eventID == "") return;

        $.ajax({
            url: "/admin/rpc/get_event_progress/" + eventID,
            method: "get",
            dataType: "json",
            beforeSend: function() {
                $(".progressLoader", $this).show();
            }
        })
            .done(function(data) {
                if(data.success)
                {
                    if(data.progress > 0) {
                        $this.removeClass("zero");
                        $(".progress-bar", $this).attr("aria-valuenow", data.progress);
                        $(".progress-bar", $this).css("width", data.progress + "%");
                        $(".progress-bar", $this).text(Math.floor(data.progress) + "%");
                    }
                }
                else
                {
                    console.log("unavailable");
                }
            })
            .always(function() {
                $(".progressLoader", $this).hide();
            });
    });

    // Upload image for FAQ
    $('#faq_answer').summernote({
        dialogsInBody: true,
        height: 300,
        minHeight: null,
        maxHeight: null,
        shortCuts: false,
        disableDragAndDrop: false,
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['Insert', ['video', 'picture', 'link', 'table', 'hr']],
            ['Other', ['codeview', 'undo', 'redo', 'fullscreen']]
        ],
        callbacks: {
            onImageUpload: function(image) {
                uploadImageContent(image[0], $(this));
            }
        }
    });

    $(".members_download_csv").on("click", function() {
        const csv = exportTableToCSV($("#all_books_content")[0], "\t");
        downloadCSV(csv, "report.csv");
    });

    $(".contribs_download_csv").on("click", function() {
        const csv = exportTableToCSV($(".contributors_content")[0], "\t");
        downloadCSV(csv, "contributors.csv");
    });

    $(".add_custom_src").click(function () {
        $(".custom_src_type").toggle(200);
    });

    $(".custom_src_type button").on("click", function () {
        const srcSlug = $(".custom_src_type #src_slug").val();
        const srcName = $(".custom_src_type #src_name").val();

        if(srcSlug.trim() !== "" && srcName.trim() !== "") {
            if(!/\|/g.test(srcSlug) && !/\|/g.test(srcName)) {
                const newOption = '<option value="'+srcSlug+'|'+srcName+'" selected>' +
                    '['+srcSlug+'] ' + srcName +
                    '</option>';
                $("#src_type").append(newOption);
                $("#src_type").val(srcSlug + "|" + srcName).trigger("chosen:updated");

                $(".custom_src_type #src_slug").val("");
                $(".custom_src_type #src_name").val("")
                $(".custom_src_type").slideUp(200);
            } else {
                renderPopup("Please don't use '|' character");
            }
        }
    });

    $(".src_create").click(function () {
        const srcLang = $("#src_language").val();
        const srcType = $("#src_type").val();

        if(srcLang.trim() !== "" && srcType.trim() !== "") {
            $.ajax({
                url: "/admin/rpc/create_custom_src",
                method: "post",
                dataType: "json",
                data: {
                    lang: srcLang,
                    type: srcType
                },
                beforeSend: function() {
                    $(".src_loader").show();
                }
            })
                .done(function(data) {
                    if(data.success)
                    {
                        if(typeof data.message != "undefined") {
                            renderPopup(data.message, function () {
                                location.reload();
                            });
                        } else {
                            location.reload();
                        }
                    }
                    else
                    {
                        if(typeof data.error != "undefined") {
                            renderPopup(data.error);
                        }
                    }
                })
                .always(function() {
                    $(".src_loader").hide();
                });
        }
    });

    // Upload source
    $("button.src_upload").click(function (e) {
        const formData = new FormData();
        formData.append("file", $('#src_upload')[0].files[0]);
        formData.append("src", $("#src").val());

        $.ajax({
            url: "/admin/rpc/upload_source",
            method: "post",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            beforeSend: function() {
                $(".source_upload img").show();
            }
        })
            .done(function(data) {
                if(data.success)
                {
                    renderPopup(data.message);
                    $("#src").val("").trigger("chosen:updated");
                    $('#src_upload').val("");
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
                $(".source_upload img").hide();
            });

        e.preventDefault();
        return false;
    });
});


// --------------- Variables ---------------- //
const EventStates = {
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

const ImportStates = {
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

function setImportLinksUlb(data) {
    if(typeof data.ulb != "undefined")
    {
        switch (EventStates.states[data.ulb.state]) {
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
}

function setImportComponent(event) {
    const eventL1 = $(".event_l_1");
    const eventL2 = $(".event_l_2");
    const eventL3 = $(".event_l_3");

    const importL1 = $(".l1_import");
    const importL2 = $(".l2_import");

    const eventImports = $(".event_imports");
    const liCheckbox = $(".language_input_checkbox");

    switch (EventStates.states[event.state]) {
        case EventStates.states.started:
        case EventStates.states.translating:
            if(["ulb"].indexOf(event.bookProject) > -1)
            {
                eventL1.prop("checked", true);
                setImportLinks("l1", ImportStates.PROGRESS);
                $(".language_input_checkbox").hide();
            }
            else
            {
                eventL2.prop("checked", true);
                setImportLinks(event.bookProject, ImportStates.PROGRESS);
                setImportLinks(event.bookProject+"_l1", ImportStates.PROGRESS);
                setImportLinks(event.bookProject+"_l2", ImportStates.PROGRESS);
                $(".language_input_checkbox").hide();
            }
            break;
        case EventStates.states.translated:
            eventL1.prop("disabled", true);

            if(["ulb"].indexOf(event.bookProject) > -1)
            {
                eventL2.prop("checked", true);
                setImportLinks("l1", ImportStates.DONE);
                importL2.hide();
                $(".language_input_checkbox").hide();
            }
            else
            {
                eventL2.prop("disabled", true);
                eventL3.prop("checked", true);
                setImportLinks(event.bookProject, ImportStates.DONE);
                setImportLinks(event.bookProject+"_l1", ImportStates.DONE);
                setImportLinks(event.bookProject+"_l2", ImportStates.DONE);
                $("."+event.bookProject+"_l1_import").hide();
                $("."+event.bookProject+"_l2_import").show();
            }
            eventImports.show();
            liCheckbox.hide();
            break;

        case EventStates.states.l2_recruit:
        case EventStates.states.l2_check:
            eventL1.prop("disabled", true);
            eventL2.prop("checked", true);
            setImportLinks("l1", ImportStates.DONE);
            setImportLinks("l2", ImportStates.PROGRESS);
            importL1.show();
            importL2.hide();
            eventImports.show();
            liCheckbox.hide();
            break;
        case EventStates.states.l2_checked:
            eventL1.prop("disabled", true);
            eventL2.prop("disabled", true);
            eventL3.prop("checked", true);

            importL1.hide();
            importL2.show();
            setImportLinks("l1", ImportStates.DONE);
            setImportLinks("l2", ImportStates.DONE);

            eventImports.show();
            liCheckbox.hide();
            break;

        case EventStates.states.l3_recruit:
        case EventStates.states.l3_check:
        case EventStates.states.complete:
            eventL1.prop("disabled", true);
            eventL2.prop("disabled", true);
            eventL3.prop("checked", true);

            if(["ulb"].indexOf(event.bookProject) > -1)
            {
                importL1.hide();
                importL2.show();
                setImportLinks("l2", ImportStates.DONE);
                $(".language_input_checkbox").hide();
            }
            else
            {
                setImportLinks(event.bookProject, ImportStates.DONE);
                setImportLinks(event.bookProject+"_l1", ImportStates.DONE);
                setImportLinks(event.bookProject+"_l2", ImportStates.DONE);
                $("."+event.bookProject+"_l1_import").hide();
                $("."+event.bookProject+"_l2_import").show();
            }
            eventImports.show();
            liCheckbox.hide();
            break;
    }
}

function setEventMenuLinks(event, level) {
    $("#initialLevel").val(level);

    switch (event.bookProject) {
        case "ulb":
            $(".event_links_l1").show();
            $(".event_links_l1 .event_progress a").attr("href", "/events/information/"+event.eventID);
            $(".event_links_l1 .event_manage a").attr("href", "/events/manage/"+event.eventID);
            $(".event_links_l2").hide();
            $(".event_links_l3").hide();

            if(level > 1)
            {
                for(let i=2;i<=level;i++) {
                    $(".event_links_l" + i).show();
                    $(".event_links_l" + i + " .event_progress a")
                        .attr("href", "/events/information-l" + i + "/" + event.eventID);
                    $(".event_links_l" + i + " .event_manage a")
                        .attr("href", "/events/manage-l" + i + "/" + event.eventID);
                }
            }
            break;
        case "sun":
            $(".event_links_l1").hide();
            $(".event_links_l2").hide();
            $(".event_links_l3").show();

            $(".event_links_l3 .event_progress a")
                .attr("href", "/events/information"+(event.category === "odb" ? "-odb" : "")+"-sun/"+event.eventID);
            $(".event_links_l3 .event_manage a")
                .attr("href", "/events/manage/"+event.eventID);
            break;
        case "odb":
        case "fnd":
        case "bib":
        case "theo":
            $(".event_links_l1").hide();
            $(".event_links_l2").hide();
            $(".event_links_l3").show();

            $(".event_links_l3 .event_progress a")
                .attr("href", "/events/information-"+event.bookProject+"/"+event.eventID);
            $(".event_links_l3 .event_manage a")
                .attr("href", "/events/manage/"+event.eventID);
            break;
    }
}


function setEventMenu(event) {
    switch (EventStates.states[event.state]) {
        case EventStates.states.started:
        case EventStates.states.translating:
        case EventStates.states.translated:
            if(["ulb"].indexOf(event.bookProject) > -1)
                setEventMenuLinks(event, 1);
            // setEventMenuLinks(event, 2);
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

function resetProjectForm() {
    $("#project").trigger("reset");
    $("#projectID").val("");
    $("#projectMode").prop("disabled", false).trigger("chosen:updated");
    $("#subGwLangs").prop("disabled", false).trigger("chosen:updated");
    $("#targetLangs").prop("disabled", false).trigger("chosen:updated");
    $("#projectType").prop("disabled", false).trigger("chosen:updated");
    $("#sourceTranslation").val("").trigger("chosen:updated");
    $(".sourceTools").addClass("hidden");
    $(".sourceTranslation").removeClass("hidden");
    $(".projectType").removeClass("hidden");
    $(".subErrors").html("");
    $("#project select").val('').chosen();
    $("button[name=project]").text(Language.create);
    $("#projectAction").val("create");
    $(".toolsTn").removeClass("hidden");
    $("#toolsTn").val("en").trigger("chosen:updated");
    $(".toolsTq").removeClass("hidden");
    $("#toolsTq").val("en").trigger("chosen:updated");
    $(".toolsTw").removeClass("hidden");
    $("#toolsTw").val("en").trigger("chosen:updated");
}

function setProjectForm(data) {
    $("#projectID").val(data.project.projectID);

    let mode;
    let projectType = data.project.bookProject;
    if(["ulb","sun"].indexOf(data.project.bookProject) > -1) {
        if(data.project.sourceBible === "odb") {
            mode = data.project.sourceBible;
        } else {
            mode = "bible";
        }
    } else {
        mode = data.project.bookProject;
    }

    if (["fnd","bib","theo"].indexOf(mode) > -1) {
        $(".sourceTranslation").addClass("hidden")
        projectType = "mill";
    }

    $("#projectMode")
        .val(mode)
        .trigger("chosen:updated")
        .trigger("change")
        .prop("disabled", true);

    $("#subGwLangs")
        .val(data.project.gwLang + "|" + data.project.gwProjectID)
        .trigger("chosen:updated")
        .trigger("change")
        .prop("disabled", true);

    $("#targetLangs").prop("disabled", true);
    document.addEventListener("target-langs-updated", function() {
        $("#targetLangs")
            .val(data.project.targetLang)
            .trigger("chosen:updated");
    });

    $("#sourceTranslation")
        .val(data.project.sourceBible + "|" + data.project.sourceLangID)
        .trigger("chosen:updated");

    $("#projectType")
        .val(projectType)
        .trigger("chosen:updated")
        .prop("disabled", true);
    $("#toolsTn").val(data.project.tnLangID).trigger("chosen:updated");
    $("#toolsTq").val(data.project.tqLangID).trigger("chosen:updated");
    $("#toolsTw").val(data.project.twLangID).trigger("chosen:updated");
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

    $(".import.l2_import").hide();
    $(".import.l3_import").hide();

    $("#eventAction").val("create");
    $("button[name=startEvent]").text(Language.create);
}

function uploadImageContent(image, editor) {
    const data = new FormData();
    data.append("image", image);
    $.ajax({
        url: "/admin/rpc/upload_image",
        cache: false,
        contentType: false,
        processData: false,
        data: data,
        dataType: "json",
        type: "post",
        success: function(data) {
            console.log(data);
            if(data.success) {
                if(data.ext === "pdf") {
                    $(editor).summernote('createLink', {
                        text: "Set link name",
                        url: data.url,
                        isNewWindow: true
                    });
                } else {
                    const image = $("<img>").attr("src", data.url);
                    $(editor).summernote("insertNode", image[0]);
                }
            } else {
                renderPopup(data.error);
            }
        },
        error: function(data) {
            console.log(data);
        }
    });
}
