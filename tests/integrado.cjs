// QA de integração com a API pública; não modifica dados nem envia analytics.
const {chromium}=require(process.env.CRED_PLAYWRIGHT||'playwright');
const assert=require('node:assert/strict'),fs=require('node:fs/promises');
(async()=>{await fs.mkdir('validacao_visual',{recursive:true});const b=await chromium.launch({channel:'msedge',headless:true});try{
const ctx=await b.newContext({viewport:{width:1440,height:1000}}),p=await ctx.newPage(),errors=[],google=[];
p.on('pageerror',e=>errors.push(e.message));p.on('request',r=>{if(/googletagmanager|google-analytics/.test(r.url()))google.push(r.url())});
await ctx.route('**/www.googletagmanager.com/**',r=>r.fulfill({status:200,body:'/* QA sem coleta */'}));
await p.goto(process.env.CRED_TEST_URL||'http://127.0.0.1:8963');await p.locator('#cookie-necessarios').click();await p.waitForFunction(()=>typeof dados!=='undefined'&&dados.length>0);
assert.equal(await p.locator('#dashboard').count(),0);
await p.locator('#navi-portarias').click();assert.equal(await p.locator('#c-portarias').isVisible(),true);
await p.locator('#buscaInput').fill('Betim');await p.locator('.sug-item').filter({hasText:'MG'}).click();await p.locator('.capa-busca-btn').click();
assert.equal(await p.locator('.integrado-card').count(),7);
const esf=p.locator('.integrado-card').filter({has:p.getByRole('heading',{name:'eSF',exact:true})});
assert.match(await esf.innerText(),/Prazo não disponível na base/);
const em=p.locator('.integrado-card').filter({has:p.getByRole('heading',{name:'eMulti - Estratégica',exact:true})});assert.match(await em.innerText(),/Todas as equipes homologadas/);
await esf.getByRole('button',{name:'Entender situação'}).click();assert.equal(await p.locator('.integrado-dialog').isVisible(),true);assert.match(await p.locator('.integrado-dialog').innerText(),/Não há uma data válida/);
await p.screenshot({path:'validacao_visual/integrado-explicacao-desktop.png'});await p.keyboard.press('Escape');assert.equal(await esf.getByRole('button',{name:'Entender situação'}).evaluate(e=>e===document.activeElement),true);
// Datas controladas: corte, virada do ano, último dia inclusivo, sem data e regra específica.
await p.evaluate(()=>{const r={tipo:'3 competências',data_portaria:'2026-08-24',portaria:12123,credenciado:2,homologacao_1:1};const checks=[
 [situacaoIntegrada([r],new Date(2026,9,31)).titulo,'Última competência para cadastro'],
 [situacaoIntegrada([r],new Date(2026,10,1)).titulo,'Aguardando próximas parcelas'],
 [situacaoIntegrada([r],new Date(2026,11,14)).titulo,'Aguardando próximas parcelas'],
 [situacaoIntegrada([r],new Date(2026,11,15)).titulo,'Saldo após a última parcela prevista'],
 [situacaoIntegrada([{...r,selecao_homologacao:'Finalizado'}],new Date(2026,11,15)).titulo,'Encerrado com saldo não homologado'],
 [situacaoIntegrada([{...r,homologacao_1:2}],new Date(2026,9,24)).titulo,'Todas as equipes homologadas'],
 [situacaoIntegrada([{...r,data_portaria:null,portaria:999999}]).titulo,'Prazo não disponível na base'],
 [situacaoIntegrada([{...r,tipo:'Específicos'}]).titulo,'Regra específica de credenciamento']];for(const [a,b]of checks)if(a!==b)throw Error(a+' != '+b);
 const c=calcComps('2026-12-10');if(c[2].ano!==2027||c[2].homol!==4)throw Error('Virada de ano');});
await p.locator('#btnRefinar').click();await p.locator('#fsPortaria').selectOption('12123');assert.equal(await p.locator('.integrado-card').count(),2);await p.locator('#fsEstrategia').selectOption('eSF');assert.equal(await p.locator('.integrado-card').count(),1);await p.locator('#fsLimpar').click();
const [csv]=await Promise.all([p.waitForEvent('download'),p.getByRole('button',{name:'Baixar CSV da consulta'}).click()]);const content=await fs.readFile(await csv.path(),'utf8');assert.equal(content.trim().split('\n').length,8);assert.match(content,/Betim/);
for(const width of [1440,768,390,320]){await p.setViewportSize({width,height:1000});assert.equal(await p.evaluate(()=>document.documentElement.scrollWidth>innerWidth),false,'overflow '+width);await p.screenshot({path:`validacao_visual/integrado-${width}.png`,fullPage:true});await esf.getByRole('button',{name:'Entender situação'}).click();assert.equal(await p.evaluate(()=>document.querySelector('.integrado-dialog').scrollWidth>document.querySelector('.integrado-dialog').clientWidth),false);if(width===390)await p.screenshot({path:'validacao_visual/integrado-explicacao-mobile.png'});await p.keyboard.press('Escape');}
await p.locator('#navi-portarias').click();assert.equal(await p.locator('#c-portarias').isVisible(),true);await p.locator('#navi-ativos').click();assert.equal(await p.locator('.integrado-card').count(),7);
assert.deepEqual(google,[]);await p.locator('.painel-privacidade-link').click();await p.locator('#cookie-analise').check();await p.locator('#cookie-salvar').click();await p.waitForFunction(()=>document.getElementById('aps-ga'));assert.equal(google.length,1);await p.locator('.painel-privacidade-link').click();await p.locator('#cookie-recusar').click();await p.waitForLoadState('domcontentloaded');await p.waitForFunction(()=>!document.getElementById('aps-ga'));
assert.deepEqual(errors,[]);console.log('OK: Betim real, regras temporais, filtros, portarias, CSV, modal/teclado, 4 larguras e consentimento.');
}finally{await b.close()}})().catch(e=>{console.error(e);process.exitCode=1});
