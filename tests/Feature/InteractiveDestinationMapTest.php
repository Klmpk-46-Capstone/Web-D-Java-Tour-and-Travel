<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\User;
use Database\Seeders\CmsTourSeeder;
use Database\Seeders\VehicleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InteractiveDestinationMapTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.djava.whatsapp' => '08123456789']);
        $this->seed(VehicleSeeder::class);
        $this->seed(CmsTourSeeder::class);

        $this->superAdmin = User::where('role', 'super_admin')->first() ?? User::factory()->create(['role' => 'super_admin']);
    }

    public function test_customer_landing_page_renders_interactive_destination_map_section(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('id="peta-destinasi"', false);
        $response->assertSee('peta-jawa-timur.png', false);
        $response->assertSee('Gunung Bromo');
        $response->assertSee('Air Terjun Tumpak Sewu');
        $response->assertSee('Kawah Ijen');
        $response->assertSee('map-destination-modal', false);
    }

    public function test_english_landing_page_renders_localized_destination_map(): void
    {
        $response = $this->get('/en');

        $response->assertStatus(200);
        $response->assertSee('id="peta-destinasi"', false);
        $response->assertSee('Mount Bromo');
        $response->assertSee('Ijen Crater');
        $response->assertSee('Tumpak Sewu Waterfall');
    }

    public function test_destination_map_renders_dynamic_package_data_from_database(): void
    {
        $bromoPackage = Package::where('name', 'like', '%Bromo%')->first();
        $this->assertNotNull($bromoPackage, 'Package with Bromo should exist in database');

        $response = $this->get('/');

        $response->assertStatus(200);
        // Verify package name and slug link appear in the rendered HTML
        $response->assertSee($bromoPackage->name);
        $response->assertSee(route('packages.show', $bromoPackage->slug), false);
    }

    public function test_friendly_empty_state_appears_when_destination_has_no_packages(): void
    {
        // Deactivate packages matching Tumpak Sewu
        Package::where('route_type', 'tumpak_sewu')->orWhere('name', 'like', '%Tumpak Sewu%')->update(['is_active' => false]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Paket Wisata Sedang Disiapkan');
        $response->assertSee('Konsultasi via WhatsApp');
    }

    public function test_admin_can_create_package_with_tumpak_sewu_route_type_and_it_links_to_map(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.packages.store'), [
            'name' => 'Pesona Alam Tirai Tumpak Sewu Lumajang',
            'category' => 'Mountain Trekking & Sunrise',
            'route_type' => 'tumpak_sewu',
            'duration' => '1 Hari',
            'price' => 450000,
            'badge' => 'EKSOTIS',
            'description' => 'Eksplorasi megahnya air terjun tirai Tumpak Sewu dan Goa Tetes Lumajang.',
            'is_featured' => 0,
            'is_active' => 1,
            'itineraries' => [
                ['title' => 'Trekking Lembah Tumpak Sewu & Goa Tetes', 'description' => 'Turun ke dasar tebing air terjun dan foto di spot panorama.'],
            ],
        ]);

        $response->assertRedirect(route('admin.packages.index'));

        $package = Package::where('route_type', 'tumpak_sewu')->first();
        $this->assertNotNull($package);
        $this->assertEquals('Air Terjun Tumpak Sewu (Lumajang / Pronojiwo)', $package->route_type_label);
        $this->assertTrue($package->matchesMapDestination('tumpak_sewu'));

        // Visit landing page and verify it appears in Tumpak Sewu panel
        $homeRes = $this->get('/');
        $homeRes->assertStatus(200);
        $homeRes->assertSee('Pesona Alam Tirai Tumpak Sewu Lumajang');
        $homeRes->assertSee(route('packages.show', $package->slug), false);
    }

    public function test_backward_compatibility_matches_old_gunung_packages_to_destinations(): void
    {
        $oldPackage = Package::create([
            'name' => 'Legacy Bromo Sunrise Trek',
            'category' => 'Mountain Trekking & Sunrise',
            'route_type' => 'gunung',
            'duration' => '1 Hari',
            'price' => 500000,
            'is_active' => true,
        ]);

        $this->assertTrue($oldPackage->matchesMapDestination('bromo'));
        $this->assertFalse($oldPackage->matchesMapDestination('tumpak_sewu'));
        $this->assertFalse($oldPackage->matchesMapDestination('ijen'));
    }
}
