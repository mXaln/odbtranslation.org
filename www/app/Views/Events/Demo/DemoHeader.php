<?php
use Helpers\Constants\EventSteps;
?>

<div id="translator_steps" class="open pray <?php echo isset($data["isCheckerPage"]) ? " is_checker_page" : "" ?>">
    <div id="tr_steps_hide" class="glyphicon glyphicon-chevron-left pray <?php echo isset($data["isCheckerPage"]) ? " is_checker_page" : "" ?>"></div>

    <ul class="steps_list">
        <li class="pray-step <?php echo $data["step"] == EventSteps::PRAY ? "active" : "" ?>">
            <a href="/events/demo/pray"><span><?php echo __("pray") ?></span></a>
        </li>

        <li class="consume-step <?php echo $data["step"] == EventSteps::CONSUME ? "active" : "" ?>">
            <a href="/events/demo/consume"><span><?php echo __("consume") ?></span></a>
        </li>

        <li class="discuss-step <?php echo $data["step"] == EventSteps::DISCUSS ? "active" : "" ?>">
            <a href="/events/demo/verbalize"><span><?php echo __("discuss") ?></span></a>
        </li>

        <li class="chunking-step <?php echo $data["step"] == EventSteps::CHUNKING ? "active" : "" ?>">
            <a href="/events/demo/prep_chunks"><span><?php echo __("chunking") ?></span></a>
        </li>

        <?php if($_COOKIE["demo_mode"] == "ol") : ?>
            <li class="blind-draft-step <?php echo $data["step"] == EventSteps::BLIND_DRAFT ? "active" : "" ?>">
                <a href="/events/demo/blind_draft"><span><?php echo __("blind-draft") ?></span></a>
            </li>
        <?php endif; ?>

        <li class="self-check-step <?php echo $data["step"] == EventSteps::SELF_CHECK ? "active" : "" ?>">
            <?php if($_COOKIE["demo_mode"] == "ol") : ?>
                <a href="/events/demo/self_check"><span><?php echo __("self-check") ?></span></a>
            <?php else: ?>
                <a href="/events/demo/draft"><span><?php echo __("self-check_gl") ?></span></a>
            <?php endif; ?>
        </li>

        <?php if(!isset($_COOKIE["demo_mode"]) || $_COOKIE["demo_mode"] == "gl"):?>
            <li class="self-check-step <?php echo $data["step"] == EventSteps::SELF_CHECK_FULL ? "active" : "" ?>">
                <a href="/events/demo/self_check_full"><span><?php echo __(EventSteps::SELF_CHECK)?></span></a>
            </li>
        <?php endif; ?>

        <li class="peer-review-step <?php echo $data["step"] == EventSteps::PEER_REVIEW ? "active" : "" ?>">
            <a href="/events/demo/peer_review"><span><?php echo __("peer-review") ?></span></a>
        </li>

        <li class="keyword-check-step <?php echo $data["step"] == EventSteps::KEYWORD_CHECK ? "active" : "" ?>">
            <a href="/events/demo/keyword_check"><span><?php echo __("keyword-check") ?></span></a>
        </li>

        <li class="content-review-step <?php echo $data["step"] == EventSteps::CONTENT_REVIEW ? "active" : "" ?>">
            <a href="/events/demo/content_review"><span><?php echo __("content-review") ?></span></a>
        </li>
    </ul>

    <div class="gl_ol_mode">
        <label><input type="radio" name="mode" value="gl" <?php echo !isset($_COOKIE["demo_mode"]) || $_COOKIE["demo_mode"] == "gl" ? "checked" : "" ?>> <?php echo __("gl_mode") ?></label>
        <br>
        <label><input type="radio" name="mode" value="ol" <?php echo $_COOKIE["demo_mode"] == "ol" ? "checked" : "" ?>> <?php echo __("ol_mode") ?></label>
    </div>
</div>

<script>
    var memberID = 0;
    var eventID = 0;
    var chkMemberID = 0;
    var step = '<?php echo $data["step"]; ?>';
</script>

<div style="position: fixed; right: 0;">

</div>

<div id="chat_container" class="closed">
    <div id="chat_new_msgs" class="chat_new_msgs"></div>
    <div id="chat_hide" class="glyphicon glyphicon-chevron-down"> <?php echo __("chat") ?></div>

    <div class="chat panel panel-info">
        <div class="chat_tabs panel-heading">
            <div class="row">
                <div id="p2p" class="col-sm-4 chat_tab active">
                    <div><?php echo __("partner_tab_title") ?></div>
                    <div class="missed"></div>
                </div>
                <div id="chk" class="col-sm-4 chat_tab">
                    <div><?php echo __("checking_tab_title") ?></div>
                    <div class="missed"></div>
                </div>
                <div id="evnt" class="col-sm-4 chat_tab">
                    <div><?php echo __("event_tab_title") ?></div>
                    <div class="missed"></div>
                </div>
                <div class="col-sm-4" style="text-align: right; padding: 2px 20px 0 0">
                    <button class="btn btn-success videoCallOpen videocall glyphicon glyphicon-facetime-video" title="<?php echo __("video_call") ?>"></button>
                    <button class="btn btn-success videoCallOpen audiocall glyphicon glyphicon-earphone" title="<?php echo __("audio_call") ?>"></button>
                </div>
            </div>
        </div>
        <ul id="p2p_messages" class="chat_msgs"><li class="message msg_my" data="7"><div class="msg_name">You</div><div data-original-title="01.07.2016, 18:22:38" class="msg_text" data-toggle="tooltip" data-placement="top" title="">Hi, let's translate chapter 1</div></li></ul>
        <ul id="chk_messages" class="chat_msgs"><li class="message msg_my" data="7"><div class="msg_name">You</div><div data-original-title="04.07.2016, 17:36:45" class="msg_text" data-toggle="tooltip" data-placement="top" title="">This is chat tab for checking dialog</div></li></ul>
        <ul id="evnt_messages" class="chat_msgs"><li class="message msg_other" data="16"><div class="msg_name">mSimpson</div><div data-original-title="30.06.2016, 18:38:09" class="msg_text" data-toggle="tooltip" data-placement="top" title="">Hi, this a test event message</div></li><li class="message msg_my" data="7"><div class="msg_name">You</div><div data-original-title="01.07.2016, 18:22:02" class="msg_text" data-toggle="tooltip" data-placement="top" title="">Hi, this a test event message 2</div></li></ul>
        <form action="" class="form-inline">
            <div class="form-group">
                <textarea style="overflow: hidden; word-wrap: break-word; resize: horizontal; height: 54px;" id="m" class="form-control"></textarea>
                <input id="chat_type" value="p2p" type="hidden">
            </div>
        </form>
    </div>

    <div class="members_online panel panel-info">
        <div class="panel-heading"><?php echo __("members_online_title") ?></div>
        <ul id="online" class="panel-body"><li>Gen2Pet</li><li>mpat1977</li><li class="mine">mSimpson (facilitator)</li></ul>
    </div>

    <div class="clear"></div>
</div>

<!-- Audio for missed chat messages -->
<audio id="missedMsg">
    <source src="<?php echo template_url("sounds/missed.ogg")?>" type="audio/ogg">
</audio>

<script src="<?php echo template_url("js/chat-plugin.js")?>"></script>

<script>
    (function($) {
        $("#chat_container").chat({
            step: step
        });

        $('[data-toggle="tooltip"]').tooltip();
    }(jQuery));
</script>

<?php echo isset($page) ? $page : "" ?>