<?php

namespace simaland\timecop\tests;

use PHPUnit\Framework\TestCase;
use simaland\timecop\TimeFreeze;
use yii\web\Application;
use yii\web\Session;

/**
 * @coversDefaultClass \simaland\timecop\TimeFreeze
 */
class TimeFreezeTest extends TestCase
{
    /**
     * Проверка управления временем на сайте.
     * 
     * @covers ::bootstrap
     * @covers ::init
     */
    public function testBootstrap(): void
    {
        $config = [
            'id' => 'app-timecop',
            'basePath' => dirname(__DIR__)
        ];
        $today = date('d.m.Y');
        $app = new Application($config);
        $timeFreeze = new TimeFreeze();
        $timeFreeze->session = $this->createMock(Session::class);
        $timeFreeze->session->expects($this->any())
            ->method("setCookieParams")
            ->willReturn($this->cookie[$timeFreeze->requestVariable] = $timeFreeze->request->getQueryParam($timeFreeze->requestVariable));
        $timeFreeze->session->expects($this->any())
            ->method("getCookieParams")
            ->willReturn($this->cookie[$timeFreeze->requestVariable]);
        $timeFreeze->session->expects($this->any())
            ->method("remove")
            ->willReturnCallback(function () use ($timeFreeze) {
                $this->cookie[$timeFreeze->requestVariable] = null;
                timecop_return();
            });

        // 1606780800 = 01.12.2020 12:00am (UTC)
        $timeFreeze->request->setQueryParams([$timeFreeze->requestVariable => 1606780800]);
        $timeFreeze->bootstrap($app);
        $this->assertEquals('01.12.2020',date('d.m.Y'));

        // Сброс даты на текущее время
        $timeFreeze->request->setQueryParams([$timeFreeze->requestVariable => 'reset']);
        $timeFreeze->bootstrap($app);
        $this->assertEquals($today, date('d.m.Y'));

        // 804211200 = 27.06.1995 12:00am (UTC)
        $timeFreeze->request->setQueryParams([$timeFreeze->requestVariable => 804211200]);
        $timeFreeze->bootstrap($app);
        $this->assertEquals('27.06.1995',date('d.m.Y'));

        // Сброс даты на текущее время
        $timeFreeze->request->setQueryParams([$timeFreeze->requestVariable => 'reset']);
        $timeFreeze->bootstrap($app);
        $this->assertEquals($today, date('d.m.Y'));
    }
}
