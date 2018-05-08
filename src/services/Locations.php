<?php
/**
 * Locations plugin for Craft CMS 3.x
 *
 * Places on the globe.
 *
 * @link      https://tippingmedia.com
 * @copyright Copyright (c) 2018 Tipping Media LLC
 */

namespace tippingmedia\locations\services;

use Craft;
use craft\base\Component;

use tippingmedia\locations\elements\Location as LocationElement;
use tippingmedia\locations\models\Location;
use tippingmedia\locations\events\LocationEvent;

/**
 * Locations Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Tipping Media LLC
 * @package   Locations
 * @since     1.0.0
 */
class Locations extends Component
{
    // Constants
    // =========================================================================

    /**
     * @event LocationEvent The location that is triggered before a location is saved.
     */
    const EVENT_BEFORE_SAVE_LOCATION = 'beforeSaveLocation';

    /**
     * @event LocatioonEvent The event that is triggered after a location is saved.
     */
    const EVENT_AFTER_SAVE_LOCATION = 'afterSaveLocation';


	// Properties
    // =========================================================================

	private $_allLocationIds;
	private $_locationsById;
	private $_fetchedAllLocations = false;
	/**
	 * Returns a location by its ID.
	 *
	 * @param int $locationId
	 * @return Events_LocationModel|null
	 */
	public function getLocationById(int $locationId)
	{
		if (!$locationId) {
			return null;
		}
		$query = LocationElement::find()
    		->id($locationId)
			->status(null);
		return $query->one();
	}


	public function getAllLocations($indexBy = null)
	{

		if ($this->_fetchedAllLocations) {
            return array_values($this->_locationssById);
        }

		 $results = $this->_createLocationQuery()
            ->all();

        $this->_locationsById = [];

        foreach ($results as $result) {
            $location = new Location($result);
            $this->_locationsById[$location->id] = $location;
        }

        $this->_fetchedAllLocations = true;

        return array_values($this->_locationsById);

	}



	/**
	 * Saves a location.
	 *
	 * @param Location $location
	 * @throws Exception
	 * @return array
	 */

	public function saveLocation(LocationElement $location, bool $runValidation = true)
	{

		$isNewLocation = !$location->id;

		if ($runValidation && !$location->validate()) {
            Craft::info('Location not saved due to validation error.', __METHOD__);

            return false;
		}
		
		$this->trigger(self::EVENT_BEFORE_SAVE_LOCATION, new LocationEvent([
			'location' => $location,
			'isNew' => $isNewLocation
		]));

		$db = Craft::$app->getDb();
		$transaction = $db->beginTransaction();

		try {

			if (Craft::$app->getElements()->saveElement($location)) {

				$this->_locationsById[$location->id] = $location;
	
				// Update search index with location
				Craft::$app->getSearch()->indexElementAttributes($location);
				
			}
			
			$transaction->commit();

		} catch (\Exception $e) {
			$transaction->rollback();

			throw $e;
		}

		// Fire an 'afterSaveLocation' location
		if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE_LOCATION)) {
			$this->trigger(self::EVENT_AFTER_SAVE_LOCATION, new LocationEvent([
				'location' => $location,
				'isNew' => $isNewLocation
			]));
		}

		return true;

	}


	// Private Methods
    // =========================================================================

    /**
     * Returns a Query object prepped for retrieving locations.
     *
     * @return Query
     */
    private function _createLocationQuery(): Query
    {
        return (new Query())
            ->select([
                'loc.id',
                'loc.address',
                'loc.addressTwo',
                'loc.city',
                'loc.state',
                'loc.zipCode',
				'loc.longitude',
				'loc.latitude',
                'loc.website'
            ])
            ->from(['{{%locations_entries}} loc']);
    }
}
