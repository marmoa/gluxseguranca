<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fatura Vencida {{ $invoice->number }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; background: #f9f9f9; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .header { background: #dc2626; color: #fff; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px; }
        .alert-box { background: #fef2f2; border-left: 4px solid #dc2626; border-radius: 4px; padding: 16px; margin: 20px 0; }
        .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
        .info-row:last-child { border-bottom: none; }
        .label { color: #6b7280; font-size: 14px; }
        .value { font-weight: bold; }
        .total { font-size: 18px; color: #dc2626; }
        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>⚠️ Fatura com Vencimento em Atraso</h1>
        <p style="margin: 8px 0 0; opacity: .85;">{{ $invoice->number }}</p>
    </div>

    <div class="content">
        <p>Olá, <strong>{{ $invoice->client->name }}</strong>!</p>
        <p>Identificamos que a fatura abaixo está com o pagamento em atraso. Por favor, regularize a situação o quanto antes.</p>

        <div class="alert-box">
            <div class="info-row">
                <span class="label">Nº da Fatura</span>
                <span class="value">{{ $invoice->number }}</span>
            </div>
            @if($invoice->serviceOrder)
            <div class="info-row">
                <span class="label">Ordem de Serviço</span>
                <span class="value">#{{ $invoice->serviceOrder->number }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="label">Valor Total</span>
                <span class="value total">R$ {{ number_format($invoice->total_amount, 2, ',', '.') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Data de Vencimento</span>
                <span class="value" style="color: #dc2626;">{{ $invoice->due_date->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Dias em Atraso</span>
                <span class="value" style="color: #dc2626;">{{ $invoice->due_date->diffInDays(now()) }} dias</span>
            </div>
        </div>

        <p>Entre em contato com nossa equipe para regularizar esta pendência e evitar maiores inconvenientes.</p>
    </div>

    <div class="footer">
        <p>{{ config('app.name') }} — {{ config('app.institution_desc', config('app.name')) }}</p>
        <p>Este é um e-mail automático, por favor não responda diretamente.</p>
    </div>
</div>
</body>
</html>
