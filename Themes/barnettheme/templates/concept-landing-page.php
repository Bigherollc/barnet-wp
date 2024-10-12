<?php
/* Template Name: Concept Landing Page */

$user = new UserEntity();
if (!$user->getId()) {
    wp_redirect('/');
}

global $post;
$pageId = $post->ID;
$pageTitle = $post->post_title;
$pageMetas = get_post_meta($pageId);
$pageShortDes = '';

if (isset($pageMetas["p_short_description"]) && is_array($pageMetas["p_short_description"]) && count($pageMetas["p_short_description"]) > 0) {
    $pageShortDes = $pageMetas["p_short_description"][0];
}

$args = array(
    'post_type' => 'barnet-concept-book',
    'post_status' => 'publish',
    'posts_per_page' => -1
);

$conceptBookPosts = get_posts($args);
$conceptBooks = array();

$_i = 1;
$conceptBookPostMetaManager = new BarnetPostMetaManager($conceptBookPosts);
foreach ($conceptBookPosts as $iTax => $conceptBookPost) {
    $conceptBookMeta = $conceptBookPostMetaManager->getMetaData($conceptBookPost->ID);

    $conceptBookRow = array(
        'id' => $conceptBookPost->ID,
        'name' => $conceptBookPost->post_title
    );

    if (isset($conceptBookMeta['concept_book_order']) &&
        is_array($conceptBookMeta['concept_book_order']) &&
        count($conceptBookMeta['concept_book_order']) > 0) {
        $conceptBookRow['order'] = $conceptBookMeta['concept_book_order'][0];
    } elseif (isset($conceptBookMeta['concept_book_order'])) {
        $conceptBookRow['order'] = $conceptBookMeta['concept_book_order'];
    }

    if (isset($conceptBookMeta['concept_book_image']) &&
        is_array($conceptBookMeta['concept_book_image']) &&
        count($conceptBookMeta['concept_book_image']) > 0) {
        $conceptBookRow['image'] = wp_get_attachment_url($conceptBookMeta['concept_book_image'][0]);
    } elseif (isset($conceptBookMeta['concept_book_image'])) {
        $conceptBookRow['image'] = wp_get_attachment_url($conceptBookMeta['concept_book_image']);
    }

    $conceptBookRow['image'] = !empty($conceptBookRow['image']) ? $conceptBookRow['image'] : $barnet->getDefaultImage();

    if (isset($conceptBookMeta['concept_book_style']) &&
        is_array($conceptBookMeta['concept_book_style']) &&
        count($conceptBookMeta['concept_book_style']) > 0) {
        $conceptBookRow['style'] = $conceptBookMeta['concept_book_style'][0];
    } elseif (isset($conceptBookMeta['concept_book_style'])) {
        $conceptBookRow['style'] = wp_get_attachment_url($conceptBookMeta['concept_book_style']);
    } else {
        $conceptBookRow['style'] = 'light';
    }

    $taxonomiesSession = json_decode(json_encode(
        get_the_terms(
            $conceptBookPost->ID,
            'concept-category'
        )
    ), true);
    $conceptBookRow['taxonomies'] = array();

    if (!empty($taxonomiesSession)) {
        for ($i = 0; $i < count($taxonomiesSession); $i++) {
            $texMeta = get_term_meta($taxonomiesSession[$i]["term_id"]);
            $texOrder = empty($texMeta['order']) ? 0 : intval($texMeta['order'][0]);
            $taxonomiesSession[$i]['order'] = $texOrder;
            $taxonomiesSession[$i]['tab_id'] = $_i++;
        }
        usort($taxonomiesSession, function ($a, $b) {
            return (isset($a['order']) ? $a['order'] : '999') <=> (isset($b['order']) ? $b['order'] : '999');
        });
        $conceptBookRow['taxonomies'] = $taxonomiesSession;
    }

    $conceptBooks[] = $conceptBookRow;
}

usort($conceptBooks, function ($a, $b) {
    return (isset($a['order']) ? $a['order'] : '999') <=> (isset($b['order']) ? $b['order'] : '999');
});
$style_page = get_post_meta($post->ID, 'p_style', TRUE);
get_header(); ?>

    <main role="main">
        <div class="concepts-container">
            <div class="container">
                <div class="concepts">
                    <div class="component-heading-group <?php echo $style_page == 'light' ? '' : '--dark-mode'; ?>">
                        <h2 class="component-heading-group__heading --size-lg">
                            <?php _e('Concepts');?>
                        </h2>
                        <div class="component-heading-group__desc">
                            <?php echo $pageShortDes;?>
                        </div>
                    </div>
                    <?php
                    foreach ($conceptBooks as $conceptBook) {
                        $conceptBookEntity = new ConceptBookEntity($conceptBook['id']);
                        if (!$conceptBookEntity->checkRoleAndRegion()) {
                            continue;
                        }
                        $style = get_post_meta($conceptBook['id'], 'concept_book_style', TRUE);
                        ?>
                        <div class="concepts__productGroup --mg-top-lg">
                            <div class="row">
                                <div class="col-12 col-sm-4 col-md-5">
                                    <div class="concepts__productGroup--wrap">
                                        <div class="row">
                                            <div class="col-12 col-md-6">
                                                <div class="concepts__imageContent <?php echo $style == 'light' ? '' : '--dark-mode'; ?>"
                                                     style="background-image: url(<?php echo $conceptBook['image']; ?>)">
                                                    <div class="concepts__imageContent-desc">
                                                        <h2><?php echo $conceptBook['name']; ?></h2>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="component-tab-list --vertical --hasToggle <?php echo $style == 'light' ? '' : '--dark-mode'; ?>"
                                                     data-tab-list>
                                                    <div class="component-tab-list__toggle"
                                                         data-toggle-tab><?php echo $conceptBook['name']; ?></div>
                                                    <ul class="nav" role="tablist">
                                                        <?php
                                                        if ($conceptBook['taxonomies']) {
                                                            foreach ($conceptBook['taxonomies'] as $iTax => $taxonomy) {
                                                                if (!isset($taxonomy['name'])) {
                                                                    continue;
                                                                }
                                                                ?>
                                                                <li><a <?php echo $iTax == 0 ? 'class="active btn-gtm-concept-section"' : 'class="btn-gtm-concept-section"'; ?>
                                                                            href="#<?php echo "{$taxonomy['slug']}-{$taxonomy['tab_id']}"; ?>"
                                                                            data-toggle="tab"><?php echo $taxonomy['name']; ?></a>
                                                                </li>
                                                                <?php
                                                            }
                                                        }
                                                        ?>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-8 col-md-7">
                                    <div class="tab-content">
                                        <?php
                                        if ($conceptBook['taxonomies']) {
                                            foreach ($conceptBook['taxonomies'] as $iTax => $taxonomy) {
                                                if (!isset($taxonomy['term_id'])) {
                                                    continue;
                                                }
                                                ?>
                                                <div class="tab-pane <?php echo $iTax == 0 ? 'active' : ''; ?>"
                                                     id="<?php echo "{$taxonomy['slug']}-{$taxonomy['tab_id']}"; ?>">
                                                    <div class="component-image-concepts">
                                                        <div class="component-image-concepts__wrapper slider-control">
                                                            <?php
                                                            $argsConcept = array(
                                                                'post_type' => 'barnet-concept',
                                                                'post_status' => 'publish',
                                                                'posts_per_page' => -1,
                                                                'orderby' => 'post_title',
                                                                'order' => 'ASC',
                                                                'tax_query' => array(
                                                                    array(
                                                                        'taxonomy' => 'concept-category',
                                                                        'field' => 'term_id',
                                                                        'terms' => $taxonomy['term_id'],
                                                                        'operator' => 'IN'
                                                                    )
                                                                )
                                                            );

                                                            $queryConcepts = new WP_Query($argsConcept);
                                                            $concepts = $queryConcepts->posts;
                                                            $conceptMetaManager = new BarnetPostMetaManager($concepts);
                                                            $conceptOrders = array();
                                                            $conceptItems = array();
                                                            $concepts = array_map(function ($e) use ($conceptMetaManager, &$conceptOrders, &$conceptItems) {
                                                                $conceptOrders[$e->ID] = 999;
                                                                $conceptItems[$e->ID] = $e;
                                                                $metaConcepts = $conceptMetaManager->getMetaData($e->ID);
                                                                unset($metaConcepts['_edit_last']);
                                                                unset($metaConcepts['_edit_lock']);
                                                                foreach ($metaConcepts as $keyMeta => $metaConcept) {
                                                                    $e->$keyMeta = $metaConcept;
                                                                }
                                                                if (!empty($e->concept_order) && intval($e->concept_order) > 0) {
                                                                    $conceptOrders[$e->ID] =  intval($e->concept_order);
                                                                }
                                                                return $e;
                                                            }, $concepts);

                                                            asort($conceptOrders);

                                                            foreach ($conceptOrders as $k => $v) {
                                                                $concept = $conceptItems[$k];
                                                                $conceptEntity = new ConceptEntity($concept->ID, true, array('post' => $concept));
                                                                if (!$conceptEntity->checkRoleAndRegion()) {
                                                                    continue;
                                                                }
                                                                $descArr = explode('.', $concept->post_content);
                                                                $shortDesc = count($descArr) > 1 ? $descArr[0] . "." . $descArr[1] : $concept->post_content;
                                                                $conceptImage = is_array($concept->concept_thumbnail) ?
                                                                    (isset($concept->concept_thumbnail[0]) ? wp_get_attachment_url($concept->concept_thumbnail[0]) : $barnet->getDefaultImage("concept_thumb")) :
                                                                    (isset($concept->concept_thumbnail) ? wp_get_attachment_url($concept->concept_thumbnail) : $barnet->getDefaultImage("concept_thumb"));
                                                                $conceptImage = $conceptImage ? $conceptImage : $barnet->getDefaultImage();
                                                                ?>
                                                                <div class="component-image-concepts__item"
                                                                     data-slider-item>
                                                                    <div class="component-image-concepts__item--wrapper">
                                                                        <div class="component-image-concepts__item--img">
                                                                            <img
                                                                                    src="<?php echo $conceptImage; ?>"
                                                                                    alt="<?php echo $concept->post_title; ?>">
                                                                        </div>
                                                                        <div class="component-image-concepts__item--content">
                                                                            <div class="component-image-concepts__item--heading">
                                                                                <?php echo $concept->post_title; ?>
                                                                            </div>
                                                                            <div class="component-image-concepts__item--desc"> <?php echo $conceptEntity->getConceptShortDescription(); ?>
                                                                            </div>
                                                                        </div>
                                                                        <a href="../barnet-concept/<?php echo $concept->post_name; ?>"
                                                                           title="<?php echo $concept->post_title; ?>"
                                                                           rel="stylesheet"></a>
                                                                    </div>
                                                                </div>
                                                                <?php
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </main>

<?php get_footer(); ?>