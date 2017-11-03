var app = angular.module('inspinia.payment', [ 'inspinia.services' ]);

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
        }
    }
});

function PaymentCtrl ($scope, notify, PaymentService, util) {

}

function PaymentHistoryCtrl ($rootScope, $scope, notify, PaymentService, util, localStorageService, $filter) {

    $scope.items = [];
    $scope.orderStatuses = [];

    util.pager($scope, localStorageService);
    util.searchPage($scope, localStorageService, "allostat_payment_history_filters_v1.0");
    $scope.filters['orderDateFrom'] = new Date();
    $scope.filters['orderDateTo'] = new Date();

    PaymentService.loadOrderStatuses().then(
        function(data) {
            $scope.orderStatuses = data.statuses;
        }
    );

    $scope.load = function() {
        var df = $scope.filters['orderDateFrom'].setHours(0,0,0,0);
        var dt = $scope.filters['orderDateTo'].setHours(23,59,59,999);

        var filters = {
            page: $scope.filters['page'],
            size: $scope.filters['size'],
            orderId: $scope.filters['orderId'],
            customerUid: $rootScope.user.customerUid,
            customerEmail: $scope.filters['customerEmail'],
            orderStatusId: $scope.filters['orderStatusId'],
            orderStatusName: $scope.filters['orderStatusName'],
            orderDateFrom: $filter('date')(df, 'yyyy-MM-dd HH:mm:ss'),
            orderDateTo: $filter('date')(dt, 'yyyy-MM-dd HH:mm:ss'),
            sumFrom: $scope.filters['sumFrom'],
            sumTo: $scope.filters['sumTo']
        };
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
    }
}

function PaymentTariffCtrl ($rootScope, $scope, notify, PaymentService, util) {

}