{include file="otantik_shell_head.tpl"}
<div class="wo-bento">
  <div class="wo-card wo-s12" id="woRepFilters">
    <div class="wo-card-h">
      <div><b>Filtres</b><div class="wo-muted">Période, type, forfait, méthode, routeur</div></div>
      <button type="button" class="wo-btn wo-btn-primary" id="woRepApply"><i class="fa fa-refresh"></i> Actualiser</button>
    </div>
    <div class="wo-rep-filters-grid">
      <label>Date début<input type="date" id="woRepSd" value="{$default_sd}"></label>
      <label>Heure début<input type="time" id="woRepTs" value="00:00" step="1"></label>
      <label>Date fin<input type="date" id="woRepEd" value="{$default_ed}"></label>
      <label>Heure fin<input type="time" id="woRepTe" value="23:59" step="1"></label>
      <label>Type<select id="woRepTps" multiple size="4"></select></label>
      <label>Forfait<select id="woRepPlns" multiple size="4"></select></label>
      <label>Méthode<select id="woRepMts" multiple size="4"></select></label>
      <label id="woRepRtsWrap">Routeur<select id="woRepRts" multiple size="4"></select></label>
    </div>
  </div>

  <div class="wo-card wo-s3"><b>Répartition par type</b><div class="wo-rep-pie-box"><canvas id="woRepChartType"></canvas></div></div>
  <div class="wo-card wo-s3"><b>Répartition par forfait</b><div class="wo-rep-pie-box"><canvas id="woRepChartPlan"></canvas></div></div>
  <div class="wo-card wo-s3"><b>Répartition par méthode</b><div class="wo-rep-pie-box"><canvas id="woRepChartMethod"></canvas></div></div>
  <div class="wo-card wo-s3"><b>Répartition par routeur</b><div class="wo-rep-pie-box"><canvas id="woRepChartRouter"></canvas></div></div>

  <div class="wo-card wo-s12">
    <div class="wo-card-h">
      <div><b>Revenu par jour</b><div class="wo-muted" id="woRepPeriodLabel">&nbsp;</div></div>
      <div class="wo-big" style="text-align:right"><span id="woRepTotal">0</span> <small>FCFA</small></div>
    </div>
    <div class="wo-rep-line-box"><canvas id="woRepChartLine"></canvas></div>
  </div>

  <div class="wo-card wo-s12">
    <div class="wo-card-h">
      <div><b>Transactions</b><div class="wo-muted" id="woRepCountLabel">&nbsp;</div></div>
      <button type="button" class="wo-btn" id="woRepCsv"><i class="fa fa-download"></i> Télécharger CSV</button>
    </div>
    <div class="wo-rep-table-wrap">
      <table class="wo-table" id="woRepTable">
        <thead><tr><th>Date</th><th>Client</th><th>Type</th><th>Forfait</th><th>Méthode</th><th>Routeur</th><th style="text-align:right">Montant</th></tr></thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

{literal}
<style>
.wo-rep-filters-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-top:10px}
.wo-rep-filters-grid label{display:flex;flex-direction:column;gap:4px;font-size:12px;color:var(--wo-muted);font-weight:600}
.wo-rep-filters-grid input,.wo-rep-filters-grid select{border:1px solid var(--wo-line);border-radius:10px;padding:7px 9px;background:var(--wo-card);color:var(--wo-text);font-family:inherit;font-size:13px}
.wo-rep-pie-box{height:220px;position:relative;margin-top:8px}
.wo-rep-line-box{height:260px;position:relative;margin-top:8px}
.wo-rep-table-wrap{overflow-x:auto;margin-top:8px}
.wo-s12{grid-column:span 12}
@media (max-width:900px){.wo-rep-filters-grid{grid-template-columns:repeat(2,1fr)}}
@media (max-width:560px){.wo-rep-filters-grid{grid-template-columns:1fr}}
</style>
{/literal}

{literal}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
  "use strict";
  var dataUrl = {/literal}{$data_url_js nofilter}{literal};
  var palette = ["#002858","#f05018","#10b981","#fb7185","#f59e0b","#3b82f6","#a855f7","#14b8a6","#64748b","#eab308"];
  var pies = {};
  var lineChart = null;

  function fmt(n){ return new Intl.NumberFormat("fr-FR").format(Math.round(n)) + " FCFA"; }

  function el(tag, attrs){
    var e = document.createElement(tag);
    for (var k in attrs) e[k] = attrs[k];
    return e;
  }

  function fillSelect(select, values, keepSelected){
    var prev = keepSelected ? Array.prototype.map.call(select.selectedOptions, function(o){ return o.value; }) : [];
    select.innerHTML = "";
    values.forEach(function(v){
      var opt = document.createElement("option");
      opt.value = v; opt.textContent = v;
      if (prev.indexOf(v) !== -1) opt.selected = true;
      select.appendChild(opt);
    });
  }

  function selectedValues(select){
    return Array.prototype.map.call(select.selectedOptions, function(o){ return o.value; });
  }

  function buildPie(canvasId, chartKey, payload){
    var ctx = document.getElementById(canvasId).getContext("2d");
    if (pies[chartKey]) pies[chartKey].destroy();
    pies[chartKey] = new Chart(ctx, {
      type: "pie",
      data: {
        labels: payload.labels,
        datasets: [{ data: payload.data, backgroundColor: payload.labels.map(function(_, i){ return palette[i % palette.length]; }) }]
      },
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: "bottom", labels: { boxWidth: 10, font: { size: 11 } } } } }
    });
  }

  function buildLine(payload){
    var ctx = document.getElementById("woRepChartLine").getContext("2d");
    if (lineChart) lineChart.destroy();
    lineChart = new Chart(ctx, {
      type: "line",
      data: {
        labels: payload.labels,
        datasets: [{ label: "Revenu", data: payload.data, borderColor: "#002858", backgroundColor: "rgba(0,40,88,.08)", fill: true, tension: 0.25, pointRadius: 3 }]
      },
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
  }

  function csvEscape(v){
    v = String(v == null ? "" : v);
    if (/[",\n]/.test(v)) return '"' + v.replace(/"/g, '""') + '"';
    return v;
  }

  var lastRows = [];

  function downloadCsv(){
    var header = ["Date","Client","Type","Forfait","Methode","Routeur","Montant"];
    var lines = [header.join(",")];
    lastRows.forEach(function(r){
      lines.push([r.when, r.who, r.type, r.label, r.method, r.routers, r.amount].map(csvEscape).join(","));
    });
    var blob = new Blob([lines.join("\n")], { type: "text/csv;charset=utf-8;" });
    var url = URL.createObjectURL(blob);
    var a = document.createElement("a");
    a.href = url; a.download = "rapport-" + document.getElementById("woRepSd").value + "-au-" + document.getElementById("woRepEd").value + ".csv";
    document.body.appendChild(a); a.click(); document.body.removeChild(a);
    URL.revokeObjectURL(url);
  }

  function load(){
    var sd = document.getElementById("woRepSd").value;
    var ed = document.getElementById("woRepEd").value;
    var ts = (document.getElementById("woRepTs").value || "00:00") + ":00";
    var te = (document.getElementById("woRepTe").value || "23:59") + ":59";
    var params = new URLSearchParams();
    params.set("sd", sd); params.set("ed", ed); params.set("ts", ts); params.set("te", te);
    selectedValues(document.getElementById("woRepTps")).forEach(function(v){ params.append("tps[]", v); });
    selectedValues(document.getElementById("woRepPlns")).forEach(function(v){ params.append("plns[]", v); });
    selectedValues(document.getElementById("woRepMts")).forEach(function(v){ params.append("mts[]", v); });
    selectedValues(document.getElementById("woRepRts")).forEach(function(v){ params.append("rts[]", v); });

    fetch(dataUrl + "&" + params.toString(), { credentials: "same-origin" })
      .then(function(r){ return r.json(); })
      .then(function(d){
        if (!d.ok) return;
        fillSelect(document.getElementById("woRepTps"), d.options.types, true);
        fillSelect(document.getElementById("woRepPlns"), d.options.plans, true);
        fillSelect(document.getElementById("woRepMts"), d.options.methods, true);
        fillSelect(document.getElementById("woRepRts"), d.options.routers, true);
        // Only one router to filter by is no filter at all - hide the control.
        document.getElementById("woRepRtsWrap").hidden = d.options.routers.length <= 1;

        buildPie("woRepChartType", "type", d.charts.type);
        buildPie("woRepChartPlan", "plan", d.charts.plan);
        buildPie("woRepChartMethod", "method", d.charts.method);
        buildPie("woRepChartRouter", "router", d.charts.router);
        buildLine(d.timeline);

        document.getElementById("woRepTotal").textContent = new Intl.NumberFormat("fr-FR").format(Math.round(d.total_amount));
        document.getElementById("woRepPeriodLabel").textContent = sd + " → " + ed;
        document.getElementById("woRepCountLabel").textContent = d.rows_total + " transaction(s)" + (d.rows_total > d.rows.length ? " (" + d.rows.length + " affichées)" : "");

        lastRows = d.rows;
        var tbody = document.querySelector("#woRepTable tbody");
        tbody.innerHTML = "";
        d.rows.forEach(function(r){
          var tr = document.createElement("tr");
          [r.when, r.who || "-", r.type, r.label, r.method, r.routers, fmt(r.amount)].forEach(function(v, i){
            var td = document.createElement("td");
            td.textContent = v;
            if (i === 6) td.style.textAlign = "right";
            tr.appendChild(td);
          });
          tbody.appendChild(tr);
        });
      });
  }

  document.getElementById("woRepApply").addEventListener("click", load);
  document.getElementById("woRepCsv").addEventListener("click", downloadCsv);
  ["woRepTps","woRepPlns","woRepMts","woRepRts"].forEach(function(id){
    document.getElementById(id).addEventListener("change", load);
  });
  load();
})();
</script>
{/literal}
{include file="otantik_shell_foot.tpl"}
