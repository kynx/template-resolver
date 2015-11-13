<?php
/**
 * @copyright 2015 Matt Kynaston
 * @license MIT
 */

namespace KynxTest\Template\Resolver;

use Kynx\Template\Resolver\Result;
use PHPUnit_Framework_TestCase as TestCase;

final class ResultTest extends TestCase
{
    public function testConstruct()
    {
        $result = new Result('foo::bar', 'my template string', true, true);
        $this->assertEquals('foo::bar', $result->getKey());
        $this->assertEquals('my template string', (string) $result);
        $this->assertEquals('my template string', $result->getContents());
        $this->assertEquals(true, $result->isCompiled());
        $this->assertEquals(true, $result->isCached());
    }
}
