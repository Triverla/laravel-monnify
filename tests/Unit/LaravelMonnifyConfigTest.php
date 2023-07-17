<?php

namespace Triverla\LaravelMonnify\Unit;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Triverla\LaravelMonnify\Tests\TestCase;

class LaravelMonnifyConfigTest extends TestCase
{
    /** @test */
    function config_file_exists()
    {
        if (File::exists(config_path('monnify.php'))) {
            unlink(config_path('monnify.php'));
        }

        $this->assertFalse(File::exists(config_path('monnify.php')));

        Artisan::call('vendor:publish', [
            '--provider' => "Triverla\LaravelMonnify\MonnifyServiceProvider",
            '--tag' => "config"
        ]);

        $this->assertTrue(File::exists(config_path('monnify.php')));
    }
}
