<?php

/**
 * Page type for building unique custom content without the need for special templates
 *
 * @author Michael Eckert
 *
 * @package IQ_Content_Builder
 *
 */
class ContentBuilderPage extends Page
{
	private static $has_many = array(
		'ContentBuilderRows' => 'ContentBuilderRow'
	);
	
	public function getCMSFields()
	{
		Requirements::css(THEMES_DIR.'/'.Config::inst()->get('SSViewer','theme').'/css/typography.css');

		$fields = parent::getCMSFields();
		$fields->removeByName('Content');
		$fields->addFieldToTab('Root.Main', $gridField = GridField::create(
			'ContentBuilderRows',
			'Content Rows',
			$this->ContentBuilderRows(),
			$gf_config = GridFieldConfig_RecordEditor::create()
				->addComponent(new GridFieldSortableRows('SortOrder'))
				->removeComponentsByType('GridFieldAddNewButton')
				->removeComponentsByType('GridFieldEditButton')
				->removeComponentsByType('GridFieldDeleteAction')
				->removeComponentsByType('GridFieldSortableHeader')
				->addComponent(new GridFieldContentBuilderActionsHandler())
				->addComponent(new GridFieldMultiTypeAddNewButton('ContentBuilderRow'))
		));
		$gf_config->getComponentsByType('GridFieldPaginator')->First()->setItemsPerPage(999);
//		$gf_columns = $gridField->getConfig()->getComponentByType('GridFieldDataColumns')
//			->setFieldFormatting(array(
//				'GridFieldPreview' => function($value,$item){
//					return htmlspecialchars_decode($value);
//				}
//			));
		
		$this->extend('updateCMSFields',$fields);
		return $fields;
	}
}

class ContentBuilderPage_Controller extends Page_Controller
{
	public function PageCSS()
	{
		return array_merge(
			parent::PageCSS(),
			array(
				CONTENTBUILDER_DIR.'/css/ContentBuilderPage.css'
			)
		);
	}
	
	public function PageJS()
	{
		return array_merge(
			parent::PageJS(),
			array(
				CONTENTBUILDER_DIR.'/javascript/ContentBuilderPage.js'
			)
		);
	}
}






