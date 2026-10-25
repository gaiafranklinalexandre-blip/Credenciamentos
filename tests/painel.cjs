// API real; cenários controlados só verificam regras, sem alterar dados.
const {chromium}=require(process.env.CRED_PLAYWRIGHT||'playwright');
const assert=require('node:assert/strict'),fs=require('node:fs/promises');
(async()=>{await fs.mkdir('validacao_visual',{recursive:true});const b=await chromium.launch({channel:'msedge',headless:true});try{
const ctx=await b.newContext({viewport:{width:1440,height:1000}}),p=await ctx.newPage(),errors=[],google=[];
p.on('pageerror',e=>errors.push(e.message));p.on('request',r=>{if(/googletagmanager|google-analytics/.test(r.url()))google.push(r.url())});
await ctx.route('**/www.googletagmanager.com/**',r=>r.fulfill({status:200,body:'/* QA sem coleta */'}));
await p.goto(process.env.CRED_TEST_URL||'http://127.0.0.1:8963');await p.locator('#cookie-necessarios').click();await p.waitForFunction(()=>typeof dados!=='undefined'&&dados.length>16000&&document.querySelectorAll('.integrado-card').length>0);
assert.equal(await p.locator('#navi-ativos').getAttribute('aria-current'),'page');assert.equal(await p.evaluate(()=>municipioSel),'');assert.equal(await p.locator('.panorama-kpi').count(),4);
assert.equal(await p.evaluate(()=>gruposIntegrados.every(s=>s.credAtivo)),true);
await p.screenshot({path:'validacao_visual/panorama-nacional.png'});
await p.locator('#navi-encerrados').click();const antes=await p.locator('.integrado-card').count();await p.getByRole('button',{name:/Carregar mais/}).click();assert.ok(await p.locator('.integrado-card').count()>antes);await p.locator('#navi-ativos').click();
await p.locator('#btnRefinar').click();await p.locator('#fsUF').selectOption('MG');assert.equal(await p.evaluate(()=>gruposIntegrados.every(s=>s.r.uf==='MG')),true);await p.locator('#fsLimpar').click();await p.locator('#btnRefinar').click();
await p.locator('#buscaInput').fill('Betim');await p.locator('.sug-item').filter({hasText:'MG'}).click();await p.locator('.capa-busca-btn').click();assert.equal(await p.locator('.integrado-card').count(),2);
const esf=p.locator('.integrado-card').filter({hasText:'· eSF'});assert.match(await esf.innerText(),/Última competência/);assert.match(await esf.innerText(),/31\/10\/2026/);
await esf.getByRole('button',{name:'Entender situação'}).click();assert.match(await p.locator('.integrado-dialog').innerText(),/1 equipe\(s\) homologada\(s\) da competência CNES Ago\/26/);assert.match(await p.locator('.integrado-dialog').innerText(),/parcela financeira de Out\/26/);assert.match(await p.locator('.integrado-dialog').innerText(),/Dez\/26/);assert.equal(await p.locator('.linha-tempo .timeline-ponto').count(),5);
await p.screenshot({path:'validacao_visual/betim-linha-tempo.png'});await p.keyboard.press('Escape');assert.equal(await esf.getByRole('button',{name:'Entender situação'}).evaluate(e=>e===document.activeElement),true);
await p.locator('#navi-encerrados').click();assert.equal(await p.locator('.integrado-card').count(),5);assert.equal(await p.evaluate(()=>gruposIntegrados.every(s=>!s.credAtivo)),true);assert.equal(await p.evaluate(()=>gruposIntegrados.some(s=>s.homolAtiva)),true);
await p.locator('#navi-homologacoes').click();assert.equal(await p.locator('.integrado-card').count(),7);await p.locator('#fHomol').selectOption('ativas');assert.equal(await p.locator('.integrado-card').count(),4);await p.locator('#fHomol').selectOption('encerradas');assert.equal(await p.locator('.integrado-card').count(),3);await p.locator('#fHomol').selectOption('');
await p.locator('#navi-ativos').click();const [csv]=await Promise.all([p.waitForEvent('download'),p.getByRole('button',{name:'Baixar CSV desta aba'}).click()]);const content=await fs.readFile(await csv.path(),'utf8');assert.equal(content.trim().split('\n').length,3);
await p.evaluate(()=>{const r={tipo:'3 competências',data_portaria:'2026-08-24',portaria:12123,credenciado:2,homologacao_1:1,selecao_credenciamento:'Ativo',selecao_homologacao:'Homologando'};
const s=situacaoIntegrada([{...r,selecao_credenciamento:'Finalizado'}],new Date(2026,10,5));if(s.credAtivo||!s.homolAtiva||/cadastre|corrigir/i.test(s.acao))throw Error('Estados independentes');
if(situacaoIntegrada([r],new Date(2026,9,31)).titulo!=='Última competência para cadastro')throw Error('Último dia');
if(!situacaoIntegrada([r],new Date(2026,10,1)).credAtivo)throw Error('Calendário alterou coluna U');
const c=calcComps('2026-12-10');if(c[2].ano!==2027||c[2].homol!==4)throw Error('Virada de ano');});
for(const width of [1440,768,390,320]){await p.setViewportSize({width,height:1000});assert.equal(await p.evaluate(()=>document.documentElement.scrollWidth>innerWidth),false,'overflow '+width);await p.screenshot({path:`validacao_visual/painel-abas-${width}.png`});await esf.getByRole('button',{name:'Entender situação'}).click();assert.equal(await p.evaluate(()=>document.querySelector('.integrado-dialog').scrollWidth>document.querySelector('.integrado-dialog').clientWidth),false);if(width===390)await p.screenshot({path:'validacao_visual/betim-linha-tempo-mobile.png'});await p.keyboard.press('Escape');}
await p.locator('#navi-portarias').click();assert.equal(await p.locator('#c-portarias').isVisible(),true);await p.locator('.cred-clear').click();assert.equal(await p.evaluate(()=>municipioSel),'');assert.equal(await p.evaluate(()=>filtrados.length),16553);assert.equal(await p.locator('#navi-ativos').getAttribute('aria-current'),'page');
assert.deepEqual(google,[]);assert.deepEqual(errors,[]);console.log('OK: panorama real, coluna U, homologação independente, Betim, CNES/parcela na timeline, filtros, paginação, CSV e quatro larguras.');
}finally{await b.close()}})().catch(e=>{console.error(e);process.exitCode=1});
