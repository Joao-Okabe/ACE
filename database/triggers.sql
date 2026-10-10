/*Cria função para atualizar a coluna "atualizado_em" de qualquer tabela  */
CREATE OR REPLACE FUNCTION atualizar_timestamp()
RETURNS TRIGGER AS
$$
BEGIN
    NEW.atualizado_em = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

/* Chama a função de atualizar antes de alterar as tabelas */
CREATE TRIGGER trg_usuario_timestamp
BEFORE UPDATE ON usuario
FOR EACH ROW
EXECUTE FUNCTION atualizar_timestamp();

CREATE TRIGGER trg_aluno_timestamp
BEFORE UPDATE ON aluno
FOR EACH ROW
EXECUTE FUNCTION atualizar_timestamp();

CREATE TRIGGER trg_time_timestamp
BEFORE UPDATE ON time
FOR EACH ROW
EXECUTE FUNCTION atualizar_timestamp();