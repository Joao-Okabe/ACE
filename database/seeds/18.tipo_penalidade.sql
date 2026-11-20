INSERT INTO tipo_penalidade (nm_tipo_penalidade, descricao)
VALUES
    ('FALTA', 'Falta registrada durante a partida.'),
    ('CARTAO_AMARELO', 'Cartão amarelo aplicado durante a partida.'),
    ('CARTAO_VERMELHO', 'Cartão vermelho aplicado durante a partida.'),
    ('PENALIDADE', 'Penalidade registrada durante a partida.')
ON CONFLICT (nm_tipo_penalidade) DO NOTHING;
