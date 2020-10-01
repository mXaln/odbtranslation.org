var socket, sctUrl = 'https://odbtranslation.srv:8001';

$(document).ready(function () {
    socket = io.connect(sctUrl);

    socket.on('connect', OnConnected);
    socket.on('reconnect', OnConnected);
    socket.on('chat message', OnChatMessage);
    socket.on('event room update', OnEventRoomUpdate);
    socket.on('project room update', OnProjectRoomUpdate);
    socket.on('system message', OnSystemMessage);
    socket.on('checking request', OnCheckingRequest);

    $("#chat_container").chat({
        step: step,
        memberID: memberID,
        eventID: eventID,
        chkMemberID: chkMemberID,
        disableChat: disableChat,
        isAdmin: isAdmin,
        onSendMessage: function()
        {
            socket.emit('chat message', this);
        }
    });
});

function OnConnected()
{
    $("#chat_container").chat("clearMessages");

    var data = {
        memberID: memberID,
        eventID: eventID,
        projectID: projectID,
        aT: aT,
        step: step,
        chkMemberID: chkMemberID,
    };
    this.emit("new member", data);
}

function OnChatMessage(data)
{
    $("#chat_container").chat("newMessageArrived", data);
}

function OnEventRoomUpdate(roomMates)
{
    var membersObj = $(".member_item");
    if(membersObj.length > 0)
    {
        $(".online_indicator", membersObj).removeClass("online");
        $(".online_status", membersObj).hide();
        $(".offline_status", membersObj).show();
    }

    for(var rm in roomMates)
    {
        var memberObj = $(".member_item[data="+roomMates[rm].memberID+"]");
        if(memberObj.length > 0)
        {
            $(".online_indicator", memberObj).addClass("online");
            $(".online_status", memberObj).show();
            $(".offline_status", memberObj).hide();
        }
    }

    $("#chat_container").chat("updateChatMembers", roomMates);
}

function OnProjectRoomUpdate(roomMates)
{
    var membersObj = $(".member_item");
    if(membersObj.length > 0)
    {
        // $(".online_indicator", membersObj).removeClass("online");
        // $(".online_status", membersObj).hide();
        // $(".offline_status", membersObj).show();
    }

    for(var rm in roomMates)
    {
        var memberObj = $(".member_item[data="+roomMates[rm].memberID+"]");
        if(memberObj.length > 0)
        {
            // $(".online_indicator", memberObj).addClass("online");
            // $(".online_status", memberObj).show();
            // $(".offline_status", memberObj).hide();
        }
    }

    // $("#chat_container").chat("updateChatMembers", roomMates);
}

function OnSystemMessage(data)
{
    var msg = "";
    switch (data.type)
    {
        case "logout":
            window.location = "/members/logout";
            break;

        case "memberConnected":
            var t_mode = typeof tMode != "undefined" ? tMode : "";
            var data = {
                eventID: eventID,
                step: step,
                chkMemberID: chkMemberID,
                isChecker: isChecker,
                tMode: t_mode};
            this.emit('step enter', data);
            break;

        case "peerEnter":
            msg = Language.partnerJoinedPeerEdit;
        case "checkEnter":
            if(chkMemberID == 0 || $(".checker_waits").length > 0)
            {
                chkMemberID = parseInt(data.memberID);
                $(".check_request").remove();
                $(".checker_name_span").text(data.userName);
                $(".chk_title").text(data.userName);

                socket.io.reconnect();
                
				if(step != "")
                {
                    msg = Language.checkerJoined;
                    $(".alert.alert-danger, .alert.alert-success").remove();
                    renderPopup(msg);
                }

                $("#chat_container").chat("options", {chkMemberID: chkMemberID});
                $("#chat_container").chat("update");
            }
            break;

        case "prvtMsgs":
            $("#chat_container").chat("updatePrivateMessages", data);
            break;

        case "evntMsgs":
            $("#chat_container").chat("updateEventMessages", data);
            break;

        case "projMsgs":
            $("#chat_container").chat("updateProjectMessages", data);
            break;

        case "checkDone":
            if(typeof isChecker != "undefined" && isChecker) return;
            if(typeof isInfoPage != "undefined") return;

            $(".alert.alert-danger, .alert.alert-success").remove();
            renderPopup(Language.checkerApproved);
            break;

        case "comment":
            var editors = $(".editComment[data='"+data.verse+"']");
            data.text = unEscapeStr(data.text);

            if(editors.length > 0)
            {
                $.each(editors, function () {
                    var editor = $(this);
                    var numText = editor.prev(".comments_number").text();
                    var num = numText.trim() != "" ? parseInt(numText) : 0;
                    var wasDeleted = data.text.trim() == "";

                    if(data.memberID == memberID)
                    {
                        var myComment = $(".my_comment", editor.next(".comments"));
                        if(myComment.length > 0)
                        { // Remove or update comment
                            if(wasDeleted)
                            {
                                myComment.remove();
                                num--;
                                num = num > 0 ? num : "";
                                editor.prev(".comments_number").text(num);
                                if(num <= 0) editor.prev(".comments_number").removeClass("hasComment");

                                editor.css("color", "black");
                            }
                            else
                            {
                                myComment.text(data.text);
                            }
                        }
                        else
                        { // Add comment
                            if(wasDeleted) return;

                            editor.next(".comments").prepend(
                                "<div class='my_comment' data='"+data.verse+"'>"+data.text+"</div>"
                            );
                            num++;
                            editor.prev(".comments_number").text(num);
                            if(num == 1) editor.prev(".comments_number").addClass("hasComment");

                            editor.css("color", "#a52f20");
                        }
                    }
                    else
                    {
                        var commentor = $(".other_comments span:contains('"+data.name+" - L"+data.level+"')", editor.next(".comments"));
                        if(commentor.length > 0)
                        { // Remove or update comment
                            if(wasDeleted)
                            {
                                commentor.parent().remove();
                                num--;
                                num = num > 0 ? num : "";
                                editor.prev(".comments_number").text(num);
                                if(num <= 0) editor.prev(".comments_number").removeClass("hasComment");
                            }
                            else
                            {
                                commentor.parent().html("<span>"+data.name+" - L"+data.level+": </span>"+data.text);
                            }
                        }
                        else
                        { // Add comment
                            editor.next(".comments").append(
                                "<div class='other_comments'><span>"+data.name+" - L"+data.level+": </span>"+data.text+"</div>"
                            );
                            num++;
                            editor.prev(".comments_number").text(num);
                            if(num == 1) editor.prev(".comments_number").addClass("hasComment");
                        }
                    }
                });
            }
            break;

        case "keyword":
            if(typeof isInfoPage == "undefined" &&
                (typeof step != "undefined"
                    && (step == EventSteps.KEYWORD_CHECK || EventCheckSteps.PEER_REVIEW_L2)))
                highlightKeyword(data.verseID, data.text, data.index, data.remove == "true");
            break;

        case "chkStarted":
            var notif = $("a.notifa[data='"+data.id+"']");
            if(notif.length > 0)
            {
                notif.remove();
                var count = parseInt($(".notif_count").text());
                count--;
                $(".notif_count").text(Math.max(count, 0));

                if(count <= 0)
                {
                    $(".notif_count").remove();
                    $(".notif_block").html('<div class="no_notif">'+Language.noNotifsMsg+'</div>');
                }
            }
            break;
    }
}

function OnCheckingRequest(data)
{
    if($.inArray(memberID.toString(), data.excludes) >= 0)
        return false;

    $.ajax({
        url: "/events/rpc/get_notifications",
        method: "post",
        dataType: "json",
    })
    .done(function(data) {
        if(data.success)
        {
            //$(".notif_block").html("");
            if(data.notifs.length > 0)
            {
                $(".notif_block .no_notif").remove();
                $(".notif_count").remove();
                $("#notifications").append('<span class="notif_count">'+data.notifs.length+'</span>');
                var notifs = "";
                $.each(data.notifs, function(i, note) {
                    if($("a[href='"+note.link+"']").length <= 0)
                        notifs += '<a href="'+note.link+'" class="notifa" data="'+note.anchor+'">'+
                                '<li class="'+note.step+'_checker">'+note.text+'</li>'+
                            '</a>';
                });
                $(".notif_block").prepend(notifs);
                var notifSound = document.getElementById('notif');
                notifSound.play();
            }
            else
            {
                $(".notif_count").remove();
                $(".notif_block").html('<div class="no_notif">'+data.noNotifs+'</div>');
            }
        }
    });
}
