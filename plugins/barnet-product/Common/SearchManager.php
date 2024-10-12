<?php

class BarnetSearchManager
{
    protected $wpdb;
    protected $yamlHelper;
    protected $user;

    protected $config = array();
    protected $searchConfig = array();
    protected $includePercentPoint = array();
    protected $includeUniquePoint = array();
    protected $includeRelationship = array();
    protected $extra = array();

    protected $isShowPoint = false;
    protected $isShowSetting = false;
    protected $prefixConfig = "barnet_opt_";

    public function __construct()
    {
        global $wpdb;

        $this->wpdb = $wpdb;
        $this->user = new UserEntity();
        $this->yamlHelper = new YamlHelper();
        if (file_exists(__DIR__ . "/../Config/searches.yml")) {
            $this->config = $this->yamlHelper->loadFile(__DIR__ . "/../Config/searches.yml");
            $this->searchConfig = $this->config['setting'];

            if ($this->config['tools']['show_setting']) {
                $this->isShowSetting = $this->config['tools']['show_setting'];
            }

            if ($this->checkActive($this->config[Barnet::ADVANCE_TEXT][Barnet::PERCENT_POINT][Barnet::ACTIVE_TEXT], 'barnet_opt_sa_pp_active')) {
                $this->includePercentPoint = $this->config[Barnet::ADVANCE_TEXT][Barnet::PERCENT_POINT]['filter'];
            }

            if ($this->checkActive($this->config[Barnet::ADVANCE_TEXT][Barnet::UNIQUE_POINT][Barnet::ACTIVE_TEXT], 'barnet_opt_sa_up_active')) {
                $this->includeUniquePoint = $this->config[Barnet::ADVANCE_TEXT][Barnet::UNIQUE_POINT]['filter'];
            }

            if ($this->checkActive($this->config[Barnet::EXTRA_TEXT][Barnet::MODIFIED_DATE][Barnet::ACTIVE_TEXT], 'barnet_opt_se_md_active')) {
                $this->extra[$this->config[Barnet::EXTRA_TEXT][Barnet::MODIFIED_DATE]['field']] = $this->config[Barnet::EXTRA_TEXT][Barnet::MODIFIED_DATE]['point'] / $this->config[Barnet::EXTRA_TEXT][Barnet::MODIFIED_DATE]['value'];
            }

            if ($this->config['relationship']) {
                $this->includeRelationship = $this->config['relationship'];
            }

            $this->formatConfig();
        }
    }

    public function search($keyword, $_type = null, $_pType = null)
    {
        $keyword = strtolower($keyword);
        $type = isset($_type) ? $_type : null;
        $pType = isset($_pType) ? $_pType : null;

        if (isset($type)) {
            $entities = array_map(function ($e) {
                return ucfirst(explode('-', $e)[1]) . 'Entity';
            }, explode(',', $type));
        } else {
            $entities = BarnetEntity::getSubclassesOf();
        }

        /*$metaKeys = array();
        foreach ($entities as $entity) {
            if (!class_exists($entity)) {
                continue;
            }

            $searchFields = BarnetEntity::getSearchField($entity);
            foreach ($searchFields['meta'] as $metaKey) {
                if (in_array($metaKey, $metaKeys)) {
                    continue;
                }

                $metaKeys[] = $metaKey;
            }
        }

        $metaKeys = implode(',', array_map(function ($e) {
            return "'$e'";
        }, $metaKeys));*/

        $q = $this->formatQ($keyword);
        if (empty($q)) {
            return array();
        }

        $arrType = array(
            'barnet-product',
            'barnet-formula',
            'barnet-concept',
            'barnet-resource'
        );
        if (isset($type)) {
            $expType = explode(',', $type);
            $arrTypeTmp = array();
            foreach ($expType as $v) {
                $v = trim($v);
                if (!empty($v)) {
                    $arrTypeTmp[$v] = $v;
                }
            }
            if (count($arrTypeTmp) > 0) {
                $arrType = $arrTypeTmp;
            }
        }

        $arrFieldSearchMeta = array();
        foreach ($this->searchConfig as $keys => $configs) {
            if (in_array($keys, $arrType)) {
                foreach ($configs as $k => $v) {
                    if (!in_array($k, array('post_title', 'taxonomies', 'relationship'))) {
                        $arrFieldSearchMeta[$k] = $k;
                    }
                }
            }
        }

        $arrSearchMeta = array();
        if (count($arrFieldSearchMeta) > 0) {
            $sql = "SELECT post_id FROM {$this->wpdb->postmeta} where meta_key IN ('" . implode("','",$arrFieldSearchMeta) . "') AND meta_value REGEXP '$q' = 1 AND post_id IN (SELECT ID FROM {$this->wpdb->posts} where post_status not in ('trash', 'draft', 'private', 'auto-draft'))";
            $arrSearchMeta = $this->queryListId($sql, "post_id");
        }

        $searchTaxonomies = false;
        $arrCategory = array();
        $arrListTaxInclude = array();
        foreach ($this->searchConfig as $keys => $configs) {
            if (isset($configs['taxonomies']) && is_array($configs['taxonomies'])) {
                foreach ($configs['taxonomies'] as $k => $v) {
                    $arrListTaxInclude[$k] = $k;
                }
            }
        }

        if (count($arrListTaxInclude) > 0) {
            $sql = "SELECT wtr.object_id  FROM {$this->wpdb->term_taxonomy} wtt join {$this->wpdb->terms} wt on wtt.term_id = wt.term_id join {$this->wpdb->term_relationships} wtr on wtt.term_taxonomy_id = wtr.term_taxonomy_id WHERE taxonomy in ('".implode("','", $arrListTaxInclude)."') AND wt.name REGEXP '$q' = 1 and wtr.object_id IN (SELECT ID FROM {$this->wpdb->posts} where post_status not in ('trash', 'draft', 'private', 'auto-draft')) group by wtr.object_id";
            $arrCategory = $this->queryListId($sql, "object_id");
            $searchTaxonomies = true;
        }

        $arrRelationship = array();
        $arrListRelationshipInclude = array();
        foreach ($this->searchConfig as $keys => $configs) {
            if (isset($configs['relationship']) && is_array($configs['relationship'])) {
                foreach ($configs['relationship'] as $k => $v) {
                    $arrListRelationshipInclude[$k] = $k;
                }
            }
        }

        if (count($arrListRelationshipInclude) > 0) {
            $sql = "SELECT pr.from  FROM {$this->wpdb->mb_relationships} pr join {$this->wpdb->posts} p on pr.to = p.ID WHERE pr.type in ('".implode("','", $arrListRelationshipInclude)."') AND p.post_title REGEXP '$q' = 1 and p.post_status not in ('trash', 'draft', 'private', 'auto-draft') AND pr.from IN (SELECT ID FROM {$this->wpdb->posts} where post_status not in ('trash', 'draft', 'private', 'auto-draft')) group by pr.from";
            $arrRelationship = $this->queryListId($sql, "from");
        }

        $resultListId = array_merge(
            $this->queryListId("SELECT ID FROM {$this->wpdb->posts} where post_status not in ('trash', 'draft', 'private', 'auto-draft') AND post_type in ('".implode("','", $arrType)."') AND post_title REGEXP '$q' = 1", "ID"),
            $arrSearchMeta,
            $arrCategory,
            $arrRelationship
        );


        $dataDefault = $this->getDataDefault($resultListId);

        if (!isset($dataDefault['data'])) {
            $dataDefault['data'] = array();
        }
        $dataFilter = $this->filterSearch($dataDefault['data'], $type, $pType, $searchTaxonomies);

        $dataSort = $this->sortResult($keyword, $dataFilter);
        $result = $this->findingFeatuerdItems($dataSort);
        if ($this->isShowPoint) {
            return $result;
        }

        return array_values(array_filter($result));
    }

    public function findingFeatuerdItems($data){
        if(!get_option('search_featured_item', 1))return $data;
        $result=[];
        foreach($data as $item){

            $post_type = get_post_type($item['data']['id']);
            
            if($post_type == 'barnet-formula'){
                $sql = "SELECT pr.to ID FROM 
                {$this->wpdb->mb_relationships} pr where 
                pr.type like 'formulas_to_concepts' 
                and pr.from like '".$item['data']['id']."';";
                $featuredFormulas_c = $this->wpdb->get_results($sql, ARRAY_A);
                $ffc_result=[];
                foreach($featuredFormulas_c as $featuredFormula_c){
                    $featuredFormula_c['post_title'] = get_the_title( $featuredFormula_c['ID'] );
                    $featuredFormula_c['permalink'] = get_permalink( $featuredFormula_c['ID'] );
                    $ffc_result[] = $featuredFormula_c;
                }   
                $item['data']['featuredConcepts'] = $ffc_result ; 
            }
            if($post_type == 'barnet-product'){
                $sql = "SELECT p.ID, p.post_title FROM {$this->wpdb->posts} p
                LEFT JOIN {$this->wpdb->postmeta} pm ON p.ID = pm.post_id
                WHERE p.post_type = 'barnet-formula'
                AND p.post_status = 'publish'
                AND pm.meta_key = 'formula_key_ingredients'
                AND (pm.meta_value REGEXP '\"ki_product_cpt\";s:[0-9]+:\"(" .$item['data']['id'] . ")\"' 
                OR pm.meta_value REGEXP '\"f_linkto\";s:[0-9]+:\"(" . $item['data']['id']. ")\"')";

                $featuredFormulas = $this->wpdb->get_results($sql, ARRAY_A);
                $f_result = [];
                foreach($featuredFormulas as $featuredFormula){
                    $featuredFormula['permalink'] = get_permalink( $featuredFormula['ID'] );
                    $f_result[] = $featuredFormula;
                }
                $item['data']['featuredFormulas'] = $f_result ;
                $sql = "SELECT pr.to ID FROM 
                {$this->wpdb->mb_relationships} pr where 
                pr.type like 'products_to_concepts' 
                and pr.from like '".$item['data']['id']."';";
                $featuredConcepts = $this->wpdb->get_results($sql, ARRAY_A);
                $fc_result=[];
                foreach($featuredConcepts as $featuredConcept){
                    $featuredConcept['post_title'] = get_the_title( $featuredConcept['ID'] );
                    $featuredConcept['permalink'] = get_permalink( $featuredConcept['ID'] );
                    $fc_result[] = $featuredConcept;
                }   
                $item['data']['featuredConcepts'] = $fc_result ;   
            }
            $result[] = $item;
        }
        return $result;
    }

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return bool
     */
    public function isShowPoint()
    {
        return $this->isShowPoint;
    }

    /**
     * @param bool $isShowPoint
     * @return $this
     */
    public function setShowPoint($isShowPoint)
    {
        $this->isShowPoint = $isShowPoint;
        return $this;
    }

    protected function sortResult($keyword, $data = array())
    {
        if (!isset($this->searchConfig)) {
            return $data;
        }

        $userId = $this->user->getId();
        $tempData = array();

        /** @var BarnetEntity $row */
        foreach ($data as $row) {
            $tempData[$row->getId()] = array(
                Barnet::POST_TYPE_TEXT => $row->getPostType(),
                'data' => '',
                'lasted_modified' => (new DateTime($row->getPostModified()))->getTimestamp() - time()
            );

            if (array_key_exists($row->getPostType(), $this->searchConfig)) {
                foreach ($this->searchConfig[$row->getPostType()] as $key => $value) {
                    if ($key == 'include') {
                        foreach ($value as $relationshipName) {
                            $fieldName = DataHelper::compactString($relationshipName, '_');
                            $dataRelationship = $this->includeRelationship[$row->getPostType()][$relationshipName];
                            foreach ($dataRelationship as $pType => $pRelationship) {
                                if ($pType == Barnet::ADVANCE_TEXT) {
                                    continue;
                                }

                                if (!isset($pRelationship[Barnet::RELATION_KEY_TEXT])) {
                                    continue;
                                }

                                $fieldName .= '_' . DataHelper::compactString($pType);
                                foreach ($pRelationship as $_key => $value) {
                                    if ($_key == Barnet::RELATION_KEY_TEXT) {
                                        continue;
                                    }

                                    if ($_key == 'taxonomy') {
                                        $fieldName .= '_tax';
                                        foreach ($value as $taxKey => $taxValue) {
                                            $fieldName .= '_' . DataHelper::compactString($taxKey);
                                            foreach ($taxValue as $field => $val) {
                                                $k = $fieldName . '_' . $field;
                                                $metaData = $row->getMetaData($k);
                                                $tempData[$row->getId()]['data'] .= $this->generateSplitKey($k) . json_encode($metaData);
                                            }
                                        }
                                    } else {
                                        $k = $fieldName . '_' . DataHelper::compactString($_key, '_');
                                        $metaData = $row->getMetaData($k);
                                        $tempData[$row->getId()]['data'] .= $this->generateSplitKey($k) . json_encode($metaData);
                                    }
                                }
                            }
                        }
                    } else if ($key == 'taxonomies') {
                        $propMethod = "get" . ucfirst(DataHelper::snake2CamelCase($key));
                        $fieldName = DataHelper::compactString($key);
                        foreach ($value as $k => $v) {
                            $fkeys =  $fieldName . '_' . $k;
                            $valueField = "";
                            foreach ($row->$propMethod() as $v) {
                                if ($v["taxonomy"] != $k) {
                                    continue;
                                }
                                if ($valueField != "") {
                                    $valueField .= "__";
                                }
                                $valueField .= $v["name"];
                            }
                            $tempData[$row->getId()]['data'] .= $this->generateSplitKey($fkeys) . $valueField;
                        }
                    } else if ($key == 'relationship') {
                        $propMethod = "get" . ucfirst(DataHelper::snake2CamelCase($key));
                        $fieldName = DataHelper::compactString($key);
                        $valueAttr = $row->$propMethod();
                        foreach ($value as $k => $v) {
                            foreach ($v as $k2 => $v2) {
                                if (isset($valueAttr[$k2])) {
                                    $fkeys = $fieldName.'_' . $k2;
                                    $valueField = "";
                                    foreach ($valueAttr[$k2] as $v3) {
                                        if ($valueField != "") {
                                            $valueField .= "__";
                                        }
                                        $valueField .= $v3["data"]["post_title"];

                                    }
                                    $tempData[$row->getId()]['data'] .= $this->generateSplitKey($fkeys) . $valueField;
                                }
                            }
                        }
                    } else {
                        $propMethod = "get" . ucfirst(DataHelper::snake2CamelCase($key));

                        if (/*($key == 'product_description' && isset($userId)) ||*/($key == 'product_description_logged' && !isset($userId))) {
                            continue;
                        }

                        $tempData[$row->getId()]['data'] .= $this->generateSplitKey($key) . $row->$propMethod();
                    }
                }

                $tempData[$row->getId()]['data'] = strtolower($tempData[$row->getId()]['data']);
            }
        }

        $pointData = $this->getPoints($keyword, $tempData);

        $arrPoint = array();
        foreach ($pointData as $k => $v) {
            if (isset($arrPoint[$v])) {
                $arrPoint[$v][] = $k;
            } else {
                $arrPoint[$v] = array($k);
            }
        }

        krsort($arrPoint);
        $pointData = array();
        foreach ($arrPoint as $k => $v) {
            foreach ($v as $v2) {
                $pointData[$v2] = $k;
            }
        }

        if ($this->isShowPoint) {
            return $pointData;
        }

        return array_map(function ($k) use ($data) {
            foreach ($data as $row) {
                if ($row->getId() == $k) {
                    return $row->toArray(BarnetEntity::$PUBLIC_LANDING);
                }
            }
        }, array_keys($pointData));
    }

    protected function getPoints($keyword, $data)
    {
        $splitPos = array();
        $fixKeyword = $this->formatQ($keyword);
        $keyfull = "";
        if (!empty($keyword)) {
            $keyfull = str_replace(array('{', '}', ':', ';'), array('', '', '', ''), DataHelper::removeDuplicateWhiteSpace($keyword));
            if ($keyfull == $fixKeyword) {
                $keyfull = "";
            }
        }
        foreach ($this->searchConfig as $keys => $configs) {
            foreach ($configs as $key => $config) {
                if ($key == 'include') {
                    foreach ($config as $relationshipName) {
                        $fieldName = DataHelper::compactString($relationshipName, '_');
                        $dataRelationship = $this->includeRelationship[$keys][$relationshipName];

                        $isUnique = isset($dataRelationship[Barnet::ADVANCE_TEXT][Barnet::UNIQUE_POINT]) ? $dataRelationship[Barnet::ADVANCE_TEXT][Barnet::UNIQUE_POINT] : false;
                        $isPercent = isset($dataRelationship[Barnet::ADVANCE_TEXT][Barnet::PERCENT_POINT]) ? $dataRelationship[Barnet::ADVANCE_TEXT][Barnet::PERCENT_POINT] : false;

                        foreach ($dataRelationship as $pType => $pRelationship) {
                            if ($pType == Barnet::ADVANCE_TEXT) {
                                continue;
                            }

                            if (!isset($pRelationship[Barnet::RELATION_KEY_TEXT])) {
                                continue;
                            }

                            $fieldName .= '_' . DataHelper::compactString($pType);
                            foreach ($pRelationship as $_key => $value) {
                                if ($_key == Barnet::RELATION_KEY_TEXT) {
                                    continue;
                                }

                                if ($_key == 'taxonomy') {
                                    $fieldName .= '_tax';
                                    foreach ($value as $taxKey => $taxValue) {
                                        $fieldName .= '_' . DataHelper::compactString($taxKey);
                                        foreach ($taxValue as $field => $val) {
                                            $k = $fieldName . '_' . $field;

                                            if ($this->getOption("{$this->prefixConfig}ss_" . DataHelper::compactString($keys) . "_$k")) {
                                                $splitPos[$keys][$this->generateSplitKey($k)] = $this->getOption("{$this->prefixConfig}ss_" . DataHelper::compactString($keys) . "_$k");
                                            } else {
                                                $splitPos[$keys][$this->generateSplitKey($k)] = $val;
                                            }

                                            if ($isUnique && !in_array($k, $this->includeUniquePoint)) {
                                                $this->includeUniquePoint[] = $this->generateSplitKey($k);
                                            }

                                            if ($isPercent && !in_array($k, $this->includePercentPoint)) {
                                                $this->includePercentPoint[] = $this->generateSplitKey($k);
                                            }
                                        }
                                    }
                                } else {
                                    $k = $fieldName . '_' . DataHelper::compactString($_key, '_');
                                    $splitPos[$keys][$this->generateSplitKey($k)] = $value;
                                    if ($isUnique && !in_array($k, $this->includeUniquePoint)) {
                                        $this->includeUniquePoint[] = $this->generateSplitKey($k);
                                    }

                                    if ($isPercent && !in_array($k, $this->includePercentPoint)) {
                                        $this->includePercentPoint[] = $this->generateSplitKey($k);
                                    }
                                }
                            }
                        }
                    }
                } else if ($key == 'taxonomies') {
                    $fieldName = DataHelper::compactString($key);
                    foreach ($config as $k => $v) {
                        $fkeys = $fieldName . '_' . $k;
                        $splitPos[$keys][$this->generateSplitKey($fkeys)] = $v;
                    }
                } else if ($key == 'relationship') {
                    $fieldName = DataHelper::compactString($key);
                    foreach ($config as $k => $v) {
                        foreach ($v as $k2 => $v2) {
                            $fkeys = $fieldName.'_' . $k2;
                            $splitPos[$keys][$this->generateSplitKey($fkeys)] = $v2;
                        }
                    }
                } else {
                    $splitPos[$keys][$this->generateSplitKey($key)] = $config;
                }
            }
        }

        $result = array();
        foreach ($data as $key => $row) {
            $postType = $row[Barnet::POST_TYPE_TEXT];
            $postData = $row['data'];
            $postLastedModified = $row['lasted_modified'];

            if (!isset($splitPos[$postType])) {
                continue;
            }

            $splitPosKey = array_keys($splitPos[$postType]);
            $splitPosKeyPattern = implode('|', $splitPosKey);

            preg_match_all("/$splitPosKeyPattern/", $postData, $matchesSplitPos, PREG_OFFSET_CAPTURE);
            preg_match_all("/$fixKeyword/", $postData, $matchesKeywordPos, PREG_OFFSET_CAPTURE);
            if (!empty($keyfull)) {
                preg_match_all("/$keyfull/", $postData, $matchesKeywordPosFull, PREG_OFFSET_CAPTURE);
                if (count($matchesKeywordPos[0]) > 0 && count($matchesKeywordPosFull[0]) > 0) {
                    foreach ($matchesKeywordPosFull[0] as $mKeywordPos) {
                        $matchesKeywordPos[0][] = $mKeywordPos;
                    }
                }
            }

            $point = 0;
            $uniqueCheck = array();
            foreach ($matchesKeywordPos[0] as $mKeywordPos) {
                $keywordValue = $mKeywordPos[0];
                $keywordPos = $mKeywordPos[1];
                foreach ($matchesSplitPos[0] as $kSplitPos => $mSplitPos) {
                    if (isset($matchesSplitPos[0][$kSplitPos + 1])) {
                        $sufMatch = $matchesSplitPos[0][$kSplitPos + 1];
                        if ($keywordPos > $mSplitPos[1] && $keywordPos < $sufMatch[1]) {
                            if (in_array($mSplitPos[0], $this->includePercentPoint)) {
                                $dataLength = $sufMatch[1] - $mSplitPos[1];
                                $ratePointPercent = ($dataLength - $keywordPos + $mSplitPos[1]) / $dataLength;
                                $_point = $ratePointPercent * $splitPos[$postType][$mSplitPos[0]];
                            } else {
                                $_point = $splitPos[$postType][$mSplitPos[0]];
                            }

                            if (!isset($uniqueCheck[$keywordValue])) {
                                $uniqueCheck[$keywordValue] = array();
                            }

                            if (in_array($mSplitPos[0], $this->includeUniquePoint) && in_array($mSplitPos[0], $uniqueCheck[$keywordValue])) {
                                break;
                            }

                            if (!in_array($mSplitPos[0], $uniqueCheck[$keywordValue])) {
                                $uniqueCheck[$keywordValue][] = $mSplitPos[0];
                            }

                            $point += $_point;
                            break;
                        }
                    } else {
                        if (in_array($mSplitPos[0], $this->includePercentPoint)) {
                            $dataLength = strlen($postData) - $mSplitPos[1];
                            $ratePointPercent = ($dataLength - $keywordPos + $mSplitPos[1]) / $dataLength;
                            $_point = $ratePointPercent * $splitPos[$postType][$mSplitPos[0]];
                        } else {
                            $_point = $splitPos[$postType][$mSplitPos[0]];
                        }

                        if (!isset($uniqueCheck[$keywordValue])) {
                            $uniqueCheck[$keywordValue] = array();
                        }

                        if (in_array($mSplitPos[0], $this->includeUniquePoint) && in_array($mSplitPos[0], $uniqueCheck[$keywordValue])) {
                            break;
                        }

                        if (!in_array($mSplitPos[0], $uniqueCheck[$keywordValue])) {
                            $uniqueCheck[$keywordValue][] = $mSplitPos[0];
                        }

                        $point += $_point;
                    }
                }
            }

            if (isset($this->extra[Barnet::POST_MODIFIED_TEXT])) {
                $point += $postLastedModified * $this->extra[Barnet::POST_MODIFIED_TEXT];
            }

            $result[$key] = round($point, 5);
        }

        return $result;
    }

    protected function formatConfig()
    {
        foreach ($this->searchConfig as $key => $config) {
            uasort($this->searchConfig[$key], function ($a, $b) {
                return $b <=> $a;
            });
        }

        foreach ($this->searchConfig as $key => $config) {
            $fieldName = $this->prefixConfig . "ss_" . DataHelper::compactString($key);
            foreach ($config as $field => $value) {
                if ($this->getOption($fieldName . "_" . $field)) {
                    $this->searchConfig[$key][$field] = $this->getOption($fieldName . "_" . $field);
                }
            }
        }

        if ($this->checkActive($this->config[Barnet::ADVANCE_TEXT][Barnet::PERCENT_POINT][Barnet::ACTIVE_TEXT], "{$this->prefixConfig}sa_pp_active") && $this->getOption("{$this->prefixConfig}sa_pp_option")) {
            $this->includePercentPoint = unserialize($this->getOption("{$this->prefixConfig}sa_pp_option"));
        }

        $this->includePercentPoint = array_map(function ($e) {
            return $this->generateSplitKey($e);
        }, $this->includePercentPoint);

        if ($this->checkActive($this->config[Barnet::ADVANCE_TEXT][Barnet::UNIQUE_POINT][Barnet::ACTIVE_TEXT], "{$this->prefixConfig}sa_up_active") && $this->getOption("{$this->prefixConfig}sa_up_option")) {
            $this->includeUniquePoint = unserialize($this->getOption("{$this->prefixConfig}sa_up_option"));
        }

        $this->includeUniquePoint = array_map(function ($e) {
            return $this->generateSplitKey($e);
        }, $this->includeUniquePoint);

        if ($this->checkActive($this->config[Barnet::ADVANCE_TEXT][Barnet::UNIQUE_POINT][Barnet::ACTIVE_TEXT], "{$this->prefixConfig}se_md_active") && $this->getOption("{$this->prefixConfig}se_up_value") && $this->getOption("{$this->prefixConfig}se_up_point")) {
            $this->extra[Barnet::POST_MODIFIED_TEXT] = $this->getOption("{$this->prefixConfig}se_up_point") / $this->getOption("{$this->prefixConfig}se_up_value");
        }
    }

    protected function filterSearch($dataDefault, $type, $pType, $searchTaxonomies = false)
    {
        $result = array();

        if (isset($dataDefault) && is_array($dataDefault)) {
            $userId = $this->user->getId();
           
            $typeArr = isset($type) ? explode(',', $type) : null;
            $relationshipManager = null;
            if ($searchTaxonomies) {
                $relationshipManager = new BarnetRelationshipManager();
                $relationshipManager->syncTerm();
            }

            foreach ($dataDefault as $data) {
                $postType = $data[Barnet::RESPONSE_TEXT]->post_type;
                
                if (in_array($postType, array('post', 'page'))) {
                    continue;
                }

                if (!isset($userId) && $postType != 'barnet-product') {
                    continue;
                }

                if ($postType == 'barnet-product') {
                    $product_only_for_code_list = 0;
                    $product_only_for_code_list = get_post_meta(intval($data[Barnet::RESPONSE_TEXT]->ID), 'product_only_for_code_list', TRUE);
                    if (intval($product_only_for_code_list) == 1) {
                        continue;
                    }
                }

                if (isset($typeArr) && !in_array($postType, $typeArr)) {
                    continue;
                }

                $entity = ucfirst(substr($postType, 7, strlen($postType) - 7)) . 'Entity';
                if (!class_exists($entity)) {
                    continue;
                }

                $entityObject = new $entity(
                    $data[Barnet::RESPONSE_TEXT]->ID,
                    true,
                    array('post' => $data[Barnet::RESPONSE_TEXT])
                );

                if ($entityObject instanceof ResourceEntity) {
                    $show_search = '';
                    $show_search = get_post_meta($data[Barnet::RESPONSE_TEXT]->ID, 'show_search', TRUE);
                    $mediaType = $entityObject->getResourceMediaType();
                    if ($show_search != 1 && $mediaType == 'application/pdf') {
                        continue;
                    }
                    // if ($entityObject->getResourceMediaType() == "application/pdf") {
                    //     continue;
                    // }
                }
                if ($searchTaxonomies) {
                    $entityObject->setRelationshipManager($relationshipManager);
                }

                if ($entityObject instanceof ProductEntity) {
                    //$productType = strtolower($entityObject->getProductType());
                    $productType = strtolower(get_term($entityObject->getProductTypeTerm())->name);
                    if (isset($pType) && $pType != $productType) {
                        continue;
                    }
                }
                
                $result[] = $entityObject;

            }
        }

        return $result;
    }

    protected function formatQ($q)
    {
        $qtemp = DataHelper::removeDuplicateWhiteSpace($q);
        $q = implode('|', array_filter(array_map(function ($e) {
            return strlen($e) > 2 ? trim(trim($e, ',')) : null;
        }, explode(' ', DataHelper::removeDuplicateWhiteSpace($q)))));
        if (empty($q)) {
            $q = trim(trim($qtemp, ','));
        }

        return str_replace(array('{', '}', ':', ';'), array('', '', '', ''), $q);
    }

    protected function getDataDefault($listId = array())
    {
        $dataDefault = array();

        if (count($listId) > 0) {
            $posts = get_posts(array(
                'numberposts' => -1,
                'post__in' => $listId,
                'orderby' => 'post_title',
                'order' => 'ASC',
                Barnet::POST_TYPE_TEXT => 'any'
            ));

            $postMetaManager = new BarnetPostMetaManager($posts);

            foreach ($posts as $post) {
                $dataDefault["data"][] = array(
                    Barnet::RESPONSE_TEXT => $post,
                    "metas" => $postMetaManager->getMetaData($post->ID)
                );
            }
        }

        return $dataDefault;
    }

    protected function queryListId($query, $colName)
    {
        return array_map(function ($e) use ($colName) {
            return $e[$colName];
        }, $this->wpdb->get_results($query, ARRAY_A));
    }

    protected function generateSplitKey($key)
    {
        return '{' . implode('-', str_split($key)) . '}';
    }

    protected function getOption($key)
    {
        return $this->isShowSetting ? get_option($key) : $this->isShowSetting;
    }

    protected function checkActive($defaultValue, $optionKey)
    {
        if ($this->isShowSetting) {
            return $this->getOption($optionKey);
        } else {
            return $defaultValue;
        }
    }
}