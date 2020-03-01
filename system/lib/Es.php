<?php

namespace king\lib;

use Elasticsearch\ClientBuilder;
use king\core\Error;

class Es
{
    private $config = ['hosts' => ['127.0.0.1:9200'], 'index' => 'my_index', 'type' => 'my_type']; // type实际是mapping的type
    private $client;

    public static function getClass($config)
    {
        return new Es($config);
    }

    public function __construct($config)
    {
        foreach ($config as $key => $value) {
            if (isset($this->config[$key])) {
                $this->config[$key] = $value;
            }
        }

        $this->client = ClientBuilder::create()->setHosts($this->config['hosts'])->build();
        $this->params = [
            'index' => $this->config['index'],
            'body' => []
        ];
    }

    public function createIndex($properties, $shared = 5, $replicas = 0)
    {
        $params = [
            'index' => $this->config['index'],
            'body' => [
                'settings' => [
                    'number_of_shards' => $shared,
                    'number_of_replicas' => $replicas
                ],
                'mappings' => [
                    $this->config['type'] => [
                        'properties' => $properties
                    ]
                ]
            ]
        ];

        return $this->client->indices()->create($params);
    }

    /**************** 6.x 不支持多type,7.x将取消type,所以createIndex与createMapping合并到createIndex中了************
     * public function createIndex($properties, $shared = 5, $replicas = 0)
     * {
     * $params = [
     * 'index' => $this->config['index'],
     * 'body' => [
     * 'settings' => [
     * 'number_of_shards' => $shared,
     * 'number_of_replicas' => $replicas
     * ]
     * ]
     * ];
     *
     * return $this->client->indices()->create($params);
     * }
     *
     * public function createMapping($properties)
     * {
     * $params = [
     * 'index' => $this->config['index'],
     * 'type' => $this->config['type'],
     * 'body' => [
     * $this->config['type'] => [
     * 'properties' => $properties
     * ]
     * ]
     * ];
     *
     * return $this->client->indices()->putMapping($params);
     * }
     **********************************************************************************************************/

    public function deleteIndex()
    {
        $params = [
            'index' => $this->config['index']
        ];

        return $this->client->indices()->delete($params);
    }

    public function existIndex()
    {
        $params = [
            'index' => $this->config['index']
        ];

        return $this->client->indices()->exists($params);
    }

    public function searchScrollAllDoc($query, $size = 10000, $time = '10s')
    {
        $params['index'] = $this->config['index'];
        if (!empty($query)) {
            if (!is_array($query)) {
                $query = json_decode($query, true);
            }
            $params['body']['query'] = $query;
        }
        $params['body']['size'] = $size;
        $params['scroll'] = $time;
        $params['body']['sort'] = ["_doc"];
        $response = $this->client->search($params);
        $data = [];
        $total = $response['hits']['total'] ?? 0;
        while (isset($response['hits']['hits']) && count($response['hits']['hits']) > 0) {
            $hits = $response['hits']['hits'];
            foreach ($hits as $item) {
                $data[] = ['id' => $item['_id'], 'content' => $item['_source']];
            }
            $scroll_id = $response['_scroll_id'];
            $response = $this->client->scroll([
                    "scroll_id" => $scroll_id,
                    "scroll" => $time
                ]
            );
        }

        $rs = ['total' => $total, 'data' => $data];
        return $rs;
    }

    public function addDoc($doc, $id = '')
    {
        $doc = (array)$doc;

        $params = [
            'index' => $this->config['index'],
            'type' => $this->config['type'],
            'body' => $doc
        ];

        if ($id) {
            $params['id'] = $id;
        }

        return $this->client->index($params);
    }

    public function addBulkDoc($bulk, $pk = 'id')
    {
        if (count($bulk) > 0) {
            foreach ($bulk as $row) {
                $row = (array)$row;
                $index = [
                    '_index' => $this->config['index'],
                    '_type' => $this->config['type'],
                ];

                if (isset($row[$pk])) {
                    $index['_id'] = $row[$pk];
                }

                $params['body'][] = [
                    'index' => $index
                ];
                $params['body'][] = $row;
            }

            return $this->client->bulk($params);
        }
    }

    public function getDoc($id)
    {
        $params = [
            'index' => $this->config['index'],
            'type' => $this->config['type'],
            'id' => $id
        ];

        return $this->client->get($params);
    }

    public function updateDoc($id, $row)
    {
        $params = [
            'index' => $this->config['index'],
            'type' => $this->config['type'],
            'id' => $id,
            'body' => [
                'doc' => $row
            ]
        ];

        return $this->client->update($params);
    }

    public function deleteDoc($id)
    {
        $params = [
            'index' => $this->config['index'],
            'type' => $this->config['type'],
            'id' => $id
        ];

        return $this->client->delete($params);
    }

    public function searchDoc($query, $from = 0, $size = 10, $order = '', $field = '')
    {
        $params['index'] = $this->config['index'];
        if (!is_array($query)) {
            $query = json_decode($query, true);
        }

        if (!empty($query)) {
            $params['body']['query'] = $query;
        }

        if (!empty($field)) {
            if (!is_array($field)) {
                $field = json_decode($field, true);
            }
            $params['body']['_source'] = $field;
        }

        if ($size > 0) {
            $params['body']['size'] = $size;
        }

        if ($from !== false) {
            $params['body']['from'] = $from;
        }

        if (is_array($order)) {
            $params['body']['sort'] = $order;
        }

        $response = $this->client->search($params);
        $total = $response['hits']['total'];
        $hits = $response['hits']['hits'];
        $rs = ['total' => $total];

        foreach ($hits as $item) {
            $rs['data'][] = ['id' => $item['_id'], 'content' => $item['_source']];
        }

        return $rs;
    }

    public function searchDocGroup($query, $from = 0, $size = 10, $order = '', $group = '', $count = '',$field = '')
    {
        $params['index'] = $this->config['index'];
        if (!is_array($query)) {
            $query = json_decode($query, true);
        }

        if (!empty($query)) {
            $params['body']['query'] = $query;
        }
        if (!empty($field)) {
            if (!is_array($field)) {
                $field = json_decode($field, true);
            }
            $params['body']['_source'] = $field;
        }
        $params['body']['from'] = 0;
        $params['body']['size'] = 10000;
        if (is_array($order)) {
            $params['body']['sort'] = $order;
        }
        if (!empty($group)) {
            $params['body']['aggs']['rs']['terms']['field'] = $group;
            $params['body']['aggs']['rs']['terms']['order'] = ["_term" => "desc"];
            if ($size > 0) {
                $params['body']['aggs']['rs']['terms']['size'] = 10000;
            }
            if (is_array($count)) {
                $params['body']['aggs']['rs']['aggs'] = $count;
            }
        }
        $response = $this->client->search($params);
        $hits = $response['hits']['hits'];
        $aggregations = $response['aggregations']['rs']['buckets'];
        $data = [];
        foreach ($aggregations as $key => &$item) {
            if ($key < $from || $key >= ($from + $size)) {
                continue;
            }
            foreach ($hits as $value) {
                if ($item['key'] == $value['_source'][$group]) {
                    $item['list'][] = $value['_source'];
                }
            }
            $data[] = $item;
        }
        $rs['total'] = count($aggregations);
        $rs['data'] = $data;
        return $rs;
    }

    public function searchMix($query, $field = '')
    {
        $params['index'] = $this->config['index'];
        if (!is_array($query)) {
            $query = json_decode($query, true);
        }

        foreach ($query as $key => $value) {
            if(!empty($value)){
                $params['body'][$key] = $value;
            }
        }

        $response = $this->client->search($params);
        if ($field) {
            $rs = $response['aggregations'];
            return $rs[$field]['value'] ?? 0;
        } else {
            return $response;
        }
    }

    public function deleteBulk($ids)
    {
        foreach ($ids as $id) {
            $this->deleteDoc($id);
        }
    }
}