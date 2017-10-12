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

namespace oat\taoBattery\model\model;

/**
 * Class BatteryResource
 * An ontology abstraction to data using \core_kernel_classes_Resource
 * @package oat\taoBattery\model\model
 */
class BatteryResource extends \core_kernel_classes_Resource implements BatteryModel
{
    const BATTERY_DELIVERIES = 'http://www.taotesting.com/ontologies/battery.rdf#deliveries';

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getUri();
    }

    /**
     * @inheritdoc
     */
    public function getDeliveries()
    {
        return $this->getPropertyValues($this->getProperty(self::BATTERY_DELIVERIES));
    }
}