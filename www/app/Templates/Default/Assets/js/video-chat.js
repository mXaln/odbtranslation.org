/**
 * Created by mXaln on 07.09.2016.
 */
'use strict';

var isInitiator = false;
var isStarted = false;
var localStream;
var remoteStream;
var pc;
var time;
var timeoutInterval,
    timeoutDelay = 30000, // Call busy timeout
    timeoutClose,
    timeoutCloseDelay = 2000; // timeout of closing video window with error
var constraints = {audio: true, video: false};
var calleeID = 0;
var calleeName = "";
var callAnswered = false;

$(".videoCallOpen").click(openVideoDialog);
$(".video-chat-close").click(closeVideoDialog);
$("#hangupButton").click(onHangupClick);
$("#answerButton").click(onAnswerClick);
$("#cameraButton").click(manageCamera);
$("#micButton").click(manageMic);

var iceConfig = {
    'iceServers': [
        { "url": "stun:v-mast.com" },
        { "url": ["turn:v-mast.com"], "username": turnUsername, "credential": turnPassword}
    ]
};

var localVideo = $("#localVideo")[0];
var remoteVideo = $('#remoteVideo')[0];
var callLog = $('#callLog');

var callin = document.getElementById('callin');
callin.loop = true;
var callout = document.getElementById('callout');
callout.loop = true;

$(document).ready(function () {
    socket.on('videoCallMessage', function(message) {
        console.log('Client received message:', message);
        onVideoCallMessage(message);
    });

    socket.on('callAnswered', function(message) {
        if(!isInitiator && !callAnswered)
        {
            closeVideoDialog(false);
        }
    });

    window.onbeforeunload = function() {
        sendMessage({
            type: 'bye',
            callType: $("#chat_type").val(),
            eventID: eventID,
            memberID: memberID,
            isChecker: isChecker,
            chkMemberID: chkMemberID
        });
    };

    $(".video_chat_container").draggable({
        snap: "window",
        cursor: "move",
        containment: "window",
        scroll: false,
    }).resizable({
        containment: "body",
        aspectRatio: true,
        maxHeight: 640,
        minHeight: 300,
        resize: function( event, ui ) {
            $(".video").css("min-height", ui.size.height - 40);
            $(".video").css("max-width", ui.size.width);
            $("#remoteVideo").attr("height", $(".video").height());
        }
    });
});


// ----------------------------- Functions --------------------------------- //
function sendMessage(message)
{
    console.log('Client sending message: ', message);
    socket.emit('videoCallMessage', message);
}

function maybeStart() {
    console.log('>>>>>>> maybeStart() ', isStarted, localStream);
    if (!isStarted && typeof localStream !== 'undefined') {
        console.log('>>>>>> creating peer connection');
        createPeerConnection();
        pc.addStream(localStream);
        isStarted = true;
    }
}

function createPeerConnection() {
    try {
        pc = new RTCPeerConnection(iceConfig);
        pc.onicecandidate = handleIceCandidate;
        pc.onaddstream = handleRemoteStreamAdded;
        pc.onremovestream = handleRemoteStreamRemoved;
        console.log('Created RTCPeerConnnection');
    } catch (e) {
        callLog.html('Failed to create PeerConnection, exception: ' + e.message);
        timeoutClose = setTimeout(function () {
            closeVideoDialog();
        }, timeoutCloseDelay);
        return;
    }
}

function doCall() {
    $("#hangupButton").prop("disabled", false);
    $("#answerButton").hide().prop("disabled", true);

    sendMessage({
        type: 'gotUserMedia',
        callType: $("#chat_type").val(),
        eventID: eventID,
        chkMemberID: chkMemberID,
        isChecker: isChecker,
        isIncomming: isInitiator,
        isVideoCall: constraints.video
    });
}

function doAnswer() {
    console.log('Sending onAnswerClick to peer.');
    pc.createAnswer().then(
        setLocalAndSendMessage,
        onCreateSessionDescriptionError
    );
}

function setLocalAndSendMessage(sessionDescription) {
    var msg = {
        type: "offer",
        callType: $("#chat_type").val(),
        eventID: eventID,
        chkMemberID: chkMemberID,
        sessionDescription: sessionDescription
    };

    pc.setLocalDescription(msg.sessionDescription);
    console.log('setLocalAndSendMessage sending message', msg);
    sendMessage(msg);
}

// ----------------------------- Handler functions ----------------------------- //
function openVideoDialog() {
    time = new Date().getTime();

    constraints.video = $(this).hasClass("videocall");

    timeoutInterval = setInterval(function () {
        var now = new Date().getTime();
        if((now - time) > timeoutDelay)
        {
            callLog.html(Language.call_timeout);
            callout.pause();
            $("#hangupButton").prop("disabled", true);
            timeoutClose = setTimeout(function () {
                closeVideoDialog();
            }, timeoutCloseDelay);
        }
    }, 1000);

    $(".video_chat_container").show();
    isInitiator = true;

    $("#answerButton").hide();
    $("#cameraButton").addClass("btn-success").removeClass("btn-danger").hide();
    $("#micButton").addClass("btn-success glyphicon-volume-up").removeClass("btn-danger glyphicon-volume-off").hide();
    $(".videoCallOpen").prop("disabled", true);

    callLog.html(Language.calling);
    callout.currentTime = 0;
    callout.play();

    navigator.mediaDevices.getUserMedia(constraints)
        .then(gotStream)
        .catch(function(e) {
            callout.pause();
            callLog.html(e.name + ": "+ e.message);
            timeoutClose = setTimeout(function () {
                closeVideoDialog();
            }, timeoutCloseDelay);
        });
}

function closeVideoDialog(bye) {
    bye = typeof bye != "undefined" ? bye : true;

    if(!$(".video_chat_container").is(":visible")) return;

    $(".video_chat_container").hide();
    $(".videoCallOpen").prop("disabled", false);
    callLog.html("");
    isStarted = false;
    isInitiator = false;
    callAnswered = false;
    calleeID = 0;
    calleeName = "";

    clearInterval(timeoutInterval);
    clearTimeout(timeoutClose);

    callout.pause();
    callin.pause();

    if(typeof pc != "undefined" && pc != null)
    {
        pc.close();
        pc = null;
    }

    if(typeof localStream != "undefined")
    {
        if(localStream.getVideoTracks().length > 0)
            localStream.getVideoTracks()[0].stop();
        if(localStream.getAudioTracks().length > 0)
            localStream.getAudioTracks()[0].stop();

        localVideo.src = "";
    }

    if(typeof remoteStream != "undefined")
    {
        if(remoteStream.getVideoTracks().length > 0)
            remoteStream.getVideoTracks()[0].stop();
        if(remoteStream.getAudioTracks().length > 0)
            remoteStream.getAudioTracks()[0].stop();

        remoteVideo.src = "";
    }

    if(bye)
    {
        sendMessage({
            type: 'bye',
            callType: $("#chat_type").val(),
            eventID: eventID,
            memberID: memberID,
            isChecker: isChecker,
            chkMemberID: chkMemberID
        });
    }
}

function onAnswerClick() {
    navigator.mediaDevices.getUserMedia(constraints)
        .then(gotStream)
        .catch(function(e) {
            callin.pause();
            callLog.html(e.name + ": "+ e.message);
            timeoutClose = setTimeout(function () {
                closeVideoDialog();
            }, timeoutCloseDelay);
        });
}


function onHangupClick() {
    console.log('Hanging up.');
    closeVideoDialog();
}

function manageCamera() {
    if(typeof localStream != "undefined")
    {
        if(localStream.getVideoTracks().length > 0)
        {
            var enabled = !localStream.getVideoTracks()[0].enabled;
            localStream.getVideoTracks()[0].enabled = enabled;
            if(enabled)
                $("#cameraButton").addClass("btn-success").removeClass("btn-danger");
            else
                $("#cameraButton").removeClass("btn-success").addClass("btn-danger");
        }
    }
}

function manageMic() {
    if(typeof localStream != "undefined")
    {
        if(localStream.getAudioTracks().length > 0)
        {
            var enabled = !localStream.getAudioTracks()[0].enabled;
            localStream.getAudioTracks()[0].enabled = enabled;
            if(enabled)
                $("#micButton").addClass("btn-success glyphicon-volume-up").removeClass("btn-danger glyphicon-volume-off");
            else
                $("#micButton").removeClass("btn-success glyphicon-volume-up").addClass("btn-danger glyphicon-volume-off");
        }

    }
}

function gotStream(stream)
{
    console.log('Adding local stream.');
    localVideo.src = window.URL.createObjectURL(stream);
    localStream = stream;

    if(!isInitiator)
    {
        callAnswered = true;

        sendMessage({
            type: 'gotUserMedia',
            callType: $("#chat_type").val(),
            eventID: eventID,
            chkMemberID: chkMemberID,
            isChecker: isChecker,
            isIncomming: isInitiator
        });

        callin.pause();
        maybeStart();
    }
    else
    {
        calleeID = chkMemberID;
        doCall();
    }
}

function onVideoCallMessage(message)
{
    //if((step == eventSteps.KEYWORD_CHECK || step == eventSteps.CONTENT_REVIEW) &&
    //   message.callType != "chk") return false;

    if(message.isChecker && message.isChecker == isChecker) return false; // Do not accept call from checker if you are checker too

    switch(message.type)
    {
        case "gotUserMedia":
            calleeID = message.memberID;
            calleeName = message.userName;

            if(message.isIncomming) // Incomming call
            {
                $(".video_chat_container").show();
                $(".videoCallOpen").prop("disabled", true);
                callLog.html(Language.incomming_call + " " + calleeName);
                $("#answerButton").show().prop("disabled", false);
                $("#hangupButton").prop("disabled", false);
                $("#cameraButton").addClass("btn-success").removeClass("btn-danger").hide();
                $("#micButton").addClass("btn-success glyphicon-volume-up").removeClass("btn-danger glyphicon-volume-off").hide();
                isInitiator = false;

                callin.currentTime = 0;
                callin.play();

                constraints.video = message.isVideoCall;

                time = new Date().getTime();
                timeoutInterval = setInterval(function () {
                    var now = new Date().getTime();
                    if((now - time) > timeoutDelay)
                    {
                        callLog.html(Language.call_timeout);
                        callout.pause();
                        $("#hangupButton").prop("disabled", true);
                        timeoutClose = setTimeout(function () {
                            closeVideoDialog();
                        }, timeoutCloseDelay);
                    }
                }, 1000);
            }
            else // Peer answered
            {
                maybeStart();
                pc.createOffer(setLocalAndSendMessage, handleCreateOfferError);
            }
            break;

        case "offer":
            if(!isInitiator && !callAnswered) break;

            pc.setRemoteDescription(new RTCSessionDescription(message.sessionDescription));
            doAnswer();
            break;

        case "answer":
            if(!isInitiator && !callAnswered) break;

            if(isStarted)
            {
                pc.setRemoteDescription(new RTCSessionDescription(message.sessionDescription));
            }
            break;

        case "candidate":
            if(!isInitiator && !callAnswered) break;

            if(isStarted)
            {
                var candidate = new RTCIceCandidate({
                    sdpMLineIndex: message.label,
                    candidate: message.candidate
                });
                pc.addIceCandidate(candidate);
            }
            break;

        case "bye":
            if(!isInitiator && !callAnswered) break;

            if(calleeID == message.memberID)
                closeVideoDialog();
            break;
    }
}

function handleIceCandidate(event) {
    console.log('icecandidate event: ', event);
    if (event.candidate) {
        sendMessage({
            type: 'candidate',
            label: event.candidate.sdpMLineIndex,
            id: event.candidate.sdpMid,
            candidate: event.candidate.candidate,
            callType: $("#chat_type").val(),
            eventID: eventID,
            isChecker: isChecker,
            chkMemberID: chkMemberID
        });
    } else {
        console.log('End of candidates.');
    }
}

function handleRemoteStreamAdded(event) {
    console.log('Remote stream added.');
    remoteVideo.src = window.URL.createObjectURL(event.stream);
    remoteStream = event.stream;

    $("#answerButton").hide().prop("disabled", true);
    if(constraints.video) $("#cameraButton").show();
    $("#micButton").show();
    callLog.html("");
    if(!constraints.video) callLog.html(Language.audio_chat_with + " " + calleeName);

    callout.pause();
    clearInterval(timeoutInterval);
    clearTimeout(timeoutClose);
}

function handleRemoteStreamRemoved(event) {
    callout.pause();
    callLog.html('Remote stream removed. Event: ', event);
    timeoutClose = setTimeout(function () {
        closeVideoDialog();
    }, timeoutCloseDelay);
}

function handleCreateOfferError(event) {
    callout.pause();
    callLog.html('createOffer() error: ', event);
    timeoutClose = setTimeout(function () {
        closeVideoDialog();
    }, timeoutCloseDelay);
}

function onCreateSessionDescriptionError(error) {
    callout.pause();
    callLog.html('Failed to create session description: ' + error.toString());
    timeoutClose = setTimeout(function () {
        closeVideoDialog();
    }, timeoutCloseDelay);
}