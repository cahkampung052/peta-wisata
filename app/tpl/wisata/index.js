app.controller('WisataCtrl', function($scope, Data, toaster, uiGmapGoogleMapApi, FileUploader) {
    var tableStateRef;
    var control_link = "appwisata";
    $scope.displayed = [];
    $scope.form = {};
    $scope.is_edit = false;
    $scope.is_view = false;
    $scope.editorOptions = {
        height: '250px',
    };
    $scope.callMap = function(lat, long) {
        if (!$scope.is_view) {
            $scope.map = {
                center: {
                    latitude: lat,
                    longitude: long
                },
                scrollwheel: false,
                zoom: 13,
                markers: {
                    id: 1,
                    coords: {
                        latitude: lat,
                        longitude: long
                    }
                },
                events: {
                    click: function(map, eventName, originalEventArgs) {
                        var e = originalEventArgs[0];
                        var lat = e.latLng.lat(),
                            lon = e.latLng.lng();
                        var marker = {
                            id: 1,
                            coords: {
                                latitude: lat,
                                longitude: lon
                            }
                        };
                        $scope.map.markers = marker;
                        $scope.$apply();
                    }
                }
            };
        }
    };
    /** get list data */
    $scope.callServer = function callServer(tableState) {
        tableStateRef = tableState;
        $scope.isLoading = true;
        /** set offset and limit */
        var offset = tableState.pagination.start || 0;
        var limit = tableState.pagination.number || 10;
        var param = {
            offset: offset,
            limit: limit
        };
        /** set sort and order */
        if (tableState.sort.predicate) {
            param['sort'] = tableState.sort.predicate;
            param['order'] = tableState.sort.reverse;
        }
        /** set filter */
        if (tableState.search.predicateObject) {
            param['filter'] = tableState.search.predicateObject;
        }
        Data.get(control_link + '/index', param).then(function(response) {
            $scope.displayed = response.data.list;
            tableState.pagination.numberOfPages = Math.ceil(response.data.totalItems / limit);
        });
    };
    /** Get kategori */
    $scope.getKategori = function() {
        $scope.listKategori = [];
        Data.get(control_link + '/getkategori').then(function(response) {
            $scope.listKategori = response.data;
        });
    };
    /** create */
    $scope.create = function(form) {
        $scope.is_edit = true;
        $scope.is_view = false;
        $scope.is_create = true;
        $scope.form = {};
        $scope.form.publish = '1';
        $scope.getKategori();
        $scope.callMap(-7.871162, 112.526753);
    };
    /** update */
    $scope.update = function(form) {
        $scope.is_edit = true;
        $scope.is_view = false;
        $scope.is_create = false;
        $scope.getKategori();
        $scope.form = form;
        $scope.callMap(form.lattitude, form.longitude);
        $scope.getFoto(form.id);
    };
    /** view */
    $scope.view = function(form) {
        $scope.is_edit = true;
        $scope.is_view = true;
        $scope.is_create = false;
        $scope.getKategori();
        $scope.form = form;
        $scope.callMap(form.lattitude, form.longitude);
        $scope.getFoto(form.id);
    };
    /** save action */
    $scope.save = function(form) {
        form.lattitude = $scope.map.markers.coords.latitude;
        form.longitude = $scope.map.markers.coords.longitude;
        Data.post(control_link + '/save', form).then(function(result) {
            if (result.status_code == 200) {
                $scope.is_edit = false;
                $scope.callServer(tableStateRef);
                toaster.pop('success', "Berhasil", "Data berhasil tersimpan");
            } else {
                toaster.pop('error', "Terjadi Kesalahan", setErrorMessage(result.errors));
            }
        });
    };
    $scope.getFoto = function(id) {
        Data.get(control_link + '/getfoto', {
            id: id
        }).then(function(response) {
            $scope.gambar = response.data;
        });
    };
    /** cancel action */
    $scope.cancel = function() {
        if (!$scope.is_view) {
            $scope.callServer(tableStateRef);
        }
        $scope.is_edit = false;
        $scope.is_view = false;
    };
    /** delete data */
    $scope.delete = function(row) {
        if (confirm("Apa anda yakin akan MENGHAPUS PERMANENT item ini ?")) {
            Data.delete(control_link + '/delete/' + row.id).then(function(result) {
                $scope.displayed.splice($scope.displayed.indexOf(row), 1);
            });
        }
    };
    //============================GAMBAR===========================//
    var uploader = $scope.uploader = new FileUploader({
        url: Data.base + 'site/uploadgambar',
        formData: [],
        removeAfterUpload: true,
    });
    $scope.uploadGambar = function(form) {
        $scope.uploader.uploadAll();
    };
    uploader.filters.push({
        name: 'imageFilter',
        fn: function(item) {
            var type = '|' + item.type.slice(item.type.lastIndexOf('/') + 1) + '|';
            var x = '|jpg|png|jpeg|bmp|gif|'.indexOf(type) !== -1;
            if (!x) {
                toaster.pop('error', "Jenis gambar tidak sesuai");
            }
            return x;
        }
    });
    uploader.filters.push({
        name: 'sizeFilter',
        fn: function(item) {
            var xz = item.size < 2097152;
            if (!xz) {
                toaster.pop('error', "Ukuran gambar tidak boleh lebih dari 2 MB");
            }
            return xz;
        }
    });
    $scope.gambar = [];
    uploader.onSuccessItem = function(fileItem, response) {
        if (response.data.answer == 'File transfer completed') {
            $scope.gambar.unshift({
                name: response.data.name
            });
        }
    };
    uploader.onBeforeUploadItem = function(item) {
        item.formData.push({
            id: $scope.form.id,
        });
    };
    $scope.removeFoto = function(paramindex, namaFoto) {
        var comArr = eval($scope.gambar);
        Data.post(control_link + '/removegambar', {
            nama: namaFoto
        }).then(function(data) {
            $scope.gambar.splice(paramindex, 1);
        });
    };
    // END GAMBAR
})