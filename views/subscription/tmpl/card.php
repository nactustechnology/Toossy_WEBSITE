
<?php
defined('_JEXEC') or die;
setlocale(LC_MONETARY,"fr_FR");
JHtml::_('behavior.formvalidator');
$itinerary = $this->itinerary;
?>
    <legend class="legend"><?php echo JText::_("COM_ITINERARY_SUBSCRIPTION_CARD_LEGEND");?> :</legend>
    <script src="https://js.stripe.com/v2/"></script>
    <script src="https://js.stripe.com/v3/"></script>
    <div class="form-horizontal voffset1 parcoursblock opaque">
        <div class="center">
                <img src="<?php echo $this->path.'logo_1.png'; ?>" class="card" alt="No image"/>
                <img src="<?php echo $this->path.'logo_2.png'; ?>" class="card" alt="No image"/>
                <img src="<?php echo $this->path.'logo_3.png'; ?>" class="card" alt="No image"/>
        </div>
        <div class="panel-body">
            <?php
            if(isset($this->source))
            {
            ?>
                <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active"><a href="#availableCard" aria-controls="availableCard" role="tab" data-toggle="tab"><?php echo JText::_('COM_ITINERARY_SUBSCRIPTION_CARD_AVAILABLE'); ?></a></li>
                        <!--<li role="presentation" id="tab_new_iban"><a href="#newiban" aria-controls="newiban" role="tab" data-toggle="tab"><?php echo ''; // JText::_('COM_ITINERARY_SUBSCRIPTION_IBAN_NEW'); ?></a></li>-->
                        <li role="presentation" id="tab_new_card"><a href="#newcard" aria-controls="newcard" role="tab" data-toggle="tab"><?php echo JText::_('COM_ITINERARY_SUBSCRIPTION_CARD_NEW'); ?></a></li>
                </ul>
            <?php
            }
            else
            {
            ?>
                <ul class="nav nav-tabs" role="tablist">
                        <!--<li role="presentation" id="tab_new_iban"><a href="#newiban" aria-controls="newiban" role="tab" data-toggle="tab"><?php echo ''; // JText::_('COM_ITINERARY_SUBSCRIPTION_IBAN_NEW'); ?></a></li>-->
                        <li role="presentation" id="tab_new_card"><a href="#newcard" aria-controls="newcard" role="tab" data-toggle="tab"><?php echo JText::_('COM_ITINERARY_SUBSCRIPTION_CARD_NEW'); ?></a></li>
                </ul>
            <?php
            }
            ?>

            <div class="tab-content">
                <?php
                if(isset($this->source))
                {
                    $source=$this->source;
                ?>
                    <div role="tabpanel" class="tab-pane active" id="availableCard">
                        <form action="<?php echo JRoute::_('index.php?option=com_itinerary&task=subscription.discardMeansOfPayment&tmpl=component'); ?>" method="post" id="payment-form-1">
                            <div class="row group">
                                    <div class="span8"><u><?php echo JText::_('COM_ITINERARY_SUBSCRIPTION_CARD_OWNER').' :';?></u></div>
                                    <div class="span6"><?php echo $source->name;?></div>
                            </div>

                            <div class="row group voffset3">
                                    <div class="span6"><u><?php echo JText::_('COM_ITINERARY_SUBSCRIPTION_CARD_NUMBER').' :'; ?></u></div><div class="span5"><u><?php echo JText::_('COM_ITINERARY_SUBSCRIPTION_CARD_EXPIRATION_DATE').' :';?></u></div>
                                    <div class="span6"><?php echo 'XXXX XXXX XXXX '.$source->last4;?></div><div class="span5"><? echo sprintf("%02d", $source->exp_month).'/'.substr($source->exp_year,2,3); ?></div>
                            </div>

                            <div class="row center voffset3">
                                    <button id="formDiscardCard" class="btn btn-warning" onclick="discardConfirmation()"><?php echo JText::_('COM_ITINERARY_SUBSCRIPTION_CARD_DISCARD');?></button>
                            </div>
                        </form>
                    </div>
                <?php 
                } 
                ?>
                <div role="tabpanel" class="tab-pane" id="newcard">
                    <!-- 1) Set up stripe elements-->
                    <script>
                        //publishable key
                        var stripe = Stripe('pk_test_pkNQf9te1KBYMPT4MqOrkVZM');
                        var elements = stripe.elements();
                    </script>
                    
                    <!-- 2) Create your payment form-->
                    <form action="<?php echo JRoute::_('index.php?option=com_itinerary&task=subscription.associateCard&tmpl=component'); ?>" method="post" id="payment-form-2">
                        <div class="row group">
                            <div><u><?php echo JText::_('COM_ITINERARY_SUBSCRIPTION_CARD_OWNER');?></u></div>
                            <input name="cardholder-name" required type="text" class="input_card_large"  placeholder="James Bridge" />
                        </div>

                        <div class="row group voffset3">						
                                <label for="card-element">
                                    <u>
                                        <?php echo  JText::_('COM_ITINERARY_SUBSCRIPTION_CARD_NUMBER').' / '.
                                        JText::_('COM_ITINERARY_SUBSCRIPTION_CARD_EXPIRATION_DATE').' / '.
                                        JText::_('COM_ITINERARY_SUBSCRIPTION_CARD_CRYPTOGRAMME');?>
                                    </u>
                                </label>
                                <div id="card-element">
                                <!-- a Stripe Element will be inserted here. -->
                                </div>
                                <!-- Used to display Element errors -->
                                <div id="card-errors"></div>
                        </div>

                        <div class="row center voffset4">
                                <button id="formAssociateCard" class="btn btn-success" onclick="cardAssociationConfirmation()"><?php echo JText::_('COM_ITINERARY_SUBSCRIPTION_CARD_ASSOCIATE');?></button>
                        </div>
                    </form>

                    <script>
                        // Custom styling can be passed to options when creating an Element.
                        var style = {
                          base: {
                                // Add your base input styles here. For example:
                                fontSize: '16px',
                                lineHeight: '24px'
                          }
                        };
                        // Create an instance of the card Element
                        var card = elements.create('card', {style: style});
                        // Add an instance of the card Element into the `card-element`
                        card.mount('#card-element');
                        //listen to change events on the card Element and display any errors
                        card.addEventListener('change', function(event) {
                                var displayError = document.getElementById('card-errors');
                                if (event.error) {
                                        displayError.textContent = event.error.message;
                                } else {
                                        displayError.textContent = '';
                                }
                        });

                        <!-- 3) Create a token to securely transmit card information-->

                        var cardForm = document.getElementById('payment-form-2');
                        cardForm.addEventListener('submit', function(event) {
                                event.preventDefault();

                                stripe.createToken(card).then(function(result) {
                                        if (result.error) {
                                                // Inform the user if there was an error
                                                var errorElement = document.getElementById('card-errors');
                                                errorElement.textContent = result.error.message;
                                        } else {
                                                // Send the token to your server
                                                stripeTokenHandler(result.token);
                                        }
                                });
                        });

                        <!-- 4) Submit the token and the rest of your form to your server-->
                        function stripeTokenHandler(token) {
                                // Insert the token ID into the form so it gets submitted to the server
                                var form = document.getElementById('payment-form-2');
                                var hiddenInput = document.createElement('input');
                                hiddenInput.setAttribute('type', 'hidden');
                                hiddenInput.setAttribute('name', 'stripeToken');
                                hiddenInput.setAttribute('value', token.id);
                                form.appendChild(hiddenInput);

                                // Submit the form
                                form.submit();
                        }
                    </script>
                </div>
                <!--!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!-->
                <!--<div role="tabpanel" class="tab-pane" id="newiban">                    
                    <!--2) Create your payment form
                    <form action="<?php echo ''; // JRoute::_('index.php?option=com_itinerary&task=subscription.associateIban&tmpl=component'); ?>" method="post" id="payment-form-3">
                        <div class="row group">
                            <div><u><?php echo ''; // JText::_('COM_ITINERARY_SUBSCRIPTION_IBAN_OWNER');?></u></div> 
                            <input id="ibanholdername" required type="text" class="input_card_large"  placeholder="James Bridge" />
                        </div>

                        <div class="row group">
                            <div><u><?php echo ''; // JText::_('COM_ITINERARY_SUBSCRIPTION_IBAN_NUMBER');?></u></div>
                            <input id="ibannumber" required type="text" class="input_card_large"  placeholder="Entrez votre iban ici" />
                            <div id="ibanerrors"></div>
                        </div>

                        <div class="row center voffset4">
                                <button id="formAssociateIban" class="btn btn-success" onclick="ibanAssociationConfirmation()"><?php echo '';// JText::_('COM_ITINERARY_SUBSCRIPTION_IBAN_ASSOCIATE');?></button>
                        </div>
                    </form>

                    <script>
                        var ibanForm = document.getElementById('payment-form-3');
                        
                        function registerIban() {
                        
                            

                            var ibanholdername = document.getElementById('ibanholdername').value ;
                            var ibannumber = document.getElementById('ibannumber').value ;
                            
                            
                            
                            stripe.createSource({
                                type: 'sepa_debit',
                                sepa_debit: {
                                    iban: ibannumber,
                                },
                                currency: 'eur',
                                owner: {
                                    name: ibanholdername,
                                },
                            }).then(function(result) {
                                // handle result.error or result.source
                                if (result.error) {
                                    // Inform the user if there was an error
                                    var errorElement = document.getElementById('ibanerrors');
                                    errorElement.textContent = result.error.message;
                                    
                                } else {
                                    console.log(result);
                                    // Send the source to your server
                                    var form = document.getElementById('payment-form-3');
                                    
                                    var hiddenInput = document.createElement('input');
                                    hiddenInput.setAttribute('type', 'hidden');
                                    hiddenInput.setAttribute('name', 'stripeToken');
                                    hiddenInput.setAttribute('value', result.token.id);
                                    form.appendChild(hiddenInput);

                                    // Submit the form
                                    form.submit();
                                }
                            });
                        };
                        
                    </script>
                </div>-->
                <!--!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!-->
            </div>	
        </div>	

        <div class="row center voffset3">
                        <?php echo JText::_('COM_ITINERARY_SUBSCRIPTION_CARD_SECURITY');?>
        </div>
    </div>