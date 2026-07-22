<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cooperation;
use App\Models\User;
use App\Models\Roles;
use App\Models\UserRole;
use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\ExpenseCategory;
use App\Models\Expense;
use App\Models\SavingsType;
use App\Models\SavingsTransaction;
use App\Models\LoanType;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\SystemSetting;
use App\Models\Supplier;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CompleteSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeder komprehensif untuk semua modul sistem koperasi
     */
    public function run(): void
    {
        $this->command->info('🚀 Memulai seeding data sistem Karya Tantri Abadi...');
        
        // 1. Cooperation
        $this->command->info('📊 Seeding Cooperation...');
        $cooperation = $this->seedCooperation();
        
        // 2. Roles & Users
        $this->command->info('👥 Seeding Roles & Users...');
        $users = $this->seedRolesAndUsers($cooperation);
        
        // 3. System Settings
        $this->command->info('⚙️ Seeding System Settings...');
        $this->seedSystemSettings($cooperation);
        
        // 4. Product Categories & Products
        $this->command->info('📦 Seeding Products...');
        $products = $this->seedProducts($cooperation);
        
        // 4.5 Suppliers
        $this->command->info('🏪 Seeding Suppliers...');
        $suppliers = $this->seedSuppliers($cooperation);
        
        // 5. Purchases
        $this->command->info('🛒 Seeding Purchases...');
        $this->seedPurchases($cooperation, $products, $suppliers, $users['bendahara']);
        
        // 6. Sales
        $this->command->info('💰 Seeding Sales...');
        $this->seedSales($cooperation, $products, $users['bendahara']);
        
        // 7. Expense Categories & Expenses
        $this->command->info('💸 Seeding Expenses...');
        $this->seedExpenses($cooperation, $users['bendahara']);
        
        // 8. Savings Types & Transactions
        $this->command->info('🏦 Seeding Savings...');
        $this->seedSavings($cooperation, $users['anggota']);
        
        // 9. Loan Types & Loans
        $this->command->info('💳 Seeding Loans...');
        $this->seedLoans($cooperation, $users['anggota']);
        
        $this->command->info('✅ Seeding selesai!');
        $this->command->newLine();
        $this->printLoginCredentials($users);
    }
    
    protected function seedCooperation(): Cooperation
    {
        return Cooperation::firstOrCreate(
            ['code' => 'KOP-001'],
            [
                'name' => 'Karya Tantri Abadi',
                'address' => 'Jl. Pendidikan No. 123, Jakarta',
                'phone' => '021-12345678',
                'email' => 'info@karya-tantri-abadi.test',
                'is_active' => true,
            ]
        );
    }
    
    protected function seedRolesAndUsers(Cooperation $cooperation): array
    {
        // Create roles if not exists
        $adminRole = Roles::firstOrCreate(
            ['name' => 'admin', 'cooperation_id' => $cooperation->id], 
            ['description' => 'Administrator']
        );
        $anggotaRole = Roles::firstOrCreate(
            ['name' => 'anggota', 'cooperation_id' => $cooperation->id], 
            ['description' => 'Petugas']
        );
        $bendaharaRole = Roles::firstOrCreate(
            ['name' => 'bendahara', 'cooperation_id' => $cooperation->id], 
            ['description' => 'Kasir']
        );
        $kepalaYayasanRole = Roles::firstOrCreate(
            ['name' => 'kepalayayasan', 'cooperation_id' => $cooperation->id], 
            ['description' => 'Supervisor']
        );
        
        // Create users
        $admin = User::firstOrCreate(
            ['email' => 'admin@karya-tantri-abadi.test'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'cooperation_id' => $cooperation->id,
                'phone' => '081234567890',
                'address' => 'Jakarta',
            ]
        );
        UserRole::firstOrCreate(['user_id' => $admin->id], ['role_id' => $adminRole->id]);
        
        $bendahara = User::firstOrCreate(
            ['email' => 'kasir@karya-tantri-abadi.test'],
            [
                'name' => 'Kasir',
                'password' => Hash::make('password'),
                'cooperation_id' => $cooperation->id,
                'phone' => '081234567891',
                'address' => 'Jakarta',
            ]
        );
        UserRole::firstOrCreate(['user_id' => $bendahara->id], ['role_id' => $bendaharaRole->id]);
        
        $kepalaYayasan = User::firstOrCreate(
            ['email' => 'spv@karya-tantri-abadi.test'],
            [
                'name' => 'Supervisor',
                'password' => Hash::make('password'),
                'cooperation_id' => $cooperation->id,
                'phone' => '081234567892',
                'address' => 'Jakarta',
            ]
        );
        UserRole::firstOrCreate(['user_id' => $kepalaYayasan->id], ['role_id' => $kepalaYayasanRole->id]);
        
        // Create multiple anggota
        $anggotaUsers = [];
        for ($i = 1; $i <= 5; $i++) {
            $anggota = User::firstOrCreate(
                ['email' => "anggota{$i}@koperasi-sekolah1.test"],
                [
                    'name' => "Anggota {$i}",
                    'password' => Hash::make('password'),
                    'cooperation_id' => $cooperation->id,
                    'phone' => '08123456789' . $i,
                    'address' => "Jakarta Area {$i}",
                ]
            );
            UserRole::firstOrCreate(['user_id' => $anggota->id], ['role_id' => $anggotaRole->id]);
            $anggotaUsers[] = $anggota;
        }
        
        return [
            'admin' => $admin,
            'kasir' => $bendahara,
            'kepalayayasan' => $kepalaYayasan,
            'petugas' => $anggotaUsers,
        ];
    }
    
    protected function seedSystemSettings(Cooperation $cooperation): void
    {
        $settings = [
            ['key' => 'cooperative_name', 'value' => 'Karya Tantri Abadi', 'description' => 'Nama Organisasi'],
            ['key' => 'cooperative_address', 'value' => 'Jl. Pendidikan No. 123', 'description' => 'Alamat Organisasi'],
            ['key' => 'cooperative_phone', 'value' => '021-12345678', 'description' => 'Telepon Organisasi'],
            ['key' => 'min_savings_balance', 'value' => '100000', 'description' => 'Minimum Saldo Simpanan'],
            ['key' => 'max_loan_amount', 'value' => '10000000', 'description' => 'Maximum Jumlah Pinjaman'],
            ['key' => 'loan_interest_rate', 'value' => '12', 'description' => 'Bunga Pinjaman (%)'],
            ['key' => 'late_payment_penalty', 'value' => '50000', 'description' => 'Denda Keterlambatan'],
        ];
        
        foreach ($settings as $setting) {
            SystemSetting::firstOrCreate(
                ['key' => $setting['key'], 'cooperation_id' => $cooperation->id],
                [
                    'value' => $setting['value'],
                    'description' => $setting['description'],
                ]
            );
        }
    }
    
    protected function seedSuppliers(Cooperation $cooperation): array
    {
        $suppliers = [
            ['name' => 'PT. Sumber Makmur', 'address' => 'Jl. Industri No. 45, Jakarta', 'phone' => '021-5551234'],
            ['name' => 'CV. Maju Jaya', 'address' => 'Jl. Perdagangan No. 12, Bandung', 'phone' => '022-7771234'],
            ['name' => 'Toko Serba Ada', 'address' => 'Jl. Pasar No. 78, Surabaya', 'phone' => '031-8881234'],
        ];
        
        $suppliersData = [];
        foreach ($suppliers as $supplier) {
            $suppliersData[] = Supplier::firstOrCreate(
                ['name' => $supplier['name'], 'cooperation_id' => $cooperation->id],
                [
                    'address' => $supplier['address'],
                    'phone' => $supplier['phone'],
                    'email' => strtolower(str_replace([' ', '.'], '', $supplier['name'])) . '@supplier.com',
                    'contact_person' => 'Manager',
                    'is_active' => true,
                ]
            );
        }
        
        return $suppliersData;
    }
    
    protected function seedProducts(Cooperation $cooperation): array
    {
        // Product Categories
        $categories = [
            ['name' => 'Makanan & Minuman', 'description' => 'Produk makanan dan minuman'],
            ['name' => 'Alat Tulis Kantor', 'description' => 'Perlengkapan kantor dan sekolah'],
            ['name' => 'Elektronik', 'description' => 'Peralatan elektronik'],
            ['name' => 'Perlengkapan Rumah Tangga', 'description' => 'Keperluan rumah tangga'],
        ];
        
        $productCategories = [];
        foreach ($categories as $cat) {
            $productCategories[] = ProductCategory::firstOrCreate(
                ['name' => $cat['name'], 'cooperation_id' => $cooperation->id],
                ['description' => $cat['description']]
            );
        }
        
        // Products
        $productData = [
            // Makanan & Minuman
            ['category' => 0, 'code' => 'MKN001', 'name' => 'Indomie Goreng', 'unit' => 'pcs', 'purchase_price' => 2500, 'selling_price' => 3000, 'stock' => 100, 'min_stock' => 20],
            ['category' => 0, 'code' => 'MKN002', 'name' => 'Air Mineral 600ml', 'unit' => 'btl', 'purchase_price' => 2000, 'selling_price' => 3000, 'stock' => 150, 'min_stock' => 30],
            ['category' => 0, 'code' => 'MKN003', 'name' => 'Teh Botol', 'unit' => 'btl', 'purchase_price' => 4000, 'selling_price' => 5000, 'stock' => 80, 'min_stock' => 20],
            ['category' => 0, 'code' => 'MKN004', 'name' => 'Roti Tawar', 'unit' => 'pak', 'purchase_price' => 12000, 'selling_price' => 15000, 'stock' => 50, 'min_stock' => 10],
            
            // Alat Tulis
            ['category' => 1, 'code' => 'ATK001', 'name' => 'Pulpen Standard', 'unit' => 'pcs', 'purchase_price' => 2000, 'selling_price' => 3000, 'stock' => 200, 'min_stock' => 50],
            ['category' => 1, 'code' => 'ATK002', 'name' => 'Buku Tulis 38 Lembar', 'unit' => 'pcs', 'purchase_price' => 3000, 'selling_price' => 4000, 'stock' => 150, 'min_stock' => 30],
            ['category' => 1, 'code' => 'ATK003', 'name' => 'Penggaris 30cm', 'unit' => 'pcs', 'purchase_price' => 2500, 'selling_price' => 3500, 'stock' => 100, 'min_stock' => 20],
            ['category' => 1, 'code' => 'ATK004', 'name' => 'Penghapus', 'unit' => 'pcs', 'purchase_price' => 1000, 'selling_price' => 1500, 'stock' => 200, 'min_stock' => 40],
            
            // Elektronik
            ['category' => 2, 'code' => 'ELK001', 'name' => 'Kabel USB Type-C', 'unit' => 'pcs', 'purchase_price' => 15000, 'selling_price' => 25000, 'stock' => 50, 'min_stock' => 10],
            ['category' => 2, 'code' => 'ELK002', 'name' => 'Power Bank 10000mAh', 'unit' => 'pcs', 'purchase_price' => 75000, 'selling_price' => 100000, 'stock' => 30, 'min_stock' => 5],
            
            // Perlengkapan RT
            ['category' => 3, 'code' => 'PRT001', 'name' => 'Sabun Cuci Piring', 'unit' => 'btl', 'purchase_price' => 8000, 'selling_price' => 10000, 'stock' => 60, 'min_stock' => 15],
            ['category' => 3, 'code' => 'PRT002', 'name' => 'Tissue Gulung', 'unit' => 'roll', 'purchase_price' => 4000, 'selling_price' => 5500, 'stock' => 100, 'min_stock' => 20],
        ];
        
        $products = [];
        foreach ($productData as $data) {
            $products[] = Product::firstOrCreate(
                ['code' => $data['code'], 'cooperation_id' => $cooperation->id],
                [
                    'product_category_id' => $productCategories[$data['category']]->id,
                    'name' => $data['name'],
                    'unit' => $data['unit'],
                    'purchase_price' => $data['purchase_price'],
                    'selling_price' => $data['selling_price'],
                    'current_stock' => $data['stock'],
                    'min_stock' => $data['min_stock'],
                    'is_active' => true,
                ]
            );
        }
        
        return $products;
    }
    
    protected function seedPurchases(Cooperation $cooperation, array $products, array $suppliers, User $bendahara): void
    {
        $startDate = Carbon::now()->subMonths(3);
        
        for ($i = 1; $i <= 10; $i++) {
            $purchaseDate = $startDate->copy()->addDays($i * 7);
            $supplier = $suppliers[$i % count($suppliers)];
            
            $purchase = Purchase::create([
                'cooperation_id' => $cooperation->id,
                'supplier_id' => $supplier->id,
                'purchase_number' => 'PO-' . $purchaseDate->format('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'invoice_number' => 'INV-' . $purchaseDate->format('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'purchase_date' => $purchaseDate,
                'total_amount' => 0,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'grand_total' => 0,
                'processed_by' => $bendahara->id,
                'status' => 'received',
                'notes' => 'Pembelian rutin periode ' . $purchaseDate->format('F Y'),
            ]);
            
            // Add purchase details
            $subtotal = 0;
            $productsToAdd = array_rand($products, min(5, count($products)));
            if (!is_array($productsToAdd)) $productsToAdd = [$productsToAdd];
            
            foreach ($productsToAdd as $productIndex) {
                $product = $products[$productIndex];
                $quantity = rand(10, 50);
                $total = $product->purchase_price * $quantity;
                
                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->purchase_price,
                    'total_price' => $total,
                ]);
                
                $subtotal += $total;
            }
            
            $discount = $i % 5 == 0 ? $subtotal * 0.05 : 0;
            $afterDiscount = $subtotal - $discount;
            $tax = $afterDiscount * 0.11;
            
            $purchase->update([
                'total_amount' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'grand_total' => $afterDiscount + $tax,
            ]);
        }
    }
    
    protected function seedSales(Cooperation $cooperation, array $products, User $bendahara): void
    {
        $startDate = Carbon::now()->subMonths(2);
        
        for ($i = 1; $i <= 30; $i++) {
            $saleDate = $startDate->copy()->addDays($i * 2);
            $sale = Sale::create([
                'cooperation_id' => $cooperation->id,
                'customer_id' => null, // Optional customer (walk-in)
                'sale_number' => 'SO-' . $saleDate->format('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'sale_date' => $saleDate,
                'subtotal' => 0,
                'discount_amount' => $i % 5 == 0 ? 5000 : 0,
                'tax_amount' => 0,
                'total_amount' => 0,
                'payment_method' => $i % 3 == 0 ? 'credit' : 'cash',
                'processed_by' => $bendahara->id,
                'status' => 'completed',
                'notes' => 'Penjualan ' . $saleDate->format('d F Y'),
            ]);
            
            // Add sale details
            $subtotal = 0;
            $productsToSell = array_rand($products, min(rand(2, 5), count($products)));
            if (!is_array($productsToSell)) $productsToSell = [$productsToSell];
            
            foreach ($productsToSell as $productIndex) {
                $product = $products[$productIndex];
                $quantity = rand(1, 10);
                $total = $product->selling_price * $quantity;
                
                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->selling_price,
                    'total_price' => $total,
                ]);
                
                $subtotal += $total;
            }
            
            $sale->update([
                'subtotal' => $subtotal,
                'total_amount' => $subtotal - $sale->discount_amount,
            ]);
        }
    }
    
    protected function seedExpenses(Cooperation $cooperation, User $bendahara): void
    {
        // Expense Categories
        $expenseCategories = [
            ['name' => 'Operasional', 'description' => 'Biaya operasional harian'],
            ['name' => 'Gaji & Upah', 'description' => 'Pembayaran gaji karyawan'],
            ['name' => 'Utilitas', 'description' => 'Listrik, air, internet'],
            ['name' => 'Perawatan', 'description' => 'Biaya perawatan dan perbaikan'],
            ['name' => 'Lain-lain', 'description' => 'Pengeluaran lainnya'],
        ];
        
        $categories = [];
        foreach ($expenseCategories as $cat) {
            $categories[] = ExpenseCategory::firstOrCreate(
                ['name' => $cat['name'], 'cooperation_id' => $cooperation->id],
                ['description' => $cat['description']]
            );
        }
        
        // Create expenses
        $startDate = Carbon::now()->subMonths(3);
        $expenseData = [
            ['category' => 0, 'name' => 'Biaya ATK', 'amount' => 500000],
            ['category' => 0, 'name' => 'Biaya Transport', 'amount' => 300000],
            ['category' => 1, 'name' => 'Gaji Kasir', 'amount' => 3000000],
            ['category' => 1, 'name' => 'Gaji Admin', 'amount' => 2500000],
            ['category' => 2, 'name' => 'Listrik', 'amount' => 800000],
            ['category' => 2, 'name' => 'Air', 'amount' => 200000],
            ['category' => 2, 'name' => 'Internet', 'amount' => 500000],
            ['category' => 3, 'name' => 'Perbaikan AC', 'amount' => 1500000],
            ['category' => 4, 'name' => 'Biaya Administrasi Bank', 'amount' => 50000],
        ];
        
        for ($month = 0; $month < 3; $month++) {
            foreach ($expenseData as $data) {
                $expenseDate = $startDate->copy()->addMonths($month)->addDays(rand(1, 28));
                Expense::create([
                    'cooperation_id' => $cooperation->id,
                    'expense_category_id' => $categories[$data['category']]->id,
                    'expense_date' => $expenseDate,
                    'amount' => $data['amount'] + rand(-50000, 50000), // variasi amount
                    'receipt_number' => 'RCP-' . $expenseDate->format('Ymd') . '-' . rand(1000, 9999),
                    'recipient' => $data['name'],
                    'processed_by' => $bendahara->id,
                    'approved_by' => $bendahara->id,
                    'status' => 'approved',
                    'notes' => 'Pembayaran ' . $data['name'] . ' periode ' . $expenseDate->format('F Y'),
                ]);
            }
        }
    }
    
    protected function seedSavings(Cooperation $cooperation, array $anggotaUsers): void
    {
        // Savings Types
        $savingsTypes = [
            ['name' => 'Simpanan Pokok', 'code' => 'SP', 'amount' => 500000, 'is_mandatory' => true, 'description' => 'Simpanan pokok anggota saat bergabung'],
            ['name' => 'Simpanan Wajib', 'code' => 'SW', 'amount' => 100000, 'is_mandatory' => true, 'description' => 'Simpanan wajib bulanan'],
            ['name' => 'Simpanan Sukarela', 'code' => 'SS', 'amount' => null, 'is_mandatory' => false, 'description' => 'Simpanan sukarela sesuai kemampuan'],
        ];
        
        $types = [];
        foreach ($savingsTypes as $type) {
            $types[] = SavingsType::firstOrCreate(
                ['code' => $type['code'], 'cooperation_id' => $cooperation->id],
                [
                    'name' => $type['name'],
                    'amount' => $type['amount'],
                    'is_mandatory' => $type['is_mandatory'],
                    'description' => $type['description'],
                    'is_active' => true,
                ]
            );
        }
        
        // Create savings transactions
        $startDate = Carbon::now()->subMonths(6);
        $bendahara = User::whereHas('roles', fn($q) => $q->where('name', 'bendahara'))->first();
        
        foreach ($anggotaUsers as $anggota) {
            // Simpanan Pokok (one time)
            $transDate = $startDate->copy()->addDays(rand(1, 10));
            SavingsTransaction::create([
                'cooperation_id' => $cooperation->id,
                'user_id' => $anggota->id,
                'savings_type_id' => $types[0]->id,
                'transaction_number' => 'SVT-' . $transDate->format('Ymd') . '-' . str_pad($anggota->id, 4, '0', STR_PAD_LEFT),
                'transaction_date' => $transDate,
                'amount' => 500000,
                'receipt_number' => 'RCP-SP-' . $transDate->format('Ymd') . '-' . $anggota->id,
                'processed_by' => $bendahara->id ?? null,
                'status' => 'completed',
                'notes' => 'Simpanan Pokok - Pembayaran saat bergabung',
            ]);
            
            // Simpanan Wajib (monthly)
            for ($month = 0; $month < 6; $month++) {
                $transDate = $startDate->copy()->addMonths($month)->addDays(rand(1, 5));
                SavingsTransaction::create([
                    'cooperation_id' => $cooperation->id,
                    'user_id' => $anggota->id,
                    'savings_type_id' => $types[1]->id,
                    'transaction_number' => 'SVT-' . $transDate->format('Ymd') . '-' . str_pad($anggota->id * 100 + $month, 4, '0', STR_PAD_LEFT),
                    'transaction_date' => $transDate,
                    'amount' => 100000,
                    'receipt_number' => 'RCP-SW-' . $transDate->format('Ymd') . '-' . $anggota->id,
                    'processed_by' => $bendahara->id ?? null,
                    'status' => 'completed',
                    'notes' => 'Simpanan Wajib bulan ' . $transDate->format('F Y'),
                ]);
            }
            
            // Simpanan Sukarela (random)
            for ($i = 0; $i < rand(3, 6); $i++) {
                $amount = rand(5, 20) * 10000;
                $transDate = $startDate->copy()->addDays(rand(1, 180));
                
                SavingsTransaction::create([
                    'cooperation_id' => $cooperation->id,
                    'user_id' => $anggota->id,
                    'savings_type_id' => $types[2]->id,
                    'transaction_number' => 'SVT-' . $transDate->format('Ymd') . '-' . str_pad($anggota->id * 1000 + $i, 4, '0', STR_PAD_LEFT),
                    'transaction_date' => $transDate,
                    'amount' => $amount,
                    'receipt_number' => 'RCP-SS-' . $transDate->format('Ymd') . '-' . $anggota->id . '-' . $i,
                    'processed_by' => $bendahara->id ?? null,
                    'status' => 'completed',
                    'notes' => 'Simpanan Sukarela',
                ]);
            }
        }
    }
    
    private function seedLoans(Cooperation $cooperation, array $anggotaUsers)
    {
        // Create Loan Types
        $loanTypesData = [
            ['name' => 'Pinjaman Konsumtif', 'interest_rate' => 12.0, 'max_tenor_months' => 24, 'max_amount' => 20000000, 'description' => 'Pinjaman untuk kebutuhan konsumtif'],
            ['name' => 'Pinjaman Produktif', 'interest_rate' => 10.0, 'max_tenor_months' => 36, 'max_amount' => 50000000, 'description' => 'Pinjaman untuk usaha produktif'],
            ['name' => 'Pinjaman Pendidikan', 'interest_rate' => 8.0, 'max_tenor_months' => 12, 'max_amount' => 10000000, 'description' => 'Pinjaman untuk biaya pendidikan'],
        ];
        
        $types = [];
        foreach ($loanTypesData as $loanType) {
            $types[] = LoanType::firstOrCreate(
                ['name' => $loanType['name'], 'cooperation_id' => $cooperation->id],
                [
                    'interest_rate' => $loanType['interest_rate'],
                    'max_tenor_months' => $loanType['max_tenor_months'],
                    'max_amount' => $loanType['max_amount'],
                    'description' => $loanType['description'],
                    'is_active' => true,
                ]
            );
        }
        
        $ketuaYayasan = User::whereHas('roles', fn($q) => $q->where('name', 'kepalayayasan'))->first();
        $bendahara = User::whereHas('roles', fn($q) => $q->where('name', 'bendahara'))->first();
        
        if (empty($anggotaUsers)) {
            return;
        }
        
        $startDate = Carbon::now()->subMonths(12);
        
        // Create some active loans with payments (take first 3 anggota)
        foreach (array_slice($anggotaUsers, 0, 3) as $index => $anggota) {
            $loanType = $types[array_rand($types)];
            $principal = rand(3, 10) * 1000000;
            $interestRate = $loanType->interest_rate;
            $tenor = rand(6, min(12, $loanType->max_tenor_months));
            
            // Calculate monthly payment and total
            $monthlyInterest = $principal * ($interestRate / 100) / 12;
            $monthlyPayment = ($principal / $tenor) + $monthlyInterest;
            $totalPayment = $monthlyPayment * $tenor;
            
            $applicationDate = $startDate->copy()->addDays(rand(1, 30));
            $approvedDate = $applicationDate->copy()->addDays(rand(3, 7));
            $disbursementDate = $approvedDate->copy()->addDays(rand(1, 3));
            $dueDate = $disbursementDate->copy()->addMonths($tenor);
            
            // Calculate paid installments (some months already paid)
            $paidInstallments = min(rand(2, 6), $tenor - 1);
            $remainingBalance = $totalPayment - ($monthlyPayment * $paidInstallments);
            
            $loan = Loan::create([
                'cooperation_id' => $cooperation->id,
                'user_id' => $anggota->id,
                'loan_type_id' => $loanType->id,
                'loan_number' => 'LOAN-' . $applicationDate->format('Ymd') . '-' . str_pad($anggota->id, 4, '0', STR_PAD_LEFT),
                'principal_amount' => $principal,
                'interest_rate' => $interestRate,
                'tenor_months' => $tenor,
                'monthly_payment' => $monthlyPayment,
                'total_payment' => $totalPayment,
                'remaining_balance' => $remainingBalance,
                'application_date' => $applicationDate,
                'approved_date' => $approvedDate,
                'disbursement_date' => $disbursementDate,
                'due_date' => $dueDate,
                'approved_by' => $ketuaYayasan->id ?? null,
                'purpose' => ['Modal usaha', 'Renovasi rumah', 'Pendidikan anak'][rand(0, 2)],
                'status' => 'active',
                'notes' => 'Pinjaman disetujui dan telah dicairkan',
            ]);
            
            // Create loan payments (installments)
            for ($i = 1; $i <= $tenor; $i++) {
                $dueDate = $disbursementDate->copy()->addMonths($i);
                $isPaid = $i <= $paidInstallments;
                
                LoanPayment::create([
                    'cooperation_id' => $cooperation->id,
                    'loan_id' => $loan->id,
                    'payment_number' => 'PAY-' . $loan->loan_number . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'installment_number' => $i,
                    'due_date' => $dueDate,
                    'payment_date' => $isPaid ? $dueDate->copy()->addDays(rand(0, 3)) : null,
                    'principal_amount' => $principal / $tenor,
                    'interest_amount' => $monthlyInterest,
                    'total_amount' => $monthlyPayment,
                    'paid_amount' => $isPaid ? $monthlyPayment : 0,
                    'penalty_amount' => 0,
                    'processed_by' => $isPaid ? ($bendahara->id ?? null) : null,
                    'status' => $isPaid ? 'paid' : 'pending',
                    'notes' => $isPaid ? 'Pembayaran angsuran ke-' . $i : 'Belum dibayar',
                ]);
            }
        }
    }
    
    protected function printLoginCredentials(array $users): void
    {
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('📝 LOGIN CREDENTIALS');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->newLine();
        
        $this->command->info('👤 Admin:');
        $this->command->line('   Email: admin@karya-tantri-abadi.test');
        $this->command->line('   Panel: /admin');
        $this->command->newLine();
        
        $this->command->info('💰 Kasir:');
        $this->command->line('   Email: kasir@karya-tantri-abadi.test');
        $this->command->line('   Panel: /bendahara');
        $this->command->newLine();
        
        $this->command->info('📊 Supervisor:');
        $this->command->line('   Email: spv@karya-tantri-abadi.test');
        $this->command->line('   Panel: /kepalayayasan');
        $this->command->newLine();
        
        $this->command->info('👥 Anggota (5 users):');
        for ($i = 1; $i <= 5; $i++) {
            $this->command->line("   Email: anggota{$i}@koperasi-sekolah1.test");
        }
        $this->command->line('   Panel: /anggota');
        $this->command->newLine();
        
        $this->command->warn('🔑 Password untuk semua user: password');
        $this->command->newLine();
        $this->command->info('═══════════════════════════════════════════════════════');
    }
}
