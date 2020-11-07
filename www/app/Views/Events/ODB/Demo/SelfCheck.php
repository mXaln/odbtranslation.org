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
                <?php echo __("demo") . " (".__("odb").")" ?>
            </div>
            <div><?php echo __("step_num", ["step_number" => 4]) . ": " . __("self-check")?></div>
        </div>
        <!--<div class="demo_video">
            <span class="glyphicon glyphicon-play"></span>
            <a href="#"><?php /*echo __("demo_video"); */?></a>
        </div>-->
    </div>

    <div class="">
        <div class="main_content">
            <form action="" method="post" id="main_form">
                <div class="main_content_text row" style="padding-left: 15px">

                    <h4>Español - <?php echo __("odb") ?> - <span class="book_name">A01 4</span></h4>

                    <div class="col-sm-12 no_padding">
                        <br/>

                        <strong class="ltr" style="font-size: 20px;"> <sup><?php echo __("title") ?></sup> </strong>
                        <div class="flex_container chunk_block words_block verse" style="width: 100%;">
                            <div class="chunk_verses flex_left" dir="ltr">
                                A Good Man
                            </div>
                            <div class="flex_middle editor_area" dir="ltr">
                                <div class="vnote">
                                    <textarea name="chunks[]"
                                              class="col-sm-6 peer_verse_ta textarea"
                                              style="min-height: 80px;">Un buen hombre</textarea>
                                    
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
                            <div class="chunk_verses flex_left" dir="ltr">
                                Cindy Hess Kasper
                            </div>
                            <div class="flex_middle editor_area" dir="ltr">
                                <div class="vnote">
                                    <textarea name="chunks[]"
                                              class="col-sm-6 peer_verse_ta textarea"
                                              style="min-height: 80px;"> Cindy Hess Kasper </textarea>
                                    
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
                            <div class="chunk_verses flex_left" dir="ltr">
                                Romans 3:10-18
                            </div>
                            <div class="flex_middle editor_area" dir="ltr">
                                <div class="vnote">
                                    <textarea name="chunks[]"
                                              class="col-sm-6 peer_verse_ta textarea"
                                              style="min-height: 80px;">Romanos 3:10-18</textarea>
                                    
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
                            <div class="chunk_verses flex_left" dir="ltr">
                                Numbers 26-27; Mark 8:1-21
                            </div>
                            <div class="flex_middle editor_area" dir="ltr">
                                <div class="vnote">
                                    <textarea name="chunks[]"
                                              class="col-sm-6 peer_verse_ta textarea"
                                              style="min-height: 80px;"> Números 26-27; Marcos 8:1-21 </textarea>
                                    
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
                            <div class="chunk_verses flex_left" dir="ltr">
                                Salvation is God’s gift. It is not based on
                                anything you have done. Ephesians 2:8
                            </div>
                            <div class="flex_middle editor_area" dir="ltr">
                                <div class="vnote">
                                    <textarea name="chunks[]"
                                              class="col-sm-6 peer_verse_ta textarea"
                                              style="min-height: 80px;"> La salvación es un regalo de Dios. No se basa en nada que hayas hecho. Efesios 2:8 </textarea>
                                    
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
                            <div class="chunk_verses flex_left" dir="ltr">
                                We are saved by God’s work
                            </div>
                            <div class="flex_middle editor_area" dir="ltr">
                                <div class="vnote">
                                    <textarea name="chunks[]"
                                              class="col-sm-6 peer_verse_ta textarea"
                                              style="min-height: 80px;">Somos salvados por la obra de Dios</textarea>
                                    
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
                            <div class="chunk_verses flex_left" dir="ltr">
                                My friend Jerry was a good
                                man. When he died the past said Jerry loved his family. Jerry’s wife trusted him Jerry served
                                his country in the military. Jerry was a good dad and grandfather. Jerry was a great friend.
                                Jerry was a good man.
                            </div>
                            <div class="flex_middle editor_area" dir="ltr">
                                <div class="vnote">
                                    <textarea name="chunks[]"
                                              class="col-sm-6 peer_verse_ta textarea"
                                              style="min-height: 80px;"> Mi amigo Jerry era un buen hombre. Cuando murió, el pasado dijo que Jerry amaba a su familia. La esposa de Jerry confiaba en él Jerry sirvió a su país en el ejército. Jerry fue un buen padre y abuelo. Jerry fue un gran amigo. Jerry era un buen hombre.</textarea>
                                    
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
                            <div class="chunk_verses flex_left" dir="ltr">
                                The pastor said Jerry’s good
                                things do not get him to heaven. The Bible says no one is perfect. The good things Jerry did are
                                not good enough. Jerry knew that when you sin, the cost is death and hell. His final place after
                                death was not from a good life. Jerry knew he needed Jesus. Jesus died in place of Jerry. Jerry
                                knew believing in Jesus was his way to heaven.
                            </div>
                            <div class="flex_middle editor_area" dir="ltr">
                                <div class="vnote">
                                    <textarea name="chunks[]"
                                              class="col-sm-6 peer_verse_ta textarea"
                                              style="min-height: 80px;"> El pastor dijo que las cosas buenas de Jerry no lo llevan al cielo. La Biblia dice que nadie es perfecto. Las cosas buenas que hizo Jerry no son lo suficientemente buenas. Jerry sabía que cuando pecas, el costo es la muerte y el infierno. Su último lugar después de la muerte no fue el de una buena vida. Jerry sabía que necesitaba a Jesús. Jesús murió en lugar de Jerry. Jerry sabía que creer en Jesús era su camino al cielo. </textarea>
                                    
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
                            <div class="chunk_verses flex_left" dir="ltr">
                                Every person needs God to
                                forgive them. Jesus died on the cross for our sins. We cannot be good enough. We need faith in
                                Jesus. Heaven is God’s figt. It is not based on anything we do. His gift is wonderful.
                            </div>
                            <div class="flex_middle editor_area" dir="ltr">
                                <div class="vnote">
                                    <textarea name="chunks[]"
                                              class="col-sm-6 peer_verse_ta textarea"
                                              style="min-height: 80px;"> Toda persona necesita que Dios los perdone. Jesús murió en la cruz por nuestros pecados. No podemos ser lo suficientemente buenos. Necesitamos fe en Jesús. El cielo es obra de Dios. No se basa en nada de lo que hacemos. Su regalo es maravilloso.</textarea>
                                    
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
    </div>
</div>

<div class="content_help closed">
    <div id="help_hide" class="glyphicon glyphicon-chevron-down"> <?php echo __("help") ?></div>

    <div class="help_float">
        <div class="help_info_steps">
            <div class="help_name_steps"><span><?php echo __("step_num", ["step_number" => 4])?>:</span> <?php echo __("self-check")?></div>
            <div class="help_descr_steps">
                <ul><?php echo __("self-edit_odb_desc")?></ul>
                <div class="show_tutorial_popup"> >>> <?php echo __("show_more")?></div>
            </div>
        </div>

        <div class="event_info">
            <div class="participant_info">
                <div class="additional_info">
                    <a href="/events/demo-odb/information"><?php echo __("event_info") ?></a>
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
            <ul><?php echo __("self-edit_odb_desc")?></ul>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $("#next_step").click(function (e) {
            e.preventDefault();

            if(!hasChangesOnPage) window.location.href = '/events/demo-odb/peer_review_checker';

            return false;
        });
    });
</script>