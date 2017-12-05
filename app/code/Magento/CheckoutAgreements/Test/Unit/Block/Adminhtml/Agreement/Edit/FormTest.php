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
//        $this->agreementMock = $this->createMock(\Magento\CheckoutAgreements\Model\Agreement::class);
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

    public function testPrepareForm()
    {
        $this->storeManagerMock
            ->expects($this->once())
            ->method('isSingleStoreMode')
            ->willReturn(true);
        $this->registryMock
            ->expects($this->once())
            ->method('registry')
            ->with('checkout_agreement')
            ->willReturn($this->agreementMock);
        $this->storeManagerMock
            ->expects($this->exactly(2))
            ->method('getStore')
            ->with(true)
            ->willReturn($this->storeMock);
        $this->storeMock
            ->expects($this->exactly(2))
            ->method('getId')
            ->willReturn(100);//storeId

        $this->invokeMethod($this->model, '_prepareForm');

        $this->assertEquals(
            100,
            $this->model->getForm()->getElement('stores')->getEscapedValue()
        );
    }
}
