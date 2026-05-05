<?php

declare(strict_types=1);

namespace Tests\Unit\Resources;

use HiEvents\Resources\BaseResource;
use Tests\TestCase;

class BaseResourceStaticStateTest extends TestCase
{
    protected function tearDown(): void
    {
        BaseResource::resetAdditionalData();
        parent::tearDown();
    }

    public function test_collection_with_additional_data_stores_data(): void
    {
        BaseResource::collectionWithAdditionalData([], ['totals' => 100]);

        $resource = new class(null) extends BaseResource {};

        $this->assertSame(100, $resource->getAdditionalDataByKey('totals'));
    }

    public function test_reset_clears_additional_data(): void
    {
        BaseResource::collectionWithAdditionalData([], ['totals' => 100]);
        BaseResource::resetAdditionalData();

        $resource = new class(null) extends BaseResource {};

        $this->assertNull($resource->getAdditionalDataByKey('totals'));
    }
}
