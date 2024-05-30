<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use App\Trait\ChangeTimeZoneTrait;



class Kernel extends BaseKernel
{
    use MicroKernelTrait;
    use ChangeTimeZoneTrait;

    public function __construct( string $environment, bool $debug){ 
        $this->changeTimeZone($_ENV['TIME_ZONE']);
        parent::__construct($environment, $debug);
    }
}
 