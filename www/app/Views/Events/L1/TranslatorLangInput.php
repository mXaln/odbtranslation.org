<?php
use Helpers\Constants\EventSteps;
use Helpers\Session;
use Shared\Legacy\Error;

echo Error::display(@$error);

if(isset($data["success"]))
    echo Error::display($data["success"], "alert alert-success");

if(!empty($data["event"]) && !isset($data["error"]) && $data["event"][0]->step != EventSteps::FINISHED):
?>

<noscript>
    <div class="noscript">
        <?php echo __("noscript_message") ?>
    </div>
</noscript>

<div id="translator_steps" class="open <?php echo $data["event"][0]->step ?>">
    <div id="tr_steps_hide" class="glyphicon glyphicon-chevron-left <?php echo $data["event"][0]->step ?>"></div>

    <ul class="steps_list">
        <li class="pray-step <?php echo $data["event"][0]->step == EventSteps::PRAY ? "active" : "" ?>">
            <span><?php echo __(EventSteps::PRAY)?></span>
        </li>
        
        <li class="content-review-step <?php echo $data["event"][0]->step == EventSteps::MULTI_DRAFT ? "active" : "" ?>">
            <span><?php echo __(EventSteps::MULTI_DRAFT."_lang_input")?></span>
        </li>

        <li class="self-check-step <?php echo $data["event"][0]->step == EventSteps::SELF_CHECK ? "active" : "" ?>">
            <span><?php echo __(EventSteps::SELF_CHECK)?></span>
        </li>
    </ul>
</div>

<script>
    var memberID = <?php echo Session::get('memberID') ;?>;
    var eventID = <?php echo $data["event"][0]->eventID; ?>;
    var projectID = <?php echo $data["event"][0]->projectID; ?>;
    var myChapter = <?php echo $data["event"][0]->currentChapter; ?>;
    var myChunk = <?php echo $data["event"][0]->currentChunk; ?>;
    var chkMemberID = <?php echo isset($data["event"][0]->myMemberID) ? $data["event"][0]->checkerID : $data["event"][0]->memberID; ?>;
    var isChecker = false;
    var aT = '<?php echo Session::get('authToken'); ?>';
    var step = '<?php echo $data["event"][0]->step; ?>';
    var tMode = '<?php echo $data["event"][0]->bookProject ?>';
    var isAdmin = false;
    var disableChat = false;
    var turnUsername = '<?php echo isset($data["turn"]) ? $data["turn"][0] : "" ?>';
    var turnPassword = '<?php echo isset($data["turn"]) ? $data["turn"][1] : "" ?>';

</script>

<div id="chat_container" class="closed">
    <div id="chat_new_msgs" class="chat_new_msgs"></div>
    <div id="chat_hide" class="glyphicon glyphicon-chevron-left"> <?php echo __("chat") ?></div>

    <div class="chat panel panel-info">
        <div class="chat_tabs panel-heading">
            <div class="row">
                <div id="chk" class="col-sm-4 chat_tab">
                    <div class="chk_title">
                        <?php
                        echo isset($data["event"][0]->checkerFName) && $data["event"][0]->checkerFName !== null ?
                            $data["event"][0]->checkerFName . " " . mb_substr($data["event"][0]->checkerLName, 0, 1)."." :
                                (isset($data["event"][0]->firstName) && $data["event"][0]->firstName !== null ?
                                    $data["event"][0]->firstName . " " . mb_substr($data["event"][0]->lastName, 0, 1)."." : __("not_available"))
                        ?>
                    </div>
                    <div class="missed"></div>
                </div>
                <div id="evnt" class="col-sm-2 chat_tab active">
                    <div><?php echo __("event_tab_title") ?></div>
                    <div class="missed"></div>
                </div>
                <div id="proj" class="col-sm-2 chat_tab">
                    <div><?php echo __("project_tab_title") ?></div>
                    <div class="missed"></div>
                </div>
                <div class="col-sm-4" style="text-align: right; float: right; padding: 2px 20px 0 0">
                    <div class="<?php echo $data["event"][0]->checkerID <= 0 ? "videoBtnHide" : "" ?>">
                        <button class="btn btn-success videoCallOpen videocall glyphicon glyphicon-facetime-video" title="<?php echo __("video_call") ?>"></button>
                        <button class="btn btn-success videoCallOpen audiocall glyphicon glyphicon-earphone" title="<?php echo __("audio_call") ?>"></button>
                    </div>
                </div>
            </div>
        </div>
        <ul id="chk_messages" class="chat_msgs"></ul>
        <ul id="evnt_messages" class="chat_msgs"></ul>
        <ul id="proj_messages" class="chat_msgs"></ul>
        <form action="" class="form-inline">
            <div class="form-group">
                <textarea id="m" class="form-control"></textarea>
                <input type="hidden" id="chat_type" value="evnt" />
            </div>
        </form>
    </div>

    <div class="members_online panel panel-info">
        <div class="panel-heading"><?php echo __("members_online_title") ?></div>
        <ul id="online" class="panel-body"></ul>
    </div>

    <div class="clear"></div>
</div>

<div class="video_chat_container">
    <div class="video-chat-close glyphicon glyphicon-remove"></div>
    <div class="video_chat panel panel-info">
        <div class="panel-heading">
            <h1 class="panel-title"><?php echo __("video_call_title")?><span></span></h1>
            <span class="video-chat-close glyphicon glyphicon-remove"></span>
        </div>
        <div class="video">
            <video id="localVideo" muted autoplay width="160"></video>
            <video id="remoteVideo" autoplay ></video>

            <div class="buttons">
                <button class="btn btn-primary glyphicon glyphicon-facetime-video" id="cameraButton" title="<?php echo __("turn_off_camera") ?>"></button>
                <button class="btn btn-primary glyphicon glyphicon-volume-up" id="micButton" title="<?php echo __("mute_mic") ?>"></button>
                <button class="btn btn-success glyphicon glyphicon-earphone" id="answerButton" disabled="disabled" title="<?php echo __("answer_call") ?>"></button>
                <button class="btn btn-danger glyphicon glyphicon-earphone" id="hangupButton" disabled="disabled" title="<?php echo __("hangup") ?>"></button>
            </div>

            <div id="callLog"></div>
        </div>
    </div>
</div>

<!-- Audio for missed chat messages -->
<audio id="missedMsg">
    <source src="<?php echo template_url("sounds/missed.ogg")?>" type="audio/ogg" />
</audio>

<!-- Audio for notifications -->
<audio id="notif">
    <source src="<?php echo template_url("sounds/notif.ogg")?>" type="audio/ogg" />
</audio>

<!-- Audio for video calls -->
<audio id="callin">
    <source src="<?php echo template_url("sounds/callin.ogg")?>" type="audio/ogg" />
</audio>

<audio id="callout">
    <source src="<?php echo template_url("sounds/callout.ogg")?>" type="audio/ogg" />
</audio>

<script src="<?php echo template_url("js/socket.io-1.4.5.js")?>"></script>
<script src="<?php echo template_url("js/chat-plugin.js?7")?>"></script>
<script src="<?php echo template_url("js/socket.js?12")?>"></script>
<script src="<?php echo template_url("js/adapter-latest.js")?>"></script>
<script src="<?php echo template_url("js/video-chat.js?1")?>"></script>

<?php else:?>

<input type="hidden" id="evnt_state_checker" value="<?php echo isset($data["error"]) && $data["error"] === true ? "error" : "" ?>">
<input type="hidden" id="evntid" value="<?php echo !empty($data["event"]) && $data["event"][0]->eventID ?>">

<?php endif; ?>

<?php echo isset($page) ? $page : "" ?>
