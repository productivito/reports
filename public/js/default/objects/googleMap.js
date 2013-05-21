/* The object */
var googleMap = new Object();

googleMap = {
    map : '',
    directions : '',
    routeBoxer : '',
    distance : '',

    mapsLoaded: function() {


        /* Get the map*/
        googleMap.map = new google.maps.Map2(document.getElementById("googleMapModule_map"));
        /* Get the position to view */
        googleMap.map.setCenter(new google.maps.LatLng($('#googleMapModule_map').attr('lng'), $('#googleMapModule_map').attr('lat')), 15);
        /* Set an marker */
        var marker = new GMarker(googleMap.map.getCenter());
        googleMap.map.addOverlay(marker);

        //googleMap.map.setUIToDefault();

        googleMap.directions = new GDirections(googleMap.map);
        googleMap.routeBoxer = new RouteBoxer();

        GEvent.addListener(googleMap.directions, 'load', function() {
            // Generate the boxes along the polyline for this route
            var polyline = googleMap.directions.getPolyline();
            var boxes = googleMap.routeBoxer.box(polyline, googleMap.distance);
            googleMap.drawBoxes(boxes);
        });

        GEvent.addListener(googleMap.directions, 'error', function() {
            alert("Directions request failed: " + googleMap.directions.getStatus().code);
        });
    },

    route: function() {
        // Clear any previous routes or boxes from the map
        googleMap.map.clearOverlays();

        // Convert the distance to box around the route from miles to km
        googleMap.distance = parseFloat($('#googleMapModule_map').attr('radius')) * 1.609344;

        // Make the directions request
        var from = $('#route_from').val();
        from.replace(" ", "+");
        from.replace(",", "+");
        from.replace(".", "+");
        var to = $('#googleMapModule_map').attr('addr');
        to.replace(" ", "+");
        to.replace(",", "+");
        to.replace(".", "+");
        googleMap.directions.load("from:" + from + " to:" + to);
    },

    drawBoxes :function(boxes) {
        if (boxes != null) {
            for (var i = 0; i < boxes.length; i++) {
                var vertices = [
                boxes[i].getSouthWest(),
                new GLatLng(boxes[i].getSouthWest().lat(),
                    boxes[i].getNorthEast().lng()),
                boxes[i].getNorthEast(),
                new GLatLng(boxes[i].getNorthEast().lat(),
                    boxes[i].getSouthWest().lng()),
                boxes[i].getSouthWest()
                ];
                var polygon = new GPolyline(vertices, '#000000', 1, 1.0);
                googleMap.map.addOverlay(polygon);
            }
        }
    },

    loadMaps: function(){
        google.load("maps", "2", {
            "callback" : googleMap.initRoute
        });
    },
    initialize: function() {
        var script = document.createElement("script");



        script.src = "https://www.google.com/jsapi?key=" + $('.googleMapModule_container').attr('apikey') + "&callback=googleMap.loadMaps";
        script.type = "text/javascript";
        document.getElementsByTagName("head")[0].appendChild(script);

    },

    initRoute: function(){
        var script = document.createElement("script");
        script.src = "http://gmaps-utility-library-dev.googlecode.com/svn/trunk/routeboxer/src/RouteBoxer.js";
        script.type = "text/javascript";
        document.getElementsByTagName("head")[0].appendChild(script);

        googleMap.waitForObject();

    },

    waitForObject: function(){
        if(typeof RouteBoxer == 'function') {
            googleMap.mapsLoaded();

        }
        else {
            // method of choice for loading script
            window.setTimeout(googleMap.waitForObject,100);
            
        }


    }


}

  /*  var map = null;
    var directions = null;
    var routeBoxer = null;
    var distance = null; // km

    function initialize() {
      if (GBrowserIsCompatible()) {
        // Default the map view to the continental U.S.
        map = new GMap2(document.getElementById("map"));
        map.setCenter(new GLatLng(37.09024, -95.712891), 4);
        map.setUIToDefault();

        directions = new GDirections(map);
        routeBoxer = new RouteBoxer();

        GEvent.addListener(directions, 'load', function() {
          // Generate the boxes along the polyline for this route
          var polyline = directions.getPolyline();
          var boxes = routeBoxer.box(polyline, distance);
          drawBoxes(boxes);
        });

        GEvent.addListener(directions, 'error', function() {
          alert("Directions request failed: " + directions.getStatus().code);
        });
      }
    }

    function route() {
      // Clear any previous routes or boxes from the map
      map.clearOverlays();

      // Convert the distance to box around the route from miles to km
      distance = parseFloat(document.getElementById("distance").value)
                  * 1.609344;

      // Make the directions request
      var from = document.getElementById("from").value;
      var to = document.getElementById("to").value;
      directions.load("from:" + from + " to:" + to);
    }

    // Draw an array of boxes as Polylines on the map
    function drawBoxes(boxes) {
      if (boxes != null) {
        for (var i = 0; i < boxes.length; i++) {
          var vertices = [
            boxes[i].getSouthWest(),
            new GLatLng(boxes[i].getSouthWest().lat(),
                        boxes[i].getNorthEast().lng()),
            boxes[i].getNorthEast(),
            new GLatLng(boxes[i].getNorthEast().lat(),
                        boxes[i].getSouthWest().lng()),
            boxes[i].getSouthWest()
          ];
          var polygon = new GPolyline(vertices, '#000000', 1, 1.0);
          map.addOverlay(polygon);
        }
      }
    }
*/