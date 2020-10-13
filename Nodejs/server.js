const app = require('express')(),
    fs = require('fs'),
    https = require('https'),
    server = https.createServer({
        key:    fs.readFileSync('ssl/server.key'),
        cert:   fs.readFileSync('ssl/server.crt'),
        ca:     fs.readFileSync('ssl/rootCA.pem')
    }, app).listen(8001),
    io = require('socket.io')(server),
    redis = require("redis"),
    util = require("util"),
    _ = require("underscore"),
    XMLHttpRequest = require("xmlhttprequest-ssl").XMLHttpRequest,
    Member = require("./Member").Member,
    Event = require("./Event").Event,
    EventSteps = require("./EventSteps").EventSteps;

const members = [];

const clientRedis = redis.createClient();

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

        const member = getMemberBySocketId(this.id);

        if(member)
        {
            let sktNum = 0;

            for(const e in member.events)
            {
                const index = member.events[e].sockets.indexOf(this.id);

                if(index === -1) {
                    sktNum += member.events[e].sockets.length;
                    continue;
                }

                member.events[e].sockets.splice(index, 1);
                sktNum += member.events[e].sockets.length;

                this.leave("eventroom" + member.events[e].eventID);
                this.leave("projectroom" + member.events[e].projectID);

                // Delete event and project record if it is connected to no socket
                if(member.events[e].sockets.length === 0)
                {
                    const eventID = member.events[e].eventID;
                    const projectID = member.events[e].projectID;
                    delete member.events[e];

                    const eventRoomMates = getMembersByRoomID(eventID, "event");
                    io.to("eventroom" + eventID).emit('event room update', eventRoomMates);

                    const projectRoomMates = getMembersByRoomID(projectID, "project");
                    io.to("projectroom" + projectID).emit('project room update', projectRoomMates);
                }
            }

            // Delete member if he is connected to no socket
            if(sktNum === 0)
                delete members["user" + member.memberID];
        }
    });

    socket.on('new member', function(data)
    {
        const sct = this;
        const member = getMemberByUserId("user" + data.memberID);

        if(member && member.authToken === data.aT)
        {
            const event = getMemberEvent(member, data.eventID);

            if(!_.isEmpty(event))
            {
                event.sockets.push(sct.id);
                sct.join("eventroom" + event.eventID);
                sct.join("projectroom" + event.projectID);

                const eventRoomMates = getMembersByRoomID(event.eventID, "event");
                io.to("eventroom" + event.eventID).emit('event room update', eventRoomMates);

                const projectRoomMates = getMembersByRoomID(event.projectID, "project");
                io.to("projectroom" + event.projectID).emit('project room update', projectRoomMates);

                // Add pair to checkers chat
                let checkPair;

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
        if(chatData.msg.trim() === "")
            return false;

        const member = getMemberBySocketId(this.id);

        if(member)
        {
            let msgObj, date;
            const client = {
                memberID: member.memberID,
                userName: member.userName,
                fullName: member.firstName + " " + member.lastName.charAt(0) + ".",
                //firstName: member.firstName,
                //lastName: member.lastName
            };
            const event = getMemberEvent(member, chatData.eventID);

            if(!_.isEmpty(event))
            {
                date = Date.now();
                msgObj = {
                    member: client,
                    msg: _.escape(chatData.msg),
                    date: date,
                    chatType: "chk"
                };

                if(chatData.chatType === "chk")
                {
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

                    const translator = getMemberByUserId("user" + id);

                    if(typeof translator != 'undefined')
                    {
                        const trEvent = getMemberEvent(translator, event.eventID);

                        if(!_.isEmpty(trEvent))
                        {
                            // Send message to co-translator/checker
                            for(const skt in trEvent.sockets)
                            {
                                io.to(trEvent.sockets[skt]).emit('chat message', msgObj);
                            }
                        }
                    }

                    // Send message to sender himself
                    for(const skt in event.sockets)
                    {
                        io.to(event.sockets[skt]).emit('chat message', msgObj);
                    }

                    clientRedis.ZADD("rooms:" + pairID, date, JSON.stringify(msgObj));
                }
                else if(chatData.chatType === "evnt")
                {
                    msgObj.chatType = "evnt";
                    io.to("eventroom" + event.eventID).emit('chat message', msgObj);

                    clientRedis.ZADD("rooms:event-" + event.eventID, date, JSON.stringify(msgObj));
                }
                else if(chatData.chatType === "proj")
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
        const member = getMemberBySocketId(this.id);

        if(member)
        {
            const event = getMemberEvent(member, data.eventID);

            if(!_.isEmpty(event))
            {
                let checkMember;
                let checkEvent;

                switch (data.type)
                {
                    case "checkDone":
                        checkMember = getMemberByUserId("user" + data.chkMemberID);

                        if(typeof checkMember != 'undefined')
                        {
                            checkEvent = getMemberEvent(checkMember, event.eventID);

                            if(!_.isEmpty(checkEvent))
                            {
                                // Send message to check member
                                for(const skt in checkEvent.sockets)
                                {
                                    io.to(checkEvent.sockets[skt]).emit('system message', {type: "checkDone"});
                                }
                            }
                        }
                        break;

                    case "comment":
                        const commentData = {
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
                        checkMember = getMemberByUserId("user" + data.chkMemberID);

                        if(typeof checkMember != 'undefined')
                        {
                            checkEvent = getMemberEvent(checkMember, event.eventID);

                            if(!_.isEmpty(checkEvent))
                            {
                                const keywordData = {
                                    type: "keyword",
                                    remove: _.escape(data.remove),
                                    verseID: _.escape(data.verseID),
                                    index: _.escape(data.index),
                                    text: _.escape(data.text)
                                };

                                // Send message to check member
                                for(const skt in checkEvent.sockets)
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
        const member = getMemberBySocketId(this.id);
        const eSteps = new EventSteps();

        if(member)
        {
            const event = getMemberEvent(member, data.eventID);

            if(!_.isEmpty(event))
            {
                let messageType = "";
                switch (data.step)
                {
                    case eSteps.PEER_REVIEW:
                    case eSteps.KEYWORD_CHECK:
                    case eSteps.CONTENT_REVIEW:
                    case eSteps.PEER_REVIEW_L2:
                    case eSteps.PEER_REVIEW_L3:
                        if((data.step === eSteps.KEYWORD_CHECK || data.step === eSteps.CONTENT_REVIEW)
                            && data.tMode !== "undefined"
                            && ["sun"].indexOf(data.tMode) > -1) break;

                        messageType = "checkEnter";
                        if(!data.isChecker)
                        {
                            const msgObj = {
                                excludes: [member.memberID],
                                anchor: "check:"+event.eventID+":"+member.memberID
                            };
                            io.to("eventroom" + event.eventID).emit('checking request', msgObj);
                        }
                        else
                        {
                            if(data.chkMemberID > 0)
                            {
                                const checkMember = getMemberByUserId("user" + data.chkMemberID);

                                if(typeof checkMember != 'undefined')
                                {
                                    const checkEvent = getMemberEvent(checkMember, event.eventID);

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
                                        for(const skt in checkEvent.sockets)
                                        {
                                            const name = member.firstName + " " + member.lastName.charAt(0) + ".";
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
        const member = getMemberBySocketId(this.id);

        if(member)
        {
            const event = getMemberEvent(member, data.eventID);

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

                const translator = getMemberByUserId("user" + id);

                if(typeof translator != 'undefined')
                {
                    const trEvent = getMemberEvent(translator, event.eventID);

                    if(!_.isEmpty(trEvent))
                    {
                        if(data.type === "gotUserMedia")
                        {
                            data.userName = member.userName;
                            data.memberID = member.memberID;
                        }

                        // Send message to co-translator/checker
                        for(const skt in trEvent.sockets)
                        {
                            io.to(trEvent.sockets[skt]).emit('videoCallMessage', data);
                        }
                    }
                }

                // Send message back to member sockets
                if(data.type === "gotUserMedia")
                {
                    for(const skt in event.sockets)
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
    const xhr = new XMLHttpRequest({
        pfx: null,
    });

    xhr.onreadystatechange = function()
    {
        if (this.readyState == this.DONE)
        {
            try
            {
                const response = JSON.parse(this.responseText);

                if(!_.isEmpty(response))
                {
                    const newEvent = new Event();
                    newEvent.eventID = data.eventID;
                    newEvent.projectID = data.projectID;
                    newEvent.sockets.push(sct.id);

                    // Add pair to checkers chat
                    let checkPair;

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
                        const newMember = new Member();
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

                    const eventRoomMates = getMembersByRoomID(data.eventID, "event");
                    io.to("eventroom" + data.eventID).emit('event room update', eventRoomMates);

                    const projectRoomMates = getMembersByRoomID(data.projectID, "project");
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

    xhr.open("GET", "https://odbtranslation.srv/members/rpc/auth/" + data.memberID + "/" + data.eventID + "/" + data.aT);
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

    for (const m in members)
    {
        for (const e in members[m].events)
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
    const roomMates = [];

    if(_.keys(members).length <= 0)
        return roomMates;

    for (const m in members)
    {
        if(_.keys(members[m].events).length <= 0)
            continue;

        for(const e in members[m].events)
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
                const tmpm = {};
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
    const event = {};

    if(!_.isEmpty(member))
    {
        for (const e in member.events)
        {
            if(member.events[e].eventID == eventID)
                return member.events[e];
        }
    }

    return event;
}

function sendSavedMessages(socket, event, checkPair)
{
    const since = Date.now() - 60 * 24 * 60 * 60 * 1000; // get messages within 60 days period

    if(checkPair !== "zero")
    {
        clientRedis.ZREMRANGEBYSCORE("rooms:" + checkPair, "-inf", since);
        clientRedis.ZRANGEBYSCORE("rooms:" + checkPair, since, "+inf", function(err, value) {
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
    clientRedis.ZRANGEBYSCORE("rooms:event-" + event.eventID, since, "+inf", function(err, value) {
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
    clientRedis.ZRANGEBYSCORE("rooms:project-" + event.projectID, since, "+inf", function(err, value) {
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
