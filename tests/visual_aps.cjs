// Requer Playwright disponível no ambiente; não altera API, MySQL ou planilhas.
const {chromium}=require('playwright');const fs=require('node:fs/promises');const assert=require('node:assert/strict');
const ROOT=process.env.CRED_TEST_URL||'http://127.0.0.1:8963/';
(async()=>{await fs.mkdir('validacao_visual',{recursive:true});const b=await chromium.launch({channel:'msedge',headless:true});try{
 const context=await b.newContext({viewport:{width:1440,height:1050}}),p=await context.newPage(),errors=[],google=[];
 p.on('pageerror',e=>errors.push(e.message));p.on('request',r=>{if(/google-analytics\.com|googletagmanager\.com/.test(r.url()))google.push(r.url());});
 await context.route('**/www.googletagmanager.com/**',r=>r.fulfill({status:200,body:'/* analytics mock: sem coleta QA */'}));
 await p.goto(ROOT);await p.locator('#cookie-necessarios').click();
 await p.waitForFunction(()=>typeof dados!=='undefined'&&dados.length>0);
 const total=await p.evaluate(()=>dados.length);assert.ok(total>1000);
 assert.equal(await p.locator('script[src*="chart.js"]').count(),0);assert.equal(await p.locator('script[src*="leaflet.js"]').count(),0);assert.deepEqual(google,[]);
 await p.screenshot({path:'validacao_visual/capa-desktop.png',fullPage:false});
 await p.locator('#buscaInput').fill('Salvador');await p.locator('.sug-item').filter({has:p.locator('.sug-uf',{hasText:'BA'})}).click();
 await p.locator('.capa-busca-btn').click();assert.match(await p.locator('#resultadoTitulo').innerText(),/Salvador/i);
 for(const aba of ['ativos','encerrados','homol-ativas','homol-encerradas','portarias']){
  await p.locator('#btnNavAba').click();await p.locator('#navi-'+aba).click();assert.equal(await p.locator('#c-'+aba).isVisible(),true);
 }
 await p.screenshot({path:'validacao_visual/resultado-municipio.png',fullPage:false});
 await p.locator('[data-cred-nav="portarias"]').click();
 await p.locator('#btnNavAba').click();await p.locator('#navi-encerrados').click();
 await p.locator('#c-encerrados .btn-download').waitFor();
 const download=await Promise.all([p.waitForEvent('download'),p.locator('#c-encerrados .btn-download').click()]);assert.match(download[0].suggestedFilename(),/\.csv$/i);
 await p.screenshot({path:'validacao_visual/resultado-desktop.png',fullPage:false});
 await p.locator('[data-cred-nav="dashboard"]').click();await p.locator('#chartRegiao').waitFor();
 await p.waitForFunction(()=>typeof Chart!=='undefined'&&typeof L!=='undefined'&&document.querySelector('#dashMapa .leaflet-pane'));
 await p.locator('#credMenuToggle').click();await p.waitForFunction(()=>_mapaInst.getSize().x===document.getElementById('dashMapa').clientWidth);
 await p.locator('#credMenuToggle').click();await p.waitForFunction(()=>_mapaInst.getSize().x===document.getElementById('dashMapa').clientWidth);
 assert.ok(await p.locator('#dashFUF option').count()>1);await p.locator('#dashFUF').selectOption('BA');assert.equal(await p.locator('#dashFUF').inputValue(),'BA');await p.locator('#dashFLimpar').click();assert.equal(await p.locator('#dashFUF').inputValue(),'');
 await p.waitForFunction(()=>Number(document.getElementById('kpi-cred').textContent.replace(/\D/g,''))===dadosDash.reduce((s,d)=>s+Number(d.credenciado||0),0));
 await p.screenshot({path:'validacao_visual/dashboard-desktop.png',fullPage:false});
 for(const width of [1920,1440,1280,1024,768,390,320]){
  await p.setViewportSize({width,height:1000});
  for(const tab of ['consulta','dashboard','portarias']){
   if(width<=800)await p.locator('#credMenuToggle').click();
   await p.locator(`[data-cred-nav="${tab}"]`).click();
   if(width<=800)await p.waitForFunction(()=>document.getElementById('credMenu').getBoundingClientRect().right<=0);
   assert.equal(await p.evaluate(()=>document.documentElement.scrollWidth>innerWidth),false,`${width}/${tab}: overflow`);
   if(tab==='consulta'){
    const h=await p.locator('#buscaInput,.capa-busca-btn,.cred-clear').evaluateAll(es=>es.map(e=>e.getBoundingClientRect().height));assert.ok(h.every(v=>Math.abs(v-48)<1));
    if(width===390)await p.screenshot({path:'validacao_visual/capa-mobile.png',fullPage:false});
   }
   if(tab==='dashboard'&&width===390)await p.screenshot({path:'validacao_visual/dashboard-mobile.png',fullPage:false});
  }
 }
 assert.deepEqual(errors,[]);assert.deepEqual(google,[]);
 await p.locator('.painel-privacidade-link').click();await p.locator('#cookie-analise').check();await p.locator('#cookie-salvar').click();await p.waitForFunction(()=>document.getElementById('aps-ga'));assert.equal(google.length,1);
 await p.locator('.painel-privacidade-link').click();await p.locator('#cookie-recusar').click();await p.waitForLoadState('domcontentloaded');await p.waitForFunction(()=>!document.getElementById('aps-ga'));assert.equal(google.length,1);
 await fs.writeFile('validacao_visual/resultado.json',JSON.stringify({url:ROOT,registros:total,verificado:new Date().toISOString(),resultado:'Consulta real, autocomplete, 5 abas, CSV, filtros/dashboard/mapa/graficos, 21 layouts e consentimento GA: OK'},null,2));
 console.log('OK: consulta real, autocomplete, 5 abas, CSV, dashboard/mapa/gráficos/filtros, 21 layouts e consentimento GA.');
}finally{await b.close()}})().catch(e=>{console.error(e);process.exitCode=1});
