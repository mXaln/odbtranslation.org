<?php
use Core\Language;


if(\Helpers\Session::get("isSuperAdmin")):
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo Language::show("gw_projects", "Events") ?></h1>
    </div>

    <div class="form-inline dt-bootstrap no-footer">
        <div class="row">
            <div class="col-sm-6">&nbsp;</div>
            <div class="add-event-btn col-sm-6">
                <?php echo Language::show("create_gw_project", "Events") ?>
                <button id="cregwpr" class="btn btn-primary glyphicon glyphicon-plus"></button>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <table class="table table-bordered table-hover" role="grid">
                    <thead>
                        <tr>
                            <th><?php echo Language::show("gw_language", "Events") ?></th>
                            <th width="150px">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($data["gwProjects"] as $gwProject):?>
                        <tr>
                            <td><?php echo $gwProject->langName ?></td>
                            <td style="text-align: center"><span class="action-btn main-edit" data="<?php echo $gwProject->langID; ?>"><?php echo Language::show("edit", "Events") ?></span></td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="main-content form-panel">
    <div class="create-main-content panel panel-default">
        <div class="panel-heading">
            <h1 class="panel-title"><?php echo Language::show("create_gw_project", "Events") ?></h1>
            <span class="panel-close glyphicon glyphicon-remove"></span>
        </div>

        <div class="page-content row panel-body">
            <div class="errors"></div>

            <form action="/admin/rpc/create_gw_project" method="post" id="gwProject" style="width: 400px;">
                <div class="form-group">
                    <label for="gwLang"><?php echo Language::show('gw_language', 'Events'); ?></label>
                    <select class="form-control" id="gwLang" name="gwLang" data-placeholder="<?php echo Language::show("choose_gw_lang", "Events") ?>">
                        <option value=""></option>
                        <?php foreach ($data["gwLangs"] as $targetLang):?>
                        <option value="<?php echo $targetLang->langID; ?>"><?php echo $targetLang->langName; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="gwLang"><?php echo Language::show('facilitators', 'Members'); ?></label>
                    <select class="form-control" name="admins[]" id="adminsSelect" multiple data-placeholder="<?php echo Language::show("add_admins_by_username", "Events") ?>">
                        <option></option>
                    </select>
                </div>

                <br><br>

                <input type="hidden" name="act" id="gwProjectAction" value="create">
                <button type="submit" name="gwProject" class="btn btn-primary"><?php echo Language::show('create', 'Events'); ?></button>
                <img class="gwProjectLoader" width="24px" src="<?php echo \Helpers\Url::templatePath() ?>img/loader.gif">
            </form>
        </div>
    </div>
</div>
<?php
endif;


if(\Helpers\Session::get("isAdmin")):
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo Language::show("projects", "Events") ?></h1>
    </div>

    <h3 style="margin-left: 10px"><?php echo Language::show("choose_project", "Events") ?></h3>

    <div class="form-inline dt-bootstrap no-footer">
        <div class="row">
            <div class="col-sm-5">&nbsp;</div>
            <div class="add-event-btn col-sm-6">
                <?php echo Language::show("create_project", "Events") ?>
                <button id="crepr" class="btn btn-primary glyphicon glyphicon-plus"></button>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <table class="table table-bordered table-hover" role="grid">
                    <thead>
                    <tr>
                        <th><?php echo Language::show("target_lang", "Events") ?></th>
                        <th><?php echo Language::show("gw_language", "Events") ?></th>
                        <th><?php echo Language::show("project", "Events") ?></th>
                        <th><?php echo Language::show("source", "Events") ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($data["projects"] as $project):?>
                        <tr>
                            <td><a href="/admin/project/<?php echo $project->projectID ?>"><?php echo $project->tLang ?></a></td>
                            <td><?php echo $project->gwLang ?></td>
                            <td><?php echo Language::show($project->bookProject, "Events") ?></td>
                            <td><?php echo Language::show($project->sourceBible, 'Events'). " (".$project->sourceLangID.")"  ?></td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="sub-content form-panel">
    <div class="create-sub-content panel panel-default">
        <div class="panel-heading">
            <h1 class="panel-title"><?php echo Language::show("create_project", "Events") ?></h1>
            <span class="panel-close glyphicon glyphicon-remove"></span>
        </div>

        <div class="page-content row panel-body">
            <div class="subErrors"></div>

            <form action="/admin/rpc/create_project" method="post" id="project" style="width: 400px;">
                <div class="form-group">
                    <label for="subGwLangs"><?php echo Language::show('gw_language', 'Events'); ?></label>
                    <select class="form-control" id="subGwLangs" name="subGwLangs" data-placeholder="<?php echo Language::show('choose_gw_lang', 'Events'); ?>">
                        <option value=""></option>
                        <?php foreach ($data["memberGwLangs"] as $gwLang):?>
                            <option value="<?php echo $gwLang->langID."|".$gwLang->gwProjectID ?>"><?php echo $gwLang->langName; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <img class="subGwLoader" width="24px" src="<?php echo \Helpers\Url::templatePath() ?>img/loader.gif">
                </div>

                <div class="form-group">
                    <label for="targetLangs"><?php echo Language::show('target_lang', 'Events'); ?></label>
                    <select class="form-control" id="targetLangs" name="targetLangs" data-placeholder="<?php echo Language::show('choose_target_lang', 'Events'); ?>">
                        <option value=""></option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="projects"><?php echo Language::show('book_project', 'Events'); ?></label>
                    <select name="sourceTranslation" id="sourceTranslation" class="form-control" data-placeholder="<?php echo Language::show('choose_source_trans', 'Events'); ?>">
                        <option value=""></option>
                        <?php foreach ($data["sourceTranslations"] as $sourceTranslation):?>
                            <option value="<?php echo $sourceTranslation->bookProject . "|" . $sourceTranslation->langID; ?>"><?php echo $sourceTranslation->langName . " - " . Language::show($sourceTranslation->bookProject, 'Events') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group projectType hidden">
                    <label for="projectType"><?php echo Language::show('project_type', 'Events'); ?></label>
                    <select name="projectType" id="projectType" class="form-control" data-placeholder="<?php echo Language::show('choose_project_type', 'Events'); ?>">
                        <option value=""></option>
                        <option value="udb"><?php echo Language::show("udb", 'Events') ?></option>
                        <option value="ulb"><?php echo Language::show("ulb", 'Events') ?></option>
                    </select>
                </div>

                <br><br>

                <button type="submit" name="project" class="btn btn-primary"><?php echo Language::show('Create', 'Events'); ?></button>
                <img class="projectLoader" width="24px" src="<?php echo \Helpers\Url::templatePath() ?>img/loader.gif">
            </form>
        </div>
    </div>
</div>
<?php
endif;
?>

<link href="<?php echo \Helpers\Url::templatePath()?>css/chosen.min.css" type="text/css" rel="stylesheet" />
<script src="<?php echo \Helpers\Url::templatePath()?>js/chosen.jquery.min.js"></script>
<script src="<?php echo \Helpers\Url::templatePath()?>js/ajax-chosen.min.js"></script>

<script>
    var buttonCreate = '<?php echo Language::show('create', 'Events'); ?>';
    var buttonEdit = '<?php echo Language::show('edit', 'Events'); ?>';
</script>