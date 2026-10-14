{if $google_client_id}
<div class="wo-google-wrap">
<button type="button" class="wo-google-custom" id="woGoogleCustom" data-cid="{$google_client_id|escape}">
  <span class="wo-google-g" aria-hidden="true">
    <svg viewBox="0 0 48 48" width="22" height="22">
      <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
      <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
      <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
      <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
    </svg>
  </span>
  Continuer avec Google
</button>
<div id="woGoogleHidden" class="wo-google-hidden"></div>
<div id="woGoogleForm" hidden data-action="{$google_url}"><input type="hidden" name="credential" id="woGoogleCred">{if $ref|default:''}<input type="hidden" name="ref" value="{$ref|escape}">{/if}</div>
<script src="https://accounts.google.com/gsi/client" async defer></script>
{literal}
<script>
(function(){
  function boot(){
    if(!window.google || !google.accounts){ setTimeout(boot, 80); return; }
    var btn=document.getElementById('woGoogleCustom');
    var hidden=document.getElementById('woGoogleHidden');
    if(!btn||!hidden) return;
    var cid=btn.getAttribute('data-cid');
    if(!cid) return;
    google.accounts.id.initialize({
      client_id: cid,
      callback: function(r){
        var i=document.getElementById('woGoogleCred');
        var box=document.getElementById('woGoogleForm');
        if(!i||!box||!r||!r.credential) return;
        i.value=r.credential;
        var f=document.createElement('form');
        f.method='post';
        f.action=box.getAttribute('data-action')||'';
        f.style.display='none';
        while(box.firstChild) f.appendChild(box.firstChild);
        document.body.appendChild(f);
        f.submit();
      }
    });
    google.accounts.id.renderButton(hidden, {type:'icon', theme:'outline', size:'large', shape:'circle'});
    btn.addEventListener('click', function(){
      var g=hidden.querySelector('div[role="button"]');
      if(g) g.click();
      else google.accounts.id.prompt();
    });
  }
  if(document.readyState==='complete') boot();
  else window.addEventListener('load', boot);
})();
</script>
{/literal}
</div>
{/if}
