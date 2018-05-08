<?php
namespace tippingmedia\locations\helpers;

use tippingmedia\locations\Locations;

use Craft;
use craft\helpers\FileHelper;

/**
 * Location helper class.
 *
 * @author    Tipping Media LLC. <support@tippingmedia.com>
 * @copyright Copyright (c) 2016, Tipping Media LLC.
 * @see       http://tippingmedia.com
 * @package   location.helpers
 * @since     2.0
 */

class CountriesHelper
{
    public static function countries()
    {
        $string = file_get_contents(__DIR__ . "/countries.json");

        $json_a = json_decode($string, true);
        asort($json_a);
        return $json_a;
    }


    public static function countryOptions()
    {
        $countries = static::countries();
        $options = array();
        foreach ($countries as $key => $value)
        {
            array_push($options,array("label" => $value, "value"=> $key));
        }
        return $options;
    }

    public static function country()
    {
        $settings = Craft::$app->getPlugins()->getPlugin('locations')->getSettings();
        return $settings['country'];
    }
}
