<?php
class Codilar_Custom_Block_Homeproducts extends Mage_Catalog_Block_Product_List
{
	public function getAllCategories()
	{
		$_categories = Mage::getModel('catalog/category')
				->getCollection()
				->addAttributeToSelect('*');
		$_categories->addFieldToFilter('level','2');
		if(count($_categories))
			return $_categories;
		return null;
	}
	public function getProducts($_category)
	{
		$_products = Mage::getModel('catalog/product')
			->getCollection()
			->addAttributeToSelect('*')
			->addCategoryFilter($_category)
			->load();
		if(count($_products))
			return $_products;
		return null;
	}
}