<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class TenantBusinessRelationTest extends TestCase
{
    public function test_user_business_relation_uses_the_business_model_connection(): void
    {
        $user = new User();
        $relation = $user->business();
        $relatedModel = $relation->getRelated();

        $this->assertSame('mysql', $relatedModel->getConnectionName());
    }
}
