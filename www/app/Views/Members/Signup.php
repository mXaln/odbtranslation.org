<?php 
use Shared\Legacy\Error;
?>

<div class="members_login center-block" style="width:370px;">
    <h1><?php echo __('login_message'); ?></h1>
    <p><?php echo __('already_member'); ?> <a href='<?php echo SITEURL;?>members/login'><?php echo __('login'); ?></a>
    <?if (isset($error) AND is_array($error)):?>
    <script>
    $(function(){
    <?foreach($error as $k=>$v):?>
    <?if (in_array($k, array('tou', 'sof'))):?>
      $("input[name=<?=$k?>]").parents("label").popover({
              trigger: 'manual',
              placement: 'right',
              container: 'body',
              delay: 0,
              content:  "<?=$v?>"
            }).popover('show');
    <?elseif($k == 'recaptcha' && Config::get("app.type") == "remote"):?>
      $(".g-recaptcha").popover({
              trigger: 'manual',
              placement: 'right',
              container: 'body',
              delay: 0,
              content:  "<?=$v?>"
            }).popover('show');
    <?elseif(in_array($k, array('projects', 'proj_lang'))):?>
        var formGroup = $("select#<?=$k?>").parents(".form-group");
        formGroup.addClass('has-error');
        var popover = $(".chosen-single", formGroup);
        if ($("select#<?=$k?>").hasClass("select-chosen-multiple")) {
            popover = $(".chosen-choices", formGroup);
        }
        popover.popover({
            trigger: 'manual',
            placement: 'right',
            container: 'body',
            delay: 0,
            content:  "<?=$v?>"
        }).popover('show');
    <?else:?>
      $("input[name=<?=$k?>]").popover({
              trigger: 'manual',
              placement: 'right',
              container: 'body',
              delay: 0,
              content:  "<?=$v?>"
            }).popover('show');
    <?endif;?>
    <?endforeach;?>
    });
    </script>
    <?endif;?>
    <form action='' method='post'>
        <div class="form-group">
            <label for="userName" class="sr-only"><?php echo __('userName'); ?></label>
            <input type="text" data-type="login" data-custom-error="<?=__('userName_characters_error')?>" data-empty-error="<?=__('userName_length_error')?>" class="form-control input-lg" id="userName" name="userName" placeholder="<?php echo __('userName'); ?>" value="<?php if(!empty($error)){ echo $_POST['userName']; } ?>">
        </div>

        <div class="form-group">
            <label for="firstName" class="sr-only"><?php echo __('firstName'); ?></label>
            <input type="text" data-type="name" data-custom-error="<?=__('firstName_length_error')?>" data-empty-error="<?=__('firstName_length_error')?>" class="form-control input-lg" id="firstName" name="firstName" placeholder="<?php echo __('firstName'); ?>" value="<?php if(!empty($error)){ echo $_POST['firstName']; } ?>">
        </div>

        <div class="form-group">
            <label for="lastName" class="sr-only"><?php echo __('lastName'); ?></label>
            <input type="text" data-type="name" data-custom-error="<?=__('lastName_length_error')?>" data-empty-error="<?=__('lastName_length_error')?>" class="form-control input-lg" id="lastName" name="lastName" placeholder="<?php echo __('lastName'); ?>" value="<?php if(!empty($error)){ echo $_POST['lastName']; } ?>">
        </div>

        <div class="form-group">
            <label for="email" class="sr-only">Email</label>
            <input type="text" data-type="email" data-custom-error="<?=__('enter_valid_email_error')?>" data-empty-error="<?=__('enter_valid_email_error')?>" class="form-control input-lg" id="email" name="email" placeholder="Email" value="<?php if(!empty($error)){ echo $_POST['email']; } ?>">
        </div>

        <div class="form-group">
            <label for="password" class="sr-only"><?php echo __('password'); ?></label>
            <input type="password" data-type="password" data-custom-error="<?=__('password_short_error')?>" data-empty-error="<?=__('password_short_error')?>" class="form-control input-lg" id="password" name="password" placeholder="<?php echo __('password'); ?>" value="">
        </div>

        <div class="form-group">
            <label for="passwordConfirm" class="sr-only"><?php echo __('confirm_password'); ?></label>
            <input type="password" data-type="confirm" data-custom-error="<?=__('passwords_notmatch_error')?>" data-empty-error="<?=__('passwords_notmatch_error')?>" class="form-control input-lg" id="passwordConfirm" name="passwordConfirm" placeholder="<?php echo __('confirm_password'); ?>" value="">
        </div>

        <div class="form-group">
            <label for="projects" class="sr-only"><?php echo __('select_project'); ?>: </label>
            <select id="projects"
                    class="form-control input-lg select-chosen-multiple"
                    name="projects[]"
                    data-type="projects"
                    data-empty-error="<?=__('projects_empty_error')?>"
                    data-placeholder="<?php echo __("select_project") ?>"
                    multiple>
                <option></option>
                <option <?php echo isset($_POST["projects"]) && in_array("vmast", $_POST["projects"]) ? "selected" : "" ?>
                        value="vmast"><?php echo __("8steps_vmast") ?></option>
                <option <?php echo isset($_POST["projects"]) && in_array("vsail", $_POST["projects"]) ? "selected" : "" ?>
                        value="vsail"><?php echo __("vsail") ?></option>
                <option <?php echo isset($_POST["projects"]) && in_array("l2", $_POST["projects"]) ? "selected" : "" ?>
                        value="l2"><?php echo __("l2_3_events", [2]) ?></option>
                <option <?php echo isset($_POST["projects"]) && in_array("l3", $_POST["projects"]) ? "selected" : "" ?>
                        value="l3"><?php echo __("l2_3_events", [3]) ?></option>
                <option <?php echo isset($_POST["projects"]) && in_array("tn", $_POST["projects"]) ? "selected" : "" ?>
                        value="tn"><?php echo __("tn") ?></option>
                <option <?php echo isset($_POST["projects"]) && in_array("tq", $_POST["projects"]) ? "selected" : "" ?>
                        value="tq"><?php echo __("tq") ?></option>
                <option <?php echo isset($_POST["projects"]) && in_array("tw", $_POST["projects"]) ? "selected" : "" ?>
                        value="tw"><?php echo __("tw") ?></option>
            </select>
        </div>

        <div class="form-group">
            <label for="proj_lang" class="sr-only"><?php echo __('proj_lang_select'); ?>: </label>
            <select id="proj_lang"
                    class="form-control input-lg select-chosen-single"
                    name="proj_lang"
                    data-type="proj_lang"
                    data-empty-error="<?=__('proj_lang_empty_error')?>"
                    data-placeholder="<?php echo __('proj_lang_select'); ?>">
                <option></option>
                <?php foreach ($data["languages"] as $lang):?>
                    <option <?php echo isset($_POST["proj_lang"]) && $lang->langID == $_POST["proj_lang"] ? "selected" : "" ?>
                            value="<?php echo $lang->langID; ?>">
                        <?php echo "[".$lang->langID."] " . $lang->langName .
                            ($lang->angName != "" && $lang->langName != $lang->angName ? " ( ".$lang->angName." )" : ""); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label><input name="tou" data-type="checkbox" data-custom-error="<?=__('tou_accept_error')?>" id="tou" type="checkbox" value="1" /> <?php echo __('tou'); ?></label><br><br>
            <label><input name="sof" data-type="checkbox" data-custom-error="<?=__('sof_accept_error')?>" id="sof" type="checkbox" value="1" /> <?php echo __('sof'); ?></label>
        </div>

        <input type="hidden" name="csrfToken" value="<?php echo $data['csrfToken']; ?>" />

        <?php if(Config::get("app.type") == "remote"): ?>
        <div class="form-group">
            <div class="g-recaptcha" data-sitekey="<?php echo ReCaptcha::getSiteKey() ?>"></div>
        </div>
        <?php endif; ?>

        <button type="submit" name="submit" class="btn btn-primary btn-lg"><?php echo __('signup'); ?></button>
    </form>
</div>

<?php if(Config::get("app.type") == "remote"): ?>
<script src="https://www.google.com/recaptcha/api.js?hl=<?php echo Language::code()?>" async defer></script>
<?php endif; ?>

<!-- Modal -->
<div class="modal fade" id="sof_modal" tabindex="-1" role="dialog" style="z-index: 9999;">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h1 class="modal-title">Statement of Faith</h1>
      </div>
      <div class="modal-body">
              <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <p><em>The following statement of faith is subscribed to by all member
                        organizations of and contributors to the <a href="https://unfoldingword.org" target="_blank">unfoldingWord</a> project. It
                        is in agreement with the <a href="http://www.lausanne.org/en/documents/lausanne-covenant.html" title="http://www.lausanne.org/en/documents/lausanne-covenant.html" target="_blank">Lausanne Covenant</a>.</em></p>

                <p>We believe that Christian belief can and should be divided into
                    <strong>essential beliefs</strong> and <strong>peripheral beliefs</strong>.</p>

                <h3 id="essential-beliefs">Essential beliefs</h3>

                <p>Essential beliefs are what define a follower of Jesus Christ and can
                    never be compromised or ignored.</p>

                <ul>
                    <li>We believe the Bible to be the only inspired, inerrant, sufficient,
                        authoritative Word of God.</li>
                    <li>We believe that there is one God, eternally existent in three
                        persons: God the Father, Jesus Christ the Son and the Holy Spirit.</li>
                    <li>We believe in the deity of Jesus Christ.</li>
                    <li>We believe in the humanity of Jesus Christ, in His virgin birth, in
                        His sinless life, in His miracles, in His vicarious and atoning
                        death through His shed blood, in His bodily resurrection, in His
                        ascension to the right hand of the Father.</li>
                    <li>We believe that every person is inherently sinful and so is
                        deserving of eternal hell.</li>
                    <li>We believe that salvation from sin is a gift of God, provided
                        through the sacrificial death and resurrection of Jesus Christ,
                        attained by grace through faith, not by works.</li>
                    <li>We believe that true faith is always accompanied by repentance and
                        regeneration by the Holy Spirit.</li>
                    <li>We believe in the present ministry of the Holy Spirit by whose
                        indwelling the follower of Jesus Christ is enabled to live a godly
                        life.</li>
                    <li>We believe in the spiritual unity of all believers in the Lord Jesus
                        Christ, from all nations and languages and people groups.</li>
                    <li>We believe in the personal and physical return of Jesus Christ.</li>
                    <li>We believe in the resurrection of both the saved and the lost; the
                        unsaved will be resurrected to eternal damnation in hell and the
                        saved will be resurrected to eternal blessing in heaven with God.</li>
                </ul>

                <h3 id="peripheral-beliefs">Peripheral beliefs</h3>

                <p>Peripheral beliefs are everything else that is in Scripture but about
                    which sincere followers of Christ may disagree (e.g. Baptism, Lord’s
                    Supper, the Rapture, etc.). We choose to agree to disagree agreeably on
                    these topics and press on together toward a common goal of making
                    disciples of every people group (Matthew 28:18-20).</p>
                <br />
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <input type="button" class="btn btn-success" id="sof_agree" value="<?php echo __('accept_btn'); ?>" />
        <input type="button" class="btn btn-danger" id="sof_cancel" value="<?php echo __('deny_btn'); ?>" />
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="tou_modal" tabindex="-1" role="dialog" style="z-index: 9999;">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h1 class="modal-title">Freedom</h1>
      </div>
      <div class="modal-body">
              <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <p>All content in the unfoldingWord project is made available under a
                    <a href="http://creativecommons.org/licenses/by-sa/4.0/" target="_blank">Creative Commons Attribution-ShareAlike 4.0 License</a>.</p>

                <p>You are free to:</p>

                <ul>
                    <li><strong>Share</strong> — copy and redistribute the material in any medium or format</li>
                    <li><strong>Adapt</strong> — remix, transform, and build upon the material for any purpose, even commercially</li>
                </ul>

                <p>Under the following conditions:</p>

                <ul>
                    <li><strong>Attribution</strong> — You must attribute the work as follows: “Original work available at <a href="https://unfoldingword.org" target="_blank">unfoldingWord.org</a>.”
                        Attribution statements in derivative works should not in any way suggest that we endorse you or your use of this work.</li>
                    <li><strong>ShareAlike</strong> — If you remix, transform, or build upon the material, you must distribute your contributions under
                        the same license as the original.</li>
                </ul>

                <p>Use of trademarks: <strong>unfoldingWord™</strong> is a trademark of Distant Shores Media and may not be included on any derivative
                    works created from this content. Unaltered content from <a href="https://unfoldingword.org" target="_blank">unfoldingWord.org</a> must include the unfoldingWord logo when
                    distributed to others. If you alter the content in any way, you must remove the unfoldingWord logo before distributing your work.</p>

                <p>For contributors to projects in Door43 or unfoldingWord, use this form:</p>

                <p><a class="btn btn-dark" href="https://unfoldingword.org/assets/docs/legal/unfoldingWord - Guidelines &amp; License.pdf" target="_blank">Download Guidelines &amp; License PDF</a></p>

                <p>For release of existing content under CC BY-SA, use this form:</p>

                <p><a class="btn btn-dark" href="https://unfoldingword.org/assets/docs/legal/unfoldingWord - License Release Form.pdf" target="_blank">Download License Release PDF</a></p>
                <br />
            </div>
        </div>
      </div>
      <div class="modal-footer">
                <input type="button" class="btn btn-success" id="tou_agree" value="<?php echo __('accept_btn'); ?>" />
                <input type="button" class="btn btn-danger" id="tou_cancel" value="<?php echo __('deny_btn'); ?>" />
      </div>
    </div>
  </div>
</div>
<style>
    .popover {
      z-index:5;
    }
    .chosen-choices {
        min-height: 45px;
    }
    .chosen-single {
        min-height: 45px;
    }
    .chosen-container {
        font-size: 16px !important;
    }
    .search-choice {
        line-height: 30px !important;
    }
    .chosen-container-single .chosen-single {
        line-height: 42px !important;
    }
    .chosen-container-multi .chosen-choices li.search-field input[type="text"] {
        height: 42px !important;
    }
    .has-error .chosen-choices, .has-error .chosen-single {
        border-color: #a94442 !important;
    }
</style>
<?
Assets::js([
    template_url('js/formvalidation.js?2'),
    template_url('js/chosen.jquery.min.js'),
]);

Assets::css([
    template_url('css/chosen.min.css'),
]);
?>

<script>
    (function () {
        $("select").chosen().change(function () {
            formGroup = $(this).parents(".form-group");
            formGroup.removeClass('has-error');
            if ($(this).hasClass("select-chosen-single")) {
                $(".chosen-single", formGroup).popover('destroy');
            }
            if ($(this).hasClass("select-chosen-multiple")) {
                $(".chosen-choices", formGroup).popover('destroy');
            }
        });
    })()
</script>
