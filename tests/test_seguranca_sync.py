"""Confere bloqueio dos envios e credencial em ambiente, sem acessar a rede."""

import importlib
import os
from pathlib import Path
import subprocess
import sys
import unittest
from unittest.mock import Mock, patch

RAIZ = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(RAIZ))

from credenciamentos_config import obter_headers_api


class SegurancaSincronizacao(unittest.TestCase):
    def test_chave_ausente_ou_vazia_bloqueia_envio(self):
        for valor in (None, '', '   '):
            with self.subTest(valor=valor), patch.dict(os.environ, {}, clear=True):
                if valor is not None:
                    os.environ['CREDENCIAMENTOS_API_KEY'] = valor
                with self.assertRaisesRegex(RuntimeError, 'CREDENCIAMENTOS_API_KEY'):
                    obter_headers_api()

    def test_chave_usa_ambiente_e_nao_vaza_na_mensagem(self):
        # Sentinela de teste, sem validade em qualquer servidor.
        with patch.dict(os.environ, {'CREDENCIAMENTOS_API_KEY': 'sentinela-de-teste'}):
            self.assertEqual(obter_headers_api(), {'X-Api-Key': 'sentinela-de-teste'})

    def test_funcoes_de_escrita_param_antes_do_post_sem_chave(self):
        sync = importlib.import_module('sync')
        for nome in ('sync', 'sync_portarias', 'sync_represados'):
            with self.subTest(funcao=nome), patch.dict(os.environ, {}, clear=True), \
                    patch.object(sync.requests, 'post') as post:
                with self.assertRaises(RuntimeError):
                    getattr(sync, nome)([{'ibge': 123456}])
                post.assert_not_called()

    def test_post_recebe_somente_chave_do_ambiente(self):
        sync = importlib.import_module('sync')
        resposta = Mock()
        resposta.json.return_value = {'inserted': 1}
        for nome in ('sync', 'sync_portarias', 'sync_represados'):
            with self.subTest(funcao=nome), \
                    patch.dict(os.environ, {'CREDENCIAMENTOS_API_KEY': 'sentinela-de-teste'}), \
                    patch.object(sync.requests, 'post', return_value=resposta) as post:
                getattr(sync, nome)([{'ibge': 123456}])
                self.assertEqual(post.call_count, 1)
                self.assertEqual(post.call_args.kwargs['headers'],
                                 {'X-Api-Key': 'sentinela-de-teste'})

    def test_scripts_falham_antes_de_ler_bases_sem_chave(self):
        ambiente = dict(os.environ)
        ambiente.pop('CREDENCIAMENTOS_API_KEY', None)
        for script in ('sync.py', 'sync_base_filtro.py', 'sync_pagos_aps360.py'):
            with self.subTest(script=script):
                resultado = subprocess.run(
                    [sys.executable, str(RAIZ / script)], cwd=RAIZ,
                    env=ambiente, capture_output=True, text=True, timeout=45,
                )
                self.assertNotEqual(resultado.returncode, 0)
                self.assertIn('CREDENCIAMENTOS_API_KEY', resultado.stderr)
                self.assertNotIn('Lendo ', resultado.stdout)
                self.assertNotIn('Enviando ', resultado.stdout)


if __name__ == '__main__':
    unittest.main()
