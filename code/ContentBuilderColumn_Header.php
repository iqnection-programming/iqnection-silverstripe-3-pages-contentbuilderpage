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
		'Align' => "Enum('Left,Center,Right','Left')",
		'Content' => 'Text',
	);
	
	private static $singular_name = 'Header';
	
	public function Contents()
	{
		return '<h'.$this->Type.' style="text-align:'.$this->Align.';'.$this->CustomCSS.'">'.$this->Content.'</h'.$this->Type.'>';
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
	
	public function GridFieldPreview()
	{
		return $this->Contents();
	}
}