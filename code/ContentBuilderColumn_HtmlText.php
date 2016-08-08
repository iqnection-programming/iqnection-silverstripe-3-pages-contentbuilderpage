<?php

/**
 * Page type for building unique custom content without the need for special templates
 *
 * @author Michael Eckert
 *
 * @package IQ_Content_Builder
 *
 */
 
class ContentBuilderColumn_HtmlText extends ContentBuilderColumn
{
	private static $db = array(
		'Content' => 'HTMLText'
	);
	
	private static $singular_name = 'HTML Text';
	
	public function Contents()
	{
		return $this->relObject('Content')->forTemplate();
	}
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->addFieldToTab('Root.Main', HTMLEditorField::create('Content','Content') );
		return $fields;
	}
	
	public function GridFieldPreview()
	{
		return $this->relObject('Content')->Summary(20);
	}
}