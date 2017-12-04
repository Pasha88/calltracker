var app = angular.module('inspinia.tariffsettings', [ 'inspinia.services' ]);

app.service('TariffSettingsService', function(Restangular) {
    return {
        saveTariffList: function(customerUid, tariffList) {
            var params = {
                tariffList: tariffList,
                customerUid: customerUid
            };
            return Restangular.all("install/saveTariffList").post(params);
        },
        getTariffList: function(customerUid) {
            var params = {
                customerUid: customerUid
            };
            return Restangular.all("install/getTariffList").post(params);
        }, getTariffList2: function(customerUid) {
            var params = {
                customerUid: customerUid
            };
            return Restangular.all("install/getTariffList").post(params);
        }
    }
});

function TariffSettingsCtrl ($rootScope, $scope, notify, TariffSettingsService, util) {

    $scope.installConditions = {
        tariffs: false,
        defaultDomain: false
    };

    $scope.customerTimeZone = {
        offset: 0,
        value: ""
    };

    $scope.tariffList = [];
    $scope.tariffListBkp = [];
    $scope.tariffList2 = [];
    $scope.tariffListBkp2 = [];

    $scope.loadTariffList = function() {
        TariffSettingsService.getTariffList($rootScope.user.customerUid).then(
            function(res) {
                $scope.tariffList = res.itemArray.slice();
                $scope.tariffListBkp = res.itemArray.slice();
                $scope.customerTimeZone = "" + res.customerTimeZone;
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

    };

    $scope.addTariff = function() {
        if($scope.tariffList.length > 0 && util.isEmpty($scope.tariffList[$scope.tariffList.length-1]) ) {
            return;
        }
        $scope.tariffList.push({});
    };

    $scope.removeTariff = function(elem) {
        var ind = $scope.tariffList.indexOf(elem);
        $scope.tariffList.splice(ind, 1);
    };

    $scope.restoreTariffList = function() {
        $scope.tariffList = $scope.tariffListBkp.slice();
    };

    $scope.saveTariffList = function() {
        TariffSettingsService.saveTariffList($rootScope.user.customerUid, $scope.tariffList).then(
            function(res) {
                notify({
                    message: "Тарифы сохранены",
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
    };

    //================================= DEBUG ====================================

    $scope.loadTariffList();

}
