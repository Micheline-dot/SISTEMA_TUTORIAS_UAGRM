<style>
    .tt-lista { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }
    .tt-card { background: white; border-radius: 12px; box-shadow: 0 3px 14px rgba(25, 45, 75, .06); display: flex; flex-direction: column; overflow: hidden; transition: transform .15s, box-shadow .15s; }
    .tt-card:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(25, 45, 75, .10); }
    .tt-card .tt-portada { display: block; position: relative; aspect-ratio: 16 / 8; background: linear-gradient(135deg, #174ea6, #3aa0d8); color: #fff; text-decoration: none; }
    .tt-card .tt-portada img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .tt-card .tt-portada .tt-sin { position: absolute; inset: 0; display: grid; place-items: center; padding: 0 18px; text-align: center; font-weight: 800; font-size: 18px; text-shadow: 0 2px 8px rgba(0,0,0,.25); }
    .tt-card .tt-etq { position: absolute; left: 10px; top: 10px; display: flex; gap: 6px; flex-wrap: wrap; }
    .tt-card .tt-tag { padding: 4px 9px; border-radius: 999px; font-size: 12px; font-weight: 700; background: rgba(255,255,255,.92); color: #1d5a86; }
    .tt-card .tt-tag.gratis { color: #166534; }
    .tt-card .tt-tag.pago { color: #4a5568; }
    .tt-card .tt-tag.lleno { background: #fdecea; color: #9b2c22; }
    .tt-card .tt-cuerpo { padding: 14px 18px 16px; display: flex; flex-direction: column; gap: 8px; flex: 1; }
    .tt-card h3 { margin: 0; font-size: 17px; }
    .tt-card h3 a { color: #172b4d; text-decoration: none; }
    .tt-card h3 a:hover { color: #174ea6; }
    .tt-card .tt-desc { color: #65738a; font-size: 14px; line-height: 1.5; margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .tt-card .tt-meta { color: #52657e; font-size: 13.5px; line-height: 1.6; }
    .tt-card .tt-tutor { display: flex; align-items: center; gap: 10px; padding-top: 10px; border-top: 1px solid #eef2f7; margin-top: auto; text-decoration: none; color: inherit; border-radius: 8px; }
    .tt-card .tt-tutor:hover strong { color: #174ea6; text-decoration: underline; }
    .tt-card .tt-avatar { width: 34px; height: 34px; border-radius: 50%; object-fit: cover; background: #e7f2fb; color: #21618e; display: grid; place-items: center; font-weight: 800; font-size: 14px; flex: none; }
    .tt-card .tt-tutor strong { display: block; color: #172b4d; font-size: 14px; }
    .tt-card .tt-tutor small { color: #8492a6; font-size: 12.5px; }
    .tt-card .estrellas { color: #e0a100; letter-spacing: 1px; }
</style>
