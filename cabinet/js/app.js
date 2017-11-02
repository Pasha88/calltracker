/**
 * INSPINIA - Responsive Admin Theme
 *
 */
(function () {
    angular.module('inspinia', [
        'ui.router',                    // Routing
        'oc.lazyLoad',                  // ocLazyLoad
        'ui.bootstrap',                 // Ui Bootstrap
        'pascalprecht.translate',       // Angular Translate
        'ngIdle',                       // Idle timer
        'ngSanitize',                    // ngSanitize
		'LocalStorageModule',		
		'restangular',
        'cgNotify',
        'cp.ngConfirm',
        'ncy-angular-breadcrumb',
		'angularFileUpload',
        'ui.timepicker',
        'ngCookies',

		'inspinia.authenticate',
		'inspinia.configuration',
		'inspinia.services',

        'inspinia.usersettings',
		'inspinia.calls',
		'inspinia.install',
        'inspinia.support',
        'inspinia.mainsettings',
        'inspinia.tariffsettings'
    ])
})();

// Other libraries are loaded dynamically in the config.js file using the library ocLazyLoad