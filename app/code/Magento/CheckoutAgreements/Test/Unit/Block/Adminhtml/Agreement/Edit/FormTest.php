<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Test\Unit\Block\Adminhtml\Agreement\Edit;

use Magento\CheckoutAgreements\Model\AgreementsProvider;
use Magento\Store\Model\ScopeInterface;

class FormTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\ObjectManagerInterface
     */
    protected $objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Api\Data\StoreInterface
     */
    protected $storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\System\Store
     */
    protected $systemStoreMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\CheckoutAgreements\Model\Agreement
     */
    protected $agreementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Registry
     */
    protected $registryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Data\Form\Element\Factory
     */
    protected $factoryElement;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Data\Form\Element\CollectionFactory
     */
    protected $factoryCollection;

    /**
     * @var \Magento\CheckoutAgreements\Block\Adminhtml\Agreement\Edit\Form
     */
    protected $model;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->storeMock = $this->createMock(\Magento\Store\Api\Data\StoreInterface::class);
        $this->systemStoreMock = $this->createMock(\Magento\Store\Model\System\Store::class);
        $this->agreementMock = $this->objectManager->getObject(\Magento\CheckoutAgreements\Model\Agreement::class);
        $this->registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $this->formFactory = $this->objectManager->getObject(
            \Magento\Framework\Data\FormFactory::class,
            ['objectManager' => $this->objectManagerMock]
        );
        $this->factoryElement = $this->objectManager->getObject(
            \Magento\Framework\Data\Form\Element\Factory::class,
            ['objectManager' => $this->objectManagerMock]
        );
        $this->factoryCollection = $this->objectManager->getObject(
            \Magento\Framework\Data\Form\Element\CollectionFactory::class,
            ['objectManager' => $this->objectManagerMock]
        );
        $this->model = $this->objectManager->getObject(
            \Magento\CheckoutAgreements\Block\Adminhtml\Agreement\Edit\Form::class,
            [
                'storeManager' => $this->storeManagerMock,
                'formFactory' => $this->formFactory,
                'registry' => $this->registryMock,
                'systemStore' => $this->systemStoreMock
            ]
        );

        $this->objectManagerMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function($className, $arguments) {
                    // Always inject object manager and factory classes
                    $arguments['objectManager'] = $this->objectManagerMock;
                    $arguments['factoryElement'] = $this->factoryElement;
                    $arguments['factoryCollection'] = $this->factoryCollection;
                    return $this->objectManager->getObject($className, $arguments);
                }
            );
    }

    /**
     * Call protected/private method of a class.
     *
     * @param $object
     * @param $methodName
     * @param array $parameters
     * @return mixed
     */
    protected function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    public function dataForStoreState()
    {
        return [
            [
                false, [1]
            ],
            [
                false, [2]
            ],
            [
                false, [1, 2]
            ],
            [
                false, [2, 1]
            ],
            [
                true, [1]
            ],
            [
                true, [2]
            ],
            [
                true, [1, 2]
            ],
            [
                true, [2, 1]
            ]
        ];
    }

    /**
     * @dataProvider dataForStoreState
     */
    public function testPrepareForm($singleStoreMode, array $storeIds)
    {
        $this->agreementMock->setData('stores', $storeIds);

        $this->storeManagerMock
            ->expects($this->once())
            ->method('isSingleStoreMode')
            ->willReturn($singleStoreMode);
        $this->registryMock
            ->expects($this->once())
            ->method('registry')
            ->with('checkout_agreement')
            ->willReturn($this->agreementMock);

        if ($singleStoreMode) {
            $this->storeManagerMock
                ->expects($this->exactly(2))
                ->method('getStore')
                ->with(true)
                ->willReturn($this->storeMock);
            $this->storeMock
                ->expects($this->exactly(2))
                ->method('getId')
                ->willReturn($storeIds[0]);
        } else {
            $rendererMock = $this->createMock(
                \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element::class
            );
            $this->systemStoreMock
                ->expects($this->once())
                ->method('getStoreValuesForForm')
                ->with(false, true)
                ->willReturn($storeIds);
            $this->model->getLayout()
                ->expects($this->once())
                ->method('createBlock')
                ->willReturn($rendererMock);
        }

        $this->invokeMethod($this->model, '_prepareForm');
        $this->assertEquals(
            $singleStoreMode ? $storeIds[0] : $storeIds,
            $this->model->getForm()->getElement('stores')->getData('value')
        );
    }
}
