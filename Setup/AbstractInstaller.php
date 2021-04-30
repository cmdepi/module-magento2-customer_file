<?php
/**
 *
 * @description Customer file attribute installer
 *
 * @author Bina Commerce      <https://www.binacommerce.com>
 * @author C. M. de Picciotto <cmdepicciotto@binacommerce.com>
 *
 */
namespace Bina\CustomerFile\Setup;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Model\Customer;
use Bina\CustomerFile\Api\Data\FileInterface;

abstract class AbstractInstaller implements DataPatchInterface
{
    /**
     *
     * @var FileInterface
     *
     */
    protected $_attribute;

    /**
     *
     * @var CustomerSetupFactory
     *
     */
    private $_customerSetupFactory;

    /**
     *
     * @var SetFactory
     *
     */
    private $_attributeSetFactory;

    /**
     *
     * @var ModuleDataSetupInterface
     *
     */
    private $_moduleDataSetup;

    /**
     *
     * Constructor
     *
     * @param FileInterface            $attribute
     * @param CustomerSetupFactory     $customerSetupFactory
     * @param SetFactory               $attributeSetFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     *
     */
    public function __construct(
        FileInterface            $attribute,
        CustomerSetupFactory     $customerSetupFactory,
        SetFactory               $attributeSetFactory,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        /**
         *
         * @note Init attribute
         *
         */
        $this->_attribute = $attribute;

        /**
         *
         * @note Init customer setup factory
         *
         */
        $this->_customerSetupFactory = $customerSetupFactory;

        /**
         *
         * @note Init attribute set factory
         *
         */
        $this->_attributeSetFactory = $attributeSetFactory;

        /**
         *
         * @note Init module data setup
         *
         */
        $this->_moduleDataSetup = $moduleDataSetup;
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function apply()
    {
        /**
         *
         * @note Init customer setup
         *
         */
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->_customerSetupFactory->create(['setup' => $this->_moduleDataSetup]);

        /**
         *
         * @note Get attribute code
         *
         */
        $attributeCode = $this->_attribute->getAttributeCode();

        /**
         *
         * @note Get attribute label
         *
         */
        $attributeLabel = $this->_attribute->getAttributeLabel();

        /**
         *
         * @note Get file extensions
         *
         */
        $fileExtensions = implode(',', $this->_attribute->getAllowedExtensions());

        /**
         *
         * @note Add attribute
         *
         */
        $customerSetup->addAttribute(
            Customer::ENTITY,
            $attributeCode,
            [
                'type'           => 'varchar',
                'label'          => $attributeLabel,
                'input'          => 'file',
                'validate_rules' => '{"file_extensions":"' . $fileExtensions . '"}',
                'required'       => false,
                'system'         => false,
                'sort_order'     => 100
            ]
        );

        /**
         *
         * @note Get attribute set ID
         *
         */
        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /**
         *
         * @note Get attribute group ID
         *
         */
        /** @var Set $attributeSet */
        $attributeSet     = $this->_attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        /**
         *
         * @note Add attribute set ID and attribute group ID to attribute
         * @note Associate attribute to forms
         *
         */
        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, $attributeCode)
                                                   ->addData([
                                                        'attribute_set_id'   => $attributeSetId,
                                                        'attribute_group_id' => $attributeGroupId,
                                                        'used_in_forms'      => ['adminhtml_customer', 'customer_account_edit']
                                                   ]);

        /**
         *
         * @note Save attribute
         *
         */
        $attribute->save();
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function getAliases()
    {
        return [];
    }
}