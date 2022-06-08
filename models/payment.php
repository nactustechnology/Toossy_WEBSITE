<?php
//No direct access to the file
defined('_JEXEC') or die('Restricted access');

class ItineraryModelPayment extends JModelAdmin
{
	protected $text_prefix = 'COM_ITINERARY';
	
	public function __construct($config = array())
	{
		require_once(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php' );
		
                $_SESSION['parcoursMsgMax']=30;
                
		parent::__construct($config);
	}
	
	protected function canEditState($record)
	{
		$user = JFactory::getUser();
		
		return $user->authorise('core.edit.own','com_itinerary');
	}	
	
        protected function prepareTable($table)
	{
            $table->titre = htmlspecialchars($table->titre, ENT_QUOTES);
            $table->texte = htmlspecialchars($table->texte, ENT_QUOTES);
            $table->titre_illustrations = htmlspecialchars($table->titre_illustrations, ENT_QUOTES);

            if(isset($table->date_modification))
            {
                $table->date_modification = date("Y-m-d H:i:s");
            }
	}
        
	public function getTable($type='Subscription',$prefix='ItineraryTable',$config=array())
	{
		$table=JTable::getInstance($type,$prefix,$config);
			
		return $table;
	}
	
	public function getForm($data = array(), $loadData = true)
	{		
		$form = $this->loadForm('com_itinerary.subscription','subscription',array('control'=>'jform','load_data'=>$loadData));
		
		if (empty($form))
		{
                    return false;
		}
		return $form;
	}
	
	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_itinerary.edit.subscription.data', array());
		

		return $data;
	}
	
	protected function populateState()
	{	
            

            parent::populateState();
	}

        public function getAmount($codePromo,$subscriptionStr)
	{
            $pricing = $this->getPricing();
            
            $subscriptionsByDuration = $this->getSubscriptionsByDuration();
                        
            if(!empty($pricing)&&!is_null($subscriptionsByDuration))
            {
                $pricingArray=array();
                
                foreach($pricing as $item)
                {
                    $pricingArray[$item["duree"]][$item["clef_size"]]=$item["tarif"];
                }
 
                $subscriptionsByDurationArray=array("1"=>"0","3"=>"0","6"=>"0","12"=>"0");
                
                foreach($subscriptionsByDuration as $item)
                {
                    $subscriptionsByDurationArray[$item["duree"]]=$item["subscriptionQty"];
                }
                
                $subscriptionArray = explode("@",$subscriptionStr);
                
                $subscriptionRequest = array();
                
                $newSubscriptionArray = array();
                
                $amountHT=0;
                $promoValue=0;
                $promoValue= $this->getPromoValue($codePromo);
                $promoAmount=0;
                
                
                foreach($subscriptionArray as $item)
                {
                    $itemArray = explode("#",$item);
                    
                    $subscriptionsByDurationArray[$itemArray[1]]++;
                    
                    $totalPriceSubscription=$pricingArray[$itemArray[1]][$subscriptionsByDurationArray[$itemArray[1]]]*$itemArray[1];
                    
                    $amountHT+= $totalPriceSubscription;           
                    
                    if($promoValue==$itemArray[1]&&$promoAmount==0)
                    {
                        $promoAmount=$totalPriceSubscription;
                        $amountHT-=$promoAmount;
                    }
                    
                    array_push($itemArray,$totalPriceSubscription);
                    
                    array_push($newSubscriptionArray,implode("#",$itemArray));
                }
                
                $userCountry=$this->getUserCountry();
                
                $tvaRate=$this->getTvaRate($userCountry);
                
                $amountTTC=$amountHT*(1+$tvaRate);
                
                $newSubscriptionStr=implode("@",$newSubscriptionArray);
                
                return array("amountHT"=>$amountHT,"amountTTC"=>$amountTTC,"promoAmount"=>$promoAmount,"newSubscriptionStr"=>$newSubscriptionStr);
            }
            else{ return null; }
	}
        
        private function getPricing()
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query -> select('clef_size, duree, tarif');
            $query -> from($db->quoteName('#__itinerary_pricing'));    

            $db -> setQuery($query); 
            $result = $db -> loadAssocList();
                
            if(empty($result))
            {
                return array();
            }
            else
            {   
                
                return $result;
            }
        }
        
        private function getSubscriptionsByDuration()
        {
            $user = JFactory::getUser()->id;
            $today = date("Y-m-d");
            
            if (!empty($user))
            {
                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $query -> select('duree, COUNT(clef) as subscriptionQty');
                $query -> from($db->quoteName('#__itinerary_subscriptions'));
                $query -> where('clef_planificateur ='.(int)$user,'AND');
                $query -> where('date_debut <= "'.$today.'"','AND');
                $query -> where('date_fin >= "'.$today.'"','AND');
                $query -> group('duree');          
                $db -> setQuery($query);
                $result = $db -> loadAssocList();
                
                if(empty($result))
                {
                    return array();
                }
                else
                {
                    return $result;
                }
            }
            
            return array();
        }
        
        private function getPromoValue($codePromo=null)
        {
            if(!empty($codePromo))
            {
                $db=$this->getDbo();
                $query = $db->getQuery(true);
                $query->select('type,offre');
                $query->from('#__itinerary_promotions');
                $query->where('code_promo = "'.htmlentities($codePromo,ENT_QUOTES).'"');

                $db->setQuery($query);

                $result = $db->loadAssoc();  
                
                if(is_null($result))
                {
                    return 0;
                }
                else
                {
                    return $result["offre"];
                }
            }
            else
            {
                return 0;
            }
        }

        public function getUserCountry()
        {
            $user=JFactory::getUser()->id;
            
            if(!is_null($user))
            {
                $db=$this->getDbo();
                $query = $db->getQuery(true);
                $query->select('profile_value');
                $query->from('#__user_profiles');
                $query->where('user_id ='.(int)$user,'AND');
                $query->where('profile_key = "profile.country"');

                $db->setQuery($query);
                
                return $db->loadAssoc();
            }
            else
            {
                return array();
            }
        } 
        
        public function getTvaRate($userCountry=null)
        {
            if(!is_null($userCountry))
            {               
                $userCountry=$userCountry["profile_value"];

                $db=$this->getDbo();
                $query = $db->getQuery(true);
                $query->select('taux');
                $query->from('#__itinerary_tva');
                $query->where('pays ='.$userCountry.'');

                $db->setQuery($query);
                $result=$db->loadAssoc();

                if($result)
                {
                    return floatval($result["taux"]);
                }
                else
                {
                    return null;
                }
            }
            else
            {
                return null;
            }
        }
        
        public function getCurrency($userCountry=null)
        {
            if(!is_null($userCountry))
            {                
                $userCountry=$userCountry["profile_value"];;

                $db=$this->getDbo();
                $query = $db->getQuery(true);
                $query->select('currency');
                $query->from('#__itinerary_currency');
                $query->where('pays ='.$userCountry.'');

                $db->setQuery($query);
                $result=$db->loadAssoc();

                if($result)
                {
                    return $result["currency"];
                }
                else 
                {
                    return null;
                }
            }
            else
            {
                return null;
            }
        }

        public function getUserCusId()
	{
            $user=JFactory::getUser()->id;

            if($user!=null)
            {
                $db=$this->getDbo();
                $query=$db->getQuery(true);
                $query->select('id_cus_u');
                $query->from('#__users');
                $query->where('id ='.(int)$user);

                $db->setQuery($query);
                $result=$db->loadResult();

                return $result;
            }
            else
            {
                return null;
            }
	}
        
        public function setUserCusId($cusId=null)
	{
            $user=JFactory::getUser()->id;

            if(!empty($user) && !empty($cusId))
            {
                $db = $this->getDbo();
                $query = $db -> getQuery(true);
                $query->update('#__users');
                $query->set('id_cus_u = "'.$cusId.'"');
                $query->where('id ='.(int)$user);

                $db->setQuery($query);
                $db->execute();
            }
	}
        
        public function registerOrder($promoAmount=null,$amountHT=null,$amountTTC=null,$cgvAcceptanceValue=null,$subscriptionsList=null)
        {              
            if(!is_null($promoAmount)&&!is_null($amountHT)&&!is_null($amountTTC)&&!empty($cgvAcceptanceValue)&&!empty($subscriptionsList))
            { 
                $clef=$this->getOrderNb();
                $clef_planificateur=JFactory::getUser()->id;
                
                $userCountry=$this->getUserCountry();
                $tva_rate=$this->getTvaRate($userCountry);
                $currency=$this->getCurrency($userCountry);

                $db = $this->getDbo();
                
                $columns = array('clef','clef_planificateur','subscription_request','cgv_approved','promoAmount','prixHT','prixTTC','tva_rate','currency');
                $values = array($clef,$clef_planificateur,'"'.$subscriptionsList.'"',$cgvAcceptanceValue,$promoAmount,$amountHT,$amountTTC,$tva_rate,'"'.$currency.'"');
                
                $query = $db->getQuery(true);		
                $query->insert($db->quoteName('#__itinerary_orders'));
                $query->columns($db->quoteName($columns));
                $query->values(implode(',',$values));
                
                $db->setQuery($query);
                
                if($db->execute())
                {     
                    return $clef;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }
        }
        
        public function addRenewalDates($subscriptionsList=null,$renewals=null)
        {
            if(!empty($subscriptionsList)&&!empty($renewals))
            { 
                $newRenewalsArray=array();
                
                foreach($renewals as $renewal)
                {
                    $renewalArray=explode('_',$renewal);
                    
                    $newRenewalsArray[$renewalArray[0]]=$renewalArray[1];
                }

                if(!empty($newRenewalsArray))
                {
                    $newsubscriptionsArray=array();
                    
                    $subscriptionsArray=explode('@',$subscriptionsList);

                    
                    foreach($subscriptionsArray as $subscription)
                    {
                        $subscriptionArray=explode('#',$subscription);

                        array_push($subscriptionArray,$newRenewalsArray[intval($subscriptionArray[0])]);
                        
                        array_push($newsubscriptionsArray,implode('#',$subscriptionArray));
                    }
                    
                    if(!empty($newsubscriptionsArray))
                    {
                        return implode('@',$newsubscriptionsArray);
                    }
                }    
            }
            
            return $subscriptionsList;
        }
        
        protected function getOrderNb()
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->select('MAX(clef) as orderNb');
            $query->from($db->quoteName('#__itinerary_orders'));
            $db->setQuery($query);
            $result=$db->loadResult();

            if(!empty($result))
            {
                return (int)$result+1;        
            }
            else
            {
                return 29810611;
            }
        }
        
        public function activationOrder($orderKey=null)
        {              
            if(!empty($orderKey))
            {   
             
                $subscriptionsList = $this->getSubscriptionsList($orderKey);
                
                if($subscriptionsList!=false)
                {                    
                    if($this->registerSubscriptions($orderKey,$subscriptionsList))
                    {                        
                        if($this->itinerariesActivation($orderKey))
                        {
                            $this->setInvoiceNb($orderKey);
                            return true;
                        }
                        else { return false; }
                    }
                    else { return false; }
                }
                else { return false; }
            }
            else { return false; }
        }
        
        
        
        private function getSubscriptionsList($orderKey=null)
        {
            if(!empty($orderKey))
            {
                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $query->select('subscription_request');
                $query->from($db->quoteName('#__itinerary_orders'));
                $query->where('clef='.(int)$orderKey);
                $db->setQuery($query);
                $result=$db->loadResult();
                
                if(!empty($result))
                {
                    return $result;
                }
                else
                {
                    return false;
                }
            }                
        }
        
        private function registerSubscriptions($orderKey=null,$subscriptionsList=null)
        {
            if(!empty($orderKey)&&!empty($subscriptionsList))
            { 
                $subscriptionsArray=explode("@",$subscriptionsList);

                $registerSubscriptionKeys = array();
                
                foreach($subscriptionsArray as $subscription)
                {
                    $subscriptionArray = explode("#", $subscription);

                    $result= $this->setSubscription($orderKey,$subscriptionArray);
                    
                    if($result!=false)
                    {
                        $registerSubscriptionKeys[$subscriptionArray[0]]=$result;
                    }
                }   

                if(!empty($registerSubscriptionKeys))
                {                    
                    $this->setRegisterSubscriptionKeys($orderKey,implode('#',$registerSubscriptionKeys));
                }
                
                return true;
            }
            else{ return false; }
        }
        
        private function setSubscription($orderKey=null,$subscriptionArray=null)
        {
            if(!empty($orderKey)&&!empty($subscriptionArray))
            {
                $commandeNb=$orderKey;
                $clef_parcours=$subscriptionArray[0];
                $duree=$subscriptionArray[1];
                $price=$subscriptionArray[2];
                $startDate=null;
                
                if(isset($subscriptionArray[3]))
                {
                    $startDate=$subscriptionArray[3];
                }

                $clef_planificateur=JFactory::getUser()->id;
                
                if(is_null($startDate)||$startDate<date('Y-m-d'))
                {
                    $date_debut=date('Y-m-d');
                    $date_fin=date('Y-m-d',strtotime('+'.$duree.' month - 1 days'));
                }
                else
                {
                    $date_debut=date('Y-m-d',strtotime('+ 1 day',strtotime($startDate)));
                    $date_fin=date('Y-m-d',strtotime('+'.$duree.' month - 1 days',strtotime($date_debut)));
                }
                
                $db = $this->getDbo();
                
                $columns = array('commandeNb','clef_parcours','clef_planificateur','date_debut','date_fin','duree','prixHT');
                $values = array($commandeNb,$clef_parcours,$clef_planificateur,$db->quote($date_debut),$db->quote($date_fin),$duree,$price);
                
                $query = $db->getQuery(true);		
                $query->insert($db->quoteName('#__itinerary_subscriptions'));
                $query->columns($db->quoteName($columns));
                $query->values(implode(',', $values));

                $db->setQuery($query);
                $result = $db->execute();
                $result = $db->insertid();
                
                if(!empty($result))
                {   
                    return $result;
                }
                else
                {
                    return false;
                }
            }
        }
        
        private function setRegisterSubscriptionKeys($orderKey=null,$registrationKeys=null)
        {
            if(!empty($orderKey)&&!empty($registrationKeys))
            {
                $db = $this->getDbo();
                $query = $db -> getQuery(true);
                $query->update('#__itinerary_orders');
                $query->set('clef_subscriptions = "'.$registrationKeys.'"');
                $query->where('clef ='.(int)$orderKey);

                $db->setQuery($query);
                $db->execute();
            }
        }
        
        private function getRegisterSubscriptionKeys($orderKey=null)
        {
            if(!empty($orderKey))
            {
                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $query->select('clef_subscriptions');
                $query->from('#__itinerary_orders');
                $query->where('clef ='.(int)$orderKey);

                $db->setQuery($query);
                $result = $db->loadResult();
                if(!empty($result))
                {   
                    return $result;
                }
                else
                {
                    return false;
                }
            }
        }

        public function itinerariesActivation($orderKey=null)
        {
            $user=JFactory::getUser()->id;
             
            if(!empty($orderKey)&&!empty($user))
            {
                $subscriptionsList=$this->getRegisterSubscriptionKeys($orderKey);
                    
                if(!empty($subscriptionsList))
                {
                    $subscriptionsArray=explode("#",$subscriptionsList);

                    foreach($subscriptionsArray as $subscriptionId)
                    {
                        $this->itineraryActivation($subscriptionId); 
                    }
                    
                    $this->setItinerarySubscriptionPaid($user,$orderKey);
                    
                    return true;
                }
            }
            
            return false;
        }
        
        public function itineraryActivation($subscriptionId=null)
        {    
            $user=JFactory::getUser()->id;
            
            $itineraryId=$this->getItineraryIdFromSubscription($subscriptionId);
            $itineraryActivated=$this->getParcoursActivation($user,$itineraryId);
            
            if(!empty($itineraryId)&&!empty($user)&&!$itineraryActivated)
            {                
                $this->copyParcoursToProduction($user,$itineraryId);
		
                $this->copyMessagesToProduction($user,$itineraryId);
                
                $this->setParcoursActivation($user,$itineraryId);
            }
        }
        
        public function getParcoursActivation($user=null,$itineraryId=null)
	{

            if(!empty($user)&&!empty($itineraryId))
            {
                $db = $this->getDbo();
                $query = $db -> getQuery(true);
                $query->select('activation_planificateur');
                $query->from($db->quoteName('#__itinerary_parcours'));
                $query->where('clef ='.(int)$itineraryId, 'AND');
                $query->where('clef_planificateur ='.(int)$user);

                $db->setQuery($query);
                $result=$db->loadResult();

                if(!is_null($result))
                {
                        return $result;
                }
                else
                {
                        return null;
                }
            }
	}
        
        private function getItineraryIdFromSubscription($subscriptionId=null)
        {
            if(!empty($subscriptionId))
            {
                $db=$this->getDbo();
                $query=$db->getQuery(true);
                $query->select('clef_parcours');
                $query->from($db->quoteName('#__itinerary_subscriptions'));
                $query->where('clef='.(int)$subscriptionId);
                $db->setQuery($query);
                
                $result=$db->loadResult();
                
                if(!empty($result))
                {
                    return $result;
                }
                else{ return null; }
            }
            
            return null;
        }
        
        private function copyParcoursToProduction($user=null,$itineraryId=null)
	{  
            if(!empty($user)&&!empty($itineraryId))
            {
                //$this->deleteParcoursFromProduction($user,$idParcours);
                
                $db=$this->getDbo();
                $query=$db->getQuery(true);
                $query->select('clef,clef_planificateur,titre,description,titre_illustrations,illustrations,type_parcours,theme,langue,duree,payant,telechargeable,tarif,tva,currency,nombre_messages,note,nombre_commentaires,latitude,longitude');
                $query->from($db->quoteName('#__itinerary_parcours'));
                $query->where('clef_planificateur = '.(int)$user, 'AND');
                $query->where('clef = '.(int)$itineraryId);

                $db->setQuery($query);
                $values=$db->loadAssoc();
                                
                if(!empty($values))
                {
                    $columns=array('clef','clef_planificateur','titre','description','titre_illustrations','illustrations','type_parcours','theme','langue','duree','payant','telechargeable','tarif','tva','currency','nombre_messages','note','nombre_commentaires','latitude','longitude');

                    $db = ItineraryHelper::connectToAnotherDB();
                    $query = $db->getQuery(true);
                    $query->insert($db->quoteName('itinerary_parcours_prod'));
                    $query->columns($db->quoteName($columns));
                    $query->values( '"'.implode('","',$values).'"');
                    $db->setQuery($query);

                    if($db->execute())
                    {		
                        ItineraryHelper::setItineraryPinsFieldInProduction($this, $itineraryId);
                        ItineraryHelper::putImageInProduction($values['illustrations']);

                        if($values['payant'] == 1)
                        {
                            if(ItineraryHelper::deleteDestinationFromProduction($user)==true)
                            {
                                ItineraryHelper::copyDestinationToProduction($user);
                            }	
                        }

                        return true;
                    }
                    else
                    {
                        return false;
                    }
                }
            }
	}
        
        private function copyMessagesToProduction($user=null,$itineraryId=null)
	{
            if(!empty($user) && !empty($itineraryId))
            {
                //$this->deleteMessagesFromProduction($user,$idParcours);

                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $query->select('clef,clef_parcours,titre,texte,titre_illustrations,illustrations');
                $query->from($db->quoteName('#__itinerary_messages'));
                $query->where('clef_planificateur = '.(int) $user, 'AND');
                $query->where('clef_parcours = '.(int) $itineraryId);

                $db->setQuery($query);
                $messagesParcours=$db->loadAssocList();

                if(!empty($messagesParcours))
                {
                    foreach($messagesParcours as $values)
                    {
                        $columns=array('clef','clef_parcours','titre','texte','titre_illustrations','illustrations');

                        $db = ItineraryHelper::connectToAnotherDB();
                        $query = $db->getQuery(true);
                        $query->insert($db->quoteName('itinerary_messages_prod'));
                        $query->columns($db->quoteName($columns));
                        $query->values( '"'.implode('","',$values).'"');

                        $db->setQuery($query);
                        $db->execute();

                        ItineraryHelper::putImageInProduction($values['illustrations']);
                    }
                }
            }
	}
        
        private function setParcoursActivation($user=null,$itineraryId=null)
	{
            if(!empty($user) && !empty($itineraryId))
            {
                $db = $this->getDbo();
                $query = $db -> getQuery(true);
                $query->update('#__itinerary_parcours');
                $query->set('activation_planificateur = 1');
                $query->where('clef ='.(int)$itineraryId, 'AND');
                $query->where('clef_planificateur ='.(int)$user);

                $db->setQuery($query);
                $db->execute();
            }
	}
        
        private function setItinerarySubscriptionPaid($user=null,$orderKey=null)
        {
            if(!empty($user)&&!empty($orderKey))
            {
                $db=$this->getDbo();
                $query=$db->getQuery(true);
                $query->update('#__itinerary_orders');
                $query->set('paid = 1');
                $query->where('clef ='.(int)$orderKey, 'AND');
                $query->where('clef_planificateur ='.(int)$user);

                $db->setQuery($query);
                $db->execute();
            }
        }
        
        private function getSubscriptionsFromOrder($orderKey=null)
        {
            if(!empty($orderKey))
            {
                $db=$this->getDbo();
                $query=$db->getQuery(true);
                $query->select('s.clef_parcours,s.date_debut, s.date_fin, s.duree, s.prixHT, p.titre');
                $query->from($db->quoteName('#__itinerary_subscriptions','s'));
                $query->join('INNER', $db->quoteName('#__itinerary_parcours','p').'ON('.$db->quoteName('s.clef_parcours').'='.$db->quoteName('p.clef').')');
                $query->where($db->quoteName('commandeNb').'='.(int)$orderKey);
                $db->setQuery($query);
                $result=$db->loadAssocList();
                
                if(empty($result))
                {
                    return array();
                }
                else
                {
                    return $result;
                }
                
            }
            
            return array();
        }
        
        private function getSubscriptionsFromOrder2($orderKey)
        {
        
            if(!empty($orderKey))
            {
                $db=$this->getDbo();
                
                $query=$db->getQuery(true);
                $query->select('subscription_request');
                $query->from($db->quoteName('#__itinerary_orders'));
                $query->where($db->quoteName('clef').'='.(int)$orderKey);
                $db->setQuery($query);
                $result=$db->loadResult();

                
                if(!empty($result))
                {
                    $dataRequestArray=array();
                            
                    $requestsArray=explode('@',$result);
                    
                    
                    foreach($requestsArray as $request)
                    {
                        $dataRequestItem=array();
                        $requestArray=explode('#',$request);
                        
                        $query=$db->getQuery(true);
                        $query->select('titre');
                        $query->from($db->quoteName('#__itinerary_parcours'));
                        $query->where($db->quoteName('clef').'='.(int)$requestArray[0]);
                        $db->setQuery($query);
                        $result=$db->loadResult();
                        
                        if(empty($result))
                        {
                            $result="";
                        }
                        
                        $dataRequestItem=array('clef'=>$requestArray[0],'duree'=>$requestArray[1],'prixHT'=>$requestArray[2],'titre'=>$result);
                        
                        array_push($dataRequestArray,$dataRequestItem);
                    }

                    if(empty($dataRequestArray))
                    {
                        return array();
                    }
                    else
                    {
                        return $dataRequestArray;
                    }
                }
            }
            
            return array();
        }
        
  
        public function getDocument($orderKey,$priceHT,$priceTTC,$promoAmount,$typePaiementLabel,$documentType="Invoice")
	{                          
            if(!empty($orderKey)&&!is_null($priceHT)&&!is_null($priceTTC)&&!is_null($promoAmount)&&!empty($typePaiementLabel)&&!empty($documentType))
            {
                $cpyName=JText::_('COM_ITINERARY_CPY_NAME');
                $cpyAddress1=JText::_('COM_ITINERARY_CPY_ADRESS_1');
                $cpyPostalCode=JText::_('COM_ITINERARY_CPY_ADRESS_2');
                $cpyCity=JText::_('COM_ITINERARY_CPY_ADRESS_3');
                $cpyCountry=JText::_('COM_ITINERARY_CPY_ADRESS_4');
                $cpySIRET=JText::_('COM_ITINERARY_CPY_SIRET');
                $cpyTVA=JText::_('COM_ITINERARY_CPY_NB_TVA');

                $clientProfile=ItineraryHelper::getUserProfile();		

                $clientName=JFactory::getUser()->username;
                $clientAddress1=$clientProfile['profile.address1'];
                $clientPostalCode=$clientProfile['profile.postal_code'];
                $clientCity=$clientProfile['profile.city'];
                $clientCountry=JText::_($clientProfile['profile.country']);


                $commandeDateLabel=JText::_('COM_ITINERARY_ORDER_DATE').' :';
                $todayDate=date('d/m/Y');

                $clientReferenceLabel=JText::_('COM_ITINERARY_INVOICE_CLIENT_REFERENCE').' :';

                if($this->getUserReference())
                {
                        $clientReference=$this->getUserReference();
                }
                else
                {
                        $clientReference=$this->setUserReference();
                }

                $commandeNbLabel=JText::_('COM_ITINERARY_ORDER_NUMBER').' :';
                $commandeNb='C-'.$orderKey;

                $factureNbLabel=JText::_('COM_ITINERARY_INVOICE_NUMBER').' :';
                $factureNb=$this->getInvoiceNb($orderKey);
                $factureNb='F-'.$factureNb;

                $line1=JText::_('COM_ITINERARY_CPY_NAME').' - ';
                $line1.=JText::_('COM_ITINERARY_CPY_ADRESS_1').' - ';
                $line1.=JText::_('COM_ITINERARY_CPY_ADRESS_2').' '.JText::_('COM_ITINERARY_CPY_ADRESS_3').' - ';
                $line1.=JText::_('COM_ITINERARY_CPY_ADRESS_4');

                $line2=JText::_('COM_ITINERARY_CPY_SIRET').' - '.JText::_('COM_ITINERARY_CPY_NB_TVA');
                $line3=JText::_('COM_ITINERARY_CPY_FORME_CAPITAL_SOCIAL');
                $line4=JText::_('COM_ITINERARY_SUBSCRIPTION_IBAN_ORDER_EXPLANATION_4').' - '.JText::_('COM_ITINERARY_SUBSCRIPTION_IBAN_ORDER_EXPLANATION_5');
                $line5=JText::_('COM_ITINERARY_CPY_TEL').' - '.JText::_('COM_ITINERARY_CPY_SITE_WEB');
                       
                setlocale(LC_MONETARY,"fr_FR");

                $userCountry = $this->getUserCountry();

                $tvaRate = $this->getTvaRate($userCountry);

                $currency = $this->getCurrency($userCountry);
                
                if($documentType=="Invoice")
                { 
                    $priceTTCLabel=JText::_('COM_ITINERARY_INVOICE_TOTAL_TTC_LABEL');
                }
                else
                {
                    $priceTTCLabel=JText::_('COM_ITINERARY_FIELD_TOTAL_AMOUNT_LABEL');
                }

                $priceHTLabel=JText::_('COM_ITINERARY_INVOICE_TOTAL_HT_LABEL');
                $TVA=$priceTTC-$priceHT;

                $priceTTC=money_format('%.2n',$priceTTC);
                $priceHT=money_format('%.2n',$priceHT);


                $TVALabel=sprintf(JText::_("COM_ITINERARY_FIELD_TVA_LABEL"),$tvaRate*100);
                $TVA=money_format('%.2n',$TVA);

                /*$priceTTC += " " . $currency;
                $priceHT += " " . $currency;
                $TVA += " " . $currency;*/

                if($typePaiementLabel=="card"&&$documentType=="Invoice")
                { 
                    $typePaiementLabel=JText::_('COM_ITINERARY_INVOICE_PAYMENT_TYPE_CARD');
                }
                else if($typePaiementLabel=="iban"&&$documentType=="Invoice")
                {
                    $typePaiementLabel=JText::_('COM_ITINERARY_INVOICE_PAYMENT_TYPE_IBAN');
                }
                else if($typePaiementLabel=="iban"&&$documentType=="Order")
                {
                    $typePaiementLabel=JText::_('COM_ITINERARY_ORDER_PAYMENT_TYPE_IBAN');
                }
                else if($typePaiementLabel=="gratuit"&&$documentType=="Invoice")
                {
                    $typePaiementLabel=JText::_('COM_ITINERARY_INVOICE_FREE_OF_PAYMENT');
                }

                //remplissage modèle facture		
                //Including PHPExcel library and creation of its object
                define('__ROOT__',$_SERVER['DOCUMENT_ROOT'] );
                require_once(__ROOT__.'/libraries/PHPExcel/PHPExcel.php');

                $temp=str_replace('www','temp',__ROOT__).'/';

                $titreSheet=JText::_('COM_ITINERARY_INVOICE_LABEL');

                if($documentType=="Invoice")
                { 
                    $titreSpreadSheet=JText::_('COM_ITINERARY_INVOICE_TITLE').'-'.$factureNb;
                }
                else
                {
                    $titreSpreadSheet=JText::_('COM_ITINERARY_ORDER_TITLE').'-'.$commandeNb;
                }

                $phpExcel = new PHPExcel;

                // Setting font to Arial Black
                $phpExcel->getDefaultStyle()->getFont()->setName('Tahoma');

                // Setting font size to 14
                $phpExcel->getDefaultStyle()->getFont()->setSize(14);
                $phpExcel ->getProperties()->setTitle(JText::_('COM_ITINERARY_INVOICE_TITLE'));
                $phpExcel ->getProperties()->setCreator("NACTECH");

                $sheet = $phpExcel ->getActiveSheet();
                $sheet->setTitle($titreSheet);

                $cpyArray = array($cpyName,$cpyAddress1,$cpyPostalCode." ".$cpyCity,$cpyCountry,$cpySIRET,$cpyTVA);
                $cpyArray = array_chunk($cpyArray, 1);
                

                $clientArray = array($clientName,$clientAddress1,$clientPostalCode." ".$clientCity,$clientCountry);
                $clientArray = array_chunk($clientArray, 1);
                

                if($documentType=="Invoice")
                { 
                    $referenceLabelArray = array(JText::_('COM_ITINERARY_INVOICE_LABEL'),$clientReferenceLabel,$commandeDateLabel,$commandeNbLabel,$factureNbLabel);
                }
                else
                {
                    $referenceLabelArray = array(JText::_('COM_ITINERARY_ORDER_LABEL'),$clientReferenceLabel,$commandeDateLabel,$commandeNbLabel,'');
                }

                $referenceLabelArray = array_chunk($referenceLabelArray, 1);
                
                

                if($documentType=="Invoice")
                { 
                    $referenceArray = array('',$clientReference,$todayDate,$commandeNb,$factureNb);
                }
                else
                {
                    $referenceArray = array('',$clientReference,$todayDate,$commandeNb,'');
                }

                $referenceArray = array_chunk($referenceArray, 1);
               
                $sheet->fromArray($cpyArray,NULL,'A6');
                $sheet->fromArray($clientArray,NULL,'E13');
                $sheet->fromArray($referenceLabelArray,NULL,'A17');
                $sheet->fromArray($referenceArray,NULL,'B17');

    //#############################################################################################################

                $productReferenceLabel=JText::_('COM_ITINERARY_INVOICE_PRODUCT_REFERENCE');
                $productIdentifiantLabel=JText::_('COM_ITINERARY_FIELD_PARCOURS_ID_LABEL');
                $titreLabel=JText::_('COM_ITINERARY_FIELD_PARCOURS_TITLE_DESC');
                $productDescriptionLabel=JText::_('COM_ITINERARY_INVOICE_PRODUCT_DESCRIPTION_LABEL');
                $priceLabel=JText::_('COM_ITINERARY_FIELD_PRIX_LABEL');
                
                $dataLabelArray=array($productIdentifiantLabel,$productReferenceLabel,$titreLabel,$productDescriptionLabel,$priceLabel);
                $sheet->fromArray($dataLabelArray,NULL,'A23');
                
    //start loop
                $tableDataArray=array();
                
                if($documentType=="Invoice")
                { 
                    $subscriptionsList=$this->getSubscriptionsFromOrder($orderKey);
                }
                else if($documentType=="Order")
                {
                    $subscriptionsList=$this->getSubscriptionsFromOrder2($orderKey);
                }
                
                $msgQtyMax=$_SESSION['parcoursMsgMax'];
                
                $rowNb=23;
                
                foreach($subscriptionsList as $subscription)
                {
                    $rowNb++;
                    
                    $productIdentifiant=(4*(int)$subscription['clef_parcours']+426957183);
                    $productReference='PA-'.$subscription['duree'].'-'.$msgQtyMax;
                    
                    $titreParcours=htmlspecialchars_decode($subscription['titre']);
                    
                    if($documentType=="Invoice")
                    { 
                        $productDescription=sprintf(JText::_('COM_ITINERARY_INVOICE_PRODUCT_SUBSCRIPTION_DESCRIPTION'), $subscription['duree'],$msgQtyMax, date("d/m/Y",strtotime($subscription['date_debut'])), date("d/m/Y",strtotime($subscription['date_fin'])));
                    }
                    else
                    {
                        $productDescription=sprintf(JText::_('COM_ITINERARY_INVOICE_PRODUCT_SUBSCRIPTION_DESCRIPTION_NO_DATE'), $subscription['duree'], $msgQtyMax);
                    }
                    
                    if($documentType=="Invoice")
                    {
                        $prix=$subscription['prixHT'];
                    }
                    else
                    {
                        $prix=$subscription['prixHT']*(1+$tvaRate);
                    }
                        
                    
                    $prix=money_format('%.2n',$prix);
                    
                    $rowDataArray=array($productIdentifiant,$productReference,$titreParcours,$productDescription,$prix);
                    
                    $cellToPaste="A".$rowNb;
                    $sheet->fromArray($rowDataArray,NULL,$cellToPaste);
                }
                
                $cellToPaste='A24:E'.$rowNb;
                
                $sheet->setShowGridLines(false);
                $sheet->getStyle($cellToPaste)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($cellToPaste)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $sheet->getStyle($cellToPaste)->getBorders()->applyFromArray(
                                        array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM,'color' => array('rgb' => '000000'))));
                
                $cellToPaste='D24:D'.$rowNb;
                $sheet->getStyle($cellToPaste)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY);
                $cellToPaste='E24:E'.$rowNb;
                $sheet->getStyle($cellToPaste)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                
    //end loop
                $rowNb++;
                
                $totalLabel=JText::_('COM_ITINERARY_FIELD_TOTAL_AMOUNT_LABEL');
                
                $promoLabel="";
                $promo="";
                
                if(!empty($promoAmount)&&$documentType=="Invoice")
                {
                    $promoLabel=JText::_('COM_ITINERARY_INVOICE_PROMO_LABEL');
                    $promo=-$promoAmount;
                    $promo=money_format('%.2n',$promo);
                }
                else if(!empty($promoAmount)&&$documentType=="Order")
                {
                    $promoLabel=JText::_('COM_ITINERARY_INVOICE_PROMO_LABEL');
                    $promo=-$promoAmount*(1+$tvaRate);
                    $promo=money_format('%.2n',$promo);
                }
                
                if($documentType=="Invoice")
                {
                    $dataLabelArray = array($promoLabel,$priceHTLabel,$TVALabel,$priceTTCLabel);
                    $dataArray = array($promo,$priceHT,$TVA,$priceTTC);
                }
                else
                {
                    $dataLabelArray = array($promoLabel,'','',$totalLabel);
                    $dataArray = array($promo,'','',$priceTTC);
                }
                

                $dataLabelArray = array_chunk($dataLabelArray, 1);
                $dataArray = array_chunk($dataArray, 1);
                
                $cellToPaste="D".$rowNb;
                $sheet->fromArray($dataLabelArray,NULL,$cellToPaste);
                
                $cellToPaste="E".$rowNb;
                $sheet->fromArray($dataArray,NULL,$cellToPaste);
                
                $cellToPaste="D".$rowNb.':D'.((int)$rowNb+3);
                $sheet->getStyle($cellToPaste)->getFont()->setBold('true');
                $cellToPaste="D".$rowNb.':E'.((int)$rowNb+3);
                $sheet->getStyle($cellToPaste)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                
                $rowNb++;
                $rowNb++;
                $rowNb++;
                $rowNb++;
    //#############################################################################################################
                $cellToPaste="C".$rowNb.':D'.$rowNb;
                $sheet->mergeCells($cellToPaste);
                
                $cellToPaste="C".$rowNb;
                $sheet->getCell($cellToPaste)->setValue($typePaiementLabel);
                $sheet->getRowDimension($cellToPaste)->setRowHeight(37);
                
                
                        
                $rowNb++;

                
                
                $sheet->getStyle('A2:E2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('a61f21');
                $sheet->getStyle('A3:E3')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('bf383a');
                $sheet->getStyle('E13:E16')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('A6:B21')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                
                $sheet->getStyle('A6')->getFont()->setBold('true');
                $sheet->getStyle('A17')->getFont()->setBold('true');
                $sheet->getStyle('A17')->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
                $sheet->getStyle('E13')->getFont()->setBold('true');

                $sheet->getStyle('A23:E23')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A23:E23')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);
                $sheet->getStyle('A23:E23')->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
                $sheet->getStyle('A23:E23')->getFont()->setBold('true');

                $cellToPaste='A'.$rowNb.':E'.$rowNb;
                $sheet->mergeCells($cellToPaste);
                
                $dataFooter = $line1.' '.$line2.' '.$line3.' '.$line4.' '.$line5;
                $cellToPaste="A".$rowNb;
                $sheet->getCell($cellToPaste)->setValue($dataFooter);
                $sheet->getRowDimension($rowNb)->setRowHeight(50);
                $sheet->getStyle($cellToPaste)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($cellToPaste)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);
                //#a61f21 -83.58
                //#bf383a -62.4

                $sheet->getColumnDimension('A')->setWidth(30);
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(60);
                $sheet->getColumnDimension('E')->setWidth(20);

                $sheet->getRowDimension('2')->setRowHeight(42);
                $sheet->getRowDimension('3')->setRowHeight(31);

                $objDrawing = new PHPExcel_Worksheet_Drawing();
                $objDrawing->setName('Logo');
                $objDrawing->setDescription('Logo');
                $objDrawing->setPath('/images/logos/Toossy_logo.jpg',false);
                $objDrawing->setHeight(100);
                $objDrawing->setWorksheet($sheet);
                $objDrawing->setCoordinates('A2');

                $printOptions=$sheet->getPageSetup();
                $printOptions->setFitToWidth(1);
                $printOptions->setFitToHeight(0);
                $printOptions->setHorizontalCentered(true);
                $printOptions->setVerticalCentered(false);	

                require_once __ROOT__ . '/libraries/PDF/mPDF/mpdf.php';

                //set PDF renderer
                $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                $rendererLibrary = 'mPDF';
                $rendererLibraryPath = __ROOT__.'/libraries/PDF/' . $rendererLibrary;

                if(!PHPExcel_Settings::setPdfRenderer($rendererName,$rendererLibraryPath))
                {
                    die('Please set the $rendererName and $rendererLibraryPath values' .
                    PHP_EOL .' as appropriate for your directory structure');
                }
                
                $pdfWriter = new PHPExcel_Writer_PDF($phpExcel,'PDF');
                $pdfWriter->setSheetIndex(0);

                $pdfWriter->save($temp.$titreSpreadSheet.'.pdf');

                $phpExcel->disconnectWorksheets();

                unset($phpExcel);

                return array($temp,$titreSpreadSheet.'.pdf');
            }
	}
        
        
        
        
        
        
        public function sendDocument($document=null,$orderKey=null, $emailType="Invoice")
	{            
            if(!is_null($document)&&!is_null($orderKey)&&!is_null($emailType))
            {                    
                $mailer = JFactory::getMailer();			

                $sender=array('noreply@nactustechnology.com','Nactus Technology');	

                $mailer->setSender($sender);

                $user = JFactory::getUser();

                $recipient = $user->email;

                $mailer->addRecipient($recipient);
                //$mailer->addBcc('comptabilite@nactustechnology.com');

                /*preg_match('#F-[0-9]*#',$document[1],$numberFacture);
                preg_match('#C-[0-9]*#',$document[1],$numberCommande);*/

                if($emailType=="Invoice")
                {
                    $invoiceNb=$this->getInvoiceNb($orderKey); 
                    $mailer->setSubject(JText::_('COM_ITINERARY_INVOICE_EMAIL_TITLE').'-F-'.$invoiceNb);

                    $body = JText::_('COM_ITINERARY_INVOICE_EMAIL_CONTENT');
                    $mailer->setBody($body);		
                }
                else
                {
                    $mailer->setSubject(JText::_('COM_ITINERARY_ORDER_EMAIL_TITLE').'-C-'.$orderKey);

                    $body = JText::_('COM_ITINERARY_ORDER_EMAIL_CONTENT');
                    $mailer->setBody($body);		
                }


                // Optional file attached
                $CGV='Nactus_Technology-CGV.pdf';
                $notice='TOOSSY-Notice_d\'utilisation.pdf';

                $attachment=array($document[0].$document[1],$document[0].$CGV,$document[0].$notice);

                $mailer->addAttachment($attachment);


                $send = $mailer->Send();		

                unlink($document[0].$document[1]);
                
                return true;
            }
            
            return false;
	}
        
        protected function getInvoiceNb($orderKey=null)
        {
            $user=JFactory::getUser()->id;
            
            if(!empty($user)&&!empty($orderKey))
            {                
                $db = $this->getDbo();
                
                $query = $db->getQuery(true);
                $query->select('factureNb');
                $query->from($db->quoteName('#__itinerary_orders'));
                $query->where('clef='.(int)$orderKey,'AND');
                $query->where('clef_planificateur ='.(int)$user);
                
                $db->setQuery($query);
                $result=$db->loadResult();
                
                if(empty($result))
                {
                    return null;     
                }
                else
                {
                    return $result;
                }
            }
        }
        
        protected function setInvoiceNb($orderKey=null)
        {
            $user=JFactory::getUser()->id;
        
            if(!empty($user)&&!empty($orderKey))
            {
                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $query->select('MAX(factureNb) as invoiceNb');
                $query->from($db->quoteName('#__itinerary_orders'));
                $db->setQuery($query);
                $result=$db->loadResult();
                
                if(!empty($result))
                {
                    $factureNb = (int)$result+1;        
                }
                else
                {
                    $factureNb = 29860729;
                }
                
                $query = $db->getQuery(true);
                $query->update('#__itinerary_orders');
                $query->set('factureNb = "'.(int)$factureNb.'"');
                $query->where('clef ='.(int)$orderKey,'AND');
                $query->where('clef_planificateur ='.(int)$user);
                $db->setQuery($query);
                $db->execute();
            }  
        }
        
        public function getUserReference()
	{
            $user=JFactory::getUser()->id;

            if($user!=null)
            {
                $db=$this->getDbo();
                $query=$db->getQuery(true);
                $query->select('ref_com_u');
                $query->from('#__users');
                $query->where('id ='.(int)$user);			

                $db->setQuery($query);

                $result=$db->loadResult();

                return $result;
            }
            else
            {
                return null;
            }
	}


	
	
	public function setUserReference()
	{
            $user=JFactory::getUser()->id;
            $referenceUser = (int)(date('Y').$user)+11111111;

            if(!empty($user) && !empty($referenceUser))
            {
                $db = $this->getDbo();
                $query = $db -> getQuery(true);
                $query->update('#__users');
                $query->set('ref_com_u = "'.$referenceUser.'"');
                $query->where('id ='.(int)$user);

                $db->setQuery($query);
                $db->execute();
            }

            return $referenceUser;
	}
        
//###############################################################################################################################################################################
//###############################################################################################################################################################################        
//###############################################################################################################################################################################
        //###############################################################################################################################################################################
//###############################################################################################################################################################################        
//###############################################################################################################################################################################
        //###############################################################################################################################################################################
//###############################################################################################################################################################################        
//###############################################################################################################################################################################
        //###############################################################################################################################################################################
//###############################################################################################################################################################################        
//###############################################################################################################################################################################//###############################################################################################################################################################################
//###############################################################################################################################################################################        
//###############################################################################################################################################################################
        //###############################################################################################################################################################################
//###############################################################################################################################################################################        
//###############################################################################################################################################################################
        
        //###############################################################################################################################################################################
//###############################################################################################################################################################################        
//###############################################################################################################################################################################
        
        //###############################################################################################################################################################################
//###############################################################################################################################################################################        
//###############################################################################################################################################################################
        //###############################################################################################################################################################################
//###############################################################################################################################################################################        
//###############################################################################################################################################################################
        //###############################################################################################################################################################################
//###############################################################################################################################################################################        
//###############################################################################################################################################################################

	
	
	
	
	/*public function getSubscriptionMsgLimit($idParcours=null)
	{
            $user=JFactory::getUser()->id;

            if(!empty($idParcours)&&!empty($user))
            {
                $today=date('Y-m-d');

                $db = $this->getDbo();
                $query = $db->getQuery(true);

                $query -> select('sb.clef_parcours_size');
                $query -> from('#__itinerary_subscriptions AS sb');
                $query->select('ps.max')
                        ->join('INNER', $db->quoteName('#__itinerary_parcours_size') . ' AS ps ON ps.clef = sb.clef_parcours_size');
                $query -> where('sb.clef_parcours ='.(int)$idParcours,'AND');
                $query -> where('sb.clef_planificateur ='.(int)$user,'AND');
                $query -> where('sb.date_debut <= "'.$today.'"','AND');
                $query -> where('sb.date_fin >= "'.$today.'"');

                $db -> setQuery($query);

                $result = $db->loadAssoc();
                $result = $result['max'];		

                if(!is_null($result))
                {
                    return $result;
                }
                else
                {
                    return null;
                }
            }
            else
            {
                return null;
            }
	}*/
	
	/*public function updateDataInProduction($newData=null,$tableName=null)
	{		
            if(!empty($newData)&&!empty($tableName)&&$this->getParcoursActivation()==1)
            {
                $table=$this->getTable($tableName);

                $key = $table->getKeyName();
                $pk = (!empty($newData[$key])) ? $newData[$key] : (int) $this->getState($this->getName() . '.id');

                $newMsgId=null;

                if($pk>0)
                {
                    $table->load($pk);

                    // Bind the data.
                    if (!$table->bind($newData))
                    {
                        $this->setError($table->getError());

                        return false;
                    }

                    // Prepare the row for saving
                    $this->prepareTable($table);

                    // Store the data.
                    if (!$table->store())
                    {
                        $this->setError($table->getError());

                        return false;
                    }

                    if(!is_null($newMsgId))
                    {
                        $table->clef=$newMsgId;
                    }

                    // Clean the cache.
                    $this->cleanCache();
                }
                else
                {
                    $user=JFactory::getUser()->id;
                    $idParcours=JFactory::getApplication()->getUserState('idParcours');

                    $db = $this->getDbo();

                    $query = $db -> getQuery(true);
                    $query->select('clef,clef_parcours,ordering,titre,texte,titre_illustrations,illustrations,latitude,longitude');
                    $query->from('#__itinerary_messages');
                    $query->where('clef_planificateur = '.(int) $user,'AND');
                    $query->where('clef_parcours = '.(int) $idParcours,'AND');
                    $query->order('date_creation DESC');

                    $db->setQuery($query);
                    $newData=$db->loadAssoc();

                    $newData['titre'] = htmlspecialchars($newData['titre'], ENT_QUOTES);
                    $newData['texte'] = htmlspecialchars($newData['texte'], ENT_QUOTES);
                    $newData['titre_illustrations'] = htmlspecialchars($newData['titre_illustrations'], ENT_QUOTES);

                    $columns=array('clef','clef_parcours','ordering','titre','texte','titre_illustrations','illustrations','latitude','longitude');

                    $db = $this->getDbo();
                    $query = $db->getQuery(true);
                    $query->insert($db->quoteName('#__itinerary_messages_prod'));
                    $query->columns($db->quoteName($columns));
                    $query->values( '"'.implode('","',$newData).'"');

                    $db->setQuery($query);

                    $db->execute();
                }
            }
	}*/
	
	
	
	
	

	
        
	
	
	

    
        
        /*protected function defineOrderNb()
        {
            $user=JFactory::getUser()->id;
            $idParcours=JFactory::getApplication()->getUserState('idParcours');
        
            if(!empty($user) && !empty($idParcours))
            {
                $today = date('Y-m-d');

                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query->select('MAX(commandeNb) as orderNb');
                $query->from($db->quoteName('#__itinerary_subscriptions'));
                $db->setQuery($query);
                $result=$db->loadResult();
                
                if(!empty($result))
                {
                    $commandeNb = (int)$result+1;        
                }
                else
                {
                    $commandeNb = 29810611;
                }
                
                return $commandeNb;
            }
            
            return 'null';
        }*/
        
        /*protected function setOrderNb()
        {
            $user=JFactory::getUser()->id;
            $idParcours=JFactory::getApplication()->getUserState('idParcours');
        
            if(!empty($user) && !empty($idParcours))
            {
                $today = date('Y-m-d');

                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query->select('MAX(commandeNb) as orderNb');
                $query->from($db->quoteName('#__itinerary_subscriptions'));
                $db->setQuery($query);
                $result=$db->loadResult();
                
                if(!empty($result))
                {
                    $commandeNb = (int)$result+1;        
                }
                else
                {
                    $commandeNb = 29810611;
                }
                
                $query = $db->getQuery(true);
                $query->update('#__itinerary_subscriptions');
                $query->set('commandeNb = "'.$commandeNb.'"');
                $query -> where('clef_parcours ='.(int)$idParcours,'AND');
                $query -> where('clef_planificateur ='.(int)$user,'AND');
                $query -> where('date_debut <= "'.$today.'"','AND');
                $query -> where('date_fin >= "'.$today.'"'); 
                $db -> setQuery($query);
                $db->execute();
            }
                
        }*/
        
        /*protected function getOrderNb()
	{
		$user = JFactory::getUser()->id;
		$idParcours = JFactory::getApplication()->getUserState('idParcours');

		if (!empty($user) && !empty($idParcours))
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('commandeNb');
			$query->from($db->quoteName('#__itinerary_subscriptions'));
			$query->where('clef_planificateur = '.(int) $user, 'AND');
			$query->where('clef_parcours = '.(int) $idParcours);
			$query->order('clef DESC');
			$db->setQuery($query);
			$result=$db->loadResult();
                        
                        //$result = date("Y").date("m").sprintf('%04d',$result);
                        
			return $result;
		}
		else
		{
			return false;
		}
	}*/
        
        

        /*private function userSubscriptionQuantity($duree=null)
        {
            if(isset($_SESSION['renewalDate']))
            {
                $researchedDate = $_SESSION['renewalDate'];
            }
            else
            {
                $researchedDate = date('Y-m-d');
            }
            
            
            
            $user = JFactory::getUser()->id;
            
            if (!empty($user) && !empty($duree) )
            {
                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $query -> select('COUNT(clef) as subscriptionQty');
                $query -> from($db->quoteName('#__itinerary_subscriptions'));
                $query -> where('clef_planificateur ='.(int)$user,'AND');
                $query -> where('date_debut <= "'.$researchedDate.'"','AND');
                $query -> where('date_fin >= "'.$researchedDate.'"','AND'); 
                $query -> where('duree ='.(int)$duree); 
                $query -> order('clef DESC');
                $db -> setQuery($query);
                $result = $db -> loadResult();
                
                if(empty($result))
                {
                    return 0;
                }
                else
                {
                    return $result;
                }
            }
            
            return 0;
        }*/	
}