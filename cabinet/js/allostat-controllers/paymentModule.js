var app = angular.module('inspinia.payment', [ 'inspinia.services' ]);

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

function PaymentHistoryCtrl ($rootScope, $scope, notify, PaymentService, util, localStorageService) {

    $scope.items = [];
    $scope.orderStatuses = [];

    util.pager($scope, localStorageService);
    util.searchPage($scope, localStorageService, "allostat_payment_history_filters_v1.0");

    PaymentService.loadOrderStatuses().then(
        function(data) {
            $scope.orderStatuses = data.statuses;
        }
    );

    $scope.load = function() {
        var filters = {
            page: $scope.filters['page'],
            size: $scope.filters['size'],
            orderId: $scope.filters['orderId'],
            customerUid: $rootScope.user.customerUid,
            customerEmail: $scope.filters['customerEmail'],
            orderStatusId: $scope.filters['orderStatusId'],
            orderStatusName: $scope.filters['orderStatusName'],
            orderDateFrom: $scope.filters['orderDateFrom'],
            orderDateTo: $scope.filters['orderDateTo'],
            sumFrom: $scope.filters['sumFrom'],
            sumTo: $scope.filters['sumTo']
        };
        PaymentService.getOrders(filters).then(
            function(data){
                $scope.items = data.orders;
            },
            function(){});
    }
}

function PaymentTariffCtrl ($rootScope, $scope, notify, PaymentService, util) {

}