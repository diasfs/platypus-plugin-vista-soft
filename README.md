# platypus

Plugin to access the VistaSoft api

## Installation

```
composer require diasfs/platypus-plugin-vista-soft
```

## Configuration

```php
	<?php
	/*
		File: application/config/app.php
	*/
	return array(
		...
		"vista" => array(
			"endpoint" => "http://sandbox-rest.vistahost.com.br",
			"api_key" => "c9fdd79584fb8d369a6a579af1a8f681"
		),
		...
	);
```

## Usage

### Execution a query

```php
	<?php
	use Platypus\Plugin\VistaSoft;
	
	$query = array(
		"showtotal" => 1,
		"pesquisa" => array(
			"fields" => array(
				"Codigo",
				"UF",
				"Cidade",
				"Bairro"
			),
			"paginacao" => {
				"pagina" => 1,
				"quantidade" => 5
			}
		)
	);
	
	try {
		$rows = VistaSoft::exec("imoveis/listar", $query);
		print_r($rows);

		/*
			stdClass Object
			(
				[MF13993] => stdClass Object
					(
						[Codigo] => MF13993
						[UF] => RS
						[Cidade] => Porto Alegre
						[Bairro] => Centro
					)

				[MF13937] => stdClass Object
					(
						[Codigo] => MF13937
						[UF] => RS
						[Cidade] => Porto Alegre
						[Bairro] => Centro
					)

				[MF13004] => stdClass Object
					(
						[Codigo] => MF13004
						[UF] => RS
						[Cidade] => Porto Alegre
						[Bairro] => Floresta
					)

				[MF13362] => stdClass Object
					(
						[Codigo] => MF13362
						[UF] => RS
						[Cidade] => Porto Alegre
						[Bairro] => Floresta
					)

				[MF13731] => stdClass Object
					(
						[Codigo] => MF13731
						[UF] => RS
						[Cidade] => Porto Alegre
						[Bairro] => Floresta
					)

				[total] => 25539
				[paginas] => 5108
				[pagina] => 1
				[quantidade] => 5
			)
		*/
	} catch (\Exception $err) {
		echo $err->getMessage();
	}
	

```