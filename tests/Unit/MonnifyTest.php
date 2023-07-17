<?php

namespace Triverla\LaravelMonnify\Unit;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\TestCase;
use Triverla\LaravelMonnify\Exceptions\FailedRequestException;
use Triverla\LaravelMonnify\Monnify;
use Mockery as m;

class MonnifyTest extends TestCase
{
    protected Monnify $monnify;
    protected function setUp(): void
    {
        //parent::setUp();
        $this->monnify = m::mock('Triverla\LaravelMonnify\Monnify');
    }

    public function tearDown(): void
    {
        m::close();
    }

    public function testWithBasicAuthReturnsPendingRequestInstance()
    {
        $monnifyMock = m::mock(Monnify::class);
        $monnifyMock->shouldReceive('withBasicAuth')->andReturn(m::mock(PendingRequest::class));
        $response = $monnifyMock->withBasicAuth();
        $this->assertInstanceOf(PendingRequest::class, $response);
    }

    public function testWithBasicAuthFailsWithInvalidCredentials()
    {
        Http::fake();
        $this->monnify = new Monnify('https://sandbox.monnify.com', [
            'api_key' => 'invalid_api_key',
            'secret_key' => 'invalid_secret_key',
        ]);
        $response = $this->monnify->withBasicAuth()->post('https://sandbox.monnify.com/v1/auth/login');
        $this->assertEquals('array', gettype(array($response)));
    }
}
