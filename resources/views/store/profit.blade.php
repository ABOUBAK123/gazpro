@extends('layouts.app')
@section('title', 'Bénéfices')
@section('page-title', 'Analyse des bénéfices')

@section('content')
<div class="space-y-6 pt-4">

    {{-- KPI totals --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="stat-card border-l-4 border-green-400">
            <div class="text-xs text-gray-500 mb-1">Revenus totaux (12 mois)</div>
            <div class="text-2xl font-bold text-gray-800">{{ number_format($totals['revenue'], 0, ',', ' ') }}</div>
            <div class="text-xs text-green-600">XOF</div>
        </div>
        <div class="stat-card border-l-4 border-red-400">
            <div class="text-xs text-gray-500 mb-1">Dépenses totales (12 mois)</div>
            <div class="text-2xl font-bold text-gray-800">{{ number_format($totals['expenses'], 0, ',', ' ') }}</div>
            <div class="text-xs text-red-500">XOF</div>
        </div>
        <div class="stat-card border-l-4 {{ $totals['profit'] >= 0 ? 'border-blue-400' : 'border-orange-400' }}">
            <div class="text-xs text-gray-500 mb-1">Bénéfice net (12 mois)</div>
            <div class="text-2xl font-bold {{ $totals['profit'] >= 0 ? 'text-blue-700' : 'text-orange-600' }}">
                {{ number_format($totals['profit'], 0, ',', ' ') }}
            </div>
            <div class="text-xs {{ $totals['profit'] >= 0 ? 'text-blue-500' : 'text-orange-500' }}">XOF</div>
        </div>
    </div>

    {{-- Monthly breakdown --}}
    <div class="card p-0 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Répartition mensuelle — 12 derniers mois</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-6 py-3 text-gray-600 font-medium">Mois</th>
                        <th class="text-right px-6 py-3 text-gray-600 font-medium">Revenus (XOF)</th>
                        <th class="text-right px-6 py-3 text-gray-600 font-medium">Dépenses (XOF)</th>
                        <th class="text-right px-6 py-3 text-gray-600 font-medium">Bénéfice net (XOF)</th>
                        <th class="text-right px-6 py-3 text-gray-600 font-medium">Marge</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($months as $m)
                    @php
                        $margin = $m['revenue'] > 0 ? ($m['profit'] / $m['revenue'] * 100) : 0;
                        $isProfit = $m['profit'] >= 0;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-700 capitalize">{{ $m['label'] }}</td>
                        <td class="px-6 py-4 text-right text-green-700 font-medium">
                            {{ $m['revenue'] > 0 ? number_format($m['revenue'], 0, ',', ' ') : '—' }}
                        </td>
                        <td class="px-6 py-4 text-right text-red-600">
                            {{ $m['expenses'] > 0 ? number_format($m['expenses'], 0, ',', ' ') : '—' }}
                        </td>
                        <td class="px-6 py-4 text-right font-bold {{ $isProfit ? 'text-blue-700' : 'text-orange-600' }}">
                            {{ $m['revenue'] > 0 || $m['expenses'] > 0
                                ? ($isProfit ? '+' : '') . number_format($m['profit'], 0, ',', ' ')
                                : '—' }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($m['revenue'] > 0)
                                <span class="badge {{ $margin >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ number_format($margin, 1) }}%
                                </span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                    <tr>
                        <td class="px-6 py-4 font-bold text-gray-700">Total</td>
                        <td class="px-6 py-4 text-right font-bold text-green-700">{{ number_format($totals['revenue'], 0, ',', ' ') }}</td>
                        <td class="px-6 py-4 text-right font-bold text-red-600">{{ number_format($totals['expenses'], 0, ',', ' ') }}</td>
                        <td class="px-6 py-4 text-right font-bold {{ $totals['profit'] >= 0 ? 'text-blue-700' : 'text-orange-600' }}">
                            {{ ($totals['profit'] >= 0 ? '+' : '') . number_format($totals['profit'], 0, ',', ' ') }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($totals['revenue'] > 0)
                                @php $gm = $totals['profit'] / $totals['revenue'] * 100; @endphp
                                <span class="badge {{ $gm >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} font-bold">
                                    {{ number_format($gm, 1) }}%
                                </span>
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
@endsection
