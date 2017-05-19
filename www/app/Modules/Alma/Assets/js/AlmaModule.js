angular.module('Alma', ['ngSanitize'])
.controller('textRangeController', function($rootScope, $scope, $http){
    
    $scope.words = [];
    $scope.term  = '';
    $scope.mainText = '';
    $rootScope.triggerTextRefresh = function() {
        $rootScope.$broadcast('textRefreshEvent');
    };
    
    $scope.getWords = function(){
        $http.post('list-words')
        .success(function(data) {
            $.each(data, function(key, word) {
                $scope.words.push(word);
            });
        })
        .error(function(){
            console.log('не удалось получить данные');
        })
        .finally(function(){
            
        });

    };

    if(bookCode != "")
        $scope.getWords();
    
    $scope.findWord = function(word_id){
		for (var i = 0, len = $scope.words.length; i < len; i++) {
            var word = $scope.words[i];
            
            if (word.id == word_id) {
                return word;
                break;
            }
        }
        
        return undefined;
    };
    
    $scope.wordClick = function(word_id){
        var word = $scope.findWord(word_id);
        $scope.chosen = word;
    };
    $scope.wordCancel = function(){
        $scope.chosen = undefined;
    };
    
    $scope.getText = function(){
        var txt = '';
        if (window.getSelection) {
            txt = window.getSelection().toString();

            if (txt == '') return false;

            $scope.text = txt;
        } else {
            txt = document.selection.createRange().text;
        }
        
        $('#add-new-word').focus();
    };
    
    $scope.addTerm = function(type, word_id , term){
        var new_term = {
            title   : type === 'word' ? $scope.text : term,
            word_id : word_id
        };

        $http.post('add/' + type, new_term)
        .success(function(data) {
            
            if (data.error) {
                renderPopup(data.message);
                if (type == 'word' && data.type == 'exists') {
                    setTimeout(function(){
                        $scope.wordClick(data.term.id);
                    }, 1);
                }
                console.log(data.type, data.message);
            } else {
                switch (type) {
                    case 'word':
                        $scope.words.push(data.term);
                        
                        if (data.term !== undefined) {
                            setTimeout(function(){
                                $scope.wordClick(data.term.id);
                            }, 1);
                        }
                        break;

                    case 'translation':
                        angular.forEach($scope.words, function(word, key) {
                            if (word.id == data.term.word_id) {
                                if (word.translations === undefined) word.translations = [];
                                word.translations.push(data.term);
                            }
                        });
                        break;

                    case 'variant':
                        angular.forEach($scope.words, function(word, key) {
                            if (word.id == data.term.parent_id) {
                                if (word.variants === undefined) word.variants = [];
                                word.variants.push(data.term);
                            }
                        });
                        break;
                
//                    case 'synonym':
//                        angular.forEach($scope.words, function(word, key) {
//                            if (word.id == data.term.word_id) {
//                                if (word.synonyms === undefined) word.synonyms = [];
//                                word.synonyms.push(data.term);
//                            }
//                        });
//                        break;
                }

                $scope.text = '';
                $scope.term = '';
            }
            
            $scope.triggerTextRefresh();
        })
        .error(function() {
            console.log('не удалось получить данные');
        })
        .finally(function() {});
    };
    
    
    $scope.vote = function(term_id, action_type){
        switch(action_type) {
            case 'approve':
            case 'approve_back':
                var path = 'approve/'
                break;
            default:
                var path = 'vote/';
                break;
        }
        
        $http.post(path + term_id, {action_type : action_type})
        .success(function(data) {
            if (data.error) {
                renderPopup(data.message);
                console.log(data.type, data.message);
            } else {
                angular.forEach($scope.words, function(word, key){
                    if (word.id == data.word.id) {
                        $scope.words[key] = data.word;
                        $scope.chosen     = data.word;
                    }
                });
            }
        })
        .error(function() {
            console.log('не удалось получить данные');
        })
        .finally(function() {});
    };
    
    $scope.saveComment = function(translation){
		$http.post('add-comment', translation)
        .success(function(data) {
            if (data.error) {
                renderPopup(data.message);
                console.log(data.type, data.message);
            }
        })
        .error(function() {
            console.log('не удалось получить данные');
        })
        .finally(function() {});
	};
    
})
.directive('getMainText', function($rootScope, $http, $compile) {
    if(bookCode == "") return;
    return {
        link : function($scope, element, attrs) {
            var triggerReload = function() {
                $http.post('main-text/' + bookCode)
                .success(function(data) {
                        if (data.error) {
                            console.log('не удалось получить данные');
                        } else {
                            var linkFn = $compile(data.mainText);
                            var html   = linkFn($scope);

                            element.html(html);
                        }
                })
                .error(function(){
                        console.log('не удалось получить данные');
                });
            };
            
            triggerReload();                
            $rootScope.$on(attrs.getMainText, triggerReload);
        },
    }
})
.directive('getTermForm', function() {
    return {
        template : function (element, attrs) {
            var type = (attrs.type === undefined) ? '' : attrs.type;
            var placeholder = (attrs.placeholder === undefined) ? '' : attrs.placeholder;
            return '<form ng-submit="addTerm(\'' + type + '\', chosen.id, term); term = \'\';">\n\
                <input ng-model="term" placeholder="' + placeholder + '">\n\
                <button class="btn btn-info">Добавить</button>\n\
            </form>'
        }
    };
});


