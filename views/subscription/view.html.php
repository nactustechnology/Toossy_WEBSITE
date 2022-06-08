<!--View subscription-->
<?php
defined('_JEXEC') or die;

class ItineraryViewSubscription extends JViewLegacy
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
		
		$this->user=JFactory::getUser()->username;
		$this->profile = ItineraryHelper::getUserProfile();
		
		$this->path_image = "images" . DS . "com_itinerary" . DS ;
		$this->path = "images" . DS . "logos" . DS ;

		$app=JFactory::getApplication();
		
		$this->modelParcours=JModelLegacy::getInstance('Itinerary', 'ItineraryModel');		
		$this->idParcours = $app->getUserState('idParcours');
		$this->itinerary = $this->modelParcours->getItem($this->idParcours);
		$app->setUserState('nombreMessages',$this->itinerary->nombre_messages);

		if(!empty($app->input->post->get('duree')))
		{
			$_SESSION['dureeAbonnement']=(int)$app->input->post->get('duree');
		}

		if(!empty($_SESSION['dureeAbonnement']))
		{
			$_SESSION['amount'] = 100*$this->model->getAmount($_SESSION['dureeAbonnement']);
		}

		if($app->input->post->getInt('cgvAcceptanceValue')!=null)
		{
			$_SESSION['cgv_approval'] = $app->input->post->getInt('cgvAcceptanceValue');
		}
		

		if($app->input->post->getInt('cardRemenberValue')!=null)
		{
			$_SESSION['cardRemenber_approval'] = $app->input->post->getInt('cardRemenberValue');
		}

		
		if(isset($_SESSION['renewalDate'])&&$_SESSION['renewalDate']>date('Y-m-d')&&!isset($_SESSION['dateDebut']))
		{
			$date_debut=strtotime($_SESSION['renewalDate'])*1000;
		}
		elseif(isset($_SESSION['dateDebut']))
		{
			$jourDebut=date('d',strtotime($_SESSION['dateDebut']));
			
			if(date('d')>=$jourDebut)
			{
				$date_debut=strtotime(date('Y-m-'.$jourDebut))*1000;
			}
			else
			{
				$moisDebut=date('m',strtotime('previous month'));
				
				$date_debut=strtotime(date('Y-'.$moisDebut.'-'.$jourDebut))*1000;
			}				
		}
		else
		{
			$date_debut='';
		}
		
                if(!is_null($this->item->tva_rate))
                {
                    //$taux_tva=0.20;
                    $taux_tva=$this->item->tva_rate;
                    //$currency=$this->item->currency;
                    $currency="EUR";
                    
                }
                else
                {
                    $taux_tva="undefined";
                    $currency="undefined";
                }
                
		//Include the Stripe Library
		define('__ROOT__', $_SERVER['DOCUMENT_ROOT'] );
		require_once(__ROOT__.'/libraries/stripe-php/init.php');
		\Stripe\Stripe::setApiKey("sk_test_z0J9ztS4FFBddItntsNbduUE");

		define('__HOST__', $_SERVER['HTTP_HOST'] );

		$idCustomer = $this->model->getUserCusId();
		
		if($idCustomer!=null)
		{
			$customer = \Stripe\Customer::retrieve($idCustomer);
		}

		if(isset($customer) && !isset($customer->deleted))
		{
			$sourceId = $customer->default_source;

			if(!empty($sourceId))
			{
				$this->source = $customer->sources->retrieve($sourceId);
			}
		}
		else
		{
			$userMail = JFactory::getUser()->email;

			// Create a Customer:
			$customer = \Stripe\Customer::create(array(
				"email" => $userMail
			));		

			$this->model->setUserCusId($customer->id);
		}

		

		$this->customer = $customer;
		$_SESSION['customer'] = $customer;

		
		$document = JFactory::getDocument();
		$document->addScriptDeclaration("

			jQuery( document ).ready(
				function()
                                {
                                    function actualisationVue()
                                    {
                                        if(document.getElementById('subscriptionSelectForm'))
                                        {
                                            var subscriptionStart = new Date(".$date_debut."); 
                                            var subscriptionEnd = new Date(".$date_debut."); 
                                            var subscriptionValues=document.getElementById('subscriptionDuree').value;
                                            subscriptionValues=subscriptionValues.split('@');
                                            var subscriptionDuree = Number(subscriptionValues.slice(0,1));
                                            var unite = '".JText::_('COM_ITINERARY_SUBSCRIPTION_MONTH')."';
                                            var subscriptionTarif = Number(subscriptionValues.slice(1,2));
                                            var subscriptionMsgMax = Number(subscriptionValues.slice(2));
                                            var subscriptionPrixHT=subscriptionDuree*subscriptionTarif;
                                            subscriptionEnd.setMonth(subscriptionStart.getMonth()+subscriptionDuree);
                                            subscriptionEnd.setDate(subscriptionEnd.getDate()-1);


                                            var tvaTaux = ".$taux_tva.";
                                            var currency = '".$currency."';


                                            document.getElementById('date_debut').innerHTML=subscriptionStart.toLocaleDateString();
                                            document.getElementById('date_fin').innerHTML=subscriptionEnd.toLocaleDateString();
                                            document.getElementById('tarif').innerHTML=subscriptionTarif.toLocaleString('fr-FR', {style: 'currency', currency:currency});
                                            document.getElementById('prix_ht').innerHTML=subscriptionPrixHT.toLocaleString('fr-FR', {style: 'currency', currency:currency});

                                            document.getElementById('duree_abonnement').innerHTML=subscriptionDuree+' '+unite;
                                            document.getElementById('duree').value=subscriptionDuree;
                                            document.getElementById('msg_max').innerHTML=subscriptionMsgMax;

                                            

                                            if(tvaTaux != undefined)
                                            {
                                                console.log('test');
                                                var subscriptionPrixTTC= (1+tvaTaux)*subscriptionPrixHT;
                                                var subscriptionTVA = tvaTaux*subscriptionPrixHT;
                                                document.getElementById('tva').innerHTML=subscriptionTVA.toLocaleString('fr-FR', {style: 'currency', currency:currency});
                                                document.getElementById('prix_ttc').innerHTML=subscriptionPrixTTC.toLocaleString('fr-FR', {style: 'currency', currency:currency});
                                            }
                                            else
                                            {
                                                document.getElementById('tva').innerHTML = '-';
                                                document.getElementById('prix_ttc').innerHTML = '-';
                                            }
                                        }
                                    }

                                    function changeCardRemenberValue()
                                    {
                                        if(document.getElementById('cardRemenber').checked==true)
                                        {
                                            document.getElementById('cardRemenberValue').value=1;
                                        }
                                        else
                                        {
                                            document.getElementById('cardRemenberValue').value=0;
                                        }
                                    }



                                    if(document.getElementById('cardRemenber'))
                                    {
                                        document.getElementById('cardRemenber').addEventListener('change',changeCardRemenberValue);
                                    }

                                    if(document.getElementById('subscriptionDuree'))
                                    {
                                        document.addEventListener('load',actualisationVue());
                                        document.getElementById('subscriptionDuree').addEventListener('click',actualisationVue);
                                    }

                                    function changeCgvAcceptanceValueAndSubmitButton()
                                    {
                                        if(document.getElementById('cgvAcceptance').checked==true)
                                        {
                                            document.getElementById('cgvAcceptanceValue').value=1;
                                        }
                                        else
                                        {
                                            document.getElementById('cgvAcceptanceValue').value=0;
                                        }
                                    }

                                    if(document.getElementById('cgvAcceptance'))
                                    {
                                        document.getElementById('cgvAcceptance').addEventListener('change',changeCgvAcceptanceValueAndSubmitButton);
                                    }

                                    if(document.getElementById('formSubmitButton'))
                                    {
                                        document.getElementById('formSubmitButton').addEventListener('click',submitButtonClickEvent);
                                    }

                                    function printSummary()
                                    {
                                        window.print();
                                    }

                                    if(document.getElementById('printSummary'))
                                    {
                                        document.getElementById('printSummary').addEventListener('click',printSummary);
                                    }
                                    
                                    function submitButtonClickEvent()
                                    {
                                        if(document.getElementById('cgvAcceptance').checked==true)
                                        {
                                            console.log('vrai');
                                            if(document.getElementById('cardRadio').checked==true)
                                            {
                                                document.getElementById('subscriptionSelectForm').submit();
                                            }
                                            else if(document.getElementById('ibanRadio').checked==true)
                                            {
                                                document.getElementById('subscriptionSelformSubmitButtonectForm').action = '".JRoute::_('index.php?option=com_itinerary&view=subscription&layout=payment_iban')."';
                                                document.getElementById('subscriptionSelectForm').submit();
                                            }
                                        }
                                        else
                                        {
                                            console.log('faux');
                                            alert('".JText::_('COM_ITINERARY_SUBSCRIPTION_CGV_NOT_CHECKED')."');
                                        }
                                    }
				});
			");

                
                /*if(document.getElementById('formSubmitButton'))
                {
                        document.getElementById('formSubmitButton').addEventListener('click',submitButtonClickEvent);
                }*/
                
		$document->addScriptDeclaration("
				function discardConfirmation()
				{
                                    if(confirm('".JText::_('COM_ITINERARY_CARD_WARNING_BEFORE_DISCARDING_LABEL')."'))
                                    {
                                        document.getElementById('payment-form-1').submit();
                                    }
				};
                                
                                function cardAssociationConfirmation()
				{
                                    if(confirm('".JText::_('COM_ITINERARY_CARD_WARNING_BEFORE_ASSOCIATION_LABEL')."'))
                                    {
                                        document.getElementById('payment-form-2').submit();
                                    }
				};
                                
                                function ibanAssociationConfirmation()
				{
                                    if(confirm('".JText::_('COM_ITINERARY_IBAN_WARNING_BEFORE_ASSOCIATION_LABEL')."'))
                                    {
                                        document.getElementById('payment-form-3').submit();
                                    }
				};
			");

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{			
			//JError::raiseError(500, implode("\n", $errors));
			throw new Exception(implode("\n", $errors),500);		

			return false;

		}	

		// Display the view
		parent::display($tpl);
	}

}