<?php

class Filtro
{
	public function filtrosAluno(array $filtros): array
	{
		$onde = [];
		$parametros = [];

		if (!empty($filtros['nome'])) {
			$onde[] = 'u.nm_usuario ILIKE :nome';
			$parametros[':nome'] = '%' . $filtros['nome'] . '%';
		}

		if (!empty($filtros['escola'])) {
			$onde[] = 'a.cd_usuario IN (
				SELECT up.cd_usuario
				FROM vinculo_usuario_escola up
<<<<<<< HEAD:app/Core/Filtro.php
				WHERE up.cd_escola = :cd_escola
=======
				INNER JOIN papel p ON p.cd_papel = up.cd_papel
				WHERE up.cd_escola = :cd_escola
					AND p.nm_papel = \'ALU\'
>>>>>>> api_v1:web/back-end/app/Core/Filtro.php
					AND up.ativo = TRUE
			)';
			$parametros[':cd_escola'] = (int) $filtros['escola'];
		}

		return [
			'onde' => $onde,
			'parametros' => $parametros,
		];
	}

	public function filtrosEscola(array $filtros): array
	{
		$onde = [];
		$parametros = [];

		if (!empty($filtros['nome'])) {
<<<<<<< HEAD:app/Core/Filtro.php
			$onde[] = 'e.nome ILIKE :nome';
=======
			$onde[] = 'e.nm_escola ILIKE :nome';
>>>>>>> api_v1:web/back-end/app/Core/Filtro.php
			$parametros[':nome'] = '%' . $filtros['nome'] . '%';
		}

		if (!empty($filtros['categoria'])) {
			$onde[] = 'e.categoria_administrativa = :categoria';
			$parametros[':categoria'] = $filtros['categoria'];
		}

		return [
			'onde' => $onde,
			'parametros' => $parametros,
		];
	}

	public function filtrosCompeticao(array $filtros): array
	{
		$onde = [];
		$parametros = [];

		if (!empty($filtros['nome'])) {
			$onde[] = 'c.nm_competicao ILIKE :nome';
			$parametros[':nome'] = '%' . $filtros['nome'] . '%';
		}

		if (!empty($filtros['escola'])) {
<<<<<<< HEAD:app/Core/Filtro.php
			$onde[] = 'c.cd_criador IN (
				SELECT v.cd_usuario
				FROM vinculo_usuario_escola v
				WHERE v.cd_escola = :cd_escola_competicao
					AND v.ativo = TRUE
			)';
=======
			$onde[] = 'c.cd_escola = :cd_escola_competicao';
>>>>>>> api_v1:web/back-end/app/Core/Filtro.php
			$parametros[':cd_escola_competicao'] = (int) $filtros['escola'];
		}

		return [
			'onde' => $onde,
			'parametros' => $parametros,
		];
	}

	public function filtrosTime(array $filtros): array
	{
		$onde = [];
		$parametros = [];

		if (!empty($filtros['nome'])) {
			$onde[] = 't.nm_time ILIKE :nome';
			$parametros[':nome'] = '%' . $filtros['nome'] . '%';
		}

		return [
			'onde' => $onde,
			'parametros' => $parametros,
		];
	}

	public function filtrosPartidas(array $filtros): array
	{
		$onde = [];
		$parametros = [];

		if (!empty($filtros['nome'])) {
			$onde[] = 't.nm_time ILIKE :nome';
			$parametros[':nome'] = '%' . $filtros['nome'] . '%';
		}

		return [
			'onde' => $onde,
			'parametros' => $parametros,
		];
	}

	public function ordem(array $filtros): string
	{
		$ordem = strtoupper($filtros['ordem'] ?? 'ASC');

		return in_array($ordem, ['ASC', 'DESC'], true) ? $ordem : 'ASC';
	}
}
