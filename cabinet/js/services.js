var app = angular.module('inspinia.services', [ ]);

app.service('util', function() {
    
	return {
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
		},
        guid: function() {
            function s4() {
                return Math.floor((1 + Math.random()) * 0x10000)
                    .toString(16)
                    .substring(1);
            }
            return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
                s4() + '-' + s4() + s4() + s4();
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
