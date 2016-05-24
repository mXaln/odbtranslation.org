(function($) {
    var isActive;
    var hasP2pNewmsgs = {val: false};
    var hasEvntNewMsgs = {val: false};
    var newEvntMsgsShown = false;
    var newp2pMsgsShown = false;
    var currentP2Ptab, currentP2Pmsgs, currentChatType;
    var titleChanger, initTitle = document.title, isNewTitle = false;
    var missedMsgsNum = 0;
    var isInfoPage = false;
    var chatRightPos = 0;

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

    var settings;
    var methods = {
        init : function( options ) {
            var $this = this;

            settings = $.extend({
                step: "",
                memberID: memberID,
                eventID: 0,
                chkMemberID: chkMemberID,
                disableChat: false,
                isAdmin: false,
                onSendMessage : function() {},
            }, options );

            isInfoPage = $this.hasClass("info");

            if(settings.step == "keyword-check" || settings.step == "content-review")
            {
                currentP2Ptab = $("#chk");
                currentP2Pmsgs = $("#chk_messages");
                currentChatType = "chk";
            }
            else
            {
                if(!isInfoPage)
                {
                    currentP2Ptab = $("#p2p");
                    currentP2Pmsgs = $("#p2p_messages");
                    currentChatType = "p2p";
                }
                else
                {
                    currentP2Ptab = $("#evnt");
                    currentP2Pmsgs = $("#evnt_messages");
                    currentChatType = "evnt";
                    chatRightPos = -210;
                }

                currentP2Ptab.addClass("active");
            }

            currentP2Ptab.show();
            $("#chat_type").val(currentChatType);

            // Show/Hide chat window
            $("#chat_hide").click(function() {
                if($this.hasClass("open"))
                {
                    $this.removeClass("open")
                        .addClass("closed");
                    $this.animate({right: -610}, 500, function() {
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
                    $this.removeClass("closed")
                        .addClass("open");
                    $this.animate({right: chatRightPos}, 500, function() {
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

            // Switch chat room tabs
            $(".chat_tab").click(function() {
                var id = $(this).prop("id");

                switch (id)
                {
                    case currentP2Ptab.prop("id"):
                        $("#evnt").removeClass("active");
                        $("#evnt_messages").hide();
                        $(this).addClass("active");
                        currentP2Pmsgs.show();
                        $("#chat_type").val(currentChatType);
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
                        eventID: settings.eventID,
                        chatType: $("#chat_type").val(),
                        step: settings.step,
                        chkMemberID: settings.chkMemberID,
                        msg: $(this).val(),
                    };

                    settings.onSendMessage.call(chatData);
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

            $this.click(function() {
                clearInterval(titleChanger);
                document.title = initTitle;
                $(".chat_new_msgs").text("").hide();
                missedMsgsNum = 0;
            });

            $(".chat_new_msgs").click(function() {
                $this.removeClass("closed")
                    .addClass("open");
                $this.animate({right: chatRightPos}, 500, function() {
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
            });
        },
        clearMessages: function()
        {
            $(".chat_msgs").html("");
        },
        newMessageArrived: function(data)
        {
            if(settings.disableChat === true)
            {
                if(!settings.isAdmin)
                {
                    return;
                }
                else
                {
                    if(currentChatType == "p2p" || currentChatType == "chk")
                        return;
                }
            }

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

                clearInterval(titleChanger);
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
        },
        updateChatMembers: function(roomMates)
        {
            $("#online").html("");

            for(var rm in roomMates)
            {
                if(roomMates[rm].memberID != settings.memberID)
                {
                    var name = roomMates[rm].userName; // roomMates[rm].firstName + ' ' + roomMates[rm].lastName
                    $("#online").append('<li>'+ name + (roomMates[rm].isAdmin ? " (facilitator)" : "")+'</li>');
                }
            }
        },
        updatePrivateMessages: function(data)
        {
            if(currentP2Pmsgs.prop("id") == "evnt_messages")
                return true;

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

            $('[data-toggle="tooltip"]').tooltip();
        },
        updateEventMessages: function(data)
        {
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

            $('[data-toggle="tooltip"]').tooltip();
        }
    };

    /**
     * V-Mast Chat
     * @param method
     * @returns {*}
     */
    $.fn.chat = function(method) {
        if ( methods[method] ) {
            return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' +  method + ' is not defined' );
        }
    };


    // ----------------------- Private Functions ------------------------- //
    function renderMessages(messages, messagesType, cookieLastMsg)
    {
        if(settings.disableChat === true)
        {
            if(!settings.isAdmin)
            {
                return;
            }
            else
            {
                if(messagesType == "p2p" || messagesType == "chk")
                    return;
            }
        }

        var lastDate;
        var hasNewmsgs = false;
        cookieLastMsg = typeof cookieLastMsg == "undefined" ? Date.now() : cookieLastMsg;

        $.each(messages, function(i, msgObj) {
            var msgName, msgClass, lastMsg, newBlock = "";

            lastMsg = $(".message:last", messagesType);

            if(msgObj.member.memberID == settings.memberID)
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
                if(cookieLastMsg < msgObj.date)
                    missedMsgsNum++;

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
                if(cookieLastMsg < msgObj.date)
                    missedMsgsNum++

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

        if(missedMsgsNum > 0)
            $(".chat_new_msgs").text(missedMsgsNum).show();

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

}(jQuery));