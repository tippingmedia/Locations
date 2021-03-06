<?php
/**
 * Locations plugin for Craft CMS 3.x
 *
 * Places on the globe.
 *
 * @link      https://tippingmedia.com
 * @copyright Copyright (c) 2018 Tipping Media LLC
 */

namespace tippingmedia\locations;

use tippingmedia\locations\services\Locations as LocationsService;
use tippingmedia\locations\variables\LocationsVariable;
use tippingmedia\locations\models\Settings;
use tippingmedia\locations\elements\Location as LocationElement;
use tippingmedia\locations\fields\Location as LocationField;
use tippingmedia\locations\helpers\CountriesHelper;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\services\Elements;
use craft\services\Fields;
use craft\services\UserPermissions;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;


use yii\base\Event;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://craftcms.com/docs/plugins/introduction
 *
 * @author    Tipping Media LLC
 * @package   Locations
 * @since     1.0.0
 *
 * @property  LocationsService $locations
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class Locations extends Plugin
{
    // Static Properties
    // =========================================================================

    const TRANSLATION_HANDLE            = 'locations';
    const PERMISSION_CREATE_LOCATIONS   = 'locations-createLocations';
    const PERMISSION_EDIT_LOCATIONS     = 'locations-editLocations';
    const PERMISSION_DELETE_LOCATIONS   = 'locations-deleteLocations';
    const PERMISSION_SETTINGS           = 'locations-settings';

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * Locations::$plugin
     *
     * @var Locations
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * Locations::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->setComponents([
            'locations' => \tippingmedia\locations\services\Locations::class,
        ]);

        // Register our site routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['siteActionTrigger1'] = 'locations/location';
            }
        );

        // Register our CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                // Locations
                $event->rules['locations'] = 'locations/location/location-index';
                $event->rules['locations/new'] = 'locations/location/edit-location';
                $event->rules['locations/<locationId:\d+><slug:(?:-[^\/]*)?>'] = 'locations/location/edit-location';
            }
        );

        // Register our elements
        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = LocationElement::class;
            }
        );

        // Register our fields
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = LocationField::class;
            }
        );

        // Register our variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('locations', LocationsVariable::class);
            }
        );

        if (Craft::$app->getEdition() >= Craft::Pro) {
            Event::on( 
                UserPermissions::class, 
                UserPermissions::EVENT_REGISTER_PERMISSIONS, 
                function (RegisterUserPermissionsEvent $event) {
                    $event->permissions[$this->name] = [
                        self::PERMISSION_SETTINGS  => ['label' => self::t('Locations Settings')],
                        self::PERMISSION_CREATE_LOCATIONS  => ['label' => self::t('Create Locations')],
                        self::PERMISSION_EDIT_LOCATIONS  => ['label' => self::t('Edit Locations')],
                        self::PERMISSION_DELETE_LOCATIONS  => ['label' => self::t('Delete Locations')]
                    ];
                });
            }


        // Do something after we're installed
        // Event::on(
        //     Plugins::class,
        //     Plugins::EVENT_AFTER_INSTALL_PLUGIN,
        //     function (PluginEvent $event) {
        //         if ($event->plugin === $this) {
        //             // We were just installed
        //         }
        //     }
        // );
/**
 * Logging in Craft involves using one of the following methods:
 *
 * Craft::trace(): record a message to trace how a piece of code runs. This is mainly for development use.
 * Craft::info(): record a message that conveys some useful information.
 * Craft::warning(): record a warning message that indicates something unexpected has happened.
 * Craft::error(): record a fatal error that should be investigated as soon as possible.
 *
 * Unless `devMode` is on, only Craft::warning() & Craft::error() will log to `craft/storage/logs/web.log`
 *
 * It's recommended that you pass in the magic constant `__METHOD__` as the second parameter, which sets
 * the category to the method (prefixed with the fully qualified class name) where the constant appears.
 *
 * To enable the Yii debug toolbar, go to your user account in the AdminCP and check the
 * [] Show the debug toolbar on the front end & [] Show the debug toolbar on the Control Panel
 *
 * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
 */
        Craft::info(
            Craft::t(
                'locations',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

      /**
     * @param string $message
     * @param array  $params
     * @param string $language
     *
     * @return string
     */
    public static function t(string $message, array $params = [], string $language = null): string
    {
        return Craft::t(self::TRANSLATION_HANDLE, $message, $params, $language);
    }

    public function defineTemplateComponent()
    {
        return LocationVariable::class;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string The rendered settings HTML
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'locations/settings',
            [
                'countries' => CountriesHelper::countryOptions(),
                'settings' => $this->getSettings()
            ]
        );
    }
}
