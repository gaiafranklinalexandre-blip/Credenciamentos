# Credenciamentos

Frontend nativo: **https://credenciamentos.onrender.com/**.

Painel de consulta municipal, credenciamentos, homologações, portarias e dashboard, servido pelo Render a partir de `main`. A API PHP/MySQL permanece na Hostinger e não é alterada pela publicação do frontend.

O endereço `painel-credenciamentos.onrender.com` pertence ao wrapper legado de Power BI, de outro repositório. Não usar esse wrapper para validar deploys deste projeto.

Em 24/10/2026 foi integrado o estilo visual compartilhado dos painéis, com navegação lateral, controles de 48 px, tipografia ampliada, sombras/degradês e consentimento opcional de Analytics. Tudo permanece incorporado em `index.html`. Regras de negócio, dados e sincronização foram preservados; apenas uma verificação defensiva foi adicionada ao autocomplete para filtros antigos ausentes.

Validação: `node tests/visual_aps.cjs`, com Playwright/Edge disponíveis e `CRED_TEST_URL` apontando para o servidor local ou o site público. Confere dados reais, cinco abas, CSV, mapa/gráficos/filtros, responsividade e consentimento sem coleta de QA.
