<div class="comment_div panel panel-default">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo __("write_note_title")?></h1>
        <span class="editor-close btn btn-success"><?php echo __("save") ?></span>
        <span class="xbtn glyphicon glyphicon-remove"></span>
    </div>
    <textarea style="overflow-x: hidden; word-wrap: break-word; overflow-y: visible;" class="textarea textarea_editor"></textarea>
    <div class="other_comments_list"></div>
    <img src="<?php echo template_url("img/loader.gif") ?>" class="commentEditorLoader">
</div>

<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title">
            <div class="demo_title"><?php echo __("demo") . " (".__("vsail").")" ?></div>
            <div><?php echo __("step_num", [7]) . ": " . __("content-review")?></div>
        </div>
        <div class="demo_video">
            <span class="glyphicon glyphicon-play"></span>
            <a href="#"><?php echo __("demo_video"); ?></a>
        </div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <div class="main_content_text row" style="padding-left: 15px">
                <h4>English - <?php echo __("sun") ?> - <?php echo __("new_test") ?> - <span class="book_name">2 Timothy 2:1-26</span></h4>

                <ul class="nav nav-tabs">
                    <li role="presentation" id="source_scripture" class="my_tab">
                        <a href="#"><?php echo __("source_text") ?></a>
                    </li>
                    <li role="presentation" id="rearrange" class="my_tab">
                        <a href="#"><?php echo __("rearrange") ?></a>
                    </li>
                </ul>

                <div id="source_scripture_content" class="col-sm-12 no_padding my_content shown">
                    <div class="row chunk_block">
                        <div class="chunk_verses col-sm-6" dir="ltr">
                            <div> <strong class="ltr"> <sup>1</sup> </strong> <div class="kwverse_2_0_1">You therefore, my <b data="0">child</b>, be strengthened in the grace that is in <b data="0">Christ Jesus</b>.</div></div>
                            <div> <strong class="ltr"> <sup>2</sup> </strong> <div class="kwverse_2_0_2">And the things you heard from me among many witnesses, entrust them to <b data="0">faithful</b> people who will be able to teach others also.</div></div>
                            <div> <strong class="ltr"> <sup>3</sup> </strong> <div class="kwverse_2_0_3">Suffer hardship with me, as a good soldier of <b data="0">Christ Jesus</b>.</div></div>
                        </div>
                        <div class="col-sm-6 editor_area" dir="ltr">
                            <div class="vnote">
                                <textarea name="chunks[]"
                                          class="col-sm-6 verse_ta textarea sun_content">                             </textarea>
                                <div class="comments_number hasComment">2 </div>
                                <img class="editComment" data="0:0" src="<?php echo template_url("img/edit.png") ?>" title="<?php echo __("write_note_title")?>" width="16">
                                <div class="comments">
                                    <div class="other_comments">
                                        <div><span>Anna S. (L1):</span> This is comment of a translator</div>
                                    </div>
                                    <div class="my_comment">This is a comment of checker</div>
                                </div>
                                <div class="clear"></div>
                            </div>
                        </div>
                    </div>
                    <div class="chunk_divider col-sm-12"></div>
                    <div class="row chunk_block">
                        <div class="chunk_verses col-sm-6" dir="ltr">
                            <div> <strong class="ltr"> <sup>4</sup> </strong> <div class="kwverse_2_0_4">No <b data="0">soldier</b> serves while entangled in the affairs of this life, so that he may please his superior officer.</div></div>
                            <div> <strong class="ltr"> <sup>5</sup> </strong> <div class="kwverse_2_0_5">Also, if someone competes as an <b data="0">athlete</b>, he is not crowned unless he competes by the rules.</div></div>
                            <div> <strong class="ltr"> <sup>6</sup> </strong> <div class="kwverse_2_0_6">It is necessary that the hardworking farmer receive his share of the crops first.</div></div>
                            <div> <strong class="ltr"> <sup>7</sup> </strong> <div class="kwverse_2_0_7">Think about what <b data="0">I</b> am saying, for the <b data="0">Lord</b> will give you understanding in everything.</div></div>
                        </div>
                        <div class="col-sm-6 editor_area" dir="ltr">
                            <div class="vnote">
                                <textarea name="chunks[]"
                                          class="col-sm-6 peer_verse_ta textarea sun_content">                         </textarea>
                                <div class="comments_number hasComment">1 </div>
                                <img class="editComment" data="0:0" src="<?php echo template_url("img/edit.png") ?>" title="<?php echo __("write_note_title")?>" width="16">
                                <div class="comments">
                                    <div class="other_comments">

                                    </div>
                                    <div class="my_comment">This is a comment of checker</div>
                                </div>
                                <div class="clear"></div>
                            </div>
                        </div>
                    </div>
                    <div class="chunk_divider col-sm-12"></div>
                    <div class="row chunk_block">
                        <div class="chunk_verses col-sm-6" dir="ltr">
                            <div> <strong class="ltr"> <sup>8</sup> </strong> <div class="kwverse_2_0_8">Remember Jesus Christ, from David's seed, who was raised from the dead ones. This is according to my gospel message,</div></div>
                            <div> <strong class="ltr"> <sup>9</sup> </strong> <div class="kwverse_2_0_9">for which I am suffering to the point of being chained as a criminal. But the word of God is not chained.</div></div>
                            <div> <strong class="ltr"> <sup>10</sup> </strong> <div class="kwverse_2_0_10">Therefore I endure all things for those who are chosen, so that they also may obtain the salvation that is in Christ Jesus, with eternal glory.</div></div>
                        </div>
                        <div class="col-sm-6 editor_area" dir="ltr">
                            <div class="vnote">
                                <textarea name="chunks[]"
                                          class="col-sm-6 peer_verse_ta textarea sun_content">                                   </textarea>
                                <div class="comments_number "> </div>
                                <img class="editComment" data="0:0" src="<?php echo template_url("img/edit.png") ?>" title="<?php echo __("write_note_title")?>" width="16">
                                <div class="comments"> </div>
                                <div class="clear"></div>
                            </div>
                        </div>
                    </div>
                    <div class="chunk_divider col-sm-12"></div>
                    <div class="row chunk_block">
                        <div class="chunk_verses col-sm-6" dir="ltr">
                            <div> <strong class="ltr"> <sup>11</sup> </strong> <div class="kwverse_2_0_11">This saying is trustworthy: "If we have died with him, we will also live with him.</div></div>
                            <div> <strong class="ltr"> <sup>12</sup> </strong> <div class="kwverse_2_0_12">If we endure, we will also reign with him. If we deny him, he also will deny us.</div></div>
                            <div> <strong class="ltr"> <sup>13</sup> </strong> <div class="kwverse_2_0_13">if we are unfaithful, he remains faithful, for he cannot deny himself."</div></div>
                            <div> <strong class="ltr"> <sup>14</sup> </strong> <div class="kwverse_2_0_14">Keep reminding them of these things. Warn them before God not to quarrel about words. Because of this there is nothing useful. Because of this there is destruction for those who listen.</div></div>
                        </div>
                        <div class="col-sm-6 editor_area" dir="ltr">
                            <div class="vnote">
                                <textarea name="chunks[]"
                                          class="col-sm-6 peer_verse_ta textarea sun_content">                        </textarea>
                                <div class="comments_number "> </div>
                                <img class="editComment" data="0:0" src="<?php echo template_url("img/edit.png") ?>" title="<?php echo __("write_note_title")?>" width="16">
                                <div class="comments"> </div>
                                <div class="clear"></div>
                            </div>
                        </div>
                    </div>
                    <div class="chunk_divider col-sm-12"></div>
                    <div class="row chunk_block">
                        <div class="chunk_verses col-sm-6" dir="ltr">
                            <div> <strong class="ltr"> <sup>15</sup> </strong> <div class="kwverse_2_0_15">Do your best to present yourself to God as one approved, a worker who has no reason to be ashamed, who accurately teaches the word of truth.</div></div>
                            <div> <strong class="ltr"> <sup>16</sup> </strong> <div class="kwverse_2_0_16">Avoid profane talk, which leads to more and more godlessness.</div></div>
                            <div> <strong class="ltr"> <sup>17</sup> </strong> <div class="kwverse_2_0_17">Their talk will spread like gangrene. Among whom are Hymenaeus and Philetus.</div></div>
                        </div>
                        <div class="col-sm-6 editor_area" dir="ltr">
                            <div class="vnote">
                                <textarea name="chunks[]"
                                          class="col-sm-6 peer_verse_ta textarea sun_content">                           </textarea>
                                <div class="comments_number "> </div>
                                <img class="editComment" data="0:0" src="<?php echo template_url("img/edit.png") ?>" title="<?php echo __("write_note_title")?>" width="16">
                                <div class="comments"> </div>
                                <div class="clear"></div>
                            </div>
                        </div>
                    </div>
                    <div class="chunk_divider col-sm-12"></div>
                    <div class="row chunk_block">
                        <div class="chunk_verses col-sm-6" dir="ltr">
                            <div> <strong class="ltr"> <sup>18</sup> </strong> <div class="kwverse_2_0_18">These are men who have missed the truth. They say that the resurrection has already happened. They overturn the faith of some.</div></div>
                            <div> <strong class="ltr"> <sup>19</sup> </strong> <div class="kwverse_2_0_19">However, the firm foundation of God stands. It has this inscription: "The Lord knows those who are his" and "Everyone who names the name of the Lord must depart from unrighteousness."</div></div>
                            <div> <strong class="ltr"> <sup>20</sup> </strong> <div class="kwverse_2_0_20">In a wealthy home, there are not only containers of gold and silver. There are also containers of wood and clay. Some of these are for honorable use, and some for dishonorable.</div></div>
                        </div>
                        <div class="col-sm-6 editor_area" dir="ltr">
                            <div class="vnote">
                                <textarea name="chunks[]"
                                          class="col-sm-6 peer_verse_ta textarea sun_content">                           </textarea>
                                <div class="comments_number "> </div>
                                <img class="editComment" data="0:0" src="<?php echo template_url("img/edit.png") ?>" title="<?php echo __("write_note_title")?>" width="16">
                                <div class="comments"> </div>
                                <div class="clear"></div>
                            </div>
                        </div>
                    </div>
                    <div class="chunk_divider col-sm-12"></div>
                    <div class="row chunk_block">
                        <div class="chunk_verses col-sm-6" dir="ltr">
                            <div> <strong class="ltr"> <sup>21</sup> </strong> <div class="kwverse_2_0_21">If someone cleans himself from dishonorable use, he is an honorable container. He is set apart, useful to the Master, and prepared for every good work.</div></div>
                            <div> <strong class="ltr"> <sup>22</sup> </strong> <div class="kwverse_2_0_22">Flee youthful lusts. Pursue righteousness, faith, love, and peace with those who call on the Lord out of a clean heart.</div></div>
                            <div> <strong class="ltr"> <sup>23</sup> </strong> <div class="kwverse_2_0_23">But refuse foolish and ignorant questions. You know that they give birth to arguments.</div></div>
                        </div>
                        <div class="col-sm-6 editor_area" dir="ltr">
                            <div class="vnote">
                                <textarea name="chunks[]"
                                          class="col-sm-6 peer_verse_ta textarea sun_content">                       </textarea>
                                <div class="comments_number "> </div>
                                <img class="editComment" data="0:0" src="<?php echo template_url("img/edit.png") ?>" title="<?php echo __("write_note_title")?>" width="16">
                                <div class="comments"> </div>
                                <div class="clear"></div>
                            </div>
                        </div>
                    </div>
                    <div class="chunk_divider col-sm-12"></div>
                    <div class="row chunk_block">
                        <div class="chunk_verses col-sm-6" dir="ltr">
                            <div> <strong class="ltr"> <sup>24</sup> </strong> <div class="kwverse_2_0_24">The Lord's servant must not quarrel. Instead he must be gentle toward all, able to teach, and patient.</div></div>
                            <div> <strong class="ltr"> <sup>25</sup> </strong> <div class="kwverse_2_0_25">He must in meekness educate those who oppose him. God may perhaps give them repentance for the knowledge of the truth.</div></div>
                            <div> <strong class="ltr"> <sup>26</sup> </strong> <div class="kwverse_2_0_26">They may become sober again and leave the devil's trap, after they have been captured by him for his will.</div></div>
                        </div>
                        <div class="col-sm-6 editor_area" dir="ltr">
                            <div class="vnote">
                                <textarea name="chunks[]"
                                          class="col-sm-6 peer_verse_ta textarea sun_content">                        </textarea>
                                <div class="comments_number "> </div>
                                <img class="editComment" data="0:0" src="<?php echo template_url("img/edit.png") ?>" title="<?php echo __("write_note_title")?>" width="16">
                                <div class="comments"> </div>
                                <div class="clear"></div>
                            </div>
                        </div>
                    </div>
                    <div class="chunk_divider col-sm-12"></div>
                </div>

                <div id="rearrange_content" class="my_content">
                    <div class="row chunk_block">
                        <div class="chunk_verses col-sm-12" dir="ltr"> <strong class="ltr"> <sup>1-3</sup> </strong> You child strengthened grace Christ Jesus. things you heard among many witnesses
                            entrust them faithful people teach others. Suffer hardship me, good soldier Christ Jesus. </div>
                    </div>
                    <div class="chunk_divider col-sm-12"></div>
                    <div class="row chunk_block">
                        <div class="chunk_verses col-sm-12" dir="ltr"> <strong class="ltr"> <sup>4-7</sup> </strong> soldier serves affairs life, he please officer.
                            someone competes athlete, he not crowned he competes rules.
                            hardworking farmer receive share crops.
                            Think I saying, Lord give you understanding. </div>
                    </div>
                    <div class="chunk_divider col-sm-12"></div>
                    <div class="row chunk_block">
                        <div class="chunk_verses col-sm-12" dir="ltr"> <strong class="ltr"> <sup>8-11</sup> </strong> Remember Jesus Christ, David's seed, raised dead. gospel message,
                            I suffering point chained criminal. word God not chained.
                            I endure all things those who chosen, they obtain salvation Christ Jesus, eternal glory. </div>
                    </div>
                    <div class="chunk_divider col-sm-12"></div>
                    <div class="row chunk_block">
                        <div class="chunk_verses col-sm-12" dir="ltr"> <strong class="ltr"> <sup>12-14</sup> </strong> saying trustworthy: "we died with him, we live with him.
                            we endure, we reign with him. we deny him, he deny us.
                            we unfaithful, he remains faithful, he cannot deny himself."
                            Keep reminding them these things. Warn them before God not quarrel about words. destruction those who listen. </div>
                    </div>
                    <div class="chunk_divider col-sm-12"></div>
                    <div class="row chunk_block">
                        <div class="chunk_verses col-sm-12" dir="ltr"> <strong class="ltr"> <sup>15-17</sup> </strong> Do best present yourself God, worker has no reason be ashamed, who accurately teaches word truth.
                            Avoid profane talk, leads more godlessness.
                            Their talk will spread gangrene. Among whom Hymenaeus and Philetus. </div>
                    </div>
                    <div class="chunk_divider col-sm-12"></div>
                    <div class="row chunk_block">
                        <div class="chunk_verses col-sm-12" dir="ltr"> <strong class="ltr"> <sup>18-19</sup> </strong> These men who missed truth. They say resurrection happened. They overturn faith.
                            firm foundation God stands. It has inscription: "Lord knows those his" "Everyone names name Lord depart unrighteousness."
                            wealthy home, not containers gold silver. containers wood clay. these honorable use, some dishonorable. </div>
                    </div>
                    <div class="chunk_divider col-sm-12"></div>
                    <div class="row chunk_block">
                        <div class="chunk_verses col-sm-12" dir="ltr"> <strong class="ltr"> <sup>20-22</sup> </strong> someone cleans himself dishonorable use, he honorable container. He set apart, useful Master, prepared good work.
                            Flee youthful lusts. Pursue righteousness, faith, love, peace who call Lord clean heart.
                            refuse foolish ignorant questions. You know they give birth arguments. </div>
                    </div>
                    <div class="chunk_divider col-sm-12"></div>
                    <div class="row chunk_block">
                        <div class="chunk_verses col-sm-12" dir="ltr"> <strong class="ltr"> <sup>23-26</sup> </strong> Lord's servant must not quarrel. he must gentle, teach, patient.
                            He must meekness educate those who oppose him. God give them repentance knowledge truth.
                            They become sober leave devil's trap, they captured him his will. </div>
                    </div>
                    <div class="chunk_divider col-sm-12"></div>
                </div>
            </div>

            <div class="main_content_footer row">
                <form action="" method="post">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                        <label><input name="confirm_step" id="confirm_step" value="1" type="checkbox"> <?php echo __("confirm_yes")?></label>
                    </div>

                    <button id="next_step" class="btn btn-primary" disabled="disabled"><?php echo __("next_step")?></button>
                </form>
                <div class="step_right"><?php echo __("step_num", [7])?></div>
            </div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps is_checker_page_help">
                <div class="help_hide toggle-help glyphicon glyphicon-eye-close" title="<?php echo __("hide_help") ?>"></div>
                <div class="help_title_steps"><?php echo __("help") ?></div>

                <div class="clear"></div>

                <div class="help_name_steps"><span><?php echo __("step_num", [7])?>:</span> <?php echo __("content-review")?></div>
                <div class="help_descr_steps">
                    <ul><?php echo __("content-review_sun_desc")?></ul>
                    <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div>
                </div>
            </div>

            <div class="event_info is_checker_page_help">
                <div class="participant_info">
                    <div class="additional_info">
                        <a href="/events/demo-sun/information"><?php echo __("event_info") ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="help_show toggle-help glyphicon glyphicon-question-sign" title="<?php echo __("show_help") ?>"></div>
</div>

<div class="tutorial_container">
    <div class="tutorial_popup">
        <div class="tutorial-close glyphicon glyphicon-remove"></div>
        <div class="tutorial_pic">
            <img src="<?php echo template_url("img/steps/icons/content-review.png") ?>" height="100px" width="100px">
            <img src="<?php echo template_url("img/steps/big/content-review.png") ?>" height="280px" width="280px">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="consume" value="0" type="checkbox"> <?php echo __("do_not_show_tutorial")?></label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo __("content-review")?></h3>
            <ul><?php echo __("content-review_sun_desc")?></ul>
        </div>
    </div>
</div>

<script>
    isChecker = true;
    $(document).ready(function () {
        $("#next_step").click(function (e) {
            e.preventDefault();

            deleteCookie("temp_tutorial");
            window.location.href = '/events/demo-sun/verse-markers';

            return false;
        });
    });
</script>