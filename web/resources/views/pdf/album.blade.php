<style>
    .page-break {
        page-break-after: always;
    }
</style>

<img src="{{ storage_path('app/public/albums/'.$album->id.'/'.$coverImageName) }}" width="100%">
<div class="page-break"></div>
@for($i = 1; $i<= $pagesNumber; $i++)
    <img src="{{ storage_path('app/public/albums/'.$album->id.'/pdf/page_'.$i.'.jpg') }}" width="100%">
    <div class="page-break"></div>
@endfor
<p>Invite {{ $coupon->usage_times }} of your friends and let them get  {{ $coupon->value }}$ now!</p>
<p>{{ $coupon->code }}</p>
