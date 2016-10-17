<?php
use Helpers\Constants\EventStates;
use Helpers\Url;

$code = isset($_COOKIE['lang']) ? $_COOKIE['lang'] : "en";

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
                        <th><?php echo __("checkers_l2") ?></th>
                        <th><?php echo __("checkers_l3") ?></th>
                        <th><?php echo __("time_start") ?></th>
                        <th><?php echo __("time_end") ?></th>
                        <th><?php echo __("state") ?></th>
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
                                <?php
                                switch($event->state)
                                {
                                    case null:
                                        echo '<button data="'.$event->code.'" data2="'.$event->name.'" data3="'.$event->chaptersNum.'" class="btn btn-primary startEvnt">'.__("create_event").'</button>';
                                        break;

                                    case EventStates::STARTED:

                                        break;
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
                        <div class="form-group">
                            <label for="translators"><?php echo __('max_translators'); ?>
                                <br>
                                <input type="text" class="form-control" id="translators" name="translators" value="<?php if(isset($error)){ echo $_POST['translators']; } ?>">
                            </label>
                        </div>

                        <div class="form-group">
                            <label for="checkers_l2"><?php echo __('max_checkers_l2'); ?>
                                <br>
                                <input type="text" class="form-control" id="checkers_l2" name="checkers_l2" value="<?php if(isset($error)){ echo $_POST['checkers_l2']; } ?>">
                            </label>
                        </div>

                        <div class="form-group">
                            <label for="checkers_l3"><?php echo __('max_checkers_l3'); ?>
                                <br>
                                <input type="text" class="form-control" id="checkers_l3" name="checkers_l3" value="<?php if(isset($error)){ echo $_POST['checkers_l3']; } ?>">
                            </label>
                        </div>

                        <div class="form-group">
                            <label for="cal_from"><?php echo __('time_start'); ?>
                                <input type="text" class="form-control" id="cal_from" name="cal_from" autocomplete="off" value="<?php if(isset($error)){ echo $_POST['cal_from']; } ?>">
                            </label>
                        </div>

                        <div class="form-group">
                            <label for="cal_to"><?php echo __('time_end'); ?>
                                <input type="text" class="form-control" id="cal_to" name="cal_to" autocomplete="off" value="<?php if(isset($error)){ echo $_POST['cal_to']; } ?>">
                            </label>
                        </div>

                        <input type="hidden" name="book_code" id="bookCode" value="" />
                        <input type="hidden" name="projectID" id="projectID" value="<?php echo $data["project"][0]->projectID?>" />
                        <input type="hidden" name="bookProject" id="bookProject" value="<?php echo $data["project"][0]->bookProject?>" />
                        <input type="hidden" name="sourceLangID" id="sourceLangID" value="<?php echo $data["project"][0]->sourceLangID?>" />

                        <br><br>

                        <button type="submit" name="startEvent" class="btn btn-primary"><?php echo __("create_event"); ?></button>
                        <img class="startEventLoader" width="24px" src="<?php echo Url::templatePath() ?>img/loader.gif">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div>Project does not exist or you do not have rights to see it</div>
<?php endif; ?>

<link href="<?php echo Url::templatePath()?>css/jquery-ui-timepicker-addon.css" type="text/css" rel="stylesheet" />
<script src="<?php echo Url::templatePath()?>js/jquery-ui-timepicker-addon.min.js"></script>
<script src="<?php echo Url::templatePath()?>js/i18n/jquery-ui-timepicker-<?php echo $code ?>.js"></script>
<script src="<?php echo Url::templatePath()?>js/i18n/datepicker-<?php echo $code ?>.js"></script>
