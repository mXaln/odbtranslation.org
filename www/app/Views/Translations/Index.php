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
        <h1 style="text-align: center">—— <?php echo __($data['data']->bookCode) ?> ——</h1>

        <h4 class="download_header" style="text-align: right">
        <?php if($data["data"]->sourceBible == "odb"): ?>
            <a href="<?php echo $data['data']->bookCode ?>/json">
                <?php echo __("download_json") ?>
            </a>
        <?php elseif(!in_array($data["mode"], ["tn","tq","tw"])): ?>
            <a href="<?php echo $data['data']->bookCode ?>/usfm">
                <?php echo __("download_usfm") ?>
            </a>
            <a href="<?php echo $data['data']->bookCode ?>/ts">
                <?php echo __("download_ts") ?>
            </a>
        <?php else: ?>
            <a href="<?php echo $data['data']->bookCode ?>/md">
                <?php echo __("download_markdown") ?>
            </a>
        <?php endif; ?>
        </h4>

        <div class="bible_book
            <?php echo ($data["data"]->bookProject == "sun"
                ? " sun_content" : "") . " font_".$data["data"]->targetLang?>"
        dir="<?php echo $data["data"]->direction ?>">
        <?php echo $data["book"] ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
