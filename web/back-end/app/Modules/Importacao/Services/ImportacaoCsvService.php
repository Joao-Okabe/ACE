<?php

class ImportacaoCsvValidationException extends RuntimeException
{
    public function __construct(public readonly array $linhas)
    {
        parent::__construct('Corrija as linhas inválidas antes de confirmar a importação.');
    }
}

class ImportacaoCsvService
{
    private const MAXIMO_LINHAS = 2000;

    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connect();
    }

    public static function parseCsv(string $caminho, string $tipo): array
    {
        self::validarTipo($tipo);

        $arquivo = fopen($caminho, 'rb');
        if ($arquivo === false) {
            throw new InvalidArgumentException('Não foi possível ler o arquivo CSV.');
        }

        try {
            $cabecalhoOriginal = fgets($arquivo);
            if ($cabecalhoOriginal === false) {
                throw new InvalidArgumentException('O arquivo CSV está vazio.');
            }

            $delimitador = ',';
            $maiorQuantidadeColunas = 0;
            foreach ([',', ';', "\t"] as $candidato) {
                $colunas = str_getcsv(rtrim($cabecalhoOriginal, "\r\n"), $candidato, '"', '\\');
                if (count($colunas) > $maiorQuantidadeColunas) {
                    $maiorQuantidadeColunas = count($colunas);
                    $delimitador = $candidato;
                }
            }

            rewind($arquivo);
            $cabecalhos = fgetcsv($arquivo, 0, $delimitador, '"', '\\');
            if (!is_array($cabecalhos) || $cabecalhos === []) {
                throw new InvalidArgumentException('Não foi possível identificar o cabeçalho do CSV.');
            }

            $cabecalhos[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $cabecalhos[0]) ?? '';
            $indices = [];
            foreach ($cabecalhos as $indice => $cabecalho) {
                $campo = self::mapearCabecalho((string) $cabecalho, $tipo);
                if ($campo === null) {
                    continue;
                }
                if (isset($indices[$campo])) {
                    throw new InvalidArgumentException(
                        "O cabeçalho contém mais de uma coluna para o campo {$campo}."
                    );
                }
                $indices[$campo] = $indice;
            }

            $obrigatorios = $tipo === 'alunos'
                ? ['nome', 'email', 'ra', 'data_nascimento']
                : ['nome', 'categoria_administrativa'];
            $ausentes = array_values(array_diff($obrigatorios, array_keys($indices)));
            if ($ausentes !== []) {
                throw new InvalidArgumentException(
                    'Colunas obrigatórias ausentes: ' . implode(', ', $ausentes) . '.'
                );
            }

            $linhas = [];
            $numeroLinha = 1;
            while (($valores = fgetcsv($arquivo, 0, $delimitador, '"', '\\')) !== false) {
                $numeroLinha++;
                if ($valores === [null] || count(array_filter(
                    $valores,
                    static fn ($valor): bool => trim((string) $valor) !== ''
                )) === 0) {
                    continue;
                }

                if (count($linhas) >= self::MAXIMO_LINHAS) {
                    throw new InvalidArgumentException(
                        'O arquivo excede o limite de 2.000 registros por importação.'
                    );
                }

                $dados = [];
                foreach ($indices as $campo => $indice) {
                    $dados[$campo] = isset($valores[$indice])
                        ? trim((string) $valores[$indice])
                        : '';
                }

                $erros = count($valores) === count($cabecalhos)
                    ? []
                    : [[
                        'campo' => null,
                        'mensagem' => 'A quantidade de colunas desta linha difere do cabeçalho.',
                    ]];
                $linhas[] = [
                    'linha' => $numeroLinha,
                    'dados' => $dados,
                    'erros' => $erros,
                ];
            }

            if ($linhas === []) {
                throw new InvalidArgumentException('O CSV não contém registros para importar.');
            }

            return $linhas;
        } finally {
            fclose($arquivo);
        }
    }

    public function importar(string $tipo, string $caminho, ?int $idEscola): array
    {
        self::validarTipo($tipo);
        $this->exigirPermissao($tipo, $idEscola);

        $linhas = self::parseCsv($caminho, $tipo);
        $rascunho = [
            'tipo' => $tipo,
            'escola_id' => $tipo === 'alunos' ? $idEscola : null,
            'linhas' => $linhas,
        ];
        $this->validarLinhas($rascunho);
        $this->pdo->exec('DELETE FROM importacao_csv WHERE expira_em <= CURRENT_TIMESTAMP');

        $id = self::uuid();
        $stmt = $this->pdo->prepare("
            INSERT INTO importacao_csv (
                id,
                cd_usuario,
                tipo,
                cd_escola,
                dados,
                expira_em
            ) VALUES (
                :id,
                :usuario,
                :tipo,
                :escola,
                CAST(:dados AS JSONB),
                CURRENT_TIMESTAMP + INTERVAL '24 hours'
            )
        ");
        $stmt->execute([
            ':id' => $id,
            ':usuario' => AuthContext::id(),
            ':tipo' => $tipo,
            ':escola' => $rascunho['escola_id'],
            ':dados' => json_encode($rascunho, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        ]);

        return $this->resumo($id, 'PENDENTE', $rascunho);
    }

    public function escolasDisponiveis(): array
    {
        $idUsuario = AuthContext::id();
        if ($idUsuario === null) {
            throw new DomainException('Autenticação necessária.');
        }

        if (Permissoes::temPapel('ADM')) {
            $stmt = $this->pdo->query("
                SELECT cd_escola, nm_escola AS nome
                FROM escola
                WHERE ativa = TRUE
                ORDER BY nm_escola
            ");
        } elseif (Permissoes::temPapel('DIR')) {
            $stmt = $this->pdo->prepare("
                SELECT DISTINCT e.cd_escola, e.nm_escola AS nome
                FROM escola e
                INNER JOIN vinculo_usuario_escola vue
                    ON vue.cd_escola = e.cd_escola AND vue.ativo = TRUE
                INNER JOIN papel p
                    ON p.cd_papel = vue.cd_papel AND p.nm_papel = 'DIR'
                WHERE vue.cd_usuario = :usuario AND e.ativa = TRUE
                ORDER BY e.nm_escola
            ");
            $stmt->execute([':usuario' => $idUsuario]);
        } else {
            throw new DomainException('Você não tem permissão para importar alunos.');
        }

        return array_map(
            static fn (array $escola): array => [
                'cd_escola' => (int) $escola['cd_escola'],
                'nome' => $escola['nome'],
            ],
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function editarLinha(string $id, int $numeroLinha, array $alteracoes): array
    {
        $this->pdo->beginTransaction();
        try {
            $rascunhoBanco = $this->buscarRascunho($id, true);
            $this->exigirPermissao($rascunhoBanco['tipo'], $rascunhoBanco['escola_id']);
            $this->exigirPendente($rascunhoBanco);

            $rascunho = $rascunhoBanco['dados'];
            $indiceLinha = $this->indiceLinha($rascunho, $numeroLinha);
            $camposPermitidos = $rascunho['tipo'] === 'alunos'
                ? ['nome', 'email', 'ra', 'data_nascimento', 'sexo', 'telefone', 'cep']
                : ['nome', 'email', 'telefone', 'cep', 'numero', 'categoria_administrativa'];
            $camposDesconhecidos = array_diff(array_keys($alteracoes), $camposPermitidos);
            if ($camposDesconhecidos !== []) {
                throw new InvalidArgumentException(
                    'Campos não editáveis: ' . implode(', ', $camposDesconhecidos) . '.'
                );
            }

            foreach ($alteracoes as $campo => $valor) {
                if (!is_string($valor) && $valor !== null && !is_numeric($valor)) {
                    throw new InvalidArgumentException("O campo {$campo} deve ser um texto.");
                }
                $rascunho['linhas'][$indiceLinha]['dados'][$campo] = trim((string) ($valor ?? ''));
            }
            $rascunho['linhas'][$indiceLinha]['erros'] = [];

            $this->validarLinhas($rascunho);
            $this->salvarRascunho($id, $rascunho);
            $this->pdo->commit();

            return $this->resumo($id, $rascunhoBanco['status'], $rascunho);
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function confirmar(string $id): array
    {
        $this->pdo->beginTransaction();

        try {
            $rascunhoBanco = $this->buscarRascunho($id, true);
            $this->exigirPermissao($rascunhoBanco['tipo'], $rascunhoBanco['escola_id']);
            $this->exigirPendente($rascunhoBanco);
            $rascunho = $rascunhoBanco['dados'];
            $this->validarLinhas($rascunho);

            if ($this->contarErros($rascunho['linhas']) > 0) {
                $this->salvarRascunho($id, $rascunho);
                $this->pdo->commit();
                throw new ImportacaoCsvValidationException($rascunho['linhas']);
            }

            if ($rascunho['tipo'] === 'alunos') {
                $credenciais = $this->cadastrarAlunos($rascunho);
            } else {
                $quantidade = $this->cadastrarEscolas($rascunho);
                $credenciais = [];
            }

            $stmt = $this->pdo->prepare("
                UPDATE importacao_csv
                SET status = 'CONCLUIDA', finalizada_em = CURRENT_TIMESTAMP
                WHERE id = :id
            ");
            $stmt->execute([':id' => $id]);
            $this->pdo->commit();

            return [
                'id' => $id,
                'tipo' => $rascunho['tipo'],
                'status' => 'CONCLUIDA',
                'registros_importados' => $rascunho['tipo'] === 'alunos'
                    ? count($credenciais)
                    : $quantidade,
                'credenciais' => $credenciais,
            ];
        } catch (ImportacaoCsvValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    private function cadastrarAlunos(array $rascunho): array
    {
        $stmtPapel = $this->pdo->prepare("SELECT cd_papel FROM papel WHERE nm_papel = 'ALU'");
        $stmtPapel->execute();
        $idPapelAluno = $stmtPapel->fetchColumn();
        if ($idPapelAluno === false) {
            throw new RuntimeException('Papel ALU não encontrado.');
        }

        $usuarioStmt = $this->pdo->prepare("
            INSERT INTO usuario (nm_usuario, email, senha)
            VALUES (:nome, :email, :senha)
            RETURNING cd_usuario
        ");
        $alunoStmt = $this->pdo->prepare("
            INSERT INTO aluno (cd_usuario, ra, data_nascimento, sexo, telefone, cep)
            VALUES (:usuario, :ra, :nascimento, :sexo, :telefone, :cep)
        ");
        $vinculoStmt = $this->pdo->prepare("
            INSERT INTO vinculo_usuario_escola (cd_usuario, cd_escola, cd_papel)
            VALUES (:usuario, :escola, :papel)
        ");
        $credenciais = [];

        foreach ($rascunho['linhas'] as $linha) {
            $dados = $linha['dados'];
            $senha = bin2hex(random_bytes(12));
            $usuarioStmt->execute([
                ':nome' => $dados['nome'],
                ':email' => $dados['email'],
                ':senha' => password_hash($senha, PASSWORD_DEFAULT),
            ]);
            $idUsuario = (int) $usuarioStmt->fetchColumn();
            $alunoStmt->execute([
                ':usuario' => $idUsuario,
                ':ra' => $dados['ra'],
                ':nascimento' => $dados['data_nascimento'],
                ':sexo' => $dados['sexo'] ?: null,
                ':telefone' => $dados['telefone'] ?: null,
                ':cep' => $dados['cep'] ?: null,
            ]);
            $vinculoStmt->execute([
                ':usuario' => $idUsuario,
                ':escola' => $rascunho['escola_id'],
                ':papel' => $idPapelAluno,
            ]);
            $credenciais[] = [
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'senha_inicial' => $senha,
            ];
        }

        return $credenciais;
    }

    private function cadastrarEscolas(array $rascunho): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO escola (
                nm_escola, telefone, cep, numero, email, categoria_administrativa
            ) VALUES (
                :nome, :telefone, :cep, :numero, :email, :categoria
            )
        ");
        foreach ($rascunho['linhas'] as $linha) {
            $dados = $linha['dados'];
            $stmt->execute([
                ':nome' => $dados['nome'],
                ':telefone' => $dados['telefone'] ?: null,
                ':cep' => $dados['cep'] ?: null,
                ':numero' => $dados['numero'] ?: null,
                ':email' => $dados['email'] ?: null,
                ':categoria' => $dados['categoria_administrativa'],
            ]);
        }

        return count($rascunho['linhas']);
    }

    private function validarLinhas(array &$rascunho): void
    {
        $valoresUnicos = ['ra' => [], 'email' => [], 'nome' => []];
        foreach ($rascunho['linhas'] as $indiceLinha => &$linha) {
            $errosCsv = array_values(array_filter(
                $linha['erros'] ?? [],
                static fn (array $erro): bool => ($erro['campo'] ?? null) === null
            ));
            [$dados, $erros] = self::normalizarRegistro($linha['dados'], $rascunho['tipo']);
            $linha['dados'] = $dados;
            $linha['erros'] = [...$errosCsv, ...$erros];

            if ($rascunho['tipo'] === 'alunos') {
                foreach (['ra', 'email'] as $campo) {
                    if ($dados[$campo] === '') {
                        continue;
                    }
                    $chave = mb_strtolower($dados[$campo], 'UTF-8');
                    if (isset($valoresUnicos[$campo][$chave])) {
                        $mensagem = "Valor duplicado no arquivo: {$campo}.";
                        $linha['erros'][] = ['campo' => $campo, 'mensagem' => $mensagem];
                        $rascunho['linhas'][$valoresUnicos[$campo][$chave]]['erros'][] = [
                            'campo' => $campo,
                            'mensagem' => $mensagem,
                        ];
                    } else {
                        $valoresUnicos[$campo][$chave] = $indiceLinha;
                    }
                }
            } else {
                $chave = mb_strtolower($dados['nome'], 'UTF-8');
                if ($chave !== '' && isset($valoresUnicos['nome'][$chave])) {
                    $linha['erros'][] = [
                        'campo' => 'nome',
                        'mensagem' => 'Nome de escola duplicado no arquivo.',
                    ];
                    $rascunho['linhas'][$valoresUnicos['nome'][$chave]]['erros'][] = [
                        'campo' => 'nome',
                        'mensagem' => 'Nome de escola duplicado no arquivo.',
                    ];
                } elseif ($chave !== '') {
                    $valoresUnicos['nome'][$chave] = $indiceLinha;
                }
            }
        }
        unset($linha);

        $chavesExistentes = $rascunho['tipo'] === 'alunos'
            ? ['ra' => 'aluno.ra', 'email' => 'usuario.email']
            : ['nome' => 'escola.nm_escola'];
        foreach ($chavesExistentes as $campo => $tabelaColuna) {
            $valores = array_keys($valoresUnicos[$campo]);
            $existentes = $this->buscarValoresExistentes($tabelaColuna, $valores);
            if ($existentes === []) {
                continue;
            }
            foreach ($rascunho['linhas'] as &$linha) {
                $chave = mb_strtolower((string) ($linha['dados'][$campo] ?? ''), 'UTF-8');
                if (in_array($chave, $existentes, true)) {
                    $linha['erros'][] = [
                        'campo' => $campo,
                        'mensagem' => $campo === 'nome'
                            ? 'Já existe uma escola cadastrada com este nome.'
                            : "Já existe um cadastro com este {$campo}.",
                    ];
                }
            }
            unset($linha);
        }
    }

    private function buscarValoresExistentes(string $tabelaColuna, array $valores): array
    {
        if ($valores === []) {
            return [];
        }
        $expressoes = [
            'aluno.ra' => 'LOWER(a.ra)',
            'usuario.email' => 'LOWER(u.email)',
            'escola.nm_escola' => 'LOWER(e.nm_escola)',
        ];
        $joins = [
            'aluno.ra' => 'FROM aluno a',
            'usuario.email' => 'FROM usuario u',
            'escola.nm_escola' => 'FROM escola e',
        ];
        $expressao = $expressoes[$tabelaColuna];
        $placeholders = [];
        $parametros = [];
        foreach (array_values($valores) as $indice => $valor) {
            $nome = ':valor_' . $indice;
            $placeholders[] = $nome;
            $parametros[$nome] = $valor;
        }
        $stmt = $this->pdo->prepare(
            'SELECT ' . $expressao . ' AS valor ' . $joins[$tabelaColuna]
            . ' WHERE ' . $expressao . ' IN (' . implode(', ', $placeholders) . ')'
        );
        $stmt->execute($parametros);

        return array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    private static function normalizarRegistro(array $dados, string $tipo): array
    {
        $campos = $tipo === 'alunos'
            ? ['nome', 'email', 'ra', 'data_nascimento', 'sexo', 'telefone', 'cep']
            : ['nome', 'email', 'telefone', 'cep', 'numero', 'categoria_administrativa'];
        $normalizados = [];
        foreach ($campos as $campo) {
            $valor = $dados[$campo] ?? '';
            if (!is_scalar($valor)) {
                $valor = '';
            }
            $normalizados[$campo] = trim((string) $valor);
        }

        $erros = [];
        $adicionarErro = static function (string $campo, string $mensagem) use (&$erros): void {
            $erros[] = ['campo' => $campo, 'mensagem' => $mensagem];
        };

        if ($normalizados['nome'] === '') {
            $adicionarErro('nome', 'Informe o nome.');
        } elseif (mb_strlen($normalizados['nome'], 'UTF-8') > 150) {
            $adicionarErro('nome', 'O nome deve ter no máximo 150 caracteres.');
        }

        if ($tipo === 'alunos') {
            $normalizados['email'] = mb_strtolower($normalizados['email'], 'UTF-8');
            if (
                $normalizados['email'] === ''
                || mb_strlen($normalizados['email'], 'UTF-8') > 150
                || !filter_var($normalizados['email'], FILTER_VALIDATE_EMAIL)
            ) {
                $adicionarErro('email', 'Informe um e-mail válido com até 150 caracteres.');
            }
            if ($normalizados['ra'] === '' || mb_strlen($normalizados['ra'], 'UTF-8') > 20) {
                $adicionarErro('ra', 'Informe um RA com até 20 caracteres.');
            }
            $normalizados['data_nascimento'] = self::normalizarData($normalizados['data_nascimento']);
            if ($normalizados['data_nascimento'] === null) {
                $adicionarErro('data_nascimento', 'Informe uma data válida (AAAA-MM-DD ou DD/MM/AAAA).');
                $normalizados['data_nascimento'] = '';
            } elseif (
                $normalizados['data_nascimento'] > date('Y-m-d')
                || $normalizados['data_nascimento'] < date('Y-m-d', strtotime('-120 years'))
            ) {
                $adicionarErro('data_nascimento', 'Informe uma data de nascimento plausível.');
            }

            $sexo = self::normalizarTexto($normalizados['sexo']);
            $normalizados['sexo'] = match ($sexo) {
                '', 'nao_informado', 'nao_informada' => '',
                'm', 'masculino' => 'M',
                'f', 'feminino' => 'F',
                'o', 'outro', 'outra' => 'O',
                default => $normalizados['sexo'],
            };
            if ($normalizados['sexo'] !== '' && !in_array($normalizados['sexo'], ['M', 'F', 'O'], true)) {
                $adicionarErro('sexo', 'Sexo inválido. Use M, F ou O.');
            }
        } else {
            $categoria = self::normalizarTexto($normalizados['categoria_administrativa']);
            $normalizados['categoria_administrativa'] = match ($categoria) {
                'municipal', 'escola_municipal' => 'Escola Municipal',
                'estadual', 'escola_estadual' => 'Escola Estadual',
                'privada', 'particular', 'escola_privada' => 'Privada',
                default => $normalizados['categoria_administrativa'],
            };
            if (!in_array(
                $normalizados['categoria_administrativa'],
                ['Escola Municipal', 'Escola Estadual', 'Privada'],
                true
            )) {
                $adicionarErro('categoria_administrativa', 'Categoria inválida. Use Escola Municipal, Escola Estadual ou Privada.');
            }
            if (mb_strlen($normalizados['numero'], 'UTF-8') > 20) {
                $adicionarErro('numero', 'O número do endereço deve ter no máximo 20 caracteres.');
            }
        }

        if ($tipo === 'escolas' && $normalizados['email'] !== '') {
            $normalizados['email'] = mb_strtolower($normalizados['email'], 'UTF-8');
            if (
                mb_strlen($normalizados['email'], 'UTF-8') > 150
                || !filter_var($normalizados['email'], FILTER_VALIDATE_EMAIL)
            ) {
                $adicionarErro('email', 'Informe um e-mail válido com até 150 caracteres.');
            }
        }

        $telefone = preg_replace('/\D/', '', $normalizados['telefone']) ?? '';
        if ($telefone !== '' && !in_array(strlen($telefone), [10, 11], true)) {
            $adicionarErro('telefone', 'Informe um telefone com DDD (10 ou 11 dígitos).');
        }
        if ($telefone !== '') {
            $normalizados['telefone'] = $telefone;
        }

        $cep = preg_replace('/\D/', '', $normalizados['cep']) ?? '';
        if ($cep !== '' && strlen($cep) !== 8) {
            $adicionarErro('cep', 'Informe um CEP válido com 8 dígitos.');
        }
        if ($cep !== '') {
            $normalizados['cep'] = $cep;
        }

        return [$normalizados, $erros];
    }

    private static function normalizarData(string $valor): ?string
    {
        if ($valor === '') {
            return null;
        }
        foreach (['!Y-m-d', '!d/m/Y'] as $formato) {
            $data = DateTimeImmutable::createFromFormat($formato, $valor);
            $erros = DateTimeImmutable::getLastErrors();
            if (
                $data !== false
                && ($erros === false || ($erros['warning_count'] === 0 && $erros['error_count'] === 0))
            ) {
                return $data->format('Y-m-d');
            }
        }
        return null;
    }

    private static function normalizarTexto(string $valor): string
    {
        $valor = mb_strtolower(trim($valor), 'UTF-8');
        $valor = strtr($valor, [
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ç' => 'c',
        ]);

        return trim(preg_replace('/[^a-z0-9]+/', '_', $valor) ?? '', '_');
    }

    private static function mapearCabecalho(string $cabecalho, string $tipo): ?string
    {
        $chave = self::normalizarTexto($cabecalho);
        $aliases = [
            'email' => ['email', 'e_mail'],
            'telefone' => ['telefone', 'celular', 'phone'],
            'cep' => ['cep', 'codigo_postal', 'postal_code'],
        ];
        if ($tipo === 'alunos') {
            $aliases += [
                'nome' => ['nome', 'name', 'aluno', 'nm_usuario', 'nm_aluno'],
                'ra' => ['ra', 'registro_academico', 'registro'],
                'data_nascimento' => ['data_nascimento', 'nascimento', 'data_de_nascimento', 'birth_date'],
                'sexo' => ['sexo', 'genero', 'gender'],
            ];
        } else {
            $aliases += [
                'nome' => ['nome', 'name', 'escola', 'nm_escola'],
                'numero' => ['numero', 'número', 'numero_endereco', 'number'],
                'categoria_administrativa' => [
                    'categoria',
                    'categoria_administrativa',
                    'categoria_escolar',
                    'tipo_escola',
                ],
            ];
        }

        foreach ($aliases as $campo => $opcoes) {
            if (in_array($chave, $opcoes, true)) {
                return $campo;
            }
        }

        return null;
    }

    private function buscarRascunho(string $id, bool $bloquear = false): array
    {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $id)) {
            throw new OutOfBoundsException('Importação não encontrada.');
        }
        $sql = "
            SELECT tipo, cd_escola, dados, status
            FROM importacao_csv
            WHERE id = :id
              AND cd_usuario = :usuario
              AND expira_em > CURRENT_TIMESTAMP
        ";
        if ($bloquear) {
            $sql .= ' FOR UPDATE';
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':usuario' => AuthContext::id(),
        ]);
        $registro = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($registro === false) {
            throw new OutOfBoundsException('Importação não encontrada ou expirada.');
        }

        $dados = is_array($registro['dados'])
            ? $registro['dados']
            : json_decode((string) $registro['dados'], true, 512, JSON_THROW_ON_ERROR);

        return [
            'tipo' => $registro['tipo'],
            'escola_id' => $registro['cd_escola'] === null ? null : (int) $registro['cd_escola'],
            'dados' => $dados,
            'status' => $registro['status'],
        ];
    }

    private function salvarRascunho(string $id, array $rascunho): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE importacao_csv
            SET dados = CAST(:dados AS JSONB)
            WHERE id = :id AND cd_usuario = :usuario AND status = 'PENDENTE'
        ");
        $stmt->execute([
            ':dados' => json_encode($rascunho, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            ':id' => $id,
            ':usuario' => AuthContext::id(),
        ]);
    }

    private function exigirPermissao(string $tipo, ?int $idEscola): void
    {
        if (AuthContext::id() === null) {
            throw new DomainException('Autenticação necessária.');
        }
        if ($tipo === 'escolas') {
            if (!Permissoes::temPapel('ADM')) {
                throw new DomainException('Somente administradores podem importar escolas.');
            }
            return;
        }
        if (!Permissoes::temPapel('ADM') && !Permissoes::temPapel('DIR')) {
            throw new DomainException('Você não tem permissão para importar alunos.');
        }
        if ($idEscola === null || $idEscola <= 0) {
            throw new InvalidArgumentException('Informe escola_id para importar alunos.');
        }
        if (!Permissoes::podeGerenciarEscola($idEscola)) {
            throw new DomainException('Você não tem permissão para importar alunos nesta escola.');
        }
        $stmt = $this->pdo->prepare('SELECT 1 FROM escola WHERE cd_escola = :id AND ativa = TRUE');
        $stmt->execute([':id' => $idEscola]);
        if ($stmt->fetchColumn() === false) {
            throw new InvalidArgumentException('Escola inexistente ou inativa.');
        }
    }

    private function exigirPendente(array $rascunho): void
    {
        if ($rascunho['status'] !== 'PENDENTE') {
            throw new LogicException('Esta importação já foi confirmada.');
        }
    }

    private function indiceLinha(array $rascunho, int $numeroLinha): int
    {
        foreach ($rascunho['linhas'] as $indice => $linha) {
            if ((int) $linha['linha'] === $numeroLinha) {
                return $indice;
            }
        }
        throw new OutOfBoundsException('Linha não encontrada nesta importação.');
    }

    private function resumo(string $id, string $status, array $rascunho): array
    {
        $erros = $this->contarErros($rascunho['linhas']);

        return [
            'id' => $id,
            'tipo' => $rascunho['tipo'],
            'status' => $status,
            'total_linhas' => count($rascunho['linhas']),
            'linhas_validas' => count($rascunho['linhas']) - $erros,
            'linhas_com_erro' => $erros,
            'escola_id' => $rascunho['escola_id'],
            'linhas' => $rascunho['linhas'],
        ];
    }

    private function contarErros(array $linhas): int
    {
        return count(array_filter(
            $linhas,
            static fn (array $linha): bool => ($linha['erros'] ?? []) !== []
        ));
    }

    private static function validarTipo(string $tipo): void
    {
        if (!in_array($tipo, ['alunos', 'escolas'], true)) {
            throw new InvalidArgumentException('Tipo inválido. Use alunos ou escolas.');
        }
    }

    private static function uuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);

        return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-'
            . substr($hex, 12, 4) . '-' . substr($hex, 16, 4) . '-'
            . substr($hex, 20, 12);
    }
}
