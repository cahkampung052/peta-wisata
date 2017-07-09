app.controller('siteCtrl', function($scope, Data, toaster, $state) {
    $scope.authError = null;
    $scope.login = function(form) {
        $scope.authError = null;
        Data.post('site/login', form).then(function(result) {
            if (result.status_code == 200) {
                $state.go('site.dashboard');
            } else {
                $scope.authError = result.errors[0];
            }
        });
    };
})