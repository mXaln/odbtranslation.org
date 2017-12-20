<?php
use Helpers\Constants\EventCheckSteps;
use Helpers\Constants\EventStates;
use Shared\Legacy\Error;

echo Error::display($error);

if(!isset($error)):
?> 

<div class="back_link">
    <span class="glyphicon glyphicon-chevron-left"></span>
    <a href="#" onclick="history.back(); return false;"><?php echo __("go_back") ?></a>
</div>

<div class="manage_container row">
    <div class="row">
        <div class="col-sm-6">
            <div class="book_title" style="padding-left: 15px"><?php echo $data["event"][0]->name ?></div>
            <div class="project_title" style="padding-left: 15px"><?php echo __($data["event"][0]->bookProject)." - ".$data["event"][0]->langName ?></div>
        </div>
        <div class="col-sm-6 start_translation">
            <?php if($data["event"][0]->state == EventStates::L2_RECRUIT): ?>
                <form action="" method="post">
                    <button type="submit" name="submit" class="btn btn-warning" id="startTranslation" style="width: 150px; height: 50px;"><?php echo __("start_checking")?></button>
                </form>
            <?php else: ?>
                <div class="event_state"><?php echo __("event_status").": ".__("state_".$data["event"][0]->state) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="manage_chapters col-sm-6">
        <h3><?php echo __("chapters") ?></h3>
        <ul>
            <?php foreach ($data["chapters"] as $chapter => $chapData): ?>
                <?php
                if(!empty($chapData))
                {
                    $key = array_search($chapData["l2memberID"], array_column($data["members"], 'memberID'));
                    $userName = $data["members"][$key]["userName"];
                    $name = $data["members"][$key]["firstName"] . " " . mb_substr($data["members"][$key]["lastName"], 0, 1).".";
                    $data["members"][$key]["assignedChapters"][] = $chapter;
                }
                ?>
                <li class="row" style="position:relative;">
                    <div class="assignChapterLoader inline_f" data="<?php echo $chapter ?>">
                        <img src="<?php echo template_url("img/loader.gif") ?>" width="22">
                    </div>
                    <div class="col-sm-4 manage_chapter">
                        <?php echo $chapter > 0 ? __("chapter_number", $chapter) : __("chapter_number", __("intro")); ?>
                    </div>
                    <div class="col-sm-4 manage_chapters_user chapter_<?php echo $chapter ?>">
                        <button class="btn btn-success add_person_chapter" data="<?php echo $chapter ?>" <?php echo !empty($chapData) ? 'style="display: none"' : '' ?>>
                            <?php echo __("add_person") ?>
                        </button>
                        <div class="manage_username" <?php echo !empty($chapData) ? 'style="display: block"' : '' ?>>
                            <div class="uname"><?php echo !empty($chapData) ? '<a href="/members/profile/'.$chapData["l2memberID"].'" target="_blank">'.$name.'</a>' : '' ?></div>
                            <div class="uname_delete glyphicon glyphicon-remove" data="<?php echo !empty($chapData) ? $chapData["l2memberID"] : '' ?>"></div>
                            <div class="clear"></div>
                        </div>
                    </div>
                    <div class="col-sm-4" data-chapter="<?php echo $chapter ?>"
                            data-member="<?php echo !empty($chapData) ? $chapData["l2memberID"] : "" ?>">
                        <?php
                        $snd = !empty($chapData["sndCheck"])
                            && array_key_exists($chapter, $chapData["sndCheck"])
                            && $chapData["sndCheck"][$chapter]["memberID"] > 0;
                        $p1 = !empty($chapData["peer1Check"])
                            && array_key_exists($chapter, $chapData["peer1Check"])
                            && $chapData["peer1Check"][$chapter]["memberID"] > 0;
                        $p2 = !empty($chapData["peer2Check"])
                            && array_key_exists($chapter, $chapData["peer2Check"])
                            && $chapData["peer2Check"][$chapter]["memberID"] > 0;
                        ?>
                        <?php if($snd): ?>
                        <button class="btn btn-danger remove_checker_l2" id="snd_checker"
                                data-level="<?php echo $chapData["sndCheck"][$chapter]["done"] ?>"
                            <?php echo $p1 ? "disabled" : "" ?>
                                title="<?php echo __("l2_snd_checker") ?>">2nd</button>
                            <?php if($p1): ?>
                            <button class="btn btn-danger remove_checker_l2" id="p1_checker"
                                <?php echo $p2 ? "disabled" : "" ?>
                                    title="<?php echo __("l2_p1_checker") ?>">P1</button>
                                <?php if($p2): ?>
                                <button class="btn btn-danger remove_checker_l2" id="p2_checker"
                                        title="<?php echo __("l2_p2_checker") ?>">P2</button>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="manage_members col-sm-6">
        <h3>
            <?php echo __("people_number", sizeof($data["members"])) ?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <button 
                class="btn btn-primary" 
                id="openMembersSearch">
                <?php echo __("add_checker") ?>
            </button>
            <button 
                class="btn btn-success glyphicon glyphicon-refresh" 
                id="refresh" 
                title="<?php echo __("refresh"); ?>">
            </button>
        </h3>
        <ul>
            <?php foreach ($data["members"] as $member):?>
                <li>
                    <div class="member_usname" data="<?php echo $member["memberID"] ?>">
                        <a href="/members/profile/<?php echo $member["memberID"] ?>" target="_blank"><?php echo $member["firstName"] . " " . mb_substr($member["lastName"], 0, 1)."."; ?></a>
                        (<span><?php echo isset($member["assignedChapters"]) ? sizeof($member["assignedChapters"]) : 0 ?></span>)
                        <div class="glyphicon glyphicon-remove delete_user" title="<?php echo __("remove_from_event") ?>"></div>
                    </div>
                    <div class="member_chapters" <?php echo isset($member["assignedChapters"]) ? "style='display:block'" : "" ?>>
                        <?php echo __("chapters").": <span><b>". (isset($member["assignedChapters"]) ? join("</b>, <b>", $member["assignedChapters"]) : "")."</b></span>" ?>
                    </div>
                    <div class="step_selector_block row">
                        <div class="col-sm-6">
                            <?php
                            $mode = "l2";
                            $s_disabled = EventCheckSteps::enum($member["step"], $mode) < 2;
                            ?>
                            <label><?php echo __("current_step") ?>:</label>
                            <select class="step_selector form-control" 
                                <?php echo $s_disabled ? "disabled" : "" ?> 
                                data-event="<?php echo $data["event"][0]->eventID ?>"
                                data-member="<?php echo $member["memberID"] ?>"
                                data-mode="<?php echo $mode ?>">
                                <?php foreach (EventCheckSteps::enumArray($mode) as $step => $i): ?>
                                    <?php
                                    // Skip None step
                                    if($step == EventCheckSteps::NONE) continue;
                                    if(EventCheckSteps::enum($step, $mode) > 3) continue;
                                    
                                    $selected = $step == $member["step"];
                                    $o_disabled = EventCheckSteps::enum($member["step"], $mode) < $i ||
                                        (EventCheckSteps::enum($member["step"], $mode) - $i) > 1;
                                    ?>

                                    <option <?php echo ($selected ? " selected" : "").($o_disabled ? " disabled" : "") ?> value="<?php echo $step ?>">
                                        <?php echo __($step) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <?php 
                            $showButton = false;
                            if($member["step"] == EventCheckSteps::PEER_REVIEW_L2)
                            {
                                if($member["checkerID"] > 0)
                                    $showButton = true;
                                else
                                {
                                    $peerCheck = (array)json_decode($member["peerCheck"], true);
                                        if(array_key_exists($member["currentChapter"], $peerCheck))
                                            $showButton = true;
                                }
                            }
                            
                            if($showButton):
                            ?>
                            <button class="remove_checker btn btn-danger" style="margin-top: 22px;" 
                                    data="<?php echo $data["event"][0]->eventID.":".$member["memberID"] ?>" 
                                    data2="<?php echo $member["step"] ?>">
                                <?php echo __("remove_checker") ?>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="clear"></div>
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
                    <div class="divvalue">(<span><?php echo isset($member["assignedChapters"]) ? sizeof($member["assignedChapters"]) : 0 ?></span>)</div>
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
            <h1 class="panel-title"><?php echo __("add_checker")?> <span></span></h1>
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
<?php endif; ?>

<script>
    isManagePage = true;
    manageMode = "l2";
    userType = EventMembers.L2_CHECKER;

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
