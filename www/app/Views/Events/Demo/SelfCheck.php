<div class="editor">
    <div class="comment_div panel panel-default">
        <div class="panel-heading">
            <h1 class="panel-title"><?php echo __("write_note_title", array(""))?></h1>
            <span class="editor-close glyphicon glyphicon-floppy-disk"></span>
        </div>
        <textarea style="overflow-x: hidden; word-wrap: break-word; overflow-y: visible;" class="textarea textarea_editor"></textarea>
        <img src="<?php echo template_url("img/loader.gif") ?>" class="commentEditorLoader">
    </div>
</div>

<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo __("step_num", array(5)) . __("self-check")?></div>
        <div class="demo_title"><?php echo __("demo") ?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <form action="" method="post" id="main_form">
                <div class="main_content_text row">
                    <div class="row" style="padding-left: 15px">
                        <h4>English - Unlocked Literal Bible - New Testament - <span class='book_name'>2 Timothy 4:1-6</span></h4>

                        <!-- Show blind draft text if it is a translation to other language -->
                                                <div class="col-sm-12">
                            <textarea readonly="readonly" class="readonly blind_ta textarea">Demo blind draft text, Demo blind draft text, Demo blind draft text, Demo blind draft text, Demo blind draft text, Demo blind draft text, Demo blind draft text, Demo blind draft text, Demo blind draft text, Demo blind draft text, Demo blind draft text, Demo blind draft text, Demo blind draft text, Demo blind draft text</textarea>
                        </div>
                                            </div>

                    <div class="row">
                        <div class="col-sm-12">
                                                        <div class="row chunk_verse">
                                <div class="col-sm-6 verse"><strong><sup>1</sup></strong>
 I give this solemn command before God and Christ Jesus, who will judge 
the living and the dead, and because of his appearing and his kingdom:

  </div>
                                <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 93px; overflow: hidden; word-wrap: break-word; height: 93px;" name="verses[]" class="verse_ta textarea"></textarea>
                                    <img class="editComment" data="0:0" src="<?php echo template_url("img/edit.png") ?>" title="write note" width="16px">

                                    <div class="comments">
                                                                        </div>
                                                                </div>
                            </div>
                                                        <div class="row chunk_verse">
                                <div class="col-sm-6 verse"><strong><sup>2</sup></strong> Preach the Word. Be ready when it is convenient and when it is not. Reprove, rebuke, exhort, with all patience and teaching.
  </div>
                                <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 62px; overflow: hidden; word-wrap: break-word; height: 80px;" name="verses[]" class="verse_ta textarea"></textarea>
                                    <img class="editComment" data="0:0" src="<?php echo template_url("img/edit.png") ?>" title="write note" width="16px">

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
                                                                    <textarea style="min-height: 124px; overflow: hidden; word-wrap: break-word; height: 124px;" name="verses[]" class="verse_ta textarea"></textarea>
                                    <img class="editComment" data="0:0" src="<?php echo template_url("img/edit.png") ?>" title="write note" width="16px">

                                    <div class="comments">
                                                                        </div>
                                                                </div>
                            </div>
                                                        <div class="row chunk_verse">
                                <div class="col-sm-6 verse"><strong><sup>4</sup></strong> They will turn their hearing away from the truth, and they will turn aside to myths.

  </div>
                                <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 62px; overflow: hidden; word-wrap: break-word; height: 80px;" name="verses[]" class="verse_ta textarea"></textarea>
                                    <img class="editComment" data="0:0" src="<?php echo template_url("img/edit.png") ?>" title="write note" width="16px">

                                    <div class="comments">
                                                                        </div>
                                                                </div>
                            </div>
                                                        <div class="row chunk_verse">
                                <div class="col-sm-6 verse"><strong><sup>5</sup></strong> But you, be sober-minded in all things. Suffer hardship; do the work of an evangelist; fulfill your service.
  </div>
                                <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 62px; overflow: hidden; word-wrap: break-word; height: 80px;" name="verses[]" class="verse_ta textarea"></textarea>
                                    <img class="editComment" data="0:0" src="<?php echo template_url("img/edit.png") ?>" title="write note" width="16px">

                                    <div class="comments">
                                                                        </div>
                                                                </div>
                            </div>
                                                        <div class="row chunk_verse">
                                <div class="col-sm-6 verse"><strong><sup>6</sup></strong> For I am already being poured out. The time of my departure has come.

  </div>
                                <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 62px; overflow: hidden; word-wrap: break-word; height: 80px;" name="verses[]" class="verse_ta textarea"></textarea>
                                    <img class="editComment" data="0:0" src="<?php echo template_url("img/edit.png") ?>" title="write note" width="16px">

                                    <div class="comments">
                                                                        </div>
                                                                </div>
                            </div>
                                                    </div>
                    </div>
                </div>

                <div class="main_content_footer row">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo __("confirm_finished")?></div>
                        <label><input name="confirm_step" id="confirm_step" value="1" type="checkbox"> <?php echo __("confirm_yes")?></label>
                    </div>

                    <button id="next_step" onclick="window.location.href='<?php echo SITEURL ?>events/demo/peer_review'; return false;" class="btn btn-primary" disabled="disabled"><?php echo __("next_step")?></button>
                </div>
            </form>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps">
                <div class="help_title_steps"><?php echo __("help") ?></div>

                <div class="clear"></div>

                <div class="help_name_steps"><span><?php echo __("step_num", array(5))?></span> <?php echo __("self-check")?></div>
                <div class="help_descr_steps">
                    <ul><?php echo mb_substr(__("self-check_desc"), 0, 300)?>... <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div></ul>
                </div>
            </div>

            <div class="event_info">
                <div class="participant_info">
                    <div class="participant_name">
                        <span><?php echo __("your_partner") ?>:</span>
                        <span>Gen2Pet</span>
                    </div>
                    <div class="participant_name">
                        <span><?php echo __("your_checker") ?>:</span>
                        <span>N/A</span>
                    </div>
                    <div class="additional_info">
                        <a href="#"><?php echo __("event_info") ?></a>
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
            <img src="<?php echo template_url("img/steps/icons/self-check.png") ?>" height="100px" width="100px">
            <img src="<?php echo template_url("img/steps/big/self-check.png") ?>" height="280px" width="280px">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="self-check" value="0" type="checkbox"> <?php echo __("do_not_show_tutorial")?></label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo __("self-check")?></h3>
            <ul><?php echo __("self-check_desc")?></ul>
        </div>
    </div>
</div>