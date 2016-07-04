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
                <div style="display: block;" id="chk" class="col-sm-4 chat_tab active">
                    <div>Checking</div>
                    <div class="missed"></div>
                </div>
                <div id="evnt" class="col-sm-4 chat_tab">
                    <div>Event</div>
                    <div class="missed"></div>
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
        <ul id="online" class="panel-body"><li>Gen2Pet</li><li class="mine">mpat1977</li><li>mSimpson (facilitator)</li></ul>
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
        <div class="main_content_title">Keyword Check</div>
        <div class="demo_title">Demo</div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <form action="" method="post" id="main_form">
                <div class="main_content_text row">
                                        
                    
                    <h4>English - Unlocked Literal Bible - New Testament - 2 Timothy 4:1-22</h4>

                    <div class="col-sm-12 no_padding">
                                                                                                                    <div class="row chunk_verse">
                                    <div class="col-sm-6 verse"><strong><sup>1</sup></strong>
 I give this solemn command before God and Christ Jesus, who will judge 
the living and the dead, and because of his appearing and his kingdom:

  </div>
                                    <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 93px; overflow: hidden; word-wrap: break-word; height: 111px;" name="chunks[0][verses][]" class="col-sm-6 peer_verse_ta textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <div class="comments_number">
                                                                            </div>
                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                                                    </div>
                                    </div>
                                                                                                    <div class="row chunk_verse">
                                    <div class="col-sm-6 verse"><strong><sup>2</sup></strong> Preach the Word. Be ready when it is convenient and when it is not. Reprove, rebuke, exhort, with all patience and teaching.
  </div>
                                    <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 62px; overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[0][verses][]" class="col-sm-6 peer_verse_ta textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <div class="comments_number">
                                                                            </div>
                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                                                    </div>
                                    </div>
                                                                                                    <div class="row chunk_verse">
                                    <div class="col-sm-6 verse"><strong><sup>3</sup></strong>
 For the time will come when people will not endure sound teaching. 
Instead, they will heap up for themselves teachers according to their 
own desires. They will be tickling their hearing.

  </div>
                                    <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 124px; overflow: hidden; word-wrap: break-word; height: 142px;" name="chunks[0][verses][]" class="col-sm-6 peer_verse_ta textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <div class="comments_number">
                                                                            </div>
                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                                                    </div>
                                    </div>
                                                                                                    <div class="row chunk_verse">
                                    <div class="col-sm-6 verse"><strong><sup>4</sup></strong> They will turn their hearing away from the truth, and they will turn aside to myths.

  </div>
                                    <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 62px; overflow: hidden; word-wrap: break-word; height: 111px;" name="chunks[0][verses][]" class="col-sm-6 peer_verse_ta textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <div class="comments_number">
                                                                            </div>
                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                                                    </div>
                                    </div>
                                                                                                    <div class="row chunk_verse">
                                    <div class="col-sm-6 verse"><strong><sup>5</sup></strong> But you, be sober-minded in all things. Suffer hardship; do the work of an evangelist; fulfill your service.
  </div>
                                    <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 62px; overflow: hidden; word-wrap: break-word; height: 111px;" name="chunks[0][verses][]" class="col-sm-6 peer_verse_ta textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <div class="comments_number">
                                                                            </div>
                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                                                    </div>
                                    </div>
                                                                                                    <div class="row chunk_verse">
                                    <div class="col-sm-6 verse"><strong><sup>6</sup></strong> For I am already being poured out. The time of my departure has come.

  </div>
                                    <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 62px; overflow: hidden; word-wrap: break-word; height: 111px;" name="chunks[0][verses][]" class="col-sm-6 peer_verse_ta textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <div class="comments_number">
                                                                            </div>
                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                                                    </div>
                                    </div>
                                                                <div class="chunk_divider col-sm-12"></div>
                                                                                                                    <div class="row chunk_verse">
                                    <div class="col-sm-6 verse"><strong><sup>7</sup></strong> I have competed in the good contest; I have finished the race; I have kept the faith.

  </div>
                                    <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 62px; overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[1][verses][]" class="col-sm-6 peer_verse_ta textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <div class="comments_number">
                                                                            </div>
                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                                                    </div>
                                    </div>
                                                                                                    <div class="row chunk_verse">
                                    <div class="col-sm-6 verse"><strong><sup>8</sup></strong>
 The crown of righteousness has been reserved for me, which the Lord, 
the righteous judge, will give to me on that day. And not to me only, 
but also to all those who have loved his appearing.


  </div>
                                    <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 124px; overflow: hidden; word-wrap: break-word; height: 142px;" name="chunks[1][verses][]" class="col-sm-6 peer_verse_ta textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <div class="comments_number">
                                                                            </div>
                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                                                    </div>
                                    </div>
                                                                                                    <div class="row chunk_verse">
                                    <div class="col-sm-6 verse"><strong><sup>9</sup></strong> Do your best to come to me quickly.

  </div>
                                    <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 31px; overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[1][verses][]" class="col-sm-6 peer_verse_ta textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <div class="comments_number">
                                                                            </div>
                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                                                    </div>
                                    </div>
                                                                                                    <div class="row chunk_verse">
                                    <div class="col-sm-6 verse"><strong><sup>10</sup></strong>
 For Demas has left me. He loves this present world and has gone to 
Thessalonica. Crescens went to Galatia, and Titus went to Dalmatia.
  </div>
                                    <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 93px; overflow: hidden; word-wrap: break-word; height: 111px;" name="chunks[1][verses][]" class="col-sm-6 peer_verse_ta textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <div class="comments_number">
                                                                            </div>
                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                                                    </div>
                                    </div>
                                                                                                    <div class="row chunk_verse">
                                    <div class="col-sm-6 verse"><strong><sup>11</sup></strong> Only Luke is with me. Get Mark and bring him with you because he is useful to me in the work.

  </div>
                                    <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 62px; overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[1][verses][]" class="col-sm-6 peer_verse_ta textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text,</textarea>

                                    <div class="comments_number">
                                                                            </div>
                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                                                    </div>
                                    </div>
                                                                                                    <div class="row chunk_verse">
                                    <div class="col-sm-6 verse"><strong><sup>12</sup></strong> Tychicus I sent to Ephesus.

  </div>
                                    <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 31px; overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[1][verses][]" class="col-sm-6 peer_verse_ta textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text,</textarea>

                                    <div class="comments_number">
                                                                            </div>
                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                                                    </div>
                                    </div>
                                                                <div class="chunk_divider col-sm-12"></div>
                                                                                                                    <div class="row chunk_verse">
                                    <div class="col-sm-6 verse"><strong><sup>13</sup></strong> The cloak that I left at Troas with Carpus, bring it when you come, and the books, especially the parchments.
  </div>
                                    <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 62px; overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[2][verses][]" class="col-sm-6 peer_verse_ta textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <div class="comments_number">
                                                                            </div>
                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                                                    </div>
                                    </div>
                                                                                                    <div class="row chunk_verse">
                                    <div class="col-sm-6 verse"><strong><sup>14</sup></strong> Alexander the coppersmith displayed many evil deeds against me. The Lord will repay to him according to his deeds.

  </div>
                                    <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 62px; overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[2][verses][]" class="col-sm-6 peer_verse_ta textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <div class="comments_number">
                                                                            </div>
                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                                                    </div>
                                    </div>
                                                                                                    <div class="row chunk_verse">
                                    <div class="col-sm-6 verse"><strong><sup>15</sup></strong> You also should guard yourself against him, because he greatly opposed our words.

  </div>
                                    <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 62px; overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[2][verses][]" class="col-sm-6 peer_verse_ta textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <div class="comments_number">
                                                                            </div>
                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                                                    </div>
                                    </div>
                                                                                                    <div class="row chunk_verse">
                                    <div class="col-sm-6 verse"><strong><sup>16</sup></strong> At my first defense, no one stood with me. Instead, everyone left me. May it not be counted against them.
  </div>
                                    <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 62px; overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[2][verses][]" class="col-sm-6 peer_verse_ta textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <div class="comments_number">
                                                                            </div>
                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                                                    </div>
                                    </div>
                                                                <div class="chunk_divider col-sm-12"></div>
                                                                                                                    <div class="row chunk_verse">
                                    <div class="col-sm-6 verse"><strong><sup>17</sup></strong>
 But the Lord stood by me and strengthened me so that, through me, the 
proclamation might be fully fulfilled, and that all the Gentiles might 
hear. I was rescued out of the lion's mouth.

  </div>
                                    <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 93px; overflow: hidden; word-wrap: break-word; height: 111px;" name="chunks[3][verses][]" class="col-sm-6 peer_verse_ta textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <div class="comments_number">
                                                                            </div>
                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                                                    </div>
                                    </div>
                                                                                                    <div class="row chunk_verse">
                                    <div class="col-sm-6 verse"><strong><sup>18</sup></strong>
 The Lord will rescue me from every evil deed and will save me for his 
heavenly kingdom. To him be the glory forever and ever. Amen.


  </div>
                                    <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 93px; overflow: hidden; word-wrap: break-word; height: 93px;" name="chunks[3][verses][]" class="col-sm-6 peer_verse_ta textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <div class="comments_number">
                                                                            </div>
                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                                                    </div>
                                    </div>
                                                                                                    <div class="row chunk_verse">
                                    <div class="col-sm-6 verse"><strong><sup>19</sup></strong> Greet Priscilla, Aquila, and the house of Onesiphorus.

  </div>
                                    <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 31px; overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[3][verses][]" class="col-sm-6 peer_verse_ta textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <div class="comments_number">
                                                                            </div>
                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                                                    </div>
                                    </div>
                                                                                                    <div class="row chunk_verse">
                                    <div class="col-sm-6 verse"><strong><sup>20</sup></strong> Erastus remained at Corinth, but Trophimus I left sick at Miletus.

  </div>
                                    <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 62px; overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[3][verses][]" class="col-sm-6 peer_verse_ta textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <div class="comments_number">
                                                                            </div>
                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                                                    </div>
                                    </div>
                                                                                                    <div class="row chunk_verse">
                                    <div class="col-sm-6 verse"><strong><sup>21</sup></strong> Do your best to come before winter. Eubulus greets you, also Pudens, Linus, Claudia, and all the brothers.



  </div>
                                    <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 62px; overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[3][verses][]" class="col-sm-6 peer_verse_ta textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <div class="comments_number">
                                                                            </div>
                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                                                    </div>
                                    </div>
                                                                                                    <div class="row chunk_verse">
                                    <div class="col-sm-6 verse"><strong><sup>22</sup></strong> May the Lord be with your spirit. May grace be with you.</div>
                                    <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 31px; overflow: hidden; word-wrap: break-word; height: 80px;" name="chunks[3][verses][]" class="col-sm-6 peer_verse_ta textarea">Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>

                                    <div class="comments_number">
                                                                            </div>
                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                            </div>
                                                                    </div>
                                    </div>
                                                                <div class="chunk_divider col-sm-12"></div>
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

                    <button id="next_step" onclick="window.location.href='<?php echo DIR ?>events/demo/content_review'; return false;" class="btn btn-primary" disabled="disabled">Next step</button>
                </div>
            </form>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps">
                <div class="help_title_steps">HELP</div>

                <div class="clear"></div>

                <div class="help_name_steps"><span>Step 7:</span> Keyword Check</div>
                <div class="help_descr_steps">
                    <ul><li><b>PURPOSE:</b> to ensure certain significant words are present in the translated text and accurately expressed</li><li>Your
 text has been submitted to someone else on your translation team 
besides your translation partner for this step. When someone has 
accepted the task, they will see only the... <div class="show_tutorial_popup"> &gt;&gt;&gt; Show more</div></li></ul>
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
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/icons/keyword-check.png" height="100px" width="100px">
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/big/keyword-check.png" height="280px" width="280px">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="keyword-check" value="0" type="checkbox"> Don't show this message again</label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3>Keyword Check</h3>
            <ul><li><b>PURPOSE:</b> to ensure certain significant words are present in the translated text and accurately expressed</li><li>Your
 text has been submitted to someone else on your translation team 
besides your translation partner for this step. When someone has 
accepted the task, they will see only the source from which you have 
translated.</li><li>Contact your checker on a suitable conversation platform: Skype, Hangout, phone, etc.</li><li>Your
 checker will proceed through each verse of the entire chapter (all 
chunks) and ask you about how you have translated certain words.</li><li>Make any appropriate changes to your text as the discussion proceeds.</li><li>Spend
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