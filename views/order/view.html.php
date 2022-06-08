<?php
defined('_JEXEC') or die;

class ItineraryViewOrder extends JViewLegacy
{
	/**
	* Display the Message View
	*
	* @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	*
	* @return  void
	*/

	protected $item;

	 // Overwriting JView display method
	public function display($tpl = null)
	{
		// Get some data from the models
		$this->item = $this->get('Item');
		$this->state= $this->get('State');		
		$this->model=$this->getModel();
                
		$this->ItinerarySizeMaximum=ItineraryHelper::getItinerarySizeMax($this->model);
		$this->profile = ItineraryHelper::getUserProfile();
		$this->itineraryList = $this->model->getItineraryList();
                $this->subscriptionQtyByDuration = $this->model->countSubscriptionsGroupByDuration();
                $this->pricing = $this->model->getPricing();
                
                $userCountry=$this->model->getUserCountry();
                $this->tvaRate = $this->model->getTvaRate($userCountry);
                $this->user = JFactory::getUser()->username;;
                    
                $itineraryHTMLListe=$this->itineraryList;
                
                $document = JFactory::getDocument();                
                
                $document->addScriptDeclaration('
                    var subscriptionQtyByDurationArray = new Array();
                    
                    subscriptionQtyByDurationArray["1"] = 0 ;
                    subscriptionQtyByDurationArray["3"] = 0 ;
                    subscriptionQtyByDurationArray["6"] = 0 ;
                    subscriptionQtyByDurationArray["12"] = 0 ; 
                ');
                
                foreach($this->subscriptionQtyByDuration as $qtyByDuration)
                {                   
                    $document->addScriptDeclaration('subscriptionQtyByDurationArray["'.$qtyByDuration['duree'].'"] = '.$qtyByDuration['subscriptionQty'].';');
                }

                $document->addScriptDeclaration("var tableTarifs = new Array(13);");

                $document->addScriptDeclaration("tableTarifs[1] = new Array(6);");
                $document->addScriptDeclaration("tableTarifs[3] = new Array(6);");
                $document->addScriptDeclaration("tableTarifs[6] = new Array(6);");
                $document->addScriptDeclaration("tableTarifs[12] = new Array(6);");
                
                foreach($this->pricing as $price)
                {
                    $document->addScriptDeclaration('tableTarifs['.$price["duree"].']['.$price["clef_size"].']='.$price["tarif"].';');
                }
              
                
                $document->addScriptDeclaration('
                    jQuery(document).ready(function(){

                        jQuery("#checkCodePromo").click(function(){

                            var codePromo = jQuery("#codePromo").val();

                            // you can apply validation here on the name field.

                            jQuery.post("/components/com_itinerary/ajax.php?codePromo="+codePromo ,
                                {}, 
                                function(response){

                                    var divText ="";

                                    if(response.valueOf()==0)
                                    {
                                        divText="<div>Vérifiez le code promo!</div>";
                                    }
                                    else
                                    {
                                        divText="<div>Un abonnement d\'une durée de "+response+" mois offert(s)!</div>";
                                    }

                                    jQuery("#typePromo").html(jQuery(divText).fadeIn("slow"));
                                    jQuery("#promoValue").val(response.valueOf()).fadeIn("slow").change(setDescriptionAndTarif());
                               });

                        });
                    });
                ');

                
                
                $document->addScriptDeclaration('
                    function writeHTMLcode(subscriptionId)
                    {   
                        var subscription = "subscription_"+subscriptionId;
                        var html = \'<div class="span2 centrer"> \
                            '.JText::_("COM_ITINERARY_ORDER_SUBSCRIPTION_FOR").' : \
                        </div> \
                        <div class="span4 center"> \
                            <select id="idSelectedParcours_\'+subscriptionId+\'" class="rowSubscriptionsOrderItinerarySelect" onchange="copyItineraryName(\'+subscriptionId+\')"> \
                            </select> \
                        </div> \
                        <div class="span2"> \
                            '.JText::_("COM_ITINERARY_ORDER_SUBSCRIPTION_DURATION").' : \
                        </div> \
                        <div class="span3 center"> \
                            <select id="durationSelectedParcours_\'+subscriptionId+\'" class="rowSubscriptionsOrderSelect" onchange="copySubscriptionDuration(\'+subscriptionId+\')"> \
                                <option value="undefined" selected="true">'.JText::_("COM_ITINERARY_ORDER_SELECT_DURATION").'</option> \
                                <option value="1">1 '.JText::_("COM_ITINERARY_SUBSCRIPTION_MONTH").'</option> \
                                <option value="3">3 '.JText::_("COM_ITINERARY_SUBSCRIPTION_MONTH").'</option> \
                                <option value="6">6 '.JText::_("COM_ITINERARY_SUBSCRIPTION_MONTH").'</option> \
                                <option value="12">12 '.JText::_("COM_ITINERARY_SUBSCRIPTION_MONTH").'</option> \
                            </select> \
                        </div> \
                        <div class="span1"> \
                            <span type="button" class="btn btn-primary pull-right align-middle rowSubscriptionsOrderTrash" onclick="removeSubscriptionRow(\'+subscription+\')"> \
                                <i class="icon-trash"></i> \
                            </span> \
                        </div>\';

                        return html;
                    }

                    function addSubscriptionRow(indexSelectedItinerary="undefined")
                    {
                        if(checkLastSubscriptionRow())
                        {                            
                            var rowCount = document.getElementById("subscriptionsList").lastElementChild.id;
                            rowCount=rowCount.split("_");
                            rowCount=rowCount[1];       

                            rowCount++;

                            var newRow = document.createElement("DIV");
                            newRow.setAttribute("id", "subscription_"+rowCount);
                            newRow.setAttribute("class", "row center rowSubscriptionsOrder voffset2");
                            
                            newRow.innerHTML = writeHTMLcode(rowCount);

                            var liste = document.getElementById("subscriptionsList");
                            liste.appendChild(newRow);

                            lockItinerarySelect();
                            
                            resetSelectDropdownList(indexSelectedItinerary);
                            
                            generateDetailOrder(rowCount);
                        }
                    }
                    
                    function generateDetailOrder(rowCount)
                    {
                        var html = \'<td id="\'+rowCount+\'" class="hidden rowData">undefined#undefined</td> \
                                    <td id="subscription_titre_\'+rowCount+\'" class="center bg-danger"> \
                                        '.JText::_("COM_ITINERARY_ORDER_CHOOSE_ITINERARY_TYPE").' \
                                    </td> \
                                    <td id="subscription_description_\'+rowCount+\'" class="text-justify bg-danger"> \
                                        '.JText::_("COM_ITINERARY_ORDER_CHOOSE_ITINERARY_DURATION").' \
                                    </td> \
                                    <td id="subscription_monthly_price_HT_\'+rowCount+\'" class="center bg-danger"> \
                                        '.JText::_("COM_ITINERARY_ORDER_CHOOSE_ITINERARY_DURATION").' \
                                    </td> \
                                    <td id="subscription_duration_\'+rowCount+\'" class="center bg-danger"> \
                                        '.JText::_("COM_ITINERARY_ORDER_CHOOSE_ITINERARY_DURATION").' \
                                    </td> \
                                    <td id="subscription_price_HT_\'+rowCount+\'" class="center bg-danger"> \
                                        '.JText::_("COM_ITINERARY_ORDER_CHOOSE_ITINERARY_DURATION").' \
                                    </td>\' ;

                        var newRow = document.createElement("TR");
                        
                        newRow.setAttribute("id", "subscription_details_"+rowCount);
                        newRow.innerHTML = html;
                        
                        var liste = document.getElementById("subscriptionDetailsList");
                        liste.appendChild(newRow);
                    }
                            
                    function removeSubscriptionRow(elementId)
                    {
                        var parent = document.getElementById("subscriptionsList");
                        var child = document.getElementById(elementId.id);

                        parent.removeChild(elementId);
                        
                        var rowId = elementId.id;

                        var child = document.getElementById(rowId.replace("subscription_","subscription_details_"));
                        var parent = document.getElementById("subscriptionDetailsList");
                        
                        parent.removeChild(child);
                        
                        lockItinerarySelect();
                        
                        var itinerarySelectList = document.getElementsByClassName("rowSubscriptionsOrderItinerarySelect");
                        var itinerarySelectArray = Array.from(itinerarySelectList);                        
                        var indexSelectedItinerary = itinerarySelectArray[itinerarySelectArray.length-1].selectedOptions[0].value;
                        
                        resetSelectDropdownList(indexSelectedItinerary);
                        
                        setDescriptionAndTarif();
                    }
                    
                    function lockItinerarySelect()
                    {
                        var itinerarySelectList = document.getElementsByClassName("rowSubscriptionsOrderItinerarySelect");

                        var itinerarySelectArray = Array.from(itinerarySelectList);
                        
                        itinerarySelectArray.map(function (element)
                        {
                            element.disabled=true;
                        });
                        
                        itinerarySelectArray[itinerarySelectArray.length-1].disabled=false;
                    }
                    
                    function resetSelectDropdownList(indexSelectedItinerary)
                    {
                        var itineraryRawListe = \'{"array": '. json_encode($itineraryHTMLListe).' }\';
                        var itineraryArrayListe = JSON.parse(itineraryRawListe);
                        selectedItineraryIdArray=checkRedundancy();
                        
                        
                        var html =  \'<option value="undefined">'.JText::_("COM_ITINERARY_ORDER_SELECT_ITINERARY").'</option>\';
                                        
                        if(indexSelectedItinerary!="undefined")
                        {
                            selectedItineraryIdArray.pop();
                        }

                        itineraryArrayListe["array"].forEach(function(element) {
                            if(selectedItineraryIdArray.includes(element.clef)==false)
                            {                               
                                var selectedBool="";

                                if(element.clef==indexSelectedItinerary)
                                {
                                     selectedBool=\' selected="true" \';
                                }

                                html += \'<option value="\'+element.clef+\'" \'+selectedBool+\'>\'+element.titre+\'</option> \ \';
                            }
                        });
                        
                        var itinerarySelectList = document.getElementsByClassName("rowSubscriptionsOrderItinerarySelect");

                        var itinerarySelectArray = Array.from(itinerarySelectList);
                        
                        
                        
                        itinerarySelectArray[itinerarySelectArray.length-1].innerHTML=html;
                    }
                    
                    function checkRedundancy()
                    {
                        var subscriptionList = document.getElementsByClassName("rowSubscriptionsOrderItinerarySelect");
                        var subscriptionArray = Array.from(subscriptionList);

                        var selectedItineraryIdArray = subscriptionArray.map(function (element)
                        {
                            if(element.selectedOptions[0])
                            {
                                return element.selectedOptions[0].value;
                            }
                            
                        });
                        
                        return selectedItineraryIdArray;
                    }
                    
                    function checkLastSubscriptionRow()
                    {
                        var itinerarySelectList = document.getElementsByClassName("rowSubscriptionsOrderItinerarySelect");
                        
                        
                        var itinerarySelectArray = Array.from(itinerarySelectList);
             
                        if(itinerarySelectArray[itinerarySelectArray.length-1].selectedOptions[0].value=="undefined")
                        {
                            alert("Veuillez d\'abord choisir un parcours avant d\'ajouter un nouvel abonnement");
                            return false;
                        }
                        else
                        {
                            return true;
                        }
                    }
                    
                    function copyItineraryName(rowId)
                    {
                        document.getElementById("subscription_titre_"+rowId).innerHTML = document.getElementById("idSelectedParcours_"+rowId).selectedOptions[0].text;
                        
                        var selectedIndex = document.getElementById("idSelectedParcours_"+rowId).selectedOptions[0].index;
                        if(selectedIndex==0)
                        {
                            document.getElementById("subscription_titre_"+rowId).classList.add("bg-danger");
                        }
                        else
                        {
                            document.getElementById("subscription_titre_"+rowId).classList.remove("bg-danger");
                        }
                        
                        var selectedValue = document.getElementById("idSelectedParcours_"+rowId).selectedOptions[0].value;
                        var parentIdStr = document.getElementById("subscription_details_"+rowId).firstElementChild.innerHTML;
                        var parentIdArr = parentIdStr.split("#");
                        parentIdArr[0] = selectedValue;
                        
                        document.getElementById("subscription_details_"+rowId).firstElementChild.innerHTML = parentIdArr.join("#");
                        
                        //new part
                        var rowDataList = document.getElementsByClassName("rowData");
                        var rowDataArray = Array.from(rowDataList);
                        var subscriptionsArray = [];

                        rowDataArray.map(function (element)
                        {
                            var rowDataStr = element.innerHTML;
                            subscriptionsArray.push(rowDataStr);
                        });
                        
                        document.getElementById("subscriptionArray").value = subscriptionsArray.join("@");
                    }
                    
                    String.prototype.format = function() {
                        a = this;
                        for (k in arguments) {
                          a = a.replace("{" + k + "}", arguments[k]);
                        }
                        return a;
                    }
                    
                    function getGetOrdinal(n) {
                        if(n==1)
                        {
                            return n+"er";
                        }
                        else
                        {
                            return n+"ème";
                        }
                    }

                    function copySubscriptionDuration(rowId)
                    {
                        var duration = document.getElementById("durationSelectedParcours_"+rowId).selectedOptions[0].text;
                        document.getElementById("subscription_duration_"+rowId).innerHTML = duration;
                        
                        if(document.getElementById("durationSelectedParcours_"+rowId).selectedOptions[0].index==0)
                        {
                            document.getElementById("subscription_description_"+rowId).innerHTML = duration;
                            document.getElementById("subscription_monthly_price_HT_"+rowId).innerHTML = duration;
                            document.getElementById("subscription_price_HT_"+rowId).innerHTML = duration;
                            
                            document.getElementById("subscription_description_"+rowId).classList.add("bg-danger");
                            document.getElementById("subscription_monthly_price_HT_"+rowId).classList.add("bg-danger");
                            document.getElementById("subscription_duration_"+rowId).classList.add("bg-danger");
                            document.getElementById("subscription_price_HT_"+rowId).classList.add("bg-danger");
                        }
                        else
                        {
                            document.getElementById("subscription_description_"+rowId).classList.remove("bg-danger");
                            document.getElementById("subscription_monthly_price_HT_"+rowId).classList.remove("bg-danger");
                            document.getElementById("subscription_duration_"+rowId).classList.remove("bg-danger");
                            document.getElementById("subscription_price_HT_"+rowId).classList.remove("bg-danger");
                        }
                        
                        var selectedValue = document.getElementById("durationSelectedParcours_"+rowId).selectedOptions[0].value;
                        var parentIdStr = document.getElementById("subscription_details_"+rowId).firstElementChild.innerHTML;
                        var parentIdArr = parentIdStr.split("#");
                        parentIdArr[1] = selectedValue;
                        
                        document.getElementById("subscription_details_"+rowId).firstElementChild.innerHTML = parentIdArr.join("#");
                        
                        setDescriptionAndTarif();
                    }
                        
                    function setDescriptionAndTarif()
                    {   
                        var newSubscriptionQtyByDurationArray = subscriptionQtyByDurationArray.slice(0);

                        var rowDataList = document.getElementsByClassName("rowData");
                        var rowDataArray = Array.from(rowDataList);
                        var totalHT = 0;
                        var subscriptionsArray = [];
                        var currency = "EUR";
                        
                        var promoValue = document.getElementById("promoValue").value;
                        var montantPromo=0;

                        rowDataArray.map(function (element)
                        {
                            var rowDataStr = element.innerHTML;
                            subscriptionsArray.push(rowDataStr);
                            
                            var rowDataArray = rowDataStr.split("#");
                            var subscriptionDuration = rowDataArray[1];
                            var rowNumber = element.id;
                            

                            if(rowDataArray[1]!="undefined")
                            {
                                newSubscriptionQtyByDurationArray[subscriptionDuration]++;
                                var newSubscriptionQty = Math.min(newSubscriptionQtyByDurationArray[subscriptionDuration],5);
                                

                                document.getElementById("subscription_description_"+rowNumber).innerHTML = ("'.sprintf(JText::_('COM_ITINERARY_ORDER_SUBSCRIPTION_DESCRIPTION'),$_SESSION['parcoursMsgMax']).'").format(subscriptionDuration,getGetOrdinal(newSubscriptionQtyByDurationArray[subscriptionDuration]));
                                document.getElementById("subscription_monthly_price_HT_"+rowNumber).innerHTML = (tableTarifs[subscriptionDuration.valueOf()][newSubscriptionQty.valueOf()]).toLocaleString(\'fr-FR\', {style: \'currency\', currency:currency, minimumFractionDigits:0});
                                document.getElementById("subscription_price_HT_"+rowNumber).innerHTML = (tableTarifs[subscriptionDuration.valueOf()][newSubscriptionQty.valueOf()]*subscriptionDuration.valueOf()).toLocaleString(\'fr-FR\', {style: \'currency\', currency:currency, minimumFractionDigits:0});
                                
                                if(subscriptionDuration.valueOf()==promoValue.valueOf())
                                {
                                    montantPromo = Math.max(montantPromo,tableTarifs[subscriptionDuration.valueOf()][newSubscriptionQty.valueOf()]*subscriptionDuration.valueOf());
                                }

                                totalHT+= tableTarifs[subscriptionDuration.valueOf()][newSubscriptionQty.valueOf()]*subscriptionDuration.valueOf();
                            }
                        });
                        
                        if(montantPromo.valueOf()!=0)
                        {
                            document.getElementById("montantPromo").innerHTML = "- " + montantPromo.toLocaleString(\'fr-FR\', {style: \'currency\', currency:currency, minimumFractionDigits:0});
                        }
                        else
                        {
                            document.getElementById("montantPromo").innerHTML ="";
                        }

                        totalHT-=montantPromo;

                        var tva = totalHT.valueOf() * ('.$this->tvaRate.').valueOf();
                        var totalTTC = totalHT.valueOf() + tva.valueOf();
                        
                        if(totalTTC.valueOf()==0)
                        {
                            document.getElementById("paymentTypeArea").classList.add("hidden");
                        }
                        else
                        {
                            document.getElementById("paymentTypeArea").classList.remove("hidden");
                        }

                        document.getElementById("subscriptionArray").value = subscriptionsArray.join("@");

                        document.getElementById("prix_ht").innerHTML = totalHT.toLocaleString(\'fr-FR\', {style: \'currency\', currency:currency, minimumFractionDigits:0});
                        document.getElementById("tva").innerHTML = tva.toLocaleString(\'fr-FR\', {style: \'currency\', currency:currency, currency:currency, minimumFractionDigits:0});
                        document.getElementById("prix_ttc").innerHTML = totalTTC.toLocaleString(\'fr-FR\', {style: \'currency\', currency:currency, currency:currency, minimumFractionDigits:0});
                    }
                    
                    jQuery(document).ready(function() {
                        document.getElementById("cgvAcceptance").addEventListener("change",changeCgvAcceptanceValueAndSubmitButton);
                        document.getElementById("formSubmitButton").addEventListener("click",submitButtonClickEvent);
                        document.getElementById("printSummary").addEventListener("click",printSummary);
                        
                        
                        function changeCgvAcceptanceValueAndSubmitButton()
                        {
                            if(document.getElementById("cgvAcceptance").checked==true)
                            {
                                document.getElementById("cgvAcceptanceValue").value=1;
                            }
                            else
                            {
                                document.getElementById("cgvAcceptanceValue").value=0;
                            }
                        }
                        
                        function submitButtonClickEvent()
                        {
                            if(document.getElementById("cgvAcceptance").checked==true)
                            {
                                var subscriptionsString = document.getElementById("subscriptionArray").value;
                                console.log(subscriptionsString);
                                if(subscriptionsString.match("undefined"))
                                {
                                    alert("'.JText::_('COM_ITINERARY_ORDER_CHECK_INPUT').'");
                                }
                                else
                                {

                                    Joomla.submitbutton("payment.paymentRooting");
   
                                }
                            }
                            else
                            {
                                alert("'.JText::_('COM_ITINERARY_SUBSCRIPTION_CGV_NOT_CHECKED').'");
                            }
                        }
                        
                        function printSummary()
                        {
                            window.print();
                        }
                    });
		');
                
                if(isset($_SESSION['renewal']))
                {
                    $renewalsArray=$_SESSION['renewal'];
                    
                    $renewalsArray[0];
                    
                    $jsScript="";
                    
                    if(!empty($renewalsArray[0]))
                    {
                        $firstItinerary=explode("_",$renewalsArray[0]);
                        
                        $jsScript.='document.getElementById("idSelectedParcours_1").value='.$firstItinerary[0].';';
                        $jsScript.='copyItineraryName(1);';
                    }
                            
                    array_shift($renewalsArray);
                    $i=2;  
                    foreach($renewalsArray as $renewal)
                    {
                        $tinerary=explode("_",$renewal);
                        
                        $jsScript.='addSubscriptionRow('.$tinerary[0].');';
                        $jsScript.='copyItineraryName('.$i.');';
                        
                        $i++;
                    }
                    
                    $document->addScriptDeclaration('
                            jQuery(document).ready(function() {
                                '.$jsScript.'
                            });
                    ');
                }
                
		// Display the view
		parent::display($tpl);
	}

}