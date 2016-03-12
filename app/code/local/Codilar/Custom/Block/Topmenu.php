<?php
class Codilar_Custom_Block_Topmenu extends Mage_Page_Block_Html_Topmenu
{
	protected function _getHtml(Varien_Data_Tree_Node $menuTree, $childrenWrapClass)
	{
		$html = '';

		$children = $menuTree->getChildren();
		$parentLevel = $menuTree->getLevel();
		$childLevel = is_null($parentLevel) ? 0 : $parentLevel + 1;

		$counter = 1;
		$childrenCount = $children->count();

		$parentPositionClass = $menuTree->getPositionClass();
		$itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';

		foreach ($children as $child) {

			$child->setLevel($childLevel);
			$child->setIsFirst($counter == 1);
			$child->setIsLast($counter == $childrenCount);
			$child->setPositionClass($itemPositionClassPrefix . $counter);

			$outermostClassCode = '';
			$outermostClass = $menuTree->getOutermostClass();

			$iconCode = $this->_getIconCode($child->getName());


			if ($childLevel == 0 && $outermostClass) {
				$outermostClassCode = ' class="' . $outermostClass . ' fai '.$iconCode.'" ';
				$child->setClass($outermostClass);
			}

			$html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
			$html .= '<a href="' . $child->getUrl() . '" ' . $outermostClassCode . '><span>'
				. $this->escapeHtml($child->getName()) . '</span></a>';

			if ($child->hasChildren()) {
				if (!empty($childrenWrapClass)) {
					$html .= '<div class="' . $childrenWrapClass . '">';
				}
				$html .= '<ul class="level' . $childLevel . '">';
				$html .= $this->_getHtml($child, $childrenWrapClass);
				$html .= '</ul>';

				if (!empty($childrenWrapClass)) {
					$html .= '</div>';
				}
			}
			$html .= '</li>';

			$counter++;
		}

		return $html;
	}
	protected function _getIconCode($name){
		return Mage::getModel('catalog/category')->loadByAttribute('name', $name)->getData('icon_code');
	}
}