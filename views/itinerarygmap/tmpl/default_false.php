<!DOCTYPE html>
<?php
defined('_JEXEC') or die('Restricted access');
?>
<html>
	<head>	
		<title>Geolocation</title>
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
		<meta charset="utf-8">
                
                <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
		<style>
			html, body {
				height: 100%;
				margin: 0;
				padding: 0;
			}
			#map {
				height: 100%;
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
                                <!--//text-align: center;
				//font-family: 'Roboto','sans-serif';
				//line-height: 3em;
                                padding: 5px;
				border: 1px solid #999;
				
				padding-left: 10px;
                                border-radius: 5px;
                                #bf383a-->
	<body>
		<div class="container p-3 " id="floating-panel">
                    <div class="row m-1">
                        <p class="text-white text-justify"><?php echo JText::_('COM_ITINERARY_CONDITIONS_WARNING'); ?></p>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                          <div class="input-group">
                            <span class="input-group-btn">
                              <button id="submit" class="btn btn-secondary" type="button">Go!</button>
                            </span>
                            <input id="address" type="text" class="form-control" placeholder="<?php echo JText::_('COM_ITINERARY_ADDRESS_LABEL'); ?>" aria-label="Search for...">
                          </div>
                        </div>
                        <input id="myWindow" type="button" class="btn" value="<?php echo JText::_('COM_ITINERARY_SELECT_COORDINATES_LABEL'); ?>" onclick="exportCoordinates()" >
                    </div>	
                    <input id="latitude" type="hidden"  placeholder="latitude" readonly="true"/>
                    <input id="longitude" type="hidden" placeholder="longitude" readonly="true"/>	
		</div>
			
		<div id="map"></div>
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
				
                                var map;
				var marker;
				
				function initMap() {

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
					
					var mapcenter = {lat: 48.865551, lng: 2.321312};
					
					if(getParamValue('lat') != '' && getParamValue('lng') != '')
					{
						var position_msg = {
							lat: parseFloat(getParamValue('lat')),
							lng: parseFloat(getParamValue('lng'))
						};
						
						document.getElementById('latitude').value = position_msg.lat;
						document.getElementById('longitude').value =  position_msg.lng;
						
						mapcenter = position_msg;
					}
					
					map = new google.maps.Map(document.getElementById('map'),
						{
							center: mapcenter,
							zoom: 18,
							signInControl: false
						});

					
					if(position_msg)
					{
						marker = new google.maps.Marker({ //on créé le marqueur
								position: position_msg,
								map: map,
								title: 'Position choisie'
							});
					}
						
					
					var infoWindow = new google.maps.InfoWindow({map: map});

					var geocoder = new google.maps.Geocoder();

					document.getElementById('submit').addEventListener('click', function() {
						geocodeAddress(geocoder, map);
					});
					
					google.maps.event.addListener(map, 'click', function(event) {                              
						placeMarker(event.latLng, map);
					});

					// Try HTML5 geolocation.
					if (navigator.geolocation)
					{	
						navigator.geolocation.getCurrentPosition(
							function(position) {
								var pos = {
									lat: position.coords.latitude,
									lng: position.coords.longitude
								};

								infoWindow.setPosition(pos);
								infoWindow.setContent('Vous êtes par ici');
								map.setCenter(pos);
							}
						
							, function() {
								handleLocationError(true, infoWindow, map.getCenter());
							}
						);
					} 
					else 
					{
					// Browser doesn't support Geolocation
					handleLocationError(false, infoWindow, map.getCenter());
					}

					function placeMarker(location, maptype)
                                        {
						
                                                
						if(marker){ //on vérifie si le marqueur existe
							marker.setPosition(location); //on change sa position
						}
						else
						{
							marker = new google.maps.Marker({ //on créé le marqueur
								position: location,
								map: maptype,
								title: 'Position choisie'
							});
                                                        
                                                        
						}
                                                
                                                document.getElementById('myWindow').value = "<?php echo JText::_("COM_ITINERARY_COORDINATES_SELECTED_LABEL"); ?>";
                                                
                                                document.getElementById("floating-panel").backgroundColor = "#ADFF2F";
                                                
						latitude.value=location.lat();
						longitude.value=location.lng();
					}
				
				
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
				
				
					function handleLocationError(browserHasGeolocation, infoWindow, pos)
                                        {  
                                            /*if(browserHasGeolocation===true)
                                            {
                                                alert("<?php ""; //echo JText::_('COM_ITINERARY_FORM_ERROR_GEOLOC_FAILED'); ?>");
                                            }
                                            else
                                            {
                                                alert("<?php ""; //echo JText::_("COM_ITINERARY_FORM_ERROR_GEOLOC_NOT_ACCEPTED"); ?>");
                                            } */ 
					}
                                        
                                    }
			</script>
                        <!--<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAwdambm6vx7WH5_xZkVnGpgCrtMaH7kTs&signed_in=true&callback=initMap" async defer></script>	
                        <script src="https://maps.googleapis.com/maps/api/js?callback=initMap&signed_in=true&key=AIzaSyAwdambm6vx7WH5_xZkVnGpgCrtMaH7kTs" async defer></script>-->
                        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAwdambm6vx7WH5_xZkVnGpgCrtMaH7kTs&callback=initMap"
    async defer>></script>
	</body>
</html>
