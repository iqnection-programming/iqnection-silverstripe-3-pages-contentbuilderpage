<?php

/**
 * Page type for building unique custom content without the need for special templates
 *
 * @author Michael Eckert
 *
 * @package IQ_Content_Builder
 *
 */
class GridFieldCloneContentBuilderBlockActionButton implements GridField_ColumnProvider, GridField_ActionProvider 
{

    public function augmentColumns($gridField, &$columns) 
	{
        if(!in_array('Actions', $columns)) {
            $columns[] = 'Actions';
        }
    }

    public function getColumnAttributes($gridField, $record, $columnName) 
	{
        return array('class' => 'col-buttons');
    }

    public function getColumnMetadata($gridField, $columnName) 
	{
        if($columnName == 'Actions') 
		{
            return array('title' => 'Clone');
        }
    }

    public function getColumnsHandled($gridField) 
	{
        return array('Actions');
    }

    public function getColumnContent($gridField, $record, $columnName) 
	{
        if(!$record->canEdit()||!$record->canCreate()) return;

        $field = GridField_FormAction::create(
            $gridField,
            'CloneBlock'.$record->ID,
            'Clone',
            "docloneblock",
            array('RecordID' => $record->ID)
        );

        return $field->Field();
    }

    public function getActions($gridField) 
	{
        return array('doCloneBlock');
    }

    public function handleAction(GridField $gridField, $actionName, $arguments, $data) 
	{
        if(strtolower($actionName) == 'docloneblock') 
		{
            // perform your action here
			if (!$item = $gridField->getList()->byID($arguments['RecordID'])) 
			{
				Controller::curr()->getResponse()->setStatusCode(
					200,
					'Item Not Found'
				);
			}
			
			if (!$item->CloneBlock())
			{
				Controller::curr()->getResponse()->setStatusCode(
					200,
					'Clone Failed'
				);
			}
            // output a success message to the user
            Controller::curr()->getResponse()->setStatusCode(
                200,
                'Success'
            );
        }
    }
}