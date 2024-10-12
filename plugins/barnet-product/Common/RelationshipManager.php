<?php

class BarnetRelationshipManager
{
    protected $data;
    protected $post;
    protected $term;

    public function __construct()
    {
        $this->data = array();
        $this->post = array();
        $this->term = array();
    }

    public function getData($relationKey = null, $trackPost = 0, $trackValue = null)
    {
        if (!isset($relationKey)) {
            return $this->data;
        }

        $result = array();

        if (!isset($this->data[$relationKey])) {
            $this->syncData($relationKey);
        }

        $relationObjectName = explode('_to_', $relationKey);
        foreach ($this->data[$relationKey] as $data) {
            if ($data[$relationObjectName[$trackPost]] == $trackValue) {
                $result[] = $trackPost == 0 ? $data[$relationObjectName[1]] : $data[$relationObjectName[0]];
            }
        }

        return $result;
    }

    public function getPost($id)
    {
        return $this->post[$id] ?? null;
    }

    public function getTerms($postId)
    {
        return $this->term[$postId] ?? array();
    }

    public function syncData($relationKey)
    {
        global $wpdb;

        $relationObjectName = explode('_to_', $relationKey);
        $query = "SELECT `from` {$relationObjectName[0]}, `to` $relationObjectName[1]
                  FROM $wpdb->mb_relationships WHERE `type` = '$relationKey'";
        $this->data[$relationKey] = BarnetDB::sql($query);

        $dataPostImport = array();
        foreach ($this->data[$relationKey] as $result) {
            if (!isset($this->post[$result[$relationObjectName[0]]])) {
                $dataPostImport[] = $result[$relationObjectName[0]];
            }

            if (!isset($this->post[$result[$relationObjectName[1]]])) {
                $dataPostImport[] = $result[$relationObjectName[1]];
            }
        }

        if (count($dataPostImport) > 0) {
            $query = "SELECT * FROM $wpdb->posts WHERE ID in (" . implode(",", $dataPostImport) . ")";
            $dataPostImportResult = BarnetDB::sql($query);
            foreach ($dataPostImportResult as $importResult) {
                $this->post[$importResult['ID']] = $importResult;
            }
        }

        return $this;
    }

    public function syncTerm()
    {
        global $wpdb;

        $termRelationship = BarnetDB::sql("
            SELECT t.term_id,
                   t.name,
                   t.slug,
                   tt.taxonomy,
                   tt.description,
                   tt.parent,
                   tt.count,
                   tr.object_id post_id
            FROM $wpdb->terms t
            INNER JOIN $wpdb->term_taxonomy tt ON tt.term_id = t.term_id
            INNER JOIN $wpdb->term_relationships tr ON tr.term_taxonomy_id = tt.term_taxonomy_id");

        foreach ($termRelationship as $termPost) {
            $this->term[$termPost['post_id']][] = array(
                'term_id' => intval($termPost['term_id']),
                'name' => html_entity_decode($termPost['name']),
                'slug' => $termPost['slug'],
                'taxonomy' => $termPost['taxonomy'],
                'description' => $termPost['description'],
                'parent' => intval($termPost['parent']),
                'count' => intval($termPost['count']),
            );
        }

        return $this;
    }
}