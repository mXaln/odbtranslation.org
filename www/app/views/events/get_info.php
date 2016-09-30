<?php
use Core\Language;
use Helpers\Constants\EventSteps;
use Helpers\Session;
?>

<?php foreach ($data["chapters"] as $key => $chapter):?>
    <div class="chapter_item">
        <div class="chapter_number"><?php echo Language::show("chapter_number", "Events", array($key)) ?></div>
        <div class="chapter_accordion">
            <div class="section_header" data="<?php echo "sec_".$key?>">
                <div class="section_arrow glyphicon glyphicon-triangle-right"></div>
                <div class="section_title">Draft 1 (Check level 1)</div>
                <div class="section_translator_progress_bar">
                    <div class="progress <?php echo $chapter["progress"] <= 0 ? "zero" : ""?>">
                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $chapter["progress"] ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 0em; width: <?php echo $chapter["progress"]."%" ?>">
                            <?php echo round($chapter["progress"])."%" ?>
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
                        <span style="color: #2ea02e; font-weight: bold"><?php echo Language::show("translator", "Members") ?>: </span>
                        <span><?php echo $data["members"][$chapter["memberID"]] ?></span>
                    </div>
                    <div class="section_translator_step">
                        <span style="color: #4084ff; font-weight: bold"><?php echo Language::show("current_step", "Events") ?>: </span>
                        <span><?php echo isset($chapter["step"]) ? Language::show($chapter["step"], "Events") : "N/A"?></span>
                    </div>
                    <div class="section_translator_chunks">
                        <div style="font-weight: bold"><?php echo sizeof($chapter["chunks"]) > 0 ? Language::show("chunks_number", "Events", array(sizeof($chapter["chunks"]))).":" : Language::show("no_chunks_number", "Events") ?></div>
                        <?php if(isset($chapter["chunks"])): ?>
                            <?php foreach ($chapter["chunks"] as $index => $chunk):?>
                                <div class="section_translator_chunk">
                                    <?php echo Language::show("chunk_number", "Events", array($chunk[0]." - ".$chunk[sizeof($chunk)-1])); ?>
                                    <?php if(array_key_exists($index, (array)$chapter["chunksData"]) && $chapter["chunksData"][$index]->translateDone) {
                                        echo Language::show("chunk_finished", "Events");
                                    } ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="checker_peer <?php echo $chapter["peer"]["state"] == "not_started" ? "not_started_block" : "" ?>">
                    <div class="checker_header">
                        <span class="checker_label <?php echo $chapter["peer"]["state"] == "not_started" ? "not_started_label" : "" ?>"><?php echo Language::show("checker_peer", "Events") ?>:</span>
                        <span class="checker_name <?php echo $chapter["peer"]["state"] == "not_started" ? "not_started_name" : "" ?>"><?php echo $data["members"][$chapter["peer"]["checkerID"]] ?></span>
                    </div>
                    <div class="checker_status">
                        <span style="font-weight: bold;"><?php echo Language::show("checker_status", "Events") ?>:</span>
                        <span class="state_active <?php echo $chapter["peer"]["state"] == "not_started" ? "not_started_name" : "" ?>">
                                    <?php echo Language::show("checker_status_".$chapter["peer"]["state"], "Events") ?>
                            <span class="<?php echo $chapter["peer"]["state"] == "finished" ? "glyphicon glyphicon-ok" : "" ?>"></span>
                                </span>
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