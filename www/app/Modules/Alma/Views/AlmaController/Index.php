<div class="content"  ng-app="Alma" ng-controller="textRangeController">
    <div class="row">
        
        
        <div class="col-md-8" ng-mouseup="getText()">
			<div get-main-text="textRefreshEvent"></div>
        </div>
        
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Переводы слов
                </div>
                <div class="panel-body">
                    <form ng-submit="addTerm('word')">
                        <input ng-model="text" id="add-new-word">
                        <button class="btn btn-default">Добавить слово</button>
                    </form>
                </div>
            </div>


            <div class="panel" ng-class="(chosen === undefined) ? 'panel-default' : 'panel-info'" id="word_block">
                <div class="panel-heading">
                    <h4 class="panel-title row">
                        <span ng-if="chosen === undefined" class="col-md-12">Слово</span>
                        <span ng-if="chosen !== undefined" ng-bind="chosen.title" class="bold col-md-10"></span>
                        <a ng-click="wordCancel()" ng-if="chosen !== undefined" class="text-mute text-right col-md-2" href="#" title="Закрыть слово" onclick="return false">
                            <span class="glyphicon glyphicon-remove text-mute"></span>
                        </a>
                    </h4>
                </div>
                <div ng-if="chosen === undefined" class="panel-body">
                    выберите слово.
                </div>
                
                <div ng-if="chosen != undefined" class="panel-body">
                    <ul class="nav nav-tabs">
                        <li class="active"><a data-toggle="tab" href="#translations_{{ chosen.id }}">Переводы</a></li>
                        <!--<li><a data-toggle="tab" href="#synonyms_{{ chosen.id }}">Синонимы</a></li>-->
                        <li><a data-toggle="tab" href="#variants_{{ chosen.id }}">Варианты</a></li>
                    </ul>

                    <div class="tab-content">
                        
                        <div id="translations_{{ chosen.id }}" class="tab-pane fade in active">
                            <div class="panel-body">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Перевод</th>
                                            <th>Голосов</th>
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
                                                <a ng-click="vote(translation.id, 'vote')" href="#" title="Отдать голос" onclick="return false">
                                                    <span class="glyphicon glyphicon-plus text-primary"></span>
                                                </a>
                                            </td>
                                            <td>
                                                <a ng-click="vote(translation.id, 'vote_back')" href="#" title="Отозвать голос" onclick="return false">
                                                    <span class="glyphicon glyphicon-minus text-primary"></span>
                                                </a>
                                            </td>
                                            <td>
                                                <a ng-click="vote(translation.id, 'approve')" ng-if="translation.is_approved == 0" href="#" title="Утвердить перевод" onclick="return false">
                                                    <span class="glyphicon glyphicon-ok text-primary"></span>
                                                </a>
                                                <a ng-click="vote(translation.id, 'approve_back')" ng-if="translation.is_approved == 1" href="#" title="Отменить утверждение" onclick="return false">
                                                    <span class="glyphicon glyphicon-remove text-primary"></span>
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
											<td colspan="5" style="padding:0;">
												<div id="comment_{{ translation.id }}" class="collapse" style="padding:8px;">
													<h6>коммент:</h6>
													<div ng-bind="translation.comment" style="margin-bottom:5px;"></div>
													<div class="text-right small">
														<a ng-if="translation.comment != ''" data-toggle="collapse" data-target="#comment_edit_{{ translation.id }}">редактировать</a>
														<a ng-if="translation.comment == ''" data-toggle="collapse" data-target="#comment_edit_{{ translation.id }}">добавить</a>
													</div>
													<div id="comment_edit_{{ translation.id }}" class="collapse" style="padding:8px;">
														<form ng-submit="saveComment(translation)">
															<div class="form-group">
																<textarea ng-model="translation.comment" class="form-control" rows="2"></textarea>
															</div>
															<button class="btn btn-info">Сохранить</button>
														</form>
													</div>
												</div>
											</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <span get-term-form type="translation" placeholder="Перевод"></span>
                            </div>
                        </div>

<!--                        <div id="synonyms_{{ chosen.id }}" class="tab-pane fade">
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
                                <ul>
                                    <li ng-repeat="variant in chosen.variants">
                                        <span ng-bind="variant.title"></span>
                                    </li>
                                </ul>
                                <span get-term-form type="variant" placeholder="Вариант"></span>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php
\Helpers\Assets::js([
    site_url('Modules/Alma/Assets/js/angular.min.js'),
    site_url('Modules/Alma/Assets/js/angular-route.min.js'),
    site_url('Modules/Alma/Assets/js/angular-sanitize.min.js'),
    site_url('Modules/Alma/Assets/js/AlmaModule.js'),
]);
print isset($js) ? $js : '';
?>
