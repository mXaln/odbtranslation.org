<?php
use \Helpers\Constants\EventSteps;
use Shared\Legacy\Error;

echo Error::display($error);

if(!isset($error)):
?> 

<div class="back_link">
    <?php if(isset($_SERVER["HTTP_REFERER"])): ?>
        <span class="glyphicon glyphicon-chevron-left"></span>
        <a href="<?php echo $_SERVER["HTTP_REFERER"] ?>"><?php echo __("go_back") ?></a>
    <?php endif; ?>
</div>

<div class="manage_container row">
    <div class="row">
        <div class="col-sm-8">
            <div class="book_title" style="padding-left: 15px"><?php echo $data["event"][0]->name ?></div>
            <div class="project_title" style="padding-left: 15px"><?php echo __($data["event"][0]->bookProject)." - ".$data["event"][0]->langName ?></div>
        </div>
        <div class="col-sm-4 start_translation">
            <?php if($data["event"][0]->state == "started"): ?>
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
                    <div class="col-sm-8 manage_chapters_user chapter_<?php echo $chapter ?>">
                        <button class="btn btn-success add_person_chapter" data="<?php echo $chapter ?>" <?php echo !empty($chapData) ? 'style="display: none"' : '' ?>>
                            <?php echo __("add_person") ?>
                        </button>
                        <div class="manage_username" <?php echo !empty($chapData) ? 'style="display: block"' : '' ?>>
                            <div class="uname"><?php echo !empty($chapData) ? '<a href="/members/profile/'.$chapData["memberID"].'" target="_blank">'.$name.'</a>' : '' ?></div>
                            <div class="uname_delete glyphicon glyphicon-remove" data="<?php echo !empty($chapData) ? $chapData["memberID"] : '' ?>"></div>
                            <div class="clear"></div>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="manage_members col-sm-6">
        <h3>
            <?php echo __("people_number", array(sizeof($data["members"]), $data["event"][0]->translatorsNum)) ?>
            <button class="btn btn-primary" id="openMembersSearch"><?php echo __("add_translator") ?></button>
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
                            $s_disabled = EventSteps::enum($member["step"]) < 2;
                            ?>
                            <label><?php echo __("current_step") ?>:</label>
                            <select class="step_selector form-control" <?php echo $s_disabled ? "disabled" : "" ?> data="<?php echo $data["event"][0]->eventID.":".$member["memberID"] ?>">
                                <?php foreach (EventSteps::enumArray() as $step => $i): ?>
                                    <?php
                                    if($step == EventSteps::NONE) continue;

                                    $selected = $step == $member["step"];
                                    $o_disabled = EventSteps::enum($member["step"]) < $i ||
                                        (EventSteps::enum($member["step"]) - $i) > 1;
                                    ?>

                                    <?php if($step == EventSteps::READ_CHUNK):
                                        $ch_disabled = $member["currentChunk"] <= 0 ||
                                            EventSteps::enum($member["step"]) >= EventSteps::enum(EventSteps::BLIND_DRAFT);
                                        ?>
                                        <option <?php echo ($ch_disabled ? "disabled" : "") ?>
                                                value="<?php echo EventSteps::BLIND_DRAFT."_prev" ?>"
                                        style="padding-left: 20px">
                                            <?php echo __(EventSteps::BLIND_DRAFT."_previous").($member["currentChunk"] > 0 ? " ".$member["currentChunk"] : "") ?>
                                        </option>
                                    <?php endif; ?>

                                    <option <?php echo ($selected ? " selected" : "").($o_disabled ? " disabled" : "") ?> value="<?php echo $step ?>">
                                        <?php echo EventSteps::enum($step) == 5 ? __("read-chunk-alt") : __($step) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <?php 
                            $showButton = false;
                            if($member["step"] == EventSteps::VERBALIZE || 
                                    $member["step"] == EventSteps::PEER_REVIEW || 
                                    $member["step"] == EventSteps::KEYWORD_CHECK || 
                                    $member["step"] == EventSteps::CONTENT_REVIEW) 
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
<?php endif; ?>

<script>
    isManagePage = true;

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
