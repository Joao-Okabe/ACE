<?php

class Filtro
{
	public function filtrosAluno(array $filtros): array
	{
		$onde = [];
		$parametros = [];

		if (!empty($filtros['nome'])) {
			$onde[] = 'a.nome ILIKE :nome';
			$parametros[':nome'] = '%' . $filtros['nome'] . '%';
		}

		if (!empty($filtros['escola'])) {
			$onde[] = 'a.cd_usuario IN (
				SELECT up.cd_usuario
				FROM vinculo_usuario_escola up
				WHERE up.cd_escola = :cd_escola
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
			$onde[] = 'e.nome ILIKE :nome';
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

	public function ordem(array $filtros): string
	{
		$ordem = strtoupper($filtros['ordem'] ?? 'ASC');

		return in_array($ordem, ['ASC', 'DESC'], true) ? $ordem : 'ASC';
	}
}
