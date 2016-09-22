<?php
use Core\Language;
?>

<div class="editor">
    <div class="comment_div panel panel-default">
        <div class="panel-heading">
            <h1 class="panel-title"><?php echo Language::show("write_note_title", "Events", array(""))?></h1>
            <span class="editor-close glyphicon glyphicon-floppy-disk"></span>
        </div>
        <textarea style="overflow-x: hidden; word-wrap: break-word; overflow-y: visible;" class="textarea textarea_editor"></textarea>
        <img src="<?php echo \Helpers\Url::templatePath() ?>img/loader.gif" class="commentEditorLoader">
    </div>
</div>

<div id="translator_contents" class="row panel-body">
    <div class="row main_content_header">
        <div class="main_content_title"><?php echo Language::show("step_num", "Events", array(4)) . Language::show("self-check_gl", "Events")?></div>
        <div class="demo_title"><?php echo Language::show("demo", "Events") ?></div>
    </div>

    <div class="row">
        <div class="main_content col-sm-9">
            <form action="" method="post" id="main_form">
                <div class="main_content_text row">
                    <div class="row" style="padding-left: 15px">
                        <h4>English - Unlocked Literal Bible - New Testament - <span class='book_name'>2 Timothy 4:1-6</span></h4>

                        <!-- Show blind draft text if it is a translation to other language -->
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
                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                        </div>
                                                                </div>
                            </div>
                                                        <div class="row chunk_verse">
                                <div class="col-sm-6 verse"><strong><sup>2</sup></strong> Preach the Word. Be ready when it is convenient and when it is not. Reprove, rebuke, exhort, with all patience and teaching.
  </div>
                                <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 62px; overflow: hidden; word-wrap: break-word; height: 80px;" name="verses[]" class="verse_ta textarea"></textarea>
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
                                                                    <textarea style="min-height: 124px; overflow: hidden; word-wrap: break-word; height: 124px;" name="verses[]" class="verse_ta textarea"></textarea>
                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                        </div>
                                                                </div>
                            </div>
                                                        <div class="row chunk_verse">
                                <div class="col-sm-6 verse"><strong><sup>4</sup></strong> They will turn their hearing away from the truth, and they will turn aside to myths.

  </div>
                                <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 62px; overflow: hidden; word-wrap: break-word; height: 80px;" name="verses[]" class="verse_ta textarea"></textarea>
                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                        </div>
                                                                </div>
                            </div>
                                                        <div class="row chunk_verse">
                                <div class="col-sm-6 verse"><strong><sup>5</sup></strong> But you, be sober-minded in all things. Suffer hardship; do the work of an evangelist; fulfill your service.
  </div>
                                <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 62px; overflow: hidden; word-wrap: break-word; height: 80px;" name="verses[]" class="verse_ta textarea"></textarea>
                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                        </div>
                                                                </div>
                            </div>
                                                        <div class="row chunk_verse">
                                <div class="col-sm-6 verse"><strong><sup>6</sup></strong> For I am already being poured out. The time of my departure has come.

  </div>
                                <div class="col-sm-6 editor_area">
                                                                    <textarea style="min-height: 62px; overflow: hidden; word-wrap: break-word; height: 80px;" name="verses[]" class="verse_ta textarea"></textarea>
                                    <img class="editComment" data="0:0" src="<?php echo \Helpers\Url::templatePath() ?>img/edit.png" title="write note" width="16px">

                                    <div class="comments">
                                                                        </div>
                                                                </div>
                            </div>
                                                    </div>
                    </div>
                </div>

                <div class="main_content_footer row">
                    <div class="form-group">
                        <div class="main_content_confirm_desc"><?php echo Language::show("confirm_finished", "Events")?></div>
                        <label><input name="confirm_step" id="confirm_step" value="1" type="checkbox"> <?php echo Language::show("confirm_yes", "Events")?></label>
                    </div>

                    <button id="next_step" onclick="window.location.href='<?php echo DIR ?>events/demo/self_check_full'; return false;" class="btn btn-primary" disabled="disabled"><?php echo Language::show("next_step", "Events")?></button>
                </div>
            </form>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_info_steps">
                <div class="help_title_steps">HELP</div>

                <div class="clear"></div>

                <div class="help_name_steps"><span><?php echo Language::show("step_num", "Events", array(4))?></span><?php echo Language::show("self-check_gl", "Events")?></div>
                <div class="help_descr_steps">
                    <ul><?php echo mb_substr(Language::show("self-check_gl_desc", "Events"), 0, 300)?>... <div class="show_tutorial_popup"> >>> <?php echo Language::show("show_more", "Events")?></div></ul>
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
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/icons/self-check.png" height="100px" width="100px">
            <img src="<?php echo \Helpers\Url::templatePath() ?>img/steps/big/self-check.png" height="280px" width="280px">
            <div class="hide_tutorial">
                <label><input id="hide_tutorial" data="self-check" value="0" type="checkbox"> <?php echo Language::show("do_not_show_tutorial", "Events")?></label>
            </div>
        </div>

        <div class="tutorial_content">
            <h3><?php echo Language::show("self-check_gl", "Events")?></h3>
            <ul><?php echo Language::show("self-check_gl_desc", "Events")?></ul>
        </div>
    </div>
</div>