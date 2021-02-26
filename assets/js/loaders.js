// Loaders
window.activateLoader=function( type, parent_selector, scroll = false ) {
  // Add class for body
  var body_classes = ( scroll ) ? 'active-loader loader-no-scroll' : 'active-loader';
  jQuery( 'body' ).addClass( body_classes );

  jQuery( parent_selector ).addClass( type + '-loader' );
}

window.disactivateLoader=function( type, parent_selector ) {
  // Remove class from body
  jQuery( 'body' ).removeClass( 'active-loader' ).removeClass( 'loader-no-scroll' );

  jQuery( parent_selector ).removeClass( type + '-loader' );
}