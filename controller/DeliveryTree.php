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

namespace oat\taoBattery\controller;

use common_Exception;
use common_exception_IsAjaxAction;
use oat\generis\model\kernel\persistence\smoothsql\search\filter\Filter;
use oat\generis\model\kernel\persistence\smoothsql\search\filter\FilterOperator;
use oat\oatbox\event\EventManagerAwareTrait;
use oat\tao\model\Tree\GetTreeRequest;
use oat\tao\model\Tree\GetTreeService;
use oat\taoBattery\model\BatteryException;
use oat\taoBattery\model\event\BatteryModifiedEvent;
use oat\taoBattery\model\service\BatteryService;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;

class DeliveryTree extends \tao_actions_GenerisTree
{
    use EventManagerAwareTrait;

    /**
     * @throws common_Exception
     * @throws common_exception_IsAjaxAction
     */
    public function getData()
    {
        /** @var GetTreeService $service */
        $service = $this->getServiceLocator()->get(GetTreeService::SERVICE_ID);
        $request = GetTreeRequest::create($this->getRequest());

        $request->setFilters([
            new Filter(DeliveryAssemblyService::PROPERTY_DELIVERY_RUNTIME, null, FilterOperator::createIsNotNull())
        ]);

        $response = $service->handle($request);

        return $this->returnJson($response->getTreeArray());
    }

    /**
     * Callback for delivery tree to register deliveries to battery
     * Foreach deliveries received, it will be deleted from all batteries before set it to current
     *
     * @throws \common_exception_IsAjaxAction
     */
    public function setValues()
    {
        if (!$this->isXmlHttpRequest()) {
            throw new \common_exception_IsAjaxAction(__FUNCTION__);
        }

        $values = \tao_helpers_form_GenerisTreeForm::getSelectedInstancesFromPost();

        $resource = $this->getResource($this->getRequestParameter('resourceUri'));
        $property = $this->getProperty($this->getRequestParameter('propertyUri'));

        try {
            foreach ($values as $delivery) {
                $this->getBatteryService()->deleteDeliveryFromBatteries($this->getResource($delivery));
            }
        } catch (BatteryException $e) {
            $this->returnJson(['saved' => false]);
            return;
        }

        $success = $resource->editPropertyValues($property, $values);

        $this->getEventManager()->trigger(new BatteryModifiedEvent($resource, [$property->getUri() => $values]));

        $this->returnJson(['saved' => $success]);
    }

    /**
     * Return the battery service
     *
     * @return BatteryService
     */
    protected function getBatteryService()
    {
        return $this->getServiceManager()->get(BatteryService::SERVICE_ID);
    }
}
