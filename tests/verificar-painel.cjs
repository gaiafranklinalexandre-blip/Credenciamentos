/* Verificação do protótipo do novo painel (somente leitura; não altera arquivos nem publica).
 *
 * Uso (com o protótipo servido, ex.: python -m http.server 8972 dentro de prototipo/):
 *   node verificar_prototipo.cjs --playwright "<caminho do playwright-core>" [--url http://127.0.0.1:8972/]
 * Exige Node, Edge e um pacote Playwright já instalado (não instala nada). Sai com código 1 se algo falhar.
 * Cobre: rolagem horizontal da página, grade das tabelas, IDs únicos, erros de console, contraste de texto (AA),
 * dica ao passar o mouse, aba Simulador (br-select, teclado, URL) e navegação por setas entre as abas.
 * Não cobre: leitor de tela, zoom real do navegador, contraste em todos os estados.
 */
const args = process.argv.slice(2);
const opt = (n, d) => { const i = args.indexOf('--' + n); return i >= 0 ? args[i + 1] : d; };
const { chromium } = require(opt('playwright', 'playwright-core'));
const URL = opt('url', 'http://127.0.0.1:8972/');
const LARGURAS = [320, 390, 575, 640, 768, 992, 1024, 1280, 1440, 1920];
const falhas = [];
const falha = (m) => { falhas.push(m); console.log('FALHA:', m); };
const ok = (m) => console.log('ok:', m);

(async () => {
  const b = await chromium.launch({ channel: 'msedge', headless: true });
  /* 1) larguras x abas */
  for (const w of LARGURAS) {
    for (const tab of ['', 'encerrados', 'port', 'sim']) {
      const p = await b.newPage({ viewport: { width: w, height: 900 } });
      p.on('pageerror', e => falha(`${w}px ${tab || 'ativos'}: erro de página ${e.message}`));
      p.on('console', m => { if (m.type() === 'error' && !/favicon|fonts|net::/.test(m.text())) falha(`${w}px ${tab || 'ativos'}: console ${m.text()}`); });
      await p.goto(URL + (tab ? '#tab=' + tab : ''), { waitUntil: 'networkidle' });
      await p.waitForSelector('#app:not([hidden])'); await p.waitForTimeout(150);
      if ((tab === '' || tab === 'encerrados') && await p.locator('.cred-expande').count()) { await p.locator('.cred-expande').first().click(); await p.waitForTimeout(100); }
      const r = await p.evaluate(() => {
        const ids = [...document.querySelectorAll('[id]')].map(e => e.id);
        const reg = document.querySelector('.cred-tabela');
        const mx = document.querySelector('.mx');
        return { ov: document.documentElement.scrollWidth > innerWidth, dup: [...new Set(ids.filter((x, i) => ids.indexOf(x) !== i))],
          tabela: reg ? getComputedStyle(reg.querySelector('table')).display : null, colunas: reg ? reg.querySelectorAll('thead th').length : null,
          matrizCortada: mx ? mx.scrollWidth > mx.clientWidth + 1 : false };
      });
      if (r.ov) falha(`${w}px ${tab || 'ativos'}: rolagem horizontal da página`);
      if (r.dup.length) falha(`${w}px ${tab || 'ativos'}: IDs duplicados ${r.dup}`);
      if (r.tabela && r.tabela !== 'table') falha(`${w}px ${tab || 'ativos'}: tabela virou ${r.tabela}`);
      if (r.colunas !== null && r.colunas < 6) falha(`${w}px ${tab || 'ativos'}: colunas ocultas (${r.colunas})`);
      if (r.matrizCortada) falha(`${w}px ${tab || 'ativos'}: matriz por estratégia cortada`);
      await p.close();
    }
  }
  ok(`${LARGURAS.length} larguras x 4 abas (rolagem, grade, IDs, console)`);

  /* 2) contraste (texto x fundo efetivo), 1280 px, detalhe aberto e aba Simulador preenchida */
  const p = await b.newPage({ viewport: { width: 1280, height: 900 } });
  const contraste = () => p.evaluate(() => {
    const parse = s => { const m = s.match(/[\d.]+/g).map(Number); return { c: m.slice(0, 3), a: m.length > 3 ? m[3] : 1 }; };
    const fundo = el => { let e = el; while (e) { const c = parse(getComputedStyle(e).backgroundColor); if (c.a > 0.9) return c.c; e = e.parentElement; } return [255, 255, 255]; };
    const lum = ([r, g, b]) => { const f = v => { v /= 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); }; return 0.2126 * f(r) + 0.7152 * f(g) + 0.0722 * f(b); };
    const out = [];
    document.querySelectorAll('body *').forEach(e => {
      if (![...e.childNodes].some(n => n.nodeType === 3 && n.textContent.trim())) return;
      if (!e.offsetParent && getComputedStyle(e).position !== 'fixed') return;
      const cs = getComputedStyle(e), l1 = lum(parse(cs.color).c), l2 = lum(fundo(e)), ratio = (Math.max(l1, l2) + .05) / (Math.min(l1, l2) + .05);
      const px = parseFloat(cs.fontSize), grande = px >= 24 || (px >= 18.66 && parseInt(cs.fontWeight) >= 700);
      if (ratio < (grande ? 3 : 4.5)) out.push(`${e.tagName}.${e.className} "${e.textContent.trim().slice(0, 24)}" ${ratio.toFixed(2)}`);
    });
    return out.slice(0, 10);
  });
  await p.goto(URL + '#tab=encerrados', { waitUntil: 'networkidle' }); await p.waitForSelector('#app:not([hidden])');
  await p.locator('.cred-expande').first().click(); await p.waitForTimeout(150);
  let c = await contraste(); c.forEach(x => falha('contraste abaixo de AA (Encerrados): ' + x));
  await p.goto(URL + '#tab=sim&sEstr=Equipe%20de%20Sa%C3%BAde%20da%20Fam%C3%ADlia&sMes=9', { waitUntil: 'networkidle' }); await p.waitForSelector('#app:not([hidden])'); await p.waitForTimeout(300);
  c = await contraste(); c.forEach(x => falha('contraste abaixo de AA (Simulador): ' + x));
  ok('contraste de texto (Encerrados com detalhe e Simulador)');

  /* 3) dica ao passar o mouse: abre, o ponteiro pode ir até ela, Esc fecha */
  await p.goto(URL + '#tab=encerrados', { waitUntil: 'networkidle' }); await p.waitForSelector('#app:not([hidden])');
  await p.mouse.move(5, 5); await p.locator('[data-tip]').first().hover(); await p.waitForTimeout(150);
  const aberta1 = await p.evaluate(() => !document.getElementById('dica').hidden);
  const bx = await p.locator('#dica').boundingBox(); await p.mouse.move(bx.x + bx.width / 2, bx.y + bx.height / 2, { steps: 6 }); await p.waitForTimeout(200);
  const aberta2 = await p.evaluate(() => !document.getElementById('dica').hidden);
  await p.keyboard.press('Escape'); await p.waitForTimeout(100);
  const fechou = await p.evaluate(() => document.getElementById('dica').hidden);
  if (!(aberta1 && aberta2 && fechou)) falha(`dica: abre=${aberta1} segue aberta ao ir até ela=${aberta2} fecha com Esc=${fechou}`); else ok('dica (hover, ponteiro sobre ela, Esc)');

  /* 4) simulador: componente br-select por mouse e teclado, regra de datas, Limpar, URL */
  await p.goto(URL + '#tab=sim', { waitUntil: 'networkidle' }); await p.waitForSelector('#app:not([hidden])'); await p.waitForTimeout(300);
  if (await p.locator('#conteudoTab .br-select').count() !== 2) falha('simulador: br-select não renderizado (core.min.js não carregou?)');
  else {
    await p.click('[data-sim=sEstr] [data-trigger]'); await p.click('[data-sim=sEstr] label:has-text("Residentes")');
    await p.click('[data-sim=sMes] [data-trigger]'); await p.click('[data-sim=sMes] label:has-text("01 a 31 de janeiro")'); await p.waitForTimeout(200);
    const lin = () => p.evaluate(() => [...document.querySelectorAll('#simRes tbody tr')].map(r => r.cells[1].innerText.replace(/\s+/g, ' ').trim()));
    let v = await lin();
    if (v[0] !== 'Junho' || v[3] !== 'Parcela 03') falha('simulador: Residentes/janeiro esperado Junho e Parcela 03, veio ' + JSON.stringify(v));
    await p.fill('#sEstr', ''); await p.click('#sEstr'); await p.keyboard.type('Acad'); await p.keyboard.press('ArrowDown'); await p.keyboard.press('Enter'); await p.waitForTimeout(200);
    v = await lin();
    if (v[0] !== 'Março' || !/1ª Jan.*2ª Fev.*3ª Mar/.test(v[1])) falha('simulador: Academia da Saúde/janeiro por teclado: ' + JSON.stringify(v));
    await p.click('#simLimpar'); await p.waitForTimeout(200);
    if (await p.inputValue('#sEstr') !== '' || await p.evaluate(() => document.activeElement.id) !== 'sEstr') falha('simulador: Limpar Filtros não limpou ou perdeu o foco');
    await p.goto(URL + '#tab=sim&sEstr=Academia%20da%20Sa%C3%BAde&sMes=11', { waitUntil: 'networkidle' }); await p.waitForSelector('#app:not([hidden])'); await p.waitForTimeout(300);
    v = await lin();
    if (v[0] !== 'Fevereiro') falha('simulador: URL direta Academia/dezembro esperado prazo Fevereiro, veio ' + v[0]); else ok('simulador (mouse, teclado, Limpar, URL, regra de datas)');
  }

  /* 5) abas por setas incluem Simulador */
  await p.goto(URL, { waitUntil: 'networkidle' }); await p.waitForSelector('#app:not([hidden])');
  await p.focus('#tab-port'); await p.keyboard.press('ArrowRight'); await p.waitForTimeout(150);
  if (await p.evaluate(() => document.querySelector('#abas [aria-selected=true]').textContent.trim()) !== 'Simulador') falha('abas: seta direita de Portarias não chegou em Simulador'); else ok('abas por teclado');

  await b.close();
  console.log(falhas.length ? `\n${falhas.length} falha(s).` : '\nTudo certo.');
  process.exit(falhas.length ? 1 : 0);
})();
