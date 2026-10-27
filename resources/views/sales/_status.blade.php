@if ($sale->status === 'completed')
    <span class="badge-s b-ok">Selesai</span>
@elseif ($sale->status === 'pending')
    <span class="badge-s b-low">Menunggu</span>
@else
    <span class="badge-s b-out">Dibatalkan</span>
@endif
