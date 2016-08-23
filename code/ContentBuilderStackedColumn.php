<?php

/**
 *
 * @author Michael Eckert
 *
 * @package IQ_Content_Builder
 *
 * This class acts just like a row, except all child blocks will be stacked vertically in a single column
 */
class ContentBuilderStackedColumn extends ContentBuilderColumn
{
	private static $db = array(
	);

	private static $has_one = array(
	);
	
	private static $has_many = array(
		'ContentBuilderBlocks' => 'ContentBuilderBlock'
	);
	
	private static $singular_name = 'Stacked Column';
	
	private static $can_hold_blocks = true;
	
	function getCMSFields()
	{		
		$fields = parent::getCMSFields();
		if (!$this->ID)
		{
			$fields->addFieldToTab('Root.Main', HeaderField::create('savenote','You must save before adding content blocks',2) );
			$this->extend('updateCMSFields',$fields);
			return $fields;
		}

		// remove teh existing tab and gridfield
		$fields->removeByName('ContentBuilderBlocks');
			
		// create teh grid field
		$fields->addFieldToTab('Root.Main', $this->GridField());

		$this->extend('updateCMSFields',$fields);
		return $fields;
	}

	protected function GridField()
	{
		$gf_config = GridFieldConfig_RecordEditor::create()
			->addComponents( new GridFieldSortableRows('SortOrder')	)
			->removeComponentsByType('GridFieldAddNewButton')
			->removeComponentsByType('GridFieldEditButton')
			->removeComponentsByType('GridFieldDeleteAction')
			->removeComponentsByType('GridFieldSortableHeader')
			->addComponent(new GridFieldContentBuilderActionsHandler());
		$gf_config->getComponentsByType('GridFieldPaginator')->First()->setItemsPerPage(999);
			
		// get all subclasses for ContentBuilderBlock and add an Add button for each, except the base class
		foreach(ClassInfo::subclassesFor('ContentBuilderBlock') as $modelClass)
		{
			if (in_array($modelClass,array('ContentBuilderBlock',$this->ClassName))) continue;
			$gf_config->addComponent(new GridFieldMultiTypeAddNewButton($modelClass));
		}
		// set formatting on the preview field
//		$gf_config->getComponentByType('GridFieldDataColumns')
//			->setFieldFormatting(array(
//				'GridFieldPreview' => function($value,$item){
//					return htmlspecialchars_decode($value);
//				}
//			));
			
		// create teh grid field
		return GridField::create(
			'ContentBuilderBlocks',
			'Content Blocks',
			$this->ContentBuilderBlocks(),
			$gf_config
		);
	}
	
	public function canCreate($member = null) { return true; }
	public function canDelete($member = null) { return true; }
	public function canEdit($member = null)   { return true; }
	public function canView($member = null)   { return true; }
	
	/**
	 * Arranges the building blocks into lists so each row starts a new section, and columns can be treated as such in the template
	 * @returns object ArrayList
	 */
	public function Sections()
	{
		$sections = ArrayList::create();
		foreach($this->ContentBuilderBlocks() as $block)
		{
			$sections->push( $block );
		}
		
		$this->extend('updateSections',$sections);
		return $sections;
	}
	
	public function forTemplate()
	{
		return $this->renderWith('ContentBuilderStackedColumn');
	}
	
	public function GridFieldPreview($parentURL,$gridField)
	{
		$this->_parentURL = $parentURL;
		$this->_gridField = $gridField;
		$html = '<div class="cb-col cb-stacked-col">'.$this->GridFieldTitle();
		$html .= '<div class="cb-holder-contents">';
		$html .= $this->GridFieldContents();
		$html .= '</div>';
		$html .= '</div>';
		return $html;
	}
	
	protected function GridFieldContents()
	{
		$html = '';
		if ($this->Sections()->Count())
		{
			$html .= '<div class="cb-section">';
			$childLink = ($this->_parentURL) ? Controller::join_links($this->_parentURL,$this->ID,'ItemEditForm/field/ContentBuilderBlocks/item') : null;
			$myGridField = $this->GridField();
			if ($this->_gridField) { $myGridField->setForm($this->_gridField->getForm()); }
			foreach($this->Sections() as $section)
			{
				$html .= $section->GridFieldPreview($childLink,$myGridField);
			}
			$html .= '</div>';
		}
		return ($html) ? $html : '<div style="text-align:center;">[ Empty ]</div>';
	}
	
	public function CloneBlock()
	{
		if (!$newItem = parent::CloneBlock()) return;
		foreach($this->ContentBuilderBlocks() as $child)
		{
			if ($clonedChild = $child->CloneBlock())
			{
				$newItem->ContentBuilderBlocks()->add($clonedChild);
			}
		}
		return $newItem;
	}

}