<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoBattery\model\service\rdf;

use oat\generis\model\kernel\persistence\smoothsql\search\ComplexSearchService;
use oat\search\base\exception\SearchGateWayExeption;
use oat\taoBattery\model\model\BatteryModel;
use oat\taoBattery\model\model\BatteryModelException;
use oat\taoBattery\model\model\rdf\RdfBattery;
use oat\taoBattery\model\BatteryException;
use oat\taoBattery\model\service\AbstractBatteryService;

/**
 * Class BatteryService
 * @package oat\taoBattery\model
 */
class RdfBatteryService extends AbstractBatteryService
{
    const BATTERY_URI = 'http://www.taotesting.com/ontologies/battery.rdf#Battery';

    const BATTERY_DELIVERIES = 'http://www.taotesting.com/ontologies/battery.rdf#deliveries';

    /**
     * Construct the battery model from the given battery param
     * If the $battery is an ontology resource then return a RdfBattery
     *
     * @param $battery
     * @return RdfBattery
     * @throws BatteryException
     */
    public function buildBattery($battery)
    {
        $battery = new RdfBattery($battery);
        if ($battery->exists()) {
            throw new BatteryModelException('The battery resource does not exist.');
        }
        if (!$this->isValid($battery)) {
            throw new BatteryModelException('The battery resource is not under Battery Root Class.');
        }
        return $battery;
    }

    /**
     * Check if battery exists
     *
     * @param RdfBattery $battery
     * @return boolean
     */
    public function exists($battery)
    {
        return $battery->exists();
    }

    /**
     * Create a battery in ontology
     *
     * @param $label
     * @param $deliveries
     * @return \core_kernel_classes_Resource
     */
    public function createBattery($label, $deliveries)
    {
        return RdfBatteryClassService::singleton()->getRootClass()->createInstanceWithProperties(array(
            RDFS_LABEL => $label,
            self::BATTERY_DELIVERIES => $deliveries
        ));
    }

    /**
     * Fetch a battery by label
     *
     * @param $label
     * @return null|RdfBattery
     * @throws BatteryException
     */
    public function fetchByLabel($label)
    {
        /** @var ComplexSearchService $search */
        $search = $this->getServiceLocator()->get(ComplexSearchService::SERVICE_ID);
        $queryBuilder = $search->query();

        $query = $search
            ->searchType($queryBuilder, self::BATTERY_URI, true)
            ->add(RDFS_LABEL)->equals($label);

        $queryBuilder->setCriteria($query);
        try {
            $result = $search->getGateway()->search($queryBuilder);
        } catch (SearchGateWayExeption $e) {
            throw new BatteryException('A search runtime exception has occurred.', 0, $e);
        }

        if ($result->count() == 1) {
            return new RdfBattery($result->current());
        } elseif ($result->count() == 0) {
            return null;
        } else {
            throw new BatteryException('Battery with same label are detected.');
        }
    }

    /**
     * Delete a delivery from all batteries
     *
     * @param \core_kernel_classes_Resource $delivery
     * @throws BatteryException
     */
    public function deleteDeliveryFromBatteries(\core_kernel_classes_Resource $delivery)
    {
        /** @var ComplexSearchService $search */
        $search = $this->getServiceLocator()->get(ComplexSearchService::SERVICE_ID);
        $queryBuilder = $search->query();

        $myQuery = $search->searchType($queryBuilder, self::BATTERY_URI)
            ->add(self::BATTERY_DELIVERIES)->equals($delivery->getUri());

        $queryBuilder->setCriteria($myQuery);
        try {
            $result = $search->getGateway()->search($queryBuilder);
        } catch (SearchGateWayExeption $e) {
            throw new BatteryException('A search runtime exception has occurred.', 0, $e);
        }

        if ($result->count() > 0) {
            /** @var \core_kernel_classes_Resource $battery */
            foreach ($result as $battery) {
                $battery->removePropertyValue($this->getProperty(self::BATTERY_DELIVERIES), $delivery->getUri());
            }
        }
    }

    /**
     * Add a delivery to a battery.
     * If delivery exists for another battery, delete it from others
     *
     * @param $batteryLabel
     * @param array $deliveries
     * @return \core_kernel_classes_Resource|null|BatteryModel|RdfBattery
     * @throws BatteryException
     */
    public function addDeliveriesToBattery($batteryLabel, array $deliveries)
    {
        $battery = $this->fetchByLabel($batteryLabel);
        if (is_null($battery)) {
            $battery = $this->createBattery($batteryLabel, $deliveries);
        } else {
            foreach ($deliveries as $delivery) {
                $this->deleteDeliveryFromBatteries($delivery);
            }
            $battery->setPropertiesValues(array(
                self::BATTERY_DELIVERIES => $deliveries
            ));

        }
        return $battery;
    }

    /**
     * Get all deliveries associated to a battery
     *
     * @param RdfBattery|BatteryModel $battery
     * @return array
     */
    public function getBatteryDeliveries(BatteryModel $battery)
    {
        return $battery->getPropertyValues($this->getProperty(self::BATTERY_DELIVERIES));
    }

    /**
     * Check if battery is valid by checking if it belongs to battery root class
     *
     * @param RdfBattery|BatteryModel $battery
     * @return bool
     */
    public function isValid(BatteryModel $battery)
    {
        $typeUris = array_map(function($type) {
            return $type->getUri();
        }, $battery->getTypes());

        if (in_array(self::BATTERY_URI, $typeUris)) {
            return true;
        }
        return false;
    }

    /**
     * Check if battery contains a delivery with the given $uri
     *
     * @param \core_kernel_classes_Resource $battery
     * @param $uri
     * @return bool
     * @throws BatteryException
     */
    public function isBatteryDelivery($battery, $uri)
    {
        /** @var ComplexSearchService $search */
        $search = $this->getServiceLocator()->get(ComplexSearchService::SERVICE_ID);
        $queryBuilder = $search->query();

        $myQuery = $search->searchType($queryBuilder, self::BATTERY_URI)
            ->add(self::BATTERY_DELIVERIES)->equals($uri)
        ;

        $queryBuilder->setCriteria($myQuery);
        try {
            $result = $search->getGateway()->search($queryBuilder);
        } catch (SearchGateWayExeption $e) {
            throw new BatteryException('A search runtime exception has occurred.', 0, $e);
        }

        if ($result->count() == 1) {
            $foundBattery = $result[0];
            if ($foundBattery->subject == $battery->getUri()) {
                return true;
            }
        }

        return false;
    }
}
