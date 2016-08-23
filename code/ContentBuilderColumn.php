<?php

/**
 * Page type for building unique custom content without the need for special templates
 *
 * @author Michael Eckert
 *
 * @package IQ_Content_Builder
 *
 */
 
class ContentBuilderColumn extends ContentBuilderBlock
{		
	private static $db = array(
		'VerticalAlign' => 'Boolean'
	);
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->insertBefore(CheckboxField::create('VerticalAlign','Align Vertically'), 'BackgroundColor');
		return $fields;
	}

	public function canCreate($member = null) { return ($this->ClassName != 'ContentBuilderColumn'); }
	public function canDelete($member = null) { return true; }
	public function canEdit($member = null)   { return true; }
	public function canView($member = null)   { return true; }
	public function canClone($member = null) { return ($this->ClassName != 'ContentBuilderColumn'); }

	public function forTemplate()
	{
		return $this->renderWith(array($this->ClassName,'ContentBuilderColumn'));
	}
	
	public function Contents()
	{
		return 'method "Contents" must be implimented in class '.$this->ClassName.' to display the content, or create a custom template';
	}
	
	protected function GridFieldContents()
	{
		return 'method "GridFieldContents" must be implimented in class '.$this->ClassName.' to display the content in the GridField';
	}
	
	public function GridFieldPreview($parentURL,$gridField)
	{
		$this->_parentURL = $parentURL;
		$this->_gridField = $gridField;
		return '<div class="cb-col">'.$this->GridFieldTitle().$this->GridFieldContents().'</div>';
	}
	
}

