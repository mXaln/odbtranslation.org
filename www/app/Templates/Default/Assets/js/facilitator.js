/**
 * Created by Maxim on 05 Feb 2016.
 */

// ------------------ Jquery Events --------------------- //

$(function () {

    $(".panel-close").click(function() {
        $(this).parents(".form-panel").css("left", "-9999px");
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

    // Assign chapter to translator/checker
    $(document).on("click", ".assign_chapter", function() {
        var data = {};
        data.eventID = $("#eventID").val();
        data.chapter = $(".chapter_members_div .panel-title span").text();
        data.memberID = $(this).attr("data");
        data.memberName = $(this).prev(".member_usname").children(".divname").text();
        data.manageMode = typeof manageMode != "undefined" ? manageMode : "l1";

        assignChapter(data, "add");
    });

    // Show "add translator/checker" dialog
    $("#openMembersSearch").click(function() {
        $(".user_translators").html("");
        $("#user_translator").val("");

        $(".members_search_dialog").show();

        $('html, body').css({
            'overflow': 'hidden',
            'height': '100%'
        });
    });


    // Close "add translator/checker" dialog
    $(".members-search-dialog-close").click(function() {
        $(".user_translators").html("");
        $("#user_translator").val("");

        $(".members_search_dialog").hide();

        $('html, body').css({
            'overflow': 'auto',
            'height': 'auto'
        });
    });

    var searchTimeout;
    $("#user_translator").keyup(function (event) {
        $this = $(this);
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function () {
            var name = $this.val();
            if(name.trim() == "")
            {
                $(".user_translators").html("");
                return;
            }

            $.ajax({
                url: "/members/search",
                method: "post",
                data: {
                    name: name,
                    ext: true,
                    verified: true
                },
                dataType: "json",
                beforeSend: function() {
                    $(".openMembersSearch.dialog_f").show();
                }
            })
                .done(function(data) {
                    $(".user_translators").html("");
                    if(data.success)
                    {
                        $.each(data.members, function () {
                            var exist = $(".assign_chapter[data="+this.memberID+"]");
                            if(exist.length > 0) return true;
                            if(this.blocked == "1") return true;

                            var li = '<li>' +
                                        '<label>' +
                                            '<div class="tr_member">'+ this.firstName + ' ' + this.lastName +' ('+this.userName+')</div>' +
                                            '<div class="form-group tr_member_add">' +
                                                '<button class="btn btn-primary add_translator" data="'+this.memberID+'">'+Language.add+'</button>' +
                                            '</div>' +
                                        '</label>' +
                                    '</li>';

                            $(".user_translators").append(li);
                        });
                    }
                    else
                    {
                        debug(data.error);
                    }
                })
                .always(function () {
                    $(".openMembersSearch.dialog_f").hide();
                });
        }, 500);
    });

    $(document).on("click", ".add_translator", function () {
        var $this = $(this);
        var memberID = $(this).attr("data");
        var eventID = $("#eventID").val();

        $.ajax({
            url: "/events/rpc/apply_event",
            method: "post",
            data: {
                memberID: memberID,
                eventID: eventID,
                userType: userType
            },
            dataType: "json",
            beforeSend: function() {
                $(".openMembersSearch.dialog_f").show();
            }
        })
            .done(function(data) {
                if(data.success)
                {
                    $this.parents("li").remove();
                    getNewMembersList();
                }
                else
                {
                    renderPopup(data.error);
                }
            })
            .always(function () {
                $(".openMembersSearch.dialog_f").hide();
            });
    });

    $(document).on("click", ".member_chapters span b", function() {
        var chapter = $(this).text();
        $(".chapter_"+chapter+" .uname_delete").trigger("click");
    });


    // Show "Create words group" dialog
    $("#word_group_create").click(function() {
        $("#word_group").val("");

        $(".words_group_dialog").show();

        $('html, body').css({
            'overflow': 'hidden',
            'height': '100%'
        });
    });

    // Close "Create words group" dialog
    $(".words-group-dialog-close").click(function() {
        $(".words_group_dialog").hide();

        $('html, body').css({
            'overflow': 'auto',
            'height': 'auto'
        });
    });

    // Create a group of translation words
    $(document).on("click", "#create_group", function () {

        var group = $("#word_group").val();
        var eventID = $("#eventID").val();

        $.ajax({
            url: "/events/rpc/create_words_group",
            method: "post",
            data: {
                group: group,
                eventID: eventID
            },
            dataType: "json",
            beforeSend: function() {
                $(".openWordsGroup.dialog_f").show();
            }
        })
            .done(function(data) {
                if(data.success)
                {
                    $(".words_group_dialog").hide();
                    window.location.reload();
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
                $(".openWordsGroup.dialog_f").hide();
            });
    });

    // Delete a group of translation words
    $(document).on("click", ".group_delete", function () {
        var groupID = $(this).data("groupid");
        var eventID = $("#eventID").val();
        var $this = $(this);

        renderConfirmPopup(Language.deleteGroupConfirmTitle, Language.deleteGroupConfirm, function () {
            $( this ).dialog( "close" );

            $.ajax({
                url: "/events/rpc/delete_words_group",
                method: "post",
                data: {
                    groupID: groupID,
                    eventID: eventID
                },
                dataType: "json",
                beforeSend: function() {
                    $this.css("background-color", "#f00");
                }
            })
                .done(function(data) {
                    if(data.success)
                    {
                        $(".words_group_dialog").hide();
                        window.location.reload();
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
                    $this.css("background-color", "#666666");
                });
        }, function () {
            $( this ).dialog( "close" );
        });
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
            data.manageMode = typeof manageMode != "undefined" ? manageMode : "l1";

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
                data: {eventID: eventID, memberID: memberID, manageMode: manageMode},
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
        getNewMembersList();
    }, 300000);

    function getNewMembersList() {
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
            data: {eventID: eventID, memberIDs: ids, manageMode: manageMode},
            dataType: "json"
        })
            .done(function(data) {
                if(data.success)
                {
                    var newUsers = [];
                    $.each(data.members, function(index, value) {
                        var hiddenListLi = '<li>'+
                            '   <div class="member_usname userlist chapter_ver">'+
                            '       <div class="divname">'+value.name+'</div>'+
                            '       <div class="divvalue">(<span>0</span>)</div>'+
                            '   </div>'+
                            '   <button class="btn btn-success assign_chapter" data="'+value.memberID+'">'+Language.assign+'</button>'+
                            '   <div class="clear"></div>'+
                            '</li>';
                        $(".chapter_members_div ul").append(hiddenListLi);

                        var shownListLi = '<li>'+
                            '   <div class="member_usname" data="'+value.memberID+'">'+
                            value.name+' (<span>0</span>)'+
                            '   <div class="glyphicon glyphicon-remove delete_user" title="'+Language.removeFromEvent+'"></div>'+
                            '   </div>'+
                            '   <div class="member_chapters">'+
                            Language.chapters+': <span></span>'+
                            '   </div>'+
                            '</li>';
                        $(".manage_members ul").append(shownListLi);

                        newUsers.push(value.name);
                    });

                    if(newUsers.length > 0)
                    {
                        var mNum = parseInt($(".manage_members h3 span").text()); // number of current members
                        mNum += newUsers.length;
                        $(".manage_members h3 span").text(mNum);

                        //renderPopup(Language.newUsersApplyed+": "+newUsers.join(", "));
                    }
                }
                else
                {
                    console.log(data.error);
                }
            });
    }

    /*$("#startTranslation").click(function (e) {
        var $this = $(this);

        renderConfirmPopup(Language.startTranslation, Language.startTranslationConfirm, function () {
            $(this).dialog( "close" );
            $this.data("yes", true).click();
        }, function () {
            $(this).dialog("close");
        });

        if(typeof $this.data("yes") == "undefined")
            e.preventDefault();
    });*/

    // Show info tip
    $(".create_info_tip a").click(function () {
        renderPopup($(".create_info_tip span").text());
        return false;
    });

    // Members search
    // Submit Filter form
    $(".filter_apply button").click(function () {
        var button = $(this);
        button.prop("disabled", true);
        $(".filter_page").val(1);

        if(/\/admin\/members/.test(window.location.pathname))
            return false;

        $.ajax({
            url: "/members/search",
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
                            "<td>"+(v.prefered_roles != "" && v.prefered_roles != null
                                ? JSON.parse(v.prefered_roles).map(function(role) {
                                return " "+Language[role];
                            })
                                : "<span style='color: #f00'>"+Language.emptyProfileError)+"</span></td>" +
                            "<td><input type='checkbox' "+(parseInt(v.isAdmin) ? "checked" : "")+" disabled></td>" +
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

    // Show more search members results
    $(document).on("click", "#search_more", function () {
        if(typeof isSuperAdmin != "undefined") return false;
        var button = $(this);

        if(button.hasClass("disabled")) return false;

        button.addClass("disabled");
        var page = parseInt($(".filter_page").val());

        $.ajax({
            url: "/members/search",
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
                                "<td>"+(v.prefered_roles != "" && v.prefered_roles != null
                                    ? JSON.parse(v.prefered_roles).map(function(role) {
                                    return " "+Language[role];
                                })
                                    : "<span style='color: #f00'>"+Language.emptyProfileError)+"</span></td>" +
                                "<td><input type='checkbox' "+(parseInt(v.isAdmin) ? "checked" : "")+" disabled></td>" +
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

    // Moving transators step back
    $(".step_selector").change(function () {
        var eventID = $(this).data("event");
        var memberID = $(this).data("member");
        var mode = $(this).data("mode");
        var chk = $(this).data("chk");
        var to_step = $(this).val();

        var prev_chunk = /_prev$/.test(to_step);
        to_step = to_step.replace(/_prev$/, "");

        var $this = $(this);

        if(to_step == EventSteps.PEER_REVIEW
            || to_step == EventSteps.KEYWORD_CHECK || to_step == EventSteps.CONTENT_REVIEW)
        {
            renderConfirmPopup(Language.attention, Language.removeCheckerConfirm,
                function () {
                    moveStepBack($this, eventID, memberID, to_step, true, false, chk);
                    $( this ).dialog( "close" );
                },
                function () {
                    moveStepBack($this, eventID, memberID, to_step, false, false, chk);
                    $( this ).dialog( "close" );
                },
                function () {
                    $('option', $this).each(function () {
                        if (this.defaultSelected) {
                            this.selected = true;
                            return false;
                        }
                    });
                }
            );
        }
        else
        {
            var confirm = true;
            if($.inArray(mode, ["tn"]) > -1)
            {
                confirm = to_step != EventSteps.CONSUME;
            }
            else
            {
                confirm = to_step != EventSteps.CHUNKING;
            }
            moveStepBack($this, eventID, memberID, to_step, confirm, prev_chunk, chk);
        }
    });

    $(".remove_checker").click(function() {
        var event_member = $(this).attr("data").split(":");
        var eventID = event_member[0];
        var memberID = event_member[1];
        var to_step = $(this).attr("data2");
        var chk = $(this).attr("data3");
        
        moveStepBack(null, eventID, memberID, to_step, true, false, chk);
    });

    $(".remove_checker_alt").click(function() {
        var id = $(this).attr("id");
        var eventID = $("#eventID").val();
        var memberID = $(this).parent().data("member");
        var chapter = $(this).parent().data("chapter");
        var name = "<span class='l2_checker_name'>"+$(this).data("name")+"</span>";
        var message = Language.remove_l2_checker + name;

        if(id == "other_checker")
        {
            var level = $(this).data("level");
            var prev_level = level - 1;
            var disabled = prev_level < 0 || prev_level > 4 ? "disabled" : "";
            var prev_step = "n/a";

            switch (prev_level) {
                case 0:
                    prev_step = Language.tn_other_pray;
                    break;
                case 1:
                    prev_step = Language.tn_other_consume;
                    break;
                case 2:
                    prev_step = Language.tn_other_highlight;
                    break;
                case 3:
                    prev_step = Language.tn_other_self_check;
                    break;
                case 4:
                    prev_step = Language.tn_other_keyword_check;
                    break;
            }

            var html = "" +
                "<div class='other_check_remove'>" +
                    "<h5>" + Language.remove_other_checker_opt + name + "</h5>" +
                    "<label>" +
                        "<input type='radio' name='other_option' value='remove' checked /> " +
                            Language.remove_checker + "</label>" +
                    "<label class='" + disabled + "'>" +
                        "<input type='radio' name='other_option' value='move_back' " + disabled + " /> " +
                            Language.move_to_step + prev_step + "</label>" +
                "</div>";
            message = html;
        }

        renderConfirmPopup(Language.attention, message, function() {
            var other_check = $(".other_check_remove input:checked").val();

            $.ajax({
                url: "/events/rpc/move_step_back_alt",
                method: "post",
                data: {
                    eventID : eventID,
                    memberID : memberID,
                    chapter: chapter,
                    mode: id,
                    otherChk: other_check
                },
                dataType: "json",
                beforeSend: function() {
                    //$(".filter_loader").show();
                }
            })
                .done(function(data) {
                    if(data.success)
                    {
                        window.location.reload();
                    }
                    else
                    {
                        if(typeof data.error != "undefined")
                        {
                            renderPopup(data.error,
                                function () {
                                    $( this ).dialog( "close" );
                                },
                                function () {
                                    window.location.reload();
                                });
                        }
                    }
                })
                .always(function() {
                    //$(".filter_loader").hide();
                });
        }, function () {
            $( this ).dialog( "close" );
        }, function () {
            $( this ).dialog( "close" );
        });
    });

    function moveStepBack(selector, eventID, memberID, to_step, confirm, prev_chunk, chk) {
        confirm = confirm || false;
        prev_chunk = prev_chunk || false;
        var mMode = typeof manageMode != "undefined" ? manageMode : "l1";

        $.ajax({
            url: "/events/rpc/move_step_back",
            method: "post",
            data: {
                eventID : eventID,
                memberID : memberID,
                to_step: to_step,
                confirm: confirm,
                prev_chunk: prev_chunk,
                chk: chk,
                manageMode: mMode
            },
            dataType: "json",
            beforeSend: function() {
                //$(".filter_loader").show();
            }
        })
            .done(function(data) {
                if(data.success)
                {
                    window.location.reload();
                }
                else
                {
                    if(typeof data.error != "undefined")
                    {
                        renderPopup(data.error,
                            function () {
                                $( this ).dialog( "close" );
                            },
                            function () {
                                window.location.reload();
                            });
                    }

                    if(typeof data.confirm != "undefined")
                    {
                        renderConfirmPopup(Language.attention, data.message,
                            function () {
                                moveStepBack(selector, eventID, memberID, to_step, true, false, chk);
                                $( this ).dialog( "close" );
                            },
                            function () {
                                $( this ).dialog( "close" );
                            },
                            function () {
                                $('option', selector).each(function () {
                                    if (this.defaultSelected) {
                                        this.selected = true;
                                        return false;
                                    }
                                });
                            });
                    }
                }
            })
            .always(function() {
                //$(".filter_loader").hide();
            });
    }

    // Set checker role for tN project
    $(".is_checker_input").click(function(e) {
        e.preventDefault();

        var $this = $(this);
        var parent = $(this).parents(".member_usname");
        var memberID = parent.attr("data");
        var eventID = $("#eventID").val();

        $.ajax({
            url: "/events/rpc/set_tn_checker",
            method: "post",
            data: {
                eventID : eventID,
                memberID : memberID,
            },
            dataType: "json",
            beforeSend: function() {
                $this.prop("disabled", true);
            }
        })
            .done(function(data) {
                if(data.success)
                {
                    $this.prop("checked", !$this.prop("checked"));
                }
                else
                {
                    if(typeof data.error != "undefined")
                    {
                        renderPopup(data.error,
                            function () {
                                $( this ).dialog( "close" );
                            },
                            function () {
                                window.location.reload();
                            });
                    }
                }
            })
            .always(function() {
                $this.prop("disabled", false);
            });
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
            manageMode: manageMode,
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