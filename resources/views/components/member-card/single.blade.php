<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Anggota - {{ $user->name }}</title>
    <style>
        @page {
            margin: 10mm;
            size: A4 portrait;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 10pt;
            color: #333;
            background-color: #ffffff;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        .page-container {
            width: 190mm;
            height: 267mm;
            margin: 0 auto;
            position: relative;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #37c01b;
            padding-bottom: 10px;
        }

        .organization-name {
            font-size: 16pt;
            font-weight: bold;
            color: #37c01b;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .card-title {
            font-size: 14pt;
            font-weight: normal;
            color: #555;
            margin-top: 0;
        }

        .card-container {
            display: flex;
            align-items: flex-start;
            margin: 20px auto;
            width: 170mm;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 1px 5px rgba(0,0,0,0.1);
        }

        .photo-section {
            width: 60mm;
            padding: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .photo {
            width: 45mm;
            height: 55mm;
            border: 1px solid #ddd;
            object-fit: cover;
            margin-bottom: 10px;
            background-color: #fff;
        }

        .member-id {
            font-weight: bold;
            font-size: 10pt;
            color: #37c01b;
            text-align: center;
            margin-bottom: 10px;
        }

        .qr-code {
            width: 40mm;
            height: 40mm;
            border: 1px solid #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8pt;
            color: #999;
        }

        .info-section {
            flex: 1;
            padding: 15px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table tr {
            border-bottom: 1px solid #eee;
        }

        .info-table tr:last-child {
            border-bottom: none;
        }

        .info-table td {
            padding: 8px 5px;
            vertical-align: top;
        }

        .info-table td.label {
            font-weight: bold;
            width: 40mm;
            color: #555;
        }

        .signature-section {
            margin-top: 25px;
            display: flex;
            justify-content: space-between;
        }

        .signature {
            text-align: center;
            width: 45%;
        }

        .signature-line {
            border-top: 1px solid #333;
            width: 70%;
            margin: 30px auto 5px;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 8pt;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 10px;
            position: absolute;
            bottom: 10mm;
            left: 0;
            right: 0;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="header">
            <div class="organization-name">Karya Tantri Abadi</div>
            <div class="card-title">KARTU TANDA ANGGOTA</div>
        </div>

        <div class="card-container">
            <div class="photo-section">
                @if($user->profile_photo)
                    <img src="{{ public_path('storage/' . $user->profile_photo) }}" alt="Foto Profil" class="photo">
                @else
                    <div class="photo" style="display: flex; align-items: center; justify-content: center;">
                        [Foto Anggota]
                    </div>
                @endif
                
                <div class="member-id">
                    No. Anggota: {{ $user->member_number ?? 'N/A' }}
                </div>
                
            </div>

            <div class="info-section">
                <table class="info-table">
                    <tr>
                        <td class="label">Nama Lengkap</td>
                        <td>: {{ $user->name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Alamat</td>
                        <td>: {{ $user->address ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">No. Telepon</td>
                        <td>: {{ $user->phone }}</td>
                    </tr>
                    <tr>
                        <td class="label">Pekerjaan</td>
                        <td>: {{ $user->job }}</td>
                    </tr>
                    <tr>
                        <td class="label">Tanggal Bergabung</td>
                        <td>: {{ $user->join_date->format('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Status Keanggotaan</td>
                        <td>: Aktif</td>
                    </tr>
                </table>

                {{-- <div class="signature-section">
                    <div class="signature">
                        <div class="signature-line"></div>
                        <div>Anggota</div>
                    </div>
                    <div class="signature">
                        <div class="signature-line"></div>
                        <div>Ketua</div>
                    </div>
                </div> --}}
            </div>
        </div>

        <div class="footer">
            Kartu ini berlaku selama menjadi anggota aktif Karya Tantri Abadi.<br>
            Diterbitkan pada: {{ now()->format('d F Y') }} | © {{ now()->format('Y') }} Karya Tantri Abadi
        </div>
    </div>
</body>
</html>