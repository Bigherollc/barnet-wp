<?php

class BarnetAnnoucement extends BarnetDataType
{
    const OPTION = "options";
    const RADIO = "radio";

    public static $LOCATION_TYPE_LIST = array(
        'home_page' => 'Home Page',
        //'actives_landing' => 'Actives Landing',
        'product_landing' => 'Product Landing',
        'formula_landing' => 'Formula Landing',
        'resource_landing' => 'Resource Landing',
    );

    public static $DEVICE_TYPE__LIST = array(
        'both' => 'Both',
        'web' => 'Web',
        'app' => 'App',
    );

    public function createPostType()
    {
        register_post_type(
            $this->postType,
            $this->buildArgs(
                'Barnet Annoucement',
                'Barnet Annoucements',
                array()
            )
        );
    }

    public function addExt()
    {
        return array(
            'title' => esc_html__('Annoucement Type', $this->domain),
            'id' => 'barnet-annoucement',
            'post_types' => array($this->postType),
            'context' => 'normal',
            'priority' => 'high',
            'clone' => true,
            'fields' => array(
                array(
                    'type' => 'wysiwyg',
                    'name' => esc_html__('Body', $this->domain),
                    'id' => $this->prefix . 'description',
                    self::OPTION => array(
                        'media_buttons' => false,
                    ),
                ),
                array(
                    'type' => 'text',
                    'name' => esc_html__('CTA URL', $this->domain),
                    'id' => $this->prefix . 'optional',
                ),
                array(
                    'type' => 'text',
                    'name' => esc_html__('Button Text', $this->domain),
                    'id' => $this->prefix . 'btn_text',
                ),
                array(
                    'type' => self::RADIO,
                    'name' => esc_html__('Button Type', $this->domain),
                    'id' => $this->prefix . 'btn_type',
                    self::OPTION => array(
                        'solid' => esc_html__('Solid', $this->domain),
                        'regular' => esc_html__('Regular', $this->domain),
                    ),
                ),
                array(
                    'type' => 'checkbox',
                    'name' => esc_html__('Open In New Tab?', $this->domain),
                    'id' => $this->prefix . 'new_window',
                ),
                array(
                    'type' => 'text',
                    'name' => esc_html__('Alert Banner', $this->domain),
                    'id' => $this->prefix . 'alert_banner',
                ),
                array(
                    'type' => 'color',
                    'name' => esc_html__('Alert Background Color', $this->domain),
                    'id' => $this->prefix . 'alert_bg_color',
                ),
                array(
                    'type' => self::RADIO,
                    'name' => esc_html__('Style', $this->domain),
                    'id' => $this->prefix . 'style',
                    self::OPTION => array(
                        'light' => esc_html__('Light', $this->domain),
                        'dark' => esc_html__('Dark', $this->domain),
                    ),
                ),
                array(
                    'name' => esc_html__('Background Image', $this->domain),
                    'id' => $this->prefix . 'bb_image',
                    'type' => 'image_advanced',
                    'max_file_uploads' => 1,
                    'max_status' => 'false',
                ),

                array(
                    'type' => 'date',
                    'name' => esc_html__('Expiration Date', $this->domain),
                    'id' => $this->prefix . 'expirated_date',
                ),
                array(
                    'type' => self::RADIO,
                    'name' => esc_html__('Location/Type', $this->domain),
                    'id' => $this->prefix . 'location_type',
                    self::OPTION => self::$LOCATION_TYPE_LIST,
                    'std' => 'home_page'
                ),
                array(
                    'type' => self::RADIO,
                    'name' => esc_html__('Show In', $this->domain),
                    'id' => $this->prefix . 'device',
                    self::OPTION => self::$DEVICE_TYPE__LIST,
                    'std' => 'both'
                ),
               array(
                    'type' => self::RADIO,
                    'name' => esc_html__('Region Type', $this->domain),
                    'inline'=> true,
                    'id' => $this->prefix . 'area',
                    BarnetProduct::OPTION => BarnetProduct::$AREA_LIST,
                    'std' => 'global'
                ),
            ),
        );
    }

    public function createTaxonomy()
    {
    }

    public function addRelationship()
    {
    }
}

$barnetAnnoucement = new BarnetAnnoucement("an_", 'barnet-annoucement');
