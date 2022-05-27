<?php
use Helpers\Constants\EventSteps;
use Helpers\Constants\StepsStates;
?>

<div class="back_link">
    <?php if(isset($_SERVER["HTTP_REFERER"])): ?>
        <a href="<?php echo $_SERVER["HTTP_REFERER"] ?>"><?php echo __("go_back") ?></a>
    <?php endif; ?>
    <div class="demo_title">
        <?php echo __("demo") . " (".__("mill").")" ?>
    </div>
</div>

<div>
    <div class="book_title">Biblical Foundations 1: Manuscript</div>
    <div class="project_title">Español - <?php echo __("mill") ?></div>
    <div class="overall_progress_bar">
        <h3><?php echo __("progress_all") ?></h3>
        <div class="progress progress_all ">
            <div style="min-width: 0em; width: 11%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="11" role="progressbar" class="progress-bar progress-bar-success">
                11%
            </div>
        </div>
    </div>
    <div class="clear"></div>
</div>

<div class="row" style="position:relative;">
    <div class="chapter_list">
        <div class="chapter_item">
            <div class="chapter_accordion">
                <div class="section_header" data="sec_1">
                    <div class="section_arrow glyphicon glyphicon-triangle-right"></div>
                    <div class="chapter_number section_title"><?php echo __("chapter_number", ["chapter" => 1]) ?></div>
                    <div class="section_translator_progress_bar">
                        <div class="progress ">
                            <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="min-width: 0em; width: 100%"> 100% </div>
                        </div>
                        <div class="glyphicon glyphicon-ok finished_icon"></div>
                        <div class="clear"></div>
                    </div>
                    <div> <span style="font-weight: bold;"><?php echo __("last_edit") .": " ?> </span> <span class="datetime" data="Thu, 08 Feb 2018 20:43:39 +0000">Thu, February 8, 2018, 3:43 PM</span> </div>
                    <div class="clear"></div>
                </div>
                <div class="section_content" style="display: none;">
                    <div class="section_translator">
                        <div class="section_translator_name tnleft odb"> <img src="<?php echo template_url("img/avatars/m13.png") ?>" width="50"> <span><b>Max A.</b></span> </div>
                        <div class="section_translator_name tnleft odb" style="margin-left: 460px"> <img src="<?php echo template_url("img/avatars/m19.png") ?>" width="50"> <span><b>Genry M.</b></span> </div>
                        <div class="section_translator_name tnleft odb" style="margin-left: 30px"> <img src="<?php echo template_url("img/avatars/m19.png") ?>" width="50"> <span><b>Genry M.</b></span> </div>
                        <div class="clear"></div>
                    </div>
                    <div class="section_steps">
                        <!-- Consume Step -->
                        <div class="section_step finished">
                            <div class="step_status"><?php echo __("step_status_".StepsStates::FINISHED) ?></div>
                            <div class="step_light"></div>
                            <div class="step_icon"><img src="<?php echo template_url("img/steps/icons/consume.png") ?>" width="40"></div>
                            <div class="step_name">1. <?php echo __(EventSteps::CONSUME) ?></div>
                        </div>
                        <!-- Verbalize Step -->
                        <div class="section_step finished">
                            <div class="step_status"><?php echo __("step_status_".StepsStates::FINISHED) ?></div>
                            <div class="step_light"></div>
                            <div class="step_icon"><img width="40" src="<?php echo template_url("img/steps/icons/".EventSteps::VERBALIZE.".png") ?>"></div>
                            <div class="step_name">2. <?php echo __(EventSteps::VERBALIZE); ?></div>
                            <div class="step_checker">
                                <img width="50" src="<?php echo template_url("img/avatars/f10.png") ?>">
                                <div>Amy G.</div>
                                <span class="glyphicon glyphicon-ok checked"></span>
                            </div>
                        </div>
                        <!-- Draft Step -->
                        <div class="section_step finished" style="width: 180px">
                            <div class="step_status"><?php echo __("step_status_".StepsStates::FINISHED) ?></div>
                            <div class="step_light"></div>
                            <div class="step_icon"><img src="<?php echo template_url("img/steps/icons/blind-draft.png") ?>" width="40"></div>
                            <div class="step_name">3. <?php echo __(EventSteps::BLIND_DRAFT."_mill"); ?></div>
                            <div class="step_chunks more_chunks">
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 1]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 2]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 3]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 4]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 5]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 6]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 7]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 8]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 9]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 10]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 11]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 12]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 13]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 14]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 15]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 16]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 17]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 18]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 19]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 20]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 21]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 22]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 23]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 24]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="chunks_blind glyphicon glyphicon-triangle-bottom"></div>
                            </div>
                        </div>
                        <!-- Self Check Step -->
                        <div class="section_step finished">
                            <div class="step_status"><?php echo __("step_status_".StepsStates::FINISHED) ?></div>
                            <div class="step_light"></div>
                            <div class="step_icon"><img src="<?php echo template_url("img/steps/icons/self-check.png") ?>" width="40"></div>
                            <div class="step_name">4. <?php echo __(EventSteps::SELF_CHECK); ?></div>
                        </div>
                        <!-- Checking stage -->
                        <div class="section_step chk" style="width: 50px"> </div>
                        <!-- Peer Check Step -->
                        <div class="section_step chk finished">
                            <div class="step_status"><?php echo __("step_status_".StepsStates::FINISHED) ?></div>
                            <div class="step_light"></div>
                            <div class="step_icon"><img src="<?php echo template_url("img/steps/icons/peer-review.png") ?>" width="40"></div>
                            <div class="step_name">5. <?php echo __(EventSteps::PEER_REVIEW); ?></div>
                        </div>
                        <!-- Checking stage -->
                        <div class="section_step chk" style="width: 50px"> </div>
                        <!-- Verse-by-verse Check Step -->
                        <div class="section_step chk finished">
                            <div class="step_status"><?php echo __("step_status_".StepsStates::FINISHED) ?></div>
                            <div class="step_light"></div>
                            <div class="step_icon"><img src="<?php echo template_url("img/steps/icons/content-review.png") ?>" width="40"></div>
                            <div class="step_name">6. <?php echo __(EventSteps::CONTENT_REVIEW."_mill"); ?></div>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="chapter_item">
            <div class="chapter_accordion">
                <div class="section_header" data="sec_2">
                    <div class="section_arrow glyphicon glyphicon-triangle-right"></div>
                    <div class="chapter_number section_title"><?php echo __("chapter_number", ["chapter" => 2]) ?></div>
                    <div class="section_translator_progress_bar">
                        <div class="progress ">
                            <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="min-width: 0em; width: 100%"> 100% </div>
                        </div>
                        <div class="glyphicon glyphicon-ok finished_icon"></div>
                        <div class="clear"></div>
                    </div>
                    <div> <span style="font-weight: bold;"><?php echo __("last_edit") .": " ?> </span> <span class="datetime" data="Fri, 09 Feb 2018 15:30:52 +0000">Fri, February 9, 2018, 10:30 AM</span> </div>
                    <div class="clear"></div>
                </div>
                <div class="section_content">
                    <div class="section_translator">
                        <div class="section_translator_name tnleft sun"> <img src="<?php echo template_url("img/avatars/m13.png") ?>" width="50"> <span><b>Max A.</b></span> </div>
                        <div class="section_translator_name tnleft sun" style="margin-left: 460px"> <img src="<?php echo template_url("img/avatars/m7.png") ?>" width="50"> <span><b>Mark P.</b></span> </div>
                        <div class="section_translator_name tnleft sun" style="margin-left: 30px"> <img src="<?php echo template_url("img/avatars/f10.png") ?>" width="50"> <span><b>Amy G.</b></span> </div>
                        <div class="clear"></div>
                    </div>
                    <div class="section_steps">
                        <!-- Consume Step -->
                        <div class="section_step finished">
                            <div class="step_status"><?php echo __("step_status_".StepsStates::FINISHED) ?></div>
                            <div class="step_light"></div>
                            <div class="step_icon"><img src="<?php echo template_url("img/steps/icons/consume.png") ?>" width="40"></div>
                            <div class="step_name">1. <?php echo __(EventSteps::CONSUME) ?></div>
                        </div>
                        <!-- Verbalize Step -->
                        <div class="section_step finished">
                            <div class="step_status"><?php echo __("step_status_".StepsStates::FINISHED) ?></div>
                            <div class="step_light"></div>
                            <div class="step_icon"><img width="40" src="<?php echo template_url("img/steps/icons/".EventSteps::VERBALIZE.".png") ?>"></div>
                            <div class="step_name">2. <?php echo __(EventSteps::VERBALIZE); ?></div>
                            <div class="step_checker">
                                <img width="50" src="<?php echo template_url("img/avatars/m19.png") ?>">
                                <div>Genry M.</div>
                                <span class="glyphicon glyphicon-ok checked"></span>
                            </div>
                        </div>
                        <!-- Draft Step -->
                        <div class="section_step finished" style="width: 180px">
                            <div class="step_status"><?php echo __("step_status_".StepsStates::FINISHED) ?></div>
                            <div class="step_light"></div>
                            <div class="step_icon"><img src="<?php echo template_url("img/steps/icons/blind-draft.png") ?>" width="40"></div>
                            <div class="step_name">3. <?php echo __(EventSteps::BLIND_DRAFT."_mill"); ?></div>
                            <div class="step_chunks more_chunks">
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 1]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 2]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 3]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 4]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 5]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 6]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 7]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 8]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 9]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 10]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 11]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 12]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 13]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 14]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 15]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 16]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="chunks_blind glyphicon glyphicon-triangle-bottom"></div>
                            </div>
                        </div>
                        <!-- Self Check Step -->
                        <div class="section_step finished">
                            <div class="step_status"><?php echo __("step_status_".StepsStates::FINISHED) ?></div>
                            <div class="step_light"></div>
                            <div class="step_icon"><img src="<?php echo template_url("img/steps/icons/self-check.png") ?>" width="40"></div>
                            <div class="step_name">4. <?php echo __(EventSteps::SELF_CHECK); ?></div>
                        </div>
                        <!-- Checking stage -->
                        <div class="section_step chk" style="width: 50px"> </div>
                        <!-- Peer Check Step -->
                        <div class="section_step chk finished">
                            <div class="step_status"><?php echo __("step_status_".StepsStates::FINISHED) ?></div>
                            <div class="step_light"></div>
                            <div class="step_icon"><img src="<?php echo template_url("img/steps/icons/peer-review.png") ?>" width="40"></div>
                            <div class="step_name">5. <?php echo __(EventSteps::PEER_REVIEW); ?></div>
                        </div>
                        <!-- Checking stage -->
                        <div class="section_step chk" style="width: 50px"> </div>
                        <!-- Verse-by-verse Check Step -->
                        <div class="section_step chk finished">
                            <div class="step_status"><?php echo __("step_status_".StepsStates::FINISHED) ?></div>
                            <div class="step_light"></div>
                            <div class="step_icon"><img src="<?php echo template_url("img/steps/icons/content-review.png") ?>" width="40"></div>
                            <div class="step_name">6. <?php echo __(EventSteps::CONTENT_REVIEW."_mill"); ?></div>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="chapter_item">
            <div class="chapter_accordion">
                <div class="section_header" data="sec_3">
                    <div class="section_arrow glyphicon glyphicon-triangle-right"></div>
                    <div class="chapter_number section_title"><?php echo __("chapter_number", ["chapter" => 3]) ?></div>
                    <div class="section_translator_progress_bar">
                        <div class="progress zero">
                            <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="min-width: 0em; width: 0%"> 0% </div>
                        </div>
                        <div class=" finished_icon"></div>
                        <div class="clear"></div>
                    </div>
                    <div> </div>
                    <div class="clear"></div>
                </div>
                <div class="section_content">
                    <div class="section_translator">
                        <div class="section_translator_name tnleft sun"> <img src="<?php echo template_url("img/avatars/m7.png") ?>" width="50"> <span><b>Mark P.</b></span> </div>
                        <div class="clear"></div>
                    </div>
                    <div class="section_steps">
                        <!-- Consume Step -->
                        <div class="section_step not_started">
                            <div class="step_status"><?php echo __("step_status_".StepsStates::NOT_STARTED) ?></div>
                            <div class="step_light"></div>
                            <div class="step_icon"><img src="<?php echo template_url("img/steps/icons/consume.png") ?>" width="40"></div>
                            <div class="step_name">1. <?php echo __(EventSteps::CONSUME) ?></div>
                        </div>
                        <!-- Verbalize Step -->
                        <div class="section_step not_started">
                            <div class="step_status"><?php echo __("step_status_".StepsStates::NOT_STARTED) ?></div>
                            <div class="step_light"></div>
                            <div class="step_icon"><img width="40" src="<?php echo template_url("img/steps/icons/".EventSteps::VERBALIZE.".png") ?>"></div>
                            <div class="step_name">2. <?php echo __(EventSteps::VERBALIZE); ?></div>
                        </div>
                        <!-- Draft Step -->
                        <div class="section_step not_started" style="width: 180px">
                            <div class="step_status"><?php echo __("step_status_".StepsStates::NOT_STARTED) ?></div>
                            <div class="step_light"></div>
                            <div class="step_icon"><img src="<?php echo template_url("img/steps/icons/blind-draft.png") ?>" width="40"></div>
                            <div class="step_name">3. <?php echo __(EventSteps::BLIND_DRAFT."_mill"); ?></div>
                            <div class="step_chunks more_chunks"> </div>
                        </div>
                        <!-- Self Check Step -->
                        <div class="section_step not_started">
                            <div class="step_status"><?php echo __("step_status_".StepsStates::NOT_STARTED) ?></div>
                            <div class="step_light"></div>
                            <div class="step_icon"><img src="<?php echo template_url("img/steps/icons/self-check.png") ?>" width="40"></div>
                            <div class="step_name">4. <?php echo __(EventSteps::SELF_CHECK); ?></div>
                        </div>
                        <!-- Checking stage -->
                        <div class="section_step chk" style="width: 50px"> </div>
                        <!-- Peer Check Step -->
                        <div class="section_step chk not_started">
                            <div class="step_status"><?php echo __("step_status_".StepsStates::NOT_STARTED) ?></div>
                            <div class="step_light"></div>
                            <div class="step_icon"><img src="<?php echo template_url("img/steps/icons/peer-review.png") ?>" width="40"></div>
                            <div class="step_name">5. <?php echo __(EventSteps::PEER_REVIEW); ?></div>
                        </div>
                        <!-- Checking stage -->
                        <div class="section_step chk" style="width: 50px"> </div>
                        <!-- Verse-by-verse Check Step -->
                        <div class="section_step chk not_started">
                            <div class="step_status"><?php echo __("step_status_".StepsStates::NOT_STARTED) ?></div>
                            <div class="step_light"></div>
                            <div class="step_icon"><img src="<?php echo template_url("img/steps/icons/content-review.png") ?>" width="40"></div>
                            <div class="step_name">6. <?php echo __(EventSteps::CONTENT_REVIEW."_mill"); ?></div>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="chapter_item">
            <div class="chapter_accordion">
                <div class="section_header" data="sec_4">
                    <div class="section_arrow glyphicon glyphicon-triangle-right"></div>
                    <div class="chapter_number section_title"><?php echo __("chapter_number", ["chapter" => 4]) ?></div>
                    <div class="section_translator_progress_bar">
                        <div class="progress ">
                            <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="33" aria-valuemin="0" aria-valuemax="100" style="min-width: 0em; width: 33%"> 33% </div>
                        </div>
                        <div class=" finished_icon"></div>
                        <div class="clear"></div>
                    </div>
                    <div> <span style="font-weight: bold;"><?php echo __("last_edit") .": " ?> </span> <span class="datetime" data="Fri, 09 Feb 2018 14:19:23 +0000">Fri, February 9, 2018, 9:19 AM</span> </div>
                    <div class="clear"></div>
                </div>
                <div class="section_content">
                    <div class="section_translator">
                        <div class="section_translator_name tnleft sun"> <img src="<?php echo template_url("img/avatars/f10.png") ?>" width="50"> <span><b>Amy G.</b></span> </div>
                        <div class="clear"></div>
                    </div>
                    <div class="section_steps">
                        <!-- Consume Step -->
                        <div class="section_step finished">
                            <div class="step_status"><?php echo __("step_status_".StepsStates::FINISHED) ?></div>
                            <div class="step_light"></div>
                            <div class="step_icon"><img src="<?php echo template_url("img/steps/icons/consume.png") ?>" width="40"></div>
                            <div class="step_name">1. <?php echo __(EventSteps::CONSUME) ?></div>
                        </div>
                        <!-- Verbalize Step -->
                        <div class="section_step finished">
                            <div class="step_status"><?php echo __("step_status_".StepsStates::FINISHED) ?></div>
                            <div class="step_light"></div>
                            <div class="step_icon"><img width="40" src="<?php echo template_url("img/steps/icons/".EventSteps::VERBALIZE.".png") ?>"></div>
                            <div class="step_name">2. <?php echo __(EventSteps::VERBALIZE); ?></div>
                            <div class="step_checker">
                                <img width="50" src="<?php echo template_url("img/avatars/m19.png") ?>">
                                <div>Genry M.</div>
                                <span class="glyphicon glyphicon-ok checked"></span>
                            </div>
                        </div>
                        <!-- Draft Step -->
                        <div class="section_step in_progress" style="width: 180px">
                            <div class="step_status"><?php echo __("step_status_".StepsStates::IN_PROGRESS) ?></div>
                            <div class="step_light"></div>
                            <div class="step_icon"><img src="<?php echo template_url("img/steps/icons/blind-draft.png") ?>" width="40"></div>
                            <div class="step_name">3. <?php echo __(EventSteps::BLIND_DRAFT."_mill"); ?></div>
                            <div class="step_chunks more_chunks">
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 1]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 2]) ?> &nbsp;&nbsp;<span class="finished_msg glyphicon glyphicon-ok"></span> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 3]) ?> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 4]) ?> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 5]) ?> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 6]) ?> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 7]) ?> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 8]) ?> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 9]) ?> </div>
                                <div class="section_translator_chunk"> <?php echo __("chunk_number", ["chunk_number" => 10]) ?> </div>
                                <div class="chunks_blind glyphicon glyphicon-triangle-bottom"></div>
                            </div>
                        </div>
                        <!-- Self Check Step -->
                        <div class="section_step not_started">
                            <div class="step_status"><?php echo __("step_status_".StepsStates::NOT_STARTED) ?></div>
                            <div class="step_light"></div>
                            <div class="step_icon"><img src="<?php echo template_url("img/steps/icons/self-check.png") ?>" width="40"></div>
                            <div class="step_name">4. <?php echo __(EventSteps::SELF_CHECK); ?></div>
                        </div>
                        <!-- Checking stage -->
                        <div class="section_step chk" style="width: 50px"> </div>
                        <!-- Peer Check Step -->
                        <div class="section_step chk not_started">
                            <div class="step_status"><?php echo __("step_status_".StepsStates::NOT_STARTED) ?></div>
                            <div class="step_light"></div>
                            <div class="step_icon"><img src="<?php echo template_url("img/steps/icons/peer-review.png") ?>" width="40"></div>
                            <div class="step_name">5. <?php echo __(EventSteps::PEER_REVIEW); ?></div>
                        </div>
                        <!-- Checking stage -->
                        <div class="section_step chk" style="width: 50px"> </div>
                        <!-- Verse-by-verse Check Step -->
                        <div class="section_step chk not_started">
                            <div class="step_status"><?php echo __("step_status_".StepsStates::NOT_STARTED) ?></div>
                            <div class="step_light"></div>
                            <div class="step_icon"><img src="<?php echo template_url("img/steps/icons/content-review.png") ?>" width="40"></div>
                            <div class="step_name">6. <?php echo __(EventSteps::CONTENT_REVIEW."_mill"); ?></div>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="chapter_item">
            <div class="chapter_number nofloat"><?php echo __("chapter_number", ["chapter" => 5]) ?></div>
        </div>
        <div class="chapter_item">
            <div class="chapter_number nofloat"><?php echo __("chapter_number", ["chapter" => 6]) ?></div>
        </div>
        <div class="chapter_item">
            <div class="chapter_number nofloat"><?php echo __("chapter_number", ["chapter" => 7]) ?></div>
        </div>
        <div class="chapter_item">
            <div class="chapter_number nofloat"><?php echo __("chapter_number", ["chapter" => 8]) ?></div>
        </div>
        <div class="chapter_item">
            <div class="chapter_number nofloat"><?php echo __("chapter_number", ["chapter" => 9]) ?></div>
        </div>
        <div class="chapter_item">
            <div class="chapter_number nofloat"><?php echo __("chapter_number", ["chapter" => 10]) ?></div>
        </div>
        <div class="chapter_item">
            <div class="chapter_number nofloat"><?php echo __("chapter_number", ["chapter" => 11]) ?></div>
        </div>
        <div class="chapter_item">
            <div class="chapter_number nofloat"><?php echo __("chapter_number", ["chapter" => 12]) ?></div>
        </div>
        <div class="chapter_item">
            <div class="chapter_number nofloat"><?php echo __("chapter_number", ["chapter" => 13]) ?></div>
        </div>
        <div class="chapter_item">
            <div class="chapter_number nofloat"><?php echo __("chapter_number", ["chapter" => 14]) ?></div>
        </div>
        <div class="chapter_item">
            <div class="chapter_number nofloat"><?php echo __("chapter_number", ["chapter" => 15]) ?></div>
        </div>
        <div class="chapter_item">
            <div class="chapter_number nofloat"><?php echo __("chapter_number", ["chapter" => 16]) ?></div>
        </div>
        <div class="chapter_item">
            <div class="chapter_number nofloat"><?php echo __("chapter_number", ["chapter" => 17]) ?></div>
        </div>
        <div class="chapter_item">
            <div class="chapter_number nofloat"><?php echo __("chapter_number", ["chapter" => 18]) ?></div>
        </div>
        <div class="chapter_item">
            <div class="chapter_number nofloat"><?php echo __("chapter_number", ["chapter" => 19]) ?></div>
        </div>
        <div class="chapter_item">
            <div class="chapter_number nofloat"><?php echo __("chapter_number", ["chapter" => 20]) ?></div>
        </div>
    </div>

    <div class="members_list">
        <div class="members_title"><?php echo __("event_participants") ?>:</div>
        <div class="member_item" data="4">
            <span class="online_indicator glyphicon glyphicon-record">&nbsp;</span>
            <span class="member_uname">Mur M.</span>
            <span class="member_admin"> </span> <span class="online_status" style="display: none;"><?php echo __("status_online") ?></span>
            <span class="offline_status"><?php echo __("status_offline") ?></span>
        </div>
        <div class="member_item" data="5">
            <span class="online_indicator glyphicon glyphicon-record">&nbsp;</span>
            <span class="member_uname">Mark P.</span>
            <span class="member_admin"> </span> <span class="online_status" style="display: none;"><?php echo __("status_online") ?></span>
            <span class="offline_status"><?php echo __("status_offline") ?></span>
        </div>
        <div class="member_item" data="3">
            <span class="online_indicator glyphicon glyphicon-record online">&nbsp;</span>
            <span class="member_uname">Max A.</span>
            <span class="member_admin"> (<?php echo __("facilitator"); ?>)</span>
            <span class="online_status" style="display: inline;"><?php echo __("status_online") ?></span>
            <span class="offline_status" style="display: none;"><?php echo __("status_offline") ?></span>
        </div>
        <div class="member_item" data="6">
            <span class="online_indicator glyphicon glyphicon-record online">&nbsp;</span>
            <span class="member_uname">Amy G.</span>
            <span class="member_admin"> </span> <span class="online_status" style="display: inline;"><?php echo __("status_online") ?></span>
            <span class="offline_status" style="display: none;"><?php echo __("status_offline") ?></span>
        </div>
    </div>
</div>