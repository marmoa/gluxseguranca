<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fatura {{ $invoice->number }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; background: #f9f9f9; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .header { background: #1e40af; color: #fff; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px; }
        .info-box { background: #f0f4ff; border-left: 4px solid #1e40af; border-radius: 4px; padding: 16px; margin: 20px 0; }
        .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
        .info-row:last-child { border-bottom: none; }
        .label { color: #6b7280; font-size: 14px; }
        .value { font-weight: bold; }
        .total { font-size: 18px; color: #1e40af; }
        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #9ca3af; }
        .btn { display: inline-block; background: #1e40af; color: #fff; padding: 12px 24px; border-radius: 6px; text-decoration: none; margin-top: 20px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>📄 Nova Fatura Emitida</h1>
        <p style="margin: 8px 0 0; opacity: .85;">{{ $invoice->number }}</p>
    </div>

    <div class="content">
        <p>Olá, <strong>{{ $invoice->client->name }}</strong>!</p>
        <p>Uma nova fatura foi emitida para você. Confira os detalhes abaixo:</p>

        <div class="info-box">
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
                <span class="label">Valor dos Serviços</span>
                <span class="value">R$ {{ number_format($invoice->amount, 2, ',', '.') }}</span>
            </div>
            @if($invoice->tax_amount > 0)
            <div class="info-row">
                <span class="label">Impostos / Taxas</span>
                <span class="value">R$ {{ number_format($invoice->tax_amount, 2, ',', '.') }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="label">Total</span>
                <span class="value total">R$ {{ number_format($invoice->total_amount, 2, ',', '.') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Vencimento</span>
                <span class="value" style="color: {{ $invoice->due_date < now() ? '#dc2626' : '#374151' }}">
                    {{ $invoice->due_date->format('d/m/Y') }}
                </span>
            </div>
        </div>

        @if($invoice->notes)
        <p><strong>Observações:</strong> {{ $invoice->notes }}</p>
        @endif

        @if(!$invoice->hasPdf())
        <p style="color: #6b7280; font-size: 14px;">O PDF desta fatura será anexado quando disponível.</p>
        @endif

        <p>Em caso de dúvidas, entre em contato conosco.</p>
    </div>

    <div class="footer">
        <p>{{ config('app.name') }} — {{ config('app.institution_desc', config('app.name')) }}</p>
        <p>Este é um e-mail automático, por favor não responda diretamente.</p>
    </div>
</div>
</body>
</html>
