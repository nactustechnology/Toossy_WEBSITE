
<?php
defined('_JEXEC') or die('Restricted access');

$msgForMarker = $this->items;
?>
        <link href="/media/jui/css/sortablelist.css" rel="stylesheet" />
	<link href="/media/gantry5/assets/css/font-awesome.min.css" rel="stylesheet" />
	<link href="/media/gantry5/engines/nucleus/css-compiled/nucleus.css" rel="stylesheet" />
	<link href="/templates/g5_hydrogen/custom/css-compiled/hydrogen_9.css?58ada572" rel="stylesheet" />
	<link href="/media/gantry5/assets/css/bootstrap-gantry.css" rel="stylesheet" />
	<link href="/media/gantry5/engines/nucleus/css-compiled/joomla.css" rel="stylesheet" />
	<link href="/media/jui/css/icomoon.css" rel="stylesheet" />
	<link href="/templates/g5_hydrogen/custom/css-compiled/hydrogen-joomla_9.css?58ada572" rel="stylesheet" />
	<link href="/templates/g5_hydrogen/custom/css-compiled/custom_9.css?58adb123" rel="stylesheet" />
		<style>
			html, body {
				height: 100%;
				margin: 0;
				padding: 0;
			}
		</style>		

		<div id="floating-panel">
				<label><?php echo JText::_('COM_ITINERARY_MAP_PANEL_LABEL'); ?></label>
				<div class="form-inline text-center">
					<button class="btn btn-default" type="submit" onclick="firstMsg()"><<</button>
					<button class="btn btn-default" type="submit" onclick="previousMsg()"><</button>
					<label><?php echo JText::_('COM_ITINERARY_FIELD_NUM_MSG_LABEL'); ?></label><input class="text-center" type="text" id="numberMsg" disabled="true" readonly="true" value="1" size="1">
					<button class="btn btn-default" type="submit" onclick="nextMsg()">></button>
					<button class="btn btn-default" type="submit" onclick="lastMsg()">>></button>
				</div>
		</div>
		<div id="map"></div>
			<script type="text/javascript">

				function RefreshParent(){
					
					if (window.opener != null && !window.opener.closed)
					{
						window.opener.location.reload();
					}
					window.close();
				}
				
				var marker;
				var markersTable = [];
				var stocker = {};
				<?php
				if(!empty($msgForMarker))
				{
					$clef=0;
				
					foreach( $msgForMarker as $item)
					{
						$item->titre=addslashes(htmlspecialchars_decode($item->titre,ENT_QUOTES));
						$item->texte=addslashes(htmlspecialchars_decode(str_replace("\r\n","<br/>",$item->texte),ENT_QUOTES));
						$item->titre_illustrations=addslashes(htmlspecialchars_decode(str_replace("\r\n","<br/>",$item->titre_illustrations),ENT_QUOTES));
						
						echo 	'stocker={
                                                            latitude:'.$item->latitude.',
                                                            longitude:'.$item->longitude.',
                                                            titre:"'.$item->titre.'",
                                                            texte:"'.$item->texte.'",
                                                            titre_image:"'.$item->titre_illustrations.'",
                                                            image:"'.$this->path.$item->illustrations.'",
                                                            activation_planificateur:"'.$item->activation_planificateur.'"
                                                    };
						markersTable.push(stocker);';
					
						$clef++;
					}
				}
				
				?>
                                var map;
                                
				function initMap() 
				{
					var mapcenter = {lat: 50.117286, lng: 9.247769};
					
                                        var mapProp ={
                                            center: mapcenter,
                                            zoom: 4,
                                            signInControl: false,
                                        };
                                        
					map = new google.maps.Map(document.getElementById('map'),mapProp);
                                        
					if(typeof markersTable == 'object')
					{
						var firstMsg = {};
						
						firstMsg = {lat: markersTable[0].latitude, lng: markersTable[0].longitude}; 

						map.setZoom(15);
						map.setCenter(firstMsg);
						
						var bounds = new google.maps.LatLngBounds();
						
						var index = 1;
						markersTable.forEach(function(item){
							
							var msgPosition;
							var msgTitle;
							var msgThumb;
							var msgText;
							var infoWindow = new google.maps.InfoWindow({map: map, maxWidth: 400});
							
							msgPosition = {lat: item['latitude'], lng: item['longitude']};
							msgTitle = String(index);
							msgText = item['titre'];
							
							marker = new google.maps.Marker({position: msgPosition, map: map, label: msgTitle, title: msgText});
							
							prepareInfoWindow(item,infoWindow,marker,index);
							
							marker.addListener('click', function() {
								infoWindow.open(map, this);
							});

							
							if(index==1)
							{
								infoWindow.open(map, marker);
							}
							
							bounds.extend(msgPosition);
							
							index++;
						});
						
						map.fitBounds(bounds);
					}
					else 
					{
						marker = new google.maps.Marker({ //on créé le marqueur
								position: mapcenter,
								map: map,
								title: 'Europe'
							});

						<?php 
						echo 'var noMsgOnMap = "'.JText::_('COM_ITINERARY_FIELD_NO_MESSAGES_MAP').'";' ;
						?>
						
						infoWindow.setContent(noMsgOnMap);
						infoWindow.setPosition(mapcenter);
					}
				}

				function prepareInfoWindow(msgInfo,infoWindow,marker,index)
				{					
					console.log(msgInfo.activation_planificateur);
					
					var InfoMsgOnMap;
					
					var alertUnpublish ='';
					if(msgInfo.activation_planificateur==0)
					{
						alertUnpublish = ' - Ce message n\'est pas activé!';
					}
					
					if(msgInfo.image != "images/com_itinerary/")
					{
						
						
						InfoMsgOnMap = 
										'<div>'+
											'<h4><u>'+msgInfo.titre+'</u>'+alertUnpublish+'</h4>'+
											'<img style="max-width: 100%; height: auto;" src="'+msgInfo.image+'" class="img-responsive center-block"/>'+
											'<div class="text-center">'+msgInfo.titre_image+'</div>'+
											'<div class="text-justify-infowindow voffset3">'+msgInfo.texte+'</div>'+
										'</div>';
					}
					else
					{
						InfoMsgOnMap = 
										'<div>'+
											'<h4><u>'+msgInfo.titre+'</u>'+alertUnpublish+'</h4>'+
											'<div class="text-justify-infowindow voffset3">'+msgInfo.texte+'</div>'+
										'</div>';
					}
					
					latLngMsg = {lat: msgInfo.latitude, lng: msgInfo.longitude}; 
					
					infoWindow.setContent(InfoMsgOnMap);
					infoWindow.setPosition(latLngMsg);
					infoWindow.close();
					
					document.getElementById('floating-panel').addEventListener("click", function(){
								
						var selectedMsg = document.getElementById("numberMsg").value;
						var numberMsg = parseInt(index);
						
						console.log('selectedMsg: '+selectedMsg);
						console.log('index: '+numberMsg);
						
						if(selectedMsg == numberMsg)
						{
							infoWindow.open(map, marker);
						}
						else
						{
							infoWindow.close();
						}
					});
				
				}
				
				function nextMsg()
				{
					var msgNumber = document.getElementById('numberMsg').value;
					
					msgNumber = Math.min(parseInt(msgNumber) + 1, Object.keys(markersTable).length);
					
					document.getElementById('numberMsg').value = msgNumber;
				}
				
				function previousMsg()
				{
					var msgNumber = document.getElementById('numberMsg').value;
					
					msgNumber = Math.max(parseInt(msgNumber) - 1, 1);
					
					document.getElementById('numberMsg').value = msgNumber;
				}
				
				function firstMsg()
				{
					var msgNumber = 1;
					
					document.getElementById('numberMsg').value = msgNumber;
				}
				
				function lastMsg()
				{
					msgNumber = Object.keys(markersTable).length;
					
					document.getElementById('numberMsg').value = msgNumber;

				}
				
			</script>

			<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDl-FHUqBXtyqBK-kqCQ_EtlpZWTuawqMU&callback=initMap" ></script>		

