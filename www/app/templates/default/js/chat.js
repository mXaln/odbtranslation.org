var isActive;
var hasP2pNewmsgs = {val: false};
var hasEvntNewMsgs = {val: false};
var newEvntMsgsShown = false;
var newp2pMsgsShown = false;
var currentP2Ptab, currentP2Pmsgs, currentChatType;
var titleChanger, initTitle = document.title, isNewTitle = false;
var missedMsgsNum = 0;

var eventSteps = {
    PRAY: "pray",
    CONSUME: "consume",
    DISCUSS: "discuss",
    PRE_CHUNKING: "pre-chunking",
    CHUNKING: "chunking",
    BLIND_DRAFT: "blind-draft",
    SELF_CHECK: "self-check",
    PEER_REVIEW: "peer-review",
    KEYWORD_CHECK: "keyword-check",
    CONTENT_REVIEW: "content-review",
    FINISHED: "finished",
};

$(function () {
    var socket = io.connect('http://v-mast.com:8001');

    socket.on('connect', OnConnected);
    socket.on('chat message', OnChatMessage);
    socket.on('room update', OnRoomUpdate);
    socket.on('system message', OnSystemMessage);
    socket.on('checking request', OnCheckingRequest);

    if(step == "keyword-check" || step == "content-review")
    {
        currentP2Ptab = $("#chk");
        currentP2Pmsgs = $("#chk_messages");
        currentChatType = "chk";
    }
    else
    {
        currentP2Ptab = $("#p2p");
        currentP2Pmsgs = $("#p2p_messages");
        currentChatType = "p2p";
    }

    currentP2Ptab.show();
    $("#chat_type").val(currentChatType);


    // Show/Hide chat window
    $("#chat_hide").click(function() {
        if($("#chat_container").hasClass("open"))
        {
            $("#chat_container").removeClass("open")
                .addClass("closed");
            $("#chat_container").animate({right: "-610px"}, 500, function() {
                $("#chat_hide").removeClass("glyphicon-remove")
                    .addClass("glyphicon-chevron-left");

                $(".chat_tab").removeClass("active");
                currentP2Ptab.addClass("active");
                $("#chat_type").val(currentChatType);

                $(".chat_msgs").hide();
            });
        }
        else
        {
            $("#chat_container").removeClass("closed")
                .addClass("open");
            $("#chat_container").animate({right: 0}, 500, function() {
                $("#chat_hide").removeClass("glyphicon-chevron-left")
                    .addClass("glyphicon-remove");

                currentP2Pmsgs.show();

                var newmsgs = $(".newmsgs", currentP2Pmsgs);
                if(!newp2pMsgsShown && newmsgs.length > 0) {
                    currentP2Pmsgs.animate({scrollTop: newmsgs.offset().top - currentP2Pmsgs.offset().top + currentP2Pmsgs.scrollTop()}, 200);
                    newp2pMsgsShown = true;
                }
                else
                {
                    currentP2Pmsgs.animate({scrollTop: currentP2Pmsgs[0].scrollHeight}, 200);
                }
            });
        }
    });

    // Change chat room tabs
    $(".chat_tab").click(function() {
        var id = $(this).prop("id");

        switch (id)
        {
            case currentP2Ptab.prop("id"):
                $(this).addClass("active");
                $("#evnt").removeClass("active");
                $("#chat_type").val(currentChatType);
                currentP2Pmsgs.show();
                $("#evnt_messages").hide();
                break;

            default:
                $(this).addClass("active");
                currentP2Ptab.removeClass("active");
                $("#chat_type").val("evnt");
                $("#evnt_messages").show();
                currentP2Pmsgs.hide();

                var newmsgs = $(".newmsgs", $("#evnt_messages"));

                if(!newEvntMsgsShown && newmsgs.length > 0) {
                    $("#evnt_messages").animate({scrollTop: newmsgs.offset().top - $("#evnt_messages").offset().top + $("#evnt_messages").scrollTop()}, 200);

                    newEvntMsgsShown = true;
                }
                else
                {
                    $("#evnt_messages").animate({scrollTop: $("#evnt_messages")[0].scrollHeight}, 200);
                }
                break;
        }
    });


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

            var chatData = {
                eventID: eventID,
                chatType: $("#chat_type").val(),
                step: step,
                chkMemberID: chkMemberID,
                msg: $(this).val(),
            };
            socket.emit('chat message', chatData);
            $(this).blur().val('');
        }
    });

    window.onfocus = function () {
        isActive = true;

        if(currentP2Pmsgs.is(":visible"))
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
    $(".chat_msgs").html("");

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
    //if(data.chatType != "evnt" && data.step != step)
    //    return false;

    data.msg = data.msg.replace(/\n/g,'<br/>');

    var messagesObj;
    var lastMsg;
    var msgName, msgClass, playMissed = false;
    var newBlock = "";
    var hasNewMsgs = false;

    switch (data.chatType)
    {
        case currentChatType:
            messagesObj = currentP2Pmsgs;
            lastMsg = $(".message:last", currentP2Pmsgs);
            setTimeout(function() {
                setCookie(currentChatType + "_last_msg", data.date, {expires: 24*60*60});
            }, 1000);
            hasNewMsgs = hasP2pNewmsgs;
            break;

        default:
            messagesObj = $("#evnt_messages");
            lastMsg = $("#evnt_messages .message:last");
            setTimeout(function() {
                setCookie("evnt_last_msg", data.date, {expires: 24*60*60});
            }, 1000);
            hasNewMsgs = hasEvntNewMsgs;
            break;
    }

    if(isActive)
    {
        isActive = messagesObj.is(":visible");
    }

    if(data.member.memberID == memberID)
    {
        msgClass = 'msg_my';
        msgName = 'You';
        $(".newmsgs", messagesObj).remove();
    }
    else
    {
        msgClass = 'msg_other';
        msgName = data.member.userName;
        playMissed = true;
    }

    if(lastMsg.attr("data") == data.member.memberID)
    {
        var newmsgs = "";

        if(!isActive && !hasNewMsgs.val && data.member.memberID != memberID)
        {
            $(".newmsgs", messagesObj).remove();

            newmsgs = '<li class="newmsgs">new messages</li>';
            hasNewMsgs.val = true;

            newBlock = newmsgs + '<li class="message ' + msgClass + '" data="' + data.member.memberID + '">' +
                '<div class="msg_name">' + msgName + '</div>' +
                '<div class="msg_text" data-toggle="tooltip" data-placement="top" title="'+ParseDate(data.date)+'">' + data.msg + '</div>' +
                '</li>';
        }
        else
        {
            lastMsg.append('<div class="msg_text" data-toggle="tooltip" data-placement="top" title="'+ParseDate(data.date)+'">' + data.msg + '</div>');
        }
    }
    else
    {
        var newmsgs = "";
        if(!isActive && !hasNewMsgs.val && data.member.memberID != memberID)
        {
            $(".newmsgs", messagesObj).remove();

            newmsgs = '<li class="newmsgs">new messages</li>';
            hasNewMsgs.val = true;
        }

        newBlock = newmsgs + '<li class="message '+msgClass+'" data="'+ data.member.memberID +'">' +
            '<div class="msg_name">'+msgName+'</div>' +
            '<div class="msg_text" data-toggle="tooltip" data-placement="top" title="'+ParseDate(data.date)+'">' + data.msg + '</div>' +
            '</li>';

    }

    messagesObj.append(newBlock);

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

        titleChanger = setInterval(function() {
            document.title = !isNewTitle ? "New message arrived" : initTitle;
            isNewTitle = !isNewTitle;
        }, 1000);

        missedMsgsNum++;
        $(".chat_new_msgs").text(missedMsgsNum).show();
    }

    messagesObj.animate({ scrollTop: messagesObj[0].scrollHeight}, 200);

    if(isActive)
        $('#m').focus();

    $('[data-toggle="tooltip"]').tooltip();

    $("#chat_container").click(function() {
        clearInterval(titleChanger);
        document.title = initTitle;
        $(".chat_new_msgs").text("").hide();
        missedMsgsNum = 0;
    });
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
            var data = {eventID: eventID, step: step, chkMemberID: chkMemberID};
            this.emit('step enter', data);
            break;

        case "peerEnter":
            if($(".cotr_not_ready").length > 0)
            {
                $.ajax({
                        url: "/events/rpc/get_partner_translation",
                        method: "post",
                        dataType: "html",
                        data: {eventID: eventID}
                    })
                    .done(function(data) {
                        $(".cotrData").html(data);
                        $('[data-toggle="tooltip"]').tooltip();
                    });
            }
            break;

        case "checkEnter":
            if(chkMemberID == 0 || $(".checker_waits").length > 0)
            {
                $(".check_request").remove();
            }
            break;

        case "prvtMsgs":
            var messages = [];
            var date, msgObj;
            var cookieLastMsg = getCookie(currentChatType + "_last_msg");

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

            var lastDate = renderMessages(messages, currentP2Pmsgs, cookieLastMsg);

            setCookie(currentChatType + "_last_msg", lastDate, {expires: 24*60*60});
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

    $('[data-toggle="tooltip"]').tooltip();
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
            $(".notif_block").html("");
            if(data.notifs.length > 0)
            {
                $(".notif_count").remove();
                $("#notifications").append('<span class="notif_count">'+data.notifs.length+'</span>');
                var notifs = "";
                $.each(data.notifs, function(i, note) {
                    notifs += '<a href="'+note.link+'" data="'+note.anchor+'" target="_blank"><li>'+note.text+'</li></a>';
                });
                $(".notif_block").html(notifs);
            }
            else
            {
                $(".notif_count").remove();
                $(".notif_block").html('<div class="no_notif">'+data.noNotifs+'</div>');
            }
        }
    });
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
                lastMsg.append('<div class="msg_text" data-toggle="tooltip" data-placement="top" title="'+ParseDate(msgObj.date)+'">' + msgObj.msg + '</div>');
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
                    '<div class="msg_text" data-toggle="tooltip" data-placement="top" title="'+ParseDate(msgObj.date)+'">' + msgObj.msg + '</div>' +
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
                '<div class="msg_text" data-toggle="tooltip" data-placement="top" title="'+ParseDate(msgObj.date)+'">' + msgObj.msg + '</div>' +
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
