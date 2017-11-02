var app = angular.module('inspinia.configuration', [ 'restangular' ]);
        app.constant('BASE_PATH', '/');
        app.constant('API_END_POINT', '/api');
        app.constant('API_END_POINT_BACK', '/api_back');
		app.constant('API_PUBLIC_LOGIN_URL', '/public_api/authenticate');
        app.constant('API_LOGIN_URL', '/api/authenticate');
        app.constant('API_LOGIN_METHOD','POST');
		app.constant('LOGOUT_URL', '/logout');
        app.constant('LOGOUT_METHOD','POST');
        app.constant('PWD_MIN_LENGTH', 6);
        app.config(function (RestangularProvider, API_END_POINT) {
            RestangularProvider.setBaseUrl(API_END_POINT);
        });
