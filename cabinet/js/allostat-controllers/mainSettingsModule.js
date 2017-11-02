var app = angular.module('inspinia.mainsettings', [ 'inspinia.services' ]);

app.service('MainSettingsService', function(Restangular) {
    return {
        loadMainSettings: function() {
            return Restangular.all("mainSettings/load").post();
        },
        saveMainSettings: function(settings) {
            return Restangular.all("mainSettings/save").post({settings: settings});
        }
    }
});

function MainSettingsCtrl ($rootScope, $scope, notify, MainSettingsService, util) {

    $scope.settings = [];

    MainSettingsService.loadMainSettings().then(
        function(result) {
            $scope.settings = result.settings;
        },
        function(err) {
            notify({
                message: err.data.error,
                classes: 'allostat-alert-danger',
                position: 'center',
                duration: '5000'
            });
        }
    );

    $scope.save = function() {
        MainSettingsService.saveMainSettings($scope.settings).then(
            function(result) {
                notify({
                    message: 'Настройки сохранены',
                    position: 'center',
                    duration: '5000'
                });
            },
            function(err) {
                notify({
                    message: err.data.error,
                    classes: 'allostat-alert-danger',
                    position: 'center',
                    duration: '5000'
                });
            }
        );
    }

}
