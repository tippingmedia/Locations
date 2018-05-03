<?php
/**
 * Locations plugin for Craft CMS 3.x
 *
 * Places on the globe.
 *
 * @link      https://tippingmedia.com
 * @copyright Copyright (c) 2018 Tipping Media LLC
 */

namespace tippingmedia\locations\models;

use tippingmedia\locations\Locations;

use Craft;
use craft\base\Model;

/**
 * Locations Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Tipping Media LLC
 * @package   Locations
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
       * @var string|null googleMapsApiKey
    */
    public $googleMapsApiKey;
    
    /**
       * @var string|null country
    */
    public $country;

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['googleMapsApiKey', 'string'],
            ['country', 'default', 'value' => 'US'],
        ];
    }
}
