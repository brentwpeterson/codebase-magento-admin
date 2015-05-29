<?php
$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();
$setup->addAttributeGroup('catalog_product', 'Default', 'Codebase Information', 1000);

$attributeArray = array('owner_name' => 'Owner Name', 'owner_email' => 'Owner Email', 'owner_phone' => 'Owner Phone');


foreach($attributeArray as $code => $title)
{
	$setup->addAttribute('catalog_product', $code, array(
	    'group' => 'Codebase Information',
	    'input' => 'text',
	    'type' => 'text',
	    'label' => $title,
	    'backend' => '',
	    'visible' => 1,
	    'required' => 0,
	    'user_defined' => 1,
	    'searchable' => 0,
	    'filterable' => 0,
	    'comparable' => 0,
	    'visible_on_front' => 0,
	    'visible_in_advanced_search' => 0,
	    'is_html_allowed_on_front' => 0,
	    'is_configurable' => 0,
	    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
	));
}
