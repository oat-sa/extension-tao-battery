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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 */

namespace oat\taoBattery\model\handler;

use oat\taoBattery\model\event\BatteryEventInterface;
use oat\taoEventLog\model\eventLog\LoggerService;
use oat\oatbox\service\ConfigurableService;

/**
 * Class BatteryEventHandler
 * @package oat\taoBattery\model\handler
 */
class BatteryEventHandler extends ConfigurableService
{
    const SERVICE_ID = 'taoBattery/batteryEventHandler';

    /**
     * Handle batteries events
     * @param BatteryEventInterface $event
     * @return string
     * @throws \common_exception_Error
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    public function logBatteryChangesEntry(BatteryEventInterface $event)
    {
        $this->getLoggerService()->setAction($event->getBatteryAction());
        $this->getLoggerService()->log($event);
    }

    /**
     * @return ConfigurableService | LoggerService
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    private function getLoggerService()
    {
        return $this->getServiceManager()->get(LoggerService::SERVICE_ID);
    }
}