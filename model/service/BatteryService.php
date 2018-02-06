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

use oat\taoBattery\model\BatteryException;
use oat\taoBattery\model\model\BatteryModel;

interface BatteryService
{
    const SERVICE_ID = 'taoBattery/batteryService';

    /**
     * Construct the battery model from the given battery param
     * Return an implementation of battery model, following the used service
     *
     * @param $battery
     * @return BatteryModel
     * @throws BatteryException
     */
     public function buildBattery($battery);

    /**
     * Create a battery
     *
     * @param $label
     * @param array $deliveries
     * @return mixed
     */
     public function createBattery($label, array $deliveries);

    /**
     * Fetch a battery by label
     *
     * @param $label
     * @return null|BatteryModel
     * @throws BatteryException
     */
     public function fetchByLabel($label);

    /**
     * Delete a delivery from all batteries
     *
     * @param \core_kernel_classes_Resource $delivery
     * @throws BatteryException
     */
     public function deleteDeliveryFromBatteries(\core_kernel_classes_Resource $delivery);

    /**
     * Find all batteries where the delivery is used
     *
     * @param \core_kernel_classes_Resource $delivery
     * @return array
     * @throws BatteryException
     */
    public function findDeliveryBattery(\core_kernel_classes_Resource $delivery);

    /**
     * Find all batteries without any delivery
     *
     * @return array|BatteryModel[]
     * @throws BatteryException
     */
    public function findEmptyBatteries();

    /**
     * Add deliveries to a battery.
     * If delivery exists for another battery, delete it from others
     *
     * @param $batteryLabel
     * @param array $deliveries
     * @return BatteryModel
     * @throws BatteryException
     */
     public function addDeliveriesToBattery($batteryLabel, array $deliveries);

    /**
     * Add a delivery to battery
     * Delete delivery from other battery
     *
     * @param \core_kernel_classes_Resource $battery
     * @param \core_kernel_classes_Resource $delivery
     * @throws BatteryException
     */
    public function addDeliveryToBattery(\core_kernel_classes_Resource $battery, \core_kernel_classes_Resource $delivery);

    /**
     * Get all deliveries associated to a battery
     * Should return deliveries array at format:
     * `array(
         'uri1' => 'label1',
         'uri2' => 'label2',
     * )`
     * Should an empty array in case of no deliveries
     *
     * @param BatteryModel $battery
     * @return array
     */
     public function getBatteryDeliveries(BatteryModel $battery);

    /**
     * Get a delivery from the given battery.
     * A battery contains a list of deliveries, the deliveryPicker will extract one from this array.
     * Return null if there is no valid delivery
     *
     * @param $battery
     * @return \core_kernel_classes_Resource|null
     * @throws BatteryException
     */
     public function pickDeliveryByBattery($battery);

    /**
     * Build the battery
     * Check if the given delivery $uri is part of $battery deliveries list
     *
     * @param $battery
     * @param $uri
     * @return bool
     * @throws BatteryException
     */
     public function isBatteryDelivery($battery, $uri);
}
