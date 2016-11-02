<div class="panel panel-default">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo __("projects") ?></h1>
    </div>

    <div class="form-inline dt-bootstrap no-footer">
        <div class="row">
            <div class="col-sm-12">
                <table class="table table-bordered table-hover" role="grid">
                    <thead>
                    <tr>
                        <th><?php echo __("target_lang") ?></th>
                        <th><?php echo __("gw_language") ?></th>
                        <th><?php echo __("project") ?></th>
                        <th><?php echo __("source") ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($data["projects"] as $project): ?>
                        <tr>
                            <td><a href="/events/project/<?php echo $project->projectID ?>"><?php echo $project->tLang ?></a></td>
                            <td><?php echo $project->gwLang ?></td>
                            <td><?php echo __($project->bookProject) ?></td>
                            <td><?php echo __($project->sourceBible). " (".$project->sLang.")" ?></td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
