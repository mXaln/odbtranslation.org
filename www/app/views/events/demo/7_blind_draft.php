<div id="translator_steps" class="open blind-draft">
    <div id="tr_steps_hide" class="glyphicon glyphicon-chevron-left blind-draft"></div>

    <ul class="steps_list">
        <li class="pray-step ">
            <span>Pray</span>
        </li>
        <li class="consume-step ">
            <span>Consume Text</span>
        </li>
        <li class="discuss-step ">
            <span>Verbalize Text</span>
        </li>
        <li class="chunking-step ">
            <span>Working with Chunks</span>
        </li>

                <li class="blind-draft-step active">
            <span>Blind Draft</span>
        </li>
        
                <li class="self-check-step ">
            <span>Self Check</span>
        </li>
        <li class="peer-review-step ">
            <span>Peer Review</span>
        </li>
        <li class="keyword-check-step ">
            <span>Keyword Check</span>
        </li>
        <li class="content-review-step ">
            <span>Content Review</span>
        </li>
    </ul>
</div>

<script>
    var memberID = 0;
    var eventID = 0;
    var chkMemberID = 0;
    var step = 'blind-draft';
</script>

<div style="position: fixed; right: 0;">

</div>

<div id="chat_container" class="closed">
    <div id="chat_new_msgs" class="chat_new_msgs"></div>
    <div id="chat_hide" class="glyphicon glyphicon-chevron-left"></div>

    <div class="chat panel panel-info">
        <div class="chat_tabs panel-heading">
            <div class="row">
                <div style="display: block;" id="p2p" class="col-sm-4 chat_tab active">
                    <div>Partner</div>
                    <div class="missed"></div>
                </div>
                <div id="chk" class="col-sm-4 chat_tab">
                    <div>Checking</div>
                    <div class="missed"></div>
                </div>
                <div id="evnt" class="col-sm-4 chat_tab">
                    <div>Event</div>
                    <div class="missed"></div>
                </div>
            </div>
        </div>
        <ul id="p2p_messages" class="chat_msgs"><li class="message msg_my" data="7"><div class="msg_name">You</div><div data-original-title="01.07.2016, 18:22:38" class="msg_text" data-toggle="tooltip" data-placement="top" title="">Hi, let's translate chapter 1</div></li></ul>
        <ul id="chk_messages" class="chat_msgs"></ul>
        <ul id="evnt_messages" class="chat_msgs"><li class="message msg_other" data="16"><div class="msg_name">mSimpson</div><div data-original-title="30.06.2016, 18:38:09" class="msg_text" data-toggle="tooltip" data-placement="top" title="">Hi, this a test event message</div></li><li class="message msg_my" data="7"><div class="msg_name">You</div><div data-original-title="01.07.2016, 18:22:02" class="msg_text" data-toggle="tooltip" data-placement="top" title="">Hi, this a test event message 2</div></li></ul>
        <form action="" class="form-inline">
            <div class="form-group">
                <textarea style="overflow: hidden; word-wrap: break-word; resize: horizontal; height: 54px;" id="m" class="form-control"></textarea>
                <input id="chat_type" value="p2p" type="hidden">
            </div>
        </form>
    </div>

    <div class="members_online panel panel-info">
        <div class="panel-heading">Members Online</div>
        <ul id="online" class="panel-body"><li>Gen2Pet</li><li class="mine">mpat1977</li></ul>
    </div>

    <div class="clear"></div>
</div>

<!-- Audio for missed chat messages -->
<audio id="missedMsg">
    <source src="#" type="audio/ogg">
</audio>

<script src="<?php echo \Helpers\Url::templatePath()?>js/chat-plugin.js"></script>

<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title">Blind Draft</div>
        <div class="demo_title">Demo</div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <form action="" method="post" id="main_form">
                <div class="main_content_text">
                    <div class="row">
                        <h4>English - Unlocked Literal Bible - New Testament - 2 Timothy 4:1-6</h4>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <textarea style="overflow: hidden; word-wrap: break-word; height: 328px;" name="draft" rows="10" class="col-sm-6 blind_ta textarea"></textarea>
                        </div>
                    </div>
                </div>

                <div class="main_content_footer row">
                    <div class="form-group">
                        <div class="main_content_confirm_desc">Please confirm that you finished this step</div>
                        <label><input name="confirm_step" id="confirm_step" value="1" type="checkbox"> Yes, I did</label>
                    </div>

                    <button id="next_step" onclick="window.location.href='<?php echo DIR ?>events/demo/self_check'; return false;" class="btn btn-primary" disabled="disabled">Next step</button>
                </div>
            </form>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps">
                <div class="help_title_steps">HELP</div>

                <div class="clear"></div>

                <div class="help_name_steps"><span>Step 4:</span> Blind Draft</div>
                <div class="help_descr_steps">
                    <ul><li><b>PURPOSE:</b> to translate the source text as naturally as possible in the target language.</li><li>Without
 reference to the source text, type your translation of the chunk as 
best you can remember it, in as natural a way as you can, using words 
that would be clear and understandable to the sp... <div class="show_tutorial_popup"> &gt;&gt;&gt; Show more</div></li></ul>
                </div>
            </div>

            <div class="event_info">
                <div class="participant_info">
                    <div class="participant_name">
                        <span>Your partner:</span>
                        <span>Gen2Pet</span>
                    </div>
                    <div class="participant_name">
                        <span>Your checker:</span>
                        <span>N/A</span>
                    </div>
                    <div class="additional_info">
                        <a href="#">Event Progress</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="tutorial_container">
    <div class="tutorial_popup">
        <div class="tutorial-close glyphicon glyphicon-remove"></div>
        <div class="tutorial_pic">
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/icons/blind-draft.png" height="100px" width="100px">
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/big/blind-draft.png" height="280px" width="280px">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="blind-draft" value="0" type="checkbox"> Don't show this message again</label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3>Blind Draft</h3>
            <ul><li><b>PURPOSE:</b> to translate the source text as naturally as possible in the target language.</li><li>Without
 reference to the source text, type your translation of the chunk as 
best you can remember it, in as natural a way as you can, using words 
that would be clear and understandable to the speakers of the target 
language.</li><li>The goal in this step is the best expression and form of the target language, not accuracy.</li><li>Do not at any point refer to the source text. Just translate what you remember.</li><li>Do not be concerned about marking verses. This will be done in the next step</li><li>Spend no more than 10-12 minutes on this exercise</li></ul>
        </div>
    </div>
</div>

<script>
    (function($) {
        $("#chat_container").chat();
    }(jQuery));
</script>