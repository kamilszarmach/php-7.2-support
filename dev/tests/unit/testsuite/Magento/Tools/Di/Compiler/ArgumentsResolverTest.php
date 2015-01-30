<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Tools\Di\Compiler;

class ArgumentsResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\Di\Compiler\ArgumentsResolver
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $diContainerConfig;

    protected function setUp()
    {
        $this->diContainerConfig = $this->getMock(
            'Magento\Framework\ObjectManager\ConfigInterface',
            [],
            [],
            '',
            false
        );
        $this->model = new \Magento\Tools\Di\Compiler\ArgumentsResolver($this->diContainerConfig);
    }

    public function testGetResolvedArgumentsConstructorFormat()
    {
        $expectedResultDefault = $this->getResolvedSimpleConfigExpectation();

        $constructor = [
            new ConstructorArgument(['type_dependency', 'Type\Dependency', true, null]),
            new ConstructorArgument(['type_dependency_shared', 'Type\Dependency\Shared', true, null]),
            new ConstructorArgument(['value', null, false, 'value']),
            new ConstructorArgument(['value_array', null, false, ['default_value1', 'default_value2']]),
        ];
        $this->diContainerConfig->expects($this->any())
            ->method('isShared')
            ->willReturnMap(
                [
                    ['Type\Dependency', false],
                    ['Type\Dependency\Shared', true]
                ]
            );

        $type = 'Class';
        $this->diContainerConfig->expects($this->any())
            ->method('getArguments')
            ->with($type)
            ->willReturn([]);

        $this->assertSame(
            $expectedResultDefault,
            $this->model->getResolvedConstructorArguments($type, $constructor)
        );
    }

    public function testGetResolvedArgumentsConstructorConfiguredFormat()
    {
        $expectedResultConfigured = $this->getResolvedConfigurableConfigExpectation();

        $constructor = [
            new ConstructorArgument(['type_dependency_configured', 'Type\Dependency', true, null]),
            new ConstructorArgument(['type_dependency_shared_configured', 'Type\Dependency\Shared', true, null]),
            new ConstructorArgument(['global_argument', null, false, null]),
            new ConstructorArgument(['global_argument_def', null, false, []]),
            new ConstructorArgument(['value_configured', null, false, 'value']),
            new ConstructorArgument(['value_array_configured', null, false, []]),
        ];



        $this->diContainerConfig->expects($this->any())
            ->method('isShared')
            ->willReturnMap(
                [
                    ['Type\Dependency', false],
                    ['Type\Dependency\Shared', true],
                    ['Type\Dependency\Configured', false],
                    ['Type\Dependency\Shared\Configured', true]
                ]
            );

        $type = 'Class';
        $this->diContainerConfig->expects($this->any())
            ->method('getArguments')
            ->with($type)
            ->willReturn(
                $this->getConfiguredArguments()
            );

        $this->assertSame(
            $expectedResultConfigured,
            $this->model->getResolvedConstructorArguments($type, $constructor)
        );
    }

    /**
     * Returns resolved simple config expectation
     *
     * @return array
     */
    private function getResolvedSimpleConfigExpectation()
    {
        return [
            'type_dependency' => [
                '_i_' => 'Type\Dependency',
                '_s_' => false,
            ],
            'type_dependency_shared' => [
                '_i_' => 'Type\Dependency\Shared',
                '_s_' => true,
            ],
            'value' => [
                '_v_' => 'value',
            ],
            'value_array' => [
                '_v_' => ['default_value1', 'default_value2'],
            ],
        ];
    }

    /**
     * Returns configured arguments expectation
     *
     * @return array
     */
    private function getConfiguredArguments()
    {
        return [
            'type_dependency_configured' => ['instance' => 'Type\Dependency\Configured'],
            'type_dependency_shared_configured' => ['instance' => 'Type\Dependency\Shared\Configured'],
            'global_argument' => ['argument' => 'global_argument_configured'],
            'global_argument_def' => ['argument' => 'global_argument_configured'],
            'value_configured' => 'value_configured',
            'value_array_configured' => [
                'array_value' => 'value',
                'array_configured_instance' => ['instance' => 'Type\Dependency\Shared\Configured'],
                'array_configured_array' => [
                    'array_array_value' => 'value',
                    'array_array_configured_instance' => [
                        'instance' => 'Type\Dependency\Shared\Configured',
                        'shared' => false
                    ]
                ],
                'array_global_argument' => ['argument' => 'global_argument_configured']
            ]
        ];
    }

    /**
     * Returns resolved configurable config expectation
     *
     * @return array
     */
    private function getResolvedConfigurableConfigExpectation()
    {
        return [
            'type_dependency_configured' => [
                '_i_' => 'Type\Dependency\Configured',
                '_s_' => false,
            ],
            'type_dependency_shared_configured' => [
                '_i_' => 'Type\Dependency\Shared\Configured',
                '_s_' => true,
            ],
            'global_argument' => [
                '_a_' => 'global_argument_configured',
                '_d_' => null
            ],
            'global_argument_def' => [
                '_a_' => 'global_argument_configured',
                '_d_' => []
            ],
            'value_configured' => [
                '_v_' => 'value_configured',
            ],
            'value_array_configured' => [
                '_v_' => [
                    'array_value' => 'value',
                    'array_configured_instance' => [
                        '_i_' => 'Type\Dependency\Shared\Configured',
                        '_s_' => true,
                    ],
                    'array_configured_array' => [
                        'array_array_value' => 'value',
                        'array_array_configured_instance' => [
                            '_i_' => 'Type\Dependency\Shared\Configured',
                            '_s_' => false,
                        ],
                    ],
                    'array_global_argument' => [
                        '_a_' => 'global_argument_configured',
                        '_d_' => null
                    ]
                ],
            ]
        ];
    }
}
