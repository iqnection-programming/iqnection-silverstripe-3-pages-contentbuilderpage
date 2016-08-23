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
	
	private static $defaults = array(
		'Content' => '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>'
	);
	
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
	
	public function GridFieldContents()
	{
		return $this->relObject('Content')->Summary(20);
	}
}