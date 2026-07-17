<?php
if (!isset($pageTitle)) {
    $pageTitle = "Powercabs Dispatcher";
}
?>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($pageTitle); ?></title>

    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://maps.googleapis.com">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="stylesheet" href="/global.css" />
    <style>
      .pw-global-loader{position:fixed;inset:0;z-index:99999;background:#F4F4F5;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:14px;transition:opacity .3s}
      .pw-global-loader.is-hidden{opacity:0;pointer-events:none}
      .pw-loader-spinner{width:38px;height:38px;border:3.5px solid #E4E4E7;border-top-color:#f37a20;border-radius:50%;animation:pwSpin .7s linear infinite}
      @keyframes pwSpin{to{transform:rotate(360deg)}}
      .pw-loader-text{font-size:.82rem;font-weight:600;color:#71717A;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}
    </style>
    <script>
      (function(){
        document.addEventListener('DOMContentLoaded',function(){
          var d=document.createElement('div');
          d.id='pwGlobalLoader';d.className='pw-global-loader';
          d.innerHTML='<div class="pw-loader-spinner"></div><div class="pw-loader-text">Loading...</div>';
          document.body.prepend(d);
          // Fallback auto-hide shortly after the DOM is ready, so page
          // navigation doesn't sit on this spinner waiting for slow external
          // resources (Google Maps, CDN scripts, images) to fully finish via
          // window 'load' — pages that fetch their own data still call
          // hideGlobalLoader() explicitly as soon as that data is ready,
          // which fires sooner than this fallback.
          setTimeout(window.hideGlobalLoader,600);
        });
        window.hideGlobalLoader=function(){
          var el=document.getElementById('pwGlobalLoader');
          if(el){el.classList.add('is-hidden');setTimeout(function(){el.remove()},350);}
        };
      })();
    </script>
    <script src="js/status-badge.js" defer></script>
    <script src="js/beep-monitor.js" defer></script>
    <script src="js/searching-ride-beep.js" defer></script>
</head>
