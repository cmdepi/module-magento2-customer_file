<?php
/**
 *
 * @description File management model
 *
 * @author Bina Commerce      <https://www.binacommerce.com>
 * @author C. M. de Picciotto <cmdepicciotto@binacommerce.com>
 *
 */
namespace Bina\CustomerFile\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Model\FileUploaderFactory;
use Magento\Customer\Model\FileUploader;
use Magento\Customer\Model\FileProcessorFactory;
use Magento\Customer\Model\FileProcessor;
use Bina\CustomerFile\Api\FileManagementInterface;

class FileManagement implements FileManagementInterface
{
    /**
     *
     * @var AttributeMetadataInterface
     *
     */
    protected $_customerMetadataService;

    /**
     *
     * @var FileUploaderFactory
     *
     */
    protected $_fileUploaderFactory;

    /**
     *
     * @var FileProcessorFactory
     *
     */
    protected $_fileProcessorFactory;

    /**
     *
     * @var ReadInterface
     *
     */
    protected $_mediaDirectory;

    /**
     *
     * Constructor
     *
     * @param CustomerMetadataInterface $customerMetadataService
     * @param FileUploaderFactory       $fileUploaderFactory
     * @param FileProcessorFactory      $fileProcessorFactory
     * @param Filesystem                $filesystem
     *
     */
    public function __construct(
        CustomerMetadataInterface $customerMetadataService,
        FileUploaderFactory       $fileUploaderFactory,
        FileProcessorFactory      $fileProcessorFactory,
        Filesystem                $filesystem
    ) {
        /**
         *
         * @note Init customer metadata service
         *
         */
        $this->_customerMetadataService = $customerMetadataService;

        /**
         *
         * @note Init file uploader factory
         *
         */
        $this->_fileUploaderFactory = $fileUploaderFactory;

        /**
         *
         * @note Init file processor factory
         *
         */
        $this->_fileProcessorFactory = $fileProcessorFactory;

        /**
         *
         * @note Init media directory
         *
         */
        $this->_mediaDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
    }

    /**
     *
     * Get allowed extensions
     *
     * @param string $attributeCode
     *
     * @return array
     *
     */
    public function getAllowedExtensions($attributeCode)
    {
        /**
         *
         * @note Init allowed extensions
         *
         */
        $allowedExtensions = [];

        /**
         *
         * @note Get validation rules
         *
         */
        $validationRules = $this->_getAttributeMetadata($attributeCode)->getValidationRules();

        /**
         *
         * @note Loop validation rules
         *
         */
        foreach ($validationRules as $validationRule) {
            /**
             *
             * @note Check file extensions
             *
             */
            if ($validationRule->getName() == 'file_extensions') {
                /**
                 *
                 * @note Get allowed extensions
                 *
                 */
                $allowedExtensions = explode(',', $validationRule->getValue());

                /**
                 *
                 * @ntoe Format allowed extensions
                 *
                 */
                array_walk($allowedExtensions, function (&$value) {
                    $value = strtolower(trim($value));
                });

                /**
                 *
                 * @note Break
                 *
                 */
                break;
            }
        }

        /**
         *
         * @note Return allowed extensions
         *
         */
        return $allowedExtensions;
    }

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
    public function upload($attributeCode, $scope)
    {
        /**
         *
         * @var FileUploader $fileUploader
         *
         */
        $fileUploader = $this->_fileUploaderFactory->create([
            'attributeMetadata' => $this->_getAttributeMetadata($attributeCode),
            'entityTypeCode'    => CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            'scope'             => $scope
        ]);

        /**
         *
         * @note Validate errors
         *
         */
        $errors = $fileUploader->validate();
        if (true !== $errors) {
            /**
             *
             * @note Throw exception error
             *
             */
           throw new LocalizedException(__(implode('-', $errors)));
        }

        /**
         *
         * @note Get file uploaded data
         *
         */
        $result = $fileUploader->upload();

        /**
         *
         * @note Add file URL
         * @note Fix file URL. For some reason, it is used the file name instead of the file path uploaded. If the customer uploads a file with a name of a file already used, the first file that uses this name is shown
         *
         * @see FileUploader::upload()
         *
         */
        $result['url'] = $this->getFileUrl($attributeCode, FileProcessor::TMP_DIR . '/' . ltrim($result['file'], '/'));

        /**
         *
         * @note Return file uploaded data
         *
         */
        return $result;
    }

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
    public function getFileUrl($attributeCode, $file)
    {
        /**
         *
         * @note Init file processor
         *
         */
        /** @var FileProcessor $fileProcessor */
        $fileProcessor = $this->_fileProcessorFactory->create(['entityTypeCode' => CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER]);

        /**
         *
         * @note Return file URL
         *
         */
        return $fileProcessor->getViewUrl($file, $this->_getAttributeMetadata($attributeCode)->getFrontendInput());
    }

    /**
     *
     * Get file absolute path
     *
     * @param string $file
     *
     * @return string
     *
     */
    public function getFileAbsolutePath($file)
    {
        /**
         *
         * @note Get filename
         *
         */
        $filename = CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER . DIRECTORY_SEPARATOR . ltrim($file, DIRECTORY_SEPARATOR);

        /**
         *
         * @note Return file absolute path
         *
         */
        return $this->_mediaDirectory->getAbsolutePath($filename);
    }

    /**
     *
     * Get attribute metadata
     *
     * @param string $attributeCode
     *
     * @return AttributeMetadataInterface
     *
     */
    private function _getAttributeMetadata($attributeCode)
    {
        return $this->_customerMetadataService->getAttributeMetadata($attributeCode);
    }
}