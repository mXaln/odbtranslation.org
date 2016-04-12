var app = require('express')(),
    http = require('http').Server(app),
    io = require('socket.io')(http),
    redis = require("redis"),
    util = require("util"),
    _ = require("underscore"),
    XMLHttpRequest = require("xmlhttprequest-ssl").XMLHttpRequest,
    Member = require("./Member").Member,
    Event = require("./Event").Event,
    EventSteps = require("./EventSteps").EventSteps;

var members = [];

var clientRedis = redis.createClient();
//var clientDatabase.subscribe("LSpB8W8MMJXtv2Nkk9uf");

clientRedis.on("connect", function() {
    this.auth("Tss55-fw7-khU39", function(err, val) {
        util.log("Redis connected: " + val);
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

                this.leave("room" + member.events[evnt].eventID);

                // Delete event record if it is connected to no socket
                if(member.events[evnt].sockets.length == 0)
                {
                    var eventID = member.events[evnt].eventID;
                    delete member.events[evnt];

                    var roomMates = getMembersByRoomID(eventID);
                    io.to("room" + eventID).emit('room update', roomMates);
                }
            }

            // Delete member if he is connected to no socket
            if(sktNum == 0)
            {
                delete members["user" + member.memberID];
            }
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
                sct.join("room" + event.eventID);

                var roomMates = getMembersByRoomID(event.eventID);
                io.to("room" + event.eventID).emit('room update', roomMates);

                sendSavedMessages(sct, event);
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
                firstName: member.firstName,
                lastName: member.lastName
            };
            var event = getMemberEvent(member, chatData.eventID);

            if(!_.isEmpty(event))
            {
                date = Date.now();
                msgObj = {
                    member: client,
                    msg: _.escape(chatData.msg),
                    date: date,
                    chatType: "p2p",
                    step: chatData.step
                };

                if(chatData.chatType == "p2p")
                {
                    var eSteps = new EventSteps();
                    var id = event.cotrMemberID;
                    var pairID = event.pairID;

                    if(chatData.step == eSteps.KEYWORD_CHECK || chatData.step == eSteps.CONTENT_REVIEW)
                    {
                        id = chatData.trMemberID;
                        pairID = "pair-"
                            + chatData.eventID
                            + "-"
                            + (Math.min(id, member.memberID))
                            + "-"
                            + (Math.max(id, member.memberID));
                    }

                    var translator = getMemberByUserId("user" + id);

                    if(typeof translator !== 'undefined')
                    {
                        var trEvent = getMemberEvent(translator, event.eventID);

                        if(!_.isEmpty(trEvent))
                        {
                            // Send message to co-translator
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
                else
                {
                    msgObj.chatType = "evnt";
                    io.to("room" + event.eventID).emit('chat message', msgObj);

                    clientRedis.ZADD("rooms:event-" + event.eventID, date, JSON.stringify(msgObj));
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
                switch (data.step)
                {
                    case eSteps.PEER_REVIEW:
                        var coTranslator = getMemberByUserId("user" + event.cotrMemberID);

                        if(typeof coTranslator !== 'undefined')
                        {
                            var cotrEvent = getMemberEvent(coTranslator, event.eventID);

                            if(!_.isEmpty(cotrEvent))
                            {
                                // Send message to co-translator
                                for(var skt in cotrEvent.sockets)
                                {
                                    io.to(cotrEvent.sockets[skt]).emit('system message', {type: "peerEnter"});
                                }
                            }
                        }
                        break;

                    case eSteps.KEYWORD_CHECK:
                    case eSteps.CONTENT_REVIEW:
                        var msgObj = {
                            excludes: [member.memberID, event.cotrMemberID],
                            anchor: "check:"+event.eventID+":"+member.memberID
                        };
                        io.to("room" + event.eventID).emit('checking request', msgObj);
                        break;
                }
            }
        }
    });
});

http.listen(8001, function()
{
    console.log('listening on *:8001');
});


/**************************************************
 ** HELPER FUNCTIONS
 **************************************************/
function registerNewMemberEvent(data, sct, member)
{
    var xhr = new XMLHttpRequest();

    xhr.onreadystatechange = function()
    {
        if (this.readyState == this.DONE)
        {
            try
            {
                var response = JSON.parse(this.responseText);

                if(!_.isEmpty(response))
                {
                    var pairID = "pair-"
                        + data.eventID
                        + "-"
                        + (Math.min(response.memberID, response.cotrMemberID))
                        + "-"
                        + (Math.max(response.memberID, response.cotrMemberID));

                    var newEvent = new Event();
                    newEvent.eventID = data.eventID;
                    newEvent.cotrMemberID = response.cotrMemberID;
                    newEvent.pairID = pairID;
                    newEvent.sockets.push(sct.id);

                    if(_.isEmpty(member))
                    {
                        var newMember = new Member();
                        newMember.memberID = response.memberID;
                        newMember.userName = response.userName;
                        newMember.firstName = response.firstName;
                        newMember.lastName = response.lastName;
                        //newMember.userType = response.userType;
                        newMember.authToken = data.aT;
                        newMember.events.push(newEvent);

                        members["user" + newMember.memberID] = newMember;
                    }
                    else
                    {
                        member.events.push(newEvent);
                    }

                    sct.join("room" + data.eventID);

                    var roomMates = getMembersByRoomID(data.eventID);
                    io.to("room" + data.eventID).emit('room update', roomMates);

                    sendSavedMessages(sct, newEvent);
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

    xhr.open("GET", "http://v-mast.mvc/members/rpc/auth/" + data.memberID + "/" + data.eventID + "/" + data.aT);
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
};

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

function getMembersByRoomID(roomID)
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
            if(members[m].events[e].eventID == roomID)
            {
                var tmpm = {};
                tmpm.memberID = members[m].memberID;
                tmpm.userName = members[m].userName;
                tmpm.firstName = members[m].firstName;
                tmpm.lastName = members[m].lastName;
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

function sendSavedMessages(socket, event)
{
    var since = Date.now() - 10 * 24 * 60 * 60 * 1000; // get messages within 10 days period

    // TODO Remove old messages by running command ZREMRANGEBYSCORE zset -inf since

    clientRedis.ZRANGEBYSCORE("rooms:" + event.pairID, since, "+inf", "WITHSCORES", function(err, value) {
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

    socket.emit('system message', {type: "memberConnected"});
}

function inspect(obj) {
    console.log(util.inspect(obj, { showHidden: true, depth: null }));
}