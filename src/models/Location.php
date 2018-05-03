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
 * Location Model
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
class Location extends Model
{
       #-- Properties
	protected $elementType = 'Location';


    public function getMapUrl($type = 'google')
    {
        $mapurl = "";
        switch ($type) {
            case 'google':
                $mapurl = "http://maps.google.com/?q=" . $this->fullAddress();
                break;
            case 'apple':
                $mapurl = "http://maps.apple.com/?address=" . $this->fullAddress();
                break;
        }
        return $mapurl;
    }

    public function fullAddress()
    {
        return $this->address ." ". $this->city ." ". $this->state ." ". $this->zipCode;
    }

	// Properties
    // =========================================================================


	/**
     * @var string|null address 
     */
    public $address;
	/**
     * @var string|null addressTwo
     */
    public $addressTwo;
	/**
     * @var string|null city
     */
    public $city;
	/**
     * @var string|null state
     */
    public $state;
	/**
     * @var string|null zipCode
     */
    public $zipCode;
	/**
     * @var string|null country
     */
    public $country;
	/**
	   * @var string|null latitude
	*/
	public $latitude;
	/**
	   * @var string|null longitude
	*/
	public $longitude;
	/**
     * @var string|null website
     */
    public $website;

    public $region;
    public $town;
    public $province;
	
	

    /**
	 * Returns whether the current user can edit the element.
	 *
	 * @return bool
	 */
	public function isEditable()
	{
		return true;
	}

    /**
	 * @inheritDoc BaseElementModel::getCpEditUrl()
	 *
	 * @return string|false
	 */
	public function getCpEditUrl()
	{

		// The slug *might* not be set if this is a Draft and they've deleted it for whatever reason
		$url = UrlHelper::getCpUrl('venti/location/'.$this->id.($this->slug ? '-'.$this->slug : ''));

		return $url;

	}

    /**
	 * Returns the reference string to this element.
	 *
	 * @return string|null
	 */
	public function getRef()
	{
		return 'location/'.$this->id."-".$this->slug;
	}
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
            [['address','addressTwo','city','state','town','zipCode','longitude','latitude','website','region','provice','postalCode'], 'string'],
        ];
    }
}
