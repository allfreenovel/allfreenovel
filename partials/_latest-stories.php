<?php
/**
 * Partial: Latest Stories
 *
 * Renders the (buffered) HTML for the [fictioneer_latest_stories] shortcode.
 *
 * @package WordPress
 * @subpackage Fictioneer
 * @since 4.0
 *
 * @internal $args['count']    The number of posts provided by the shortcode.
 * @internal $args['author']   The author provided by the shortcode.
 * @internal $args['order']    Order of posts. Default 'desc'.
 * @internal $args['orderby']  Sorting of posts. Default 'date'.
 * @internal $args['post_ids'] Comma-separated list of story IDs. Overrides count.
 * @internal $args['class']    Additional classes.
 */
?>

<?php

// Prepare query
$query_args = array(
  'post_type' => array( 'fcn_story' ),
  'post_status' => array( 'publish' ),
  'post__in' => $args['post_ids'],
  'meta_key' => 'fictioneer_story_sticky',
  'orderby' => 'meta_value ' . $args['orderby'],
  'order' => $args['order'] ?? 'desc',
  'posts_per_page' => $args['count'],
  'no_found_rows' => true
);

// Parameter for author?
if ( isset( $args['author'] ) && $args['author'] ) $query_args['author_name'] = $args['author'];

// Query stories
$entries = new WP_Query( $query_args );

?>

<section class="small-card-block latest-stories <?php echo implode( ' ', $args['classes'] ); ?>">
  <?php if ( $entries->have_posts() ) : ?>

    <ul class="two-columns _collapse-on-mobile">
      <?php while ( $entries->have_posts() ) : $entries->the_post(); ?>

        <?php
          // Setup
          $story = fictioneer_get_story_data( $post->ID );
          $tags = get_option( 'fictioneer_show_tags_on_story_cards' ) ? get_the_tags( $post ) : false;
        ?>

        <li class="card _small">
          <div class="card__body polygon">

            <div class="card__main _grid _small">

              <?php if ( has_post_thumbnail() ) : ?>
                <a href="<?php the_post_thumbnail_url( 'full' ); ?>" title="<?php echo esc_attr( sprintf( __( '%s Thumbnail', 'fictioneer' ), $story['title'] ) ); ?>" class="card__image cell-img" <?php echo fictioneer_get_lightbox_attribute(); ?>>
                  <?php echo get_the_post_thumbnail( $post, 'snippet', ['class' => 'no-auto-lightbox'] ); ?>
                </a>
              <?php endif; ?>

              <h3 class="card__title _small cell-title"><a href="<?php the_permalink(); ?>" class="truncate truncate--1-1"><?php echo $story['title']; ?></a></h3>

              <div class="card__content _small cell-desc truncate truncate--3-3">
                <?php if ( get_option( 'fictioneer_show_authors' ) ) : ?>
                  <span><?php
                    printf(
                      __( '<span class="author-by">by</span> %s <span>—</span> ', 'fictioneer' ),
                      fictioneer_get_author_node()
                    );
                  ?></span>
                <?php endif; ?>
                <span><?php
                  $short_description = fictioneer_first_paragraph_as_excerpt( fictioneer_get_content_field( 'fictioneer_story_short_description' ) );
                  echo strlen( $short_description ) < 230 ? get_the_excerpt() : $short_description;
                ?></span>
              </div>

              <?php if ( ! get_option( 'fictioneer_hide_taxonomies_on_story_cards' ) ) : ?>
                <div class="card__tag-list _small _scrolling cell-tax">
                  <div class="dot-separator scroll-horizontal">
                    <?php
                      if ( $story['has_taxonomies'] || $tags ) {
                        if ( $story['fandoms'] ) {
                          foreach ( $story['fandoms'] as $fandom ) {
                            echo '<span><a href="' . get_tag_link( $fandom ) . '" class="tag-pill _inline _fandom">' . $fandom->name . '</a></span>';
                          }
                        }

                        if ( $story['genres'] ) {
                          foreach ( $story['genres'] as $genre ) {
                            echo '<span><a href="' . get_tag_link( $genre ) . '" class="tag-pill _inline _genre">' . $genre->name . '</a></span>';
                          }
                        }

                        if ( $tags ) {
                          foreach ( $tags as $tag ) {
                            echo '<span><a href="' . get_tag_link( $tag ) . '" class="tag-pill _inline">' . $tag->name . '</a></span>';
                          }
                        }

                        if ( $story['characters'] ) {
                          foreach ( $story['characters'] as $character ) {
                            echo '<span><a href="' . get_tag_link( $character ) . '" class="tag-pill _inline _character">' . $character->name . '</a></span>';
                          }
                        }
                      } else {
                        ?><span class="card__no-taxonomies"><?php _e( 'No taxonomies specified yet.', 'fictioneer' ); ?></span><?php
                      }
                    ?>
                  </div>
                </div>
              <?php endif; ?>

            </div>

            <div class="card__footer _small">

              <div class="card__left text-overflow-ellipsis">
                <i class="fa-solid fa-list" title="<?php esc_attr_e( 'Chapters', 'fictioneer' ); ?>"></i>
                <span title="<?php esc_attr_e( 'Chapters', 'fictioneer' ); ?>"><?php echo $story['chapter_count']; ?></span>

                <i class="fa-solid fa-font" title="<?php esc_attr_e( 'Total Words', 'fictioneer' ); ?>"></i>
                <span title="<?php esc_attr_e( 'Total Words', 'fictioneer' ); ?>"><?php echo $story['word_count_short']; ?></span>

                <i class="fa-regular fa-clock" title="<?php esc_attr_e( 'Last Updated', 'fictioneer' ); ?>"></i>
                <span title="<?php esc_attr_e( 'Last Updated', 'fictioneer' ); ?>"><?php echo get_the_modified_date( get_option( 'fictioneer_subitem_date_format', 'M j, y' ), $post ); ?></span>

                <i class="<?php echo $story['icon']; ?>"></i>
                <span><?php echo fcntr( $story['status'] ); ?></span>
              </div>

              <div class="card__right rating-letter-label tooltipped" data-tooltip="<?php echo fcntr( $story['rating'], true ); ?>">
                <span><?php echo fcntr( $story['rating_letter'] ); ?></span>
              </div>

            </div>

          </div>
        </li>

      <?php endwhile; ?>
    </ul>

  <?php else: ?>

    <div class="no-results"><?php _e( 'No stories have been published yet.', 'fictioneer' ); ?></div>

  <?php endif; wp_reset_postdata(); ?>
</section>
