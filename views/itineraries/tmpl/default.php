<?php
defined('_JEXEC') or die;

setlocale(LC_MONETARY,"fr_FR");

$user = JFactory::getUser();

/*echo '<pre>';
    $app=JFactory::getApplication();
    
    var_dump($app->getUserState('test1'));
    var_dump($app->getUserState('test2'));
    var_dump($app->getUserState('test3'));
    var_dump($app->getUserState('test4'));
    var_dump($app->getUserState('test5'));
    var_dump($app->getUserState('test6'));
echo '</pre>';*/


//make sure user is logged in
if($user->id == 0)
{
    JError::raiseWarning( 403, JText::_( 'COM_FOLIO_ERROR_MUST_LOGIN') );
}
else
{
    $listOrder = $this->escape($this->state->get('list.ordering'));
    $listDirn = $this->escape($this->state->get('list.direction'));
?>	
	<form action="<?php echo JRoute::_('index.php?option=com_itinerary&view=itineraries'); ?>" method="post" name="adminForm" id="adminForm">
	
		<legend class="legend"><?php echo JText::_("COM_ITINERARY_FIELD_PARCOURS_LIST");?> :</legend>
		<div class="btn-toolbar">
			<div class="btn-group">
				<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('itinerary.add')">
					<i class="icon-new"></i> <?php echo JText::_('COM_ITINERARY_BUTTON_NOUVEAU_PARCOURS_LABEL') ?>
				</button>
				<?php
				if(!empty($this->items))
				{?>
					<button type="button" class="btn btn-primary" onclick="deleteConfirmation()">
						<i class="icon-remove"></i> <?php echo JText::_('COM_ITINERARY_BUTTON_SUPPRIMER_PARCOURS_LABEL') ?>
					</button>
				<?php
				}?>
			</div>
                    
                        <div class="btn-group">
                            <button id="go_to_orderPage" type="button" class="btn btn-info" onclick="location.href='<?php echo JRoute::_('index.php?option=com_itinerary&view=order&layout=edit');?>'">
                                <?php echo JText::_('COM_ITINERARY_BUTTON_COMMANDER_SERVICES') ?>
                            </button>
                            
                            <!--<button type="button" class="btn btn-info" onclick="Joomla.submitbutton('payment.testinvoicing')">
                                test invoicing
                            </button>-->
                        </div>
		</div>
		
		<div class="parcoursblock container-fluid" id="j-main-container">
		<?php 
			if(empty($this->items))
			{
				echo '<div class="center">'.JText::_('COM_ITINERARY_FIELD_NO_PARCOURS').'</div>';
			}
			else
			{
			?>
				<table class="table table-striped table-bordered table-hover"  id="itineraryList">
					<thead>
						<tr>
							<th width="1%" class="hidden-phone">
								<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
							</th>
							<th class="span2 center">
								<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.titre', $listDirn, $listOrder); ?>
							</th>
							<th class="span1 center">
								<?php echo JText::_('COM_ITINERARY_IMAGE_THUMBNAIL'); ?>
							</th>
							<th class="span1 center">
								<?php echo JHtml::_('grid.sort', 'COM_ITINERARY_FIELD_NOMBRE_ETAPES_LABEL', 'a.nombre_messages', $listDirn, $listOrder); ?>
							</th>
							<th class="span1 center">
								<?php echo JHtml::_('grid.sort', 'COM_ITINERARY_FIELD_NOTE_LABEL', 'a.note', $listDirn, $listOrder); ?>
							</th>
							<th class="span1 center">
								<?php echo JHtml::_('grid.sort', 'COM_ITINERARY_FIELD_NOMBRE_NOTATIONS_LABEL', 'a.nombre_commentaires', $listDirn, $listOrder); ?>
							</th>
							<th class="span2 center">
								<?php echo JHtml::_('grid.sort', 'JFIELD_LANGUAGE_LABEL', 'a.langue', $listDirn, $listOrder); ?>
							</th>
							<th class="span1 center">
								<?php echo JHtml::_('grid.sort', 'COM_ITINERARY_FIELD_DUREE_LABEL', 'a.duree', $listDirn, $listOrder); ?>
							</th>
							<!--<th class="span1 center">
								<?php ""; //echo JHtml::_('grid.sort', 'COM_ITINERARY_FIELD_FREE_LABEL', 'a.payant', $listDirn, $listOrder); ?>
							</th>
							<th class="span1 center">
								<?php ""; //echo JHtml::_('grid.sort', 'COM_ITINERARY_FIELD_TARIF_LABEL', 'a.tarif', $listDirn, $listOrder); ?>
							</th>-->
							<th class="span1 center">
								<?php echo JHtml::_('grid.sort', 'COM_ITINERARY_FIELD_PARCOURS_ACTIVATED_LABEL', 'a.activation_planificateur', $listDirn, $listOrder); ?>
							</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($this->items as $i => $item) :
						
						$clef_parcours = (int) $item->clef;			
										?>
						<tr style="cursor:pointer" >
							<td class="center">
								<?php echo JHtml::_('grid.id', $i, $clef_parcours); ?>
							</td>
							<td id="<?php echo $clef_parcours.'_titre'; ?>" class="center" onClick="messagesDuParcours( <?php echo $clef_parcours; ?> )">
									<?php echo htmlspecialchars_decode($item->titre,ENT_QUOTES); ?>
							</td>
							<td id="<?php echo $clef_parcours.'_illustrations'; ?>" class="center" onClick="messagesDuParcours( <?php echo $clef_parcours; ?> )">
								<?php
									if(empty($item->illustrations))
									{
										echo '-';
									}
									else
									{
										$thumbnail = $this->path . str_replace("main","thumb",$item->illustrations);
										echo '<img src="'.$thumbnail.'" class="img-responsive center-block" alt="No image"/>';
									}
								?>
							</td>
							<td id="<?php echo $clef_parcours.'_nombre_messages'; ?>" class="center" onClick="messagesDuParcours( <?php echo $clef_parcours; ?> )">
								<?php echo $item->nombre_messages; ?>
							</td>
							<td id="<?php echo $clef_parcours.'_note'; ?>" class="center" onClick="messagesDuParcours( <?php echo $clef_parcours; ?> )">
								<?php echo $item->note; ?>
							</td>
							<td id="<?php echo $clef_parcours.'_nombre_commentaires'; ?>" class="center" onClick="messagesDuParcours( <?php echo $clef_parcours; ?> )">
								<?php echo $item->nombre_commentaires; ?>
							</td>
							<td id="<?php echo $clef_parcours.'_titre_langue'; ?>" class="sm center" onClick="messagesDuParcours( <?php echo $clef_parcours; ?> )">
								<?php 
								echo $item->titre_langue ? JHtml::_('image', 'mod_languages/' . $item->image_langue . '.gif', $item->titre_langue, array('title' => $item->titre_langue), true) . '&nbsp;' . $this->escape($item->titre_langue) : JText::_('JUNDEFINED');?>
							</td>
							<td id="<?php echo $clef_parcours.'_duree'; ?>" class="center" onClick="messagesDuParcours( <?php echo $clef_parcours; ?> )">
								<?php echo sprintf("%02dH%02d", floor($item->duree/60), $item->duree%60); ?>
							</td>
							<!--<td id="<?php ""; // echo $clef_parcours.'_gratuit'; ?>" class="center" onClick="messagesDuParcours( <?php echo $clef_parcours; ?> )">
								<?php ""; // echo ($item->payant==1) ? JText::_('JNo') : JText::_('JYes'); ?>
							</td>
							<td id="<?php ""; // echo $clef_parcours.'_tarif'; ?>" class="center" onClick="messagesDuParcours( <?php echo $clef_parcours; ?> )">
								<?php ""; // echo ($item->payant==1)? money_format("%.2n", $item->tarif) : 'NA' ; ?>
							</td>-->
							<?php
								$statusClass=($item->activation_planificateur) ? 'text-success' : 'text-danger';
								
						echo '<td id="'.$clef_parcours.'_activation_planificateur" class="center parcours '.$statusClass.'" onClick="messagesDuParcours('.$clef_parcours.')">';
								
								echo ($item->activation_planificateur)?  JText::_('JYes') : JText::_('JNo');
							?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
					<tfoot>
						<td colspan="12">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tfoot>
				</table>
			<?php }?>
			
		</div>

			<input type="hidden" id="clef" name="clef" value="" />			
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<?php echo JHtml::_('form.token'); ?>	
		
	</form>
<?php
}