<?php

class GridFieldContentBuilderActionsHandler implements GridField_ColumnProvider, GridField_ActionProvider
{
	public static $MoveEnabled = false;
	
	public function augmentColumns($gridField, &$columns)
	{
		if (!in_array('Preview',$columns))
		{
			$columns[] = 'Preview';
		}
	}
	
	public function getColumnMetadata($gridField, $columnName) 
	{
		if($columnName == 'Preview') {
			return array('title' => '');
		}
	}
 
	public function getColumnAttributes($gridField, $record, $columnName) 
	{
		return array('class' => 'col-GridFieldPreview');
	}
	
	public function getColumnsHandled($gridField)
	{
		return array('Preview');
	}
	
	public function getActions($gridField)
	{
		return array('deleteblock','cloneblock','moveblock','moveblockhere','cancelmoveblock');
	}
	
	public function getColumnContent($gridField,$record,$columnName)
	{
		return $record->GridFieldPreview(Controller::join_links($gridField->Link('item')),$gridField);
	}
	
	public function handleAction(GridField $gridField, $actionName, $args, $data) 
	{ 
		switch(strtolower($actionName))
		{
			case 'deleteblock':
			{
				if (!$block = ContentBuilderBlock::get()->byId($args['RecordID'])) { return; }
				if (!$block->canDelete()) {	throw new ValidationException('No Delete Permissions',0); }
				$block->delete();
				break;
			}
			case 'moveblock':
			{
				self::$MoveEnabled = $args['RecordID'];
				break;
			}
			case 'cancelmoveblock':
			{
				self::$MoveEnabled = false;
				break;
			}
			case 'moveblockhere':
			{
				if (!$block = ContentBuilderBlock::get()->byId($args['RecordID'])) { return; }
				if (!$parent = ContentBuilderBlock::get()->byId($args['ParentID'])) { return; }
				$block->SortOrder = $parent->ContentBuilderBlocks()->max('SortOrder') + 1;
				$block->ContentBuilderBlockID = $args['ParentID'];
				$block->write();
				break;
			}
			case 'cloneblock':
			{
				if (!$block = ContentBuilderBlock::get()->byId($args['RecordID']))
				{
					Controller::curr()->getResponse()->setStatusCode(
						200,
						'Item Not Found'
					);
				}
				
				if (!$block->CloneBlock())
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
				break;
			}
		}
	}
}






