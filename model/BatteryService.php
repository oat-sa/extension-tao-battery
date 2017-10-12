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

namespace oat\taoBattery\model;

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\taoBattery\model\model\BatteryModel;
use oat\taoBattery\model\model\BatteryModelException;
use oat\taoBattery\model\model\BatteryResource;
use oat\taoBattery\model\picker\DeliveryPicker;

/**
 * Class BatteryService
 * @package oat\taoBattery\model
 */
class BatteryService extends ConfigurableService
{
    use OntologyAwareTrait;

    const SERVICE_ID = 'taoBattery/batteryService';

    const BATTERY_URI = 'http://www.taotesting.com/ontologies/battery.rdf#Battery';
    
    //const BATTERY_GROUPS = 'http://www.taotesting.com/ontologies/battery.rdf#groups';

    /**
     * Get a delivery from the given battery.
     * A battery contains a list of deliveries, the deliveryPicker will extract one from this array.
     * Return null if there is no valid delivery
     *
     * @param $battery
     * @return array|\core_kernel_classes_Resource|null
     * @throws BatteryException
     */
    public function pickDeliveryByBattery($battery)
    {
        $deliveries = $this->buildBatteryModel($battery)->getDeliveries();
        if (empty($deliveries)) {
            \common_Logger::i(sprintf('No deliveries associated to the battery %s.', $battery->getId()));
            return [];
        }
        $delivery = $this->getResource($this->getDeliveryPicker()->pickDelivery($deliveries));
        if ($this->isValidDelivery($delivery)) {
            return $delivery;
        }
        return null;
    }

    /**
     * Construct the battery model from the given battery param
     * If the $battery is an ontology resource then return a BatteryResource
     *
     * @param $battery
     * @return BatteryModel
     * @throws BatteryException
     */
    public function buildBatteryModel($battery)
    {
        if ($battery instanceof \core_kernel_classes_Resource) {
            if ($this->isBatteryResource($battery)) {
                return new BatteryResource($battery->getUri());
            }
            \common_Logger::w('BuildBattery has detected battery as resource, but this resource is not under Battery Root Class.');
        }

        throw new BatteryModelException('Unable to find a battery model for the given battery.');
    }

    protected function isValidDelivery(\core_kernel_classes_Resource $delivery)
    {
        return true;
    }

    /**
     * Check if a resource is under the battery root class
     *
     * @param \core_kernel_classes_Resource $battery
     * @return bool
     */
    protected function isBatteryResource(\core_kernel_classes_Resource $battery)
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
     * Get the delivery picker from taoBattery config
     *
     * @return DeliveryPicker
     */
    protected function getDeliveryPicker()
    {
        return $this->getServiceLocator()->get(DeliveryPicker::SERVICE_ID);
    }
}