<?php
use Helpers\Constants\EventStates;

$language = Language::code();

if(!empty($data["project"])):
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h1 class="panel-title">
            <?php echo "[".$data["project"][0]->targetLang."] " 
                . $data["project"][0]->tLang 
                . ($data["project"][0]->tLang != $data["project"][0]->tAng 
                    && $data["project"][0]->tAng != "" ? " (" . $data["project"][0]->tAng . ")" : "")
                . " - ".__($data["project"][0]->bookProject) ?>
        </h1>
    </div>

    <div class="form-inline dt-bootstrap no-footer">
        <div style="display: flex; margin-bottom: 50px; border-bottom: 1px solid #ccc;">
            <div style="flex: 2; display: flex; justify-content: flex-end">
                <div class="add-event-btn">
                    <img class="contibLoader" width="24px" src="<?php echo template_url("img/loader.gif") ?>">
                    <button style="margin-top: 12px" class="btn btn-warning showAllContibutors"
                            data-projectid="<?php echo $data["project"][0]->projectID ?>"><?php echo __("all_contributors") ?></button>
                </div>
                <div class="add-event-btn">
                    <img class="cacheLoader" width="24px" src="<?php echo template_url("img/loader.gif") ?>">
                    <button style="margin-top: 12px;" class="btn btn-danger"
                            name="updateAllCache"
                            data-sourcelangid="<?php echo $data["project"][0]->sourceLangID ?>"
                            data-sourcebible="<?php echo $data["project"][0]->sourceBible ?>"><?php echo __("update_cache_all") ?></button>
                </div>
            </div>
        </div>

        <?php foreach($data["events"] as $event): ?>
            <?php if($event->abbrID < 68 || $event->abbrID > 70) continue; ?>
            <div class="row" id="old_test">
                <div class="project_progress progress <?php echo $data["TWprogress"] <= 0 ? "zero" : ""?>"
                     style="left: 30px;">
                    <div class="progress-bar progress-bar-success" role="progressbar"
                         aria-valuenow="<?php echo floor($data["OTprogress"]) ?>"
                         aria-valuemin="0" aria-valuemax="100" style="min-width: 0em; width: <?php echo floor($data["OTprogress"])."%" ?>">
                        <?php echo floor($data["OTprogress"])."%" ?>
                    </div>
                </div>
                <div class="col-sm-12">
                    <?php if($event->abbrID == 68): ?>
                    <table class="table table-bordered table-hover" role="grid">
                        <thead>
                        <tr>
                            <th><?php echo __("book") ?></th>
                            <th><?php echo __("time_start") ?></th>
                            <th><?php echo __("time_end") ?></th>
                            <th><?php echo __("state") ?></th>
                            <th><?php echo __("contributors") ?></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                    <?php endif; ?>
                        <tr>
                            <td><?php echo $event->name ?></td>
                            <td class="datetime" data="<?php echo $event->dateFrom != "" && $event->dateFrom != "0000-00-00 00:00:00" ?
                                date(DATE_RFC2822, strtotime($event->dateFrom)) : "" ?>">
                                <?php echo $event->dateFrom != "" && $event->dateFrom != "0000-00-00 00:00:00" ? $event->dateFrom . " UTC" : "" ?></td>
                            <td class="datetime" data="<?php echo $event->dateTo != "" && $event->dateTo != "0000-00-00 00:00:00" ?
                                date(DATE_RFC2822, strtotime($event->dateTo)) : "" ?>">
                                <?php echo $event->dateTo != "" && $event->dateTo != "0000-00-00 00:00:00" ? $event->dateTo . " UTC" : "" ?></td>
                            <td><?php echo $event->state ? __("state_".$event->state) : "" ?></td>
                            <td>
                                <?php if($event->state != "" && EventStates::enum($event->state) >= EventStates::enum(EventStates::L2_CHECKED)): ?>
                                    <button class="btn btn-warning showContributors"
                                            data-eventid="<?php echo $event->eventID?>"
                                            data-level="2"
                                            data-mode="<?php echo $data["project"][0]->bookProject ?>">
                                        <?php echo __("L2") ?>
                                    </button>
                                <?php endif; ?>
                                <?php if($event->state != "" && EventStates::enum($event->state) >= EventStates::enum(EventStates::COMPLETE)): ?>
                                    <button class="btn btn-warning showContributors"
                                            data-eventid="<?php echo $event->eventID?>"
                                            data-level="3"
                                            data-mode="<?php echo $data["project"][0]->bookProject ?>">
                                        <?php echo __("L3") ?>
                                    </button>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                switch($event->state)
                                {
                                    case null:
                                        echo '<button 
                                            data-bookcode="'.$event->code.'" 
                                            data-bookname="'.$event->name.'" 
                                            data-chapternum="'.$event->chaptersNum.'" 
                                                class="btn btn-primary startEvnt">'.__("create").'</button>';
                                        break;

                                    default:
                                        echo '<button 
                                            data-bookcode="'.$event->code.'" 
                                            data-eventid="'.$event->eventID.'" 
                                            data-abbrid="'.$event->abbrID.'"
                                                class="btn btn-success editEvnt">'.__("edit").'</button>';
                                }
                                ?>
                            </td>
                        </tr>

            <?php if($event->abbrID == 70): ?>
                    </tbody>
                </table>
                </div>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

<div class="event-content form-panel">
    <div class="create-event-content panel panel-default">
        <div class="panel-heading">
            <h1 class="panel-title"><?php echo __("create_event"); ?></h1>
            <span name="importTranslation" class="glyphicon glyphicon-download-alt importTranslation"></span>
            <span class="glyphicon glyphicon-info-sign importInfo" title="<?php echo __("import_translation_tip") ?>"></span>
            <span class="panel-close glyphicon glyphicon-remove"></span>
        </div>

        <div class="page-content row panel-body">
            <div class="bookName"></div>
            <div class="clear"></div>

            <div class="errors"></div>

            <div class="row">
                <div class="col-sm-12">
                    <form action="/admin/rpc/create_tw_event" method="post" id="startEvent">
                        <div class="form-group" style="width: 450px;">
                            <label for="adminsSelect" style="width: 100%; display: block"><?php echo __('facilitators'); ?></label>
                            <select class="form-control" name="admins[]" id="adminsSelect" multiple data-placeholder="<?php echo __("add_admins_by_username") ?>">
                                <option></option>
                            </select>
                        </div>

                        <div class="form-group delinput" style="width: 350px; display: none">
                            <label for="delevnt" style="width: 100%; display: block; color: #f00;"><?php echo __('delete_warning'); ?></label>
                            <input class="form-control" type="text" id="delevnt" autocomplete="off">
                        </div>

                        <input type="hidden" name="eID" id="eID" value="">
                        <input type="hidden" name="act" id="eventAction" value="create">
                        <input type="hidden" name="abbrID" id="abbrID" value="" />
                        <input type="hidden" name="book_code" id="bookCode" value="" />
                        <input type="hidden" name="projectID" id="projectID" value="<?php echo $data["project"][0]->projectID?>" />
                        <input type="hidden" name="sourceBible" id="sourceBible" value="<?php echo $data["project"][0]->sourceBible?>" />
                        <input type="hidden" name="bookProject" id="bookProject" value="<?php echo $data["project"][0]->bookProject?>" />
                        <input type="hidden" name="sourceLangID" id="sourceLangID" value="<?php echo $data["project"][0]->sourceLangID?>" />
                        <input type="hidden" name="targetLangID" id="targetLangID" value="<?php echo $data["project"][0]->targetLang?>" />

                        <br>

                        <label><?php echo __("translation_event") ?></label>
                        <br>
                        <button type="submit" name="startEvent" class="btn btn-primary"><?php echo __("create"); ?></button>
                        <button type="submit" name="deleteEvent" class="btn btn-danger"><?php echo __("delete"); ?></button>

                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <button name="progressEvent" data-mode="<?php echo $data["project"][0]->bookProject?>" class="btn btn-success"><?php echo __("progress"); ?></button>
                        <button name="manageEvent" data-mode="<?php echo $data["project"][0]->bookProject?>" class="btn btn-warning"><?php echo __("manage"); ?></button>
                        &nbsp;&nbsp;
                        <button name="clearCache" class="btn btn-danger" title="<?php echo __("clear_cache_info") ?>"><?php echo __("clear_cache"); ?></button>

                        <div class="breaks"><br><br></div>
                        <div class="l2_buttons">

                            <label><?php echo __("l2_event") ?></label>
                            <br>
                            <button type="submit" name="startL2Event" class="btn btn-primary"><?php echo __("create"); ?></button>
                            <button type="submit" name="deleteL2Event" class="btn btn-danger"><?php echo __("delete"); ?></button>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <button name="progressL2Event" data-mode="<?php echo $data["project"][0]->bookProject?>" class="btn btn-success"><?php echo __("progress"); ?></button>
                            <button name="manageL2Event" class="btn btn-warning"><?php echo __("manage"); ?></button>
                        </div>
                        <img class="startEventLoader" width="24px" src="<?php echo template_url("img/loader.gif") ?>">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="contributors_container">
    <div class="contributors_block">
        <div class="contributors-close glyphicon glyphicon-remove"></div>
        <div class="contributors_title"><?php echo __("event_contributors") ?></div>
        <div class="contributors_title proj"><?php echo __("contributors") ?></div>
        <div class="contributors_content"></div>
    </div>
</div>
<?php else: ?>
<div>Project does not exist or you do not have rights to see it</div>
<?php endif; ?>

<link href="<?php echo template_url("css/jquery-ui-timepicker-addon.css")?>" type="text/css" rel="stylesheet" />
<script src="<?php echo template_url("js/jquery-ui-timepicker-addon.min.js")?>"></script>
<?php if($language != "en"): ?>
<script src="<?php echo template_url("js/i18n/jquery-ui-timepicker-".$language.".js")?>"></script>
<script src="<?php echo template_url("js/i18n/datepicker-".$language.".js")?>"></script>
<?php endif; ?>

<link href="<?php echo template_url("css/chosen.min.css")?>" type="text/css" rel="stylesheet" />
<script src="<?php echo template_url("js/chosen.jquery.min.js")?>"></script>
<script src="<?php echo template_url("js/ajax-chosen.min.js")?>"></script>