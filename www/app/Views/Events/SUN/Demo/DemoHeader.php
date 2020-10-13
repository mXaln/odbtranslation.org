<?php
use Helpers\Constants\EventSteps;
?>

<div id="translator_steps" class="open pray <?php echo $data["isCheckerPage"] ? " is_checker_page" : "" ?>">
    <div id="tr_steps_hide" class="glyphicon glyphicon-chevron-left pray <?php echo $data["isCheckerPage"] ? " is_checker_page" : "" ?>"></div>

    <ul class="steps_list">
        <li class="pray-step <?php echo $data["step"] == EventSteps::PRAY ? "active" : "" ?>">
            <a href="/events/demo-sun/pray"><span><?php echo __(EventSteps::PRAY)?></span></a>
        </li>

        <li class="consume-step <?php echo $data["step"] == EventSteps::CONSUME ? "active" : "" ?>">
            <a href="/events/demo-sun/consume"><span><?php echo __(EventSteps::CONSUME)?></span></a>
        </li>

        <li class="chunking-step <?php echo $data["step"] == EventSteps::CHUNKING ? "active" : "" ?>">
            <a href="/events/demo-sun/chunking"><span><?php echo __(EventSteps::CHUNKING . "_sun")?></span></a>
        </li>

        <li class="rearrange-step <?php echo $data["step"] == EventSteps::REARRANGE ? "active" : "" ?>">
            <a href="/events/demo-sun/rearrange"><span><?php echo __(EventSteps::REARRANGE)?></span></a>
        </li>

        <li class="symbol-draft-step <?php echo $data["step"] == EventSteps::SYMBOL_DRAFT ? "active" : "" ?>">
            <a href="/events/demo-sun/symbol-draft"><span><?php echo __(EventSteps::SYMBOL_DRAFT)?></span></a>
        </li>

        <li class="self-check-step <?php echo $data["step"] == EventSteps::SELF_CHECK ? "active" : "" ?>">
            <a href="/events/demo-sun/self-check"><span><?php echo __(EventSteps::SELF_CHECK)?></span></a>
        </li>

        <li class="theo-check-step <?php echo $data["step"] == EventSteps::THEO_CHECK ? "active" : "" ?>">
            <a href="/events/demo-sun/theo_check_checker"><span><?php echo __(EventSteps::THEO_CHECK)?></span></a>
        </li>

        <li class="content-review-step <?php echo $data["step"] == EventSteps::CONTENT_REVIEW ? "active" : "" ?>">
            <a href="/events/demo-sun/content_review_checker"><span><?php echo __(EventSteps::CONTENT_REVIEW)?></span></a>
        </li>

        <li class="final-review-step <?php echo $data["step"] == EventSteps::FINAL_REVIEW ? "active" : "" ?>">
            <a href="/events/demo-sun/verse-markers"><span><?php echo __(EventSteps::FINAL_REVIEW)?></span></a>
        </li>
    </ul>
</div>

<?php
$isCheckPage = $data["step"] == EventSteps::THEO_CHECK || $data["step"] == EventSteps::CONTENT_REVIEW;
?>

<script>
    var memberID = 0;
    var eventID = 0;
    var chkMemberID = <?php echo $isCheckPage? "1" : "0"; ?>;
    var step = '<?php echo $data["step"]; ?>';
    var isDemo = true;
    var myChapter = 2;
    var tMode = "sun";
</script>

<div style="position: fixed; right: 0;">

</div>

<div class="video_container">
    <div class="video_popup">
        <div class="video-close glyphicon glyphicon-remove"></div>

        <iframe id="demo-player" width="900" height="450"
                src="" frameborder="0" allowfullscreen></iframe>

        <script>
            var tag = document.createElement('script');
            tag.id = 'iframe-demo';
            tag.src = 'https://www.youtube.com/iframe_api';
            var firstScriptTag = document.getElementsByTagName('script')[0];
            firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

            var player;
            function onYouTubeIframeAPIReady() {
                player = new YT.Player('demo-player');
            }
        </script>
    </div>
</div>

<div id="chat_container" class="closed">
    <div id="chat_new_msgs" class="chat_new_msgs"></div>
    <div id="chat_hide" class="glyphicon glyphicon-chevron-down"> <?php echo __("chat") ?></div>

    <div class="chat panel panel-info">
        <div class="chat_tabs panel-heading">
            <div class="row">
                <div id="chk" class="col-sm-4 chat_tab">
                    <div>Marge S.</div>
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
                <div class="col-sm-4" style="text-align: right; padding: 2px 20px 0 0">
                    <div class="<?php echo !$isCheckPage ? "videoBtnHide" : "" ?>">
                        <button class="btn btn-success videoCallOpen videocall glyphicon glyphicon-facetime-video" title="<?php echo __("video_call") ?>"></button>
                        <button class="btn btn-success videoCallOpen audiocall glyphicon glyphicon-earphone" title="<?php echo __("audio_call") ?>"></button>
                    </div>
                </div>
            </div>
        </div>
        <ul id="chk_messages" class="chat_msgs">
            <li class="message msg_my" data="7">
                <div class="msg_name">You</div>
                <div data-original-title="04.07.2016, 17:36:45" class="msg_text" data-toggle="tooltip" data-placement="top" title="">This is chat tab for checking dialog</div>
            </li>
        </ul>
        <ul id="evnt_messages" class="chat_msgs">
            <li class="message msg_other" data="16">
                <div class="msg_name">Marge S.</div>
                <div data-original-title="30.06.2016, 18:38:09" class="msg_text" data-toggle="tooltip" data-placement="top" title="">Hi, this a test event message</div>
            </li>
            <li class="message msg_my" data="7">
                <div class="msg_name">You</div>
                <div data-original-title="01.07.2016, 18:22:02" class="msg_text" data-toggle="tooltip" data-placement="top" title="">Hi, this a test event message 2</div>
            </li>
        </ul>
        <ul id="proj_messages" class="chat_msgs">
            <li class="message msg_other" data="16">
                <div class="msg_name">Marge S.</div>
                <div data-original-title="30.06.2016, 18:38:09" class="msg_text" data-toggle="tooltip" data-placement="top" title="">Hi, this a test project message</div>
            </li>
            <li class="message msg_my" data="7">
                <div class="msg_name">You</div>
                <div data-original-title="01.07.2016, 18:22:02" class="msg_text" data-toggle="tooltip" data-placement="top" title="">Hi, this a test project message 2</div>
            </li>
        </ul>
        <form action="" class="form-inline">
            <div class="form-group">
                <textarea style="overflow: hidden; word-wrap: break-word; resize: horizontal; height: 54px;" id="m" class="form-control"></textarea>
                <input id="chat_type" value="evnt" type="hidden">
            </div>
        </form>
    </div>

    <div class="members_online panel panel-info">
        <div class="panel-heading"><?php echo __("members_online_title") ?></div>
        <ul id="online" class="panel-body">
            <li>Genry M.</li>
            <li>Mark P.</li>
            <li class="mine">Marge S. (<?php echo __("facilitator"); ?>)</li>
        </ul>
    </div>

    <div class="clear"></div>
</div>

<!-- Audio for missed chat messages -->
<audio id="missedMsg">
    <source src="<?php echo template_url("sounds/missed.ogg")?>" type="audio/ogg">
</audio>

<script src="<?php echo template_url("js/chat-plugin.js?5")?>"></script>

<script>
    (function($) {
        $("#chat_container").chat({
            step: step,
            chkMemberID: chkMemberID
        });

        $('[data-toggle="tooltip"]').tooltip();
    }(jQuery));
</script>

<?php echo isset($page) ? $page : "" ?>


<?php if($isCheckPage): ?>
<style>
    .buttons_spec {
        position: absolute;
        top: 97px;
        left: 930px;
        text-align: center;
        border: 1px solid #ccc;
        background-color: white;
        padding: 5px 10px;
        z-index: 102;
    }
    
    .buttons_spec.unlinked {
        position: fixed;
        left: calc(50% + 345px);
        top: 20px;
    }
</style>

<!--<div class="buttons_spec">
    <button class="spec_char" data="D̃">D̃</button>
    <button class="spec_char" data="d̃">d̃</button>&nbsp;&nbsp;
    <button class="spec_char" data="Õ">Õ</button>
    <button class="spec_char" data="õ">õ</button>&nbsp;&nbsp;
    <button class="spec_char" data="T̃">T̃</button>
    <button class="spec_char" data="t̃">t̃</button><br>
    <button class="spec_char" data="Ṽ">Ṽ</button>
    <button class="spec_char" data="ṽ">ṽ</button>&nbsp;&nbsp;
    <button class="spec_char" data="W̃">W̃</button>
    <button class="spec_char" data="w̃">w̃</button>
</div>-->

<script>
    $(document).ready(function () {
        var focused;
        
        $("textarea").focus(function() {
            focused = $(this);
        });
        
        $(".spec_char").click(function(e) {
            e.preventDefault();
            var char = $(this).attr("data");
            if(typeof focused != "undefined")
            {
                var caretPos = focused[0].selectionStart;
                var textAreaTxt = focused.val();
                focused.val(textAreaTxt.substring(0, caretPos) + char + textAreaTxt.substring(caretPos));
            }
        });
        
        $(window).scroll(function () {
            if($(this).scrollTop() > 150)
                $(".buttons_spec").addClass("unlinked");
            else
                $(".buttons_spec").removeClass("unlinked");
        });
    });
</script>
<?php endif; ?>
