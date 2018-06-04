<?php

namespace sima\timecop\tests;

use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use sima\timecop\TimeFreeze;
use yii\web\Application;
use yii\web\Session;

class TimeFreezeTest extends TestCase
{

    protected $cookie = null;

    /**
     * Проверка управления временем на сайте.
     */
    public function testBootstrap(): void
    {
        $config = [
            'id' => 'app-timecop',
            'basePath' => dirname(__DIR__)
        ];
        $app = new Application($config);
        $timeFreeze = new TimeFreeze();
        $timeFreeze->session = $this->createMock(Session::class);
        $timeFreeze->session->expects($this->any())
            ->method("setCookieParams")
            ->will($this->returnValue($this->cookie[$timeFreeze->requestVariable] = $timeFreeze->request->getQueryParam($timeFreeze->requestVariable)));
        $timeFreeze->session->expects($this->any())
            ->method("getCookieParams")
            ->will($this->returnValue($this->cookie[$timeFreeze->requestVariable]));
        // 1606780800 = 01.12.2020 12:00am (UTC)
        $timeFreeze->request->setQueryParams([$timeFreeze->requestVariable => 1606780800]);
        $timeFreeze->bootstrap($app);
        $this->assertEquals('01.12.2020',date('d.m.Y'));
        // 804211200 = 27.06.1995 12:00am (UTC)
        $timeFreeze->request->setQueryParams([$timeFreeze->requestVariable => 804211200]);
        $timeFreeze->bootstrap($app);
        $this->assertEquals('27.06.1995',date('d.m.Y'));
        // Сброс даты на текущее время
        $today = date('d.m.Y');
        $timeFreeze->request->setQueryParams([$timeFreeze->requestVariable => 'reset']);
        $timeFreeze->bootstrap($app);
        $this->assertEquals($today,date('d.m.Y'));
    }
}
