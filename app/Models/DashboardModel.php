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
}
