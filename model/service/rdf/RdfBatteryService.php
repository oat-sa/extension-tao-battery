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
        if ($battery instanceof RdfBattery) {
            return $battery;
        }

        $battery = new RdfBattery($battery);
        if (!$battery->exists()) {
            throw new BatteryModelException('The battery resource does not exist.');
        }
        if (!$battery->isInstanceOf($this->getClass(self::BATTERY_URI))) {
            throw new BatteryModelException('The battery resource is not under Battery Root Class.');
        }
        return $battery;
    }

    /**
     * Create a battery in ontology
     *
     * @param $label
     * @param $deliveries
     * @return \core_kernel_classes_Resource
     */
    public function createBattery($label, array $deliveries)
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
        $batteries = $this->findDeliveryBattery($delivery);
        if (!empty($batteries)) {
            /** @var \core_kernel_classes_Resource $battery */
            foreach ($batteries as $battery) {
                $battery->removePropertyValue($this->getProperty(self::BATTERY_DELIVERIES), $delivery->getUri());
            }
        }

    }

    /**
     * Find all batteries where the delivery is used
     *
     * @param \core_kernel_classes_Resource $delivery
     * @return array
     * @throws BatteryException
     */
    public function findDeliveryBattery(\core_kernel_classes_Resource $delivery)
    {
        /** @var ComplexSearchService $search */
        $search = $this->getServiceLocator()->get(ComplexSearchService::SERVICE_ID);
        $queryBuilder = $search->query();

        $myQuery = $search->searchType($queryBuilder, self::BATTERY_URI, true)
            ->add(self::BATTERY_DELIVERIES)->contains($delivery->getUri());

        $queryBuilder->setCriteria($myQuery);
        try {
            $result = $search->getGateway()->search($queryBuilder);
        } catch (SearchGateWayExeption $e) {
            throw new BatteryException('A search runtime exception has occurred.', 0, $e);
        }

        $batteries = [];
        if ($result->count() > 0) {
            /** @var \core_kernel_classes_Resource $battery */
            foreach ($result as $battery) {
                $batteries[$battery->getUri()] = $battery;
            }
        }

        return $batteries;
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
     * Add a delivery to battery
     * Delete delivery from other battery
     *
     * @param \core_kernel_classes_Resource $battery
     * @param \core_kernel_classes_Resource $delivery
     * @throws BatteryException
     */
    public function addDeliveryToBattery(\core_kernel_classes_Resource $battery, \core_kernel_classes_Resource $delivery)
    {
        $this->deleteDeliveryFromBatteries($delivery);
        $battery->setPropertyValue(
            $this->getProperty(RdfBatteryService::BATTERY_DELIVERIES), $delivery
        );
    }

    /**
     * Get all deliveries associated to a battery
     *
     * @param RdfBattery|BatteryModel $battery
     * @return array
     */
    public function getBatteryDeliveries(BatteryModel $battery)
    {
        $deliveryUris = $battery->getPropertyValues($this->getProperty(self::BATTERY_DELIVERIES));
        $deliveries = [];
        foreach ($deliveryUris as $key => $deliveryUri) {
            $delivery = $this->getResource($deliveryUri);
            if ($this->isValidDelivery($delivery)) {
                $deliveries[$key] = $delivery;
            }
        }
        return $deliveries;
    }

    /**
     * Check if battery contains a delivery with the given $uri
     *
     * @param $battery
     * @param $uri
     * @return bool
     * @throws BatteryException
     */
    public function isBatteryDelivery($battery, $uri)
    {
        $battery = $this->buildBattery($battery);

        /** @var ComplexSearchService $search */
        $search = $this->getServiceLocator()->get(ComplexSearchService::SERVICE_ID);
        $queryBuilder = $search->query();

        $myQuery = $search->searchType($queryBuilder, self::BATTERY_URI, true)
            ->add(self::BATTERY_DELIVERIES)->contains($uri)
        ;

        $queryBuilder->setCriteria($myQuery);
        try {
            $result = $search->getGateway()->search($queryBuilder);
        } catch (SearchGateWayExeption $e) {
            throw new BatteryException('A search runtime exception has occurred.', 0, $e);
        }

        if ($result->count() != 0) {
            $foundBattery = $result[0];
            if ($foundBattery->subject == $battery->getId()) {
                return true;
            }
        }

        return false;
    }

}
