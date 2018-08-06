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

<div class="manage_container row">
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

    <div class="manage_chapters col-sm-6">
        <h3><?php echo __("chapters") ?></h3>
        <ul>
            <?php foreach ($data["chapters"] as $chapter => $chapData): ?>
                <?php
                if(!empty($chapData))
                {
                    $userName = "unknown";
                    $key = array_search($chapData["memberID"], array_column($data["members"], 'memberID'));
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
                            <div class="uname"><?php echo !empty($chapData) ? '<a href="/members/profile/'.$chapData["memberID"].'" target="_blank">'.$name.'</a>' : '' ?></div>
                            <div class="uname_delete glyphicon glyphicon-remove" data="<?php echo !empty($chapData) ? $chapData["memberID"] : '' ?>"></div>
                            <div class="clear"></div>
                        </div>
                    </div>
                    <?php if($data["event"][0]->bookProject == "sun"): ?>
                    <div class="col-sm-4" data-chapter="<?php echo $chapter ?>"
                         data-member="<?php echo !empty($chapData) ? $chapData["memberID"] : "" ?>">
                        <?php
                        $kw = !empty($chapData["kwCheck"])
                            && array_key_exists($chapter, $chapData["kwCheck"])
                            && $chapData["kwCheck"][$chapter]["memberID"] > 0;
                        $cr = !empty($chapData["crCheck"])
                            && array_key_exists($chapter, $chapData["crCheck"])
                            && $chapData["crCheck"][$chapter]["memberID"] > 0;

                        $kwName = "";
                        $crName = "";
                        if($kw)
                        {
                            $kwKey = array_search($chapData["kwCheck"][$chapter]["memberID"], array_column($data["members"], 'memberID'));
                            if($kwKey)
                                $kwName = $data["members"][$kwKey]["firstName"] . " " . mb_substr($data["members"][$kwKey]["lastName"], 0, 1).".";

                            if(!$kwKey)
                            {
                                $kwKey = array_search($chapData["kwCheck"][$chapter]["memberID"], array_column($data["out_members"], 'memberID'));
                                $kwName = $data["out_members"][$kwKey]->firstName . " " . mb_substr($data["out_members"][$kwKey]->lastName, 0, 1).".";
                            }
                        }
                        if($cr)
                        {
                            $crKey = array_search($chapData["crCheck"][$chapter]["memberID"], array_column($data["members"], 'memberID'));
                            if($crKey)
                                $crName = $data["members"][$crKey]["firstName"] . " " . mb_substr($data["members"][$crKey]["lastName"], 0, 1).".";

                            if(!$crKey)
                            {
                                $crKey = array_search($chapData["crCheck"][$chapter]["memberID"], array_column($data["out_members"], 'memberID'));
                                $crName = $data["out_members"][$crKey]->firstName . " " . mb_substr($data["out_members"][$crKey]->lastName, 0, 1).".";
                            }

                        }
                        ?>
                        <?php if($kw): ?>
                            <button class="btn btn-danger remove_checker_alt" id="kw_checker"
                                    data-name="<?php echo $kwName ?>"
                                <?php echo $cr ? "disabled" : "" ?>
                                    title="<?php echo __("sun_theo_checker") ?>">Theo</button>
                            <?php if($cr): ?>
                                <button class="btn btn-danger remove_checker_alt" id="cr_checker"
                                        data-level="<?php echo $chapData["crCheck"][$chapter]["done"] ?>"
                                        data-name="<?php echo $crName ?>"
                                        title="<?php echo __("sun_vbv_checker") ?>">V-b-v</button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <?php elseif ($data["event"][0]->bookProject == "tn"): ?>
                    <div class="col-sm-4" data-chapter="<?php echo $chapter ?>"
                         data-member="<?php echo !empty($chapData) ? $chapData["memberID"] : "" ?>">
                        <?php
                        $other = !empty($chapData["otherCheck"])
                            && array_key_exists($chapter, $chapData["otherCheck"])
                            && $chapData["otherCheck"][$chapter]["memberID"] > 0;
                        $peer = !empty($chapData["peerCheck"])
                            && array_key_exists($chapter, $chapData["peerCheck"])
                            && $chapData["peerCheck"][$chapter]["memberID"] > 0;

                        $otherName = "";
                        $peerName = "";
                        if($other)
                        {
                            $otherKey = array_search($chapData["otherCheck"][$chapter]["memberID"], array_column($data["members"], 'memberID'));
                            if($otherKey)
                                $otherName = $data["members"][$otherKey]["firstName"] . " " . mb_substr($data["members"][$otherKey]["lastName"], 0, 1).".";

                            if(!$otherKey)
                            {
                                $otherKey = array_search($chapData["otherCheck"][$chapter]["memberID"], array_column($data["out_members"], 'memberID'));
                                $otherName = $data["out_members"][$otherKey]->firstName . " " . mb_substr($data["out_members"][$otherKey]->lastName, 0, 1).".";
                            }
                        }
                        if($peer)
                        {
                            $peerKey = array_search($chapData["peerCheck"][$chapter]["memberID"], array_column($data["members"], 'memberID'));
                            if($peerKey)
                                $peerName = $data["members"][$peerKey]["firstName"] . " " . mb_substr($data["members"][$peerKey]["lastName"], 0, 1).".";

                            if(!$peerKey)
                            {
                                $peerKey = array_search($chapData["peerCheck"][$chapter]["memberID"], array_column($data["out_members"], 'memberID'));
                                $peerName = $data["out_members"][$peerKey]->firstName . " " . mb_substr($data["out_members"][$peerKey]->lastName, 0, 1).".";
                            }

                        }
                        ?>
                        <?php if($other): ?>
                            <button class="btn btn-danger remove_checker_alt" id="other_checker"
                                    data-level="<?php echo $chapData["otherCheck"][$chapter]["done"] ?>"
                                    data-name="<?php echo $otherName ?>"
                                <?php echo $peer ? "disabled" : "" ?>
                                    title="<?php echo __("tn_checker") ?>">Checker</button>
                            <?php if($peer): ?>
                                <button class="btn btn-danger remove_checker_alt" id="peer_checker"
                                        data-level="<?php echo $chapData["peerCheck"][$chapter]["done"] ?>"
                                        data-name="<?php echo $peerName ?>"
                                        title="<?php echo __("tn_peer_checker") ?>">Peer</button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <?php elseif ($data["event"][0]->bookProject == "tq" || $data["event"][0]->bookProject == "tw"): ?>
                    <div class="col-sm-4" data-chapter="<?php echo $chapter ?>"
                         data-member="<?php echo !empty($chapData) ? $chapData["memberID"] : "" ?>">
                        <?php
                        $kw = !empty($chapData["kwCheck"])
                            && array_key_exists($chapter, $chapData["kwCheck"])
                            && $chapData["kwCheck"][$chapter]["memberID"] > 0;
                        $peer = !empty($chapData["peerCheck"])
                            && array_key_exists($chapter, $chapData["peerCheck"])
                            && $chapData["peerCheck"][$chapter]["memberID"] > 0;

                        $kwName = "";
                        $peerName = "";
                        if($kw)
                        {
                            $kwKey = array_search($chapData["kwCheck"][$chapter]["memberID"], array_column($data["members"], 'memberID'));
                            if($kwKey)
                                $kwName = $data["members"][$kwKey]["firstName"] . " " . mb_substr($data["members"][$kwKey]["lastName"], 0, 1).".";

                            if(!$kwKey)
                            {
                                $kwKey = array_search($chapData["kwCheck"][$chapter]["memberID"], array_column($data["out_members"], 'memberID'));
                                $kwName = $data["out_members"][$kwKey]->firstName . " " . mb_substr($data["out_members"][$kwKey]->lastName, 0, 1).".";
                            }
                        }
                        if($peer)
                        {
                            $peerKey = array_search($chapData["peerCheck"][$chapter]["memberID"], array_column($data["members"], 'memberID'));
                            if($peerKey)
                                $peerName = $data["members"][$peerKey]["firstName"] . " " . mb_substr($data["members"][$peerKey]["lastName"], 0, 1).".";

                            if(!$peerKey)
                            {
                                $peerKey = array_search($chapData["peerCheck"][$chapter]["memberID"], array_column($data["out_members"], 'memberID'));
                                $peerName = $data["out_members"][$peerKey]->firstName . " " . mb_substr($data["out_members"][$peerKey]->lastName, 0, 1).".";
                            }

                        }
                        ?>
                        <?php if($kw): ?>
                            <button class="btn btn-danger remove_checker_alt" id="kw_checker"
                                    data-level="<?php echo $chapData["kwCheck"][$chapter]["done"] ?>"
                                    data-name="<?php echo $kwName ?>"
                                <?php echo $peer ? "disabled" : "" ?>
                                    title="<?php echo __("keyword_checker") ?>">Keyword</button>
                            <?php if($peer): ?>
                                <button class="btn btn-danger remove_checker_alt" id="peer_checker"
                                        data-level="<?php echo $chapData["peerCheck"][$chapter]["done"] ?>"
                                        data-name="<?php echo $peerName ?>"
                                        title="<?php echo __("peer_checker") ?>">Peer</button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
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
                <?php echo __("add_translator") ?>
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
                        
                        <?php if(in_array($data["event"][0]->bookProject, ["tn"])): ?>
                        <label class="is_checker_label">
                            <input 
                                class="is_checker_input" 
                                type="checkbox"
                                <?php echo $member["isChecker"] ? "checked" : "" ?>> <?php echo __("checking_tab_title") ?>
                        </label>
                        <?php endif; ?>
                    </div>
                    <div class="member_chapters" <?php echo isset($member["assignedChapters"]) ? "style='display:block'" : "" ?>>
                        <?php echo __("chapters").": <span><b>". (isset($member["assignedChapters"]) ? join("</b>, <b>", $member["assignedChapters"]) : "")."</b></span>" ?>
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

                                    if($mode == "sun") {
                                        if (EventSteps::enum($step, $mode) > EventSteps::enum(EventSteps::SELF_CHECK, $mode))
                                            continue;
                                    }

                                    $selected = $step == $member["step"];
                                    $o_disabled = EventSteps::enum($member["step"], $mode) < $i ||
                                        (EventSteps::enum($member["step"], $mode) - $i) > 1;
                                    ?>

                                    <?php if($step == EventSteps::READ_CHUNK):
                                        $ch_disabled = $member["currentChunk"] <= 0 ||
                                            EventSteps::enum($member["step"], $mode) >= EventSteps::enum(EventSteps::BLIND_DRAFT, $mode);
                                        ?>
                                        <option <?php echo ($ch_disabled ? "disabled" : "") ?>
                                                value="<?php echo EventSteps::BLIND_DRAFT."_prev" ?>">
                                            &nbsp;&nbsp;&nbsp;&nbsp;
                                            <?php echo __(EventSteps::BLIND_DRAFT."_previous").($member["currentChunk"] > 0 ? " ".$member["currentChunk"] : "") ?>
                                        </option>
                                    <?php endif; ?>

                                    <?php if($step == EventSteps::REARRANGE):
                                        $ch_disabled = ($member["currentChunk"] <= 0 && $member["step"] != EventSteps::SYMBOL_DRAFT) ||
                                            ($member["step"] == EventSteps::SYMBOL_DRAFT && $member["currentChunk"] > 0) ||
                                            (EventSteps::enum($member["step"], $mode) - EventSteps::enum($step, $mode)) > 1;

                                        $chunks = (array)json_decode($member["chunks"], true);
                                        $currentChunk = $member["currentChunk"] > 0 || $member["step"] != EventSteps::SYMBOL_DRAFT
                                            ? $member["currentChunk"]
                                            : sizeof($chunks);
                                        ?>
                                        <option <?php echo ($ch_disabled ? "disabled" : "") ?>
                                                value="<?php echo EventSteps::REARRANGE."_prev" ?>">
                                            &nbsp;&nbsp;&nbsp;&nbsp;
                                            <?php echo __(EventSteps::REARRANGE."_previous").($currentChunk > 0 ? " ".$currentChunk : "") ?>
                                        </option>
                                    <?php endif; ?>

                                    <?php if($step == EventSteps::SYMBOL_DRAFT):
                                        $ch_disabled = $member["currentChunk"] <= 0 ||
                                            EventSteps::enum($member["step"], $mode) < EventSteps::enum($step, $mode) ||
                                            (EventSteps::enum($member["step"], $mode) - EventSteps::enum($step, $mode)) > 1;

                                        $chunks = (array)json_decode($member["chunks"], true);
                                        $currentChunk = $member["step"] != EventSteps::SELF_CHECK
                                            ? $member["currentChunk"]
                                            : sizeof($chunks);
                                        ?>
                                        <option <?php echo ($ch_disabled ? "disabled" : "") ?>
                                                value="<?php echo EventSteps::SYMBOL_DRAFT."_prev" ?>">
                                            &nbsp;&nbsp;&nbsp;&nbsp;
                                            <?php echo __(EventSteps::SYMBOL_DRAFT."_previous").($member["currentChunk"] > 0 ? " ".$currentChunk : "") ?>
                                        </option>
                                    <?php endif; ?>

                                    <option <?php echo ($selected ? " selected" : "").($o_disabled ? " disabled" : "") ?> value="<?php echo $step ?>">
                                        <?php
                                        $altStep = 5;
                                        if($mode == "tn")
                                            $altStep = 3;
                                        elseif($mode == "sun")
                                            $altStep = 4;
                                        elseif($mode == "tq" || $mode == "tw")
                                            $altStep = 0;

                                        $add = "";

                                        if($step != EventSteps::PRAY && $step != EventSteps::FINISHED)
                                        {
                                            $add = in_array($mode, ["tn"]) ? "_" . $mode : "";
                                        }
                                        if($step == EventSteps::CHUNKING && $mode == "sun")
                                            $add = "_sun";
                                        echo EventSteps::enum($step, $mode) == $altStep ? __($step."-alt") : __($step.$add)
                                        ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <?php 
                            $showButton = false;
                            if(($member["step"] == EventSteps::VERBALIZE ||
                                    $member["step"] == EventSteps::PEER_REVIEW || 
                                    $member["step"] == EventSteps::KEYWORD_CHECK || 
                                    $member["step"] == EventSteps::CONTENT_REVIEW) && !in_array($mode, ["tn","sun","tq","tw"]))
                            {
                                if($member["checkerID"] > 0)
                                    $showButton = true;
                                else
                                {
                                    if($member["step"] == EventSteps::VERBALIZE)
                                    {
                                        $verbCheck = (array)json_decode($member["verbCheck"], true);
                                        if(array_key_exists($member["currentChapter"], $verbCheck))
                                            $showButton = true;
                                    }
                                    if($member["step"] == EventSteps::PEER_REVIEW)
                                    {
                                        $peerCheck = (array)json_decode($member["peerCheck"], true);
                                        if(array_key_exists($member["currentChapter"], $peerCheck))
                                            $showButton = true;
                                    }
                                    if($member["step"] == EventSteps::KEYWORD_CHECK)
                                    {
                                        $kwCheck = (array)json_decode($member["kwCheck"], true);
                                        if(array_key_exists($member["currentChapter"], $kwCheck))
                                            $showButton = true;
                                    }
                                    if($member["step"] == EventSteps::CONTENT_REVIEW)
                                    {
                                        $crCheck = (array)json_decode($member["crCheck"], true);
                                        if(array_key_exists($member["currentChapter"], $crCheck))
                                            $showButton = true;
                                    }
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
