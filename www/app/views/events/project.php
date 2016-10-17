<?php
use Helpers\Session;
use Helpers\Url;

$profile = Session::get("profile");

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

                <div class="row event-block">
                    <div class="col-sm-3 event-box">
                        <div class="book-name"><?php echo $event->name ?></div>
                    </div>

                    <div class="col-sm-3">
                        <div class="event-box">
                            <div class="event-header"><?php echo __("draft1") ?></div>

                            <div><?php echo __("translators") ?>: <?php echo $event->translators . "/" . (integer)$event->translatorsNum ?></div>

                            <div>
                                <?php echo $event->translatorsNum > $event->translators
                                    ? '<button data="'.$event->eventID.'" data2="'.$event->name.'" data3="d1" class="btn btn-primary applyDraft applyEvent">'.__("apply").'</button>'
                                    : 'N/A' ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-3">
                        <div class="event-box">
                            <div class="event-header"><?php echo __("level2_3_check", array(2)) ?></div>

                            <div><?php echo __("checkers") ?>: <?php echo $event->checkers_l2 . "/" . (integer)$event->l2CheckersNum ?></div>

                            <div>
                                <?php echo $event->l2CheckersNum > $event->checkers_l2
                                    ? '<button data="'.$event->eventID.'" data2="'.$event->name.'" data3="l2" class="btn btn-primary applyL2Checkers applyEvent">'.__("apply").'</button>'
                                    : 'N/A' ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-3 event-box">
                        <div class="event-box">
                            <div class="event-header"><?php echo __("level2_3_check", array(3)) ?></div>

                            <div><?php echo __("checkers") ?>: <?php echo $event->checkers_l3 . "/" . (integer)$event->l3CheckersNum ?></div>

                            <div>
                                <?php echo $event->l3CheckersNum > $event->checkers_l3
                                    ? '<button data="'.$event->eventID.'" data2="'.$event->name.'" data3="l3" class="btn btn-primary applyL3Checkers applyEvent">'.__("apply").'</button>'
                                    : 'N/A' ?>
                            </div>
                        </div>
                    </div>
                </div>

            <?php if($event->abbrID == 39): ?>
                </div>
            </div>
            <?php elseif($event->abbrID == 67): ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endforeach ?>

    </div>
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
                        <h3 class="ftr"><?php echo __("apply_as_translator") ?></h3>
                        <h3 class="fl2" style="display: none"><?php echo __("apply_as_checker", array(2)) ?></h3>
                        <h3 class="fl3" style="display: none"><?php echo __("apply_as_checker", array(3)) ?></h3>
                    </div>

                    <div class="checker_info">
                        <div class="form-group">
                            <label class="church_role"><?php echo __('church_role'); ?>: </label>
                            <div class="form-control">
                                <label><input type="checkbox" name="church_role[]" value="Elder"
                                        <?php echo isset($profile["church_role"]) && in_array("Elder", $profile["church_role"]) ? "checked" : "" ?>> <?php echo __('elder'); ?> &nbsp;</label>
                                <label><input type="checkbox" name="church_role[]" value="Bishop"
                                        <?php echo isset($profile["church_role"]) && in_array("Bishop", $profile["church_role"]) ? "checked" : "" ?>> <?php echo __('bishop'); ?> &nbsp;</label>
                                <label><input type="checkbox" name="church_role[]" value="Pastor"
                                        <?php echo isset($profile["church_role"]) && in_array("Pastor", $profile["church_role"]) ? "checked" : "" ?>> <?php echo __('pastor'); ?> &nbsp;</label>
                                <label><input type="checkbox" name="church_role[]" value="Teacher"
                                        <?php echo isset($profile["church_role"]) && in_array("Teacher", $profile["church_role"]) ? "checked" : "" ?>> <?php echo __('teacher'); ?> &nbsp;</label>
                                <label><input type="checkbox" name="church_role[]" value="Denominational Leader"
                                        <?php echo isset($profile["church_role"]) && in_array("Denominational Leader", $profile["church_role"]) ? "checked" : "" ?>> <?php echo __('denominational_leader'); ?> &nbsp;</label>
                                <label><input type="checkbox" name="church_role[]" value="Seminary Professor"
                                        <?php echo isset($profile["church_role"]) && in_array("Seminary Professor", $profile["church_role"]) ? "checked" : "" ?>> <?php echo __('seminary_professor'); ?> &nbsp;</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><?php echo __('orig_langs'); ?>: </label>
                            <div class="form-control">
                                <label class="hebrew_knwlg"><?php echo __('hebrew_knwlg'); ?>: </label> &nbsp;&nbsp;
                                <label><input type="radio" name="hebrew_knwlg" value="0"
                                        <?php echo isset($profile["hebrew_knwlg"]) && $profile["hebrew_knwlg"] == 0 ? "checked" : "" ?>> <?php echo __('none'); ?> &nbsp;</label>
                                <label><input type="radio" name="hebrew_knwlg" value="1"
                                        <?php echo isset($profile["hebrew_knwlg"]) && $profile["hebrew_knwlg"] == 1 ? "checked" : "" ?>> <?php echo __('limited'); ?> &nbsp;</label>
                                <label><input type="radio" name="hebrew_knwlg" value="2"
                                        <?php echo isset($profile["hebrew_knwlg"]) && $profile["hebrew_knwlg"] == 2 ? "checked" : "" ?>> <?php echo __('moderate'); ?> &nbsp;</label>
                                <label><input type="radio" name="hebrew_knwlg" value="3"
                                        <?php echo isset($profile["hebrew_knwlg"]) && $profile["hebrew_knwlg"] == 3 ? "checked" : "" ?>> <?php echo __('strong'); ?> &nbsp;</label>
                                <label><input type="radio" name="hebrew_knwlg" value="4"
                                        <?php echo isset($profile["hebrew_knwlg"]) && $profile["hebrew_knwlg"] == 4 ? "checked" : "" ?>> <?php echo __('expert'); ?> &nbsp;</label>
                            </div>
                            <br>
                            <div class="form-control">
                                <label class="greek_knwlg"><?php echo __('greek_knwlg'); ?>: </label> &nbsp;&nbsp;
                                <label><input type="radio" name="greek_knwlg" value="0"
                                        <?php echo isset($profile["greek_knwlg"]) && $profile["greek_knwlg"] == 0 ? "checked" : "" ?>> <?php echo __('none'); ?> &nbsp;</label>
                                <label><input type="radio" name="greek_knwlg" value="1"
                                        <?php echo isset($profile["greek_knwlg"]) && $profile["greek_knwlg"] == 1 ? "checked" : "" ?>> <?php echo __('limited'); ?> &nbsp;</label>
                                <label><input type="radio" name="greek_knwlg" value="2"
                                        <?php echo isset($profile["greek_knwlg"]) && $profile["greek_knwlg"] == 2 ? "checked" : "" ?>> <?php echo __('moderate'); ?> &nbsp;</label>
                                <label><input type="radio" name="greek_knwlg" value="3"
                                        <?php echo isset($profile["greek_knwlg"]) && $profile["greek_knwlg"] == 3 ? "checked" : "" ?>> <?php echo __('strong'); ?> &nbsp;</label>
                                <label><input type="radio" name="greek_knwlg" value="4"
                                        <?php echo isset($profile["greek_knwlg"]) && $profile["greek_knwlg"] == 4 ? "checked" : "" ?>> <?php echo __('expert'); ?> &nbsp;</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="education"><?php echo __('education'); ?>: </label>
                            <div class="form-control">
                                <label><input type="checkbox" name="education[]" value="BA"
                                        <?php echo isset($profile["education"]) && in_array("BA", $profile["education"]) ? "checked" : "" ?>> <?php echo __('ba_edu'); ?> &nbsp;</label>
                                <label><input type="checkbox" name="education[]" value="MA"
                                        <?php echo isset($profile["education"]) && in_array("MA", $profile["education"]) ? "checked" : "" ?>> <?php echo __('ma_edu'); ?> &nbsp;</label>
                                <label><input type="checkbox" name="education[]" value="PHD"
                                        <?php echo isset($profile["education"]) && in_array("PHD", $profile["education"]) ? "checked" : "" ?>> <?php echo __('phd_edu'); ?> &nbsp;</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="ed_area"><?php echo __('ed_area'); ?>: </label>
                            <div class="form-control">
                                <label><input type="checkbox" name="ed_area[]" value="Theology"
                                        <?php echo isset($profile["ed_area"]) && in_array("Theology", $profile["ed_area"]) ? "checked" : "" ?>> <?php echo __('theology'); ?> &nbsp;</label>
                                <label><input type="checkbox" name="ed_area[]" value="Pastoral Ministry"
                                        <?php echo isset($profile["ed_area"]) && in_array("Pastoral Ministry", $profile["ed_area"]) ? "checked" : "" ?>> <?php echo __('pastoral_ministry'); ?> &nbsp;</label>
                                <label><input type="checkbox" name="ed_area[]" value="Bible Translation"
                                        <?php echo isset($profile["ed_area"]) && in_array("Bible Translation", $profile["ed_area"]) ? "checked" : "" ?>> <?php echo __('bible_translation'); ?> &nbsp;</label>
                                <label><input type="checkbox" name="ed_area[]" value="Exegetics"
                                        <?php echo isset($profile["ed_area"]) && in_array("Exegetics", $profile["ed_area"]) ? "checked" : "" ?>> <?php echo __('exegetics'); ?> &nbsp;</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="ed_place"><?php echo __('ed_place'); ?>: </label>
                            <input type="text" class="form-control" name="ed_place"
                                   value="<?php echo isset($profile["ed_place"]) ? $profile["ed_place"] : "" ?>">
                        </div>
                    </div>

                    <input type="hidden" name="eventID" id="eventID" value="" />
                    <input type="hidden" name="userType" value="translator">

                    <br><br>

                    <button type="submit" name="applyEvent" class="btn btn-primary"><?php echo __('apply_now'); ?></button>
                    <img class="applyEventLoader" width="24px" src="<?php echo Url::templatePath() ?>img/loader.gif">
                </form>
            </div>

            <!--<div class="col-sm-4">
                <div class="help_info">
                    <div class="help_title"><?php echo __('help'); ?></div>
                    <div class="help_name"><?php echo __('translator'); ?></div>
                    <div class="help_descr">Description about translator's role</div>

                    <div class="help_name"><?php echo __('checker'); ?></div>
                    <div class="help_descr">Description about checker's role</div>
                </div>
            </div>-->

        </div>
    </div>
</div>
<?php else: ?>
<div>Project does not exist or you do not have rights to see it</div>
<?php endif; ?>