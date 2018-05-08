<?php
/**
 * Locations plugin for Craft CMS 3.x
 *
 * Places on the globe.
 *
 * @link      https://tippingmedia.com
 * @copyright Copyright (c) 2018 Tipping Media LLC
 */

namespace tippingmedia\locations\elements;

use tippingmedia\locations\elements\db\LocationQuery;
use tippingmedia\locations\services\Locations;
use tippingmedia\locations\models\Location as LocationModel;
use tippingmedia\locations\records\Location as LocationRecord;
use tippingmedia\locations\assetbundles\locations\LocationsAsset;
use tippingmedia\locations\helpers\CountriesHelper;
use tippingmedia\locations\elements\actions\Edit;
use tippingmedia\locations\elements\actions\Delete;
use tippingmedia\locations\elements\actions\View;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\UrlHelper;

/**
 * Location Element
 *
 * Element is the base class for classes representing elements in terms of objects.
 *
 * @property FieldLayout|null      $fieldLayout           The field layout used by this element
 * @property array                 $htmlAttributes        Any attributes that should be included in the element’s DOM representation in the Control Panel
 * @property int[]                 $supportedSiteIds      The site IDs this element is available in
 * @property string|null           $uriFormat             The URI format used to generate this element’s URL
 * @property string|null           $url                   The element’s full URL
 * @property \Twig_Markup|null     $link                  An anchor pre-filled with this element’s URL and title
 * @property string|null           $ref                   The reference string to this element
 * @property string                $indexHtml             The element index HTML
 * @property bool                  $isEditable            Whether the current user can edit the element
 * @property string|null           $cpEditUrl             The element’s CP edit URL
 * @property string|null           $thumbUrl              The URL to the element’s thumbnail, if there is one
 * @property string|null           $iconUrl               The URL to the element’s icon image, if there is one
 * @property string|null           $status                The element’s status
 * @property Element               $next                  The next element relative to this one, from a given set of criteria
 * @property Element               $prev                  The previous element relative to this one, from a given set of criteria
 * @property Element               $parent                The element’s parent
 * @property mixed                 $route                 The route that should be used when the element’s URI is requested
 * @property int|null              $structureId           The ID of the structure that the element is associated with, if any
 * @property ElementQueryInterface $ancestors             The element’s ancestors
 * @property ElementQueryInterface $descendants           The element’s descendants
 * @property ElementQueryInterface $children              The element’s children
 * @property ElementQueryInterface $siblings              All of the element’s siblings
 * @property Element               $prevSibling           The element’s previous sibling
 * @property Element               $nextSibling           The element’s next sibling
 * @property bool                  $hasDescendants        Whether the element has descendants
 * @property int                   $totalDescendants      The total number of descendants that the element has
 * @property string                $title                 The element’s title
 * @property string|null           $serializedFieldValues Array of the element’s serialized custom field values, indexed by their handles
 * @property array                 $fieldParamNamespace   The namespace used by custom field params on the request
 * @property string                $contentTable          The name of the table this element’s content is stored in
 * @property string                $fieldColumnPrefix     The field column prefix this element’s content uses
 * @property string                $fieldContext          The field context this element’s content uses
 *
 * http://pixelandtonic.com/blog/craft-element-types
 *
 * @author    Tipping Media LLC
 * @package   Locations
 * @since     1.0.0
 */
class Location extends Element
{
    
    // Public Properties
    // =========================================================================

    /**
     * address, addressTwo, state, zipCode, country, longitude, latitude, town, website
     *
     * @var string
     */
    public $address;
    public $addressTwo;
    public $city;
    public $state;
    public $zipCode;
    public $country;
    public $longitude;
    public $latitude;
    public $website;

    // Static Methods
    // =========================================================================

    /**
     * Returns the display name of this class.
     *
     * @return string The display name of this class.
     */
    public static function getName(): string
    {
        return Craft::t('locations', 'Location');
    }

    /**
     * @inheritdoc
     */
    public static function refHandle()
    {
        return 'location';
    }

    /**
     * Returns whether elements of this type will be storing any data in the `content`
     * table (tiles or custom fields).
     *
     * @return bool Whether elements of this type will be storing any data in the `content` table.
     */
    public static function hasContent(): bool
    {
        return true;
    }

    /**
     * Returns whether elements of this type have traditional titles.
     *
     * @return bool Whether elements of this type have traditional titles.
     */
    public static function hasTitles(): bool
    {
        return true;
    }

    /**
     * Returns whether elements of this type have statuses.
     *
     * If this returns `true`, the element index template will show a Status menu
     * by default, and your elements will get status indicator icons next to them.
     *
     * Use [[statuses()]] to customize which statuses the elements might have.
     *
     * @return bool Whether elements of this type have statuses.
     * @see statuses()
     */
    public static function isLocalized(): bool
    {
        return false;
    }

    /**
     *
     * @return ElementQueryInterface The newly created [[ElementQueryInterface]] instance.
     */
    public static function find(): ElementQueryInterface
    {
        return new LocationQuery(static::class);
    }

    /**
     * @inheritdoc
     */
    protected static function defineSources(string $context = null): array
    {
        $sources = [
            [
                'key' => '*',
                'label' => Craft::t('locations', 'All locations'),
                'criteria' => []
            ]
        ];
        return $sources;
    }



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
            [['address','addressTwo','city','state','zipCode','longitude','latitude','website'], 'string'],
        ];
    }

    /**
     * Returns whether the current user can edit the element.
     *
     * @return bool
     */
    public function getIsEditable(): bool
    {
        return true;
    }






    // Indexes, etc.
    // -------------------------------------------------------------------------

    /**
     * Returns the HTML for the element’s editor HUD.
     *
     * @return string The HTML for the editor HUD
     */
    public function getEditorHtml(): string
    {
        $namespacedId = Craft::$app->getView()->getNamespace();

        Craft::$app->getView()->registerAssetBundle(LocationsAsset::class);
		#-- Start/End Dates
		$html = Craft::$app->getView()->renderTemplate('locations/_editor', [
            'location' 			=> $this,
            'countries'         => CountriesHelper::countryOptions(),
            'defaultCountry'    => CountriesHelper::country(),
            'settings'          => Craft::$app->getPlugins()->getPlugin('locations')->getSettings(),
			'namespacedId' 		=> $namespacedId
		]);

        $html .= parent::getEditorHtml();

        return $html;
    }

    // Events
    // -------------------------------------------------------------------------

    /**
     * Performs actions before an element is saved.
     *
     * @param bool $isNew Whether the element is brand new
     *
     * @return bool Whether the element should be saved
     */
    public function beforeSave(bool $isNew): bool
    {
        return true;
    }

    /**
     * Performs actions after an element is saved.
     *
     * @param bool $isNew Whether the element is brand new
     *
     * @return void
     */
    public function afterSave(bool $isNew)
    {
        if (!$isNew) {
			$record = LocationRecord::findOne($this->id);

            if (!$record) {
                throw new Exception('Invalid Location ID: '.$this->id);
            }
        } else {
            $record = new LocationRecord();
            $record->id = $this->id;
		}
		
		$record->siteId = $this->siteId;
        $record->address = $this->address;
        $record->addressTwo = $this->addressTwo;
        $record->city = $this->city;
		$record->state = $this->state;
        $record->zipCode = $this->zipCode;
        $record->country = $this->country;
        $record->longitude = $this->longitude;
		$record->latitude = $this->latitude;
		$record->website = $this->website;

        $record->save(false);
        
        parent::afterSave($isNew);
    }

    /**
     * Performs actions before an element is deleted.
     *
     * @return bool Whether the element should be deleted
     */
    public function beforeDelete(): bool
    {
        return true;
    }

    /**
     * Performs actions after an element is deleted.
     *
     * @return void
     */
    public function afterDelete()
    {
    }

    /**
     * @inheritdoc
     */
    protected static function defineSortOptions(): array
    {
        return [
            'title' => Craft::t('app', 'Title'),
            'state' => Craft::t('locations', 'State'),
            'city' => Craft::t('locations', 'City'),
            'town' => Craft::t('locations', 'Town'),
            'zipCode' => Craft::t('locations', 'ZipCode'),
            'country' => Craft::t('locations', 'Country'),
            'elements.dateCreated' => Craft::t('app', 'Date Created'),
            'elements.dateUpdated' => Craft::t('app', 'Date Updated'),
        ];
    }

    /**
     * @inheritdoc
     */
	protected static function defineTableAttributes(): array
	{
		$attributes = [
            'uri'     => ['label' => Craft::t('locations','URI')],
            'title'     => ['label' => Craft::t('locations','Title')],
            'address' 	=> ['label' => Craft::t('locations','Address')],
            'addressTwo' => ['label' => Craft::t('locations','AddressTwo')],
            'city'   	=> ['label' => Craft::t('locations','City/Town')],
			'state'   	=> ['label' => Craft::t('locations','State/Province/Region')],
			'zipCode'	=> ['label' => Craft::t('locations','Zip Code/Postal Code')],
            'country'   => ['label' => Craft::t('locations','Country')],
		];

		return $attributes;
    }

    protected function tableAttributeHtml(string $attribute): string
	{
		// switch ($attribute)
		// {
		// 	case 'country':
		// 	{
		// 		if ($this->country == "US")
		// 		{
        //             return '<div style="font-size:1.7rem">🇺🇸</div>';
		// 		}
		// 		else
		// 		{
		// 			return $this->country;
		// 		}
        //     }
		// }
		return parent::tableAttributeHtml($attribute);
	}
    

    /**
     * Returns the reference string to this element.
     *
     * @return string|null
     */
    public function getRef()
    {
        return 'locations/'.$this->id."-".$this->slug;
    }
    
    /**
     * @inheritdoc
     */
    public function getCpEditUrl()
    {
        // The slug *might* not be set if this is a Draft and they've deleted it for whatever reason
        $url = UrlHelper::cpUrl('locations/'.$this->id.($this->slug ? '-'.$this->slug : ''));

        return $url;
	}

}
