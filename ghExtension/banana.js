var app = angular.module('app', ['app.service']);

app.controller('ItemCtrl', function ($scope, chromeHelper,server) {

    $scope.init = function () {

        $scope.website = "Noresult!";

        chromeHelper.getActiveTabDomain(function (domain) {
            $scope.website = domain;
            $scope.$apply();
        });

        var response = server.sendImp({"userId": "5060", "impressionURL":"www.etgalim.com"});

        response.success(function(data) {
            $scope.website = "ok";
            $scope.$apply();
        }).error(function(data) {
            $scope.website = "nope";
            $scope.$apply();
        });



        //var response = server.ping();
        /*
        var response = server.sendImp({"userId": "5060", "impressionURL":"www.etgalim.com"});

        response.success(function(data) {
            $scope.website = "ok";
        }).error(function(data) {
            $scope.website = "nope";
        });
        */
    }

    $scope.init();
});

var service = angular.module('app.service', []);

service.factory('chromeHelper', function() {
    var chromeHelper = {};

    chromeHelper.getActiveTabDomain = function (callback){
        chrome.tabs.query({'active': true}, function(tabs){
            if(tabs && tabs.length > 0) callback(tabs[0].url);
        });
    };

    return chromeHelper;
});

app.service('server', function ($http) {

    this.ping = function () {
        return $http.get("http://192.168.12.11/ghServer/index.php/ping");
        //return $http.get("http://www.google.com");
    }

    this.sendImp = function(all) {
        return $http({
                url: 'http://192.168.12.11/ghServer/index.php/sendImpression',
            method: 'POST',
            data: JSON.stringify(all),
            headers: {'Content-Type': 'application/json'}
        });

    }
});
