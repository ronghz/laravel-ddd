<?php
namespace Ronghz\LaravelDdd\Framework\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Ronghz\LaravelDdd\Framework\Exceptions\DevelopException;
use Ronghz\LaravelDdd\Framework\Exceptions\DomainException;
use Ronghz\LaravelDdd\Framework\Traits\Models\Scope;

class DddModel extends Model
{
    public function __construct(array $attributes = [])
    {
        $this->connection = 'mysql';
        parent::__construct($attributes);
    }
}
