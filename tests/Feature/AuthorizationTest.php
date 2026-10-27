<?php

namespace Tests\Feature;

class AuthorizationTest extends MedikaCareTestCase
{
    public function test_staff_cannot_use_admin_features(): void
    {
        $staff = $this->staff();

        foreach (['/categories', '/suppliers', '/users', '/products/create', '/laporan/penjualan'] as $url) {
            $this->actingAs($staff)->get($url)->assertForbidden();
        }

        $this->actingAs($staff)->post('/products', [])->assertForbidden();
    }

    public function test_customer_cannot_open_staff_or_admin_pages(): void
    {
        $customer = $this->customer();

        foreach (['/products', '/purchases', '/sales', '/categories', '/users'] as $url) {
            $this->actingAs($customer)->get($url)->assertForbidden();
        }
    }

    public function test_admin_and_staff_can_open_operational_pages(): void
    {
        foreach (['/products', '/purchases', '/sales', '/purchases/create', '/sales/create'] as $url) {
            $this->actingAs($this->staff())->get($url)->assertOk();
            $this->actingAs($this->admin())->get($url)->assertOk();
        }

        foreach (['/categories', '/suppliers', '/users', '/laporan/penjualan', '/laporan/penjualan/cetak'] as $url) {
            $this->actingAs($this->admin())->get($url)->assertOk();
        }
    }

    public function test_catalog_is_public_but_cart_needs_login(): void
    {
        $this->get('/katalog')->assertOk();
        $this->get('/keranjang')->assertRedirect(route('login'));
    }
}