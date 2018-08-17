<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Twig;

use App\Entity\Timesheet;
use App\Twig\Extensions;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\TwigFilter;

/**
 * @covers \App\Twig\Extensions
 */
class ExtensionsTest extends TestCase
{
    /**
     * @param string $locales
     * @param string $locale
     * @return Extensions
     */
    protected function getSut($locales, $locale = 'en')
    {
        $request = new Request();
        $request->setLocale($locale);
        $requestStack = new RequestStack();
        $requestStack->push($request);

        return new Extensions($requestStack, $locales);
    }

    public function testGetFilters()
    {
        $filters = ['duration', 'money', 'currency', 'country', 'icon'];
        $sut = $this->getSut('de');
        $twigFilters = $sut->getFilters();
        $this->assertCount(count($filters), $twigFilters);
        $i = 0;
        foreach ($twigFilters as $filter) {
            $this->assertInstanceOf(TwigFilter::class, $filter);
            $this->assertEquals($filters[$i++], $filter->getName());
        }
    }

    public function testGetFunctions()
    {
        $functions = ['locales', 'is_visible_column'];
        $sut = $this->getSut('de');
        $twigFunctions = $sut->getFunctions();
        $this->assertCount(count($functions), $twigFunctions);
        $i = 0;
        foreach ($twigFunctions as $filter) {
            $this->assertInstanceOf(\Twig_SimpleFunction::class, $filter);
            $this->assertEquals($functions[$i++], $filter->getName());
        }
    }

    public function testLocales()
    {
        $locales = [
            ['code' => 'en', 'name' => 'English'],
            ['code' => 'de', 'name' => 'Deutsch'],
            ['code' => 'ru', 'name' => 'русский'],
        ];

        $sut = $this->getSut('en|de|ru');
        $this->assertEquals($locales, $sut->getLocales());
    }

    public function testCurrency()
    {
        $symbols = [
            'EUR' => '€',
            'USD' => '$',
            'RUB' => 'RUB',
        ];

        $sut = $this->getSut('en');
        foreach ($symbols as $name => $symbol) {
            $this->assertEquals($symbol, $sut->currency($name));
        }
    }

    public function testCountry()
    {
        $countries = [
            'DE' => 'Germany',
            'RU' => 'Russia',
            'ES' => 'Spain',
        ];

        $sut = $this->getSut('en');
        foreach ($countries as $locale => $name) {
            $this->assertEquals($name, $sut->country($locale));
        }
    }

    /**
     * @param string $result
     * @param int $amount
     * @param string $currency
     * @param string $locale
     * @dataProvider getMoneyData
     */
    public function testMoney($result, $amount, $currency, $locale)
    {
        $sut = $this->getSut('en', $locale);
        $this->assertEquals($result, $sut->money($amount, $currency));
    }

    public function getMoneyData()
    {
        return [
            ['2.345 €', 2345, 'EUR', 'de'],
            ['2,345 €', 2345, 'EUR', 'en'],
            ['2,345.01 €', 2345.009, 'EUR', 'en'],
            ['2.345,01 €', 2345.009, 'EUR', 'de'],
            ['13.75 $', 13.75, 'USD', 'en'],
            ['13,75 $', 13.75, 'USD', 'de'],
            ['13,75 RUB', 13.75, 'RUB', 'de'],
            ['13,5 RUB', 13.50, 'RUB', 'de'],
            ['13,75 ₽', 13.75, 'RUB', 'ru'],
            ['14 ¥', 13.75, 'JPY', 'de'],
            ['13 933 ¥', 13933.49, 'JPY', 'ru'],
            ['1.234.567,89 $', 1234567.891234567890000, 'USD', 'de'],
        ];
    }

    public function testDuration()
    {
        $record = $this->getTimesheet(9437);

        $sut = $this->getSut('en');
        $this->assertEquals('02:37 h', $sut->duration($record->getDuration()));
        $this->assertEquals('02:37:17 h', $sut->duration($record->getDuration(), true));

        $this->assertEquals('02:37 h', $sut->duration($record));
        $this->assertEquals('02:37:17 h', $sut->duration($record, true));
    }

    protected function getTimesheet($seconds)
    {
        $begin = new \DateTime();
        $end = clone $begin;
        $end->setTimestamp($begin->getTimestamp() + $seconds);
        $record = new Timesheet();
        $record->setBegin($begin);
        $record->setEnd($end);
        $record->setDuration($seconds);

        return $record;
    }

    public function testIcon()
    {
        $icons = [
            'user', 'customer', 'project', 'activity', 'admin', 'invoice', 'timesheet', 'dashboard', 'logout', 'trash',
            'delete', 'repeat', 'edit', 'manual', 'help', 'start', 'start-small', 'stop', 'stop-small', 'filter',
            'create', 'list', 'print', 'visibility', 'calendar', 'money', 'duration',
        ];

        // test pre-defined icons
        $sut = $this->getSut('en');
        foreach ($icons as $icon) {
            $result = $sut->icon($icon);
            $this->assertNotEmpty($result, 'Problem with icon definition: ' . $icon);
            $this->assertInternalType('string', $result);
        }

        // test fallback will be returned
        $this->assertEquals('', $sut->icon('foo'));
        $this->assertEquals('bar', $sut->icon('foo', 'bar'));
    }
}
