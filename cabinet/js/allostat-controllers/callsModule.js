var app = angular.module('inspinia.calls', [ 'inspinia.services' ]);

app.controller('CallsCtrl', CallsCtrl);

app.service('CallsService', function(Restangular, BackRestangular) {
	return {
		getCallsPage: function(filters) {
			var params = {
				filters: filters
			};
			return Restangular.all("calls/page").post(params);
		},
        getCallsPageBack: function(filters) {
            var params = {
                filters: filters
            };
            return BackRestangular.all("calls/page").post(params);
        },
		changeCallStatus: function(callId, typeId, numberId) {
			var params = {
				id: callId,
				typeId: typeId,
				numberId: numberId
			};
            return Restangular.all("calls/changeStatus").post(params);
		},
        deleteCall: function(callId) {
			var params = {
				id: callId
			};
			return Restangular.all("calls/delete").post(params);
		},
        hasNewCalls: function(lastCallId) {
            var params = {
                lastCallId: lastCallId
            };
            return Restangular.all("calls/hasNewCalls").post(params);
        },
        setNoYaAuth: function(customerUid) {
            var params = {
                customerUid: customerUid
            };
            return Restangular.all("calls/setNoYaAuth").post(params);
        }
	}
});

function CallsCtrl ($rootScope, $scope, CallsService, localStorageService, util, notify, $filter, $ngConfirm, $interval, confirmation, Idle, $state, $cookies) {

    var backGroundRequest = false;
	$scope.calls = [];
    $scope.lastCallId = null;
    $scope.alerts = [];
    $scope.yaIdAuthNotValid = null;
    util.pager($scope, localStorageService);
	util.searchPage($scope, localStorageService, "allostat_filters_v1.2");

    $scope.closeAlert = function(index) {
        $scope.alerts.splice(index, 1);
    };

	$scope.arrayByNumber = function(num) {
		var res = [];
		for(var i=0; i<num; i++) { res.push(i+1); }
		return res;
	};

	$scope.load = function(backGroundRequest) {
	    var filters = {
	        page: $scope.filters['page'],
            size: $scope.filters['size'],
            customerUid: $rootScope.user.customerUid
        };

	    var getCallsPage = backGroundRequest ? CallsService.getCallsPageBack : CallsService.getCallsPage;

        getCallsPage(filters).then(
			function(result) {
                $scope.lastCallId = result.lastCallId;
				$scope.calls = result.data;
				for(var i=0; i<$scope.calls.length; i++) {
					$scope.calls[i].call_date = Date.parse($scope.calls[i].call_date_time);
					$scope.calls[i].call_date_str = $filter('date')($scope.calls[i].call_date, "dd-MM-yyyy");
					$scope.calls[i].call_time_str = $filter('date')($scope.calls[i].call_date, "HH:mm:ss");
				}
				$scope.totalPages = result.totalPages;

				var yaBeenRefreshed =  $cookies.get("allostat_ya_metrics_updated");
				if(util.isEmpty(yaBeenRefreshed) || yaBeenRefreshed == "false") {
				    if(result.yesterdayCallsYaSent >= 1) {
                        var d = new Date();
                        d.setDate(d.getDate() + 1)
                        d.setHours(0,0,0,0);
                        $cookies.put("allostat_ya_metrics_updated", true, {
                            expires: d
                        });
                        $scope.alerts.push({ type: 'success', msg: 'Статистика по звонкам за прошедшие сутки была успешно отправлена в Яндекс метрику' });
                    }
                }

                var yaIdAuthNotValidDefer =  $cookies.get("allostat_ya_auth_not_valid");
                if(util.isEmpty(yaIdAuthNotValidDefer) || yaIdAuthNotValidDefer != "true") {
                   if(result.yaIdAuthValid == false) {
                       $scope.yaIdAuthNotValid = true;
                   }
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

    $scope.hasCall = function(item) {
    	$scope.callItem = item;
        $ngConfirm({
            title: 'Изменение статуса звонка',
            content: 'Изменить статус на "Входящий звонок" ?',
            scope: $scope,
            buttons: {
                ok: {
                    text: 'Да',
                    btnClass: 'btn-blue',
                    action: function(scope, button){
                        CallsService.changeCallStatus(scope.callItem.call_object_id, 2, scope.callItem.numberId).then(
                        	function(res) {
                        		if(res.result == false) {
                                    notify({
                                        message: res.message,
                                        classes: 'allostat-alert-danger',
                                        position: 'center',
                                        duration: '5000'
                                    });
								}
								$scope.load();
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
                },
                close: {
                	text: 'Отмена',
                    action: function(scope, button){}
				}
            }
        });
	};

    $scope.noCall = function(item) {
        $scope.callItem = item;
        $ngConfirm({
            title: 'Изменение статуса звонка',
            content: 'Изменить статус на "Звонка не было" ?',
            scope: $scope,
            buttons: {
                ok: {
                    text: 'Да',
                    btnClass: 'btn-blue',
                    action: function(scope, button){
                        CallsService.changeCallStatus(scope.callItem.call_object_id, 3, null, null).then(
                            function(res) {
                                $scope.load();
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
                },
                close: {
                    text: 'Отмена',
                    action: function(scope, button){}
                }
            }
        });
    };

    $scope.deleteCall = function(item) {
        $ngConfirm({
            title: 'Удаление',
            content: 'Удалить звонок?',
            scope: $scope,
            buttons: {
                ok: {
                    text: 'Да',
                    btnClass: 'btn-blue',
                    action: function(scope, button){
                        CallsService.deleteCall(item.call_object_id).then(
                            function(res) {
                                $scope.load();
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
                },
                close: {
                    text: 'Отмена',
                    action: function(scope, button){}
                }
            }
        });
	};

    $scope.deferYaIdAuth = function() {
        var d = new Date();
        d.setDate(d.getDate() + 1);
        d.setHours(0,0,0,0);
        $cookies.put("allostat_ya_auth_not_valid", true, {
            expires: d
        });
        $scope.yaIdAuthNotValid = false;
    };

    $scope.setNoYaAuth = function() {
        CallsService.setNoYaAuth($rootScope.user.customerUid).then(
            function() {
                $scope.yaIdAuthNotValid = false;
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

    var poller = null;

    var startNewCallsPoller = function() {
        $interval.cancel($scope.newCallsPoller);
        poller = $interval(function() {
            CallsService.hasNewCalls($scope.lastCallId).then(
                function(res) {
                    if(res.hasNewCalls == true) {
                        $scope.load();
                    }
                }
            );
        }, 5000);
    };

    $scope.$on('$destroy', function() {
        $interval.cancel(poller);
    });

    $scope.$on('IdleStart', function () {
        $interval.cancel(poller);
    });

    $scope.$on('IdleEnd', function () {
        $scope.load();
        startNewCallsPoller();
    });

    $scope.$on('IdleTimeout', function () {
        $interval.cancel(poller);
        confirmation.inform("Время ожидания истекло","Похоже, данные устарели. Нажмите ОК, чтобы обновить", "OK").then(
            function() {
                // $scope.load();
                $state.go('container.calls', {}, { reload: true });
                startNewCallsPoller();
            }
        );
    });

    backGroundRequest = false;
	$scope.load(backGroundRequest);
    startNewCallsPoller();
    Idle.watch();
}

