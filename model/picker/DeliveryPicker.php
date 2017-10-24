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

namespace oat\taoBattery\model\picker;

/**
 * Interface BatteryPicker
 * A component to extract a delivery from battery
 * @package oat\taoBattery\model\picker
 */
interface DeliveryPicker
{
    const SERVICE_ID = 'taoBattery/deliveryPicker';

    /**
     * Pick a delivery from $deliveries array
     *
     * @param array $deliveries An array of delivery uris
     * @return \core_kernel_classes_Resource|null
     */
    public function pickDelivery(array $deliveries);
}