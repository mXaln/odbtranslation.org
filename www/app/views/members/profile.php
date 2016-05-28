<?php
use Core\Language;
?>

<h1><?php echo Language::show('profile_message', 'Members'); ?></h1>

<?php
$profile = $data["profile"];
?>

<form action='' method='post' style="width: 700px" class="form-horizontal profile_form">
    <?php
    echo \Core\Error::display($error);
    echo \Helpers\Session::message();
    ?>

    <h3><?php echo Language::show('common_skills', 'Members'); ?></h3>

    <label for="known_languages" class="<?php echo isset($data["errors"]["langs"]) ? "label_error" : "" ?>">
        <?php echo Language::show('known_languages', 'Members'); ?>:
    </label>
    <div class="form-group">
        <div class="language_add glyphicon glyphicon-plus col-sm-1"></div>
        <div class="col-sm-11">
            <select class="form-control langs" name="langs[]" multiple data-placeholder="Click Plus Button to add languages >>" disabled >
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
    </div>

    <div class="form-group">
        <label class="<?php echo isset($data["errors"]["bbl_trans_yrs"]) ? "label_error" : "" ?>">
            <?php echo Language::show('bbl_trans_yrs', 'Members'); ?>:
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
            <?php echo Language::show('othr_trans_yrs', 'Members'); ?>:
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
        <label class="<?php echo isset($data["errors"]["bbl_knwlg_degr"]) ? "label_error" : "" ?>"><?php echo Language::show('bbl_knwlg_degr', 'Members'); ?>: </label>
        <div class="form-control">
            <label><input type="radio" name="bbl_knwlg_degr" value="1"
                    <?php echo isset($_POST["bbl_knwlg_degr"]) && $_POST["bbl_knwlg_degr"] == 1 ? "checked" :
                        (isset($profile["bbl_knwlg_degr"]) && $profile["bbl_knwlg_degr"] == 1 ? "checked" : "") ?>> <?php echo Language::show('weak', 'Members'); ?> &nbsp;</label>
            <label><input type="radio" name="bbl_knwlg_degr" value="2"
                    <?php echo isset($_POST["bbl_knwlg_degr"]) && $_POST["bbl_knwlg_degr"] == 2 ? "checked" :
                        (isset($profile["bbl_knwlg_degr"]) && $profile["bbl_knwlg_degr"] == 2 ? "checked" : "") ?>> <?php echo Language::show('moderate', 'Members'); ?> &nbsp;</label>
            <label><input type="radio" name="bbl_knwlg_degr" value="3"
                    <?php echo isset($_POST["bbl_knwlg_degr"]) && $_POST["bbl_knwlg_degr"] == 3 ? "checked" :
                        (isset($profile["bbl_knwlg_degr"]) && $profile["bbl_knwlg_degr"] == 3 ? "checked" : "") ?>> <?php echo Language::show('strong', 'Members'); ?> &nbsp;</label>
            <label><input type="radio" name="bbl_knwlg_degr" value="4"
                    <?php echo isset($_POST["bbl_knwlg_degr"]) && $_POST["bbl_knwlg_degr"] == 4 ? "checked" :
                        (isset($profile["bbl_knwlg_degr"]) && $profile["bbl_knwlg_degr"] == 4 ? "checked" : "") ?>> <?php echo Language::show('expert', 'Members'); ?> &nbsp;</label>
        </div>
    </div>

    <div class="form-group">
        <label class="<?php echo isset($data["errors"]["mast_evnts"]) ? "label_error" : "" ?>">
            <?php echo Language::show('mast_evnts', 'Members'); ?>:
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
        <label class="<?php echo isset($data["errors"]["mast_role"]) ? "label_error" : "" ?>"><?php echo Language::show('mast_role', 'Members'); ?>: </label>
        <div class="form-control">
            <label><input type="checkbox" name="mast_role[]" value="translator" disabled
                    <?php echo isset($_POST["mast_role"]) && in_array("translator", $_POST["mast_role"]) ? "checked" :
                        (isset($profile["mast_role"]) && in_array("translator", $profile["mast_role"]) ? "checked" : "") ?>> <?php echo Language::show('translator', 'Members'); ?> &nbsp;</label>
            <label><input type="checkbox" name="mast_role[]" value="facilitator" disabled
                    <?php echo isset($_POST["mast_role"]) && in_array("facilitator", $_POST["mast_role"]) ? "checked" :
                        (isset($profile["mast_role"]) && in_array("facilitator", $profile["mast_role"]) ? "checked" : "") ?>> <?php echo Language::show('facilitator', 'Members'); ?> &nbsp;</label>
            <label><input type="checkbox" name="mast_role[]" value="l2_checker" disabled
                    <?php echo isset($_POST["mast_role"]) && in_array("l2_checker", $_POST["mast_role"]) ? "checked" :
                        (isset($profile["mast_role"]) && in_array("l2_checker", $profile["mast_role"]) ? "checked" : "") ?>> <?php echo Language::show('l2_checker', 'Members'); ?> &nbsp;</label>
            <label><input type="checkbox" name="mast_role[]" value="l3_checker" disabled
                    <?php echo isset($_POST["mast_role"]) && in_array("l3_checker", $_POST["mast_role"]) ? "checked" :
                        (isset($profile["mast_role"]) && in_array("l3_checker", $profile["mast_role"]) ? "checked" : "") ?>> <?php echo Language::show('l3_checker', 'Members'); ?> &nbsp;</label>
        </div>
    </div>

    <div class="form-group">
        <label class="<?php echo isset($data["errors"]["teamwork"]) ? "label_error" : "" ?>">
            <?php echo Language::show('teamwork', 'Members'); ?>:
        </label>
        <div class="form-control">
            <label><input type="radio" name="teamwork" value="1"
                    <?php echo isset($_POST["teamwork"]) && $_POST["teamwork"] == 1 ? "checked" :
                        (isset($profile["teamwork"]) && $profile["teamwork"] == 1 ? "checked" : "") ?>> <?php echo Language::show('rarely', 'Members'); ?> &nbsp;</label>
            <label><input type="radio" name="teamwork" value="2"
                    <?php echo isset($_POST["teamwork"]) && $_POST["teamwork"] == 2 ? "checked" :
                        (isset($profile["teamwork"]) && $profile["teamwork"] == 2 ? "checked" : "") ?>> <?php echo Language::show('some', 'Members'); ?> &nbsp;</label>
            <label><input type="radio" name="teamwork" value="3"
                    <?php echo isset($_POST["teamwork"]) && $_POST["teamwork"] == 3 ? "checked" :
                        (isset($profile["teamwork"]) && $profile["teamwork"] == 3 ? "checked" : "") ?>> <?php echo Language::show('much', 'Members'); ?> &nbsp;</label>
            <label><input type="radio" name="teamwork" value="4"
                    <?php echo isset($_POST["teamwork"]) && $_POST["teamwork"] == 4 ? "checked" :
                        (isset($profile["teamwork"]) && $profile["teamwork"] == 4 ? "checked" : "") ?>> <?php echo Language::show('frequently', 'Members'); ?> &nbsp;</label>
        </div>
    </div>

    <h3><?php echo Language::show('facilitator_skills', 'Members'); ?></h3>

    <div class="form-group">
        <label class="<?php echo isset($data["errors"]["mast_facilitator"]) ? "label_error" : "" ?>">
            <?php echo Language::show('mast_facilitator', 'Members'); ?>:
        </label>
        <div class="form-control">
            <label><input type="radio" name="mast_facilitator" value="1"
                    <?php echo isset($_POST["mast_facilitator"]) && $_POST["mast_facilitator"] == 1 ? "checked" :
                        (isset($profile["mast_facilitator"]) && $profile["mast_facilitator"] == 1 ? "checked" : "") ?>> <?php echo Language::show('yes', 'Members'); ?> &nbsp;</label>
            <label><input type="radio" name="mast_facilitator" value="0"
                    <?php echo ((isset($_POST["mast_facilitator"]) && $_POST["mast_facilitator"] == 0) || (isset($profile["mast_facilitator"]) && $profile["mast_facilitator"] == 0)) ? "checked" :
                        (!isset($_POST["mast_facilitator"]) && !isset($profile["mast_facilitator"]) ? "checked" : "") ?>> <?php echo Language::show('no', 'Members'); ?> &nbsp;</label>
        </div>
    </div>

    <div class="form-group">
        <label class="<?php echo isset($data["errors"]["org"]) ? "label_error" : "" ?>"><?php echo Language::show('org', 'Members'); ?>: </label>
        <div class="form-control">
            <label><input type="radio" name="org" value="Other" disabled
                    <?php echo isset($_POST["org"]) && $_POST["org"] == "Other" ? "checked" :
                        (isset($profile["org"]) && $profile["org"] == "Other" ? "checked" : "") ?>> <?php echo Language::show('other', 'Members'); ?> &nbsp;</label>
            <label><input type="radio" name="org" value="WA EdServices" disabled
                    <?php echo isset($_POST["org"]) && $_POST["org"] == "WA EdServices" ? "checked" :
                        (isset($profile["org"]) && $profile["org"] == "WA EdServices" ? "checked" : "") ?>> WA EdServices &nbsp;</label>
        </div>
    </div>

    <div class="form-group">
        <label class="<?php echo isset($data["errors"]["ref_person"]) ? "label_error" : "" ?>"><?php echo Language::show('ref_person', 'Members'); ?>: </label>
        <input class="form-control" type="text" name="ref_person"
               value="<?php echo isset($_POST["ref_person"]) ? $_POST["ref_person"] : (isset($profile["ref_person"]) ? $profile["ref_person"] : "") ?>" disabled>
    </div>

    <div class="form-group">
        <label class="<?php echo isset($data["errors"]["ref_email"]) ? "label_error" : "" ?>"><?php echo Language::show('ref_email', 'Members'); ?>: </label>
        <input type="text" class="form-control" name="ref_email"
               value="<?php echo isset($_POST["ref_email"]) ? $_POST["ref_email"] : (isset($profile["ref_email"]) ? $profile["ref_email"] : "") ?>" disabled>
    </div>

    <h3><?php echo Language::show('checker_skills', 'Members'); ?></h3>

    <div class="form-group">
        <label class="<?php echo isset($data["errors"]["church_role"]) ? "label_error" : "" ?>"><?php echo Language::show('church_role', 'Members'); ?>: </label>
        <div class="form-control">
            <label><input type="checkbox" name="church_role[]" value="Elder"
                    <?php echo isset($_POST["church_role"]) && in_array("Elder", $_POST["church_role"]) ? "checked" :
                        (isset($profile["church_role"]) && in_array("Elder", $profile["church_role"]) ? "checked" : "") ?>> <?php echo Language::show('elder', 'Members'); ?> &nbsp;</label>
            <label><input type="checkbox" name="church_role[]" value="Bishop"
                    <?php echo isset($_POST["church_role"]) && in_array("Bishop", $_POST["church_role"]) ? "checked" :
                        (isset($profile["church_role"]) && in_array("Bishop", $profile["church_role"]) ? "checked" : "") ?>> <?php echo Language::show('bishop', 'Members'); ?> &nbsp;</label>
            <label><input type="checkbox" name="church_role[]" value="Pastor"
                    <?php echo isset($_POST["church_role"]) && in_array("Pastor", $_POST["church_role"]) ? "checked" :
                        (isset($profile["church_role"]) && in_array("Pastor", $profile["church_role"]) ? "checked" : "") ?>> <?php echo Language::show('pastor', 'Members'); ?> &nbsp;</label>
            <label><input type="checkbox" name="church_role[]" value="Teacher"
                    <?php echo isset($_POST["church_role"]) && in_array("Teacher", $_POST["church_role"]) ? "checked" :
                        (isset($profile["church_role"]) && in_array("Teacher", $profile["church_role"]) ? "checked" : "") ?>> <?php echo Language::show('teacher', 'Members'); ?> &nbsp;</label>
            <label><input type="checkbox" name="church_role[]" value="Denominational Leader"
                    <?php echo isset($_POST["church_role"]) && in_array("Denominational Leader", $_POST["church_role"]) ? "checked" :
                        (isset($profile["church_role"]) && in_array("Denominational Leader", $profile["church_role"]) ? "checked" : "") ?>> <?php echo Language::show('denominational_leader', 'Members'); ?> &nbsp;</label>
            <label><input type="checkbox" name="church_role[]" value="Seminary Professor"
                    <?php echo isset($_POST["church_role"]) && in_array("Seminary Professor", $_POST["church_role"]) ? "checked" :
                        (isset($profile["church_role"]) && in_array("Seminary Professor", $profile["church_role"]) ? "checked" : "") ?>> <?php echo Language::show('seminary_professor', 'Members'); ?> &nbsp;</label>
        </div>
    </div>

    <div class="form-group">
        <label><?php echo Language::show('orig_langs', 'Members'); ?>: </label>
        <div class="form-control">
            <label class="<?php echo isset($data["errors"]["hebrew_knwlg"]) ? "label_error" : "" ?>"><?php echo Language::show('hebrew_knwlg', 'Members'); ?>: </label> &nbsp;&nbsp;
            <label><input type="radio" name="hebrew_knwlg" value="0"
                    <?php echo isset($_POST["hebrew_knwlg"]) && $_POST["hebrew_knwlg"] == 0 ? "checked" :
                        (isset($profile["hebrew_knwlg"]) && $profile["hebrew_knwlg"] == 0 ? "checked" : "") ?>> <?php echo Language::show('none', 'Members'); ?> &nbsp;</label>
            <label><input type="radio" name="hebrew_knwlg" value="1"
                    <?php echo isset($_POST["hebrew_knwlg"]) && $_POST["hebrew_knwlg"] == 1 ? "checked" :
                        (isset($profile["hebrew_knwlg"]) && $profile["hebrew_knwlg"] == 1 ? "checked" : "") ?>> <?php echo Language::show('limited', 'Members'); ?> &nbsp;</label>
            <label><input type="radio" name="hebrew_knwlg" value="2"
                    <?php echo isset($_POST["hebrew_knwlg"]) && $_POST["hebrew_knwlg"] == 2 ? "checked" :
                        (isset($profile["hebrew_knwlg"]) && $profile["hebrew_knwlg"] == 2 ? "checked" : "") ?>> <?php echo Language::show('moderate', 'Members'); ?> &nbsp;</label>
            <label><input type="radio" name="hebrew_knwlg" value="3"
                    <?php echo isset($_POST["hebrew_knwlg"]) && $_POST["hebrew_knwlg"] == 3 ? "checked" :
                        (isset($profile["hebrew_knwlg"]) && $profile["hebrew_knwlg"] == 3 ? "checked" : "") ?>> <?php echo Language::show('strong', 'Members'); ?> &nbsp;</label>
            <label><input type="radio" name="hebrew_knwlg" value="4"
                    <?php echo isset($_POST["hebrew_knwlg"]) && $_POST["hebrew_knwlg"] == 4 ? "checked" :
                        (isset($profile["hebrew_knwlg"]) && $profile["hebrew_knwlg"] == 4 ? "checked" : "") ?>> <?php echo Language::show('expert', 'Members'); ?> &nbsp;</label>
        </div>
        <br>
        <div class="form-control">
            <label class="<?php echo isset($data["errors"]["greek_knwlg"]) ? "label_error" : "" ?>"><?php echo Language::show('greek_knwlg', 'Members'); ?>: </label> &nbsp;&nbsp;
            <label><input type="radio" name="greek_knwlg" value="0"
                    <?php echo isset($_POST["greek_knwlg"]) && $_POST["greek_knwlg"] == 0 ? "checked" :
                        (isset($profile["greek_knwlg"]) && $profile["greek_knwlg"] == 0 ? "checked" : "") ?>> <?php echo Language::show('none', 'Members'); ?> &nbsp;</label>
            <label><input type="radio" name="greek_knwlg" value="1"
                    <?php echo isset($_POST["greek_knwlg"]) && $_POST["greek_knwlg"] == 1 ? "checked" :
                        (isset($profile["greek_knwlg"]) && $profile["greek_knwlg"] == 1 ? "checked" : "") ?>> <?php echo Language::show('limited', 'Members'); ?> &nbsp;</label>
            <label><input type="radio" name="greek_knwlg" value="2"
                    <?php echo isset($_POST["greek_knwlg"]) && $_POST["greek_knwlg"] == 2 ? "checked" :
                        (isset($profile["greek_knwlg"]) && $profile["greek_knwlg"] == 2 ? "checked" : "") ?>> <?php echo Language::show('moderate', 'Members'); ?> &nbsp;</label>
            <label><input type="radio" name="greek_knwlg" value="3"
                    <?php echo isset($_POST["greek_knwlg"]) && $_POST["greek_knwlg"] == 3 ? "checked" :
                        (isset($profile["greek_knwlg"]) && $profile["greek_knwlg"] == 3 ? "checked" : "") ?>> <?php echo Language::show('strong', 'Members'); ?> &nbsp;</label>
            <label><input type="radio" name="greek_knwlg" value="4"
                    <?php echo isset($_POST["greek_knwlg"]) && $_POST["greek_knwlg"] == 4 ? "checked" :
                        (isset($profile["greek_knwlg"]) && $profile["greek_knwlg"] == 4 ? "checked" : "") ?>> <?php echo Language::show('expert', 'Members'); ?> &nbsp;</label>
        </div>
    </div>

    <div class="form-group">
        <label class="<?php echo isset($data["errors"]["education"]) ? "label_error" : "" ?>"><?php echo Language::show('education', 'Members'); ?>: </label>
        <div class="form-control">
            <label><input type="checkbox" name="education[]" value="BA"
                    <?php echo isset($_POST["education"]) && in_array("BA", $_POST["education"]) ? "checked" :
                        (isset($profile["education"]) && in_array("BA", $profile["education"]) ? "checked" : "") ?>> <?php echo Language::show('ba_edu', 'Members'); ?> &nbsp;</label>
            <label><input type="checkbox" name="education[]" value="MA"
                    <?php echo isset($_POST["education"]) && in_array("MA", $_POST["education"]) ? "checked" :
                        (isset($profile["education"]) && in_array("MA", $profile["education"]) ? "checked" : "") ?>> <?php echo Language::show('ma_edu', 'Members'); ?> &nbsp;</label>
            <label><input type="checkbox" name="education[]" value="PHD"
                    <?php echo isset($_POST["education"]) && in_array("PHD", $_POST["education"]) ? "checked" :
                        (isset($profile["education"]) && in_array("PHD", $profile["education"]) ? "checked" : "") ?>> <?php echo Language::show('phd_edu', 'Members'); ?> &nbsp;</label>
        </div>
    </div>

    <div class="form-group">
        <label class="<?php echo isset($data["errors"]["ed_area"]) ? "label_error" : "" ?>"><?php echo Language::show('ed_area', 'Members'); ?>: </label>
        <div class="form-control">
            <label><input type="checkbox" name="ed_area[]" value="Theology"
                    <?php echo isset($_POST["ed_area"]) && in_array("Theology", $_POST["ed_area"]) ? "checked" :
                        (isset($profile["ed_area"]) && in_array("Theology", $profile["ed_area"]) ? "checked" : "") ?>> <?php echo Language::show('theology', 'Members'); ?> &nbsp;</label>
            <label><input type="checkbox" name="ed_area[]" value="Pastoral Ministry"
                    <?php echo isset($_POST["ed_area"]) && in_array("Pastoral Ministry", $_POST["ed_area"]) ? "checked" :
                        (isset($profile["ed_area"]) && in_array("Pastoral Ministry", $profile["ed_area"]) ? "checked" : "") ?>> <?php echo Language::show('pastoral_ministry', 'Members'); ?> &nbsp;</label>
            <label><input type="checkbox" name="ed_area[]" value="Bible Translation"
                    <?php echo isset($_POST["ed_area"]) && in_array("Bible Translation", $_POST["ed_area"]) ? "checked" :
                        (isset($profile["ed_area"]) && in_array("Bible Translation", $profile["ed_area"]) ? "checked" : "") ?>> <?php echo Language::show('bible_translation', 'Members'); ?> &nbsp;</label>
            <label><input type="checkbox" name="ed_area[]" value="Exegetics"
                    <?php echo isset($_POST["ed_area"]) && in_array("Exegetics", $_POST["ed_area"]) ? "checked" :
                        (isset($profile["ed_area"]) && in_array("Exegetics", $profile["ed_area"]) ? "checked" : "") ?>> <?php echo Language::show('exegetics', 'Members'); ?> &nbsp;</label>
        </div>
    </div>

    <div class="form-group">
        <label class="<?php echo isset($data["errors"]["ed_place"]) ? "label_error" : "" ?>"><?php echo Language::show('ed_place', 'Members'); ?>: </label>
        <input type="text" class="form-control" name="ed_place"
               value="<?php echo isset($_POST["ed_place"]) ? $_POST["ed_place"] : (isset($profile["ed_place"]) ? $profile["ed_place"] : "") ?>">
    </div>

    <input type="hidden" name="csrf_token" value="<?php echo $data['csrf_token']; ?>" />

    <button type="submit" name="submit" class="btn btn-primary"><?php echo Language::show('save', 'Members'); ?></button>
</form>

<div class="language_container">
    <div class="language_block">
        <div class="language-close glyphicon glyphicon-remove"></div>

        <label><?php echo Language::show('select_language', 'Members'); ?>: </label>
        <select class="form-control language" data-placeholder="Select an option">
            <option></option>
            <?php foreach ($data["languages"] as $lang):?>
                <option value="<?php echo $lang->langID; ?>"><?php echo "[".$lang->langID."] " . $lang->langName; ?></option>
            <?php endforeach; ?>
        </select>

        <br><br>

        <label><?php echo Language::show('language_fluency', 'Members'); ?>: </label>
        <div class="form-control">
            <label><input type="radio" class="fluency" name="" value="1" disabled> <?php echo Language::show('moderate', 'Members'); ?> &nbsp;</label>
            <label><input type="radio" class="fluency" name="" value="2" disabled> <?php echo Language::show('strong', 'Members'); ?> &nbsp;</label>
            <label><input type="radio" class="fluency" name="" value="3" disabled> <?php echo Language::show('fluent', 'Members'); ?> &nbsp;</label>
            <label><input type="radio" class="fluency" name="" value="4" disabled> <?php echo Language::show('native', 'Members'); ?> &nbsp;</label>
            <label><input type="radio" class="fluency" name="" value="5" disabled> <?php echo Language::show('expert', 'Members'); ?> &nbsp;</label>
        </div>

        <br>

        <label><?php echo Language::show('lang_geographic_years', 'Members'); ?>: </label>
        <div class="form-control">
            <label><input type="radio" class="geo_years" name="" value="1" disabled> <?php echo Language::show('less_than', 'Members', array(2)); ?> &nbsp;&nbsp;</label>
            <label><input type="radio" class="geo_years" name="" value="2" disabled> 2-4 &nbsp;</label>
            <label><input type="radio" class="geo_years" name="" value="3" disabled> 5-7 &nbsp;</label>
            <label><input type="radio" class="geo_years" name="" value="4" disabled> 8-10 &nbsp;</label>
        </div>
        <br>
        <button class="add_lang btn btn-primary" disabled>Add</button>
    </div>
</div>

<link href="<?php echo \Helpers\Url::templatePath()?>css/chosen.min.css" type="text/css" rel="stylesheet" />
<script src="<?php echo \Helpers\Url::templatePath()?>js/chosen.jquery.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        $("select").chosen();
    });
</script>