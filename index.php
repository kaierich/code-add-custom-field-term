// REGISTER TERM META
add_action( 'init', '___register_term_meta_text' );
function ___register_term_meta_text() {
    register_meta( 'term', '__term_meta_text', '___sanitize_term_meta_text' );
}
// SANITIZE DATA
function ___sanitize_term_meta_text ( $value ) {
    return sanitize_text_field ($value);
}
// GETTER (will be sanitized)
function ___get_term_meta_text( $term_id ) {
  $value = get_term_meta( $term_id, '__term_meta_text', true );
  $value = ___sanitize_term_meta_text( $value );
  return $value;
}
// ADD FIELD TO CATEGORY TERM PAGE
add_action( 'product_cat_add_form_fields', '___add_form_field_term_meta_text' );
function ___add_form_field_term_meta_text() { ?>
    <?php wp_nonce_field( basename( __FILE__ ), 'term_meta_text_nonce' ); ?>
    <div class="form-field term-meta-text-wrap">
        <label for="term-meta-text"><?php _e( 'TERM META TEXT', 'text_domain' ); ?></label>
        <input type="text" name="term_meta_text" id="term-meta-text" value="" class="term-meta-text-field" />
    </div>
<?php }
// ADD FIELD TO CATEGORY EDIT PAGE
add_action( 'product_cat_edit_form_fields', '___edit_form_field_term_meta_text' );
function ___edit_form_field_term_meta_text( $term ) {
    $value  = ___get_term_meta_text( $term->term_id );
    if ( ! $value )
        $value = ""; ?>

    <tr class="form-field term-meta-text-wrap">
        <th scope="row"><label for="term-meta-text"><?php _e( 'TERM META TEXT', 'text_domain' ); ?></label></th>
        <td>
            <?php wp_nonce_field( basename( __FILE__ ), 'term_meta_text_nonce' ); ?>
            <input type="text" name="term_meta_text" id="term-meta-text" value="<?php echo esc_attr( $value ); ?>" class="term-meta-text-field"  />
        </td>
    </tr>
<?php }
// SAVE TERM META (on term edit & create)
add_action( 'edit_product_cat',   '___save_term_meta_text' );
add_action( 'create_product_cat', '___save_term_meta_text' );
function ___save_term_meta_text( $term_id ) {
    // verify the nonce --- remove if you don't care
    if ( ! isset( $_POST['term_meta_text_nonce'] ) || ! wp_verify_nonce( $_POST['term_meta_text_nonce'], basename( __FILE__ ) ) )
        return;
    $old_value  = ___get_term_meta_text( $term_id );
    $new_value = isset( $_POST['term_meta_text'] ) ? ___sanitize_term_meta_text ( $_POST['term_meta_text'] ) : '';
    if ( $old_value && '' === $new_value )
        delete_term_meta( $term_id, '__term_meta_text' );
    else if ( $old_value !== $new_value )
        update_term_meta( $term_id, '__term_meta_text', $new_value );
}


// MODIFY COLUMNS (add our meta to the list)
add_filter( 'manage_edit-product_cat_columns', '___edit_term_columns', 10, 3 );
function ___edit_term_columns( $columns ) {
    $columns['__term_meta_text'] = __( 'TERM META TEXT', 'text_domain' );
    return $columns;
}
// RENDER COLUMNS (render the meta data on a column)
add_filter( 'manage_product_cat_custom_column', '___manage_term_custom_column', 10, 3 );
function ___manage_term_custom_column( $out, $column, $term_id ) {
    if ( '__term_meta_text' === $column ) {
        $value  = ___get_term_meta_text( $term_id );
        if ( ! $value )
            $value = '';
        $out = sprintf( '<span class="term-meta-text-block" style="" >%s</div>', esc_attr( $value ) );
    }
    return $out;
}
