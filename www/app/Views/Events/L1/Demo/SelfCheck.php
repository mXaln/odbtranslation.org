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

<div class="footnote_editor panel panel-default">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo __("write_footnote_title")?></h1>
        <span class="footnote-editor-close btn btn-success"><?php echo __("save") ?></span>
        <span class="xbtnf glyphicon glyphicon-remove"></span>
    </div>
    <div class="footnote_window">
        <div class="fn_preview"></div>
        <div class="fn_buttons" dir="ltr">
            <!--<button class="btn btn-default" data-fn="fr" title="footnote text">fr</button>-->
            <button class="btn btn-default" data-fn="ft" title="footnote text">ft</button>
            <!--<button class="btn btn-default" data-fn="fq" title="footnote translation quotation">fq</button>-->
            <button class="btn btn-default" data-fn="fqa" title="footnote alternate translation">fqa</button>
            <!--<button class="btn btn-default" data-fn="fk" title="footnote keyword">fk</button>-->
            <!--<button class="btn btn-default" data-fn="fl" title="footnote label text">fl</button>-->
            <!--<button class="btn btn-link" data-fn="link">Footnotes Specification</button>-->
        </div>
        <div class="fn_builder"></div>
    </div>
</div>

<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title">
            <div class="demo_title"><?php echo __("demo") . " (".__("8steps_vmast").")" ?></div>
            <div><?php echo __("step_num", ["step_number" => 5]) . ": " . __("self-check")?></div>
        </div>
        <div class="demo_video">
            <span class="glyphicon glyphicon-play"></span>
            <a href="#"><?php echo __("demo_video"); ?></a>
        </div>
    </div>

    <div class="main_content">
        <form action="" method="post" id="main_form">
            <div class="main_content_text row" style="padding-left: 15px">

                <h4>English - <?php echo __("ulb") ?> - <?php echo __("new_test") ?> - <span class='book_name'>2 Timothy 2:1-26</span></h4>

                <div class="col-sm-12 no_padding">
                    <div class="row flex_container chunk_block">
                        <div class="chunk_verses flex_left">
                            <strong><sup>1</sup></strong>You therefore, my child, be strengthened in the grace that is in Christ Jesus.
                            <strong><sup>2</sup></strong>And the things you heard from me among many witnesses, entrust them to faithful people who will be able to teach others also.
                            <strong><sup>3</sup></strong>Suffer hardship with me, as a good soldier of Christ Jesus.
                        </div>
                        <div class="editor_area flex_middle">
                            <div class="vnote">
                                <textarea class="col-sm-6 peer_verse_ta textarea" name="chunks[]" style="overflow: hidden; word-wrap: break-word; height: 266px;">Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text Demo translation text, Demo translation text</textarea>
                            </div>
                        </div>
                        <div class="flex_right">
                            <div class="notes_tools">
                                    <span class="editComment mdi mdi-lead-pencil"
                                          data="0:0"
                                          title="<?php echo __("write_note_title", [""])?>"></span>

                                <div class="comments"></div>

                                <span class="editFootNote mdi mdi-bookmark" title="<?php echo __("write_footnote_title") ?>"></span>
                            </div>
                        </div>
                    </div>
                    <div class="chunk_divider"></div>
                    <div class="row flex_container chunk_block">
                        <div class="chunk_verses flex_left">
                            <strong><sup>4</sup></strong>No soldier serves while entangled in the affairs of this life, so that he may please his superior officer.
                            <strong><sup>5</sup></strong>Also, if someone competes as an athlete, he is not crowned unless he competes by the rules.
                            <strong><sup>6</sup></strong>It is necessary that the hardworking farmer receive his share of the crops first.
                            <strong><sup>7</sup></strong>Think about what I am saying, for the Lord will give you understanding in everything.
                        </div>
                        <div class="editor_area flex_middle">
                            <div class="vnote">
                                <textarea class="col-sm-6 peer_verse_ta textarea" name="chunks[]" style="overflow: hidden; word-wrap: break-word; height: 266px;">Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>
                            </div>
                        </div>
                        <div class="flex_right">
                            <div class="notes_tools">
                                    <span class="editComment mdi mdi-lead-pencil"
                                          data="0:0"
                                          title="<?php echo __("write_note_title", [""])?>"></span>

                                <div class="comments"></div>

                                <span class="editFootNote mdi mdi-bookmark" title="<?php echo __("write_footnote_title") ?>"></span>
                            </div>
                        </div>
                    </div>
                    <div class="chunk_divider"></div>
                    <div class="row flex_container chunk_block">
                        <div class="chunk_verses flex_left">
                            <strong><sup>8</sup></strong>Remember Jesus Christ, from David's seed, who was raised from the dead ones. This is according to my gospel message,
                            <strong><sup>9</sup></strong>for which I am suffering to the point of being chained as a criminal. But the word of God is not chained.
                            <strong><sup>10</sup></strong>Therefore I endure all things for those who are chosen, so that they also may obtain the salvation that is in Christ Jesus, with eternal glory.
                        </div>
                        <div class="editor_area flex_middle">
                            <div class="vnote">
                                <textarea class="col-sm-6 peer_verse_ta textarea" name="chunks[]" style="overflow: hidden; word-wrap: break-word; height: 266px;">Demo translation text, Demo translation text, Demo translation text, Demo translation text Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>
                            </div>
                        </div>
                        <div class="flex_right">
                            <div class="notes_tools">
                                    <span class="editComment mdi mdi-lead-pencil"
                                          data="0:0"
                                          title="<?php echo __("write_note_title", [""])?>"></span>

                                <div class="comments"></div>

                                <span class="editFootNote mdi mdi-bookmark" title="<?php echo __("write_footnote_title") ?>"></span>
                            </div>
                        </div>
                    </div>
                    <div class="chunk_divider"></div>
                    <div class="row flex_container chunk_block">
                        <div class="chunk_verses flex_left">
                            <strong><sup>11</sup></strong>This saying is trustworthy:   "If we have died with him, we will also live with him.
                            <strong><sup>12</sup></strong>If we endure, we will also reign with him. If we deny him, he also will deny us.
                            <strong><sup>13</sup></strong>if we are unfaithful, he remains faithful,  for he cannot deny himself."
                            <strong><sup>14</sup></strong>Keep reminding them of these things. Warn them before God not to quarrel about words. Because of this there is nothing useful. Because of this there is destruction for those who listen. <span class="mdi mdi-bookmark" title="" data-placement="auto right" data-toggle="tooltip" data-original-title="Some versions read, Warn them before the Lord "></span>
                        </div>
                        <div class="editor_area flex_middle">
                            <div class="vnote">
                                <textarea class="col-sm-6 peer_verse_ta textarea" name="chunks[]" style="overflow: hidden; word-wrap: break-word; height: 266px;">Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>
                            </div>
                        </div>
                        <div class="flex_right">
                            <div class="notes_tools">
                                    <span class="editComment mdi mdi-lead-pencil"
                                          data="0:0"
                                          title="<?php echo __("write_note_title", [""])?>"></span>

                                <div class="comments"></div>

                                <span class="editFootNote mdi mdi-bookmark" title="<?php echo __("write_footnote_title") ?>"></span>
                            </div>
                        </div>
                    </div>
                    <div class="chunk_divider"></div>
                    <div class="row flex_container chunk_block">
                        <div class="chunk_verses flex_left">
                            <strong><sup>15</sup></strong>Do your best to present yourself to God as one approved, a worker who has no reason to be ashamed, who accurately teaches the word of truth.
                            <strong><sup>16</sup></strong>Avoid profane talk, which leads to more and more godlessness.
                            <strong><sup>17</sup></strong>Their talk will spread like gangrene. Among whom are Hymenaeus and Philetus.
                        </div>
                        <div class="editor_area flex_middle">
                            <div class="vnote">
                                <textarea class="col-sm-6 peer_verse_ta textarea" name="chunks[]" style="overflow: hidden; word-wrap: break-word; height: 266px;">Demo translation text, Demo translation text, Demo translation text, Demo translation text Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>
                            </div>
                        </div>
                        <div class="flex_right">
                            <div class="notes_tools">
                                    <span class="editComment mdi mdi-lead-pencil"
                                          data="0:0"
                                          title="<?php echo __("write_note_title", [""])?>"></span>

                                <div class="comments"></div>

                                <span class="editFootNote mdi mdi-bookmark" title="<?php echo __("write_footnote_title") ?>"></span>
                            </div>
                        </div>
                    </div>
                    <div class="chunk_divider"></div>
                    <div class="row flex_container chunk_block">
                        <div class="chunk_verses flex_left">
                            <strong><sup>18</sup></strong>These are men who have missed the truth. They say that the resurrection has already happened. They overturn the faith of some.
                            <strong><sup>19</sup></strong>However, the firm foundation of God stands. It has this inscription: "The Lord knows those who are his" and "Everyone who names the name of the Lord must depart from unrighteousness."
                            <strong><sup>20</sup></strong>In a wealthy home, there are not only containers of gold and silver. There are also containers of wood and clay. Some of these are for honorable use, and some for dishonorable.
                        </div>
                        <div class="editor_area flex_middle">
                            <div class="vnote">
                                <textarea class="col-sm-6 peer_verse_ta textarea" name="chunks[]" style="overflow: hidden; word-wrap: break-word; height: 266px;">Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>
                            </div>
                        </div>
                        <div class="flex_right">
                            <div class="notes_tools">
                                    <span class="editComment mdi mdi-lead-pencil"
                                          data="0:0"
                                          title="<?php echo __("write_note_title", [""])?>"></span>

                                <div class="comments"></div>

                                <span class="editFootNote mdi mdi-bookmark" title="<?php echo __("write_footnote_title") ?>"></span>
                            </div>
                        </div>
                    </div>
                    <div class="chunk_divider"></div>
                    <div class="row flex_container chunk_block">
                        <div class="chunk_verses flex_left">
                            <strong><sup>21</sup></strong>If someone cleans himself from dishonorable use, he is an honorable container. He is set apart, useful to the Master, and prepared for every good work.
                            <strong><sup>22</sup></strong>Flee youthful lusts. Pursue righteousness, faith, love, and peace with those who call on the Lord out of a clean heart.
                            <strong><sup>23</sup></strong>But refuse foolish and ignorant questions. You know that they give birth to arguments.
                        </div>
                        <div class="editor_area flex_middle">
                            <div class="vnote">
                                <textarea class="col-sm-6 peer_verse_ta textarea" name="chunks[]" style="overflow: hidden; word-wrap: break-word; height: 266px;">Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>
                            </div>
                        </div>
                        <div class="flex_right">
                            <div class="notes_tools">
                                    <span class="editComment mdi mdi-lead-pencil"
                                          data="0:0"
                                          title="<?php echo __("write_note_title", [""])?>"></span>

                                <div class="comments"></div>

                                <span class="editFootNote mdi mdi-bookmark" title="<?php echo __("write_footnote_title") ?>"></span>
                            </div>
                        </div>
                    </div>
                    <div class="chunk_divider"></div>
                    <div class="row flex_container chunk_block">
                        <div class="chunk_verses flex_left">
                            <strong><sup>24</sup></strong>The Lord's servant must not quarrel. Instead he must be gentle toward all, able to teach, and patient.
                            <strong><sup>25</sup></strong>He must in meekness educate those who oppose him. God may perhaps give them repentance for the knowledge of the truth.
                            <strong><sup>26</sup></strong>They may become sober again and leave the devil's trap, after they have been captured by him for his will.
                        </div>
                        <div class="editor_area flex_middle">
                            <div class="vnote">
                                <textarea class="col-sm-6 peer_verse_ta textarea" name="chunks[]" style="overflow: hidden; word-wrap: break-word; height: 266px;">Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text, Demo translation text</textarea>
                            </div>
                        </div>
                        <div class="flex_right">
                            <div class="notes_tools">
                                    <span class="editComment mdi mdi-lead-pencil"
                                          data="0:0"
                                          title="<?php echo __("write_note_title", [""])?>"></span>

                                <div class="comments"></div>

                                <span class="editFootNote mdi mdi-bookmark" title="<?php echo __("write_footnote_title") ?>"></span>
                            </div>
                        </div>
                    </div>
                    <div class="chunk_divider"></div>
                </div>
            </div>

            <div class="main_content_footer row">
                <div class="form-group">
                    <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                    <label><input name="confirm_step" id="confirm_step" value="1" type="checkbox"> <?php echo __("confirm_yes")?></label>
                </div>

                <button id="next_step" class="btn btn-primary" disabled="disabled"><?php echo __("next_step")?></button>
                <img src="<?php echo template_url("img/saving.gif") ?>" class="unsaved_alert">
            </div>
        </form>
        <div class="step_right alt"><?php echo __("step_num", ["step_number" => 5])?></div>
    </div>
</div>

<div class="content_help closed">
    <div id="help_hide" class="glyphicon glyphicon-chevron-down"> <?php echo __("help") ?></div>

    <div class="help_float">
        <div class="help_info_steps">
            <div class="help_name_steps"><span><?php echo __("step_num", ["step_number" => 5])?>:</span> <?php echo __("self-check")?></div>
            <div class="help_descr_steps">
                <ul><?php echo __("self-check_desc")?></ul>
                <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div>
            </div>
        </div>

        <div class="event_info">
            <div class="participant_info">
                <div class="additional_info">
                    <a href="/events/demo/information"><?php echo __("event_info") ?></a>
                </div>
            </div>
        </div>

        <div class="tr_tools">
            <button class="btn btn-primary ttools" data-tool="tn"><?php echo __("show_notes") ?></button>
            <button class="btn btn-primary ttools" data-tool="tq"><?php echo __("show_questions") ?></button>
            <button class="btn btn-warning ttools" data-tool="rubric"><?php echo __("show_rubric") ?></button>
        </div>
    </div>
</div>

<div class="ttools_panel tn_tool panel panel-default" draggable="true">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo __("tn") ?></h1>
        <span class="panel-close glyphicon glyphicon-remove" data-tool="tn"></span>
    </div>

    <div class="ttools_content page-content panel-body">
        <div class="labels_list">
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"><?php echo __("intro") ?></span> </div>
                        <div class="word_def">
                            <h1>2 Timothy 02 General Notes</h1>
                            <h4>Structure and formatting</h4>
                            <p>Some translations set words farther to the right on the page than the rest of the text. The ULB does this with verses 11-13. Paul may be quoting a poem or hymn in these verses.</p>
                            <h4>Special concepts in this chapter</h4>
                            <h5>We will reign with him</h5>
                            <p>Faithful Christians will reign with Christ in the future. (See: rc://en/tw/dict/bible/kt/faithful)</p>
                            <h4>Important figures of speech in this chapter</h4>
                            <h5>Analogies</h5>
                            <p>In this chapter, Paul makes several analogies to teach about living as a Christian. He uses analogies of soldiers, athletes, and farmers. Later in the chapter, he uses the analogy of different kinds of containers in a house.</p>
                            <h2>Links:</h2>
                            <ul>
                                <li><strong><b>2 Timothy 02:01 Notes</b></strong></li>
                            </ul>
                            <p><strong><b>&lt;&lt;</b> | <b>&gt;&gt;</b></strong></p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> <?php echo __("verse_number", 1) ?> </span> </div>
                        <div class="word_def">
                            <h1>Connecting Statement:</h1>
                            <p>Paul pictures Timothy's Christian life as a soldier's life, as a farmer's life, and as an athlete's life.</p>
                            <h1>my child</h1>
                            <p>Here "child" is a term of great love and approval. It is also likely that Timothy was converted to Christ by Paul, and so this is why Paul considered him like his own child. Alternate translation: "who is like my child" (See: [[rc://en/ta/man/translate/figs-metaphor]])</p>
                            <h1>be strengthened in the grace that is in Christ Jesus</h1>
                            <p>Paul speaks about the motivation and determination that God's grace allows believers to have. Alternate translation: "let God use the grace he gave you through your relationship to Christ Jesus to make you strong" (See: [[rc://en/ta/man/translate/figs-metaphor]])</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> <?php echo __("verse_number", 2) ?> </span> </div>
                        <div class="word_def">
                            <h1>among many witnesses</h1>
                            <p>"with many witnesses there to agree that what I said is true"</p>
                            <h1>entrust them to faithful people</h1>
                            <p>Paul speaks of his instructions to Timothy as if they were objects that Timothy could give to other people and trust them to use correctly. Alternate translation: "commit them" or "teach them" (See: [[rc://en/ta/man/translate/figs-metaphor]])</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> <?php echo __("verse_number", 3) ?> </span> </div>
                        <div class="word_def">
                            <h1>Suffer hardship with me</h1>
                            <p>Possible meanings are 1) "Endure suffering as I do" or 2) "Share in my suffering"</p>
                            <h1>as a good soldier of Christ Jesus</h1>
                            <p>Paul compares suffering for Christ Jesus to the suffering that a good soldier endures. (See: [[rc://en/ta/man/translate/figs-simile]])</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> <?php echo __("verse_number", 4) ?> </span> </div>
                        <div class="word_def">
                            <h1>No soldier serves while entangled in the affairs of this life</h1>
                            <p>"No soldier serves when he is involved in the everyday business of this life" or "When soldiers are serving, they do not get distracted by the ordinary things that people do." Christ's servants should not allow everyday life to keep them from working for Christ.</p>
                            <h1>while entangled</h1>
                            <p>Paul speaks of this distraction as if it were a net that tripped people up as they were walking. (See: [[rc://en/ta/man/translate/figs-metaphor]])</p>
                            <h1>his superior officer</h1>
                            <p>"his leader" or "the one who commands him"</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> <?php echo __("verse_number", 5) ?> </span> </div>
                        <div class="word_def">
                            <h1>as an athlete, he is not crowned unless he competes by the rules</h1>
                            <p>Paul is implicitly speaking of Christ's servants as if they were athletes. (See: [[rc://en/ta/man/translate/figs-explicit]] and [[rc://en/ta/man/translate/figs-metaphor]])</p>
                            <h1>he is not crowned unless he competes by the rules</h1>
                            <p>This can be stated in active form. Alternate translation: "they will crown him as winner only if he competes by the rules" (See: [[rc://en/ta/man/translate/figs-activepassive]])</p>
                            <h1>he is not crowned</h1>
                            <p>"he does not win the prize." Athletes in Paul's time were crowned with wreaths made from the leaves of plants when they won competitions.</p>
                            <h1>competes by the rules</h1>
                            <p>"competes according to the rules" or "strictly obeys the rules"</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> <?php echo __("verse_number", 6) ?> </span> </div>
                        <div class="word_def">
                            <h1>It is necessary that the hardworking farmer receive his share of the crops first</h1>
                            <p>This is the third metaphor Paul gives Timothy about working. The reader should understand that Christ's servants need to work hard. (See: [[rc://en/ta/man/translate/figs-metaphor]])</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> <?php echo __("verse_number", 7) ?> </span> </div>
                        <div class="word_def">
                            <h1>Think about what I am saying</h1>
                            <p>Paul gave Timothy word pictures, but he did not completely explain their meanings. He expected Timothy to figure out what he was saying about Christ's servants.</p>
                            <h1>in everything</h1>
                            <p>"about everything"</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> <?php echo __("verse_number", 8) ?> </span> </div>
                        <div class="word_def">
                            <h1>Connecting Statement:</h1>
                            <p>Paul gives Timothy instructions on how to live for Christ, how to suffer for Christ, and how to teach others to live for Christ.</p>
                            <h1>from David's seed</h1>
                            <p>This is a metaphor that means Jesus descended from David. Alternate translation: "who is a descendant of David" (See: [[rc://en/ta/man/translate/figs-metaphor]])</p>
                            <h1>who was raised from the dead</h1>
                            <p>Here to raise up is an idiom for causing someone who has died to become alive again. This can be stated in active form. Alternate translation: "whom God caused to live again" or "whom God raised from the dead" (See: [[rc://en/ta/man/translate/figs-activepassive]] and [[rc://en/ta/man/translate/figs-idiom]])</p>
                            <h1>according to my gospel message</h1>
                            <p>Paul speaks of the gospel message as if it were especially his. He means that this is the gospel message that he proclaims. Alternate translation: "according to the gospel message that I preach" (See: [[rc://en/ta/man/translate/figs-metonymy]])</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> <?php echo __("verse_number", 9) ?> </span> </div>
                        <div class="word_def">
                            <h1>to the point of being bound with chains as a criminal</h1>
                            <p>Here "being chained" represents being a prisoner. This can be stated in active form. Alternate translation: "to the point of wearing chains as a criminal in prison" (See: [[rc://en/ta/man/translate/figs-metonymy]] and [[rc://en/ta/man/translate/figs-activepassive]])</p>
                            <h1>the word of God is not bound</h1>
                            <p>Here "bound" speaks of what happens to a prisoner, and the phrase is a metaphor that means no one can stop God's message. This can be translated in active form. Alternate translation: "no one can put the word of God in prison" or "no one can stop the word of God" (See: [[rc://en/ta/man/translate/figs-metaphor]])</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> <?php echo __("verse_number", 10) ?> </span> </div>
                        <div class="word_def">
                            <h1>for those who are chosen</h1>
                            <p>This can be stated in active form. Alternate translation: "for the people whom God has chosen" (See: [[rc://en/ta/man/translate/figs-activepassive]])</p>
                            <h1>may obtain the salvation that is in Christ Jesus</h1>
                            <p>Paul speaks of salvation as if it were an object that could be physically grasped. Alternate translation: "will receive salvation from Christ Jesus" (See: [[rc://en/ta/man/translate/figs-metaphor]])</p>
                            <h1>with eternal glory</h1>
                            <p>"and that they will be forever with him in the glorious place where he is"</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> <?php echo __("verse_number", 11) ?> </span> </div>
                        <div class="word_def">
                            <h1>This is a trustworthy saying</h1>
                            <p>"These are words you can trust"</p>
                            <h1>If we have died with him, we will also live with him</h1>
                            <p>This is most likely the beginning of a song or poem that Paul is quoting. If your language has a way of indicating that this is poetry, you could use it here. If not, you could translate this as regular prose rather than poetry. (See: [[rc://en/ta/man/translate/writing-poetry]])</p>
                            <h1>died with him</h1>
                            <p>Paul uses this expression to mean that people share in Christ's death when they trust in him, deny their own wants, and obey him.</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> <?php echo __("verse_number", 12) ?> </span> </div>
                        <div class="word_def">
                            <h1>General Information:</h1>
                            <p>This page has intentionally been left blank.</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> <?php echo __("verse_number", 13) ?> </span> </div>
                        <div class="word_def">
                            <h1>if we are unfaithful ... he cannot deny himself</h1>
                            <p>This is most likely the end of a song or poem that Paul is quoting. If your language has a way of indicating that this is poetry you could use it here. If not, you could translate this as regular prose rather than poetry. (See: [[rc://en/ta/man/translate/writing-poetry]])</p>
                            <h1>if we are unfaithful</h1>
                            <p>"even if we fail God" or "even if we do not do what we believe God wants us to do"</p>
                            <h1>he cannot deny himself</h1>
                            <p>"he must always act according to his character" or "he cannot act in ways that are the opposite of his real character"</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> <?php echo __("verse_number", 14) ?> </span> </div>
                        <div class="word_def">
                            <h1>General Information:</h1>
                            <p>The word "them" may refer to "the teachers" or "the people of the church"</p>
                            <h1>before God</h1>
                            <p>Paul speaks of God's awareness of Paul as if he is in God's physical presence. This implies that God will be Timothy's witness. Alternate translation: "in God's presence" or "with God as your witness" (See: [[rc://en/ta/man/translate/figs-metaphor]] and [[rc://en/ta/man/translate/figs-explicit]])</p>
                            <h1>against quarreling about words</h1>
                            <p>Possible meanings are 1) "not to argue about foolish things that people say" or 2) "not to quarrel about what words mean"</p>
                            <h1>it is of no value</h1>
                            <p>"this does not benefit anyone"</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> <?php echo __("verse_number", 15) ?> </span> </div>
                        <div class="word_def">
                            <h1>to present yourself to God as one approved, a worker who has no reason to be ashamed</h1>
                            <p>"to present yourself to God as a person who has proven to be worthy and with no cause for shame"</p>
                            <h1>a worker</h1>
                            <p>Paul presents the idea of Timothy correctly explaining God's word as if he were a skilled workman. Alternate translation: "like a workman" or "like a worker" (See: [[rc://en/ta/man/translate/figs-metaphor]])</p>
                            <h1>accurately teaches the word of truth</h1>
                            <p>Possible meanings are 1) "explains the message about the truth correctly" or 2) "explains the true message correctly."</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> <?php echo __("verse_number", 16) ?> </span> </div>
                        <div class="word_def">
                            <h1>which leads to more and more godlessness</h1>
                            <p>Paul speaks of this kind of talk as if it were something that could physically move to another location, and he speaks of godlessness as if it were that new location. Alternate translation: "which causes people to become more and more ungodly" (See: [[rc://en/ta/man/translate/figs-metaphor]])</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> <?php echo __("verse_number", 17) ?> </span> </div>
                        <div class="word_def">
                            <h1>Their talk will spread like cancer</h1>
                            <p>Cancer quickly spreads in a person's body and destroys it. This is a metaphor that means what those people were saying would spread from person to person and harm the faith of those who heard it. Alternate translation: "What they say will spread like an infectious disease" or "Their talk will spread quickly and cause destruction like cancer" (See: [[rc://en/ta/man/translate/figs-simile]])</p>
                            <h1>Hymenaeus and Philetus</h1>
                            <p>These are names of men. (See: [[rc://en/ta/man/translate/translate-names]])</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> <?php echo __("verse_number", 18) ?> </span> </div>
                        <div class="word_def">
                            <h1>who have gone astray from the truth</h1>
                            <p>Here "gone astray from the truth" is a metaphor for no longer believing or teaching what is true. Alternate translation: "who have started saying things that are not true" (See: [[rc://en/ta/man/translate/figs-metaphor]])</p>
                            <h1>the resurrection has already happened</h1>
                            <p>"God has already raised dead believers to eternal life"</p>
                            <h1>they destroy the faith of some</h1>
                            <p>"they cause some people to stop believing"</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> <?php echo __("verse_number", 19) ?> </span> </div>
                        <div class="word_def">
                            <h1>General Information:</h1>
                            <p>Just as precious and common containers can be used for honorable ways in a wealthy house, any person who turns to God can be used by God in honorable ways in doing good works. (See: [[rc://en/ta/man/translate/figs-metaphor]])</p>
                            <h1>the firm foundation of God stands</h1>
                            <p>Possible meanings are 1) "God's truth is like a firm foundation" or 2) "God has established his people like a building on a firm foundation" or 3) "God's faithfulness is like a firm foundation." In any case, Paul speaks of this idea as if it were a building's foundation laid in the ground. (See: [[rc://en/ta/man/translate/figs-metaphor]])</p>
                            <h1>who names the name of the Lord</h1>
                            <p>"who calls on the name of the Lord." Here "name of the Lord" refers to the Lord himself. Alternate translation: "who calls on the Lord" or "who says he is a believer in Christ" (See: [[rc://en/ta/man/translate/figs-metonymy]])</p>
                            <h1>depart from unrighteousness</h1>
                            <p>Paul speaks of unrighteousness as if it were a place from which one could leave. Alternate translation: "stop being evil" or "stop doing wrong things" (See: [[rc://en/ta/man/translate/figs-metaphor]])</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> <?php echo __("verse_number", 20) ?> </span> </div>
                        <div class="word_def">
                            <h1>containers of gold and silver ... containers of wood and clay</h1>
                            <p>Here "containers" is a general word for bowls, plates, and pots, which people put food or drink into or on. If your language does not have a general word, use the word for "bowls" or "pots." Paul is using this as a metaphor to describe different types of people. (See: [[rc://en/ta/man/translate/figs-metaphor]])</p>
                            <h1>honorable use ... dishonorable</h1>
                            <p>Possible meanings are 1) "special occasions ... ordinary times" or 2) "the kinds of activities people do in public ... the kinds of activities people do in private."</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> <?php echo __("verse_number", 21) ?> </span> </div>
                        <div class="word_def">
                            <h1>cleans himself from dishonorable use</h1>
                            <p>Possible meanings are 1) "separates himself from dishonorable people" or 2) "makes himself pure." In any case, Paul speaks of this process as if it were a person washing himself. (See: [[rc://en/ta/man/translate/figs-metaphor]])</p>
                            <h1>he is an honorable container</h1>
                            <p>Paul speaks about this person as if he were an honorable container. Alternate translation: "he is like the container that is useful for special occasions" or "he is like the container that is useful for activities good people do in public" (See: [[rc://en/ta/man/translate/figs-metaphor]])</p>
                            <h1>He is set apart, useful to the Master, and prepared for every good work</h1>
                            <p>This can be stated in active form. Alternate translation: "The Master sets him apart, and he is ready for the Master to use him for every good work" (See: [[rc://en/ta/man/translate/figs-activepassive]])</p>
                            <h1>He is set apart</h1>
                            <p>He is not set apart physically or in the sense of location, but instead to fulfill a purpose. Some versions translate this "sanctified," but the text signals the essential idea of being set apart. (See: [[rc://en/ta/man/translate/figs-metaphor]])</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> <?php echo __("verse_number", 22) ?> </span> </div>
                        <div class="word_def">
                            <h1>Flee youthful lusts</h1>
                            <p>Paul speaks about youthful lusts as if they are a dangerous person or animal that Timothy should run away from. Alternate translation: "Completely avoid youthful lusts" or "Absolutely refuse to do the wrong things that young people strongly desire to do" (See: [[rc://en/ta/man/translate/figs-metaphor]])</p>
                            <h1>Pursue righteousness</h1>
                            <p>Here "Pursue" means the opposite of "Flee." Paul speaks of righteousness as if it is an object that Timothy should run towards because it will do him good. Alternate translation: "Try your best to obtain righteousness" or "Seek after righteousness" (See: [[rc://en/ta/man/translate/figs-metaphor]])</p>
                            <h1>with those</h1>
                            <p>Possible meanings are 1) Paul wants Timothy to join with other believers in pursuing righteousness, faith, love, and peace, or 2) Paul wants Timothy to be at peace and not argue with other believers.</p>
                            <h1>those who call on the Lord</h1>
                            <p>Here "call on the Lord" is an idiom that means to trust and worship the Lord. Alternate translation: "those who worship the Lord" (See: [[rc://en/ta/man/translate/figs-idiom]])</p>
                            <h1>out of a clean heart</h1>
                            <p>Here "clean" is a metaphor for something pure or sincere. And, "heart" here is a metonym for "thoughts" or "emotions." Alternate translation: "with a sincere mind" or "with sincerity" (See: [[rc://en/ta/man/translate/figs-metaphor]] and [[rc://en/ta/man/translate/figs-metonymy]])</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> <?php echo __("verse_number", 23) ?> </span> </div>
                        <div class="word_def">
                            <h1>refuse foolish and ignorant questions</h1>
                            <p>"refuse to answer foolish and ignorant questions." Paul means that the people who ask such questions are foolish and ignorant. Alternate translation: "refuse to answer the questions that foolish people who do not want to know the truth ask" (See: [[rc://en/ta/man/translate/figs-metonymy]])</p>
                            <h1>they give birth to arguments</h1>
                            <p>Paul speaks of ignorant questions as if they were women giving birth to children. Alternate translation: "they cause arguments" (See: [[rc://en/ta/man/translate/figs-metaphor]])</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> <?php echo __("verse_number", 24) ?> </span> </div>
                        <div class="word_def">
                            <h1>General Information:</h1>
                            <p>This page has intentionally been left blank.</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> <?php echo __("verse_number", 25) ?> </span> </div>
                        <div class="word_def">
                            <h1>in meekness</h1>
                            <p>"meekly" or "gently"</p>
                            <h1>educate those</h1>
                            <p>"teach those" or "correct those"</p>
                            <h1>God may perhaps give them repentance</h1>
                            <p>Paul speaks of repentance as if it were an object that God could give people. Alternate translation: "God may give them the opportunity to repent" (See: [[rc://en/ta/man/translate/figs-metaphor]])</p>
                            <h1>for the knowledge of the truth</h1>
                            <p>"so that they will know the truth"</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> <?php echo __("verse_number", 26) ?> </span> </div>
                        <div class="word_def">
                            <h1>They may become sober again</h1>
                            <p>Paul speaks of sinners learning to think correctly about God as if they were drunk people becoming sober again. Alternate translation: "They may think correctly again" (See: [[rc://en/ta/man/translate/figs-metaphor]])</p>
                            <h1>leave the devil's trap</h1>
                            <p>Paul speaks of the devil's ability to convince Christians to sin as if it were a trap. Alternate translation: "stop doing what the devil wants" (See: [[rc://en/ta/man/translate/figs-metaphor]])</p>
                            <h1>after they have been captured by him for his will</h1>
                            <p>Convincing Christians to sin is spoken of as if the devil had physically captured them and made them his slaves. This can be stated in active form. Alternate translation: "after he has deceived them into obeying his will" (See: [[rc://en/ta/man/translate/figs-metaphor]] and [[rc://en/ta/man/translate/figs-activepassive]])</p>
                        </div>
                    </li>
                </ul>
            </label>
        </div>
        <div class="word_def_popup">
            <div class="word_def-close glyphicon glyphicon-remove"></div>

            <div class="word_def_title"></div>
            <div class="word_def_content"></div>
        </div>
    </div>
</div>

<div class="ttools_panel tq_tool panel panel-default" draggable="true">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo __("tq") ?></h1>
        <span class="panel-close glyphicon glyphicon-remove" data-tool="tq"></span>
    </div>

    <div class="ttools_content page-content panel-body">
        <div class="labels_list">
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"><?php echo __("verse_number", 1) ?> </span> </div>
                        <div class="word_def">
                            <h1>What is the relationship between Paul and Timothy?</h1>
                            <p>Timothy is Paul's spiritual son. </p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"><?php echo __("verse_number", 2) ?> </span> </div>
                        <div class="word_def">
                            <h1>To whom is Timothy to entrust the message Paul has taught him?</h1>
                            <p>Timothy is to entrust the message to faithful people who will be able to teach others also. </p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"><?php echo __("verse_number", 4) ?> </span> </div>
                        <div class="word_def">
                            <h1>As an illustration for Timothy, Paul says a good soldier does not entangle himself in what?</h1>
                            <p>A good soldier does not entangle himself in the affairs of this life. </p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"><?php echo __("verse_number", 9) ?> </span> </div>
                        <div class="word_def">
                            <h1>As he writes to Timothy, in what condition is Paul suffering for his preaching the word of God?</h1>
                            <p>Paul is suffering by being chained like a criminal. </p>
                            <h1>What does Paul say is not chained?</h1>
                            <p>The word of God is not chained. </p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"><?php echo __("verse_number", 10) ?> </span> </div>
                        <div class="word_def">
                            <h1>Why does Paul endure all these things?</h1>
                            <p>Paul endures all things for those chosen by God, that they may obtain the salvation that is in Christ Jesus. </p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"><?php echo __("verse_number", 12) ?> </span> </div>
                        <div class="word_def">
                            <h1>What is Christ's promise to those who endure?</h1>
                            <p>Those who endure will reign with Christ. </p>
                            <h1>What is Christ's warning to those who deny him?</h1>
                            <p>Those who deny Christ, Christ will deny. </p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"><?php echo __("verse_number", 14) ?> </span> </div>
                        <div class="word_def">
                            <h1>About what should Timothy warn the people not to quarrel?</h1>
                            <p>Timothy should warn the people not to quarrel about words, which profits nothing. </p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"><?php echo __("verse_number", 18) ?> </span> </div>
                        <div class="word_def">
                            <h1>Two men have wandered from the truth, teaching what false doctrine?</h1>
                            <p>They were teaching that the resurrection had already happened. </p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"><?php echo __("verse_number", 21) ?> </span> </div>
                        <div class="word_def">
                            <h1>How are the believers to prepare themselves for every good work?</h1>
                            <p>The believers are to clean themselves from dishonorable use, consecrating themselves for every good work. </p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"><?php echo __("verse_number", 22) ?> </span> </div>
                        <div class="word_def">
                            <h1>From what is Timothy to flee?</h1>
                            <p>Timothy is to flee youthful lusts. </p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"><?php echo __("verse_number", 24) ?> </span> </div>
                        <div class="word_def">
                            <h1>What kind of character must a servant of the Lord have?</h1>
                            <p>A servant of the Lord must be gentle, able to teach, patient, in meekness educating those who oppose him. </p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"><?php echo __("verse_number", 25) ?> </span> </div>
                        <div class="word_def">
                            <h1>What kind of character must a servant of the Lord have?</h1>
                            <p>A servant of the Lord must be gentle, able to teach, patient, in meekness educating those who oppose him. </p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"><?php echo __("verse_number", 26) ?> </span> </div>
                        <div class="word_def">
                            <h1>What has the devil done with unbelievers?</h1>
                            <p>The devil has trapped and captured the unbelievers for his will. </p>
                        </div>
                    </li>
                </ul>
            </label>
        </div>
        <div class="word_def_popup">
            <div class="word_def-close glyphicon glyphicon-remove"></div>

            <div class="word_def_title"></div>
            <div class="word_def_content"></div>
        </div>
    </div>
</div>

<div class="ttools_panel rubric_tool panel panel-default" draggable="true">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo __("show_rubric") ?></h1>
        <span class="panel-close glyphicon glyphicon-remove" data-tool="rubric"></span>
    </div>

    <div class="ttools_content page-content panel-body">
        <ul class="nav nav-tabs nav-justified read_rubric_tabs">
            <li role="presentation" id="tab_orig" class="active"><a href="#">English demo1</a></li>
            <li role="presentation" id='tab_eng'><a href="#">English</a></li>
        </ul>
        <div class="read_rubric_qualities">
            <br>
            <div class="read_rubric_quality orig" dir="ltr"> 1. Accessible </div>
            <div class="read_rubric_quality eng" style="display: none;"> </div>
            <div class="read_rubric_defs">
                <div class="read_rubric_def orig" dir="ltr"> 1. Created in necessary formats. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Is it created in necessary formats? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 2. Easily reproduced and distributed. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Is it easily reproduced? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                    <div class="read_rubric_measurement orig" dir="ltr"> 2. Is it easily distributed? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 3. Appropriate font, size and layout. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Is it in the appropriate font, size and layout? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 4. Editable. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Is it editable? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
            </div>
            <div class="read_rubric_quality orig" dir="ltr"> 2. Faithful </div>
            <div class="read_rubric_quality eng" style="display: none;"> </div>
            <div class="read_rubric_defs">
                <div class="read_rubric_def orig" dir="ltr"> 1. Reflects Original Text. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Does in reflect original text? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 2. True to Greek and Hebrew. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Is it true to Greek and Hebrew? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 3. Does not have additions or deletions. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Does it have additions or deletions? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 4. Names of God retained. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Are the names of God retained? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 5. Accurate key terms/key words. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Are key terms/words accurate? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
            </div>
            <div class="read_rubric_quality orig" dir="ltr"> 3. Culturally Relevant </div>
            <div class="read_rubric_quality eng" style="display: none;"> </div>
            <div class="read_rubric_defs">
                <div class="read_rubric_def orig" dir="ltr"> 1. Idioms are understandable </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Are idioms understandable? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 2. Words and expressions appropriate for local culture. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Are words and expressions appropriate for local culture? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 3. Reflects original language artistry. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Does it reflect original language artistry? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 4. Captures literary genres. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Are literary genres captured accurately? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
            </div>
            <div class="read_rubric_quality orig" dir="ltr"> 4. Clear </div>
            <div class="read_rubric_quality eng" style="display: none;"> </div>
            <div class="read_rubric_defs">
                <div class="read_rubric_def orig" dir="ltr"> 1. Meaning is clear. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Is the meaning clear? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 2. Uses common language. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Does it use common language? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 3. Easily understood by wide audience. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Is it easily understood by a wide audience? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
            </div>
            <div class="read_rubric_quality orig" dir="ltr"> 5. Proper Grammar </div>
            <div class="read_rubric_quality eng" style="display: none;"> </div>
            <div class="read_rubric_defs">
                <div class="read_rubric_def orig" dir="ltr"> 1. Follows grammar norms. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Does it follow grammar norms? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 2. Correct punctuation. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Is correct punctuation used? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
            </div>
            <div class="read_rubric_quality orig" dir="ltr"> 6. Consistent </div>
            <div class="read_rubric_quality eng" style="display: none;"> </div>
            <div class="read_rubric_defs">
                <div class="read_rubric_def orig" dir="ltr"> 1. Translation reflects contextual meaning. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Does the translation reflect contextual meaning? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 2. Does not contradict itself. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Does the text contradict itself? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 3. Writing style consistent. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Is the writing style consistent? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
            </div>
            <div class="read_rubric_quality orig" dir="ltr"> 7. Historically Accurate </div>
            <div class="read_rubric_quality eng" style="display: none;"> </div>
            <div class="read_rubric_defs">
                <div class="read_rubric_def orig" dir="ltr"> 1. All names, dates, places, events are accurately represented. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Are all names accurately represented? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                    <div class="read_rubric_measurement orig" dir="ltr"> 2. Are all dates accurately represented? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                    <div class="read_rubric_measurement orig" dir="ltr"> 3. Are all places accurately represented? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                    <div class="read_rubric_measurement orig" dir="ltr"> 4. Are all events accurately represented? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
            </div>
            <div class="read_rubric_quality orig" dir="ltr"> 8. Natural </div>
            <div class="read_rubric_quality eng" style="display: none;"> </div>
            <div class="read_rubric_defs">
                <div class="read_rubric_def orig" dir="ltr"> 1. Translation uses common and natural language. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Does the translation use common and natural language? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 2. Pleasant to read/listen to. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. It is pleasant to read/listen to? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 3. Easy to read. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Is it easy to read? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
            </div>
            <div class="read_rubric_quality orig" dir="ltr"> 9. Objective </div>
            <div class="read_rubric_quality eng" style="display: none;"> </div>
            <div class="read_rubric_defs">
                <div class="read_rubric_def orig" dir="ltr"> 1. Translation does not explain or commentate. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Does the translation explain or commentate? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
                <div class="read_rubric_def orig" dir="ltr"> 2. Translation is free of political, social, denominational bias. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Is translation is free of political bias? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                    <div class="read_rubric_measurement orig" dir="ltr"> 2. Is translation is free of social bias? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                    <div class="read_rubric_measurement orig" dir="ltr"> 3. Is translation is free of denominational bias? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
            </div>
            <div class="read_rubric_quality orig" dir="ltr"> 10. Widely Accepted </div>
            <div class="read_rubric_quality eng" style="display: none;"> </div>
            <div class="read_rubric_defs">
                <div class="read_rubric_def orig" dir="ltr"> 1. Translation is widely accepted by local church. </div>
                <div class="read_rubric_def eng" style="display: none;"> </div>
                <div class="read_rubric_measurements">
                    <div class="read_rubric_measurement orig" dir="ltr"> 1. Is translation widely accepted by the local church? </div>
                    <div class="read_rubric_measurement eng" style="display: none;"> </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="tutorial_container">
    <div class="tutorial_popup">
        <div class="tutorial-close glyphicon glyphicon-remove"></div>
        <div class="tutorial_pic">
            <img src="<?php echo template_url("img/steps/icons/self-check.png") ?>" height="100px" width="100px">
            <img src="<?php echo template_url("img/steps/big/self-check.png") ?>" height="280px" width="280px">
            
        </div>

        <div class="tutorial_content">
            <h3><?php echo __("self-check")?></h3>
            <ul><?php echo __("self-check_desc")?></ul>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $("#next_step").click(function (e) {
            e.preventDefault();

            
            if(!hasChangesOnPage) window.location.href = '/events/demo/peer_review';

            return false;
        });

        $(".peer_verse_ta").highlightWithinTextarea({
            highlight: /\\f\s[+-]\s(.*?)\\f\*/gi
        });
    });
</script>