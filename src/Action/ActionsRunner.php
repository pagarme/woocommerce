<?php

namespace Woocommerce\Pagarme\Action;

class ActionsRunner implements RunnerInterface
{
    private $actionClasses = [
        "OrderActions"
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
