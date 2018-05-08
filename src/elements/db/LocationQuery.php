<?php
namespace tippingmedia\locations\elements\db;

use tippingmedia\locations\models\Location;
use tippingmedia\locations\services\Locations;

use Craft;
use craft\db\Query;
use craft\elements\db\ElementQuery;
use craft\db\QueryAbortedException;
use craft\elements\Entry;
use craft\helpers\ArrayHelper;
use craft\helpers\Db;
use DateTime;
use yii\db\Connection;


class LocationQuery extends ElementQuery
{
     /**
     * @var bool Whether to only return locations that the user has permission to edit.
     */
    public $editable = false;
    public $address;
    public $addressTwo;
    public $city;
    public $state;
    public $zipCode;
    public $country;
    public $longitude;
    public $latitude;
    public $website;


    public function __construct($elementType, array $config = [])
    {
        parent::__construct($elementType, $config);
    }

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        parent::__set($name, $value);
    }

    /**
     * Sets the [[editable]] property.
     *
     * @param bool $value The property value (defaults to true)
     *
     * @return static self reference
     */
    public function editable(bool $value = true)
    {
        $this->editable = $value;

        return $this;
    }


     /**
     * Sets the [[address]] property.
     *
     * @param string|string[]|null $value The property value
     *
     * @return static self reference
     */
    public function address($value)
    {
        $this->address = $value;

        return $this;
    }


    /**
     * Sets the [[addressTwo]] property.
     *
     * @param string|string[]|null $value The property value
     *
     * @return static self reference
     */
    public function addressTwo($value)
    {
        $this->addressTwo = $value;

        return $this;
    }

    /**
     * Sets the [[city]] property.
     *
     * @param $string|null $value The property value
     *
     * @return static self reference
     */
    public function city($value)
    {
        $this->city = $value;

        return $this;
    }


    /**
     * Sets the [[state]] property.
     *
     * @param $string|null $value The property value
     *
     * @return static self reference
     */
    public function state($value)
    {
        $this->state = $value;

        return $this;
    }

    /**
     * Sets the [[zipCode]] property.
     *
     * @param $string|null $value The property value
     *
     * @return static self reference
     */
    public function zipCode($value)
    {
        $this->zipCode = $value;

        return $this;
    }
    
    /**
     * Sets the [[country]] property.
     *
     * @param $string|null $value The property value
     *
     * @return static self reference
     */
    public function country($value)
    {
        $this->country = $value;

        return $this;
    }

    /**
     * Sets the [[longitude]] property.
     *
     * @param $string|null $value The property value
     *
     * @return static self reference
     */
    public function longitude($value)
    {
        $this->longitude = $value;

        return $this;
    }

    /**
     * Sets the [[latitude]] property.
     *
     * @param $string|null $value The property value
     *
     * @return static self reference
     */
    public function latitude($value)
    {
        $this->latitude = $value;

        return $this;
    }

    /**
     * Sets the [[website]] property.
     *
     * @param $string|null $value The property value
     *
     * @return static self reference
     */
    public function website($value)
    {
        $this->website = $value;

        return $this;
    }

    



    /**
     * @inheritdoc
     */
    protected function beforePrepare(): bool
    {


        $this->joinElementTable('locations_entries');

        $this->query->select([
            'locations_entries.address',
            'locations_entries.addressTwo',
            'locations_entries.city',
            'locations_entries.state',
            'locations_entries.zipCode',
            'locations_entries.country',
            'locations_entries.longitude',
            'locations_entries.latitude',
            'locations_entries.website'
        ]);


        if ($this->address) {
            $this->subQuery->andWhere(Db::parseParam('locations_entries.address', $this->address));
        }

        if ($this->addressTwo) {
            $this->subQuery->andWhere(Db::parseDateParam('locations_entries.addressTwo', $this->addressTwo));
        }

        if ($this->city) {
            $this->subQuery->andWhere(Db::parseDateParam('locations_entries.city', $this->city));
        }

        if ($this->state) {
            $this->subQuery->andWhere(Db::parseParam('locations_entries.state', $this->state));
        }

        if ($this->zipCode) {
            $this->subQuery->andWhere(Db::parseParam('locations_entries.zipCode', $this->zipCode));
        }

        if ($this->country) {
            $this->subQuery->andWhere(Db::parseParam('locations_entries.country', $this->country));
        }

        if ($this->longitude) {
            $this->subQuery->andWhere(Db::parseParam('locations_entries.longitude', $this->longitude));
        }

        if ($this->latitude) {
            $this->subQuery->andWhere(Db::parseParam('locations_entries.latitude', $this->latitude));
        }

        if ($this->website) {
            $this->subQuery->andWhere(Db::parseParam('locations_entries.website', $this->website));
        }

        //$this->_applyRefParam();


        return parent::beforePrepare();
    }
}