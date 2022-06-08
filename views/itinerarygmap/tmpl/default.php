<!DOCTYPE html>
<?php
defined('_JEXEC') or die('Restricted access');
?>
<html>
	<head>	
		<title>Geolocation</title>
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
		<meta charset="utf-8">
                <link rel="stylesheet" href="https://unpkg.com/leaflet@1.2.0/dist/leaflet.css"   integrity="sha512-M2wvCLH6DSRazYeZRIm1JnYyh22purTM+FDB5CsyxtQJYeKq83arPe5wgbNmcFXGqiSH2XR8dT/fJISVA1r/zQ==" crossorigin=""/>
                <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
		<link rel="stylesheet" href="https://rawgit.com/k4r573n/leaflet-control-osm-geocoder/master/Control.OSMGeocoder.css" />
                <style>
			html, body {
				height: 100%;
				margin: 0;
				padding: 0;
			}

                          #mapid {
                            position: absolute;
                            left: 0;
                            right: 0;
                            bottom: 0;
                            top: 0;
                            height: 100%;
                            width: 100%;
                            float: left;
                            z-index: 0;
                          }
			
			#floating-panel {
				position: absolute;
				top: 10px;
				left: 25%;
                                width: 50%;
				z-index: 5;
				background-color: #a61f21;
				
			}

		</style>		
	</head>

	<body>
		<div class="container p-3 " id="floating-panel">
                    <div class="row m-1">
                        <p class="text-white text-justify"><?php echo JText::_('COM_ITINERARY_CONDITIONS_WARNING'); ?></p>
                    </div>
                    <div class="row m-1">
                        <!--<div class="col-lg-6">
                          <div class="input-group">
                            <span class="input-group-btn">
                              <button id="submit" class="btn btn-secondary" type="button">Go!</button>
                            </span>
                            <input id="address" type="text" class="form-control" placeholder="<?php echo JText::_('COM_ITINERARY_ADDRESS_LABEL'); ?>" aria-label="Search for...">
                          </div>
                        </div>-->
                        <input id="myWindow" type="button" class="btn" value="<?php echo JText::_('COM_ITINERARY_SELECT_COORDINATES_LABEL'); ?>" onclick="exportCoordinates()" >
                    </div>	
                    <input id="latitude" type="hidden"  placeholder="latitude" readonly="true"/>
                    <input id="longitude" type="hidden" placeholder="longitude" readonly="true"/>	
		</div>
			
		<div id="mapid"></div>
                <script src="https://unpkg.com/leaflet@1.2.0/dist/leaflet.js"   integrity="sha512-lInM/apFSqyy1o6s89K4iQUKg6ppXEgsVxT35HbzUupEVRh2Eu9Wdl4tHj7dZO0s1uvplcYGmt3498TtHq+log=="crossorigin=""></script>
                <script src = "https://unpkg.com/pouchdb@^5.2.0/dist/pouchdb.js"></script>
                <script src="https://rawgit.com/k4r573n/leaflet-control-osm-geocoder/master/Control.OSMGeocoder.js"></script>
                
                
			<script type="text/javascript">
				// Note: This example requires that you consent to location sharing when
				// prompted by your browser. If you see the error "The Geolocation service
				// failed.", it means you probably did not give permission for the browser to
				// locate you.

				function exportCoordinates(){
					
					if (window.opener != null && !window.opener.closed)
					{
					window.opener.document.getElementById("jform_latitude").value = document.getElementById("latitude").value;
					window.opener.document.getElementById("jform_longitude").value = document.getElementById("longitude").value;
					window.opener.document.getElementById("jform_latitude_btn").value = "<?php echo JText::_('COM_ITINERARY_FORM_COORDINATES_SELECTED'); ?>";
					window.opener.document.getElementById("jform_latitude_btn").toggleClass("modal_jform_latitude btn btn-danger",false);
					window.opener.document.getElementById("jform_latitude_btn").toggleClass("modal_jform_latitude btn btn-success",true);
					}
					window.close();
					
				}
                                
                                
                                var mymap = L.map('mapid');
                                var osmGeocoder = new L.Control.OSMGeocoder();

                                mymap.addControl(osmGeocoder);
                                
                                var myLayer = L.tileLayer('https://stamen-tiles-{s}.a.ssl.fastly.net/terrain/{z}/{x}/{y}.{ext}', {
                                    attribution: 'Map tiles by <a href=\"http://stamen.com\">Stamen Design</a>, <a href=\"http://creativecommons.org/licenses/by/3.0\">CC BY 3.0</a> &mdash; Map data &copy; <a href=\"http://www.openstreetmap.org/copyright\">OpenStreetMap</a>',
                                    subdomains: 'abcd',
                                    //bounds: myBounds,
                                    useCache: true,
                                    minZoom: 0,
                                    maxZoom: 18,
                                    ext: 'png',
                                }).addTo(mymap);
                                
                                function getParamValue(param)
                                {
                                        var u = document.location.href;
                                        var reg = new RegExp('(\\?|&|^)'+param+'=(.*?)(&|$)');
                                        matches = u.match(reg);

                                        if(typeof(matches) !== 'undefined' && matches != null)
                                        {
                                                return matches[2] != undefined ? decodeURIComponent(matches[2]).replace(/\+/g,' ') : '';
                                        }
                                        else
                                        {
                                                return '';
                                        }
                                }
                                
                                if(getParamValue('lat') !== '' && getParamValue('lng') !== '')
                                {
                                        var position_msg = {
                                                lat: parseFloat(getParamValue('lat')),
                                                lng: parseFloat(getParamValue('lng'))
                                        };

                                        document.getElementById('latitude').value = position_msg.lat;
                                        document.getElementById('longitude').value =  position_msg.lng;
                                        
                                        mymap.setView([position_msg.lat, position_msg.lng], 15);
                                }
                                else
                                {
                                    mymap.locate({setView : true}).on('locationfound', function(event){
                                            mymap.setView(event.latlng ,14);
                                       }).on('locationerror', function(event){
                                            vmymap.setView([48.905247, 2.231889], 13);
                                            alert("Nous n'avons pas pu vous localiser");
                                       });
                                }
                                
                                var positionMarker;
                                
                                if(position_msg)
                                {
                                    positionMarker = L.marker([position_msg.lat, position_msg.lng],{ draggable: true}).addTo(mymap);
                                    
                                    var html = "<p>Position choisie</p>";
                                    
                                    positionMarker.bindPopup(html).openPopup();
                                }
                                        
                                /*document.getElementById('submit').addEventListener('click', function() {
                                        geocodeAddress(geocoder);
                                });*/
                                
                                /*function geocodeAddress(geocoder)
                                {
                                    var address = document.getElementById('address').value;

                                    geocoder.geocode({'address': address});
                                }*/

                                mymap.on('click', placeMarker);
                                positionMarker.on('move',updateCoordinates)
                                
                                function placeMarker(e)
                                {
                                    var location = e.latlng;
                                    
                                    if(typeof(positionMarker) ==='undefined')
                                    { //on vérifie si le marqueur existe
                                        positionMarker = L.marker(location,{ draggable: true}).addTo(mymap);
                                        document.getElementById('latitude').value = location.lat;
                                        document.getElementById('longitude').value = location.lng;
                                    }
                                    else
                                    {
                                        positionMarker.setLatLng(location);
                                    }

                                    document.getElementById('myWindow').value = "<?php echo JText::_("COM_ITINERARY_COORDINATES_SELECTED_LABEL"); ?>";

                                    document.getElementById("floating-panel").backgroundColor = "#ADFF2F";
                                }
                                
                                function updateCoordinates(e){
                                    var location = e.latlng;
                                    document.getElementById('latitude').value = location.lat;
                                    document.getElementById('longitude').value = location.lng;
                                }
                                
                                
                                
                                
                                
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!     				
                                //var map;
				//var marker;
                                
				/*function initMap() {

				
					function geocodeAddress(geocoder, resultsMap)
                                        {
					
			 			var address = document.getElementById('address').value;
						
						geocoder.geocode({'address': address}, function(results, status) {
							if (status === google.maps.GeocoderStatus.OK) {
								
								resultsMap.setCenter(results[0].geometry.location);
								
								placeMarker(results[0].geometry.location, resultsMap);
								
							} else {
								alert("<?php ""; echo JText::_('COM_ITINERARY_FORM_ERROR_GEOLOC_FAILED'); ?>");
							}
						});
					}
				
				
					
                                        
                                    }*/
			</script>
                       
	</body>
</html>
