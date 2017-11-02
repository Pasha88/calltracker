var app = angular.module('inspinia.install', ['ui.router']);

app.controller('InstallWizardCtrl', InstallWizardCtrl);
app.controller('InstallCheckCtrl', InstallCheckCtrl);

app.service('InstallService', function(Restangular) {
    return {
        savePhoneList: function(customerUid, phoneNumberList) {
            var params = {
                phoneNumberList: phoneNumberList,
                customerUid: customerUid
            };
            return Restangular.all("install/savePhoneList").post(params);
        },
        getPhoneList: function(customerUid) {
            var params = {
                customerUid: customerUid
            };
            return Restangular.all("install/getPhoneList").post(params);
        },
        saveGaId: function(customerUid, gaId) {
            var params = {
                customerUid: customerUid,
                gaId: gaId
            };
            return Restangular.all("install/save/gaid").post(params);
        },
        saveDefaultNumber: function(customerUid, defaultNumber, defaultDomain) {
            var params = {
                customerUid: customerUid,
                number: defaultNumber,
                domain: defaultDomain
            };
            return Restangular.all("install/save/defaultNumber").post(params);
        },
        checkInstall: function(customerUid) {
            return Restangular.all("install/check").post({ customerUid: customerUid});
        },
        saveYaId: function(customerUid, yaId, guid) {
            var params = {
                customerUid: customerUid,
                yaId: yaId,
                guid: guid
            };
            return Restangular.all("install/save/yaId").post(params);
        },
        confirmYaToken: function(customerUid, code, state) {
            var params = {
                customerUid: customerUid,
                code: code,
                state: state
            };
            return Restangular.all("install/confirm/yaId").post(params);
        }
//================================= DEBUG ====================================
        ,freeNumber: function(id) {
            var params = {
                id: id
            };
            return Restangular.all("install/free/number").post(params)
        },
        freeAllNumbers: function() {
            return Restangular.all("install/free/numbers").post({})
        }
//================================= DEBUG ====================================
    }
});


function InstallWizardCtrl($rootScope, $scope, InstallService, util, notify, $stateParams, $http) {

    $scope.installConditions = {
        phoneNumbers: false,
        defaultDomain: false,
        gaId: false
    };

    $scope.phoneNumberList = [];
    $scope.phoneNumberListBkp = [];

    $scope.gaId = {
        val: ''
    };
    $scope.gaIdBkp = {};

    $scope.defaultNumber = {
        val: ''
    };
    $scope.defaultNumberBkp = {};

    $scope.defaultDomain = {
        val: ''
    };
    $scope.defaultDomainBkp = {};

    $scope.yaId = {
        val: '',
        auth: 0
    };
    $scope.yaIdBkp = {};

    $scope.hasYaId = false;

    var confirmYaToken = function($stateParams) {

        InstallService.confirmYaToken($rootScope.user.customerUid, $stateParams.code, $stateParams.state).then(
            function(result) {
                if(result.success == true) {
                    notify({
                        message: 'Токен безопасности Yandex получен и сохранен',
                        position: 'center',
                        duration: '5000'
                    });
                }
                else {
                    notify({
                        message: result.error,
                        classes: 'allostat-alert-danger',
                        position: 'center',
                        duration: '5000'
                    });
                }
                $scope.loadPhoneNumberList();
            },
            function(err) {
                notify({
                    messageTemplate: '<span>' + err.data.error + '<span>',
                    classes: 'allostat-alert-danger',
                    position: 'center',
                    duration: '10000'
                });
            }

        );
    };

    if(!util.isEmpty($stateParams.code) && !util.isEmpty($stateParams.state)) {
        confirmYaToken($stateParams);
    }

    $scope.loadPhoneNumberList = function() {
        InstallService.getPhoneList($rootScope.user.customerUid).then(
            function(res) {
                $scope.gaId = { val: res.gaId };
                $scope.gaIdBkp = { val: res.gaId };
                $scope.yaId = { val: res.yaId, auth: res.yaIdAuth };
                $scope.yaIdBkp = { val: res.yaId, auth: res.yaIdAuth };

                $scope.defaultNumber = { val: res.defaultNumber };
                $scope.defaultNumberBkp = { val: res.defaultNumber };

                $scope.defaultDomain = { val: res.defaultDomain };
                $scope.defaultDomainBkp = { val: res.defaultDomainBkp };

                $scope.phoneNumberList = res.itemArray.slice();
                $scope.phoneNumberListBkp = res.itemArray.slice();

                $scope.hasYaId = !util.isEmpty(res.yaId) && !util.isEmpty($scope.yaId.auth);
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
        InstallService.savePhoneList($rootScope.user.customerUid, $scope.phoneNumberList).then(
            function(res) {
                notify({
                    message: "Номера сохранены",
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

    $scope.getScriptHeadString = function() {
        return "<script type=\"application\/javascript\"> \r\n" +
            " function loadJS(file) { \r\n" +
            "  var jsElm = document.createElement(\"script\"); \r\n" +
            " jsElm.type = \"application\/javascript\"; \r\n" +
            " jsElm.src = file;    \r\n" +
            " document.body.appendChild(jsElm); } \r\n" +
            " loadJS(\"http:\/\/develop2.allostat.ru\/public_api\/allostat\/numloader.js?customerUid=" + $rootScope.user.customerUid + "\"); \r\n" +
            " <\/script>";
    };

    $scope.getPhoneNumberMarkup = function() {
        return '<span class="phoneAllostat">' + $scope.defaultNumber.val + '<span>';
    };

    $scope.saveGaId = function() {
        InstallService.saveGaId($rootScope.user.customerUid, $scope.gaId.val).then(
            function(res) {
                $scope.gaIdBkp = { val: $scope.gaId.val };
                notify({
                    message: "GA ID сохранен",
                    position: 'center',
                    duration: '5000'
                });
            },
            function(err) {
                notify({
                    messageTemplate: "<span>err.data.error<span>",
                    classes: 'allostat-alert-danger',
                    position: 'center',
                    duration: '5000'
                });
            }
        );
    };

    $scope.restoreGaId = function() {
        $scope.gaId = { val: $scope.gaIdBkp.val };
    };

    $scope.getGaIdVal = function() {
        return $scope.gaId.val;
    };

    $scope.saveDefaultNumber = function() {
        var domainRegexp = new RegExp("^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}$");

        if(util.isEmpty($scope.defaultDomain.val) || !domainRegexp.test($scope.defaultDomain.val)) {
            notify({
                message: "Некорректно указан домен. Введите домен второго уровня, например \"site.com\"",
                classes: 'allostat-alert-danger',
                position: 'center',
                duration: '5000'
            });
            return;
        }

        InstallService.saveDefaultNumber($rootScope.user.customerUid, $scope.defaultNumber.val, $scope.defaultDomain.val).then(
            function(res) {
                $scope.defaultNumberBkp = { val: $scope.defaultNumber.val };
                $scope.defaultDomainBkp = { val: $scope.defaultDomain.val };
                notify({
                    message: "Номер по умолчанию и домен сохранены",
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

    $scope.restoreDefaultNumber = function() {
        $scope.defaultNumber = { val: $scope.defaultNumberBkp.val };
        $scope.defaultDomain = { val: $scope.defaultDomainBkp.val };
    };

    $scope.isEmpty = function(val) {
        return util.isEmpty(val);
    };

    $scope.saveYaId = function() {
        var guid = util.guid();
        InstallService.saveYaId($rootScope.user.customerUid, $scope.yaId.val, guid)
            .then(
            function(result) {
                window.location.href = 'https://oauth.yandex.ru/authorize?response_type=code&client_id=' + result.appId + '&state=' + guid;
            },
            function(err) {
                notify({
                    message: err.data.error,
                    position: 'center',
                    duration: '5000'
                });
            }
        );
    };

    $scope.unbindYaId = function() {
        InstallService.saveYaId($rootScope.user.customerUid, null, null).then(
            function(result) {
                if(result.result == true) {
                    $scope.loadPhoneNumberList();
                    notify({
                        message: "ID Яндекс.Метрики удален",
                        classes: 'allostat-alert-',
                        position: 'center',
                        duration: '5000'
                    });
                }
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
    $scope.freeNumber = function(item) {
        InstallService.freeNumber(item.id).then(
            function(result) {
                $scope.loadPhoneNumberList();
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

    $scope.freeAllNumbers = function() {
        InstallService.freeAllNumbers().then(
            function() {
                $scope.loadPhoneNumberList();
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

    $scope.loadPhoneNumberList();
}

function InstallCheckCtrl($rootScope, $scope, InstallService) {

    var parentScope = $scope.$parent;

    $scope.updateInstallConditions = function() {
        InstallService.checkInstall($rootScope.user.customerUid).then(
            function(result) {
                parentScope.installConditions.phoneNumbers = result.phoneNumbers;
                parentScope.installConditions.defaultDomain = result.defaultDomain;
                parentScope.installConditions.gaId = result.gaId;
            },
            function(error) {
            }
        );
    };

    $scope.installConditionsGood = function() {
        return parentScope.installConditions.phoneNumbers === true && parentScope.installConditions.defaultDomain === true && parentScope.installConditions.gaId === true;
    };

    $scope.updateInstallConditions();
}