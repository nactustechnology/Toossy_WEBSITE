<?php
defined('_JEXEC') or die;

//make sure user is logged in
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_itinerary&view=feedbacks'); ?>" method="post" name="adminForm" id="adminForm">

    <legend class="legend"><?php echo JText::_("COM_ITINERARY_FEEDBACKS_LEGEND");?> :</legend>
    <div class="btn-toolbar">
        <div class="btn-group">
            <button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('message.cancel')">
                <i class="icon-arrow-left"></i> <?php echo JText::_('COM_ITINERARY_RETOUR_PARCOURS_LABEL'); ?>
            </button>
        </div>
    </div>
    
    <div class="parcoursblock container-fluid" id="j-main-container">
        <ul class="nav nav-tabs">
            <li role="presentation" class="active"><a  href="#notesList" aria-controls="notesList" role="tab" data-toggle="tab" ><?php echo JText::_('COM_ITINERARY_FEEDBACKS_NOTES_TAB'); ?></a></li>
            <li role="presentation"><a  href="#reportsList" aria-controls="reportsList" role="tab" data-toggle="tab" ><?php echo JText::_('COM_ITINERARY_FEEDBACKS_REPORTS_TAB'); ?></a></li>
        </ul>
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="notesList">
            <?php 
            if(empty($this->items))
            {
                echo '<div class="center">'.JText::_('COM_ITINERARY_FEEDBACKS_NO_NOTES').'</div>';			
            }
            else
            {
            ?>
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th class="span1 center">
                                <?php echo JHtml::_('grid.sort', 'COM_ITINERARY_FEEDBACKS_DATE', 'e.date_creation', $listDirn, $listOrder); ?>
                            </th>
                            <th class="span3 center">
                                <?php echo JHtml::_('grid.sort', 'COM_ITINERARY_FEEDBACKS_COMMENT', 'e.commentaire', $listDirn, $listOrder); ?>
                            </th>
                            <th class="span1 center">
                                <?php echo JHtml::_('grid.sort', 'COM_ITINERARY_FEEDBACKS_NOTE', 'e.note', $listDirn, $listOrder); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                <?php   foreach($this->items as $i => $item)
                        { ?>
                            <tr>					
                                <td class="center">
                                    <?php echo $item->date_creation; ?>
                                </td>
                                <td class="text-justify">
                                    <?php echo htmlspecialchars_decode($item->commentaire,ENT_QUOTES); ?>
                                </td>
                                <td class="center">
                                    <?php echo $item->note." / 5"; ?> 
                                </td>
                            </tr>
                        <?php
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
            <div role="tabpanel" class="tab-pane" id="reportsList"> 
                <?php 
            if(empty($this->items))
            {
                echo '<div class="center">'.JText::_('COM_ITINERARY_FEEDBACKS_NO_REPORTS').'</div>';			
            }
            else
            {
            ?>
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th class="span1 center">
                                <?php echo JText::_('COM_ITINERARY_FEEDBACKS_DATE'); ?>
                            </th>
                            <th class="span3 center">
                                <?php echo JText::_('COM_ITINERARY_FEEDBACKS_REPORT'); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                <?php   foreach($this->reports as $i => $item)
                        { ?>
                            <tr>					
                                <td class="center">
                                    <?php echo $item['date_creation']; ?>
                                </td>
                                <td class="text-justify">
                                    <?php echo htmlspecialchars_decode($item['commentaire'],ENT_QUOTES); ?>
                                </td>
                            </tr>
                        <?php
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
        </div>	
    </div>
		

    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />

    <?php echo JHtml::_('form.token'); ?>	
</form>