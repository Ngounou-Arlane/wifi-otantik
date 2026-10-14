{include file="otantik_shell_head.tpl"}
<style>
{literal}
.wo-wa-app{--wa-bg:#efeae2;--wa-panel:#ffffff;--wa-panel-2:#f0f2f5;--wa-border:#e9edef;--wa-text:#111b21;--wa-muted:#667781;--wa-in:#ffffff;--wa-out:#d9fdd3;--wa-accent:#00a884;--wa-hover:rgba(0,0,0,.035);--wa-item-border:rgba(0,0,0,.06);--wa-texture:rgba(0,0,0,.015);--wa-fail:#dc2626;
  display:flex;height:calc(100vh - 150px);min-height:480px;max-height:820px;border-radius:14px;overflow:hidden;border:1px solid var(--wa-border);background:var(--wa-panel);color:var(--wa-text);font-size:14px}
[data-theme="dark"] .wo-wa-app{--wa-bg:#0b141a;--wa-panel:#111b21;--wa-panel-2:#202c33;--wa-border:#273238;--wa-text:#e9edef;--wa-muted:#8696a0;--wa-in:#202c33;--wa-out:#005c4b;--wa-accent:#00a884;--wa-hover:rgba(255,255,255,.03);--wa-item-border:rgba(255,255,255,.04);--wa-texture:rgba(255,255,255,.02);--wa-fail:#f87171}
.wo-wa-side{width:340px;flex:0 0 340px;display:flex;flex-direction:column;border-right:1px solid var(--wa-border);background:var(--wa-panel)}
.wo-wa-side-head{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border-bottom:1px solid var(--wa-border)}
.wo-wa-side-head h2{margin:0;font-size:18px}
.wo-wa-compose-btn{width:36px;height:36px;border-radius:50%;background:var(--wa-accent);border:none;color:#fff;font-size:18px;display:flex;align-items:center;justify-content:center;cursor:pointer}
.wo-wa-compose-btn:hover{filter:brightness(1.08)}
.wo-wa-search-wrap{padding:8px 12px}
.wo-wa-search{width:100%;box-sizing:border-box;background:var(--wa-panel-2);border:1px solid var(--wa-border);border-radius:20px;padding:8px 14px;color:var(--wa-text);font-size:13px}
.wo-wa-search::placeholder{color:var(--wa-muted)}
.wo-wa-list{flex:1;overflow-y:auto}
.wo-wa-item{display:flex;gap:12px;align-items:center;padding:10px 16px;text-decoration:none;color:inherit;border-bottom:1px solid var(--wa-item-border)}
.wo-wa-item:hover{background:var(--wa-hover)}
.wo-wa-item.active{background:var(--wa-panel-2)}
.wo-wa-avatar{width:46px;height:46px;border-radius:50%;flex:0 0 46px;display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:17px}
.wo-wa-c1{background:#e17076}.wo-wa-c2{background:#7bc862}.wo-wa-c3{background:#65aadd}.wo-wa-c4{background:#a695e7}
.wo-wa-c5{background:#edb86b}.wo-wa-c6{background:#f68787}.wo-wa-c7{background:#4fae8e}.wo-wa-c8{background:#9a8af3}
.wo-wa-item-body{flex:1;min-width:0}
.wo-wa-item-top{display:flex;justify-content:space-between;align-items:baseline;gap:8px}
.wo-wa-item-name{font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.wo-wa-item-time{font-size:11px;color:var(--wa-muted);flex:0 0 auto}
.wo-wa-item-bottom{display:flex;justify-content:space-between;gap:8px}
.wo-wa-item-preview{color:var(--wa-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:13px}
.wo-wa-item-phone{color:var(--wa-muted);font-size:11px}
.wo-wa-more-wrap{padding:12px;text-align:center}
.wo-wa-more-btn{background:none;border:1px solid var(--wa-border);color:var(--wa-text);border-radius:20px;padding:7px 18px;font-size:12px;cursor:pointer}
.wo-wa-more-btn:hover{background:var(--wa-panel-2)}
.wo-wa-main{flex:1;display:flex;flex-direction:column;background:var(--wa-bg);background-image:radial-gradient(circle at 20% 20%, var(--wa-texture) 0, transparent 40%)}
.wo-wa-main-head{display:flex;align-items:center;gap:12px;padding:10px 16px;border-bottom:1px solid var(--wa-border);background:var(--wa-panel)}
.wo-wa-back{display:none;background:none;border:none;color:var(--wa-text);font-size:18px;cursor:pointer;padding:4px}
.wo-wa-main-head-name{font-weight:600}
.wo-wa-main-head-phone{font-size:12px;color:var(--wa-muted)}
.wo-wa-badge{margin-left:auto;font-size:11px;font-weight:600;padding:4px 10px;border-radius:10px;white-space:nowrap;letter-spacing:.2px}
.wo-wa-badge-open{background:rgba(16,185,129,.15);color:#0d9488}
.wo-wa-badge-closed{background:rgba(220,38,38,.13);color:var(--wa-fail)}
.wo-wa-badge-none{background:var(--wa-panel-2);color:var(--wa-muted)}
.wo-wa-thread{flex:1;overflow-y:auto;padding:18px 24px;display:flex;flex-direction:column;gap:2px}
.wo-wa-day{align-self:center;background:var(--wa-panel-2);color:var(--wa-muted);font-size:12px;padding:5px 12px;border-radius:8px;margin:12px 0}
.wo-wa-row{display:flex;margin-bottom:4px}
.wo-wa-row.in{justify-content:flex-start}
.wo-wa-row.out{justify-content:flex-end}
.wo-wa-bubble{max-width:68%;padding:7px 10px;border-radius:9px;line-height:1.4;position:relative}
.wo-wa-row.in .wo-wa-bubble{background:var(--wa-in);border-top-left-radius:2px}
.wo-wa-row.out .wo-wa-bubble{background:var(--wa-out);border-top-right-radius:2px}
.wo-wa-bubble-label{font-size:11px;color:var(--wa-muted);display:block;margin-bottom:2px}
.wo-wa-bubble-text{white-space:pre-wrap;word-break:break-word}
.wo-wa-bubble-foot{display:flex;justify-content:flex-end;gap:4px;margin-top:2px}
.wo-wa-bubble-time{font-size:10px;color:var(--wa-muted)}
.wo-wa-bubble-fail{font-size:10px;color:var(--wa-fail)}
.wo-wa-compose-row{border-top:1px solid var(--wa-border);background:var(--wa-panel);padding:10px 14px}
.wo-wa-send-err{font-size:12px;color:var(--wa-fail);margin:0 0 6px;text-align:center;display:none}
.wo-wa-compose-form{display:flex;gap:10px;align-items:flex-end}
.wo-wa-input{flex:1;background:var(--wa-panel-2);border:1px solid var(--wa-border);border-radius:20px;padding:10px 16px;color:var(--wa-text);resize:none;font-family:inherit;font-size:14px;max-height:110px}
.wo-wa-input::placeholder{color:var(--wa-muted)}
.wo-wa-send{width:40px;height:40px;border-radius:50%;background:var(--wa-accent);border:none;color:#fff;font-size:16px;display:flex;align-items:center;justify-content:center;cursor:pointer;flex:0 0 40px}
.wo-wa-send:disabled{opacity:.5;cursor:not-allowed}
.wo-wa-reopen-hint{font-size:12px;color:var(--wa-muted);margin-top:8px;text-align:center}
.wo-wa-empty{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--wa-muted);gap:10px}
.wo-wa-empty i{font-size:48px;opacity:.3}
.wo-wa-note{padding:10px 16px;font-size:12px;color:#f0b429;background:rgba(240,180,41,.08);border-bottom:1px solid var(--wa-border)}
@media (max-width:760px){
  .wo-wa-app{height:calc(100vh - 110px)}
  .wo-wa-side{width:100%;flex:1 1 auto}
  .wo-wa-main{display:none}
  .wo-wa-app.wo-wa-open .wo-wa-side{display:none}
  .wo-wa-app.wo-wa-open .wo-wa-main{display:flex;flex:1 1 auto}
  .wo-wa-back{display:block}
}
{/literal}
</style>
<div class="wo-head">
  <div>
    <h1>WhatsApp</h1>
    <p>Répondez aux clients par écrit (fenêtre de 24 h après leur dernier message).{if $is_platform} Compte SuperAdmin uniquement.{/if}</p>
  </div>
  {if $is_platform}
  <a class="wo-btn wo-btn-ghost" href="{Text::url('plugin/wifi_otantik_wa_logs')}">Journal complet</a>
  {/if}
</div>
{if !$wa_ready}
<div class="wo-card" style="margin-bottom:16px">
  {if $is_platform}
  <p style="margin:0;color:#b45309">WhatsApp n'est pas encore configuré. Collez l'identifiant du numéro et le jeton d'accès dans <a href="{Text::url('plugin/wifi_otantik_pg')}">Paiements</a>.</p>
  {else}
  <p style="margin:0;color:#b45309">WhatsApp n'est pas encore configuré côté plateforme. Contactez le support.</p>
  {/if}
</div>
{/if}

<div class="wo-wa-app{if $sel != ''} wo-wa-open{/if}" id="woWaApp">
  <div class="wo-wa-side">
    <div class="wo-wa-side-head">
      <h2>Discussions</h2>
      <button type="button" class="wo-wa-compose-btn" title="Nouvelle conversation" onclick="woWaComposeOpen()"><i class="fa fa-plus"></i></button>
    </div>
    <div class="wo-wa-search-wrap">
      <input type="text" class="wo-wa-search" id="woWaSearch" placeholder="Rechercher un numéro…" inputmode="numeric">
    </div>
    <div class="wo-wa-list" id="woWaList">
      {foreach $convos as $c}
      <a href="{Text::url('plugin/wifi_otantik_wa_inbox')}&phone={$c.phone|escape:'url'}" class="wo-wa-item{if $c.active} active{/if}" data-phone="{$c.phone|escape}">
        <div class="wo-wa-avatar {$c.avatar_color}">{$c.avatar_letter|escape}</div>
        <div class="wo-wa-item-body">
          <div class="wo-wa-item-top">
            <span class="wo-wa-item-name">{$c.name|escape}</span>
            <span class="wo-wa-item-time">{$c.ago|escape}</span>
          </div>
          <div class="wo-wa-item-bottom">
            <span class="wo-wa-item-preview">{if $c.direction == 'in'}← {/if}{$c.preview|escape}</span>
            {if $c.name != $c.phone}<span class="wo-wa-item-phone">{$c.phone|escape}</span>{/if}
          </div>
        </div>
      </a>
      {/foreach}
      {if $convos|@count == 0}
      <div class="wo-wa-empty" style="height:100%"><i class="fa fa-whatsapp"></i><span>Aucune conversation pour le moment</span></div>
      {/if}
    </div>
    <div class="wo-wa-more-wrap" id="woWaMoreWrap"{if !$convos_has_more} style="display:none"{/if}>
      <button type="button" class="wo-wa-more-btn" id="woWaMoreBtn" onclick="woWaLoadMore()">Charger plus</button>
    </div>
  </div>

  <div class="wo-wa-main">
    {if $sel == ''}
    <div class="wo-wa-empty"><i class="fa fa-comments-o"></i><span>Choisissez une conversation à gauche, ou démarrez-en une nouvelle</span></div>
    {else}
    <div class="wo-wa-main-head">
      <button type="button" class="wo-wa-back" onclick="woWaBack()"><i class="fa fa-arrow-left"></i></button>
      <div class="wo-wa-avatar {$sel_avatar_color}" style="width:38px;height:38px;flex:0 0 38px;font-size:14px">{$sel_avatar_letter|escape}</div>
      <div>
        <div class="wo-wa-main-head-name">{$sel_name|escape}</div>
        {if $sel_name != $sel}<div class="wo-wa-main-head-phone">{$sel|escape}</div>{/if}
      </div>
      <span class="wo-wa-badge wo-wa-badge-none" id="woWaWindowBadge"></span>
    </div>
    <div class="wo-wa-thread" id="woWaThread">
      {if $thread|@count == 0}
      <div class="wo-wa-empty" style="flex:none;padding:30px 0"><i class="fa fa-search" style="font-size:28px"></i><span>Aucun message pour ce numéro</span></div>
      {/if}
      {assign var="wa_day" value=""}
      {foreach $thread as $m}
        {if $m.day != $wa_day}
          {assign var="wa_day" value=$m.day}
          <div class="wo-wa-day">{$m.day|escape}</div>
        {/if}
        <div class="wo-wa-row {$m.direction}">
          <div class="wo-wa-bubble">
            {if $m.direction != 'in'}<small class="wo-wa-bubble-label">{$m.label|escape}</small>{/if}
            <div class="wo-wa-bubble-text">{$m.detail|escape|nl2br nofilter}</div>
            <div class="wo-wa-bubble-foot">
              {if !$m.ok}<span class="wo-wa-bubble-fail">Échec</span>{/if}
              <span class="wo-wa-bubble-time">{$m.time|escape}</span>
            </div>
          </div>
        </div>
      {/foreach}
    </div>
    <div class="wo-wa-compose-row">
      <p class="wo-wa-send-err" id="woWaSendErr"></p>
      <form method="post" class="wo-wa-compose-form" id="woWaSendForm">
        <input type="hidden" name="wa_send" value="1">
        <input type="hidden" name="wa_phone" value="{$sel|escape}">
        <textarea class="wo-wa-input" name="wa_text" id="woWaText" rows="1" placeholder="Écrire un message…" required{if !$wa_ready} disabled{/if}></textarea>
        <button class="wo-wa-send" type="submit" title="Envoyer"{if !$wa_ready} disabled{/if}><i class="fa fa-paper-plane"></i></button>
      </form>
      <p class="wo-wa-reopen-hint">Si l’envoi échoue pour cause de fenêtre de 24 h dépassée :
        <form method="post" style="display:inline">
          <input type="hidden" name="wa_reopen" value="1">
          <input type="hidden" name="wa_phone" value="{$sel|escape}">
          <button type="submit" style="background:none;border:none;color:var(--wo-blue, #4fa8e8);text-decoration:underline;cursor:pointer;font:inherit"{if !$wa_ready} disabled{/if}>rouvrir avec le modèle wo_hello</button>
        </form>
      </p>
    </div>
    {/if}
  </div>
</div>

<div class="wo-modal" id="woWaComposeModal" onclick="if(event.target===this) woWaComposeClose()">
  <div class="wo-modal-card" style="width:min(420px,100%)">
    <div class="wo-modal-h">
      <h2 style="margin:0">Nouvelle conversation</h2>
      <a href="#" onclick="woWaComposeClose();return false;" class="wo-iconbtn">&times;</a>
    </div>
    <form method="post" style="margin-top:16px">
      <input type="hidden" name="wa_start" value="1">
      <p><label class="wo-label">Numéro de téléphone</label>
      <input class="wo-input" type="tel" name="wa_phone" inputmode="numeric" placeholder="6XX XX XX XX" required autofocus></p>
      <p style="font-size:12px;color:var(--wo-muted)">Un premier message (modèle « wo_hello ») sera envoyé pour ouvrir la conversation - c’est obligatoire pour contacter un numéro qui ne vous a jamais écrit.</p>
      <div style="text-align:right;margin-top:12px">
        <button class="wo-btn wo-btn-primary" type="submit"{if !$wa_ready} disabled{/if}>Démarrer la conversation</button>
      </div>
    </form>
  </div>
</div>

{literal}
<script>
(function(){
  var app = document.getElementById('woWaApp');
  var thread = document.getElementById('woWaThread');
  if (thread) { thread.scrollTop = thread.scrollHeight; }

  var ta = document.getElementById('woWaText');
  var sendForm = document.getElementById('woWaSendForm');
  var sendErr = document.getElementById('woWaSendErr');
  if (ta) {
    ta.addEventListener('input', function(){
      ta.style.height = 'auto';
      ta.style.height = Math.min(110, ta.scrollHeight) + 'px';
    });
    ta.addEventListener('keydown', function(e){
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        woWaSend();
      }
    });
  }
  if (sendForm) {
    sendForm.addEventListener('submit', function(e){
      e.preventDefault();
      woWaSend();
    });
  }

  var lastId = {/literal}{$thread_last_id|default:0|intval}{literal};
  var lastDay = {/literal}'{$thread_last_day|escape:'javascript'}'{literal};
  var pollTimer = null;

  function woWaAppendRow(row){
    var threadEl = document.getElementById('woWaThread');
    if (!threadEl) return;
    var empty = threadEl.querySelector('.wo-wa-empty');
    if (empty) empty.remove();
    if (row.day && row.day !== lastDay) {
      lastDay = row.day;
      var dayEl = document.createElement('div');
      dayEl.className = 'wo-wa-day';
      dayEl.textContent = row.day;
      threadEl.appendChild(dayEl);
    }
    var rowEl = document.createElement('div');
    rowEl.className = 'wo-wa-row ' + row.direction;
    var labelHtml = row.direction !== 'in' ? '<small class="wo-wa-bubble-label">' + escHtml(row.label) + '</small>' : '';
    var failHtml = !row.ok ? '<span class="wo-wa-bubble-fail">Échec</span>' : '';
    rowEl.innerHTML = '<div class="wo-wa-bubble">' + labelHtml +
      '<div class="wo-wa-bubble-text">' + escHtml(row.detail).replace(/\n/g, '<br>') + '</div>' +
      '<div class="wo-wa-bubble-foot">' + failHtml + '<span class="wo-wa-bubble-time">' + escHtml(row.time) + '</span></div></div>';
    threadEl.appendChild(rowEl);
    threadEl.scrollTop = threadEl.scrollHeight;
    if (row.id > lastId) lastId = row.id;
  }

  function woWaFmtRemaining(s){
    s = Math.max(0, s | 0);
    var h = Math.floor(s / 3600), m = Math.floor((s % 3600) / 60);
    return h + 'h' + (m < 10 ? '0' : '') + m;
  }

  window.woWaRenderWindow = function(win){
    var el = document.getElementById('woWaWindowBadge');
    if (!el) return;
    if (!win || !win.last_in_at) {
      el.className = 'wo-wa-badge wo-wa-badge-none';
      el.textContent = 'Le client n’a jamais écrit';
      return;
    }
    if (win.open) {
      el.className = 'wo-wa-badge wo-wa-badge-open';
      el.textContent = 'Fenêtre 24h ouverte · ' + woWaFmtRemaining(win.remaining) + ' restant';
    } else {
      el.className = 'wo-wa-badge wo-wa-badge-closed';
      el.textContent = 'Fenêtre 24h fermée';
    }
  };

  window.woWaSend = function(){
    if (!ta || !sendForm) return;
    var text = ta.value.trim();
    if (text === '') return;
    var btn = sendForm.querySelector('.wo-wa-send');
    if (btn) btn.disabled = true;
    if (sendErr) sendErr.style.display = 'none';
    var body = 'wa_phone=' + encodeURIComponent(sel) + '&wa_text=' + encodeURIComponent(text);
    fetch({/literal}'{Text::url('plugin/wifi_otantik_wa_inbox/send')}'{literal}, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: body
    }).then(function(r){ return r.json(); }).then(function(d){
      if (btn) btn.disabled = false;
      if (d.row) woWaAppendRow(d.row);
      if (d.window) woWaRenderWindow(d.window);
      if (d.ok) {
        ta.value = '';
        ta.style.height = 'auto';
      } else if (sendErr) {
        sendErr.textContent = 'Échec : ' + (d.error || 'erreur inconnue');
        sendErr.style.display = 'block';
      }
    }).catch(function(){
      if (btn) btn.disabled = false;
      if (sendErr) {
        sendErr.textContent = 'Erreur réseau. Réessayez.';
        sendErr.style.display = 'block';
      }
    });
  };

  function woWaPoll(){
    if (!sel) return;
    fetch({/literal}'{Text::url('plugin/wifi_otantik_wa_inbox/poll')}'{literal} + '&phone=' + encodeURIComponent(sel) + '&after=' + lastId, {credentials:'same-origin'})
      .then(function(r){ return r.json(); })
      .then(function(d){
        (d.rows || []).forEach(function(row){ woWaAppendRow(row); });
        if (d.window) woWaRenderWindow(d.window);
      })
      .catch(function(){});
  }

  if (sel) {
    woWaRenderWindow({/literal}{$wa_window_json}{literal});
    pollTimer = setInterval(woWaPoll, 8000);
  }

  window.woWaBack = function(){ if (app) app.classList.remove('wo-wa-open'); };

  window.woWaComposeOpen = function(){ document.getElementById('woWaComposeModal').classList.add('on'); };
  window.woWaComposeClose = function(){ document.getElementById('woWaComposeModal').classList.remove('on'); };

  var searchInput = document.getElementById('woWaSearch');
  var searchTimer = null;
  if (searchInput) {
    searchInput.addEventListener('input', function(){
      clearTimeout(searchTimer);
      searchTimer = setTimeout(function(){ woWaReload(searchInput.value.trim()); }, 300);
    });
  }

  var pageSize = {/literal}{$convos_page_size|default:40}{literal};
  var currentOffset = {/literal}{$convos|@count}{literal};
  var currentSearch = '';
  var sel = {/literal}'{$sel|escape:'javascript'}'{literal};

  function escHtml(s){
    return String(s == null ? '' : s).replace(/[&<>"']/g, function(c){
      return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
    });
  }

  function renderRow(c){
    var a = document.createElement('a');
    a.href = {/literal}'{Text::url('plugin/wifi_otantik_wa_inbox')}'{literal} + '&phone=' + encodeURIComponent(c.phone);
    a.className = 'wo-wa-item' + (c.phone === sel ? ' active' : '');
    a.setAttribute('data-phone', c.phone);
    var phoneLine = c.name !== c.phone ? '<span class="wo-wa-item-phone">' + escHtml(c.phone) + '</span>' : '';
    a.innerHTML = '<div class="wo-wa-avatar ' + c.avatar_color + '">' + escHtml(c.avatar_letter) + '</div>' +
      '<div class="wo-wa-item-body">' +
        '<div class="wo-wa-item-top"><span class="wo-wa-item-name">' + escHtml(c.name) + '</span><span class="wo-wa-item-time">' + escHtml(c.ago) + '</span></div>' +
        '<div class="wo-wa-item-bottom"><span class="wo-wa-item-preview">' + (c.direction === 'in' ? '← ' : '') + escHtml(c.preview) + '</span>' + phoneLine + '</div>' +
      '</div>';
    return a;
  }

  window.woWaReload = function(search){
    currentSearch = search || '';
    currentOffset = 0;
    var list = document.getElementById('woWaList');
    list.innerHTML = '<div class="wo-wa-empty" style="height:100%"><i class="fa fa-spinner fa-spin"></i></div>';
    fetch({/literal}'{Text::url('plugin/wifi_otantik_wa_inbox/more')}'{literal} + '&offset=0&q=' + encodeURIComponent(currentSearch) + '&phone=' + encodeURIComponent(sel), {credentials:'same-origin'})
      .then(function(r){ return r.json(); })
      .then(function(d){
        list.innerHTML = '';
        if (!d.rows || !d.rows.length) {
          list.innerHTML = '<div class="wo-wa-empty" style="height:100%"><i class="fa fa-search"></i><span>Aucun résultat</span></div>';
        } else {
          d.rows.forEach(function(c){ list.appendChild(renderRow(c)); });
          currentOffset = d.rows.length;
        }
        document.getElementById('woWaMoreWrap').style.display = d.has_more ? 'block' : 'none';
      })
      .catch(function(){});
  };

  window.woWaLoadMore = function(){
    var btn = document.getElementById('woWaMoreBtn');
    btn.disabled = true;
    btn.textContent = 'Chargement…';
    fetch({/literal}'{Text::url('plugin/wifi_otantik_wa_inbox/more')}'{literal} + '&offset=' + currentOffset + '&q=' + encodeURIComponent(currentSearch) + '&phone=' + encodeURIComponent(sel), {credentials:'same-origin'})
      .then(function(r){ return r.json(); })
      .then(function(d){
        var list = document.getElementById('woWaList');
        (d.rows || []).forEach(function(c){ list.appendChild(renderRow(c)); });
        currentOffset += (d.rows || []).length;
        document.getElementById('woWaMoreWrap').style.display = d.has_more ? 'block' : 'none';
        btn.disabled = false;
        btn.textContent = 'Charger plus';
      })
      .catch(function(){
        btn.disabled = false;
        btn.textContent = 'Charger plus';
      });
  };
})();
</script>
{/literal}
{include file="otantik_shell_foot.tpl"}
