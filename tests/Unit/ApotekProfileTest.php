<?php

namespace Tests\Unit;

use App\Models\ApotekProfile;
use Illuminate\Http\Request;
use Tests\TestCase;

class ApotekProfileTest extends TestCase
{
    public function test_logo_url_uses_the_current_request_host(): void
    {
        config(['filesystems.disks.public.url' => 'http://wrong-host.test/storage']);
        app('url')->setRequest(Request::create('http://127.0.0.1:8123/settings/profile-apotek'));

        $profile = new ApotekProfile([
            'logo_path' => 'apotek-logos/logo-apotek.png',
        ]);

        $this->assertSame(
            'http://127.0.0.1:8123/storage/apotek-logos/logo-apotek.png',
            $profile->logo_url
        );
    }
}
