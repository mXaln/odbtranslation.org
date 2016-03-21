var app = require('express')(),
    http = require('http').Server(app),
    io = require('socket.io')(http),
    util = require("util"),
    _ = require("underscore"),
    XMLHttpRequest = require("xmlhttprequest-ssl").XMLHttpRequest,
    Member = require("./Member").Member;

var members = [];

io.on('connection', function(socket)
{
    console.log('a user connected: %s', socket.id);

    socket.on('disconnect', function()
    {
        console.log('user disconnected');

        var member = getMemberBySocketId(this.id);

        if(member)
        {
            member.sockets.splice(member.sockets.indexOf(this.id), 1);

            this.leave("room" + member.tID);

            // Delete member if he is connected to no socket
            if(member.sockets.length == 0)
            {
                var tid = member.tID;
                delete members[member.userName];

                var roomMates = getMembersByRoomID(tid);
                io.to("room" + tid).emit('room update', roomMates);
            }
        }
    });

    socket.on('new member', function(data)
    {
        var sct = this;
        var member = getMemberByUserId("user" + data.memberID);

        if(member && member.authToken == data.aT)
        {
            member.sockets.push(sct.id);
            sct.join("room" + member.tID);

            var roomMates = getMembersByRoomID(member.tID);
            io.to("room" + member.tID).emit('room update', roomMates);

            return;
        }

        var xhr = new XMLHttpRequest();

        xhr.onreadystatechange = function()
        {
            if (this.readyState == this.DONE)
            {
                try
                {
                    var response = JSON.parse(this.responseText);

                    var newMember = new Member();
                    newMember.memberID = response.memberID;
                    newMember.userName = "user" + newMember.memberID;
                    newMember.firstName = response.firstName;
                    newMember.lastName = response.lastName;
                    newMember.userType = response.userType;
                    newMember.authToken = data.aT;
                    newMember.tID = data.tID;
                    newMember.sockets.push(sct.id);

                    members[newMember.userName] = newMember;

                    sct.join("room" + newMember.tID);

                    var roomMates = getMembersByRoomID(newMember.tID);
                    io.to("room" + newMember.tID).emit('room update', roomMates);
                }
                catch(err)
                {
                    util.log(err);
                }
            }
        };

        xhr.open("GET", "http://v-mast.mvc/members/rpc/auth/" + data.memberID + "/" + data.tID + "/" + data.aT);
        xhr.send();
    });

    socket.on('chat message', function(msg)
    {
        if(msg.trim() == "")
            return false;

        var member = getMemberBySocketId(this.id);

        if(member)
        {
            var client = {
                memberID: member.memberID,
                firstName: member.firstName,
                lastName: member.lastName
            };

            io.to("room" + member.tID).emit('chat message', {
                member : client,
                msg : _.escape(msg)
            });
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
        return object.sockets.indexOf(socketID) > -1;
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
        if(members[m].tID == roomID)
        {
            var tmpm = {};
            tmpm.memberID = members[m].memberID;
            tmpm.firstName = members[m].firstName;
            tmpm.lastName = members[m].lastName;
            tmpm.userType = members[m].userType;

            roomMates.push(tmpm);
        }
    }

    return roomMates;
}