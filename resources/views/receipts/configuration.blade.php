<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Preventivo Auto #{{ $configuration->id }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .details-table, .optionals-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .details-table th, .details-table td, .optionals-table th, .optionals-table td { 
            border: 1px solid #ddd; padding: 10px; text-align: left; 
        }
        .total { text-align: right; font-size: 20px; font-weight: bold; margin-top: 20px; color: #b91c1c; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Riepilogo Configurazione</h1>
        <p>Data: {{ $configuration->created_at->format('d/m/Y') }} | Ordine N°: {{ $configuration->id }}</p>
    </div>

    <h3>Veicolo e Motore</h3>
    <table class="details-table">
        <tr>
            <th>Auto</th>
            <td>{{ $configuration->carModel->brand }} {{ $configuration->carModel->name }}</td>
            <td>€ {{ number_format($configuration->carModel->base_price, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Motorizzazione</th>
            <td>{{ $configuration->engine->name }}</td>
            <td>€ {{ number_format($configuration->engine->additional_price, 2, ',', '.') }}</td>
        </tr>
    </table>

    @if($configuration->optionals->isNotEmpty())
        <h3>Optional Selezionati</h3>
        <table class="optionals-table">
            @foreach($configuration->optionals as $optional)
                <tr>
                    <td>{{ $optional->name }}</td>
                    <td style="width: 150px;">€ {{ number_format($optional->price, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <div class="total">
        Totale Preventivo: € {{ number_format($configuration->total_price, 2, ',', '.') }}
    </div>

</body>
</html>