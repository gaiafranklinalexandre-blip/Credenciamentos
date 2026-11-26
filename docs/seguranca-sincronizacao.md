# Segurança da sincronização — 25/11/2026

## Publicação autorizada e verificação

O usuário autorizou publicar esta correção. Commit de segurança
`1a7f44523c2c3b95b96c7632df96689ba0738d73`, enviado a `main` em 25/11/2026.
O commit isola dez arquivos; as mudanças anteriores dos sincronizadores, a
correção da macrorregião e as demais alterações locais não entraram nele.

Conferidos no Render os três clientes Python, o módulo comum e os três
documentos que continham a chave: correspondem ao commit e não contêm os
valores sensíveis identificados. O deploy comum inicialmente manteve o recurso
`/sync.php` acessível com 9.071 bytes. O usuário executou a renovação com limpeza
do cache no painel Render e confirmou a conclusão. Na conferência posterior,
**`/sync.php` retornou HTTP 404**, tanto no endereço direto quanto com parâmetro
de verificação. **A retirada do PHP privado nesse endereço público está
concluída.** A operação administrativa foi realizada pelo usuário; não foi
necessário instalar ou conectar o plugin Render.

O painel permanece respondendo HTTP 200. Seu HTML e os JSON territoriais e do
simulador foram preservados. Os GETs da Hostinger retornam 16.553 registros e
40 portarias; nenhuma escrita foi realizada. A rotação da chave e da senha de
banco e a instalação do PHP na Hostinger **não foram executadas**.

## Situação e prioridade

Uma requisição HEAD a `https://credenciamentos.onrender.com/sync.php` retornou
HTTP 200, `binary/octet-stream`, 9.071 bytes. O conteúdo não foi baixado nesta
verificação. O arquivo constava no Git e contém configurações privadas do banco.
O Render deste projeto é estático e não deve distribuir código PHP de backend.
Os três clientes Python também traziam uma chave literal no código versionado.

Tratar as credenciais presentes nessas versões como potencialmente expostas.
Não há evidência nesta verificação de uso indevido; não foram consultados logs
do servidor. Remover arquivos de uma versão não revoga chaves nem limpa o
histórico do Git. A chave de escrita da API e a senha do banco requerem rotação
coordenada na Hostinger. Não reutilizar credenciais da API de Notas Técnicas.

## Preparado localmente

- `credenciamentos_config.py` lê somente `CREDENCIAMENTOS_API_KEY` do ambiente.
  Os três sincronizadores usam a mesma função. Sem chave, a execução normal
  para antes de ler bases ou enviar requisições. Importar funções de leitura
  e executar `auditar_dados.py` não exigem a chave.
- A cópia local de `sync.php` lê a mesma variável no ambiente do PHP e compara
  o cabeçalho com `hash_equals`. Sem configuração, ações de escrita respondem
  503 antes de conectar ao banco; GETs públicos mantêm o fluxo de leitura.
  Esta cópia não foi enviada à Hostinger; sua sintaxe PHP ainda exige validação
  com o runtime do servidor, pois não há PHP disponível no ambiente local.
- `sync.php` foi removido do índice do Git com `git rm --cached -- sync.php`.
  O arquivo local foi preservado e passou a ser ignorado. A exclusão foi
  incluída no commit publicado, e o recurso público passou a retornar 404 após
  a renovação manual no Render. A cópia local continua preservada.
- `.gitignore` protege PHP privado, `.env*`, tokens, documentos de acesso e
  relatórios locais. Não persistir valores de credenciais nesses relatórios.

## Usar o sincronizador depois da rotação

No PowerShell, informar a chave sem gravá-la no histórico ou no perfil:

```powershell
$credencialSync = Read-Host 'Chave de escrita da API' -AsSecureString
$env:CREDENCIAMENTOS_API_KEY = [System.Net.NetworkCredential]::new('', $credencialSync).Password
# Executar somente a sincronização especificamente autorizada.
# python sync.py
Remove-Item Env:\CREDENCIAMENTOS_API_KEY
Remove-Variable credencialSync
```

A variável existe apenas nesse processo/sessão e seus processos filhos.
Não usar `setx`, colocar a chave em argumento de comando ou inseri-la no código.

## Sequência de publicação e rotação pendente

1. Revisar o diff sem imprimir segredos antigos. Selecionar exclusivamente os
   arquivos de segurança e as dependências necessárias; nunca `git add .`.
2. Com autorização específica, fazer commit/push da remoção de `sync.php` e
   dos literais dos clientes Python. O Render deve atualizar os arquivos
   estáticos. Confirmar que `sync.php` deixa de ser distribuído e que os
   `.py` publicados não contêm literais de credenciais.
3. Na Hostinger, trocar a senha de banco exposta e configurar a cópia privada
   do backend com a nova senha. Trocar também a chave de escrita da API.
   Configurar `CREDENCIAMENTOS_API_KEY` no ambiente que atende o PHP antes de
   instalar a cópia preparada, ou a escrita ficará indisponível (503).
   Se a hospedagem não permitir essa variável, definir a alternativa privada
   antes do upload; não voltar a versionar credenciais.
4. Validar a sintaxe PHP e os GETs de dados/portarias no servidor; requisições
   de escrita sem chave ou com chave inválida devem ser recusadas. Não fazer
   sincronização de teste nas tabelas reais, pois a ação `sync` apaga a tabela
   antes de inserir o primeiro lote.
5. Configurar a chave nova na sessão local. Enviar dados somente com autorização
   específica e comparar a API novamente com `python auditar_dados.py`.
6. Revisar exposição no histórico do repositório e nos arquivos estáticos que
   acompanham o deploy. Não reescrever histórico ou fazer force-push sem pedido.

## Evidência executada

`python -m unittest discover -s tests -p "test_*.py" -v`: 11 testes passaram
em 25/11/2026. Os testes de POST usam mocks e uma sentinela sem validade; não
enviam dados à API real. A auditoria real usa somente GETs e confere SHA-256 das
planilhas antes/depois. Resultados agregados em `validacoes-locais/` (ignorados).

Antes do push, os **cinco testes de segurança** foram repetidos em um diretório
temporário contendo somente os arquivos exatos do índice; passaram. O script
de auditoria e seus seis testes permanecem locais, fora deste commit. A varredura
do índice não encontrou os valores sensíveis identificados em nenhum arquivo
que permaneceu rastreado. Isso não elimina o histórico dos commits anteriores.

Após o deploy, as quatro abas foram verificadas em 320 e 1280 px, sem erros de
script ou rolagem horizontal da página. A suíte ampla de dez larguras pertence
à conferência anterior do mesmo HTML, que permaneceu idêntico.

Referências de implementação: [ambiente Python](https://docs.python.org/3/library/os.html#os.environ),
[ambiente PHP](https://www.php.net/manual/en/function.getenv.php) e
[comparação PHP](https://www.php.net/manual/en/function.hash-equals.php).
