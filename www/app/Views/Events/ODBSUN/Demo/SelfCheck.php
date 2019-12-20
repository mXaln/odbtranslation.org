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
            <div class="demo_title">
                <?php echo __("demo") . " (".__("odb")." - ".__("vsail").")" ?>
            </div>
            <div><?php echo __("step_num", ["step_number" => 4]) . ": " . __("self-check")?></div>
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

                    <h4>Symbolic Universal Notation - <?php echo __("odb") ?> - <span class="book_name">A01 4</span></h4>

                    <div class="col-sm-12 no_padding">
                        <strong class="ltr" style="font-size: 20px;"> <sup><?php echo __("title") ?></sup> </strong>
                        <div class="flex_container chunk_block words_block verse" style="width: 100%;">
                            <div class="chunk_verses flex_left sun_content sun_ta" dir="ltr">
                                <textarea name="symbols[]" class="col-sm-6 verse_ta textarea"
                                          style="min-height: 80px">   </textarea>
                            </div>
                            <div class="flex_middle editor_area" dir="ltr">
                                <div class="vnote">
                                    <textarea name="chunks[]"
                                              class="col-sm-6 peer_verse_ta textarea font_backsun"
                                              style="min-height: 80px;">   </textarea>
                                    
                                </div>
							</div>
							<div class="flex_right">
								<span class="editComment mdi mdi-lead-pencil"
                                              data="0:0"
                                              title="<?php echo __("write_note_title", [""])?>"></span>
								<div class="comments"></div>
							</div>
                        </div>
                        <div class="chunk_divider col-sm-12"></div>
                        <strong class="ltr" style="font-size: 20px;"> <sup><?php echo __("author") ?></sup> </strong>
                        <div class="flex_container chunk_block words_block">
                            <div class="chunk_verses flex_left sun_content sun_ta" dir="ltr">
                                <textarea name="symbols[]" class="col-sm-6 verse_ta textarea"
                                style="min-height: 80px"> </textarea>
                            </div>
                            <div class="flex_middle editor_area" dir="ltr">
                                <div class="vnote">
                                    <textarea name="chunks[]"
                                              class="col-sm-6 peer_verse_ta textarea font_backsun"
                                              style="min-height: 80px;"> </textarea>
                                    
                                </div>
							</div>
							<div class="flex_right">
								<span class="editComment mdi mdi-lead-pencil"
                                              data="0:0"
                                              title="<?php echo __("write_note_title", [""])?>"></span>
								<div class="comments"></div>
							</div>
                        </div>
                        <div class="chunk_divider col-sm-12"></div>
                        <strong class="ltr" style="font-size: 20px;"> <sup><?php echo __("passage") ?></sup> </strong>
                        <div class="flex_container chunk_block words_block">
                            <div class="chunk_verses flex_left sun_content sun_ta" dir="ltr">
                                <textarea name="symbols[]" class="col-sm-6 verse_ta textarea"
                                          style="min-height: 80px;">  :      :  -  </textarea>
                            </div>
                            <div class="flex_middle editor_area" dir="ltr">
                                <div class="vnote">
                                    <textarea name="chunks[]"
                                              class="col-sm-6 peer_verse_ta textarea font_backsun"
                                              style="min-height: 80px;">  :      :  -  </textarea>
                                    
                                </div>
							</div>
							<div class="flex_right">
								<span class="editComment mdi mdi-lead-pencil"
                                              data="0:0"
                                              title="<?php echo __("write_note_title", [""])?>"></span>
								<div class="comments"></div>
							</div>
                        </div>
                        <div class="chunk_divider col-sm-12"></div>
                        <strong class="ltr" style="font-size: 20px;"> <sup><?php echo __("bible_in_a_year") ?></sup> </strong>
                        <div class="flex_container chunk_block words_block">
                            <div class="chunk_verses flex_left sun_content sun_ta" dir="ltr">
                                <textarea name="symbols[]" class="col-sm-6 verse_ta textarea"
                                          style="min-height: 80px;"> </textarea>
                            </div>
                            <div class="flex_middle editor_area" dir="ltr">
                                <div class="vnote">
                                    <textarea name="chunks[]"
                                              class="col-sm-6 peer_verse_ta textarea font_backsun"
                                              style="min-height: 80px;"> </textarea>
                                    
                                </div>
							</div>
							<div class="flex_right">
								<span class="editComment mdi mdi-lead-pencil"
                                              data="0:0"
                                              title="<?php echo __("write_note_title", [""])?>"></span>
								<div class="comments"></div>
							</div>
                        </div>
                        <div class="chunk_divider col-sm-12"></div>
                        <strong class="ltr" style="font-size: 20px;"> <sup><?php echo __("verse") ?></sup> </strong>
                        <div class="flex_container chunk_block words_block">
                            <div class="chunk_verses flex_left sun_content sun_ta" dir="ltr">
                                <textarea name="symbols[]" class="col-sm-6 verse_ta textarea"
                                          style="min-height: 80px;">                        :   </textarea>
                            </div>
                            <div class="flex_middle editor_area" dir="ltr">
                                <div class="vnote">
                                    <textarea name="chunks[]"
                                              class="col-sm-6 peer_verse_ta textarea font_backsun"
                                              style="min-height: 80px;">                        :   </textarea>
                                    
                                </div>
							</div>
							<div class="flex_right">
								<span class="editComment mdi mdi-lead-pencil"
                                              data="0:0"
                                              title="<?php echo __("write_note_title", [""])?>"></span>
								<div class="comments"></div>
							</div>
                        </div>
                        <div class="chunk_divider col-sm-12"></div>
                        <strong class="ltr" style="font-size: 20px;"> <sup><?php echo __("thought") ?></sup> </strong>
                        <div class="flex_container chunk_block words_block">
                            <div class="chunk_verses flex_left sun_content sun_ta" dir="ltr">
                                <textarea name="symbols[]" class="col-sm-6 verse_ta textarea"
                                          style="min-height: 80px;">      </textarea>
                            </div>
                            <div class="flex_middle editor_area" dir="ltr">
                                <div class="vnote">
                                    <textarea name="chunks[]"
                                              class="col-sm-6 peer_verse_ta textarea font_backsun"
                                              style="min-height: 80px;">      </textarea>
                                    
                                </div>
							</div>
							<div class="flex_right">
								<span class="editComment mdi mdi-lead-pencil"
                                              data="0:0"
                                              title="<?php echo __("write_note_title", [""])?>"></span>
								<div class="comments"></div>
							</div>
                        </div>
                        <div class="chunk_divider col-sm-12"></div>
                        <strong class="ltr" style="font-size: 20px;"> <sup><?php echo __("content", ["number" => 1]) ?></sup> </strong>
                        <div class="flex_container chunk_block words_block">
                            <div class="chunk_verses flex_left sun_content sun_ta" dir="ltr">
                                <textarea name="symbols[]" class="col-sm-6 verse_ta textarea"
                                          style="min-height: 80px;">                                       </textarea>
                            </div>
                            <div class="flex_middle editor_area" dir="ltr">
                                <div class="vnote">
                                    <textarea name="chunks[]"
                                              class="col-sm-6 peer_verse_ta textarea font_backsun"
                                              style="min-height: 80px;">                                       </textarea>
                                    
                                </div>
							</div>
							<div class="flex_right">
								<span class="editComment mdi mdi-lead-pencil"
                                              data="0:0"
                                              title="<?php echo __("write_note_title", [""])?>"></span>
								<div class="comments"></div>
							</div>
                        </div>
                        <div class="chunk_divider col-sm-12"></div>
                        <strong class="ltr" style="font-size: 20px;"> <sup><?php echo __("content", ["number" => 2]) ?></sup> </strong>
                        <div class="flex_container chunk_block words_block">
                            <div class="chunk_verses flex_left sun_content sun_ta" dir="ltr">
                                <textarea name="symbols[]" class="col-sm-6 verse_ta textarea"
                                          style="min-height: 80px;">                                                                        </textarea>
                            </div>
                            <div class="flex_middle editor_area" dir="ltr">
                                <div class="vnote">
                                    <textarea name="chunks[]"
                                              class="col-sm-6 peer_verse_ta textarea font_backsun"
                                              style="min-height: 80px;">                                                                        </textarea>
                                    
                                </div>
							</div>
							<div class="flex_right">
								<span class="editComment mdi mdi-lead-pencil"
                                              data="0:0"
                                              title="<?php echo __("write_note_title", [""])?>"></span>
								<div class="comments"></div>
							</div>
                        </div>
                        <div class="chunk_divider col-sm-12"></div>
                        <strong class="ltr" style="font-size: 20px;"> <sup><?php echo __("content", ["number" => 3]) ?></sup> </strong>
                        <div class="flex_container chunk_block words_block">
                            <div class="chunk_verses flex_left sun_content sun_ta" dir="ltr">
                                <textarea name="symbols[]" class="col-sm-6 verse_ta textarea"
                                          style="min-height: 80px;">                                       </textarea>
                            </div>
                            <div class="flex_middle editor_area" dir="ltr">
                                <div class="vnote">
                                    <textarea name="chunks[]"
                                              class="col-sm-6 peer_verse_ta textarea font_backsun"
                                              style="min-height: 80px;">                                       </textarea>
                                    
                                </div>
							</div>
							<div class="flex_right">
								<span class="editComment mdi mdi-lead-pencil"
                                              data="0:0"
                                              title="<?php echo __("write_note_title", [""])?>"></span>
								<div class="comments"></div>
							</div>
                        </div>
                        <div class="chunk_divider col-sm-12"></div>
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
            <div class="step_right alt"><?php echo __("step_num", ["step_number" => 4])?></div>
        </div>

        <div class="content_help col-sm-3">
            <div class="help_float">
                <div class="help_info_steps">
                    <div class="help_hide toggle-help glyphicon glyphicon-eye-close" title="<?php echo __("hide_help") ?>"></div>
                    <div class="help_title_steps"><?php echo __("help") ?></div>

                    <div class="clear"></div>

                    <div class="help_name_steps"><span><?php echo __("step_num", ["step_number" => 4])?>:</span> <?php echo __("self-check")?></div>
                    <div class="help_descr_steps">
                        <ul><?php echo __("self-edit_sun_desc")?></ul>
                        <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div>
                    </div>
                </div>

                <div class="event_info">
                    <div class="participant_info">
                        <div class="additional_info">
                            <a href="/events/demo-sun-odb/information"><?php echo __("event_info") ?></a>
                        </div>
                    </div>
                </div>

                <div class="tr_tools">
                    <button class="btn btn-warning ttools" data-tool="saildict"><?php echo __("show_dictionary") ?></button>
                    <button class="btn btn-primary ttools" data-tool="sunbible"><?php echo __("go_sun_bible") ?></button>
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
            <img src="<?php echo template_url("img/steps/icons/self-check.png") ?>" height="100px" width="100px">
            <img src="<?php echo template_url("img/steps/big/self-check.png") ?>" height="280px" width="280px">
            
        </div>

        <div class="tutorial_content">
            <h3><?php echo __("self-check")?></h3>
            <ul><?php echo __("self-edit_sun_desc")?></ul>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $("#next_step").click(function (e) {
            e.preventDefault();

            
            if(!hasChangesOnPage) window.location.href = '/events/demo-sun-odb/theo_check_checker';

            return false;
        });
    });
</script>