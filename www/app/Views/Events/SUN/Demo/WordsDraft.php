<?php
use Helpers\Constants\EventMembers;
?>
<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title">
            <div class="demo_title"><?php echo __("demo") . " (".__("vsail").")" ?></div>
            <div><?php echo __("step_num", [3]). ": " . __("rearrange")?></div>
        </div>
        <div class="demo_video">
            <span class="glyphicon glyphicon-play"></span>
            <a href="#"><?php echo __("demo_video"); ?></a>
        </div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <form action="" method="post" id="main_form">
                <div class="main_content_text row" style="padding-left: 15px">
                    <h4 dir="ltr">English - <?php echo __("sun") ?> - <?php echo __("new_test") ?> - <span class="book_name">Matthew 17:1-2</span> </h4>

                    <div class="col-sm-12 no_padding">
                        <div class="row chunk_block words_block">
                            <div class="chunk_verses col-sm-6" dir="ltr">
                                <p><strong><sup>1</sup></strong> Six days later Jesus took with him Peter, James, and John his brother,
                                    and brought them up a high mountain by themselves.</p>
                                <p><strong><sup>2</sup></strong> He was transfigured before them. His face shone like the sun, and
                                    his garments became as brilliant as the light.</p>
                            </div>
                            <div class="col-sm-6 editor_area" dir="ltr">
                                <textarea name="draft"
                                          class="col-sm-6 verse_ta textarea"
                                          style="overflow: hidden; word-wrap: break-word; height: 80px; min-height: 150px;"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="main_content_footer row">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                        <label><input name="confirm_step" id="confirm_step" type="checkbox" value="1" /> <?php echo __("confirm_yes")?></label>
                    </div>

                    <button id="next_step" type="submit" name="submit" class="btn btn-primary" disabled><?php echo __("next_step")?></button>
                    <img src="<?php echo template_url("img/saving.gif") ?>" class="unsaved_alert" style="float:none">
                </div>
            </form>
            <div class="step_right alt"><?php echo __("step_num", [3])?></div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_float">
                <div class="help_info_steps">
                    <div class="help_hide toggle-help glyphicon glyphicon-eye-close" title="<?php echo __("hide_help") ?>"></div>
                    <div class="help_title_steps"><?php echo __("help") ?></div>

                    <div class="clear"></div>

                    <div class="help_name_steps"><span><?php echo __("step_num", [3])?>:</span> <?php echo __("rearrange")?></div>
                    <div class="help_descr_steps">
                        <ul><?php echo __("rearrange_desc")?></ul>
                        <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div>
                    </div>
                </div>

                <div class="event_info">
                    <div class="participant_info">
                        <div class="additional_info">
                            <a href="/events/demo-sun/information"><?php echo __("event_info") ?></a>
                        </div>
                    </div>
                </div>

                <div class="tr_tools">
                    <button class="btn btn-warning ttools" data-tool="saildict"><?php echo __("show_dictionary") ?></button>
                    <button class="btn btn-primary ttools" data-tool="tn"><?php echo __("show_notes") ?></button>
                    <button class="btn btn-primary ttools" data-tool="tw"><?php echo __("show_keywords") ?></button>
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
            <img src="<?php echo template_url("img/steps/icons/rearrange.png") ?>" width="100" height="100">
            <img src="<?php echo template_url("img/steps/big/rearrange.png") ?>" width="280" height="280">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="rearrange" type="checkbox" value="0" /> <?php echo __("do_not_show_tutorial")?></label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo __("rearrange")?></h3>
            <ul><?php echo __("rearrange_desc")?></ul>
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
                        <div class="word_term"> <span style="font-weight: bold;"> Intro </span> </div>
                        <div class="word_def">
                            <h1>Matthew 17 General Notes</h1>
                            <h4>Special concepts in this chapter</h4>
                            <h5>Elijah</h5>
                            <p>The Old Testament prophet Malachi lived many years before Jesus was born. Malachi had said that before the Messiah came a prophet named Elijah would return. Jesus explained that Malachi had been talking about John the Baptist. Jesus said this because John the Baptist had done what Malachi had said that Elijah would do. (See: [[rc://en/tw/dict/bible/kt/prophet]] and [[rc://en/tw/dict/bible/kt/christ]]) </p>
                            <h5>"transfigured"</h5>
                            <p>Scripture often speaks of God's glory as a great, brilliant light. When people see this light, they are afraid. Matthew says in this chapter that Jesus' body shone with this glorious light so that his followers could see that Jesus truly was God's Son. At the same time, God told them that Jesus was his Son. (See: [[rc://en/tw/dict/bible/kt/glory]] and [[rc://en/tw/dict/bible/kt/fear]])</p>
                            <h2>Links:</h2>
                            <ul>
                                <li><strong><b>Matthew 17:01 Notes</b></strong></li>
                            </ul>
                            <p><strong><b>&lt;&lt;</b> | <b>&gt;&gt;</b></strong></p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> Verse 1 </span> </div>
                        <div class="word_def">
                            <h1>General Information:</h1>
                            <p>This begins the account of Jesus' transfiguration.</p>
                            <h1>Peter, James, and John his brother</h1>
                            <p>"Peter, James, and James's brother John"</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> Verse 2 </span> </div>
                        <div class="word_def">
                            <h1>He was transfigured before them</h1>
                            <p>When they looked at him, his appearance was different from what it had been.</p>
                            <h1>He was transfigured</h1>
                            <p>This can be stated in active form. Alternate translation: "his appearance had changed" or "he appeared very different" (See: [[rc://en/ta/man/translate/figs-activepassive]])</p>
                            <h1>before them</h1>
                            <p>"in front of them" or "so they could clearly him"</p>
                            <h1>His face shone like the sun, and his garments became as brilliant as the light</h1>
                            <p>These are similes that emphasize how bright Jesus' appearance became. (See: [[rc://en/ta/man/translate/figs-simile]])</p>
                            <h1>his garments</h1>
                            <p>"what he was wearing"</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> Verse 3 </span> </div>
                        <div class="word_def">
                            <h1>Behold</h1>
                            <p>This word alerts us to pay attention to the surprising information that follows.</p>
                            <h1>to them</h1>
                            <p>This refers to Peter, James, and John.</p>
                            <h1>with him</h1>
                            <p>"with Jesus"</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> Verse 4 </span> </div>
                        <div class="word_def">
                            <h1>answered and said</h1>
                            <p>"said." Peter is not responding to a question.</p>
                            <h1>it is good for us to be here</h1>
                            <p>It is not clear whether "us" refers only to Peter, James, and John, or if it refers to everyone there, including Jesus, Elijah, and Moses. If you can translate so that both options are possible, do so. (See: [[rc://en/ta/man/translate/figs-exclusive]] and [[rc://en/ta/man/translate/figs-inclusive]])</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> Verse 5 </span> </div>
                        <div class="word_def">
                            <h1>behold</h1>
                            <p>This alerts the reader to pay attention to the surprising information that follows.</p>
                            <h1>overshadowed them</h1>
                            <p>"came over them"</p>
                            <h1>there was a voice out of the cloud</h1>
                            <p>Here "voice" refers to God speaking. Alternate translation: "God spoke to them from out of the cloud" (See: [[rc://en/ta/man/translate/figs-metonymy]])</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> Verse 6 </span> </div>
                        <div class="word_def">
                            <h1>the disciples heard it</h1>
                            <p>"the disciples heard God speak"</p>
                            <h1>they fell on their face</h1>
                            <p>Here "fell on their face" here is an idiom. Alternate translation: "they fell forward, with their faces to the ground" (See: [[rc://en/ta/man/translate/figs-idiom]])</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> Verse 7 </span> </div>
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
                        <div class="word_term"> <span style="font-weight: bold;"> Verse 8 </span> </div>
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
                        <div class="word_term"> <span style="font-weight: bold;"> Verse 9 </span> </div>
                        <div class="word_def">
                            <h1>Connecting Statement:</h1>
                            <p>The following events happen immediately after the three disciples witness Jesus' transfiguration.</p>
                            <h1>As they</h1>
                            <p>"As Jesus and the disciples"</p>
                            <h1>the Son of Man</h1>
                            <p>Jesus is speaking about himself. (See: [[rc://en/ta/man/translate/figs-123person]])</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> Verse 10 </span> </div>
                        <div class="word_def">
                            <h1>Why then do the scribes say that Elijah must come first?</h1>
                            <p>The disciples are referring to the belief that Elijah will come back to life and return to the people of Israel before the Messiah comes. (See: [[rc://en/ta/man/translate/figs-explicit]])</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> Verse 11 </span> </div>
                        <div class="word_def">
                            <h1>restore all things</h1>
                            <p>"put things in order" or "get the people ready to receive the Messiah"</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> Verse 12 </span> </div>
                        <div class="word_def">
                            <h1>But I tell you</h1>
                            <p>This adds emphasis to what Jesus says next.</p>
                            <h1>they ... their</h1>
                            <p>All occurrences of these words may mean either 1) the Jewish leaders or 2) all the Jewish people.</p>
                            <h1>the Son of Man will also suffer at their hands</h1>
                            <p>Here "hands" refers to power. Alternate translation: "they will make the Son of Man suffer" (See: [[rc://en/ta/man/translate/figs-metonymy]])</p>
                            <h1>Son of Man</h1>
                            <p>Jesus is referring to himself. (See: [[rc://en/ta/man/translate/figs-123person]])</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> Verse 13 </span> </div>
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
                        <div class="word_term"> <span style="font-weight: bold;"> Verse 14 </span> </div>
                        <div class="word_def">
                            <h1>Connecting Statement:</h1>
                            <p>This begins an account of Jesus healing a boy who had an evil spirit. These events happen immediately after Jesus and his disciples descend from the mountain.</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> Verse 15 </span> </div>
                        <div class="word_def">
                            <h1>have mercy on my son</h1>
                            <p>It is implied that the man wants Jesus to heal his son. Alternate translation: "have mercy on my son and heal him" (See: [[rc://en/ta/man/translate/figs-explicit]])</p>
                            <h1>is epileptic</h1>
                            <p>This means that he sometimes had seizures. He would become unconscious and move uncontrollably. Alternate translation: "has seizures"</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> Verse 16 </span> </div>
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
                        <div class="word_term"> <span style="font-weight: bold;"> Verse 17 </span> </div>
                        <div class="word_def">
                            <h1>Unbelieving and corrupt generation, how</h1>
                            <p>"This generation does not believe in God and does not know what is right or wrong. How"</p>
                            <h1>how long will I have to stay with you? How long must I bear with you?</h1>
                            <p>These questions show Jesus is unhappy with the people. Alternate translation: "I am tired of being with you! I am tired of your unbelief and corruption!" (See: [[rc://en/ta/man/translate/figs-rquestion]])</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> Verse 18 </span> </div>
                        <div class="word_def">
                            <h1>the boy was healed</h1>
                            <p>This can be stated in active form. Alternate translation: "the boy became well" (See: [[rc://en/ta/man/translate/figs-activepassive]])</p>
                            <h1>from that hour</h1>
                            <p>This is an idiom. Alternate translation: "immediately" or "at that moment" (See: [[rc://en/ta/man/translate/figs-idiom]])</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> Verse 19 </span> </div>
                        <div class="word_def">
                            <h1>we</h1>
                            <p>Here "we" refers to the speakers but not the hearers and so is exclusive. (See: [[rc://en/ta/man/translate/figs-exclusive]])</p>
                            <h1>Why could we not cast it out?</h1>
                            <p>"Why could we not make the demon come out of the boy?"</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> Verse 20 </span> </div>
                        <div class="word_def">
                            <h1>For I truly say to you</h1>
                            <p>"I tell you the truth." This adds emphasis to what Jesus says next.</p>
                            <h1>if you have faith even as small as a grain of mustard seed</h1>
                            <p>Jesus compares the size of a mustard seed to the amount of faith needed to do a miracle. A mustard seed is very small, but it grows into a large plant. Jesus means it only takes a small amount of faith to do a great miracle. (See: [[rc://en/ta/man/translate/figs-simile]])</p>
                            <h1>nothing will be impossible for you</h1>
                            <p>This can be stated in a positive form. Alternate translation: "you will be able to do anything" (See: [[rc://en/ta/man/translate/figs-litotes]])</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> Verse 21 </span> </div>
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
                        <div class="word_term"> <span style="font-weight: bold;"> Verse 22 </span> </div>
                        <div class="word_def">
                            <h1>Connecting Statement:</h1>
                            <p>Here the scene shifts momentarily, and Jesus foretells his death and resurrection a second time.</p>
                            <h1>they stayed</h1>
                            <p>"Jesus and his disciples stayed"</p>
                            <h1>The Son of Man will be delivered</h1>
                            <p>This can be stated in active form. Alternate translation: "Someone will deliver the Son of Man" (See: [[rc://en/ta/man/translate/figs-activepassive]])</p>
                            <h1>delivered into the hands of people</h1>
                            <p>The word "hands" here is a metonym for the power that people use hands to exercise. Alternate translation: "taken and put under the power of people" or "taken and given to people who will control him" (See: [[rc://en/ta/man/translate/figs-metonymy]])</p>
                            <h1>The Son of Man</h1>
                            <p>Jesus is referring to himself in the third person. (See: [[rc://en/ta/man/translate/figs-123person]])</p>
                            <h1>into the hands of people</h1>
                            <p>Here "hands" refers to power or control. Alternate translation: "to the control of the people" or "to the people" (See: [[rc://en/ta/man/translate/figs-metonymy]])</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> Verse 23 </span> </div>
                        <div class="word_def">
                            <h1>him ... he</h1>
                            <p>Jesus is referring to himself in the third person. (See: [[rc://en/ta/man/translate/figs-123person]])</p>
                            <h1>third day</h1>
                            <p>"Third" is the ordinal form of "three." (See: [[rc://en/ta/man/translate/translate-ordinal]])</p>
                            <h1>he will be raised up</h1>
                            <p>Here to raise up is an idiom for causing someone who has died to become alive again. This can be stated in active form. Alternate translation: "God will raise him up" or "God will cause him to become alive again" (See: [[rc://en/ta/man/translate/figs-activepassive]] and [[rc://en/ta/man/translate/figs-idiom]])</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> Verse 24 </span> </div>
                        <div class="word_def">
                            <h1>Connecting Statement:</h1>
                            <p>Here the scene shifts again to a later time when Jesus teaches Peter about paying the temple tax.</p>
                            <h1>When they</h1>
                            <p>"When Jesus and his disciples"</p>
                            <h1>the two-drachma tax</h1>
                            <p>This was a tax that Jewish men paid to support the temple in Jerusalem. Alternate translation: "the temple tax" (See: [[rc://en/ta/man/translate/translate-bmoney]] and [[rc://en/ta/man/translate/figs-explicit]])</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> Verse 25 </span> </div>
                        <div class="word_def">
                            <h1>the house</h1>
                            <p>"the place where Jesus was staying"</p>
                            <h1>"What do you think, Simon? From whom do the kings of the earth collect tolls or taxes? From their sons or from others?"</h1>
                            <p>Jesus asks these questions to teach Simon, not to gain information for himself. Alternate translation: "Listen, Simon. We know that when kings collect taxes, they collect it from people who are not members of their own family" (See: [[rc://en/ta/man/translate/figs-rquestion]])</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> Verse 26 </span> </div>
                        <div class="word_def">
                            <h1>General Information:</h1>
                            <p>This is the end of the part of the story that began in <b>Matthew 13:54</b>, where Matthew tells of continued opposition to Jesus' ministry and teaching about the kingdom of heaven.</p>
                            <h1>Connecting Statement:</h1>
                            <p>Jesus continues to teach Peter about paying the temple tax.</p>
                            <h1>When he said, "From others," Jesus said</h1>
                            <p>If you translated Jesus' questions as statements in <b>Matthew 17:25</b>, you may need to give an alternate response here. You could also state it as an indirect quotation. Alternate translation: "When Peter said, 'Yes, that is true. Kings collect taxes from foreigners,' Jesus said" or "After Peter agreed with Jesus, Jesus said" (See: [[rc://en/ta/man/translate/figs-quotations]])</p>
                            <h1>From others</h1>
                            <p>In modern times, leaders usually tax their own citizens. But, in ancient times, the leaders often taxed the people they had conquered rather than their own citizens.</p>
                            <h1>sons</h1>
                            <p>people over whom a ruler or king rules</p>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;"> Verse 27-2 </span> </div>
                        <div class="word_def">
                            <h1>But so that we do not cause the tax collectors to sin, go</h1>
                            <p>"But we do not want to make the tax collectors angry. So, go."</p>
                            <h1>throw in a hook</h1>
                            <p>Fishermen tied hooks to the end of a line, then threw it in the water to catch fish. (See: [[rc://en/ta/man/translate/figs-explicit]])</p>
                            <h1>its mouth</h1>
                            <p>"the fish's mouth"</p>
                            <h1>a shekel</h1>
                            <p>a silver coin worth four days' wages (See: [[rc://en/ta/man/translate/translate-bmoney]])</p>
                            <h1>Take it</h1>
                            <p>"Take the shekel"</p>
                            <h1>for me and you</h1>
                            <p>Here "you" is singular and refers to Peter. Each man had to pay a half shekel tax. So one shekel would be enough for Jesus and Peter to pay their taxes. (See: [[rc://en/ta/man/translate/figs-you]])</p>
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

<div class="ttools_panel tw_tool panel panel-default" draggable="true">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo __("tw") ?></h1>
        <span class="panel-close glyphicon glyphicon-remove" data-tool="tw"></span>
    </div>

    <div class="ttools_content page-content panel-body">
        <div class="labels_list">
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Amen </span> (verses: 20) </div>
                        <div class="word_def">
                            <h1>amen, truly</h1>
                            <h2>Definition:</h2>
                            <p>The term "amen" is a word used to emphasize or call attention to what a person has said. It is often used at the end of a prayer. Sometimes it is translated as "truly."</p>
                            <ul>
                                <li>When used at the end of a prayer, "amen" communicates agreement with the prayer or expresses a desire that the prayer be fulfilled.</li>
                                <li>In his teaching, Jesus used "amen" to emphasize the truth of what he said. He often followed that by "and I say to you" to introduce another teaching that related to the previous teaching.</li>
                                <li>When Jesus uses "amen" this way, some English versions (and the ULB) translate this as "verily" or "truly."</li>
                                <li>Another word meaning "truly" is sometimes translated as "surely" or "certainly" and is also used to emphasize what the speaker is saying.</li>
                            </ul>
                            <h2>Translation Suggestions:</h2>
                            <ul>
                                <li>Consider whether the target language has a special word or phrase that is used to emphasize something that has been said.</li>
                                <li>When used at the end of a prayer or to confirm something, "amen" could be translated as "let it be so" or "may this happen" or "that is true."</li>
                                <li>When Jesus says, "truly I tell you," this could also be translated as "Yes, I tell you sincerely" or "That is true, and I also tell you."</li>
                                <li>The phrase "truly, truly I tell you" could be translated as "I tell you this very sincerely" or "I tell you this very earnestly" or "what I am telling you is true."</li>
                            </ul>
                            <p>(See also: <b>fulfill</b>, <b>true</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>Deuteronomy 27:15</b></li>
                                <li><b>John 05:19-20</b></li>
                                <li><b>Jude 01:24-25</b></li>
                                <li><b>Matthew 26:33-35</b></li>
                                <li><b>Philemon 01:23-25</b></li>
                                <li><b>Revelation 22:20-21</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H543, G281</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Bearanimal </span> (verses: 17) </div>
                        <div class="word_def">
                            <h1>bear, bears</h1>
                            <h2>Definition:</h2>
                            <p>A bear is a large, four-legged furry animal with dark brown or black hair, with sharp teeth and claws. Bears were common in Israel during Bible times.</p>
                            <ul>
                                <li>These animals live in forests and mountain areas; they eat fish, insects, and plants.</li>
                                <li>In the Old Testament, the bear is used as a symbol of strength.</li>
                                <li>While tending sheep, the shepherd David fought a bear and defeated it.</li>
                                <li>Two bears came out of the forest and attacked a group of youths who had mocked the prophet Elisha.</li>
                            </ul>
                            <p>(See also: <b>David</b>, <b>Elisha</b>)</p>
                            <h2>Bible References:</h2>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H1677, G715</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Beloved </span> (verses: 5) </div>
                        <div class="word_def">
                            <h1>beloved</h1>
                            <h2>Definition:</h2>
                            <p>The term "beloved" is an expression of affection that describes someone who is loved and dear to someone else.</p>
                            <ul>
                                <li>The term "beloved" literally means "loved (one)" or "(who is) loved."</li>
                                <li>God refers to Jesus as his "beloved Son."</li>
                                <li>In their letters to Christian churches, the apostles frequently address their fellow believers as "beloved."</li>
                            </ul>
                            <h2>Translation Suggestions:</h2>
                            <ul>
                                <li>This term could also be translated as "loved" or "loved one" or "well-loved," or "very dear."</li>
                                <li>In the context of talking about a close friend, this could be translated as "my dear friend" or "my close friend." In English it is natural to say "my dear friend, Paul" or "Paul, who is my dear friend." Other languages may find it more natural to order this in a different way.</li>
                                <li>Note that the word "beloved" comes from the word for God's love, which is unconditional, unselfish, and sacrificial.</li>
                            </ul>
                            <p>(See also: <b>love</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>1 Corinthians 04:14-16</b></li>
                                <li><b>1 John 03:1-3</b></li>
                                <li><b>1 John 04:7-8</b></li>
                                <li><b>Mark 01:9-11</b></li>
                                <li><b>Mark 12:6-7</b></li>
                                <li><b>Revelation 20:9-10</b></li>
                                <li><b>Romans 16:6-8</b></li>
                                <li><b>Song of Solomon 01:12-14</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H157, H1730, H2532, H3033, H3039, H4261, G25, G27, G5207</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Biblicaltimeday </span> (verses: 23) </div>
                        <div class="word_def">
                            <h1>day, days</h1>
                            <h2>Definition:</h2>
                            <p>The term "day" literally refers to a period of time lasting 24 hours beginning at sundown. It is also used figuratively.</p>
                            <ul>
                                <li>For the Israelites and the Jews, a day began at sunset of one day and ended at sunset of the next day.</li>
                                <li>Sometimes the term "day" is used figuratively to refer to a longer period of time, such as the "day of Yahweh" or "last days."</li>
                                <li>Some languages will use a different expression to translate these figurative uses or will translate "day" nonfiguratively.</li>
                                <li>Other translations of "day" could include, "time" or "season" or "occasion" or "event," depending on the context.</li>
                            </ul>
                            <p>(See also: <b>judgment day</b>, <b>last day</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>Acts 20:4-6</b></li>
                                <li><b>Daniel 10:4-6</b></li>
                                <li><b>Ezra 06:13-15</b></li>
                                <li><b>Ezra 06:19-20</b></li>
                                <li><b>Matthew 09:14-15</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H3117, H3118, H6242, G2250</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Biblicaltimehour </span> (verses: 18) </div>
                        <div class="word_def">
                            <h1>hour, hours</h1>
                            <h2>Definition:</h2>
                            <p>The term "hour" is often used in the Bible to tell what time of day a certain event took place. It is also used figuratively to mean "time" or "moment."</p>
                            <ul>
                                <li>The Jews counted daylight hours starting at sunrise (around 6 a.m.). For example, "the ninth hour" meant "around three in the afternoon."</li>
                                <li>Nighttime hours were counted starting at sunset (around 6 p.m.). For example, "the third hour of the night" meant "around nine in the evening" in our present-day system..</li>
                                <li>Since references to time in the Bible will not correspond exactly to the present-day time system, phrases such as "around nine" or "about six o'clock" could be used. </li>
                                <li>Some translations might add phrases like "in the evening" or "in the morning" or "in the afternoon" to make it clear what time of day is being talked about.</li>
                                <li>The phrase, "in that hour" could be translated as, "at that time" or "in that moment."</li>
                                <li>Referring to Jesus, the expression "his hour had come" could be translated as, "the time had come for him to" or "the appointed time for him had come."</li>
                            </ul>
                            <h2>Bible References: ##</h2>
                            <ul>
                                <li><b>Acts 02:14-15</b></li>
                                <li><b>John 04:51-52</b></li>
                                <li><b>Luke 23:44-45</b></li>
                                <li><b>Matthew 20:3-4</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H8160, G5610</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Brother </span> (verses: 1) </div>
                        <div class="word_def">
                            <h1>brother, brothers</h1>
                            <h2>Definition:</h2>
                            <p>The term "brother" usually refers to a male person who shares at least one biological parent with another person.</p>
                            <ul>
                                <li>In the Old Testament, the term "brothers" is also used as a general reference to relatives, such as members of the same tribe, clan, or people group.</li>
                                <li>In the New Testament, the apostles often used "brothers" to refer to fellow Christians, including both men and women, since all believers in Christ are members of one spiritual family, with God as their heavenly Father.</li>
                                <li>A few times in the New Testament, the apostles used the term "sister" when referring specifically to a fellow Christian who was a woman, or to emphasize that both men and women are being included. For example, James emphasizes that he is talking about all believers when he refers to "a brother or sister who is in need of food or clothing."</li>
                            </ul>
                            <h2>Translation Suggestions:</h2>
                            <ul>
                                <li>It is best to translate this term with the literal word that is used in the target language to refer to a natural or biological brother, unless this would give wrong meaning.</li>
                                <li>In the Old Testament especially, when "brothers" is used very generally to refer to members of the same family, clan, or people group, possible translations could include "relatives" or "clan members" or "fellow Israelites."</li>
                                <li>In the context of referring to a fellow believer in Christ, this term could be translated as "brother in Christ" or "spiritual brother."</li>
                                <li>If both males and females are being referred to and "brother" would give a wrong meaning, then a more general kinship term could be used that would include both males and females.</li>
                                <li>Other ways to translate this term so that it refers to both male and female believers could be "fellow believers" or "Christian brothers and sisters."</li>
                                <li>Make sure to check the context to determine whether only men are being referred to, or whether both men and women are included.</li>
                            </ul>
                            <p>(See also: <b>apostle</b>, <b>God the Father</b>, <b>sister</b>, <b>spirit</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>Acts 07:26-28</b></li>
                                <li><b>Genesis 29:9-10</b></li>
                                <li><b>Leviticus 19:17-18</b></li>
                                <li><b>Nehemiah 03:1-2</b></li>
                                <li><b>Philippians 04:21-23</b></li>
                                <li><b>Revelation 01:9-11</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H251, H252, H264, H1730, H2992, H2993, H2994, H7453, G80, G81, G2385, G2455, G2500, G4613, G5360, G5569</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Capernaum </span> (verses: 24) </div>
                        <div class="word_def">
                            <h1>Capernaum</h1>
                            <h2>Facts:</h2>
                            <p>Capernaum was a fishing village on the northwest shore of the Sea of Galilee.</p>
                            <ul>
                                <li>Jesus lived in Capernaum whenever he was teaching in Galilee.</li>
                                <li>Several of his disciples were from Capernaum.</li>
                                <li>Jesus also did many miracles in this city, including bringing a dead girl back to life.</li>
                                <li>Capernaum was one of three cities that Jesus publicly rebuked because their people rejected him and did not believe his message. He warned them that God would punish them for their unbelief.</li>
                            </ul>
                            <p>(Translation suggestions: <b>How to Translate Names</b>)</p>
                            <p>(See also: <b>Galilee</b>, <b>Sea of Galilee</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>John 02:12</b></li>
                                <li><b>Luke 04:31-32</b></li>
                                <li><b>Luke 07:1</b></li>
                                <li><b>Mark 01:21-22</b></li>
                                <li><b>Mark 02:1-2</b></li>
                                <li><b>Matthew 04:12-13</b></li>
                                <li><b>Matthew 17:24-25</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: G2584</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Command </span> (verses: 9) </div>
                        <div class="word_def">
                            <h1>command, commands, commanded, commandment, commandments</h1>
                            <h2>Definition:</h2>
                            <p>The term to "command" means to order someone to do something. A "command" or "commandment" is what the person was ordered to do.</p>
                            <ul>
                                <li>Although these terms have basically the same meaning, "commandment" often refers to certain commands of God which are more formal and permanent, such as the "Ten Commandments."</li>
                                <li>A command can be positive ("Honor your parents") or negative ("Do not steal").</li>
                                <li>To "take command" means to "take control" or "take charge" of something or someone.</li>
                            </ul>
                            <h2>Translation Suggestions</h2>
                            <ul>
                                <li>It is best to translate this term differently from the term, "law." Also compare with the definitions of "decree" and "statute."</li>
                                <li>Some translators may prefer to translate "command" and "commandment" with the same word in their language.</li>
                                <li>Others may prefer to use a special word for commandment that refers to lasting, formal commands that God has made.</li>
                            </ul>
                            <p>(See <b>decree</b>, <b>statute</b>, <b>law</b>, <b>Ten Commandments</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>Luke 01:5-7</b></li>
                                <li><b>Matthew 01:24-25</b></li>
                                <li><b>Matthew 22:37-38</b></li>
                                <li><b>Matthew 28:20</b></li>
                                <li><b>Numbers 01:17-19</b></li>
                                <li><b>Romans 07:7-8</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H559, H560, H565, H1696, H1697, H1881, H2706, H2708, H2710, H2941, H2942, H2951, H3027, H3982, H3983, H4406, H4662, H4687, H4929, H4931, H4941, H5057, H5713, H5749, H6213, H6310, H6346, H6490, H6673, H6680, H7101, H7218, H7227, H7262, H7761, H7970, H8269, G1263, G1291, G1296, G1297, G1299, G1690, G1778, G1781, G1785, G2003, G2004, G2008, G2036, G2753, G3056, G3726, G3852, G3853, G4367, G4483, G4487, G5506</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Commander </span> (verses: 9, 19) </div>
                        <div class="word_def">
                            <h1>commander, commanders</h1>
                            <h2>Definition:</h2>
                            <p>The term "commander" refers to a leader of an army who is responsible for leading and commanding a certain group of soldiers.</p>
                            <ul>
                                <li>A commander could be in charge of a small group of soldiers or a large group, such as a thousand men.</li>
                                <li>This term is also used to refer to Yahweh as the commander of angel armies.</li>
                                <li>Other ways to translate "commander" could include, "leader" or "captain" or "officer."</li>
                                <li>The term to "command" an army could be translated as to "lead" or to "be in charge of."</li>
                            </ul>
                            <p>(See also: <b>command</b>, <b>ruler</b>, <b>centurion</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>1 Chronicles 11:4-6</b></li>
                                <li><b>2 Chronicles 11:11-12</b></li>
                                <li><b>Daniel 02:14-16</b></li>
                                <li><b>Mark 06:21-22</b></li>
                                <li><b>Proverbs 06:6-8</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H2710, H2951, H1169, H4929, H5057, H6346, H7101, H7262, H7218, H7227, H7229, H7990, H8269, G5506</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Corrupt </span> (verses: 17) </div>
                        <div class="word_def">
                            <h1>corrupt, corrupts, corrupted, corrupting, corruption, corruptly, incorruptibility</h1>
                            <h2>Definition:</h2>
                            <p>The terms "corrupt" and "corruption" refer to a state of affairs in which people have become ruined, immoral, or dishonest.</p>
                            <ul>
                                <li>The term "corrupt" literally means to be "bent" or "broken" morally.</li>
                                <li>A person who is corrupt has turned away from truth and is doing things that are dishonest or immoral.</li>
                                <li>To corrupt someone means to influence that person to do dishonest and immoral things.</li>
                            </ul>
                            <h2>Translation Suggestions:</h2>
                            <ul>
                                <li>The term to "corrupt" could be translated as to "influence to do evil" or to "cause to be immoral."</li>
                                <li>A corrupt person could be described as a person "who has become immoral" or "who practices evil."</li>
                                <li>This term could also be translated as "bad" or "immoral" or "evil."</li>
                                <li>The term "corruption" could be translated as "the practice of evil" or "evil" or "immorality."</li>
                            </ul>
                            <p>(See also: <b>evil</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>Ezekiel 20:42-44</b></li>
                                <li><b>Galatians 06:6-8</b></li>
                                <li><b>Genesis 06:11-12</b></li>
                                <li><b>Matthew 12:33-35</b></li>
                                <li><b>Psalm 014:1</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H1097, H1605, H2254, H2610, H4167, H4743, H4889, H4893, H7843, H7844, H7845, G853, G861, G862, G1311, G1312, G2585, G2704, G4550, G4595, G5349, G5351, G5356</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Death </span> (verses: 9) </div>
                        <div class="word_def">
                            <h1>die, dies, died, dead, deadly, deadness, death, deaths, deathly</h1>
                            <h2>Definition:</h2>
                            <p>This term is used to refer to both physical and spiritual death. Physically, it refers to when the physical body of a person stops living. Spiritually, it refers to sinners being separated from a holy God because of their sin.</p>
                            <h2>1. Physical death</h2>
                            <ul>
                                <li>To "die" means to stop living. Death is the end of physical life.</li>
                                <li>A person's spirit leaves his body when he dies.</li>
                                <li>When Adam and Eve sinned, physical death came into the world.</li>
                                <li>The expression "put to death" refers to killing or murdering someone, especially when a king or other ruler gives an order for someone to be killed.</li>
                            </ul>
                            <h2>2. Spiritual death</h2>
                            <ul>
                                <li>Spiritual death is the separation of a person from God.</li>
                                <li>Adam died spiritually when he disobeyed God. His relationship with God was broken. He became ashamed and tried to hide from God.</li>
                                <li>Every descendant of Adam is a sinner, and is spiritually dead. God makes us spiritually alive again when we have faith in Jesus Christ.</li>
                            </ul>
                            <h2>Translation Suggestions:</h2>
                            <ul>
                                <li>To translate this term, it is best to use the everyday, natural word or expression in the target language that refers to death.</li>
                                <li>In some languages, to "die" may be expressed as to "not live." The term "dead" may be translated as "not alive" or "not having any life" or "not living."</li>
                                <li>Many languages use figurative expressions to describe death, such as to "pass away" in English. However, in the Bible it is best to use the most direct term for death that is used in everyday language.</li>
                                <li>In the Bible, physical life and death are often compared to spiritual life and death. It is important in a translation to use the same word or phrase for both physical death and spiritual death.</li>
                                <li>In some languages it may be more clear to say "spiritual death" when the context requires that meaning. Some translators may also feel it is best to say "physical death" in contexts where it is being contrasted to spiritual death.</li>
                                <li>The expression "the dead" is a nominal adjective that refers to people who have died. Some languages will translate this as "dead people" or "people who have died." (See: <b>nominal adjective</b>)</li>
                                <li>The expression "put to death" could also be translated as "kill" or "murder" or "execute."</li>
                            </ul>
                            <p>(See also: <b>believe</b>, <b>faith</b>, <b>life</b>, <b>spirit</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>1 Corinthians 15:20-21</b></li>
                                <li><b>1 Thessalonians 04:16-18</b></li>
                                <li><b>Acts 10:42-43</b></li>
                                <li><b>Acts 14:19-20</b></li>
                                <li><b>Colossians 02:13-15</b></li>
                                <li><b>Colossians 02:20-23</b></li>
                                <li><b>Genesis 02:15-17</b></li>
                                <li><b>Genesis 34:27-29</b></li>
                                <li><b>Matthew 16:27-28</b></li>
                                <li><b>Romans 05:10-11</b></li>
                                <li><b>Romans 05:12-13</b></li>
                                <li><b>Romans 06:10-11</b></li>
                            </ul>
                            <h2>Examples from the Bible stories:</h2>
                            <ul>
                                <li><strong><b>01:11</b></strong> God told Adam that he could eat from any tree in the garden except from the tree of the knowledge of good and evil. If he ate from this tree, he would <strong>die</strong>.</li>
                                <li><strong><b>02:11</b></strong> "Then you will <strong>die</strong>, and your body will return to dirt."</li>
                                <li><strong><b>07:10</b></strong> Then Isaac <strong>died</strong>, and Jacob and Esau buried him.</li>
                                <li><strong><b>37:05</b></strong> "Jesus replied, "I am the Resurrection and the Life. Whoever believes in me will live, even though he <strong>dies</strong>. Everyone who believes in me will never <strong>die</strong>."</li>
                                <li><strong><b>40:08</b></strong> Through his <strong>death</strong>, Jesus opened a way for people to come to God.</li>
                                <li><strong><b>43:07</b></strong> "Although Jesus <strong>died</strong>, God raised him from the dead."</li>
                                <li><strong><b>48:02</b></strong> Because they sinned, everyone on earth gets sick and everyone <strong>dies</strong>.</li>
                                <li><strong><b>50:17</b></strong> He (Jesus) will wipe away every tear and there will be no more suffering, sadness, crying, evil, pain, or <strong>death</strong>.</li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H6, H1478, H1826, H1934, H2491, H4191, H4192, H4193, H4194, H4463, H5038, H5315, H6297, H6757, H7496, H7523, H8045, H8546, H8552, G336, G337, G520, G581, G599, G615, G622, G684, G1634, G1935, G2079, G2253, G2286, G2287, G2288, G2289, G2348, G2837, G2966, G3498, G3499, G3500, G4430, G4880, G4881, G5053, G5054</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Demon </span> (verses: 18, 21) </div>
                        <div class="word_def">
                            <h1>demon, evil spirit, unclean spirit</h1>
                            <h2>Definition:</h2>
                            <p>All these terms refer to demons, which are spirit beings that oppose God's will.</p>
                            <ul>
                                <li>God created angels to serve him. When the devil rebelled against God, some of the angels also rebelled and were thrown out of heaven. It is believed that demons and evil spirits are these "fallen angels."</li>
                                <li>Sometimes these demons are called "unclean spirits." The term "unclean" means "impure" or "evil" or "unholy."</li>
                                <li>Because demons serve the devil, they do evil things. Sometimes they live inside people and control them.</li>
                                <li>Demons are more powerful than human beings, but not as powerful as God.</li>
                            </ul>
                            <h2>Translation Suggestions:</h2>
                            <ul>
                                <li>The term "demon" could also be translated as "evil spirit."</li>
                                <li>The term "unclean spirit" could also be translated as "impure spirit" or "corrupt spirit" or "evil spirit."</li>
                                <li>Make sure that the word or phrase used to translate this term is different from the term used to refer to the devil.</li>
                                <li>Also consider how the term "demon" is translated in a local or national language. (See: <b>How to Translate Unknowns</b>)</li>
                            </ul>
                            <p>(See also: <b>demon-possessed</b>, <b>Satan</b>, <b>false god</b>, <b>false god</b>, <b>angel</b>, <b>evil</b>, <b>clean</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>James 02:18-20</b></li>
                                <li><b>James 03:15-18</b></li>
                                <li><b>Luke 04:35-37</b></li>
                                <li><b>Mark 03:20-22</b></li>
                                <li><b>Matthew 04:23-25</b></li>
                            </ul>
                            <h2>Examples from the Bible stories:</h2>
                            <ul>
                                <li><strong><b>26:09</b></strong> Many people who had <strong>demons</strong> in them were brought to Jesus. When Jesus commanded them, the <strong>demons</strong> came out of the people, and often shouted, "You are the Son of God!"</li>
                                <li><strong><b>32:08</b></strong> The <strong>demons</strong> came out of the man and entered the pigs.</li>
                                <li><strong><b>47:05</b></strong> Finally one day when the slave girl started yelling, Paul turned to her and said to the <strong>demon</strong> that was in her, "In the name of Jesus, come out of her." Right away the <strong>demon</strong> left her.</li>
                                <li><strong><b>49:02</b></strong> He (Jesus) walked on water, calmed storms, healed many sick people, drove out <strong>demons</strong>, raised the dead to life, and turned five loaves of bread and two small fish into enough food for over 5,000 people.</li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H2932, H7307, H7451, H7700, G169, G1139, G1140, G1141, G1142, G4190, G4151, G4152, G4189</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Disciple </span> (verses: 6, 10, 13, 16, 19, 22-23) </div>
                        <div class="word_def">
                            <h1>disciple, disciples</h1>
                            <h2>Definition:</h2>
                            <p>The term "disciple" refers to a person who spends much time with a teacher, learning from that teacher's character and teaching.</p>
                            <ul>
                                <li>The people who followed Jesus around, listening to his teachings and obeying them, were called his "disciples."</li>
                                <li>John the Baptist also had disciples.</li>
                                <li>During Jesus' ministry, there were many disciples who followed him and heard his teachings.</li>
                                <li>Jesus chose twelve disciples to be his closest followers; these men became known as his "apostles."</li>
                                <li>Jesus' twelve apostles continued to be known as his "disciples" or "the twelve."</li>
                                <li>Just before Jesus went up to heaven, he commanded his disciples to teach other people about how to become Jesus' disciples, too.</li>
                                <li>Anyone who believes in Jesus and obeys his teachings is called a disciple of Jesus.</li>
                            </ul>
                            <h2>Translation Suggestions:</h2>
                            <ul>
                                <li>The term "disciple" could be translated by a word or phrase that means "follower" or "student" or "pupil" or "learner."</li>
                                <li>Make sure that the translation of this term does not refer only to a student who learns in a classroom.</li>
                                <li>The translation of this term should also be different from the translation of "apostle."</li>
                            </ul>
                            <p>(See also: <b>apostle</b>, <b>believe</b>, <b>Jesus</b>, <b>John (the Baptist)</b>, <b>the twelve</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>Acts 06:1</b></li>
                                <li><b>Acts 09:26-27</b></li>
                                <li><b>Acts 11:25-26</b></li>
                                <li><b>Acts 14:21-22</b></li>
                                <li><b>John 13:23-25</b></li>
                                <li><b>Luke 06:39-40</b></li>
                                <li><b>Matthew 11:1-3</b></li>
                                <li><b>Matthew 26:33-35</b></li>
                                <li><b>Matthew 27:62-64</b></li>
                            </ul>
                            <h2>Examples from the Bible stories:</h2>
                            <ul>
                                <li><strong><b>30:08</b></strong> He (Jesus) gave the pieces to his <strong>disciples</strong> to give to the people. The <strong>disciples</strong> kept passing out the food, and it never ran out!</li>
                                <li><strong><b>38:01</b></strong> About three years after Jesus first began preaching and teaching publicly, Jesus told his <strong>disciples</strong> that he wanted to celebrate this Passover with them in Jerusalem, and that he would be killed there.</li>
                                <li><strong><b>38:11</b></strong> Then Jesus went with his <strong>disciples</strong> to a place called Gethsemane. Jesus told his <strong>disciples</strong> to pray that they would not enter into temptation.</li>
                                <li><strong><b>42:10</b></strong> Jesus said to his <strong>disciples</strong>, "All authority in heaven and on earth has been given to me. So go, make <strong>disciples</strong> of all people groups by baptizing them in the name of the Father, the Son, and the Holy Spirit, and by teaching them to obey everything I have commanded you."</li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H3928, G3100, G3101, G3102</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Earth </span> (verses: 25) </div>
                        <div class="word_def">
                            <h1>earth, earthen, earthly</h1>
                            <h2>Definition:</h2>
                            <p>The term "earth" refers to the world that human beings live on, along with all other forms of life.</p>
                            <ul>
                                <li>"Earth" can also refer to the ground or soil that covers the land.</li>
                                <li>This term is often used figuratively to refer to the people who live on the earth. (See: <b>metonymy</b>)</li>
                                <li>The expressions "let the earth be glad" and "he will judge the earth" are examples of figurative uses of this term.</li>
                                <li>The term "earthly" usually refers to physical things in contrast to spiritual things.</li>
                            </ul>
                            <h2>Translation Suggestions:</h2>
                            <ul>
                                <li>This term can be translated by the word or phrase that the local language or nearby national languages use to refer to the planet earth on which we live.</li>
                                <li>Depending on the context, "earth" could also be translated as "world" or "land" or "dirt" or "soil."</li>
                                <li>When used figuratively, "earth" could be translated as "people on the earth" or "people living on earth" or "everything on earth."</li>
                                <li>Ways to translate "earthly" could include "physical" or "things of this earth" or "visible."</li>
                            </ul>
                            <p>(See also: <b>spirit</b>, <b>world</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>1 Kings 01:38-40</b></li>
                                <li><b>2 Chronicles 02:11-12</b></li>
                                <li><b>Daniel 04:35</b></li>
                                <li><b>Luke 12:51-53</b></li>
                                <li><b>Matthew 06:8-10</b></li>
                                <li><b>Matthew 11:25-27</b></li>
                                <li><b>Zechariah 06:5-6</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H127, H772, H776, H778, H2789, H3007, H3335, H6083, H7494, G1093, G1919, G2709, G2886, G3625, G3749, G4578, G5517</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Elijah </span> (verses: 3-4, 10-12) </div>
                        <div class="word_def">
                            <h1>Elijah</h1>
                            <h2>Facts:</h2>
                            <p>Elijah was one of the most important prophets of Yahweh. Elijah prophesied during the reigns of several kings of Israel and Judah, including King Ahab.</p>
                            <ul>
                                <li>God did many miracles through Elijah, including raising a dead boy back to life.</li>
                                <li>Elijah rebuked King Ahab for worshiping the false god Baal.</li>
                                <li>He challenged the prophets of Baal to a test that proved that Yahweh is the only true God.</li>
                                <li>At the end of Elijah's life, God miraculously took him up to heaven while he was still alive.</li>
                                <li>Hundreds of years later, Elijah, along with Moses, appeared with Jesus on a mountain, and they talked together about Jesus' coming suffering and death in Jerusalem.</li>
                            </ul>
                            <p>(Translation suggestions: <b>How to Translate Names</b>)</p>
                            <p>(See also: <b>miracle</b>, <b>prophet</b>, <b>Yahweh</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>1 Kings 17:1</b></li>
                                <li><b>2 Kings 01:3-4</b></li>
                                <li><b>James 05:16-18</b></li>
                                <li><b>John 01:19-21</b></li>
                                <li><b>John 01:24-25</b></li>
                                <li><b>Mark 09:4-6</b></li>
                            </ul>
                            <h2>Examples from the Bible stories:</h2>
                            <ul>
                                <li><strong><b>19:02</b></strong> <strong>Elijah</strong> was a prophet when Ahab was king over the kingdom of Israel.</li>
                                <li><strong><b>19:02</b></strong> <strong>Elijah</strong> said to Ahab, "There will be no rain or dew in the kingdom of Israel until I say so."</li>
                                <li><strong><b>19:03</b></strong> God told <strong>Elijah</strong> to go to a stream in the wilderness to hide from Ahab who wanted to kill him. Every morning and every evening, birds would bring him bread and meat.</li>
                                <li><strong><b>19:04</b></strong> But they took care of <strong>Elijah</strong>, and God provided for them so that their flour jar and their bottle of oil never became empty.</li>
                                <li><strong><b>19:05</b></strong> After three and a half years, God told <strong>Elijah</strong> to return to the kingdom of Israel and speak with Ahab because he was going to send rain again.</li>
                                <li><strong><b>19:07</b></strong> Then <strong>Elijah</strong> said to the prophets of Baal, "Kill a bull and prepare it as a sacrifice, but do not light the fire."</li>
                                <li><strong><b>19:12</b></strong> Then <strong>Elijah</strong> said, "Do not let any of the prophets of Baal escape!"</li>
                                <li><strong><b>36:03</b></strong> Then Moses and the prophet <strong>Elijah</strong> appeared. These men had lived hundreds of years before this. They talked with Jesus about his death that would soon happen in Jerusalem.</li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H452, G2243</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Face </span> (verses: 2, 6) </div>
                        <div class="word_def">
                            <h1>face, faces, faced, facing, facial, facedown</h1>
                            <h2>Definition:</h2>
                            <p>The word "face" literally refers to the front part of a person's head. This term also has several figurative meanings.</p>
                            <ul>
                                <li>The expression "your face" is often a figurative way of saying "you." Similarly, the expression "my face" often means "I" or "me."</li>
                                <li>In a physical sense, to "face" someone or something means to look in the direction of that person or thing.</li>
                                <li>To "face each other" means to "look directly at each other."</li>
                                <li>Being "face to face" means that two people are seeing each other in person, at a close distance.</li>
                                <li>When Jesus "steadfastly set his face to go to Jerusalem," it means that he very firmly decided to go.</li>
                                <li>To "set one's face against" people or a city means to firmly decide to no longer support, or to reject that city or person.</li>
                                <li>The expression "face of the land" refers to the surface of the earth and often is a general reference to the whole earth. For example, a "famine covering the face of the earth" refers to a widespread famine affecting many people living on earth.</li>
                                <li>The figurative expression "do not hide your face from your people" means "do not reject your people" or "do not desert your people" or "do not stop taking care of your people." </li>
                            </ul>
                            <h2>Translation Suggestions:</h2>
                            <ul>
                                <li>If possible, it is best to keep the expression or use an expression in the project language that has a similar meaning. </li>
                                <li>The term to "face" could be translated as to "turn toward" or to "look at directly" or to "look at the face of."</li>
                                <li>The expression "face to face" could be translated as "up close" or "right in front of" or "in the presence of."</li>
                                <li>Depending on the context, the expression "before his face" could be translated as "ahead of him" or "in front of him" or "before him" or "in his presence."</li>
                                <li>The expression "set his face toward" could be translated as "began traveling toward" or "firmly made up his mind to go to."</li>
                                <li>The expression "hide his face from" could be translated as "turn away from" or "stop helping or protecting" or "reject." </li>
                                <li>To "set his face against" a city or people could be translated as "look at with anger and condemn" or "refuse to accept" or "decide to reject" or "condemn and reject" or "pass judgment on."</li>
                                <li>The expression "say it to their face" could be translated as "say it to them directly" or "say it to them in their presence" or "say it to them in person."</li>
                                <li>The expression "on the face of the land" could also be translated as "throughout the land" or "over the whole earth" or "living throughout the earth."</li>
                            </ul>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>Deuteronomy 05:4-6</b></li>
                                <li><b>Genesis 33:9-11</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H600, H639, H5869, H6440, H8389, G3799, G4383, G4750</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Faith </span> (verses: 20) </div>
                        <div class="word_def">
                            <h1>faith</h1>
                            <h2>Definition:</h2>
                            <p>In general, the term "faith" refers to a belief, trust or confidence in someone or something.</p>
                            <ul>
                                <li>To "have faith" in someone is to believe that what he says and does is true and trustworthy.</li>
                                <li>To "have faith in Jesus" means to believe all of God's teachings about Jesus. It especially means that people trust in Jesus and his sacrifice to cleanse them from their sin and to rescue them from the punishment they deserve because of their sin.</li>
                                <li>True faith or belief in Jesus will cause a person to produce good spiritual fruits or behaviors because the Holy Spirit is living in him.</li>
                                <li>Sometimes "faith" refers generally to all the teachings about Jesus, as in the expression "the truths of the faith."</li>
                                <li>In contexts such as "keep the faith" or "abandon the faith," the term "faith" refers to the state or condition of believing all the teachings about Jesus.</li>
                            </ul>
                            <h2>Translation Suggestions:</h2>
                            <ul>
                                <li>In some contexts, "faith" can be translated as "belief" or "conviction" or "confidence" or "trust."</li>
                                <li>For some languages these terms will be translated using forms of the verb "believe." (See: <b>abstractnouns</b>)</li>
                                <li>The expression "keep the faith" could be translated by "keep believing in Jesus" or "continue to believe in Jesus."</li>
                                <li>The sentence "they must keep hold of the deep truths of the faith" could be translated by "they must keep believing all the true things about Jesus that they have been taught."</li>
                                <li>The expression "my true son in the faith" could be translated by something like "who is like a son to me because I taught him to believe in Jesus" or "my true spiritual son, who believes in Jesus."</li>
                            </ul>
                            <p>(See also: <b>believe</b>, <b>faithful</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>2 Timothy 04:6-8</b></li>
                                <li><b>Acts 06:7</b></li>
                                <li><b>Galatians 02:20-21</b></li>
                                <li><b>James 02:18-20</b></li>
                            </ul>
                            <h2>Examples from the Bible stories:</h2>
                            <ul>
                                <li><strong><b>05:06</b></strong> When Isaac was a young man, God tested Abraham's <strong>faith</strong> by saying, "Take Isaac, your only son, and kill him as a sacrifice to me."</li>
                                <li><strong><b>31:07</b></strong> Then he (Jesus) said to Peter, "You man of little <strong>faith</strong>, why did you doubt?"</li>
                                <li><strong><b>32:16</b></strong> Jesus said to her, "Your <strong>faith</strong> has healed you. Go in peace."</li>
                                <li><strong><b>38:09</b></strong> Then Jesus said to Peter, "Satan wants to have all of you, but I have prayed for you, Peter, that your <strong>faith</strong> will not fail.</li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H529, H530, G1680, G3640, G4102, G6066</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Fear </span> (verses: 6-7) </div>
                        <div class="word_def">
                            <h1>fear, fears, afraid</h1>
                            <h2>Definition:</h2>
                            <p>The terms "fear" and "afraid" refer to the unpleasant feeling a person has when there is a threat of harm to himself or others.</p>
                            <ul>
                                <li>The term "fear" can also refer to a deep respect and awe for a person in authority.</li>
                                <li>The phrase "fear of Yahweh," as well as related terms "fear of God" and "fear of the Lord," refer to a deep respect of God and the showing of that respect by obeying him. This fear is motivated by knowing that God is holy and hates sin.</li>
                                <li>The Bible teaches that a person who fears Yahweh will become wise.</li>
                            </ul>
                            <h2>Translation Suggestions:</h2>
                            <ul>
                                <li>Depending on the context, to "fear" can be translated as to "be afraid" or to "deeply respect" or to "revere" or to "be in awe of."</li>
                                <li>The term "afraid" could be translated as "terrified" or "scared" or "fearful."</li>
                                <li>The sentence "The fear of God fell on all of them" could be translated as "Suddenly they all felt a deep awe and respect for God" or "Immediately, they all felt very amazed and revered God deeply" or "Right then, they all felt very afraid of God (because of his great power)."</li>
                                <li>The phrase "fear not" could also be translated as "do not be afraid" or "stop being afraid."</li>
                                <li>Note that the phrase "fear of Yahweh" does not occur in the New Testament. The phrase "fear of the Lord" or "fear of the Lord God" is used instead.</li>
                            </ul>
                            <p>(See also: <b>marvel</b>, <b>awe</b>, <b>Lord</b>, <b>power</b>, <b>Yahweh</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>1 John 04:17-18</b></li>
                                <li><b>Acts 02:43-45</b></li>
                                <li><b>Acts 19:15-17</b></li>
                                <li><b>Genesis 50:18-21</b></li>
                                <li><b>Isaiah 11:3-5</b></li>
                                <li><b>Job 06:14-17</b></li>
                                <li><b>Jonah 01:8-10</b></li>
                                <li><b>Luke 12:4-5</b></li>
                                <li><b>Matthew 10:28-31</b></li>
                                <li><b>Proverbs 10:24-25</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H367, H926, H1204, H1481, H1672, H1674, H1763, H2119, H2296, H2727, H2729, H2730, H2731, H2844, H2849, H2865, H3016, H3025, H3068, H3372, H3373, H3374, H4032, H4034, H4035, H4116, H4172, H6206, H6342, H6343, H6345, H6427, H7264, H7267, H7297, H7374, H7461, H7493, H8175, G870, G1167, G1168, G1169, G1630, G1719, G2124, G2125, G2962, G5398, G5399, G5400, G5401</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Fire </span> (verses: 15) </div>
                        <div class="word_def">
                            <h1>fire, fires, firebrands, firepans, fireplaces, firepot, firepots</h1>
                            <h2>Definition:</h2>
                            <p>Fire is the heat, light, and flames that are produced when something is burned.</p>
                            <ul>
                                <li>Burning wood by fire turns the wood into ashes.</li>
                                <li>The term "fire" is also used figuratively, usually referring to judgment or purification.</li>
                                <li>The final judgment of unbelievers is in the fire of hell.</li>
                                <li>Fire is used to refine gold and other metals. In the Bible, this process is used to explain how God refines people through difficult things that happen in their lives.</li>
                                <li>The phrase "baptize with fire" could also be translated as "cause to experience suffering in order to be purified."</li>
                            </ul>
                            <p>(See also: <b>pure</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>1 Kings 16:18-20</b></li>
                                <li><b>2 Kings 01:9-10</b></li>
                                <li><b>2 Thessalonians 01:6-8</b></li>
                                <li><b>Acts 07:29-30</b></li>
                                <li><b>John 15:5-7</b></li>
                                <li><b>Luke 03:15-16</b></li>
                                <li><b>Matthew 03:10-12</b></li>
                                <li><b>Nehemiah 01:3</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H215, H217, H398, H784, H800, H801, H1197, H1200, H1513, H2734, H3341, H3857, H4071, H4168, H5135, H6315, H8316, G439, G440, G1067, G2741, G4442, G4443, G4447, G4448, G4451, G5394, G5457</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Galilee </span> (verses: 22) </div>
                        <div class="word_def">
                            <h1>Galilee, Galilean, Galileans</h1>
                            <h2>Facts:</h2>
                            <p>Galilee was the most northern region of Israel, just north of Samaria. A "Galilean" was a person who lived in Galilee or who lived in Galilee.</p>
                            <ul>
                                <li>Galilee, Samaria, and Judea were the three main provinces of Israel during New Testament times.</li>
                                <li>Galilee is bordered on the east by a large lake called the "Sea of Galilee."</li>
                                <li>Jesus grew up and lived in the town of Nazareth in Galilee.</li>
                                <li>Most of the miracles and teachings of Jesus took place in the region of Galilee.</li>
                            </ul>
                            <p>(See also: <b>Nazareth</b>, <b>Samaria</b>, <b>Sea of Galilee</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>Acts 09:31-32</b></li>
                                <li><b>Acts 13:30-31</b></li>
                                <li><b>John 02:1-2</b></li>
                                <li><b>John 04:1-3</b></li>
                                <li><b>Luke 13:1-3</b></li>
                                <li><b>Mark 03:7-8</b></li>
                                <li><b>Matthew 02:22-23</b></li>
                                <li><b>Matthew 03:13-15</b></li>
                            </ul>
                            <h2>Examples from the Bible stories:</h2>
                            <ul>
                                <li><strong><b>21:10</b></strong> The prophet Isaiah said the Messiah would live in <strong>Galilee</strong>, comfort broken-hearted people, and proclaim freedom to captives and release to prisoners.</li>
                                <li><strong><b>26:01</b></strong> After overcoming Satan's temptations, Jesus returned in the power of the Holy Spirit to the region of <strong>Galilee</strong> where he lived.</li>
                                <li><strong><b>39:06</b></strong> Finally, the people said, "We know that you were with Jesus because you both are from <strong>Galilee</strong>."</li>
                                <li><strong><b>41:06</b></strong> Then the angel told the women, "Go and tell the disciples, 'Jesus has risen from the dead and he will go to <strong>Galilee</strong> ahead of you.'"</li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H1551, G1056, G1057</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Generation </span> (verses: 17) </div>
                        <div class="word_def">
                            <h1>generation</h1>
                            <h2>Definition:</h2>
                            <p>The term "generation" refers to a group of people who were all born around the same time period.</p>
                            <ul>
                                <li>A generation can also refer to a span of time. In Bible times, a generation was usually considered to be about 40 years.</li>
                                <li>Parents and their children are from two different generations.</li>
                                <li>In the Bible, the term "generation" is also used figuratively to refer generally to people who share common characteristics. </li>
                            </ul>
                            <h2>Translation Suggestions</h2>
                            <ul>
                                <li>The phrase "this generation" or "people of this generation" could be translated as "the people living now" or "you people."</li>
                                <li>"This wicked generation" could also be translated as "these wicked people living now."</li>
                                <li>The expression "from generation to generation" or "from one generation to the next" could be translated as "people living now, as well as their children and grandchildren" or "people in every time period" or "people in this time period and future time periods" or "all people and their descendants."</li>
                                <li>"A generation to come will serve him; they will tell the next generation about Yahweh" could also be translated as "Many people in the future will serve Yahweh and will tell their children and grandchildren about him."</li>
                            </ul>
                            <p>(See also: <b>descendant</b>, <b>evil</b>, <b>ancestor</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>Acts 15:19-21</b></li>
                                <li><b>Exodus 03:13-15</b></li>
                                <li><b>Genesis 15:14-16</b></li>
                                <li><b>Genesis 17:7-8</b></li>
                                <li><b>Mark 08:11-13</b></li>
                                <li><b>Matthew 11:16-17</b></li>
                                <li><b>Matthew 23:34-36</b></li>
                                <li><b>Matthew 24:34-35</b></li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Glory </span> (verses: 15) </div>
                        <div class="word_def">
                            <h1>glory, glorious, glorify, glorifies</h1>
                            <h2>Definition:</h2>
                            <p>In general, the term "glory" means honor, splendor, and extreme greatness. Anything that has glory is said to be "glorious."</p>
                            <ul>
                                <li>Sometimes "glory" refers to something of great value and importance. In other contexts it communicates splendor, brightness, or judgment.</li>
                                <li>For example, the expression "glory of the shepherds" refers to the lush pastures where their sheep had plenty of grass to eat.</li>
                                <li>Glory is especially used to describe God, who is more glorious than anyone or anything in the universe. Everything in his character reveals his glory and his splendor.</li>
                                <li>The expression to "glory in" means to boast about or take pride in something.</li>
                            </ul>
                            <p>The term "glorify" means to show or tell how great and important something or someone is. It literally means to "give glory to."</p>
                            <ul>
                                <li>People can glorify God by telling about the wonderful things he has done.</li>
                                <li>They can also glorify God by living in a way that honors him and shows how great and magnificent he is.</li>
                                <li>When the Bible says that God glorifies himself, it means that he reveals to people his amazing greatness, often through miracles.</li>
                                <li>God the Father will glorify God the Son by revealing to people the Son's perfection, splendor, and greatness.</li>
                                <li>Everyone who believes in Christ will be glorified with him. When they are raised to life, they will be changed to reflect his glory and to display his grace to all creation.</li>
                            </ul>
                            <h2>Translation Suggestions:</h2>
                            <ul>
                                <li>
                                    <p>Depending on the context, different ways to translate "glory" could include "splendor" or "brightness" or "majesty" or "awesome greatness" or "extreme value."</p>
                                </li>
                                <li>
                                    <p>The term "glorious" could be translated as "full of glory" or "extremely valuable" or "brightly shining" or "awesomely majestic."</p>
                                </li>
                                <li>
                                    <p>The expression "give glory to God" could be translated as "honor God's greatness" or "praise God because of his splendor" or "tell others how great God is."</p>
                                </li>
                                <li>
                                    <p>The expression "glory in" could also be translated as "praise" or "take pride in" or "boast about" or "take pleasure in."</p>
                                </li>
                                <li>
                                    <p>"Glorify" could also be translated as "give glory to" or "bring glory to" or "cause to appear great."</p>
                                </li>
                                <li>
                                    <p>The phrase "glorify God" could also be translated as "praise God" or "talk about God's greatness" or "show how great God is" or "honor God (by obeying him)."</p>
                                </li>
                                <li>
                                    <p>The term "be glorified" could also be translated as, "be shown to be very great" or "be praised" or "be exalted."</p>
                                </li>
                            </ul>
                            <p>(See also: <b>exalt</b>, <b>obey</b>, <b>praise</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>Exodus 24:16-18</b></li>
                                <li><b>Numbers 14:9-10</b></li>
                                <li><b>Isaiah 35:1-2</b></li>
                                <li><b>Luke 18:42-43</b></li>
                                <li><b>Luke 02:8-9</b></li>
                                <li><b>John 12:27-29</b></li>
                                <li><b>Acts 03:13-14</b></li>
                                <li><b>Acts 07:1-3</b></li>
                                <li><b>Romans 08:16-17</b></li>
                                <li><b>1 Corinthians 06:19-20</b></li>
                                <li><b>Philippians 02:14-16</b></li>
                                <li><b>Philippians 04:18-20</b></li>
                                <li><b>Colossians 03:1-4</b></li>
                                <li><b>1 Thessalonians 02:5-6</b></li>
                                <li><b>James 02:1-4</b></li>
                                <li><b>1 Peter 04:15-16</b></li>
                                <li><b>Revelation 15:3-4</b></li>
                            </ul>
                            <h2>Examples from the Bible stories:</h2>
                            <ul>
                                <li><strong><b>23:07</b></strong> Suddenly, the skies were filled with angels praising God, saying, "<strong>Glory</strong> to God in heaven and peace on earth to the people he favors!"</li>
                                <li><strong><b>25:06</b></strong> Then Satan showed Jesus all the kingdoms of the world and all their <strong>glory</strong> and said, "I will give you all this if you bow down and worship me."</li>
                                <li><strong><b>37:01</b></strong> When Jesus heard this news, he said, "This sickness will not end in death, but it is for the <strong>glory</strong> of God."</li>
                                <li><strong><b>37:08</b></strong> Jesus responded, "Did I not tell you that you would see God's <strong>glory</strong> if you believe in me?"</li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H117, H142, H155, H215, H1342, H1921, H1922, H1925, H1926, H1935, H1984, H2892, H3367, H3513, H3519, H3520, H6286, H6643, H7623, H8597, G1391, G1392, G1740, G1741, G2620, G2744, G2745, G2746, G2755, G2811, G4888</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Good </span> (verses: 4) </div>
                        <div class="word_def">
                            <h1>good, goodness</h1>
                            <h2>Definition:</h2>
                            <p>The word "good" has different meanings depending on the context. Many languages will use different words to translate these different meanings.</p>
                            <ul>
                                <li>In general, something is good if it fits with God's character, purposes, and will.</li>
                                <li>Something that is "good" could be pleasing, excellent, helpful, suitable, profitable, or morally right.</li>
                                <li>Land that is "good" could be called "fertile" or "productive."</li>
                                <li>A "good" crop could be a "plentiful" crop.</li>
                                <li>A person can be "good" at what they do if they are skillful at their task or profession, as in, the expression, "a good farmer."</li>
                                <li>In the Bible, the general meaning of "good" is often contrasted with "evil."</li>
                                <li>The term "goodness" usually refers to being morally good or righteous in thoughts and actions.</li>
                                <li>The goodness of God refers to how he blesses people by giving them good and beneficial things. It also can refer to his moral perfection.</li>
                            </ul>
                            <h2>Translation Suggestions:</h2>
                            <ul>
                                <li>The general term for "good" in the target language should be used wherever this general meaning is accurate and natural, especially in contexts where it is contrasted to evil.</li>
                                <li>Depending on the context, other ways to translate this term could include "kind" or "excellent" or "pleasing to God" or "righteous" or "morally upright" or "profitable."</li>
                                <li>"Good land" could be translated as "fertile land" or "productive land"; a "good crop" could be translated as a "plentiful harvest" or "large amount of crops."</li>
                                <li>The phrase "do good to" means to do something that benefits others and could be translated as "be kind to" or "help" or "benefit" someone.</li>
                                <li>To "do good on the Sabbath" means to "do things that help others on the Sabbath."</li>
                                <li>Depending on the context, ways to translate the term "goodness" could include "blessing" or "kindness" or "moral perfection" or "righteousness" or "purity."</li>
                            </ul>
                            <p>(See also: <b>evil</b>, <b>holy</b>, <b>profit</b>, <b>righteous</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>Galatians 05:22-24</b></li>
                                <li><b>Genesis 01:11-13</b></li>
                                <li><b>Genesis 02:9-10</b></li>
                                <li><b>Genesis 02:15-17</b></li>
                                <li><b>James 03:13-14</b></li>
                                <li><b>Romans 02:3-4</b></li>
                            </ul>
                            <h2>Examples from the Bible stories:</h2>
                            <ul>
                                <li><strong><b>01:04</b></strong> God saw that what he had created was <strong>good</strong>.</li>
                                <li><strong><b>01:11</b></strong> God plantedâ€¦the tree of the knowledge of <strong>good</strong> and evil."</li>
                                <li><strong><b>01:12</b></strong> Then God said, "It is not <strong>good</strong> for man to be alone."</li>
                                <li><strong><b>02:04</b></strong> "God just knows that as soon as you eat it, you will be like God and will understand <strong>good</strong> and evil like he does."</li>
                                <li><strong><b>08:12</b></strong> "You tried to do evil when you sold me as a slave, but God used the evil for <strong>good</strong>!"</li>
                                <li><strong><b>14:15</b></strong> Joshua was a <strong>good</strong> leader because he tTable of Contentsrusted and obeyed God.</li>
                                <li><strong><b>18:13</b></strong> Some of these kings were <strong>good</strong> men who ruled justly and worshiped God.</li>
                                <li><strong><b>28:01</b></strong> "<strong>Good</strong> teacher, what must I do to have eternal life?" Jesus said to him, "Why do you call me '<strong>good</strong>?' There is only one who is <strong>good</strong>, and that is God."</li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H117, H145, H155, H202, H239, H410, H1580, H1926, H1935, H2532, H2617, H2623, H2869, H2895, H2896, H2898, H3190, H3191, H3276, H3474, H3788, H3966, H4261, H4399, H5232, H5750, H6287, H6643, H6743, H7075, H7368, H7399, H7443, H7999, H8231, H8232, H8233, H8389, H8458, G14, G15, G18, G19, G515, G744, G865, G979, G1380, G2095, G2097, G2106, G2107, G2108, G2109, G2114, G2115, G2133, G2140, G2162, G2163, G2174, G2293, G2565, G2567, G2570, G2573, G2887, G2986, G3140, G3617, G3776, G4147, G4632, G4674, G4851, G5223, G5224, G5358, G5542, G5543, G5544</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Grain </span> (verses: 20) </div>
                        <div class="word_def">
                            <h1>grain, grains, grainfields</h1>
                            <h2>Definition:</h2>
                            <p>The term "grain" usually refers to the seed of a food plant such as wheat, barley, corn, millet, or rice. It can also refer to the whole plant.</p>
                            <ul>
                                <li>In the Bible, the main grains that are referred to are wheat and barley.</li>
                                <li>A head of grain is the part of the plant that holds the grain.</li>
                                <li>Note that some older Bible versions use the word "corn" to refer to grain in general. In modern English however, "corn" only refers to one type of grain.</li>
                            </ul>
                            <p>(See also: <b>head</b>, <b>wheat</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>Genesis 42:1-4</b></li>
                                <li><b>Genesis 42:26-28</b></li>
                                <li><b>Genesis 43:1-2</b></li>
                                <li><b>Luke 06:1-2</b></li>
                                <li><b>Mark 02:23-24</b></li>
                                <li><b>Matthew 13:7-9</b></li>
                                <li><b>Ruth 01:22</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H1250, H1430, H1715, H2233, H2591, H3759, H3899, H7054, H7383, H7641, H7668, G248, G2590, G3450, G4621, G4719</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Heal </span> (verses: 16) </div>
                        <div class="word_def">
                            <h1>cure, cured, heal, heals, healed, healing, healings, healer, health, healthy, unhealthy</h1>
                            <h2>Definition:</h2>
                            <p>The terms "heal" and "cure" both mean to cause a sick, wounded, or disabled person to be healthy again.</p>
                            <ul>
                                <li>A person who is "healed" or "cured" has been "made well" or "made healthy."</li>
                                <li>Healing can happen naturally since God gave our bodies the ability to recover from many kinds of wounds and diseases. Thsi kind of healing usually happens slowly.</li>
                                <li>However, certain conditions, such as being blind or paralyzed, and certain serious diseases, such as leprosy, however do not heal on their own. When people are healed of these things, it is a miracle that usually happens suddenly.</li>
                                <li>For example, Jesus healed many people who were blind or lame or diseased, and they became well right away.</li>
                                <li>The apostles also healed people miraculously, such as when Peter caused a crippled man to immediately be able to walk. </li>
                            </ul>
                            <p>(See also: <b>miracle</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>Acts 05:14-16</b></li>
                                <li><b>Acts 08:6-8</b></li>
                                <li><b>Luke 05:12-13</b></li>
                                <li><b>Luke 06:17-19</b></li>
                                <li><b>Luke 08:43-44</b></li>
                                <li><b>Matthew 04:23-25</b></li>
                                <li><b>Matthew 09:35-36</b></li>
                                <li><b>Matthew 13:15</b></li>
                            </ul>
                            <h2>Examples from the Bible stories:</h2>
                            <ul>
                                <li><strong><b>19:14</b></strong> One of the miracles happened to Naaman, an enemy commander, who had a horrible skin disease. He had heard of Elisha so he went and asked Elisha to <strong>heal</strong> him.</li>
                                <li><strong><b>21:10</b></strong> He (Isaiah) also predicted that the Messiah would <strong>heal</strong> sick people and those who could not hear, see, speak, or walk.</li>
                                <li><strong><b>26:06</b></strong> Jesus continued saying, "And during the time of the prophet Elisha, there were many people in Israel with skin diseases. But Elisha did not <strong>heal</strong> any of them. He only <strong>healed</strong> the skin disease of Naaman, a commander of Israel's enemies."</li>
                                <li><strong><b>26:08</b></strong> They brought many people who were sick or handicapped, including those who could not see, walk, hear, or speak, and Jesus <strong>healed</strong> them.</li>
                                <li><strong><b>32:14</b></strong> She had heard that Jesus had <strong>healed</strong> many sick people and thought, "I'm sure that if I can just touch Jesus' clothes, then I will be <strong>healed</strong>, too!"</li>
                                <li><strong><b>44:03</b></strong> Immediately, God <strong>healed</strong> the lame man, and he began to walk and jump around, and to praise God.</li>
                                <li><strong><b>44:08</b></strong> Peter answered them, "This man stands before you <strong>healed</strong> by the power of Jesus the Messiah."</li>
                                <li><strong><b>49:02</b></strong> ] Jesus did many miracles that prove he is God. He walked on water, calmed storms, <strong>healed</strong> many sick people, drove out demons, raised the dead to life, and turned five loaves of bread and two small fish into enough food for over 5,000 people.</li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H724, H1369, H1455, H2280, H2421, H2896, H3444, H3545, H4832, H4974, H7495, H7499, H7500, H7725, H7965, H8549, H8585, H8644, H622, G1295, G1743, G2322, G2323, G2386, G2390, G2392, G2511, G3647, G4982, G4991, G5198, G5199</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Hour </span> (verses: 18) </div>
                        <div class="word_def">
                            <h1>hour, hours</h1>
                            <h2>Definition:</h2>
                            <p>The term "hour" is often used in the Bible to tell what time of day a certain event took place. It is also used figuratively to mean "time" or "moment."</p>
                            <ul>
                                <li>The Jews counted daylight hours starting at sunrise (around 6 a.m.). For example, "the ninth hour" meant "around three in the afternoon."</li>
                                <li>Nighttime hours were counted starting at sunset (around 6 p.m.). For example, "the third hour of the night" meant "around nine in the evening" in our present-day system..</li>
                                <li>Since references to time in the Bible will not correspond exactly to the present-day time system, phrases such as "around nine" or "about six o'clock" could be used. </li>
                                <li>Some translations might add phrases like "in the evening" or "in the morning" or "in the afternoon" to make it clear what time of day is being talked about.</li>
                                <li>The phrase, "in that hour" could be translated as, "at that time" or "in that moment."</li>
                                <li>Referring to Jesus, the expression "his hour had come" could be translated as, "the time had come for him to" or "the appointed time for him had come."</li>
                            </ul>
                            <h2>Bible References: ##</h2>
                            <ul>
                                <li><b>Acts 02:14-15</b></li>
                                <li><b>John 04:51-52</b></li>
                                <li><b>Luke 23:44-45</b></li>
                                <li><b>Matthew 20:3-4</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H8160, G5610</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">House </span> (verses: 25) </div>
                        <div class="word_def">
                            <h1>house, houses, housetop, housetops, storehouse, storehouses, housekeepers</h1>
                            <h2>Definition:</h2>
                            <p>The term "house" is often used figuratively in the Bible. </p>
                            <ul>
                                <li>Sometimes it means "household," referring to the people who live together in one house.</li>
                                <li>Often "house" refers to a person's descendants or other relatives. For example, the phrase "house of David" refers to all the descendants of King David.</li>
                                <li>The terms "house of God" and "house of Yahweh" refer to the tabernacle or temple. These expressions can also refer generally to where God is or dwells.</li>
                                <li>In Hebrews 3, "God's house" is used as a metaphor to refer to God's people or, more generally, to everything pertaining to God.</li>
                                <li>The phrase "house of Israel" can refer generally to the entire nation of Israel or more specifically to the tribes of the northern kingdom of Israel.</li>
                            </ul>
                            <h2>Translation Suggestions</h2>
                            <ul>
                                <li>Depending on the context, "house" could be translated as "household" or "people" or "family" or "descendants" or "temple" or "dwelling place."</li>
                                <li>The phrase "house of David" could be translated as "clan of David" or "family of David" or "descendants of David." Related expressions could be translated in a similar way.</li>
                                <li>Different ways to translate "house of Israel" could include "people of Israel" or "Israel's descendants" or "Israelites."</li>
                                <li>The phrase "house of Yahweh" could be translated as "Yahweh's temple" or "place where Yahweh is worshiped" or "place where Yahweh meets with his people" or "where Yahweh dwells."</li>
                                <li>"House of God" could be translated in a similar way.</li>
                            </ul>
                            <p>(See also: <b>David</b>, <b>descendant</b>, <b>house of God</b>, <b>household</b>, <b>kingdom of Israel</b>, <b>tabernacle</b>, <b>temple</b>, <b>Yahweh</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>Acts 07:41-42</b></li>
                                <li><b>Acts 07:47-50</b></li>
                                <li><b>Genesis 39:3-4</b></li>
                                <li><b>Genesis 41:39-41</b></li>
                                <li><b>Luke 08:38-39</b></li>
                                <li><b>Matthew 10:5-7</b></li>
                                <li><b>Matthew 15:24-26</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H1004, H1005, G3609, G3613, G3614, G3624</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Jesus </span> (verses: 1, 4, 7-9, 11, 17-20, 22, 25-26) </div>
                        <div class="word_def">
                            <h1>Jesus, Jesus Christ, Christ Jesus</h1>
                            <h2>Facts:</h2>
                            <p>Jesus is God's Son. The name "Jesus" means "Yahweh saves." The term "Christ" is a title that means "anointed one" and is another word for Messiah.</p>
                            <ul>
                                <li>The two names are often combined as "Jesus Christ" or "Christ Jesus." These names emphasize that God's Son is the Messiah, who came to save people from being punished eternally for their sins.</li>
                                <li>In a miraculous way, the Holy Spirit caused the eternal Son of God to be born as a human being. His mother was told by an angel to call him "Jesus" because he was destined to save people from their sins.</li>
                                <li>Jesus did many miracles that revealed that he is God and that he is the Christ, or the Messiah.</li>
                            </ul>
                            <h2>Translation Suggestions:</h2>
                            <ul>
                                <li>In many languages "Jesus" and "Christ" are spelled in a way that keeps the sounds or spelling as close to the original as possible. For example, "Jesucristo," "Jezus Christus," "Yesus Kristus", and "Hesukristo" are some of the ways that these names are translated into different languages.</li>
                                <li>For the term "Christ," some translators may prefer to use only some form of the term "Messiah" throughout.</li>
                                <li>Also consider how these names are spelled in a nearby local or national language.</li>
                            </ul>
                            <p>(Translation suggestions: <b>How to Translate Names</b>)</p>
                            <p>(See also: <b>Christ</b>, <b>God</b>, <b>God the Father</b>, <b>high priest</b>, <b>kingdom of God</b>, <b>Mary</b>, <b>Savior</b>, <b>Son of God</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>1 Corinthians 06:9-11</b></li>
                                <li><b>1 John 02:1-3</b></li>
                                <li><b>1 John 04:15-16</b></li>
                                <li><b>1 Timothy 01:1-2</b></li>
                                <li><b>2 Peter 01:1-2</b></li>
                                <li><b>2 Thessalonians 02:13-15</b></li>
                                <li><b>2 Timothy 01:8-11</b></li>
                                <li><b>Acts 02:22-24</b></li>
                                <li><b>Acts 05:29-32</b></li>
                                <li><b>Acts 10:36-38</b></li>
                                <li><b>Hebrews 09:13-15</b></li>
                                <li><b>Hebrews 10:19-22</b></li>
                                <li><b>Luke 24:19-20</b></li>
                                <li><b>Matthew 01:20-21</b></li>
                                <li><b>Matthew 04:1-4</b></li>
                                <li><b>Philippians 02:5-8</b></li>
                                <li><b>Philippians 02:9-11</b></li>
                                <li><b>Philippians 04:21-23</b></li>
                                <li><b>Revelation 01:4-6</b></li>
                            </ul>
                            <h2>Examples from the Bible stories:</h2>
                            <ul>
                                <li><strong><b>22:04</b></strong> The angel said, "You will become pregnant and give birth to a son. You are to name him <strong>Jesus</strong> and he will be the Messiah."</li>
                                <li><strong><b>23:02</b></strong> "Name him <strong>Jesus</strong> (which means, 'Yahweh saves'), because he will save the people from their sins."</li>
                                <li><strong><b>24:07</b></strong> So John baptized him (Jesus), even though <strong>Jesus</strong> had never sinned.</li>
                                <li><strong><b>24:09</b></strong> There is only one God. But John heard God the Father speak, and saw <strong>Jesus</strong> the Son and the Holy Spirit when he baptized <strong>Jesus</strong>.</li>
                                <li><strong><b>25:08</b></strong> <strong>Jesus</strong> did not give in to Satan's temptations, so Satan left him.</li>
                                <li><strong><b>26:08</b></strong> Then <strong>Jesus</strong> went throughout the region of Galilee, and large crowds came to him. They brought many people who were sick or handicapped, including those who could not see, walk, hear, or speak, and <strong>Jesus</strong> healed them.</li>
                                <li><strong><b>31:03</b></strong> Then <strong>Jesus</strong> finished praying and went to the disciples. He walked on top of the water across the lake toward their boat!</li>
                                <li><strong><b>38:02</b></strong> He (Judas) knew that the Jewish leaders denied that <strong>Jesus</strong> was the Messiah and that they were plotting to kill him.</li>
                                <li><strong><b>40:08</b></strong> Through his death, <strong>Jesus</strong> opened a way for people to come to God.</li>
                                <li><strong><b>42:11</b></strong> Then <strong>Jesus</strong> was taken up to heaven, and a cloud hid him from their sight. <strong>Jesus</strong> sat down at the right hand of God to rule over all things.</li>
                                <li><strong><b>50:17</b></strong> <strong>Jesus</strong> and his people will live on the new earth, and he will reign forever over everything that exists. He will wipe away every tear and there will be no more suffering, sadness, crying, evil, pain, or death. <strong>Jesus</strong> will rule his kingdom with peace and justice, and he will be with his people forever.</li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: G2424, G5547</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Johnthebaptist </span> (verses: 13) </div>
                        <div class="word_def">
                            <h1>John (the Baptist)</h1>
                            <h2>Facts:</h2>
                            <p>John was the son of Zechariah and Elizabeth. Since "John" was a common name, he is often called "John the Baptist" to distinguish him from the other people named John, such as the Apostle John.</p>
                            <ul>
                                <li>John was the prophet whom God sent to prepare people to believe in and follow the Messiah. </li>
                                <li>John told people to confess their sins, turn to God, and stop sinning, so that they would be ready to receive the Messiah. </li>
                                <li>John baptized many people in water as a sign that they were sorry for their sins and were turning away from them. </li>
                                <li>John was called "John the Baptist" because he baptized many people. </li>
                            </ul>
                            <p>(Translation suggestions: <b>How to Translate Names</b>)</p>
                            <p>(See also: <b>baptize</b>, <b>Zechariah (NT)</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>John 03:22-24</b></li>
                                <li><b>Luke 01:11-13</b></li>
                                <li><b>Luke 01:62-63</b></li>
                                <li><b>Luke 03:7</b></li>
                                <li><b>Luke 03:15-16</b></li>
                                <li><b>Luke 07:27-28</b></li>
                                <li><b>Matthew 03:13-15</b></li>
                                <li><b>Matthew 11:13-15</b></li>
                            </ul>
                            <h2>Examples from the Bible stories:</h2>
                            <ul>
                                <li><strong><b>22:02</b></strong> The angel said to Zechariah, "Your wife will have a son. You will name him <strong>John</strong>. He will be filled with the Holy Spirit, and will prepare the people for Messiah!"</li>
                                <li><strong><b>22:07</b></strong> After Elizabeth gave birth to her baby boy, Zechariah and Elizabeth named the baby <strong>John</strong>, as the angel had commanded. </li>
                                <li><strong><b>24:01</b></strong> <strong>John</strong>, the son of Zechariah and Elizabeth, grew up and became a prophet. He lived in the wilderness, ate wild honey and locusts, and wore clothes made from camel hair.</li>
                                <li><strong><b>24:02</b></strong> Many people came out to the wilderness to listen to <strong>John</strong>. He preached to them, saying, "Repent, for the kingdom of God is near!"</li>
                                <li><strong><b>24:06</b></strong> The next day, Jesus came to be baptized by <strong>John</strong>. When <strong>John</strong> saw him, he said, "Look! There is the Lamb of God who will take away the sin of the world."</li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: G910 G2491</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Kind </span> (verses: 21) </div>
                        <div class="word_def">
                            <h1>kind, kinds, kindness, kindnesses</h1>
                            <h2>Definition:</h2>
                            <p>The terms "kind" and "kinds" refer to groups or classifications of things that are connected by shared characteristics.</p>
                            <ul>
                                <li>In the Bible, this term is specifically used to refer to the distinctive kinds of plants and animals that God made when he created the world.</li>
                                <li>Often there are many different variations or species within each "kind." For example, horses, zebras, and donkeys are all members of the same "kind," but they are different species.</li>
                                <li>The main thing that distinguishes each "kind" as a separate group is that members of that group can reproduce more of their same "kind." Members of different kinds cannot do that with each other.</li>
                            </ul>
                            <h2>Translation Suggestions</h2>
                            <ul>
                                <li>Ways to translate this term could include "type" or "class" or "group" or "animal (plant) group" or "category."</li>
                            </ul>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>Genesis 01:20-21</b></li>
                                <li><b>Genesis 01:24-25</b></li>
                                <li><b>Mark 09:28-29</b></li>
                                <li><b>Matthew 13:47-48</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H2178, H3978, H4327, G1085, G5449</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Light </span> (verses: 2) </div>
                        <div class="word_def">
                            <h1>light, lights, lighting, lightning, daylight, sunlight, twilight, enlighten, enlightened</h1>
                            <h2>Definition:</h2>
                            <p>There are several figurative uses of the term "light" in the Bible. It is often used as a metaphor for righteousness, holiness, and truth. (See: <b>Metaphor</b>)</p>
                            <ul>
                                <li>Jesus said, "I am the light of the world" to express that he brings God's true message to the world and rescues people from the darkness of their sin.</li>
                                <li>Christians are commanded to "walk in the light," which means they should be living the way God wants them to and avoiding evil.</li>
                                <li>The apostle John stated that "God is light" and in him there is no darkness at all.</li>
                                <li>Light and darkness are complete opposites. Darkness is the absence of all light. </li>
                                <li>Jesus said that he was "the light of the world" and that his followers should shine like lights in the world by living in a way that clearly shows how great God is.</li>
                                <li>"Walking in the light" represents living in a way that pleases God, doing what is good and right. Walking in darkness represents living in rebellion against God, doing evil things.</li>
                            </ul>
                            <h2>Translation Suggestions:</h2>
                            <ul>
                                <li>When translating, it is important to keep the literal terms "light" and "darkness" even when they are used figuratively.</li>
                                <li>It may be necessary to explain the comparison in the text. For example, "walk as children of light" could be translated as, "live openly righteous lives, like someone who walks in bright sunlight."</li>
                                <li>Make sure that the translation of "light" does not refer to an object that gives light, such as a lamp. The translation of this term should refer to the light itself.</li>
                            </ul>
                            <p>(See also: <b>darkness</b>, <b>holy</b>, <b>righteous</b>, <b>true</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>1 John 01:5-7</b></li>
                                <li><b>1 John 02:7-8</b></li>
                                <li><b>2 Corinthians 04:5-6</b></li>
                                <li><b>Acts 26:15-18</b></li>
                                <li><b>Isaiah 02:5-6</b></li>
                                <li><b>John 01:4-5</b></li>
                                <li><b>Matthew 05:15-16</b></li>
                                <li><b>Matthew 06:22-24</b></li>
                                <li><b>Nehemiah 09:12-13</b></li>
                                <li><b>Revelation 18:23-24</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H216, H217, H3313, H3974, H4237, H5051, H5094, H5105, H5216, H6348, H7052, H7837, G681, G796, G1645, G2985, G3088, G5338, G5457, G5458, G5460, G5462</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Like </span> (verses: 2) </div>
                        <div class="word_def">
                            <h1>like, likeminded, liken, likeness, likenesses, likewise, alike, unlike</h1>
                            <h2>Definition:</h2>
                            <p>The terms "like" and "likeness" refer to something being the same as, or similar to, something else.</p>
                            <ul>
                                <li>The word "like" is also often used in a figurative expressions called a "simile" in which something is compared to something else, usually highlighting a shared characteristic. For example, "his clothes shined like the sun" and "the voice boomed like thunder." (See: <b>Simile</b>)</li>
                                <li>To "be like" or "sound like" or "look like" something or someone means to have qualities that are similar to the thing or person being compared to.</li>
                                <li>People were created in God's "likeness," that is, in his "image." It means that they have qualities or characteristics that are "like" or "similar to" qualities that God has, such as the ability to think, feel, and communicate.</li>
                                <li>To have "the likeness of" something or someone means to have characteristics that look like that thing or person.</li>
                            </ul>
                            <h2>Translation Suggestions</h2>
                            <ul>
                                <li>In some contexts, the expression "the likeness of" could be translated as "what looked like" or "what appeared to be."</li>
                                <li>The expression "in the likeness of his death" could be translated as "sharing in the experience of his death" or "as if experiencing his death with him."</li>
                                <li>The expression "in the likeness of sinful flesh" could be translated as "being like a sinful human being" or to "be a human being." Make sure the translation of this expression does not sound like Jesus was sinful.</li>
                                <li>"In his own likeness" could also be translated as to "be like him" or "having many of the same qualities that he has."</li>
                                <li>The expression "the likeness of an image of perishable man, of birds, of four-footed beasts and of creeping things" could be translated as "idols made to look like perishable humans, or animals, such as birds, beasts, and small, crawling things."</li>
                            </ul>
                            <p>(See also: <b>beast</b>, <b>flesh</b>, <b>image of God</b>, <b>image</b>, <b>perish</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>Ezekiel 01:4-6</b></li>
                                <li><b>Mark 08:24-26</b></li>
                                <li><b>Matthew 17:1-2</b></li>
                                <li><b>Matthew 18:1-3</b></li>
                                <li><b>Psalms 073:4-5</b></li>
                                <li><b>Revelation 01:12-13</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H1823, H8403, H8544, G1503, G1504, G2509, G2531, G2596, G3664, G3665, G3666, G3667, G3668, G3669, G3697, G4833, G5108, G5613, G5615, G5616, G5618, G5619</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Mediterranean </span> (verses: 27) </div>
                        <div class="word_def">
                            <h1>the sea, the Great Sea, the western sea, Mediterranean Sea</h1>
                            <h2>Facts:</h2>
                            <p>In the Bible, the "Great Sea" or "western sea" refers to what is now called the "Mediterranean Sea," which was the largest body of water known to the people of Bible times.</p>
                            <ul>
                                <li>The Mediterranean Sea is bordered by : Israel (east), Europe (north and west), and Africa (south).</li>
                                <li>This sea was very important in ancient times for trade and travel since it bordered so many countries. Cities and people groups located on the coast of this sea were very prosperous because of how easy it was to access goods from other countries by boat.</li>
                                <li>Since the Great Sea was located to the west of Israel, it was sometimes referred to as the "western sea."</li>
                            </ul>
                            <p>(Translation suggestions: <b>Translate Names</b>)</p>
                            <p>(See also: <b>Israel</b>, <b>people group</b>, <b>prosper</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>Ezekiel 47:15-17</b></li>
                                <li><b>Ezekiel 47:18-20</b></li>
                                <li><b>Joshua 15:3-4</b></li>
                                <li><b>Numbers 13:27-29</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H314, H1419, H3220</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Mercy </span> (verses: 15) </div>
                        <div class="word_def">
                            <h1>mercy, merciful</h1>
                            <h2>Definition:</h2>
                            <p>The terms "mercy" and "merciful" refer to helping people who are in need, especially when they are in a lowly or humbled condition.</p>
                            <ul>
                                <li>The term "mercy" can also include the meaning of not punishing people for something they have done wrong.</li>
                                <li>A powerful person such as a king is described as "merciful" when he treats people kindly instead of harming them.</li>
                                <li>Being merciful also means to forgive someone who has done something wrong against us.</li>
                                <li>We show mercy when we help people who are in great need.</li>
                                <li>God is merciful to us, and he wants us to be merciful to others.</li>
                            </ul>
                            <h2>Translation Suggestions:</h2>
                            <ul>
                                <li>Depending on the context, "mercy" could be translated as "kindness" or "compassion" or "pity."</li>
                                <li>The term "merciful" could be translated as "showing pity" or "being kind to" or "forgiving."</li>
                                <li>To "show mercy to" or "have mercy on" could be translated as "treat kindly" or "be compassionate toward."</li>
                            </ul>
                            <p>(See also: <b>compassion</b>, <b>forgive</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>1 Peter 01:3-5</b></li>
                                <li><b>1 Timothy 01:12-14</b></li>
                                <li><b>Daniel 09:17-19</b></li>
                                <li><b>Exodus 34:5-7</b></li>
                                <li><b>Genesis 19:16-17</b></li>
                                <li><b>Hebrews 10:28-29</b></li>
                                <li><b>James 02:12-13</b></li>
                                <li><b>Luke 06:35-36</b></li>
                                <li><b>Matthew 09:27-28</b></li>
                                <li><b>Philippians 02:25-27</b></li>
                                <li><b>Psalms 041:4-6</b></li>
                                <li><b>Romans 12:1-2</b></li>
                            </ul>
                            <h2>Examples from the Bible stories:</h2>
                            <ul>
                                <li><strong><b>19:16</b></strong> They (the prophets) all told the people to stop worshiping idols and to start showing justice and <strong>mercy</strong> to others.</li>
                                <li><strong><b>19:17</b></strong> He (Jeremiah) sank down into the mud that was in the bottom of the well, but then the king had <strong>mercy</strong> on him and ordered his servants to pull Jeremiah out of the well before he died.</li>
                                <li><strong><b>20:12</b></strong> The Persian Empire was strong but <strong>merciful</strong> to the people it conquered.</li>
                                <li><strong><b>27:11</b></strong> Then Jesus asked the law expert, "What do you think? Which one of the three men was a neighbor to the man who was robbed and beaten?" He replied, "The one who was <strong>merciful</strong> to him."</li>
                                <li><strong><b>32:11</b></strong> But Jesus said to him, "No, I want you to go home and tell your friends and family about everything that God has done for you and how he has had <strong>mercy</strong> on you."</li>
                                <li><strong><b>34:09</b></strong> "But the tax collector stood far away from the religious ruler, did not even look up to heaven. Instead, he pounded on his chest and prayed, 'God, please be <strong>merciful</strong> to me because I am a sinner.'"</li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H2551, H2603, H2604, H2616, H2617, H2623, H3722, H3727, H4627, H4819, H5503, H5504, H5505, H5506, H6014, H7349, H7355, H7356, H7359, G1653, G1655, G1656, G2433, G2436, G3628, G3629, G3741, G4698</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Moses </span> (verses: 3-4) </div>
                        <div class="word_def">
                            <h1>law, law of Moses, God's law, law of Yahweh</h1>
                            <h2>Definition:</h2>
                            <p>All these terms refer to the commandments and instructions that God gave Moses for the Israelites to obey. The terms "law" and "God's law" are also used more generally to refer to everything God wants his people to obey.</p>
                            <ul>
                                <li>
                                    <p>Depending on the context, the "law" can refer to:</p>
                                    <ul>
                                        <li>the Ten Commandments that God wrote on stone tablets for the Israelites</li>
                                        <li>all the laws given to Moses</li>
                                        <li>the first five books of the Old Testament</li>
                                        <li>the entire Old Testament (also referred to as "scriptures" in the New Testament).</li>
                                        <li>all of God's instructions and will</li>
                                    </ul>
                                </li>
                                <li>
                                    <p>The phrase "the law and the prophets" is used in the New Testament to refer to the Hebrew scriptures (or "Old Testament")</p>
                                </li>
                            </ul>
                            <h2>Translation Suggestions:</h2>
                            <ul>
                                <li>These terms could be translated using the plural, "laws," since they refer to many instructions.</li>
                                <li>The "law of Moses" could be translated as "the laws that God told Moses to give to the Israelites."</li>
                                <li>Depending on the context, "the law of Moses" could also be translated as "the law that God told to Moses" or "God's laws that Moses wrote down" or "the laws that God told Moses to give to the Israelites."</li>
                                <li>Ways to translate "the law" or "law of God" or "God's laws" could include "laws from God" or "God's commands" or "laws that God gave" or "everything that God commands" or "all of God's instructions."</li>
                                <li>The phrase "law of Yahweh" could also be translated as "Yahweh's laws" or "laws that Yahweh said to obey" or "laws from Yahweh" or "things Yahweh commanded."</li>
                            </ul>
                            <p>(See also: <b>instruct</b>, <b>Moses</b>, <b>Ten Commandments</b>, <b>lawful</b>, <b>Yahweh</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>Acts 15:5-6</b></li>
                                <li><b>Daniel 09:12-14</b></li>
                                <li><b>Exodus 28:42-43</b></li>
                                <li><b>Ezra 07:25-26</b></li>
                                <li><b>Galatians 02:15-16</b></li>
                                <li><b>Luke 24:44</b></li>
                                <li><b>Matthew 05:17-18</b></li>
                                <li><b>Nehemiah 10:28-29</b></li>
                                <li><b>Romans 03:19-20</b></li>
                            </ul>
                            <h2>Examples from the Bible stories:</h2>
                            <ul>
                                <li><strong><b>13:07</b></strong> God also gave many other <strong>laws</strong> and rules to follow. If the people obeyed these <strong>laws</strong>, God promised that he would bless and protect them. If they disobeyed them, God would punish them.\</li>
                                <li><strong><b>13:09</b></strong> Anyone who disobeyed <strong>God's law</strong> could bring an animal to the altar in front of the Tent of Meeting as a sacrifice to God.\</li>
                                <li><strong><b>15:13</b></strong> Then Joshua reminded the people of their obligation to obey the covenant that God had made with the Israelites at Sinai. The people promised to remain faithful to God and follow <strong>his laws</strong>.\</li>
                                <li><strong><b>16:01</b></strong> After Joshua died, the Israelites disobeyed God and did not drive out the rest of the Canaanites or obey <strong>God's laws</strong>.\</li>
                                <li><strong><b>21:05</b></strong> In the New Covenant, God would write <strong>his law</strong> on the people's hearts, the people would know God personally, they would be his people, and God would forgive their sins.\</li>
                                <li><strong><b>27:01</b></strong> Jesus answered, "What is written in <strong>God's law</strong>?"\</li>
                                <li><strong><b>28:01</b></strong> Jesus said to him, "Why do you call me 'good?' There is only one who is good, and that is God. But if you want to have eternal life, obey <strong>God's laws</strong>."\</li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H430, H1881, H1882, H2706, H2710, H3068, H4687, H4872, H4941, H8451, G2316, G3551, G3565</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Peter </span> (verses: 1, 4, 24-26) </div>
                        <div class="word_def">
                            <h1>Peter, Simon Peter, Cephas</h1>
                            <h2>Facts:</h2>
                            <p>Peter was one of Jesus' twelve apostles. He was an important leader of the early Church.</p>
                            <ul>
                                <li>Before Jesus called him to be his disciple, Peter's name was Simon.</li>
                                <li>Later, Jesus also named him "Cephas," which means "stone" or "rock" in the Aramaic language. The name Peter also means "stone" or "rock" in the Greek language.</li>
                                <li>God worked through Peter to heal people and to preach the good news about Jesus.</li>
                                <li>Two books in the New Testament are letters that Peter wrote to encourage and teach fellow believers.</li>
                            </ul>
                            <p>(Translation suggestions: <b>How to Translate Names</b>)</p>
                            <p>(See also: <b>disciple</b>, <b>apostle</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>Acts 08:25</b></li>
                                <li><b>Galatians 02:6-8</b></li>
                                <li><b>Galatians 02:11-12</b></li>
                                <li><b>Luke 22:56-58</b></li>
                                <li><b>Mark 03:13-16</b></li>
                                <li><b>Matthew 04:18-20</b></li>
                                <li><b>Matthew 08:14-15</b></li>
                                <li><b>Matthew 14:28-30</b></li>
                                <li><b>Matthew 26:33-35</b></li>
                            </ul>
                            <h2>Examples from the Bible stories:</h2>
                            <ul>
                                <li><strong><b>28:09</b></strong> <strong>Peter</strong> said to Jesus, "We have left everything and followed you. What will be our reward?"</li>
                                <li><strong><b>29:01</b></strong> One day <strong>Peter</strong> asked Jesus, "Master, how many times should I forgive my brother when he sins against me? As many as seven times?"</li>
                                <li><strong><b>31:05</b></strong> Then <strong>Peter</strong> said to Jesus, "Master, if it is you, command me to come to you on the water." Jesus told <strong>Peter</strong>, "Come!"</li>
                                <li><strong><b>36:01</b></strong> One day, Jesus took three of his disciples, <strong>Peter</strong>, James, and John with him.</li>
                                <li><strong><b>38:09</b></strong> <strong>Peter</strong> replied, "Even if all the others abandon you, I will not!" Then Jesus said to <strong>Peter</strong>, "Satan wants to have all of you, but I have prayed for you, <strong>Peter</strong>, that your faith will not fail. Even so, tonight, before the rooster crows, you will deny that you even know me three times."</li>
                                <li><strong><b>38:15</b></strong> As the soldiers arrested Jesus, <strong>Peter</strong> pulled out his sword and cut off the ear of the servant of the high priest.</li>
                                <li><strong><b>43:11</b></strong> <strong>Peter</strong> answered them, "Every one of you should repent and be baptized in the name of Jesus Christ so that God will forgive your sins."</li>
                                <li><strong><b>44:08</b></strong> <strong>Peter</strong> answered them, "This man stands before you healed by the power of Jesus the Messiah."</li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: G2786, G4074, G4613</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Pray </span> (verses: 21) </div>
                        <div class="word_def">
                            <h1>pray, prayer, prayers, prayed</h1>
                            <h2>Definition:</h2>
                            <p>The terms "pray" and "prayer" refer to talking with God. These terms are used to refer to people trying to talk to a false god.</p>
                            <ul>
                                <li>People can pray silently, talking to God with their thoughts, or they can pray aloud, speaking to God with their voice. Sometimes prayers are written down, such as when David wrote his prayers in the Book of Psalms.</li>
                                <li>Prayer can include asking God for mercy, for help with a problem, and for wisdom in making decisions.</li>
                                <li>Often people ask God to heal people who are sick or who need his help in other ways.</li>
                                <li>People also thank and praise God when they are praying to him.</li>
                                <li>Praying includes confessing our sins to God and asking him to forgive us.</li>
                                <li>Talking to God is sometimes called "communing" with him as our spirit communicates with his spirit, sharing our emotions and enjoying his presence.</li>
                                <li>This term could be translated as "talking to God" or "communicating with God." The translation of this term should be able to include praying that is silent.</li>
                            </ul>
                            <p>(See also: <b>false god</b>, <b>forgive</b>, <b>praise</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>1 Thessalonians 03:8-10</b></li>
                                <li><b>Acts 08:24</b></li>
                                <li><b>Acts 14:23-26</b></li>
                                <li><b>Colossians 04:2-4</b></li>
                                <li><b>John 17:9-11</b></li>
                                <li><b>Luke 11:1</b></li>
                                <li><b>Matthew 05:43-45</b></li>
                                <li><b>Matthew 14:22-24</b></li>
                            </ul>
                            <h2>Examples from the Bible stories:</h2>
                            <ul>
                                <li><strong><b>06:05</b></strong> Isaac <strong>prayed</strong> for Rebekah, and God allowed her to get pregnant with twins.</li>
                                <li><strong><b>13:12</b></strong> But Moses <strong>prayed</strong> for them, and God listened to his <strong>prayer</strong> and did not destroy them.</li>
                                <li><strong><b>19:08</b></strong> Then the prophets of Baal <strong>prayed</strong> to Baal, "Hear us, O Baal!"</li>
                                <li><strong><b>21:07</b></strong> Priests also <strong>prayed</strong> to God for the people.</li>
                                <li><strong><b>38:11</b></strong> Jesus told his disciples to <strong>pray</strong> that they would not enter into temptation.</li>
                                <li><strong><b>43:13</b></strong> The disciples continually listened to the teaching of the apostles, spent time together, ate together, and <strong>prayed</strong> with each other.</li>
                                <li><strong><b>49:18</b></strong> God tells you to <strong>pray</strong>, to study his word, to worship him with other Christians, and to tell others what he has done for you.</li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H559, H577, H1156, H2470, H3863, H3908, H4994, H6279, H6293, H6419, H6739, H7592, H7878, H7879, H7881, H8034, H8605, G154, G1162, G1189, G1783, G2065, G2171, G2172, G3870, G4335, G4336</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Raise </span> (verses: 9, 23) </div>
                        <div class="word_def">
                            <h1>raise, raises, raised, rise, risen, arise, arose</h1>
                            <h2>Definition:</h2>
                            <p><strong>raise, raise up</strong></p>
                            <p>In general, the word "raise" means to "lift up" or "make higher."</p>
                            <ul>
                                <li>The figurative phrase "raise up" means to cause something to come into being or to appear. It can also mean to appoint someone to do something.</li>
                                <li>Sometimes "raise up" means to "restore" or "rebuild."</li>
                                <li>"Raise" has a specialized meaning in the phrase "raise from the dead." It means to cause a dead person to become alive again.</li>
                                <li>Sometimes "raise up" means to "exalt" someone or something.</li>
                            </ul>
                            <p><strong>rise, arise</strong></p>
                            <p>To "rise" or "arise" means to "go up" or "get up." The terms "risen," "rose," and "arose" express past action.</p>
                            <ul>
                                <li>When a person gets up to go somewhere, this is sometimes expressed as "he arose and went" or "he rose up and went."</li>
                                <li>If something "arises" it means it "happens" or "begins to happen."</li>
                                <li>Jesus predicted that he would "rise from the dead." Three days after Jesus died, the angel said, "He has risen!"</li>
                            </ul>
                            <h2>Translation Suggestions:</h2>
                            <ul>
                                <li>The term "raise" or "raise up" could be translated as "lift up" or "make higher."</li>
                                <li>To "raise up" could also be translated as to "cause to appear" or to "appoint" or to "bring into existence."</li>
                                <li>To "raise up the strength of your enemies" could be translated as, "cause your enemies to be very strong."</li>
                                <li>The phrase "raise someone from the dead" could be translated as "cause someone to return from death to life" or "cause someone to come back to life."</li>
                                <li>Depending on the context, "raise up" could also be translated as "provide" or to "appoint" or to "cause to have" or "build up" or "rebuild" or "repair."</li>
                                <li>The phrase "arose and went" could be translated as "got up and went" or "went."</li>
                                <li>Depending on the context, the term "arose" could also be translated as "began" or "started up" or "got up" or "stood up."</li>
                            </ul>
                            <p>(See also: <b>resurrection</b>, <b>appoint</b>, <b>exalt</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>2 Chronicles 06:40-42</b></li>
                                <li><b>2 Samuel 07:12-14</b></li>
                                <li><b>Acts 10:39-41</b></li>
                                <li><b>Colossians 03:1-4</b></li>
                                <li><b>Deuteronomy 13:1-3</b></li>
                                <li><b>Jeremiah 06:1-3</b></li>
                                <li><b>Judges 02:18-19</b></li>
                                <li><b>Luke 07:21-23</b></li>
                                <li><b>Matthew 20:17-19</b></li>
                            </ul>
                            <h2>Examples from the Bible stories:</h2>
                            <ul>
                                <li><strong><b>21:14</b></strong> The prophets foretold that the Messiah would die and that God would also <strong>raise</strong> him from the dead.</li>
                                <li><strong><b>41:05</b></strong> "Jesus is not here. He has <strong>risen</strong> from the dead, just like he said he would!"</li>
                                <li><strong><b>43:07</b></strong> "Although Jesus died, God <strong>raised</strong> him from the dead. This fulfills the prophecy which says, 'You will not let your Holy One rot in the grave.' We are witnesses to the fact that God <strong>raised</strong> Jesus to life again."</li>
                                <li><strong><b>44:05</b></strong> " You killed the author of life, but God <strong>raised</strong> him from the dead. "</li>
                                <li><strong><b>44:08</b></strong> Peter answered them, "This man stands before you healed by the power of Jesus the Messiah. You crucified Jesus, but God <strong>raised</strong> him to life again!"</li>
                                <li><strong><b>48:04</b></strong> This meant that Satan would kill the Messiah, but God would <strong>raise</strong> him to life again, and then the Messiah will crush the power of Satan forever.</li>
                                <li><strong><b>49:02</b></strong> He (Jesus) walked on water, calmed storms, healed many sick people, drove out demons, <strong>raised</strong> the dead to life, and turned five loaves of bread and two small fish into enough food for over 5,000 people.</li>
                                <li><strong><b>49:12</b></strong> You must believe that Jesus is the Son of God, that he died on the cross instead of you, and that God <strong>raised</strong> him to life again.</li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H2210, H2224, H5549, H5782, H5927, H5975, H6209, H6965, H6966, H6974, H7613, H7721, G305, G386, G393, G450, G1096, G1326, G1453, G1525, G1817, G1825, G1892, G1999, G4891</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Rebuke </span> (verses: 18) </div>
                        <div class="word_def">
                            <h1>rebuke, rebukes, rebuked</h1>
                            <h2>Definition:</h2>
                            <p>To rebuke is to give someone a stern verbal correction, often in order to help that person turn away from sin. Such a correction is a rebuke.</p>
                            <ul>
                                <li>The New Testament commands Christians to rebuke other believers when they are clearly disobeying God.</li>
                                <li>The book of Proverbs instructs parents to rebuke their children when they are disobedient.</li>
                                <li>A rebuke is typically given to prevent those who committed a wrong from further involving themselves in sin.</li>
                                <li>This could be translated by "sternly correct" or "admonish."</li>
                                <li>The phrase "a rebuke" could be translated by "a stern correction" or "a strong criticism."</li>
                                <li>"Without rebuke" could be translated as "without admonishing" or "without criticism."</li>
                            </ul>
                            <p>(See also <b>admonish</b>, <b>disobey</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>Mark 01:23-26</b></li>
                                <li><b>Mark 16:14-16</b></li>
                                <li><b>Matthew 08:26-27</b></li>
                                <li><b>Matthew 17:17-18</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H1605, H1606, H2778, H2781, H3198, H4045, H4148, H8156, H8433, G298, G299, G1649, G1651, G1969, G2008, G3679</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Receive </span> (verses: 25) </div>
                        <div class="word_def">
                            <h1>receive, receives, received, receiving, receiver</h1>
                            <h2>Definition:</h2>
                            <p>The term "receive" generally means to get or accept something that is given, offered, or presented.</p>
                            <ul>
                                <li>To "receive" can also mean to suffer or experience something, as in "he received punishment for what he did."</li>
                                <li>There is also a special sense in which we can "receive" a person. For example, to "receive" guests or visitors means to welcome them and treat them with honor in order to build a relationship with them.</li>
                                <li>To "receive the gift of the Holy Spirit" means we are given the Holy Spirit and welcome him to work in and through our lives.</li>
                                <li>To "receive Jesus" means to accept God's offer of salvation through Jesus Christ.</li>
                                <li>When a blind person "receives his sight" means that God has healed him and enabled him to see.</li>
                            </ul>
                            <h2>Translation Suggestions:</h2>
                            <ul>
                                <li>Depending on the context, "receive" could be translated as "accept" or "welcome" or "experience" or "be given."</li>
                                <li>The expression "you will receive power" could be translated as "you will be given power" or "God will give you power" or "power will be given to you (by God)" or "God will cause the Holy Spirit to work powerfully in you."</li>
                                <li>The phrase "received his sight" could be translated as "was able to see" or "became able to see again" or "was healed by God so that he was able to see."</li>
                            </ul>
                            <p>(See also: <b>Holy Spirit</b>, <b>Jesus</b>, <b>lord</b>, <b>save</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>1 John 05:9-10</b></li>
                                <li><b>1 Thessalonians 01:6-7</b></li>
                                <li><b>1 Thessalonians 04:1-2</b></li>
                                <li><b>Acts 08:14-17</b></li>
                                <li><b>Jeremiah 32:33-35</b></li>
                                <li><b>Luke 09:5-6</b></li>
                                <li><b>Malachi 03:10-12</b></li>
                                <li><b>Psalms 049:14-15</b></li>
                            </ul>
                            <h2>Examples from the Bible stories:</h2>
                            <ul>
                                <li><strong><b>21:13</b></strong> The prophets also said that the Messiah would be perfect, having no sin. He would die to <strong>receive</strong> the punishment for other people's sin. His punishment would bring peace between God and people.</li>
                                <li><strong><b>45:05</b></strong> As Stephen was dying, he cried out, "Jesus, <strong>receive</strong> my spirit."</li>
                                <li><strong><b>49:06</b></strong> He (Jesus) taught that some people will receive him and be saved, but others will not.</li>
                                <li><strong><b>49:10</b></strong> When Jesus died on the cross, he <strong>received</strong> your punishment.</li>
                                <li><strong><b>49:13</b></strong> God will save everyone who believes in Jesus and <strong>receives</strong> him as their Master.</li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H1878, H2505, H3557, H3947, H6901, H6902, H8254, G308, G324, G353, G354, G568, G588, G618, G1183, G1209, G1523, G1653, G1926, G2210, G2865, G2983, G3028, G3335, G3336, G3549, G3858, G3880, G3970, G4327, G4355, G4356, G4687, G4732, G5264, G5274, G5562</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Restore </span> (verses: 11) </div>
                        <div class="word_def">
                            <h1>restore, restores, restored, restoration</h1>
                            <h2>Definition:</h2>
                            <p>The terms "restore" and "restoration" refer to causing something to return to its original and better condition.</p>
                            <ul>
                                <li>When a diseased body part is restored, this means it has been "healed."</li>
                                <li>A broken relationship that is restored has been "reconciled." God restores sinful people and brings them back to himself.</li>
                                <li>If people have been restored to their home country, they have been "brought back" or "returned" to that country.</li>
                            </ul>
                            <h2>Translation Suggestions:</h2>
                            <ul>
                                <li>Depending on the context, ways to translate "restore" could include "renew" or "repay" or "return" or "heal" or "bring back."</li>
                                <li>Other expressions for this term could be "make new" or "make like new again."</li>
                                <li>When property is "restored," it has been "repaired" or "replaced" or "given back" to its owner.</li>
                                <li>Depending on the context, "restoration" could be translated as "renewal" or "healing" or "reconciliation."</li>
                            </ul>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>2 Kings 05:8-10</b></li>
                                <li><b>Acts 03:21-23</b></li>
                                <li><b>Acts 15:15-18</b></li>
                                <li><b>Isaiah 49:5-6</b></li>
                                <li><b>Jeremiah 15:19-21</b></li>
                                <li><b>Lamentations 05:19-22</b></li>
                                <li><b>Leviticus 06:5-7</b></li>
                                <li><b>Luke 19:8-10</b></li>
                                <li><b>Matthew 12:13-14</b></li>
                                <li><b>Psalm 080:1-3</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H7725, H7999, H8421, G600, G2675</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Scribe </span> (verses: 10) </div>
                        <div class="word_def">
                            <h1>scribe, scribes</h1>
                            <h2>Definition:</h2>
                            <p>Scribes were officials who were responsible for writing or copying important government or religious documents by hand. Another name for a Jewish scribe was "expert in Jewish law."</p>
                            <ul>
                                <li>Scribes were responsible for copying and preserving the books of the Old Testament.</li>
                                <li>They also copied, preserved, and interpreted religious opinions and commentary on the law of God.</li>
                                <li>At times, scribes were important government officials.</li>
                                <li>Important biblical scribes include Baruch and Ezra.</li>
                                <li>In the New Testament, the term translated "scribes" was also translated as "teachers of the Law."</li>
                                <li>In the New Testament, scribes were usually part of the religious group called the "Pharisees," and the two groups were frequently mentioned together.</li>
                            </ul>
                            <p>(See also: <b>law</b>, <b>Pharisee</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>Acts 04:5-7</b></li>
                                <li><b>Luke 07:29-30</b></li>
                                <li><b>Luke 20:45-47</b></li>
                                <li><b>Mark 01:21-22</b></li>
                                <li><b>Mark 02:15-16</b></li>
                                <li><b>Matthew 05:19-20</b></li>
                                <li><b>Matthew 07:28-29</b></li>
                                <li><b>Matthew 12:38-40</b></li>
                                <li><b>Matthew 13:51-53</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H5608, H5613, H7083, G1122</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Seed </span> (verses: 20) </div>
                        <div class="word_def">
                            <h1>seed, semen</h1>
                            <h2>Definition:</h2>
                            <p>A seed is the part of a plant that gets planted in the ground to reproduce more of the same kind of plant. It also has several figurative meanings.</p>
                            <ul>
                                <li>The term "seed" is used figuratively and euphemistically to refer to the tiny cells inside a man that combine with cells of a woman to cause a baby to grow inside her. A collection of these is called semen.</li>
                                <li>Related to this, "seed" is also used to refer to a person's offspring or descendants.</li>
                                <li>This word often has a plural meaning, referring to more than one seed grain or more than one descendant.</li>
                                <li>In the parable of the farmer planting seeds, Jesus compared his seeds to the Word of God, which is planted in people's hearts in order to produce good spiritual fruit.</li>
                                <li>The apostle Paul also uses the term "seed" to refer to the Word of God.</li>
                            </ul>
                            <h2>Translation Suggestions:</h2>
                            <ul>
                                <li>For a literal seed, it is best to use the literal term for "seed" that is used in the target language for what a farmer plants in his field.</li>
                                <li>The literal term should also be used in contexts where it refers figuratively to God's Word.</li>
                                <li>For the figurative use that refers to people who are of the same family line, it may be more clear to use the word "descendant" or "descendants" instead of seed. Some languages may have a word that means "children and grandchildren."</li>
                                <li>For a man or woman's "seed," consider how the target expresses this in a way that will not offend or embarrass people. (See: <b>euphemism</b>)</li>
                            </ul>
                            <p>(See also: <b>descendant</b>, <b>offspring</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>1 Kings 18:30-32</b></li>
                                <li><b>Genesis 01:11-13</b></li>
                                <li><b>Jeremiah 02:20-22</b></li>
                                <li><b>Matthew 13:7-9</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H2232, H2233, H2234, H3610, H6507, G4615, G4687, G4690, G4701, G4703</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Sin </span> (verses: 27) </div>
                        <div class="word_def">
                            <h1>sin, sins, sinned, sinful, sinner, sinning</h1>
                            <h2>Definition:</h2>
                            <p>The term "sin" refers to actions, thoughts, and words that are against God's will and laws. Sin can also refer to not doing something that God wants us to do.</p>
                            <ul>
                                <li>Sin includes anything we do that does not obey or please God, even things that other people don't know about.</li>
                                <li>Thoughts and actions that disobey God's will are called "sinful."</li>
                                <li>Because Adam sinned, all human beings are born with a "sinful nature," a nature that that controls them and causes them to sin.</li>
                                <li>A "sinner" is someone who sins, so every human being is a sinner.</li>
                                <li>Sometimes the word "sinners" was used by religious people like the Pharisees to refer to people who didn't keep the law as well as the Pharisees thought they should.</li>
                                <li>The term "sinner" was also used for people who were considered to be worse sinners than other people. For example, this label was given to tax collectors and prostitutes.</li>
                            </ul>
                            <h2>Translation Suggestions:</h2>
                            <ul>
                                <li>The term "sin" could be translated with a word or phrase that means "disobedience to God" or "going against God's will" or "evil behavior and thoughts" or "wrongdoing."</li>
                                <li>To "sin" could also be translated as to "disobey God" or to "do wrong."</li>
                                <li>Depending on the context "sinful" could be translated as "full of wrongdoing" or "wicked" or "immoral" or "evil" or "rebelling against God."</li>
                                <li>Depending on the context the term "sinner" could be translated with a word or phrase that means, "person who sins" or "person who does wrong things" or "person who disobeys God" or "person who disobeys the law."</li>
                                <li>The term "sinners" could be translated by a word or phrase that means "very sinful people" or "people considered to be very sinful" or "immoral people."</li>
                                <li>Ways to translate "tax collectors and sinners" could include "people who collect money for the government, and other very sinful people" or "very sinful people, including (even) tax collectors."</li>
                                <li>In expressions like "slaves to sin" or "ruled by sin," the term "sin" could be translated as "disobedience" or "evil desires and actions."</li>
                                <li>Make sure the translation of this term can include sinful behavior and thoughts, even those that other people don't see or know about.</li>
                                <li>The term "sin" should be general, and different from the terms for "wickedness" and "evil."</li>
                            </ul>
                            <p>(See also: <b>disobey</b>, <b>evil</b>, <b>flesh</b>, <b>tax collector</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>1 Chronicles 09:1-3</b></li>
                                <li><b>1 John 01:8-10</b></li>
                                <li><b>1 John 02:1-3</b></li>
                                <li><b>2 Samuel 07:12-14</b></li>
                                <li><b>Acts 03:19-20</b></li>
                                <li><b>Daniel 09:24-25</b></li>
                                <li><b>Genesis 04:6-7</b></li>
                                <li><b>Hebrews 12:1-3</b></li>
                                <li><b>Isaiah 53:10-11</b></li>
                                <li><b>Jeremiah 18:21-23</b></li>
                                <li><b>Leviticus 04:13-15</b></li>
                                <li><b>Luke 15:17-19</b></li>
                                <li><b>Matthew 12:31-32</b></li>
                                <li><b>Romans 06:22-23</b></li>
                                <li><b>Romans 08:3-5</b></li>
                            </ul>
                            <h2>Examples from the Bible stories:</h2>
                            <ul>
                                <li><strong><b>03:15</b></strong> God said, "I promise I will never again curse the ground because of the evil things people do, or destroy the world by causing a flood, even though people are <strong>sinful</strong> from the time they are children."</li>
                                <li><strong><b>13:12</b></strong> God was very angry with them because of their <strong>sin</strong> and planned to destroy them.</li>
                                <li><strong><b>20:01</b></strong> The kingdoms of Israel and Judah both <strong>sinned</strong> against God. They broke the covenant that God made with them at Sinai.</li>
                                <li><strong><b>21:13</b></strong> The prophets also said that the Messiah would be perfect, having no <strong>sin</strong>. He would die to receive the punishment for other people's <strong>sin</strong>.</li>
                                <li><strong><b>35:01</b></strong> One day, Jesus was teaching many tax collectors and other <strong>sinners</strong> who had gathered to hear him.</li>
                                <li><strong><b>38:05</b></strong> Then Jesus took a cup and said, "Drink this. It is my blood of the New Covenant that is poured out for the forgiveness of <strong>sins</strong>.</li>
                                <li><strong><b>43:11</b></strong> Peter answered them, "Every one of you should repent and be baptized in the name of Jesus Christ so that God will forgive your <strong>sins</strong>."</li>
                                <li><strong><b>48:08</b></strong> We all deserve to die for our <strong>sins</strong>!</li>
                                <li><strong><b>49:17</b></strong> Even though you are a Christian, you will still be tempted to <strong>sin</strong>. But God is faithful and says that if you confess your <strong>sins</strong>, he will forgive you. He will give you strength to fight against <strong>sin</strong>.</li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H817, H819, H2398, H2399, H2400, H2401, H2402, H2403, H2408, H2409, H5771, H6588, H7683, H7686, G264, G265, G266, G268, G361, G3781, G3900, G4258</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Son </span> (verses: 15) </div>
                        <div class="word_def">
                            <h1>son, sons</h1>
                            <h2>Definition:</h2>
                            <p>The male offspring of a man and a woman is called their "son" for his entire life. He is also called a son of that man and a son of that woman. An "adopted son" is a male who has been legally placed into the position of being a son.</p>
                            <ul>
                                <li>"Son" was often used figuratively in the Bible to refer to any male descendant, such as a grandson or great-grandson.</li>
                                <li>The term "son" can also be used as a polite form of address to a boy or man who is younger than the speaker.</li>
                                <li>Sometimes "sons of God" was used in the New Testament to refer to believers in Christ.</li>
                                <li>God called Israel his "firstborn son." This refers to God's choosing of the nation of Israel to be his special people. It is through them that God's message of redemption and salvation came, with the result that many other people have become his spiritual children.</li>
                                <li>The phrase "son of" often has the figurative meaning "person having the characteristics of." Examples of this include "sons of the light," "sons of disobedience," "a son of peace," and "sons of thunder."</li>
                                <li>The phrase "son of" is also used to tell who a person's father is. This phrase is used in genealogies and many other places.</li>
                                <li>Using "son of" to give the name of the father frequently helps distinguish people who have the same name. For example, "Azariah son of Zadok" and "Azariah son of Nathan" in 1 Kings 4, and "Azariah son of Amaziah" in 2 Kings 15 are three different men.</li>
                            </ul>
                            <h2>Translation Suggestions:</h2>
                            <ul>
                                <li>In most occurrences of this term, it is best to translate "son" by the literal term in the language that is used to refer to a son.</li>
                                <li>When translating the term "Son of God," the project language's common term for "son" should be used.</li>
                                <li>When used to refer to a descendant rather than a direct son, the term "descendant" could be used, as in referring to Jesus as the "descendant of David" or in genealogies where sometimes "son" referred to a male descendant who was not an actual son.</li>
                                <li>Sometimes "sons" can be translated as "children," when both males and females are being referred to. For example, "sons of God" could be translated as "children of God" since this expression also includes girls and women.</li>
                                <li>The figurative expression "son of" could also be translated as "someone who has the characteristics of" or "someone who is like" or "someone who has" or "someone who acts like."</li>
                            </ul>
                            <p>(See also: <b>Azariah</b>, <b>descendant</b>, <b>ancestor</b>, <b>firstborn</b>, <b>Son of God</b>, <b>sons of God</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>1 Chronicles 18:14-17</b></li>
                                <li><b>1 Kings 13:1-3</b></li>
                                <li><b>1 Thessalonians 05:4-7</b></li>
                                <li><b>Galatians 04:6-7</b></li>
                                <li><b>Hosea 11:1-2</b></li>
                                <li><b>Isaiah 09:6-7</b></li>
                                <li><b>Matthew 03:16-17</b></li>
                                <li><b>Matthew 05:9-10</b></li>
                                <li><b>Matthew 08:11-13</b></li>
                                <li><b>Nehemiah 10:28-29</b></li>
                            </ul>
                            <h2>Examples from the Bible stories:</h2>
                            <ul>
                                <li><strong><b>04:08</b></strong> God spoke to Abram and promised again that he would have a <strong>son</strong> and as many descendants as the stars in the sky.</li>
                                <li><strong><b>04:09</b></strong> God said, "I will give you a <strong>son</strong> from your own body."</li>
                                <li><strong><b>05:05</b></strong> About a year later, when Abraham was 100 years old and Sarah was 90, Sarah gave birth to Abraham's <strong>son</strong>.</li>
                                <li><strong><b>05:08</b></strong> When they reached the place of sacrifice, Abraham tied up his <strong>son</strong> Isaac and laid him on an altar. He was about to kill his <strong>son</strong> when God said, "Stop! Do not hurt the boy! Now I know that you fear me because you did not keep your only <strong>son</strong> from me."</li>
                                <li><strong><b>09:07</b></strong> When she saw the baby, she took him as her own <strong>son</strong>.</li>
                                <li><strong><b>11:06</b></strong> God killed every one of the Egyptians' firstborn <strong>sons</strong>.</li>
                                <li><strong><b>18:01</b></strong> After many years, David died, and his <strong>son</strong> Solomon began to rule.</li>
                                <li><strong><b>26:04</b></strong> "Is this the <strong>son</strong> of Joseph?â€š" they said.</li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H1060, H1121, H1123, H1248, H3173, H3206, H3211, H4497, H5209, H5220, G3816, G5043, G5207</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Sonofgod </span> (verses: 15, 5, 9, 12, 22) </div>
                        <div class="word_def">
                            <h1>Son of God, Son</h1>
                            <h2>Facts:</h2>
                            <p>The term "Son of God" refers to Jesus, the Word of God, who came into the world as a human being. He is also often referred to as "the Son."</p>
                            <ul>
                                <li>The Son of God has the same nature as God the Father, and is fully God.</li>
                                <li>God the Father, God the Son, and God the Holy Spirit are all of one essence.</li>
                                <li>Unlike human sons, the Son of God has always existed.</li>
                                <li>In the beginning, the Son of God was active in creating the world, along with the Father and the Holy Spirit.</li>
                            </ul>
                            <p>Because Jesus is God's Son, he loves and obeys his Father, and his Father loves him.</p>
                            <h2>Translation Suggestions:</h2>
                            <ul>
                                <li>For the term "Son of God," it is best to translate "Son" with the same word the language would naturally use to refer to a human son.</li>
                                <li>Make sure the word used to translate "son" fits with the word used to translate "father" and that these words are the most natural ones used to express a true father-son relationship in the project language.</li>
                                <li>Using a capital letter to begin "Son" may help show that this is talking about God.</li>
                                <li>The phrase "the Son" is a shortened form of "the Son of God," especially when it occurs in the same context as "the Father."</li>
                            </ul>
                            <p>(Translation suggestions: <b>How to Translate Names</b>)</p>
                            <p>(See also: <b>Christ</b>, <b>ancestor</b>, <b>God</b>, <b>God the Father</b>, <b>Holy Spirit</b>, <b>Jesus</b>, <b>son</b>, <b>sons of God</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>1 John 04:9-10</b></li>
                                <li><b>Acts 09:20-22</b></li>
                                <li><b>Colossians 01:15-17</b></li>
                                <li><b>Galatians 02:20-21</b></li>
                                <li><b>Hebrews 04:14-16</b></li>
                                <li><b>John 03:16-18</b></li>
                                <li><b>Luke 10:22</b></li>
                                <li><b>Matthew 11:25-27</b></li>
                                <li><b>Revelation 02:18-19</b></li>
                                <li><b>Romans 08:28-30</b></li>
                            </ul>
                            <h2>Examples from the Bible stories:</h2>
                            <ul>
                                <li><strong><b>22:05</b></strong> The angel explained, "The Holy Spirit will come to you, and the power of God will overshadow you. So the baby will be holy, the <strong>Son of God</strong>."</li>
                                <li><strong><b>24:09</b></strong> God had told John, "The Holy Spirit will come down and rest on someone you baptize. That person is <strong>the Son of God</strong>."</li>
                                <li><strong><b>31:08</b></strong> The disciples were amazed. They worshiped Jesus, saying to him, "Truly, you are <strong>the Son of God</strong>."</li>
                                <li><strong><b>37:05</b></strong> Martha answered, "Yes, Master! I believe you are the Messiah, the <strong>Son of God</strong>."</li>
                                <li><strong><b>42:10</b></strong> So go, make disciples of all people groups by baptizing them in the name of the Father, <strong>the Son</strong>, and the Holy Spirit, and by teaching them to obey everything I have commanded you."</li>
                                <li><strong><b>46:06</b></strong> Right away, Saul began preaching to the Jews in Damascus, saying, "Jesus is the <strong>Son of God</strong>!"</li>
                                <li><strong><b>49:09</b></strong> But God loved everyone in the world so much that he gave his only <strong>Son</strong> so that whoever believes in Jesus will not be punished for his sins, but will live with God forever.</li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H426, H430, H1121, H1247, G2316, G5207</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Sonofman </span> (verses: 9, 12, 22) </div>
                        <div class="word_def">
                            <h1>Son of Man, son of man</h1>
                            <h2>Definition:</h2>
                            <p>The title "Son of Man" was used by Jesus to refer to himself. He often used this term instead of saying "I" or "me."</p>
                            <ul>
                                <li>In the Bible, "son of man" could be a way of referring to or addressing a man. It could also mean "human being."</li>
                                <li>Throughout the Old Testament book of Ezekiel, God frequently addressed Ezekiel as "son of man." For example he said, "You, son of man, must prophesy."</li>
                                <li>The prophet Daniel saw a vision of a "son of man" coming with the clouds, which is a reference to the coming Messiah.</li>
                                <li>Jesus also said that the Son of Man will be coming back someday on the clouds.</li>
                                <li>These references to the Son of Man coming on the clouds reveal that Jesus the Messiah is God.</li>
                            </ul>
                            <h2>Translation Suggestions:</h2>
                            <ul>
                                <li>When Jesus uses the term "Son of Man" it could be translated as "the One who became a human being" or "the Man from heaven."</li>
                                <li>Some translators occasionally include "I" or "me" with this title (as in "I, the Son of Man") to make it clear that Jesus was talking about himself.</li>
                                <li>Check to make sure that the translation of this term does not give a wrong meaning (such as referring to an illegitimate son or giving the wrong impression that Jesus was only a human being).</li>
                                <li>When used to refer to a person, "son of man" could also be translated as "you, a human being" or "you, man" or "human being" or "man."</li>
                            </ul>
                            <p>(See also: <b>heaven</b>, <b>son</b>, <b>Son of God</b>, <b>Yahweh</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>Acts 07:54-56</b></li>
                                <li><b>Daniel 07:13-14</b></li>
                                <li><b>Ezekiel 43:6-8</b></li>
                                <li><b>John 03:12-13</b></li>
                                <li><b>Luke 06:3-5</b></li>
                                <li><b>Mark 02:10-12</b></li>
                                <li><b>Matthew 13:36-39</b></li>
                                <li><b>Psalms 080:17-18</b></li>
                                <li><b>Revelation 14:14-16</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H120, H606, H1121, H1247, G444, G5207</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Suffer </span> (verses: 12) </div>
                        <div class="word_def">
                            <h1>suffer, suffers, suffered, suffering, sufferings</h1>
                            <h2>Definition:</h2>
                            <p>The terms "suffer" and "suffering" refer to experiencing something very unpleasant, such as illness, pain, or other hardships.</p>
                            <ul>
                                <li>When people are persecuted or when they are sick, they suffer.</li>
                                <li>Sometimes people suffer because of wrong things they have done; other times they suffer because of sin and disease in the world.</li>
                                <li>Suffering can be physical, such as feeling pain or sickness. It can also be emotional, such as feeling fear, sadness, or loneliness.</li>
                                <li>The phrase "suffer me" means "bear with me" or "hear me out" or "listen patiently."</li>
                            </ul>
                            <h2>Translation Suggestions:</h2>
                            <ul>
                                <li>The term "suffer" can be translated as "feel pain" or "endure difficulty" or "experience hardships" or "go through difficult and painful experiences."</li>
                                <li>Depending on the context, "suffering" could be translated as "extremely difficult circumstances" or "severe hardships" or "experiencing hardship" or "time of painful experiences."</li>
                                <li>The phrase "suffer thirst" could be translated as "experience thirst" or "suffer with thirst."</li>
                                <li>To "suffer violence" could also be translated as "undergo violence" or "be harmed by violent acts."</li>
                            </ul>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>1 Thessalonians 02:14-16</b></li>
                                <li><b>2 Thessalonians 01:3-5</b></li>
                                <li><b>2 Timothy 01:8-11</b></li>
                                <li><b>Acts 07:11-13</b></li>
                                <li><b>Isaiah 53:10-11</b></li>
                                <li><b>Jeremiah 06:6-8</b></li>
                                <li><b>Matthew 16:21-23</b></li>
                                <li><b>Psalms 022:24-25</b></li>
                                <li><b>Revelation 01:9-11</b></li>
                                <li><b>Romans 05:3-5</b></li>
                            </ul>
                            <h2>Examples from the Bible stories:</h2>
                            <ul>
                                <li><strong><b>09:13</b></strong> God said, "I have seen the <strong>suffering</strong> of my people."</li>
                                <li><strong><b>38:12</b></strong> Jesus prayed three times, "My Father, if it is possible, please let me not have to drink this cup of <strong>suffering</strong>."</li>
                                <li><strong><b>42:03</b></strong> He (Jesus) reminded them that the prophets said the Messiah would <strong>suffer</strong> and be killed, but would rise again on the third day.</li>
                                <li><strong><b>42:07</b></strong> He (Jesus) said, "It was written long ago that the Messiah would <strong>suffer</strong>, die, and rise from the dead on the third day."</li>
                                <li><strong><b>44:05</b></strong> "Although you did not understand what you were doing, God used your actions to fulfill the prophecies that the Messiah would <strong>suffer</strong> and die."</li>
                                <li><strong><b>46:04</b></strong> God said, "I have chosen him (Saul) to declare my name to the unsaved. I will show him how much he must <strong>suffer</strong> for my sake."</li>
                                <li><strong><b>50:17</b></strong> He (Jesus) will wipe away every tear and there will be no more <strong>suffering</strong>, sadness, crying, evil, pain, or death.</li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H943, H1741, H1934, H4342, H4531, H4912, H5142, H5254, H5375, H5999, H6031, H6040, H6041, H6064, H6090, H6770, H6869, H6887, H7661, G91, G941, G971, G2210, G2346, G2347, G3804, G3958, G4310, G4778, G4841, G5004, G5723</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Tax </span> (verses: 24-25, 27) </div>
                        <div class="word_def">
                            <h1>tax, taxes, taxed, taxing, taxation, taxpayers, tax collector, tax collectors,</h1>
                            <h2>Definition:</h2>
                            <p>The terms "tax" and "taxes" refer to money or goods that people pay to a government that is in authority over them. A "tax collector" was a government worker whose job was to receive money that people were required to pay the government in taxes.</p>
                            <ul>
                                <li>The amount of money that is paid as a tax is usually based on the value of an item or on how much a person's property is worth.</li>
                                <li>In the time of Jesus and and the apostles, the Roman government required taxes from everyone living in the Roman empire, including the Jews.</li>
                                <li>If taxes are not paid, the government can take legal action against a person to get the money that is owed.</li>
                                <li>Joseph and Mary traveled to Bethlehem to be counted in the census held to tax everyone living in the Roman empire.</li>
                                <li>The term "tax" could also be translated as, "required payment" or "government money" or "temple money," depending on the context.</li>
                                <li>To "pay taxes" could also be translated as to "pay money to the government" or "receive money for the government" or "make the required payment." To "collect taxes" could be translated as to "receive money for the government.</li>
                                <li>A "tax collector" is someone who works for the government and receives the money that people are required to pay it.</li>
                                <li>The people who collected taxes for the Roman government would often demand more money from the people than the government required. The tax collectors would keep the extra amount for themselves.</li>
                                <li>Because tax collectors cheated people in this way, the Jews considered them to be among the worst of sinners.</li>
                                <li>The Jews also considered Jewish tax collectors to be traitors to their own people because they worked for the Roman government which was oppressing the Jewish people.</li>
                                <li>The phrase, "tax collectors and sinners" was a common expression in the New Testament, showing how much the Jews despised tax collectors.</li>
                            </ul>
                            <p>(See also: Jew, Rome, sin,)</p>
                            <h2>Bible References</h2>
                            <ul>
                                <li><b>Luke 20:21-22</b></li>
                                <li><b>Mark 02:13-14</b></li>
                                <li><b>Matthew 09:7-9</b></li>
                                <li><b>Numbers 31:28-29</b></li>
                                <li><b>Romans 13:6-7</b></li>
                                <li><b>Luke 03:12-13</b></li>
                                <li><b>Luke 05:27-28</b></li>
                                <li><b>Matthew 05:46-48</b></li>
                                <li><b>Matthew 09:10-11</b></li>
                                <li><b>Matthew 11:18-19</b></li>
                                <li><b>Matthew 17:26-27</b></li>
                                <li><b>Matthew 18:17</b></li>
                            </ul>
                            <h2>Examples from the Bible stories:</h2>
                            <p>34:06 He said, "Two men went to the Temple to pray. One of them was a tax collector, and the other was a religious leader."
                                34:07 "The religious leader prayed like this, 'Thank you, God, that I am not a sinner like other men—such as robbers, unjust men, adulterers, or even like that tax collector.'"
                                34:09 "But the tax collector stood far away from the religious ruler, did not even look up to heaven. Instead, he pounded on his chest and prayed, 'God, please be merciful to me because I am a sinner.'"
                                34:10 Then Jesus said, "I tell you the truth, God heard the tax collector's prayer and declared him to be righteous."
                                35:01 One day, Jesus was teaching many tax collectors and other sinners who had gathered to hear him.
                            </p>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Tax: Strong's: H2670, H4060, H4371, H4522, H4864, H6186, G583, G5411</li>
                                <li>Tax Collector: Strong's: H5065, H5674, G5057, G5058</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Teach </span> (verses: 24) </div>
                        <div class="word_def">
                            <h1>teach, teaches, taught, teaching, teachings, untaught</h1>
                            <h2>Definition:</h2>
                            <p>To "teach" someone is to tell him something he doesn’t already know. It can also mean to "provide information" in general, with no reference to the person who is learning. Usually the information is given in a formal or systematic way. A person’s "teaching" is or his "teachings" are what he has taught.</p>
                            <ul>
                                <li>A "teacher" is someone who teaches. The past action of "teach" is "taught."</li>
                                <li>When Jesus was teaching, he was explaining things about God and his kingdom.</li>
                                <li>Jesus' disciples called him "Teacher" as a respectful form of address for someone who taught people about God.</li>
                                <li>The information that is being taught can be shown or spoken.</li>
                                <li>The term "doctrine" refers to a set of teachings from God about himself as well as God's instructions about how to live. This could also be translated as "teachings from God" or "what God teaches us."</li>
                                <li>The phrase "what you have been taught" could also be translated as, "what these people have taught you" or "what God has taught you," depending on the context.</li>
                                <li>Other ways to translate "teach" could include "tell" or "explain" or "instruct."</li>
                                <li>Often this term can be translated as "teaching people about God."</li>
                            </ul>
                            <p>(See also: <b>instruct</b>, <b>teacher</b>, <b>word of God</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>1 Timothy 01:3-4</b></li>
                                <li><b>Acts 02:40-42</b></li>
                                <li><b>John 07:14-16</b></li>
                                <li><b>Luke 04:31-32</b></li>
                                <li><b>Matthew 04:23-25</b></li>
                                <li><b>Psalms 032:7-8</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H502, H2094, H2449, H3045, H3046, H3256, H3384, H3925, H3948, H7919, H8150, G1317, G1321, G1322, G2085, G2605, G2727, G3100, G2312, G2567, G3811, G4994</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Teacher </span> (verses: 24) </div>
                        <div class="word_def">
                            <h1>teacher, teachers, Teacher</h1>
                            <h2>Definition:</h2>
                            <p>A teacher is a person who gives other people new information. Teachers help others to obtain and use both knowledge and skills.</p>
                            <ul>
                                <li>In the Bible, the word "teacher" is used in a special sense to refer to someone who teaches about God. </li>
                                <li>People who learn from a teacher are called "students" or "disciples."</li>
                                <li>In some Bible translations, this term is capitalized ("Teacher") when it is used as a title for Jesus.</li>
                            </ul>
                            <h2>Translation Suggestions:</h2>
                            <ul>
                                <li>The usual word for a teacher can be used to translate this term, unless that word is only used for a school teacher.</li>
                                <li>Some cultures may have a special title that is used for religious teachers, such as "Sir" or "Rabbi" or "Preacher."</li>
                            </ul>
                            <p>(See also: <b>disciple</b>, <b>preach</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>Ecclesiastes 01:12-15</b></li>
                                <li><b>Ephesians 04:11-13</b></li>
                                <li><b>Galatians 06:6-8</b></li>
                                <li><b>Habakkuk 02:18-20</b></li>
                                <li><b>James 03:1-2</b></li>
                                <li><b>John 01:37-39</b></li>
                                <li><b>Luke 06:39-40</b></li>
                                <li><b>Matthew 12:38-40</b></li>
                            </ul>
                            <h2>Examples from the Bible stories:</h2>
                            <ul>
                                <li><strong><b>27:01</b></strong> One day, an expert in the Jewish law came to Jesus to test him, saying, "<strong>Teacher</strong>, what must I do to inherit eternal life?"</li>
                                <li><strong><b>28:01</b></strong> One day a rich young ruler came up to Jesus and asked him, "Good <strong>Teacher</strong>, what must I do to have eternal life?"</li>
                                <li><strong><b>37:02</b></strong> After the two days had passed, Jesus said to his disciples, "Let's go back to Judea." "But <strong>Teacher</strong>," the disciples answered, "Just a short time ago the people there wanted to kill you!"</li>
                                <li><strong><b>38:14</b></strong> Judas came to Jesus and said, "Greetings, <strong>Teacher</strong>," and kissed him.</li>
                                <li><strong><b>49:03</b></strong> Jesus was also a great <strong>teacher</strong>, and he spoke with authority because he is the Son of God.</li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H3384, H3887, H3925, G1320, G2567, G3547, G5572</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Tribute </span> (verses: 25) </div>
                        <div class="word_def">
                            <h1>tribute</h1>
                            <h2>Definition:</h2>
                            <p>The term "tribute" refers to a gift from one ruler to another ruler, for the purpose of protection and for good relations between their nations.</p>
                            <ul>
                                <li>A tribute can also be a payment that a ruler or government requires from the people, such as a toll or tax.</li>
                                <li>In Bible times, traveling kings or rulers sometimes paid a tribute to the king of the region they were traveling through to make sure they would be protected and safe.</li>
                                <li>Often the tribute would include things besides money, such as foods, spices, rich clothing, and expensive metals such as gold.</li>
                            </ul>
                            <h2>Translation Suggestions:</h2>
                            <ul>
                                <li>Depending on the context, "tribute" could be translated as "official gifts" or "special tax" or "required payment."</li>
                            </ul>
                            <p>(See also: <b>gold</b>, <b>king</b>, <b>ruler</b>, <b>tax</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>1 Chronicles 18:1-2</b></li>
                                <li><b>2 Chronicles 09:22-24</b></li>
                                <li><b>2 Kings 17:1-3</b></li>
                                <li><b>Luke 23:1-2</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H1093, H4060, H4061, H4371, H4503, H4522, H4530, H4853, H6066, H7862, G1323, G2778, G5411</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Turn </span> (verses: 15) </div>
                        <div class="word_def">
                            <h1>return, returns, returned, returning</h1>
                            <h2>Definition:</h2>
                            <p>The term "return" means to go back or to give something back.</p>
                            <ul>
                                <li>To "return to" something means to start doing that activity again. To "return to" a place or person means to bo back to that place or person again.</li>
                                <li>When the Israelites returned to their worship of idols, they were starting to worship them again.</li>
                                <li>When they returned to Yahweh, they repented and were worshiping Yahweh again.</li>
                                <li>To return land or things that were taken or received from someone else means to give that property back to the person it belongs to.</li>
                            </ul>
                            <p>(See also: <b>turn</b>)</p>
                            <h2>Bible References:</h2>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H5437, H7725, H7729, H8421, H8666, G344, G360, G390, G1877, G1880, G1994, G5290</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Vision </span> (verses: 9) </div>
                        <div class="word_def">
                            <h1>vision, visions, envision</h1>
                            <h2>Facts:</h2>
                            <p>The term "vision" refers to something that a person sees. It especially refers to something unusual or supernatural that God shows people in order to give them a message.</p>
                            <ul>
                                <li>Usually, visions are seen while the person is awake. However, sometimes a vision is something a person sees in a dream while asleep.</li>
                                <li>God sends visions to tell people something that is very important. For example, Peter was shown a vision to tell him that God wanted him to welcome Gentiles.</li>
                            </ul>
                            <h2>Translation Suggestion</h2>
                            <ul>
                                <li>The phrase "saw a vision" could be translated as "saw something unusual from God" or "God showed him something special."</li>
                                <li>Some languages may not have separate words for "vision" and "dream." So a sentence such as "Daniel had dreams and visions in his mind" could be translated as something like "Daniel was dreaming while asleep and God caused him to see unusual things."</li>
                            </ul>
                            <p>(See also: <b>dream</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>Acts 09:10-12</b></li>
                                <li><b>Acts 10:3-6</b></li>
                                <li><b>Acts 10:9-12</b></li>
                                <li><b>Acts 12:9-10</b></li>
                                <li><b>Luke 01:21-23</b></li>
                                <li><b>Luke 24:22-24</b></li>
                                <li><b>Matthew 17:9-10</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H2376, H2377, H2378, H2380, H2384, H4236, H4758, H4759, H7203, H7723, H8602, G3701, G3705, G3706</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Voice </span> (verses: 5) </div>
                        <div class="word_def">
                            <h1>voice, voices</h1>
                            <h2>Definition:</h2>
                            <p>The term "voice" is often used figuratively to refer to speaking or communicating something.</p>
                            <ul>
                                <li>God is said to use his voice, even though he doesn't have a voice in the same way a human being does.</li>
                                <li>This term can be used to refer to the whole person, as in the statement "A voice is heard in the desert saying, 'Prepare the way of the Lord.'" This could be translated as "A person is heard calling out in the desert…." (See: <b>synecdoche</b>)</li>
                                <li>To "hear someone's voice" could also be translated as "hear someone speaking."</li>
                                <li>Sometimes the word "voice" may be used for objects that cannot literally speak, such as when David exclaims in the psalms that the "voice" of the heavens proclaims God's mighty works. This could also be translated as "their splendor shows clearly how great God is."</li>
                            </ul>
                            <p>(See also: <b>call</b>, <b>proclaim</b>, <b>splendor</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>John 05:36-38</b></li>
                                <li><b>Luke 01:42-45</b></li>
                                <li><b>Luke 09:34-36</b></li>
                                <li><b>Matthew 03:16-17</b></li>
                                <li><b>Matthew 12:19-21</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H6963, H7032, H7445, H8193, G2906, G5456, G5586</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Water </span> (verses: 15) </div>
                        <div class="word_def">
                            <h1>water, waters, watered, watering</h1>
                            <h2>Definition:</h2>
                            <p>In addition to its primary meaning, "water" also often refers to a body of water, such as an ocean, sea, lake, or river.</p>
                            <ul>
                                <li>The term "waters" refers to bodies of water or many sources of water. It can also be a general reference for a large amount of water.</li>
                                <li>A figurative use of "waters" refers to great distress, difficulties, and suffering. For example, God promises that when we "go through the waters" he will be with us.</li>
                                <li>The phrase "many waters" emphasizes how great the difficulties are.</li>
                                <li>To "water" livestock and other animals means to "provide water for" them. In Bible times, this usually involved drawing water from a well with a bucket and pouring the water into a trough or other container for the animals to drink from.</li>
                                <li>In the Old Testament, God is referred to as the spring or fountain of "living waters" for his people. This means he is the source of spiritual power and refreshment.</li>
                                <li>In the New Testament, Jesus used the phrase "living water" to refer to the Holy Spirit working in a person to transform and bring new life.</li>
                            </ul>
                            <h2>Translation Suggestions:</h2>
                            <ul>
                                <li>The phrase, "draw water" could be translated as "pull water up from a well with a bucket."</li>
                                <li>"Streams of living water will flow from them" could be translated as "the power and blessings from the Holy Spirit will flow out of them them like streams of water." Instead of "blessings" the term "gifts" or "fruits" or "godly character" could be used.</li>
                                <li>When Jesus is talking to the Samaritan woman at the well, the phrase "living water" could be translated as "water that gives life" or "lifegiving water." In this context, the imagery of water must be kept in the translation.</li>
                                <li>Depending on the context, the term "waters" or "many waters" could be translated as "great suffering (that surrounds you like water)" or "overwhelming difficulties (like a flood of water)" or "large amounts of water."</li>
                            </ul>
                            <p>(See also: <b>life</b>, <b>spirit</b>, <b>Holy Spirit</b>, <b>power</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>Acts 08:36-38</b></li>
                                <li><b>Exodus 14:21-22</b></li>
                                <li><b>John 04:9-10</b></li>
                                <li><b>John 04:13-14</b></li>
                                <li><b>John 04:15-16</b></li>
                                <li><b>Matthew 14:28-30</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H2222, H4325, H4529, H4857, H7301, H7783, H8248, G504, G4215, G4222, G5202, G5204</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </label>
            <label>
                <ul>
                    <li>
                        <div class="word_term"> <span style="font-weight: bold;">Well </span> (verses: 5, 5) </div>
                        <div class="word_def">
                            <h1>cistern, cisterns, well, wells</h1>
                            <h2>Definition:</h2>
                            <p>The terms "well" and "cistern" refer to two different kinds of sources for water in Bible times.</p>
                            <ul>
                                <li>A well is a deep hole dug into the ground so that underground water can flow into it.</li>
                                <li>A cistern is a deep hole dug into rock that was used as a holding tank for collecting rain water.</li>
                                <li>Cisterns were usually dug into rock and sealed with plaster to keep the water in. A "broken cistern" happened when the plaster became cracked so that the water leaked out.</li>
                                <li>Cisterns were often located in the courtyard area of people's homes to catch the rainwater that would run off the roof.</li>
                                <li>Wells were often located where they could be accessed by several families or a whole community.</li>
                                <li>Because water was very important for both people and livestock, the right to use a well was often a cause of strife and conflict.</li>
                                <li>Both wells and cisterns were usually covered with a large stone to prevent anything falling in it. Often there was a rope with a bucket or pot attached to it to bring the water up to the surface.</li>
                                <li>Sometimes a dry cistern was used as a place to imprison someone, such as happened to Joseph and Jeremiah.</li>
                            </ul>
                            <h2>Translation Suggestions:</h2>
                            <ul>
                                <li>Ways to translate "well" could include "deep water hole" or "deep hole for spring water" or "deep hole for drawing water."</li>
                                <li>The term "cistern" could be translated as "stone water pit" or "deep and narrow pit for water" or "underground tank for holding water."</li>
                                <li>These terms are similar in meaning. The main difference is that a well continually receives water from underground springs, whereas a cistern is a holding tank for water that usually comes from rain.</li>
                            </ul>
                            <p>(See also: <b>Jeremiah</b>, <b>prison</b>, <b>strife</b>)</p>
                            <h2>Bible References:</h2>
                            <ul>
                                <li><b>1 Chronicles 11:15-17</b></li>
                                <li><b>2 Samuel 17:17-18</b></li>
                                <li><b>Genesis 16:13-14</b></li>
                                <li><b>Luke 14:4-6</b></li>
                                <li><b>Numbers 20:17</b></li>
                            </ul>
                            <h2>Word Data:</h2>
                            <ul>
                                <li>Strong's: H875, H883, H953, H1360, H3653, H4599, H4726, H4841, G4077, G5421</li>
                            </ul>
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

<script>
    $(document).ready(function () {
        $("#next_step").click(function (e) {
            e.preventDefault();

            deleteCookie("temp_tutorial");
            window.location.href = '/events/demo-sun/symbol-draft';

            return false;
        });
    });
</script>