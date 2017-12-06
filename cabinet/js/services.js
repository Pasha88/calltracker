var app = angular.module('inspinia.services', [ ]);

app.service('util', function() {

    var obj = {
        isEmpty: function (obj) {
            if(typeof obj == 'undefined' || obj == null) {
                return true;
            }

            if(typeof obj == 'string') {
                return obj.length == 0;
            }

            if(Object.prototype.toString.call( obj ) === '[object Array]') {
                return obj.length <= 0;
            }

            if(Object.prototype.toString.call( obj ) === '[object Object]') {
                var size = 0, key;
                for (key in obj) {
                    if (obj.hasOwnProperty(key)) size++;
                }
                return size == 0;
            }

            return false;
        }
    };
    
	return {
		isEmpty: obj.isEmpty,
        guid: function() {
            function s4() {
                return Math.floor((1 + Math.random()) * 0x10000)
                    .toString(16)
                    .substring(1);
            }
            return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
                s4() + '-' + s4() + s4() + s4();
        },
        pager: function(scope, localStorageService) {
            scope.MAX_PAGE_BUTTONS = 5;
            scope.filters = [];
            scope.totalPages = 0;
            scope.pageSize = 20;
            scope.prev = function() {
                scope.filters['page']	= scope.filters['page'] > 0 ? scope.filters['page']-1 : 0;
                scope.load();
            };
            scope.next = function() {
                scope.filters['page']	= scope.filters['page'] < scope.totalPages-1 ? scope.filters['page']+1 : scope.totalPages-1;
                scope.load();
            };
            scope.first = function() {
                scope.filters['page']	= 0;
                scope.load();
            };
            scope.last = function() {
                scope.filters['page']	= scope.totalPages-1;
                scope.load();
            };
            scope.pageNumbers = function() {
                var current = scope.filters['page'];
                var result = [];
                var first = current-(scope.MAX_PAGE_BUTTONS/2); first = first < 0 ? 0 : first;
                var last = first + scope.MAX_PAGE_BUTTONS - 1; last = last > scope.totalPages-1 ? scope.totalPages-1 : last;
                for(var i=first; i<=last; i++) {
                    result.push(i);
                }
                return result;
            };

            scope.setPageSize = function(val) {
                if(scope.pageSize > val) {
                    scope.filters['page'] = 0;
                }
                scope.pageSize = val;
                scope.filters['size'] = val;
                localStorageService.set("allostat_filters_v1.2", scope.filters);
                scope.load();
            };

            scope.setFilter = function(name, val) {
                scope.filters[name] = val;
            };

            scope.loadPage = function(pageNum) {
                scope.filters['page'] = pageNum;
                scope.load();
            };
        },
        searchPage: function(scope, localStorageService, filtername) {
            scope.loadFilters = function() {
                scope.filters = localStorageService.get(filtername);
                if(obj.isEmpty(scope.filters)) {
                    scope.filters = {};
                    scope.filters['page'] = 0;
                    scope.filters['size'] = scope.pageSize;
                    // Еще фильтры
                    localStorageService.set(filtername, scope.filters);
                }
                else {
                    scope.pageSize = scope.filters['size'];
                }
            };
            scope.saveFilters = function(filtername) {
                if(obj.isEmpty(scope.filters)) {
                    scope.filters = {};
                    scope.filters['page'] = 0;
                    scope.filters['size'] = scope.pageSize;
                }
                localStorageService.set(filtername, scope.filters);
            };
            scope.loadFilters(filtername);
        }
	}
});

app.factory('confirmation', function($uibModal) {
    return {
        confirm: function (headerText, messageText) {
            var modalInstanceConfirmationDialog = $uibModal.open({
                templateUrl: '/cabinet/views/allostat/confirmation.html',
                windowClass: 'modal-mini',
                controller: ConfirmationCtrl,
                resolve: {
                    header: function () {
                        return headerText;
                    },
                    message: function () {
                        return messageText;
                    },
                    okButtonText: function() {
                        return 'ОК';
                    }
                }
            });

            return modalInstanceConfirmationDialog.result;
        },
        inform: function (headerText, messageText, okButtonText) {
            var modalInstanceConfirmationDialog = $uibModal.open({
                templateUrl: '/cabinet/views/allostat/information.html',
                windowClass: 'modal-mini',
                controller: ConfirmationCtrl,
                resolve: {
                    header: function () {
                        return headerText;
                    },
                    message: function () {
                        return messageText;
                    },
                    okButtonText: function() {
                        return okButtonText;
                    }
                }
            });

            return modalInstanceConfirmationDialog.result;
        }
    }
});

app.factory('BackRestangular', function(Restangular, API_END_POINT_BACK) {
    return Restangular.withConfig(function(RestangularConfigurer) {
        RestangularConfigurer.setBaseUrl(API_END_POINT_BACK);
    });
});
