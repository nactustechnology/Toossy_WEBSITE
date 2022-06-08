<?php
defined('_JEXEC') or die;

setlocale(LC_MONETARY,"fr_FR");
JHtml::_('behavior.formvalidator');

$itinerary = $this->itinerary;

if(!isset($_SESSION['amount'])||empty($_SESSION['amount']))
{	
	echo '<div class="form-horizontal parcoursblock">'.JText::_('COM_ITINERARY_PAYMENT_IMPOSSIBLE').'</div>';

}
elseif(!isset($_SESSION['cgv_approval'])||empty($_SESSION['cgv_approval']))
{
	echo '<div class="form-horizontal parcoursblock">'.JText::_('COM_ITINERARY_NO_CGV_APPROVAL').'</div>';
}
elseif(ItineraryHelper::checkSubscription()===true&&empty($_SESSION['renewalDate']))
{
	JFactory::getApplication()->redirect(JRoute::_('index.php?view=itinerary&layout=edit&clef='.$this->idParcours, false),JText::_('COM_ITINERARY_SUBSCRIPTION_ALREDY_EXISTS'),'error');
}
else
{
   
?>
	
	<div class="form-horizontal voffset1 parcoursblock opaque">
            
            <div class="panel-body">
                <?php
                
                ?>
                <div class="tab-content">

                <div role="tabpanel" class="tab-pane active" id="availableCard">
                        <form action="<?php echo JRoute::_('index.php?option=com_itinerary&task=subscription.order&tmpl=component'); ?>" method="post" id="payment-form-4">			
                        <div class="row span12"><legend><?php echo JText::_("COM_ITINERARY_SUBSCRIPTION_PAYMENT_IBAN");?> :</legend></div>
                        <div class="row span12"><?php echo JText::_('COM_ITINERARY_SUBSCRIPTION_IBAN_ORDER_EXPLANATION_1');?></div>  
                        <div class="row span12"><?php echo JText::_('COM_ITINERARY_SUBSCRIPTION_IBAN_ORDER_EXPLANATION_2');?></div>
                        <div class="row span12 center"><?php echo JText::_('COM_ITINERARY_SUBSCRIPTION_IBAN_ORDER_EXPLANATION_3');?></div>
                        <div class="row span12 center"><?php echo JText::_('COM_ITINERARY_SUBSCRIPTION_IBAN_ORDER_EXPLANATION_4');?></div>
                        <div class="row span12 center"><?php echo JText::_('COM_ITINERARY_SUBSCRIPTION_IBAN_ORDER_EXPLANATION_5');?></div>
                        <div class="row span12"><?php echo JText::_('COM_ITINERARY_SUBSCRIPTION_IBAN_ORDER_EXPLANATION_6');?></div>
                        <div class="row span12"><?php echo JText::_('COM_ITINERARY_SUBSCRIPTION_IBAN_ORDER_EXPLANATION_7');?></div>                           

                        <div class="row center voffset3">
                                <button id="formSubmitButton" class="btn btn-success"><?php echo JText::_('COM_ITINERARY_SUBSCRIPTION_CONFIRMATION_IBAN');?></button>
                        </div>
                        </form>
                </div>
                </div>
            </div>  
	</div>
<?php	
}
?>