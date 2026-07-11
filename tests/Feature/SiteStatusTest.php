<?php

namespace Tests\Feature;

use App\Models\MainSiteData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure default settings exist
        MainSiteData::setValue('site_status', 'active');
        MainSiteData::setValue('site_maintenance', '0');
        MainSiteData::setValue('site_message', 'Custom announcement message');
        MainSiteData::setValue('site_name', 'Test ISP');
    }

    public function test_site_is_accessible_when_active_and_no_maintenance()
    {
        $response = $this->get('http://localhost/');
        $response->assertStatus(200);
    }

    public function test_site_and_portal_redirect_or_show_maintenance_when_maintenance_is_on()
    {
        MainSiteData::setValue('site_maintenance', '1');

        // Main site
        $response = $this->get('http://localhost/');
        $response->assertStatus(503);
        $response->assertSee('Under Maintenance');
        $response->assertSee('Custom announcement message');

        // Portal subdomain
        $response = $this->get('http://portal.localhost/');
        $response->assertStatus(503);
        $response->assertSee('Under Maintenance');

        // Billing subdomain should not be blocked (redirects to login/dashboard or 200/302)
        $response = $this->get('http://billing.localhost/dashboard');
        $this->assertNotEquals(503, $response->getStatusCode());
    }

    public function test_site_and_portal_show_disabled_when_site_is_disabled()
    {
        MainSiteData::setValue('site_status', 'disabled');

        // Main site
        $response = $this->get('http://localhost/');
        $response->assertStatus(503);
        $response->assertSee('Site Temporarily Offline');

        // Portal subdomain
        $response = $this->get('http://portal.localhost/');
        $response->assertStatus(503);
        $response->assertSee('Site Temporarily Offline');

        // Billing subdomain should not be blocked
        $response = $this->get('http://billing.localhost/dashboard');
        $this->assertNotEquals(503, $response->getStatusCode());
    }
}
