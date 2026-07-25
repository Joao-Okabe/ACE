CREATE OR REPLACE FUNCTION atualizar_timestamp()
RETURNS TRIGGER AS
$$
BEGIN
    NEW.atualizado_em = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_usuario_timestamp
BEFORE UPDATE ON usuario
FOR EACH ROW
EXECUTE FUNCTION atualizar_timestamp();

CREATE TRIGGER trg_aluno_timestamp
BEFORE UPDATE ON aluno
FOR EACH ROW
EXECUTE FUNCTION atualizar_timestamp();