var app = require('express')(),
    fs = require('fs'),
    https = require('https'),
    server = https.createServer({
        key:    fs.readFileSync('/var/www/html/v-mast.mvc/Nodejs/ssl/server.key'),
        cert:   fs.readFileSync('/var/www/html/v-mast.mvc/Nodejs/ssl/server.crt'),
        ca:     fs.readFileSync('/var/www/html/v-mast.mvc/Nodejs/ssl/rootCA.pem')
    }, app).listen(8001),
    io = require('socket.io')(server),
    redis = require("redis"),
    util = require("util"),
    _ = require("underscore"),
    XMLHttpRequest = require("xmlhttprequest-ssl").XMLHttpRequest,
    Member = require("./Member").Member,
    Event = require("./Event").Event,
    EventSteps = require("./EventSteps").EventSteps;

var members = [];

var clientRedis = redis.createClient();

clientRedis.on("connect", function() {
    this.auth("P@ssw0rd-22", function(err, val) {
        util.log("Redis connection status: " + val);
    });
});

io.on('connection', function(socket)
{
    console.log('a user connected: %s', socket.id);

    socket.on('disconnect', function()
    {
        console.log('user disconnected: %s', this.id);

        var member = getMemberBySocketId(this.id);

        if(member)
        {
            var sktNum = 0;

            for(var evnt in member.events)
            {
                var index = member.events[evnt].sockets.indexOf(this.id);

                if(index == -1) {
                    sktNum += member.events[evnt].sockets.length;
                    continue;
                }

                member.events[evnt].sockets.splice(index, 1);
                sktNum += member.events[evnt].sockets.length;

                this.leave("eventroom" + member.events[evnt].eventID);
                this.leave("projectroom" + member.events[evnt].projectID);

                // Delete event and project record if it is connected to no socket
                if(member.events[evnt].sockets.length == 0)
                {
                    var eventID = member.events[evnt].eventID;
                    var projectID = member.events[evnt].projectID;
                    delete member.events[evnt];

                    var eventRoomMates = getMembersByRoomID(eventID, "event");
                    io.to("eventroom" + eventID).emit('event room update', eventRoomMates);

                    var projectRoomMates = getMembersByRoomID(projectID, "project");
                    io.to("projectroom" + projectID).emit('project room update', projectRoomMates);
                }
            }

            // Delete member if he is connected to no socket
            if(sktNum == 0)
                delete members["user" + member.memberID];
        }
    });

    socket.on('new member', function(data)
    {
        var sct = this;
        var member = getMemberByUserId("user" + data.memberID);

        if(member && member.authToken == data.aT)
        {
            var event = getMemberEvent(member, data.eventID);

            if(!_.isEmpty(event))
            {
                event.sockets.push(sct.id);
                sct.join("eventroom" + event.eventID);
                sct.join("projectroom" + event.projectID);

                var eventRoomMates = getMembersByRoomID(event.eventID, "event");
                io.to("eventroom" + event.eventID).emit('event room update', eventRoomMates);

                var projectRoomMates = getMembersByRoomID(event.projectID, "project");
                io.to("projectroom" + event.projectID).emit('project room update', projectRoomMates);

                // Add pair to checkers chat
                var eSteps = new EventSteps();
                var checkPair = "";

                if(data.chkMemberID > 0)
                {
                    checkPair = "pair-"
                        + event.eventID
                        + "-"
                        + (Math.min(data.chkMemberID, member.memberID))
                        + "-"
                        + (Math.max(data.chkMemberID, member.memberID));

                    if(event.checkPairs.indexOf(checkPair) < 0)
                        event.checkPairs.push(checkPair);
                }
                else
                {
                    checkPair = "zero";
                }

                sendSavedMessages(sct, event, checkPair);
            }
            else
            {
                registerNewMemberEvent(data, sct, member);
            }

            return;
        }

        registerNewMemberEvent(data, sct, {});
    });

    socket.on('chat message', function(chatData)
    {
        if(chatData.msg.trim() == "")
            return false;

        var member = getMemberBySocketId(this.id);

        if(member)
        {
            var msgObj, date;
            var client = {
                memberID: member.memberID,
                userName: member.userName,
                fullName: member.firstName + " " + member.lastName.charAt(0) + ".",
                //firstName: member.firstName,
                //lastName: member.lastName
            };
            var event = getMemberEvent(member, chatData.eventID);

            if(!_.isEmpty(event))
            {
                date = Date.now();
                msgObj = {
                    member: client,
                    msg: _.escape(chatData.msg),
                    date: date,
                    chatType: "chk"
                };

                if(chatData.chatType == "chk")
                {
                    var eSteps = new EventSteps();

                    id = chatData.chkMemberID;

                    if(id <= 0) return false;

                    pairID = "pair-"
                        + event.eventID
                        + "-"
                        + (Math.min(id, member.memberID))
                        + "-"
                        + (Math.max(id, member.memberID));

                    if(event.checkPairs.indexOf(pairID) < 0)
                        return false;

                    var translator = getMemberByUserId("user" + id);

                    if(typeof translator !== 'undefined')
                    {
                        var trEvent = getMemberEvent(translator, event.eventID);

                        if(!_.isEmpty(trEvent))
                        {
                            // Send message to co-translator/checker
                            for(var skt in trEvent.sockets)
                            {
                                io.to(trEvent.sockets[skt]).emit('chat message', msgObj);
                            }
                        }
                    }

                    // Send message to sender himself
                    for(var skt in event.sockets)
                    {
                        io.to(event.sockets[skt]).emit('chat message', msgObj);
                    }

                    clientRedis.ZADD("rooms:" + pairID, date, JSON.stringify(msgObj));
                }
                else if(chatData.chatType == "evnt")
                {
                    msgObj.chatType = "evnt";
                    io.to("eventroom" + event.eventID).emit('chat message', msgObj);

                    clientRedis.ZADD("rooms:event-" + event.eventID, date, JSON.stringify(msgObj));
                }
                else if(chatData.chatType == "proj")
                {
                    msgObj.chatType = "proj";
                    io.to("projectroom" + event.projectID).emit('chat message', msgObj);

                    clientRedis.ZADD("rooms:project-" + event.projectID, date, JSON.stringify(msgObj));
                }
            }
        }
    });

    socket.on('system message', function(data)
    {
        var member = getMemberBySocketId(this.id);

        if(member)
        {
            var event = getMemberEvent(member, data.eventID);

            if(!_.isEmpty(event))
            {
                switch (data.type)
                {
                    case "checkDone":
                        var checkMember = getMemberByUserId("user" + data.chkMemberID);

                        if(typeof checkMember !== 'undefined')
                        {
                            var checkEvent = getMemberEvent(checkMember, event.eventID);

                            if(!_.isEmpty(checkEvent))
                            {
                                // Send message to check member
                                for(var skt in checkEvent.sockets)
                                {
                                    io.to(checkEvent.sockets[skt]).emit('system message', {type: "checkDone"});
                                }
                            }
                        }
                        break;

                    case "comment":
                        var commentData = {
                            type: "comment",
                            memberID: member.memberID,
                            name: member.firstName + " " + member.lastName,
                            verse: _.escape(data.verse),
                            text: _.escape(data.text),
                            level: _.escape(data.level)
                        };

                        io.to("eventroom" + event.eventID).emit('system message', commentData);
                        break;

                    case "keyword":
                        var checkMember = getMemberByUserId("user" + data.chkMemberID);

                        if(typeof checkMember !== 'undefined')
                        {
                            var checkEvent = getMemberEvent(checkMember, event.eventID);

                            if(!_.isEmpty(checkEvent))
                            {
                                var keywordData = {
                                    type: "keyword",
                                    remove: _.escape(data.remove),
                                    verseID: _.escape(data.verseID),
                                    index: _.escape(data.index),
                                    text: _.escape(data.text)
                                };

                                // Send message to check member
                                for(var skt in checkEvent.sockets)
                                {
                                    io.to(checkEvent.sockets[skt]).emit('system message', keywordData);
                                }
                            }
                        }
                        break;
                }
            }
        }
    });

    socket.on('step enter', function(data)
    {
        var member = getMemberBySocketId(this.id);
        var eSteps = new EventSteps();

        if(member)
        {
            var event = getMemberEvent(member, data.eventID);

            if(!_.isEmpty(event))
            {
                var messageType = "";
                switch (data.step)
                {
                    case eSteps.PEER_REVIEW:
                    case eSteps.KEYWORD_CHECK:
                    case eSteps.CONTENT_REVIEW:
                    case eSteps.PEER_REVIEW_L2:
                    case eSteps.PEER_REVIEW_L3:
                        if((data.step == eSteps.KEYWORD_CHECK || data.step == eSteps.CONTENT_REVIEW)
                            && data.tMode != "undefined"
                            && ["tn","tq","tw","sun"].indexOf(data.tMode) > -1) break;

                        messageType = "checkEnter";
                        if(!data.isChecker)
                        {
                            var msgObj = {
                                excludes: [member.memberID],
                                anchor: "check:"+event.eventID+":"+member.memberID
                            };
                            io.to("eventroom" + event.eventID).emit('checking request', msgObj);
                        }
                        else
                        {
                            if(data.chkMemberID > 0)
                            {
                                var checkMember = getMemberByUserId("user" + data.chkMemberID);

                                if(typeof checkMember !== 'undefined')
                                {
                                    var checkEvent = getMemberEvent(checkMember, event.eventID);

                                    if(!_.isEmpty(checkEvent))
                                    {
                                        checkPair = "pair-"
                                            + checkEvent.eventID
                                            + "-"
                                            + (Math.min(data.chkMemberID, member.memberID))
                                            + "-"
                                            + (Math.max(data.chkMemberID, member.memberID));

                                        if(checkEvent.checkPairs.indexOf(checkPair) < 0)
                                            checkEvent.checkPairs.push(checkPair);

                                        // Send message to check member
                                        for(var skt in checkEvent.sockets)
                                        {
                                            var name = member.firstName + " " + member.lastName.charAt(0) + ".";
                                            io.to(checkEvent.sockets[skt]).emit('system message', {type: messageType, memberID: member.memberID, userName: name});
                                        }

                                        // Send message to roommates
                                        io.to("eventroom" + event.eventID).emit('system message', {type: "chkStarted", id: "check:"+event.eventID+":"+data.chkMemberID});
                                    }
                                }
                            }
                        }
                        break;
                }
            }
        }
    });

    socket.on('videoCallMessage', function (data) {
        var member = getMemberBySocketId(this.id);

        if(member)
        {
            var event = getMemberEvent(member, data.eventID);

            if(!_.isEmpty(event))
            {
                id = data.chkMemberID;

                if(id <= 0) return false;

                pairID = "pair-"
                    + event.eventID
                    + "-"
                    + (Math.min(id, member.memberID))
                    + "-"
                    + (Math.max(id, member.memberID));

                if(event.checkPairs.indexOf(pairID) < 0)
                    return false;

                var translator = getMemberByUserId("user" + id);

                if(typeof translator !== 'undefined')
                {
                    var trEvent = getMemberEvent(translator, event.eventID);

                    if(!_.isEmpty(trEvent))
                    {
                        if(data.type == "gotUserMedia")
                        {
                            data.userName = member.userName;
                            data.memberID = member.memberID;
                        }

                        // Send message to co-translator/checker
                        for(var skt in trEvent.sockets)
                        {
                            io.to(trEvent.sockets[skt]).emit('videoCallMessage', data);
                        }
                    }
                }

                // Send message back to member sockets
                if(data.type == "gotUserMedia")
                {
                    for(var skt in event.sockets)
                    {
                        io.to(event.sockets[skt]).emit('callAnswered', {});
                    }
                }
            }
        }
    });
});

/**************************************************
 ** HELPER FUNCTIONS
 **************************************************/
function registerNewMemberEvent(data, sct, member)
{
    var xhr = new XMLHttpRequest({
        pfx: null,
    });

    xhr.onreadystatechange = function()
    {
        if (this.readyState == this.DONE)
        {
            try
            {
                var response = JSON.parse(this.responseText);

                if(!_.isEmpty(response))
                {
                    var newEvent = new Event();
                    newEvent.eventID = data.eventID;
                    newEvent.projectID = data.projectID;
                    newEvent.sockets.push(sct.id);

                    // Add pair to checkers chat
                    var checkPair = "";

                    if(data.chkMemberID > 0)
                    {
                        checkPair = "pair-"
                            + newEvent.eventID
                            + "-"
                            + (Math.min(data.chkMemberID, response.memberID))
                            + "-"
                            + (Math.max(data.chkMemberID, response.memberID));

                        newEvent.checkPairs.push(checkPair);
                    }
                    else
                    {
                        checkPair = "zero";
                    }

                    if(_.isEmpty(member))
                    {
                        var newMember = new Member();
                        newMember.memberID = response.memberID;
                        newMember.userName = response.userName;
                        newMember.firstName = response.firstName;
                        newMember.lastName = response.lastName;
                        newMember.isAdmin = response.isAdmin;
                        newMember.isSuperAdmin = response.isSuperAdmin;
                        newMember.authToken = data.aT;
                        newMember.events.push(newEvent);

                        members["user" + newMember.memberID] = newMember;
						console.log("------------- @"+newMember.userName);
                    }
                    else
                    {
                        member.events.push(newEvent);
						console.log("------------- @"+member.userName);
                    }

                    sct.join("eventroom" + data.eventID);
                    sct.join("projectroom" + data.projectID);

                    var eventRoomMates = getMembersByRoomID(data.eventID, "event");
                    io.to("eventroom" + data.eventID).emit('event room update', eventRoomMates);

                    var projectRoomMates = getMembersByRoomID(data.projectID, "project");
                    io.to("projectroom" + data.projectID).emit('project room update', projectRoomMates);

                    sendSavedMessages(sct, newEvent, checkPair);
                }
                else
                {
                    sct.emit('system message', {type: "logout"});
                }
            }
            catch(err)
            {
                util.log(err);
            }
        }
    };

    xhr.open("GET", "https://v-mast.mvc/members/rpc/auth/" + data.memberID + "/" + data.eventID + "/" + data.aT);
    xhr.send();
}


/**
 * Find member by userID
 * @param userID
 * @returns {*}
 */
function getMemberByUserId(userID) {
    if(_.keys(members).length > 0) {
        return members[userID];
    }

    return false;
}
/**
 * Find member by socketID
 * @param socketID
 * @returns {*}
 */
function getMemberBySocketId(socketID)
{
    if(_.keys(members).length <= 0)
        return null;

    for (var m in members)
    {
        for (var e in members[m].events)
        {
            if(members[m].events[e].sockets.indexOf(socketID) > -1)
            {
                return members[m];
            }
        }
    }

    return null;
}

function getMembersByRoomID(roomID, room)
{
    var roomMates = [];

    if(_.keys(members).length <= 0)
        return roomMates;

    for (var m in members)
    {
        if(_.keys(members[m].events).length <= 0)
            continue;

        for(var e in members[m].events)
        {
            mRoomID = 0;
            switch (room)
            {
                case "event":
                    mRoomID = members[m].events[e].eventID;
                    break;
                case "project":
                    mRoomID = members[m].events[e].projectID;
                    break;
                default:
                    break;
            }

            if(mRoomID == roomID)
            {
                var tmpm = {};
                tmpm.memberID = members[m].memberID;
                tmpm.userName = members[m].userName;
                tmpm.isAdmin = members[m].isAdmin;
                tmpm.isSuperAdmin = members[m].isSuperAdmin;
                tmpm.name = members[m].firstName + " " + members[m].lastName.charAt(0) + ".";
                //tmpm.firstName = members[m].firstName;
                //tmpm.lastName = members[m].lastName;
                //tmpm.userType = members[m].userType;

                roomMates.push(tmpm);
            }
        }
    }

    return roomMates;
}

function getMemberEvent(member, eventID)
{
    var event = {};

    if(!_.isEmpty(member))
    {
        for (var evnt in member.events)
        {
            if(member.events[evnt].eventID == eventID)
                return member.events[evnt];
        }
    }

    return event;
}

function sendSavedMessages(socket, event, checkPair)
{
    var since = Date.now() - 60 * 24 * 60 * 60 * 1000; // get messages within 60 days period

    if(checkPair != "zero")
    {
        clientRedis.ZREMRANGEBYSCORE("rooms:" + checkPair, "-inf", since);
        clientRedis.ZRANGEBYSCORE("rooms:" + checkPair, since, "+inf", "WITHSCORES", function(err, value) {
            try
            {
                if(!_.isEmpty(value))
                {
                    socket.emit('system message', {type: "prvtMsgs", msgs: value});
                }
            }
            catch (err)
            {
                util.log(err);
            }
        });
    }

    clientRedis.ZREMRANGEBYSCORE("rooms:event-" + event.eventID, "-inf", since);
    clientRedis.ZRANGEBYSCORE("rooms:event-" + event.eventID, since, "+inf", "WITHSCORES", function(err, value) {
        try
        {
            if(!_.isEmpty(value))
            {
                socket.emit('system message', {type: "evntMsgs", msgs: value});
            }
        }
        catch (err)
        {
            util.log(err);
        }
    });

    clientRedis.ZREMRANGEBYSCORE("rooms:project-" + event.projectID, "-inf", since);
    clientRedis.ZRANGEBYSCORE("rooms:project-" + event.projectID, since, "+inf", "WITHSCORES", function(err, value) {
        try
        {
            if(!_.isEmpty(value))
            {
                socket.emit('system message', {type: "projMsgs", msgs: value});
            }
        }
        catch (err)
        {
            util.log(err);
        }
    });

    socket.emit('system message', {type: "memberConnected"});
}

function inspect(obj) {
    console.log(util.inspect(obj, { showHidden: true, depth: null }));
}
