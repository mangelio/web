<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Traits;

use App\Tests\Traits\Api\AssertApiCollectionFilterTrait;
use App\Tests\Traits\Api\AssertApiCollectionTrait;
use App\Tests\Traits\Api\AssertApiDeleteTrait;
use App\Tests\Traits\Api\AssertApiGetTrait;
use App\Tests\Traits\Api\AssertApiOperationsTrait;
use App\Tests\Traits\Api\AssertApiPatchTrait;
use App\Tests\Traits\Api\AssertApiPostTrait;
use App\Tests\Traits\Api\AssertApiResponseTrait;
use App\Tests\Traits\Api\AssertApiTokenTrait;

trait AssertApiTrait
{
    use AssertApiCollectionTrait;
    use AssertApiCollectionFilterTrait;
    use AssertApiOperationsTrait;
    use AssertApiResponseTrait;
    use AssertApiGetTrait;
    use AssertApiPostTrait;
    use AssertApiPatchTrait;
    use AssertApiDeleteTrait;
    use AssertApiTokenTrait;
}
