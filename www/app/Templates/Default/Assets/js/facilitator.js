/**
 * Created by Maxim on 05 Feb 2016.
 */

// ------------------ Jquery Events --------------------- //

$(function () {

    let searchTimeout;

    $(".panel-close").click(function() {
        $(this).parents(".form-panel").css("left", "-9999px");
    });

    // Show assign chapter dialog
    $(".add_person_chapter").click(function() {
        const chapter = $(this).attr("data");

        $(".chapter_members_div .panel-title span").text(chapter);
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
        const chapter = $(".chapter_members_div .panel-title span").text();

        const data = {};
        data.eventID = $("#eventID").val();
        data.chapter = chapter;
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

        window.location.reload();
    });

    $("#user_translator").keyup(function () {
        const $this = $(this);
        clearTimeout(searchTimeout);

        searchTimeout = setTimeout(function () {
            const name = $this.val();
            if(name.trim() === "")
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
                            const exist = $(".assign_chapter[data="+this.memberID+"]");
                            if(exist.length > 0) return true;
                            if(this.blocked == "1") return true;

                            const li = '<li>' +
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
        const $this = $(this);
        const memberID = $(this).attr("data");
        const eventID = $("#eventID").val();

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
                    renderPopup(data.error + (typeof data.errors != "undefined"
                        ? " ("+Object.keys(data.errors).join(", ")+")"
                        : ""));
                }
            })
            .always(function () {
                $(".openMembersSearch.dialog_f").hide();
            });
    });

    $(document).on("click", ".member_chapters span b", function() {
        const chapter = $(this).text();
        $(".chapter_"+chapter+" .uname_delete").trigger("click");
    });


    // Remove chapter from translator's chapter list
    $(document).on("click", ".uname_delete", function() {
        const parent = $(this).parents(".manage_chapters_user");
        const $this = $(this);

        renderConfirmPopup(Language.deleteChapterConfirmTitle, Language.deleteChapterConfirm, function () {
            $( this ).dialog( "close" );

            const data = {};
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

            const memberID = $this.parents(".member_usname").attr("data");
            const eventID = $("#eventID").val();

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

                        let mNum = parseInt($(".manage_members h3 span").text()); // number of current members
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
    /*setInterval(function() {
        getNewMembersList();
    }, 300000);*/

    function getNewMembersList() {
        const eventID = $("#eventID").val();
        const ids = [];

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
                    const newUsers = [];
                    $.each(data.members, function(index, value) {
                        const hiddenListLi = '<li>'+
                            '   <div class="member_usname userlist chapter_ver">'+
                            '       <div class="divname">'+value.name+'</div>'+
                            '       <div class="divvalue">(<span>0</span>)</div>'+
                            '   </div>'+
                            '   <button class="btn btn-success assign_chapter" data="'+value.memberID+'">'+Language.assign+'</button>'+
                            '   <div class="clear"></div>'+
                            '</li>';
                        $(".chapter_members_div ul").append(hiddenListLi);

                        const shownListLi = '<li>'+
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
                        let mNum = parseInt($(".manage_members h3 span").text()); // number of current members
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

    // Show info tip
    $(".create_info_tip a").click(function () {
        renderPopup($(".create_info_tip span").text());
        return false;
    });

    // Members search
    // Submit Filter form
    $(".filter_apply button").click(function () {
        const button = $(this);
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
                        const row = "<tr>" +
                            "<td><a href='/members/profile/"+v.memberID+"'>"+v.userName+"</a></td>" +
                            "<td>"+v.firstName+" "+v.lastName+"</td>" +
                            "<td>"+v.email+"</td>" +
                            "<td>"+(v.projects ? JSON.parse(v.projects).map(function (proj) {
                                return Language[proj];
                            }).join(", ") : "")+"</td>" +
                            "<td>"+(v.proj_lang ? "["+v.langID+"] "+v.langName +
                                (v.angName !== "" && v.angName !== v.langName ? " ("+v.angName+")" : "") : "")+"</td>" +
                            "<td><input type='checkbox' "+(parseInt(v.complete) ? "checked" : "")+" disabled></td>" +
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
        const button = $(this);

        if(button.hasClass("disabled")) return false;

        button.addClass("disabled");
        const page = parseInt($(".filter_page").val());

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
                            const row = "<tr>" +
                                "<td><a href='/members/profile/"+v.memberID+"'>"+v.userName+"</a></td>" +
                                "<td>"+v.firstName+" "+v.lastName+"</td>" +
                                "<td>"+v.email+"</td>" +
                                "<td>"+(v.projects ? JSON.parse(v.projects).map(function (proj) {
                                    return Language[proj];
                                }).join(", ") : "")+"</td>" +
                                "<td>"+(v.proj_lang ? "["+v.langID+"] "+v.langName +
                                    (v.angName !== "" && v.angName !== v.langName ? " ("+v.angName+")" : "") : "")+"</td>" +
                                "<td><input type='checkbox' "+(parseInt(v.complete) ? "checked" : "")+" disabled></td>" +
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

    // Moving transators step back
    $(".step_selector").change(function () {
        const eventID = $(this).data("event");
        const memberID = $(this).data("member");
        const mode = $(this).data("mode");
        const chk = $(this).data("chk");
        let to_step = $(this).val();

        const prev_chunk = /_prev$/.test(to_step);
        to_step = to_step.replace(/_prev$/, "");

        const $this = $(this);

        if(to_step === EventSteps.PEER_REVIEW
            || to_step === EventSteps.KEYWORD_CHECK
            || to_step === EventSteps.CONTENT_REVIEW)
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
            const confirm = to_step !== EventSteps.CHUNKING;
            moveStepBack($this, eventID, memberID, to_step, confirm, prev_chunk, chk);
        }
    });

    $(".remove_checker").click(function() {
        const event_member = $(this).attr("data").split(":");
        const eventID = event_member[0];
        const memberID = event_member[1];
        const to_step = $(this).attr("data2");
        const chk = $(this).attr("data3");
        
        moveStepBack(null, eventID, memberID, to_step, true, false, chk);
    });

    $(".remove_checker_alt").click(function() {
        const id = $(this).attr("id");
        const eventID = $("#eventID").val();
        const memberID = $(this).parent().data("member");
        const chapter = $(this).parent().data("chapter");
        const name = "<span class='l2_checker_name'>"+$(this).data("name")+"</span>";
        const message = Language.remove_l2_checker + name;

        renderConfirmPopup(Language.attention, message, function() {
            const other_check = $(".other_check_remove input:checked").val();

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
        const mMode = typeof manageMode != "undefined" ? manageMode : "l1";

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
                const chapterBlock = $(".chapter_"+data.chapter);
                if(action === "add")
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
                const memberBlock = $(".manage_members .member_usname[data="+data.memberID+"]").parent("li");
                let arr = $(".member_chapters span", memberBlock).text().split(", ");
                let currentChapNum = parseInt($(".member_usname span", memberBlock).text());

                if(action === "add")
                {
                    if(arr[0] === "")
                        arr[0] = data.chapter;
                    else
                        arr.push(data.chapter);

                    arr.sort(function(a, b){return a-b});

                    currentChapNum++;
                }
                else
                {
                    arr = $.grep(arr, function( a ) {
                        return a != data.chapter;
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