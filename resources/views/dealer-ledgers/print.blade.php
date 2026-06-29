<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Detailed Ledger - {{ $dealer->name }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #333;
            margin: 30px;
            font-size: 13px;
        }
        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }
        .company-logo {
            float: left;
            height: 50px;
        }
        .title {
            float: right;
            text-align: right;
        }
        .title h2 {
            margin: 0;
            font-size: 20px;
            color: #333;
        }
        .clear {
            clear: both;
        }
        .details-box {
            margin-bottom: 30px;
        }
        .details-col {
            float: left;
            width: 50%;
        }
        .details-col table {
            width: 100%;
        }
        .details-col td {
            padding: 4px 0;
        }
        .details-col td strong {
            color: #555;
        }
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .ledger-table th, .ledger-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .ledger-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            color: #444;
        }
        .text-right {
            text-align: right;
        }
        .totals-box {
            float: right;
            width: 300px;
            margin-top: 20px;
            border-top: 2px solid #333;
            padding-top: 10px;
        }
        .totals-box table {
            width: 100%;
        }
        .totals-box td {
            padding: 5px 0;
            font-size: 14px;
        }
        @media print {
            body {
                margin: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print();" style="padding: 8px 16px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">
            Print Statement
        </button>
    </div>

    <div class="header">
        <div class="company-logo">
            <!-- Render company name if logo url fails -->
            <span style="font-size: 24px; font-weight: bold; color: #007bff;">{{ company()->company_name }}</span>
        </div>
        <div class="title">
            <h2>ACCOUNT STATEMENT</h2>
            <p style="margin: 5px 0 0 0; color: #777;">Date range: 
                {{ $startDate ? $startDate->format(company()->date_format) : 'Start' }} to 
                {{ $endDate ? $endDate->format(company()->date_format) : 'End' }}
            </p>
        </div>
        <div class="clear"></div>
    </div>

    <div class="details-box">
        <div class="details-col">
            <h3>Dealer Details</h3>
            <table>
                <tr>
                    <td width="30%"><strong>Name:</strong></td>
                    <td>{{ $dealer->name }}</td>
                </tr>
                <tr>
                    <td><strong>Location:</strong></td>
                    <td>{{ $dealer->clientDetails->city ?? '--' }} / {{ $dealer->clientDetails->area ?? '--' }}</td>
                </tr>
                <tr>
                    <td><strong>NTN / STRN:</strong></td>
                    <td>{{ $dealer->clientDetails->ntn_number ?? '--' }} / {{ $dealer->clientDetails->strn_number ?? '--' }}</td>
                </tr>
                <tr>
                    <td><strong>Salesperson:</strong></td>
                    <td>{{ $dealer->clientDetails->salesperson->name ?? '--' }}</td>
                </tr>
            </table>
        </div>
        <div class="details-col">
            <h3>Credit Summary</h3>
            <table>
                <tr>
                    <td width="40%"><strong>Credit Limit:</strong></td>
                    <td>{{ currency_format($dealer->clientDetails->credit_limit ?? 0.00, company()->currency_id) }}</td>
                </tr>
                <tr>
                    <td><strong>Total Debits:</strong></td>
                    <td>{{ currency_format($totalDebit, company()->currency_id) }}</td>
                </tr>
                <tr>
                    <td><strong>Total Credits:</strong></td>
                    <td>{{ currency_format($totalCredit, company()->currency_id) }}</td>
                </tr>
                <tr>
                    <td><strong>Net Outstanding:</strong></td>
                    <td><strong>{{ currency_format($currentBalance, company()->currency_id) }}</strong></td>
                </tr>
            </table>
        </div>
        <div class="clear"></div>
    </div>

    <table class="ledger-table">
        <thead>
            <tr>
                <th width="12%">Date</th>
                <th width="18%">Reference</th>
                <th width="15%">Type</th>
                <th class="text-right" width="15%">Debit (+)</th>
                <th class="text-right" width="15%">Credit (-)</th>
                <th class="text-right" width="25%">Running Balance</th>
            </tr>
        </thead>
        <tbody>
            @if($startDate)
                <tr style="background-color: #fafafa; font-weight: bold;">
                    <td>{{ $startDate->format(company()->date_format) }}</td>
                    <td>--</td>
                    <td>Opening Balance</td>
                    <td class="text-right">--</td>
                    <td class="text-right">--</td>
                    <td class="text-right">{{ currency_format($openingBalance, company()->currency_id) }}</td>
                </tr>
            @endif

            @forelse($ledgerEntries as $entry)
                <tr style="{{ $entry->is_reversed ? 'text-decoration: line-through; color: #888;' : '' }}">
                    <td>{{ $entry->date->format(company()->date_format) }}</td>
                    <td>{{ $entry->reference_number }}</td>
                    <td>{{ ucfirst($entry->transaction_type) }}</td>
                    <td class="text-right" style="color: #c9302c;">
                        {{ $entry->debit > 0 ? currency_format($entry->debit, company()->currency_id) : '--' }}
                    </td>
                    <td class="text-right" style="color: #5cb85c;">
                        {{ $entry->credit > 0 ? currency_format($entry->credit, company()->currency_id) : '--' }}
                    </td>
                    <td class="text-right" style="font-weight: bold; color: {{ $entry->balance > 0 ? '#c9302c' : '#5cb85c' }}">
                        {{ currency_format($entry->balance, company()->currency_id) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">No transaction entries found for the selected date range.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="totals-box">
        <table>
            <tr>
                <td><strong>Statement Total Debit:</strong></td>
                <td class="text-right">{{ currency_format($totalDebit, company()->currency_id) }}</td>
            </tr>
            <tr>
                <td><strong>Statement Total Credit:</strong></td>
                <td class="text-right">{{ currency_format($totalCredit, company()->currency_id) }}</td>
            </tr>
            <tr>
                <td><strong>Statement Net Balance:</strong></td>
                <td class="text-right"><strong>{{ currency_format($currentBalance, company()->currency_id) }}</strong></td>
            </tr>
        </table>
    </div>
    <div class="clear"></div>

    <div style="margin-top: 50px; border-top: 1px solid #ddd; padding-top: 15px; color: #777; font-size: 11px;">
        <span style="float: left;">Printed By: {{ auth()->user()->name }} on {{ now()->format(company()->date_format . ' H:i') }}</span>
        <span style="float: right;">Page 1 of 1</span>
        <div class="clear"></div>
    </div>
</body>
</html>
