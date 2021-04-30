<?php
/**
 *
 * @description File attribute interface
 *
 * @author Bina Commerce      <https://www.binacommerce.com>
 * @author C. M. de Picciotto <cmdepicciotto@binacommerce.com>
 *
 */
namespace Bina\CustomerFile\Api\Data;

interface FileInterface
{
    /**
     *
     * Get attribute code
     *
     * @return string
     *
     */
    public function getAttributeCode();

    /**
     *
     * Get attribute label
     *
     * @return string
     *
     */
    public function getAttributeLabel();

    /**
     *
     * Get allowed extensions
     *
     * @return array
     *
     */
    public function getAllowedExtensions();
}