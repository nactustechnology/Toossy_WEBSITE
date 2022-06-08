<?php
defined('_JEXEC') or die;
jimport('joomla.form.formfield');

class JFormFieldItineraryToggleButton extends JFormField
{
	public $type = 'ItineraryToggleButton';
	public function getInput()
	{
		// Initialize variables.
		$html ='';
		// Initialize some field attributes.
			if($this->value==1)			{				$classGreen = 'btn btn-success active';				$classRed = 'btn';				$checkedGreen = 'checked';				$checkedRed = '';			}			else			{				$classGreen = 'btn';				$classRed = 'btn btn-danger active';				$checkedGreen = '';				$checkedRed = 'checked';			}												/*$document = JFactory::getDocument();			$document->addScriptDeclaration('						(function($)						{							$(document).ready(									$("#'.$this->id.'_1").on(\'click\', 										function ()										{											$(this).button(\'complete\'); // button text will be "finished!"										}									)																		$("#'.$this->id.'_2").on(\'click\', 										function ()										{											$(this).button(\'complete\'); // button text will be "finished!"										}									)								)						})					');*/						$html='							<div class="btn-group" data-toggle="buttons">												<label class="'.$classGreen.'">							<input type="radio" name="'.$this->name.'" id="'.$this->id.'_1" value="1" autocomplete="off" '.$checkedGreen.'>'.JText::_('JYes').'						</label>						<label class="'.$classRed.'">							<input type="radio" name="'.$this->name.'" id="'.$this->id.'_0" value="0" autocomplete="off" '.$checkedRed.'>'.JText::_('JNo').'						</label>					</div>';

		return $html;
	}
}