<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cooperation;
use App\Models\SavingsTransaction;
use App\Models\SavingsType;
use App\Models\Roles;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Livewire;

class SavingsTransactionTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $cooperation;
    private $savingsType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cooperation = Cooperation::factory()->create();

        $this->savingsType = SavingsType::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'name' => 'Simpanan Pokok',
            'is_mandatory' => true,
        ]);

        $this->user = User::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'email_verified_at' => now(),
        ]);

        $adminRole = Roles::firstOrCreate([
            'cooperation_id' => $this->cooperation->id,
            'name' => 'admin',
        ]);
        UserRole::create([
            'user_id' => $this->user->id,
            'role_id' => $adminRole->id,
        ]);

        $this->actingAs($this->user);
    }

    /** @test */
    public function user_can_list_savings_transactions()
    {
        $transaction = SavingsTransaction::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $this->user->id,
            'savings_type_id' => $this->savingsType->id,
            'amount' => 150000,
            'status' => 'completed',
        ]);

        Livewire::test(\App\Filament\Resources\SavingResource\Pages\ListSavings::class)
            ->assertCanSeeTableRecords([$transaction]);
    }

    /** @test */
    public function user_can_create_savings_transaction()
    {
        $anotherUser = User::factory()->create([
            'cooperation_id' => $this->cooperation->id,
        ]);

        Livewire::test(\App\Filament\Resources\SavingResource\Pages\CreateSaving::class)
            ->fillForm([
                'user_id' => $anotherUser->id,
                'savings_type_id' => $this->savingsType->id,
                'amount' => 250000,
                'status' => 'completed',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('savings_transactions', [
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $anotherUser->id,
            'savings_type_id' => $this->savingsType->id,
            'amount' => 250000,
            'status' => 'completed',
        ]);
    }
}
