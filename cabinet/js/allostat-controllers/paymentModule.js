var app = angular.module('inspinia.payment', [ 'inspinia.services' ]);
app.controller('CallsCtrl', CallsCtrl);
app.directive("mwConfirmClick", [
    function() {
        return {
            priority: -1,
            restrict: 'A',
            scope: {confirmFunction: "&mwConfirmClick" },
            link: function(scope, element, attrs){
                element.bind('click', function(e){
                    //default message
                    var tariffName = $('#selected_user_tariff').find(":selected").text();
                    var message = attrs.mwConfirmClickMessage ? (attrs.mwConfirmClickMessage + " " + tariffName) : "Будет произведена смена тарифа!?";
                    if(confirm(message)) {
                        scope.confirmFunction();
                    }
                });
            }
        }
    }
]);

// datepicker empty input workaround: See  https://github.com/g00fy-/angular-datepicker/issues/199#issuecomment-154249452
// dpapp = angular.module('datePicker', []);
// dpapp.filter('mFormat', function () {
//     return function (m, format, tz) {
//         if (!(moment.isMoment(m))) {
//             return '';
//         }
//         return tz ? moment.tz(m, tz).format(format) : m.format(format);
//     };
// });

app.service('PaymentService', function(Restangular) {
    return {
        loadOrderStatuses: function() {
            return Restangular.all("order/allstatuses").post();
        },
        getOrders: function(filters) {
            return Restangular.all("order/getOrders").post({filters: filters});
        },
        makePayment: function(customerUid, sum) {
            return Restangular.all("order/makePayment").post( {customerUid: customerUid, sum: sum } );
        },
        saveUserTariff: function(customerUid, selectedTariff) {
             var params = {
                selectedTariff: selectedTariff,
                customerUid: customerUid
            };
            return Restangular.all("install/saveUserTariff").post(params);
        },
        getUserTariff: function(customerUid) {
            var params = {
                customerUid: customerUid
            };
            return Restangular.all("install/getUserTariff").post(params);
        }
    }
});

function PaymentCtrl ($rootScope, $scope, notify, PaymentService, util) {

    $scope.sum = 0.0;
    $scope.numberPattern = '^[1-9][0-9]{1,6}$';

    $scope.redirectConfirm = function() {
        PaymentService.makePayment($rootScope.user.customerUid, $scope.sum).then(
            function(data){
                window.location = data.confirmationUrl;
            },
            function(err) {
                notify({
                    message: err.data.error,
                    classes: 'allostat-alert-danger',
                    position: 'center',
                    duration: '5000'
                });
        });
    }

}

function PaymentHistoryCtrl ($rootScope, $scope, notify, PaymentService, util, localStorageService, $filter) {

    $scope.items = [];
    $scope.orderStatuses = [];

    util.pager($scope, localStorageService);
    util.searchPage($scope, localStorageService, "allostat_payment_history_filters_v1.0");
    $scope.filters['orderDateFrom'] = moment().add(-30, 'days');
    $scope.filters['orderDateTo'] = moment();
    $scope.filters['customerEmail'] = $rootScope.user.role < 100 ? $rootScope.user.email : '';

    PaymentService.loadOrderStatuses().then(
        function(data) {
            $scope.orderStatuses = data.statuses;
        }
    );

    $scope.load = function() {
        var df = $scope.filters['orderDateFrom'].toDate().setHours(0,0,0,0);
        var dt = $scope.filters['orderDateTo'].toDate().setHours(23,59,59,999);

        var filters = {
            page: $scope.filters['page'],
            size: $scope.filters['size'],
            orderId: $scope.filters['orderId'],
            customerUid: null,//$rootScope.user.customerUid,
            customerEmail: $scope.filters['customerEmail'],
            orderStatusId: $scope.filters['orderStatusId'],
            orderStatusName: $scope.filters['orderStatusName'],
            orderDateFrom: $filter('date')(df, 'yyyy-MM-dd HH:mm:ss'),
            orderDateTo: $filter('date')(dt, 'yyyy-MM-dd HH:mm:ss'),
            sumFrom: $scope.filters['sumFrom'],
            sumTo: $scope.filters['sumTo']
        };

        $scope.saveFilters("allostat_payment_history_filters_v1.0");

        PaymentService.getOrders(filters).then(
            function(data){
                $scope.items = data.orders;
                for(var i=0; i<$scope.items.length; i++) {
                    $scope.items[i].orderDate = Date.parse($scope.items[i].orderDate);
                    $scope.items[i].orderDateStr = $filter('date')($scope.items[i].orderDate, "dd-MM-yyyy");
                    $scope.items[i].orderTimeStr = $filter('date')($scope.items[i].orderDate, "HH:mm:ss");
                }
                $scope.totalPages = data.totalPages;
            },
            function(err) {
                notify({
                    message: err.data.error,
                    classes: 'allostat-alert-danger',
                    position: 'center',
                    duration: '5000'
                });
        });
    };

    $scope.customerFilterDisabled = function() {
        return $rootScope.user.role < 100;
    };

    $scope.load();
}

function PaymentTariffCtrl ($rootScope, $scope, notify, PaymentService, confirmation) {
    $scope.userTariff = [];
    $scope.tariffList = [];
    $scope.selectedTariff = 0;
    $scope.selectedTariff = $scope.tariffList[0];

    $scope.itemList = [];
    $scope.changedValue  = function(item) {
        $scope.selectedTariff = item;
    };

    $scope.loadTariffList = function() {
        PaymentService.getUserTariff($rootScope.user.customerUid).then(
            function(res) {
                $scope.userTariff = res.itemArray.slice();
                $scope.tariffList = res.itemArray2.slice();
                $scope.selectedTariff = $scope.tariffList[0];
            },
            function(err) {
                notify({
                    message: 'Ошибка при загрузке первоначальных данных',
                    classes: 'allostat-alert-danger',
                    position: 'center',
                    duration: '5000'
                });
            }
        );
    };

    $scope.saveUserTariff = function() {
        var tariffName = $('#selected_user_tariff').find(":selected").text();
        confirmation.confirm("Смена тарифа", "Будет произведена смена тарифа на " + tariffName).then(
            function() {
                PaymentService.saveUserTariff($rootScope.user.customerUid, $scope.selectedTariff).then(
                    function(res) {
                        notify({
                            message: 'Тариф изменен',
                            classes: 'allostat-success-green',
                            position: 'center',
                            duration: '5000'
                        });
                        $scope.loadTariffList();
                    },
                    function(err) {
                        notify({
                            message: 'Ошибка сохранения тарифа пользователя',
                            classes: 'allostat-alert-danger',
                            position: 'center',
                            duration: '5000'
                        });
                    }
                );
            }
        );
    };

    $scope.loadTariffList();
}