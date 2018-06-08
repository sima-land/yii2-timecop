<?php

namespace simaland\timecop;

use yii\base\{
    Application,
    BootstrapInterface,
    Component,
    InvalidConfigException
};
use yii\web\{
    Request,
    Session
};
use yii\di\Instance;

/**
 * Класс для смены времени
 */
class TimeFreeze extends Component implements BootstrapInterface
{
    /**
     * @var string Наименование гет-параметра для управления временем.
     */
    public $requestVariable = 'TIME_VECTOR';

    /**
     * @var string|Session Сессия.
     */
    public $session = 'session';

    /**
     * @var string|Request Запрос.
     */
    public $request = 'request';

    /**
     * @inheritdoc
     */
    public function bootstrap($app): void
    {
        if (\extension_loaded('timecop')) {
            $this->bootstrapInit();
            $timeVector = $this->request->getQueryParam($this->requestVariable);
            if (null !== $timeVector) {
                if ($timeVector === 'reset') {
                    $this->session->remove($this->requestVariable);
                    $timeVector = null;
                } else {
                    $this->session->set($this->requestVariable, $timeVector);
                }
            } else {
                $timeVector = $this->session->get($this->requestVariable);
            }
            if (null !== $timeVector) {
                timecop_freeze((int)$timeVector);
            }
        }
    }

    /**
     * Инициализация параметров.
     *
     * @throws InvalidConfigException
     */
    public function bootstrapInit(): void
    {
        $this->session = Instance::ensure($this->session, Session::class);
        $this->request = Instance::ensure($this->request, Request::class);
    }
}
