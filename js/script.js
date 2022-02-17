jQuery(document).ready(function($){

    //Setting up
    var listingmode = 'tag'; //anything different will show markers listing

    mapboxgl.accessToken = vars_php.token;

    var map = new mapboxgl.Map({
        container: 'map',
        style: 'mapbox://styles/mapbox/light-v10',
        center: [-96, 37.8],
        zoom: 3
    });

    /*
     * Method to allow users to create the markers in the map
     * @params: none
     * @return: none
     */
    map.on('click', function(e) {

        var coordinates = e.lngLat;
        var description = "<a id='creating_marker' class='mapboxgl-CTpopup' href='javascript:;'>Click here to create mark!<a/>";

        if(vars_php.is_user){
            new mapboxgl.Popup()
                .setLngLat(coordinates)
                .setHTML(description)
                .addTo(map);
        }

        //coordinates
        jQuery("#lat").val(e.lngLat.lat);
        jQuery("#long").val(e.lngLat.lng);
    });

    //Showing modal
    jQuery("#creating_marker").live('click', function(e) {
        jQuery('#myModal').modal("show");
    });

    //Hidding if click marker
    jQuery(".marker").live('click', function(e) {
        //jQuery('.mapboxgl-CTpopup').hide();
        jQuery('.mapboxgl-CTpopup').html("<a id='creating_marker' class='mapboxgl-CTpopup' href='javascript:;'>One more here!<a/>");
    });

    map.on('load', function(e) {
        //If wants listing or markers
        if (listingmode != 'tag'){
            buildLocationList(geojson);
        }
        map.resize();
    });

    /*
     * Method to show the list of tags in the side bar, it is optional
     * @params: none
     * @return: none
     */
    function buildLocationList(data) {
        // Iterate through the list of stores
        for (i = 0; i < data.features.length; i++) {
            var currentFeature = data.features[i];
            // Shorten data.feature.properties to `prop` so we're not
            // writing this long form over and over again.
            var prop = currentFeature.properties;
            // Select the listing container in the HTML and append a div
            // with the class 'item' for each store
            var listings = document.getElementById('listings');
            var listing = listings.appendChild(document.createElement('div'));
            listing.className = 'item list-group-item list-group-item-action';
            listing.id = 'listing-' + i;

            // Create a new link with the class 'title' for each store
            // and fill it with the store address
            var link = listing.appendChild(document.createElement('a'));
            link.href = '#';
            link.className = 'title';
            link.dataPosition = i;
            link.innerHTML = prop.title;

            var details = listing.appendChild(document.createElement('div'));
        }
    }

    // Select 2
    jQuery('#tags').select2({
        placeholder: "Select tag(s)",
    });

    /*
     * Method to show the list of tags using the RES API from WP
     * @params: none
     * @return: none
     */
    function tags_list(){
        const headers = new Headers({
            'Content-Type': 'application/json',
            'X-WP-Nonce': ajax_var.nonce
        });

        fetch(ajax_var.taglist_url, {
            method: 'get',
            headers: headers,
            credentials: 'same-origin'
        })
            .then(response => {
                return response.ok ? response.json() : 'Not Found...';
            }).then(json_response => {
            let html;
            let listing;

                html = '';
                json_response.forEach((element, index, data) => {
                    html += '<option value="' + element.name + '">' + element.name + '</option>';
                });

                listing = '';
                json_response.forEach((element2, index2, data2) => {
                    listing += '<div class="item list-group-item list-group-item-action" id="listing-0"><a href="#" class="title">' + element2.name + ' ('+ element2.id +') </a><div></div></div>';
                });

            jQuery("#tags").html(html);
            if (listingmode == 'tag'){
                //Tag listing
                jQuery("#listings").html(listing);
            }
        });
    }

    //running tags list
    tags_list();

    //getting markers
    marker_list();

    /*
     * Method to show the list of markers and fill up the markers in the map using the RES API from WP
     * @params: none
     * @return: object
     */
    function marker_list(){
        const headers = new Headers({
            'Content-Type': 'application/json',
            'X-WP-Nonce': ajax_var.nonce
        });

        fetch(ajax_var.marklist_url, {
            method: 'get',
            headers: headers,
            credentials: 'same-origin'
        })
            .then(response => {
                return response.ok ? response.json() : 'Not Found...';
            }).then(json_response => {

            var markers = [];
            json_response.forEach((element, index, data) => {

                var marker = {
                    type: 'Feature',
                    geometry: {
                        type: 'Point',
                        coordinates: [element.long, element.lat]
                    },
                    properties: {
                        title: element.marker_title,
                        CTdate: element.CTdate,
                        CTtags: element.CTtags,
                        author: element.author,
                    }
                };

                markers.push(marker);

                var geojson = {
                    type: 'FeatureCollection',
                    features: markers
                };
                // add markers to map
                geojson.features.forEach(function(marker) {

                    // create a HTML element for each feature
                    var el = document.createElement('div');
                    if (marker.properties.author == vars_php.current_user){
                        el.style.backgroundColor = "green";
                    }
                    el.className = 'marker';

                    // make a marker for each feature and add to the map
                    new mapboxgl.Marker(el)
                        .setLngLat(marker.geometry.coordinates)
                        .setPopup(new mapboxgl.Popup({ offset: 25 }) // add popups
                            .setHTML('<h5>' + marker.properties.title + '</h5><p> ' + marker.properties.title + ' </p><p> ' + marker.properties.CTdate + ' </p><p> Tags: ' + marker.properties.CTtags + ' </p>'))
                        .addTo(map);
                });

            });

            return markers;
        });

    }

    /*
     * Method to search using the keypress and hidding and showing the correct list on the sidebard
     * @params: none
     * @return: none
     */
    jQuery("#searchname").live('keypress', function(e) {
        if (jQuery(this).val().length > 2){
            marker_find();
            jQuery("#searchlistings").show(500);
            jQuery("#listings").hide(500);
        }else{
            jQuery("#searchlistings").hide(500);
            jQuery("#listings").show(500);
        }
    });

    /*
     * Method to search the markers using the RES API from wp
     * @params: none
     * @return: none
     */
    function marker_find(){
        const headers = new Headers({
            'Content-Type': 'application/json',
            'X-WP-Nonce': ajax_var.nonce
        });

        var data = {name: jQuery('#searchname').val()};

        fetch(ajax_var.markfind_url, {
            method: 'post',
            body: JSON.stringify(data),
            headers: headers,
            credentials: 'same-origin'
        })
            .then(response => {
                return response.ok ? response.json() : 'Not Found...';
            }).then(json_response => {

            var html = '';
            json_response.forEach((element, index, data) => {
                html += '<div class="item list-group-item list-group-item-action" id="listing-0"><a href="javascript:;" class="point_locator" data-long="'+element.long+'" data-lat="'+element.lat+'" data-title="' + element.marker_title + '" class="title">' + element.marker_title + '</a></div>'
            });

            jQuery("#searchlistings").html(html);
        });

    }

    /*
     * Method to create tags using the RES API from wp
     * @params: none
     * @return: none
     */
    $("#tag-add").on("click", function ($e) {
        $e.preventDefault();

        const headers = new Headers({
            'Content-Type': 'application/json',
            'X-WP-Nonce': ajax_var.nonce
        });

        //Creating data
        var data = {tagname: jQuery('#tagname').val()};

            fetch(ajax_var.tag_url, {
                method: 'post',
                body: JSON.stringify(data),
                headers: headers,
                credentials: 'same-origin'
            })
            .then(response => {
                return response.ok ? response.json() : 'Not Found...';
            }).then(json_response => {

                //create
                if(json_response){
                    jQuery("#tagname").val("");
                    //updating tags list
                    tags_list();
                }
        });
    });

    /*
     * Method to add markers using the RES API from wp
     * @params: none
     * @return: none
     */
    $("#marker-add").on("click", function ($e) {
        $e.preventDefault();

        const headers = new Headers({
            'Content-Type': 'application/json',
            'X-WP-Nonce': ajax_var.nonce
        });

        //Creating data
        var data = {
            markername: jQuery('#markername').val(),
            lat:    jQuery('#lat').val(),
            long:   jQuery('#long').val(),
            tags:   jQuery('#tags').val(),

        };

        fetch(ajax_var.marker_url, {
            method: 'post',
            body: JSON.stringify(data),
            headers: headers,
            credentials: 'same-origin'
        })
            .then(response => {
                return response.ok ? response.json() : 'Not Found...';
            }).then(json_response => {

            //create
            if(json_response){
                //do something
                if (json_response){
                    jQuery("#alertjs").html("Marker created successful!");
                    jQuery("#alertjs").show();
                }

                marker_list();
            }
        });
    });


    /*
     * Method to locate the marker found and apply highlight 1 sec
     * @params: none
     * @return: none
     */
    jQuery(".point_locator").live('click', function(e) {
        var coordinates = [$(this).attr("data-long"), $(this).attr("data-lat")]

        // create a HTML element for each feature
        var el = document.createElement('div');

        el.style.backgroundColor = "red";
        function backgroundCT(){
            el.style.backgroundColor = "";
        }
        setTimeout(backgroundCT, 1000);
        el.className = 'marker';

        // make a marker for each feature and add to the map
        new mapboxgl.Marker(el)
            .setLngLat(coordinates)
            .setPopup(new mapboxgl.Popup({ offset: 25 }) // add popups
                .setHTML('<h5>' + $(this).attr("data-title") + '</h5>'))
            .addTo(map);
    });

});