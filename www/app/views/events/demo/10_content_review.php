<div id="translator_steps" class="open content-review">
    <div id="tr_steps_hide" class="glyphicon glyphicon-chevron-left content-review"></div>

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

        
                <li class="self-check-step ">
            <span>Draft and Self Check</span>
        </li>
        <li class="peer-review-step ">
            <span>Peer Review</span>
        </li>
        <li class="keyword-check-step ">
            <span>Keyword Check</span>
        </li>
        <li class="content-review-step active">
            <span>Content Review</span>
        </li>
    </ul>
</div>

<script>
    var memberID = 0;
    var eventID = 0;
    var chkMemberID = 0;
    var step = 'content-review';
</script>

<div style="position: fixed; right: 0;">

</div>

<div style="right: -610px;" id="chat_container" class="closed">
    <div style="display: none;" id="chat_new_msgs" class="chat_new_msgs"></div>
    <div id="chat_hide" class="glyphicon glyphicon-chevron-left"></div>

    <div class="chat panel panel-info">
        <div class="chat_tabs panel-heading">
            <div class="row">
                <div id="p2p" class="col-sm-4 chat_tab">
                    <div>Partner</div>
                    <div class="missed"></div>
                </div>
                <div style="display: block;" id="chk" class="col-sm-4 chat_tab active">
                    <div>Checking</div>
                    <div style="display: none;" class="missed"></div>
                </div>
                <div id="evnt" class="col-sm-4 chat_tab">
                    <div>Event</div>
                    <div style="display: none;" class="missed"></div>
                </div>
            </div>
        </div>
        <ul id="p2p_messages" class="chat_msgs"></ul>
        <ul style="display: block;" id="chk_messages" class="chat_msgs"><li class="message msg_my" data="7"><div class="msg_name">You</div><div data-original-title="04.07.2016, 17:36:45" class="msg_text" data-toggle="tooltip" data-placement="top" title="">This is chat tab for checking dialog</div></li></ul>
        <ul style="display: none;" id="evnt_messages" class="chat_msgs"><li class="message msg_other" data="16"><div class="msg_name">mSimpson</div><div data-original-title="30.06.2016, 18:38:09" class="msg_text" data-toggle="tooltip" data-placement="top" title="">u druyrdr udru</div></li><li class="message msg_my" data="7"><div class="msg_name">You</div><div data-original-title="01.07.2016, 18:22:02" class="msg_text" data-toggle="tooltip" data-placement="top" title="">sgsegse gse gseg segs</div></li></ul>
        <form action="" class="form-inline">
            <div class="form-group">
                <textarea style="overflow: hidden; word-wrap: break-word; resize: horizontal; height: 54px;" id="m" class="form-control"></textarea>
                <input id="chat_type" value="chk" type="hidden">
            </div>
        </form>
    </div>

    <div class="members_online panel panel-info">
        <div class="panel-heading">Members Online</div>
        <ul id="online" class="panel-body"><li>Gen2Pet</li><li>mSimpson (facilitator)</li><li class="mine">mpat1977</li></ul>
    </div>

    <div class="clear"></div>
</div>

<!-- Audio for missed chat messages -->
<audio id="missedMsg">
    <source src="#" type="audio/ogg">
</audio>

<script src="<?php echo \Helpers\Url::templatePath()?>js/chat-plugin.js"></script>

<div class="editor">
    <div class="comment_div panel panel-default">
        <div class="panel-heading">
            <h1 class="panel-title">write_note_title</h1>
            <span class="editor-close glyphicon glyphicon-floppy-disk"></span>
        </div>
        <textarea style="overflow-x: hidden; word-wrap: break-word; overflow-y: visible;" class="textarea textarea_editor"></textarea>
        <div class="other_comments_list"></div>
        <img src="<?php echo \Helpers\Url::templatePath() ?>img/loader.gif" class="commentEditorLoader">
    </div>
</div>

<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title">Content Review</div>
        <div class="demo_title">Demo</div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <form action="" method="post" id="main_form">
                <div class="main_content_text row">
                    
                    <h4>English - Unlocked Literal Bible - New Testament - 2 Timothy 4:1-22</h4>

                    <div class="col-sm-12">
                                                                                    <div class="row chunk_verse editor_area">
                                    <div class="p_verse_num"><strong><sup>1</sup></strong></div>

                                    <div class="comments_number">
                                                                            </div>

                                    <textarea style="overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[0][verses][]" class="col-sm-9 peer_verse_ta verse_right textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                </div>
                                                                <div class="row chunk_verse editor_area">
                                    <div class="p_verse_num"><strong><sup>2</sup></strong></div>

                                    <div class="comments_number">
                                                                            </div>

                                    <textarea style="overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[0][verses][]" class="col-sm-9 peer_verse_ta verse_right textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                </div>
                                                                <div class="row chunk_verse editor_area">
                                    <div class="p_verse_num"><strong><sup>3</sup></strong></div>

                                    <div class="comments_number">
                                                                            </div>

                                    <textarea style="overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[0][verses][]" class="col-sm-9 peer_verse_ta verse_right textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                </div>
                                                                <div class="row chunk_verse editor_area">
                                    <div class="p_verse_num"><strong><sup>4</sup></strong></div>

                                    <div class="comments_number">
                                                                            </div>

                                    <textarea style="overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[0][verses][]" class="col-sm-9 peer_verse_ta verse_right textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                </div>
                                                                <div class="row chunk_verse editor_area">
                                    <div class="p_verse_num"><strong><sup>5</sup></strong></div>

                                    <div class="comments_number">
                                                                            </div>

                                    <textarea style="overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[0][verses][]" class="col-sm-9 peer_verse_ta verse_right textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                </div>
                                                                <div class="row chunk_verse editor_area">
                                    <div class="p_verse_num"><strong><sup>6</sup></strong></div>

                                    <div class="comments_number">
                                                                            </div>

                                    <textarea style="overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[0][verses][]" class="col-sm-9 peer_verse_ta verse_right textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                </div>
                                                            <div class="chunk_divider col-sm-12"></div>
                            <div class="clear"></div>
                                                                                    <div class="row chunk_verse editor_area">
                                    <div class="p_verse_num"><strong><sup>7</sup></strong></div>

                                    <div class="comments_number">
                                                                            </div>

                                    <textarea style="overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[1][verses][]" class="col-sm-9 peer_verse_ta verse_right textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                </div>
                                                                <div class="row chunk_verse editor_area">
                                    <div class="p_verse_num"><strong><sup>8</sup></strong></div>

                                    <div class="comments_number">
                                                                            </div>

                                    <textarea style="overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[1][verses][]" class="col-sm-9 peer_verse_ta verse_right textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                </div>
                                                                <div class="row chunk_verse editor_area">
                                    <div class="p_verse_num"><strong><sup>9</sup></strong></div>

                                    <div class="comments_number">
                                                                            </div>

                                    <textarea style="overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[1][verses][]" class="col-sm-9 peer_verse_ta verse_right textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                </div>
                                                                <div class="row chunk_verse editor_area">
                                    <div class="p_verse_num"><strong><sup>10</sup></strong></div>

                                    <div class="comments_number">
                                                                            </div>

                                    <textarea style="overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[1][verses][]" class="col-sm-9 peer_verse_ta verse_right textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                </div>
                                                                <div class="row chunk_verse editor_area">
                                    <div class="p_verse_num"><strong><sup>11</sup></strong></div>

                                    <div class="comments_number">
                                                                            </div>

                                    <textarea style="overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[1][verses][]" class="col-sm-9 peer_verse_ta verse_right textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text,</textarea>

                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                </div>
                                                                <div class="row chunk_verse editor_area">
                                    <div class="p_verse_num"><strong><sup>12</sup></strong></div>

                                    <div class="comments_number">
                                                                            </div>

                                    <textarea style="overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[1][verses][]" class="col-sm-9 peer_verse_ta verse_right textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text,</textarea>

                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                </div>
                                                            <div class="chunk_divider col-sm-12"></div>
                            <div class="clear"></div>
                                                                                    <div class="row chunk_verse editor_area">
                                    <div class="p_verse_num"><strong><sup>13</sup></strong></div>

                                    <div class="comments_number">
                                                                            </div>

                                    <textarea style="overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[2][verses][]" class="col-sm-9 peer_verse_ta verse_right textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                </div>
                                                                <div class="row chunk_verse editor_area">
                                    <div class="p_verse_num"><strong><sup>14</sup></strong></div>

                                    <div class="comments_number">
                                                                            </div>

                                    <textarea style="overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[2][verses][]" class="col-sm-9 peer_verse_ta verse_right textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                </div>
                                                                <div class="row chunk_verse editor_area">
                                    <div class="p_verse_num"><strong><sup>15</sup></strong></div>

                                    <div class="comments_number">
                                                                            </div>

                                    <textarea style="overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[2][verses][]" class="col-sm-9 peer_verse_ta verse_right textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                </div>
                                                                <div class="row chunk_verse editor_area">
                                    <div class="p_verse_num"><strong><sup>16</sup></strong></div>

                                    <div class="comments_number">
                                                                            </div>

                                    <textarea style="overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[2][verses][]" class="col-sm-9 peer_verse_ta verse_right textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                </div>
                                                            <div class="chunk_divider col-sm-12"></div>
                            <div class="clear"></div>
                                                                                    <div class="row chunk_verse editor_area">
                                    <div class="p_verse_num"><strong><sup>17</sup></strong></div>

                                    <div class="comments_number">
                                                                            </div>

                                    <textarea style="overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[3][verses][]" class="col-sm-9 peer_verse_ta verse_right textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                </div>
                                                                <div class="row chunk_verse editor_area">
                                    <div class="p_verse_num"><strong><sup>18</sup></strong></div>

                                    <div class="comments_number">
                                                                            </div>

                                    <textarea style="overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[3][verses][]" class="col-sm-9 peer_verse_ta verse_right textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                </div>
                                                                <div class="row chunk_verse editor_area">
                                    <div class="p_verse_num"><strong><sup>19</sup></strong></div>

                                    <div class="comments_number">
                                                                            </div>

                                    <textarea style="overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[3][verses][]" class="col-sm-9 peer_verse_ta verse_right textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                </div>
                                                                <div class="row chunk_verse editor_area">
                                    <div class="p_verse_num"><strong><sup>20</sup></strong></div>

                                    <div class="comments_number">
                                                                            </div>

                                    <textarea style="overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[3][verses][]" class="col-sm-9 peer_verse_ta verse_right textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                </div>
                                                                <div class="row chunk_verse editor_area">
                                    <div class="p_verse_num"><strong><sup>21</sup></strong></div>

                                    <div class="comments_number">
                                                                            </div>

                                    <textarea style="overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[3][verses][]" class="col-sm-9 peer_verse_ta verse_right textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                </div>
                                                                <div class="row chunk_verse editor_area">
                                    <div class="p_verse_num"><strong><sup>22</sup></strong></div>

                                    <div class="comments_number">
                                                                            </div>

                                    <textarea style="overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[3][verses][]" class="col-sm-9 peer_verse_ta verse_right textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                </div>
                                                            <div class="chunk_divider col-sm-12"></div>
                            <div class="clear"></div>
                                            </div>

                    <div class="col-sm-12">
                        <button id="save_step" onclick="return false;" value="1" class="btn btn-primary">Save</button>
                        <img src="<?php echo \Helpers\Url::templatePath() ?>img/alert.png" class="unsaved_alert">
                    </div>
                </div>

                <div class="main_content_footer row">
                    <div class="form-group">
                        <div class="main_content_confirm_desc">Please confirm that you finished this step</div>
                        <label><input name="confirm_step" id="confirm_step" value="1" type="checkbox"> Yes, I did</label>
                    </div>

                    <button id="next_step" onclick="window.location.href='<?php echo DIR ?>events/demo/pray'; return false;" class="btn btn-primary" disabled="disabled">Next step</button>
                </div>
            </form>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps">
                <div class="help_title_steps">HELP</div>

                <div class="clear"></div>

                <div class="help_name_steps"><span>Step 8:</span> Content Review</div>
                <div class="help_descr_steps">
                    <ul><li><b>PURPOSE:</b> to ensure that each verse and chunk and chapter accurately communicate the same message in the target language</li><li>Contact your checker on a suitable conversation platform: Skype, Hangout, phone, etc.</li><li>The review can be done in two ways: <ol><li>If the checker knows on... <div class="show_tutorial_popup"> &gt;&gt;&gt; Show more</div></li></ol></li></ul>
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
                        <span class="checker_name_span">mSimpson</span>
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
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/icons/content-review.png" height="100px" width="100px">
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/big/content-review.png" height="280px" width="280px">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="content-review" value="0" type="checkbox"> Don't show this message again</label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3>Content Review</h3>
            <ul><li><b>PURPOSE:</b> to ensure that each verse and chunk and chapter accurately communicate the same message in the target language</li><li>Contact your checker on a suitable conversation platform: Skype, Hangout, phone, etc.</li><li>The review can be done in two ways: <ol><li>If
 the checker knows only the source, the check must be done by back 
translation. In this case, you will read the translated text verse by 
verse, then you or a second person will back translate into the source, 
and the checker will compare what they hear to the source text they are 
reading.</li><li>If the checker is fluent in both languages, they can 
elect to use method one, or review your translation side by side with 
the source. In either case, your checker will then ask questions about 
anything that doesn’t seem to transfer accurately or completely. Make 
appropriate adjustments to your translation as the discussion proceeds.</li></ol></li><li>Spend
 no more than 30 minutes on this exercise. Do not waste time on 
disagreements. In such cases, leave the translated text as is, record a 
note on the appropriate verse, and move on.</li></ul>
        </div>
    </div>
</div>

<script>
    (function($) {
        $("#chat_container").chat({
            step: step
        });
    }(jQuery));
</script>