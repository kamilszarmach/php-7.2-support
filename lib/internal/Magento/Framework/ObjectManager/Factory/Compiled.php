<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Factory;

class Compiled extends AbstractFactory
{
    /**
     * Create instance with call time arguments
     *
     * @param string $requestedType
     * @param array $arguments
     * @return object
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function create($requestedType, array $arguments = [])
    {
        $type = $this->config->getInstanceType($requestedType);
        $requestedType = ltrim($requestedType, '\\');
        $args = $this->config->getArguments($requestedType);
        if ($args == null) {
            return new $type();
        }

        foreach ($args as $key => &$argument) {
            if (isset($arguments[$key])) {
                $argument = $arguments[$key];
            } else {
                if ($argument === (array)$argument) {
                    if (isset($argument['_v_'])) {
                        $argument = $argument['_v_'];
                        if ($argument === (array)$argument) {
                            $this->parseArray($argument);
                        }
                    } elseif (isset($argument['_i_'])) {
                        if ($argument['_s_']) {
                            $argument = $this->objectManager->get($argument['_i_']);
                        } else {
                            $argument = $this->objectManager->create($argument['_i_']);
                        }
                    } elseif (isset($argument['_a_'])) {
                        if (isset($this->globalArguments[$argument['_a_']])) {
                            $argument = $this->globalArguments[$argument['_a_']];
                        } else {
                            $argument = $argument['_d_'];
                        }
                    }
                }
            }
        }

        $args = array_values($args);
        if (substr($type, -12) == '\Interceptor') {
            $args = array_merge([
                $this->objectManager, $this->objectManager->get('Magento\Framework\Interception\PluginListInterface'),
                $this->objectManager->get('Magento\Framework\Interception\ChainInterface'),
            ], $args);
        }

        return $this->createObject($type, $args);
    }

    /**
     * Parse array argument
     *
     * @param array $array
     *
     * @return void
     */
    protected function parseArray(&$array)
    {
        foreach ($array as $key => $item) {
            if ($item === (array)$item) {
                if (isset($item['_i_'])) {
                    if ($item['_s_']) {
                        $array[$key] = $this->objectManager->get($item['_i_']);
                    } else {
                        $array[$key] = $this->objectManager->create($item['_i_']);
                    }

                } elseif (isset($item['_a_'])) {
                    if (isset($this->globalArguments[$item['_a_']])) {
                        $array[$key] = $this->globalArguments[$item['_a_']];
                    } else {
                        $array[$key] = $item['_d_'];
                    }
                } else {
                    $this->parseArray($array[$key]);
                }
            }
        }
    }
}
