<?php

/**
 * Page type for building unique custom content without the need for special templates
 *
 * @author Michael Eckert
 *
 * @package IQ_Content_Builder
 *
 */
 
class ContentBuilderBlock extends DataObject
{
	private static $db = array(
		'SortOrder' => 'Int',
		'ExtraCssClass' => 'Varchar(255)',
		'CustomCSS' => 'Varchar(255)',
		'PaddingTop' => 'Varchar(10)',
		'PaddingRight' => 'Varchar(10)',
		'PaddingBottom' => 'Varchar(10)',
		'PaddingLeft' => 'Varchar(10)',
		'BackgroundColor' => 'Varchar(20)',
		'BorderTop' => 'Varchar(30)',
		'BorderRight' => 'Varchar(30)',
		'BorderBottom' => 'Varchar(30)',
		'BorderLeft' => 'Varchar(30)',
	);

	private static $has_one = array(
		'ContentBuilderBlock' => 'ContentBuilderBlock',
	);

	private static $summary_fields = array(
		'ContentBuilderBlockType' => 'Type',
		'GridFieldPreview' => 'Contents'
	);
	
	private static $default_sort = "SortOrder ASC";
	
	function getCMSFields()
	{
		Requirements::css(CONTENTBUILDER_DIR."/css/spectrum.css");
		Requirements::javascript(CONTENTBUILDER_DIR."/javascript/spectrum.js");
		Requirements::javascript(CONTENTBUILDER_DIR."/javascript/ContentBuilderPage_cms.js");
		$fields = parent::getCMSFields();
		$fields->insertBefore( HeaderField::create('contentbuilderblocktype',$this->ContentBuilderBlockType(),2),'ExtraCssClass' );
		if ($this->ClassName == 'ContentBuilderBlock')
		{
			// a type must be selected before we can do anything with it
		}
		$fields->push( new HiddenField('SortOrder',null,$fields->dataFieldByName('SortOrder')->Value()) );
		$fields->push( new HiddenField('ContentBuilderBlockID',null,$fields->dataFieldByName('ContentBuilderBlockID')->Value()) );
		
		$fields->addFieldToTab('Root.Style', SimpleColorPickerField::create('BackgroundColor','Background Color') );
		
		$fields->addFieldToTab('Root.Style', $paddingFields = FieldGroup::create('Padding<br />(ex. 10px or 3%)') );
		$paddingFields->push( TextField::create('PaddingTop','Top') );
		$paddingFields->push( TextField::create('PaddingRight','Right') );
		$paddingFields->push( TextField::create('PaddingBottom','Bottom') );
		$paddingFields->push( TextField::create('PaddingLeft','Left') );
		
		$fields->addFieldToTab('Root.Style', $borderFields = FieldGroup::create('Borders') );
		$borderStyles = array(
			'solid' => 'Solid',
			'dashed' => 'Dashed',
			'dotted' => 'Dotted',
			'double' => 'Double'
		);
		foreach(array('BorderTop','BorderRight','BorderBottom','BorderLeft') as $area)
		{
			$fields->removeByName($area);
			$defaults = explode(' ',$this->{$area});
			$Border_Group = FieldGroup::create($area);
			$Border_Group->push( HeaderField::create($area.'title',str_replace('Border','',$area),3) );
			$Border_Group->push( TextField::create($area.'[width]','Width Pixels (ex. 5)')->setValue(isset($defaults[0])?$defaults[0]:null) );
			$Border_Group->push( DropdownField::create($area.'[style]','Style',$borderStyles)->setValue(isset($defaults[1])?$defaults[1]:null) );
			$Border_Group->push( SimpleColorPickerField::create($area.'[color]','Color')->setValue(isset($defaults[2])?$defaults[2]:null) );
			if ($defaults[2])
			{
				$Border_Group->push( LiteralField::create($area.'colorpreview','<span style="display:block;padding-top:25px;background-color:'.$defaults[2].';"></span>') );
			}
			$borderFields->push( $Border_Group );
		}
		
		$developer_fields = array(
			'ExtraCssClass',
			'CustomCSS'
		);
		foreach($developer_fields as $dev_field) { $fields->removeByName($dev_field); }
		
		$this->extend('updateCMSFields',$fields);
		return $fields;
	}

	public function canCreate($member = null) { return true; }
	public function canDelete($member = null) { return true; }
	public function canEdit($member = null)   { return true; }
	public function canView($member = null)   { return true; }
	
	public function validate()
	{
		$result = parent::validate();
		if ($this->ClassName == 'ContentBuilderBlock') $result->error('You must select a block type','ClassName');
		foreach(array('BorderTop','BorderRight','BorderBottom','BorderLeft') as $area)
		{
			if (isset($_REQUEST[$area]['width']) && !empty($_REQUEST[$area]['width']))
			{
				if (empty($_REQUEST[$area]['style'])) $result->error('Please select a style for '.FormField::name_to_label($area),$area);
				if (empty($_REQUEST[$area]['color'])) $result->error('Please choose a color for '.FormField::name_to_label($area),$area);
			}
		}
		return $result;
	}
	
	public function onBeforeWrite()
	{
		parent::onBeforeWrite();
		foreach(array('BorderTop','BorderRight','BorderBottom','BorderLeft') as $area)
		{
			if (isset($_REQUEST[$area]['width']) && !empty($_REQUEST[$area]['width']))
			{
				$this->{$area} = preg_replace('/[^0-9]/','',$_REQUEST[$area]['width']).'px '.$_REQUEST[$area]['style'].' '.$_REQUEST[$area]['color'];
			}
		}
	}
	
	/**
	 * returns a list of the different types of building blocks that can be inserted into a page
	 * @returns array [$modelClass => $modelClassTitle]
	 */
	public function ContentBuilderBlockTypes()
	{
		$blockTypes = array();
		foreach(ClassInfo::subclassesFor('ContentBuilderBlock') as $modelClass)
		{
			if ($modelClass == 'ContentBuilderBlock') continue;
			$blockTypes[$modelClass] = singleton($modelClass)->ContentBuilderBlockType();
		}
		$this->extend('updateContentBuilderBlockTypes',$blockTypes);
		return $blockTypes;
	}
			
	public function ContentBuilderBlockType()
	{
		return $this->i18n_singular_name();
	}
	
	public function Level()
	{
		return ($this->ContentBuilderRowID) ? $this->ContentBuilderRow() + 1 : 1;
	}
	
	public function forTemplate()
	{
		return 'method forTemplate must be called in class '.$this->ClassName;
	}
	
	public function Preview()
	{
		return $this->forTemplate();
	}

	public function GridFieldPreview()
	{
		return null;
	}
	
	public function CustomStyling()
	{
		$styles = array();
		// build any padding
		if ($this->PaddingTop) $styles[] = 'padding-top:'.$this->PaddingTop;
		if ($this->PaddingRight) $styles[] = 'padding-right:'.$this->PaddingRight;
		if ($this->PaddingBottom) $styles[] = 'padding-bottom:'.$this->PaddingBottom;
		if ($this->PaddingLeft) $styles[] = 'padding-left:'.$this->PaddingLeft;
		
		// build any borders
		if ($this->BorderTop) $styles[] = 'border-top:'.$this->BorderTop;
		if ($this->BorderRight) $styles[] = 'border-right:'.$this->BorderRight;
		if ($this->BorderBottom) $styles[] = 'border-bottom:'.$this->BorderBottom;
		if ($this->BorderLeft) $styles[] = 'border-left:'.$this->BorderLeft;
		
		// add any background
		if ($this->BackgroundColor) $styles[] = 'background-color:'.$this->BackgroundColor;
		
		$style = implode('; ',$styles);
		
		// add user styling
		$style .= $this->CustomCSS;
		return $style;
	}
	
	public function CloneBlock()
	{
		$className = $this->ClassName;
		if (!singleton($className)->canClone()) return;
		$newItem = new $className($this->toMap(), false, $this->model);
		$newItem->ID = 0;
		$newItem->ContentBuilderBlockID = $this->ContentBuilderBlockID;
		$newItem->write();
		return $newItem;
	}
}






