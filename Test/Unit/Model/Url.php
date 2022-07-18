<?php
declare(strict_types=1);

namespace Gene\StoreSwitcher\Test\Unit\Model;

use Gene\StoreSwitcher\Model\Url as UrlModel;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

class Url extends TestCase
{
    /**
     * @var UrlModel|mixed|\PHPUnit\Framework\MockObject\MockObject
     */
    private mixed $url;

    /** @inheritdoc */
    protected function setUp(): void
    {
        $this->url = $this->getMockBuilder(UrlModel::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['build'])
            ->getMock();
        $url = $this->url;

    }

}
