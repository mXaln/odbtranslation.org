<!-- Languages list -->
<?php if(isset($data['languages']) && !empty($data['languages'])): ?>
    <?php echo __("bible") ?>
    <br>
    <br>
    <?php foreach ($data['languages'] as $language): ?>
        <a href="/translations/<?php echo $language->targetLang ?>">
            <?php echo $language->angName
                .($language->langName != $language->angName ? " (".$language->langName.")" : "") ?>
        </a>
        <br>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Projects list -->
<?php if(isset($data['bookProjects']) && !empty($data['bookProjects'])): ?>
    <a href="/translations">
        <?php echo __("bible") ?>
    </a>
    →
    <?php echo $data['language'][0]->angName
        .($data['language'][0]->langName != $data['language'][0]->angName ? ' ('.$data['language'][0]->langName.')' : '') ?>
    <br>
    <br>
    <?php foreach ($data['bookProjects'] as $bookProject): ?>
        <a href="/translations/<?php echo $bookProject->targetLang . "/". $bookProject->bookProject . "/" . $bookProject->sourceBible ?>">
            <?php echo strtoupper($bookProject->bookProject)
                ." (".__($bookProject->bookProject).")"
                .($bookProject->sourceBible == "odb" ? " - " . __("odb") : "") ?>
        </a>
        <br>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Books list -->
<?php if(isset($data['books']) && !empty($data['books'])): ?>
    <a href="/translations">
        <?php echo __("bible") ?>
    </a>
    →
    <a href="/translations/<?php echo $data['language'][0]->langID ?>">
        <?php echo $data['language'][0]->angName
            .($data['language'][0]->langName != $data['language'][0]->angName ? ' ('.$data['language'][0]->langName.')' : '') ?>
    </a>
    →
    <?php echo __($data['project']["bookProject"])
        .($data['project']['sourceBible'] == "odb" ? " - " . __("odb") : "") ?>
    <br>
    <br>

    <?php if(sizeof($data['books']) > 0 && $data['books'][0]->bookCode != ""): ?>
        <?php if($data['books'][0]->sourceBible == "odb"): ?>
            <h4 style="text-align: right">
                <a href="<?php echo $data['books'][0]->sourceBible ?>/dl/json">
                    <?php echo __("download_json") ?>
                </a>
            </h4>
        <?php elseif(!in_array($data["mode"], ["tn","tq","tw"])): ?>
            <h4 style="text-align: right">
                <a href="<?php echo $data['books'][0]->sourceBible ?>/dl/usfm">
                    <?php echo __("download_usfm") ?>
                </a>
            </h4>
        <?php else: ?>
            <h4 style="text-align: right">
                <a href="<?php echo $data['books'][0]->sourceBible ?>/dl/md">
                    <?php echo __("download_markdown") ?>
                </a>
            </h4>
        <?php endif; ?>

        <?php foreach ($data['books'] as $book): ?>
            <a href="/translations/<?php echo $book->targetLang . "/" .$book->bookProject . "/" . $book->sourceBible . "/" . $book->bookCode ?>">
                <?php echo __($book->bookCode) ?>
            </a>
            <br>
        <?php endforeach; ?>
    <?php endif; ?>
<?php endif; ?>

<!-- Book content -->
<?php if(isset($data['book'])): ?>
    <a href="/translations">
        <?php echo __("bible") ?>
    </a>
    →
    <a href="/translations/<?php echo $data['language'][0]->langID ?>">
        <?php echo $data['language'][0]->angName
            .($data['language'][0]->langName != $data['language'][0]->angName ? ' ('.$data['language'][0]->langName.')' : '') ?>
    </a>
    →
    <a href="/translations/<?php echo $data['language'][0]->langID . "/" .$data['project']['bookProject'] . "/" . $data['project']['sourceBible'] ?>">
        <?php echo __($data['project']['bookProject'])
            .($data['project']['sourceBible'] == "odb" ? " - " . __("odb") : "") ?>
    </a>
    →
    <?php echo __($data['bookInfo'][0]->code) ?>
    <br>
    <br>

    <?php if(!empty($data['book'])): ?>
        <div id="upload_menu">
            <span class="glyphicon glyphicon-export"></span>
            <ul>
                <?php if($data["data"]->sourceBible == "odb"): ?>
                <li>
                    <a href="<?php echo $data['data']->bookCode ?>/json">
                        <?php echo __("download_json") ?>
                    </a>
                </li>
                <?php elseif(!in_array($data["mode"], ["tn","tq","tw"])): ?>
                <li>
                    <a href="<?php echo $data['data']->bookCode ?>/usfm">
                        <?php echo __("download_usfm") ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo $data['data']->bookCode ?>/ts">
                        <?php echo __("download_ts") ?>
                    </a>
                </li>
                <?php else: ?>
                <li>
                    <a href="<?php echo $data['data']->bookCode ?>/md">
                        <?php echo __("download_markdown") ?>
                    </a>
                </li>
                <?php endif; ?>
                <li class="export_cloud">
                    <a href="<?php echo $data['data']->bookCode ?>/wacs/export"><?php echo __("upload_wacs") ?></a>
                </li>
                <li class="export_cloud">
                    <a href="<?php echo $data['data']->bookCode ?>/door43/export"><?php echo __("upload_door43") ?></a>
                </li>
            </ul>
        </div>

        <h1 style="text-align: center">—— <?php echo __($data['data']->bookCode) ?> ——</h1>

        <div class="bible_book
            <?php echo ($data["data"]->bookProject == "sun"
                ? " sun_content" : "") . " font_".$data["data"]->targetLang?>"
        dir="<?php echo $data["data"]->direction ?>">
        <?php echo $data["book"] ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="login_cloud_server form-panel">
    <div class="login_cloud_server_body panel panel-default">
        <div class="panel-heading">
            <h1 class="panel-title"><span class="cloud_server_name"></span></h1>
            <span class="panel-close glyphicon glyphicon-remove"></span>
        </div>

        <div class="page-content row panel-body">
            <div>
                <?php echo __("login_cloud_server") ?>
                <a href="" target="_blank" class="create_login_link"><?php echo __("create") ?></a>
            </div>
            <div class="login_cloud_form">
                <div class="form-group" style="width: 250px;">
                    <label for="cloud_username" style="width: 100%; display: block"><?php echo __('userName'); ?></label>
                    <input class="form-control" name="cloud_username" id="cloud_username">
                </div>
                <div class="form-group" style="width: 250px;">
                    <label for="cloud_password" style="width: 100%; display: block"><?php echo __('password'); ?></label>
                    <input class="form-control" name="cloud_password" id="cloud_password" type="password">
                </div>
                <div class="form-group" style="width: 250px;">
                    <input name="cloud_otp" id="cloud_otp" type="checkbox">
                    <label for="cloud_otp"><?php echo __('two_factor_auth'); ?></label>
                </div>
                <div class="form-group cloud_otp_code_group" style="width: 250px;">
                    <label for="cloud_otp_code" style="width: 100%; display: block"><?php echo __('cloud_otp_code'); ?></label>
                    <input class="form-control" name="cloud_otp_code" id="cloud_otp_code">
                </div>
                <div class="cloudError"></div>
                <br>
                <button type="submit" name="cloudLogin" class="btn btn-primary"><?php echo __("login"); ?></button>
                <input type="hidden" name="cloudServer" id="cloudServer" value="">
                <input type="hidden" name="cloudUrl" id="cloudUrl" value="">

                <img class="cloudLoginLoader" width="24px" src="<?php echo template_url("img/loader.gif") ?>">
            </div>
        </div>
    </div>
</div>
