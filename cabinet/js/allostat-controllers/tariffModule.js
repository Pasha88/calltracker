var app = angular.module('inspinia.tariffsettings', [ 'inspinia.services' ]);

app.service('TariffSettingsService', function(Restangular) {
    return {
        load: function() {
            return Restangular.all("tariffSettings/load").post();
        },
        save: function(settings) {
            return Restangular.all("tariffSettings/save").post({settings: settings});
        }
    }
});

function TariffSettingsCtrl ($rootScope, $scope, notify, TariffSettingsService, util) {

    $scope.settings = [];

    TariffSettingsService.load().then(
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
        TariffSettingsService.save($scope.settings).then(
            function(result) {
                notify({
                    message: 'Настройки тарифов сохранены',
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
