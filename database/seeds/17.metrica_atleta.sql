INSERT INTO metrica_atleta (cd_esporte, chave, nome)
SELECT e.cd_esporte, m.chave, m.nome
FROM esporte e
CROSS JOIN (VALUES
    ('futsal', 'pontuacao', 'Gols'), ('futsal', 'assistencias', 'Assistências'), ('futsal', 'bloqueios', 'Bloqueios'),
    ('basquete', 'pontuacao', 'Pontos'), ('basquete', 'assistencias', 'Assistências'), ('basquete', 'bloqueios', 'Bloqueios'),
    ('handebol', 'pontuacao', 'Gols'), ('handebol', 'assistencias', 'Assistências'), ('handebol', 'bloqueios', 'Bloqueios'),
    ('vôlei', 'pontuacao', 'Pontos'), ('vôlei', 'assistencias', 'Assistências'), ('vôlei', 'bloqueios', 'Bloqueios'),
    ('queimada', 'pontuacao', 'Eliminações'), ('queimada', 'assistencias', 'Assistências'), ('queimada', 'bloqueios', 'Bloqueios'),
    ('xadrez', 'pontuacao', 'Pontos'), ('tênis de mesa', 'pontuacao', 'Pontos'),
    ('natação', 'pontuacao', 'Pontos'), ('judô', 'pontuacao', 'Pontos')
) AS m(esporte, chave, nome)
WHERE lower(trim(e.nm_esporte)) = m.esporte
ON CONFLICT (cd_esporte, chave) DO UPDATE SET nome = EXCLUDED.nome;
