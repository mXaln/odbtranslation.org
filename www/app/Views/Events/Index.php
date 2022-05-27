<?php
use Helpers\Constants\EventStates;
use Helpers\Constants\EventSteps;
use Helpers\Session;

$profile = Session::get("profile");
?>

<div style="margin-bottom: 20px">
    <h1 class="demo_h"><?php echo __("mast_events") ?></h1>
    <div class="demo_title events_index">
        <a href="#" class="demo_link"><?php echo __("demo")?></a>
        <span class="glyphicon glyphicon-chevron-right"></span>
        <div class="demo_options">
            <ul>
                <a href="/events/demo"><li><?php echo __("8steps_mast") ?></li></a>
                <a href="/events/demo-scripture-input"><li><?php echo __("lang_input") ?></li></a>
                <a href="/events/demo-l2"><li><?php echo __("l2_l3_mast", ["level" => 2]); ?></li></a>
                <a href="/events/demo-l3"><li><?php echo __("l2_l3_mast", ["level" => 3]); ?></li></a>
                <a href="/events/demo-sun"><li><?php echo __("vsail") ?></li></a>
                <a href="/events/demo-sun-odb"><li><?php echo __("odb") . " (".__("vsail").")" ?></li></a>
                <a href="/events/demo-odb"><li><?php echo __("odb") ?></li></a>
                <a href="/events/demo-mill"><li><?php echo __("mill") ?></li></a>
            </ul>
        </div>
    </div>
</div>

<ul class="nav nav-tabs">
    <?php if(Session::get("isAdmin")): ?>
        <li role="presentation" id="my_facilitation" class="active my_tab">
            <a href="#"><?php echo __("facilitator_events") ?>
                <span>(<?php echo sizeof($data["myFacilitatorEventsInProgress"]) ?>)</span>
            </a>
        </li>
    <?php endif ?>

    <li role="presentation" id="my_translations" class="my_tab">
        <a href="#"><?php echo __("translator_events") ?>
            <span>(<?php echo sizeof($data["myTranslatorEvents"]) ?>)</span>
        </a>
    </li>
    <li role="presentation" id="my_checks" class="my_tab">
        <a href="#"><?php echo __("l1_events") ?>
            <span>(<?php echo sizeof($data["myCheckerL1Events"]) +
                    sizeof($data["myCheckerL2Events"]) + sizeof($data["myCheckerL3Events"])?>)</span>
        </a>
    </li>
</ul>

<?php if(Session::get("isAdmin")): ?>
    <div id="my_facilitation_content" class="my_content shown">
        <?php if(Session::get("isSuperAdmin")): ?>
            <div class="create_event_block">
                <div>
                    <a href="/admin" class="create_event_link"><?php echo __("admin") ?></a>
                </div>
                <div class="create_info_tip"><?php echo __("create_info_tip", ["test" => "252352"]) ?></div>
                <div>
                    <img src="<?php echo template_url("img/tip.png") ?>" width="95">
                </div>
            </div>
        <?php endif; ?>

        <div class="clear"></div>

        <?php if(sizeof($data["myFacilitatorEventsInProgress"]) > 0): ?>
        <div class="events_separator"><?php echo __("events_in_progress") ?></div>
        <?php endif; ?>

        <?php foreach($data["myFacilitatorEventsInProgress"] as $key => $event): ?>
            <?php
            switch ($event->state)
            {
                case EventStates::L2_RECRUIT:
                case EventStates::L2_CHECK:
                case EventStates::L2_CHECKED:
                    $eventType = __("l2_3_events", ["level" => 2]);
                    $mode = $event->bookProject;
                    $eventImg = template_url("img/steps/big/l2_check.png");
                    $logoBorderClass = "checkingl2";
                    $bgColor = "purple-marked";
                    $currentMembers = $event->chl2Cnt;
                    $members = __("checkers");
                    $manageLink = "/events/manage-l2/".$event->eventID;
                    $progressLink = "/events/information-l2/".$event->eventID;
                    break;

                case EventStates::L3_RECRUIT:
                case EventStates::L3_CHECK:
                    $eventType = __("l2_3_events", ["level" => 3]);
                    $mode = $event->bookProject;
                    $eventImg = template_url("img/steps/big/l2_check.png");
                    $logoBorderClass = "checkingl3";
                    $bgColor = "purple-marked";
                    $currentMembers = $event->chl3Cnt;
                    $members = __("checkers");
                    $manageLink = "/events/manage-l3/".$event->eventID;
                    $progressLink = "/events/information".(!in_array($event->bookProject, ["ulb","udb"]) ? "-".$event->bookProject : "")."-l3/".$event->eventID;
                    break;

                default:
                    $mode = $event->bookProject;
                    $eventImg = template_url("img/steps/big/peer-review.png");
                    if(in_array($mode, ["ulb","udb"]))
                    {
                        $eventType = $event->langInput ? __("lang_input") : __("8steps_mast");
                        if($event->langInput)
                        {
                            $eventImg = template_url("img/steps/big/consume.png");
                        }
                    }
                    elseif ($mode == "sun")
                    {
                        $eventType = $event->sourceBible == "odb" ? __("odb") : __("vsail");
                        $eventImg = template_url("img/steps/big/vsail.png");
                    }
                    elseif (in_array($mode, ["fnd","bib","theo"]))
                    {
                        $eventType = __("mill");
                        $eventImg = template_url("img/steps/big/mill.png");
                    }
                    else
                    {
                        $eventType = "";
                        $eventImg = template_url("img/steps/big/peer-review.png");
                    }

                    $logoBorderClass = "translation";
                    $bgColor = "purple-marked";
                    $currentMembers = $event->trsCnt;
                    $members = __("translators");
                    $manageLink = "/events/manage".($mode == "tw" ? "-tw" : "")."/".$event->eventID;
                    $progressLink = "/events/information".
                        (in_array($event->sourceBible, ["odb","fnd","bib","theo"]) ? "-" . $event->sourceBible : "").
                        (in_array($mode, ["sun"]) ? "-".$mode : "").
                        "/".$event->eventID;
                    break;
            }
            ?>

            <div class="event_block <?php echo $key%2 == 0 ? $bgColor : "" ?>">
                <div class="event_logo <?php echo $logoBorderClass ?>">
                    <div class="event_type"><?php echo __($eventType) ?></div>
                    <div class="event_mode <?php echo $mode ?>"><?php echo __($mode) ?></div>
                    <div class="event_img">
                        <img width="146" src="<?php echo $eventImg ?>">
                    </div>
                </div>
                <div class="event_details">
                    <div class="event_project">
                        <div class="event_book"><?php echo $event->name ?></div>
                        <div class="event_proj">
                            <div><?php echo $event->sourceBible == "odb" ? __($event->sourceBible) : __($event->bookProject) ?></div>
                            <div><?php echo $event->langName . ($event->bookProject == "ulb" ? ", " . ($event->abbrID < 41 ? __("old_test") : __("new_test")) : "")?></div>
                        </div>
                        <div class="event_facilitator">

                        </div>
                    </div>
                    <div class="event_current_pos">
                        <div class="event_current_title"><?php echo __("state") ?></div>
                        <div class="event_curr_step">
                            <?php echo __("state_".$event->state) ?>
                        </div>
                    </div>
                    <div class="event_action">
                        <div class="event_manage_link"><a href="<?php echo $manageLink ?>"><?php echo __("manage") ?></a></div>
                        <div class="event_progress_link"><a href="<?php echo $progressLink ?>"><?php echo __("progress") ?></a></div>
                        <div class="event_members">
                            <div><?php echo $members ?></div>
                            <div class="trs_num"><?php echo $currentMembers ?></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>



        <?php if(sizeof($data["myFacilitatorEventsFinished"]) > 0): ?>
            <div class="events_separator"><?php echo __("events_finished") ?></div>
        <?php endif; ?>

        <?php foreach($data["myFacilitatorEventsFinished"] as $key => $event): ?>
            <?php
            switch ($event->state)
            {
                case EventStates::L2_RECRUIT:
                case EventStates::L2_CHECK:
                case EventStates::L2_CHECKED:
                    $eventType = __("l2_3_events", ["level" => 2]);
                    $mode = $event->bookProject;
                    $eventImg = template_url("img/steps/big/l2_check.png");
                    $logoBorderClass = "checkingl2";
                    $bgColor = "purple-marked";
                    $currentMembers = $event->chl2Cnt;
                    $members = __("checkers");
                    $manageLink = "/events/manage-l2/".$event->eventID;
                    $progressLink = "/events/information-l2/".$event->eventID;
                    break;

                case EventStates::L3_RECRUIT:
                case EventStates::L3_CHECK:
                    $eventType = __("l2_3_events", ["level" => 3]);
                    $mode = $event->bookProject;
                    $eventImg = template_url("img/steps/big/l2_check.png");
                    $logoBorderClass = "checkingl3";
                    $bgColor = "purple-marked";
                    $currentMembers = $event->chl3Cnt;
                    $members = __("checkers");
                    $manageLink = "/events/manage-l3/".$event->eventID;
                    $progressLink = "/events/information".(!in_array($event->bookProject, ["ulb","udb"]) ? "-".$event->bookProject : "")."-l3/".$event->eventID;
                    break;

                default:
                    $mode = $event->bookProject;
                    $eventImg = template_url("img/steps/big/peer-review.png");
                    if(in_array($mode, ["ulb","udb"]))
                    {
                        $eventType = $event->langInput ? __("lang_input") : __("8steps_mast");
                        if($event->langInput)
                        {
                            $eventImg = template_url("img/steps/big/consume.png");
                        }
                    }
                    elseif ($mode == "sun")
                    {
                        $eventType = $event->sourceBible == "odb" ? __("odb") : __("vsail");
                        $eventImg = template_url("img/steps/big/vsail.png");
                    }
                    elseif (in_array($mode, ["fnd","bib","theo"]))
                    {
                        $eventType = __("mill");
                        $eventImg = template_url("img/steps/big/mill.png");
                    }
                    else
                    {
                        $eventType = "";
                    }

                    $logoBorderClass = "translation";
                    $bgColor = "purple-marked";
                    $currentMembers = $event->trsCnt;
                    $members = __("translators");
                    $manageLink = "/events/manage/".$event->eventID;
                    $progressLink = "/events/information".
                        ($event->sourceBible == "odb" ? "-odb" : "").
                        (in_array($mode, ["tn","sun","tq","tw","rad"]) ? "-".$mode : "").
                        "/".$event->eventID;
                    break;
            }
            ?>

            <div class="event_block <?php echo $key%2 == 0 ? $bgColor : "" ?>">
                <div class="event_logo <?php echo $logoBorderClass ?>">
                    <div class="event_type"><?php echo __($eventType) ?></div>
                    <div class="event_mode <?php echo $mode ?>"><?php echo __($mode) ?></div>
                    <div class="event_img">
                        <img width="146" src="<?php echo $eventImg ?>">
                    </div>
                </div>
                <div class="event_details">
                    <div class="event_project">
                        <div class="event_book"><?php echo $event->name ?></div>
                        <div class="event_proj">
                            <div><?php echo $event->sourceBible == "odb" ? __($event->sourceBible) : __($event->bookProject) ?></div>
                            <div><?php echo $event->langName . ($event->bookProject == "ulb" ? ", " . ($event->abbrID < 41 ? __("old_test") : __("new_test")) : "")?></div>
                        </div>
                        <div class="event_facilitator">

                        </div>
                    </div>
                    <div class="event_current_pos">
                        <div class="event_current_title"><?php echo __("state") ?></div>
                        <div class="event_curr_step">
                            <?php echo __("state_".$event->state) ?>
                        </div>
                    </div>
                    <div class="event_action">
                        <div class="event_manage_link"><a href="<?php echo $manageLink ?>"><?php echo __("manage") ?></a></div>
                        <div class="event_progress_link"><a href="<?php echo $progressLink ?>"><?php echo __("progress") ?></a></div>
                        <div class="event_members">
                            <div><?php echo $members ?></div>
                            <div class="trs_num"><?php echo $currentMembers ?></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if(sizeof($data["myFacilitatorEventsInProgress"]) <= 0 && sizeof($data["myFacilitatorEventsFinished"]) <= 0): ?>
            <div class="no_events_message"><?php echo __("no_events_message") ?></div>
        <?php endif; ?>
    </div>
<?php endif ?>

<div id="my_translations_content" class="my_content">
    <?php foreach($data["myTranslatorEvents"] as $key => $event): ?>
        <?php
        $mode = $event->bookProject;
        $eventImg = template_url("img/steps/big/peer-review.png");
        if(in_array($mode, ["ulb","udb"]))
        {
            $eventType = $event->langInput ? __("lang_input") : __("8steps_mast");
            if($event->langInput)
            {
                $eventImg = template_url("img/steps/big/consume.png");
            }
        }
        elseif ($mode == "sun")
        {
            $eventType = $event->sourceBible == "odb" ? __("odb") : __("vsail");
            $eventImg = template_url("img/steps/big/vsail.png");
        }
        elseif (in_array($mode, ["fnd","bib","theo"]))
        {
            $eventType = __("mill");
            $eventImg = template_url("img/steps/big/mill.png");
        }
        else
        {
            $eventType = "";
        }

        $tw_group = $event->bookProject == "tw" ? json_decode($event->words, true) : [];
        ?>
        <div class="event_block <?php echo $key%2 == 0 ? "green-marked" : "" ?>">
            <div class="event_logo translation">
                <div class="event_type"><?php echo $eventType ?></div>
                <div class="event_mode <?php echo $event->bookProject ?>"><?php echo __($event->bookProject) ?></div>
                <div class="event_img">
                    <img width="146" src="<?php echo $eventImg?>">
                </div>
            </div>
            <div class="event_details">
                <div class="event_project">
                    <div class="event_book"><?php echo $event->name ?></div>
                    <div class="event_proj">
                        <div><?php echo $event->sourceBible == "odb" ? __($event->sourceBible) : __($event->bookProject) ?></div>
                        <div><?php echo $event->tLang . ($event->bookProject == "ulb" ? ", " . ($event->abbrID < 41 ? __("old_test") : __("new_test")) : "")?></div>
                    </div>
                    <div class="event_facilitator">
                        <div><?php echo __("facilitators") ?>:</div>
                        <div class="facil_names">
                            <?php foreach ((array)json_decode($event->admins, true) as $admin): ?>
                                <a href="#" data="<?php echo $admin ?>"><?php echo $data["admins"][$admin]["name"] ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="event_current_pos">
                    <?php if($event->step != EventSteps::NONE): ?>
                        <div class="event_current_title"><?php echo __("you_are_at") ?></div>
                        <div class="event_curr_step">
                            <?php
                            $step = $event->step;
                            if($step == EventSteps::READ_CHUNK)
                                $step = EventSteps::BLIND_DRAFT;
                            ?>
                            <img class='img_current' src="<?php echo template_url("img/steps/green_icons/". $step. ".png") ?>">
                            <div class="step_current">
                                <div>
                                    <?php echo ($event->currentChapter > 0
                                        ? ($event->bookProject == "tw"
                                            ? "[".$tw_group[0]."...".$tw_group[sizeof($tw_group)-1]."]"
                                            : __("chapter_number", ["chapter" => $event->currentChapter]))
                                        : ($event->currentChapter == 0 && in_array($event->bookProject, ["tn"])
                                            ? __("front")
                                            : "")) ?>
                                </div>
                                <div>
                                    <?php echo __($event->step . (in_array($event->bookProject, ["tn"]) ? "_tn" :
                                            ($event->bookProject == "sun" && $event->step == EventSteps::CHUNKING ? "_sun" : ""))) ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="event_action">
                    <div class="event_link">
                        <a href="/events/translator<?php echo ($event->sourceBible == "odb" ? "-odb" : "")
                            .(in_array($event->bookProject, ["tn","sun","tq","tw","rad"]) ? "-".
                                $event->bookProject : "") ?>/<?php echo $event->eventID ?>">
                            <?php echo __("continue_alt") ?>
                        </a>
                    </div>
                    <div class="event_members">
                        <div><?php echo __("translators") ?></div>
                        <div class="trs_num"><?php echo $event->currTrs ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach ?>

    <?php if(sizeof($data["myTranslatorEvents"]) <= 0): ?>
        <div class="no_events_message"><?php echo __("no_events_message") ?></div>
    <?php endif; ?>
</div>

<div id="my_checks_content" class="my_content">
    <?php foreach($data["myCheckerL1Events"] as $key => $event): ?>
        <?php
        $mode = $event->bookProject;
        $eventImg = template_url("img/steps/big/peer-review.png");
        if(in_array($mode, ["ulb","udb"]))
        {
            $eventType = $event->langInput ? __("lang_input") : __("8steps_mast");
        }
        elseif ($mode == "sun")
        {
            $eventType = $event->sourceBible == "odb" ? __("odb") : __("vsail");
            $eventImg = template_url("img/steps/big/vsail.png");
        }
        elseif (in_array($mode, ["fnd","bib","theo"]))
        {
            $eventType = __("mill");
            $eventImg = template_url("img/steps/big/mill.png");
        }
        else
        {
            $eventType = "";
        }
        ?>
        <div class="event_block <?php echo $key%2 == 0 ? "gray-marked" : "" ?>">
            <div class="event_logo checking">
                <div class="event_type">
                    <div class="event_mode <?php echo $event->bookProject ?>"><?php echo __($event->bookProject) ?></div>
                    <?php $add = $event->sourceBible == "odb" ? "_odb" : "" ?>
                    <div><?php echo __($event->step . $add) ?></div>
                </div>
                <div class="event_img">
                    <img width="85" src="<?php echo $eventImg ?>">
                </div>
            </div>
            <div class="event_details">
                <div class="event_project">
                    <div class="event_book"><?php echo $event->bookName ?? $event->name ?></div>
                    <div class="event_proj">
                        <div><?php echo $event->sourceBible == "odb" ? __($event->sourceBible) : __($event->bookProject) ?></div>
                        <div><?php echo $event->tLang . ($event->bookProject == "ulb" ? ", " . ($event->abbrID < 41 ? __("old_test") : __("new_test")) : "")?></div>
                    </div>
                    <div class="event_facilitator">
                        <div><?php echo __("facilitators") ?>:</div>
                        <div class="facil_names">
                            <?php foreach ((array)json_decode($event->admins, true) as $admin): ?>
                                <a href="#" data="<?php echo $admin ?>"><?php echo $data["admins"][$admin]["name"] ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="event_translator">
                    <div class="event_translator_data">
                        <div class="event_translator_title"><?php echo __("translator") ?></div>
                        <div class="event_translator_name"><?php echo $event->firstName . " " . mb_substr($event->lastName, 0, 1)."." ?></div>
                    </div>
                </div>
                <div class="event_current_pos">
                    <div class="event_current_title"><?php echo __("you_are_at") ?></div>
                    <div class="event_curr_step">
                        <?php
                        $step = $event->step;
                        if($step == EventSteps::READ_CHUNK)
                            $step = EventSteps::BLIND_DRAFT;
                        ?>
                        <img class='img_current' src="<?php echo template_url("img/steps/green_icons/". $step. ".png") ?>">
                        <div class="step_current">
                            <div>
                                <?php echo ($event->currentChapter > 0
                                    ? __("chapter_number", ["chapter" => $event->currentChapter])
                                    : __("front")) ?>
                            </div>
                            <div>
                                <?php echo __($event->step.
                                    (in_array($event->sourceBible, ["odb","fnd","bib","theo"]) ?
                                        ($event->sourceBible == "odb" ? "_odb" : "_mill") : "")) ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="event_action check1">
                    <div class="event_link">
                        <a href="/events/checker<?php echo (in_array($event->sourceBible, ["odb","fnd","bib","theo"]) ? "-".$event->sourceBible : "")
                            .(in_array($event->bookProject, ["sun"]) ? "-".$event->bookProject : "")
                            ."/".$event->eventID."/".$event->memberID
                            .(isset($event->isContinue) ? "/".$event->currentChapter : "")?>"
                           data="<?php echo $event->eventID."_".$event->memberID?>">
                            <?php echo __("continue_alt") ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach ?>

    <?php foreach($data["myCheckerL2Events"] as $key => $event): ?>
        <div class="event_block <?php echo $key%2 == 0 ? "lemon-marked" : "" ?>">
            <div class="event_logo checkingl2">
                <div class="event_type"><?php echo __("l2_3_events", ["level" => 2]) ?></div>
                <div class="event_mode <?php echo $event->bookProject ?>"><?php echo __($event->bookProject) ?></div>
                <div class="event_img">
                    <img width="146" src="<?php echo template_url("img/steps/big/l2_check.png") ?>">
                </div>
            </div>
            <div class="event_details">
                <div class="event_project">
                    <div class="event_book"><?php echo $event->name ?></div>
                    <div class="event_proj">
                        <div><?php echo __($event->bookProject) ?></div>
                        <div><?php echo $event->tLang . ($event->bookProject == "ulb" ? ", " . ($event->abbrID < 41 ? __("old_test") : __("new_test")) : "")?></div>
                    </div>
                    <div class="event_facilitator">
                        <div><?php echo __("facilitators") ?>:</div>
                        <div class="facil_names">
                            <?php foreach ((array)json_decode($event->admins_l2, true) as $admin): ?>
                                <a href="#" data="<?php echo $admin ?>"><?php echo $data["admins"][$admin]["name"] ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="event_current_pos">
                    <?php if($event->step != EventSteps::NONE): ?>
                        <div class="event_current_title"><?php echo __("you_are_at") ?></div>
                        <div class="event_curr_step">
                            <img class='img_current' src="<?php echo template_url("img/steps/green_icons/". $event->step. ".png") ?>">
                            <div class="step_current">
                                <div>
                                    <?php echo ($event->currentChapter > 0 ? __("chapter_number",
                                        ["chapter" => $event->currentChapter]) : "") ?>
                                </div>
                                <div>
                                    <?php echo __($event->step) ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="event_action check2">
                    <div class="event_link">
                        <a href="/events/checker-l2/<?php echo $event->eventID
                            .(isset($event->isContinue) ? "/".$event->l2memberID."/".$event->currentChapter : "") ?>">
                            <?php echo __("continue_alt") ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach ?>

    <?php foreach($data["myCheckerL3Events"] as $key => $event): ?>
        <div class="event_block <?php echo $key%2 == 0 ? "blue-marked" : "" ?>">
            <div class="event_logo checkingl3">
                <div class="event_type"><?php echo __("l2_3_events", ["level" => 3]) ?></div>
                <div class="event_mode <?php echo $event->bookProject ?>"><?php echo __($event->bookProject) ?></div>
                <div class="event_img">
                    <img width="146" src="<?php echo template_url("img/steps/big/l2_check.png") ?>">
                </div>
            </div>
            <div class="event_details">
                <div class="event_project">
                    <div class="event_book"><?php echo $event->name ?></div>
                    <div class="event_proj">
                        <div><?php echo __($event->bookProject) ?></div>
                        <div><?php echo $event->tLang . ($event->bookProject == "ulb" ? ", " . ($event->abbrID < 41 ? __("old_test") : __("new_test")) : "")?></div>
                    </div>
                    <div class="event_facilitator">
                        <div><?php echo __("facilitators") ?>:</div>
                        <div class="facil_names">
                            <?php foreach ((array)json_decode($event->admins_l3, true) as $admin): ?>
                                <a href="#" data="<?php echo $admin ?>"><?php echo $data["admins"][$admin]["name"] ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="event_current_pos">
                    <?php if($event->step != EventSteps::NONE): ?>
                        <div class="event_current_title"><?php echo __("you_are_at") ?></div>
                        <div class="event_curr_step">
                            <img class='img_current' src="<?php echo template_url("img/steps/green_icons/". $event->step. ".png") ?>">
                            <div class="step_current">
                                <div>
                                    <?php echo ($event->currentChapter > 0
                                        ? __("chapter_number", ["chapter" => $event->currentChapter])
                                        : __("front")) ?>
                                </div>
                                <div>
                                    <?php echo __($event->step) ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="event_action <?php echo !empty($event->isContinue) ? "check3" : "" ?>">
                    <div class="event_link">
                        <a href="/events/checker<?php echo "-l3/".$event->eventID
                            .(isset($event->isContinue) ? "/".$event->memberID."/".$event->currentChapter : "")?>"
                           data="<?php echo $event->eventID."_".$event->memberID?>">
                            <?php echo __("continue_alt") ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach ?>

    <?php if((sizeof($data["myCheckerL1Events"]) + sizeof($data["myCheckerL2Events"]) + sizeof($data["myCheckerL3Events"])) <= 0): ?>
        <div class="no_events_message"><?php echo __("no_events_message") ?></div>
    <?php endif; ?>

    <div class="clear"></div>
</div>

<div class="mailer_container">
    <div class="mailer_block">
        <div class="mailer-close glyphicon glyphicon-remove"></div>

        <form class="mailer_form">
            <div class="form-group">
                <div class="mailer_name">
                    <label><?php echo __("send_message_to") ?>:
                        <span></span>
                    </label>
                </div>
            </div>
            <div class="form-group">
                <div class="mailer_subject">
                    <label><?php echo __("message_subject") ?>:
                        <input name="subject" type="text" size="90" class="form-control"></label>
                </div>
            </div>
            <div class="form-group">
                <div class="mailer_message">
                    <label><?php echo __("message_content") ?>:
                        <textarea name="message" cols="90" rows="10" class="form-control"></textarea></label>
                </div>
            </div>
            <div class="form-group">
                <div class="mailer_button">
                    <button class="btn btn-primary form-control"><?php echo __("send") ?></button>
                </div>
            </div>
            <input type="hidden" name="adminID" value="" class="adm_id">
        </form>
    </div>
</div>
