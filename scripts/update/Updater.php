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

use oat\oatbox\event\EventManager;
use oat\tao\scripts\update\OntologyUpdater;
use oat\taoBattery\model\event\BatteryCreatedEvent;
use oat\taoBattery\model\event\BatteryModifiedEvent;
use oat\taoBattery\model\event\BatteryRemovedEvent;
use oat\taoBattery\model\event\BatteryRemoveFailedEvent;
use oat\taoBattery\model\handler\BatteryEventHandler;
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
            $batteryService = new RdfBatteryService();
            $batteryService->setHeader(
                '<?php' . PHP_EOL .
                '/**' . PHP_EOL .
                ' * Service to manage battery of deliveries' . PHP_EOL .
                ' *' . PHP_EOL .
                ' * MUST implements \oat\taoBattery\model\service\BatteryService' . PHP_EOL .
                ' *' . PHP_EOL .
                ' */' . PHP_EOL
            );
            $this->getServiceManager()->register(BatteryService::SERVICE_ID, $batteryService);

            $deliveryPickerService = new RandomDeliveryPicker();
            $deliveryPickerService->setHeader(
                '<?php' . PHP_EOL .
                '/**' . PHP_EOL .
                ' * Battery delivery picker' . PHP_EOL .
                ' *' . PHP_EOL .
                ' * MUST implements \oat\taoBattery\model\picker\DeliveryPicker' . PHP_EOL .
                ' *' . PHP_EOL .
                ' * A component to extract the delivery from deliveries array provided by a battery' . PHP_EOL .
                ' *' . PHP_EOL .
                ' */' . PHP_EOL
            );
            $this->getServiceManager()->register(DeliveryPicker::SERVICE_ID, $deliveryPickerService);

            OntologyUpdater::syncModels();

            $this->setVersion('0.1.0');
        }

        $this->skip('0.1.0', '0.6.3');
        if ($this->isVersion('0.6.3')) {
            // events
            /** @var EventManager $eventManager */
            $this->getServiceManager()->register(BatteryEventHandler::SERVICE_ID, new BatteryEventHandler());
            $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
            $eventManager->attach(BatteryCreatedEvent::class, [BatteryEventHandler::SERVICE_ID, 'logBatteryCreateEntry']);
            $eventManager->attach(BatteryModifiedEvent::class, [BatteryEventHandler::SERVICE_ID, 'logBatteryModifyEntry']);
            $eventManager->attach(BatteryRemovedEvent::class, [BatteryEventHandler::SERVICE_ID, 'logBatteryModifyEntry']);
            $eventManager->attach(BatteryRemoveFailedEvent::class, [BatteryEventHandler::SERVICE_ID, 'logBatteryModifyEntry']);
            $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);
            $this->setVersion('0.6.4');
        }
    }

}
