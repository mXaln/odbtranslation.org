<div class="content"  ng-app="Alma" ng-controller="textRangeController">
    <div class="row">

        <?php if($bookCode != null): ?>
        <div class="col-md-12" style="font-size: 18px;"><a href="/alma"><?php echo __("to_list") ?></a></div>

        <div class="col-md-8" ng-mouseup="getText()">
			<div get-main-text="textRefreshEvent"></div>
        </div>
        
        <div class="col-md-4 word_tools">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?php echo __("word_translations") ?>
                </div>
                <div class="panel-body">
                    <form ng-submit="addTerm('word')">
                        <input ng-model="text" id="add-new-word">
                        <button class="btn btn-default"><?php echo __("add_word") ?></button>
                    </form>
                </div>
            </div>


            <div class="panel" ng-class="(chosen === undefined) ? 'panel-default' : 'panel-info'" id="word_block">
                <div class="panel-heading">
                    <h4 class="panel-title row">
                        <span ng-if="chosen === undefined" class="col-md-12"><?php echo __("word") ?></span>
                        <span ng-if="chosen !== undefined" ng-bind="chosen.title" class="bold col-md-10"></span>
                        <a ng-click="termDelete(chosen.id, 'word')" ng-if="chosen !== undefined" class="text-mute text-right col-md-1" href="#" title="<?php echo __("delete_word") ?>" onclick="return false">
                            <span class="glyphicon glyphicon-trash text-mute"></span>
                        </a>
                        <a ng-click="wordCancel()" ng-if="chosen !== undefined" class="text-mute text-right col-md-1" href="#" title="<?php echo __("close_word") ?>" onclick="return false">
                            <span class="glyphicon glyphicon-remove text-mute"></span>
                        </a>
                    </h4>
                </div>
                <div ng-if="chosen === undefined" class="panel-body">
                    <?php echo __("choose_word") ?>.
                </div>
                
                <div ng-if="chosen != undefined" class="panel-body">
                    <ul class="nav nav-tabs">
                        <li class="active"><a data-toggle="tab" href="#translations_{{ chosen.id }}"><?php echo __("translations_title") ?></a></li>
                        <!--<li><a data-toggle="tab" href="#synonyms_{{ chosen.id }}">Синонимы</a></li>-->
                        <li><a data-toggle="tab" href="#variants_{{ chosen.id }}"><?php echo __("variations") ?></a></li>
                    </ul>

                    <div class="tab-content">
                        
                        <div id="translations_{{ chosen.id }}" class="tab-pane fade in active">
                            <div class="panel-body">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><?php echo __("translation") ?></th>
                                            <th><?php echo __("votes") ?></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody ng-repeat="translation in chosen.translations">
                                        <tr ng-class="{'bg-info' : translation.is_approved == 1}">
                                            <td>
												<a ng-bind="translation.title" data-toggle="collapse" data-target="#comment_{{ translation.id }}"></a>
                                            </td>
                                            <td ng-bind="translation.votes || 0"></td>
                                            <td>
                                                <a ng-click="vote(translation.id, 'vote')" href="#" title="<?php echo __("vote") ?>" onclick="return false">
                                                    <span class="glyphicon glyphicon-plus text-primary"></span>
                                                </a>
                                            </td>
                                            <td>
                                                <a ng-click="vote(translation.id, 'vote_back')" href="#" title="<?php echo __("unvote") ?>" onclick="return false">
                                                    <span class="glyphicon glyphicon-minus text-primary"></span>
                                                </a>
                                            </td>
                                            <td>
                                                <a ng-click="vote(translation.id, 'approve')" ng-if="translation.is_approved == 0" href="#" title="<?php echo __("confirm_translation") ?>" onclick="return false">
                                                    <span class="glyphicon glyphicon-ok text-primary"></span>
                                                </a>
                                                <a ng-click="vote(translation.id, 'approve_back')" ng-if="translation.is_approved == 1" href="#" title="<?php echo __("cancel_confirmation") ?>" onclick="return false">
                                                    <span class="glyphicon glyphicon-remove text-primary"></span>
                                                </a>
                                            </td>
                                            <td>
                                                <a ng-click="termDelete(translation.id, 'translation')" href="#" title="<?php echo __("delete_translation") ?>" onclick="return false">
                                                    <span class="glyphicon glyphicon-trash text-primary"></span>
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
											<td colspan="6" style="padding:0;">
												<div id="comment_{{ translation.id }}" class="collapse" style="padding:8px;">
													<h6><?php echo __("comment") ?>:</h6>
													<div ng-bind="translation.comment" style="margin-bottom:5px;"></div>
													<div class="text-right small">
														<a ng-if="translation.comment != ''" data-toggle="collapse" data-target="#comment_edit_{{ translation.id }}"><?php echo __("edit") ?></a>
														<a ng-if="translation.comment == ''" data-toggle="collapse" data-target="#comment_edit_{{ translation.id }}"><?php echo __("add") ?></a>
													</div>
													<div id="comment_edit_{{ translation.id }}" class="collapse" style="padding:8px;">
														<form ng-submit="saveComment(translation)">
															<div class="form-group">
																<textarea ng-model="translation.comment" class="form-control" rows="2"></textarea>
															</div>
															<button class="btn btn-info"><?php echo __("save") ?></button>
														</form>
													</div>
												</div>
											</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <span get-term-form type="translation" placeholder="<?php echo __("translation") ?>"></span>
                            </div>
                        </div>

                        <!--<div id="synonyms_{{ chosen.id }}" class="tab-pane fade">
                            <div class="panel-body">
                                <ul>
                                    <li ng-repeat="synonym in chosen.synonyms">
                                        <span ng-bind="synonym.title"></span>
                                    </li>
                                </ul>
                                <span get-term-form type="synonym" placeholder="Синоним"></span>
                            </div>
                        </div>-->

                        <div id="variants_{{ chosen.id }}" class="tab-pane fade">
                            <div class="panel-body">
                                <table class="table table-hover">
                                    <tbody ng-repeat="variant in chosen.variants">
                                        <tr>
                                            <td><span ng-bind="variant.title"></span></td>
                                            <td>
                                                <a ng-click="termDelete(variant.id, 'word')" href="#" title="<?php echo __("delete_variation") ?>" onclick="return false">
                                                    <span class="glyphicon glyphicon-trash text-primary"></span>
                                                </a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <span get-term-form type="variant" placeholder="<?php echo __("variation") ?>"></span>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="col-md-12">
            <div style="float: left; width: 400px;">
                <h3><?php echo __("old_test") ?></h3>
            <?php foreach ($books as $book): ?>
            <div class="book_link"><a href="/alma/<?php echo $book->code ?>"><?php echo __($book->code) ?></a></div>
                <?php if($book->code == "mal"): ?>
            </div>
            <div style="float: left; width: 400px;">
                <h3><?php echo __("new_test") ?></h3>
                <?php endif; ?>
            <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="alma_top">
    <img src="<?php echo template_url("img/top.png") ?>" width="50">
</div>

<script>
    var bookCode = '<?php echo $bookCode ?>';

    $(document).ready(function() {
        $(window).scroll(function () {
            if ($(this).scrollTop() > 70)
            {
                $(".word_tools").addClass("scrolled");
                $(".alma_top").fadeIn(500);
            }
            else
            {
                $(".word_tools").removeClass("scrolled");
                $(".alma_top").fadeOut(500);
            }
        });

        $(".alma_top").click(function () {
            $('html,body').animate({
                    scrollTop: $("body").offset().top},
                'slow');
        });
    });
</script>

<?php
\Helpers\Assets::js([
    site_url('Modules/Alma/Assets/js/angular.min.js'),
    site_url('Modules/Alma/Assets/js/angular-route.min.js'),
    site_url('Modules/Alma/Assets/js/angular-sanitize.min.js'),
    site_url('Modules/Alma/Assets/js/AlmaModule.js'),
]);
print isset($js) ? $js : '';
?>
