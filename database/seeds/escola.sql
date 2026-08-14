INSERT INTO escola (nome, categoria_administrativa)
SELECT 'ETEC DE ITANHAÉM', 'PUBLICA'
WHERE NOT EXISTS (
    SELECT 1
    FROM escola
    WHERE UPPER(nome) = UPPER('ETEC DE ITANHAÉM')
);
