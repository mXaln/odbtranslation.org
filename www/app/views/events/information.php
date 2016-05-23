<?php
use Core\Language;
use Helpers\Constants\EventSteps;
use Helpers\Session;


echo \Core\Error::display($error);

if(!isset($error)):
    ?>

    <div>
        <div class="book_title"><?php echo $data["event"][0]->name ?></div>
        <div class="project_title"><?php echo Language::show($data["event"][0]->bookProject, "Events")." - ".$data["event"][0]->tLang ?></div>
    </div>

    <div class="row">
        <div class="chapter_list col-sm-7">
            <?php foreach ($data["chapters"] as $key => $chapter):?>
                <div class="chapter_item">
                    <div class="chapter_number"><?php echo Language::show("chapter_number", "Events", array($key)) ?></div>
                    <div class="chapter_accordion">
                        <div class="section_header">
                            <span class="section_arrow glyphicon glyphicon-triangle-right"></span>
                            <span class="section_title">Draft 1</span>
                            <?php if(!empty($chapter["chunks"])): ?>
                                <?php $progress = sizeof($chapter["chunksData"]) * 100 / sizeof($chapter["chunks"]); ?>
                                <span class="section_progress" style="color: #2ea02e;">
                            <?php if(sizeof($chapter["chunksData"]) < sizeof($chapter["chunks"])): ?>
                                <?php echo Language::show("chapter_in_progress", "Events", array($progress)) ?>
                            <?php else: ?>
                                <?php echo Language::show("chapter_finished", "Events") ?>
                            <?php endif; ?>
                        </span>
                            <?php else: ?>
                                <span class="section_progress" style="color: #ff9125;">
                            <?php echo Language::show("chapter_not_started", "Events") ?>
                        </span>
                            <?php endif; ?>
                        </div>
                        <?php if(!empty($chapter["chunks"])): ?>
                            <div class="section_content">
                                <div class="section_translator">
                                    <div class="section_translator_name">
                                        <span style="color: #2ea02e; font-weight: bold"><?php echo Language::show("translator", "Members") ?>: </span>
                                        <span><?php echo $data["members"][$chapter["memberID"]] ?></span>
                                    </div>
                                    <div class="section_translator_progress_bar">
                                        <div class="progress">
                                            <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $progress ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em; width: <?php echo $progress."%" ?>">
                                                <?php echo $progress."%" ?>
                                            </div>
                                        </div>
                                        <div class="<?php echo $progress >= 100 ? "glyphicon glyphicon-ok" : "" ?> finished_icon"></div>
                                        <div class="clear"></div>
                                    </div>
                                    <div class="section_translator_chunks">
                                        <div style="font-weight: bold"><?php echo Language::show("chunks_number", "Events", array(sizeof($chapter["chunks"]))) ?>:</div>
                                        <?php foreach ($chapter["chunks"] as $index => $chunk):?>
                                            <div class="section_translator_chunk">
                                                <?php echo Language::show("chunk_number", "Events", array($chunk[0]." - ".$chunk[sizeof($chunk)-1])); ?>
                                                <?php if(array_key_exists($index, (array)$chapter["chunksData"]) && $chapter["chunksData"][$index]->translateDone) {
                                                    echo Language::show("chunk_finished", "Events");
                                                } ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="checker_kwc <?php echo $chapter["kwc"]["state"] == "not_started" ? "not_started_block" : "" ?>">
                                    <div class="checker_header">
                                        <span class="checker_label <?php echo $chapter["kwc"]["state"] == "not_started" ? "not_started_label" : "" ?>"><?php echo Language::show("checker_kwc", "Events") ?>:</span>
                                        <span class="checker_name <?php echo $chapter["kwc"]["state"] == "not_started" ? "not_started_name" : "" ?>"><?php echo $data["members"][$chapter["kwc"]["checkerID"]] ?></span>
                                    </div>
                                    <div class="checker_status">
                                        <span style="font-weight: bold;"><?php echo Language::show("checker_status", "Events") ?>:</span>
                            <span class="state_active <?php echo $chapter["kwc"]["state"] == "not_started" ? "not_started_name" : "" ?>">
                                <?php echo Language::show("checker_status_".$chapter["kwc"]["state"], "Events") ?>
                                <span class="<?php echo $chapter["kwc"]["state"] == "finished" ? "glyphicon glyphicon-ok" : "" ?>"></span>
                            </span>
                                    </div>
                                </div>

                                <div class="checker_crc <?php echo $chapter["crc"]["state"] == "not_started" ? "not_started_block" : "" ?>">
                                    <div class="checker_header">
                                        <span class="checker_label <?php echo $chapter["crc"]["state"] == "not_started" ? "not_started_label" : "" ?>"><?php echo Language::show("checker_crc", "Events") ?>:</span>
                                        <span class="checker_name <?php echo $chapter["crc"]["state"] == "not_started" ? "not_started_name" : "" ?>"><?php echo $data["members"][$chapter["crc"]["checkerID"]] ?></span>
                                    </div>
                                    <div class="checker_status">
                                        <span style="font-weight: bold;"><?php echo Language::show("checker_status", "Events") ?>:</span>
                            <span class="state_active <?php echo $chapter["crc"]["state"] == "not_started" ? "not_started_name" : "" ?>">
                                <?php echo Language::show("checker_status_".$chapter["crc"]["state"], "Events") ?>
                                <span class="<?php echo $chapter["crc"]["state"] == "finished" ? "glyphicon glyphicon-ok" : "" ?>"></span>
                            </span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="section_header">
                            <span class="section_arrow glyphicon glyphicon-triangle-right"></span>
                            <span class="section_title">Check level 2</span>
                        </div>
                        <div class="section_content">
                            Not implemented
                        </div>

                        <div class="section_header">
                            <span class="section_arrow glyphicon glyphicon-triangle-right"></span>
                            <span class="section_title">Check level 3</span>
                        </div>
                        <div class="section_content">
                            Not implemented
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="col-sm-5 members_list">
            <div class="members_title"><?php echo Language::show("event_participants", "Events") ?>:</div>
            <?php foreach ($data["members"] as $id => $member): ?>
                <?php if($id == "na") continue; ?>
                <div class="member_item" data="<?php echo $id ?>">
                    <span class="online_indicator glyphicon glyphicon-record">&nbsp;</span>
                    <span class="member_uname"><?php echo $member ?></span>
                    <span class="member_admin"> (<?php echo Language::show("facilitator", "Events") ?>)</span>
                    <span class="online_status"><?php echo Language::show("status_online", "Events") ?></span>
                    <span class="offline_status"><?php echo Language::show("status_offline", "Events") ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        var memberID = <?php echo Session::get('memberID') ;?>;
        var eventID = <?php echo $data["event"][0]->eventID; ?>;
        var chkMemberID = 0;
        var aT = '<?php echo Session::get('authToken'); ?>';
        var step = '<?php //echo $data["event"][0]->step; ?>';
        var disableChat = true;
        var isAdmin = <?php echo (integer)$data["isAdmin"]; ?>;
    </script>

    <?php if($data["isAdmin"]): ?>
    <div id="chat_container" class="closed info">
        <div id="chat_new_msgs" class="chat_new_msgs"></div>
        <div id="chat_hide" class="glyphicon glyphicon-chevron-left"></div>

        <div class="chat panel panel-info">
            <div class="chat_tabs panel-heading">
                <div class="row">
                    <div id="evnt" class="col-sm-4 chat_tab">Event</div>
                </div>
            </div>
            <ul id="evnt_messages" class="chat_msgs info"></ul>
            <form action="" class="form-inline">
                <div class="form-group">
                    <textarea id="m" class="form-control"></textarea>
                    <input type="hidden" id="chat_type" value="evnt" />
                </div>
            </form>
        </div>

        <div class="members_online info panel panel-info">
            <div class="panel-heading">Members Online</div>
            <ul id="online" class="panel-body"></ul>
        </div>

        <div class="clear"></div>
    </div>

    <!-- Audio for missed chat messages -->
    <audio id="missedMsg">
        <source src="<?php echo \Helpers\Url::templatePath()?>sounds/missed.ogg" type="audio/ogg" />
    </audio>
<?php endif; ?>

    <script src="https://cdn.socket.io/socket.io-1.4.5.js"></script>
    <script src="<?php echo \Helpers\Url::templatePath()?>js/chat.js"></script>

    <?php
endif;
?>