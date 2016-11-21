
angular.module('Alma', [])
.controller('textRangeController', function($scope, $http){
    
    $scope.words = [];
    $scope.term  = '';
    
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
            
            switch (type) {
                case 'word':
                    $scope.words.push(data.term);
                    break;
                
                case 'variant':
                    $.each($scope.words, function(key, word) {
                        if (word.id === data.term.word_id) {
                            word.variants['new_' + data.term.id] = data.term;
                        }
                    });
                    break;
            
                case 'synonym':
                    $.each($scope.words, function(key, word) {
                        if (word.id === data.term.word_id) {
                            word.synonyms['new_' + data.term.id] = data.term;
                        }
                    });
                    break;
            
                case 'translation':
                    $.each($scope.words, function(key, word) {
                        console.log(word.id, data.term.word_id);
                        if (word.id === data.term.word_id) {
                            if (word.translations == undefined) word.translations = {};
                            word.translations[word.translations.length] = data.term;
                            
                            console.log(word.translations, word.translations.length);
                        }
                    });
                    break;
            
            }
            
            $scope.text = '';
            $scope.term = '';
        })
        .error(function() {
            console.log('не удалось получить данные');
        })
        .finally(function() {
            
        });
    };
    
    
    $scope.getWords();
    
})
.directive("getTermForm", function() {
    return {
        template : '<form ng-submit="addTerm(\'translation\', word.id, term); term = \'\';">\n\
                        <input ng-model="term">\n\
                        <button class="btn btn-default">Добавить</button>\n\
                    </form>'
    };
});


