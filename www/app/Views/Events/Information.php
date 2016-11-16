<?php
use Helpers\Session;

echo Error::display($error);
if(!isset($error)):
    ?>

    <div class="back_link">
        <?php if(isset($_SERVER["HTTP_REFERER"])): ?>
        <a href="<?php echo $_SERVER["HTTP_REFERER"] ?>"><?php echo __("go_back") ?></a>
        <?php endif; ?>
    </div>

    <div>
        <div class="book_title"><?php echo $data["event"][0]->name ?></div>
        <div class="project_title"><?php echo __($data["event"][0]->bookProject)." - ".$data["event"][0]->tLang ?></div>
        <div class="overall_progress_bar">
            <h3><?php echo __("progress_all") ?></h3>
            <div class="progress progress_all <?php echo $data["overall_progress"] <= 0 ? "zero" : ""?>">
                <div class="progress-bar progress-bar-success" role="progressbar"
                     aria-valuenow="<?php echo floor($data["overall_progress"]) ?>"
                     aria-valuemin="0" aria-valuemax="100" style="min-width: 0em; width: <?php echo floor($data["overall_progress"])."%" ?>">
                    <?php echo floor($data["overall_progress"])."%" ?>
                </div>
            </div>
        </div>
        <div class="clear"></div>
    </div>

    <div class="row">
        <div class="chapter_list col-sm-7">
            <?php foreach ($data["chapters"] as $key => $chapter):?>
                <?php
                if(empty($chapter)) {
                    echo '<div class="chapter_item"><div class="chapter_number">'.__("chapter_number", [$key]).'</div></div>';
                    continue;
                }
                ?>
                <div class="chapter_item">
                    <div class="chapter_number"><?php echo __("chapter_number", [$key]) ?></div>
                    <div class="chapter_accordion">
                        <div class="section_header" data="<?php echo "sec_".$key?>">
                            <div class="section_arrow glyphicon glyphicon-triangle-right"></div>
                            <div class="section_title">Draft 1 (Check level 1)</div>
                            <div class="section_translator_progress_bar">
                                <div class="progress <?php echo $chapter["progress"] <= 0 ? "zero" : ""?>">
                                    <div class="progress-bar progress-bar-success" role="progressbar"
                                         aria-valuenow="<?php echo floor($chapter["progress"]) ?>" aria-valuemin="0"
                                         aria-valuemax="100" style="min-width: 0em; width: <?php echo floor($chapter["progress"])."%" ?>">
                                        <?php echo floor($chapter["progress"])."%" ?>
                                    </div>
                                </div>
                                <div class="<?php echo $chapter["progress"] >= 100 ? "glyphicon glyphicon-ok" : "" ?> finished_icon"></div>
                                <div class="clear"></div>
                            </div>
                            <div class="clear"></div>
                        </div>
                        <div class="section_content">
                            <div class="section_translator">
                                <div class="section_translator_name">
                                    <span style="color: #2ea02e; font-weight: bold"><?php echo __("translator") ?>: </span>
                                    <span><?php echo $data["members"][$chapter["memberID"]] ?></span>
                                </div>
                                <div class="section_translator_step">
                                    <span style="color: #4084ff; font-weight: bold"><?php echo __("current_step") ?>: </span>
                                    <span><?php echo isset($chapter["step"]) ? __($chapter["step"]) : "N/A"?></span>
                                </div>
                                <div class="section_translator_chunks">
                                    <div style="font-weight: bold"><?php echo sizeof($chapter["chunks"]) > 0 ? __("chunks_number", [sizeof($chapter["chunks"])]).":" : __("no_chunks_number") ?></div>
                                    <?php if(isset($chapter["chunks"])): ?>
                                        <?php foreach ($chapter["chunks"] as $index => $chunk):?>
                                            <div class="section_translator_chunk">
                                                <?php echo __("chunk_number", array($chunk[0]." - ".$chunk[sizeof($chunk)-1])); ?>
                                                <?php if(array_key_exists($index, (array)$chapter["chunksData"])) {
                                                    echo __("chunk_finished");
                                                } ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="checker_verb <?php echo $chapter["verb"]["state"] == "not_started" ? "not_started_block" : "" ?>">
                                <div class="checker_header">
                                    <span class="checker_label <?php echo $chapter["verb"]["state"] == "not_started" ? "not_started_label" : "" ?>"><?php echo __("checker_verb") ?>:</span>
                                    <span class="checker_name <?php echo $chapter["verb"]["state"] == "not_started" ? "not_started_name" : "" ?>"><?php echo $data["members"][$chapter["verb"]["checkerID"]] ?></span>
                                </div>
                                <div class="checker_status">
                                    <span style="font-weight: bold;"><?php echo __("checker_status") ?>:</span>
                                    <span class="state_active <?php echo $chapter["verb"]["state"] == "not_started" ? "not_started_name" : "" ?>">
                                            <?php echo __("checker_status_".$chapter["verb"]["state"]) ?>
                                        <span class="<?php echo $chapter["verb"]["state"] == "finished" ? "glyphicon glyphicon-ok" : "" ?>"></span>
                                        </span>
                                </div>
                            </div>

                            <div class="checker_peer <?php echo $chapter["peer"]["state"] == "not_started" ? "not_started_block" : "" ?>">
                                <div class="checker_header">
                                    <span class="checker_label <?php echo $chapter["peer"]["state"] == "not_started" ? "not_started_label" : "" ?>"><?php echo __("checker_peer") ?>:</span>
                                    <span class="checker_name <?php echo $chapter["peer"]["state"] == "not_started" ? "not_started_name" : "" ?>"><?php echo $data["members"][$chapter["peer"]["checkerID"]] ?></span>
                                </div>
                                <div class="checker_status">
                                    <span style="font-weight: bold;"><?php echo __("checker_status") ?>:</span>
                                    <span class="state_active <?php echo $chapter["peer"]["state"] == "not_started" ? "not_started_name" : "" ?>">
                                            <?php echo __("checker_status_".$chapter["peer"]["state"]) ?>
                                        <span class="<?php echo $chapter["peer"]["state"] == "finished" ? "glyphicon glyphicon-ok" : "" ?>"></span>
                                        </span>
                                </div>
                            </div>

                            <div class="checker_kwc <?php echo $chapter["kwc"]["state"] == "not_started" ? "not_started_block" : "" ?>">
                                <div class="checker_header">
                                    <span class="checker_label <?php echo $chapter["kwc"]["state"] == "not_started" ? "not_started_label" : "" ?>"><?php echo __("checker_kwc") ?>:</span>
                                    <span class="checker_name <?php echo $chapter["kwc"]["state"] == "not_started" ? "not_started_name" : "" ?>"><?php echo $data["members"][$chapter["kwc"]["checkerID"]] ?></span>
                                </div>
                                <div class="checker_status">
                                    <span style="font-weight: bold;"><?php echo __("checker_status") ?>:</span>
                                    <span class="state_active <?php echo $chapter["kwc"]["state"] == "not_started" ? "not_started_name" : "" ?>">
                                            <?php echo __("checker_status_".$chapter["kwc"]["state"]) ?>
                                        <span class="<?php echo $chapter["kwc"]["state"] == "finished" ? "glyphicon glyphicon-ok" : "" ?>"></span>
                                        </span>
                                </div>
                            </div>

                            <div class="checker_crc <?php echo $chapter["crc"]["state"] == "not_started" ? "not_started_block" : "" ?>">
                                <div class="checker_header">
                                    <span class="checker_label <?php echo $chapter["crc"]["state"] == "not_started" ? "not_started_label" : "" ?>"><?php echo __("checker_crc") ?>:</span>
                                    <span class="checker_name <?php echo $chapter["crc"]["state"] == "not_started" ? "not_started_name" : "" ?>"><?php echo $data["members"][$chapter["crc"]["checkerID"]] ?></span>
                                </div>
                                <div class="checker_status">
                                    <span style="font-weight: bold;"><?php echo __("checker_status") ?>:</span>
                                    <span class="state_active <?php echo $chapter["crc"]["state"] == "not_started" ? "not_started_name" : "" ?>">
                                            <?php echo __("checker_status_".$chapter["crc"]["state"]) ?>
                                        <span class="<?php echo $chapter["crc"]["state"] == "finished" ? "glyphicon glyphicon-ok" : "" ?>"></span>
                                        </span>
                                </div>
                            </div>
                        </div>

                        <div class="section_header" data="<?php echo "sec_l2_".$key?>">
                            <div class="section_arrow glyphicon glyphicon-triangle-right"></div>
                            <div class="section_title">Check level 2</div>
                            <div class="clear"></div>
                        </div>
                        <div class="section_content">
                            Not implemented
                        </div>

                        <div class="section_header" data="<?php echo "sec_l3_".$key?>">
                            <div class="section_arrow glyphicon glyphicon-triangle-right"></div>
                            <div class="section_title">Check level 3</div>
                            <div class="clear"></div>
                        </div>
                        <div class="section_content">
                            Not implemented
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="col-sm-5 members_list">
            <div class="members_title"><?php echo __("event_participants") ?>:</div>
            <?php foreach ($data["members"] as $id => $member): ?>
                <?php if($id == "na") continue; ?>
                <div class="member_item" data="<?php echo $id ?>">
                    <span class="online_indicator glyphicon glyphicon-record">&nbsp;</span>
                    <span class="member_uname"><?php echo $member ?></span>
                    <span class="member_admin"> <?php echo in_array($id, $data["admins"]) ? "(".__("facilitator").")" : "" ?></span>
                    <span class="online_status"><?php echo __("status_online") ?></span>
                    <span class="offline_status"><?php echo __("status_offline") ?></span>
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
        var isAdmin = <?php echo (integer)$data["isAdmin"]; ?>;
        var disableChat = true;
        var isChecker = false;
        var isInfoPage = true;
    </script>

    <?php if($data["isAdmin"]): ?>
    <div id="chat_container" class="closed info">
        <div id="chat_new_msgs" class="chat_new_msgs"></div>
        <div id="chat_hide" class="glyphicon glyphicon-chevron-down"> <?php echo __("chat") ?></div>

        <div class="chat panel panel-info">
            <div class="chat_tabs panel-heading">
                <div class="row">
                    <div id="evnt" class="col-sm-4 chat_tab">
                        <div><?php echo __("event_tab_title") ?></div>
                        <div class="missed"></div>
                    </div>
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
        <source src="<?php echo template_url("sounds/missed.ogg")?>" type="audio/ogg" />
    </audio>
<?php endif; ?>

    <script src="<?php echo template_url("js/socket.io-1.4.5.js")?>"></script>
    <script src="<?php echo template_url("js/chat-plugin.js")?>"></script>
    <script src="<?php echo template_url("js/socket.js")?>"></script>

    <?php
endif;
?>