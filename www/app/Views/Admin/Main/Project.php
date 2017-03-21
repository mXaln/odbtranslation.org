<?
use Helpers\Constants\EventStates;
use Support\Facades\Cache;

$language = Language::code();

if(!empty($data["project"])):
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo $data["project"][0]->tLang . " [".__($data["project"][0]->bookProject)."]" ?></h1>
    </div>

    <div class="form-inline dt-bootstrap no-footer">
        <div class="row">
            <div class="col-sm-6">
                <ul class="nav nav-pills book-parts">
                    <li role="presentation" class="active"><a href="#old_test"><?php echo __("old_test") ?></a></li>
                    <li role="presentation"><a href="#new_test"><?php echo __("new_test") ?></a></li>
                </ul>
            </div>
            <div class="add-event-btn col-sm-6"></div>
        </div>

        <?php foreach($data["events"] as $event): ?>
            <?php if($event->abbrID == 1): ?>
            <div class="row" id="old_test">
                <div class="col-sm-12">
            <?php elseif($event->abbrID == 41): ?>
                <div class="row" id="new_test">
                    <div class="col-sm-12">
            <?php endif; ?>
                <?php if($event->abbrID == 1 || $event->abbrID == 41): ?>
                <table class="table table-bordered table-hover" role="grid">
                    <thead>
                    <tr>
                        <th><?php echo __("book") ?></th>
                        <th><?php echo __("translators") ?></th>
                        <th><?php echo __('level', [2]); ?></th>
                        <th><?php echo __('level', [3]); ?></th>
                        <th><?php echo __("time_start") ?></th>
                        <th><?php echo __("time_end") ?></th>
                        <th><?php echo __("state") ?></th>
                        <th></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                <?php endif; ?>
                        <tr>
                            <td><?php echo $event->name ?></td>
                            <td><?php echo $event->translators . "/" . (integer)$event->translatorsNum ?></td>
                            <td><?php echo $event->checkers_l2 . "/" . (integer)$event->l2CheckersNum ?></td>
                            <td><?php echo $event->checkers_l3 . "/" . (integer)$event->l3CheckersNum ?></td>
                            <td class="datetime" data="<?php echo $event->dateFrom != "" ? date(DATE_RFC2822, strtotime($event->dateFrom)) : "" ?>"><?php echo $event->dateFrom != "" ? $event->dateFrom . " UTC" : "" ?></td>
                            <td class="datetime" data="<?php echo $event->dateTo != "" ? date(DATE_RFC2822, strtotime($event->dateTo)) : "" ?>"><?php echo $event->dateTo != "" ? $event->dateTo . " UTC" : "" ?></td>
                            <td><?php echo $event->state ? __("state_".$event->state) : "" ?></td>
                            <td>
                                <?php if($event->state == EventStates::TRANSLATED): ?>
                                <button class="btn btn-warning showContributors" data="<? echo $event->eventID?>"><?php echo __("contributors") ?></button>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?
                                switch($event->state)
                                {
                                    case null:
                                        echo '<button 
                                            data="'.$event->code.'" 
                                            data2="'.$event->name.'" 
                                            data3="'.$event->chaptersNum.'" 
                                                class="btn btn-primary startEvnt">'.__("create").'</button>';
                                        break;

                                    default:
                                        echo '<button 
                                            data="'.$event->code.'" data2="'.$event->eventID.'" 
                                            data3="'.(Cache::has($event->code."_".$data["project"][0]->sourceLangID."_".$data["project"][0]->bookProject."_usfm")).'" 
                                            data4="'.$event->abbrID.'"
                                                class="btn btn-success editEvnt">'.__("edit").'</button>';
                                }
                                ?>
                            </td>
                        </tr>

            <?php if($event->abbrID == 39): ?>
                    </tbody>
                </table>
                </div>
            </div>
            <?php elseif($event->abbrID == 67): ?>
                    </tbody>
                </table>
                </div>
            </div>
            <?php endif; ?>
        <?php endforeach ?>
    </div>
</div>

<div class="event-content form-panel">
    <div class="create-event-content panel panel-default">
        <div class="panel-heading">
            <h1 class="panel-title"><?php echo __("create_event"); ?></h1>
            <span class="panel-close glyphicon glyphicon-remove"></span>
        </div>

        <div class="page-content row panel-body">
            <div class="bookName"></div>
            <div class="book_info_content"></div>
            <div class="clear"></div>

            <div class="errors"></div>

            <div class="row">
                <div class="col-sm-12">
                    <form action="/admin/rpc/create_event" method="post" id="startEvent">
                        <div class="form-group row numbersGroup">
                            <div class="col-sm-3 participantsNum">
                                <label for="translators" style="width: 100%; display: block"><?php echo __('translators'); ?></label>
                                <input type="text" class="form-control" id="translators" name="translators" size="3" value="<?php if(isset($error)){ echo $_POST['translators']; } ?>">
                                <img src="<?php echo template_url("img/note.png")?>" data-toggle="tooltip" data-placement="right" title="<?php echo __('max_translators'); ?>">
                            </div>
                            <div class="col-sm-3 participantsNum">
                                <label for="checkers_l2" style="width: 100%; display: block"><?php echo __('level', [2]); ?></label>
                                <input type="text" class="form-control" id="checkers_l2" name="checkers_l2" size="3" value="<?php if(isset($error)){ echo $_POST['checkers_l2']; } ?>">
                                <img src="<?php echo template_url("img/note.png")?>" data-toggle="tooltip" data-placement="right" title="<?php echo __('max_checkers_l2'); ?>">
                            </div>
                            <div class="col-sm-3 participantsNum">
                                <label for="checkers_l3" style="width: 100%; display: block"><?php echo __('level', [3]); ?></label>
                                <input type="text" class="form-control" id="checkers_l3" name="checkers_l3" size="3" value="<?php if(isset($error)){ echo $_POST['checkers_l3']; } ?>">
                                <img src="<?php echo template_url("img/note.png")?>" data-toggle="tooltip" data-placement="right" title="<?php echo __('max_checkers_l3'); ?>">
                            </div>
                        </div>


                        <div class="form-group">
                            <label for="cal_from" style="width: 100%; display: block"><?php echo __('time_start'); ?></label>
                            <input type="text" class="form-control" id="cal_from" name="cal_from" autocomplete="off" value="<?php if(isset($error)){ echo $_POST['cal_from']; } ?>">
                        </div>

                        <div class="form-group">
                            <label for="cal_to" style="width: 100%; display: block"><?php echo __('time_end'); ?></label>
                            <input type="text" class="form-control" id="cal_to" name="cal_to" autocomplete="off" value="<?php if(isset($error)){ echo $_POST['cal_to']; } ?>">
                        </div>

                        <div class="form-group" style="width: 350px;">
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
                        <input type="hidden" name="bookProject" id="bookProject" value="<?php echo $data["project"][0]->bookProject?>" />
                        <input type="hidden" name="sourceLangID" id="sourceLangID" value="<?php echo $data["project"][0]->sourceLangID?>" />

                        <br><br>

                        <button type="submit" name="startEvent" class="btn btn-primary"><?php echo __("create"); ?></button>
                        <button type="submit" name="deleteEvent" class="btn btn-danger"><?php echo __("delete"); ?></button>

                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <button name="progressEvent" class="btn btn-success"><?php echo __("progress"); ?></button>
                        <button name="manageEvent" class="btn btn-warning"><?php echo __("manage"); ?></button>
                        &nbsp;&nbsp;
                        <button name="" class="btn btn-danger" title="<?php echo __("clear_cache_info") ?>"><?php echo __("clear_cache"); ?></button>
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