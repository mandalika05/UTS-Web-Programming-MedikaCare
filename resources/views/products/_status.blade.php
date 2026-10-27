@if ($product->stock_status === 'habis')
    <span class="badge-s b-out">Habis</span>
@elseif ($product->stock_status === 'rendah')
    <span class="badge-s b-low">Hampir habis</span>
@else
    <span class="badge-s b-ok">Tersedia</span>
@endif
