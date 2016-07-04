<div id="translator_steps" class="open keyword-check">
    <div id="tr_steps_hide" class="glyphicon glyphicon-chevron-left keyword-check"></div>

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
        <li class="keyword-check-step active">
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
    var step = 'keyword-check';
</script>

<div style="position: fixed; right: 0;">

</div>

<div id="chat_container" class="closed">
    <div id="chat_new_msgs" class="chat_new_msgs"></div>
    <div id="chat_hide" class="glyphicon glyphicon-chevron-left"></div>

    <div class="chat panel panel-info">
        <div class="chat_tabs panel-heading">
            <div class="row">
                <div id="p2p" class="col-sm-4 chat_tab">
                    <div>Partner</div>
                    <div class="missed"></div>
                </div>
                <div id="chk" class="col-sm-4 chat_tab active" style="display: block;">
                    <div>Checking</div>
                    <div class="missed"></div>
                </div>
                <div id="evnt" class="col-sm-4 chat_tab">
                    <div>Event</div>
                    <div class="missed"></div>
                </div>
            </div>
        </div>
        <ul id="p2p_messages" class="chat_msgs" style="display: none;"></ul>
        <ul id="chk_messages" class="chat_msgs" style="display: none;"><li class="message msg_other" data="7"><div class="msg_name">mpat1977</div><div class="msg_text" data-toggle="tooltip" data-placement="top" title="" data-original-title="7/4/2016, 5:36:45 PM">This is chat tab for checking dialog</div></li></ul>
        <ul id="evnt_messages" class="chat_msgs" style="display: none;"><li class="message msg_my" data="16"><div class="msg_name">You</div><div class="msg_text" data-toggle="tooltip" data-placement="top" title="" data-original-title="6/30/2016, 6:38:09 PM">Demo event message 1</div></li><li class="message msg_other" data="7"><div class="msg_name">mpat1977</div><div class="msg_text" data-toggle="tooltip" data-placement="top" title="" data-original-title="7/1/2016, 6:22:02 PM">Demo event message 2</div></li></ul>
        <form action="http://v-mast.mvc/events/checker/31/7" class="form-inline">
            <div class="form-group">
                <textarea id="m" class="form-control" style="overflow: hidden; word-wrap: break-word; resize: horizontal; height: 54px;"></textarea>
                <input type="hidden" id="chat_type" value="chk">
            </div>
        </form>
    </div>

    <div class="members_online panel panel-info">
        <div class="panel-heading">Members Online</div>
        <ul id="online" class="panel-body"><li>Gen2Pet</li><li>mpat1977</li><li class="mine">mSimpson (facilitator)</li></ul>
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
        <textarea class="textarea textarea_editor" style="overflow-x: hidden; word-wrap: break-word; overflow-y: visible;"></textarea>
        <div class="other_comments_list"></div>
        <img src="<?php echo \Helpers\Url::templatePath() ?>img/loader.gif" class="commentEditorLoader">
    </div>
</div>


<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title">Keyword Check (Check)</div>
        <div class="demo_title">Demo</div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <div class="main_content_text row">
                <div class="keywords_show" style="">Keywords</div>

                <div class="keywords_list_container">
                    <div class="keywords_list">
                        <div class="keywords-list-close glyphicon glyphicon-remove"></div>
                        <div class="labels_list">
                                                    </div>
                    </div>
                </div>

                <h4>English - Unlocked Literal Bible - New Testament - 2 Timothy 4:1-22</h4>

                
                <div class="col-sm-12 one_side_content">
                                                                                                    <div class="source_content verse_with_note">
                                <div style="padding-right: 15px" class="verse_line"><strong><sup>1</sup></strong> I give this solemn command before God and Christ Jesus, who will judge the living and the dead, and because of his appearing and his kingdom:

  </div>

                                <div class="comments_number">
                                                                    </div>
                                <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                <div class="comments">
                                                                            <div class="my_comment" data="4:1"></div>
                                                                    </div>
                            </div>
                                                                                    <div class="source_content verse_with_note">
                                <div style="padding-right: 15px" class="verse_line"><strong><sup>2</sup></strong> Preach the Word. Be ready when it is convenient and when it is not. Reprove, rebuke, exhort, with all patience and teaching.
  </div>

                                <div class="comments_number">
                                                                    </div>
                                <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                <div class="comments">
                                                                            <div class="my_comment" data="4:2"></div>
                                                                    </div>
                            </div>
                                                                                    <div class="source_content verse_with_note">
                                <div style="padding-right: 15px" class="verse_line"><strong><sup>3</sup></strong> For the time will come when people will not endure sound teaching. Instead, they will heap up for themselves teachers according to their own desires. They will be tickling their hearing.

  </div>

                                <div class="comments_number">
                                                                    </div>
                                <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                <div class="comments">
                                                                            <div class="my_comment" data="4:3"></div>
                                                                    </div>
                            </div>
                                                                                    <div class="source_content verse_with_note">
                                <div style="padding-right: 15px" class="verse_line"><strong><sup>4</sup></strong> They will turn their hearing away from the truth, and they will turn aside to myths.

  </div>

                                <div class="comments_number">
                                                                    </div>
                                <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                <div class="comments">
                                                                            <div class="my_comment" data="4:4"></div>
                                                                    </div>
                            </div>
                                                                                    <div class="source_content verse_with_note">
                                <div style="padding-right: 15px" class="verse_line"><strong><sup>5</sup></strong> But you, be sober-minded in all things. Suffer hardship; do the work of an evangelist; fulfill your service.
  </div>

                                <div class="comments_number">
                                                                    </div>
                                <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                <div class="comments">
                                                                            <div class="my_comment" data="4:5"></div>
                                                                    </div>
                            </div>
                                                                                    <div class="source_content verse_with_note">
                                <div style="padding-right: 15px" class="verse_line"><strong><sup>6</sup></strong> For I am already being poured out. The time of my departure has come.

  </div>

                                <div class="comments_number">
                                                                    </div>
                                <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                <div class="comments">
                                                                            <div class="my_comment" data="4:6"></div>
                                                                    </div>
                            </div>
                                                                                                            <div class="source_content verse_with_note">
                                <div style="padding-right: 15px" class="verse_line"><strong><sup>7</sup></strong> I have competed in the good contest; I have finished the race; I have kept the faith.

  </div>

                                <div class="comments_number">
                                                                    </div>
                                <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                <div class="comments">
                                                                            <div class="my_comment" data="4:7"></div>
                                                                    </div>
                            </div>
                                                                                    <div class="source_content verse_with_note">
                                <div style="padding-right: 15px" class="verse_line"><strong><sup>8</sup></strong> The crown of righteousness has been reserved for me, which the Lord, the righteous judge, will give to me on that day. And not to me only, but also to all those who have loved his appearing.


  </div>

                                <div class="comments_number">
                                                                    </div>
                                <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                <div class="comments">
                                                                            <div class="my_comment" data="4:8"></div>
                                                                    </div>
                            </div>
                                                                                    <div class="source_content verse_with_note">
                                <div style="padding-right: 15px" class="verse_line"><strong><sup>9</sup></strong> Do your best to come to me quickly.

  </div>

                                <div class="comments_number">
                                                                    </div>
                                <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                <div class="comments">
                                                                            <div class="my_comment" data="4:9"></div>
                                                                    </div>
                            </div>
                                                                                    <div class="source_content verse_with_note">
                                <div style="padding-right: 15px" class="verse_line"><strong><sup>10</sup></strong> For Demas has left me. He loves this present world and has gone to Thessalonica. Crescens went to Galatia, and Titus went to Dalmatia.
  </div>

                                <div class="comments_number">
                                                                    </div>
                                <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                <div class="comments">
                                                                            <div class="my_comment" data="4:10"></div>
                                                                    </div>
                            </div>
                                                                                    <div class="source_content verse_with_note">
                                <div style="padding-right: 15px" class="verse_line"><strong><sup>11</sup></strong> Only Luke is with me. Get Mark and bring him with you because he is useful to me in the work.

  </div>

                                <div class="comments_number">
                                                                    </div>
                                <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                <div class="comments">
                                                                            <div class="my_comment" data="4:11"></div>
                                                                    </div>
                            </div>
                                                                                    <div class="source_content verse_with_note">
                                <div style="padding-right: 15px" class="verse_line"><strong><sup>12</sup></strong> Tychicus I sent to Ephesus.

  </div>

                                <div class="comments_number">
                                                                    </div>
                                <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                <div class="comments">
                                                                            <div class="my_comment" data="4:12"></div>
                                                                    </div>
                            </div>
                                                                                                            <div class="source_content verse_with_note">
                                <div style="padding-right: 15px" class="verse_line"><strong><sup>13</sup></strong> The cloak that I left at Troas with Carpus, bring it when you come, and the books, especially the parchments.
  </div>

                                <div class="comments_number">
                                                                    </div>
                                <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                <div class="comments">
                                                                            <div class="my_comment" data="4:13"></div>
                                                                    </div>
                            </div>
                                                                                    <div class="source_content verse_with_note">
                                <div style="padding-right: 15px" class="verse_line"><strong><sup>14</sup></strong> Alexander the coppersmith displayed many evil deeds against me. The Lord will repay to him according to his deeds.

  </div>

                                <div class="comments_number">
                                                                    </div>
                                <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                <div class="comments">
                                                                            <div class="my_comment" data="4:14"></div>
                                                                    </div>
                            </div>
                                                                                    <div class="source_content verse_with_note">
                                <div style="padding-right: 15px" class="verse_line"><strong><sup>15</sup></strong> You also should guard yourself against him, because he greatly opposed our words.

  </div>

                                <div class="comments_number">
                                                                    </div>
                                <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                <div class="comments">
                                                                            <div class="my_comment" data="4:15"></div>
                                                                    </div>
                            </div>
                                                                                    <div class="source_content verse_with_note">
                                <div style="padding-right: 15px" class="verse_line"><strong><sup>16</sup></strong> At my first defense, no one stood with me. Instead, everyone left me. May it not be counted against them.
  </div>

                                <div class="comments_number">
                                                                    </div>
                                <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                <div class="comments">
                                                                            <div class="my_comment" data="4:16"></div>
                                                                    </div>
                            </div>
                                                                                                            <div class="source_content verse_with_note">
                                <div style="padding-right: 15px" class="verse_line"><strong><sup>17</sup></strong> But the Lord stood by me and strengthened me so that, through me, the proclamation might be fully fulfilled, and that all the Gentiles might hear. I was rescued out of the lion's mouth.

  </div>

                                <div class="comments_number">
                                                                    </div>
                                <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                <div class="comments">
                                                                            <div class="my_comment" data="4:17"></div>
                                                                    </div>
                            </div>
                                                                                    <div class="source_content verse_with_note">
                                <div style="padding-right: 15px" class="verse_line"><strong><sup>18</sup></strong> The Lord will rescue me from every evil deed and will save me for his heavenly kingdom. To him be the glory forever and ever. Amen.


  </div>

                                <div class="comments_number">
                                                                    </div>
                                <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                <div class="comments">
                                                                            <div class="my_comment" data="4:18"></div>
                                                                    </div>
                            </div>
                                                                                    <div class="source_content verse_with_note">
                                <div style="padding-right: 15px" class="verse_line"><strong><sup>19</sup></strong> Greet Priscilla, Aquila, and the house of Onesiphorus.

  </div>

                                <div class="comments_number">
                                                                    </div>
                                <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                <div class="comments">
                                                                            <div class="my_comment" data="4:19"></div>
                                                                    </div>
                            </div>
                                                                                    <div class="source_content verse_with_note">
                                <div style="padding-right: 15px" class="verse_line"><strong><sup>20</sup></strong> Erastus remained at Corinth, but Trophimus I left sick at Miletus.

  </div>

                                <div class="comments_number">
                                                                    </div>
                                <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                <div class="comments">
                                                                            <div class="my_comment" data="4:20"></div>
                                                                    </div>
                            </div>
                                                                                    <div class="source_content verse_with_note">
                                <div style="padding-right: 15px" class="verse_line"><strong><sup>21</sup></strong> Do your best to come before winter. Eubulus greets you, also Pudens, Linus, Claudia, and all the brothers.



  </div>

                                <div class="comments_number">
                                                                    </div>
                                <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                <div class="comments">
                                                                            <div class="my_comment" data="4:21"></div>
                                                                    </div>
                            </div>
                                                                                    <div class="source_content verse_with_note">
                                <div style="padding-right: 15px" class="verse_line"><strong><sup>22</sup></strong> May the Lord be with your spirit. May grace be with you.</div>

                                <div class="comments_number">
                                                                    </div>
                                <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                <div class="comments">
                                                                            <div class="my_comment" data="4:22"></div>
                                                                    </div>
                            </div>
                                            </div>
            </div>

                        <div class="main_content_footer row">
                <form action="" method="post" id="checker_submit">
                    <div class="form-group">
                        <div class="main_content_confirm_desc">Please confirm that you finished this step</div>
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1"> Yes, I did</label>
                    </div>

                    <button id="next_step" onclick="window.location.href='<?php echo DIR ?>events/demo/content_review_checker'; return false;" class="btn btn-primary" disabled="">Next step</button>
                </form>
            </div>
                    </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps">
                <div class="help_title_steps">HELP</div>

                <div class="clear"></div>

                <div class="help_name_steps"><span>Step 7:</span> Keyword Check</div>
                <div class="help_descr_steps">
                    <ul><li><b>PURPOSE:</b> to ensure certain significant words are present in the translated text and accurately expressed</li><li>When you accept the task to check someone’s work, you will see only the source from which they have translated.</li><li>Contact the translator on a suitable conversation platfo... <div class="show_tutorial_popup"> &gt;&gt;&gt; Show more</div></li></ul>
                </div>
            </div>

            <div class="event_info">
                <div class="participant_info">
                    <div class="participant_name">
                        <span>Your translator:</span>
                        <span>mpat1977</span>
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
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/icons/keyword-check.png" width="100px" height="100px">
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/big/keyword-check.png" width="280px" height="280px">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="keyword-check_checker" data2="checker" type="checkbox" value="0"> Don't show this message again</label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3>Keyword Check</h3>
            <ul><li><b>PURPOSE:</b> to ensure certain significant words are present in the translated text and accurately expressed</li><li>When you accept the task to check someone’s work, you will see only the source from which they have translated.</li><li>Contact the translator on a suitable conversation platform: Skype, Hangout, phone, etc.</li><li>Proceed through each verse, and review with the translator each highlighted word. Ensure the word exists in the translated text and ask how the translator expressed it. Look for major errors and significant misses.</li><li>Review any notes associated with the verse and attempt to resolve keyword issues.</li><li>You may ask about other words that you think are important beyond the highlighted, but do not over analyze or critique the text, and focus only the words which carry the more important meaning.</li><li>Spend no more than 30 minutes on this exercise. Do not waste time on disagreements. In such cases, leave the translated text as is, record a note on the appropriate verse, and move on.</li></ul>
        </div>
    </div>
</div>

<script>
    var isChecker = true;
    var keywords = [];

                                                                                                        if($.inArray('death', keywords) <= -1)
                            keywords.push('death');
                                            if($.inArray('die', keywords) <= -1)
                            keywords.push('die');
                                            if($.inArray('dead', keywords) <= -1)
                            keywords.push('dead');
                                                                                                    if($.inArray('exhort', keywords) <= -1)
                            keywords.push('exhort');
                                            if($.inArray('exhortation', keywords) <= -1)
                            keywords.push('exhortation');
                                                                                                    if($.inArray('God', keywords) <= -1)
                            keywords.push('God');
                                                                                                    if($.inArray('Jesus', keywords) <= -1)
                            keywords.push('Jesus');
                                            if($.inArray('Jesus Christ', keywords) <= -1)
                            keywords.push('Jesus Christ');
                                            if($.inArray('Christ Jesus', keywords) <= -1)
                            keywords.push('Christ Jesus');
                                                                                                    if($.inArray('judge', keywords) <= -1)
                            keywords.push('judge');
                                            if($.inArray('judgment', keywords) <= -1)
                            keywords.push('judgment');
                                                                                                    if($.inArray('kingdom', keywords) <= -1)
                            keywords.push('kingdom');
                                                                                                    if($.inArray('life', keywords) <= -1)
                            keywords.push('life');
                                            if($.inArray('live', keywords) <= -1)
                            keywords.push('live');
                                            if($.inArray('living', keywords) <= -1)
                            keywords.push('living');
                                            if($.inArray('alive', keywords) <= -1)
                            keywords.push('alive');
                                                                                                    if($.inArray('preach', keywords) <= -1)
                            keywords.push('preach');
                                                                                                    if($.inArray('word', keywords) <= -1)
                            keywords.push('word');
                                                                                                                                if($.inArray('endure', keywords) <= -1)
                            keywords.push('endure');
                                            if($.inArray('endurance', keywords) <= -1)
                            keywords.push('endurance');
                                                                                                    if($.inArray('fulfill', keywords) <= -1)
                            keywords.push('fulfill');
                                                                                                    if($.inArray('lust', keywords) <= -1)
                            keywords.push('lust');
                                                                                                    if($.inArray('minister', keywords) <= -1)
                            keywords.push('minister');
                                            if($.inArray('ministry', keywords) <= -1)
                            keywords.push('ministry');
                                                                                                    if($.inArray('suffer', keywords) <= -1)
                            keywords.push('suffer');
                                            if($.inArray('suffering', keywords) <= -1)
                            keywords.push('suffering');
                                                                                                    if($.inArray('teacher', keywords) <= -1)
                            keywords.push('teacher');
                                            if($.inArray('Teacher', keywords) <= -1)
                            keywords.push('Teacher');
                                                                                                    if($.inArray('true', keywords) <= -1)
                            keywords.push('true');
                                            if($.inArray('truth', keywords) <= -1)
                            keywords.push('truth');
                                                                                                                                if($.inArray('faith', keywords) <= -1)
                            keywords.push('faith');
                                                                                                    if($.inArray('judge', keywords) <= -1)
                            keywords.push('judge');
                                                                                                    if($.inArray('Lord', keywords) <= -1)
                            keywords.push('Lord');
                                                                                                    if($.inArray('love', keywords) <= -1)
                            keywords.push('love');
                                                                                                    if($.inArray('righteous', keywords) <= -1)
                            keywords.push('righteous');
                                            if($.inArray('righteousness', keywords) <= -1)
                            keywords.push('righteousness');
                                                                                                    if($.inArray('sacrifice', keywords) <= -1)
                            keywords.push('sacrifice');
                                            if($.inArray('offering', keywords) <= -1)
                            keywords.push('offering');
                                                                                                                                if($.inArray('Galatia', keywords) <= -1)
                            keywords.push('Galatia');
                                                                                                    if($.inArray('Thessalonica', keywords) <= -1)
                            keywords.push('Thessalonica');
                                            if($.inArray('Thessalonians', keywords) <= -1)
                            keywords.push('Thessalonians');
                                                                                                    if($.inArray('Titus', keywords) <= -1)
                            keywords.push('Titus');
                                                                                                                                if($.inArray('Ephesus', keywords) <= -1)
                            keywords.push('Ephesus');
                                                                                                    if($.inArray('John Mark', keywords) <= -1)
                            keywords.push('John Mark');
                                                                                                    if($.inArray('Luke', keywords) <= -1)
                            keywords.push('Luke');
                                                                                                    if($.inArray('Troas', keywords) <= -1)
                            keywords.push('Troas');
                                                                                                    if($.inArray('Tychicus', keywords) <= -1)
                            keywords.push('Tychicus');
                                                                                                                                if($.inArray('Lord', keywords) <= -1)
                            keywords.push('Lord');
                                                                                                    if($.inArray('watch', keywords) <= -1)
                            keywords.push('watch');
                                            if($.inArray('watchman', keywords) <= -1)
                            keywords.push('watchman');
                                                                                                    if($.inArray('word', keywords) <= -1)
                            keywords.push('word');
                                                                                                                                if($.inArray('evil', keywords) <= -1)
                            keywords.push('evil');
                                            if($.inArray('wicked', keywords) <= -1)
                            keywords.push('wicked');
                                            if($.inArray('wickedness', keywords) <= -1)
                            keywords.push('wickedness');
                                                                                                    if($.inArray('Gentile', keywords) <= -1)
                            keywords.push('Gentile');
                                                                                                    if($.inArray('glory', keywords) <= -1)
                            keywords.push('glory');
                                            if($.inArray('glorious', keywords) <= -1)
                            keywords.push('glorious');
                                                                                                    if($.inArray('heaven', keywords) <= -1)
                            keywords.push('heaven');
                                            if($.inArray('sky', keywords) <= -1)
                            keywords.push('sky');
                                            if($.inArray('heavens', keywords) <= -1)
                            keywords.push('heavens');
                                            if($.inArray('heavenly', keywords) <= -1)
                            keywords.push('heavenly');
                                                                                                    if($.inArray('kingdom', keywords) <= -1)
                            keywords.push('kingdom');
                                                                                                    if($.inArray('Lord', keywords) <= -1)
                            keywords.push('Lord');
                                                                                                    if($.inArray('proclaim', keywords) <= -1)
                            keywords.push('proclaim');
                                            if($.inArray('proclamation', keywords) <= -1)
                            keywords.push('proclamation');
                                                                                                                                if($.inArray('Aquila', keywords) <= -1)
                            keywords.push('Aquila');
                                                                                                    if($.inArray('Corinth', keywords) <= -1)
                            keywords.push('Corinth');
                                            if($.inArray('Corinthians', keywords) <= -1)
                            keywords.push('Corinthians');
                                                                                                    if($.inArray('grace', keywords) <= -1)
                            keywords.push('grace');
                                            if($.inArray('gracious', keywords) <= -1)
                            keywords.push('gracious');
                                                                                                    if($.inArray('Lord', keywords) <= -1)
                            keywords.push('Lord');
                                                                                                    if($.inArray('Priscilla', keywords) <= -1)
                            keywords.push('Priscilla');
                                                                                                    if($.inArray('spirit', keywords) <= -1)
                            keywords.push('spirit');
                                            if($.inArray('spiritual', keywords) <= -1)
                            keywords.push('spiritual');

    (function($) {
        $("#chat_container").chat({
            step: step
        });
    }(jQuery));
                                                            </script>

<script src="<?php echo \Helpers\Url::templatePath() ?>/js/jquery.mark.min.js" type="text/javascript"></script>