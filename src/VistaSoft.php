<?php

namespace Platypus\Plugins;

use Platypus\Config;

class VistaSoft
{
    public static $curl = null;
    public static function getCurl()
    {
        if (null == static::$curl) {
            static::$curl = curl_init();
            $options = array(
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_FOLLOWLOCATION => TRUE,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_SSL_VERIFYPEER => FALSE,
                CURLOPT_SSL_VERIFYHOST => FALSE,
                //CURLOPT_SSL_VERIFYSTATUS => FALSE
            );
            curl_setopt_array(static::$curl, $options);
            register_shutdown_function(function () {
                static::close();
            });
        }

        return static::$curl;
    }

    public static function getApiKey()
    {
        $key = Config::get('vista.api_key');
        if (!$key) {
            throw new \Exception("VistaSoft ApiKey not set!");
        }
        return $key;
    }

    public static function getEndpoint()
    {
        $host = Config::get('vista.endpoint');
        if (!$host) {
            throw new \Exception("VistaSoft Endpoint not set!");
        }
        return $host;
    }

    public static function exec($path, $query = array(), $headers = array())
    {
        $key = static::getApiKey();
        $endpoint =  static::getEndpoint();

        $query['key'] = $key;

        if (isset($query['pesquisa'])) {
            $query["pesquisa"] = json_encode($query['pesquisa']);
        }

        $url = $endpoint . '/' . $path . '?' . http_build_query($query);
        $headers[] = "Accept: application/json";
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $headers
        );
        $ch = static::getCurl();
        curl_setopt_array($ch, $options);
        $data = curl_exec($ch);
        if (FALSE === $data) {
            throw new \Exception(curl_error($ch), curl_errno($ch));
        }
        $resp = json_decode($data);

        if (isset($resp->message)) {
            if (is_array($resp->message)) {
                throw new \Exception($resp->message[0]);
            }
            throw new \Exception($resp->message);
        }

        if (!is_object($resp) && !is_array($resp)) {
            return $data;
        }
        return $resp;
    }

    public static function close()
    {
        if (null != static::$curl) {
            curl_close(static::$curl);
            static::$curl = null;
        }
    }

    public static function getPages()
    {
        $query = array(
            'showtotal' => 1,
            'pesquisa' => array(
                'fields' => array(),
                'paginacao' => array(
                    'pagina' => 1,
                    'quantidade' => 50
                )
            )
        );
        $resp = static::exec('imoveis/listar', $query);
        return (int) $resp->paginas;
    }

    public static function getUpdates()
    {
        $pages = static::getPages();

        $query = array(
            'pesquisa' => array(
                'fields' => array(
                    'DataHoraAtualizacao'
                ),
                'paginacao' => array(
                    'pagina' => 1,
                    'quantidade' => 50
                )
            )
        );

        $imoveis = array();
        for ($page = 1; $page <= $pages; $page++) {

            $query['pesquisa']['paginacao']['pagina'] = $page;
            $resp = static::exec('imoveis/listar', $query);
            foreach ($resp as $codigo => $imovel) {
                $imoveis[$codigo] = strtotime($imovel->DataHoraAtualizacao);
            }
        }
        return $imoveis;
    }

    public static function getDetails($code, $fields = array(
        'ImoCodigo',
        'UF',
        'Codigo',
        'Cidade',
        'Bairro',
        'BairroComercial',
        'LinkTour',
        'DataHoraAtualizacao'
    ))
    {
        $query = array(
            'imovel' => $code,
            'pesquisa' => array(
                'fields' => $fields
            )
        );

        return static::exec('imoveis/detalhes', $query);
    }
}
