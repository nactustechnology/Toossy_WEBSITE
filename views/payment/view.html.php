<!--View subscription-->
<?php
defined('_JEXEC') or die;

class ItineraryViewPayment extends JViewLegacy
{
    protected $item;

    public function display($tpl = null)
    {
        $this->path = "images" . DS . "logos" . DS ;
        $this->model = $this->getModel();
        
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

                $this->model->setUserId($customer->id);
        }
        
        $document = JFactory::getDocument();
        $document->addScriptDeclaration("
            jQuery( document ).ready(
                function()
                {
                    document.getElementById('formSubmitButton').addEventListener('click',submitButtonClickEvent);

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
                                document.getElementById('subscriptionSelectForm').action = '".JRoute::_('index.php?option=com_itinerary&view=subscription&layout=payment_iban')."';
                                document.getElementById('subscriptionSelectForm').submit();
                            }
                        }
                        else
                        {
                            console.log('faux');
                            alert('".JText::_('COM_ITINERARY_SUBSCRIPTION_CGV_NOT_CHECKED')."');
                        }
                    }
                }
            );
        ");

        parent::display($tpl);
    }
}