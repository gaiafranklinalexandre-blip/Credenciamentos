# Power BI — Medidas DAX

## Regras de negócio aplicadas nas medidas

As mesmas regras do site se aplicam aqui:

- Portaria publicada no mês **M**
- 1ª Competência CNES: **M** → 1ª Parcela financeira: **M+2**
- 2ª Competência CNES: **M+1** → 2ª Parcela financeira: **M+3**
- 3ª Competência CNES: **M+2** → 3ª Parcela financeira: **M+4**
- Dia de corte: **dia 15** do mês da parcela
  - Antes do dia 15 → parcela ainda não processada (zero é esperado)
  - Após o dia 15 → parcela processada (zero real = problema de cadastro CNES)

---

## Medidas criadas

### 1ª Competência (tooltip da capa)

Exibe status da 1ª competência CNES (mês M) com data de cadastro, homologação e status.

```dax
1ª Competência = 
VAR Tipo = LOWER(TRIM(MAX('Credenciamento'[Tipo])))
VAR DataPub = MAX('Credenciamento'[Data])
VAR EhEspecifico = CONTAINSSTRING(Tipo, "espec")
VAR EhTresCompetencias = CONTAINSSTRING(Tipo, "3") || CONTAINSSTRING(Tipo, "compet")
VAR MesCadastro = DataPub
VAR MesHomologacao = EDATE(MesCadastro, 2)
VAR MesAtualInicio = DATE(YEAR(TODAY()), MONTH(TODAY()), 1)
VAR MesCadastroInicio = DATE(YEAR(MesCadastro), MONTH(MesCadastro), 1)
VAR TextoCadastro = FORMAT(MesCadastro, "mmm/yy", "pt-BR")
VAR CadastroFormatado = UPPER(LEFT(TextoCadastro, 1)) & RIGHT(TextoCadastro, LEN(TextoCadastro) - 1)
VAR TextoHomologacao = FORMAT(MesHomologacao, "mmmm", "pt-BR")
VAR HomologacaoMes = UPPER(LEFT(TextoHomologacao, 1)) & RIGHT(TextoHomologacao, LEN(TextoHomologacao) - 1)
VAR AnoHomologacao = FORMAT(MesHomologacao, "yyyy")
VAR StatusInfo =
    SWITCH(TRUE(),
        ISBLANK(DataPub), BLANK(),
        MesCadastroInicio < MesAtualInicio, UNICHAR(128308) & " Status: Finalizado",
        MesCadastroInicio = MesAtualInicio, UNICHAR(128994) & " Status: Atual",
        UNICHAR(128993) & " Status: Aguardando")
VAR ResultadoTresCompetencias =
    "Cadastro no CNES: " & CadastroFormatado & UNICHAR(10) &
    "Homologação: " & HomologacaoMes & " de " & AnoHomologacao & UNICHAR(10) &
    StatusInfo
RETURN
SWITCH(TRUE(),
    ISBLANK(DataPub), BLANK(),
    EhEspecifico, "Credenciado",
    EhTresCompetencias, ResultadoTresCompetencias,
    ResultadoTresCompetencias)
```

---

### 1ª Parcela Implantação (tooltip da tabela de homologação)

Exibe competência CNES, parcela financeira e status de homologação da 1ª parcela.

```dax
1ª Parcela Implantação = 
VAR Tipo = LOWER(TRIM(MAX('Credenciamento'[Tipo])))
VAR DataPub = MAX('Credenciamento'[Data])
VAR EhEspecifico = CONTAINSSTRING(Tipo, "espec") || CONTAINSSTRING(Tipo, "estabelec")
VAR EhTresCompetencias = CONTAINSSTRING(Tipo, "3") || CONTAINSSTRING(Tipo, "compet")
VAR MesComp1 = DataPub
VAR MesParcela = EDATE(DataPub, 2)
VAR Homologado = MAX('Credenciamento'[Homologação 1])
VAR Credenciado = MAX('Credenciamento'[Credenciado])
VAR Saldo = Credenciado - Homologado
VAR Dia15Parcela = DATE(YEAR(MesParcela), MONTH(MesParcela), 15)
VAR ParcelaProcessada = TODAY() >= Dia15Parcela
VAR TextoCompMes = FORMAT(MesComp1, "mmmm", "pt-BR")
VAR NomeCompMes = UPPER(LEFT(TextoCompMes, 1)) & RIGHT(TextoCompMes, LEN(TextoCompMes) - 1)
VAR NomeComp = NomeCompMes & " de " & FORMAT(MesComp1, "yyyy")
VAR TextoParcelaMes = FORMAT(MesParcela, "mmmm", "pt-BR")
VAR NomeParcelaMes = UPPER(LEFT(TextoParcelaMes, 1)) & RIGHT(TextoParcelaMes, LEN(TextoParcelaMes) - 1)
VAR NomeParcela = NomeParcelaMes & " de " & FORMAT(MesParcela, "yyyy")
VAR PctHomol = IF(Credenciado > 0, FORMAT(DIVIDE(Homologado, Credenciado), "0%"), "—")
VAR LinhaStatus =
    SWITCH(TRUE(),
        Homologado > 0,
            UNICHAR(9989) & " Homologado em " & NomeParcela & ": " &
            Homologado & " equipe(s) — " & PctHomol & " do total" &
            IF(Saldo > 0, UNICHAR(10) & UNICHAR(9888) & " Saldo não homologado: " & Saldo & " equipe(s)", ""),
        ParcelaProcessada,
            UNICHAR(9888) & " Parcela de " & NomeParcela & " processada — nenhuma equipe homologada" &
            UNICHAR(10) & "Verifique o cadastro no CNES de " & NomeComp,
        UNICHAR(8987) & " Parcela de " & NomeParcela & " ainda não processada" &
        UNICHAR(10) & "Disponível após o dia 15 de " & NomeParcela)
VAR ResultadoTexto =
    UNICHAR(128203) & " 1ª Competência CNES: " & NomeComp & UNICHAR(10) &
    UNICHAR(128197) & " 1ª Parcela financeira: " & NomeParcela & UNICHAR(10) & UNICHAR(10) &
    LinhaStatus
RETURN
SWITCH(TRUE(),
    ISBLANK(DataPub), BLANK(),
    EhEspecifico, BLANK(),
    EhTresCompetencias, ResultadoTexto,
    ResultadoTexto)
```

---

### 2ª Parcela Implantação

Competência CNES M+1, parcela financeira M+3, coluna `Homologação 2`.

```dax
2ª Parcela Implantação = 
VAR Tipo = LOWER(TRIM(MAX('Credenciamento'[Tipo])))
VAR DataPub = MAX('Credenciamento'[Data])
VAR EhEspecifico = CONTAINSSTRING(Tipo, "espec") || CONTAINSSTRING(Tipo, "estabelec")
VAR EhTresCompetencias = CONTAINSSTRING(Tipo, "3") || CONTAINSSTRING(Tipo, "compet")
VAR MesComp2 = EDATE(DataPub, 1)
VAR MesParcela = EDATE(DataPub, 3)
VAR Homologado = MAX('Credenciamento'[Homologação 2])
VAR Credenciado = MAX('Credenciamento'[Credenciado])
VAR Saldo = Credenciado - Homologado
VAR Dia15Parcela = DATE(YEAR(MesParcela), MONTH(MesParcela), 15)
VAR ParcelaProcessada = TODAY() >= Dia15Parcela
VAR TextoCompMes = FORMAT(MesComp2, "mmmm", "pt-BR")
VAR NomeCompMes = UPPER(LEFT(TextoCompMes, 1)) & RIGHT(TextoCompMes, LEN(TextoCompMes) - 1)
VAR NomeComp = NomeCompMes & " de " & FORMAT(MesComp2, "yyyy")
VAR TextoParcelaMes = FORMAT(MesParcela, "mmmm", "pt-BR")
VAR NomeParcelaMes = UPPER(LEFT(TextoParcelaMes, 1)) & RIGHT(TextoParcelaMes, LEN(TextoParcelaMes) - 1)
VAR NomeParcela = NomeParcelaMes & " de " & FORMAT(MesParcela, "yyyy")
VAR PctHomol = IF(Credenciado > 0, FORMAT(DIVIDE(Homologado, Credenciado), "0%"), "—")
VAR LinhaStatus =
    SWITCH(TRUE(),
        Homologado > 0,
            UNICHAR(9989) & " Homologado em " & NomeParcela & ": " &
            Homologado & " equipe(s) — " & PctHomol & " do total" &
            IF(Saldo > 0, UNICHAR(10) & UNICHAR(9888) & " Saldo não homologado: " & Saldo & " equipe(s)", ""),
        ParcelaProcessada,
            UNICHAR(9888) & " Parcela de " & NomeParcela & " processada — nenhuma equipe homologada" &
            UNICHAR(10) & "Verifique o cadastro no CNES de " & NomeComp,
        UNICHAR(8987) & " Parcela de " & NomeParcela & " ainda não processada" &
        UNICHAR(10) & "Disponível após o dia 15 de " & NomeParcela)
VAR ResultadoTexto =
    UNICHAR(128203) & " 2ª Competência CNES: " & NomeComp & UNICHAR(10) &
    UNICHAR(128197) & " 2ª Parcela financeira: " & NomeParcela & UNICHAR(10) & UNICHAR(10) &
    LinhaStatus
RETURN
SWITCH(TRUE(),
    ISBLANK(DataPub), BLANK(),
    EhEspecifico, BLANK(),
    EhTresCompetencias, ResultadoTexto,
    ResultadoTexto)
```

---

### 3ª Parcela Implantação

Competência CNES M+2, parcela financeira M+4, coluna `Homologação 3`.

```dax
3ª Parcela Implantação = 
VAR Tipo = LOWER(TRIM(MAX('Credenciamento'[Tipo])))
VAR DataPub = MAX('Credenciamento'[Data])
VAR EhEspecifico = CONTAINSSTRING(Tipo, "espec") || CONTAINSSTRING(Tipo, "estabelec")
VAR EhTresCompetencias = CONTAINSSTRING(Tipo, "3") || CONTAINSSTRING(Tipo, "compet")
VAR MesComp3 = EDATE(DataPub, 2)
VAR MesParcela = EDATE(DataPub, 4)
VAR Homologado = MAX('Credenciamento'[Homologação 3])
VAR Credenciado = MAX('Credenciamento'[Credenciado])
VAR Saldo = Credenciado - Homologado
VAR Dia15Parcela = DATE(YEAR(MesParcela), MONTH(MesParcela), 15)
VAR ParcelaProcessada = TODAY() >= Dia15Parcela
VAR TextoCompMes = FORMAT(MesComp3, "mmmm", "pt-BR")
VAR NomeCompMes = UPPER(LEFT(TextoCompMes, 1)) & RIGHT(TextoCompMes, LEN(TextoCompMes) - 1)
VAR NomeComp = NomeCompMes & " de " & FORMAT(MesComp3, "yyyy")
VAR TextoParcelaMes = FORMAT(MesParcela, "mmmm", "pt-BR")
VAR NomeParcelaMes = UPPER(LEFT(TextoParcelaMes, 1)) & RIGHT(TextoParcelaMes, LEN(TextoParcelaMes) - 1)
VAR NomeParcela = NomeParcelaMes & " de " & FORMAT(MesParcela, "yyyy")
VAR PctHomol = IF(Credenciado > 0, FORMAT(DIVIDE(Homologado, Credenciado), "0%"), "—")
VAR LinhaStatus =
    SWITCH(TRUE(),
        Homologado > 0,
            UNICHAR(9989) & " Homologado em " & NomeParcela & ": " &
            Homologado & " equipe(s) — " & PctHomol & " do total" &
            IF(Saldo > 0, UNICHAR(10) & UNICHAR(9888) & " Saldo não homologado: " & Saldo & " equipe(s)", ""),
        ParcelaProcessada,
            UNICHAR(9888) & " Parcela de " & NomeParcela & " processada — nenhuma equipe homologada" &
            UNICHAR(10) & "Verifique o cadastro no CNES de " & NomeComp,
        UNICHAR(8987) & " Parcela de " & NomeParcela & " ainda não processada" &
        UNICHAR(10) & "Disponível após o dia 15 de " & NomeParcela)
VAR ResultadoTexto =
    UNICHAR(128203) & " 3ª Competência CNES: " & NomeComp & UNICHAR(10) &
    UNICHAR(128197) & " 3ª Parcela financeira (final): " & NomeParcela & UNICHAR(10) & UNICHAR(10) &
    LinhaStatus
RETURN
SWITCH(TRUE(),
    ISBLANK(DataPub), BLANK(),
    EhEspecifico, BLANK(),
    EhTresCompetencias, ResultadoTexto,
    ResultadoTexto)
```

---

### Legenda Homologação (tooltip de ajuda)

Texto fixo explicativo para colocar em ícone de informação no visual.

```
Após a publicação da portaria o município tem 3 competências CNES para cadastrar as equipes.
A parcela financeira é paga 2 meses após cada competência: 1ª parcela em M+2, 2ª em M+3 e 3ª em M+4.

✅ Homologado: parcela processada com equipes pagas.

⚠️ Zero após dia 15: parcela foi processada mas nenhuma equipe foi homologada.
   Verifique o cadastro no CNES.

⏳ Zero antes do dia 15: a parcela ainda não foi processada pelo Ministério,
   o zero é esperado e não indica problema.
```

---

## Erros já ocorridos no DAX

| Erro | Causa | Solução |
|---|---|---|
| `"Março '13e' 2026"` na dica de ferramenta | `FORMAT(data, "mmmm 'de' yyyy")` — Power BI interpreta texto entre aspas simples como parte do formato | Separar: `FORMAT(data,"mmmm","pt-BR") & " de " & FORMAT(data,"yyyy")` |
