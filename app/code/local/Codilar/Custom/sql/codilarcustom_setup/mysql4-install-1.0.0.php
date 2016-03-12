<?php
$installer = $this;
$installer->startSetup();

$entityTypeId     = $installer->getEntityTypeId('catalog_category');
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
//$attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);
$attributeGroupId = $installer->getAttributeGroupId($entityTypeId, $attributeSetId,'General Information');
//$installer->removeAttribute('catalog_category', 'icon_code');
$installer->addAttribute('catalog_category', 'icon_code',  array(
'type'     => 'varchar',
'label'    => 'Icons Code',
'input'    => 'text',
'global'   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
'visible'           => true,
'required'          => false,
'user_defined'      => true,
'default'           => 0
));


$installer->addAttributeToGroup(
$entityTypeId,
$attributeSetId,
$attributeGroupId,
'icon_code',
'11'					//last Magento's attribute position in General tab is 10
);

$attributeId = $installer->getAttributeId($entityTypeId, 'icon_code');

$installer->run("
INSERT INTO `{$installer->getTable('catalog_category_entity_int')}`
(`entity_type_id`, `attribute_id`, `entity_id`, `value`)
SELECT '{$entityTypeId}', '{$attributeId}', `entity_id`, 'fa-th-large'
FROM `{$installer->getTable('catalog_category_entity')}`;
");


//this will set data of your custom attribute for root category
Mage::getModel('catalog/category')
->load(1)
->setImportedCatId(0)
->setInitialSetupFlag(true)
->save();

//this will set data of your custom attribute for default category
Mage::getModel('catalog/category')
->load(2)
->setImportedCatId(0)
->setInitialSetupFlag(true)
->save();

$installer->endSetup();