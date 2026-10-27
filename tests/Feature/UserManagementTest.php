<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserManagementTest extends MedikaCareTestCase
{
    private function payload(array $override = []): array
    {
        return array_merge([
            'name' => 'Staf Baru',
            'email' => 'staf.baru@example.com',
            'phone' => '0812-0000-1111',
            'role' => 'staff',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ], $override);
    }

    public function test_admin_can_create_a_user_with_a_hashed_password(): void
    {
        $this->actingAs($this->admin())
            ->post('/users', $this->payload())
            ->assertRedirect(route('users.index'));

        $user = User::where('email', 'staf.baru@example.com')->first();

        $this->assertSame('staff', $user->role);
        $this->assertTrue(Hash::check('rahasia123', $user->password));
        $this->assertNotSame('rahasia123', $user->password);
    }

    public function test_email_must_be_unique_and_password_must_match(): void
    {
        $existing = $this->staff();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/users', $this->payload(['email' => $existing->email]))
            ->assertSessionHasErrors('email');

        $this->actingAs($admin)
            ->post('/users', $this->payload([
                'password_confirmation' => 'beda-total',
            ]))
            ->assertSessionHasErrors('password');
    }

    public function test_admin_cannot_change_own_role_or_delete_own_account(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put("/users/{$admin->id}", [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'staff',
        ])->assertSessionHasErrors('role');

        $this->assertSame('admin', $admin->fresh()->role);

        $this->actingAs($admin)
            ->delete("/users/{$admin->id}")
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_user_with_transaction_history_cannot_be_deleted(): void
    {
        $staff = $this->staff();

        Sale::create([
            'user_id' => $staff->id,
            'sale_number' => 'SL-TEST-0001',
            'total' => 0,
            'status' => 'completed',
            'payment_method' => 'tunai',
            'sold_at' => now(),
        ]);

        $this->actingAs($this->admin())
            ->delete("/users/{$staff->id}")
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $staff->id]);
    }

    public function test_user_without_history_can_be_deleted(): void
    {
        $customer = $this->customer();

        $this->actingAs($this->admin())
            ->delete("/users/{$customer->id}")
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseMissing('users', ['id' => $customer->id]);
    }
}