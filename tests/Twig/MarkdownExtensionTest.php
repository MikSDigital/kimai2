<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Twig;

use App\Twig\MarkdownExtension;
use App\Utils\Markdown;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Twig\MarkdownExtension
 */
class MarkdownExtensionTest extends TestCase
{
    public function testGetFilters()
    {
        $sut = new MarkdownExtension(new Markdown());
        $filters = $sut->getFilters();
        $this->assertCount(1, $filters);
        $this->assertEquals('md2html', $filters[0]->getName());
    }

    public function testMarkdownToHtml()
    {
        $sut = new MarkdownExtension(new Markdown());
        $this->assertEquals('<p><em>test</em></p>', $sut->markdownToHtml('*test*'));
        $this->assertEquals('<h1>foobar</h1>', $sut->markdownToHtml('# foobar'));
    }
}
