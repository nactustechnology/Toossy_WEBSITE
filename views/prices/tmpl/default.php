<?php
defined('_JEXEC') or die;

setlocale(LC_MONETARY,"fr_FR");
?>
<legend class="legend"><?php echo JText::_("COM_ITINERARY_PRICES_LIST");?> :</legend>


<div class="parcoursblock container-fluid">
<?php 
	if(empty($this->items))
	{
		echo '<div class="center">'.JText::_('COM_ITINERARY_NO_PRICES').'</div>';
	}
	else
	{
	?>
		<table class="table table-striped table-bordered table-hover">
			<thead>
				<?php 	
					echo'<th class="span1">
							'.JText::_('COM_ITINERARY_PRICES_DUREE').'
						</th>';
					
					foreach($this->itinerarySizeList AS $i=>$itinerarySize)
					{
						echo'<th class="span3">
								'.sprintf(JText::_('COM_ITINERARY_PRICES_SIZE'), $itinerarySize['clef'],$itinerarySize['min'], $itinerarySize['max']).'
							</th>';
					}?>
			</thead>
			<tbody>
			<?php 	foreach($this->itineraryDureeList AS $i=>$itineraryDuree)
					{
						echo '<tr>
								<td class="center">
									'.$itineraryDuree.' '.JText::_('COM_ITINERARY_SUBSCRIPTION_MONTH').'
								</td>';
							
								foreach($this->items AS $key=>$item)
								{
									if($item->duree==$itineraryDuree)
									{
										echo '<td class="center">
												'.money_format('%.0n',$item->tarif).' '.JText::_('COM_ITINERARY_PRICES_HT').'/'.JText::_('COM_ITINERARY_SUBSCRIPTION_MONTH').'
											</td>';
									}
								}

						echo '</tr>';
					}
					?>
			</tbody>
			
		</table>
	<?php }?>
	
</div>

<legend class="legend voffset3"><?php echo JText::_('COM_ITINERARY_PRICES_EXPLANATIONS_LABEL');?> :</legend>
<div class="parcoursblock container-fluid">
	<div class="row">
		<?php echo JText::_('COM_ITINERARY_PRICES_SUBSCRIPTION_EXPLANATIONS'); ?>
	</div>
	<div class="row voffset3">
		<div><?php echo JText::_('COM_ITINERARY_PRICES_INCREASE_EXPLANATIONS_1'); ?></div>
		<div class="offset1"><?php echo JText::_('COM_ITINERARY_PRICES_INCREASE_EXPLANATIONS_2'); ?></div>
		<div class="offset1"><?php echo JText::_('COM_ITINERARY_PRICES_INCREASE_EXPLANATIONS_3'); ?></div>
		<div class="offset1"><?php echo JText::_('COM_ITINERARY_PRICES_INCREASE_EXPLANATIONS_4'); ?></div>
	</div>
	<div class="row voffset3">
		<div><?php echo JText::_('COM_ITINERARY_PRICES_INCREASE_EXEMPLE_1'); ?></div>
		<div class="offset1"><?php echo JText::_('COM_ITINERARY_PRICES_INCREASE_EXEMPLE_2'); ?></div>
		<div class="offset1"><?php echo JText::_('COM_ITINERARY_PRICES_INCREASE_EXEMPLE_3'); ?></div>
		<div class="offset1"><?php echo JText::_('COM_ITINERARY_PRICES_INCREASE_EXEMPLE_4'); ?></div>
		<div class="offset1"><?php echo JText::_('COM_ITINERARY_PRICES_INCREASE_EXEMPLE_5'); ?></div>
		<div class="offset1"><?php echo JText::_('COM_ITINERARY_PRICES_INCREASE_EXEMPLE_6'); ?></div>
		<div class="offset1"><?php echo JText::_('COM_ITINERARY_PRICES_INCREASE_EXEMPLE_7'); ?></div>
		<div class="offset1"><?php echo JText::_('COM_ITINERARY_PRICES_INCREASE_EXEMPLE_8'); ?></div>
		<div class="offset1"><?php echo JText::_('COM_ITINERARY_PRICES_INCREASE_EXEMPLE_9'); ?></div>
		<div class="offset1"><?php echo JText::_('COM_ITINERARY_PRICES_INCREASE_EXEMPLE_10'); ?></div>
		<div class="offset1"><?php echo JText::_('COM_ITINERARY_PRICES_INCREASE_EXEMPLE_11'); ?></div>
	<div>
	
	<div class="row voffset5">
		<?php echo JText::_('COM_ITINERARY_PRICES_EXPLANATION_TERMS_AND_CONDITIONS');?>
		<a href="/media/documents/Nactus_Technology-CGV.pdf" target="_blank"><?php echo JText::_('COM_ITINERARY_SUBSCRIPTION_CGV_ACCEPTANCE_2');?></a>
	</div>
</div>


<input type="hidden" id="clef" name="clef" value="" />			
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<?php echo JHtml::_('form.token'); ?>	