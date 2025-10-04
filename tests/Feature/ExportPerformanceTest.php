<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ExportPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Run migrations + seeds (large dataset)
        $this->artisan('migrate', ['--force' => true]);
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        // Test user + token
        $user = User::factory()->create([
            'email' => 'perf@example.com',
            'password' => bcrypt('password'),
        ]);
        $token = $user->createToken('api')->plainTextToken;
        $this->withHeaders(['Authorization' => "Bearer {$token}"]);
    }

    public function test_export_performance()
    {
        $url = '/api/export?locale=en&tags[]=web&tags[]=mobile';

        // Cold run
        $t0 = hrtime(true);
        $res1 = $this->getJson($url);
        $t1 = hrtime(true);

        $res1->assertOk();
        $coldMs = ($t1 - $t0) / 1e6;

        // Warm run (should benefit from DB caches, OS cache, etc.)
        $t2 = hrtime(true);
        $res2 = $this->getJson($url);
        $t3 = hrtime(true);

        $res2->assertOk();
        $warmMs = ($t3 - $t2) / 1e6;

        // Budgets (adjust if needed for your machine)
        $this->assertLessThan(500, $coldMs, "Cold export took {$coldMs} ms");
        $this->assertLessThan(300, $warmMs, "Warm export took {$warmMs} ms");
    }
}
