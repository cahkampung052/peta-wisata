$(document).ready(function($) {
    var _latitude = -7.871162;
    var _longitude = 112.526753;
    var _url = '{{baseUrl}}';
    var keyword = kategori = '';
    // function createHomepageGoogleMap(_url, _latitude, _longitude, keyword, kategori) {
    var mapCenter = {
        lat: _latitude,
        lng: _longitude
    };
    /** Inialisasi Map */
    var map = new google.maps.Map(document.getElementById('map'), {
        scrollwheel: false,
        zoom: 14,
        center: mapCenter,
        mapTypeControl: true,
        mapTypeControlOptions: {
            style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
            position: google.maps.ControlPosition.BOTTOM_CENTER
        },
        mapTypeId: 'roadmap',
    });
    /** End Inialisasi Map */
    /** Setting Marker */
    var infoWindow = new google.maps.InfoWindow;
    downloadUrl(_url + '/getloc.php?keyword=' + keyword + '&kategori=' + kategori, function(data) {
        var xml = data.responseXML;
        var markers = xml.documentElement.getElementsByTagName('marker');
        var newMarkers = [];
        var i = 0;
        Array.prototype.forEach.call(markers, function(markerElem) {
            var infowincontent = document.createElement('div');
            var pictureLabel = document.createElement("img");
            pictureLabel.src = _url + '/public/images/noimage.jpg';
            infowincontent.innerHTML = '<div class="infobox-inner">' + '<a href="">' + '<div class="infobox-image" style="position: relative; max-width: 250px;">' + '<img src="' + _url + '/' + markerElem.getAttribute('img') + '" style="margin-top: -20%;">' + '<div><span class="infobox-price">' + markerElem.getAttribute('name') + '</span></div>' + '</div>' + '</a>' + '<div class="infobox-description">' + '<div class="infobox-location" style="margin-top:10px;">' + '<a class="btn btn-primary" href="' + _url + '/wisata/' + markerElem.getAttribute('id') + '/' + markerElem.getAttribute('alias') + '.html" style="width: 48%">' + '<span class="fa fa-search"></span> Detail ' + '</a>' + '<a class="btn btn-primary" href="#" style="width: 50%; float: right;">' + '<span class="fa fa-eye"></span> Lihat Rute' + '</a>' + '</div>' + '</div>' + '</div>';
            infoboxOptions = {
                content: infowincontent,
                disableAutoPan: false,
                pixelOffset: new google.maps.Size(-100, 0),
                zIndex: null,
                alignBottom: true,
                boxClass: "infobox-wrapper",
                enableEventPropagation: true,
                closeBoxMargin: "0px 0px -8px 0px",
                closeBoxURL: _url + "/public/images/map/close-btn.png",
                infoBoxClearance: new google.maps.Size(1, 1)
            };
            /** Set size icon */
            var imageIcon = new google.maps.MarkerImage(markerElem.getAttribute('icon'), new google.maps.Size(71, 71), new google.maps.Point(0, 0), new google.maps.Point(17, 34), new google.maps.Size(42, 42));
            /** Set marker */
            var marker = new google.maps.Marker({
                title: name,
                position: new google.maps.LatLng(markerElem.getAttribute('lat'), markerElem.getAttribute('lng')),
                map: map,
                icon: imageIcon,
            });
            newMarkers.push(marker);
            newMarkers[i].infobox = new InfoBox(infoboxOptions);
            google.maps.event.addListener(marker, 'click', (function(marker, i) {
                return function() {
                    for (h = 0; h < newMarkers.length; h++) {
                        newMarkers[h].infobox.close();
                    }
                    newMarkers[i].infobox.open(map, this);
                }
            })(marker, i));
            i++;
        });
        /** End Marker */
    });

    function downloadUrl(url, callback) {
        var request = window.ActiveXObject ? new ActiveXObject('Microsoft.XMLHTTP') : new XMLHttpRequest;
        request.onreadystatechange = function() {
            if (request.readyState == 4) {
                request.onreadystatechange = doNothing;
                callback(request, request.status);
            }
        };
        request.open('GET', url, true);
        request.send(null);
    }

    function doNothing() {}
    // }
    /** Get Current Location */
    $("#getLoc").click(function() {
        navigator.geolocation.getCurrentPosition(function(position) {
            window.lat = position.coords.latitude;
            window.lng = position.coords.longitude;
            console.log(position);
        });
    });
    /** End get current location */
    // $(document).ready(function($) {
    //     createHomepageGoogleMap("{{baseUrl}}", -7.871162, 112.526753, $("#keyword").val(), $("#kategori").val());
});

$("#cari").click(function() {
    createHomepageGoogleMap("{{baseUrl}}", -7.871162, 112.526753, $("#keyword").val(), $("#kategori").val());
});