<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cooperation;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\SavingsTransaction;
use App\Models\SavingsType;
use App\Models\ShuDistribution;
use App\Models\ShuMemberShare;
use App\Models\Roles;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Livewire;

class ShuDistributionTest extends TestCase
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
            'name' => 'Simpanan Sukarela',
            'is_mandatory' => false,
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
    public function user_can_list_shu_distributions()
    {
        $distribution = ShuDistribution::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'year' => 2025,
            'total_shu' => 6000000.00,
        ]);

        $this->assertDatabaseHas('shu_distributions', [
            'id' => $distribution->id,
            'year' => 2025,
            'total_shu' => 6000000.00,
        ]);
    }

    /** @test */
    public function calculate_auto_shu_creates_distribution_and_member_shares()
    {
        $lastYear = now()->year - 1;

        $member1 = User::factory()->create(['cooperation_id' => $this->cooperation->id]);
        $member2 = User::factory()->create(['cooperation_id' => $this->cooperation->id]);

        // Create savings transactions for members
        SavingsTransaction::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $member1->id,
            'savings_type_id' => $this->savingsType->id,
            'amount' => 1000000.00,
            'transaction_date' => now()->subYear(),
            'status' => 'completed',
        ]);

        SavingsTransaction::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'user_id' => $member2->id,
            'savings_type_id' => $this->savingsType->id,
            'amount' => 2000000.00,
            'transaction_date' => now()->subYear(),
            'status' => 'completed',
        ]);

        // Create sales (revenue)
        Sale::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'customer_id' => $member1->id,
            'sale_date' => now()->subYear(),
            'total_amount' => 5000000.00,
            'status' => 'completed',
        ]);

        // Create purchases (expense)
        Purchase::factory()->create([
            'cooperation_id' => $this->cooperation->id,
            'purchase_date' => now()->subYear(),
            'total_amount' => 2000000.00,
            'status' => 'received',
        ]);

        Livewire::test(\App\Filament\Pages\ShuReport::class)
            ->call('calculateAutoShu')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('shu_distributions', [
            'cooperation_id' => $this->cooperation->id,
            'year' => $lastYear,
            'total_revenue' => 5000000.00,
            'total_expenses' => 2000000.00,
            'total_shu' => 3000000.00,
        ]);

        $distribution = ShuDistribution::where('cooperation_id', $this->cooperation->id)
            ->where('year', $lastYear)
            ->first();

        // Total SHU = 3,000,000. 50% for savings (1,500,000) and 50% for transactions (1,500,000).
        // Total savings = 3,000,000. Member 1 savings share = 1M/3M = 1/3 * 1.5M = 500,000.
        // Total transactions = 5,000,000 (all from Member 1). Member 1 transaction share = 5M/5M = 1.0 * 1.5M = 1,500,000.
        // Total SHU for Member 1 = 500,000 + 1,500,000 = 2,000,000.
        $this->assertDatabaseHas('shu_member_shares', [
            'shu_distribution_id' => $distribution->id,
            'user_id' => $member1->id,
            'savings_contribution' => 500000.00,
            'transaction_contribution' => 1500000.00,
            'shu_amount' => 2000000.00,
        ]);

        // Member 2 savings share = 2M/3M = 2/3 * 1.5M = 1,000,000.
        // Member 2 transaction share = 0.
        // Total SHU for Member 2 = 1,000,000.
        $this->assertDatabaseHas('shu_member_shares', [
            'shu_distribution_id' => $distribution->id,
            'user_id' => $member2->id,
            'savings_contribution' => 1000000.00,
            'transaction_contribution' => 0.00,
            'shu_amount' => 1000000.00,
        ]);
    }
}
