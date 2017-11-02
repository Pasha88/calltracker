var app = angular.module('inspinia.usersettings', [ 'inspinia.services' ]);

app.service('SettingsService', function(Restangular, $http) {
    return {
        resetPwd: function(customerUid, oldPwd, newPwd) {
            var params = {
                customerUid: customerUid,
                oldPwd: oldPwd,
                newPwd: newPwd
            };
            return Restangular.all("settings/change_pwd").post(params);
        },
        loadUserSettings: function(customerUid) {
            var params = {
                customerUid: customerUid
            };
            return Restangular.all("settings/load").post(params);
        },
        saveUserSettings: function(settings) {
            return Restangular.all("settings/save").post(settings);
        }
    }
});

function UserSettingsCtrl ($rootScope, $scope, PWD_MIN_LENGTH, notify, SettingsService, util) {
    $scope.oldPwd = "";
    $scope.newPwd = "";
    $scope.newPwdRepeat = "";

    $scope.customerTimeZone = {
        offset: 0,
        value: ""
    };

    $scope.timePickerFromOptions = {
        step: 15,
        timeFormat: 'H:i'
    };
    $scope.timePickerToOptions = {
        step: 15,
        timeFormat: 'H:i'
    };

    $scope.upTimeSchedule = false;

    $scope.upTimeFrom = new Date(1970,0,1,7,0,0,0);

    $scope.upTimeTo = new Date(1970,0,1,23,0,0,0);

    $scope.isScheduledUptime = function() {
        return $scope.upTimeSchedule == 'true';
    };

    $scope.pwdMinLength = function() {
        return PWD_MIN_LENGTH;
    };

    $scope.saveButtonEnabled = function() {
        // return $resetPwdForm.valid;
    };

    $scope.resetPwd = function() {
        if($scope.newPwd !== $scope.newPwdRepeat) {
            notify({
                message: "Пароли не совпадают",
                classes: 'allostat-alert-danger',
                position: 'center',
                duration: '5000'
            });
            return;
        }

        SettingsService.resetPwd($rootScope.user.customerUid, $scope.oldPwd, $scope.newPwd).then(
            function(res) {
                $scope.oldPwd = "";
                $scope.newPwd = "";
                $scope.newPwdRepeat = "";
                notify({
                    message: 'Пароль изменен',
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

    var loadUserSettings = function() {
        SettingsService.loadUserSettings($rootScope.user.customerUid).then(
            function(result) {
                // if(util.isEmpty(result.customerTimeZone)) {
                //     result.customerTimeZone = new Date().getTimezoneOffset()/60*-1;
                // }
                $scope.customerTimeZone = "" + result.customerTimeZone;
                $scope.upTimeFrom = !util.isEmpty(result.upTimeFrom) ? new Date(result.upTimeFrom*1000) : $scope.upTimeFrom;
                $scope.upTimeTo = !util.isEmpty(result.upTimeTo) ? new Date(result.upTimeTo*1000) : $scope.upTimeTo;
                $scope.upTimeSchedule = new String(result.upTimeSchedule == 1);
            }
        );
    };

    $scope.saveUserSettings = function() {
        $scope.upTimeFrom.setYear(1970);
        $scope.upTimeFrom.setMonth(0, 1);
        $scope.upTimeTo.setYear(1970);
        $scope.upTimeTo.setMonth(0, 1);
        var settings = {
            customerUid: $rootScope.user.customerUid,
            customerTimeZone: $scope.customerTimeZone,
            upTimeFrom: $scope.upTimeFrom.getTime()/1000,
            upTimeTo: $scope.upTimeTo.getTime()/1000,
            upTimeSchedule: $scope.upTimeSchedule == 'true'
        };
        SettingsService.saveUserSettings(settings).then(
            function(res) {
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
    };

    $scope.getClientTimeZone = function(offset) {
       switch(offset) {
            case -12: return { offset: offset, value: "(GMT -12) Эневеток, Кваджалейн" };
            case -11: return { offset: offset, value: "(GMT -11) Остров Мидуэй, Самоа" };
            case -10: return { offset: offset, value: "(GMT -10) Гавайи" };
            case -9: return { offset: offset, value: "(GMT -9) Аляска" };
            case -8: return { offset: offset, value: "(GMT -8) Тихоокеанское время (США и Канада), Тихуана" };
            case -7: return { offset: offset, value: "(GMT -7) Горное время (США и Канада), Аризона" };
            case -6: return { offset: offset, value: "(GMT -6) Центральное время (США и Канада), Мехико" };
            case -5: return { offset: offset, value: "(GMT -5) Восточное время (США и Канада), Богота, Лима" };
            case -4: return { offset: offset, value: "(GMT -4) Атлантическое время (Канада), Ла Пас" };
            case -3: return { offset: offset, value: "(GMT -3) Бразилия, Буэнос-Айрес, Джорджтаун" };
            case -2: return { offset: offset, value: "(GMT -2) Среднеатлантическое время" };
            case -1: return { offset: offset, value: "(GMT -1) Азорские острова, острова Зелёного Мыса" };
            case -0: return { offset: offset, value: "(GMT +0)  Дублин, Лондон, Лиссабон, Касабланка, Эдинбург" };
            case 1: return { offset: offset, value: "(GMT +1) Брюссель, Копенгаген, Мадрид, Париж, Берлин" };
            case 2: return { offset: offset, value: "(GMT +2) Афины, Киев, Минск, Бухарест, Рига, Таллин" };
            case 3: return { offset: offset, value: "(GMT +3) Москва, Санкт-Петербург, Волгоград" };
            case 4: return { offset: offset, value: "(GMT +4) Абу-Даби, Баку, Тбилиси, Ереван" };
            case 5: return { offset: offset, value: "(GMT +5) Екатеринбург, Исламабад, Карачи, Ташкент" };
            case 6: return { offset: offset, value: "(GMT +6) Омск, Новосибирск, Алма-Ата, Астана" };
            case 7: return { offset: offset, value: "(GMT +7) Красноярск, Норильск, Бангкок, Ханой, Джакарта" };
            case 8: return { offset: offset, value: "(GMT +8) Иркутск, Пекин, Перт, Сингапур, Гонконг" };
            case 9: return { offset: offset, value: "(GMT +9) Якутск, Токио, Сеул, Осака, Саппоро" };
            case 10: return { offset: offset, value: "(GMT +10) Владивосток, Восточная Австралия, Гуам" };
            case 11: return { offset: offset, value: "(GMT +11) Магадан, Сахалин, Соломоновы Острова" };
            case 12: return { offset: offset, value: "(GMT +12) Камчатка, Окленд, Уэллингтон, Фиджи" };
        }
    };

    $scope.tzList =
        [   { offset: -12, value: "(GMT -12) Эневеток, Кваджалейн" },
            { offset: -11, value: "(GMT -11) Остров Мидуэй, Самоа" },
            { offset: -10, value: "(GMT -10) Гавайи" },
            { offset: -9, value: "(GMT -9) Аляска" },
            { offset: -8, value: "(GMT -8) Тихоокеанское время (США и Канада), Тихуана" },
            { offset: -7, value: "(GMT -7) Горное время (США и Канада), Аризона" },
            { offset: -6, value: "(GMT -6) Центральное время (США и Канада), Мехико" },
            { offset: -5, value: "(GMT -5) Восточное время (США и Канада), Богота, Лима" },
            { offset: -4, value: "(GMT -4) Атлантическое время (Канада), Ла Пас" },
            { offset: -3, value: "(GMT -3) Бразилия, Буэнос-Айрес, Джорджтаун" },
            { offset: -2, value: "(GMT -2) Среднеатлантическое время" },
            { offset: -1, value: "(GMT -1) Азорские острова, острова Зелёного Мыса" },
            { offset: 0, value: "(GMT +0)  Дублин, Лондон, Лиссабон, Касабланка, Эдинбург" },
            { offset: 1, value: "(GMT +1) Брюссель, Копенгаген, Мадрид, Париж, Берлин" },
            { offset: 2, value: "(GMT +2) Афины, Киев, Минск, Бухарест, Рига, Таллин" },
            { offset: 3, value: "(GMT +3) Москва, Санкт-Петербург, Волгоград" },
            { offset: 4, value: "(GMT +4) Абу-Даби, Баку, Тбилиси, Ереван" },
            { offset: 5, value: "(GMT +5) Екатеринбург, Исламабад, Карачи, Ташкент" },
            { offset: 6, value: "(GMT +6) Омск, Новосибирск, Алма-Ата, Астана" },
            { offset: 7, value: "(GMT +7) Красноярск, Норильск, Бангкок, Ханой, Джакарта" },
            { offset: 8, value: "(GMT +8) Иркутск, Пекин, Перт, Сингапур, Гонконг" },
            { offset: 9, value: "(GMT +9) Якутск, Токио, Сеул, Осака, Саппоро" },
            { offset: 10, value: "(GMT +10) Владивосток, Восточная Австралия, Гуам" },
            { offset: 11, value: "(GMT +11) Магадан, Сахалин, Соломоновы Острова" },
            { offset: 12, value: "(GMT +12) Камчатка, Окленд, Уэллингтон, Фиджи" }
        ];

    loadUserSettings();

}

function ResetForgottenPwdCtrl($scope, $stateParams, $http, notify, $state, $ngConfirm) {
    $scope.newPwd = "";
    $scope.newPwdRepeat = "";

    $scope.resetPwd = function() {
        if($scope.newPwd !== $scope.newPwdRepeat) {
            notify({
                message: "Пароли не совпадают",
                classes: 'allostat-alert-danger',
                position: 'center',
                duration: '5000'
            });
            return;
        }

        var params = {
            customerUid: $stateParams.customerUid,
            token: $stateParams.resetUID,
            newPwd: $scope.newPwd
        };
        $http.post(
            '/public_api/reset_pwd',
            params,
            { headers: {'Content-Type': 'application/json'} }
        ).then(
            function() {
                $ngConfirm({
                    title: 'Пароль изменен',
                    content: 'Пароль изменен',
                    scope: $scope,
                    buttons: {
                        ok: {
                            text: 'Ок',
                            btnClass: 'btn-blue',
                            action: function(scope, button){
                                $state.go('login');
                            }
                        }
                    }
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
}

