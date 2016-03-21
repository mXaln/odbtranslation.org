<?php
use Core\Language;
?>

<h1><?php echo Language::show('passwordreset_title', 'Members'); ?></h1>

<?php
echo \Core\Error::display($error);

if(isset($data['success'])){
    echo "<div class='alert alert-success'>";
    echo $data['success'];
    echo "</div>";
}
else {
    if ($data['step'] == 2) {
        ?>

        <form action='' method='post' style="width: 500px">
            <div class="form-group">
                <label for="password"><?php echo Language::show('password', 'Members'); ?></label>
                <input type="password" class="form-control" id="password" name="password"
                       placeholder="<?php echo Language::show('password', 'Members'); ?>" value="">
            </div>

            <div class="form-group">
                <label for="passwordConfirm"><?php echo Language::show('passwordConfirm', 'Members'); ?></label>
                <input type="password" class="form-control" id="passwordConfirm" name="passwordConfirm"
                       placeholder="<?php echo Language::show('passwordConfirm', 'Members'); ?>" value="">
            </div>

            <input type="hidden" name="csrf_token" value="<?php echo $data['csrf_token']; ?>"/>

            <button type="submit" name="submit"
                    class="btn btn-primary"><?php echo Language::show('continue', 'Members'); ?></button>
        </form>

        <?php
    }
}
?>


