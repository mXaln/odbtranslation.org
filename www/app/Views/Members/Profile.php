<?php
use Helpers\Session;
use Shared\Legacy\Error;
?>

<h1><?php echo __('profile_message'); ?></h1>

<?php
$profile = $data["profile"];
?>

<div>
    <div class="public_uname"><?php echo Session::get("userName") ?></div>
    <div class="public_fname"><?php echo Session::get("firstName")." ".Session::get("lastName") ?></div>
</div>

<form action='' method='post' style="width: 900px" class="form-horizontal profile_form">

    <div class="profile_messages">
        <?php
        echo Error::display($error);
        echo Session::message();
        ?>
    </div>

    <h3><?php echo __('common_skills'); ?></h3>

    <div class="form-group">
        <label class="<?php echo isset($data["errors"]["avatar"]) ? "label_error" : "" ?>">
            <?php echo __('avatar'); ?>:
        </label>
        <div class="form-control avatar_control">
            <img src="<?php echo template_url("img/avatars/".(isset($_POST["avatar"]) ? $_POST["avatar"] : (isset($profile["avatar"]) ? $profile["avatar"] : "m1")).".png") ?>">
            <div class="avatar_buttons">
                <button class="btn btn-primary avatar_btn" id="avatarMales"><?php echo __('male'); ?></button>
                <button class="btn btn-primary avatar_btn" id="avatarFemales"><?php echo __('female'); ?></button>
            </div>
            <input type="hidden" name="avatar" autocomplete="off"
                   value="<?php echo isset($_POST["avatar"]) ? $_POST["avatar"] : (isset($profile["avatar"]) ? $profile["avatar"] : "m1") ?>">
        </div>
    </div>

    <div class="form-group">
        <label class="<?php echo isset($data["errors"]["prefered_roles"]) ? "label_error" : "" ?>"><?php echo __('prefered_roles'); ?>: </label>
        <div class="form-control">
            <label><input type="checkbox" class="tr_role" name="prefered_roles[]" value="translator"
                    <?php echo isset($_POST["prefered_roles"]) && in_array("translator", $_POST["prefered_roles"]) ? "checked" :
                        (isset($profile["prefered_roles"]) && in_array("translator", $profile["prefered_roles"]) ? "checked" : "") ?>> <?php echo __('translator'); ?> &nbsp;</label>
            <label><input type="checkbox" class="fc_role" name="prefered_roles[]" value="facilitator"
                    <?php echo isset($_POST["prefered_roles"]) && in_array("facilitator", $_POST["prefered_roles"]) ? "checked" :
                        (isset($profile["prefered_roles"]) && in_array("facilitator", $profile["prefered_roles"]) ? "checked" : "") ?>> <?php echo __('facilitator'); ?> &nbsp;</label>
            <label><input type="checkbox" class="ch_role" name="prefered_roles[]" value="checker"
                    <?php echo isset($_POST["prefered_roles"]) && in_array("checker", $_POST["prefered_roles"]) ? "checked" :
                        (isset($profile["prefered_roles"]) && in_array("checker", $profile["prefered_roles"]) ? "checked" : "") ?>> <?php echo __('checker'); ?> &nbsp;</label>
        </div>
    </div>

    <label for="known_languages" class="<?php echo isset($data["errors"]["langs"]) ? "label_error" : "" ?>">
        <?php echo __('known_languages'); ?>:
    </label>
    <div class="form-group">
        <div class="language_add glyphicon glyphicon-plus"></div>
        <div class="language_add_input">
            <select class="form-control langs" name="langs[]" multiple data-placeholder="<?php echo __("show_langs_window")?>" disabled >
                <?php if(isset($_POST['langs'])): ?>
                    <?php foreach ($_POST['langs'] as $lang): ?>
                        <option value="<?php echo $lang?>" selected><?php echo $lang?></option>
                    <?php endforeach; ?>
                <?php elseif(isset($profile["languages"])):?>
                    <?php foreach ($profile["languages"] as $lang => $values):
                        if(isset($values["isAdmin"])) continue;?>
                        <option value="<?php echo $lang.":".$values["lang_fluency"].":".$values["geo_lang_yrs"]?>" selected><?php echo $lang?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="clear"></div>
    </div>

    <div class="form-group">
        <label class="<?php echo isset($data["errors"]["bbl_trans_yrs"]) ? "label_error" : "" ?>">
            <?php echo __('bbl_trans_yrs'); ?>:
        </label>
        <div class="form-control">
            <label><input type="radio" name="bbl_trans_yrs" value="1"
                    <?php echo isset($_POST["bbl_trans_yrs"]) && $_POST["bbl_trans_yrs"] == 1 ? "checked" :
                        (isset($profile["bbl_trans_yrs"]) && $profile["bbl_trans_yrs"] == 1 ? "checked" : "") ?>> 0 &nbsp;</label>
            <label><input type="radio" name="bbl_trans_yrs" value="2"
                    <?php echo isset($_POST["bbl_trans_yrs"]) && $_POST["bbl_trans_yrs"] == 2 ? "checked" :
                        (isset($profile["bbl_trans_yrs"]) && $profile["bbl_trans_yrs"] == 2 ? "checked" : "") ?>> 1 &nbsp;</label>
            <label><input type="radio" name="bbl_trans_yrs" value="3"
                    <?php echo isset($_POST["bbl_trans_yrs"]) && $_POST["bbl_trans_yrs"] == 3 ? "checked" :
                        (isset($profile["bbl_trans_yrs"]) && $profile["bbl_trans_yrs"] == 3 ? "checked" : "") ?>> 2 &nbsp;</label>
            <label><input type="radio" name="bbl_trans_yrs" value="4"
                    <?php echo isset($_POST["bbl_trans_yrs"]) && $_POST["bbl_trans_yrs"] == 4 ? "checked" :
                        (isset($profile["bbl_trans_yrs"]) && $profile["bbl_trans_yrs"] == 4 ? "checked" : "") ?>> 3+ &nbsp;</label>
        </div>
    </div>

    <div class="form-group">
        <label class="<?php echo isset($data["errors"]["othr_trans_yrs"]) ? "label_error" : "" ?>">
            <?php echo __('othr_trans_yrs'); ?>:
        </label>
        <div class="form-control">
            <label><input type="radio" name="othr_trans_yrs" value="1"
                    <?php echo isset($_POST["othr_trans_yrs"]) && $_POST["othr_trans_yrs"] == 1 ? "checked" :
                        (isset($profile["othr_trans_yrs"]) && $profile["othr_trans_yrs"] == 1 ? "checked" : "") ?>> 0 &nbsp;</label>
            <label><input type="radio" name="othr_trans_yrs" value="2"
                    <?php echo isset($_POST["othr_trans_yrs"]) && $_POST["othr_trans_yrs"] == 2 ? "checked" :
                        (isset($profile["othr_trans_yrs"]) && $profile["othr_trans_yrs"] == 2 ? "checked" : "") ?>> 1 &nbsp;</label>
            <label><input type="radio" name="othr_trans_yrs" value="3"
                    <?php echo isset($_POST["othr_trans_yrs"]) && $_POST["othr_trans_yrs"] == 3 ? "checked" :
                        (isset($profile["othr_trans_yrs"]) && $profile["othr_trans_yrs"] == 3 ? "checked" : "") ?>> 2 &nbsp;</label>
            <label><input type="radio" name="othr_trans_yrs" value="4"
                    <?php echo isset($_POST["othr_trans_yrs"]) && $_POST["othr_trans_yrs"] == 4 ? "checked" :
                        (isset($profile["othr_trans_yrs"]) && $profile["othr_trans_yrs"] == 4 ? "checked" : "") ?>> 3+ &nbsp;</label>
        </div>
    </div>

    <div class="form-group">
        <label class="<?php echo isset($data["errors"]["bbl_knwlg_degr"]) ? "label_error" : "" ?>"><?php echo __('bbl_knwlg_degr'); ?>: </label>
        <div class="form-control">
            <label><input type="radio" name="bbl_knwlg_degr" value="1"
                    <?php echo isset($_POST["bbl_knwlg_degr"]) && $_POST["bbl_knwlg_degr"] == 1 ? "checked" :
                        (isset($profile["bbl_knwlg_degr"]) && $profile["bbl_knwlg_degr"] == 1 ? "checked" : "") ?>> <?php echo __('weak'); ?> &nbsp;</label>
            <label><input type="radio" name="bbl_knwlg_degr" value="2"
                    <?php echo isset($_POST["bbl_knwlg_degr"]) && $_POST["bbl_knwlg_degr"] == 2 ? "checked" :
                        (isset($profile["bbl_knwlg_degr"]) && $profile["bbl_knwlg_degr"] == 2 ? "checked" : "") ?>> <?php echo __('moderate'); ?> &nbsp;</label>
            <label><input type="radio" name="bbl_knwlg_degr" value="3"
                    <?php echo isset($_POST["bbl_knwlg_degr"]) && $_POST["bbl_knwlg_degr"] == 3 ? "checked" :
                        (isset($profile["bbl_knwlg_degr"]) && $profile["bbl_knwlg_degr"] == 3 ? "checked" : "") ?>> <?php echo __('strong'); ?> &nbsp;</label>
            <label><input type="radio" name="bbl_knwlg_degr" value="4"
                    <?php echo isset($_POST["bbl_knwlg_degr"]) && $_POST["bbl_knwlg_degr"] == 4 ? "checked" :
                        (isset($profile["bbl_knwlg_degr"]) && $profile["bbl_knwlg_degr"] == 4 ? "checked" : "") ?>> <?php echo __('expert'); ?> &nbsp;</label>
        </div>
    </div>

    <div class="form-group">
        <label class="<?php echo isset($data["errors"]["mast_evnts"]) ? "label_error" : "" ?>">
            <?php echo __('mast_evnts'); ?>:
        </label>
        <div class="form-control">
            <label><input type="radio" name="mast_evnts" value="1"
                    <?php echo isset($_POST["mast_evnts"]) && $_POST["mast_evnts"] == 1 ? "checked" :
                        (isset($profile["mast_evnts"]) && $profile["mast_evnts"] == 1 ? "checked" : "") ?>> 0 &nbsp;</label>
            <label><input type="radio" name="mast_evnts" value="2"
                    <?php echo isset($_POST["mast_evnts"]) && $_POST["mast_evnts"] == 2 ? "checked" :
                        (isset($profile["mast_evnts"]) && $profile["mast_evnts"] == 2 ? "checked" : "") ?>> 1 &nbsp;</label>
            <label><input type="radio" name="mast_evnts" value="3"
                    <?php echo isset($_POST["mast_evnts"]) && $_POST["mast_evnts"] == 3 ? "checked" :
                        (isset($profile["mast_evnts"]) && $profile["mast_evnts"] == 3 ? "checked" : "") ?>> 2 &nbsp;</label>
            <label><input type="radio" name="mast_evnts" value="4"
                    <?php echo isset($_POST["mast_evnts"]) && $_POST["mast_evnts"] == 4 ? "checked" :
                        (isset($profile["mast_evnts"]) && $profile["mast_evnts"] == 4 ? "checked" : "") ?>> 3+ &nbsp;</label>
        </div>
    </div>

    <div class="form-group">
        <label class="<?php echo isset($data["errors"]["mast_role"]) ? "label_error" : "" ?>"><?php echo __('mast_role'); ?>: </label>
        <div class="form-control">
            <label><input type="checkbox" name="mast_role[]" value="translator" disabled
                    <?php echo isset($_POST["mast_role"]) && in_array("translator", $_POST["mast_role"]) ? "checked" :
                        (isset($profile["mast_role"]) && in_array("translator", $profile["mast_role"]) ? "checked" : "") ?>> <?php echo __('translator'); ?> &nbsp;</label>
            <label><input type="checkbox" name="mast_role[]" value="facilitator" disabled
                    <?php echo isset($_POST["mast_role"]) && in_array("facilitator", $_POST["mast_role"]) ? "checked" :
                        (isset($profile["mast_role"]) && in_array("facilitator", $profile["mast_role"]) ? "checked" : "") ?>> <?php echo __('facilitator'); ?> &nbsp;</label>
            <label><input type="checkbox" name="mast_role[]" value="l2_checker" disabled
                    <?php echo isset($_POST["mast_role"]) && in_array("l2_checker", $_POST["mast_role"]) ? "checked" :
                        (isset($profile["mast_role"]) && in_array("l2_checker", $profile["mast_role"]) ? "checked" : "") ?>> <?php echo __('l2_checker'); ?> &nbsp;</label>
            <label><input type="checkbox" name="mast_role[]" value="l3_checker" disabled
                    <?php echo isset($_POST["mast_role"]) && in_array("l3_checker", $_POST["mast_role"]) ? "checked" :
                        (isset($profile["mast_role"]) && in_array("l3_checker", $profile["mast_role"]) ? "checked" : "") ?>> <?php echo __('l3_checker'); ?> &nbsp;</label>
        </div>
    </div>

    <div class="form-group">
        <label class="<?php echo isset($data["errors"]["teamwork"]) ? "label_error" : "" ?>">
            <?php echo __('teamwork'); ?>:
        </label>
        <div class="form-control">
            <label><input type="radio" name="teamwork" value="1"
                    <?php echo isset($_POST["teamwork"]) && $_POST["teamwork"] == 1 ? "checked" :
                        (isset($profile["teamwork"]) && $profile["teamwork"] == 1 ? "checked" : "") ?>> <?php echo __('rarely'); ?> &nbsp;</label>
            <label><input type="radio" name="teamwork" value="2"
                    <?php echo isset($_POST["teamwork"]) && $_POST["teamwork"] == 2 ? "checked" :
                        (isset($profile["teamwork"]) && $profile["teamwork"] == 2 ? "checked" : "") ?>> <?php echo __('some'); ?> &nbsp;</label>
            <label><input type="radio" name="teamwork" value="3"
                    <?php echo isset($_POST["teamwork"]) && $_POST["teamwork"] == 3 ? "checked" :
                        (isset($profile["teamwork"]) && $profile["teamwork"] == 3 ? "checked" : "") ?>> <?php echo __('much'); ?> &nbsp;</label>
            <label><input type="radio" name="teamwork" value="4"
                    <?php echo isset($_POST["teamwork"]) && $_POST["teamwork"] == 4 ? "checked" :
                        (isset($profile["teamwork"]) && $profile["teamwork"] == 4 ? "checked" : "") ?>> <?php echo __('frequently'); ?> &nbsp;</label>
        </div>
    </div>

    <div class="facilitator_section
            <?php echo isset($_POST["prefered_roles"]) && in_array("facilitator", $_POST["prefered_roles"]) ? "shown" :
                (isset($profile["prefered_roles"]) && in_array("facilitator", $profile["prefered_roles"]) ? "shown" : "") ?>">
        <h3><?php echo __('facilitator_skills'); ?></h3>

        <div class="form-group">
            <label class="<?php echo isset($data["errors"]["mast_facilitator"]) ? "label_error" : "" ?>">
                <?php echo __('mast_facilitator'); ?>:
            </label>
            <div class="form-control">
                <label><input type="radio" name="mast_facilitator" value="1"
                        <?php echo isset($_POST["mast_facilitator"]) && $_POST["mast_facilitator"] == 1 ? "checked" :
                            (isset($profile["mast_facilitator"]) && $profile["mast_facilitator"] == 1 ? "checked" : "") ?>> <?php echo __('yes'); ?> &nbsp;</label>
                <label><input type="radio" name="mast_facilitator" value="0"
                        <?php echo ((isset($_POST["mast_facilitator"]) && $_POST["mast_facilitator"] == 0) || (isset($profile["mast_facilitator"]) && $profile["mast_facilitator"] == 0)) ? "checked" :
                            (!isset($_POST["mast_facilitator"]) && !isset($profile["mast_facilitator"]) ? "checked" : "") ?>> <?php echo __('no'); ?> &nbsp;</label>
            </div>
        </div>

        <div class="form-group">
            <label class="<?php echo isset($data["errors"]["org"]) ? "label_error" : "" ?>"><?php echo __('org'); ?>: </label>
            <div class="form-control">
                <label><input type="radio" name="org" value="Other" disabled
                        <?php echo isset($_POST["org"]) && $_POST["org"] == "Other" ? "checked" :
                            (isset($profile["org"]) && $profile["org"] == "Other" ? "checked" : "") ?>> <?php echo __('other'); ?> &nbsp;</label>
                <label><input type="radio" name="org" value="WA EdServices" disabled
                        <?php echo isset($_POST["org"]) && $_POST["org"] == "WA EdServices" ? "checked" :
                            (isset($profile["org"]) && $profile["org"] == "WA EdServices" ? "checked" : "") ?>> WA EdServices &nbsp;</label>
            </div>
        </div>

        <div class="form-group">
            <label class="<?php echo isset($data["errors"]["ref_person"]) ? "label_error" : "" ?>"><?php echo __('ref_person'); ?>: </label>
            <input class="form-control" type="text" name="ref_person"
                   value="<?php echo isset($_POST["ref_person"]) ? $_POST["ref_person"] : (isset($profile["ref_person"]) ? $profile["ref_person"] : "") ?>" disabled>
        </div>

        <div class="form-group">
            <label class="<?php echo isset($data["errors"]["ref_email"]) ? "label_error" : "" ?>"><?php echo __('ref_email'); ?>: </label>
            <input type="text" class="form-control" name="ref_email"
                   value="<?php echo isset($_POST["ref_email"]) ? $_POST["ref_email"] : (isset($profile["ref_email"]) ? $profile["ref_email"] : "") ?>" disabled>
        </div>
    </div>

    <div class="checker_section
        <?php echo isset($_POST["prefered_roles"]) && (in_array("translator", $_POST["prefered_roles"]) || in_array("checker", $_POST["prefered_roles"])) ? "shown" :
            (isset($profile["prefered_roles"]) && (in_array("translator", $profile["prefered_roles"]) || in_array("checker", $profile["prefered_roles"])) ? "shown" : "") ?>">
        <h3><?php echo __('checker_skills'); ?></h3>

        <div class="form-group">
            <label class="<?php echo isset($data["errors"]["church_role"]) ? "label_error" : "" ?>"><?php echo __('church_role'); ?>: </label>
            <div class="form-control">
                <label><input type="checkbox" name="church_role[]" value="Elder"
                        <?php echo isset($_POST["church_role"]) && in_array("Elder", $_POST["church_role"]) ? "checked" :
                            (isset($profile["church_role"]) && in_array("Elder", $profile["church_role"]) ? "checked" : "") ?>> <?php echo __('elder'); ?> &nbsp;</label>
                <label><input type="checkbox" name="church_role[]" value="Bishop"
                        <?php echo isset($_POST["church_role"]) && in_array("Bishop", $_POST["church_role"]) ? "checked" :
                            (isset($profile["church_role"]) && in_array("Bishop", $profile["church_role"]) ? "checked" : "") ?>> <?php echo __('bishop'); ?> &nbsp;</label>
                <label><input type="checkbox" name="church_role[]" value="Pastor"
                        <?php echo isset($_POST["church_role"]) && in_array("Pastor", $_POST["church_role"]) ? "checked" :
                            (isset($profile["church_role"]) && in_array("Pastor", $profile["church_role"]) ? "checked" : "") ?>> <?php echo __('pastor'); ?> &nbsp;</label>
                <label><input type="checkbox" name="church_role[]" value="Teacher"
                        <?php echo isset($_POST["church_role"]) && in_array("Teacher", $_POST["church_role"]) ? "checked" :
                            (isset($profile["church_role"]) && in_array("Teacher", $profile["church_role"]) ? "checked" : "") ?>> <?php echo __('teacher'); ?> &nbsp;</label>
                <label><input type="checkbox" name="church_role[]" value="Denominational Leader"
                        <?php echo isset($_POST["church_role"]) && in_array("Denominational Leader", $_POST["church_role"]) ? "checked" :
                            (isset($profile["church_role"]) && in_array("Denominational Leader", $profile["church_role"]) ? "checked" : "") ?>> <?php echo __('denominational_leader'); ?> &nbsp;</label>
                <label><input type="checkbox" name="church_role[]" value="Seminary Professor"
                        <?php echo isset($_POST["church_role"]) && in_array("Seminary Professor", $_POST["church_role"]) ? "checked" :
                            (isset($profile["church_role"]) && in_array("Seminary Professor", $profile["church_role"]) ? "checked" : "") ?>> <?php echo __('seminary_professor'); ?> &nbsp;</label>
            </div>
        </div>

        <div class="form-group">
            <label><?php echo __('orig_langs'); ?>: </label>
            <div class="form-control">
                <label class="<?php echo isset($data["errors"]["hebrew_knwlg"]) ? "label_error" : "" ?>"><?php echo __('hebrew_knwlg'); ?>: </label> &nbsp;&nbsp;
                <label><input type="radio" name="hebrew_knwlg" value="0"
                        <?php echo isset($_POST["hebrew_knwlg"]) && $_POST["hebrew_knwlg"] == 0 ? "checked" :
                            (isset($profile["hebrew_knwlg"]) && $profile["hebrew_knwlg"] == 0 ? "checked" : "") ?>> <?php echo __('none'); ?> &nbsp;</label>
                <label><input type="radio" name="hebrew_knwlg" value="1"
                        <?php echo isset($_POST["hebrew_knwlg"]) && $_POST["hebrew_knwlg"] == 1 ? "checked" :
                            (isset($profile["hebrew_knwlg"]) && $profile["hebrew_knwlg"] == 1 ? "checked" : "") ?>> <?php echo __('limited'); ?> &nbsp;</label>
                <label><input type="radio" name="hebrew_knwlg" value="2"
                        <?php echo isset($_POST["hebrew_knwlg"]) && $_POST["hebrew_knwlg"] == 2 ? "checked" :
                            (isset($profile["hebrew_knwlg"]) && $profile["hebrew_knwlg"] == 2 ? "checked" : "") ?>> <?php echo __('moderate'); ?> &nbsp;</label>
                <label><input type="radio" name="hebrew_knwlg" value="3"
                        <?php echo isset($_POST["hebrew_knwlg"]) && $_POST["hebrew_knwlg"] == 3 ? "checked" :
                            (isset($profile["hebrew_knwlg"]) && $profile["hebrew_knwlg"] == 3 ? "checked" : "") ?>> <?php echo __('strong'); ?> &nbsp;</label>
                <label><input type="radio" name="hebrew_knwlg" value="4"
                        <?php echo isset($_POST["hebrew_knwlg"]) && $_POST["hebrew_knwlg"] == 4 ? "checked" :
                            (isset($profile["hebrew_knwlg"]) && $profile["hebrew_knwlg"] == 4 ? "checked" : "") ?>> <?php echo __('expert'); ?> &nbsp;</label>
            </div>
            <br>
            <div class="form-control">
                <label class="<?php echo isset($data["errors"]["greek_knwlg"]) ? "label_error" : "" ?>"><?php echo __('greek_knwlg'); ?>: </label> &nbsp;&nbsp;
                <label><input type="radio" name="greek_knwlg" value="0"
                        <?php echo isset($_POST["greek_knwlg"]) && $_POST["greek_knwlg"] == 0 ? "checked" :
                            (isset($profile["greek_knwlg"]) && $profile["greek_knwlg"] == 0 ? "checked" : "") ?>> <?php echo __('none'); ?> &nbsp;</label>
                <label><input type="radio" name="greek_knwlg" value="1"
                        <?php echo isset($_POST["greek_knwlg"]) && $_POST["greek_knwlg"] == 1 ? "checked" :
                            (isset($profile["greek_knwlg"]) && $profile["greek_knwlg"] == 1 ? "checked" : "") ?>> <?php echo __('limited'); ?> &nbsp;</label>
                <label><input type="radio" name="greek_knwlg" value="2"
                        <?php echo isset($_POST["greek_knwlg"]) && $_POST["greek_knwlg"] == 2 ? "checked" :
                            (isset($profile["greek_knwlg"]) && $profile["greek_knwlg"] == 2 ? "checked" : "") ?>> <?php echo __('moderate'); ?> &nbsp;</label>
                <label><input type="radio" name="greek_knwlg" value="3"
                        <?php echo isset($_POST["greek_knwlg"]) && $_POST["greek_knwlg"] == 3 ? "checked" :
                            (isset($profile["greek_knwlg"]) && $profile["greek_knwlg"] == 3 ? "checked" : "") ?>> <?php echo __('strong'); ?> &nbsp;</label>
                <label><input type="radio" name="greek_knwlg" value="4"
                        <?php echo isset($_POST["greek_knwlg"]) && $_POST["greek_knwlg"] == 4 ? "checked" :
                            (isset($profile["greek_knwlg"]) && $profile["greek_knwlg"] == 4 ? "checked" : "") ?>> <?php echo __('expert'); ?> &nbsp;</label>
            </div>
        </div>

        <div class="form-group">
            <label class="<?php echo isset($data["errors"]["education"]) ? "label_error" : "" ?>"><?php echo __('education'); ?>: </label>
            <div class="form-control">
                <label><input type="checkbox" name="education[]" value="BA"
                        <?php echo isset($_POST["education"]) && in_array("BA", $_POST["education"]) ? "checked" :
                            (isset($profile["education"]) && in_array("BA", $profile["education"]) ? "checked" : "") ?>> <?php echo __('ba_edu'); ?> &nbsp;</label>
                <label><input type="checkbox" name="education[]" value="MA"
                        <?php echo isset($_POST["education"]) && in_array("MA", $_POST["education"]) ? "checked" :
                            (isset($profile["education"]) && in_array("MA", $profile["education"]) ? "checked" : "") ?>> <?php echo __('ma_edu'); ?> &nbsp;</label>
                <label><input type="checkbox" name="education[]" value="PHD"
                        <?php echo isset($_POST["education"]) && in_array("PHD", $_POST["education"]) ? "checked" :
                            (isset($profile["education"]) && in_array("PHD", $profile["education"]) ? "checked" : "") ?>> <?php echo __('phd_edu'); ?> &nbsp;</label>
            </div>
        </div>

        <div class="form-group">
            <label class="<?php echo isset($data["errors"]["ed_area"]) ? "label_error" : "" ?>"><?php echo __('ed_area'); ?>: </label>
            <div class="form-control">
                <label><input type="checkbox" name="ed_area[]" value="Theology"
                        <?php echo isset($_POST["ed_area"]) && in_array("Theology", $_POST["ed_area"]) ? "checked" :
                            (isset($profile["ed_area"]) && in_array("Theology", $profile["ed_area"]) ? "checked" : "") ?>> <?php echo __('theology'); ?> &nbsp;</label>
                <label><input type="checkbox" name="ed_area[]" value="Pastoral Ministry"
                        <?php echo isset($_POST["ed_area"]) && in_array("Pastoral Ministry", $_POST["ed_area"]) ? "checked" :
                            (isset($profile["ed_area"]) && in_array("Pastoral Ministry", $profile["ed_area"]) ? "checked" : "") ?>> <?php echo __('pastoral_ministry'); ?> &nbsp;</label>
                <label><input type="checkbox" name="ed_area[]" value="Bible Translation"
                        <?php echo isset($_POST["ed_area"]) && in_array("Bible Translation", $_POST["ed_area"]) ? "checked" :
                            (isset($profile["ed_area"]) && in_array("Bible Translation", $profile["ed_area"]) ? "checked" : "") ?>> <?php echo __('bible_translation'); ?> &nbsp;</label>
                <label><input type="checkbox" name="ed_area[]" value="Exegetics"
                        <?php echo isset($_POST["ed_area"]) && in_array("Exegetics", $_POST["ed_area"]) ? "checked" :
                            (isset($profile["ed_area"]) && in_array("Exegetics", $profile["ed_area"]) ? "checked" : "") ?>> <?php echo __('exegetics'); ?> &nbsp;</label>
            </div>
        </div>

        <div class="form-group">
            <label class="<?php echo isset($data["errors"]["ed_place"]) ? "label_error" : "" ?>"><?php echo __('ed_place'); ?>: </label>
            <input type="text" class="form-control" name="ed_place"
                   value="<?php echo isset($_POST["ed_place"]) ? $_POST["ed_place"] : (isset($profile["ed_place"]) ? $profile["ed_place"] : "") ?>">
        </div>
    </div>

    <input type="hidden" name="csrfToken" value="<?php echo $data['csrfToken']; ?>" />

    <div class="save_profile_container unlinked">
        <button type="submit" name="submit" id="save_profile" class="btn btn-primary"><?php echo __('save'); ?></button>
    </div>
</form>

<div class="language_container">
    <div class="language_block">
        <div class="language-close glyphicon glyphicon-remove"></div>

        <label><?php echo __('select_language'); ?>: </label>
        <select class="form-control language" data-placeholder="<?php echo __('select_search_lang_option'); ?>">
            <option></option>
            <?php foreach ($data["languages"] as $lang):?>
                <option value="<?php echo $lang->langID; ?>"><?php echo "[".$lang->langID."] " . $lang->langName . ($lang->langName != $lang->angName ? " ( ".$lang->angName." )" : ""); ?></option>
            <?php endforeach; ?>
        </select>

        <br><br>

        <label><?php echo __('language_fluency'); ?>: </label>
        <div class="form-control">
            <label><input type="radio" class="fluency" name="" value="1" disabled> <?php echo __('moderate'); ?> &nbsp;</label>
            <label><input type="radio" class="fluency" name="" value="2" disabled> <?php echo __('strong'); ?> &nbsp;</label>
            <label><input type="radio" class="fluency" name="" value="3" disabled> <?php echo __('fluent'); ?> &nbsp;</label>
            <label><input type="radio" class="fluency" name="" value="4" disabled> <?php echo __('native'); ?> &nbsp;</label>
            <label><input type="radio" class="fluency" name="" value="5" disabled> <?php echo __('expert'); ?> &nbsp;</label>
        </div>
        <br>

        <!--<label><?php echo __('lang_geographic_years'); ?>: </label>
        <div class="form-control">
            <label><input type="radio" class="geo_years" name="" value="1" disabled> <?php echo __('less_than', array(2)); ?> &nbsp;&nbsp;</label>
            <label><input type="radio" class="geo_years" name="" value="2" disabled> 2-4 &nbsp;</label>
            <label><input type="radio" class="geo_years" name="" value="3" disabled> 5-7 &nbsp;</label>
            <label><input type="radio" class="geo_years" name="" value="4" disabled> 8-10 &nbsp;</label>
        </div>
        <br>-->
        <button class="add_lang btn btn-primary" disabled><?php echo __("add_lang") ?></button>
    </div>
</div>

<div class="avatar_container">
    <div class="avatar_block">
        <div class="avatar-close glyphicon glyphicon-remove"></div>

        <div class="genderMale">
            <?php for ($i=1; $i <=20; $i++): ?>
            <img id="<?php echo "m".$i ?>" src="<?php echo template_url("img/avatars/m".$i.".png") ?>">
            <?php endfor; ?>
        </div>

        <div class="genderFemale">
            <?php for ($i=1; $i <=20; $i++): ?>
                <img id="<?php echo "f".$i ?>" src="<?php echo template_url("img/avatars/f".$i.".png") ?>">
            <?php endfor; ?>
        </div>
    </div>
</div>

<link href="<?php echo template_url("css/chosen.min.css")?>" type="text/css" rel="stylesheet" />
<script src="<?php echo template_url("js/chosen.jquery.min.js")?>"></script>

<script type="text/javascript">
    $(document).ready(function() {
        $(".profile_form select").chosen();
    });
</script>
