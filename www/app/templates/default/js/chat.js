var isActive;
var hasP2pNewmsgs = {val: false};
var hasEvntNewmsgs = {val: false};

$(function () {
    var socket = io.connect('http://v-mast.mvc:8001');

    socket.on('connect', OnConnected);
    socket.on('chat message', OnChatMessage);
    socket.on('room update', OnRoomUpdate);
    socket.on('system message', OnSystemMessage);

    $('#m').keydown(function (e) {
        if (e.ctrlKey && e.keyCode == 13) {
            // Ctrl-Enter pressed
            $(this).val(function(i,val) {
                return val + "\r\n";
            });
            $(this).animate({ scrollTop: $(this)[0].scrollHeight}, 200);
        }
        else if(e.keyCode == 13) {
            if ($(this).val().trim() == "")
                return false;

            var chatData = {chatType: $("#chat_type").val(), eventID: eventID, msg: $(this).val()};
            socket.emit('chat message', chatData);
            $(this).blur().val('');
        }
    });

    window.onfocus = function () {
        isActive = true;

        if($("#p2p_messages").is(":visible"))
        {
            newp2pMsgsShown = true;
        }
        if($("#evnt_messages").is(":visible"))
        {
            newEvntMsgsShown = true;
        }
    };

    window.onblur = function () {
        isActive = false;
        newEvntMsgsShown = false;
        newp2pMsgsShown = false;
    };
});

function OnConnected()
{
    $("#evnt_messages").html("");
    $("#p2p_messages").html("");
    var data = {
        memberID: memberID,
        eventID: eventID,
        aT: aT,
        step: step,
        peerStep: peerStep
    };
    this.emit("new member", data);
}

function OnChatMessage(data)
{
    data.msg = data.msg.replace(/\n/g,'<br/>');

    var messagesType;
    var lastMsg;
    var msgName, msgClass, playMissed = false;
    var newBlock = "";
    var hasNewmsgs = false;

    if(data.chatType == "p2p")
    {
        messagesType = $("#p2p_messages");
        lastMsg = $("#p2p_messages .message:last");
        setCookie("p2p_last_msg", data.date, {expires: 24*60*60});
        hasNewmsgs = hasP2pNewmsgs;

    }
    else
    {
        messagesType = $("#evnt_messages");
        lastMsg = $("#evnt_messages .message:last");
        setCookie("evnt_last_msg", data.date, {expires: 24*60*60});
        hasNewmsgs = hasEvntNewmsgs;
    }

    if(isActive)
    {
        isActive = messagesType.is(":visible");
    }

    if(data.member.memberID == memberID)
    {
        msgClass = 'msg_my';
        msgName = 'You';
        $(".newmsgs", messagesType).remove();
    }
    else
    {
        msgClass = 'msg_other';
        msgName = data.member.userName; //data.member.firstName + " " + data.member.lastName;
        playMissed = true;
    }

    if(lastMsg.attr("data") == data.member.memberID)
    {
        var newmsgs = "";

        if(!isActive && !hasNewmsgs.val)
        {
            $(".newmsgs", messagesType).remove();

            newmsgs = '<li class="newmsgs">new messages</li>';
            hasNewmsgs.val = true;

            newBlock = newmsgs + '<li class="message ' + msgClass + '" data="' + data.member.memberID + '">' +
                '<div class="msg_name">' + msgName + '</div>' +
                '<div class="msg_text" data-toggle="tooltip" title="'+ParseDate(data.date)+'">' + data.msg + '</div>' +
                '</li>';
        }
        else
        {
            lastMsg.append('<div class="msg_text" data-toggle="tooltip" title="'+ParseDate(data.date)+'">' + data.msg + '</div>');
        }
    }
    else
    {
        var newmsgs = "";
        if(!isActive && !hasNewmsgs.val)
        {
            $(".newmsgs", messagesType).remove();

            newmsgs = '<li class="newmsgs">new messages</li>';
            hasNewmsgs.val = true;
        }

        newBlock = newmsgs + '<li class="message '+msgClass+'" data="'+ data.member.memberID +'">' +
            '<div class="msg_name">'+msgName+'</div>' +
            '<div class="msg_text" data-toggle="tooltip" title="'+ParseDate(data.date)+'">' + data.msg + '</div>' +
            '</li>';

    }

    messagesType.append(newBlock);

    if(playMissed && !isActive)
    {
        var missedMsg = document.getElementById('missedMsg');
        missedMsg.play();

        if(newp2pMsgsShown)
        {
            newp2pMsgsShown = false;
        }

        if(newEvntMsgsShown)
        {
            newEvntMsgsShown = false;
        }
    }

    messagesType.animate({ scrollTop: messagesType[0].scrollHeight}, 200);

    if(isActive)
        $('#m').focus();

    $('[data-toggle="tooltip"]').tooltip({placement: "top"});
}

function OnRoomUpdate(roomMates)
{
    $("#online").html("");

    for(var rm in roomMates)
    {
        if(roomMates[rm].memberID != memberID)
        {
            var name = roomMates[rm].userName; // roomMates[rm].firstName + ' ' + roomMates[rm].lastName
            $("#online").append('<li>'+ name +'</li>');
        }
    }
}

function OnSystemMessage(data)
{
    switch (data.type)
    {
        case "logout":
            window.location = "/members/logout";
            break;

        case "memberConnected":
            if(step == peerStep)
            {
                var data = {eventID: eventID, step: step};
                this.emit('step enter', data);
            }
            break;

        case "peerEnter":
            if($(".cotr_not_ready").length > 0)
            {
                window.location.reload();
            }
            break;

        case "prvtMsgs":
            var messages = [];
            var date, msgObj;
            var cookieLastMsg = getCookie("p2p_last_msg");

            for(var i in data.msgs)
            {
                if(isNaN(data.msgs[i]))
                {
                    msgObj = JSON.parse(data.msgs[i]);
                }
                else
                {
                    date = data.msgs[i];
                    msgObj.date = parseInt(date);

                    messages.push(msgObj);
                }
            }

            var lastDate = renderMessages(messages, $("#p2p_messages"), cookieLastMsg);

            setCookie("p2p_last_msg", lastDate, {expires: 24*60*60});
            break;

        case "evntMsgs":
            var messages = [];
            var date, msgObj;
            var cookieLastMsg = getCookie("evnt_last_msg");

            for(var i in data.msgs)
            {
                if(isNaN(data.msgs[i]))
                {
                    msgObj = JSON.parse(data.msgs[i]);
                }
                else
                {
                    date = data.msgs[i];
                    msgObj.date = parseInt(date);

                    messages.push(msgObj);
                }
            }

            var lastDate = renderMessages(messages, $("#evnt_messages"), cookieLastMsg);

            setCookie("evnt_last_msg", lastDate, {expires: 24*60*60});
            break;
    }

    $('[data-toggle="tooltip"]').tooltip({placement: "top"});
}

function renderMessages(messages, messagesType, cookieLastMsg)
{
    var lastDate;
    var hasNewmsgs = false;
    cookieLastMsg = typeof cookieLastMsg == "undefined" ? Date.now() : cookieLastMsg;

    $.each(messages, function(i, msgObj) {
        var msgName, msgClass, lastMsg, newBlock = "";

        lastMsg = $(".message:last", messagesType);

        if(msgObj.member.memberID == memberID)
        {
            msgClass = 'msg_my';
            msgName = 'You';
        }
        else
        {
            msgClass = 'msg_other';
            msgName = msgObj.member.userName; //msgObj.member.firstName + " " + msgObj.member.lastName;
        }

        if(lastMsg.attr("data") == msgObj.member.memberID)
        {
            if(hasNewmsgs || cookieLastMsg >= msgObj.date)
            {
                lastMsg.append('<div class="msg_text" data-toggle="tooltip" title="'+ParseDate(msgObj.date)+'">' + msgObj.msg + '</div>');
            }
            else
            {
                var newmsgs = "";
                if(!hasNewmsgs)
                {
                    newmsgs = '<li class="newmsgs">new messages</li>';
                    hasNewmsgs = true;
                }

                newBlock = newmsgs + '<li class="message ' + msgClass + '" data="' + msgObj.member.memberID + '">' +
                    '<div class="msg_name">' + msgName + '</div>' +
                    '<div class="msg_text" data-toggle="tooltip" title="'+ParseDate(msgObj.date)+'">' + msgObj.msg + '</div>' +
                    '</li>';
            }
        }
        else {
            var newmsgs = "";
            if(!hasNewmsgs && cookieLastMsg < msgObj.date)
            {
                newmsgs = '<li class="newmsgs">new messages</li>';
                hasNewmsgs = true;
            }

            newBlock = newmsgs + '<li class="message ' + msgClass + '" data="' + msgObj.member.memberID + '">' +
                '<div class="msg_name">' + msgName + '</div>' +
                '<div class="msg_text" data-toggle="tooltip" title="'+ParseDate(msgObj.date)+'">' + msgObj.msg + '</div>' +
                '</li>';
        }

        messagesType.append(newBlock);

        lastDate = msgObj.date;
    });

    return lastDate;
}

function ParseDate(timestamp) {
    var date = new Date();
    date.setTime(timestamp);

    return date.toLocaleString();

    var hours = date.getHours();
    var minutes = date.getMinutes();
    var seconds = date.getSeconds();
    var day = date.getDate();
    var month = date.getMonth()+1;
    var year = date.getFullYear();
    return day + "." + month + "." + year + " " + hours + ":" + minutes + ":" + seconds;
}
