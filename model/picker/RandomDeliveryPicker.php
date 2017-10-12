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

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;

/**
 * Class RandomDeliveryPicker
 * @package oat\taoBattery\model\picker
 */
class RandomDeliveryPicker extends ConfigurableService implements DeliveryPicker
{
    use OntologyAwareTrait;

    /**
     * Pick a delivery randomly from $deliveries array
     * If $deliveries array is empty, return null
     *
     * @param array $deliveries An array of delivery uris
     * @return \core_kernel_classes_Resource|null
     */
    public function pickDelivery(array $deliveries)
    {
        if (empty($deliveries)) {
            return null;
        }
        return $this->getResource($deliveries[array_rand($deliveries)]);
    }
}