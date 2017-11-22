var app = angular.module('inspinia.tariffsettings', [ 'inspinia.services' ]);

app.service('TariffSettingsService', function(Restangular) {
    return {
        saveTariffList: function(customerUid, phoneNumberList) {
            var params = {
                phoneNumberList: phoneNumberList,
                customerUid: customerUid
            };
            return Restangular.all("install/saveTariffList").post(params);
        },
        getTariffList: function(customerUid) {
            var params = {
                customerUid: customerUid
            };
            return Restangular.all("install/getTariffList").post(params);
        }
    }
});

function TariffSettingsCtrl ($rootScope, $scope, notify, TariffSettingsService, util, $stateParams, $http) {

    $scope.installConditions = {
        phoneNumbers: false,
        defaultDomain: false
    };

    $scope.phoneNumberList = [];
    $scope.phoneNumberListBkp = [];

    $scope.loadPhoneNumberList = function() {
        TariffSettingsService.getTariffList($rootScope.user.customerUid).then(
            function(res) {
                $scope.phoneNumberList = res.itemArray.slice();
                $scope.phoneNumberListBkp = res.itemArray.slice();
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

    $scope.onBlur = function(ind, item) {
        $scope.phoneNumberList[ind] = item;
    };

    $scope.addPhoneNumber = function() {
        if($scope.phoneNumberList.length > 0 && util.isEmpty($scope.phoneNumberList[$scope.phoneNumberList.length-1]) ) {
            return;
        }
        $scope.phoneNumberList.push({});
    };

    $scope.removePhoneNumber = function(elem) {
        var ind = $scope.phoneNumberList.indexOf(elem);
        $scope.phoneNumberList.splice(ind, 1);
    };

    $scope.restorePhoneNumberList = function() {
        $scope.phoneNumberList = $scope.phoneNumberListBkp.slice();
    };

    $scope.savePhoneNumberList = function() {
        TariffSettingsService.saveTariffList($rootScope.user.customerUid, $scope.phoneNumberList).then(
            function(res) {
                notify({
                    message: "Номера сохранены2",
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

    $scope.isEmpty = function(val) {
        return util.isEmpty(val);
    };

    //================================= DEBUG ====================================

    $scope.loadPhoneNumberList();

}
