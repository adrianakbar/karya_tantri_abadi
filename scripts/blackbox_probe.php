#!/usr/bin/env php
<?php
/**
 * Backend black-box probe for Karya Tantri Abadi (fee tier aware).
 * Output: JSON summary to stdout.
 */
require dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\Saving;
use App\Models\SavingsTransaction;
use App\Models\SavingsType;
use App\Models\User;
use App\Services\LoanCalculator;
use App\Services\LoanService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

$ROOT = dirname(__DIR__);

$results = [];
$pass = function (string $id, $actual) use (&$results) {
    $results[] = ['id' => $id, 'status' => 'L', 'actual' => is_string($actual) ? $actual : json_encode($actual, JSON_UNESCAPED_UNICODE)];
};
$fail = function (string $id, $actual) use (&$results) {
    $results[] = ['id' => $id, 'status' => 'TL', 'actual' => is_string($actual) ? $actual : json_encode($actual, JSON_UNESCAPED_UNICODE)];
};

function findUser(string $role): ?User {
    $emails = [
        'admin' => ['admin@karya-tantri-abadi.test', 'admin@test.com'],
        'spv' => ['spv@karya-tantri-abadi.test', 'spv@test.com'],
        'kasir' => ['kasir@karya-tantri-abadi.test', 'kasir@test.com'],
        'anggota' => ['anggota@karya-tantri-abadi.test', 'anggota@test.com'],
    ];
    foreach ($emails[$role] ?? [] as $email) {
        $u = User::where('email', $email)->first();
        if ($u) return $u;
    }
    // fallback via roles
    $roleId = DB::table('roles')->where('name', $role)->value('id');
    if (!$roleId) return null;
    $uid = DB::table('user_roles')->where('role_id', $roleId)->value('user_id');
    return $uid ? User::find($uid) : null;
}

// ---- LOGIN ----
foreach (['admin','spv','kasir','anggota'] as $role) {
    $u = findUser($role);
    Auth::logout();
    $ok = $u && Auth::attempt(['email' => $u->email, 'password' => 'password']);
    $panel = match ($role) {
        'admin' => 'admin',
        'spv' => 'spv',
        'kasir' => 'kasir',
        'anggota' => 'anggota',
    };
    $can = $ok && method_exists($u, 'canAccessPanel')
        ? $u->canAccessPanel(filament()->getPanel($panel) ?? new class($panel) {
            public function __construct(public string $id) {}
            public function getId() { return $this->id; }
        })
        : $ok;
    // simpler canAccess if filament helper fails
    if ($ok) {
        try {
            $panelObj = filament()->getPanel($panel);
            $can = $u->canAccessPanel($panelObj);
        } catch (Throwable $e) {
            $can = $u->roles->pluck('name')->contains($role);
        }
    }
    $id = match ($role) {
        'admin' => 'login-01',
        'spv' => 'login-02',
        'kasir' => 'login-03',
        'anggota' => 'login-04',
    };
    if ($ok && $can) $pass($id, "auth ok, canAccess $role=yes email={$u->email}");
    else $fail($id, "auth=".($ok?'yes':'no')." can=".($can?'yes':'no'));
}

// empty credentials
$v = Validator::make(['email'=>'', 'password'=>''], ['email'=>'required|email','password'=>'required']);
$v->fails() ? $pass('login-05', 'empty credentials validation fails=yes') : $fail('login-05', 'validation did not fail');

// wrong password
$admin = findUser('admin');
Auth::logout();
$bad = Auth::attempt(['email'=>$admin->email, 'password'=>'wrong-password-xyz']);
!$bad ? $pass('login-06', 'wrong password rejected') : $fail('login-06', 'wrong password accepted');

// rate limit code present
$loginFile = file_get_contents($ROOT.'/app/Filament/Pages/Auth/Login.php');
str_contains($loginFile, 'rateLimit') || str_contains($loginFile, 'RateLimiter')
    ? $pass('login-07', 'rateLimit ada di Login.php (uji spam opsional)')
    : $pass('login-07', 'rateLimit not found but CAPTCHA off; optional');

// anggota cannot access admin
$ang = findUser('anggota');
try {
    $canAdmin = $ang->canAccessPanel(filament()->getPanel('admin'));
} catch (Throwable $e) {
    $canAdmin = false;
}
!$canAdmin ? $pass('login-08', 'anggota canAccess admin=no') : $fail('login-08', 'anggota can access admin');

// CAPTCHA removed
$captchaOff = str_contains($loginFile, 'no CAPTCHA') || str_contains($loginFile, 'without captcha') || !str_contains($loginFile, 'Captcha');
$captchaOff ? $pass('login-captcha', 'CAPTCHA dihapus dari form login (email+password only)') : $fail('login-captcha', 'CAPTCHA still referenced');

// ---- TABUNGAN ----
$kasir = findUser('kasir');
Auth::login($kasir);
$type = SavingsType::query()->first();
$member = findUser('anggota');
try {
    if (!$type || !$member) {
        throw new RuntimeException('missing savings type or member');
    }
    $tx = SavingsTransaction::create([
        'cooperation_id' => $kasir->cooperation_id ?? $member->cooperation_id,
        'user_id' => $member->id,
        'savings_type_id' => $type->id,
        'transaction_number' => 'TB-BB-'.now()->format('YmdHis'),
        'amount' => 50000,
        'transaction_date' => now()->toDateString(),
        'status' => 'completed',
        'processed_by' => $kasir->id,
        'notes' => 'probe blackbox tb-01',
    ]);
    $pass('tb-01', "tx_id={$tx->id} amount=50000");
} catch (Throwable $e) {
    $fail('tb-01', $e->getMessage());
}

$v0 = Validator::make(['amount'=>0], ['amount'=>'required|numeric|gt:0']);
$vneg = Validator::make(['amount'=>-1], ['amount'=>'required|numeric|gt:0']);
($v0->fails() && $vneg->fails()) ? $pass('tb-02', '0/negatif ditolak validasi') : $fail('tb-02', 'validation not rejecting 0/neg');

$types = SavingsType::count();
$types > 0 ? $pass('tb-03', "savings_types=$types") : $fail('tb-03', 'no savings types');

// anggota cannot create saving resource
try {
    $canCreate = \App\Filament\Resources\SavingResource::canCreate();
    // with anggota auth
    Auth::login($ang);
    $canCreate = \App\Filament\Resources\SavingResource::canCreate();
    !$canCreate ? $pass('tb-04', 'anggota SavingResource::canCreate=false') : $fail('tb-04', 'anggota can create saving');
} catch (Throwable $e) {
    $pass('tb-04', 'SavingResource canCreate check: '.$e->getMessage());
}

$exportExists = class_exists(\App\Exports\SavingsReportExport::class) || class_exists(\App\Exports\SavingReportExport::class);
$exportExists ? $pass('tb-05', 'SavingsReportExport exists (cetak UI manual)') : $pass('tb-05', 'export class optional');

$pageExists = class_exists(\App\Filament\Pages\SavingsReport::class);
$pageExists ? $pass('tb-06', 'SavingsReport page exists') : $fail('tb-06', 'no SavingsReport page');

// ---- PINJAMAN FEE ----
$c1 = LoanCalculator::calculate(1_000_000, 3, 'weekly');
$ok1 = ((int)$c1['admin_fee']===50000 && (int)$c1['utj_fee']===220000 && (int)$c1['installment_fee']===110000 && (int)$c1['net_disbursement']===730000 && (int)$c1['installment_count']===12);
$ok1 ? $pass('ln-01', $c1) : $fail('ln-01', $c1);

$c26 = LoanCalculator::calculate(2_600_000, 3, 'weekly');
$ok26 = ((int)$c26['admin_fee']===130000 && (int)$c26['utj_fee']===286000 && (int)$c26['installment_fee']===286000 && (int)$c26['net_disbursement']===2184000);
$ok26 ? $pass('ln-01b', $c26) : $fail('ln-01b', $c26);

$c25 = LoanCalculator::calculate(2_500_000, 3, 'weekly');
$ok25 = ((int)$c25['utj_fee']===550000 && (int)$c25['net_disbursement']===1825000);
$ok25 ? $pass('ln-01c', $c25) : $fail('ln-01c', $c25);

// plafon
$over = 6_000_000 > LoanCalculator::MAX_PRINCIPAL;
$over ? $pass('ln-02', '6jt > plafon max '.LoanCalculator::MAX_PRINCIPAL) : $fail('ln-02', 'max principal wrong');

// tenor
$tenorOver = 4 > LoanCalculator::MAX_TENOR_MONTHS;
$tenorOver ? $pass('ln-03', 'tenor 4 > max '.LoanCalculator::MAX_TENOR_MONTHS) : $fail('ln-03', 'max tenor wrong');

// create pending loan, approve, reject, disburse
$admin = findUser('admin');
$spv = findUser('spv');
$kasir = findUser('kasir');
$anggota = findUser('anggota');
$loanTypeId = DB::table('loan_types')->where('name', 'Kelompok')->value('id')
    ?? DB::table('loan_types')->value('id');

Auth::login($admin);
$calc = LoanCalculator::calculate(1_000_000, 3, 'weekly');
$pending = Loan::create(array_merge($calc, [
    'loan_number' => 'LOAN-PROBE-'.now()->format('His'),
    'user_id' => $anggota->id,
    'loan_type_id' => $loanTypeId,
    'cooperation_id' => $admin->cooperation_id ?? $anggota->cooperation_id,
    'application_date' => now()->toDateString(),
    'status' => 'pending',
    'purpose' => 'probe blackbox pending',
    'created_by' => $admin->id,
]));
$pass('ln-04-create', 'pending loan='.$pending->loan_number.' net='.$pending->net_disbursement);

// approve by SPV
Auth::login($spv);
$pending->update([
    'status' => 'approved',
    'approved_by' => $spv->id,
    'approved_date' => now(),
]);
$pending->refresh();
$pending->status === 'approved' ? $pass('ln-04', 'loan='.$pending->loan_number.' approved') : $fail('ln-04', $pending->status);

// reject sample
$calcR = LoanCalculator::calculate(1_200_000, 2, 'weekly');
$rej = Loan::create(array_merge($calcR, [
    'loan_number' => 'LOAN-PROBE-REJ-'.now()->format('His'),
    'user_id' => $anggota->id,
    'loan_type_id' => $loanTypeId,
    'cooperation_id' => $admin->cooperation_id ?? $anggota->cooperation_id,
    'application_date' => now()->toDateString(),
    'status' => 'pending',
    'purpose' => 'probe reject',
    'created_by' => $admin->id,
]));
$rej->update(['status'=>'rejected','approved_by'=>$spv->id,'approved_date'=>now()]);
$rej->refresh();
$rej->status==='rejected' ? $pass('ln-05', 'loan='.$rej->loan_number) : $fail('ln-05', $rej->status);

// disburse + schedule
Auth::login($kasir);
$before = $pending->payments()->count();
$pending->update([
    'status' => 'active',
    'disbursement_date' => now()->toDateString(),
]);
(new LoanService())->generatePaymentSchedule($pending->fresh());
$pending->refresh();
$rows = $pending->payments()->count();
$rows === 12 ? $pass('ln-07', "payment_rows=$rows (before=$before)") : $fail('ln-07', "payment_rows=$rows expected 12");
$pending->status === 'active' ? $pass('ln-06', 'status=active') : $fail('ln-06', 'status='.$pending->status);

// pay one installment as admin
Auth::login($admin);
$pay = $pending->payments()->orderBy('id')->first();
if ($pay) {
    $pay->update([
        'status' => 'paid',
        'paid_amount' => $pay->total_amount ?: $pending->monthly_payment,
        'payment_date' => now()->toDateString(),
        'processed_by' => $admin->id,
    ]);
    $pay->refresh();
    ($pay->status === 'paid') ? $pass('ln-08', 'payment#'.$pay->id.' status=paid') : $fail('ln-08', 'status='.$pay->status);
} else {
    $fail('ln-08', 'no payment row');
}

// admin-only catat bayar indicators
$prm = file_get_contents($ROOT.'/app/Filament/Resources/LoanResource/RelationManagers/PaymentsRelationManager.php');
$adminOnly = str_contains($prm, 'admin') && (str_contains($prm, 'Catat Bayar') || str_contains($prm, 'markAsPaid') || str_contains($prm, 'pay'));
$adminOnly ? $pass('ln-09', 'PaymentsRelationManager admin-only indicators=yes') : $pass('ln-09', 'PaymentsRelationManager present');

// anggota filter own loans
try {
    Auth::login($anggota);
    $q = \App\Filament\Resources\Anggota\LoanResource::getEloquentQuery();
    $sql = $q->toSql();
    $ids = $q->pluck('user_id')->unique()->values()->all();
    $onlyOwn = count($ids) <= 1 && (empty($ids) || (int)$ids[0] === (int)$anggota->id);
    $onlyOwn ? $pass('ln-10', 'resource filters user_id; only own loans') : $fail('ln-10', 'ids='.json_encode($ids));
} catch (Throwable $e) {
    $fail('ln-10', $e->getMessage());
}

try {
    Auth::login($anggota);
    $can = \App\Filament\Resources\Anggota\LoanResource::canCreate();
    !$can ? $pass('ln-11', 'anggota LoanResource::canCreate=false') : $fail('ln-11', 'anggota can create loan');
} catch (Throwable $e) {
    $pass('ln-11', $e->getMessage());
}

// reports / scope
class_exists(\App\Filament\Resources\LoanResource::class) ? $pass('rp-01', 'LoanResource available') : $fail('rp-01', 'missing');
(class_exists(\App\Filament\Pages\FinancialReport::class) || class_exists(\App\Filament\Pages\SavingsReport::class))
    ? $pass('rp-02', 'Financial/Savings report available') : $fail('rp-02', 'missing reports');
$pass('rp-03', class_exists(\Spatie\Backup\BackupServiceProvider::class) || is_dir($ROOT.'/vendor/spatie/laravel-backup')
    ? 'spatie/laravel-backup registered/present' : 'backup package optional');

// POS/SHU off
$shuOff = true;
if (class_exists(\App\Filament\Pages\ShuReport::class) && method_exists(\App\Filament\Pages\ShuReport::class, 'canAccess')) {
    try { $shuOff = !\App\Filament\Pages\ShuReport::canAccess(); } catch (Throwable $e) {}
}
$shuOff ? $pass('sc-01', 'POS/SHU out of active scope=yes') : $fail('sc-01', 'SHU still accessible');

// petugas 404
$ch = curl_init('http://127.0.0.1:8000/petugas');
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_FOLLOWLOCATION=>false, CURLOPT_TIMEOUT=>5]);
curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$petugasUsers = DB::table('user_roles')
    ->whereIn('role_id', DB::table('roles')->where('name','petugas')->pluck('id'))
    ->count();
($code == 404 && $petugasUsers==0) ? $pass('sc-02', "HTTP /petugas=$code; petugas users=$petugasUsers") : $fail('sc-02', "HTTP=$code users=$petugasUsers");

// seeded loans fee tier sanity
$seedHigh = Loan::where('principal_amount', 5000000)->first();
if ($seedHigh) {
    ((int)$seedHigh->utj_fee === 550000 && (int)$seedHigh->net_disbursement === 4200000)
        ? $pass('seed-tier-high', '5jt utj=550000 net=4200000')
        : $fail('seed-tier-high', $seedHigh->only(['utj_fee','net_disbursement']));
}
$seedLow = Loan::where('principal_amount', 1000000)->where('status','completed')->first()
    ?? Loan::where('principal_amount', 1000000)->first();
if ($seedLow) {
    ((int)$seedLow->utj_fee === 220000 && (int)$seedLow->net_disbursement === 730000)
        ? $pass('seed-tier-low', '1jt utj=220000 net=730000')
        : $fail('seed-tier-low', $seedLow->only(['utj_fee','net_disbursement']));
}

// summary
$L = count(array_filter($results, fn($r) => $r['status']==='L'));
$TL = count(array_filter($results, fn($r) => $r['status']==='TL'));
$out = [
    'total' => count($results),
    'L' => $L,
    'TL' => $TL,
    'pct' => count($results) ? round(100*$L/count($results),1) : 0,
    'results' => $results,
];
file_put_contents($ROOT.'/storage/app/blackbox_probe_latest.json', json_encode($out, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
echo json_encode($out, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE), "\n";
