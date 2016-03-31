var newEvntMsgsShown = false;
var newp2pMsgsShown = false;

$(function () {

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

    // Show/Hide chat window
    $("#chat_hide").click(function() {
        if($("#chat_container").hasClass("open"))
        {
            $("#chat_container").removeClass("open")
                .addClass("closed");
            $("#chat_container").animate({right: "-610px"}, 500, function() {
                $("#chat_hide").removeClass("glyphicon-remove")
                    .addClass("glyphicon-chevron-left");

                $("#p2p").addClass("active");
                $("#evnt").removeClass("active");
                $("#chat_type").val("p2p");

                $("#p2p_messages").hide();
                $("#evnt_messages").hide();
            });
        }
        else
        {
            $("#chat_container").removeClass("closed")
                .addClass("open");
            $("#chat_container").animate({right: 0}, 500, function() {
                $("#chat_hide").removeClass("glyphicon-chevron-left")
                    .addClass("glyphicon-remove");

                $("#p2p_messages").show();

                var newmsgs = $(".newmsgs", $("#p2p_messages"));
                if(!newp2pMsgsShown && newmsgs.length > 0) {
                    console.log($("#p2p_messages")[0].scrollTop);
                    $("#p2p_messages").animate({scrollTop: newmsgs.offset().top - $("#p2p_messages").offset().top + $("#p2p_messages").scrollTop()}, 200, function() {
                        console.log($("#p2p_messages")[0].scrollTop);
                    });
                    newp2pMsgsShown = true;
                }
                else
                {
                    $("#p2p_messages").animate({scrollTop: $("#p2p_messages")[0].scrollHeight}, 200);
                }
            });
        }
    });

    // Change chat room tabs
    $(".chat_tab").click(function() {
        var id = $(this).prop("id");

        if(id == "p2p")
        {
            $(this).addClass("active");
            $("#evnt").removeClass("active");
            $("#chat_type").val("p2p");
            $("#p2p_messages").show();
            $("#evnt_messages").hide();
        }
        else
        {
            $(this).addClass("active");
            $("#p2p").removeClass("active");
            $("#chat_type").val("evnt");
            $("#evnt_messages").show();
            $("#p2p_messages").hide();

            var newmsgs = $(".newmsgs", $("#evnt_messages"));

            if(!newEvntMsgsShown && newmsgs.length > 0) {
                $("#evnt_messages").animate({scrollTop: newmsgs.offset().top - $("#evnt_messages").offset().top + $("#evnt_messages").scrollTop()}, 200);

                newEvntMsgsShown = true;
            }
            else
            {
                $("#evnt_messages").animate({scrollTop: $("#evnt_messages")[0].scrollHeight}, 200);
            }
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

    $("textarea").elastic();
    $("#chat_type").val("p2p");
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