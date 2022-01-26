<?php

namespace Tests\Unit\TabletApi;

use App\Models\Employee;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesApplication;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * 1. Check that the channel is required for any route
     *
     * @return void
     */
    public function testChannelFieldIfRequiredInBodyOrHeader()
    {
        $url = '/api/test';
        $response = $this->get($url);
        $response2 = $this->withHeader('channel', 'web_crm')
            ->get($url);

        $response3 = $this->get($url . '?channel=tablet_crm');

        $response->assertForbidden()
            ->assertJsonStructure(['status', 'message']);

        $response2->assertOk()->assertJsonStructure(['status','data', 'message']);

        $response3->assertOk()->assertJsonStructure(['status','data', 'message']);
    }

    /**
     * 2. Check that the login validates requests properly
     *
     * @return void
     */
    public function testLoginRequiresEmailAndPassword()
    {
        try {
            $response = $this->withHeaders([
                'channel' => 'tablet_app'
            ])->post('/api/v2/employee/login');

            $response->assertStatus(400)
                ->assertSeeText('email field is required');
        } catch (\Exception $e){
            logger($e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * 3. Test that unregistered emails cannot login
     *
     * @test
     */
    public function testInvalidEmployeeCannotLogin()
    {
        $employee = factory(Employee::class)->make();
        $response = $this->withHeaders([
            'channel' => 'tablet_app'
        ])->post('/api/v2/employee/login', [
            'email' => $employee->email,
            'password' => 'password'
        ]);


        $response->assertStatus(400)
            ->assertSeeText('email is invalid');

    }

    /**
     * 4. Test that registered employees can login
     *
     * @test
     */
    public function testValidEmployeeCanLoginSuccessfully()
    {
        $employee = generateFakeEmployee([
            'email' => 'victoria@yahoo.com',
            'password' => bcrypt('password')
        ]);
        $response = $this->withHeaders([
            'channel' => 'tablet_app'
        ])->post('/api/v2/employee/login', [
            'email' => $employee->email,
            'password' => 'password'
        ]);
        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'message', 'data']);

    }

    /**
     * 5. Test that locations array is returned when employee's location_id is null
     *
     */
    public function testLoginReturnsLocationIfNotSet()
    {
        $employee = generateFakeEmployee([
            'email' => 'vikkie_e@yahoo.com',
            'password' => bcrypt('employee_password')
        ]);
        $response = $this->withHeaders([
            'channel' => 'tablet_app'
        ])->post('/api/v2/employee/login', [
            'email' => $employee->email,
            'password' => 'employee_password'
        ]);
        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'message', 'data' => [
                'employee',
                'access_token',
                'locations'
            ]]);
    }

    /**
     * 6. Test that request fails gracefully when token not set
     */
    public function testFailsGracefullyWhenTokenNotSet()
    {
        $response = $this->withHeader('channel', 'tablet_app')->post('/api/v2/employee/test');
        $response->assertJsonStructure(['status', 'message']);
    }
}
