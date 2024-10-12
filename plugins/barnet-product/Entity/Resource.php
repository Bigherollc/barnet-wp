<?php

class ResourceEntity extends BarnetEntity
{
    const EXCEPT_PROTECTED = [
        '_postExcerpt',
        '_postExcerptFull'
    ];

    protected $taxonomyList = array(
        'resource-type',
        'resource-folder'
    );

    protected $relationShipList = array(
        'resources_to_products',
        'resources_to_formulas',
        'resources_to_concepts',
        'resources_to_resources'
    );

    protected $mediaStore;
    protected $mediaStoreAttachmentMeta;

    private $resourceMedia;
    private $resourcePptSource;
    private $resourceImage;
    private $resourceImageURL;
    private $resourceArea;
    private $resourceRoles;
    private $resourceDescription;
    private $resourceKeyword;
    private $resourceKeywordCustom;
    private $resourceOrder;
    private $resourceShowSeeMoreBefore;
    protected $_resourceTime;
    protected $_resourceOtherAttribute;
    protected $_resourceMediaType;
    protected $_mediaExternalURL;
    protected $_mediaLocalURL;

    /**
     * @return mixed
     */
    public function getResourceMedia()
    {
        return empty($this->resourcePptSource) ? $this->resourceMedia : null;
    }

    /**
     * @param mixed $resourceMedia
     * @return $this
     */
    public function setResourceMedia($resourceMedia)
    {
        $this->resourceMedia = $resourceMedia;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResourcePptSource()
    {
        if (!isset($this->resourcePptSource)) {
            return null;
        }

        $rss = array(
            'file' => $this->resourcePptSource,
            'domain' => $_SERVER['SERVER_NAME']
        );

        $token = $this->secure->encode(serialize($rss));
        return get_rest_url() . "barnet/v1/attachment?file={$this->resourcePptSource}.ppt&token=$token";
    }

    /**
     * @param mixed $resourcePptSource
     * @return $this
     */
    public function setResourcePptSource($resourcePptSource)
    {
        $this->resourcePptSource = $resourcePptSource;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResourceImage()
    {
        if (isset($this->resourceMedia)) {
            $postMetasVideo = get_post_meta($this->resourceMedia);
            $resourceImageID = null;
            if ($this->resourceImage !== null) {
                $resourceImageID = $this->resourceImage;
            } elseif (isset($postMetasVideo['_thumbnail_id']) && isset($postMetasVideo['_thumbnail_id'][0])) {
                $resourceImageID = $postMetasVideo['_thumbnail_id'][0];
            }
            return $resourceImageID;
        } else {
            return $this->resourceImage;
        }
        
    }

    /**
     * @param mixed $resourceImage
     * @return $this
     */
    public function setResourceImage($resourceImage)
    {
        $this->resourceImage = $resourceImage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResourceArea()
    {
        return $this->resourceArea;
    }

    /**
     * @param mixed $resourceArea
     * @return $this
     */
    public function setResourceArea($resourceArea)
    {
        $this->resourceArea = $resourceArea;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResourceOtherAttribute()
    {
        $wpText = "_wp_attachment_metadata";
        if (!empty($this->resourcePptSource)) {
            $this->_resourceOtherAttribute = array();
        } elseif (empty($this->_resourceOtherAttribute)) {
            $this->_resourceOtherAttribute = array();
            if (!empty($this->resourceMedia)) {
                if (!empty($this->mediaStoreAttachmentMeta) && isset($this->mediaStoreAttachmentMeta[intval($this->resourceMedia)])) {
                    $mediaPath = $this->mediaStoreAttachmentMeta[$this->resourceMedia];
                } else {
                    $mediaMetas = get_post_meta($this->resourceMedia);
                    if (!empty($mediaMetas[$wpText]) &&
                        is_array($mediaMetas[$wpText]) &&
                        count($mediaMetas[$wpText]) > 0) {
                        $mediaPath = $mediaMetas[$wpText][0];
                    }
                }
            }

            if (!empty($mediaPath) && is_serialized($mediaPath)) {
                $otherAttribute = unserialize($mediaPath);
                if (is_array($otherAttribute)) {
                    $this->_resourceOtherAttribute = $otherAttribute;
                }
            }
        }
        return $this->_resourceOtherAttribute;
    }

    /**
     * @return mixed
     */
    public function getResourceTime()
    {
        if (empty($this->_resourceTime)) {
            $this->_resourceTime = '';
            if (empty($this->_resourceOtherAttribute)) {
                $this->getResourceOtherAttribute();
            }
            if (!empty($this->_resourceOtherAttribute)) {
                if (!empty($this->_resourceOtherAttribute['length_formatted'])) {
                    $this->_resourceTime = $this->_resourceOtherAttribute['length_formatted'];
                }
            }
        }
        return $this->_resourceTime;
    }

    /**
     * @return mixed
     */
    public function getResourceMediaType()
    {
        if (!empty($this->resourcePptSource)) {
            $this->_resourceMediaType = 'application/vnd.ms-powerpoint';
        } elseif (empty($this->_resourceMediaType)) {
            $this->_resourceMediaType = '';
            if (!empty($this->resourceMedia)) {
                $this->_resourceMediaType = get_post_mime_type($this->resourceMedia);
            }
        }

        if (!$this->_resourceMediaType) {
            $this->_resourceMediaType = '';
        }

        return $this->_resourceMediaType;
    }

    /**
     * @return mixed
     */
    public function getMediaExternalURL()
    {
        if (!empty($this->resourcePptSource)) {
            $this->_mediaExternalURL = null;
        } elseif (empty($this->_mediaExternalURL)) {
            if (!empty($this->resourceMedia)) {
                $this->_resourceMediaType = get_post_mime_type($this->resourceMedia);
                $permalink = wp_get_attachment_url($this->resourceMedia);
                $fileName = basename($permalink);
                $ex = explode(".", $fileName);
                $titleFile = sanitize_title($ex[0]);
                $rss = array(
                    'id' => $this->resourceMedia,
                    'file' => basename($permalink),
                    'domain' => $_SERVER['SERVER_NAME'],
                );
                $id=$this->resourceMedia;
                $token = $this->secure->encode(serialize($rss));
                //$this->_mediaExternalURL = get_rest_url() . "barnet/v1/attachment/$titleFile?file=$fileName&token=$token";
                if(!(is_array($id)))$this->_mediaExternalURL = get_rest_url() . "barnet/v1/pubattachment/$titleFile?file=$fileName&id=$id";
            }
        }

        return $this->_mediaExternalURL;
    }

    /**
     * @return mixed
     */
    public function getMediaLocalURL()
    {
        if (!empty($this->resourcePptSource)) {
            $this->_mediaLocalURL = null;
        } elseif (empty($this->_mediaLocalURL)) {
            if (!empty($this->resourceMedia)) {
                $this->_resourceMediaType = get_post_mime_type($this->resourceMedia);
                $permalink = wp_get_attachment_url($this->resourceMedia);
                if (!empty($permalink)) {
                    $this->_mediaLocalURL = $permalink;
                } else {
                    $this->_mediaLocalURL = null;
                }
            }
        }

        return $this->_mediaLocalURL;
    }

    /**
     * @return mixed
     */
    public function getResourceRoles()
    {
        return $this->resourceRoles;
    }

    /**
     * @param mixed $resourceRoles
     * @return $this
     */
    public function setResourceRoles($resourceRoles)
    {
        $this->resourceRoles = $resourceRoles;
        return $this;
    }

    public function getResourceImageURL()
    {
        if (empty($this->resourceImageURL)) {
            $this->resourceImageURL = wp_get_attachment_url($this->resourceImage);
        }
        $img = '';
        if (isset($this->resourceMedia)) {
            $postMetasVideo = get_post_meta($this->resourceMedia);
            
            if ($this->resourceImageURL !== false) {
                $img = $this->resourceImageURL;
            } elseif (isset($postMetasVideo['_thumbnail_id']) && isset($postMetasVideo['_thumbnail_id'][0])) {
                $img = wp_get_attachment_url($postMetasVideo['_thumbnail_id'][0]);
            }
        } else {
            if ($this->resourceImageURL !== false) {
                $img = $this->resourceImageURL;
            }
        }
        global $barnet;
        return $img ? $img : $barnet->getDefaultImage();
    }

    /**
     * @return mixed
     */
    public function getResourceDescription()
    {
        return $this->resourceDescription;
    }

    /**
     * @param mixed $resourceDescription
     * @return $this
     */
    public function setResourceDescription($resourceDescription)
    {
        $this->resourceDescription = $resourceDescription;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResourceKeyword()
    {
        return $this->resourceKeyword;
    }

    /**
     * @param mixed $resourceKeyword
     * @return $this
     */
    public function setResourceKeyword($resourceKeyword)
    {
        $this->resourceKeyword = $resourceKeyword;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResourceKeywordCustom()
    {
        return $this->resourceKeywordCustom;
    }
    /**
     * @param mixed $resourceOrder
     * @return $this
     */
    public function setResourceorder($resourceOrder)
    {
        $this->resourceOrder = $resourceOrder;
        return $this;
    }
    /**
     * @return mixed
     */
    public function getResourceOrder()
    {
        return $this->resourceOrder;
    }

     /**
     * @param mixed $resourceShowSeeMoreBefore
     * @return $this
     */
    public function setResourceShowSeeMoreBefore($resourceShowSeeMoreBefore)
    {
        $this->resourceShowSeeMoreBefore = $resourceShowSeeMoreBefore;
        return $this;
    }
    /**
     * @return mixed
     */
    public function getResourceShowSeeMoreBefore()
    {
        return $this->resourceShowSeeMoreBefore;
    }
    
    /**
     * @param mixed $resourceKeywordCustom
     * @return $this
     */
    public function setResourceKeywordCustom($resourceKeywordCustom)
    {
        $this->resourceKeywordCustom = $resourceKeywordCustom;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMediaStore()
    {
        return $this->mediaStore;
    }

    /**
     * @param mixed $mediaStore
     */
    public function setMediaStore($mediaStore): void
    {
        $this->mediaStore = $mediaStore;
    }

    /**
     * @return mixed
     */
    public function getMediaStoreAttachmentMeta()
    {
        return $this->mediaStoreAttachmentMeta;
    }

    /**
     * @param mixed $mediaStoreAttachmentMeta
     * @return $this
     */
    public function setMediaStoreAttachmentMeta($mediaStoreAttachmentMeta): void
    {
        $this->mediaStoreAttachmentMeta = $mediaStoreAttachmentMeta;
    }

    public function toArray($advanced = array(), $returnSingleData = false, $fixed = true, $exceptPropsProtected = array())
    {
        return $this->checkRoleAndRegion() ? parent::toArray($advanced, $returnSingleData, $fixed, $exceptPropsProtected) : null;
    }

    public function toArrayPublic($advanced = array(), $returnSingleData = false, $fixed = true, $exceptPropsProtected = array())
    {
        return parent::toArray($advanced, $returnSingleData, $fixed, $exceptPropsProtected) ;
    }
    
    public function toArrayAllRoleAndRegion($advanced = array(), $returnSingleData = false, $fixed = true)
    {
        return parent::toArray($advanced, $returnSingleData, $fixed);
    }

    public function checkRoleAndRegion()
    {
        $globalText = "global";
        $user = $this->getUser();
        $userRoles = isset($user['role']) ? $user['role'] : array();
        foreach ($userRoles as $k => $r) {
            $userRoles[$k] = strtolower($r);
        }

        if (in_array('administrator', $userRoles)) {
            return true;
        }

        if (!is_user_logged_in()) {
            return false;
        }

        if (is_array($userRoles)) {
            $roles = $this->getResourceRoles();

            if (isset($roles) && !is_array($roles)) {
                $roles = array($roles);
            }

            if (isset($roles) && count($roles) > 0) {
                $roles = array_values($roles);
            } else {
                $roles = array();
            }

            foreach ($roles as $k => $r) {
                $roles[$k] = strtolower($r);
            }

            if (count($roles) > 0 && count(array_intersect($userRoles, $roles)) == 0) {
                return false;
            }
        }

        $userType = isset($user['type']) ? $user['type'] : $globalText;

        if ($userType == $globalText) {
            return true;
        }

        if (!in_array($this->resourceArea, array($userType, $globalText))) {
            return false;
        }

        return true;
    }
}
