UPDATE tipo_etapa AS legado
SET nm_tipo_etapa = 'ELIMINATORIA'
WHERE legado.nm_tipo_etapa = 'MATA_MATA'
  AND NOT EXISTS (
      SELECT 1
      FROM tipo_etapa AS atual
      WHERE atual.nm_tipo_etapa = 'ELIMINATORIA'
  );

UPDATE etapa_competicao AS etapa
SET cd_tipo_etapa = atual.cd_tipo_etapa
FROM tipo_etapa AS legado
JOIN tipo_etapa AS atual
  ON atual.nm_tipo_etapa = 'ELIMINATORIA'
WHERE etapa.cd_tipo_etapa = legado.cd_tipo_etapa
  AND legado.nm_tipo_etapa = 'MATA_MATA';

DELETE FROM tipo_etapa AS legado
WHERE legado.nm_tipo_etapa = 'MATA_MATA'
  AND EXISTS (
      SELECT 1
      FROM tipo_etapa AS atual
      WHERE atual.nm_tipo_etapa = 'ELIMINATORIA'
  );

INSERT INTO tipo_etapa (nm_tipo_etapa, ds_tipo_etapa)
VALUES
('GRUPOS', 'Fase de grupos'),
('ELIMINATORIA', 'Fase eliminatória'),
('FINAL', 'Final da competição')
ON CONFLICT (nm_tipo_etapa) DO UPDATE
SET ds_tipo_etapa = EXCLUDED.ds_tipo_etapa;