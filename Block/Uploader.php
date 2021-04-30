<?php
/**
 *
 * @description Uploader block
 *
 * @author Bina Commerce      <https://www.binacommerce.com>
 * @author C. M. de Picciotto <cmdepicciotto@binacommerce.com>
 *
 */
namespace Bina\CustomerFile\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Customer\Api\Data\CustomerInterface;
use Bina\CustomerFile\Api\FileManagementInterface;
use Bina\CustomerFile\Api\Data\FileInterface;

class Uploader extends Template
{
    /**
     *
     * @var FileManagementInterface
     *
     */
    protected $_fileManagement;

    /**
     *
     * @var FileInterface
     *
     */
    protected $_attribute;

    /**
     *
     * @var Json
     *
     */
    protected $_json;

    /**
     *
     * @var CustomerInterface|null
     *
     */
    protected $_customer = null;

    /**
     *
     * @var string
     *
     */
    protected $_template = 'Bina_CustomerFile::form/element/uploader.phtml';

    /**
     *
     * Constructor
     *
     * @param FileManagementInterface $fileManagement
     * @param FileInterface           $attribute
     * @param Json                    $json
     * @param Context                 $context
     * @param array                   $data
     *
     */
    public function __construct(
        FileManagementInterface $fileManagement,
        FileInterface           $attribute,
        Json                    $json,
        Context                 $context,
        array                   $data = []
    ) {
        /**
         *
         * @note Init file management
         *
         */
        $this->_fileManagement = $fileManagement;

        /**
         *
         * @note Init attribute
         *
         */
        $this->_attribute = $attribute;

        /**
         *
         * @note Init JSON helper
         *
         */
        $this->_json = $json;

        /**
         *
         * @note Call parent constructor
         *
         */
        parent::__construct($context, $data);
    }

    /**
     *
     * Get customer
     *
     * @return CustomerInterface|null
     *
     */
    public function getCustomer()
    {
        return $this->_customer;
    }

    /**
     *
     * Set customer
     *
     * @param CustomerInterface $customer
     *
     * @return $this
     *
     */
    public function setCustomer(CustomerInterface $customer)
    {
        /**
         *
         * @note Set customer
         *
         */
        $this->_customer = $customer;

        /**
         *
         * @note Return
         *
         */
        return $this;
    }

    /**
     *
     * Get upload URL
     *
     * @return string
     *
     */
    public function getUploadUrl()
    {
        return $this->_urlBuilder->getUrl('customerfile/file/upload');
    }

    /**
     *
     * Get attribute data in JSON format
     *
     * @return string
     *
     */
    public function getAttributeDataJson()
    {
        return $this->_json->serialize($this->_getAttributeData());
    }

    /**
     *
     * Get attribute code
     *
     * @return string
     *
     */
    public function getAttributeCode()
    {
        return $this->_attribute->getAttributeCode();
    }

    /**
     *
     * Get attribute label
     *
     * @return string
     *
     */
    public function getAttributeLabel()
    {
        return __($this->_attribute->getAttributeLabel());
    }

    /**
     *
     * Get attribute allowed extensions in JSON format
     *
     * @return string
     *
     */
    public function getAttributeAllowedExtensionsJson()
    {
        return $this->_json->serialize($this->_attribute->getAllowedExtensions());
    }

    /**
     *
     * Get attribute data
     *
     * @return array
     *
     */
    private function _getAttributeData()
    {
        /**
         *
         * @note Init data
         *
         */
        $data = [];

        /**
         *
         * @note Get customer attribute
         *
         */
        $attribute = $this->getCustomer()->getCustomAttribute($this->_attribute->getAttributeCode());

        /**
         *
         * @note Validate customer attribute
         *
         */
        if (!is_null($attribute)) {
            /**
             *
             * @note Validate customer attribute value
             *
             */
            if (!is_null($value = $attribute->getValue())) {
                /**
                 *
                 * @note Add data
                 *
                 */
                $data[] = [
                    'file' => $value,
                    'name' => $this->getAttributeLabel(),
                    'url'  => $this->_fileManagement->getFileUrl($value)
                ];
            }
        }

        /**
         *
         * @note Return data
         *
         */
        return $data;
    }
}