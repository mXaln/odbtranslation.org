var isActive;

$(function () {
    var socket = io.connect('http://v-mast.mvc:8001');

    socket.on('connect', OnConnected);
    socket.on('chat message', OnChatMessage);
    socket.on('room update', OnRoomUpdate);

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
    };

    window.onblur = function () {
        isActive = false;
    };
});

function OnConnected()
{
    this.emit("new member", {memberID: memberID, eventID: eventID, aT: aT});
}

function OnChatMessage(data)
{
    console.log(data);

    data.msg = data.msg.replace(/\n/g,'<br/>');

    var messagesType;
    var lastMsg;
    var msgName, msgClass, playMissed = false;

    if(data.chatType == "p2p")
    {
        messagesType = $("#p2p_messages");
        lastMsg = $("#p2p_messages .message:last");
    }
    else
    {
        messagesType = $("#evnt_messages");
        lastMsg = $("#evnt_messages .message:last");
    }

    if(data.member.memberID == memberID)
    {
        msgClass = 'msg_my';
        msgName = 'You';
    }
    else
    {
        msgClass = 'msg_other';
        msgName = data.member.firstName + " " + data.member.lastName;
        playMissed = true;
    }

    if(lastMsg.attr("data") == data.member.memberID)
    {
        lastMsg.append('<div class="msg_text">' + data.msg + '</div>');
    }
    else
    {
        var newBlock = '<li class="message '+msgClass+'" data="'+ data.member.memberID +'">' +
            '<div class="msg_name">'+msgName+'</div>' +
            '<div class="msg_text">' + data.msg + '</div>' +
            '</li>';

        messagesType.append(newBlock);
    }

    if(isActive)
    {
        isActive = messagesType.is(":visible");
    }

    if(playMissed && !isActive)
    {
        var missedMsg = document.getElementById('missedMsg');
        missedMsg.play();
    }

    messagesType.animate({ scrollTop: messagesType[0].scrollHeight}, 200);

    console.log(messagesType);

    $('#m').focus();
}

function OnRoomUpdate(roomMates)
{
    $("#online").html("");

    for(var rm in roomMates)
    {
        if(roomMates[rm].memberID != memberID)
            $("#online").append('<li>'+ roomMates[rm].firstName + ' ' + roomMates[rm].lastName + ' (' + roomMates[rm].userName + ') ' +'</li>');
    }
}