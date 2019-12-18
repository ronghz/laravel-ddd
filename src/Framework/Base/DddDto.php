<?php
namespace Ronghz\LaravelDdd\Framework\Base;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use SchulzeFelix\DataTransferObject\DataTransferObject;

class DddDto extends DataTransferObject
{
    /** @var array 字段映射关系，新字段名在前，旧字段名在后 */
    protected $attributeMapping = [];




    public function initFromModel(Model $object)
    {
        $object->getAttributes();
    }
}
