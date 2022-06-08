<!--subscription edit-->
<?php
defined('_JEXEC') or die;

setlocale(LC_MONETARY,"fr_FR");
JHtml::_('behavior.formvalidator');
$itinerary = $this->itinerary;
$profile=$this->profile;

$prices=null;


if(isset($this->item->prices))
{
	$prices=$this->item->prices;
        //$currency=$this->item->currency;
        $tvaRate=$this->item->tva_rate;
}

$domainPath = 'https://'.$_SERVER['HTTP_HOST'].'/mon-compte';

if(is_null($prices)&&1>2)
{
	echo '<div class="form-horizontal parcoursblock">'.JText::_('COM_ITINERARY_SUBSCRIPTION_IMPOSSIBLE').'</div>';
}
elseif(ItineraryHelper::checkSubscription()===true&&empty(JFactory::getApplication()->input->post->get('clefParcours')))
{
	JFactory::getApplication()->redirect(JRoute::_('index.php?view=itinerary&layout=edit&clef='.$this->idParcours, false),JText::_('COM_ITINERARY_SUBSCRIPTION_ALREDY_EXISTS'),'error');
}
else
{
?>
	<form action="<?php echo JRoute::_('index.php?option=com_itinerary&view=subscription&layout=payment'); ?>" method="post" id="subscriptionSelectForm" class="form-validate subscription_form_width center-block">
		<input type="hidden" id="duree" name="duree" class="notprintable"/>		
		
                <legend class="legend notprintable"><?php echo JText::_("COM_ITINERARY_SUBSCRIPTION_SELECT");?> :</legend>
                <div class="form-horizontal parcoursblock notprintable">
                        <div class="row">
                                <select id="subscriptionDuree" class="span12" style="background-color:#bf383a; color:white;">
                                <?php
                                $formatter = new NumberFormatter('fr_CA', NumberFormatter::ORDINAL );
                                
                                        foreach($prices as $price)
                                        {
                                            $selected='false';

                                            if($price['duree']==12)
                                            {
                                                    $selected='true';
                                            }

                                            echo '<option value="'.$price['duree'].'@'.$price['tarif'].'@'.$price['max'].'" selected="'.$selected.'">'.sprintf(JText::_("COM_ITINERARY_SUBSCRIPTION_LIST"),$price['duree'],money_format('%.2n',$price['tarif']),$price['max'],$formatter->format($price['number'])).'</option>';
                                        }
                                ?>
                                </select>
                        </div>
                </div>

		<legend class="legend notprintable"><?php echo JText::_("COM_ITINERARY_SUBSCRIPTION_SUMMARY");?> :</legend>
		<div class="form-horizontal parcoursblock">
			<div class="row">
				<div class="clearfix"></div>
					<div class="parcours span5"><?php echo JText::_("COM_ITINERARY_CPY_NAME");?></div>
					<div class="parcours span3 text-right"><?php echo JText::_("COM_ITINERARY_DATE");?> :</div>
					<div class="span2"><?php echo date('d/m/Y');?></div>
					<div class="span1">
						<button type="button" class="btn btn-primary" id="printSummary">
							<i class="icon-print"></i><?php echo JText::_('JGLOBAL_PRINT'); ?>
						</button>
					</div>
				<div class="clearfix"></div>
					<div class="span12 address"><?php echo JText::_("COM_ITINERARY_CPY_ADRESS_1");?></div>
					<div class="span12 address"><?php echo JText::_("COM_ITINERARY_CPY_ADRESS_2");?></div>
					<div class="span12 address"><?php echo JText::_("COM_ITINERARY_CPY_ADRESS_3");?></div>
					<div class="span12 address"><?php echo JText::_("COM_ITINERARY_CPY_SIRET");?></div>
					<div class="span12 address"><?php echo JText::_("COM_ITINERARY_CPY_NB_TVA");?></div>
			</div>

			
			<div class="row voffset2">
				<div class="clearfix"></div>
					<div class="parcours span11 text-right"><?php echo !empty($this->user)?$this->user:JText::_('COM_ITINERARY_USERNAME_MISSING');?></div>
					
					<?php
					if(is_null($profile)||empty($profile["profile.address1"])||empty($profile["profile.postal_code"])||empty($profile["profile.city"])||empty($profile["profile.country"]))
					{
                                                echo '<div class="span11 text-right address text-danger">
                                                        <a href="'.$domainPath.'" target="_blank" class="list-group-item active">'.JText::_('COM_ITINERARY_ADDRESS_MISSING').'</a>
                                                     </div>';
					}
					else
					{
						echo '<div class="span11 text-right address">'.$profile["profile.address1"].'</div>
							<div class="span11 text-right address">'.$profile["profile.postal_code"]." ".$profile["profile.city"].'</div>
							<div class="span11 text-right address">'.JText::_($profile["profile.country"]).'</div>';
					} ?>
			</div>
			
			<div class="row voffset3">
				<div class="clearfix"></div>
					<div class="parcours span12"><u><?php echo JText::_("COM_ITINERARY_SUBSCRIPTION_INFORMATIONS");?> :</u></div>
					
					<?php
						if(!isset($_SESSION['clefTailleParcours']))
						{
							echo '<div class="span11 text-justify-infowindow">'.JText::_("COM_ITINERARY_SUBSCRIPTION_EXPLANATIONS").'</div>';
						}
						else
						{
							echo '<div class="span11 text-justify-infowindow">'.JText::_("COM_ITINERARY_SUBSCRIPTION_INCREASE_EXPLANATIONS").'</div>';
						}
					?>
			</div>
			
			<div class="row voffset3">
				<div class="clearfix"></div>
					<div class="parcours span12"><u><?php echo JText::_("COM_ITINERARY_SUBSCRIPTION_CHARACTERISTICS");?> :</u></div>
				<div class="clearfix"></div>
					<div class="parcours span3 offset3"><?php echo JText::_("JGLOBAL_TITLE");?> :</div>
					<div class="span5"><strong><?php echo htmlspecialchars_decode($itinerary->titre,ENT_QUOTES); ?></strong></div>
				<div class="clearfix"></div>
					<div class="parcours span3 offset3"><?php echo JText::_("JGRID_HEADING_LANGUAGE");?> :</div>
					<div class="span3"><?php echo $itinerary->titre_langue ? JHtml::_('image', 'mod_languages/' . $itinerary->image_langue . '.gif', $itinerary->titre_langue, array('title' => $itinerary->titre_langue), true) . '&nbsp;' . $this->escape($itinerary->titre_langue) : JText::_('JUNDEFINED');?></div>
				<div class="clearfix"></div>
					<div class="parcours span3 offset3"><?php echo JText::_("COM_ITINERARY_FIELD_NOMBRE_MAXIMUM_MESSAGES_LABEL");?> :</div>
					
					<div class="span3" id="msg_max"></div>
				
				<div class="clearfix"></div>
					<div class="parcours span3 offset3"><?php echo JText::_("COM_ITINERARY_SUBSCRIPTION_DUREE");?> :</div>
					<div class="span3" id="duree_abonnement"></div>
				<div class="clearfix"></div>
					<div class="parcours span3 offset3"><?php echo JText::_("COM_ITINERARY_FIELD_DATE_DEBUT_LABEL"); ?> :</div>
					<div class="span3" id="date_debut"></div>
				<div class="clearfix"></div>
					<div class="parcours span3 offset3"><?php echo JText::_("COM_ITINERARY_FIELD_DATE_FIN_LABEL"); ?> :</div>
					<div class="span3" id="date_fin"></div>
				<div class="clearfix"></div>
					<div class="parcours span3 offset3"><?php echo JText::_("COM_ITINERARY_FIELD_TARIF_MENSUEL_LABEL"); ?> :</div>
					<div class="span3" id="tarif"></div>
			</div>
			
			<div class="row voffset3">
				<div class="clearfix"></div>
					<div class="parcours span12"><u><?php echo JText::_("COM_ITINERARY_SUBSCRIPTION_TARIFICATION");?> :</u></div>
				<div class="clearfix"></div>
					<div class="parcours span3 offset3"><?php echo JText::_("COM_ITINERARY_FIELD_PRIX_HT_LABEL"); ?> :</div>
					<div class="span3" id="prix_ht"></div>
				<div class="clearfix"></div>
					<div class="parcours span3 offset3"><?php echo sprintf(JText::_("COM_ITINERARY_FIELD_TVA_LABEL"),$tvaRate*100); ?> :</div>
					<div class="span3" id="tva"></div>
				<div class="clearfix"></div>
					<div class="parcours span3 offset3"><?php echo JText::_("COM_ITINERARY_FIELD_PRIX_TTC_LABEL"); ?> :</div>
					<div class="span3" id="prix_ttc"></div>
				<div class="clearfix"></div>
				
				<div class="container parcours center span6 offset3 voffset2"><strong><u><?php echo JText::_('COM_ITINERARY_INVOICE_PAYMENT_TYPE_CHOICE'); ?></u></strong>
                                        <label class="radio-inline" for="cardRadio"><input checked="checked" name="payment_type" type="radio" value="card" id="cardRadio" /> <?php echo JText::_('COM_ITINERARY_INVOICE_PAYMENT_TYPE_CARD'); ?></label>
                                        <label class="radio-inline" for="ibanRadio"><input name="payment_type" type="radio" value="iban" id="ibanRadio" /> <?php echo JText::_('COM_ITINERARY_INVOICE_PAYMENT_TYPE_IBAN'); ?></label>
                                </div>
			</div>
			
			<div class="row voffset3">
				<div class="clearfix"></div>
				<div class="span11 text-center">
					<input type="hidden" id="cgvAcceptanceValue" name="cgvAcceptanceValue" value="0">
					<label for="cgvAcceptance" class="text-center">
						<input type="checkbox" id="cgvAcceptance">
						<?php echo JText::_('COM_ITINERARY_SUBSCRIPTION_CGV_ACCEPTANCE_1');?>
                                                <a href="/media/documents/Nactus_Technology-CGV.pdf" target="_blank"><?php echo JText::_('COM_ITINERARY_SUBSCRIPTION_CGV_ACCEPTANCE_2');?></a>
					</label>
				
					
				</div>
			</div>
                        <?php
                        if(empty($profile["profile.address1"])||empty($profile["profile.postal_code"])||empty($profile["profile.city"])||empty($profile["profile.country"]))
                        {
                                //echo '<div class="row voffset3 text-danger">'.JText::_('COM_ITINERARY_ADDRESS_MISSING').'</div>';
                                echo '<div class="row span9 text-right address text-danger">
                                        <a href="'.$domainPath.'" target="_blank" class="list-group-item active">'.JText::_('COM_ITINERARY_ADDRESS_MISSING').'</a>
                                     </div>';
                        }
                        else
                        {
                                echo '<div class="row voffset3">
                                                <button id="formSubmitButton" class="btn btn-success span4 offset4" type="button">'.JText::_('COM_ITINERARY_FIELD_SUBSCRIPTION_SUBMIT_LABEL').'</button>
                                        </div>';
                        } ?>
		</div>
	</form>
<?php	
}
?>

	