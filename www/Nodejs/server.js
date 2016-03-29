var app = require('express')(),
    http = require('http').Server(app),
    io = require('socket.io')(http),
    util = require("util"),
    _ = require("underscore"),
    XMLHttpRequest = require("xmlhttprequest-ssl").XMLHttpRequest,
    Member = require("./Member").Member,
    Event = require("./Event").Event;

var members = [];

io.on('connection', function(socket)
{
    console.log('a user connected: %s', socket.id);

    socket.on('disconnect', function()
    {
        console.log('user disconnected');

        var member = getMemberBySocketId(this.id);

        util.log(this.id);

        if(member)
        {
            var sktNum = 0;

            for(var evnt in member.events)
            {
                member.events[evnt].sockets.splice(member.events[evnt].sockets.indexOf(this.id), 1);
                this.leave("room" + member.events[evnt].eventID);

                sktNum += member.events[evnt].sockets.length;

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
                util.log("old event: " + sct.id);

                event.sockets.push(sct.id);
                sct.join("room" + event.eventID);

                var roomMates = getMembersByRoomID(event.eventID);
                io.to("room" + event.eventID).emit('room update', roomMates);
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
        util.log(chatData);
        if(chatData.msg.trim() == "")
            return false;

        var member = getMemberBySocketId(this.id);

        util.log(this.id);

        if(member)
        {
            var client = {
                memberID: member.memberID,
                userName: member.userName,
                firstName: member.firstName,
                lastName: member.lastName
            };

            var event = getMemberEvent(member, chatData.eventID);
            util.log(event);
            if(!_.isEmpty(event))
            {
                if(chatData.chatType == "p2p")
                {
                    var coTranslator = getMemberByUserId("user" + event.cotrMemberID);
                    var cotrEvent = getMemberEvent(coTranslator, event.eventID);

                    for(var skt in cotrEvent.sockets)
                    {
                        io.sockets.socket(cotrEvent.sockets[skt]).emit('chat message', {
                            member : client,
                            msg : _.escape(chatData.msg),
                            chatType: "p2p"
                        });
                    }
                }
                else
                {
                    io.to("room" + event.eventID).emit('chat message', {
                        member : client,
                        msg : _.escape(chatData.msg),
                        chatType: "evnt"
                    });
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
                    if(_.isEmpty(member))
                    {
                        var newMember = new Member();
                        newMember.memberID = response.memberID;
                        newMember.userName = response.userName;
                        newMember.firstName = response.firstName;
                        newMember.lastName = response.lastName;
                        //newMember.userType = response.userType;
                        newMember.authToken = data.aT;

                        var newEvent = new Event();
                        newEvent.eventID = data.eventID;
                        newEvent.cotrMemberID = response.cotrMemberID;
                        newEvent.sockets.push(sct.id);
                        newMember.events.push(newEvent);

                        members["user" + newMember.memberID] = newMember;
                    }
                    else
                    {
                        var newEvent = new Event();
                        newEvent.eventID = data.eventID;
                        newEvent.cotrMemberID = response.cotrMemberID;
                        newEvent.sockets.push(sct.id);
                        member.events.push(newEvent);

                        util.log(member);
                    }
                    util.log("new member/event: " + sct.id);
                    sct.join("room" + data.eventID);

                    var roomMates = getMembersByRoomID(data.eventID);
                    io.to("room" + data.eventID).emit('room update', roomMates);
                }
                else
                {
                    // Broadcast to user to logout
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
        return undefined;

    var member = _.findKey(members, function(object) {
        for (var evnt in object.events)
        {
            util.log(object.events[evnt].sockets);
            return object.events[evnt].sockets.indexOf(socketID) > -1;
        }
    });

    return members[member];
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