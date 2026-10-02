<?php

/*
    estaLogado();
        ->  Verifica se o usuário está logado
            retorna bool

    temPapel();
        ->  Verifica se o usuário possui papel
            retorna bool

    temPapelADM();
        ->  Verifica se tem papel de administrador
            retorna bool

    podeGerenciarEscola();
        ->  Verifica se o usuário tem papel de diretor em uma escola
            retorna bool

    exigirPermissaoGerenciarAluno();
        ->  Verifica se o usuário pode gerenciar aluno
            retorna void

    podeGerenciarTime();
        ->  Verifica se o usuário pode gerenciar time
            retorna bool
    
*/

class Permissoes
{
    public static function estaLogado(): bool
    {
        return isset($_SESSION['usuario']['id'])
            && (int) $_SESSION['usuario']['id'] > 0;
    }

    public static function temPapel(string $papel): bool
    {
        $papeis = $_SESSION['usuario']['papeis'] ?? [];

        if (!is_array($papeis)) {
            return false;
        }

        return in_array($papel, $papeis, true);
    }

    public static function temPapelAdm(int $idUsuario): bool
    {
        $idUsuarioSessao = (int) ($_SESSION['usuario']['id'] ?? 0);

        if ($idUsuario <= 0 || $idUsuario !== $idUsuarioSessao) {
            return false;
        }

        return self::temPapel('ADM');
    }

    public static function podeGerenciarEscola(int $idEscola): bool
    {
        $idUsuario = (int) ($_SESSION['usuario']['id'] ?? 0);

        if ($idUsuario <= 0 || $idEscola <= 0) {
            return false;
        }

        // Administrador pode gerenciar qualquer escola
        if (self::temPapel('ADM')) {
            return true;
        }

        // Diretor somente pode gerenciar sua própria escola
        if (self::temPapel('DIR')) {
            $vinculoModel = new VinculoUsuarioEscola();

            $escolaDiretor = $vinculoModel->escolaAtualPorPapeis(
                $idUsuario,
                ['DIR']
            );

            return $escolaDiretor !== null
                && (int) $escolaDiretor === $idEscola;
        }

        return false;
    }

    
    public function exigirPermissaoGerenciarAluno(int $id): void
    {
        $alunoService = new AlunoService();

        $idEscola = $this->$alunoService->obterEscolaDoAluno($id);
        $idUsuario = (int) ($_SESSION['usuario']['id'] ?? 0);

        if 
        ($idEscola === null || !$this->$alunoService->usuarioPodeGerenciarEscola($idUsuario, $idEscola)) {
            throw new Exception('Você não tem permissão para gerenciar este aluno.');
        }
    }

    public static function podeGerenciarTime(int $idTime): bool
    {
        $idUsuario = (int) ($_SESSION['usuario']['id'] ?? 0);

        if ($idUsuario <= 0 || $idTime <= 0) {
            return false;
        }

        // Administrador pode gerenciar qualquer time
        if (self::temPapel('ADM')) {
            return true;
        }

        // Técnico, responsável ou diretor da escola vinculada ao time
        $vinculoTimeModel = new VinculoTime();

        return $vinculoTimeModel->usuarioPodeGerenciarTime($idUsuario, $idTime);
    }

    // Retorna true para ADMs, usado para ocultar
    // os selects de escola nos cadastros caso o usuário
    // seja um diretor, por exemplo. 
    public function resolverEscolaCadastro(array $dados): int
    {
        $vinculoUsuarioEscolaModel = new VinculoUsuarioEscola;
        $papeis = $_SESSION['usuario']['papeis'] ?? [];
        if (in_array('ADM', $papeis, true)) {
            return (int) ($dados['escola'] ?? 0);
        }

        $idUsuario = (int) ($_SESSION['usuario']['id'] ?? 0);
        return (int) ($vinculoUsuarioEscolaModel->escolaGerenciavelPorUsuario($idUsuario) ?? 0);
    }

    public static function podeGerenciarUsuarios(): bool
    {
        return self::temPapel('ADM') || self::temPapel('DIR');
    }

    public static function temPapelDiretor(string $papel): bool
    {
        return self::temPapel($papel);
    }

    public static function temPapelCoordenador(string $papel): bool
    {
        return self::temPapel($papel);
    }

    public static function temPapelProfessor(string $papel): bool
    {
        return self::temPapel($papel);
    }

    public static function temPapelGremista(string $papel): bool
    {
        return self::temPapel($papel);
    }

    public static function temPapelAluno(string $papel): bool
    {
        return self::temPapel($papel);
    }

    public static function temPapelVisitante(string $papel): bool
    {
        return self::temPapel($papel);
    }
}