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
use Bina\CustomerFile\Api\Data\FileInterface;

class FileManagement implements FileManagementInterface
{
    /**
     *
     * @var FileInterface
     *
     */
    protected $_attribute;

    /**
     *
     * @var AttributeMetadataInterface
     *
     */
    protected $_attributeMetadata;

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
     * @param FileInterface             $attribute
     * @param CustomerMetadataInterface $customerMetadataService
     * @param FileUploaderFactory       $fileUploaderFactory
     * @param FileProcessorFactory      $fileProcessorFactory
     * @param Filesystem                $filesystem
     *
     */
    public function __construct(
        FileInterface             $attribute,
        CustomerMetadataInterface $customerMetadataService,
        FileUploaderFactory       $fileUploaderFactory,
        FileProcessorFactory      $fileProcessorFactory,
        Filesystem                $filesystem
    ) {
        /**
         *
         * @note Init attribute metadata
         *
         */
        $this->_attributeMetadata = $customerMetadataService->getAttributeMetadata($attribute->getAttributeCode());

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
     * Upload file
     *
     * @param string $scope
     *
     * @return array
     *
     * @throws LocalizedException
     *
     */
    public function upload($scope)
    {
        /**
         *
         * @var FileUploader $fileUploader
         *
         */
        $fileUploader = $this->_fileUploaderFactory->create([
            'attributeMetadata' => $this->_attributeMetadata,
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
         * @note Return file uploaded data
         *
         */
        return $fileUploader->upload();
    }

    /**
     *
     * Get file URL
     *
     * @param string $file
     *
     * @return string
     *
     */
    public function getFileUrl($file)
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
        return $fileProcessor->getViewUrl($file, $this->_attributeMetadata->getFrontendInput());
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
}