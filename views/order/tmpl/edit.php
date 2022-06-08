<!--subscription edit-->
<?php
defined('_JEXEC') or die;

setlocale(LC_MONETARY,"fr_FR");
JHtml::_('behavior.formvalidator');

$profile=$this->profile;
$itineraryList=$this->itineraryList;
$domainPath = 'http://'.$_SERVER['HTTP_HOST'].'/mon-compte';
?>
<div class="container">
    <form action="#" method="post" id="adminForm" class="form-validate subscription_form_width center-block">	

            <legend class="legend notprintable"><?php echo JText::_("COM_ITINERARY_ORDER_SUBSCRIPTION_SETTINGS");?> :</legend>
            <div class="btn-toolbar">
                <div class="btn-group">
                    <button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('itinerary.cancel')">
                            <i class="icon-arrow-left"></i><?php echo JText::_('COM_ITINERARY_RETOUR_PARCOURS_LABEL'); ?>
                    </button>
                    <span type="button" class="btn btn-primary " onclick="addSubscriptionRow();">
                            <i class="icon-new"></i> <?php echo JText::_('COM_ITINERARY_BUTTON_ADD_NEW_SUBSCRIPTION'); ?>
                    </span>
                </div>        
            </div>
            
            <div class="form-inline parcoursblock notprintable" id="subscriptionsList">
                <div id="subscription_1" class="row center rowSubscriptionsOrder voffset2">
                    <div class="span2 center">
                        <?php echo JText::_("COM_ITINERARY_ORDER_SUBSCRIPTION_FOR");?> :
                    </div>
                    <div class="span4 center">
                        <select id="idSelectedParcours_1" class="rowSubscriptionsOrderItinerarySelect" onchange="copyItineraryName(1)">
                            <option value="undefined" selected="true"><?php echo JText::_("COM_ITINERARY_ORDER_SELECT_ITINERARY"); ?></option>
                            <!--<option value="0"> echo JText::_("COM_ITINERARY_ORDER_SELECT_NEW_ITINERARY"); </option>-->
                            <?php
                            foreach($itineraryList as $itinerary)
                            {

                                echo '<option value="'.$itinerary['clef'].'">'.$itinerary['titre'].'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="span2">
                        <?php echo str_replace("\\","",JText::_("COM_ITINERARY_ORDER_SUBSCRIPTION_DURATION"));?> : 
                    </div>
                    <div class="span3 center">
                        <select id="durationSelectedParcours_1" class="rowSubscriptionsOrderSelect" onchange="copySubscriptionDuration(1)">
                            <option value="undefined" selected="true"><?php echo JText::_("COM_ITINERARY_ORDER_SELECT_DURATION"); ?></option>
                            <option value="1">1 <?php echo JText::_("COM_ITINERARY_SUBSCRIPTION_MONTH"); ?></option>
                            <option value="3">3 <?php echo JText::_("COM_ITINERARY_SUBSCRIPTION_MONTH"); ?></option>
                            <option value="6">6 <?php echo JText::_("COM_ITINERARY_SUBSCRIPTION_MONTH"); ?></option>
                            <option value="12">12 <?php echo JText::_("COM_ITINERARY_SUBSCRIPTION_MONTH"); ?></option>
                        </select>
                    </div>
                    <div class="span1">
                        <span type="button" class="btn btn-primary pull-right align-middle rowSubscriptionsOrderTrash hidden" onclick="">
                            <i class="icon-trash"></i>
                        </span>
                    </div>
                </div>
            </div>

            <legend class="legend notprintable"><?php echo JText::_("COM_ITINERARY_SUBSCRIPTION_SUMMARY");?> :</legend>
            <div class="form-horizontal parcoursblock voffset2">
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
                        <div class="span5 address"><?php echo JText::_("COM_ITINERARY_CPY_ADRESS_1");?></div>
                        <div class="parcours span3 text-right"><?php echo JText::_("COM_ITINERARY_EXPIRING_DATE");?> :</div>
                        <div class="span3"><?php echo date('d/m/Y', strtotime("+2 months"));?></div>

                        
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


            
                <div class="parcours span12 voffset5"><u><?php echo JText::_("COM_ITINERARY_SUBSCRIPTION_CHARACTERISTICS");?> :</u></div>
                <input name="subscriptionArray" id="subscriptionArray" type="hidden" value="undefined#undefined"/>
                <table class="table table-bordered table-hover"  id="itineraryList">
                    <thead>
                        <tr>
                            <th class="span2 center">
                                <?php echo JText::_('COM_ITINERARY_FIELD_PARCOURS_TITLE_DESC'); ?>
                            </th>
                            <th class="span4 center">
                                <?php echo JText::_('COM_ITINERARY_INVOICE_PRODUCT_DESCRIPTION_LABEL'); ?>
                            </th>
                            <th class="span2 center">
                                <?php echo JText::_('COM_ITINERARY_ORDER_MONTHLY_PRICE_HT'); ?>
                            </th>
                            <th class="span2 center">
                                <?php echo JText::_('COM_ITINERARY_PRICES_DUREE'); ?>
                            </th>
                            <th class="span2 center">
                                <?php echo JText::_('COM_ITINERARY_FIELD_PRIX_HT_LABEL'); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="subscriptionDetailsList">
                        <tr id="subscription_details_1">
                            <td id="1" class="hidden rowData">undefined#undefined</td>
                            
                            <td id="subscription_titre_1" class="center bg-danger">
                                <?php echo JText::_("COM_ITINERARY_ORDER_CHOOSE_ITINERARY_TYPE"); ?>
                            </td>
                            <td id="subscription_description_1" class="text-justify bg-danger">
                                <?php echo JText::_("COM_ITINERARY_ORDER_CHOOSE_ITINERARY_DURATION"); ?>
                            </td>
                            <td id="subscription_monthly_price_HT_1" class="center bg-danger">
                                <?php echo JText::_("COM_ITINERARY_ORDER_CHOOSE_ITINERARY_DURATION"); ?>
                            </td>
                            <td id="subscription_duration_1" class="center bg-danger">
                                <?php echo JText::_("COM_ITINERARY_ORDER_CHOOSE_ITINERARY_DURATION"); ?>
                            </td>
                            <td id="subscription_price_HT_1" class="center bg-danger">
                                <?php echo JText::_("COM_ITINERARY_ORDER_CHOOSE_ITINERARY_DURATION"); ?>
                            </td>
                        </tr>            
                    </tbody>
                </table>
                
                <div class="row voffset3">
                    <div class="clearfix"></div>
                    <div class="parcours span12"><u><?php echo JText::_("COM_ITINERARY_ORDER_PROMOTION"); ?> :</u></div>
                    <div class="clearfix"></div>
                    <div class="parcours span12">
                        <div class="parcours form-group span6">
                            <input class="span5" type="text" name="codePromo" id="codePromo" placeholder="<?php echo JText::_("COM_ITINERARY_ORDER_PROMOTION_PLACEHOLDER"); ?>"/>
                            <button type="button" class="btn btn-primary" id="checkCodePromo">OK</button>
                        </div>
                        <div class="parcours span4 push-right" id="typePromo"></div>
                        <input type="hidden" id="promoValue" value="0"/>
                        <div class="parcours span2 push-right" id="montantPromo" ></div>
                    </div>
                    
                </div>
                
                <div class="row voffset3">
                    <div class="clearfix"></div>
                        <div class="parcours span12"><u><?php echo JText::_("COM_ITINERARY_ORDER_TARIFICATION");?> :</u></div>
                    <div class="clearfix"></div>
                        <div class="parcours span3 offset3"><?php echo JText::_("COM_ITINERARY_FIELD_PRIX_HT_LABEL"); ?> :</div>
                        <div class="span3" id="prix_ht"></div>
                    <div class="clearfix"></div>
                        <div class="parcours span3 offset3"><?php echo sprintf(JText::_("COM_ITINERARY_FIELD_TVA_LABEL"),$this->tvaRate*100); ?> :</div>
                        <div class="span3" id="tva"></div>
                    <div class="clearfix"></div>
                        <div class="parcours span3 offset3"><?php echo JText::_("COM_ITINERARY_FIELD_PRIX_TTC_LABEL"); ?> :</div>
                        <div class="span3" id="prix_ttc"></div>
                    <div class="clearfix"></div>

                    <div id="paymentTypeArea" class="container parcours center span6 offset3 voffset2"><strong><u><?php echo JText::_('COM_ITINERARY_INVOICE_PAYMENT_TYPE_CHOICE'); ?></u></strong>
                        <label class="radio-inline" for="cardRadio"><input checked="checked" name="paymentType" type="radio" value="card" id="cardRadio" /> <?php echo JText::_('COM_ITINERARY_INVOICE_PAYMENT_TYPE_CARD'); ?></label>
                        <label class="radio-inline" for="ibanRadio"><input name="paymentType" type="radio" value="iban" id="ibanRadio" /> <?php echo JText::_('COM_ITINERARY_INVOICE_PAYMENT_TYPE_IBAN'); ?></label>
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
            
        
            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>	
    </form>   
</div>