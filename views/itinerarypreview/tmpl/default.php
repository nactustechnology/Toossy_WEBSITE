<html>
<?php
defined('_JEXEC') or die('Restricted access');

$msgForMarker = $this->items;
?>
<head>     
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.2.0/dist/leaflet.css"   integrity="sha512-M2wvCLH6DSRazYeZRIm1JnYyh22purTM+FDB5CsyxtQJYeKq83arPe5wgbNmcFXGqiSH2XR8dT/fJISVA1r/zQ==" crossorigin=""/>
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

                            z-index: 3;
                          }
		</style>		
 </head>
  <body>

                <div id ="mapid" ></div>
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
                
		<!--<div id="map"></div>-->
                <script src="https://unpkg.com/leaflet@1.2.0/dist/leaflet.js"   integrity="sha512-lInM/apFSqyy1o6s89K4iQUKg6ppXEgsVxT35HbzUupEVRh2Eu9Wdl4tHj7dZO0s1uvplcYGmt3498TtHq+log=="crossorigin=""></script>
                <script src = "https://unpkg.com/pouchdb@^5.2.0/dist/pouchdb.js"></script>
                
            <script type="text/javascript">
 
                    function RefreshParent()
                    {

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
                    var mymap = L.map('mapid').setView([48.905247, 2.231889], 13);
                    var html="";
                    
                    if(typeof markersTable == 'object')
                    {
                            var firstMessageg = {};

                            firstMessageg = {lat: markersTable[0].latitude, lng: markersTable[0].longitude}; 

        
                            mymap.setView([firstMessageg.lat, firstMessageg.lng], 13);

                            //var bounds = new google.maps.LatLngBounds();

                            var index = 1;
                            markersTable.forEach(function(item)
                            {
                                marker = L.marker([item['latitude'], item['longitude']]).addTo(mymap);

                                generateHtml(item,marker,index);

                                marker.bindPopup(html);

                                if(index==1)
                                {
                                    marker.openPopup();
                                }

                                index++;
                            });

                            //map.fitBounds(bounds);
                    }
                    else 
                    {
                        marker = L.marker([item['latitude'], item['longitude']]).addTo(mymap);
                        
                        <?php 
                            echo 'var noMsgOnMap = "'.JText::_('COM_ITINERARY_FIELD_NO_MESSAGES_MAP').'";' ;
                        ?>
                    }
                    
                    
                    
                    
                    var myLayer = L.tileLayer('https://stamen-tiles-{s}.a.ssl.fastly.net/terrain/{z}/{x}/{y}.{ext}', {
                                    attribution: 'Map tiles by <a href=\"http://stamen.com\">Stamen Design</a>, <a href=\"http://creativecommons.org/licenses/by/3.0\">CC BY 3.0</a> &mdash; Map data &copy; <a href=\"http://www.openstreetmap.org/copyright\">OpenStreetMap</a>',
                                    subdomains: 'abcd',
                                    //bounds: myBounds,
                                    useCache: true,
                                    minZoom: 0,
                                    maxZoom: 19,
                                    ext: 'png',
                                }).addTo(mymap);
                                
                  
                    function generateHtml(msgInfo,_marker,_index)
                    {
                        var alertUnpublish ='';
                        
                        if(msgInfo.activation_planificateur==0)
                        {
                                alertUnpublish = ' - Ce message n\'est pas activé!';
                        }

                        if(msgInfo.image != "images/com_itinerary/")
                        {


                                html = 
                                    '<div>'+
                                            '<h4><u>'+msgInfo.titre+'</u>'+alertUnpublish+'</h4>'+
                                            '<img style="max-width: 100%; height: auto;" src="'+msgInfo.image+'" class="img-responsive center-block"/>'+
                                            '<div class="text-center">'+msgInfo.titre_image+'</div>'+
                                            '<div class="text-justify-infowindow voffset3">'+msgInfo.texte+'</div>'+
                                    '</div>';
                        }
                        else
                        {
                                html = 
                                    '<div>'+
                                            '<h4><u>'+msgInfo.titre+'</u>'+alertUnpublish+'</h4>'+
                                            '<div class="text-justify-infowindow voffset3">'+msgInfo.texte+'</div>'+
                                    '</div>';
                        }
                        
                        document.getElementById('floating-panel').addEventListener("click", function()
                        {
                                var selectedMsg = document.getElementById("numberMsg").value;
                                var numberMsg = parseInt(_index);

                                console.log('selectedMsg: '+selectedMsg);
                                console.log('index: '+numberMsg);

                                if(selectedMsg == numberMsg)
                                {
                                        _marker.openPopup();
                                }
                                else
                                {
                                        _marker.closePopup();
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
        </div>
  </body>
</html>
