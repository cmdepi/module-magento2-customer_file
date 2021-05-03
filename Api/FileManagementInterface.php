<?php
/**
 *
 * @description File management interface
 *
 * @author Bina Commerce      <https://www.binacommerce.com>
 * @author C. M. de Picciotto <cmdepicciotto@binacommerce.com>
 *
 */
namespace Bina\CustomerFile\Api;

use Magento\Framework\Exception\LocalizedException;

interface FileManagementInterface
{
    /**
     *
     * Get allowed extensions
     *
     * @param string $attributeCode
     *
     * @return array
     *
     */
    public function getAllowedExtensions($attributeCode);

    /**
     *
     * Upload file
     *
     * @param string $attributeCode
     * @param string $scope
     *
     * @return array
     *
     * @throws LocalizedException
     *
     */
    public function upload($attributeCode, $scope);

    /**
     *
     * Get file URL
     *
     * @param string $attributeCode
     * @param string $file
     *
     * @return string
     *
     */
    public function getFileUrl($attributeCode, $file);

    /**
     *
     * Get file absolute path
     *
     * @param string $file
     *
     * @return string
     *
     */
    public function getFileAbsolutePath($file);
}
