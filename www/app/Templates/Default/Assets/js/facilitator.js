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