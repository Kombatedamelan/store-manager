<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-size: 12px;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: white;
        }

        .content {
            width: 100%;
            max-width: 800px;
            margin: 50px auto;
        }

        .header {
            text-align: center;
            margin-bottom: 50px;
        }

        h1 {
            font-size: 18px;
            margin: 0;
            font-weight: bold;
            color: #2c3e50;
        }

        .header p {
            margin: 5px 0;
            font-size: 12px;
            color: #34495e;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th, .table td {
            padding: 8px;
            text-align: center;
            font-size: 12px;
        }

        .table th {
            background-color: #2c3e50;
            color: white;
        }

        .table td {
            border-bottom: 1px solid #ccc;
        }

        .total {
            margin-top: 20px;
            font-size: 14px;
            font-weight: bold;
            text-align: right;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #7f8c8d;
        }

        .footer p {
            margin: 3px 0;
        }

        .line {
            border-top: 1px solid #2c3e50;
            margin: 10px 0;
        }

        .logo {
            max-width: 50px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="content">
        <div class="header">
            {{-- <img class="logo" src="logo.png" alt="Logo de la Boutique" /> --}}
            <h1>Boutique Informatique Saint Gérard Entreprise</h1>
            <p>Adresse: 123, Rue de la Technologie, Lomé</p>
            <p>Téléphone: 92345678</p>
            <p>Email: contact@boutiquexyz.com</p>
        </div>

        <div class="line"></div>

        <div>
            <p><strong>Client :</strong> {{ $order->name }}</p>
            <p><strong>Téléphone :</strong> {{ $order->phone }}</p>
            <p><strong>Date :</strong> {{ $order->created_at->format('d/m/Y') }}</p>
        </div>

        <div class="line"></div>

        <table class="table">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Quantité</th>
                    <th>Prix Unitaire (F CFA)</th>
                    <th>Total (F CFA)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->orderDetails as $detail)
                    <tr>
                        <td>{{ $detail->product->name }}</td>
                        <td>{{ $detail->quantity }}</td>
                        <td>{{ number_format($detail->price, 2, ',', ' ') }}</td>
                        <td>{{ number_format($detail->price * $detail->quantity, 2, ',', ' ') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="line"></div>

        <div class="total">
            <p><strong>Total : {{ number_format($order->total, 2, ',', ' ') }} F CFA</strong></p>
        </div>

        <div class="footer">
            <p>Merci de votre achat chez Saint Gérard Entreprise !</p>
            <p>Nous espérons vous revoir bientôt.</p>
        </div>
    </div>
</body>
</html>
