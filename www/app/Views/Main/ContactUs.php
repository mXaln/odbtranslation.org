<?php
use \Shared\Legacy\Error;
?>

<div class="contact_us">
    <h1><?php echo __('contact_us_title') ?></h1>

    <?php
    echo Error::display($error);

    if(isset($data["success"]))
        echo Error::display($data["success"], "alert alert-success");
    ?>

    <form id="contact_us" action='' method='post'>
        <div class="form-group">
            <label for="name" class="sr-only"><?php echo __('name'); ?></label>
            <input type="text"
                   class="form-control input-lg"
                   id="name"
                   name="name"
                   placeholder="<?php echo __('name'); ?>"
                   value="<?php echo isset($_POST["name"]) ? $_POST["name"] : ""?>">
        </div>

        <div class="form-group">
            <label for="email" class="sr-only"><?php echo __('email'); ?></label>
            <input type="text"
                   class="form-control input-lg"
                   id="email"
                   name="email"
                   placeholder="<?php echo __('email'); ?>"
                   value="<?php echo isset($_POST["email"]) ? $_POST["email"] : ""?>">
        </div>

        <div class="form-group">
            <label for="lang" class="sr-only"><?php echo __('lang_select'); ?>: </label>
            <select id="lang"
                    class="form-control input-lg select-chosen-single"
                    name="lang"
                    data-placeholder="<?php echo __('lang_select'); ?>">
                <option></option>
                <?php foreach ($data["languages"] as $lang):?>
                    <?php if($lang->langID == "en") continue; ?>
                    <option <?php echo isset($_POST["lang"]) && $lang->langID == $_POST["lang"] ? "selected" : "" ?>>
                        <?php echo "[".$lang->langID."] " . $lang->langName .
                            ($lang->angName != "" && $lang->langName != $lang->angName ? " ( ".$lang->angName." )" : ""); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="message" class="sr-only"><?php echo __('message_content'); ?></label>
            <textarea class="form-control input-lg"
                      id="message"
                      name="message"
                      placeholder="<?php echo __('message_content'); ?>"
                      rows="10"><?php echo isset($_POST["message"]) ? $_POST["message"] : ""?></textarea>
        </div>

        <input type="hidden" name="csrfToken" value="<?php echo $data['csrfToken']; ?>" />

        <?php if(Config::get("app.type") == "remote"): ?>
            <div class="form-group">
                <div class="g-recaptcha" data-sitekey="<?php echo ReCaptcha::getSiteKey() ?>"></div>
            </div>
        <?php endif; ?>

        <button type="submit" name="submit" class="btn btn-primary btn-lg"><?php echo __('submit'); ?></button>
    </form>
</div>

<style>
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

<?php
Assets::js([
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


