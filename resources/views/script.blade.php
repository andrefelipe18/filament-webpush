{{-- Load webpush script with duplicate prevention for PWA mode --}}
<script>
if (!document.querySelector('script[data-webpush-loaded]') && !window.filamentWebpushInitialized) {
    const script = document.createElement('script');
    script.src = '{{ asset('js/webpush.js') }}';
    script.defer = true;
    script.setAttribute('data-webpush-loaded', 'true');
    
    script.onload = function() {
        window.filamentWebpushInitialized = true;
    };
    
    document.head.appendChild(script);
}
</script>
