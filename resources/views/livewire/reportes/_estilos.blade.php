<style>
    .rp { display: grid; gap: 16px; max-width: 1100px; }
    .rp .barra { display: flex; gap: 10px; flex-wrap: wrap; align-items: end; background: white; border-radius: 12px; padding: 14px 16px; box-shadow: 0 3px 14px rgba(25, 45, 75, .06); }
    .rp .barra label { display: grid; gap: 4px; font-size: 12.5px; font-weight: 700; color: #52657e; }
    .rp .barra input { padding: 8px 10px; border: 1px solid #c5d1e0; border-radius: 8px; font: inherit; background: white; }
    .rp .barra .acciones { margin-left: auto; display: flex; gap: 8px; flex-wrap: wrap; }
    .rp .btn { padding: 9px 14px; border: 0; border-radius: 8px; background: #174ea6; color: white; font: inherit; font-weight: 700; font-size: 14px; cursor: pointer; white-space: nowrap; }
    .rp .btn.sec { background: #e9eef5; color: #243047; }
    .rp .periodo { color: #65738a; font-size: 13.5px; }
    .rp .err { background: #fdecea; color: #9b2c22; border-radius: 8px; padding: 10px 12px; font-size: 14px; }
    .rp .kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(165px, 1fr)); gap: 12px; }
    .rp .kpi { background: white; border-radius: 12px; padding: 16px; box-shadow: 0 3px 14px rgba(25, 45, 75, .06); }
    .rp .kpi .valor { font-size: 28px; font-weight: 800; color: #172b4d; line-height: 1.1; }
    .rp .kpi .nombre { color: #65738a; font-size: 13px; margin-top: 4px; }
    .rp .kpi .extra { color: #8a97ab; font-size: 12px; margin-top: 2px; }
    .rp .destacados { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 12px; }
    .rp .destacado { border-radius: 12px; padding: 16px 18px; background: linear-gradient(135deg, #174ea6, #2b6cc4); color: white; }
    .rp .destacado .eti { font-size: 12px; text-transform: uppercase; letter-spacing: .5px; opacity: .85; font-weight: 700; }
    .rp .destacado .tit { font-size: 18px; font-weight: 800; margin: 6px 0 2px; overflow-wrap: anywhere; }
    .rp .destacado .det { font-size: 13px; opacity: .9; }
    .rp .destacado.vacio { background: #eef2f7; color: #65738a; }
    .rp .grid2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; align-items: start; }
    .rp .panel { background: white; border-radius: 12px; padding: 18px 20px; box-shadow: 0 3px 14px rgba(25, 45, 75, .06); min-width: 0; }
    .rp .panel h2 { margin: 0 0 12px; font-size: 16.5px; color: #172b4d; }
    .rp .fila { display: grid; grid-template-columns: minmax(90px, 38%) 1fr 44px; gap: 10px; align-items: center; font-size: 13.5px; color: #3a4a60; margin-top: 8px; }
    .rp .fila .n { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .rp .fila .v { text-align: right; font-weight: 700; color: #172b4d; }
    .rp .pista { height: 10px; background: #eef2f7; border-radius: 5px; overflow: hidden; }
    .rp .relleno { height: 100%; background: #2b6cc4; border-radius: 5px; }
    .rp .tabla { overflow-x: auto; }
    .rp table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .rp th { text-align: left; color: #65738a; font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: .3px; padding: 8px 10px; border-bottom: 1px solid #e3e9f1; white-space: nowrap; }
    .rp td { padding: 10px; border-bottom: 1px solid #eef2f7; color: #243047; }
    .rp td.num, .rp th.num { text-align: right; white-space: nowrap; }
    .rp .estrellas { color: #e0a100; white-space: nowrap; }
    .rp .chip { display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: 12px; font-weight: 700; }
    .rp .chip.PUBLICADO { background: #eef4ff; color: #1f4b8f; }
    .rp .chip.FINALIZADO { background: #e7f6ec; color: #1e6b3a; }
    .rp .chip.CANCELADO { background: #fdecea; color: #9b2c22; }
    .rp .vacio-txt { color: #718096; text-align: center; padding: 18px; font-size: 14px; }
    @media (max-width: 820px) { .rp .grid2 { grid-template-columns: 1fr; } .rp .barra .acciones { margin-left: 0; } }
    @media print {
        body * { visibility: hidden; }
        .rp, .rp * { visibility: visible; }
        .rp { position: absolute; inset: 0 auto auto 0; width: 100%; max-width: none; }
        .rp .no-print { display: none !important; }
        .rp .panel, .rp .kpi { box-shadow: none; border: 1px solid #dde4ee; break-inside: avoid; }
    }
</style>
