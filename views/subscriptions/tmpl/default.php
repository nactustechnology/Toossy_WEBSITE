<?php
defined('_JEXEC') or die;

setlocale(LC_MONETARY,"fr_FR");

$user = JFactory::getUser();

//make sure user is logged in

	$listOrder = $this->escape($this->state->get('list.ordering'));
	$listDirn = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_itinerary&view=subscriptions'); ?>" method="post" name="adminForm" id="adminForm">

	<legend class="legend"><?php echo JText::_("COM_ITINERARY_FIELD_SUBSCRIPTIONS_LIST");?> :</legend>
	
        
        
        <div class="btn-toolbar"> 
            <div class="btn-group">
                <button type="button" class="btn btn-info" onclick="Joomla.submitbutton('subscriptions.orderRenewal')">
                    <?php echo JText::_('COM_ITINERARY_BUTTON_COMMANDER_RENEWAL') ?>
                </button>
            </div>
        </div>

        
        
	<div class="parcoursblock container-fluid" id="j-main-container">
	<?php 
		if(empty($this->items))
		{
			echo '<div class="center">'.JText::_('COM_ITINERARY_FIELD_NO_SUBSCRIPTIONS').'</div>';			
		}
		else
		{
		?>
		
			<table class="table table-bordered table-hover"  id="itineraryList">
				<thead>
					<tr>
                                                <th width="1%" class="hidden-phone">
                                                        <input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
                                                </th>
						<th class="span1 center">
							<?php echo JHtml::_('grid.sort', 'COM_ITINERARY_ORDER_NUM', 's.commandeNb', $listDirn, $listOrder); ?>
						</th>
						<!--<th class="span1 center">
							<php echo JHtml::_('grid.sort', 'COM_ITINERARY_INVOICE_NUM', 's.factureNb', $listDirn, $listOrder); ?>
						</th>-->
						<th class="span1 center">
							<?php echo JHtml::_('grid.sort', 'COM_ITINERARY_FIELD_DATE_CREATION_LABEL', 's.date_creation', $listDirn, $listOrder); ?>
						</th>
						<th class="span4 center">
							<?php echo JText::_('COM_ITINERARY_INVOICE_PRODUCT_DESCRIPTION_LABEL'); ?>
						</th>
						<th class="span2 center">
							<?php echo JHtml::_('grid.sort', 'COM_ITINERARY_FIELD_PARCOURS_TITLE_DESC', 'p.titre', $listDirn, $listOrder); ?>
						</th>
						<th class="span1 center">
							<?php echo JText::_('COM_ITINERARY_IMAGE_THUMBNAIL'); ?>
						</th>
						<th class="span1 center">
							<?php echo JHtml::_('grid.sort', 'JFIELD_LANGUAGE_LABEL', 'p.langue', $listDirn, $listOrder); ?>
						</th>
						<th class="span1 center">
							<?php echo JHtml::_('grid.sort', 'COM_ITINERARY_FIELD_DATE_DEBUT_LABEL', 's.date_debut', $listDirn, $listOrder); ?>
						</th>
						<th class="span1 center">
							<?php echo JHtml::_('grid.sort', 'COM_ITINERARY_FIELD_DATE_FIN_LABEL', 's.date_fin', $listDirn, $listOrder); ?>
						</th>
						<th class="span1 center">
							<?php echo JHtml::_('grid.sort', 'COM_ITINERARY_INVOICE_MSG', 'z.max', $listDirn, $listOrder); ?>
						</th>
						<th class="span1 center">
							<?php echo JHtml::_('grid.sort', 'COM_ITINERARY_FIELD_PRIX_TTC_LABEL', 's.prix', $listDirn, $listOrder); ?>
						</th>
						<!--<th class="span2 center">
							<php echo JText::_('COM_ITINERARY_FIELD_ACTION'); ?>
						</th>-->
					</tr>
				</thead>
				<tbody>
			<?php	foreach($this->items as $i => $item)
                                {
                            
                                    if(isset($item->date_debut))
                                    {
                                        $class="";

                                        if($item->date_fin<Date('Y-m-d'))
                                        {
                                                $class.="bg-danger";
                                        }
                                        elseif($item->date_fin<=Date('Y-m-d',strtotime(Date('Y-m-d')." +1 month")))
                                        {
                                                $class.="bg-warning";
                                        }

                                        ?>
                                        <tr class="<?php echo $class;?>" >
                                            <td class="center">
                                                <?php echo JHtml::_('grid.id', $i, $item->clef_parcours."_".$item->date_fin); ?>
                                            </td>
                                            <td class="center">
                                                <?php echo $item->commandeNb; ?>
                                            </td>
                                            <!--<td class="center">
                                                <php  echo $item->factureNb; ?>
                                            </td>-->
                                            <td class="center">
                                                <?php echo date('d/m/Y',strtotime($item->date_creation)); ?>
                                            </td>
                                            <td class="text-justify">
                                                <?php 
                                                $duree=date('n',strtotime($item->date_fin)-strtotime($item->date_debut));

                                                if(!empty($item->factureNb))
                                                {
                                                    echo sprintf(JText::_('COM_ITINERARY_INVOICE_PRODUCT_SUBSCRIPTION_DESCRIPTION'),$item->duree,$_SESSION['parcoursMsgMax'],date('d/m/Y',strtotime($item->date_debut)),date('d/m/Y',strtotime($item->date_fin)));
                                                }
                                                else
                                                {
                                                    echo sprintf(JText::_('COM_ITINERARY_INVOICE_PRODUCT_SUBSCRIPTION_DESCRIPTION_NO_DATE'),$item->duree,$_SESSION['parcoursMsgMax']);
                                                }
                                                ?>
                                            </td>
                                            <td class="center">
                                                <?php echo htmlspecialchars_decode($item->titre,ENT_QUOTES); ?>
                                            </td>
                                            <td class="center">
                                                <?php
                                                if(empty($item->illustrations))
                                                {
                                                    echo '-';
                                                }
                                                else
                                                {
                                                    $thumbnail = $this->path_image . str_replace("main","thumb",$item->illustrations);
                                                    echo '<img src="'.$thumbnail.'" class="img-responsive center-block" alt="No image"/>';
                                                }
                                                ?>
                                            </td>
                                            <td class="center">
                                                <?php 
                                                    echo $item->titre_langue ? JHtml::_('image', 'mod_languages/' . $item->image_langue . '.gif', $item->titre_langue, array('title' => $item->titre_langue), true) . '&nbsp;' . $this->escape($item->titre_langue) : JText::_('JUNDEFINED');
                                                ?>
                                            </td>
                                            <td class="center">
                                                <?php echo $item->date_debut=="0000-00-00" ? "": date('d/m/Y',strtotime($item->date_debut)); ?>
                                            </td>
                                            <td class="center" id="date_fin">
                                                <?php echo $item->date_fin=="0000-00-00" ? "": date('d/m/Y',strtotime($item->date_fin)); ?>
                                            </td>
                                            <td class="center">
                                                <?php echo $item->nombre_messages.'/'.$_SESSION['parcoursMsgMax']; ?>
                                            </td>
                                            <td class="center">
                                                <?php echo money_format("%.2n", $item->prixHT); ?>
                                            </td>
                                        </tr>
                                <?php }
                                } ?>
				</tbody>
				<tfoot>
					<td colspan="12">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tfoot>
			</table>
		<?php }?>
		
	</div>
		
		
    <input type="hidden" id="clefParcours" name="clefParcours" value="" />
    <input type="hidden" id="clefParcours" name="clefParcours" value="" />
    <input type="hidden" id="dateDebut" name="dateDebut" value="" />


    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
    <?php echo JHtml::_('form.token'); ?>	
</form>