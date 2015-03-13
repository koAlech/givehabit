var app = angular.module('givehabit', []);

app.controller('controller', function ($scope,Chrome) {
    // Initiation code
    $scope.init = function () {
        $scope.things = "banana";

        var res = Chrome.getInfo();
        $scope.$apply();
        res.then(function(greeting) {
            $scope.things = "ok";
        }, function(reason) {
            $scope.things = "nope";
        });
    }

    $scope.init();


});

app.service('Chrome', function($q) {

        this.getInfo = function() {
            var deferred = $q.defer();
            chrome.tabs.query({currentWindow: true, active: true}, function(tabs) {
                deferred.resolve(tabs[0]);
            });
            return deferred.promise;
        };

});