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
		'ReferenceTitle' => 'Varchar(255)',
		'ExtraCssClass' => 'Varchar(255)',
		'CustomCSS' => 'Varchar(255)',
		'PaddingTop' => 'Varchar(10)',
		'PaddingRight' => 'Varchar(10)',
		'PaddingBottom' => 'Varchar(10)',
		'PaddingLeft' => 'Varchar(10)',
		'BackgroundColor' => 'Varchar(50)',
		'BorderTop' => 'Varchar(50)',
		'BorderRight' => 'Varchar(50)',
		'BorderBottom' => 'Varchar(50)',
		'BorderLeft' => 'Varchar(50)',
	);

	private static $has_one = array(
		'ContentBuilderBlock' => 'ContentBuilderBlock',
	);

	private static $summary_fields = array(
		'ContentBuilderBlockType' => 'Type',
//		'GridFieldPreview' => 'Contents'
	);
	
	private static $default_sort = "SortOrder ASC";
	
	private static $can_hold_blocks = false;
		
	function getCMSFields()
	{
		Requirements::javascript(CONTENTBUILDER_DIR."/javascript/ContentBuilderPage_cms.js");
		$fields = parent::getCMSFields();
		$fields->insertBefore( HeaderField::create('contentbuilderblocktype',$this->ContentBuilderBlockType(),2),'ExtraCssClass' );

		$fields->push( new HiddenField('SortOrder',null,$fields->dataFieldByName('SortOrder')->Value()) );
		$fields->push( new HiddenField('ContentBuilderBlockID',null,$fields->dataFieldByName('ContentBuilderBlockID')->Value()) );
		
		$fields->dataFieldByName('ReferenceTitle')->setTitle('Title')->setRightTitle('for reference only');
		
		$fields->addFieldToTab('Root.Style', $fields->dataFieldByName('ExtraCssClass') );
		$fields->addFieldToTab('Root.Style', $fields->dataFieldByName('CustomCSS') );
		$fields->addFieldToTab('Root.Style', SimpleColorPickerField::create('BackgroundColor','Background Color') );
		
		$fields->addFieldToTab('Root.Style', $paddingFields = FieldGroup::create('Padding')->setRightTitle('ex. 10px or 3%') );
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
			$Border_Group->push( TextField::create($area.'[width]','Width Pixels')->setRightTitle('ex. 5')->setValue(isset($defaults[0])?$defaults[0]:null) );
			$Border_Group->push( DropdownField::create($area.'[style]','Style',$borderStyles)->setValue(isset($defaults[1])?$defaults[1]:null) );
			$Border_Group->push( SimpleColorPickerField::create($area.'[color]','Color')->setValue(isset($defaults[2])?$defaults[2]:null) );
			if ($defaults[2])
			{
				$Border_Group->push( LiteralField::create($area.'colorpreview','<span style="display:block;padding-top:25px;background-color:'.$defaults[2].';"></span>') );
			}
			$borderFields->push( $Border_Group );
		}
		
		if (!Permission::check('ADMIN'))
		{
			$developer_fields = array(
				'ExtraCssClass',
				'CustomCSS'
			);
			foreach($developer_fields as $dev_field) { $fields->removeByName($dev_field); }
		}
		
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
		foreach(array('PaddingTop','PaddingRight','PaddingBottom','PaddingLeft') as $area)
		{
			if ( ($this->$area) && (!preg_match('/px/',$this->$area)) && (!preg_match('/\%/',$this->$area)) )
			{
				$result->error('Please specify "px" or "%" for '.FormField::name_to_label($area),$area);
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
				$this->{$area} = preg_replace('/[^0-9]/','',$_REQUEST[$area]['width']).'px '.$_REQUEST[$area]['style'].' '.str_replace(" ","",$_REQUEST[$area]['color']);
			}
			else
			{
				$this->{$area} = '';
			}
		}
	}
	
	public function Parent()
	{
		if ($this->ContentBuilderBlock()->Exists()) return $this->ContentBuilderBlock();		
	}
	
	public function Page()
	{
		if ( ($this instanceof ContentBuilderRow) && ($this->ContentBuilderPage()->Exists()) ) return $this->ContentBuilderPage();
		if ($this->Parent()) return $this->Parent()->Page();
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
	
	public function __get($var)
	{
		if ($var == 'Title')
		{
			return ($this->ReferenceTitle) ? $this->ReferenceTitle : $this->ContentBuilderBlockType();
		}
		return parent::__get($var);
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
		if ( ($style) && (!preg_match('/\;$/',$style)) ) { $style .= ';'; }
		
		// add user styling
		$style .= $this->CustomCSS;
		if ( ($style) && (!preg_match('/\;$/',$style)) ) { $style .= ';'; }
		
		$this->extend('updateCustomStyling',$style);
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
	
	protected function GridFieldEditButton($parentURL)
	{
		return ($parentURL && $this->canEdit()) 
//			? '<a href="'.Controller::join_links($parentURL,$this->ID,'edit').'" class="action action-detail edit-link">Edit</a>' 
			? '<a href="'.Controller::join_links($parentURL,$this->ID,'edit').'" class="content_builder_edit_btn ss-ui-button content-builder-button">Edit</a>' 
			: null;
	}
	
	protected function GridFieldDeleteButton($gridField)
	{
		return ($gridField && $this->canDelete()) 
			? GridField_FormAction::create($gridField, 'DeleteBlock'.$this->ID, 'Delete','deleteblock',array('RecordID'=>$this->ID))
				->addExtraClass('ss-ui-button content-builder-button content-builder-button-red content-builder-delete-with-confirm')
//				->addExtraClass('gridfield-button-delete')
//				->setAttribute('data-icon','cross-circle')
				->Field() 
			: null;
	}
		
	protected function GridFieldCloneButton($gridField)
	{
		return ($gridField && $this->canCreate()) 
			? GridField_FormAction::create($gridField, 'CloneBlock'.$this->ID, 'Clone','cloneblock',array('RecordID'=>$this->ID))
				->addExtraClass('ss-ui-button content-builder-button')
				->Field() 
			: null;
	}
	
	protected function GridFieldMoveButton($gridField)
	{
		if (!$moveBlockID = GridFieldContentBuilderActionsHandler::$MoveEnabled) 
		{		
			return ($gridField && $this->canEdit()) 
				? GridField_FormAction::create($gridField, 'MoveBlock'.$this->ID, 'Move','moveblock',array('RecordID'=>$this->ID))
					->addExtraClass('ss-ui-button content-builder-button')
					->Field() 
				: null;
		}
	}
	
	protected function GridFieldCancelMoveButton($gridField)
	{
		return ($gridField && $this->canEdit()) 
			? GridField_FormAction::create($gridField, 'CancelMoveBlock'.$this->ID, 'Cancel','cancelmoveblock',array('RecordID'=>$this->ID))
				->addExtraClass('ss-ui-button content-builder-button content-builder-button-red')
				->Field() 
			: null;
	}
	
	protected function GridFieldMoveHereButton($gridField)
	{
		return ($gridField && $this->canEdit()) 
			? GridField_FormAction::create($gridField, 'MoveBlockHere'.$this->ID, 'Move Here','moveblockhere',array('ParentID'=>$this->ID,'RecordID'=>GridFieldContentBuilderActionsHandler::$MoveEnabled))
				->addExtraClass('ss-ui-button content-builder-button content-builder-button-green')
				->Field() 
			: null;
	}
	
	protected function GridFieldActions()
	{
		// is move enabled
		if ($moveBlockID = GridFieldContentBuilderActionsHandler::$MoveEnabled)
		{
			// are we moving this block
			if ($moveBlockID == $this->ID)
			{
				return $this->GridFieldCancelMoveButton($this->_gridField);
			}
			// can this block hold other blocks
			elseif ($this->Config()->get('can_hold_blocks'))
			{
				return $this->GridFieldMoveHereButton($this->_gridField);
			}
			return null;
		}
		
		// not moving, provide normal buttons
		$html = $this->GridFieldEditButton($this->_parentURL);
		$html .= $this->GridFieldCloneButton($this->_gridField);
		$html .= $this->GridFieldMoveButton($this->_gridField);
		$html .= $this->GridFieldDeleteButton($this->_gridField);
		return $html;
	}
	
	protected function GridFieldContents()
	{
		return null;
	}
	
	protected function GridFieldTitle()
	{
		return '<h6 class="content_builder_block_title">'.$this->ContentBuilderBlockType().': '.$this->ReferenceTitle.'</h6><div class="content_builder_block_actions">'.$this->GridFieldActions().'</div>';
	}
	
	protected $_parentURL;
	protected $_gridField;
	public function GridFieldPreview($parentURL,$gridField)
	{
		$this->_parentURL = $parentURL;
		$this->_gridField = $gridField;
		return $this->GridFieldTitle().$this->GridFieldContents();
	}
}






