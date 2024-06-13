<?php
/**
 * @package stockablecustomfield
 * @copyright Copyright (C) 2014-2024 breakdesigns.net . All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Uri\Uri;

/**
 * Class tha provides the necessary functionality for multi-lingual shops
 * @since 1.8.0
 */
class LanguageStockable
{
    /**
     * The path to the flag images
     *
     * @var string
     * @since 1.8.0
     */
    const FLAG_IMAGE_PATH = 'media/mod_languages/images/';

    /**
     * @var  LanguageStockable
     * @since 1.8.0
     */
    protected static $instance;

    /**
     * Get object instance
     *
     * @return LanguageStockable
     * @since 1.8.0
     */
    public static function getInstance()
    {
        if (self::$instance === null)  {
            self::$instance = new LanguageStockable();
        }

        return self::$instance;
    }


    /**
     * Get the path of the language flag icon/image
     *
     * @param $langCode
     * @return string
     * @since 1.8.0
     */
    public function getFlagImagePath($langCode)
    {
        $iconName = substr($langCode, 0, 2);
        return Uri::root() . self::FLAG_IMAGE_PATH . $iconName . '.gif';
    }
}