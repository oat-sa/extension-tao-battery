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

namespace oat\taoBattery\scripts\update;

use oat\tao\scripts\update\OntologyUpdater;
use oat\taoBattery\model\picker\DeliveryPicker;
use oat\taoBattery\model\picker\random\RandomDeliveryPicker;
use oat\taoBattery\model\service\BatteryService;
use oat\taoBattery\model\service\rdf\RdfBatteryService;

class Updater extends \common_ext_ExtensionUpdater
{
    public function update($initialVersion)
    {
        if ($this->isVersion('0.0.1')) {
            OntologyUpdater::syncModels();
            $this->getServiceManager()->register(BatteryService::SERVICE_ID, new RdfBatteryService());
            $this->getServiceManager()->register(DeliveryPicker::SERVICE_ID, new RandomDeliveryPicker());
            $this->setVersion('0.1.0');
        }
    }

}