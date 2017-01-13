angular.module('Alma', ['ngSanitize'])
.controller('textRangeController', function($rootScope, $scope, $http){
    
    $scope.words = [];
    $scope.term  = '';
    $scope.mainText = '';
    $rootScope.triggerTextRefresh = function() {
        $rootScope.$broadcast('textRefreshEvent');
    };
    
    $scope.getWords = function(){
        $http.post('alma/list-words')
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
    
    $scope.addTerm = function(type, word_id = null, term = ''){
        var new_term = {
            title   : type === 'word' ? $scope.text : term,
            word_id : word_id
        };

        $http.post('alma/add/' + type, new_term)
        .success(function(data) {
            
            if (data.error) {
                renderPopup(data.message);
                console.log(data.type, data.message);
            } else {
                switch (type) {
                    case 'word':
                        $scope.words.push(data.term);
                        break;

                    case 'translation':
                        $.each($scope.words, function(key, word) {
                            if (word.id === data.term.word_id) {
                                if (word.translations === undefined) word.translations = {};
                                word.translations[word.translations.length] = data.term;
                            }
                        });
                        break;

    //                case 'variant':
    //                    $.each($scope.words, function(key, word) {
    //                        if (word.id === data.term.word_id) {
    //                            word.variants['new_' + data.term.id] = data.term;
    //                        }
    //                    });
    //                    break;
    //            
    //                case 'synonym':
    //                    $.each($scope.words, function(key, word) {
    //                        if (word.id === data.term.word_id) {
    //                            word.synonyms['new_' + data.term.id] = data.term;
    //                        }
    //                    });
    //                    break;

                }

                $scope.text = '';
                $scope.term = '';
            }
            
            if (type === 'word' && data.term !== undefined) {
                setTimeout(function(){
                    $scope.wordClick(data.term.id);
                }, 1);
            }
            
            $scope.triggerTextRefresh();
        })
        .error(function() {
            console.log('не удалось получить данные');
        })
        .finally(function() {});
    };
    
    
    $scope.vote = function(word_id){
        $http.post('alma/vote/' + word_id)
        .success(function(data) {
            if (data.error) {
                renderPopup(data.message);
                console.log(data.type, data.message);
            } else {
                angular.forEach($scope.words, function(word, key){
                    if (word.id == data.word.id) {
                        angular.forEach(word.translations, function(translation, key){
                            if (translation.id == data.term.id) {
                                word.translations[key] = data.term;
                            }
                        });
                    }
                });
            }
        })
        .error(function() {
            console.log('не удалось получить данные');
        })
        .finally(function() {});
    };
})
.directive('getMainText', function($rootScope, $http, $compile) {
    return {
        link : function($scope, element, attrs) {
			var triggerReload = function() {
				$http.post('alma/get-main-text')
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
        template : '<form ng-submit="addTerm(\'translation\', chosen.id, term); term = \'\';">\n\
                        <input ng-model="term" placeholder="{{ forms[form].placeholder }}">\n\
                        <button class="btn btn-info">Добавить</button>\n\
                    </form>'
    };
});


