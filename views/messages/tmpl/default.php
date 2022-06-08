<?php
defined('_JEXEC') or die;

setlocale(LC_MONETARY,"fr_FR");

$user = JFactory::getUser();

//make sure user is logged in
if($user->id == 0)
{
	JError::raiseWarning( 403, JText::_( 'COM_FOLIO_ERROR_MUST_LOGIN') );
}
else
{
	$listOrder = $this->escape($this->state->get('list.ordering'));
	$listDirn = $this->escape($this->state->get('list.direction'));
	
	$itinerary = $this->itinerary;
	
	$saveOrder = $listOrder == 'm.ordering';
	
	if($saveOrder)
	{
		$saveOrderingUrl = 'index.php?option=com_itinerary&task=messages.saveOrderAjax&tmpl=component';
		JHtml::_('sortablelist.sortable','messageList','adminForm',strtolower($listDirn),$saveOrderingUrl);
	}
?>

	<form action="<?php echo JRoute::_('index.php?option=com_itinerary&view=messages'); ?>" method="post" name="adminForm" id="adminForm">
		<legend class="legend"><?php echo JText::_("COM_ITINERARY_FIELD_INFORMATIONS_PARCOURS_LABEL");?> :</legend>
		<div class="btn-toolbar">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('itinerary.cancel')">
                                <i class="icon-arrow-left"></i> <?php echo JText::_('COM_ITINERARY_RETOUR_PARCOURS_LABEL'); ?>
                        </button>
                        <button type="button" class="btn btn-primary" onclick="location.href='<?php echo JRoute::_('index.php?option=com_itinerary&view=itinerary&layout=edit&clef='.$itinerary->clef);?>'">
                                <i class="icon-edit"></i> <?php echo JText::_('COM_ITINERARY_BUTTON_MODIFIER_PARCOURS_LABEL') ?>
                        </button>
                        <?php
                        if((int)$itinerary->nombre_messages!=0)
                        {
                            echo '<button type="button" class="btn btn-primary" onclick="createPreview()">
                                <i class="icon-play-circle"></i>'.JText::_('COM_ITINERARY_MESSAGES_PREVIEW_LABEL').'
                            </button>';
                        }
                        ?>
                        <button type="button" class="btn btn-primary" onclick="location.href='<?php echo JRoute::_('index.php?option=com_itinerary&view=feedbacks&layout=default');?>'">
                                <i class="icon-envelope"></i> <?php echo JText::_('COM_ITINERARY_FEEDBACKS_LABEL') ?>
                        </button>
                    </div>
		</div>
		
		<div class="parcoursblock">
			<div class="container-fluid">
				
				<div class="row">
                                    <div class="md parcours span3"><?php echo JText::_("JGLOBAL_TITLE");?> :</div>
                                    <div class="md span6"><strong><u><?php echo $itinerary->titre; ?></u></strong></div>

                                    <?php
                                                            JFactory::getApplication()->setUserState('test1',$itinerary->subscriptionIsOK);
                                        if($itinerary->subscriptionIsOK===true)
                                        {
                                            echo '<div class="md parcours span3 text-success">'.JText::_('COM_ITINERARY_SUBSCRIPTION_OK').'</div>';
                                        }
                                    ?>
				</div>
				
				<div class="row">
					<div class="md parcours span3"><?php echo JText::_("COM_ITINERARY_FIELD_SUMMARY_LABEL");?> :</div>
					<div class="md span9 text-justify"><?php echo htmlspecialchars_decode($itinerary->description,ENT_QUOTES);?></div>
				</div>
				<div class="clearfix"></div>
				
				<div class="row voffset3">
					<div class="md parcours span3"><?php echo JText::_("COM_ITINERARY_FIELD_PARCOURS_TYPE_LABEL");?> :</div>
					<div class="md span3">
						<?php 
							$typeParcours = ($itinerary->type_parcours) ? JText::_('COM_ITINERARY_FIELD_CIRCUIT_TYPE_LABEL') : JText::_('COM_ITINERARY_FIELD_PROMENADE_TYPE_LABEL') ;
							echo $typeParcours;
						?>
					</div>	
					<div class="md parcours span3"><?php echo JText::_("COM_ITINERARY_FIELD_NOMBRE_MESSAGES_LABEL");?> :</div>
					<div class="md span3"><?php echo $itinerary->nombre_messages;?></div>
				</div>

				<div class="row">
					<div class="md parcours span3"><?php echo JText::_("COM_ITINERARY_FIELD_NOTE_LABEL");?> :</div>
					<div class="md span3"><?php echo $itinerary->note;?></div>
					<div class="md parcours span3"><?php echo JText::_("COM_ITINERARY_FIELD_NOMBRE_NOTATIONS_LABEL");?> :</div>
					<div class="md span3"><?php echo $itinerary->nombre_commentaires;?></div>
				</div>

				<div class="row">
					<div class="md parcours span3"><?php echo JText::_("JGRID_HEADING_LANGUAGE");?> :</div>
					<div class="md span3">
						<?php echo $itinerary->titre_langue ? JHtml::_('image', 'mod_languages/' . $itinerary->image_langue . '.gif', $itinerary->titre_langue, array('title' => $itinerary->titre_langue), true) . '&nbsp;' . $this->escape($itinerary->titre_langue) : JText::_('JUNDEFINED');?>
					</div>
					<div class="md parcours span3"><?php echo JText::_("COM_ITINERARY_FIELD_DUREE_LABEL");?> :</div>
					<div class="md span3">
						<?php 
							$dureeParcours = $itinerary->duree;
							$nbHeures = floor($dureeParcours/60);
							$nbMinutes = $dureeParcours % 60;
							
							if($nbHeures==0)
							{
								echo $nbMinutes."min.";
							}
							elseif($nbMinutes==0)
							{
								echo $nbHeures."H";
							}
							else
							{
								echo $nbHeures."H".$nbMinutes;
							}
							
						?>
					</div>
				</div>
				
				<!--<div class="row">
					<div class="md parcours span3"><?php ""; // echo JText::_("COM_ITINERARY_FIELD_ACCESS_CATEGORY_LABEL");?> :</div>
					<div class="md span3">
						<?php 
							 ""; //$tarifParcours = ($itinerary->payant) ? JText::_('COM_ITINERARY_FIELD_CHARGE_LABEL') : JText::_('COM_ITINERARY_FIELD_FREE_LABEL') ;
							 ""; //echo $tarifParcours;
							?>
					</div>
					<div class="md parcours span3"><?php  ""; //echo JText::_("COM_ITINERARY_FIELD_TARIF_LABEL");?> :</div>
					<div class="md span3">
						<?php
							 ""; //$tarifParcours = ($itinerary->payant) ? money_format('%.2n',$itinerary->tarif) : "-";
							 ""; //echo $tarifParcours;
						?>
					</div>
				</div>-->
				
				<div class="row">
					<div class="md parcours span3"><?php echo JText::_("COM_ITINERARY_FIELD_THEME_LABEL");?> :</div>
					<div class="md span3"><?php echo JText::_($itinerary->theme_name);?></div>
					
					<div class="md parcours span3"><?php echo JText::_("COM_ITINERARY_FIELD_PARCOURS_DOWNLOADABLE_LABEL");?> :</div>
					<?php
						$telechargeable = ($itinerary->telechargeable) ? JText::_('JYES') : JText::_('JNO');
						$statusClass=($itinerary->telechargeable) ? 'text-success' : 'text-danger';
					
				echo '<div class="md parcours span3 '.$statusClass.'">';
							echo $telechargeable;
						?>
					</div>	
				</div>
				
				<div class="row">
					<div class="md parcours span3"><?php echo JText::_("COM_ITINERARY_IMAGE_THUMBNAIL");?> :</div>
					<div class="md span3"><?php
								if(empty($itinerary->illustrations))
								{
									echo '-';
								}
								else
								{
									$thumbnail = $this->path . str_replace("main","thumb",$itinerary->illustrations);
									echo '<img src="'.$thumbnail.'" class="img-responsive" alt="No image"/>';
								}
							?>
					</div>
					<div class="md parcours span3"><?php echo JText::_("COM_ITINERARY_FIELD_PARCOURS_ACTIVATED_LABEL");?> :</div>
					<?php
						$activationPlanificateur = ($itinerary->activation_planificateur) ? JText::_('JYES') : JText::_('JNO');
						$statusClass=($itinerary->activation_planificateur) ? 'text-success' : 'text-danger';
					
				echo '<div class="md parcours span3 '.$statusClass.'">';
							echo $activationPlanificateur;
						?>
					</div>	
				</div>
			</div>
		</div>
		
		<legend class="legend voffset3"><?php echo JText::_("COM_ITINERARY_FIELD_MESSAGES_LIST");?> :</legend>
		<div class="btn-toolbar">
			<div class="btn-group">
				<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('message.add')">
					<i class="icon-new"></i> <?php echo JText::_('COM_ITINERARY_BUTTON_NOUVEAU_MESSAGE_LABEL') ?>
				</button>
				<?php
				if(!empty($this->items))
				{
					echo '	<button type="button" class="btn btn-primary" onclick="deleteConfirmation()">
								<i class="icon-remove"></i>'.JText::_('COM_ITINERARY_BUTTON_SUPPRIMER_MESSAGE_LABEL').'
							</button>
						</div>
						
							<div class="btn btn-info hasPopover text-justify" data-original-title="'.JText::_('COM_ITINERARY_INFORMATION_LABEL').'" data-content="'.JText::_('COM_ITINERARY_INFORMATION_DESC').'">
								'.JText::_('COM_ITINERARY_INFORMATION_LABEL').'
							</div>';
				}
				else
				{
					echo '</div>';
				}
				?>
		</div>
		
		<div class="parcoursblock">
			<div class="container-fluid" id="j-main-container">
				<?php 
				if(empty($this->items))
				{
					echo '<div class="center">'.JText::_('COM_ITINERARY_FIELD_NO_MESSAGES').'</div>';
				}
				else
				{
				?>
					<table class="table table-striped table-bordered table-hover" id="messageList" onmouseover="numMsg()">
						<thead>
							<tr>
								<th width="1%" class="hidden-phone">
									<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
								</th>
								<th class="span1 center">
									<?php 
										echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'm.ordering', $listDirn, $listOrder, null, 'asc','COM_ITINERARY_FIELD_NUM_MSG_LABEL'); 
									?>
								</th>
								<th class="span1 center">
									<?php echo JText::_('COM_ITINERARY_IMAGE_THUMBNAIL'); ?>
								</th>
								<th class="span2 center">
									<?php echo JText::_('JGLOBAL_TITLE'); ?>
								</th>
								<th class="span9 center">
									<?php echo JText::_('COM_ITINERARY_FIELD_MESSAGE_TEXTE_LABEL'); ?>
								</th>
								<th class="span1 center">
									<?php echo JText::_('COM_ITINERARY_FIELD_MESSAGE_ACTIVATED_LABEL'); ?>
								</th>
							</tr>
						</thead>
						
						<tbody id="messagesTable">
							<?php
								foreach ($this->items as $i => $item) :
								$clef_message = (int) $item->clef;			
							?>
								<tr style="cursor:pointer" id="row_<?php echo $clef_message; ?>" sortable-group-id="1">
									<td valign="align-middle">
										<?php echo JHtml::_('grid.id', $i, $clef_message); ?>
									</td>
									<td class="align-middle sortable-handler">
										<span class=""><i class="icon-menu"></i></span><span class="rowNb"><?php echo $i+1;?></span>
										<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering;?>" class="width-20 text-area-order"/>
									</td>
									<td class="align-middle" onclick="messagesDuParcours(<?php echo $clef_message; ?>)">
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
									<td class="center" onclick="messagesDuParcours(<?php echo $clef_message; ?>)">
										<?php echo htmlspecialchars_decode($item->titre,ENT_QUOTES); ?>
									</td>
									<td onclick="messagesDuParcours( <?php echo $clef_message; ?> )">
										<div class="text-justify"><?php echo htmlspecialchars_decode($item->texte,ENT_QUOTES); ?></div>
									</td>
									<?php
										$statusClass=($item->activation_planificateur) ? 'text-success' : 'text-danger';
										
								echo '<td class="center parcours '.$statusClass.'" onclick="messagesDuParcours('.$clef_message.')">';
										
										echo ($item->activation_planificateur)?  JText::_('JYes') : JText::_('JNo');
									?>
									</td>
								</tr>
								
							<?php endforeach;?>
						</tbody>		
						<tfoot>
							<tr>
								<td colspan="12">
									<?php echo $this->pagination->getListFooter(); ?>
								</td>
							</tr>
						</tfoot>
					</table>
				<?php
				}
				?>
			</div>
			
                        <input type="hidden" id="clefSubscription" name="clefSubscription" value="" />
                        <input type="hidden" id="clefTailleParcours" name="clefTailleParcours" value="" />
                        <input type="hidden" id="clefParcours" name="clefParcours" value="" />
                        <input type="hidden" id="dateDebut" name="dateDebut" value="" />
                        <input type="hidden" id="dateFin" name="dateFin" value="" />
                    
			<input type="hidden" id="clef" name="clef" value="" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<?php echo JHtml::_('form.token'); ?>	
		</div>
	</form>
<?php
}