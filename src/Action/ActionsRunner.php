<?php

namespace Woocommerce\Pagarme\Action;

/**
 * @uses CustomerFieldsActions, OrderActions
 */
class ActionsRunner implements RunnerInterface
{
    private $actionClasses = [
        "OrderActions",
        "CustomerFieldsActions"
    ];

    public function run()
    {
        foreach ($this->actionClasses as $actionClass) {
            $class = sprintf(__NAMESPACE__ . '\%s', $actionClass);
            $action = new $class();
            $action->run();
        }
    }
}
