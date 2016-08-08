<?php

/**
 * Page type for building unique custom content without the need for special templates
 *
 * @author Michael Eckert
 *
 * @package IQ_Content_Builder
 *
 */
 
class ContentBuilderRow extends ContentBuilderBlock
{		
	private static $db = array(
	);

	private static $has_one = array(
		'ContentBuilderPage' => 'ContentBuilderPage',
	);
	
	private static $has_many = array(
		'ContentBuilderBlocks' => 'ContentBuilderBlock'
	);
	
	private static $singular_name = 'Content Row';
	
	private static $maximum_columns_per_row = 8;
	
	function getCMSFields()
	{		
		$fields = parent::getCMSFields();
		$fields->push( new HiddenField('ContentBuilderPageID',null,$fields->dataFieldByName('ContentBuilderPageID')->Value()) );
		if (!$this->ID)
		{
			$fields->addFieldToTab('Root.Main', HeaderField::create('savenote','You must save before adding content blocks',2) );
			$this->extend('updateCMSFields',$fields);
			return $fields;
		}

		// remove teh existing tab and gridfield
		$fields->removeByName('ContentBuilderBlocks');
		// create the new config and remove the Add button
		$gf_config = GridFieldConfig_RecordEditor::create()->addComponents(
			new GridFieldSortableRows('SortOrder')
		)->removeComponentsByType('GridFieldAddNewButton');
		// get all subclasses for ContentBuilderBlock and add an Add button for each, except the base class
		foreach(ClassInfo::subclassesFor('ContentBuilderBlock') as $modelClass)
		{
			if ($modelClass == 'ContentBuilderBlock') continue;
			$gf_config->addComponent(new GridFieldMultiTypeAddNewButton($modelClass));
		}
		// set formatting on the preview field
		$gf_config->getComponentByType('GridFieldDataColumns')->setFieldFormatting(array(
			'GridFieldPreview' => function($value,$item){
				return htmlspecialchars_decode($value);
			}
		));
		// create teh grid field
		$fields->addFieldToTab('Root.Main', $gf = GridField::create(
			'ContentBuilderBlocks',
			'Content Blocks',
			$this->ContentBuilderBlocks(),
			$gf_config
		));

		$this->extend('updateCMSFields',$fields);
		return $fields;
	}
	
	public function canAddAnotherColumn()
	{
		return !($this->Sections()->Last()->Count() >= $this->config()->maximum_columns_per_row);
	}

	public function canCreate($member = null) { return true; }
	public function canDelete($member = null) { return true; }
	public function canEdit($member = null)   { return true; }
	public function canView($member = null)   { return true; }
	public function canClone($member = null)  { return true; }
	
	public function validate()
	{
		$result = parent::validate();
		// make sure we don't have more than 12 columns in a row
		foreach($this->Sections() as $section)
		{
			if ($section->Count() > $this->config()->maximum_columns_per_row) $result->error("You've exceeded the maximum column count of ".$this->config()->maximum_columns_per_row,'ContentBuilderBlocks');
		}
		
		return $result;
	}
	
	/**
	 * Arranges the building blocks into lists so each row starts a new section, and columns can be treated as such in the template
	 * @returns object ArrayList
	 */
	public function Sections()
	{
		$sections = ArrayList::create();
		$columnCollection = ContentBuilderSection::create();
		foreach($this->ContentBuilderBlocks() as $block)
		{
			if ($block instanceof ContentBuilderRow)
			{
				// if we've collected columns, add them to the list before the this row
				if ($columnCollection->Count())
				{
					$sections->push($columnCollection);
				}
				// add the row
				$rowSection = ContentBuilderSection::create();
				$rowSection->push($block);
				$sections->push( $rowSection );
				// reset the columns section
				$columnCollection = ContentBuilderSection::create();
			}
			elseif ($block instanceof ContentBuilderColumn)
			{
				$columnCollection->push( $block );
			}
		}
		// check if we have any columns that may have not been added (if we didn't end with a row)
		if ($columnCollection->Count())
		{
			$sections->push( $columnCollection );
		}
		
		$this->extend('updateSections',$sections);
		return $sections;
	}
	
	public function forTemplate()
	{
		return $this->renderWith('ContentBuilderRow');
	}
	
	public function GridFieldPreview()
	{
		$html = '<div class="cb-row">';
		if ($this->Sections()->Count())
		{
			foreach($this->Sections() as $section)
			{
				$html .= $section->GridFieldPreview();
			}
		}
		else
		{
			$html .= '[ Empty ]';
		}		
		$html .= '</div>';
		return $html;
	}

	public function CloneBlock()
	{
		if (!$newItem = parent::CloneBlock()) return;
		$newItem->ContentBuilderPageID = $this->ContentBuilderPageID;
		$newItem->write();
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





