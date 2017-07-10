function createHomepageGoogleMap(_url, _latitude, _longitude) {
    var uluru = {
        lat: _latitude,
        lng: _longitude
    };
    var map = new google.maps.Map(document.getElementById('map'), {
        scrollwheel: false,
        zoom: 17,
        center: uluru,
        mapTypeControl: true,
        mapTypeControlOptions: {
            style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
            position: google.maps.ControlPosition.BOTTOM_CENTER
        },
        mapTypeId: 'roadmap',
        // styles: mapStyles
    });
    var infoWindow = new google.maps.InfoWindow;
    downloadUrl(_url + '/getloc.php', function(data) {
        var xml = data.responseXML;
        var markers = xml.documentElement.getElementsByTagName('marker');
        var newMarkers = [];
        var i = 0;
        Array.prototype.forEach.call(markers, function(markerElem) {
            var name = markerElem.getAttribute('name');
            var iconUrl = markerElem.getAttribute('icon');
            var address = markerElem.getAttribute('address');
            var type = markerElem.getAttribute('type');
            var point = new google.maps.LatLng(parseFloat(markerElem.getAttribute('lat')), parseFloat(markerElem.getAttribute('lng')));
            var infowincontent = document.createElement('div');
            var pictureLabel = document.createElement("img");
            pictureLabel.src = _url + '/public/images/noimage.jpg';
            infowincontent.innerHTML = '<div class="infobox-inner">' + '<a href="">' + '<div class="infobox-image" style="position: relative; max-width: 250px;">' + '<img src="' + _url + '/public/images/noimage.jpg" style="margin-top: -20%;">' + '<div><span class="infobox-price">Jatimpark 1</span></div>' + '</div>' + '</a>' + '<div class="infobox-description">' + '<div class="infobox-title"><a href="">Jl kaliurang</a></div>' + '<div class="infobox-location" style="margin-top:10px;">' + '<a class="btn btn-primary" href="#" style="width: 48%"><span class="fa fa-search"></span> Detail</a>' + '<a class="btn btn-primary" href="#" style="width: 50%; float: right;"><span class="fa fa-eye"></span> Lihat Rute</a>' + '</div>' + '</div>' + '</div>';
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
            var imageIcon = new google.maps.MarkerImage(iconUrl, new google.maps.Size(71, 71), new google.maps.Point(0, 0), new google.maps.Point(17, 34), new google.maps.Size(42, 42));
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
}