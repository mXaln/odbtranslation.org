<div class="back_link">
    <?php if(isset($_SERVER["HTTP_REFERER"])): ?>
        <a href="<?php echo $_SERVER["HTTP_REFERER"] ?>"><?php echo __("go_back") ?></a>
    <?php endif; ?>
    <div class="demo_title"><?php echo __("demo") ?></div>
</div>

<div>
    <div class="book_title">2 Timothy</div>
    <div class="project_title"><?php echo __("ulb") ?> - English</div>
    <div class="overall_progress_bar">
        <h3>Overall Progress</h3>
        <div class="progress progress_all ">
            <div style="min-width: 0em; width: 19%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="18.75" role="progressbar" class="progress-bar progress-bar-success">
                18.75%
            </div>
        </div>
    </div>
    <div class="clear"></div>
</div>

<div class="row">
    <div class="chapter_list col-sm-7">
        <div class="chapter_item">
            <div class="chapter_number"><?php echo __("chapter_number", [1]) ?></div>
            <div class="chapter_accordion">
                <div data="sec_1" class="section_header">
                    <div class="section_arrow glyphicon glyphicon-triangle-right"></div>
                    <div class="section_title">Draft 1 (Check level 1)</div>
                    <div class="section_translator_progress_bar">
                        <div class="progress ">
                            <div style="min-width: 0em; width: 75%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="75" role="progressbar" class="progress-bar progress-bar-success">
                                75%
                            </div>
                        </div>
                        <div class=" finished_icon"></div>
                        <div class="clear"></div>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="section_content">
                    <div class="section_translator">
                        <div class="section_translator_name">
                            <span style="color: #2ea02e; font-weight: bold"><?php echo __("translator") ?>: </span>
                            <span>mSimpson</span>
                        </div>
                        <div class="section_translator_step">
                            <span style="color: #4084ff; font-weight: bold"><?php echo __("current_step") ?>: </span>
                            <span><?php echo __("content-review") ?></span>
                        </div>
                        <div class="section_translator_chunks">
                            <div style="font-weight: bold"><?php echo __("chunks_number", [3]) ?>:</div>
                            <div class="section_translator_chunk">
                                <?php echo __("chunk_number", ["1 - 5"]); ?> &nbsp;&nbsp;<?php echo __("chunk_finished") ?>
                            </div>
                            <div class="section_translator_chunk">
                                <?php echo __("chunk_number", ["6 - 12"]); ?> &nbsp;&nbsp;<?php echo __("chunk_finished") ?>
                            </div>
                            <div class="section_translator_chunk">
                                <?php echo __("chunk_number", ["13 - 18"]); ?> &nbsp;&nbsp;<?php echo __("chunk_finished") ?>
                            </div>
                        </div>
                    </div>

                    <div class="checker_verb ">
                        <div class="checker_header">
                            <span class="checker_label "><?php echo __("checker_verb") ?>:</span>
                            <span class="checker_name ">mpat1977</span>
                        </div>
                        <div class="checker_status">
                            <span style="font-weight: bold;"><?php echo __("checker_status") ?>:</span>
                            <span class="state_active ">
                                <?php echo __("checker_status_finished") ?> <span class="glyphicon glyphicon-ok"></span>
                            </span>
                        </div>
                    </div>

                    <div class="checker_peer ">
                        <div class="checker_header">
                            <span class="checker_label "><?php echo __("checker_peer") ?>:</span>
                            <span class="checker_name ">mpat1977</span>
                        </div>
                        <div class="checker_status">
                            <span style="font-weight: bold;"><?php echo __("checker_status") ?>:</span>
                            <span class="state_active ">
                                <?php echo __("checker_status_finished") ?> <span class="glyphicon glyphicon-ok"></span>
                            </span>
                        </div>
                    </div>

                    <div class="checker_kwc ">
                        <div class="checker_header">
                            <span class="checker_label "><?php echo __("checker_kwc") ?>:</span>
                            <span class="checker_name ">mpat1977</span>
                        </div>
                        <div class="checker_status">
                            <span style="font-weight: bold;"><?php echo __("checker_status") ?>:</span>
                            <span class="state_active ">
                                <?php echo __("checker_status_finished") ?> <span class="glyphicon glyphicon-ok"></span>
                            </span>
                        </div>
                    </div>

                    <div class="checker_crc not_started_block">
                        <div class="checker_header">
                            <span class="checker_label not_started_label"><?php echo __("checker_crc") ?>:</span>
                            <span class="checker_name not_started_name"><?php echo __("not_available") ?></span>
                        </div>
                        <div class="checker_status">
                            <span style="font-weight: bold;"><?php echo __("checker_status") ?>:</span>
                            <span class="state_active not_started_name">
                                <?php echo __("checker_status_not_started") ?> <span class=""></span>
                            </span>
                        </div>
                    </div>
                </div>

                <div data="sec_l2_1" class="section_header">
                    <div class="section_arrow glyphicon glyphicon-triangle-right"></div>
                    <div class="section_title">Check level 2</div>
                    <div class="clear"></div>
                </div>
                <div class="section_content">
                    Not implemented
                </div>

                <div data="sec_l3_1" class="section_header">
                    <div class="section_arrow glyphicon glyphicon-triangle-right"></div>
                    <div class="section_title">Check level 3</div>
                    <div class="clear"></div>
                </div>
                <div class="section_content">
                    Not implemented
                </div>
            </div>
        </div>
        <div class="chapter_item">
            <div class="chapter_number"><?php echo __("chapter_number", [2]) ?></div>
            <div class="chapter_accordion">
                <div data="sec_2" class="section_header">
                    <div class="section_arrow glyphicon glyphicon-triangle-right"></div>
                    <div class="section_title">Draft 1 (Check level 1)</div>
                    <div class="section_translator_progress_bar">
                        <div class="progress ">
                            <div style="min-width: 0em; width: 6%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="6.25" role="progressbar" class="progress-bar progress-bar-success">
                                6.25%
                            </div>
                        </div>
                        <div class=" finished_icon"></div>
                        <div class="clear"></div>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="section_content">
                    <div class="section_translator">
                        <div class="section_translator_name">
                            <span style="color: #2ea02e; font-weight: bold"><?php echo __("translator") ?>: </span>
                            <span>mpat1977</span>
                        </div>
                        <div class="section_translator_step">
                            <span style="color: #4084ff; font-weight: bold"><?php echo __("current_step") ?>: </span>
                            <span><?php echo __("blind-draft") ?></span>
                        </div>
                        <div class="section_translator_chunks">
                            <div style="font-weight: bold"><?php echo __("chunks_number", [4]) ?>:</div>
                            <div class="section_translator_chunk">
                                <?php echo __("chunk_number", ["1 - 7"]); ?> &nbsp;&nbsp;<?php echo __("chunk_finished") ?>
                            </div>
                            <div class="section_translator_chunk">
                                <?php echo __("chunk_number", ["8 - 14"]); ?>
                            </div>
                            <div class="section_translator_chunk">
                                <?php echo __("chunk_number", ["15 - 20"]); ?>
                            </div>
                            <div class="section_translator_chunk">
                                <?php echo __("chunk_number", ["21 - 26"]); ?>
                            </div>
                        </div>
                    </div>

                    <div class="checker_verb ">
                        <div class="checker_header">
                            <span class="checker_label "><?php echo __("checker_verb") ?>:</span>
                            <span class="checker_name ">mSimpson</span>
                        </div>
                        <div class="checker_status">
                            <span style="font-weight: bold;"><?php echo __("checker_status") ?>:</span>
                            <span class="state_active ">
                                <?php echo __("checker_status_finished") ?> <span class="glyphicon glyphicon-ok"></span>
                            </span>
                        </div>
                    </div>

                    <div class="checker_peer not_started_block">
                        <div class="checker_header">
                            <span class="checker_label not_started_label"><?php echo __("checker_peer") ?>:</span>
                            <span class="checker_name not_started_name"><?php echo __("not_available") ?></span>
                        </div>
                        <div class="checker_status">
                            <span style="font-weight: bold;"><?php echo __("checker_status") ?>:</span>
                            <span class="state_active not_started_name">
                                <?php echo __("checker_status_not_started") ?> <span class=""></span>
                            </span>
                        </div>
                    </div>

                    <div class="checker_kwc not_started_block">
                        <div class="checker_header">
                            <span class="checker_label not_started_label"><?php echo __("checker_kwc") ?>:</span>
                            <span class="checker_name not_started_name"><?php echo __("not_available") ?></span>
                        </div>
                        <div class="checker_status">
                            <span style="font-weight: bold;"><?php echo __("checker_status") ?>:</span>
                            <span class="state_active not_started_name">
                                <?php echo __("checker_status_not_started") ?> <span class=""></span>
                            </span>
                        </div>
                    </div>

                    <div class="checker_crc not_started_block">
                        <div class="checker_header">
                            <span class="checker_label not_started_label"><?php echo __("checker_crc") ?>:</span>
                            <span class="checker_name not_started_name"><?php echo __("not_available") ?></span>
                        </div>
                        <div class="checker_status">
                            <span style="font-weight: bold;"><?php echo __("checker_status") ?>:</span>
                            <span class="state_active not_started_name">
                                <?php echo __("checker_status_not_started") ?> <span class=""></span>
                            </span>
                        </div>
                    </div>
                </div>

                <div data="sec_l2_2" class="section_header">
                    <div class="section_arrow glyphicon glyphicon-triangle-right"></div>
                    <div class="section_title">Check level 2</div>
                    <div class="clear"></div>
                </div>
                <div class="section_content">
                    Not implemented
                </div>

                <div data="sec_l3_2" class="section_header">
                    <div class="section_arrow glyphicon glyphicon-triangle-right"></div>
                    <div class="section_title">Check level 3</div>
                    <div class="clear"></div>
                </div>
                <div class="section_content">
                    Not implemented
                </div>
            </div>
        </div>
        <div class="chapter_item">
            <div class="chapter_number"><?php echo __("chapter_number", [3]) ?></div>
            <div class="chapter_accordion">
                <div data="sec_3" class="section_header">
                    <div class="section_arrow glyphicon glyphicon-triangle-right"></div>
                    <div class="section_title">Draft 1 (Check level 1)</div>
                    <div class="section_translator_progress_bar">
                        <div class="progress zero">
                            <div style="min-width: 0em; width: 0%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="0" role="progressbar" class="progress-bar progress-bar-success">
                                0%
                            </div>
                        </div>
                        <div class=" finished_icon"></div>
                        <div class="clear"></div>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="section_content">
                    <div class="section_translator">
                        <div class="section_translator_name">
                            <span style="color: #2ea02e; font-weight: bold"><?php echo __("translator") ?>: </span>
                            <span>mSimpson</span>
                        </div>
                        <div class="section_translator_step">
                            <span style="color: #4084ff; font-weight: bold"><?php echo __("current_step") ?>: </span>
                            <span><?php echo __("pray") ?></span>
                        </div>
                        <div class="section_translator_chunks">
                            <div style="font-weight: bold"><?php echo __("no_chunks_number") ?></div>
                        </div>
                    </div>

                    <div class="checker_verb not_started_block">
                        <div class="checker_header">
                            <span class="checker_label not_started_label"><?php echo __("checker_verb") ?>:</span>
                            <span class="checker_name not_started_name"><?php echo __("not_available") ?></span>
                        </div>
                        <div class="checker_status">
                            <span style="font-weight: bold;"><?php echo __("checker_status") ?>:</span>
                            <span class="state_active not_started_name">
                                <?php echo __("checker_status_not_started") ?> <span class=""></span>
                            </span>
                        </div>
                    </div>

                    <div class="checker_peer not_started_block">
                        <div class="checker_header">
                            <span class="checker_label not_started_label"><?php echo __("checker_peer") ?>:</span>
                            <span class="checker_name not_started_name"><?php echo __("not_available") ?></span>
                        </div>
                        <div class="checker_status">
                            <span style="font-weight: bold;"><?php echo __("checker_status") ?>:</span>
                            <span class="state_active not_started_name">
                                <?php echo __("checker_status_not_started") ?> <span class=""></span>
                            </span>
                        </div>
                    </div>

                    <div class="checker_kwc not_started_block">
                        <div class="checker_header">
                            <span class="checker_label not_started_label"><?php echo __("checker_kwc") ?>:</span>
                            <span class="checker_name not_started_name"><?php echo __("not_available") ?></span>
                        </div>
                        <div class="checker_status">
                            <span style="font-weight: bold;"><?php echo __("checker_status") ?>:</span>
                            <span class="state_active not_started_name">
                                <?php echo __("checker_status_not_started") ?> <span class=""></span>
                            </span>
                        </div>
                    </div>

                    <div class="checker_crc not_started_block">
                        <div class="checker_header">
                            <span class="checker_label not_started_label"><?php echo __("checker_crc") ?>:</span>
                            <span class="checker_name not_started_name"><?php echo __("not_available") ?></span>
                        </div>
                        <div class="checker_status">
                            <span style="font-weight: bold;"><?php echo __("checker_status") ?>:</span>
                            <span class="state_active not_started_name">
                                <?php echo __("checker_status_not_started") ?> <span class=""></span>
                            </span>
                        </div>
                    </div>
                </div>

                <div data="sec_l2_3" class="section_header">
                    <div class="section_arrow glyphicon glyphicon-triangle-right"></div>
                    <div class="section_title">Check level 2</div>
                    <div class="clear"></div>
                </div>
                <div class="section_content">
                    Not implemented
                </div>

                <div data="sec_l3_3" class="section_header">
                    <div class="section_arrow glyphicon glyphicon-triangle-right"></div>
                    <div class="section_title">Check level 3</div>
                    <div class="clear"></div>
                </div>
                <div class="section_content">
                    Not implemented
                </div>
            </div>
        </div>
        <div class="chapter_item">
            <div class="chapter_number"><?php echo __("chapter_number", [4]) ?></div>
            <div class="chapter_accordion">
                <div data="sec_4" class="section_header">
                    <div class="section_arrow glyphicon glyphicon-triangle-right"></div>
                    <div class="section_title">Draft 1 (Check level 1)</div>
                    <div class="section_translator_progress_bar">
                        <div class="progress zero">
                            <div style="min-width: 0em; width: 0%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="0" role="progressbar" class="progress-bar progress-bar-success">
                                0%
                            </div>
                        </div>
                        <div class=" finished_icon"></div>
                        <div class="clear"></div>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="section_content">
                    <div class="section_translator">
                        <div class="section_translator_name">
                            <span style="color: #2ea02e; font-weight: bold"><?php echo __("translator") ?>: </span>
                            <span>mpat1977</span>
                        </div>
                        <div class="section_translator_step">
                            <span style="color: #4084ff; font-weight: bold"><?php echo __("current_step") ?>: </span>
                            <span><?php echo __("pray") ?></span>
                        </div>
                        <div class="section_translator_chunks">
                            <div style="font-weight: bold"><?php echo __("no_chunks_number") ?></div>
                        </div>
                    </div>

                    <div class="checker_verb not_started_block">
                        <div class="checker_header">
                            <span class="checker_label not_started_label"><?php echo __("checker_verb") ?>:</span>
                            <span class="checker_name not_started_name"><?php echo __("not_available") ?></span>
                        </div>
                        <div class="checker_status">
                            <span style="font-weight: bold;"><?php echo __("checker_status") ?>:</span>
                            <span class="state_active not_started_name">
                                <?php echo __("checker_status_not_started") ?> <span class=""></span>
                            </span>
                        </div>
                    </div>

                    <div class="checker_peer not_started_block">
                        <div class="checker_header">
                            <span class="checker_label not_started_label"><?php echo __("checker_peer") ?>:</span>
                            <span class="checker_name not_started_name"><?php echo __("not_available") ?></span>
                        </div>
                        <div class="checker_status">
                            <span style="font-weight: bold;"><?php echo __("checker_status") ?>:</span>
                            <span class="state_active not_started_name">
                                <?php echo __("checker_status_not_started") ?> <span class=""></span>
                            </span>
                        </div>
                    </div>

                    <div class="checker_kwc not_started_block">
                        <div class="checker_header">
                            <span class="checker_label not_started_label"><?php echo __("checker_kwc") ?>:</span>
                            <span class="checker_name not_started_name"><?php echo __("not_available") ?></span>
                        </div>
                        <div class="checker_status">
                            <span style="font-weight: bold;"><?php echo __("checker_status") ?>:</span>
                            <span class="state_active not_started_name">
                                <?php echo __("checker_status_not_started") ?> <span class=""></span>
                            </span>
                        </div>
                    </div>

                    <div class="checker_crc not_started_block">
                        <div class="checker_header">
                            <span class="checker_label not_started_label"><?php echo __("checker_crc") ?>:</span>
                            <span class="checker_name not_started_name"><?php echo __("not_available") ?></span>
                        </div>
                        <div class="checker_status">
                            <span style="font-weight: bold;"><?php echo __("checker_status") ?>:</span>
                            <span class="state_active not_started_name">
                                <?php echo __("checker_status_not_started") ?> <span class=""></span>
                            </span>
                        </div>
                    </div>
                </div>

                <div data="sec_l2_4" class="section_header">
                    <div class="section_arrow glyphicon glyphicon-triangle-right"></div>
                    <div class="section_title">Check level 2</div>
                    <div class="clear"></div>
                </div>
                <div class="section_content">
                    Not implemented
                </div>

                <div data="sec_l3_4" class="section_header">
                    <div class="section_arrow glyphicon glyphicon-triangle-right"></div>
                    <div class="section_title">Check level 3</div>
                    <div class="clear"></div>
                </div>
                <div class="section_content">
                    Not implemented
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-5 members_list">
        <div class="members_title"><?php echo __("event_participants") ?>:</div>
        <div data="16" class="member_item">
            <span class="online_indicator glyphicon glyphicon-record online">&nbsp;</span>
            <span class="member_uname">mSimpson</span>
            <span class="member_admin"> </span>
            <span class="online_status" style="display: inline;"><?php echo __("status_online") ?></span>
            <span class="offline_status" style="display: none;"><?php echo __("status_offline") ?></span>
        </div>
        <div data="7" class="member_item">
            <span class="online_indicator glyphicon glyphicon-record online">&nbsp;</span>
            <span class="member_uname">mpat1977</span>
            <span class="member_admin"> </span>
            <span class="online_status" style="display: inline;"><?php echo __("status_online") ?></span>
            <span class="offline_status" style="display: none;"><?php echo __("status_offline") ?></span>
        </div>
        <div data="17" class="member_item">
            <span class="online_indicator glyphicon glyphicon-record">&nbsp;</span>
            <span class="member_uname">bober</span>
            <span class="member_admin"> (<?php echo __("facilitator"); ?>)</span>
            <span class="online_status" style="display: none;"><?php echo __("status_online") ?></span>
            <span class="offline_status"><?php echo __("status_offline") ?></span>
        </div>
    </div>
</div>