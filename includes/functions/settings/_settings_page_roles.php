<?php
/**
 * Partial: Role Settings
 *
 * @package WordPress
 * @subpackage Fictioneer
 * @since 5.6.0
 */
?>

<?php

global $pagenow;

// Setup
$roles = wp_roles()->roles;
$current_url = add_query_arg( $_GET, admin_url( $pagenow ) );
$admin_url = admin_url( 'admin-post.php' );
$update_role_nonce = wp_nonce_field( 'fictioneer_roles_update_role', 'fictioneer_nonce', true, false );
$add_role_nonce = wp_nonce_field( 'fictioneer_roles_add_role', 'fictioneer_nonce', true, false );
$remove_message = __( 'Are you sure you want to remove the %s role? All current holders will become Subscribers. Enter %s to confirm.', 'fictioneer' );
$remove_confirm = __( 'DELETE', 'fictioneer' );

$editor_caps = array(
  'fcn_shortcodes',
  'fcn_select_page_template',
  'fcn_custom_page_css',
  'fcn_custom_epub_css',
  'fcn_seo_meta',
  'fcn_make_sticky'
);

$restrictions = array(
  'fcn_reduced_profile',
  'fcn_edit_only_others_comments',
  'fcn_upload_limit',
  'fcn_upload_restrictions'
);

$advanced_caps = array(
  'fcn_adminbar_access',
  'fcn_admin_panel_access',
  'fcn_dashboard_access',
  'fcn_show_badge',
  'fcn_privacy_clearance',
  'upload_files',
  'edit_files',
  'fcn_read_others_files',
  'fcn_edit_others_files',
  'fcn_delete_others_files',
  'list_users',
  'create_users',
  'edit_users',
  'remove_users',
  'fcn_allow_self_delete',
  'unfiltered_html'
);

$taxonomy_caps = array(
  // Categories
  'manage_categories',
  'assign_categories',
  'edit_categories',
  'delete_categories',
  // Tags
  'manage_post_tags',
  'assign_post_tags',
  'edit_post_tags',
  'delete_post_tags',
  // Genres
  'manage_fcn_genres',
  'assign_fcn_genres',
  'edit_fcn_genres',
  'delete_fcn_genres',
  // Fandoms
  'manage_fcn_fandoms',
  'assign_fcn_fandoms',
  'edit_fcn_fandoms',
  'delete_fcn_fandoms',
  // Characters
  'manage_fcn_characters',
  'assign_fcn_characters',
  'edit_fcn_characters',
  'delete_fcn_characters',
  // Warnings
  'manage_fcn_content_warnings',
  'assign_fcn_content_warnings',
  'edit_fcn_content_warnings',
  'delete_fcn_content_warnings'
);

$post_caps = array(
  'publish_posts',
  'edit_posts',
  'delete_posts',
  'edit_published_posts',
  'delete_published_posts',
  'edit_others_posts',
  'delete_others_posts',
  'read_private_posts',
  'edit_private_posts',
  'delete_private_posts'
);

$page_caps = array(
  'publish_pages',
  'edit_pages',
  'delete_pages',
  'edit_published_pages',
  'delete_published_pages',
  'edit_others_pages',
  'delete_others_pages',
  'read_private_pages',
  'edit_private_pages',
  'delete_private_pages'
);

$story_caps = array(
  'publish_fcn_stories',
  'edit_fcn_stories',
  'delete_fcn_stories',
  'edit_published_fcn_stories',
  'delete_published_fcn_stories',
  'edit_others_fcn_stories',
  'delete_others_fcn_stories',
  'read_private_fcn_stories',
  'edit_private_fcn_stories',
  'delete_private_fcn_stories'
);

$chapter_caps = array(
  'publish_fcn_chapters',
  'edit_fcn_chapters',
  'delete_fcn_chapters',
  'edit_published_fcn_chapters',
  'delete_published_fcn_chapters',
  'edit_others_fcn_chapters',
  'delete_others_fcn_chapters',
  'read_private_fcn_chapters',
  'edit_private_fcn_chapters',
  'delete_private_fcn_chapters'
);

$collection_caps = array(
  'publish_fcn_collections',
  'edit_fcn_collections',
  'delete_fcn_collections',
  'edit_published_fcn_collections',
  'delete_published_fcn_collections',
  'edit_others_fcn_collections',
  'delete_others_fcn_collections',
  'read_private_fcn_collections',
  'edit_private_fcn_collections',
  'delete_private_fcn_collections'
);

$recommendation_caps = array(
  'publish_fcn_recommendations',
  'edit_fcn_recommendations',
  'delete_fcn_recommendations',
  'edit_published_fcn_recommendations',
  'delete_published_fcn_recommendations',
  'edit_others_fcn_recommendations',
  'delete_others_fcn_recommendations',
  'read_private_fcn_recommendations',
  'edit_private_fcn_recommendations',
  'delete_private_fcn_recommendations'
);

$all_caps = array(
  [ __( 'Editor Capabilities', 'fictioneer' ), $editor_caps ],
  [ __( 'Restricted Capabilities', 'fictioneer' ), $restrictions ],
  [ __( 'Advanced Capabilities', 'fictioneer' ), $advanced_caps ],
  [ __( 'Taxonomy Capabilities', 'fictioneer' ), $taxonomy_caps ],
  [ __( 'Post Capabilities', 'fictioneer' ), $post_caps ],
  [ __( 'Page Capabilities', 'fictioneer' ), $page_caps ],
  [ __( 'Story Capabilities', 'fictioneer' ), $story_caps ],
  [ __( 'Chapter Capabilities', 'fictioneer' ), $chapter_caps ],
  [ __( 'Collection Capabilities', 'fictioneer' ), $collection_caps ],
  [ __( 'Recommendation Capabilities', 'fictioneer' ), $recommendation_caps ]
);

// Remove administrators (do not touch them!)
unset( $roles['administrator'] );

// Order roles
$order = [
  'editor' => 0,
  'fcn_moderator' => 1,
  'author' => 2,
  'contributor' => 3,
  'subscriber' => 99
];

uksort(
  $roles,
  function( $a, $b ) use ( $order ) {
    $aVal = $order[ $a ] ?? PHP_INT_MAX;
    $bVal = $order[ $b ] ?? PHP_INT_MAX;

    return $aVal <=> $bVal;
  }
);

// Current selection
$selected_role = ( $_GET['fictioneer-subnav'] ?? 0 ) ?: array_keys( $roles )[0];

?>

<div class="fictioneer-ui fictioneer-settings">

	<?php fictioneer_settings_header( 'roles' ); ?>

  <ul class="fictioneer-settings__subnav">
      <?php
      foreach ( $roles as $key => $role ) {
        $role['type'] = $key;
        $class = $selected_role == $key ? ' class="tab active"' : ' class="tab"';
        $link = add_query_arg( 'fictioneer-subnav', $key, $current_url );

        echo '<a href="' . $link . '" ' . $class . '>' . $role['name'] . '</a>';
      }
    ?>
  </ul>

	<div class="fictioneer-settings__content">
    <div class="tab-content">

      <?php foreach ( $roles as $key => $role ) : ?>
        <form method="post" action="<?php echo $admin_url; ?>" class="<?php echo $selected_role == $key ? '' : 'hidden'; ?>" data-subnav-target="<?php echo $key; ?>">

          <input type="hidden" name="action" value="fictioneer_roles_update_role">
          <input type="hidden" name="role" value="<?php echo $key; ?>">
          <?php echo $update_role_nonce; ?>

          <div class="columns-layout two-columns">
            <?php
              foreach ( $all_caps as $caps ) {
                fictioneer_admin_capability_card( $caps[0], $caps[1], $role );
              }
            ?>
          </div>

          <div class="flex flex-wrap gap-8 space-between">
            <button type="submit" class="button button-primary">
              <?php printf( _x( 'Update %s', 'Update {Role}', 'fictioneer' ), $role['name'] ); ?>
            </button>

            <div class="flex flex-wrap gap-8">

              <button type="button" class="button button-secondary" data-dialog-target="add-role-dialog"><?php _e( 'Add Role', 'fictioneer' ); ?></button>

              <?php if ( ! in_array( $role['name'], ['Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber'] ) ) : ?>

                <?php
                  $remove_action = 'fictioneer_remove_role';
                  $remove_link = wp_nonce_url(
                    add_query_arg(
                      'role',
                      $key,
                      admin_url( "admin-post.php?action={$remove_action}" )
                    ),
                    $remove_action,
                    'fictioneer_nonce'
                  );
                ?>

                <a href="<?php echo $remove_link; ?>" class="button button-secondary confirm-dialog" data-dialog-message="<?php printf( $remove_message, $role['name'], $remove_confirm ); ?>" data-dialog-confirm="<?php echo $remove_confirm; ?>">
                  <?php printf( _x( 'Remove %s', 'Remove {Role}', 'fictioneer' ), $role['name'] ); ?>
                </a>

              <?php endif; ?>

            </div>
          </div>

        </form>
      <?php endforeach; ?>

    </div>
  </div>

  <dialog class="fictioneer-dialog" id="add-role-dialog">

    <div class="fictioneer-dialog__header">
      <span><?php _e( 'Add Role', 'fictioneer' ); ?></span>
    </div>

    <div class="fictioneer-dialog__content">
      <form method="post" action="<?php echo $admin_url; ?>">

        <input type="hidden" name="action" value="fictioneer_add_role">
        <?php echo $add_role_nonce; ?>

        <div class="text-input">
          <label for="fictioneer_add_role">
            <input id="fictioneer_add_role" name="new_role" placeholder="<?php _ex( 'Role Name', 'fictioneer' ); ?>" type="text" required>
            <p class="sub-label"><?php _e( 'Enter the name of the new role.', 'fictioneer' ) ?></p>
          </label>
        </div>

        <div class="fictioneer-dialog__actions">
          <button value="" class="button button-primary"><?php _e( 'Add', 'fictioneer' ); ?></button>
          <button value="cancel" formmethod="dialog" class="button"><?php _e( 'Cancel', 'fictioneer' ); ?></button>
        </div>

      </form>
    </div>

  </dialog>

</div>
