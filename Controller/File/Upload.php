<?php
/**
 *
 * @description Upload file
 *
 * @author Bina Commerce      <https://www.binacommerce.com>
 * @author C. M. de Picciotto <cmdepicciotto@binacommerce.com>
 *
 */
namespace Bina\CustomerFile\Controller\File;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\App\Request\Http as HttpRequest;
use Bina\CustomerFile\Api\FileManagementInterface;

class Upload extends Action implements HttpPostActionInterface
{
    /**
     *
     * @var FileManagementInterface
     *
     */
    protected $_fileManagement;

    /**
     *
     * Constructor
     *
     * @param FileManagementInterface $fileManagement
     * @param Context                 $context
     *
     */
    public function __construct(
        FileManagementInterface $fileManagement,
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
         * @note Parent constructor
         *
         */
        parent::__construct($context);
    }

    /**
     *
     * Execute
     *
     * @return Json
     *
     * @throws Exception
     *
     */
    public function execute()
    {
        try {
            /** @var HttpRequest $request */
            $request = $this->getRequest();

            /**
             *
             * @note Validate it is an AJAX request
             *
             */
            if (!$request->isAjax()) {
                throw new Exception(__('Invalid request.'));
            }

            /**
             *
             * @note Upload
             *
             */
            $result = $this->_fileManagement->upload('customer');
        }
        catch (Exception $e) {
            /**
             *
             * @note Init result with error data
             *
             */
            $result = [
                'error'     => $e->getMessage(),
                'errorcode' => $e->getCode(),
            ];
        }

        /**
         *
         * @note Send response
         *
         */
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);
        return $resultJson;
    }
}
