<?php
/**
 *
 * @description View file
 *
 * @author Bina Commerce      <https://www.binacommerce.com>
 * @author C. M. de Picciotto <cmdepicciotto@binacommerce.com>
 *
 */
namespace Bina\CustomerFile\Controller\Index;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\Result\Raw;
use Magento\MediaStorage\Helper\File\Storage;
use Magento\Customer\Api\CustomerMetadataInterface;
use Bina\CustomerFile\Api\FileManagementInterface;

class Viewfile extends Action
{
    /**
     *
     * @var FileManagementInterface
     *
     */
    protected $_fileManagement;

    /**
     *
     * @var Filesystem
     *
     */
    protected $_filesystem;

    /**
     *
     * @var FileFactory
     *
     */
    protected $_fileFactory;

    /**
     *
     * @var Storage
     *
     */
    protected $_storage;

    /**
     *
     * @var RawFactory
     *
     */
    protected $_resultRawFactory;

    /**
     *
     * @var DecoderInterface
     *
     */
    protected $_urlDecoder;

    /**
     *
     * Constructor
     *
     * @param FileManagementInterface $fileManagement
     * @param Filesystem              $filesystem
     * @param FileFactory             $fileFactory
     * @param Storage                 $storage
     * @param RawFactory              $resultRawFactory
     * @param DecoderInterface        $urlDecoder
     * @param Context                 $context
     *
     */
    public function __construct(
        FileManagementInterface $fileManagement,
        Filesystem              $filesystem,
        FileFactory             $fileFactory,
        Storage                 $storage,
        RawFactory              $resultRawFactory,
        DecoderInterface        $urlDecoder,
        Context                 $context
    ) {
        /**
         *
         * @note Init file management
         *
         */
        $this->_fileManagement = $fileManagement;

        /**
         *
         * @note Init filesystem
         *
         */
        $this->_filesystem = $filesystem;

        /**
         *
         * @note Init file factory
         *
         */
        $this->_fileFactory = $fileFactory;

        /**
         *
         * @note Init storage
         *
         */
        $this->_storage = $storage;

        /**
         *
         * @note Init result raw factory
         *
         */
        $this->_resultRawFactory = $resultRawFactory;

        /**
         *
         * @note Init URL decoder
         *
         */
        $this->_urlDecoder = $urlDecoder;

        /**
         *
         * @note Parent constructor
         *
         */
        parent::__construct($context);
    }

    /**
     *
     * View action
     *
     * @return Raw|void
     *
     * @throws NotFoundException
     *
     */
    public function execute()
    {
        /**
         *
         * @note Get file params
         *
         */
        list($file, $plain) = $this->_getFileParams();

        /**
         *
         * @note Get directory
         *
         */
        $directory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);

        /**
         *
         * @note Get filename
         *
         */
        $filename = CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER . '/' . ltrim($file, '/');

        /**
         *
         * @note Get absolute path
         *
         */
        $path = $this->_fileManagement->getFileAbsolutePath($file);

        /**
         *
         * @note Validate page exists
         *
         */
        if (mb_strpos($path, '..') !== false || (!$directory->isFile($filename) && !$this->_storage->processStorageFile($path))) {
            throw new NotFoundException(__('Page not found.'));
        }

        /**
         *
         * @note Validate file information
         *
         */
        if ($plain) {
            /**
             *
             * @note Validate extension
             *
             */
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            switch (strtolower($extension)) {
                case 'gif':
                    $contentType = 'image/gif';
                    break;
                case 'jpg':
                    $contentType = 'image/jpeg';
                    break;
                case 'png':
                    $contentType = 'image/png';
                    break;
                default:
                    $contentType = 'application/octet-stream';
                    break;
            }

            /**
             *
             * @note Get size and mtime
             *
             */
            $stat          = $directory->stat($filename);
            $contentLength = $stat['size'];
            $contentModify = $stat['mtime'];

            /**
             *
             * @var Raw $resultRaw
             *
             */
            $resultRaw = $this->_resultRawFactory->create();

            /**
             *
             * @note Create response
             *
             */
            $resultRaw->setHttpResponseCode(200)->setHeader('Pragma', 'public', true)
                                                ->setHeader('Content-type', $contentType, true)
                                                ->setHeader('Content-Length', $contentLength)
                                                ->setHeader('Last-Modified', date('r', $contentModify));
            $resultRaw->setContents($directory->readFile($filename));

            /**
             *
             * @note Send response
             *
             */
            return $resultRaw;
        }
        else {
            /**
             *
             * @note Create file
             *
             */
            $name = pathinfo($path, PATHINFO_BASENAME);
            $this->_fileFactory->create($name, ['type' => 'filename', 'value' => $filename], DirectoryList::MEDIA);
        }
    }

    /**
     *
     * Get parameters from request
     *
     * @return array
     *
     * @throws NotFoundException
     *
     */
    private function _getFileParams()
    {
        /**
         *
         * @note Init data
         *
         */
        $file  = null;
        $plain = false;

        /**
         *
         * @note Download file
         *
         */
        if ($this->getRequest()->getParam('file')) {
            $file = $this->_urlDecoder->decode($this->getRequest()->getParam('file'));
        }

        /**
         *
         * @note Show image
         *
         */
        elseif ($this->getRequest()->getParam('image')) {
            $file  = $this->_urlDecoder->decode($this->getRequest()->getParam('image'));
            $plain = true;
        }

        /**
         *
         * @note Throw error
         *
         */
        else {
            throw new NotFoundException(__('Page not found.'));
        }

        /**
         *
         * @note Return data
         *
         */
        return [$file, $plain];
    }
}