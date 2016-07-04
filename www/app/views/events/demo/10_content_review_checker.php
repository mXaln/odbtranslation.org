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

<div id="chat_container" class="closed" style="right: -610px;">
    <div id="chat_new_msgs" class="chat_new_msgs" style="display: none;"></div>
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
                    <div class="missed" style="display: none;"></div>
                </div>
                <div id="evnt" class="col-sm-4 chat_tab">
                    <div>Event</div>
                    <div class="missed" style="display: none;"></div>
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
        <div class="main_content_title">Content Review (Check)</div>
        <div class="demo_title">Demo</div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <div class="main_content_text row">
                <div class="keywords_show" style="display:none;">Keywords</div>

                <div class="keywords_list_container">
                    <div class="keywords_list">
                        <div class="keywords-list-close glyphicon glyphicon-remove"></div>
                        <div class="labels_list">
                                                    </div>
                    </div>
                </div>

                <h4>English - Unlocked Literal Bible - New Testament - 2 Timothy 4:1-22</h4>

                                <div class="row">
                    <div class="col-sm-12 side_by_side_toggle">
                        <label><input type="checkbox" id="side_by_side_toggle" value="0"> Side by side review</label>
                    </div>
                </div>

                <div class="col-sm-12 side_by_side_content">
                                                                                                        <div class="row">
                                    <div class="col-sm-6">
                                        <p><strong><sup>1</sup></strong> I give this solemn command before God and Christ Jesus, who will judge the living and the dead, and because of his appearing and his kingdom:

  </p>
                                    </div>

                                    <div class="col-sm-6 verse_with_note">
                                        <p>
                                                                        <strong><sup>1</sup></strong>
                                            Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text                                                                                    </p>
                                        <div class="comments_number">
                                                                                    </div>

                                        <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                        <div class="comments">
                                                                                            <div class="my_comment" data="4:1"></div>
                                                                                    </div>
                                    </div>
                                </div>
                                                                                    <div class="row">
                                    <div class="col-sm-6">
                                        <p><strong><sup>2</sup></strong> Preach the Word. Be ready when it is convenient and when it is not. Reprove, rebuke, exhort, with all patience and teaching.
  </p>
                                    </div>

                                    <div class="col-sm-6 verse_with_note">
                                        <p>
                                                                        <strong><sup>2</sup></strong>
                                            Demo translation text, Demo translation text, Demo translation text, Demo translation text                                                                                    </p>
                                        <div class="comments_number">
                                                                                    </div>

                                        <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                        <div class="comments">
                                                                                            <div class="my_comment" data="4:2"></div>
                                                                                    </div>
                                    </div>
                                </div>
                                                                                    <div class="row">
                                    <div class="col-sm-6">
                                        <p><strong><sup>3</sup></strong> For the time will come when people will not endure sound teaching. Instead, they will heap up for themselves teachers according to their own desires. They will be tickling their hearing.

  </p>
                                    </div>

                                    <div class="col-sm-6 verse_with_note">
                                        <p>
                                                                        <strong><sup>3</sup></strong>
                                            Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text                                                                                    </p>
                                        <div class="comments_number">
                                                                                    </div>

                                        <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                        <div class="comments">
                                                                                            <div class="my_comment" data="4:3"></div>
                                                                                    </div>
                                    </div>
                                </div>
                                                                                    <div class="row">
                                    <div class="col-sm-6">
                                        <p><strong><sup>4</sup></strong> They will turn their hearing away from the truth, and they will turn aside to myths.

  </p>
                                    </div>

                                    <div class="col-sm-6 verse_with_note">
                                        <p>
                                                                        <strong><sup>4</sup></strong>
                                            Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text                                                                                    </p>
                                        <div class="comments_number">
                                                                                    </div>

                                        <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                        <div class="comments">
                                                                                            <div class="my_comment" data="4:4"></div>
                                                                                    </div>
                                    </div>
                                </div>
                                                                                    <div class="row">
                                    <div class="col-sm-6">
                                        <p><strong><sup>5</sup></strong> But you, be sober-minded in all things. Suffer hardship; do the work of an evangelist; fulfill your service.
  </p>
                                    </div>

                                    <div class="col-sm-6 verse_with_note">
                                        <p>
                                                                        <strong><sup>5</sup></strong>
                                            Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text                                                                                    </p>
                                        <div class="comments_number">
                                                                                    </div>

                                        <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                        <div class="comments">
                                                                                            <div class="my_comment" data="4:5"></div>
                                                                                    </div>
                                    </div>
                                </div>
                                                                                    <div class="row">
                                    <div class="col-sm-6">
                                        <p><strong><sup>6</sup></strong> For I am already being poured out. The time of my departure has come.

  </p>
                                    </div>

                                    <div class="col-sm-6 verse_with_note">
                                        <p>
                                                                        <strong><sup>6</sup></strong>
                                            Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text                                                                                    </p>
                                        <div class="comments_number">
                                                                                    </div>

                                        <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                        <div class="comments">
                                                                                            <div class="my_comment" data="4:6"></div>
                                                                                    </div>
                                    </div>
                                </div>
                                                <div class="chunk_divider col-sm-12"></div>
                                                                                                        <div class="row">
                                    <div class="col-sm-6">
                                        <p><strong><sup>7</sup></strong> I have competed in the good contest; I have finished the race; I have kept the faith.

  </p>
                                    </div>

                                    <div class="col-sm-6 verse_with_note">
                                        <p>
                                                                        <strong><sup>7</sup></strong>
                                            Demo translation text, Demo translation text, Demo translation text, Demo translation text                                                                                    </p>
                                        <div class="comments_number">
                                                                                    </div>

                                        <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                        <div class="comments">
                                                                                            <div class="my_comment" data="4:7"></div>
                                                                                    </div>
                                    </div>
                                </div>
                                                                                    <div class="row">
                                    <div class="col-sm-6">
                                        <p><strong><sup>8</sup></strong> The crown of righteousness has been reserved for me, which the Lord, the righteous judge, will give to me on that day. And not to me only, but also to all those who have loved his appearing.


  </p>
                                    </div>

                                    <div class="col-sm-6 verse_with_note">
                                        <p>
                                                                        <strong><sup>8</sup></strong>
                                            Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text                                                                                    </p>
                                        <div class="comments_number">
                                                                                    </div>

                                        <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                        <div class="comments">
                                                                                            <div class="my_comment" data="4:8"></div>
                                                                                    </div>
                                    </div>
                                </div>
                                                                                    <div class="row">
                                    <div class="col-sm-6">
                                        <p><strong><sup>9</sup></strong> Do your best to come to me quickly.

  </p>
                                    </div>

                                    <div class="col-sm-6 verse_with_note">
                                        <p>
                                                                        <strong><sup>9</sup></strong>
                                            Demo translation text, Demo translation text, Demo translation text, Demo translation text                                                                                    </p>
                                        <div class="comments_number">
                                                                                    </div>

                                        <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                        <div class="comments">
                                                                                            <div class="my_comment" data="4:9"></div>
                                                                                    </div>
                                    </div>
                                </div>
                                                                                    <div class="row">
                                    <div class="col-sm-6">
                                        <p><strong><sup>10</sup></strong> For Demas has left me. He loves this present world and has gone to Thessalonica. Crescens went to Galatia, and Titus went to Dalmatia.
  </p>
                                    </div>

                                    <div class="col-sm-6 verse_with_note">
                                        <p>
                                                                        <strong><sup>10</sup></strong>
                                            Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text                                                                                    </p>
                                        <div class="comments_number">
                                                                                    </div>

                                        <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                        <div class="comments">
                                                                                            <div class="my_comment" data="4:10"></div>
                                                                                    </div>
                                    </div>
                                </div>
                                                                                    <div class="row">
                                    <div class="col-sm-6">
                                        <p><strong><sup>11</sup></strong> Only Luke is with me. Get Mark and bring him with you because he is useful to me in the work.

  </p>
                                    </div>

                                    <div class="col-sm-6 verse_with_note">
                                        <p>
                                                                        <strong><sup>11</sup></strong>
                                            Demo translation text, Demo translation text, Demo translation text, Demo translation text,                                                                                    </p>
                                        <div class="comments_number">
                                                                                    </div>

                                        <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                        <div class="comments">
                                                                                            <div class="my_comment" data="4:11"></div>
                                                                                    </div>
                                    </div>
                                </div>
                                                                                    <div class="row">
                                    <div class="col-sm-6">
                                        <p><strong><sup>12</sup></strong> Tychicus I sent to Ephesus.

  </p>
                                    </div>

                                    <div class="col-sm-6 verse_with_note">
                                        <p>
                                                                        <strong><sup>12</sup></strong>
                                            Demo translation text, Demo translation text, Demo translation text, Demo translation text,                                                                                    </p>
                                        <div class="comments_number">
                                                                                    </div>

                                        <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                        <div class="comments">
                                                                                            <div class="my_comment" data="4:12"></div>
                                                                                    </div>
                                    </div>
                                </div>
                                                <div class="chunk_divider col-sm-12"></div>
                                                                                                        <div class="row">
                                    <div class="col-sm-6">
                                        <p><strong><sup>13</sup></strong> The cloak that I left at Troas with Carpus, bring it when you come, and the books, especially the parchments.
  </p>
                                    </div>

                                    <div class="col-sm-6 verse_with_note">
                                        <p>
                                                                        <strong><sup>13</sup></strong>
                                            Demo translation text, Demo translation text, Demo translation text, Demo translation text                                                                                    </p>
                                        <div class="comments_number">
                                                                                    </div>

                                        <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                        <div class="comments">
                                                                                            <div class="my_comment" data="4:13"></div>
                                                                                    </div>
                                    </div>
                                </div>
                                                                                    <div class="row">
                                    <div class="col-sm-6">
                                        <p><strong><sup>14</sup></strong> Alexander the coppersmith displayed many evil deeds against me. The Lord will repay to him according to his deeds.

  </p>
                                    </div>

                                    <div class="col-sm-6 verse_with_note">
                                        <p>
                                                                        <strong><sup>14</sup></strong>
                                            Demo translation text, Demo translation text, Demo translation text, Demo translation text                                                                                    </p>
                                        <div class="comments_number">
                                                                                    </div>

                                        <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                        <div class="comments">
                                                                                            <div class="my_comment" data="4:14"></div>
                                                                                    </div>
                                    </div>
                                </div>
                                                                                    <div class="row">
                                    <div class="col-sm-6">
                                        <p><strong><sup>15</sup></strong> You also should guard yourself against him, because he greatly opposed our words.

  </p>
                                    </div>

                                    <div class="col-sm-6 verse_with_note">
                                        <p>
                                                                        <strong><sup>15</sup></strong>
                                            Demo translation text, Demo translation text, Demo translation text, Demo translation text                                                                                    </p>
                                        <div class="comments_number">
                                                                                    </div>

                                        <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                        <div class="comments">
                                                                                            <div class="my_comment" data="4:15"></div>
                                                                                    </div>
                                    </div>
                                </div>
                                                                                    <div class="row">
                                    <div class="col-sm-6">
                                        <p><strong><sup>16</sup></strong> At my first defense, no one stood with me. Instead, everyone left me. May it not be counted against them.
  </p>
                                    </div>

                                    <div class="col-sm-6 verse_with_note">
                                        <p>
                                                                        <strong><sup>16</sup></strong>
                                            Demo translation text, Demo translation text, Demo translation text, Demo translation text                                                                                    </p>
                                        <div class="comments_number">
                                                                                    </div>

                                        <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                        <div class="comments">
                                                                                            <div class="my_comment" data="4:16"></div>
                                                                                    </div>
                                    </div>
                                </div>
                                                <div class="chunk_divider col-sm-12"></div>
                                                                                                        <div class="row">
                                    <div class="col-sm-6">
                                        <p><strong><sup>17</sup></strong> But the Lord stood by me and strengthened me so that, through me, the proclamation might be fully fulfilled, and that all the Gentiles might hear. I was rescued out of the lion's mouth.

  </p>
                                    </div>

                                    <div class="col-sm-6 verse_with_note">
                                        <p>
                                                                        <strong><sup>17</sup></strong>
                                            Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text                                                                                    </p>
                                        <div class="comments_number">
                                                                                    </div>

                                        <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                        <div class="comments">
                                                                                            <div class="my_comment" data="4:17"></div>
                                                                                    </div>
                                    </div>
                                </div>
                                                                                    <div class="row">
                                    <div class="col-sm-6">
                                        <p><strong><sup>18</sup></strong> The Lord will rescue me from every evil deed and will save me for his heavenly kingdom. To him be the glory forever and ever. Amen.


  </p>
                                    </div>

                                    <div class="col-sm-6 verse_with_note">
                                        <p>
                                                                        <strong><sup>18</sup></strong>
                                            Demo translation text, Demo translation text, Demo translation text, Demo translation text                                                                                    </p>
                                        <div class="comments_number">
                                                                                    </div>

                                        <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                        <div class="comments">
                                                                                            <div class="my_comment" data="4:18"></div>
                                                                                    </div>
                                    </div>
                                </div>
                                                                                    <div class="row">
                                    <div class="col-sm-6">
                                        <p><strong><sup>19</sup></strong> Greet Priscilla, Aquila, and the house of Onesiphorus.

  </p>
                                    </div>

                                    <div class="col-sm-6 verse_with_note">
                                        <p>
                                                                        <strong><sup>19</sup></strong>
                                            Demo translation text, Demo translation text, Demo translation text, Demo translation text                                                                                    </p>
                                        <div class="comments_number">
                                                                                    </div>

                                        <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                        <div class="comments">
                                                                                            <div class="my_comment" data="4:19"></div>
                                                                                    </div>
                                    </div>
                                </div>
                                                                                    <div class="row">
                                    <div class="col-sm-6">
                                        <p><strong><sup>20</sup></strong> Erastus remained at Corinth, but Trophimus I left sick at Miletus.

  </p>
                                    </div>

                                    <div class="col-sm-6 verse_with_note">
                                        <p>
                                                                        <strong><sup>20</sup></strong>
                                            Demo translation text, Demo translation text, Demo translation text, Demo translation text                                                                                    </p>
                                        <div class="comments_number">
                                                                                    </div>

                                        <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                        <div class="comments">
                                                                                            <div class="my_comment" data="4:20"></div>
                                                                                    </div>
                                    </div>
                                </div>
                                                                                    <div class="row">
                                    <div class="col-sm-6">
                                        <p><strong><sup>21</sup></strong> Do your best to come before winter. Eubulus greets you, also Pudens, Linus, Claudia, and all the brothers.



  </p>
                                    </div>

                                    <div class="col-sm-6 verse_with_note">
                                        <p>
                                                                        <strong><sup>21</sup></strong>
                                            Demo translation text, Demo translation text, Demo translation text, Demo translation text                                                                                    </p>
                                        <div class="comments_number">
                                                                                    </div>

                                        <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                        <div class="comments">
                                                                                            <div class="my_comment" data="4:21"></div>
                                                                                    </div>
                                    </div>
                                </div>
                                                                                    <div class="row">
                                    <div class="col-sm-6">
                                        <p><strong><sup>22</sup></strong> May the Lord be with your spirit. May grace be with you.</p>
                                    </div>

                                    <div class="col-sm-6 verse_with_note">
                                        <p>
                                                                        <strong><sup>22</sup></strong>
                                            Demo translation text, Demo translation text, Demo translation text, Demo translation text                                                                                    </p>
                                        <div class="comments_number">
                                                                                    </div>

                                        <img class="editComment" data="0:0" width="16px" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note">

                                        <div class="comments">
                                                                                            <div class="my_comment" data="4:22"></div>
                                                                                    </div>
                                    </div>
                                </div>
                                                <div class="chunk_divider col-sm-12"></div>
                                    </div>
                
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

                    <button id="next_step" onclick="window.location.href='<?php echo DIR ?>events/demo/pray'; return false;" class="btn btn-primary" disabled="">Next step</button>
                </form>
            </div>
                    </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps">
                <div class="help_title_steps">HELP</div>

                <div class="clear"></div>

                <div class="help_name_steps"><span>Step 8:</span> Content Review</div>
                <div class="help_descr_steps">
                    <ul><li><b>PURPOSE:</b> to ensure that each verse and chunk and chapter accurately communicate the same message in the target language</li><li>Contact the translator on a suitable conversation platform: Skype, Hangout, phone, etc.</li><li>The review can be done in two ways. The default view assumes the ... <div class="show_tutorial_popup"> &gt;&gt;&gt; Show more</div></li></ul>
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
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/icons/content-review.png" width="100px" height="100px">
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/big/content-review.png" width="280px" height="280px">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="content-review_checker" data2="checker" type="checkbox" value="0"> Don't show this message again</label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3>Content Review</h3>
            <ul><li><b>PURPOSE:</b> to ensure that each verse and chunk and chapter accurately communicate the same message in the target language</li><li>Contact the translator on a suitable conversation platform: Skype, Hangout, phone, etc.</li><li>The review can be done in two ways. The default view assumes the first method. <ol><li>If you know only the source language, the check must be done by back translation. In this case, the translator will read the translated text verse by verse, then they or a second person will back translate into the source, and you will compare what you hear to the source text they are reading.</li><li>If you are fluent in both languages, you can either use method one, or choose instead to review the translation side by side with the source. In this case, press the “Side by Side” toggle button to switch views. You then compare the two yourself.</li></ol></li><li>In either case, you should ask questions about anything that doesn’t seem to transfer accurately or completely to the target language.</li><li>Spend no more than 30 minutes on this exercise. Do not waste time on disagreements. In such cases, leave the translated text as is, record a note on the appropriate verse, and move on.</li></ul>
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