<?php

namespace Tests\Feature;

class AuthTest extends MedikaCareTestCase
{
    public function test_guest_cannot_open_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = $this->staff();

        $this->post('/login', ['email' => $user->email, 'password' => 'salah-sekali'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_correct_password_logs_in(): void
    {
        $user = $this->staff();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_always_creates_a_customer(): void
    {
        $this->post('/register', [
            'name' => 'Pembeli Baru',
            'email' => 'pembeli.baru@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'pembeli.baru@example.com',
            'role' => 'customer',
        ]);
    }

    public function test_dashboard_opens_for_admin_and_staff_and_customer_goes_to_catalog(): void
    {
        $this->actingAs($this->admin())->get('/dashboard')->assertOk();
        $this->actingAs($this->staff())->get('/dashboard')->assertOk();
        $this->actingAs($this->customer())->get('/dashboard')->assertRedirect(route('shop.index'));
    }
}