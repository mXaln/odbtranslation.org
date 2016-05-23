<?php
use Core\Language;


if(\Helpers\Session::get("isSuperAdmin")):
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h1 class="panel-title">Gateway Projects</h1>
    </div>

    <div class="form-inline dt-bootstrap no-footer">
        <div class="row">
            <div class="col-sm-6">&nbsp;</div>
            <div class="add-event-btn col-sm-6">
                Create Gateway Project
                <button id="cregwpr" class="btn btn-primary glyphicon glyphicon-plus"></button>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <table class="table table-bordered table-hover" role="grid">
                    <thead>
                        <tr>
                            <th>Gateway Language</th>
                            <th>&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($data["gwProjects"] as $gwProject):?>
                        <tr>
                            <td><?php echo $gwProject->langName ?></td>
                            <td>
                                <span class="action-btn main-edit" data="<?php echo $gwProject->langID; ?>">edit</span></td>
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
            <h1 class="panel-title">Create Gateway Project</h1>
            <span class="panel-close glyphicon glyphicon-remove"></span>
        </div>

        <div class="page-content row panel-body">
            <div class="errors"></div>

            <form action="/admin/rpc/create_gw_project" method="post" id="gwProject" style="width: 500px;">
                <div class="form-group">
                    <label for="gwLang"><?php echo Language::show('gw_language', 'Events'); ?></label>
                    <select class="form-control" id="gwLang" name="gwLang">
                        <option value="">Choose Gateway Language</option>
                        <?php foreach ($data["gwLangs"] as $targetLang):?>
                        <option value="<?php echo $targetLang->langID; ?>"><?php echo $targetLang->langName; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="gwLang"><?php echo Language::show('admins', 'Events'); ?></label>
                    <select class="form-control" name="admins[]" id="adminsSelect" multiple data-placeholder=" ">
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
        <h1 class="panel-title">Projects</h1>
    </div>

    <div class="form-inline dt-bootstrap no-footer">
        <div class="row">
            <div class="col-sm-6">&nbsp;</div>
            <div class="add-event-btn col-sm-6">
                Create Project
                <button id="crepr" class="btn btn-primary glyphicon glyphicon-plus"></button>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <table class="table table-bordered table-hover" role="grid">
                    <thead>
                    <tr>
                        <th>Target Language</th>
                        <th>Gateway Language</th>
                        <th>Project</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($data["projects"] as $project):?>
                        <tr>
                            <td><a href="/admin/project/<?php echo $project->projectID ?>"><?php echo $project->langName ?></a></td>
                            <td><?php echo $project->gwLang ?></td>
                            <td><?php echo Language::show($project->bookProject, "Events") ?></td>
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
            <h1 class="panel-title">Create Sub Event</h1>
            <span class="panel-close glyphicon glyphicon-remove"></span>
        </div>

        <div class="page-content row panel-body">
            <div class="subErrors"></div>

            <form action="/admin/rpc/create_project" method="post" id="project" style="width: 500px;">
                <div class="form-group">
                    <label for="subGwLangs"><?php echo Language::show('gw_language', 'Events'); ?></label>
                    <select class="form-control" id="subGwLangs" name="subGwLangs">
                        <option value="">-- Choose Gateway Language --</option>
                        <?php foreach ($data["memberGwLangs"] as $gwLang):?>
                            <option value="<?php echo base64_encode(json_encode($gwLang)) ?>"><?php echo $gwLang->langName; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <img class="subGwLoader" width="24px" src="<?php echo \Helpers\Url::templatePath() ?>img/loader.gif">
                </div>

                <div class="form-group">
                    <label for="targetLangs"><?php echo Language::show('target_language', 'Events'); ?></label>
                    <select class="form-control" id="targetLangs" name="targetLangs">
                        <option value="">-- Choose Target Language --</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="projects"><?php echo Language::show('book_project', 'Events'); ?></label>
                    <select name="sourceTranslation" class="form-control">
                        <option value="">Choose a Source Translation</option>
                        <?php foreach ($data["sourceTranslations"] as $sourceTranslation):?>
                            <option value="<?php echo $sourceTranslation->bookProject . "|" . $sourceTranslation->langID; ?>"><?php echo $sourceTranslation->langName . " - " . Language::show($sourceTranslation->bookProject, 'Events') ?></option>
                        <?php endforeach; ?>
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