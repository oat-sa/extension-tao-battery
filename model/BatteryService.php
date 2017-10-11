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

class BatteryService extends \tao_models_classes_ClassService
{
    use OntologyAwareTrait;

    const BATTERY_URI = 'http://www.taotesting.com/ontologies/battery.rdf#Battery';

    const BATTERY_DELIVERIES = 'http://www.taotesting.com/ontologies/battery.rdf#deliveries';
    
    const BATTERY_GROUPS = 'http://www.taotesting.com/ontologies/battery.rdf#groups';

    /**
     * Get battery root class
     *
     * @return \core_kernel_classes_Class
     */
    public function getRootClass()
    {
        return $this->getClass(self::BATTERY_URI);
    }
}