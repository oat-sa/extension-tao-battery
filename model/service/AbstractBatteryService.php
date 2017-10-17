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

namespace oat\taoBattery\model\service;

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\taoBattery\model\picker\DeliveryPicker;
use oat\taoBattery\model\BatteryException;

abstract class AbstractBatteryService extends ConfigurableService implements BatteryService
{
    use OntologyAwareTrait;

    const SERVICE_ID = 'taoBattery/batteryService';

    /**
     * Get a delivery from the given battery.
     * A battery contains a list of deliveries, the deliveryPicker will extract one from this array.
     * Return null if there is no valid delivery
     *
     * @param $battery
     * @return \core_kernel_classes_Resource|null
     * @throws BatteryException
     */
    public function pickDeliveryByBattery($battery)
    {
        $battery = $this->buildBattery($battery);
        $deliveries = $this->getBatteryDeliveries($battery);
        if (empty($deliveries)) {
            \common_Logger::i(sprintf('No deliveries associated to the battery %s.', $battery->getId()));
            return null;
        }
        return $this->getResource($this->getDeliveryPicker()->pickDelivery($deliveries));
    }

    /**
     * Check if the given delivery $uri is part of $battery deliveries list
     *
     * @param $battery
     * @param $uri
     * @return bool
     * @throws BatteryException
     */
    public function isBatteryDelivery($battery, $uri)
    {
        $battery = $this->buildBattery($battery);
        foreach ($this->getBatteryDeliveries($battery) as $delivery) {
            if ($delivery == $uri) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if a delivery is valid by checking the dates
     *
     * @param \core_kernel_classes_Resource $delivery
     * @return bool
     */
    protected function isValidDelivery(\core_kernel_classes_Resource $delivery)
    {
        return $this->verifyTime($delivery);
    }

    /**
     * Check if delivery start and end time make delivery available for now
     *
     * @param \core_kernel_classes_Resource $delivery
     * @return bool
     */
    protected function verifyTime(\core_kernel_classes_Resource $delivery)
    {
        try {
            $deliveryProps = $delivery->getPropertiesValues(array(
                TAO_DELIVERY_START_PROP,
                TAO_DELIVERY_END_PROP,
            ));
        } catch (\common_exception_InvalidArgumentType $e) {
            return false;
        }

        $startExec = empty($deliveryProps[TAO_DELIVERY_START_PROP])
            ? null
            : (string)current($deliveryProps[TAO_DELIVERY_START_PROP]);
        $stopExec = empty($deliveryProps[TAO_DELIVERY_END_PROP])
            ? null
            : (string)current($deliveryProps[TAO_DELIVERY_END_PROP]);

        $startDate = date_create('@' . $startExec);
        $endDate = date_create('@' . $stopExec);
        if (!$this->areWeInRange($startDate, $endDate)) {
            \common_Logger::d("Attempt to start the compiled delivery " . $delivery->getUri() . " at the wrong date");
            return false;
        }
        return true;
    }

    /**
     * Check if the current date is in range of start and end date
     *
     * @param $startDate
     * @param $endDate
     * @return bool
     */
    protected function areWeInRange($startDate, $endDate)
    {
        return (empty($startDate) || date_create() >= $startDate)
            && (empty($endDate) || date_create() <= $endDate);
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