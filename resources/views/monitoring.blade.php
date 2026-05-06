<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Flame + Power Monitor — Laravel</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=DM+Mono:wght@300;400;500&display=swap" rel="stylesheet">

<style>
:root {
  --bg:        #05080f;
  --surface:   #0b1120;
  --surface2:  #111b2e;
  --border:    #1c2f4a;
  --safe:      #00ff88;
  --fire:      #ff3d00;
  --fire2:     #ff8c00;
  --warn:      #ffcc00;
  --blue:      #0af;
  --purple:    #a855f7;
  --cyan:      #06b6d4;
  --text:      #b8cfe0;
  --dim:       #3d5a78;
  --font-head: 'Orbitron', monospace;
  --font-body: 'DM Mono', monospace;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

html, body {
  height: 100%;
  background: var(--bg);
  color: var(--text);
  font-family: var(--font-body);
  overflow-x: hidden;
}

body::after {
  content: '';
  position: fixed; inset: 0;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
  pointer-events: none;
  z-index: 9998;
  opacity: 0.6;
}

body.fire-alert { animation: bg-flash 0.5s ease-in-out infinite alternate; }
@keyframes bg-flash {
  from { background: #05080f; }
  to   { background: #150500; }
}

/* ── HEADER ── */
header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 28px;
  height: 60px;
  border-bottom: 1px solid var(--border);
  background: linear-gradient(90deg, #0b1828 0%, var(--bg) 100%);
  position: sticky; top: 0;
  z-index: 100;
}

.brand { display: flex; align-items: center; gap: 12px; }

.brand-icon {
  font-size: 22px;
  width: 38px; height: 38px;
  display: flex; align-items: center; justify-content: center;
  border: 1px solid var(--border);
  border-radius: 6px;
  background: var(--surface);
  transition: border-color 0.3s, box-shadow 0.3s;
}

body.fire-alert .brand-icon {
  border-color: var(--fire);
  box-shadow: 0 0 20px rgba(255,61,0,0.5);
}

.brand h1 {
  font-family: var(--font-head);
  font-size: 14px;
  font-weight: 700;
  letter-spacing: 4px;
  color: var(--blue);
}
.brand h1 span { color: var(--dim); font-weight: 400; }

.hdr-right {
  display: flex; align-items: center; gap: 16px;
  font-size: 11px; letter-spacing: 1px;
}

.conn-badge {
  display: flex; align-items: center; gap: 7px;
  padding: 5px 12px;
  border: 1px solid var(--border);
  border-radius: 2px;
  background: var(--surface);
  text-transform: uppercase;
}

.conn-dot {
  width: 7px; height: 7px;
  border-radius: 50%;
  background: var(--dim);
  transition: background 0.3s, box-shadow 0.3s;
}
.conn-dot.online     { background: var(--safe); box-shadow: 0 0 8px var(--safe); animation: pulse 1.5s ease-in-out infinite; }
.conn-dot.offline    { background: var(--fire); box-shadow: 0 0 8px var(--fire); }
.conn-dot.connecting { background: var(--warn); box-shadow: 0 0 8px var(--warn); animation: pulse 0.5s steps(1) infinite; }

@keyframes pulse { 50% { opacity: 0.3; } }

#conn-label { color: var(--text); }
#uptime { color: var(--dim); font-size: 11px; }

.device-select-wrap {
  display: flex; align-items: center; gap: 8px;
}
.device-select-wrap label {
  font-size: 10px; letter-spacing: 2px; text-transform: uppercase; color: var(--dim);
}
#device-select {
  background: var(--surface);
  border: 1px solid var(--border);
  color: var(--text);
  font-family: var(--font-body);
  font-size: 11px;
  padding: 5px 10px;
  outline: none;
  cursor: pointer;
}
#device-select:focus { border-color: var(--blue); }

.refresh-btn {
  background: transparent;
  border: 1px solid var(--border);
  color: var(--dim);
  font-family: var(--font-body);
  font-size: 11px;
  letter-spacing: 1px;
  padding: 5px 12px;
  cursor: pointer;
  text-transform: uppercase;
  transition: all 0.2s;
}
.refresh-btn:hover { border-color: var(--blue); color: var(--blue); }

/* ── MAIN ── */
main {
  max-width: 1400px;
  margin: 0 auto;
  padding: 24px 20px 48px;
}

/* ── FIRE HERO ── */
.fire-hero {
  position: relative;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 2px;
  padding: 32px 36px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 40px;
  overflow: hidden;
  transition: border-color 0.3s;
}

.fire-hero::before {
  content: '';
  position: absolute; inset: 0;
  background: radial-gradient(ellipse at 20% 50%, rgba(0,170,255,0.06) 0%, transparent 70%);
  pointer-events: none;
  transition: background 0.5s;
}

body.fire-alert .fire-hero { border-color: var(--fire); }
body.fire-alert .fire-hero::before {
  background: radial-gradient(ellipse at 20% 50%, rgba(255,61,0,0.12) 0%, transparent 70%);
}

.flame-emoji {
  font-size: 80px;
  line-height: 1;
  filter: grayscale(1) brightness(0.4);
  transition: filter 0.4s, transform 0.4s;
  flex-shrink: 0;
}
body.fire-alert .flame-emoji {
  filter: grayscale(0) brightness(1);
  animation: flicker 0.3s ease-in-out infinite alternate;
  transform: scale(1.1);
}
@keyframes flicker {
  from { transform: scale(1.08) rotate(-2deg); }
  to   { transform: scale(1.12) rotate(2deg); }
}

.hero-info { flex: 1; }

.status-big {
  font-family: var(--font-head);
  font-size: 42px; font-weight: 900;
  letter-spacing: 2px;
  color: var(--safe);
  transition: color 0.3s;
  line-height: 1;
  margin-bottom: 8px;
}
body.fire-alert .status-big { color: var(--fire); }

.status-sub {
  font-size: 13px;
  color: var(--dim);
  letter-spacing: 0.5px;
  margin-bottom: 20px;
}

.adc-bar-wrap { margin-top: 8px; }
.adc-bar-label {
  display: flex; justify-content: space-between;
  font-size: 10px; letter-spacing: 2px; text-transform: uppercase;
  color: var(--dim); margin-bottom: 6px;
}
.adc-bar {
  height: 8px;
  background: var(--surface2);
  border: 1px solid var(--border);
  position: relative; overflow: hidden;
}
.adc-fill {
  height: 100%;
  width: 0%;
  background: var(--safe);
  transition: width 0.5s ease, background 0.3s;
}

.hero-stats {
  display: flex; flex-direction: column; gap: 12px;
  min-width: 200px;
  border-left: 1px solid var(--border);
  padding-left: 32px;
}

.hstat {
  display: flex; flex-direction: column; gap: 2px;
}
.hstat-label {
  font-size: 9px; letter-spacing: 2px;
  text-transform: uppercase; color: var(--dim);
}
.hstat-val {
  font-family: var(--font-head);
  font-size: 22px; font-weight: 700;
  color: var(--blue);
}

/* ── SECTION TITLE ── */
.section-title {
  font-family: var(--font-head);
  font-size: 11px; font-weight: 700;
  letter-spacing: 4px;
  text-transform: uppercase;
  color: var(--dim);
  border-left: 2px solid var(--blue);
  padding-left: 10px;
  margin-bottom: 16px;
  margin-top: 28px;
}

/* ── GRID ── */
.grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
.grid4 { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; margin-bottom: 16px; }

@media(max-width:900px) { .grid4 { grid-template-columns: repeat(2,1fr); } }
@media(max-width:600px) {
  .grid2, .grid4 { grid-template-columns: 1fr; }
  .fire-hero { flex-direction: column; gap: 20px; }
  .hero-stats { border-left: none; padding-left: 0; border-top: 1px solid var(--border); padding-top: 20px; flex-direction: row; flex-wrap: wrap; }
}

/* ── CARD ── */
.card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 2px;
  padding: 20px 22px;
  position: relative;
  overflow: hidden;
  transition: border-color 0.3s;
}
.card::before {
  content: '';
  position: absolute; top: 0; left: 0; right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, var(--blue), transparent);
  opacity: 0.4;
}
.card.no-data { opacity: 0.45; }

.card-label {
  font-size: 9px; letter-spacing: 2px;
  text-transform: uppercase; color: var(--dim);
  margin-bottom: 8px;
}
.card-val {
  font-family: var(--font-head);
  font-size: 32px; font-weight: 700;
  color: var(--blue);
  line-height: 1;
}
.card-unit {
  font-size: 13px; color: var(--dim);
  margin-left: 3px;
}
.card-sub {
  font-size: 11px; color: var(--dim);
  margin-top: 6px;
}

/* ── PZEM SECTION ── */
.pzem-header {
  display: flex; align-items: center; gap: 12px;
  margin-bottom: 16px;
}

.pzem-status-badge {
  font-family: var(--font-head);
  font-size: 10px; font-weight: 700;
  letter-spacing: 2px;
  padding: 5px 12px;
  border: 1px solid;
}
.pzem-status-badge.ok   { border-color: var(--safe);   color: var(--safe);   background: rgba(0,255,136,0.06); }
.pzem-status-badge.warn { border-color: var(--warn);   color: var(--warn);   background: rgba(255,204,0,0.06); }
.pzem-status-badge.bad  { border-color: var(--fire);   color: var(--fire);   background: rgba(255,61,0,0.06);  }

.deviasi-badge {
  font-size: 11px;
  font-family: var(--font-head);
  padding: 4px 10px;
  border: 1px solid;
}
.deviasi-badge.ok   { border-color: var(--safe); color: var(--safe); }
.deviasi-badge.warn { border-color: var(--warn); color: var(--warn); }
.deviasi-badge.bad  { border-color: var(--fire); color: var(--fire); }

.volt-bar-wrap { margin-top: 14px; }
.volt-bar-label { display: flex; justify-content: space-between; font-size: 10px; letter-spacing: 1.5px; color: var(--dim); margin-bottom: 6px; }
.volt-bar { height: 6px; background: var(--surface2); border: 1px solid var(--border); overflow: hidden; }
.volt-bar-fill { height: 100%; width: 0%; background: var(--cyan); transition: width 0.6s ease, background 0.3s; }

/* ── CHARTS ── */
.chart-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 2px;
  padding: 20px 22px;
  margin-bottom: 16px;
}
.chart-header {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 16px;
}
.chart-title {
  font-family: var(--font-head);
  font-size: 11px; font-weight: 700; letter-spacing: 3px;
  color: var(--text);
}
.chart-actions { display: flex; gap: 8px; }
.chart-btn {
  background: transparent;
  border: 1px solid var(--border);
  color: var(--dim);
  font-family: var(--font-body);
  font-size: 10px; letter-spacing: 1px;
  padding: 4px 10px;
  cursor: pointer;
  text-transform: uppercase;
  transition: all 0.2s;
}
.chart-btn:hover { border-color: var(--blue); color: var(--blue); }
.chart-canvas { width: 100%; height: 200px; }

/* ── LOG TABLE ── */
.log-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 2px;
  padding: 0;
  overflow: hidden;
  margin-bottom: 20px;
}
.log-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid var(--border);
}
.log-title {
  font-family: var(--font-head);
  font-size: 11px; font-weight: 700; letter-spacing: 3px; color: var(--text);
}
.log-actions { display: flex; gap: 8px; }
.log-table-wrap { overflow-y: auto; max-height: 300px; }

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 12px;
}
thead tr {
  background: var(--surface2);
  border-bottom: 1px solid var(--border);
  position: sticky; top: 0;
}
thead th {
  padding: 10px 16px;
  text-align: left;
  font-size: 9px; letter-spacing: 2px;
  text-transform: uppercase;
  color: var(--dim);
  font-weight: 400;
}
tbody tr {
  border-bottom: 1px solid rgba(28,47,74,0.5);
  transition: background 0.15s;
}
tbody tr:hover { background: var(--surface2); }
tbody tr.fire-row { background: rgba(255,61,0,0.06); }
tbody td { padding: 9px 16px; color: var(--text); }

/* ── TOAST ── */
.toast {
  position: fixed; bottom: 28px; right: 28px;
  background: var(--surface2);
  border: 1px solid var(--border);
  border-left: 3px solid var(--blue);
  color: var(--text);
  font-size: 12px; letter-spacing: 0.5px;
  padding: 12px 20px;
  z-index: 9999;
  opacity: 0;
  transform: translateY(10px);
  transition: opacity 0.3s, transform 0.3s;
  pointer-events: none;
  max-width: 340px;
}
.toast.show { opacity: 1; transform: translateY(0); }
.toast.danger { border-left-color: var(--fire); }
.toast.warn   { border-left-color: var(--warn); }

/* ── LAST UPDATED ── */
.last-updated {
  font-size: 10px; color: var(--dim); letter-spacing: 1px;
  text-align: right; padding: 8px 0;
}

/* ── POLLING STATUS ── */
.poll-indicator {
  display: flex; align-items: center; gap: 6px;
  font-size: 10px; color: var(--dim); letter-spacing: 1px;
}
.poll-spinner {
  width: 10px; height: 10px;
  border: 1px solid var(--dim);
  border-top-color: var(--blue);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  opacity: 0;
  transition: opacity 0.2s;
}
.poll-spinner.active { opacity: 1; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
</head>
<body>

<header>
  <div class="brand">
    <div class="brand-icon">🔥</div>
    <h1>FLAME MONITOR <span>/ LARAVEL API</span></h1>
  </div>
  <div class="hdr-right">
    <div class="poll-indicator">
      <div class="poll-spinner" id="poll-spinner"></div>
      <span id="poll-label">POLLING</span>
    </div>
    <div class="device-select-wrap">
      <label>Device</label>
      <select id="device-select">
        <option value="esp32-flame-01">esp32-flame-01</option>
        <option value="esp32-flame-02">esp32-flame-02</option>
      </select>
    </div>
    <button class="refresh-btn" onclick="fetchLatest()">↺ REFRESH</button>
    <div class="conn-badge">
      <div class="conn-dot connecting" id="conn-dot"></div>
      <span id="conn-label">MENGHUBUNGKAN</span>
    </div>
    <span id="uptime">—</span>
  </div>
</header>

<main>

  <!-- ── FIRE HERO ── -->
  <div class="fire-hero">
    <div class="flame-emoji">🔥</div>
    <div class="hero-info">
      <div class="status-big" id="status-big">MEMUAT…</div>
      <div class="status-sub" id="status-sub">Mengambil data dari API…</div>
      <div class="adc-bar-wrap">
        <div class="adc-bar-label">
          <span>ADC FLAME RAW</span>
          <span id="adc-val">— / 4095</span>
        </div>
        <div class="adc-bar">
          <div class="adc-fill" id="adc-fill"></div>
        </div>
      </div>
    </div>
    <div class="hero-stats">
      <div class="hstat">
        <div class="hstat-label">Total Record</div>
        <div class="hstat-val" id="stat-total">—</div>
      </div>
      <div class="hstat">
        <div class="hstat-label">Fire Events</div>
        <div class="hstat-val" id="stat-fire" style="color:var(--fire)">—</div>
      </div>
      <div class="hstat">
        <div class="hstat-label">Diperbarui</div>
        <div class="hstat-val" id="stat-last" style="font-size:14px;color:var(--dim)">—</div>
      </div>
    </div>
  </div>

  <!-- ── POWER SECTION ── -->
  <div class="section-title">⚡ POWER MONITOR — PZEM-017</div>

  <div class="pzem-header">
    <span class="pzem-status-badge" id="pzem-badge">— MENUNGGU DATA —</span>
    <span class="deviasi-badge" id="deviasi-badge">—</span>
  </div>

  <div class="grid4">
    <div class="card no-data" id="card-voltage">
      <div class="card-label">Tegangan</div>
      <div class="card-val"><span id="pzem-voltage">—</span><span class="card-unit">V</span></div>
      <div class="card-sub">DC Voltage</div>
    </div>
    <div class="card no-data" id="card-current">
      <div class="card-label">Arus</div>
      <div class="card-val"><span id="pzem-current">—</span><span class="card-unit">A</span></div>
      <div class="card-sub">DC Current</div>
    </div>
    <div class="card no-data" id="card-power">
      <div class="card-label">Daya</div>
      <div class="card-val"><span id="pzem-power">—</span><span class="card-unit">W</span></div>
      <div class="card-sub">Active Power</div>
    </div>
    <div class="card no-data" id="card-energy">
      <div class="card-label">Energi</div>
      <div class="card-val"><span id="pzem-energy">—</span><span class="card-unit">Wh</span></div>
      <div class="card-sub">Accumulated</div>
    </div>
  </div>

  <div class="card" style="margin-bottom:16px;">
    <div class="volt-bar-wrap">
      <div class="volt-bar-label">
        <span>TEGANGAN BAR (0 – 15 V)</span>
        <span id="volt-bar-info">— V / 15 V</span>
      </div>
      <div class="volt-bar">
        <div class="volt-bar-fill" id="volt-bar-fill"></div>
      </div>
    </div>
  </div>

  <!-- ── CHARTS ── -->
  <div class="section-title">📈 CHARTS</div>

  <div class="grid2">
    <div class="chart-card">
      <div class="chart-header">
        <div class="chart-title">ADC FLAME RAW</div>
        <div class="chart-actions">
          <button class="chart-btn" onclick="clearChart()">CLEAR</button>
          <button class="chart-btn" onclick="exportCSV()">CSV</button>
        </div>
      </div>
      <canvas id="adc-chart" class="chart-canvas"></canvas>
    </div>
    <div class="chart-card">
      <div class="chart-header">
        <div class="chart-title">POWER — V & W</div>
        <div class="chart-actions">
          <button class="chart-btn" onclick="clearPowerChart()">CLEAR</button>
          <button class="chart-btn" onclick="exportPowerCSV()">CSV</button>
        </div>
      </div>
      <canvas id="power-chart" class="chart-canvas"></canvas>
    </div>
  </div>

  <!-- ── LOG TABLES ── -->
  <div class="section-title">📋 LOG</div>

  <div class="grid2">
    <!-- Flame Log -->
    <div class="log-card">
      <div class="log-header">
        <div class="log-title">LOG API / FLAME</div>
        <div class="log-actions">
          <button class="chart-btn" onclick="clearLog()">CLEAR</button>
          <button class="chart-btn" onclick="exportCSV()">CSV</button>
        </div>
      </div>
      <div class="log-table-wrap">
        <table>
          <thead>
            <tr>
              <th>Waktu</th>
              <th>ADC Raw</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody id="log-body">
            <tr><td colspan="3" style="color:var(--dim);text-align:center;padding:20px 0;">Menunggu data…</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Power Log -->
    <div class="log-card">
      <div class="log-header">
        <div class="log-title">LOG POWER</div>
        <div class="log-actions">
          <button class="chart-btn" onclick="clearPowerLog()">CLEAR</button>
          <button class="chart-btn" onclick="exportPowerCSV()">CSV</button>
        </div>
      </div>
      <div class="log-table-wrap">
        <table>
          <thead>
            <tr>
              <th>Waktu</th>
              <th>V</th>
              <th>A</th>
              <th>W</th>
              <th>Wh</th>
            </tr>
          </thead>
          <tbody id="power-log-body">
            <tr><td colspan="5" style="color:var(--dim);text-align:center;padding:20px 0;">Menunggu data…</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="last-updated">Terakhir diperbarui: <span id="last-updated-ts">—</span></div>

</main>

<div class="toast" id="toast"></div>

<script>
// ── CONFIG ──────────────────────────────────────
const API_BASE    = '{{ url("/api/sensor-data") }}';
const POLL_MS     = 5000;   // polling interval (ms)
const MAX_CHART   = 48;
const V_NOMINAL   = 9;      // nominal voltage (V)
const FLAME_THR   = 500;    // threshold ADC for fire detection

// ── STATE ───────────────────────────────────────
let logRows   = [];
let powerRows = [];
let adcLabels = [], adcData = [];
let powerLabels = [], powerVData = [], powerWData = [];
let totalMsg = 0, totalFire = 0;
let pollTimer = null;
let startTime = Date.now();
let lastId = null;

// ── UPTIME TICKER ───────────────────────────────
setInterval(() => {
  const s = Math.floor((Date.now() - startTime) / 1000);
  const h = String(Math.floor(s / 3600)).padStart(2,'0');
  const m = String(Math.floor(s % 3600 / 60)).padStart(2,'0');
  const sec = String(s % 60).padStart(2,'0');
  document.getElementById('uptime').textContent = `UP ${h}:${m}:${sec}`;
}, 1000);

// ── CHART SETUP ─────────────────────────────────
const chartCfgBase = {
  type: 'line',
  options: {
    responsive: true,
    maintainAspectRatio: false,
    animation: false,
    plugins: { legend: { display: false }, tooltip: {
      backgroundColor: '#0b1120',
      borderColor: '#1c2f4a',
      borderWidth: 1,
      titleColor: '#b8cfe0',
      bodyColor: '#3d5a78',
      titleFont: { family: 'DM Mono' },
      bodyFont: { family: 'DM Mono' },
    }},
    scales: {
      x: { grid: { color: '#1c2f4a' }, ticks: { color: '#3d5a78', font: { size: 9, family: 'DM Mono' }, maxTicksLimit: 8 } },
      y: { grid: { color: '#1c2f4a' }, ticks: { color: '#3d5a78', font: { size: 9, family: 'DM Mono' } } },
    },
  }
};

// ADC Chart
const adcChart = new Chart(document.getElementById('adc-chart'), {
  ...chartCfgBase,
  data: {
    labels: adcLabels,
    datasets: [{
      label: 'ADC Raw',
      data: adcData,
      borderColor: '#00ff88',
      backgroundColor: 'rgba(0,255,136,0.06)',
      borderWidth: 1.5,
      pointRadius: 0,
      fill: true,
      tension: 0.4,
    }]
  },
});

// Power Chart
const pwrChart = new Chart(document.getElementById('power-chart'), {
  ...chartCfgBase,
  data: {
    labels: powerLabels,
    datasets: [
      {
        label: 'Voltage (V)',
        data: powerVData,
        borderColor: '#06b6d4',
        backgroundColor: 'transparent',
        borderWidth: 1.5,
        pointRadius: 0,
        tension: 0.4,
        yAxisID: 'yV',
      },
      {
        label: 'Power (W)',
        data: powerWData,
        borderColor: '#a855f7',
        backgroundColor: 'rgba(168,85,247,0.06)',
        borderWidth: 1.5,
        pointRadius: 0,
        fill: true,
        tension: 0.4,
        yAxisID: 'yW',
      }
    ]
  },
  options: {
    ...chartCfgBase.options,
    scales: {
      x:  { grid: { color: '#1c2f4a' }, ticks: { color: '#3d5a78', font: { size: 9, family: 'DM Mono' }, maxTicksLimit: 8 } },
      yV: { grid: { color: '#1c2f4a' }, ticks: { color: '#06b6d4', font: { size: 9, family: 'DM Mono' } }, position: 'left' },
      yW: { grid: { display: false },   ticks: { color: '#a855f7', font: { size: 9, family: 'DM Mono' } }, position: 'right' },
    }
  }
});

// ── API FETCH: LATEST ────────────────────────────
async function fetchLatest() {
  const deviceId = document.getElementById('device-select').value;
  const spinner  = document.getElementById('poll-spinner');
  spinner.classList.add('active');

  try {
    const res  = await fetch(`${API_BASE}/latest?device_id=${deviceId}`, {
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    });

    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const json = await res.json();

    if (!json.success) {
      setConnStatus('offline');
      document.getElementById('status-big').textContent = 'OFFLINE';
      document.getElementById('status-sub').textContent = json.message || 'Tidak ada data.';
      return;
    }

    setConnStatus('online');
    processRecord(json.data);
    document.getElementById('last-updated-ts').textContent = new Date().toLocaleString('id-ID');

  } catch (err) {
    setConnStatus('offline');
    toast('Gagal mengambil data: ' + err.message, 'danger');
  } finally {
    spinner.classList.remove('active');
  }
}

// ── API FETCH: HISTORY (untuk chart & log) ───────
async function fetchHistory() {
  const deviceId = document.getElementById('device-select').value;

  try {
    const res  = await fetch(`${API_BASE}?device_id=${deviceId}&limit=48`, {
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    });

    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const json = await res.json();

    if (!json.success || !json.data.length) return;

    // Hanya proses record baru
    const newRecords = lastId
      ? json.data.filter(r => r.id > lastId)
      : json.data.slice().reverse();  // urut dari lama ke baru untuk chart

    newRecords.forEach(r => appendToCharts(r));

    if (json.data.length) {
      lastId = Math.max(...json.data.map(r => r.id));
      // Stats
      totalMsg  = json.count;
      totalFire = json.data.filter(r => r.fire).length;
      document.getElementById('stat-total').textContent = totalMsg;
      document.getElementById('stat-fire').textContent  = totalFire;
    }

  } catch (err) {
    console.error('History fetch error:', err);
  }
}

// ── PROSES RECORD TERBARU (untuk hero panel) ─────
function processRecord(r) {
  const raw    = parseInt(r.flame_raw);
  const isFire = r.fire === true || r.fire === 1;
  const ts     = new Date(r.created_at).toLocaleTimeString('id-ID');

  document.getElementById('status-big').textContent = isFire ? 'API!' : 'AMAN';
  document.getElementById('status-sub').textContent = isFire
    ? `⚠ Api terdeteksi! ADC=${raw} (di bawah threshold ${FLAME_THR})`
    : `Tidak ada api. ADC=${raw} — margin ${raw - FLAME_THR} di atas threshold`;

  const pct  = (raw / 4095 * 100).toFixed(1);
  const fill = document.getElementById('adc-fill');
  fill.style.width      = pct + '%';
  fill.style.background = isFire ? 'var(--fire)' : raw < FLAME_THR * 1.5 ? 'var(--warn)' : 'var(--safe)';
  document.getElementById('adc-val').textContent = `${raw} / 4095`;

  document.getElementById('stat-last').textContent = ts;

  if (isFire) {
    document.body.classList.add('fire-alert');
    adcChart.data.datasets[0].borderColor     = '#ff3d00';
    adcChart.data.datasets[0].backgroundColor = 'rgba(255,61,0,0.08)';
  } else {
    document.body.classList.remove('fire-alert');
    adcChart.data.datasets[0].borderColor     = '#00ff88';
    adcChart.data.datasets[0].backgroundColor = 'rgba(0,255,136,0.06)';
  }
  adcChart.update('none');

  // Power fields
  if (r.voltage !== null && r.voltage !== undefined) {
    updatePowerDisplay(r);
  }

  if (isFire && !window._wasFire) toast('⚠ API TERDETEKSI!', 'danger');
  if (!isFire && window._wasFire) toast('✓ Kondisi kembali aman');
  window._wasFire = isFire;
}

// ── APPEND RECORD KE CHART & LOG ─────────────────
function appendToCharts(r) {
  const raw    = parseInt(r.flame_raw);
  const isFire = r.fire === true || r.fire === 1;
  const ts     = new Date(r.created_at).toLocaleTimeString('id-ID');

  if (adcLabels.length >= MAX_CHART) { adcLabels.shift(); adcData.shift(); }
  adcLabels.push(ts);
  adcData.push(raw);
  adcChart.update('none');

  addFlameLog(ts, raw, isFire, r.id);

  if (r.voltage !== null && r.voltage !== undefined) {
    const v = parseFloat(r.voltage);
    const a = parseFloat(r.current);
    const w = parseFloat(r.power);
    const e = parseFloat(r.energy);
    const ok = r.voltage_ok === true || r.voltage_ok === 1;

    if (powerLabels.length >= MAX_CHART) { powerLabels.shift(); powerVData.shift(); powerWData.shift(); }
    powerLabels.push(ts);
    powerVData.push(isNaN(v) ? null : parseFloat(v.toFixed(2)));
    powerWData.push(isNaN(w) ? null : parseFloat(w.toFixed(1)));
    pwrChart.update('none');

    addPowerLog(ts, v, a, w, e, ok, r.id);
  }
}

// ── POWER DISPLAY UPDATE ─────────────────────────
function updatePowerDisplay(r) {
  const voltage = parseFloat(r.voltage);
  const current = parseFloat(r.current);
  const power   = parseFloat(r.power);
  const energy  = parseFloat(r.energy);
  const devPct  = parseFloat(r.deviasi_pct);
  const voltOk  = r.voltage_ok === true || r.voltage_ok === 1;

  const vnom  = V_NOMINAL;
  const vlow  = vnom * (7.5 / 9.0);
  const vhigh = vnom * (10.5 / 9.0);

  ['card-voltage','card-current','card-power','card-energy'].forEach(id =>
    document.getElementById(id).classList.remove('no-data')
  );

  document.getElementById('pzem-voltage').textContent = isNaN(voltage) ? '—' : voltage.toFixed(2);
  document.getElementById('pzem-current').textContent = isNaN(current) ? '—' : current.toFixed(2);
  document.getElementById('pzem-power').textContent   = isNaN(power)   ? '—' : power.toFixed(1);
  document.getElementById('pzem-energy').textContent  = isNaN(energy)  ? '—' : energy.toFixed(0);

  const vBarPct = Math.min(voltage / 15 * 100, 100).toFixed(1);
  const vFill   = document.getElementById('volt-bar-fill');
  vFill.style.width = vBarPct + '%';

  let vColor = '#06b6d4';
  if (voltage < vlow)  vColor = '#ff3d00';
  if (voltage > vhigh) vColor = '#ffcc00';
  vFill.style.background = vColor;
  document.getElementById('volt-bar-info').textContent = `${voltage.toFixed(2)} V / 15 V`;

  if (!isNaN(devPct)) {
    const badge = document.getElementById('deviasi-badge');
    const sign  = devPct >= 0 ? '+' : '';
    badge.textContent = `${sign}${devPct.toFixed(1)}%`;
    badge.className   = 'deviasi-badge ' + (Math.abs(devPct) <= 15 ? 'ok' : Math.abs(devPct) <= 25 ? 'warn' : 'bad');
  }

  const pzemBadge = document.getElementById('pzem-badge');
  if (voltage < vlow) {
    pzemBadge.className   = 'pzem-status-badge bad';
    pzemBadge.textContent = '⚠ VOLTAGE DROP';
  } else if (voltage > vhigh) {
    pzemBadge.className   = 'pzem-status-badge warn';
    pzemBadge.textContent = '⚠ OVERVOLTAGE';
  } else {
    pzemBadge.className   = 'pzem-status-badge ok';
    pzemBadge.textContent = '✓ TEGANGAN NORMAL';
  }
}

// ── FLAME LOG TABLE ─────────────────────────────
const _seenFlameIds = new Set();
function addFlameLog(ts, raw, fire, id) {
  if (id && _seenFlameIds.has(id)) return;
  if (id) _seenFlameIds.add(id);

  logRows.unshift({ ts, raw, fire });
  if (logRows.length > 300) logRows.pop();

  const tbody = document.getElementById('log-body');
  if (tbody.children.length === 1 && tbody.children[0].querySelector('[colspan]')) {
    tbody.innerHTML = '';
  }
  const tr = document.createElement('tr');
  if (fire) tr.classList.add('fire-row');
  tr.innerHTML = `
    <td>${ts}</td>
    <td style="color:${fire ? 'var(--fire)' : 'var(--safe)'};font-weight:500;">${raw}</td>
    <td>${fire ? '🔥 API' : '✓ Aman'}</td>
  `;
  tbody.insertBefore(tr, tbody.firstChild);
  if (tbody.children.length > 80) tbody.removeChild(tbody.lastChild);
}

// ── POWER LOG TABLE ─────────────────────────────
const _seenPowerIds = new Set();
function addPowerLog(ts, v, a, w, wh, ok, id) {
  if (id && _seenPowerIds.has(id)) return;
  if (id) _seenPowerIds.add(id);

  powerRows.unshift({ ts, v, a, w, wh, ok });
  if (powerRows.length > 300) powerRows.pop();

  const tbody = document.getElementById('power-log-body');
  if (tbody.children.length === 1 && tbody.children[0].querySelector('[colspan]')) {
    tbody.innerHTML = '';
  }
  const vColor = ok ? 'var(--cyan)' : 'var(--fire)';
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td>${ts}</td>
    <td style="color:${vColor};font-weight:500;">${isNaN(v)?'—':parseFloat(v).toFixed(2)}</td>
    <td style="color:var(--warn);">${isNaN(a)?'—':parseFloat(a).toFixed(2)}</td>
    <td style="color:var(--purple);">${isNaN(w)?'—':parseFloat(w).toFixed(1)}</td>
    <td style="color:var(--safe);">${isNaN(wh)?'—':parseFloat(wh).toFixed(0)}</td>
  `;
  tbody.insertBefore(tr, tbody.firstChild);
  if (tbody.children.length > 80) tbody.removeChild(tbody.lastChild);
}

// ── CONN STATUS ─────────────────────────────────
function setConnStatus(status) {
  const dot   = document.getElementById('conn-dot');
  const label = document.getElementById('conn-label');
  dot.className = 'conn-dot ' + status;
  label.textContent = status === 'online' ? 'TERHUBUNG' : status === 'offline' ? 'OFFLINE' : 'MENGHUBUNGKAN';
}

// ── CLEAR & EXPORT ───────────────────────────────
function clearLog() {
  logRows = [];
  _seenFlameIds.clear();
  document.getElementById('log-body').innerHTML =
    '<tr><td colspan="3" style="color:var(--dim);text-align:center;padding:20px 0;">Log dibersihkan</td></tr>';
  toast('Log api dibersihkan');
}

function clearPowerLog() {
  powerRows = [];
  _seenPowerIds.clear();
  document.getElementById('power-log-body').innerHTML =
    '<tr><td colspan="5" style="color:var(--dim);text-align:center;padding:20px 0;">Log dibersihkan</td></tr>';
  toast('Log power dibersihkan');
}

function clearChart() {
  adcLabels.length = 0; adcData.length = 0;
  adcChart.update();
  toast('Chart ADC dibersihkan');
}

function clearPowerChart() {
  powerLabels.length = 0; powerVData.length = 0; powerWData.length = 0;
  pwrChart.update();
  toast('Chart power dibersihkan');
}

function exportCSV() {
  if (!logRows.length) { toast('Tidak ada data flame', 'danger'); return; }
  let csv = 'Waktu,ADC Raw,Status\n';
  logRows.forEach(r => { csv += `${r.ts},${r.raw},${r.fire ? 'API' : 'Aman'}\n`; });
  dlCSV(csv, 'flame_log_' + new Date().toISOString().slice(0,10) + '.csv');
  toast('CSV flame berhasil diunduh');
}

function exportPowerCSV() {
  if (!powerRows.length) { toast('Tidak ada data power', 'danger'); return; }
  let csv = 'Waktu,Tegangan (V),Arus (A),Daya (W),Energi (Wh),Status\n';
  powerRows.forEach(r => {
    csv += `${r.ts},${parseFloat(r.v).toFixed(2)},${parseFloat(r.a).toFixed(2)},${parseFloat(r.w).toFixed(1)},${parseFloat(r.wh).toFixed(0)},${r.ok ? 'Normal' : 'Warning'}\n`;
  });
  dlCSV(csv, 'power_log_' + new Date().toISOString().slice(0,10) + '.csv');
  toast('CSV power berhasil diunduh');
}

function dlCSV(content, filename) {
  const a = document.createElement('a');
  a.href = URL.createObjectURL(new Blob([content], { type: 'text/csv' }));
  a.download = filename;
  a.click();
}

// ── TOAST ────────────────────────────────────────
let toastTmr;
function toast(msg, type = '') {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.className = 'toast show' + (type ? ' ' + type : '');
  clearTimeout(toastTmr);
  toastTmr = setTimeout(() => el.classList.remove('show'), 3500);
}

// ── DEVICE CHANGE ────────────────────────────────
document.getElementById('device-select').addEventListener('change', () => {
  lastId = null;
  logRows = [];
  powerRows = [];
  _seenFlameIds.clear();
  _seenPowerIds.clear();
  document.getElementById('log-body').innerHTML =
    '<tr><td colspan="3" style="color:var(--dim);text-align:center;padding:20px 0;">Mengganti device…</td></tr>';
  document.getElementById('power-log-body').innerHTML =
    '<tr><td colspan="5" style="color:var(--dim);text-align:center;padding:20px 0;">Mengganti device…</td></tr>';
  fetchAll();
});

// ── POLLING LOOP ─────────────────────────────────
async function fetchAll() {
  await fetchHistory();
  await fetchLatest();
}

async function startPolling() {
  await fetchAll();
  pollTimer = setInterval(fetchAll, POLL_MS);
}

// ── INIT ─────────────────────────────────────────
window.addEventListener('load', startPolling);
</script>
</body>
</html>