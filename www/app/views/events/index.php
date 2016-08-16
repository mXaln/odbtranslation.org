<?php
use \Core\Language;
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo Language::show("projects", "Events") ?></h1>
    </div>

    <div class="form-inline dt-bootstrap no-footer">
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
                    <?php foreach($data["projects"] as $project): ?>
                        <tr>
                            <td><a href="/events/project/<?php echo $project->projectID ?>"><?php echo $project->tLang ?></a></td>
                            <td><?php echo $project->gwLang ?></td>
                            <td><?php echo Language::show($project->bookProject, "Events") ?></td>
                            <td><?php echo Language::show($project->sourceBible, "Events"). " (".$project->sLang.")" ?></td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
