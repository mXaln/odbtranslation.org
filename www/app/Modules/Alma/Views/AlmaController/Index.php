<h2>
    Послание к Ефесянам 2
</h2>

<div class="content"  ng-app="Alma" ng-controller="textRangeController">
    <div class="row">
        
        <div class="col-md-8" ng-mouseup="getText()">
            <?= $text ?>
        </div>
        
        <div class="col-md-4">
            <h4>
                Переводы слов
            </h4>

            <div class="panel-body">
                <form ng-submit="addTerm('word')">
                    <input ng-model="text" id="add-new-word">
                    <button class="btn btn-default">Добавить слово</button>
                </form>
            </div>       

            <div class="panel-group" id="words_accordion">

                <div ng-repeat="word in words" class="panel panel-default">
                  <div class="panel-heading">
                    <h4 class="panel-title">
                        <a id="word_{{ word.id }}" data-toggle="collapse" data-parent="#words_accordion" href="#collapse_{{ word.id }}">
                            <span ng-bind="word.title"></span>
                        </a>
                    </h4>
                  </div>
                  <div id="collapse_{{ word.id }}" class="panel-collapse collapse">
                    <div class="panel-body">
                        
                        <ul class="nav nav-tabs">
                            <li class="active"><a data-toggle="tab" href="#translations_{{ word.id }}">Переводы</a></li>
                            <li><a data-toggle="tab" href="#synonyms_{{ word.id }}">Синонимы</a></li>
                            <li><a data-toggle="tab" href="#variants_{{ word.id }}">Варианты</a></li>
                          </ul>

                          <div class="tab-content">
                            <div id="translations_{{ word.id }}" class="tab-pane fade in active">
                                
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
                                            <tr ng-repeat="translation in word.translations">
                                                <td ng-bind="translation.title"></td>
                                                <td ng-bind="translation.votes"></td>
                                                <td>
                                                    <a href="#" title="Отдать голос" onclick="return false">+</a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    
                                    <span get-term-form type="translation"></span>
                                    
                                </div>
                                
                            </div>
                              
                            <div id="synonyms_{{ word.id }}" class="tab-pane fade">
                                
                                <div class="panel-body">
                                    <ul>
                                        <li ng-repeat="synonym in word.synonyms">
                                            <span ng-bind="synonym.title"></span>
                                        </li>
                                    </ul>
                                    
                                    <form ng-submit="addTerm('synonym', word.id)">
                                        <input ng-model="term" placeholder="Синоним">
                                        <button class="btn btn-default">Добавить</button>
                                    </form>

                                </div>
                                
                            </div>
                            
                            <div id="variants_{{ word.id }}" class="tab-pane fade">

                                <div class="panel-body">
                                    <ul>
                                        <li ng-repeat="variant in word.variants">
                                            <span ng-bind="variant.title"></span>
                                        </li>
                                    </ul>
                                    
                                    <form ng-submit="addTerm('variant', word.id)">
                                        <input ng-model="term" placeholder="Вариант слова">
                                        <button class="btn btn-default">Добавить</button>
                                    </form>

                                </div>
                            
                            </div>
                          </div>
                        
                    </div>
                  </div>
                </div>                

            </div>
            
        </div>
    </div>
</div>

<?php
Assets::js([
    site_url('Modules/Alma/Assets/js/angular.min.js'),
    site_url('Modules/Alma/Assets/js/angular-route.min.js'),
    site_url('Modules/Alma/Assets/js/AlmaModule.js'),
]);
?>
