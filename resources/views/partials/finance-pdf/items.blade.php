@if(count($items))
<table class="grid">
    <thead>
        <tr>
            <th style="width:9%">Code</th>
            <th style="width:28%">Description</th>
            <th style="width:14%">Image</th>
            <th style="width:8%">Qty</th>
            <th style="width:12%">Rate</th>
            <th style="width:13%">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
        @if(!empty($item['is_section']))
        <tr class="section-row">
            <td colspan="6" class="{{ !empty($item['descriptionIsArabic']) ? 'ar' : '' }}">{{ $item['description'] }}</td>
        </tr>
        @else
        <tr>
            <td>{{ $item['code'] }}</td>
            <td class="desc {{ !empty($item['descriptionIsArabic']) ? 'ar' : '' }}">{!! nl2br(e($item['description'])) !!}</td>
            <td>
                @if(!empty($item['imageDataUri']))
                    <img class="thumb" src="{{ $item['imageDataUri'] }}" alt="">
                @else
                    <div class="thumb-empty"></div>
                @endif
            </td>
            <td class="num">{{ rtrim(rtrim(number_format((float)$item['quantity'], 2, '.', ''), '0'), '.') }}</td>
            <td class="num">{{ $currency }} {{ number_format((float)$item['rate'], 2) }}</td>
            <td class="num">{{ $currency }} {{ number_format((float)($item['final_amount'] ?? $item['amount']), 2) }}</td>
        </tr>
        @endif
        @endforeach
    </tbody>
</table>
@endif
