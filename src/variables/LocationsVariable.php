<?php
/**
 * Locations plugin for Craft CMS 3.x
 *
 * Places on the globe.
 *
 * @link      https://tippingmedia.com
 * @copyright Copyright (c) 2018 Tipping Media LLC
 */

namespace tippingmedia\locations\variables;

use tippingmedia\locations\Locations;
use tippingmedia\locations\elements\Location;

use Craft;

/**
 * Locations Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.locations }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    Tipping Media LLC
 * @package   Locations
 * @since     1.0.0
 */
class LocationsVariable
{
    // Public Methods
    // =========================================================================

    public function locations()
	{
		$query = Location::find();
        if ($criteria) {
            Craft::configure($query, $criteria);
        }
        return $query;
    }
}
