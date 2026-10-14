{include file="otantik_shell_head.tpl"}
{if isset($notify)}
<script>
document.addEventListener('DOMContentLoaded',function(){
  if(window.Swal) Swal.fire({ldelim}icon:'{if $notify_t == "s"}success{else}error{/if}',title:'{if $notify_t == "s"}C’est fait{else}Oups{/if}',text:'{$notify|escape:"javascript"}'{rdelim});
});
</script>
{/if}
<div class="wo-bento">
  <div class="wo-card wo-s5">
    <div class="wo-card-h">
      <div><b>Ajouter un membre de l’équipe</b><div class="wo-muted">Chaque employé a son propre identifiant, avec un accès limité à son rôle.</div></div>
    </div>
    <form method="post" action="{Text::url('plugin/wifi_otantik_staff/create')}" class="wo-staff-form">
      <label>Nom complet<input type="text" name="fullname" required></label>
      <label>Identifiant de connexion<input type="text" name="username" required autocomplete="off"></label>
      <label>Téléphone<input type="text" name="phone"></label>
      <label>Email (optionnel)<input type="email" name="email"></label>
      <label>Mot de passe<input type="password" name="password" minlength="6" required></label>
      <label>Rôle
        <select name="role" required>
          <option value="">Choisir…</option>
          {foreach $roles as $key => $label}
          <option value="{$key}">{$label}</option>
          {/foreach}
        </select>
      </label>
      <div class="wo-staff-roles-hint">
        <b>Gérant</b> : ventes + réseau + finances (lecture) · <b>Vendeur</b> : ventes uniquement ·
        <b>Technicien</b> : réseau uniquement · <b>Comptable</b> : finances complètes (ERP + retraits).
      </div>
      <button type="submit" class="wo-btn wo-btn-primary">Créer le compte</button>
    </form>
  </div>

  <div class="wo-card wo-s7">
    <div class="wo-card-h">
      <div><b>Équipe</b><div class="wo-muted">{$staff|@count} compte(s)</div></div>
    </div>
    {if $staff|@count}
    <div class="wo-table-wrap">
      <table class="wo-table">
        <thead><tr><th>Nom</th><th>Identifiant</th><th>Rôle</th><th>Statut</th><th></th></tr></thead>
        <tbody>
          {foreach $staff as $s}
          <tr>
            <td>{$s.fullname|escape}</td>
            <td>{$s.username|escape}</td>
            <td>{$roles[$s.user_type]|default:$s.user_type|escape}</td>
            <td><span class="wo-chip {if $s.status == 'Active'}ok{else}no{/if}">{if $s.status == 'Active'}Actif{else}Désactivé{/if}</span></td>
            <td>
              <form method="post" action="{Text::url('plugin/wifi_otantik_staff/toggle')}/{$s.id}" style="display:inline">
                <button type="submit" class="wo-btn wo-btn-ghost">{if $s.status == 'Active'}Désactiver{else}Réactiver{/if}</button>
              </form>
            </td>
          </tr>
          {/foreach}
        </tbody>
      </table>
    </div>
    {else}
    <div class="wo-empty"><i class="fa fa-users"></i>Aucun employé pour l’instant</div>
    {/if}
  </div>
</div>

{literal}
<style>
.wo-s5{grid-column:span 5}
.wo-s7{grid-column:span 7}
@media (max-width:900px){.wo-s5,.wo-s7{grid-column:1/-1}}
.wo-staff-form{display:flex;flex-direction:column;gap:10px;margin-top:10px}
.wo-staff-form label{display:flex;flex-direction:column;gap:4px;font-size:12px;color:var(--wo-muted);font-weight:600}
.wo-staff-form input,.wo-staff-form select{border:1px solid var(--wo-line);border-radius:10px;padding:8px 10px;background:var(--wo-card);color:var(--wo-text);font-family:inherit;font-size:13px}
.wo-staff-roles-hint{font-size:11.5px;color:var(--wo-muted);line-height:1.6;background:var(--wo-blue-soft);border-radius:10px;padding:8px 10px}
</style>
{/literal}
{include file="otantik_shell_foot.tpl"}
