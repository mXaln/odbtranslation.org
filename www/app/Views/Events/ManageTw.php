<?php
use Helpers\Constants\EventSteps;
use Helpers\Constants\EventStates;
use Shared\Legacy\Error;

echo Error::display($error);

if(!isset($error)):
    ?>

    <div class="back_link">
        <span class="glyphicon glyphicon-chevron-left"></span>
        <a href="#" onclick="history.back(); return false;"><?php echo __("go_back") ?></a>
    </div>

    <div class="manage_container">
        <div class="row">
            <div class="col-sm-6">
                <div class="book_title" style="padding-left: 15px"><?php echo $data["event"][0]->name ?></div>
                <div class="project_title" style="padding-left: 15px"><?php echo __($data["event"][0]->bookProject)." - ".$data["event"][0]->langName ?></div>
            </div>
            <div class="col-sm-6 start_translation">
                <?php if($data["event"][0]->state == EventStates::STARTED): ?>
                    <form action="" method="post">
                        <button type="submit" name="submit" class="btn btn-warning" id="startTranslation" style="width: 150px; height: 50px;"><?php echo __("start_translation")?></button>
                    </form>
                <?php else: ?>
                    <div class="event_state"><?php echo __("event_status").": ".__("state_".$data["event"][0]->state) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="manage_body">
            <div class="manage_chapters">
                <h3><?php echo __("chapters") ?></h3>
                <div id="word_group_block">
                    <button class="btn btn-primary" id="word_group_create"><?php echo __("create_words_group") ?></button>
                </div>
                <ul>
                    <?php $group_order = 1; foreach ($data["chapters"] as $chapter => $chapData): ?>
                        <?php
                        $group_name = null;
                        foreach ($data["tw_groups"] as $tw_group) {
                            if($tw_group->groupID == $chapter)
                            {
                                $words = (array) json_decode($tw_group->words, true);
                                $group_name = join(", ", $words);
                                break;
                            }
                        }

                        if(!empty($chapData))
                        {
                            $userName = "unknown";
                            $key = array_search($chapData["memberID"], array_column($data["members"], 'memberID'));
                            $userName = $data["members"][$key]["userName"];
                            $name = $data["members"][$key]["firstName"] . " " . mb_substr($data["members"][$key]["lastName"], 0, 1).".";
                            $data["members"][$key]["assignedChapters"][] = $chapter;
                            $data["members"][$key]["assignedGroups"][] = $group_order;
                        }
                        ?>
                        <li style="position:relative;">
                            <div class="assignChapterLoader inline_f" data="<?php echo $chapter ?>">
                                <img src="<?php echo template_url("img/loader.gif") ?>" width="22">
                            </div>
                            <div class="manage_chapter">
                                <?php echo __("group_id", $group_order); ?>
                                <span class='glyphicon glyphicon-info-sign'
                                      data-toggle='tooltip'
                                      title="<?php echo $group_name ? $group_name : "" ?>"
                                      style="font-size: 16px;"></span>
                                <div class="group_delete glyphicon glyphicon-remove" data-groupid="<?php echo $chapter ?>"></div>
                            </div>
                            <div class="manage_chapters_user chapter_<?php echo $chapter ?>">
                                <button class="btn btn-success add_person_chapter"
                                        data="<?php echo $chapter ?>"
                                        data-group="<?php echo $group_order ?>" <?php echo !empty($chapData) ? 'style="display: none"' : '' ?>>
                                    <?php echo __("add_person") ?>
                                </button>
                                <div class="manage_username" <?php echo !empty($chapData) ? 'style="display: block"' : '' ?>>
                                    <div class="uname"><?php echo !empty($chapData) ? '<a href="/members/profile/'.$chapData["memberID"].'" target="_blank">'.$name.'</a>' : '' ?></div>
                                    <div class="uname_delete glyphicon glyphicon-remove" data="<?php echo !empty($chapData) ? $chapData["memberID"] : '' ?>"></div>
                                    <div class="clear"></div>
                                </div>
                            </div>
                            <div class="manage_chapters_buttons" data-chapter="<?php echo $chapter ?>"
                                 data-member="<?php echo !empty($chapData) ? $chapData["memberID"] : "" ?>">
                                <?php
                                $other = !empty($chapData["otherCheck"])
                                    && array_key_exists($chapter, $chapData["otherCheck"])
                                    && $chapData["otherCheck"][$chapter]["memberID"] > 0;
                                $peer = !empty($chapData["peerCheck"])
                                    && array_key_exists($chapter, $chapData["peerCheck"])
                                    && $chapData["peerCheck"][$chapter]["memberID"] > 0;

                                $otherName = $other ? "Unknown: " . $chapData["otherCheck"][$chapter]["memberID"] : "";
                                $peerName = $peer ? "Unknown: " . $chapData["peerCheck"][$chapter]["memberID"] : "";
                                if($other)
                                {
                                    $otherKey = array_search($chapData["otherCheck"][$chapter]["memberID"], array_column($data["members"], 'memberID'));
                                    if($otherKey !== false)
                                        $otherName = $data["members"][$otherKey]["firstName"] . " " . mb_substr($data["members"][$otherKey]["lastName"], 0, 1).".";
                                    else
                                    {
                                        $otherKey = array_search($chapData["otherCheck"][$chapter]["memberID"], array_column($data["out_members"], 'memberID'));
                                        if($otherKey !== false)
                                            $otherName = $data["out_members"][$otherKey]["firstName"] . " " . mb_substr($data["out_members"][$otherKey]["lastName"], 0, 1).".";
                                    }
                                }
                                if($peer)
                                {
                                    $peerKey = array_search($chapData["peerCheck"][$chapter]["memberID"], array_column($data["members"], 'memberID'));
                                    if($peerKey !== false)
                                        $peerName = $data["members"][$peerKey]["firstName"] . " " . mb_substr($data["members"][$peerKey]["lastName"], 0, 1).".";
                                    else
                                    {
                                        $peerKey = array_search($chapData["peerCheck"][$chapter]["memberID"], array_column($data["out_members"], 'memberID'));
                                        if($peerKey !== false)
                                            $peerName = $data["out_members"][$peerKey]["firstName"] . " " . mb_substr($data["out_members"][$peerKey]["lastName"], 0, 1).".";
                                    }

                                }
                                ?>
                                <?php if($other): ?>
                                    <button class="btn btn-danger remove_checker_alt" id="other_checker"
                                            data-level="<?php echo $chapData["otherCheck"][$chapter]["done"] ?>"
                                            data-name="<?php echo $otherName ?>"
                                        <?php echo $peer ? "disabled" : "" ?>
                                            title="<?php echo __("other_checker") ?>">Checker</button>
                                    <?php if($peer): ?>
                                        <button class="btn btn-danger remove_checker_alt" id="peer_checker"
                                                data-level="<?php echo $chapData["peerCheck"][$chapter]["done"] ?>"
                                                data-name="<?php echo $peerName ?>"
                                                title="<?php echo __("other_peer_checker") ?>">Peer</button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </li>
                        <?php $group_order++; endforeach; ?>
                </ul>
            </div>

            <div class="manage_members">
                <h3>
                    <?php echo __("people_number", sizeof($data["members"])) ?>
                    <div class="manage_buttons">
                        <button
                                class="btn btn-primary"
                                id="openMembersSearch">
                            <?php echo __("add_translator") ?>
                        </button>
                        <button
                                class="btn btn-success glyphicon glyphicon-refresh"
                                id="refresh"
                                title="<?php echo __("refresh"); ?>">
                        </button>
                    </div>
                </h3>
                <ul>
                    <?php foreach ($data["members"] as $member):?>
                        <li>
                            <div class="member_usname" data="<?php echo $member["memberID"] ?>">
                                <a href="/members/profile/<?php echo $member["memberID"] ?>" target="_blank"><?php echo $member["firstName"] . " " . mb_substr($member["lastName"], 0, 1)."."; ?></a>
                                (<span><?php echo isset($member["assignedGroups"]) ? sizeof($member["assignedGroups"]) : 0 ?></span>)
                                <div class="glyphicon glyphicon-remove delete_user" title="<?php echo __("remove_from_event") ?>"></div>

                                <label class="is_checker_label">
                                    <input
                                            class="is_checker_input"
                                            type="checkbox"
                                        <?php echo $member["isChecker"] ? "checked" : "" ?>> <?php echo __("checking_tab_title") ?>
                                </label>
                            </div>
                            <div class="member_chapters" <?php echo isset($member["assignedGroups"]) ? "style='display:block'" : "" ?>>
                                <?php echo __("chapters").": <span>". (isset($member["assignedGroups"]) ? join(", ", $member["assignedGroups"]) : "")."</span>" ?>
                            </div>
                            <div class="step_selector_block row">
                                <div class="col-sm-6">
                                    <?php
                                    $mode = $data["event"][0]->bookProject;
                                    $s_disabled = EventSteps::enum($member["step"], $mode) < 2;
                                    ?>
                                    <label><?php echo __("current_step") ?>:</label>
                                    <select class="step_selector form-control"
                                        <?php echo $s_disabled ? "disabled" : "" ?>
                                            data-event="<?php echo $data["event"][0]->eventID ?>"
                                            data-member="<?php echo $member["memberID"] ?>"
                                            data-mode="<?php echo $mode ?>">
                                        <?php foreach (EventSteps::enumArray($mode) as $step => $i): ?>
                                            <?php
                                            // Skip None step
                                            if($step == EventSteps::NONE) continue;

                                            $selected = $step == $member["step"];
                                            $o_disabled = EventSteps::enum($member["step"], $mode) < $i ||
                                                (EventSteps::enum($member["step"], $mode) - $i) > 1;
                                            ?>

                                            <option <?php echo ($selected ? " selected" : "").($o_disabled ? " disabled" : "") ?> value="<?php echo $step ?>">
                                                <?php echo __($step) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <input type="hidden" id="eventID" value="<?php echo $data["event"][0]->eventID ?>">
    <input type="hidden" id="mode" value="<?php echo $data["event"][0]->bookProject ?>">

    <div class="chapter_members">
        <div class="chapter_members_div panel panel-default">
            <div class="panel-heading">
                <h1 class="panel-title"><?php echo __("assign_chapter_title")?> <span></span></h1>
                <span class="chapter-members-close glyphicon glyphicon-remove-sign"></span>
            </div>
            <div class="assignChapterLoader dialog_f">
                <img src="<?php echo template_url("img/loader.gif") ?>">
            </div>
            <ul>
                <?php foreach ($data["members"] as $member): ?>
                    <li>
                        <div class="member_usname userlist chapter_ver">
                            <div class="divname"><?php echo $member["firstName"] . " " . mb_substr($member["lastName"], 0, 1)."."; ?></div>
                            <div class="divvalue">(<span><?php echo isset($member["assignedGroups"]) ? sizeof($member["assignedGroups"]) : 0 ?></span>)</div>
                        </div>
                        <button class="btn btn-success assign_chapter" data="<?php echo $member["memberID"] ?>"><?php echo __("assign") ?></button>
                        <div class="clear"></div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="members_search_dialog">
        <div class="members_search_dialog_div panel panel-default">
            <div class="panel-heading">
                <h1 class="panel-title"><?php echo __("add_translator")?> <span></span></h1>
                <span class="members-search-dialog-close glyphicon glyphicon-remove-sign"></span>
            </div>
            <div class="openMembersSearch dialog_f">
                <img src="<?php echo template_url("img/loader.gif") ?>">
            </div>
            <div class="members-search-dialog-content">
                <div class="form-group">
                    <input type="text" class="form-control input-lg" id="user_translator" placeholder="Enter a name" required="">
                </div>
                <ul class="user_translators">

                </ul>
            </div>
        </div>
    </div>

    <div class="words_group_dialog">
        <div class="words_group_dialog_div panel panel-default">
            <div class="panel-heading">
                <h1 class="panel-title"><?php echo __("create_words_group")?> <span></span></h1>
                <span class="words-group-dialog-close glyphicon glyphicon-remove-sign"></span>
            </div>
            <div class="openWordsGroup dialog_f">
                <img src="<?php echo template_url("img/loader.gif") ?>">
            </div>
            <div class="words-group-dialog-content">
                <div class="word_group_hint"><?php echo __("word_group_hint") ?></div>
                <div class="form-group">
                    <select class="form-control input-lg" id="word_group" multiple>
                        <?php foreach ($data["words"] as $word): ?>
                            <option <?php echo in_array($word["word"], $data["words_in_groups"]) ? "disabled" : "" ?>>
                                <?php echo $word["word"] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="text-align: right">
                    <button class="btn btn-success" id="create_group"><?php echo __("create_group") ?></button>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <a href="#" onclick="history.back(); return false"><?php echo __('go_back')?></a>
<?php endif; ?>

<script>
    isManagePage = true;
    manageMode = "l1";
    userType = EventMembers.TRANSLATOR;

    $(document).ready(function () {
        $('.step_selector').each(function () {
            $('option', this).each(function () {
                if (this.defaultSelected) {
                    this.selected = true;
                    return false;
                }
            });
        });
    });
</script>
