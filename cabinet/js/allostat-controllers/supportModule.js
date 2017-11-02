var app = angular.module('inspinia.support', ['angularFileUpload']);

app.controller('SupportCtrl', SupportCtrl);

app.service('SupportService', function(Restangular) {
    return {
        sendRequest: function(request) {
            return Restangular.all("support/createRequest").post(request);
        },
        deleteFile: function(fileId) {
            var params = {
                fileId: fileId
            };
            return Restangular.all("support/deleteFile").post(params);
        }
    }
});


function SupportCtrl($rootScope, $scope, SupportService, util, notify, FileUploader, localStorageService) {

    $scope.FILE_SIZE_LIMIT = 1050578;
    var TOKEN = localStorageService.get('apptoken');
    var uploadHeader = "";
    if (!util.isEmpty(TOKEN)) {
        uploadHeader = 'Bearer ' + TOKEN;
    }
    else {

    }

    $scope.totalFileSize = 0;

    $scope.supportRequest = {
        fileArray: [],
        requestText: ''
    };

    var checkFileSize = function(addedFileSize) {
        if(($scope.totalFileSize + addedFileSize) <= $scope.FILE_SIZE_LIMIT)  {
            $scope.totalFileSize += addedFileSize;
            return true;
        }
        return false;
    };

    var uploader = $scope.uploader = new FileUploader({
        url: '/api/support/upload',
        autoUpload: true,
        headers: {
            'Authorization': uploadHeader,
            'Accept': 'application/json'
        }
    });

    uploader.onWhenAddingFileFailed = function(item /*{File|FileLikeObject}*/, filter, options) {
        console.info('onWhenAddingFileFailed', item, filter, options);
    };
    uploader.onAfterAddingFile = function(fileItem) {
        if(!checkFileSize(fileItem.file.size)) {
            var j=uploader.queue.indexOf(fileItem);
            uploader.queue.splice(j,1);
            notify({
                message: 'Вы можете прикрепить 3 файла общим размером до 1 Мб',
                classes: 'allostat-alert-danger',
                position: 'center',
                duration: '5000'
            });
        }
    };

    $scope.deleteFile = function(f) {
        var file = f;
        var fSize = f.file.size;
        var idx = uploader.queue.indexOf(f);
        if (file.isUploaded) {
            SupportService.deleteFile(file.fileId).then(
                function(response) {
                    $scope.totalFileSize -= fSize;
                    uploader.queue.splice(idx, 1);
                }, function(err) {
                    notify({
                        message: err.data.error,
                        classes: 'allostat-alert-danger',
                        position: 'center',
                        duration: '5000'
                    });
                }
            );
        } else {
            uploader.queue.splice(idx, 1);
        }
    };

    uploader.onSuccessItem = function(fileItem, response, status, headers) {
        fileItem.fileId = response.fileId;
    };

    uploader.onErrorItem = function(fileItem, response, status, headers) {
        var idx = uploader.queue.indexOf(fileItem);
        uploader.queue.splice(idx, 1);
        notify({
            message: "Не удалось загрузить файл",
            classes: 'allostat-alert-danger',
            position: 'center',
            duration: '5000'
        });
    };

    uploader.onProgressItem = function(fileItem, progress) {
        console.info('onProgressItem', fileItem, progress);
    };

    $scope.sendRequest = function() {
        $scope.supportRequest.customerUid = $rootScope.user.customerUid;
        for(var i=0; i<uploader.queue.length; i++) {
            $scope.supportRequest.fileArray.push(uploader.queue[i].fileId);
        }

        $scope.doingResolve = true;
        SupportService.sendRequest($scope.supportRequest).then(
            function(response) {
                $scope.doingResolve = false;
                uploader.queue = [];
                $scope.supportRequest.fileArray = [];
                $scope.supportRequest.requestText = "";
                $scope.totalFileSize = 0;
                notify({
                    message: "Заявка принята с номером [" + response.requestId + "]",
                    position: 'center',
                    duration: '0'
                });
            },
            function(err) {
                $scope.doingResolve = false;
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