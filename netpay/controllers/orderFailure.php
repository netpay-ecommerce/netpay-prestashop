<?php

class OrderFailureModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $this->context->smarty->assign(array(
            'order_url' => $this->context->link->getPageLink('order')
        ));

        $this->setTemplate('module:netpay/views/templates/front/order-confirmation-failed.tpl');
    }
}

?>