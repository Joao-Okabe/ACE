INSERT INTO tipo_etapa (
    nm_tipo_etapa,
    ds_tipo_etapa
)
VALUES
('ELIMINATORIA', 'Os times são eliminados após perderem um confronto.'),
('GRUPOS', 'Os times são distribuídos em grupos e disputam partidas dentro deles.'),
('CLASSIFICACAO', 'Etapa utilizada para determinar a ordem ou os classificados para uma fase posterior.');

INSERT INTO etapa_competicao (
    cd_competicao,
    cd_tipo_etapa,
    nm_etapa,
    ordem,
    descricao
) VALUES 
(9, 1, 'Eliminatória', 1, 'Etapa de eliminatória simples da competição.')
RETURNING cd_etapa_competicao;
