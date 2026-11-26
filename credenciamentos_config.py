"""Credencial de escrita compartilhada pelos sincronizadores do painel.

Não lê arquivos de credenciais e não define valores de reserva. Consultas
somente de leitura podem importar os sincronizadores sem configurar a chave.
"""

import os


def obter_headers_api():
    """Exige a chave no ambiente antes de preparar qualquer envio."""
    chave = os.environ.get('CREDENCIAMENTOS_API_KEY', '').strip()
    if not chave:
        raise RuntimeError(
            'Defina CREDENCIAMENTOS_API_KEY no ambiente desta sessão antes '
            'de sincronizar. Nenhum dado foi enviado. '
            'Consulte docs/seguranca-sincronizacao.md.'
        )
    return {'X-Api-Key': chave}
