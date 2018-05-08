<?php
/**
 * Locations plugin for Craft CMS 3.x
 *
 * Places on the globe.
 *
 * @link      https://tippingmedia.com
 * @copyright Copyright (c) 2018 Tipping Media LLC
 */

namespace tippingmedia\locations\controllers;

use tippingmedia\locations\Locations;
use tippingmedia\locations\controllers\BaseLocationController;
use tippingmedia\locations\models\Location;
use tippingmedia\locations\assetbundles\locations\LocationsAsset;
use tippingmedia\locations\helpers\CountriesHelper;
use tippingmedia\locations\elements\Location as LocationElement;

use Craft;
use craft\web\Controller;
use craft\helpers\UrlHelper;
use yii\web\Response;


class LocationController extends BaseLocationController
{

    /**
	 * Group index
	 */
	public function actionLocationIndex(array $variables = []):Response {
		return $this->renderTemplate('locations/_index');
	}

	/**
	 * Edit a Location.
	 *
	 * @param array $variables
	 * @throws HttpException
	 * @throws Exception
	 */
	public function actionEditLocation(int $locationId = null, string $siteHandle = null, LocationElement $location = null): Response
	{

		$variables = [
			'locationId' => $locationId,
			'location' => $location
		];
		
		if ($siteHandle !== null) {
            $variables['site'] = Craft::$app->getSites()->getSiteByHandle($siteHandle);

            if (!$variables['site']) {
                throw new NotFoundHttpException('Invalid site handle: '.$siteHandle);
            }
        }

		$this->_prepEditEntryVariables($variables);

		/** @var Site $site */
        //$site = $variables['site'];
        /** @var LocationElement $location */
        $location = $variables['location'];

		$this->enforceEditLocationPermissions($location);
		$currentUser = Craft::$app->getUser()->getIdentity();

		

		if ($location->id === null) {
			$variables['title'] = Craft::t('locations','Create a new location');
		}
		else {
			$variables['title'] = $variables['location']->title = $location->title;
			$variables['brandNewLocation'] = true;
		}

		$variables['brandNewLocation'] = false;
		$variables['fullPageForm'] = true;
		$variables['defaultCountry'] = CountriesHelper::country();


		$variables['crumbs'] = [
			['label' => Craft::t('locations','Locations'), 'url' => UrlHelper::url('locations')]
		];
		// Can't just use the entry's getCpEditUrl() because that might include the locale ID when we don't want it
		$variables['baseCpEditUrl'] = 'locations/{id}-{slug}';

		$variables['canDeleteLocation'] = (
            get_class($location) === LocationsElement::class &&
            $location->id !== null &&
            (
                ($currentUser->can('locations-deleteLocations'))
            )
        );

		// Set the "Continue Editing" URL
		$variables['continueEditingUrl'] = $variables['baseCpEditUrl'];
		$variables['saveShortcutRedirect'] = $variables['continueEditingUrl'];

		$variables['countries'] = CountriesHelper::countryOptions();
		$variables['settings'] = Craft::$app->getPlugins()->getPlugin('locations')->getSettings();
        
		$this->getView()->registerAssetBundle(LocationsAsset::class);
		return $this->renderTemplate('locations/_edit', $variables);
	}

	/**
	 * Saves a location
	 */
	public function actionSaveLocation() {
		$this->requirePostRequest();

		$location = $this->_getLocationModel();
		$request = Craft::$app->getRequest();


		$this->enforceEditLocationPermissions($location);
		$currentUser = Craft::$app->getUser()->getIdentity();
		$continueEditingUrl = $request->getBodyParam('continueEditingUrl');

		$this->_populateLocationModel($location);

		if (!Locations::getInstance()->locations->saveLocation($location)) {
			if ($request->getAcceptsJson()) {
				return $this->asJson([
					'errors' => $location->getErrors()
				]);
			}

			Craft::$app->getSession()->setError(Craft::t('locations', 'Couldn’t save location.'));
			/* Send the event back to the template
			 * newGroup is applied if event group was changed by select so tabs and fields pull from correct group.
			 */
			Craft::$app->getUrlManager()->setRouteParams([
				'location' => $location
			]);

			return null;
		} 

		if ($request->getAcceptsJson()) {

			$return = [];
			$return['success'] = true;
			$return['id'] = $location->id;
			$return['title'] = $location->title;

			if (!$request->getIsConsoleRequest() && $request->isCpRequest()) {
				$return['cpEditUrl'] = $location->getCpEditUrl();
			}

			$return['address'] = $location->address;
			$return['addressTwo'] = $location->addressTwo;
			$return['city'] = $location->city;
			$return['state'] = $location->state;
			$return['zipCode'] = $location->zipCode;
			$return['country'] = $location->country;
			$return['longitude'] = $location->longitude;
			$return['latitude'] = $location->latitude;
			$return['website'] = $location->website;
			$return['dateCreated'] = DateTimeHelper::toIso8601($location->dateCreated);
            $return['dateUpdated'] = DateTimeHelper::toIso8601($location->dateUpdated);

			return $this->asJson($return);
		}

		Craft::$app->getSession()->setNotice(Craft::t('locations', 'Location saved.'));
		return $this->redirectToPostedUrl($location);
	}

	/**
	 * Fetches or creates an Locations_LocationModel.
	 *
	 * @throws Exception
	 * @return LocationElement
	 */
	private function _getLocationModel() {
		$locationId = Craft::$app->getRequest()->getBodyParam('locationId');
		$siteId = Craft::$app->getRequest()->getBodyParam('siteId');


		if ($locationId) {
			$location = LocationElement::find()
					->id($locationId)
					->siteId($siteId)
					->one();

			if (!$location) {
				throw new Exception(Craft::t('locations','No location exists with the ID “{id}”.', array('id' => $locationId)));
			}
		} else {
			$location = new LocationElement();

			if ($siteId) {
				$location->siteId = $siteId;
			}
		}

		return $location;
	}

	/**
	 * Deletes a location.
	 */
	public function actionDeleteLocation() {
		$this->requirePostRequest();
		$locationId = Craft::$app->getRequest()->getRequiredPost('locationId');

		if (Craft::$app->getRequest()->getAcceptsJson()) {
            if(Craft::$app->getElements()->deleteElementById($locationId)) {
				Craft::$app->getSession()->setNotice(Craft::t('locations','Location deleted'));
                return $this->asJson([
                    'success' => true
				]);
            } else {
				Craft::$app->getSession()->setError(Craft::t('locations','Couldn’t delete location'));
                return $this->asJson([
                    'errors' => $location->getErrors(),
				]);
            }
        } else {
			if (Craft::$app->getElements()->deleteElementById($locationId)) {
				Craft::$app->getSession()->setNotice(Craft::t('locations','Location deleted'));
				$this->redirectToPostedUrl();
			} else {
				Craft::$app->getSession()->setError(Craft::t('locations','Couldn’t delete location'));
			}
		}
	}

	/**
	 * Preps entry edit variables.
	 *
	 * @param array &$variables
	 *
	 * @throws HttpException|Exception
	 * @return null
	 */
	private function _prepEditEntryVariables(&$variables) 
	{
		
        if (empty($variables['location'])) {
            if (!empty($variables['locationId'])) {
                //\yii\helpers\VarDumper::dump($variables, 5, true); exit;
				$variables['location'] = LocationElement::find()
					->id($variables['locationId'])
					->one();

                if (!$variables['location']) {
                    throw new NotFoundHttpException('Location not found');
                }
            } else {
                $variables['location'] = new LocationElement();
            }
        }
	}

	/**
	 * Populates an LocationModel with post data.
	 *
	 * @param LocationModel $location
	 *
	 * @return null
	 */
	public function _populateLocationModel(LocationElement $location)
	{
		$location->address         		= Craft::$app->getRequest()->getBodyParam('address');
		$location->addressTwo         	= Craft::$app->getRequest()->getBodyParam('addressTwo');
		$location->city        			= Craft::$app->getRequest()->getBodyParam('city');
		$location->state         		= Craft::$app->getRequest()->getBodyParam('state');
		$location->zipCode         		= Craft::$app->getRequest()->getBodyParam('zipCode');
		$location->country				= Craft::$app->getRequest()->getBodyParam('country');
		$location->longitude         	= Craft::$app->getRequest()->getBodyParam('longitude');
		$location->latitude        		= Craft::$app->getRequest()->getBodyParam('latitude');
		$location->website         		= Craft::$app->getRequest()->getBodyParam('website');
		$location->title 				= Craft::$app->getRequest()->getBodyParam('title');
		$location->slug 				= Craft::$app->getRequest()->getBodyParam('slug');
		
	}
}
