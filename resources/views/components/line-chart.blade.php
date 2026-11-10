@props(['title', 'labels', 'data', 'max' => null])
@php($chartId = 'chart-' . \Illuminate\Support\Str::random(8))
<canvas id="{{ $chartId }}" {{ $attributes }}></canvas>
<script>
    // app.js(Chart.js 포함)는 defer로 로드되므로 DOMContentLoaded 이후에 그린다
    document.addEventListener('DOMContentLoaded', function () {
        new Chart(document.getElementById(@json($chartId)), {
            type: 'line',
            data: {
                labels: @json($labels),
                datasets: [{ label: @json($title), data: @json($data), tension: 0.3 }],
            },
            options: {
                responsive: true,
                plugins: { title: { display: true, text: @json($title) }, legend: { display: false } },
                scales: { y: { beginAtZero: true, suggestedMax: @json($max), ticks: { precision: 0 } } },
            },
        });
    });
</script>
