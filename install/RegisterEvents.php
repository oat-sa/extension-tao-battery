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

namespace oat\taoBattery\scripts\install;

use oat\oatbox\extension\InstallAction;
use oat\taoBattery\model\event\BatteryCreatedEvent;
use oat\taoBattery\model\event\BatteryModifiedEvent;
use oat\taoBattery\model\event\BatteryRemoveFailedEvent;
use oat\taoBattery\model\handler\BatteryEventHandler;

/**
 * Class RegisterEvents
 * @package oat\taoBattery\scripts\install
 */
class RegisterEvents extends InstallAction
{

    public function __invoke($params)
    {
        $this->registerEvent(BatteryCreatedEvent::class , [BatteryEventHandler::SERVICE_ID , 'logBatteryChangesEntry']);
        $this->registerEvent(BatteryModifiedEvent::class , [BatteryEventHandler::SERVICE_ID , 'logBatteryChangesEntry']);
        $this->registerEvent(BatteryModifiedEvent::class , [BatteryEventHandler::SERVICE_ID , 'logBatteryChangesEntry']);
        $this->registerEvent(BatteryRemoveFailedEvent::class , [BatteryEventHandler::SERVICE_ID , 'logBatteryChangesEntry']);
    }
}