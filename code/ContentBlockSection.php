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
	
	public function GridFieldPreview($parentURL=null,$gridField=null)
	{
		$html .= '<div class="cb-section col-'.$this->Count().'">';
		foreach($this->toArray() as $block)
		{
			$html .= $block->GridFieldPreview($parentURL,$gridField);
		}
		$html .= '</div>';
		return $html;
	}
	
	public function forTemplate()
	{
		return $this->renderWith(array($this->ClassName,'ContentBuilderSection'));
	}
	
}