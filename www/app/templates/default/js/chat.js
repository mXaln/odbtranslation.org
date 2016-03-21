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

            socket.emit('chat message', $(this).val());
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
    this.emit("new member", {memberID: memberID, tID: tID, aT: aT});
}

function OnChatMessage(data)
{
    console.log(data);

    data.msg = data.msg.replace(/\n/g,'<br/>');

    var lastMsg = $(".message:last");
    var msgName, msgClass, playMissed = false;

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

        $('#messages').append(newBlock);
    }

    if(playMissed && !isActive)
    {
        var missedMsg = document.getElementById('missedMsg');
        missedMsg.play();
    }

    $("#messages").animate({ scrollTop: $("#messages")[0].scrollHeight}, 200);
    $('#m').focus();
}

function OnRoomUpdate(roomMates)
{
    $("#online").html("");

    for(var rm in roomMates)
    {
        if(roomMates[rm].memberID != memberID)
            $("#online").append('<li>'+ roomMates[rm].firstName + ' ' + roomMates[rm].lastName + ' (' + roomMates[rm].userType + ') ' +'</li>');
    }
}