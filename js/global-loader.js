(function(){
  document.addEventListener('DOMContentLoaded',function(){
    var d=document.createElement('div');
    d.id='pwGlobalLoader';d.className='pw-global-loader';
    d.innerHTML='<div class="pw-loader-spinner"></div><div class="pw-loader-text">Loading...</div>';
    document.body.prepend(d);
    setTimeout(window.hideGlobalLoader,600);
  });
  window.hideGlobalLoader=function(){
    var el=document.getElementById('pwGlobalLoader');
    if(el){el.classList.add('is-hidden');setTimeout(function(){el.remove()},350);}
  };
})();
