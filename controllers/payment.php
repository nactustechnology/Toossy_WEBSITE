<?php

//No direct access to the file
defined('_JEXEC') or die;

class ItineraryControllerPayment extends JControllerForm
{
	public function __construct()
	{
		parent::__construct();
                $this->registerTask('paymentRooting','paymentRooting');
		$this->registerTask('setOrder','setOrder');
                $this->registerTask('payment','payment');
                $this->registerTask('order','order');
	}
	
	protected function allowEdit($data = array(), $key='clef')
	{
		return parent::allowEdit($data, $key);
	}
	
	public function getModel($name = 'Payment', $prefix = 'ItineraryModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);		

		return $model;
	}
	
	public function edit($key = null, $urlVar = null)
	{
		return false;
	}
        
        public function testinvoicing()
        {
            $model = $this->getModel();
            
            $invoice=$model->getDocument('29810615','516','619.20','0','iban','Order');
            $model->sendDocument($invoice,'29810615','Order');
            
            //$invoice=$model->getDocument('29810611','0','0','99','gratuit','Invoice');
            //$model->sendDocument($invoice,'29810611');
            
            $this->setRedirect(JRoute::_('index.php?option=com_itinerary&view=itineraries&layout=default'));
        }
        
        public function paymentRooting()
        {            
            $app=JFactory::getApplication(); 
            
            /*$app->setUserState('test1',null);
            $app->setUserState('test2',null);
            $app->setUserState('test3',null);
            $app->setUserState('test4',null);
            $app->setUserState('test5',null);
            $app->setUserState('test6',null);*/
            
            $_SESSION['paymentType']=$app->input->post->getString('paymentType');
            $_SESSION['cgvAcceptanceValue']=$app->input->post->getBool('cgvAcceptanceValue');
            $_SESSION['subscriptionsList']=$app->input->post->getString('subscriptionArray');
            $_SESSION['codePromo']=$app->input->post->getString('codePromo');
            $_SESSION["promoAmount"]=0;
            
            $model = $this->getModel();
            $getAmountResult=$model->getAmount($_SESSION['codePromo'],$_SESSION['subscriptionsList']);
            
            $_SESSION['amountHT']=$getAmountResult['amountHT'];
            $_SESSION['amountTTC']=$getAmountResult['amountTTC'];
            $_SESSION["promoAmount"]=$getAmountResult['promoAmount'];
            $_SESSION['subscriptionsList']=$getAmountResult['newSubscriptionStr'];
            
            if(isset($_SESSION['renewal']))
            {
                $_SESSION['subscriptionsList']=$model->addRenewalDates($_SESSION['subscriptionsList'],$_SESSION['renewal']);
            }
            
            if($_SESSION['amountTTC']==0)
            {
                $orderKey = $this->setOrder($_SESSION['promoAmount'],$_SESSION['amountHT'],$_SESSION['amountTTC'],$_SESSION['cgvAcceptanceValue'],$_SESSION['subscriptionsList']);
            
                if($orderKey!=false)
                {                    
                    if($model->activationOrder($orderKey))
                    {   
                        $invoice=$model->getDocument($orderKey,$_SESSION['amountHT'],$_SESSION['amountTTC'],$_SESSION['promoAmount'],'gratuit');
                        
                        if(!$model->sendDocument($invoice,$orderKey)){
                            $app->enqueueMessage("La facture n'a pas été envoyée!", 'alert');
                        }
                    }
                    else{ $app->enqueueMessage("Tous les abonnements n'ont pas été créés!", 'alert'); }
                }
                else{ $app->enqueueMessage('La commande n\'a pas été enregistrée', 'alert'); }
                
                $_SESSION=array();
                
                $this->setRedirect(JRoute::_('index.php?option=com_itinerary&view=itineraries&layout=default'));
            }
            else if($_SESSION['paymentType']=="iban")
            {
                $this->setRedirect(JRoute::_('index.php?option=com_itinerary&view=payment&layout=paymentbyiban'));
            }
            else if($_SESSION['paymentType']=="card")
            {
                $this->setRedirect(JRoute::_('index.php?option=com_itinerary&view=payment&layout=paymentbycard'));
            }
            else
            {
                $this->setRedirect(JRoute::_('index.php?option=com_itinerary&view=order&layout=edit'));
            }
        }
        
        private function setOrder($promoAmount=null,$amountHT=null,$amountTTC=null,$cgvAcceptanceValue=null,$subscriptionsList=null)
        {                     
            if(!is_null($promoAmount)&&!is_null($amountHT)&&!is_null($amountTTC)&&!empty($cgvAcceptanceValue)&&!empty($subscriptionsList))
            {
                return $this->getModel()->registerOrder($promoAmount,$amountHT,$amountTTC,$cgvAcceptanceValue,$subscriptionsList);
            }
            else{ return false; }
        }


        public function payment()
	{
            $model=$this->getModel();

            define('__ROOT__', $_SERVER['DOCUMENT_ROOT'] ); 

            $app=JFactory::getApplication();

            //Include the 
		e Library
            require_once(__ROOT__.'/libraries/stripe-php/init.php');

            //Charges the client's card
            // Set your secret key: remember to change this to your live secret key in production
            // See your keys here: https://dashboard.stripe.com/account/apikeys
            \Stripe\Stripe::setApiKey("SECRET_API_KEY");

            // Token is created using Stripe.js or Checkout!
            // Get the payment token submitted by the form:
            $token = $app->input->post->getString('stripeToken');
            $cardRemenberValue = $app->input->post->getInt('cardRemenberValue');
            $cardHolderName = $app->input->post->getString('cardholder-name');

            $subscriptionArray = $_SESSION['subscriptionsList'];
            $amount = $_SESSION['amountTTC']*100;
            
            
            $idCustomer = $model->getUserCusId();
            $customer = \Stripe\Customer::retrieve($idCustomer);

            $user=JFactory::getUser()->id;

            if(isset($cardRemenberValue))
            {
                $newSource=$customer->sources->create(array("source" => $token));		

                $customer->default_source = $newSource->id;
                $customer->save();

                $newSource->name = $cardHolderName;
                $newSource->save();
            }


            $userCountry = $model->getUserCountry();
            //$currency = $model->getCurrency($userCountry);
            $currency = "EUR";

            $metadata = array("user_id"=>$user,"customer_id"=>$idCustomer,"parcours_data"=>var_dump($subscriptionArray));

            // Charge the Customer instead of the card:
            $charge = \Stripe\Charge::create(array(
                "amount" => $amount,
                "currency" => $currency,
                "description" => "Abonnement parcours",
                "metadata" => $metadata,
                "customer" => $customer->id
            ));

            if(isset($cardRemenberValue)&&$cardRemenberValue==0)
            {
                $customer->sources->retrieve($newSource->id)->delete();
            }
            
            $this->setRedirect(JRoute::_('index.php?option=com_itinerary&task=paymentRooting&tmpl=component'));
            
            if($charge->status=="succeeded")
            {              
                $orderKey = $this->setOrder($_SESSION['promoAmount'],$_SESSION['amountHT'],$_SESSION['amountTTC'],$_SESSION['cgvAcceptanceValue'],$_SESSION['subscriptionsList']);
            
                if($orderKey!=false)
                {                    
                    if($model->activationOrder($orderKey))
                    {   
                        $invoice=$model->getDocument($orderKey,$_SESSION['amountHT'],$_SESSION['amountTTC'],$_SESSION['promoAmount'],'card');
                        
                        if(!$model->sendDocument($invoice,$orderKey)){
                            $app->enqueueMessage("La facture n'a pas été envoyée!", 'alert');
                        }
                        else
                        {
                            $_SESSION=array();
                            
                            $this->setRedirect(JRoute::_('index.php?option=com_itinerary&view=itineraries&layout=default'));
                        }
                    }
                    else{ $app->enqueueMessage("Tous les abonnements n'ont pas été créés!", 'alert'); }
                }
                else{ $app->enqueueMessage(JText::_('COM_ITINERARY_REGISTRATION_FAILED'), 'alert'); }
                
            }
            else { $app->enqueueMessage(JText::_('COM_ITINERARY_PAYMENT_FAILED'), 'alert'); }
	}
	
        public function order()
	{
            $model=$this->getModel();

            define('__ROOT__', $_SERVER['DOCUMENT_ROOT'] ); 

            $app=JFactory::getApplication();

            //Include the Stripe Library
            require_once(__ROOT__.'/libraries/stripe-php/init.php');          
            
            $this->setRedirect(JRoute::_('index.php?option=com_itinerary&task=paymentRooting&tmpl=component'));
                   
            $orderKey=$this->setOrder($_SESSION['promoAmount'],$_SESSION['amountHT'],$_SESSION['amountTTC'],$_SESSION['cgvAcceptanceValue'],$_SESSION['subscriptionsList']);
            
            if($orderKey!=false)
            {                 
                $order=$model->getDocument($orderKey,$_SESSION['amountHT'],$_SESSION['amountTTC'],$_SESSION['promoAmount'],'iban','Order');
                        
                if($model->sendDocument($order,$orderKey,'Order'))
                {
                    $_SESSION=array();
                    
                    $this->setRedirect(JRoute::_('index.php?option=com_itinerary&view=itineraries&layout=default'));
                }
                else{ $app->enqueueMessage("Le récapitulatif n'a pas été envoyé!", 'alert'); } 
            }
            else{ $app->enqueueMessage(JText::_('COM_ITINERARY_REGISTRATION_FAILED'), 'alert'); }            
	}
}
