<?php foreach ($data["chapters"] as $key => $chapter):?>
    <?php
    if(empty($chapter)) {
        echo '<div class="chapter_item"><div class="chapter_number">'.__("chapter_number", array($key)).'</div></div>';
        continue;
    }
    ?>
    <div class="chapter_item">
        <div class="chapter_number"><?php echo __("chapter_number", array($key)) ?></div>
        <div class="chapter_accordion">
            <div class="section_header" data="<?php echo "sec_".$key?>">
                <div class="section_arrow glyphicon glyphicon-triangle-right"></div>
                <div class="section_title">Draft 1 (Check level 1)</div>
                <div class="section_translator_progress_bar">
                    <div class="progress <?php echo $chapter["progress"] <= 0 ? "zero" : ""?>">
                        <div class="progress-bar progress-bar-success" role="progressbar"
                             aria-valuenow="<?php echo floor($chapter["progress"]) ?>" aria-valuemin="0" aria-valuemax="100"
                             style="min-width: 0em; width: <?php echo floor($chapter["progress"])."%" ?>">
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
                        <div style="font-weight: bold"><?php echo sizeof($chapter["chunks"]) > 0 ? __("chunks_number", array(sizeof($chapter["chunks"]))).":" : __("no_chunks_number") ?></div>
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