<?php
/**
 * Locations Plugin
 *
 *
 * @link      http://tippingmedia.com
 * @copyright Copyright (c) 2018 tippingmedia
 */

namespace tippingmedia\locations\controllers;

use tippingmedia\locations\Locations;
use tippingmedia\locations\elements\Location as LocationElement;


use Craft;
use craft\web\Controller;

/**
 * BaseEvent Controller
 *
 *
 * @author    tippingmedia
 * @package   Venti
 * @since     2.0.0
 */

abstract class BaseLocationController extends Controller
{
	// Protected Methods
	// =========================================================================

	/**
	 * Enforces all Edit Location permissions.
	 *
	 * @param Locations_LocationModel $location
	 *
	 * @return null
	 */
	protected function enforceEditLocationPermissions(LocationElement $loc)
	{
		$userSessionService = Craft::$app->getUser();

		if (Craft::$app->getIsMultiSite()) {
            // Make sure they have access to this site
            $this->requirePermission('editSite:'.$loc->siteId);
        }

		$this->requirePermission('locations-editLocations');

		// Is it a new loc?
		if (!$loc->id)
		{
			// Make sure they have permission to create new entries in this group
			$this->requirePermission('locations-createLocations');
		}
	}
}
