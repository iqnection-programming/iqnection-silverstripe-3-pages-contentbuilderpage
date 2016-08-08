<?php

/**
 * Page type for building unique custom content without the need for special templates
 *
 * @author Michael Eckert
 *
 * @package IQ_Content_Builder
 *
 */
 
class ContentBuilderSection extends ArrayList
{	
	public function Blocks()
	{
		return ArrayList::create($this->toArray());
	}
	
	public function GridFieldPreview()
	{
		$html .= '<div class="cb-section col-'.$this->Count().'">';
		foreach($this->toArray() as $block)
		{
			if ($block instanceof ContentBuilderRow)
			{
				$html .= $block->GridFieldPreview();
			}
			else
			{
				$html .= '<div class="col">'.$block->ContentBuilderBlockType().'</div>';
			}
		}
		$html .= '</div>';
		return $html;
	}
	
	public function forTemplate()
	{
		return $this->renderWith(array($this->ClassName,'ContentBuilderSection'));
	}
	
}