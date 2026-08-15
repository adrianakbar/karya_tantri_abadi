import { chromium } from 'playwright';
import fs from 'fs';
import path from 'path';

const BASE = process.env.KTA_BASE_URL || 'http://127.0.0.1:8000';
const OUT = process.env.KTA_OUT_DIR || path.resolve('bukti-blackbox');
const PASS = 'password';

const accounts = {
  admin: 'admin@karya-tantri_abadi.test'.replace('_', '-'),
  spv: 'spv@karya-tantri-abadi.test',
  kasir: 'kasir@karya-tantri-abadi.test',
  anggota: 'anggota@karya-tantri-abadi.test',
};
// fix admin email correctly
accounts.admin = 'admin@karya-tantri-abadi.test';

fs.mkdirSync(OUT, { recursive: true });

async function forceLight(page) {
  await page.addInitScript(() => {
    try {
      localStorage.setItem('theme', 'light');
      localStorage.setItem('appearance', 'light');
      localStorage.setItem('filament_theme', 'light');
      localStorage.setItem('color-scheme', 'light');
    } catch {}
  });
  await page.emulateMedia({ colorScheme: 'light' });
  await page.evaluate(() => {
    document.documentElement.classList.remove('dark');
    document.documentElement.style.colorScheme = 'light';
    document.body?.classList?.remove('dark');
    // Filament often toggles .dark on html
    const html = document.documentElement;
    html.setAttribute('data-theme', 'light');
    html.setAttribute('data-mode', 'light');
  }).catch(() => {});
}

async function dismissTour(page) {
  const candidates = [
    'button:has-text("Lewati")',
    'button:has-text("Skip")',
    'button:has-text("Tutup")',
    'button:has-text("Close")',
    '[data-tour-skip]',
    '.driver-popover-close-btn',
    'button[aria-label="Close"]',
  ];
  for (const sel of candidates) {
    const el = page.locator(sel).first();
    if (await el.count() && await el.isVisible().catch(() => false)) {
      await el.click({ timeout: 1500 }).catch(() => {});
      await page.waitForTimeout(300);
    }
  }
  // hide leftover overlays
  await page.evaluate(() => {
    for (const sel of ['.driver-overlay', '.driver-popover', '[data-tour-overlay]', '.fi-modal-close-overlay']) {
      document.querySelectorAll(sel).forEach((n) => n.remove());
    }
  }).catch(() => {});
}

async function shot(page, name, fullPage = true) {
  await forceLight(page);
  await dismissTour(page);
  await page.waitForTimeout(400);
  const file = path.join(OUT, name);
  await page.screenshot({ path: file, fullPage });
  console.log('OK', name);
  return file;
}

async function login(page, email) {
  await page.goto(`${BASE}/auth/login`, { waitUntil: 'networkidle' });
  await forceLight(page);
  // Filament/login form fields
  const emailSel = 'input[type="email"], input[name="email"], input[id*="email"]';
  const passSel = 'input[type="password"], input[name="password"], input[id*="password"]';
  await page.locator(emailSel).first().fill(email);
  await page.locator(passSel).first().fill(PASS);
  const submit = page.locator('button[type="submit"], button:has-text("Masuk"), button:has-text("Login")').first();
  await submit.click();
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(800);
  await forceLight(page);
  await dismissTour(page);
}

async function logout(page) {
  // try user menu logout; fallback clear cookies
  try {
    const avatar = page.locator('button[aria-label*="User"], .fi-user-menu button, .fi-avatar, button:has(.fi-avatar)').first();
    if (await avatar.count()) {
      await avatar.click({ timeout: 2000 });
      const out = page.locator('text=Log out, text=Keluar, text=Sign out, a:has-text("Log out"), button:has-text("Log out")').first();
      if (await out.count()) {
        await out.click({ timeout: 2000 });
        await page.waitForLoadState('networkidle');
        return;
      }
    }
  } catch {}
  await page.context().clearCookies();
  await page.goto(`${BASE}/auth/login`, { waitUntil: 'domcontentloaded' });
}

async function main() {
  const browser = await chromium.launch({
    headless: true,
    executablePath: process.env.PLAYWRIGHT_CHROME || '/home/adrianakbar/.cache/ms-playwright/chromium-1228/chrome-linux64/chrome',
    args: ['--no-sandbox', '--disable-dev-shm-usage', '--force-color-profile=srgb'],
  });
  const context = await browser.newContext({
    viewport: { width: 1440, height: 900 },
    colorScheme: 'light',
    deviceScaleFactor: 1,
    locale: 'id-ID',
  });
  const page = await context.newPage();
  await forceLight(page);

  // 01 login
  await page.goto(`${BASE}/auth/login`, { waitUntil: 'networkidle' });
  await forceLight(page);
  await shot(page, '01-login-page.png');
  await shot(page, '01b-login-no-captcha-browser.png');

  // 18 wrong password
  await page.locator('input[type="email"], input[name="email"]').first().fill(accounts.admin);
  await page.locator('input[type="password"]').first().fill('salah-password');
  await page.locator('button[type="submit"], button:has-text("Masuk")').first().click();
  await page.waitForTimeout(1000);
  await forceLight(page);
  await shot(page, '18-login-salah.png', false);

  // 17 petugas 404
  await page.goto(`${BASE}/petugas`, { waitUntil: 'domcontentloaded' });
  await forceLight(page);
  await shot(page, '17-petugas-404.png', false);

  // ADMIN
  await login(page, accounts.admin);
  await page.goto(`${BASE}/admin`, { waitUntil: 'networkidle' });
  await forceLight(page);
  await dismissTour(page);
  await shot(page, '02-admin-dashboard.png');
  await shot(page, '02-admin-after-login.png');

  await page.goto(`${BASE}/admin/loans`, { waitUntil: 'networkidle' });
  await forceLight(page);
  await dismissTour(page);
  await shot(page, '03-admin-loans.png');
  await shot(page, '21-daftar-pinjaman-fee-tier.png');

  // open first loan detail if any
  const loanLink = page.locator('table tbody tr a, table tbody tr').first();
  if (await loanLink.count()) {
    await loanLink.click({ timeout: 3000 }).catch(() => {});
    await page.waitForLoadState('networkidle').catch(() => {});
    await forceLight(page);
    await dismissTour(page);
    await shot(page, '04-admin-loan-detail.png');
  }

  // create loan fee tier 1jt
  await page.goto(`${BASE}/admin/loans/create`, { waitUntil: 'networkidle' });
  await forceLight(page);
  await dismissTour(page);
  // fill principal if field exists
  const amountCandidates = [
    'input[wire\\:model*="principal"]',
    'input[id*="principal"]',
    'input[name*="principal"]',
    'input[id*="amount"]',
    'label:has-text("Nominal") >> xpath=..//input',
    'label:has-text("Plafon") >> xpath=..//input',
  ];
  let filled = false;
  for (const sel of amountCandidates) {
    const el = page.locator(sel).first();
    if (await el.count()) {
      await el.fill('1000000').catch(() => {});
      filled = true;
      break;
    }
  }
  // trigger livewire blur
  await page.keyboard.press('Tab').catch(() => {});
  await page.waitForTimeout(1200);
  await forceLight(page);
  await shot(page, '19-fee-tier-1jt-cair-730rb.png');

  // 2.6jt
  for (const sel of amountCandidates) {
    const el = page.locator(sel).first();
    if (await el.count()) {
      await el.fill('2600000').catch(() => {});
      break;
    }
  }
  await page.keyboard.press('Tab').catch(() => {});
  await page.waitForTimeout(1200);
  await forceLight(page);
  await shot(page, '20-fee-tier-26jt-cair-2184jt.png');

  // tabungan admin
  for (const url of [`${BASE}/admin/savings`, `${BASE}/admin/savings-transactions`, `${BASE}/admin/tabungan`]) {
    const resp = await page.goto(url, { waitUntil: 'domcontentloaded' });
    if (resp && resp.status() < 400 && !page.url().includes('login')) {
      await forceLight(page);
      await dismissTour(page);
      await shot(page, '05-admin-tabungan.png');
      break;
    }
  }

  // reports
  for (const [url, name] of [
    [`${BASE}/admin/savings-report`, '06-admin-laporan-tabungan.png'],
    [`${BASE}/admin/loan-report`, '07-admin-laporan-pinjaman.png'],
    [`${BASE}/admin/financial-report`, '08-admin-laporan-keuangan.png'],
  ]) {
    await page.goto(url, { waitUntil: 'networkidle' }).catch(() => {});
    await forceLight(page);
    await dismissTour(page);
    await shot(page, name);
  }

  await logout(page);

  // SPV
  await login(page, accounts.spv);
  await page.goto(`${BASE}/spv`, { waitUntil: 'networkidle' });
  await forceLight(page);
  await dismissTour(page);
  await shot(page, '09-spv-dashboard.png');
  await page.goto(`${BASE}/spv/loans`, { waitUntil: 'networkidle' }).catch(() => page.goto(`${BASE}/spv`, { waitUntil: 'networkidle' }));
  await forceLight(page);
  await dismissTour(page);
  await shot(page, '10-spv-loans.png');
  await logout(page);

  // KASIR
  await login(page, accounts.kasir);
  await page.goto(`${BASE}/kasir`, { waitUntil: 'networkidle' });
  await forceLight(page);
  await dismissTour(page);
  await shot(page, '11-kasir-dashboard.png');
  for (const url of [`${BASE}/kasir/savings`, `${BASE}/kasir/savings-transactions`]) {
    const resp = await page.goto(url, { waitUntil: 'domcontentloaded' }).catch(() => null);
    if (resp && resp.status() < 400) {
      await forceLight(page);
      await dismissTour(page);
      await shot(page, '12-kasir-tabungan.png');
      // try create form
      const create = page.locator('a:has-text("Baru"), a:has-text("New"), a:has-text("Tambah"), button:has-text("Baru")').first();
      if (await create.count()) {
        await create.click().catch(() => {});
        await page.waitForLoadState('networkidle').catch(() => {});
        await forceLight(page);
        await shot(page, '14-kasir-tabungan-form.png');
      }
      break;
    }
  }
  await page.goto(`${BASE}/kasir/loans`, { waitUntil: 'networkidle' }).catch(() => {});
  await forceLight(page);
  await dismissTour(page);
  await shot(page, '13-kasir-loans.png');
  await logout(page);

  // ANGGOTA
  await login(page, accounts.anggota);
  await page.goto(`${BASE}/anggota`, { waitUntil: 'networkidle' });
  await forceLight(page);
  await dismissTour(page);
  await shot(page, '15-anggota-dashboard.png');
  await page.goto(`${BASE}/anggota/loans`, { waitUntil: 'networkidle' }).catch(() => {});
  await forceLight(page);
  await dismissTour(page);
  await shot(page, '16-anggota-pinjaman.png');

  await browser.close();
  console.log('DONE', OUT);
}

main().catch((e) => {
  console.error(e);
  process.exit(1);
});
