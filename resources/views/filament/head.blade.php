<link rel="icon" type="image/png" href="{{ asset('img/logo-karya-tantri-abadi.png') }}">
<link rel="apple-touch-icon" href="{{ asset('img/logo-karya-tantri-abadi.png') }}">
<title>{{ config('app.name') }} - Sistem Manajemen Karya Tantri Abadi</title>

<style>
    /* Custom styles for inventory reports */
    .inventory-card {
        transition: transform 0.2s ease-in-out;
    }
    
    .inventory-card:hover {
        transform: translateY(-2px);
    }
    
    /* Custom badge colors */
    .stock-low {
        background-color: #fef3c7 !important;
        color: #92400e !important;
    }
    
    .stock-out {
        background-color: #fee2e2 !important;
        color: #991b1b !important;
    }
    
    .stock-normal {
        background-color: #d1fae5 !important;
        color: #065f46 !important;
    }
    
    /* Report summary cards */
    .report-summary-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 1.5rem;
        color: white;
        margin-bottom: 1rem;
    }
    
    .report-summary-card.revenue {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    
    .report-summary-card.profit {
        background: linear-gradient(135deg, #fceabb 0%, #f8b500 100%);
        color: #333;
    }
    
    .report-summary-card.loss {
        background: linear-gradient(135deg, #fc4a1a 0%, #f7b733 100%);
        color: #333;
    }
</style>
