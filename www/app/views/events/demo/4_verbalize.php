<?php
use Core\Language;
?>

<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo Language::show("step_num", "Events", array(3)) . Language::show("discuss", "Events")?></div>
        <div class="demo_title"><?php echo Language::show("demo", "Events") ?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <div class="main_content_text">
                <h4>English - Unlocked Literal Bible - New Testament - <span class='book_name'>2 Timothy 4:1-22</span></h4>

                                    
                
                                    <p><strong><sup>1</sup></strong> I 
give this solemn command before God and Christ Jesus, who will judge the
 living and the dead, and because of his appearing and his kingdom:

  </p>
                                    <p><strong><sup>2</sup></strong> Preach the Word. Be ready when it is convenient and when it is not. Reprove, rebuke, exhort, with all patience and teaching.
  </p>
                                    <p><strong><sup>3</sup></strong> For
 the time will come when people will not endure sound teaching. Instead,
 they will heap up for themselves teachers according to their own 
desires. They will be tickling their hearing.

  </p>
                                    <p><strong><sup>4</sup></strong> They will turn their hearing away from the truth, and they will turn aside to myths.

  </p>
                                    <p><strong><sup>5</sup></strong> But you, be sober-minded in all things. Suffer hardship; do the work of an evangelist; fulfill your service.
  </p>
                                    <p><strong><sup>6</sup></strong> For I am already being poured out. The time of my departure has come.

  </p>
                                    <p><strong><sup>7</sup></strong> I have competed in the good contest; I have finished the race; I have kept the faith.

  </p>
                                    <p><strong><sup>8</sup></strong> The
 crown of righteousness has been reserved for me, which the Lord, the 
righteous judge, will give to me on that day. And not to me only, but 
also to all those who have loved his appearing.


  </p>
                                    <p><strong><sup>9</sup></strong> Do your best to come to me quickly.

  </p>
                                    <p><strong><sup>10</sup></strong> 
For Demas has left me. He loves this present world and has gone to 
Thessalonica. Crescens went to Galatia, and Titus went to Dalmatia.
  </p>
                                    <p><strong><sup>11</sup></strong> Only Luke is with me. Get Mark and bring him with you because he is useful to me in the work.

  </p>
                                    <p><strong><sup>12</sup></strong> Tychicus I sent to Ephesus.

  </p>
                                    <p><strong><sup>13</sup></strong> The cloak that I left at Troas with Carpus, bring it when you come, and the books, especially the parchments.
  </p>
                                    <p><strong><sup>14</sup></strong> Alexander the coppersmith displayed many evil deeds against me. The Lord will repay to him according to his deeds.

  </p>
                                    <p><strong><sup>15</sup></strong> You also should guard yourself against him, because he greatly opposed our words.

  </p>
                                    <p><strong><sup>16</sup></strong> At my first defense, no one stood with me. Instead, everyone left me. May it not be counted against them.
  </p>
                                    <p><strong><sup>17</sup></strong> 
But the Lord stood by me and strengthened me so that, through me, the 
proclamation might be fully fulfilled, and that all the Gentiles might 
hear. I was rescued out of the lion's mouth.

  </p>
                                    <p><strong><sup>18</sup></strong> 
The Lord will rescue me from every evil deed and will save me for his 
heavenly kingdom. To him be the glory forever and ever. Amen.


  </p>
                                    <p><strong><sup>19</sup></strong> Greet Priscilla, Aquila, and the house of Onesiphorus.

  </p>
                                    <p><strong><sup>20</sup></strong> Erastus remained at Corinth, but Trophimus I left sick at Miletus.

  </p>
                                    <p><strong><sup>21</sup></strong> Do your best to come before winter. Eubulus greets you, also Pudens, Linus, Claudia, and all the brothers.



  </p>
                                    <p><strong><sup>22</sup></strong> May the Lord be with your spirit. May grace be with you.</p>
                            </div>

            <div class="main_content_footer row">
                <form action="" method="post">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo Language::show("confirm_finished", "Events")?></div>
                        <label><input name="confirm_step" id="confirm_step" value="1" type="checkbox"> <?php echo Language::show("confirm_yes", "Events")?></label>
                    </div>

                    <button id="next_step" onclick="window.location.href='<?php echo DIR ?>events/demo/prep_chunks'; return false;" class="btn btn-primary" disabled="disabled"><?php echo Language::show("next_step", "Events")?></button>
                </form>
            </div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps">
                <div class="help_title_steps"><?php echo Language::show("help", "Events") ?></div>

                <div class="clear"></div>

                <div class="help_name_steps"><span><?php echo Language::show("step_num", "Events", array(3))?></span> <?php echo Language::show("discuss", "Events")?></div>
                <div class="help_descr_steps">
                    <ul><?php echo mb_substr(Language::show("discuss_desc", "Events"), 0, 300)?>... <div class="show_tutorial_popup"> >>> <?php echo Language::show("show_more", "Events")?></div></ul>
                </div>
            </div>

            <div class="event_info">
                <div class="participant_info">
                    <div class="participant_name">
                        <span><?php echo Language::show("your_partner", "Events") ?>:</span>
                        <span>Gen2Pet</span>
                    </div>
                    <div class="participant_name">
                        <span><?php echo Language::show("your_checker", "Events") ?>:</span>
                        <span>N/A</span>
                    </div>
                    <div class="additional_info">
                        <a href="#"><?php echo Language::show("event_info", "Events") ?></a>
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
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/icons/discuss.png" height="100px" width="100px">
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/big/discuss.png" height="280px" width="280px">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="discuss" value="0" type="checkbox"> <?php echo Language::show("do_not_show_tutorial", "Events")?></label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo Language::show("discuss", "Events")?></h3>
            <ul><?php echo Language::show("discuss_desc", "Events")?></ul>
        </div>
    </div>
</div>