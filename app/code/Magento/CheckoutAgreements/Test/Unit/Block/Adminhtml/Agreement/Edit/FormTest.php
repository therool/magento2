<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Test\Unit\Block\Adminhtml\Agreement\Edit;

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
    protected $formFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Data\Form::class
     */
    protected $formMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Data\Form\Element\Fieldset::class
     */
    protected $fieldsetMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Data\Form\Element\Multiselect::class
     */
    protected $multiselectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Data\Form\Element\Hidden::class
     */
    protected $hiddenMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element::class
     */
    protected $rendererMock;

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
        $this->formFactoryMock = $this->createMock(\Magento\Framework\Data\FormFactory::class);
        $this->formMock = $this->createMock(\Magento\Framework\Data\Form::class);
        $this->fieldsetMock = $this->createMock(\Magento\Framework\Data\Form\Element\Fieldset::class);
        $this->multiselectMock = $this->createMock(\Magento\Framework\Data\Form\Element\Multiselect::class);
        $this->hiddenMock = $this->createMock(\Magento\Framework\Data\Form\Element\Hidden::class);
        $this->rendererMock = $this->createMock(
            \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element::class
        );
        $this->model = $this->objectManager->getObject(
            \Magento\CheckoutAgreements\Block\Adminhtml\Agreement\Edit\Form::class,
            [
                'storeManager' => $this->storeManagerMock,
                'formFactory' => $this->formFactoryMock,
                'registry' => $this->registryMock,
                'systemStore' => $this->systemStoreMock
            ]
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

        $this->registryMock
            ->expects($this->once())
            ->method('registry')
            ->with('checkout_agreement')
            ->willReturn($this->agreementMock);
        $this->storeManagerMock
            ->expects($this->once())
            ->method('isSingleStoreMode')
            ->willReturn($singleStoreMode);
        $this->formFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with([
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->model->getData('action'),
                    'method' => 'post'
                ]
            ])
            ->willReturn($this->formMock);
        $this->formMock
            ->expects($this->once())
            ->method('addFieldset')
            ->with(
                'base_fieldset',
                ['legend' => __('Terms and Conditions Information'), 'class' => 'fieldset-wide']
            )
            ->willReturn($this->fieldsetMock);

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
            $this->fieldsetMock
                ->expects($this->at(4))
                ->method('addField')
                ->with(
                    'stores',
                    'hidden',
                    ['name' => 'stores[]', 'value' => $storeIds[0]]
                )
                ->willReturn($this->hiddenMock);
        } else {
            $this->systemStoreMock
                ->expects($this->once())
                ->method('getStoreValuesForForm')
                ->with(false, true)
                ->willReturn($storeIds);
            $this->fieldsetMock
                ->expects($this->at(4))
                ->method('addField')
                ->with(
                    'stores',
                    'multiselect',
                    [
                        'name' => 'stores[]',
                        'label' => __('Store View'),
                        'title' => __('Store View'),
                        'required' => true,
                        'values' => $storeIds
                    ])
                ->willReturn($this->multiselectMock);
            $this->model->getLayout()
                ->expects($this->once())
                ->method('createBlock')
                ->willReturn($this->rendererMock);
            $this->multiselectMock
                ->expects($this->once())
                ->method('setRenderer')
                ->willReturn($this->rendererMock);
        }

        $this->invokeMethod($this->model, '_prepareForm');
        $this->assertEquals(
            $singleStoreMode ? $storeIds[0] : $storeIds,
            $this->agreementMock->getData('stores')
        );
    }
}
