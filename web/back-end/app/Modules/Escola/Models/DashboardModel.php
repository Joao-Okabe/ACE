<?php

class Dashboard extends Model
{
    // Conta alunos vinculados às mesmas escolas do usuário requisitante.
    public function qtAluno(int $idUsuario): int
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(DISTINCT a.cd_aluno) AS total
            FROM aluno a
            INNER JOIN vinculo_usuario_escola va
                ON va.cd_usuario = a.cd_usuario
                AND va.ativo = TRUE
            INNER JOIN vinculo_usuario_escola vr
                ON vr.cd_escola = va.cd_escola
                AND vr.cd_usuario = :usuario
                AND vr.ativo = TRUE
            WHERE a.ativo = TRUE
        ");

        $stmt->execute([
            ':usuario' => $idUsuario
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function qtTime(int $idUsuario): int
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(DISTINCT t.cd_time) AS total
            FROM time t
            INNER JOIN vinculo_time_escola vt
                ON vt.cd_time = t.cd_time
                AND vt.ativo = TRUE
            INNER JOIN vinculo_usuario_escola vu
                ON vu.cd_escola = vt.cd_escola
                AND vu.cd_usuario = :usuario
                AND vu.ativo = TRUE
            WHERE t.ativo = TRUE
        ");

        $stmt->execute([
            ':usuario' => $idUsuario
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function qtCompeticao(int $idUsuario): int
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(DISTINCT c.cd_competicao) AS total
            FROM competicao c
            WHERE c.cd_criador = :usuario_criador
               OR EXISTS (
                    SELECT 1
                    FROM usuario_papel up
                    INNER JOIN papel p ON p.cd_papel = up.cd_papel
                    WHERE up.cd_usuario = :usuario_adm
                      AND p.nm_papel = 'ADM'
               )
               OR EXISTS (
                    SELECT 1
                    FROM vinculo_usuario_escola vu
                    WHERE vu.cd_usuario = :usuario_escola
                      AND vu.cd_escola = c.cd_escola
                      AND vu.ativo = TRUE
               )
               OR EXISTS (
                    SELECT 1
                    FROM vinculo_usuario_escola vu
                    INNER JOIN vinculo_usuario_escola vc
                        ON vc.cd_escola = vu.cd_escola
                        AND vc.cd_usuario = c.cd_criador
                        AND vc.ativo = TRUE
                    WHERE vu.cd_usuario = :usuario_criador_escola
                      AND vu.ativo = TRUE
               )
        ");

        $stmt->execute([
            ':usuario_criador' => $idUsuario,
            ':usuario_adm' => $idUsuario,
            ':usuario_escola' => $idUsuario,
            ':usuario_criador_escola' => $idUsuario
        ]);

        return (int) $stmt->fetchColumn();
    }
}
