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
namespace oat\taoBattery\model\event;

use core_kernel_classes_Resource as Resource;

abstract class AbstractBatteryEvent implements BatteryEventInterface
{
    /**
     * @var Resource
     */
    protected $battery;

    /**
     * @var array
     */
    protected $newValues;

    /**
     * @param Resource $battery
     * @param array $newValues
     */
    public function __construct(Resource $battery, array $newValues)
    {
        $this->battery = $battery;
        $this->newValues = $newValues;
    }

    /**
     * @return Resource
     */
    public function getBattery()
    {
        return $this->battery;
    }

    /**
     * @return array
     */
    public function getNewValues()
    {
        return $this->newValues;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return static::class;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'subject' => $this->battery->getUri(),
            'changes' => $this->newValues
        ];
    }

    /**
     * @return string
     */
    abstract public function getBatteryAction();
}
