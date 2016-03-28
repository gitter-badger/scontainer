<?php

namespace GabiDJ\Expressive\SContainer\Exception;
use Interop\Container\Exception\NotFoundException as NotFoundException;

class ServiceNotFoundException extends \Exception implements NotFoundException{}