<h2>
    Послание к Ефесянам 2
</h2>

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
                    <h4 class="panel-title">
                        <span ng-if="chosen === undefined">Слово</span>
                        <span ng-if="chosen !== undefined" ng-bind="chosen.title" class="bold"></span>
                    </h4>
                </div>
                <div ng-if="chosen === undefined" class="panel-body">
                    выберите слово.
                </div>
                
                <div ng-if="chosen != undefined" class="panel-body">
<!--                    <ul class="nav nav-tabs">
                        <li class="active"><a data-toggle="tab" href="#translations_{{ chosen.id }}">Переводы</a></li>
                        <li><a data-toggle="tab" href="#synonyms_{{ chosen.id }}">Синонимы</a></li>
                        <li><a data-toggle="tab" href="#variants_{{ chosen.id }}">Варианты</a></li>
                    </ul>-->

                    <div class="tab-content">
                        
                        <div id="translations_{{ chosen.id }}" class="tab-pane fade in active">
                            <div class="panel-body">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Перевод</th>
                                            <th>Голосов</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr ng-repeat="translation in chosen.translations">
                                            <td ng-bind="translation.title"></td>
                                            <td ng-bind="translation.votes || 0"></td>
                                            <td>
                                                <a ng-click="vote(translation.id)" href="#" title="Отдать голос" onclick="return false">+</a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <span get-term-form type="translation"></span>
                            </div>
                        </div>

<!--                        <div id="synonyms_{{ chosen.id }}" class="tab-pane fade">
                            <div class="panel-body">
                                <ul>
                                    <li ng-repeat="synonym in chosen.synonyms">
                                        <span ng-bind="synonym.title"></span>
                                    </li>
                                </ul>
                                <span get-term-form type="synonym"></span>
                            </div>
                        </div>

                        <div id="variants_{{ chosen.id }}" class="tab-pane fade">
                            <div class="panel-body">
                                <ul>
                                    <li ng-repeat="variant in chosen.variants">
                                        <span ng-bind="variant.title"></span>
                                    </li>
                                </ul>
                                <span get-term-form type="variant"></span>
                            </div>
                        </div>-->
                        
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
