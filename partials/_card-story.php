<?php
/**
 * Partial: Story Card
 *
 * @package WordPress
 * @subpackage Fictioneer
 * @since 4.0
 *
 * @internal $args['show_type']    Whether to show the post type label. Unsafe.
 * @internal $args['cache']        Whether to account for active caching. Unsafe.
 * @internal $args['hide_author']  Whether to hide the author. Unsafe.
 * @internal $args['show_latest']  Whether to show (up to) the latest 3 chapters. Unsafe.
 * @internal $args['order']        Current order. Default 'desc'. Unsafe.
 * @internal $args['orderby']      Current orderby. Default 'modified'. Unsafe.
 */
?>

<?php

// No direct access!
defined( 'ABSPATH' ) OR exit;

// Setup
$story = fictioneer_get_story_data( $post->ID );
$latest = $args['show_latest'] ?? false;
$chapter_ids = array_slice( $story['chapter_ids'], $latest ? -3 : 0, 3, true ); // Does not include hidden or non-chapters
$chapter_count = count( $chapter_ids );
$excerpt = fictioneer_first_paragraph_as_excerpt(
  fictioneer_get_content_field( 'fictioneer_story_short_description', $post->ID )
);
$excerpt = empty( $excerpt ) ? __( 'No description provided yet.', 'fictioneer' ) : $excerpt;
$tags = false;

if (
  get_option( 'fictioneer_show_tags_on_story_cards' ) &&
  ! get_option( 'fictioneer_hide_taxonomies_on_story_cards' )
) {
  $tags = get_the_tags();
}

// Flags
$hide_author = $args['hide_author'] ?? false && ! get_option( 'fictioneer_show_authors' );
$show_taxonomies = ! get_option( 'fictioneer_hide_taxonomies_on_story_cards' ) && ( $story['has_taxonomies'] || $tags );
$show_type = $args['show_type'] ?? false;
$is_sticky = FICTIONEER_ENABLE_STICKY_CARDS &&
  get_post_meta( $post->ID, 'fictioneer_story_sticky', true ) && ! is_search() && ! is_archive();

?>

<li
  id="story-card-<?php echo $post->ID; ?>"
  class="card <?php echo $is_sticky ? '_sticky' : ''; ?>"
  data-story-id="<?php echo $post->ID; ?>"
  data-check-id="<?php echo $post->ID; ?>"
>
  <div class="card__body polygon">

    <div class="card__header _large">

      <?php if ( $show_type ) : ?>
        <div class="card__label"><?php _ex( 'Story', 'Story card label.', 'fictioneer' ); ?></div>
      <?php endif; ?>

      <h3 class="card__title"><a href="<?php the_permalink(); ?>" class="truncate _1-1"><?php
        if ( ! empty( $post->post_password ) ) {
          echo '<i class="fa-solid fa-lock protected-icon"></i> ';
        }

        echo $story['title'];
      ?></a></h3>

      <?php if ( $is_sticky ) : ?>
        <div class="card__sticky-icon" title="<?php echo esc_attr__( 'Sticky', 'fictioneer' ); ?>"><i class="fa-solid fa-thumbtack"></i></div>
      <?php endif; ?>

      <?php echo fictioneer_get_card_controls( $post->ID ); ?>

    </div>

    <div class="card__main _grid _large">

      <?php
        // Thumbnail
        if ( has_post_thumbnail() ) {
          printf(
            '<a href="%1$s" title="%2$s" class="card__image cell-img" %3$s>%4$s</a>',
            get_the_post_thumbnail_url( null, 'full' ),
            sprintf( __( '%s Thumbnail', 'fictioneer' ), $story['title'] ),
            fictioneer_get_lightbox_attribute(),
            get_the_post_thumbnail( null, 'cover' )
          );
        }

        // Content
        printf(
          '<div class="card__content cell-desc truncate %1$s">%2$s<span>%3$s</span></div>',
          $chapter_count > 2 ? '_3-4' : '_4-4',
          $hide_author ? '' : sprintf(
            '<span class="card__by-author show-below-desktop">%s</span> ',
            sprintf(
              _x( 'by %s —', 'Large card: by {Author} —.', 'fictioneer' ),
              fictioneer_get_author_node()
            )
          ),
          $excerpt
        );
      ?>

      <?php if ( $chapter_count > 0 ): ?>
        <ol class="card__link-list cell-list">
          <?php
            // Prepare
            $chapter_query_args = array(
              'post_type' => 'fcn_chapter',
              'post_status' => 'publish',
              'post__in' => fictioneer_rescue_array_zero( $chapter_ids ),
              'orderby' => 'post__in',
              'posts_per_page' => -1,
              'no_found_rows' => true, // Improve performance
              'update_post_term_cache' => false // Improve performance
            );

            $chapters = new WP_Query( $chapter_query_args );
          ?>
          <?php foreach ( $chapters->posts as $chapter ) : ?>
            <li class="card__link-list-item">
              <div class="card__left text-overflow-ellipsis">
                <i class="fa-solid fa-caret-right"></i>
                <a href="<?php the_permalink( $chapter->ID ); ?>" class="card__link-list-link"><?php
                  $list_title = get_post_meta( $chapter->ID, 'fictioneer_chapter_list_title', true );
                  $list_title = trim( wp_strip_all_tags( $list_title ) );

                  if ( empty( $list_title ) ) {
                    echo fictioneer_get_safe_title( $chapter->ID );
                  } else {
                    echo $list_title;
                  }
                ?></a>
              </div>
              <div class="card__right">
                <?php
                  printf(
                    '%1$s<span class="hide-below-480"> %2$s</span><span class="separator-dot">&#8196;&bull;&#8196;</span>%3$s',
                    fictioneer_shorten_number( fictioneer_get_word_count( $chapter->ID ) ),
                    __( 'Words', 'fictioneer' ),
                    strtotime( '-1 days' ) < strtotime( get_the_date( '', $chapter->ID ) ) ?
                      __( 'New', 'fictioneer' ) : get_the_time( FICTIONEER_CARD_STORY_LI_DATE, $chapter->ID )
                  );
                ?>
              </div>
            </li>
          <?php endforeach; ?>
        </ol>
      <?php endif; ?>

      <?php if ( $show_taxonomies ) : ?>
        <div class="card__tag-list cell-tax">
          <?php
            $taxonomies = array_merge(
              $story['fandoms'] ? fictioneer_generate_card_tags( $story['fandoms'], '_fandom' ) : [],
              $story['genres'] ? fictioneer_generate_card_tags( $story['genres'], '_genre' ) : [],
              $tags ? fictioneer_generate_card_tags( $tags ) : [],
              $story['characters'] ? fictioneer_generate_card_tags( $story['characters'], '_character' ) : []
            );

            // Implode with three-per-em spaces around a bullet
            echo implode( '&#8196;&bull;&#8196;', $taxonomies );
          ?>
        </div>
      <?php endif; ?>

    </div>

    <div class="card__footer">

      <div class="card__footer-box _left text-overflow-ellipsis"><?php
        // Build footer items
        $footer_items = [];

        if ( $story['status'] !== 'Oneshot' || $story['chapter_count'] > 1 ) {
          $footer_items['chapters'] = '<i class="card-footer-icon fa-solid fa-list" title="' .
            esc_attr__( 'Chapters', 'fictioneer' ) . '"></i> ' . $story['chapter_count'];
        }

        $footer_items['words'] = '<i class="card-footer-icon fa-solid fa-font" title="' .
          esc_attr__( 'Total Words', 'fictioneer' ) . '"></i> ' . $story['word_count_short'];

        if ( ( $args['orderby'] ?? 0 ) === 'date' ) {
          $footer_items['publish_date'] = '<i class="card-footer-icon fa-solid fa-clock" title="' .
            esc_attr__( 'Published', 'fictioneer' ) .'"></i> ' . get_the_date( FICTIONEER_CARD_STORY_FOOTER_DATE );
        } else {
          $footer_items['modified_date'] = '<i class="card-footer-icon fa-regular fa-clock" title="' .
            esc_attr__( 'Last Updated', 'fictioneer' ) .'"></i> ' . get_the_modified_date( FICTIONEER_CARD_STORY_FOOTER_DATE );
        }

        if ( ! $hide_author ) {
          $footer_items['author'] = '<i class="card-footer-icon fa-solid fa-circle-user hide-below-desktop"></i> ' .
            fictioneer_get_author_node( get_the_author_meta( 'ID' ), 'hide-below-desktop' );
        }

        $footer_items['comments'] = '<i class="card-footer-icon fa-solid fa-message hide-below-480" title="' .
          esc_attr__( 'Comments', 'fictioneer' ) . '"></i> <span class="hide-below-480" title="' .
          esc_attr__( 'Comments', 'fictioneer' ) . '">' . $story['comment_count'] . '</span>';

        $footer_items['status'] = '<i class="card-footer-icon ' . $story['icon'] . '"></i> ' . fcntr( $story['status'] );

        // Filer footer items
        $footer_items = apply_filters( 'fictioneer_filter_story_card_footer', $footer_items, $post, $story, $args );

        // Implode and render footer items
        echo implode( ' ', $footer_items );
      ?></div>

      <div class="card__footer-box _right rating-letter-label _large tooltipped" data-tooltip="<?php echo fcntr( $story['rating'], true ); ?>">
        <span class="hide-below-480"><?php echo fcntr( $story['rating'] ); ?></span>
        <span class="show-below-480"><?php echo fcntr( $story['rating_letter'] ); ?></span>
      </div>

    </div>

  </div>
</li>
