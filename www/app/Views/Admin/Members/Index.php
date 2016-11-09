<?php
/**
 * Created by PhpStorm.
 * User: mXaln
 * Date: 03.11.2016
 * Time: 16:19
 */

?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h1 class="panel-title"><?php echo __("new_members_title") ?></h1>
    </div>

    <div class="form-inline dt-bootstrap no-footer">
        <div class="row">
            <div class="col-sm-12">
                <table class="table table-bordered table-hover" role="grid">
                    <thead>
                    <tr>
                        <th><?php echo __("userName") ?></th>
                        <th><?php echo __("name") ?></th>
                        <th><?php echo __("Email") ?></th>
                        <th><?php echo __("activated") ?></th>
                        <th><?php echo __("verified") ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($data["members"] as $member):?>
                        <tr>
                            <td><?php echo $member->userName ?></td>
                            <td><?php echo $member->firstName . " " . $member->lastName ?></td>
                            <td><?php echo $member->email ?></td>
                            <td><input type="checkbox" class="activateMember" data="<?php echo $member->memberID; ?>" <?php echo $member->active ? "checked='checked'" : "" ?> disabled='disabled'></td>
                            <td><input type="checkbox" class="verifyMember" data="<?php echo $member->memberID; ?>" <?php echo $member->verified ? "checked='checked'" : "" ?>></td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
