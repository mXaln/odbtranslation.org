/**
 * Created by Maxim on 05 Feb 2016.
 */

// ------------------ Jquery Events --------------------- //

$(function () {

    $("#cregwpr").click(function () {
        $("#gwProject").trigger("reset");
        $(".errors").html("");
        $(".main-content").show();
    });

    $(document).on("click", ".admins-delete", function() {
        $(this).parent().remove();

        CorrectAdminsTextareaLength(false);
    });

    $("#admins").keydown(function (e) {
        if(e.keyCode == 8)
        {
            if($(this).val() != "") {
                GetAdminsList();
                return;
            }

            if($(".admins-div").length == 0) return;

            $(".admins-div:last").remove();
            CorrectAdminsTextareaLength(false);
            return;
        }

        GetAdminsList();

        //$(".admins-list").show();
    });
    
    $(":not(.admins-list ul li)").click(function () {
        $(".admins-list").hide();
    });

    $(document).on("click", ".admins-list ul li", function () {
        var content = '' +
            '<div class="admins-div">' +
                '<span class="admins-span">' + $(this).text() + '</span>' +
                '<span class="admins-delete glyphicon glyphicon-remove"></span>' +
                '<input name="admins[]" value="' + $(this).text() + '" type="hidden" />' +
            '</div>';

        if($(".admins-div").length > 0)
        {
            $(".admins-div:last").after(content);
        }
        else
        {
            $(".admins-area").prepend(content);
        }

        CorrectAdminsTextareaLength(true);
    });

    $(".panel-close").click(function() {
        $(this).parents(".form-panel").hide();
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
                    alert(data.success);
                    location.reload();
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


    // Sub Event Form

    $("#crepr").click(function () {
        $("#project").trigger("reset");
        $(".subErrors").html("");
        $(".sub-content").show();
    });

    $("#subGwLangs").change(function() {
        var tlOptions = "<option>-- Choose Target Language --</option>";

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
                if(data.success)
                {
                    alert(data.success);
                    location.reload();
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
        var sourceLangID = $("#sourceLangID").val();
        var bookProject = $("#bookProject").val();

        $("#startEvent").trigger("reset");
        $(".errors").html("");
        $(".bookName").text(bookName);
        $("#bookCode").val(bookCode);
        $(".event-content").show();

        // Get source text
        $.ajax({
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
                    alert(data.success);
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
});


// --------------- Variables ---------------- //

var autoCompleteTimerId;
var autoCompleteTime = 5;



// --------------- Functions ---------------- //


function CorrectAdminsTextareaLength(add)
{
    if(add)
    {
        $("#admins").width($("#admins").width() - 106);
        $("#admins").val("");

        if($("#admins").width() < 58)
        {
            $("#admins").width(478);
        }
    }
    else
    {
        if((($(".admins-div").length + 1) % 4) == 0)
        {
            $("#admins").width($("#admins").width() - 106*3);
            return;
        }

        $("#admins").width($("#admins").width() + 106);
    }

    $("#admins").focus();
}

function GetAdminsList()
{
    clearTimeout(autoCompleteTimerId);
    autoCompleteTimerId = setTimeout(function() {
        if($("#admins").val() == "") return;

        $(".admins-list").hide();

        $.ajax({
            url: "/admin/rpc/get_members",
            method: "post",
            data: {search: $("#admins").val()},
            dataType: "json",
            beforeSend: function() {
                $(".adminsEvLoader").show();
            }
        })
            .done(function(data) {
                if(data.length <= 0) return;

                var list = "";

                $.each(data, function(i, v) {
                    list += '<li>' + v.userName + '</li>';
                })

                $(".admins-list ul").html(list);
                $(".admins-list").show();
            })
            .always(function() {
                $(".adminsEvLoader").hide();
            });
    }, 500);
}