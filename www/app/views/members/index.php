<?php
use \Core\Language;
use \Helpers\Constants\EventStates;
use \Helpers\Session;
?>

<div style="border-bottom: dotted #ccc; margin-bottom: 20px">
    <h1><?php echo Language::show("vmast_events", "Events") ?></h1>
</div>

<ul class="nav nav-tabs">
    <?php if(Session::get("isAdmin")): ?>
    <li role="presentation" id="my_facilitation" class="active my_tab">
        <a href="#"><?php echo Language::show("facilitator_events", "Events") ?>
            <span>(<?php echo sizeof($data["myFacilitatorEvents"]) ?>)</span>
        </a>
    </li>
    <?php endif ?>

    <li role="presentation" id="my_translations" class="my_tab">
        <a href="#"><?php echo Language::show("translator_events", "Events") ?>
            <span>(<?php echo sizeof($data["myTranslatorEvents"]) ?>)</span>
        </a>
    </li>
    <li role="presentation" id="my_checks" class="my_tab">
        <a href="#"><?php echo Language::show("l1_events", "Events") ?>
            <span>(<?php echo sizeof($data["myCheckerL1Events"]) +
            sizeof($data["myCheckerL2Events"]) + sizeof($data["myCheckerL3Events"])?>)</span>
        </a>
    </li>
    <li role="presentation" id="new_events" class="my_tab">
        <a href="#"><?php echo Language::show("new_events", "Events") ?>
            <span class="<?php echo sizeof($data["newEvents"]) > 0 ? "hasEvents" : "" ?>">(<?php echo sizeof($data["newEvents"]) ?>)</span>
        </a>
    </li>
</ul>

<?php if(Session::get("isAdmin")): ?>
<div id="my_facilitation_content" class="my_content shown">
    <div class="create_event_block">
        <div>
            <a href="/admin" class="create_event_link"><?php echo Language::show("create_event", "Events") ?></a>
        </div>
        <div class="create_info_tip"><?php echo Language::show("create_info_tip", "Events") ?> <span><?php echo Language::show("create_event_tip", "Events") ?></span></div>
        <div>
            <img src="<?php echo \Helpers\Url::templatePath() ?>/img/tip.png" width="95">
        </div>
    </div>

    <div class="clear"></div>

    <?php foreach($data["myFacilitatorEvents"] as $key => $event): ?>
        <?php
        switch ($event->state)
        {
            case EventStates::L2_RECRUIT:
                $eventType = Language::show("l2_3_events", "Events", array(2));
                $eventImg = \Helpers\Url::templatePath()."img/steps/big/l2_check.png";
                $logoBorderClass = "checkingl2";
                $bgColor = "purple-marked";
                $currentMembers = $event->chl2Cnt;
                $totalMembers = $event->l2CheckersNum;
                $members = Language::show("checkers", "Events");
                $manageLink = "#";
                $progressLink = "#";
                break;

            case EventStates::L3_RECRUIT:
                $eventType = Language::show("l2_3_events", "Events", array(3));
                $eventImg = \Helpers\Url::templatePath()."img/steps/big/l2_check.png";
                $logoBorderClass = "checkingl3";
                $bgColor = "purple-marked";
                $currentMembers = $event->chl3Cnt;
                $totalMembers = $event->l3CheckersNum;
                $members = Language::show("checkers", "Events");
                $manageLink = "#";
                $progressLink = "#";
                break;

            default:
                $eventType = Language::show("8steps_vmast", "Events");
                $eventImg = \Helpers\Url::templatePath()."img/steps/big/peer-review.png";
                $logoBorderClass = "translation";
                $bgColor = "purple-marked";
                $currentMembers = $event->trsCnt;
                $totalMembers = $event->translatorsNum;
                $members = Language::show("translators", "Events");
                $manageLink = "/events/manage/".$event->eventID;
                $progressLink = "/events/information/".$event->eventID;
                break;
        }
        ?>

        <div class="event_block <?php echo $key%2 == 0 ? $bgColor : "" ?>">
            <div class="event_logo <?php echo $logoBorderClass ?>">
                <div class="event_type"><?php echo Language::show($eventType, "Events") ?></div>
                <div class="event_img">
                    <img width="146" src="<?php echo $eventImg ?>">
                </div>
            </div>
            <div class="event_project">
                <div class="event_book"><?php echo $event->name ?></div>
                <div class="event_proj">
                    <div><?php echo Language::show($event->bookProject, "Events") ?></div>
                    <div><?php echo $event->langName . ", " . ($event->abbrID < 41 ? Language::show("old_test", "Events") : Language::show("new_test", "Events"))?></div>
                </div>
                <div class="event_facilitator">

                </div>
            </div>
            <div class="event_time">
                <div class="event_time_start">
                    <div class="event_time_title"><?php echo Language::show("time_start", "Events") ?></div>
                    <div class="event_time_date datetime" data="<?php echo $event->dateFrom != "" ? date(DATE_RFC2822, strtotime($event->dateFrom)) : "" ?>"><?php echo $event->dateFrom ?></div>
                    <div class="event_time_time"><?php echo $event->dateFrom ?></div>
                </div>
                <div class="event_time_end">
                    <div class="event_time_title"><?php echo Language::show("time_end", "Events") ?></div>
                    <div class="event_time_date datetime" data="<?php echo $event->dateTo != "" ? date(DATE_RFC2822, strtotime($event->dateTo)) : "" ?>"><?php echo $event->dateTo ?></div>
                    <div class="event_time_time"><?php echo $event->dateTo ?></div>
                </div>
            </div>
            <div class="event_current_pos">
                <div class="event_current_title"><?php echo Language::show("state", "Events") ?></div>
                <div class="event_curr_step">
                    <?php echo Language::show("state_".$event->state, "Events") ?>
                </div>
            </div>
            <div class="event_action">
                <div class="event_manage_link"><a href="<?php echo $manageLink ?>"><?php echo Language::show("manage", "Events") ?></a></div>
                <div class="event_progress_link"><a href="<?php echo $progressLink ?>"><?php echo Language::show("progress", "Events") ?></a></div>
                <div class="event_members">
                    <div><?php echo $members ?></div>
                    <div class="trs_num"><?php echo $currentMembers."/".$totalMembers ?></div>
                </div>
            </div>

            <div class="clear"></div>
        </div>
    <?php endforeach; ?>

    <?php if(sizeof($data["myFacilitatorEvents"]) <= 0): ?>
    <div class="no_events_message"><?php echo Language::show("no_events_message", "Events") ?></div>
    <?php endif; ?>
</div>
<?php endif ?>

<div id="my_translations_content" class="my_content">
    <?php foreach($data["myTranslatorEvents"] as $key => $event): ?>
        <div class="event_block <?php echo $key%2 == 0 ? "green-marked" : "" ?>">
            <div class="event_logo translation">
                <div class="event_type"><?php echo Language::show("8steps_vmast", "Events") ?></div>
                <div class="event_img">
                    <img width="146" src="<?php echo \Helpers\Url::templatePath() ?>img/steps/big/peer-review.png">
                </div>
            </div>
            <div class="event_project">
                <div class="event_book"><?php echo $event->name ?></div>
                <div class="event_proj">
                    <div><?php echo Language::show($event->bookProject, "Events") ?></div>
                    <div><?php echo $event->tLang . ", " . ($event->abbrID < 41 ? Language::show("old_test", "Events") : Language::show("new_test", "Events"))?></div>
                </div>
                <div class="event_facilitator">
                    <div><?php echo Language::show("facilitator", "Members") ?>:</div>
                    <div class="facil_name"><a href="#"><?php echo $event->facilFname ." ". $event->facilLname ?></a></div>
                    <div class="facil_contact"><a href="#"><?php echo Language::show("contact", "Members") ?></a></div>
                </div>
            </div>
            <div class="event_time">
                <div class="event_time_start">
                    <div class="event_time_title"><?php echo Language::show("time_start", "Events") ?></div>
                    <div class="event_time_date datetime" data="<?php echo $event->dateFrom != "" ? date(DATE_RFC2822, strtotime($event->dateFrom)) : "" ?>"><?php echo $event->dateFrom ?></div>
                    <div class="event_time_time"><?php echo $event->dateFrom ?></div>
                </div>
                <div class="event_time_end">
                    <div class="event_time_title"><?php echo Language::show("time_end", "Events") ?></div>
                    <div class="event_time_date datetime" data="<?php echo $event->dateTo != "" ? date(DATE_RFC2822, strtotime($event->dateTo)) : "" ?>"><?php echo $event->dateTo ?></div>
                    <div class="event_time_time"><?php echo $event->dateTo ?></div>
                </div>
            </div>
            <div class="event_current_pos">
                <div class="event_current_title"><?php echo Language::show("you_are_at", "Events") ?></div>
                <div class="event_curr_step">
                    <?php
                    $step = $event->step;
                    if($step == \Helpers\Constants\EventSteps::PRE_CHUNKING)
                        $step = \Helpers\Constants\EventSteps::CHUNKING;

                    if($step == \Helpers\Constants\EventSteps::SELF_CHECK_FULL)
                        $step = \Helpers\Constants\EventSteps::SELF_CHECK;
                    ?>
                    <img src="<?php echo \Helpers\Url::templatePath() ."img/steps/green_icons/". $step. ".png" ?>">
                    <?php echo ($event->currentChapter > 0 ? Language::show("chapter_number", "Events", array($event->currentChapter)). ", " : "").Language::show($event->step, "Events") ?>
                </div>
            </div>
            <div class="event_action">
                <div class="event_link"><a href="/events/translator/<?php echo $event->eventID ?>"><?php echo Language::show("continue_alt", "Events") ?></a></div>
                <div class="event_members">
                    <div><?php echo Language::show("translators", "Events") ?></div>
                    <div class="trs_num"><?php echo $event->currTrs."/".$event->translatorsNum ?></div>
                </div>
            </div>

            <div class="clear"></div>
        </div>
    <?php endforeach ?>

    <?php if(sizeof($data["myTranslatorEvents"]) <= 0): ?>
        <div class="no_events_message"><?php echo Language::show("no_events_message", "Events") ?></div>
    <?php endif; ?>
</div>

<div id="my_checks_content" class="my_content">
    <?php foreach($data["myCheckerL1Events"] as $key => $event): ?>
        <div class="event_block <?php echo $key%2 == 0 ? "gray-marked" : "" ?>">
            <div class="event_logo checking">
                <div class="event_type">
                    <div><?php echo Language::show("step_num", "Events", array(7)) ?></div>
                    <div><?php echo Language::show($event->step, "Events") ?></div>
                </div>
                <div class="event_img">
                    <img width="85" src="<?php echo \Helpers\Url::templatePath() ?>img/steps/icons/<?php echo $event->step ?>-gray.png">
                </div>
            </div>
            <div class="event_project">
                <div class="event_book"><?php echo $event->bookName ?></div>
                <div class="event_proj">
                    <div><?php echo Language::show($event->bookProject, "Events") ?></div>
                    <div><?php echo $event->tLang . ", " . ($event->abbrID < 41 ? Language::show("old_test", "Events") : Language::show("new_test", "Events"))?></div>
                </div>
                <div class="event_facilitator">
                    <div><?php echo Language::show("facilitator", "Members") ?>:</div>
                    <div class="facil_name"><a href="#"><?php echo $event->facilFname ." ". $event->facilLname ?></a></div>
                    <div class="facil_contact"><a href="#"><?php echo Language::show("contact", "Members") ?></a></div>
                </div>
            </div>
            <div class="event_translator">
                <div class="event_translator_data">
                    <div class="event_translator_title"><?php echo Language::show("translator", "Events") ?></div>
                    <div class="event_translator_name"><?php echo $event->userName ?></div>
                </div>
            </div>
            <div class="event_current_pos">
                <div class="event_current_title"><?php echo Language::show("you_are_at", "Events") ?></div>
                <div class="event_curr_step">
                    <?php
                    $step = $event->step;
                    if($step == \Helpers\Constants\EventSteps::PRE_CHUNKING)
                        $step = \Helpers\Constants\EventSteps::CHUNKING;

                    if($step == \Helpers\Constants\EventSteps::SELF_CHECK_FULL)
                        $step = \Helpers\Constants\EventSteps::SELF_CHECK;
                    ?>
                    <img src="<?php echo \Helpers\Url::templatePath() ."img/steps/green_icons/". $step. ".png" ?>">
                    <?php echo Language::show("chapter_number", "Events", array($event->currentChapter)) ?>
                </div>
            </div>
            <div class="event_action check1">
                <div class="event_link"><a href="/events/checker/<?php echo $event->eventID."/".$event->memberID ?>"><?php echo Language::show("continue_alt", "Events") ?></a></div>
            </div>

            <div class="clear"></div>
        </div>
    <?php endforeach ?>

    <?php foreach($data["myCheckerL2Events"] as $key => $event): ?>
        <div class="event_block <?php echo $key%2 == 0 ? "lemon-marked" : "" ?>">
            <div class="event_logo checkingl2">
                <div class="event_type"><?php echo Language::show("l2_3_events", "Events", array(2)) ?></div>
                <div class="event_img">
                    <img width="146" src="<?php echo \Helpers\Url::templatePath() ?>img/steps/big/l2_check.png">
                </div>
            </div>
            <div class="event_project">
                <div class="event_book"><?php echo $event->name ?></div>
                <div class="event_proj">
                    <div><?php echo Language::show($event->bookProject, "Events") ?></div>
                    <div><?php echo $event->tLang . ", " . ($event->abbrID < 41 ? Language::show("old_test", "Events") : Language::show("new_test", "Events"))?></div>
                </div>
                <div class="event_facilitator">
                    <div><?php echo Language::show("facilitator", "Members") ?>:</div>
                    <div class="facil_name"><a href="#"><?php echo $event->facilFname ." ". $event->facilLname ?></a></div>
                    <div class="facil_contact"><a href="#"><?php echo Language::show("contact", "Members") ?></a></div>
                </div>
            </div>
            <div class="event_time">
                <div class="event_time_start">
                    <div class="event_time_title"><?php echo Language::show("time_start", "Events") ?></div>
                    <div class="event_time_date datetime" data="<?php echo $event->dateFrom != "" ? date(DATE_RFC2822, strtotime($event->dateFrom)) : "" ?>"><?php echo $event->dateFrom ?></div>
                    <div class="event_time_time"><?php echo $event->dateFrom ?></div>
                </div>
                <div class="event_time_end">
                    <div class="event_time_title"><?php echo Language::show("time_end", "Events") ?></div>
                    <div class="event_time_date datetime" data="<?php echo $event->dateTo != "" ? date(DATE_RFC2822, strtotime($event->dateTo)) : "" ?>"><?php echo $event->dateTo ?></div>
                    <div class="event_time_time"><?php echo $event->dateTo ?></div>
                </div>
            </div>
            <div class="event_current_pos">
            </div>
            <div class="event_action check1">
                <div class="event_link"><a href="#"><?php echo Language::show("continue_alt", "Events") ?></a></div>
            </div>

            <div class="clear"></div>
        </div>
    <?php endforeach ?>

    <?php foreach($data["myCheckerL3Events"] as $key => $event): ?>
        <div class="event_block <?php echo $key%2 == 0 ? "blue-marked" : "" ?>">
            <div class="event_logo checkingl3">
                <div class="event_type"><?php echo Language::show("l2_3_events", "Events", array(3)) ?></div>
                <div class="event_img">
                    <img width="146" src="<?php echo \Helpers\Url::templatePath() ?>img/steps/big/l2_check.png">
                </div>
            </div>
            <div class="event_project">
                <div class="event_book"><?php echo $event->name ?></div>
                <div class="event_proj">
                    <div><?php echo Language::show($event->bookProject, "Events") ?></div>
                    <div><?php echo $event->tLang . ", " . ($event->abbrID < 41 ? Language::show("old_test", "Events") : Language::show("new_test", "Events"))?></div>
                </div>
                <div class="event_facilitator">
                    <div><?php echo Language::show("facilitator", "Members") ?>:</div>
                    <div class="facil_name"><a href="#"><?php echo $event->facilFname ." ". $event->facilLname ?></a></div>
                    <div class="facil_contact"><a href="#"><?php echo Language::show("contact", "Members") ?></a></div>
                </div>
            </div>
            <div class="event_time">
                <div class="event_time_start">
                    <div class="event_time_title"><?php echo Language::show("time_start", "Events") ?></div>
                    <div class="event_time_date datetime" data="<?php echo $event->dateFrom != "" ? date(DATE_RFC2822, strtotime($event->dateFrom)) : "" ?>"><?php echo $event->dateFrom ?></div>
                    <div class="event_time_time"><?php echo $event->dateFrom ?></div>
                </div>
                <div class="event_time_end">
                    <div class="event_time_title"><?php echo Language::show("time_end", "Events") ?></div>
                    <div class="event_time_date datetime" data="<?php echo $event->dateTo != "" ? date(DATE_RFC2822, strtotime($event->dateTo)) : "" ?>"><?php echo $event->dateTo ?></div>
                    <div class="event_time_time"><?php echo $event->dateTo ?></div>
                </div>
            </div>
            <div class="event_current_pos">
            </div>
            <div class="event_action check1">
                <div class="event_link"><a href="#"><?php echo Language::show("continue_alt", "Events") ?></a></div>
            </div>

            <div class="clear"></div>
        </div>
    <?php endforeach ?>

    <?php if((sizeof($data["myCheckerL1Events"]) + sizeof($data["myCheckerL2Events"]) + sizeof($data["myCheckerL3Events"])) <= 0): ?>
        <div class="no_events_message"><?php echo Language::show("no_events_message", "Events") ?></div>
    <?php endif; ?>

    <div class="clear"></div>
</div>

<div id="new_events_content" class="my_content">
    <?php foreach($data["newEvents"] as $key => $event): ?>
        <?php
        switch ($event->state)
        {
            case EventStates::L2_RECRUIT:
                $eventType = Language::show("l2_3_events", "Events", array(2));
                $eventImg = \Helpers\Url::templatePath()."img/steps/big/l2_check.png";
                $logoBorderClass = "checkingl2";
                $bgColor = "lemon-marked";
                $currentMembers = $event->chl2Cnt;
                $totalMembers = $event->l2CheckersNum;
                $members = Language::show("checkers", "Events");
                $stage = "l2";
                break;

            case EventStates::L3_RECRUIT:
                $eventType = Language::show("l2_3_events", "Events", array(3));
                $eventImg = \Helpers\Url::templatePath()."img/steps/big/l2_check.png";
                $logoBorderClass = "checkingl3";
                $bgColor = "blue-marked";
                $currentMembers = $event->chl3Cnt;
                $totalMembers = $event->l3CheckersNum;
                $members = Language::show("checkers", "Events");
                $stage = "l3";
                break;

            default:
                $eventType = Language::show("8steps_vmast", "Events");
                $eventImg = \Helpers\Url::templatePath()."img/steps/big/peer-review.png";
                $logoBorderClass = "translation";
                $bgColor = "green-marked";
                $currentMembers = $event->trsCnt;
                $totalMembers = $event->translatorsNum;
                $members = Language::show("translators", "Events");
                $stage = "d1";
                break;
        }
        ?>

        <div class="event_block <?php echo $key%2 == 0 ? $bgColor : "" ?>">
            <div class="event_logo <?php echo $logoBorderClass ?>">
                <div class="event_type"><?php echo $eventType ?></div>
                <div class="event_img">
                    <img width="146" src="<?php echo $eventImg ?>">
                </div>
            </div>
            <div class="event_project">
                <div class="event_book"><?php echo $event->name ?></div>
                <div class="event_proj">
                    <div><?php echo Language::show($event->bookProject, "Events") ?></div>
                    <div><?php echo $event->tLang . ", " . ($event->abbrID < 41 ? Language::show("old_test", "Events") : Language::show("new_test", "Events"))?></div>
                </div>
                <div class="event_facilitator">
                    <div><?php echo Language::show("facilitator", "Members") ?>:</div>
                    <div class="facil_name"><a href="#"><?php echo $event->facilFname ." ". $event->facilLname ?></a></div>
                    <div class="facil_contact"><a href="#"><?php echo Language::show("contact", "Members") ?></a></div>
                </div>
            </div>
            <div class="event_time">
                <div class="event_time_start">
                    <div class="event_time_title"><?php echo Language::show("time_start", "Events") ?></div>
                    <div class="event_time_date datetime" data="<?php echo $event->dateFrom != "" ? date(DATE_RFC2822, strtotime($event->dateFrom)) : "" ?>"><?php echo $event->dateFrom ?></div>
                    <div class="event_time_time"><?php echo $event->dateFrom ?></div>
                </div>
                <div class="event_time_end">
                    <div class="event_time_title"><?php echo Language::show("time_end", "Events") ?></div>
                    <div class="event_time_date datetime" data="<?php echo $event->dateTo != "" ? date(DATE_RFC2822, strtotime($event->dateTo)) : "" ?>"><?php echo $event->dateTo ?></div>
                    <div class="event_time_time"><?php echo $event->dateTo ?></div>
                </div>
            </div>
            <div class="event_current_pos">
            </div>
            <div class="event_action">
                <div class="event_link">
                    <a href="#" class="applyEvent" data="<?php echo $event->eventID ?>" data2="<?php echo $event->name ?>" data3="<?php echo $stage ?>"><?php echo Language::show("apply", "Events") ?></a>
                </div>
                <div class="event_members">
                    <div><?php echo $members ?></div>
                    <div class="trs_num"><?php echo $currentMembers."/".$totalMembers ?></div>
                </div>
            </div>

            <div class="clear"></div>
        </div>
    <?php endforeach ?>

    <?php if(sizeof($data["newEvents"]) <= 0): ?>
        <div class="no_events_message"><?php echo Language::show("no_events_message", "Events") ?></div>
    <?php endif; ?>

    <div class="clear"></div>
</div>

<div class="event-content form-panel">
    <div class="create-event-content panel panel-default">
        <div class="panel-heading">
            <h1 class="panel-title"></h1>
            <span class="panel-close glyphicon glyphicon-remove"></span>
        </div>

        <div class="page-content row panel-body">
            <div class="col-sm-8">
                <div class="bookName"></div>

                <div class="errors"></div>

                <form action="/events/rpc/apply_event" method="post" id="applyEvent" style="width: 900px;">
                    <div class="form-group">
                        <h3 class="ftr"><?php echo Language::show("apply_as_translator", "Events") ?></h3>
                        <h3 class="fl2" style="display: none"><?php echo Language::show("apply_as_checker", "Events", array(2)) ?></h3>
                        <h3 class="fl3" style="display: none"><?php echo Language::show("apply_as_checker", "Events", array(3)) ?></h3>
                    </div>

                    <div class="checker_info">
                        <div class="form-group">
                            <label class="church_role"><?php echo Language::show('church_role', 'Members'); ?>: </label>
                            <div class="form-control">
                                <label><input type="checkbox" name="church_role[]" value="Elder"
                                        <?php echo isset($profile["church_role"]) && in_array("Elder", $profile["church_role"]) ? "checked" : "" ?>> <?php echo Language::show('elder', 'Members'); ?> &nbsp;</label>
                                <label><input type="checkbox" name="church_role[]" value="Bishop"
                                        <?php echo isset($profile["church_role"]) && in_array("Bishop", $profile["church_role"]) ? "checked" : "" ?>> <?php echo Language::show('bishop', 'Members'); ?> &nbsp;</label>
                                <label><input type="checkbox" name="church_role[]" value="Pastor"
                                        <?php echo isset($profile["church_role"]) && in_array("Pastor", $profile["church_role"]) ? "checked" : "" ?>> <?php echo Language::show('pastor', 'Members'); ?> &nbsp;</label>
                                <label><input type="checkbox" name="church_role[]" value="Teacher"
                                        <?php echo isset($profile["church_role"]) && in_array("Teacher", $profile["church_role"]) ? "checked" : "" ?>> <?php echo Language::show('teacher', 'Members'); ?> &nbsp;</label>
                                <label><input type="checkbox" name="church_role[]" value="Denominational Leader"
                                        <?php echo isset($profile["church_role"]) && in_array("Denominational Leader", $profile["church_role"]) ? "checked" : "" ?>> <?php echo Language::show('denominational_leader', 'Members'); ?> &nbsp;</label>
                                <label><input type="checkbox" name="church_role[]" value="Seminary Professor"
                                        <?php echo isset($profile["church_role"]) && in_array("Seminary Professor", $profile["church_role"]) ? "checked" : "" ?>> <?php echo Language::show('seminary_professor', 'Members'); ?> &nbsp;</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><?php echo Language::show('orig_langs', 'Members'); ?>: </label>
                            <div class="form-control">
                                <label class="hebrew_knwlg"><?php echo Language::show('hebrew_knwlg', 'Members'); ?>: </label> &nbsp;&nbsp;
                                <label><input type="radio" name="hebrew_knwlg" value="0"
                                        <?php echo isset($profile["hebrew_knwlg"]) && $profile["hebrew_knwlg"] == 0 ? "checked" : "" ?>> <?php echo Language::show('none', 'Members'); ?> &nbsp;</label>
                                <label><input type="radio" name="hebrew_knwlg" value="1"
                                        <?php echo isset($profile["hebrew_knwlg"]) && $profile["hebrew_knwlg"] == 1 ? "checked" : "" ?>> <?php echo Language::show('limited', 'Members'); ?> &nbsp;</label>
                                <label><input type="radio" name="hebrew_knwlg" value="2"
                                        <?php echo isset($profile["hebrew_knwlg"]) && $profile["hebrew_knwlg"] == 2 ? "checked" : "" ?>> <?php echo Language::show('moderate', 'Members'); ?> &nbsp;</label>
                                <label><input type="radio" name="hebrew_knwlg" value="3"
                                        <?php echo isset($profile["hebrew_knwlg"]) && $profile["hebrew_knwlg"] == 3 ? "checked" : "" ?>> <?php echo Language::show('strong', 'Members'); ?> &nbsp;</label>
                                <label><input type="radio" name="hebrew_knwlg" value="4"
                                        <?php echo isset($profile["hebrew_knwlg"]) && $profile["hebrew_knwlg"] == 4 ? "checked" : "" ?>> <?php echo Language::show('expert', 'Members'); ?> &nbsp;</label>
                            </div>
                            <br>
                            <div class="form-control">
                                <label class="greek_knwlg"><?php echo Language::show('greek_knwlg', 'Members'); ?>: </label> &nbsp;&nbsp;
                                <label><input type="radio" name="greek_knwlg" value="0"
                                        <?php echo isset($profile["greek_knwlg"]) && $profile["greek_knwlg"] == 0 ? "checked" : "" ?>> <?php echo Language::show('none', 'Members'); ?> &nbsp;</label>
                                <label><input type="radio" name="greek_knwlg" value="1"
                                        <?php echo isset($profile["greek_knwlg"]) && $profile["greek_knwlg"] == 1 ? "checked" : "" ?>> <?php echo Language::show('limited', 'Members'); ?> &nbsp;</label>
                                <label><input type="radio" name="greek_knwlg" value="2"
                                        <?php echo isset($profile["greek_knwlg"]) && $profile["greek_knwlg"] == 2 ? "checked" : "" ?>> <?php echo Language::show('moderate', 'Members'); ?> &nbsp;</label>
                                <label><input type="radio" name="greek_knwlg" value="3"
                                        <?php echo isset($profile["greek_knwlg"]) && $profile["greek_knwlg"] == 3 ? "checked" : "" ?>> <?php echo Language::show('strong', 'Members'); ?> &nbsp;</label>
                                <label><input type="radio" name="greek_knwlg" value="4"
                                        <?php echo isset($profile["greek_knwlg"]) && $profile["greek_knwlg"] == 4 ? "checked" : "" ?>> <?php echo Language::show('expert', 'Members'); ?> &nbsp;</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="education"><?php echo Language::show('education', 'Members'); ?>: </label>
                            <div class="form-control">
                                <label><input type="checkbox" name="education[]" value="BA"
                                        <?php echo isset($profile["education"]) && in_array("BA", $profile["education"]) ? "checked" : "" ?>> <?php echo Language::show('ba_edu', 'Members'); ?> &nbsp;</label>
                                <label><input type="checkbox" name="education[]" value="MA"
                                        <?php echo isset($profile["education"]) && in_array("MA", $profile["education"]) ? "checked" : "" ?>> <?php echo Language::show('ma_edu', 'Members'); ?> &nbsp;</label>
                                <label><input type="checkbox" name="education[]" value="PHD"
                                        <?php echo isset($profile["education"]) && in_array("PHD", $profile["education"]) ? "checked" : "" ?>> <?php echo Language::show('phd_edu', 'Members'); ?> &nbsp;</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="ed_area"><?php echo Language::show('ed_area', 'Members'); ?>: </label>
                            <div class="form-control">
                                <label><input type="checkbox" name="ed_area[]" value="Theology"
                                        <?php echo isset($profile["ed_area"]) && in_array("Theology", $profile["ed_area"]) ? "checked" : "" ?>> <?php echo Language::show('theology', 'Members'); ?> &nbsp;</label>
                                <label><input type="checkbox" name="ed_area[]" value="Pastoral Ministry"
                                        <?php echo isset($profile["ed_area"]) && in_array("Pastoral Ministry", $profile["ed_area"]) ? "checked" : "" ?>> <?php echo Language::show('pastoral_ministry', 'Members'); ?> &nbsp;</label>
                                <label><input type="checkbox" name="ed_area[]" value="Bible Translation"
                                        <?php echo isset($profile["ed_area"]) && in_array("Bible Translation", $profile["ed_area"]) ? "checked" : "" ?>> <?php echo Language::show('bible_translation', 'Members'); ?> &nbsp;</label>
                                <label><input type="checkbox" name="ed_area[]" value="Exegetics"
                                        <?php echo isset($profile["ed_area"]) && in_array("Exegetics", $profile["ed_area"]) ? "checked" : "" ?>> <?php echo Language::show('exegetics', 'Members'); ?> &nbsp;</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="ed_place"><?php echo Language::show('ed_place', 'Members'); ?>: </label>
                            <input type="text" class="form-control" name="ed_place"
                                   value="<?php echo isset($profile["ed_place"]) ? $profile["ed_place"] : "" ?>">
                        </div>
                    </div>

                    <input type="hidden" name="eventID" id="eventID" value="" />
                    <input type="hidden" name="userType" value="translator">

                    <br><br>

                    <button type="submit" name="applyEvent" class="btn btn-primary"><?php echo Language::show('apply_now', 'Events'); ?></button>
                    <img class="applyEventLoader" width="24px" src="<?php echo \Helpers\Url::templatePath() ?>img/loader.gif">
                </form>
            </div>

            <!--<div class="col-sm-4">
                <div class="help_info">
                    <div class="help_title"><?php echo Language::show('help', 'Events'); ?></div>
                    <div class="help_name"><?php echo Language::show('translator', 'Members'); ?></div>
                    <div class="help_descr">Description about translator's role</div>

                    <div class="help_name"><?php echo Language::show('checker', 'Members'); ?></div>
                    <div class="help_descr">Description about checker's role</div>
                </div>
            </div>-->

        </div>
    </div>
</div>