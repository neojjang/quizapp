<?php

namespace Tests;

use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    // RefreshDatabase 사용 시 역할(admin/user)을 시드한다. 회원가입이 assignRole('user')를 호출함
    protected $seed = true;

    protected $seeder = RolesPermissionsSeeder::class;
}
