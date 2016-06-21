var socket, sctUrl = 'http://v-mast.com:8001';

$(document).ready(function () {
    socket = io.connect(sctUrl);

    socket.on('connect', OnConnected);
    socket.on('reconnect', OnConnected);
    socket.on('chat message', OnChatMessage);
    socket.on('room update', OnRoomUpdate);
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

function OnRoomUpdate(roomMates)
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

function OnSystemMessage(data)
{
    switch (data.type)
    {
        case "logout":
            window.location = "/members/logout";
            break;

        case "memberConnected":
            var data = {eventID: eventID, step: step, chkMemberID: chkMemberID, isChecker: isChecker};
            this.emit('step enter', data);
            break;

        case "discussEnter":
            if($(".discuss_not_ready").length > 0)
            {
                $(".alert.alert-danger, .alert.alert-success").remove();
                alert("Your partner has joined verbalize step");
            }
            break;

        case "peerEnter":
            if($(".cotr_not_ready").length > 0)
            {
                $(".alert.alert-danger").remove();
                $.ajax({
                        url: "/events/rpc/get_partner_translation",
                        method: "post",
                        dataType: "html",
                        data: {eventID: eventID}
                    })
                    .done(function(data) {
                        $(".cotrData").html(data);
                        $('[data-toggle="tooltip"]').tooltip();
                        alert("Your partner has joined peer review step");
                    });
            }
            break;

        case "checkEnter":
            if(chkMemberID == 0 || $(".checker_waits").length > 0)
            {
                $(".check_request").remove();
                chkMemberID = parseInt(data.memberID);
                $("#chat_container").chat("options", {chkMemberID: chkMemberID});
                $(".checker_name_span").text(data.userName);
                //this.disconnect();
                //this.connect();
                socket.io.reconnect();
                
				if(step != "")
					alert("A checker has joined");
            }
            break;

        case "prvtMsgs":
            $("#chat_container").chat("updatePrivateMessages", data);
            break;

        case "evntMsgs":
            $("#chat_container").chat("updateEventMessages", data);
            break;

        case "checkDone":
            alert("Checker has approved your translation!");
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
                    if($("a[data='"+note.anchor+"']").length <= 0)
                        notifs += '<a href="'+note.link+'" data="'+note.anchor+'" target="_blank"><li>'+note.text+'</li></a>';
                });
                $(".notif_block").prepend(notifs);
            }
            else
            {
                $(".notif_count").remove();
                $(".notif_block").html('<div class="no_notif">'+data.noNotifs+'</div>');
            }
        }
    });
}