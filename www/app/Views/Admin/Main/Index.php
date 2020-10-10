<div class="panel panel-default">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo __("gw_projects") ?></h1>
    </div>

    <div class="form-inline dt-bootstrap no-footer">
        <div class="row">
            <div class="col-sm-6">&nbsp;</div>
            <div class="add-event-btn col-sm-6">
                <?php echo __("create_gw_project") ?>
                <button id="cregwpr" class="btn btn-primary glyphicon glyphicon-plus"></button>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <table class="table table-bordered table-hover" role="grid">
                    <thead>
                        <tr>
                            <th><?php echo __("gw_language") ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($data["gwProjects"] as $gwProject):?>
                        <tr>
                            <td><?php echo "[" . $gwProject->langID . "] " 
                                . $gwProject->langName 
                                . ($gwProject->angName != $gwProject->langName
                                    && $gwProject->angName != "" 
                                    ? " (" . $gwProject->angName . ")"
                                    : "") ?>
                            </td>
                            <td width="70">
                                <button data-id="<?php echo $gwProject->gwProjectID ?>"
                                        class="btn btn-warning gwproj_edit">
                                    <?php echo __("edit") ?>
                                </button>
                            </td>
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
            <h1 class="panel-title"><?php echo __("gw_project") ?></h1>
            <span class="panel-close glyphicon glyphicon-remove"></span>
        </div>

        <div class="page-content row panel-body">
            <div class="errors"></div>

            <form action="/admin/rpc/create_gw_project" method="post" id="gwProject" style="width: 400px;">
                <div class="form-group">
                    <label for="gwLang"><?php echo __('gw_language'); ?></label>
                    <select class="form-control" id="gwLang" name="gwLang" data-placeholder="<?php echo __("choose_gw_lang") ?>">
                        <option value=""></option>
                        <?php foreach ($data["gwLangs"] as $targetLang):?>
                        <option value="<?php echo $targetLang->langID; ?>">
                            <?php echo "[".$targetLang->langID."] " . $targetLang->langName . 
                                ($targetLang->langName != $targetLang->angName && $targetLang->angName != "" 
                                    ? " ( ".$targetLang->angName." )" : ""); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" name="gwProject" class="btn btn-primary"><?php echo __('create'); ?></button>
                <img class="gwProjectLoader" width="24px" src="<?php echo template_url("img/loader.gif") ?>">
            </form>
        </div>
    </div>
</div>


<div class="admins-content form-panel">
    <div class="edit-admins-content panel panel-default">
        <div class="panel-heading">
            <h1 class="panel-title"><?php echo __("superadmins") ?></h1>
            <span class="panel-close glyphicon glyphicon-remove"></span>
        </div>

        <div class="page-content row panel-body">
            <form action="/admin/rpc/edit_super_admins" method="post" id="gwProjectAdmins" style="width: 400px;">
                <div class="form-group">
                    <label for="superadmins"><?php echo __('superadmins'); ?></label>
                    <select class="form-control" name="superadmins[]" id="superadmins" multiple>
                        <option></option>
                    </select>
                </div>

                <input type="hidden" name="gwProjectID" id="gwProjectID" value="">

                <button type="submit" name="gwProjectAdmins" class="btn btn-primary"><?php echo __('save'); ?></button>
                <img class="gwProjectLoader" width="24px" src="<?php echo template_url("img/loader.gif") ?>">
            </form>
        </div>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo __("projects") ?></h1>
    </div>

    <h3 style="margin-left: 10px"><?php echo __("choose_project") ?></h3>

    <div class="form-inline dt-bootstrap no-footer">
        <div class="row">
            <div class="col-sm-5">&nbsp;</div>
            <div class="add-event-btn col-sm-6">
                <?php echo __("create_project") ?>
                <button id="crepr" class="btn btn-primary glyphicon glyphicon-plus"></button>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <table class="table table-bordered table-hover" role="grid">
                    <thead>
                    <tr>
                        <th><?php echo __("target_lang") ?></th>
                        <th><?php echo __("gw_language") ?></th>
                        <th><?php echo __("project") ?></th>
                        <th><?php echo __("source") ?></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($data["projects"] as $project):?>
                        <tr>
                            <td>
                                <a href="/admin/project/<?php echo $project->projectID ?>">
                                    <?php echo "[" . $project->targetLang . "] " . 
                                        $project->tLang . 
                                        ($project->tLang != $project->tAng && $project->tAng != "" 
                                            ? " (" . $project->tAng . ")" : "") ?>
                                </a>
                            </td> 
                            <td><?php echo "[" . $project->gwLang . "] " . 
                                $project->sLang . 
                                ($project->sLang != $project->sAng && $project->sAng != "" 
                                    ? " (" . $project->sAng . ")" : "") ?></td>
                            <td><?php echo __($project->bookProject) ?></td>
                            <td><?php echo __($project->sourceBible). " (".$project->sourceLangID.")"  ?></td>
                            <td>
                                <button
                                        data-projectid="<?php echo $project->projectID ?>"
                                        class="btn btn-success editProject"><?php echo __("edit") ?></button>
                            </td>
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
            <h1 class="panel-title"><?php echo __("project") ?></h1>
            <span class="panel-close glyphicon glyphicon-remove"></span>
        </div>

        <div class="page-content panel-body" style="max-height: 750px; overflow-y: auto;">
            <div class="subErrors"></div>

            <form action="/admin/rpc/create_project" method="post" id="project" style="width: 400px;">
                
                <div class="form-group">
                    <label for="projectMode"><?php echo __('project_mode'); ?></label>
                    <select name="projectMode" id="projectMode" class="form-control"
                            data-placeholder="<?php echo __('choose_project_mode'); ?>">
                        <option value=""></option>
                        <option value="bible"><?php echo __("bible_mode") ?></option>
                        <option value="odb"><?php echo __("odb_mode") ?></option>
                    </select>
                </div>
            
                <div class="form-group">
                    <label for="subGwLangs"><?php echo __('gw_language'); ?></label>
                    <select class="form-control" id="subGwLangs" name="subGwLangs"
                            data-placeholder="<?php echo __('choose_gw_lang'); ?>">
                        <option value=""></option>
                        <?php foreach ($data["gwProjects"] as $gwLang): ?>
                            <option value="<?php echo $gwLang->langID ."|".$gwLang->gwProjectID ?>">
                                <?php echo "[".$gwLang->langID."] " . $gwLang->langName . 
                                    ($gwLang->langName != $gwLang->angName && $gwLang->angName != "" 
                                        ? " ( ".$gwLang->angName." )" : ""); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <img class="subGwLoader" width="24px" src="<?php echo template_url("img/loader.gif") ?>">
                </div>

                <div class="form-group">
                    <label for="targetLangs"><?php echo __('target_lang'); ?></label>
                    <select class="form-control" id="targetLangs" name="targetLangs"
                            data-placeholder="<?php echo __('choose_target_lang'); ?>">
                        <option value=""></option>
                    </select>
                </div>

                <div class="form-group sourceTranslation">
                    <label for="sourceTranslation"><?php echo __('book_project'); ?></label>
                    <select name="sourceTranslation" id="sourceTranslation" class="form-control"
                            data-placeholder="<?php echo __('choose_source_trans'); ?>">
                        <option value=""></option>
                        <?php foreach ($data["sources"] as $source): ?>
                            <?php if(in_array($source->slug, ["tn","tq","tw"])) continue; ?>
                            <option value="<?php echo $source->slug . "|" . $source->langID; ?>">
                                <?php echo "[".$source->langID."] "
                                    . $source->langName . " - "
                                    . $source->name . " [".$source->slug."]" ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="add_custom_src">
                        <a href="/admin/tools/source"><?php echo __("add_custom_src") ?></a>
                    </div>
                </div>

                <div class="form-group projectType">
                    <label for="projectType"><?php echo __('project_type'); ?></label>
                    <select name="projectType" id="projectType" class="form-control"
                            data-placeholder="<?php echo __('choose_project_type'); ?>">
                        <option value=""></option>
                        <option value="ulb"><?php echo __("ulb") ?></option>
                        <option value="odb"><?php echo __("odb") ?></option>
                        <option value="sun"><?php echo __("sun") ?></option>
                    </select>
                </div>

                <div class="form-group sourceTools hidden">
                    <label for="sourceTools"></label>
                    <select name="sourceTools" id="sourceTools" class="form-control" data-placeholder="">
                        <option value=""></option>
                        <?php foreach ($data["gwLangs"] as $lang): ?>
                            <option value="<?php echo $lang->langID ?>">
                                <?php echo "[".$lang->langID."] " . $lang->langName .
                                    ($lang->langName != $lang->angName && $lang->angName != ""
                                        ? " ( ".$lang->angName." )" : ""); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <hr>
                <div class="form-group toolsTn">
                    <label for="toolsTn"><?php echo __("tn") ?></label>
                    <select name="toolsTn" id="toolsTn" class="form-control" data-placeholder="">
                        <option value=""></option>
                        <?php foreach ($data["gwLangs"] as $lang): ?>
                            <option value="<?php echo $lang->langID ?>">
                                <?php echo "[".$lang->langID."] " . $lang->langName .
                                    ($lang->langName != $lang->angName && $lang->angName != ""
                                        ? " ( ".$lang->angName." )" : ""); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group toolsTq">
                    <label for="toolsTq"><?php echo __("tq") ?></label>
                    <select name="toolsTq" id="toolsTq" class="form-control" data-placeholder="">
                        <option value=""></option>
                        <?php foreach ($data["gwLangs"] as $lang): ?>
                            <option value="<?php echo $lang->langID ?>">
                                <?php echo "[".$lang->langID."] " . $lang->langName .
                                    ($lang->langName != $lang->angName && $lang->angName != ""
                                        ? " ( ".$lang->angName." )" : ""); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group toolsTw">
                    <label for="toolsTw"><?php echo __("tw") ?></label>
                    <select name="toolsTw" id="toolsTw" class="form-control" data-placeholder="">
                        <option value=""></option>
                        <?php foreach ($data["gwLangs"] as $lang): ?>
                            <option value="<?php echo $lang->langID ?>">
                                <?php echo "[".$lang->langID."] " . $lang->langName .
                                    ($lang->langName != $lang->angName && $lang->angName != ""
                                        ? " ( ".$lang->angName." )" : ""); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <br><br>

                <input type="hidden" name="act" id="projectAction" value="create">
                <input type="hidden" name="projectID" id="projectID">

                <button type="submit" name="project" class="btn btn-primary"><?php echo __('create'); ?></button>
                <img class="projectLoader" width="24px" src="<?php echo template_url("img/loader.gif") ?>">
            </form>
        </div>
    </div>
</div>

<link href="<?php echo template_url("css/chosen.min.css")?>" type="text/css" rel="stylesheet" />
<script src="<?php echo template_url("js/chosen.jquery.min.js")?>"></script>
<script src="<?php echo template_url("js/ajax-chosen.min.js")?>"></script>