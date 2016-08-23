<?php

/**
 * Page type for building unique custom content without the need for special templates
 *
 * @author Michael Eckert
 *
 * @package IQ_Content_Builder
 *
 */
 
class ContentBuilderColumn_RawHtml extends ContentBuilderColumn
{
	private static $db = array(
		'Content' => 'Text'
	);
	
	private static $singular_name = 'Raw HTML';
	
	public function Contents()
	{
		return $this->Content;
	}
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->addFieldToTab('Root.Main', TextareaField::create('Content','Content') );
		return $fields;
	}
	

	public function GridFieldContents()
	{
		return htmlspecialchars(substr($this->Content,0,50));
	}
}