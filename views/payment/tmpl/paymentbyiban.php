<?php
defined('_JEXEC') or die;

setlocale(LC_MONETARY,"fr_FR");
JHtml::_('behavior.formvalidator');

if(!isset($_SESSION['amountTTC'])||empty($_SESSION['amountTTC']))
{	
	echo '<div class="form-horizontal parcoursblock">'.JText::_('COM_ITINERARY_PAYMENT_IMPOSSIBLE').'</div>';

}
elseif(!isset($_SESSION['cgvAcceptanceValue'])||empty($_SESSION['cgvAcceptanceValue']))
{
	echo '<div class="form-horizontal parcoursblock">'.JText::_('COM_ITINERARY_NO_CGV_APPROVAL').'</div>';
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
                        <form action="<?php echo JRoute::_('index.php?option=com_itinerary&task=payment.order&tmpl=component'); ?>" method="post" id="payment-form-4">			
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