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

use oat\taoBattery\model\event\BatteryRemovedEvent;
use oat\taoBattery\model\event\BatteryRemoveFailedEvent;

/**
 * Class BatteryClassService. The service use from the Battery RdfController
 * @package oat\taoBattery\model
 */
class RdfBatteryClassService extends \tao_models_classes_ClassService
{
    /**
     * Get battery root class
     *
     * @return \core_kernel_classes_Class
     */
    public function getRootClass()
    {
        return $this->getClass(RdfBatteryService::BATTERY_URI);
    }

    /** @noinspection PhpDocMissingThrowsInspection */
    /**
     * Delete a resource
     *
     * @param \core_kernel_classes_Resource $resource
     * @return boolean
     */
    public function deleteResource(\core_kernel_classes_Resource $resource)
    {
        try {
            $result = $resource->delete();
            $this->getEventManager()->trigger(new BatteryRemovedEvent($resource, []));
        }
        catch (\Throwable $e) {
            // If some BatteryBeforeRemoveEvent event handler wants to prevent battery removing and
            // throws the exception we have to ensure that other BatteryRemoveFailedEvent subscribers
            // will be notified
            $this->getEventManager()->trigger(new BatteryRemoveFailedEvent($resource, []));
            /** @noinspection PhpUnhandledExceptionInspection */
            throw $e;
        }

        return $result;
    }
}
