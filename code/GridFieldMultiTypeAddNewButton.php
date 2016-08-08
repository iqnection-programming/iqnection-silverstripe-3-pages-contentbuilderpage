<?php

/**
 *
 * @author Michael Eckert
 *
 * @package IQ_Content_Builder
 *
 * A button which allows objects to be created with a specified classname(s)
 */
class GridFieldMultiTypeAddNewButton extends Object implements GridField_HTMLProvider, GridField_ActionProvider {

	/**
	 * Name of fragment to insert into
	 *
	 * @var string
	 */
	protected $targetFragment;

	/**
	 * Button title
	 *
	 * @var string
	 */
	protected $buttonName;

	/**
	 * Additonal CSS classes for the button
	 *
	 * @var string
	 */
	protected $buttonClass = null;

	/**
	 * Class name
	 *
	 * @var array
	 */
	protected $modelClass = null;

	/**
	 * @param array $classes Class or list of classes to create.
	 * If you enter more than one class, each click of the "add" button will create one of each
	 * @param string $targetFragment The fragment to render the button into
	 */
	public function __construct($modelClass, $targetFragment = 'buttons-before-left') 
	{
		parent::__construct();
		$this->setModelClass($modelClass);
		$this->setFragment($targetFragment);
	}

	/**
	 * Change the button name
	 *
	 * @param string $name
	 * @return $this
	 */
	public function setButtonName($name) 
	{
		$this->buttonName = $name;
		return $this;
	}

	/**
	 * Get the button name
	 *
	 * @return string
	 */
	public function getButtonName() 
	{
		return $this->buttonName;
	}

	/**
	 * Gets the fragment name this button is rendered into.
	 *
	 * @return string
	 */
	public function getFragment() 
	{
		return $this->targetFragment;
	}

	/**
	 * Sets the fragment name this button is rendered into.
	 *
	 * @param string $fragment
	 * @return GridFieldAddNewInlineButton $this
	 */
	public function setFragment($fragment) 
	{
		$this->targetFragment = $fragment;
		return $this;
	}

	/**
	 * Get extra button class
	 *
	 * @return string
	 */
	public function getButtonClass() 
	{
		return $this->buttonClass;
	}

	/**
	 * Sets extra CSS classes for this button
	 *
	 * @param string $buttonClass
	 * @return $this
	 */
	public function setButtonClass($buttonClass) 
	{
		$this->buttonClass = $buttonClass;
		return $this;
	}

	/**
	 * Gets the class which can be created, with checks for permissions.
	 * Will fallback to the default model class for the given DataGrid
	 *
	 * @param DataGrid $grid
	 * @return string
	 */
	public function getModelClassCreate($grid) 
	{
		// Get explicit or fallback class list
		$class = $this->getModelClass();
		if(empty($class) && $grid) {
			$class = $grid->getModelClass();
		}
		
		return (singleton($class)->canCreate()) ? $class : '';
	}

	/**
	 * Get the object class to create
	 *
	 * @return array
	 */
	public function getModelClass() 
	{
		return $this->modelClass;
	}
	
	/**
	 * Specify the class to create
	 *
	 * @param string $class
	 */
	public function setModelClass($class) 
	{
		$this->modelClass = $class;
	}

	public function getHTMLFragments($grid) 
	{
		// Check create permission
		$singleton = singleton($this->getModelClass());
		if(!$singleton->canCreate()) {
			return array();
		}

		// Get button name
		$buttonName = $this->getButtonName();
		if(!$buttonName) {
			// provide a default button name, can be changed by calling {@link setButtonName()} on this component
			$objectName = $singleton->i18n_singular_name();
			$buttonName = _t('GridField.Add', 'Add {name}', array('name' => $objectName));
		}

		$addAction = new GridField_FormAction(
			$grid,
			$this->getAction(),
			$buttonName,
			$this->getAction(),
			array()
		);
		$addAction->setAttribute('data-icon', 'add');

		if($this->getButtonClass()) {
			$addAction->addExtraClass($this->getButtonClass());
		}

		return array(
			$this->targetFragment => $addAction->forTemplate()
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getActions($gridField) 
	{
		return array(
			$this->getAction()
		);
	}

	/**
	 * Get the action suburl for this component
	 *
	 * @return string
	 */
	protected function getAction() 
	{
		return 'add-class-' . strtolower($this->getModelClass());
	}

	public function handleAction(GridField $gridField, $actionName, $arguments, $data) 
	{
		switch(strtolower($actionName)) {
			case $this->getAction():
				return $this->handleAdd($gridField);
			default:
				return null;
		}
	}

	/**
	 * Handles adding a new instance of a selected class.
	 *
	 * @param GridField $grid
	 * @return null
	 */
	public function handleAdd($grid) 
	{
		$class = $this->getModelClassCreate($grid);
		if(empty($class)) {
			throw new SS_HTTPResponse_Exception(400);
		}

		// Add item to gridfield
		$list = $grid->getList();
		$item = $class::create();
		$item->write();
		$list->add($item);

		// Should trigger a simple reload
		return null;
	}
}

