<?php

/**
 * Page type for building unique custom content without the need for special templates
 *
 * @author Michael Eckert
 *
 * @package IQ_Content_Builder
 *
 */
class ContentBuilderColumn_Header extends ContentBuilderColumn
{
	private static $db = array(
		'Type' => "Enum('1,2,3,4,5,6','2')",
		'Align' => "Enum('Default,Left,Center,Right','Default')",
		'FontWeight' => "Enum('Default,Bold,Normal,Light','Default')",
		'FontStyle' => "Enum('Default,Normal,Italic','Default')",
		'Content' => 'Text',
	);
	
	private static $singular_name = 'Header';
	
	private static $defaults = array(
		'Content' => 'Content Header'
	);
	
	public function Contents()
	{
		$style = parent::CustomStyling();
		if ($this->Align != 'Default')
		{
			$style .= 'text-align:'.strtolower($this->Align).';';
		}
		if ($this->FontWeight != 'Default')
		{
			$style .= 'font-weight:'.strtolower($this->FontWeight).';';
		}
		if ($this->FontStyle != 'Default')
		{
			$style .= 'font-style:'.strtolower($this->FontStyle).';';
		}
		$style = ($style) ? ' style="'.$style.'"' : null;
		return '<h'.$this->Type.$style.'>'.$this->Content.'</h'.$this->Type.'>';
	}
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$choices = array();
		foreach($this->relObject('Type')->enumValues() as $value)
		{
			$choices[$value] = 'Heading '.$value;
		}
		$fields->dataFieldByName('Type')->setSource($choices);
		$fields->replaceField('Content', TextField::create('Content','Content (HTML allowed)') );
		return $fields;
	}
	
	public function GridFieldContents()
	{
		return '<h'.$this->Type.'>'.$this->Content.'</h'.$this->Type.'>';
	}
}




